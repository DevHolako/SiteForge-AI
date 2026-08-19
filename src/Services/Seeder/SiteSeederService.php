<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\Seeder;

class SiteSeederService
{
    public const AI_GENERATED_META = '_siteforge_ai_generated';
    public const BATCH_ID_META     = '_siteforge_ai_batch_id';

    public function __construct(
        private readonly BlockBuilder $blockBuilder
    ) {
    }

    /**
     * Seed complete site content and styles from blueprint.
     *
     * @param array<string, mixed> $blueprint
     * @return array<string, mixed> Snapshot containing all generated IDs for rollback
     */
    public function seed(array $blueprint): array
    {
        $batchId = uniqid('siteforge_batch_');
        $snapshot = [
            'batch_id'   => $batchId,
            'created_at' => time(),
            'pages'      => [],
            'posts'      => [],
            'menus'      => [],
            'categories' => [],
            'site'       => [],
        ];

        do_action('siteforge_ai_before_site_seed', $blueprint, $batchId);

        // 1. Seed Site Identity (Title & Tagline)
        if (!empty($blueprint['site']) && is_array($blueprint['site'])) {
            $snapshot['site'] = $this->seedSiteIdentity($blueprint['site']);
        }

        // 2. Seed Pages (with AI meta tagging)
        $createdPages = [];
        if (!empty($blueprint['pages']) && is_array($blueprint['pages'])) {
            $createdPages = $this->seedPages($blueprint['pages'], $batchId);
            $snapshot['pages'] = array_values($createdPages);
        }

        // 3. Seed Posts (with AI meta tagging)
        if (!empty($blueprint['posts']) && is_array($blueprint['posts'])) {
            $snapshot['posts'] = $this->seedPosts($blueprint['posts'], $batchId);
        }

        // 4. Seed Navigation Menu
        if (!empty($blueprint['navigation']) && is_array($blueprint['navigation'])) {
            $menuId = $this->seedNavigation($blueprint['navigation'], $createdPages, $batchId);
            if ($menuId > 0) {
                $snapshot['menus'][] = $menuId;
            }
        }

        // 5. Seed Customizer Colors & Typography
        if (!empty($blueprint['customizer']) && is_array($blueprint['customizer'])) {
            $this->seedCustomizer($blueprint['customizer']);
        }

        do_action('siteforge_ai_after_site_seed', $snapshot, $blueprint);

        return $snapshot;
    }

    /**
     * Seed site title and tagline.
     *
     * @param array<string, mixed> $site
     * @return array<string, string>
     */
    public function seedSiteIdentity(array $site): array
    {
        $oldTitle = (string) get_option('blogname');
        $oldTagline = (string) get_option('blogdescription');

        if (!empty($site['title'])) {
            update_option('blogname', sanitize_text_field((string) $site['title']));
        }
        if (!empty($site['tagline'])) {
            update_option('blogdescription', sanitize_text_field((string) $site['tagline']));
        }

        return [
            'previous_title'   => $oldTitle,
            'previous_tagline' => $oldTagline,
            'new_title'        => (string) ($site['title'] ?? $oldTitle),
            'new_tagline'      => (string) ($site['tagline'] ?? $oldTagline),
        ];
    }

    /**
     * Seed WordPress pages with Gutenberg blocks and AI metadata flags.
     *
     * @param array<int, array<string, mixed>> $pages
     * @param string $batchId
     * @return array<string, int> Map of slug => page_id
     */
    public function seedPages(array $pages, string $batchId = ''): array
    {
        $pageMap = [];
        $frontPageId = null;

        foreach ($pages as $pageData) {
            $title = sanitize_text_field((string) ($pageData['title'] ?? 'Untitled Page'));
            $slug = sanitize_title((string) ($pageData['slug'] ?? $title));
            $isFrontPage = !empty($pageData['is_front_page']);

            $sections = (array) ($pageData['sections'] ?? []);
            $blockContent = $this->blockBuilder->renderSections($sections);

            $pageId = wp_insert_post([
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_content' => $blockContent,
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);

            if (is_wp_error($pageId) || $pageId === 0) {
                continue;
            }

            $pageIdInt = (int) $pageId;
            $pageMap[$slug] = $pageIdInt;

            // 🏷️ Flag post with AI meta so real user pages are never accidentally wiped
            update_post_meta($pageIdInt, self::AI_GENERATED_META, 1);
            if (!empty($batchId)) {
                update_post_meta($pageIdInt, self::BATCH_ID_META, $batchId);
            }

            // Track Homepage
            if ($isFrontPage && $frontPageId === null) {
                $frontPageId = $pageIdInt;
            }
        }

        // Configure Static Front Page if a homepage was seeded
        if ($frontPageId !== null) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $frontPageId);
        }

        return $pageMap;
    }

    /**
     * Seed sample blog posts with AI metadata flags.
     *
     * @param array<int, array<string, mixed>> $posts
     * @param string $batchId
     * @return array<int, int> List of created post IDs
     */
    public function seedPosts(array $posts, string $batchId = ''): array
    {
        $createdIds = [];

        foreach ($posts as $postData) {
            $title = sanitize_text_field((string) ($postData['title'] ?? 'Sample Article'));
            $slug = sanitize_title((string) ($postData['slug'] ?? $title));
            $excerpt = sanitize_text_field((string) ($postData['excerpt'] ?? ''));
            $content = (string) ($postData['content'] ?? '');
            $categoryName = sanitize_text_field((string) ($postData['category'] ?? 'General'));

            // Ensure category exists
            $catId = 0;
            if (!empty($categoryName)) {
                $term = term_exists($categoryName, 'category');
                if (!$term) {
                    $term = wp_insert_term($categoryName, 'category');
                }
                if (is_array($term) && isset($term['term_id'])) {
                    $catId = (int) $term['term_id'];
                }
            }

            $formattedContent = sprintf("<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->", esc_html($content));

            $postId = wp_insert_post([
                'post_title'    => $title,
                'post_name'     => $slug,
                'post_excerpt'  => $excerpt,
                'post_content'  => $formattedContent,
                'post_status'   => 'publish',
                'post_type'     => 'post',
                'post_category' => $catId > 0 ? [$catId] : [],
            ]);

            if (!is_wp_error($postId) && $postId > 0) {
                $postIdInt = (int) $postId;
                $createdIds[] = $postIdInt;

                // 🏷️ Flag post with AI meta
                update_post_meta($postIdInt, self::AI_GENERATED_META, 1);
                if (!empty($batchId)) {
                    update_post_meta($postIdInt, self::BATCH_ID_META, $batchId);
                }
            }
        }

        return $createdIds;
    }

    /**
     * Seed WordPress Primary Navigation Menu.
     *
     * @param array<int, array<string, string>> $navigation
     * @param array<string, int> $pageMap
     * @param string $batchId
     * @return int Menu ID
     */
    public function seedNavigation(array $navigation, array $pageMap = [], string $batchId = ''): int
    {
        $menuName = 'SiteForge AI Menu';
        $menuExists = wp_get_nav_menu_object($menuName);

        $menuId = $menuExists ? (int) $menuExists->term_id : (int) wp_create_nav_menu($menuName);

        if (is_wp_error($menuId) || $menuId <= 0) {
            return 0;
        }

        update_term_meta($menuId, self::AI_GENERATED_META, 1);
        if (!empty($batchId)) {
            update_term_meta($menuId, self::BATCH_ID_META, $batchId);
        }

        $order = 1;
        foreach ($navigation as $item) {
            $label = sanitize_text_field((string) ($item['label'] ?? 'Link'));
            $url = esc_url_raw((string) ($item['url'] ?? '/'));

            // Check if URL matches one of our created pages
            $slug = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
            $pageId = $pageMap[$slug] ?? 0;

            if ($pageId > 0) {
                wp_update_nav_menu_item($menuId, 0, [
                    'menu-item-title'     => $label,
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $pageId,
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                    'menu-item-position'  => $order++,
                ]);
            } else {
                wp_update_nav_menu_item($menuId, 0, [
                    'menu-item-title'     => $label,
                    'menu-item-url'       => $url,
                    'menu-item-type'      => 'custom',
                    'menu-item-status'    => 'publish',
                    'menu-item-position'  => $order++,
                ]);
            }
        }

        // Assign to primary theme menu location
        $locations = (array) get_nav_menu_locations();
        $registeredLocations = array_keys(get_registered_nav_menus());
        $primaryKey = in_array('primary', $registeredLocations, true) ? 'primary' : (in_array('main', $registeredLocations, true) ? 'main' : ($registeredLocations[0] ?? 'primary'));

        $locations[$primaryKey] = $menuId;
        set_theme_mod('nav_menu_locations', $locations);

        return $menuId;
    }

    /**
     * Seed customizer colors and styles.
     *
     * @param array<string, mixed> $customizer
     */
    public function seedCustomizer(array $customizer): void
    {
        $colors = (array) ($customizer['colors'] ?? []);
        if (empty($colors)) {
            return;
        }

        $primary = sanitize_hex_color((string) ($colors['primary'] ?? '#1E40AF')) ?: '#1E40AF';
        $secondary = sanitize_hex_color((string) ($colors['secondary'] ?? '#3B82F6')) ?: '#3B82F6';
        $accent = sanitize_hex_color((string) ($colors['accent'] ?? '#F59E0B')) ?: '#F59E0B';

        $customCss = <<<CSS
:root {
    --siteforge-color-primary: {$primary};
    --siteforge-color-secondary: {$secondary};
    --siteforge-color-accent: {$accent};
}
.has-primary-background-color { background-color: {$primary} !important; }
.has-secondary-background-color { background-color: {$secondary} !important; }
.has-accent-background-color { background-color: {$accent} !important; }
CSS;

        $existingCss = (string) wp_get_custom_css();
        wp_update_custom_css_post($existingCss . "\n" . $customCss);
    }

    /**
     * Remove ONLY AI-generated mock data (pages, posts, menus) while leaving real user content intact.
     *
     * @param string|null $batchId Optional batch ID to target a specific generation run
     * @return array<string, int> Summary of removed items
     */
    public function removeMockData(?string $batchId = null): array
    {
        $metaQuery = [
            [
                'key'   => self::AI_GENERATED_META,
                'value' => '1',
            ],
        ];

        if (!empty($batchId)) {
            $metaQuery[] = [
                'key'   => self::BATCH_ID_META,
                'value' => $batchId,
            ];
        }

        $posts = get_posts([
            'post_type'   => ['page', 'post'],
            'post_status' => 'any',
            'numberposts' => -1,
            'meta_query'  => $metaQuery,
        ]);

        $summary = [
            'pages_deleted' => 0,
            'posts_deleted' => 0,
            'menus_deleted' => 0,
        ];

        foreach ($posts as $post) {
            $isPage = $post->post_type === 'page';
            wp_delete_post($post->ID, true); // Bypass trash

            if ($isPage) {
                $summary['pages_deleted']++;
            } else {
                $summary['posts_deleted']++;
            }
        }

        // Clean up AI generated menus
        $menus = wp_get_nav_menus();
        foreach ($menus as $menu) {
            $isAiGenerated = get_term_meta($menu->term_id, self::AI_GENERATED_META, true);
            if ($isAiGenerated === '1' || $isAiGenerated === 1) {
                wp_delete_nav_menu($menu->term_id);
                $summary['menus_deleted']++;
            }
        }

        return $summary;
    }
}
