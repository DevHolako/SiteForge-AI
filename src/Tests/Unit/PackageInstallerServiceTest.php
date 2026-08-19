<?php

declare(strict_types=1);

use SiteForgeAI\Services\Installer\PluginInstallerService;
use SiteForgeAI\Services\Installer\ThemeInstallerService;
use SiteForgeAI\Services\Installer\PackageInstallerService;

test('it checks installed and active plugins correctly', function () {
    $service = new PluginInstallerService();

    // The current active plugin is Site-Forge-AI
    $installed = $service->isInstalled('Site-Forge-AI');
    expect($installed)->toBeString();

    // Check a non-existent plugin
    $notInstalled = $service->isInstalled('non_existent_fake_plugin_xyz');
    expect($notInstalled)->toBeNull();
});

test('it checks installed and active themes correctly', function () {
    $service = new ThemeInstallerService();

    $currentTheme = $service->getCurrentTheme();
    expect($currentTheme)->toBeArray()
        ->toHaveKeys(['name', 'stylesheet', 'template', 'version']);

    // Check if the current theme is recognized as installed and active
    $stylesheet = $currentTheme['stylesheet'];
    expect($service->isInstalled($stylesheet))->toBeTrue()
        ->and($service->isActive($stylesheet))->toBeTrue();

    // Non-existent theme
    expect($service->isInstalled('fake_theme_12345_xyz'))->toBeFalse();
});

test('it orchestrates batch installation correctly', function () {
    $fakePluginInstaller = new class () extends PluginInstallerService {
        public function install(string $slug, bool $activate = true): array
        {
            return ['type' => 'plugin', 'slug' => $slug, 'installed' => true, 'active' => true];
        }
    };

    $fakeThemeInstaller = new class () extends ThemeInstallerService {
        public function install(string $slug, bool $activate = true): array
        {
            return ['type' => 'theme', 'slug' => $slug, 'installed' => true, 'active' => true];
        }
    };

    $orchestrator = new PackageInstallerService($fakePluginInstaller, $fakeThemeInstaller);
    $results = $orchestrator->installBatch(['elementor'], 'astra');

    expect($results)->toBeArray()
        ->toHaveKey('theme')
        ->toHaveKey('plugins')
        ->toHaveKey('errors')
        ->and($results['theme']['slug'])->toBe('astra')
        ->and($results['plugins']['elementor']['installed'])->toBeTrue();
});
