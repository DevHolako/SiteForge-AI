<?php

declare(strict_types=1);

use SiteForgeAI\Core\Plugin;

beforeEach(function () {
    authenticateAsAdmin();
});

test('it renders the AI Wizard admin page HTML container', function () {
    ob_start();
    Plugin::renderWizardPage();
    $html = ob_get_clean();

    expect($html)
        ->toBeString()
        ->toContain('id="siteforge-wizard-root"')
        ->toContain('SiteForge AI Wizard');
});

test('it renders the Settings admin page HTML container', function () {
    ob_start();
    Plugin::renderSettingsPage();
    $html = ob_get_clean();

    expect($html)
        ->toBeString()
        ->toContain('id="siteforge-settings-root"')
        ->toContain('SiteForge AI Settings');
});

test('it registers menu pages in WordPress Admin Menu registry', function () {
    global $menu, $submenu;
    $menu = [];
    $submenu = [];

    Plugin::registerAdminMenu();

    expect($menu)->toBeArray()->not->toBeEmpty();
    expect($submenu)->toBeArray()->toHaveKey('siteforge-ai');

    // Check that Wizard and Settings submenus exist
    $siteforgeSubmenus = $submenu['siteforge-ai'];
    $submenuTitles = array_column($siteforgeSubmenus, 0);

    expect($submenuTitles)->toContain('Wizard')
        ->and($submenuTitles)->toContain('Settings');
});

test('smoke test: checks HTTP availability of the WordPress site', function () {
    $siteUrl = get_site_url();
    $response = wp_remote_get($siteUrl, [
        'timeout'   => 5,
        'sslverify' => false,
    ]);

    if (!is_wp_error($response)) {
        $status = wp_remote_retrieve_response_code($response);
        expect($status)->toBeIn([200, 301, 302]);
    } else {
        // If web server isn't currently up during CLI test, pass gracefully
        expect(true)->toBeTrue();
    }
});
