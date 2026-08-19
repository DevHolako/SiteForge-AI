<?php

declare(strict_types=1);

namespace SiteForgeAI\Services\Installer;

class PackageInstallerService
{
    public function __construct(
        private readonly PluginInstallerService $pluginInstaller,
        private readonly ThemeInstallerService $themeInstaller
    ) {
    }

    /**
     * Install and optionally activate a plugin.
     *
     * @param string $slug
     * @param bool $activate
     * @return array<string, mixed>
     */
    public function installPlugin(string $slug, bool $activate = true): array
    {
        return $this->pluginInstaller->install($slug, $activate);
    }

    /**
     * Install and optionally activate a theme.
     *
     * @param string $slug
     * @param bool $activate
     * @return array<string, mixed>
     */
    public function installTheme(string $slug, bool $activate = true): array
    {
        return $this->themeInstaller->install($slug, $activate);
    }

    /**
     * Batch install a theme and a collection of plugins.
     *
     * @param array<int, string|array{slug: string, required?: bool}> $plugins
     * @param string|null $theme
     * @return array<string, mixed>
     */
    public function installBatch(array $plugins = [], ?string $theme = null): array
    {
        $results = [
            'theme'   => null,
            'plugins' => [],
            'errors'  => [],
        ];

        // 1. Install Theme if provided
        if (!empty($theme)) {
            try {
                $results['theme'] = $this->themeInstaller->install($theme, true);
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'type'    => 'theme',
                    'slug'    => $theme,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // 2. Install each plugin
        foreach ($plugins as $plugin) {
            $slug = is_array($plugin) ? ($plugin['slug'] ?? '') : (string) $plugin;
            if (empty($slug)) {
                continue;
            }

            try {
                $results['plugins'][$slug] = $this->pluginInstaller->install($slug, true);
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'type'    => 'plugin',
                    'slug'    => $slug,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
