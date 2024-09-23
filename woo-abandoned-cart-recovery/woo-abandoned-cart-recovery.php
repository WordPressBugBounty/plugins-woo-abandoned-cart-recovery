<?php
/**
 * Plugin Name: Abandoned Cart Recovery for WooCommerce
 * Plugin URI: https://villatheme.com/extensions/woo-abandoned-cart-recovery/
 * Description: Capture abandoned cart & send reminder emails to the customers.
 * Version: 1.1.4
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woo-abandoned-cart-recovery
 * Domain Path: /languages
 * Copyright 2019-2024 VillaTheme.com. All rights reserved.
 * Requires Plugins: woocommerce
 * Requires at least: 5.0
 * Tested up to: 6.5
 * WC requires at least: 7.0
 * WC tested up to: 8.9
 * Requires PHP: 7.0
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect plugin. For use on Front End only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'woocommerce-abandoned-cart-recovery/woocommerce-abandoned-cart-recovery.php' ) ) {
	return;
}

define( 'WACV_VERSION', '1.1.4' );

global $wp_version;

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && version_compare( $wp_version, '5.0', '>' ) ) {

	define( 'WACV_SLUG', 'woo-abandoned-cart-recovery' );
	define( 'WACV_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WACV_LANGUAGES', WACV_DIR . "/languages" . DIRECTORY_SEPARATOR );
	define( 'WACV_INCLUDES', WACV_DIR . "/includes" . DIRECTORY_SEPARATOR );
	define( 'WACV_VIEWS', WACV_DIR . "/views" . DIRECTORY_SEPARATOR );
	define( 'WACV_TEMPLATES', WACV_INCLUDES . "templates" . DIRECTORY_SEPARATOR );
	define( 'WACV_PRO_URL', 'https://1.envato.market/roBbv' );

	$init_file = WACV_INCLUDES . "define.php";
	require_once $init_file;

	register_activation_hook( __FILE__, 'wacv_activate' );

	//Compatible with High-Performance order storage (COT)
	add_action( 'before_woocommerce_init', function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	} );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wacv_add_action_links' );

	function wacv_add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=wacv_settings' ) . '">' . esc_html__( 'Settings', 'woo-abandoned-cart-recovery' ) . '</a>',
		);

		return array_merge( $links, $settings_link );
	}

} else {
	if ( ! function_exists( 'wacv_notification' ) ) {
		function wacv_notification() {
			?>
            <div id="message" class="error">
                <p><?php esc_html_e( 'Please install and activate WooCommerce to use WooCommerce Abandoned Cart Recovery.', 'woo-abandoned-cart-recovery' ); ?></p>
            </div>
			<?php
		}
	}
	add_action( 'admin_notices', 'wacv_notification' );
}

function wacv_activate( $network_wide ) {
	require_once WACV_INCLUDES . "plugin.php";
	$wacv_plugin = \WACV\Inc\Plugin::get_instance();
	$wacv_plugin->activate( $network_wide );
}

function wacvf_activate_new_blog( $blog_id ) {
	if ( is_plugin_active_for_network( 'woo-abandoned-cart-recovery/woo-abandoned-cart-recovery.php' ) ) {
		switch_to_blog( $blog_id );
		require_once WACV_INCLUDES . "plugin.php";
		$wacv_plugin = \WACV\Inc\Plugin::get_instance();
		$wacv_plugin->single_active();
		restore_current_blog();
	}
}

add_action( 'wpmu_new_blog', 'wacvf_activate_new_blog' );

