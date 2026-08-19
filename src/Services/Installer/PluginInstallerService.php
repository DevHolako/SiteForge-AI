<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\Installer;

use Automatic_Upgrader_Skin;
use Plugin_Upgrader;
use RuntimeException;

class PluginInstallerService
{
    /**
     * Ensure WordPress plugin upgrader dependencies are loaded.
     */
    private function loadDependencies(): void
    {
        if (!function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!class_exists('Plugin_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        if (!function_exists('request_filesystem_credentials')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('wp_clean_plugins_cache')) {
            require_once ABSPATH . 'wp-admin/includes/misc.php';
        }
    }

    /**
     * Check if a plugin is installed and return its main file path.
     *
     * @param string $slug e.g. 'elementor', 'contact-form-7'
     * @return string|null e.g. 'elementor/elementor.php' or null
     */
    public function isInstalled(string $slug): ?string
    {
        $this->loadDependencies();

        $plugins = get_plugins();

        // 1. Direct match: slug/slug.php
        $standardPath = sprintf('%s/%s.php', $slug, $slug);
        if (isset($plugins[$standardPath])) {
            return $standardPath;
        }

        // 2. Directory match: slug/custom-file.php
        foreach (array_keys($plugins) as $pluginFile) {
            if (dirname((string) $pluginFile) === $slug) {
                return (string) $pluginFile;
            }
        }

        // 3. Single-file plugin: slug.php
        $singleFile = sprintf('%s.php', $slug);
        if (isset($plugins[$singleFile])) {
            return $singleFile;
        }

        return null;
    }

    /**
     * Check if a plugin is active.
     */
    public function isActive(string $pluginFile): bool
    {
        $this->loadDependencies();

        return is_plugin_active($pluginFile);
    }

    /**
     * Install and optionally activate a plugin from WordPress.org.
     *
     * @param string $slug Plugin slug (e.g. 'elementor')
     * @param bool $activate Whether to activate the plugin
     * @return array<string, mixed>
     * @throws RuntimeException If installation or activation fails.
     */
    public function install(string $slug, bool $activate = true): array
    {
        $this->loadDependencies();

        do_action('siteforge_ai_before_plugin_install', $slug);

        $pluginFile = $this->isInstalled($slug);

        // 1. Download and install if missing
        if (!$pluginFile) {
            $api = plugins_api('plugin_information', [
                'slug'   => $slug,
                'fields' => ['sections' => false],
            ]);

            if (is_wp_error($api)) {
                throw new RuntimeException(
                    sprintf(__('WordPress.org API error for plugin [%s]: %s', 'siteforge-ai'), $slug, $api->get_error_message())
                );
            }

            if (empty($api->download_link)) {
                throw new RuntimeException(
                    sprintf(__('No download link found for plugin [%s] on WordPress.org.', 'siteforge-ai'), $slug)
                );
            }

            $skin = new Automatic_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader($skin);
            $installed = $upgrader->install($api->download_link);

            if (is_wp_error($installed)) {
                throw new RuntimeException(
                    sprintf(__('Failed to install plugin [%s]: %s', 'siteforge-ai'), $slug, $installed->get_error_message())
                );
            }

            if ($installed === false) {
                $skinErrors = $skin->get_errors();
                $errorMessage = !empty($skinErrors) && is_wp_error($skinErrors) ? $skinErrors->get_error_message() : __('Unknown installation error.', 'siteforge-ai');
                throw new RuntimeException(
                    sprintf(__('Installation failed for plugin [%s]: %s', 'siteforge-ai'), $slug, $errorMessage)
                );
            }

            wp_clean_plugins_cache();
            $pluginFile = $this->isInstalled($slug);

            if (!$pluginFile) {
                throw new RuntimeException(
                    sprintf(__('Plugin [%s] was downloaded but could not locate main file.', 'siteforge-ai'), $slug)
                );
            }
        }

        // 2. Activate if requested
        if ($activate && !$this->isActive($pluginFile)) {
            $activationResult = activate_plugin($pluginFile);
            if (is_wp_error($activationResult)) {
                throw new RuntimeException(
                    sprintf(__('Failed to activate plugin [%s]: %s', 'siteforge-ai'), $slug, $activationResult->get_error_message())
                );
            }
            do_action('siteforge_ai_plugin_activated', $slug, $pluginFile);
        }

        $result = [
            'type'      => 'plugin',
            'slug'      => $slug,
            'file'      => $pluginFile,
            'installed' => true,
            'active'    => $this->isActive($pluginFile),
        ];

        do_action('siteforge_ai_plugin_installed', $slug, $result);

        return $result;
    }

    /**
     * Deactivate an active plugin.
     */
    public function deactivate(string $pluginFile): void
    {
        $this->loadDependencies();

        if ($this->isActive($pluginFile)) {
            deactivate_plugins($pluginFile);
            do_action('siteforge_ai_plugin_deactivated', $pluginFile);
        }
    }
}
