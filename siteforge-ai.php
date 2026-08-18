<?php
/**
 * Plugin Name: SiteForge AI
 * Description: SiteForge AI is a powerful AI-driven plugin that helps users generate website content and recommends the best plugins and themes based on their requirements/promtes. It leverages advanced AI algorithms to analyze user input and provide tailored suggestions, making website creation easier and more efficient.
 * Version: 0.0.1
 * Requires PHP: 8.1
 * Author: Rguibi Marouane aka (DevHolako)
 * Author URI: https://www.holako.dev
 * Text Domain: siteforge-ai
 */

// Pas d'accès direct.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Constantes du plugin.
define( 'SITEFORGE_AI_VERSION', '0.0.1' );
define( 'SITEFORGE_AI_FILE', __FILE__ );
define( 'SITEFORGE_AI_DIR', plugin_dir_path( __FILE__ ) );
define( 'SITEFORGE_AI_URL', plugin_dir_url( __FILE__ ) );
// Préfixe commun aux options, transients et hooks.
define( 'SITEFORGE_AI_PREFIX', 'siteforge_ai_' );

// Namespace REST + type de site.
define( 'SITEFORGE_AI_REST_NAMESPACE', 'siteforge_ai/v1' );
define( 'SITEFORGE_AI_SITE_TYPE', 'wordpress' );

// Autoloader PSR-4
require_once SITEFORGE_AI_DIR . 'src/Core/Autoloader.php';
\SiteForgeAI\Core\Autoloader::register();

// Global Helpers
require_once SITEFORGE_AI_DIR . 'src/Support/helpers.php';

register_activation_hook( __FILE__, [ \SiteForgeAI\Core\Plugin::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \SiteForgeAI\Core\Plugin::class, 'deactivate' ] );


\SiteForgeAI\Core\Plugin::boot();
