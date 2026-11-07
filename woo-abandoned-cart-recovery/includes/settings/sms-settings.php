<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 08-06-19
 * Time: 2:35 PM
 */

namespace WACV\Inc\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SMS_Settings extends Admin_Settings {

	protected static $instance = null;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setting_page() {
		?>
        <div class="vi-ui bottom attached tab segment tab-admin" data-tab="sms">
            <div class="vi-ui info message">
                <div class="header"><?php esc_html_e( 'How SMS reminders work', 'woo-abandoned-cart-recovery' ); ?></div>
                <p><?php esc_html_e( 'SMS is sent by WP-Cron using your provider account. The guest or member must have a valid phone and billing country on the cart (or order). Recover URLs in SMS use the same General → Recover link to setting. Enable Bitly if you need a shorter URL. GSM-7 messages are limited to 160 characters; other encodings (emoji) are shorter.', 'woo-abandoned-cart-recovery' ); ?></p>
            </div>
            <h4><?php esc_html_e( 'SMS account config', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'SMS provider', 'woo-abandoned-cart-recovery' ), esc_html__( 'Choose one gateway (Twilio, Nexmo, Plivo, Infobip). Fill the matching credentials. An unsupported saved provider will not send until you pick a listed option and save.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'App ID', 'woo-abandoned-cart-recovery' ), esc_html__( 'Provider account SID / API key / Auth ID, depending on the selected gateway.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'App token', 'woo-abandoned-cart-recovery' ), esc_html__( 'Provider auth token or API secret. Keep this private.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'From number', 'woo-abandoned-cart-recovery' ), esc_html__( 'Sender number in E.164 format (plus country code). Must be a number you own with the provider.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>

            <h4><?php esc_html_e( 'Bitly config', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'Use shortlink', 'woo-abandoned-cart-recovery' ), esc_html__( 'Shorten recover URLs with Bitly so SMS stays under the character limit.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Access token', 'woo-abandoned-cart-recovery' ), esc_html__( 'Bitly generic access token (Settings → Generate Token). Keep this private.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>

            <h4><?php esc_html_e( 'Send test SMS', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'To number', 'woo-abandoned-cart-recovery' ), esc_html__( 'Send a test SMS to verify provider credentials before going live.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
            <hr>

            <h4><?php esc_html_e( 'SMS for Abandoned Cart', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'Enable', 'woo-abandoned-cart-recovery' ), esc_html__( 'Send SMS for abandoned carts after cut-off time plus each rule delay. Requires a valid phone on the cart record.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Rules', 'woo-abandoned-cart-recovery' ), esc_html__( 'Each row is one SMS. Send after starts when the cart is abandoned. Keep messages short (160 GSM-7 characters).', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
            <hr>

            <h4><?php esc_html_e( 'SMS for Abandoned Order', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'Enable', 'woo-abandoned-cart-recovery' ), esc_html__( 'Send SMS for unpaid WooCommerce orders in the statuses you select. Independent from abandoned-cart SMS. HPOS is supported.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Order status', 'woo-abandoned-cart-recovery' ), esc_html__( 'Only orders in these statuses receive SMS. Do not include paid statuses.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Rules', 'woo-abandoned-cart-recovery' ), esc_html__( 'Delays are counted from the order modified/created time used by the reminder query.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
        </div>
		<?php
	}

}
