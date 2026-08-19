<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\Installer;

use Automatic_Upgrader_Skin;
use RuntimeException;
use Theme_Upgrader;

class ThemeInstallerService
{
    /**
     * Ensure WordPress theme upgrader dependencies are loaded.
     */
    private function loadDependencies(): void
    {
        if (!function_exists('themes_api')) {
            require_once ABSPATH . 'wp-admin/includes/theme-install.php';
        }
        if (!function_exists('switch_theme')) {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
        }
        if (!class_exists('Theme_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        if (!function_exists('request_filesystem_credentials')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('wp_clean_themes_cache')) {
            require_once ABSPATH . 'wp-admin/includes/misc.php';
        }
    }

    /**
     * Check if a theme is installed on the site.
     */
    public function isInstalled(string $slug): bool
    {
        $this->loadDependencies();

        $theme = wp_get_theme($slug);

        return $theme->exists();
    }

    /**
     * Check if a theme is currently active.
     */
    public function isActive(string $slug): bool
    {
        return get_stylesheet() === $slug || get_template() === $slug;
    }

    /**
     * Get details of the currently active theme.
     *
     * @return array<string, string>
     */
    public function getCurrentTheme(): array
    {
        $theme = wp_get_theme();

        return [
            'name'       => $theme->get('Name'),
            'stylesheet' => $theme->get_stylesheet(),
            'template'   => $theme->get_template(),
            'version'    => $theme->get('Version'),
        ];
    }

    /**
     * Install and optionally switch to a theme from WordPress.org.
     *
     * @param string $slug Theme slug (e.g. 'astra', 'neve', 'generatepress')
     * @param bool $activate Whether to switch to this theme after installation
     * @return array<string, mixed>
     * @throws RuntimeException If download, installation, or switching fails.
     */
    public function install(string $slug, bool $activate = true): array
    {
        $this->loadDependencies();

        do_action('siteforge_ai_before_theme_install', $slug);

        // 1. Download & Install if missing
        if (!$this->isInstalled($slug)) {
            $api = themes_api('theme_information', [
                'slug'   => $slug,
                'fields' => ['sections' => false],
            ]);

            if (is_wp_error($api)) {
                throw new RuntimeException(
                    sprintf(__('WordPress.org API error for theme [%s]: %s', 'siteforge-ai'), $slug, $api->get_error_message())
                );
            }

            if (empty($api->download_link)) {
                throw new RuntimeException(
                    sprintf(__('No download link found for theme [%s] on WordPress.org.', 'siteforge-ai'), $slug)
                );
            }

            $skin = new Automatic_Upgrader_Skin();
            $upgrader = new Theme_Upgrader($skin);
            $installed = $upgrader->install($api->download_link);

            if (is_wp_error($installed)) {
                throw new RuntimeException(
                    sprintf(__('Failed to install theme [%s]: %s', 'siteforge-ai'), $slug, $installed->get_error_message())
                );
            }

            if ($installed === false) {
                $skinErrors = $skin->get_errors();
                $errorMessage = !empty($skinErrors) && is_wp_error($skinErrors) ? $skinErrors->get_error_message() : __('Unknown theme installation error.', 'siteforge-ai');
                throw new RuntimeException(
                    sprintf(__('Installation failed for theme [%s]: %s', 'siteforge-ai'), $slug, $errorMessage)
                );
            }

            wp_clean_themes_cache();
        }

        // 2. Switch theme if requested
        if ($activate && !$this->isActive($slug)) {
            $this->switch($slug);
        }

        $result = [
            'type'      => 'theme',
            'slug'      => $slug,
            'installed' => true,
            'active'    => $this->isActive($slug),
        ];

        do_action('siteforge_ai_theme_installed', $slug, $result);

        return $result;
    }

    /**
     * Switch active theme to specified theme slug.
     */
    public function switch(string $slug): void
    {
        $this->loadDependencies();

        if ($this->isInstalled($slug)) {
            switch_theme($slug);
            do_action('siteforge_ai_theme_switched', $slug);
        } else {
            throw new RuntimeException(
                sprintf(__('Cannot switch to theme [%s] because it is not installed.', 'siteforge-ai'), $slug)
            );
        }
    }
}
