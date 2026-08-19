<?php

declare(strict_types=1);

namespace SiteForgeAI\Services;

use RuntimeException;
use SiteForgeAI\Services\Installer\PluginInstallerService;
use SiteForgeAI\Services\Installer\ThemeInstallerService;

class RollbackService
{
    public const SNAPSHOTS_OPTION = 'siteforge_ai_snapshots';

    public function __construct(
        private readonly ?PluginInstallerService $pluginInstaller = null,
        private readonly ?ThemeInstallerService $themeInstaller = null
    ) {
    }

    /**
     * Get all recorded snapshots.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSnapshots(): array
    {
        return (array) get_option(self::SNAPSHOTS_OPTION, []);
    }

    /**
     * Record a new snapshot into history (keeps the 10 most recent).
     *
     * @param array<string, mixed> $snapshot
     */
    public function recordSnapshot(array $snapshot): void
    {
        $batchId = (string) ($snapshot['batch_id'] ?? uniqid('batch_'));
        $snapshots = $this->getSnapshots();

        $snapshots[$batchId] = $snapshot;

        // Keep last 10
        if (count($snapshots) > 10) {
            $snapshots = array_slice($snapshots, -10, 10, true);
        }

        update_option(self::SNAPSHOTS_OPTION, $snapshots);
    }

    /**
     * Get the most recent snapshot.
     *
     * @return array<string, mixed>|null
     */
    public function getLatestSnapshot(): ?array
    {
        $snapshots = $this->getSnapshots();
        if (empty($snapshots)) {
            return null;
        }

        uasort($snapshots, fn ($a, $b) => ($a['created_at'] ?? 0) <=> ($b['created_at'] ?? 0));

        return end($snapshots) ?: null;
    }

    /**
     * Revert all content and settings created during a generation batch.
     *
     * @param string|null $batchId Specific batch ID or null for latest
     * @return array<string, mixed>
     * @throws RuntimeException If no snapshot found
     */
    public function rollback(?string $batchId = null): array
    {
        $snapshots = $this->getSnapshots();

        if (empty($snapshots)) {
            throw new RuntimeException(__('No generation snapshots found to roll back.', 'siteforge-ai'));
        }

        $targetBatchId = $batchId ?: (string) array_key_last($snapshots);

        if (!isset($snapshots[$targetBatchId])) {
            throw new RuntimeException(sprintf(__('Snapshot batch [%s] not found.', 'siteforge-ai'), $targetBatchId));
        }

        $snapshot = $snapshots[$targetBatchId];
        $reverted = [
            'pages_deleted' => 0,
            'posts_deleted' => 0,
            'menus_deleted' => 0,
            'theme_restored' => false,
            'site_restored' => false,
        ];

        do_action('siteforge_ai_before_rollback', $snapshot);

        // 1. Delete Created Pages (force delete, bypass trash)
        $pages = (array) ($snapshot['pages'] ?? []);
        foreach ($pages as $pageId) {
            if ($pageId > 0) {
                wp_delete_post((int) $pageId, true);
                $reverted['pages_deleted']++;
            }
        }

        // 2. Delete Created Posts
        $posts = (array) ($snapshot['posts'] ?? []);
        foreach ($posts as $postId) {
            if ($postId > 0) {
                wp_delete_post((int) $postId, true);
                $reverted['posts_deleted']++;
            }
        }

        // 3. Delete Created Menus
        $menus = (array) ($snapshot['menus'] ?? []);
        foreach ($menus as $menuId) {
            if ($menuId > 0) {
                wp_delete_nav_menu((int) $menuId);
                $reverted['menus_deleted']++;
            }
        }

        // 4. Restore Site Title & Tagline
        $site = (array) ($snapshot['site'] ?? []);
        if (!empty($site['previous_title'])) {
            update_option('blogname', $site['previous_title']);
            $reverted['site_restored'] = true;
        }
        if (!empty($site['previous_tagline'])) {
            update_option('blogdescription', $site['previous_tagline']);
            $reverted['site_restored'] = true;
        }

        // 5. Restore Previous Theme if applicable
        $theme = (array) ($snapshot['theme'] ?? []);
        if (!empty($theme['previous']) && $this->themeInstaller !== null) {
            try {
                $this->themeInstaller->switch((string) $theme['previous']);
                $reverted['theme_restored'] = true;
            } catch (\Throwable) {
                // Silently ignore if previous theme was deleted
            }
        }

        // 6. Remove Snapshot from history
        unset($snapshots[$targetBatchId]);
        update_option(self::SNAPSHOTS_OPTION, $snapshots);

        do_action('siteforge_ai_after_rollback', $targetBatchId, $reverted);

        return [
            'batch_id' => $targetBatchId,
            'reverted' => $reverted,
        ];
    }
}
