<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\Seeder;

class BlockBuilder
{
    /**
     * Convert an array of blueprint sections into valid Gutenberg block markup.
     *
     * @param array<int, array<string, mixed>> $sections
     * @return string
     */
    public function renderSections(array $sections): string
    {
        $blocks = [];

        foreach ($sections as $section) {
            $type = (string) ($section['type'] ?? 'generic');

            $blocks[] = match (strtolower($type)) {
                'hero'         => $this->renderHero($section),
                'features'     => $this->renderFeatures($section),
                'story',
                'about'        => $this->renderStory($section),
                'cta'          => $this->renderCta($section),
                'contact_info' => $this->renderContactInfo($section),
                default        => $this->renderGeneric($section),
            };
        }

        return implode("\n\n", array_filter($blocks));
    }

    /**
     * Render a modern Hero Block.
     *
     * @param array<string, mixed> $section
     */
    public function renderHero(array $section): string
    {
        $heading = esc_html((string) ($section['heading'] ?? 'Welcome to Our Website'));
        $content = esc_html((string) ($section['content'] ?? ''));
        $ctaText = esc_html((string) ($section['cta_text'] ?? 'Get Started'));
        $ctaUrl = esc_url((string) ($section['cta_url'] ?? '/contact'));

        $buttonMarkup = '';
        if (!empty($ctaText)) {
            $buttonMarkup = <<<HTML
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
    <!-- wp:button {"className":"is-style-fill"} -->
    <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="{$ctaUrl}">{$ctaText}</a></div>
    <!-- /wp:button -->
</div>
<!-- /wp:buttons -->
HTML;
        }

        return <<<HTML
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)">
    <!-- wp:heading {"textAlign":"center","level":1,"fontSize":"x-large"} -->
    <h1 class="wp-block-heading has-text-align-center has-x-large-font-size">{$heading}</h1>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center","fontSize":"medium"} -->
    <p class="has-text-align-center has-medium-font-size">{$content}</p>
    <!-- /wp:paragraph -->

    {$buttonMarkup}
</div>
<!-- /wp:group -->
HTML;
    }

    /**
     * Render a Features / Services Column Grid Block.
     *
     * @param array<string, mixed> $section
     */
    public function renderFeatures(array $section): string
    {
        $heading = esc_html((string) ($section['heading'] ?? 'Our Key Features'));
        $content = esc_html((string) ($section['content'] ?? ''));

        return <<<HTML
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
    <!-- wp:heading {"textAlign":"center","level":2} -->
    <h2 class="wp-block-heading has-text-align-center">{$heading}</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center"} -->
    <p class="has-text-align-center">{$content}</p>
    <!-- /wp:paragraph -->

    <!-- wp:columns -->
    <div class="wp-block-columns">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading">Quality & Craft</h3>
            <!-- /wp:heading -->
            <!-- wp:paragraph -->
            <p>Designed with meticulous attention to detail and high standards of execution.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading">Seamless Experience</h3>
            <!-- /wp:heading -->
            <!-- wp:paragraph -->
            <p>Intuitive, fast, and optimized for visitors across desktop and mobile devices.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":3} -->
            <h3 class="wp-block-heading">Dedicated Support</h3>
            <!-- /wp:heading -->
            <!-- wp:paragraph -->
            <p>Always here to assist with responsive and personalized client service.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
HTML;
    }

    /**
     * Render Story / About text section.
     *
     * @param array<string, mixed> $section
     */
    public function renderStory(array $section): string
    {
        $heading = esc_html((string) ($section['heading'] ?? 'Our Story'));
        $content = esc_html((string) ($section['content'] ?? ''));

        return <<<HTML
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2 class="wp-block-heading">{$heading}</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>{$content}</p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
HTML;
    }

    /**
     * Render Call to Action (CTA) block.
     *
     * @param array<string, mixed> $section
     */
    public function renderCta(array $section): string
    {
        $heading = esc_html((string) ($section['heading'] ?? 'Ready to Take the Next Step?'));
        $content = esc_html((string) ($section['content'] ?? 'Contact us today to discuss how we can help you achieve your goals.'));
        $ctaText = esc_html((string) ($section['cta_text'] ?? 'Contact Us'));
        $ctaUrl = esc_url((string) ($section['cta_url'] ?? '/contact'));

        return <<<HTML
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"primary","textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-white-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
    <!-- wp:heading {"textAlign":"center","level":2} -->
    <h2 class="wp-block-heading has-text-align-center">{$heading}</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center"} -->
    <p class="has-text-align-center">{$content}</p>
    <!-- /wp:paragraph -->

    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
    <div class="wp-block-buttons">
        <!-- wp:button {"className":"is-style-outline"} -->
        <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="{$ctaUrl}">{$ctaText}</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
</div>
<!-- /wp:group -->
HTML;
    }

    /**
     * Render Contact Information Section.
     *
     * @param array<string, mixed> $section
     */
    public function renderContactInfo(array $section): string
    {
        $heading = esc_html((string) ($section['heading'] ?? 'Get in Touch'));
        $content = esc_html((string) ($section['content'] ?? 'We would love to hear from you.'));

        return <<<HTML
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:heading {"level":2} -->
    <h2 class="wp-block-heading">{$heading}</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>{$content}</p>
    <!-- /wp:paragraph -->

    <!-- wp:columns -->
    <div class="wp-block-columns">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":4} -->
            <h4 class="wp-block-heading">📍 Address</h4>
            <!-- /wp:heading -->
            <!-- wp:paragraph -->
            <p>123 Main Street, Suite 400<br>Rome, Italy</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":4} -->
            <h4 class="wp-block-heading">📞 Phone & Email</h4>
            <!-- /wp:heading -->
            <!-- wp:paragraph -->
            <p>Phone: +1 (555) 019-2834<br>Email: info@example.com</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
HTML;
    }

    /**
     * Render Generic section fallback.
     *
     * @param array<string, mixed> $section
     */
    public function renderGeneric(array $section): string
    {
        $heading = esc_html((string) ($section['heading'] ?? ''));
        $content = esc_html((string) ($section['content'] ?? ''));

        $headingMarkup = !empty($heading) ? "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$heading}</h2>\n<!-- /wp:heading -->\n" : '';
        $contentMarkup = !empty($content) ? "<!-- wp:paragraph -->\n<p>{$content}</p>\n<!-- /wp:paragraph -->" : '';

        return <<<HTML
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    {$headingMarkup}{$contentMarkup}
</div>
<!-- /wp:group -->
HTML;
    }
}
