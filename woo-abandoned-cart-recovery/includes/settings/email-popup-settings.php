<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 13-06-19
 * Time: 9:55 AM
 */

namespace WACV\Inc\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Popup_Settings extends Admin_Settings {

	protected static $instance = null;

	public function __construct() {

	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setting_page() {
		?>
        <div class="vi-ui bottom attached tab segment tab-admin" data-tab="popup">
            <div class="vi-ui info message">
                <div class="header"><?php esc_html_e( 'How the email popup works', 'woo-abandoned-cart-recovery' ); ?></div>
                <p><?php esc_html_e( 'The popup is shown only to guests (not logged in) who do not already have a captured email. Appear on only chooses which pages can load the popup — it does not open by itself on page load, except Cart when Immediate display is enabled.', 'woo-abandoned-cart-recovery' ); ?></p>
            </div>
            <h4><?php esc_html_e( 'Get email popup', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'Appear on', 'woo-abandoned-cart-recovery' ), esc_html__( 'Choose which pages can load the popup (Home, Single product, Shop, Cart, Category, custom page IDs or post types).', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Exclude button with class', 'woo-abandoned-cart-recovery' ), esc_html__( 'Do not open the popup when the customer clicks a button that has one of these CSS classes.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Immediate display on the cart page', 'woo-abandoned-cart-recovery' ), esc_html__( 'Show the popup as soon as a guest opens the cart page, without waiting for Add to cart.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Fields display', 'woo-abandoned-cart-recovery' ), esc_html__( 'Choose which inputs appear in the popup: Email, Phone number, Google reCAPTCHA, and GDPR.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Default phone country', 'woo-abandoned-cart-recovery' ), esc_html__( 'Default calling code in the phone field. By locate IP uses WooCommerce geolocation.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Google reCAPTCHA', 'woo-abandoned-cart-recovery' ), esc_html__( 'v2 shows a checkbox; v3 is invisible and scores the request. Requires site key and secret key.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'GDPR content', 'woo-abandoned-cart-recovery' ), esc_html__( 'Consent text shown in the popup when GDPR is enabled.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Email required', 'woo-abandoned-cart-recovery' ), esc_html__( 'Customer cannot submit the popup without a valid email.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Phone number required', 'woo-abandoned-cart-recovery' ), esc_html__( 'Customer cannot submit Template 1 without a valid phone. Only applies when the phone field is enabled.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Dismiss time', 'woo-abandoned-cart-recovery' ), esc_html__( 'After the guest closes the popup without submitting, wait this many minutes before showing it again.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Redirect after Add to cart', 'woo-abandoned-cart-recovery' ), esc_html__( 'Where to send the guest after they submit the popup and add to cart.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Send email immediately after close popup', 'woo-abandoned-cart-recovery' ), esc_html__( 'Send a one-off email as soon as the guest submits the popup (separate from the delayed abandoned-cart sequence).', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>

            <h4><?php esc_html_e( 'Unsubscribe popup', 'woo-abandoned-cart-recovery' ) ?></h4>
            <div class="vi-ui info message">
                <p><?php esc_html_e( 'Shown after the customer clicks Unsubscribe in a reminder email. Choose whether they opt out of the current cart only or all future reminders for that email.', 'woo-abandoned-cart-recovery' ); ?></p>
            </div>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'Title', 'woo-abandoned-cart-recovery' ), esc_html__( 'Heading on the unsubscribe confirmation screen.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Content', 'woo-abandoned-cart-recovery' ), esc_html__( 'Message confirming they have unsubscribed.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Button label', 'woo-abandoned-cart-recovery' ), esc_html__( 'Button after the message, usually a link back to the shop.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Redirect URL', 'woo-abandoned-cart-recovery' ), esc_html__( 'URL opened when they click the button. Leave empty to use the shop page.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Unsubscribe type', 'woo-abandoned-cart-recovery' ), esc_html__( 'Current abandoned cart: stop further emails for this cart only. Customer\'s email: never send abandoned emails to that address again.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>

            <h4><?php esc_html_e( 'Design', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'Template', 'woo-abandoned-cart-recovery' ), esc_html__( 'Template 1 can include email and phone. Template 2 is email-only.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Title', 'woo-abandoned-cart-recovery' ), esc_html__( 'Main heading inside the popup.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Sub title', 'woo-abandoned-cart-recovery' ), esc_html__( 'Supporting text under the title.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Add to cart', 'woo-abandoned-cart-recovery' ), esc_html__( 'Submit button label (continues add-to-cart after the email is saved).', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Popup close text', 'woo-abandoned-cart-recovery' ), esc_html__( 'Dismiss/skip control. Closing starts Dismiss time and still allows add-to-cart without saving email.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Invalid email notice', 'woo-abandoned-cart-recovery' ), esc_html__( 'Shown when the email format is invalid or missing while Email required is on.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Invalid phone number notice', 'woo-abandoned-cart-recovery' ), esc_html__( 'Shown when the phone fails validation in Template 1.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Color settings', 'woo-abandoned-cart-recovery' ), esc_html__( 'Background, title, subtitle, button, input, and notice colors.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
        </div>
		<?php
	}

}
