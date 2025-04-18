<?php
/*
Plugin Name: Auto Featured Image (Auto Post Thumbnail)
Plugin URI: https://cm-wp.com/apt
Description: Automatically generate the Featured Image from the first image in post or any custom post type only if Featured Image is not set manually. Featured Image Generation From Title. Native image search for Elementor, Gutenberg, Classic Editor.
Version: 4.1.7
Requires PHP: 7.4
Author: Creative Motion <support@cm-wp.com>
Author URI: https://cm-wp.com
Text Domain: apt
Domain Path: /languages
*/

/**
 * Developers who contributions in the development plugin:
 *
 * Artem Prihodko
 * ---------------------------------------------------------------------------------
 * 3.4.2 - current
 *
 * Email:         webtemyk@yandex.ru
 * Personal repo: https://github.com/temyk
 * ---------------------------------------------------------------------------------
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * -----------------------------------------------------------------------------
 * CHECK REQUIREMENTS
 * Check compatibility with php and wp version of the user's site. As well as checking
 * compatibility with other plugins from Creative Motion.
 * -----------------------------------------------------------------------------
 */

// Подключаем класс проверки совместимости.
require_once dirname( __FILE__ ) . '/libs/factory/core/includes/class-factory-requirements.php';

$plugin_info = [
	'prefix'           => 'wapt_',
	'plugin_name'      => 'wbcr_apt',
	'plugin_title'     => 'Auto Featured Image',

	// Служба поддержки.
	'support_details'  => [
		'url'       => 'https://cm-wp.com',
		'pages_map' => [
			'features' => 'features',
			'pricing'  => 'features/pricing',
			'support'  => 'support',
			'docs'     => 'docs',
		],
	],

	// Настройка обновлений плагина
	'has_updates'      => true,
	'updates_settings' => [
		'repository'        => 'wordpress',
		'slug'              => 'auto-post-thumbnail',
		'maybe_rollback'    => true,
		'rollback_settings' => [
			'prev_stable_version' => '0.0.0',
		],
	],

	// Настройка премиум плагина
	'has_premium'      => true,
	'license_settings' => [
		'has_updates'      => true,
		'provider'         => 'freemius',
		'slug'             => 'auto-post-thumbnail-premium',
		'plugin_id'        => '4146',
		'public_key'       => 'pk_5e3ec7615d3abb543e25ee6eb2fc7',
		'price'            => 29,
		// Настройка обновлений премиум плагина
		'updates_settings' => [
			'maybe_rollback'    => true,
			'rollback_settings' => [
				'prev_stable_version' => '0.0.0',
			],
		],
	],

	'render_adverts'   => true,
	'adverts_settings' => [
		'dashboard_widget' => true,
		'right_sidebar'    => true,
		'notice'           => true,
	],

	'load_factory_modules' => [
		[ 'libs/factory/bootstrap', 'factory_bootstrap_482', 'admin' ],
		[ 'libs/factory/forms', 'factory_forms_480', 'admin' ],
		[ 'libs/factory/pages', 'factory_pages_480', 'admin' ],
		[ 'libs/factory/templates', 'factory_templates_134', 'admin' ],
		[ 'libs/factory/freemius', 'factory_freemius_170', 'all' ],
		[ 'libs/factory/adverts', 'factory_adverts_159', 'admin' ],
		[ 'libs/factory/feedback', 'factory_feedback_131', 'admin' ],
		[ 'libs/factory/logger', 'factory_logger_149', 'all' ],
		[ 'libs/factory/processing', 'factory_processing_113', 'all' ],
	],
];

$wapt_compatibility = new Wbcr_Factory480_Requirements( __FILE__, array_merge( $plugin_info, [
	'plugin_already_activate' => defined( 'WAPT_PLUGIN_ACTIVE' ),
	'required_php_version'    => '7.4',
	'required_wp_version'     => '5.6.0',
	// 'required_clearfy_check_component' => false
] ) );

/**
 * If the plugin is compatible, then it will continue its work, otherwise it will be stopped,
 * and the user will throw a warning.
 */
if ( ! $wapt_compatibility->check() ) {
	return;
}

define( 'WAPT_PLUGIN_ACTIVE', true );
define( 'WAPT_PLUGIN_VERSION', $wapt_compatibility->get_plugin_version() );

define( 'WAPT_PLUGIN_FILE', __FILE__ );
define( 'WAPT_ABSPATH', dirname( __FILE__ ) );
define( 'WAPT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WAPT_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );
define( 'WAPT_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'WAPT_PLUGIN_DIR', dirname( __FILE__ ) );



/**
 * -----------------------------------------------------------------------------
 * PLUGIN INIT
 * -----------------------------------------------------------------------------
 */
require_once WAPT_PLUGIN_DIR . '/libs/factory/core/boot.php';

require_once WAPT_PLUGIN_DIR . '/includes/class-apt.php';
require_once WAPT_PLUGIN_DIR . '/includes/class-post-images.php';
require_once WAPT_PLUGIN_DIR . '/includes/class-plugin.php';
require_once WAPT_PLUGIN_DIR . '/includes/class-wapt-image.php';
require_once WAPT_PLUGIN_DIR . '/includes/image-search/boot.php';

try {
	new WAPT_Plugin( __FILE__, array_merge( $plugin_info, [
		'plugin_version'     => WAPT_PLUGIN_VERSION,
		'plugin_text_domain' => $wapt_compatibility->get_text_domain(),
	] ) );
} catch ( Exception $e ) {
	global $wapt_exception;

	$wapt_exception = $e;
	// Plugin wasn't initialized due to an error
	define( 'WAPT_PLUGIN_THROW_ERROR', true );

	/**
	 * Exception notice
	 */
	function wapt_exception_notice() {
		global $wapt_exception;

		$error = sprintf( 'The %s plugin has stopped. <b>Error:</b> %s Code: %s', 'Auto Featured Image', $wapt_exception->getMessage(), $wapt_exception->getCode() );
		echo '<div class="notice notice-error"><p>' . wp_kses_post( $error ) . '</p></div>';
	}

	add_action( 'admin_notices', 'wapt_exception_notice' );
	add_action( 'network_admin_notices', 'wapt_exception_notice' );
}
