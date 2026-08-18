<?php
declare(strict_types=1);


namespace SiteForgeAI\Core;

/**
 * Class Plugin
 * @package SiteForgeAI\Core
 */

class Plugin
{


    public static function activate(): void
    {
        // safty checks
        self::saftyChecks();

        // set default options if not set
        self::setDefaultOptions();

        //  set transient to show a notice on the next page load
        set_transient('siteforge_ai_just_activated', 1, 30);
    }

    public static function deactivate(): void
    {
        // Clean up temporary transient
        delete_transient('siteforge_ai_just_activated');

    }


    public static function boot(): void
    {

        // Load plugin textdomain for translations
        add_action('init', [self::class, 'loadTextdomain']);
        // Handle onboarding redirect
        add_action('admin_init', [self::class, 'handleActivationRedirect']);
        // Register admin menu page
        add_action('admin_menu', [self::class, 'registerAdminMenu']);
        // Register REST API routes
        add_action('rest_api_init', [self::class, 'registerRestRoutes']);

    }

    public static function loadTextdomain(): void
    {
        load_plugin_textdomain(
            'siteforge-ai',
            false,
            dirname(plugin_basename(SITEFORGE_AI_FILE)) . '/languages'
        );
    }



    private static function saftyChecks(): void
    {
        // php must be 8.1 or higher
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            deactivate_plugins(plugin_basename(SITEFORGE_AI_FILE));
            wp_die(esc_html__('SiteForge AI requires PHP 8.1 or higher. Please upgrade your PHP version.', 'siteforge-ai'));
        }

        // extension checks
        $ext = ['curl', 'json', 'mbstring', 'openssl'];
        $failedExtensions = [];

        foreach ($ext as $extension) {
            if (!extension_loaded($extension)) {
                $failedExtensions[] = $extension;
            }
        }

        if (!empty($failedExtensions)) {
            deactivate_plugins(plugin_basename(SITEFORGE_AI_FILE));
            wp_die(sprintf(
                esc_html__('SiteForge AI requires the following PHP extensions to be installed and enabled: %s', 'siteforge-ai'),
                implode(', ', $failedExtensions)
            ));
        }

    }

    private static function setDefaultOptions(): void
    {
        // Set default options using global config helper
        add_option('siteforge_ai_settings', (array) siteforge_config('defaults', []));

        // Generate encryption key if it doesn't exist yet
        if (!get_option('siteforge_ai_encryption_key')) {
            $key = bin2hex(random_bytes(32));
            add_option('siteforge_ai_encryption_key', $key);
        }

        // Initialize batches option for tracking generated content if it doesn't exist yet
        if (!get_option('siteforge_ai_batches')) {
            add_option('siteforge_ai_batches', []);
        }

    }


    public static function handleActivationRedirect(): void
    {
        // 1. Only proceed if our activation transient exists
        if (!get_transient('siteforge_ai_just_activated')) {
            return;
        }

        // 2. Delete the transient immediately so it never redirects again
        delete_transient('siteforge_ai_just_activated');

        // 3. Do not redirect if activating multiple plugins simultaneously or if user is not admin
        if (isset($_GET['activate-multi']) || !current_user_can('manage_options')) {
            return;
        }

        // 4. Safe redirect to our plugin page
        wp_safe_redirect(admin_url('admin.php?page=siteforge-ai'));
        exit;
    }


    public static function registerAdminMenu(): void
    {
        add_menu_page(
            __('SiteForge AI', 'siteforge-ai'),
            __('SiteForge AI', 'siteforge-ai'),
            'manage_options',
            'siteforge-ai',
            [self::class, 'renderWizardPage'],
            'dashicons-superhero',
            30
        );

        add_submenu_page(
            'siteforge-ai',
            __('AI Site Wizard', 'siteforge-ai'),
            __('Wizard', 'siteforge-ai'),
            'manage_options',
            'siteforge-ai',
            [self::class, 'renderWizardPage']
        );

        add_submenu_page(
            'siteforge-ai',
            __('SiteForge AI Settings', 'siteforge-ai'),
            __('Settings', 'siteforge-ai'),
            'manage_options',
            'siteforge-ai-settings',
            [self::class, 'renderSettingsPage']
        );
    }

    public static function renderWizardPage(): void
    {
        echo '<div id="siteforge-wizard-root" class="wrap"><h1>' . esc_html__('SiteForge AI Wizard', 'siteforge-ai') . '</h1></div>';
    }

    public static function renderSettingsPage(): void
    {
        echo '<div id="siteforge-settings-root" class="wrap"><h1>' . esc_html__('SiteForge AI Settings', 'siteforge-ai') . '</h1></div>';
    }


    public static function registerRestRoutes(): void
    {
        Router::register();
    }



}
