    <?php
/**
 * Fired when the plugin is uninstalled.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$tables = array(
    $wpdb->prefix . 'wacv_abandoned_cart_record',
    $wpdb->prefix . 'wacv_guest_info_record',
    $wpdb->prefix . 'wacv_email_history',
    $wpdb->prefix . 'wacv_cart_log'
);

// Chạy vòng lặp xóa sạch các bảng
foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
}