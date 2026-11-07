<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 08-06-19
 * Time: 12:01 PM
 */

namespace WACV\Inc\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class General_Settings extends Admin_Settings {

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
        <div class="vi-ui bottom attached active tab segment tab-admin" data-tab="general">
            <div class="vi-ui info message">
                <div class="header"><?php esc_html_e( 'How cart tracking works', 'woo-abandoned-cart-recovery' ); ?></div>
                <p><?php esc_html_e( 'The plugin saves the cart when a customer adds, updates, or removes items. After the cut-off time below, that cart is treated as abandoned and can receive reminder emails (configured on the Email tab). Cut-off time and reminder delay are two different clocks: reminders start after the cart is already abandoned.', 'woo-abandoned-cart-recovery' ); ?></p>
                <p><?php esc_html_e( 'Logged-in customers are tracked as members (WordPress user ID). Recover links in emails do not work when you are logged in as an Administrator.', 'woo-abandoned-cart-recovery' ); ?></p>
            </div>
            <h4><?php esc_html_e( 'Cart', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->checkbox_option( 'tracking_member', esc_html__( "Tracking Member's Cart", 'woo-abandoned-cart-recovery' ), esc_html__( 'Save carts of logged-in customers. Disable this if you only want to recover guest carts. Shop managers and excluded users are still skipped even when this is on.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'tracking_member' );
				$this->number_option( 'member_cut_off_time', esc_html__( 'Abandoned Cart time for Members', 'woo-abandoned-cart-recovery' ), esc_html__( 'Minutes of inactivity after the last cart change before a logged-in customer cart is marked abandoned. Reminder emails wait this time plus the delay in Send mail rules. Typical value: 10–30 minutes.', 'woo-abandoned-cart-recovery' ), 'minutes' );
				$this->checkbox_option( 'tracking_guest', esc_html__( "Tracking Guest's Cart", 'woo-abandoned-cart-recovery' ), esc_html__( 'Save carts of visitors who are not logged in. Guests must provide email (checkout) before reminders can be sent.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'tracking_guest' );
				$this->number_option( 'guest_cut_off_time', esc_html__( 'Abandoned Cart time for Guest', 'woo-abandoned-cart-recovery' ), esc_html__( 'Minutes of inactivity after the last cart change before a guest cart is marked abandoned. Often set a bit longer than members because guests browse more slowly. Reminder delay is added on top of this time.', 'woo-abandoned-cart-recovery' ), 'minutes' );
				$this->depends_on( 'tracking_guest' );
				$this->get_pro_version( esc_html__( 'Tracking if email available', 'woo-abandoned-cart-recovery' ), esc_html__( 'When enabled, guest carts are stored only after a valid email is captured. Phone-only guests are not tracked.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Block IP record', 'woo-abandoned-cart-recovery' ), esc_html__( 'If this IP already has an abandoned cart, do not create another record for the same number of days. Helps reduce duplicate carts from the same visitor.', 'woo-abandoned-cart-recovery' ) );
				$this->number_option( 'delete_record_time', esc_html__( 'Delete abandoned records', 'woo-abandoned-cart-recovery' ), esc_html__( 'Daily cleanup deletes abandoned cart records older than this many days. Recovered carts are not removed by this job. Use a value that matches how long you need reports (for example 30 or 90 days).', 'woo-abandoned-cart-recovery' ), 'days' );

				$tracking_user_exclude_opts = array();
				if ( is_array( self::get_field( 'tracking_user_exclude' ) ) && count( self::get_field( 'tracking_user_exclude' ) ) > 0 ) {
					foreach ( get_users( array( 'include' => self::get_field( 'tracking_user_exclude' ) ) ) as $user ) {
						$tracking_user_exclude_opts[ $user->ID ] = $user->user_nicename;
					}
				}
				$this->select_option( 'tracking_user_exclude', $tracking_user_exclude_opts, esc_html__( "Exclude people", 'woo-abandoned-cart-recovery' ), esc_html__( 'Selected WordPress users are never tracked. Use this for store owners, testers, or staff who add products while browsing the shop.', 'woo-abandoned-cart-recovery' ), '', true );
				$this->get_pro_version( esc_html__( 'Exclude user role', 'woo-abandoned-cart-recovery' ), esc_html__( 'All users with these roles are skipped (for example Administrator or Shop manager). Combine with Exclude people for specific accounts.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Send email exclude provider', 'woo-abandoned-cart-recovery' ), esc_html__( 'Do not send reminder emails to addresses that contain these domains. Separate with commas, including @. Example: @dev.com,@mailinator.com.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Send email minimum cart total', 'woo-abandoned-cart-recovery' ), esc_html__( 'Skip reminder emails when the abandoned cart total is below this amount (store currency). Leave blank to send regardless of cart value.', 'woo-abandoned-cart-recovery' ) );

				$this->select_option(
					'direct_recover_link',
					[ esc_html__( 'Cart page', 'woo-abandoned-cart-recovery' ), esc_html__( 'Checkout page', 'woo-abandoned-cart-recovery' ) ],
					esc_html__( "Recover link to", 'woo-abandoned-cart-recovery' ),
					esc_html__( 'Landing page after the customer clicks the recover URL in an email. Cart page lets them edit items; Checkout page sends them straight to pay. The recover URL does not work for Administrator accounts.', 'woo-abandoned-cart-recovery' )
				);
				$this->get_pro_version( esc_html__( 'Immediate show Cart detail in Abandoned list', 'woo-abandoned-cart-recovery' ), esc_html__( 'On Abandoned Carts, show product lines in the list instead of requiring a click to expand.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Show Abandoned order options in Admin Order', 'woo-abandoned-cart-recovery' ), esc_html__( 'Adds send-reminder buttons and a reminder-status column on WooCommerce Orders (list and edit screens), including HPOS.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Cart logs', 'woo-abandoned-cart-recovery' ), esc_html__( 'Record add-to-cart and remove-from-cart events for debugging. This grows a separate log table.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Automatically delete cart logs', 'woo-abandoned-cart-recovery' ), esc_html__( 'Daily WP-Cron deletes cart-log rows older than a number of days. Abandoned cart records are not affected.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Delete cart logs after', 'woo-abandoned-cart-recovery' ), esc_html__( 'Keep cart logs for this many days, then delete them on the daily cleanup.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Clear cart logs', 'woo-abandoned-cart-recovery' ), esc_html__( 'Permanently deletes cart-log rows older than the date you enter.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Load guest information', 'woo-abandoned-cart-recovery' ), esc_html__( 'Prefill checkout billing/shipping fields with the email, name, address, and phone already stored for this guest.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Load full phone number', 'woo-abandoned-cart-recovery' ), esc_html__( 'When prefilling checkout, include the country calling code with the phone number.', 'woo-abandoned-cart-recovery' ) );
				$this->checkbox_option( 'enable_checkout_gdpr', esc_html__( "Checkout GDPR", 'woo-abandoned-cart-recovery' ), esc_html__( 'Show a consent notice under the checkout email field. Customers can keep using checkout or decline cart tracking/reminders using the ignore button text below.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'enable_checkout_gdpr' );
				$this->text_option( 'checkout_gdpr_message', esc_html__( 'GDPR message', 'woo-abandoned-cart-recovery' ), '', false, true, esc_html__( 'Consent text shown next to checkout email. Use WPML/Polylang extra fields when they appear. Keep it short and mention that email/phone may be used for cart reminders.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'enable_checkout_gdpr' );
				$this->text_option( 'checkout_gdpr_cancel', esc_html__( 'Ignore Email Button Text', 'woo-abandoned-cart-recovery' ), '', false, true, esc_html__( 'Label of the control that opts the guest out of abandoned-cart tracking from checkout (sets a skip cookie). Example: Do not track my cart.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
            <hr>

            <table class="wacv-table">
                <tr class="vlt-row vlt-margin-top">
                    <td class="vlt-third vlt-margin-bottom-8 col-1">
                        <label style="font-weight: bold" class=""><?php esc_html_e( 'Cron job', 'woo-abandoned-cart-recovery' ) ?></label>
                    </td>
                    <td class="vlt-twothird vlt-row col-2">
						<?php
						$cron_command = site_url() . '/wp-cron.php';
						if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
							?>
                            <p>
								<?php esc_html_e( "Your WP Cron is disabled. If you want to use WP Cron, open file wp-config.php and delete row define( \"DISABLE_WP_CRON\", true );.", 'woo-abandoned-cart-recovery' ); ?>
                            </p>
                            <p>
								<?php esc_html_e( " If you want to use CronJob server, access your server and config with command:", 'woo-abandoned-cart-recovery' ); ?>
                            </p>
                            <p>
                                <input type="text" value="<?php echo esc_attr( ' * * * * * curl ' . $cron_command ); ?>" readonly>
                            </p>
							<?php
						} else {
							?>
                            <p>
								<?php esc_html_e( "Your WP Cron is enabled. If you want to use Cronjob server, open file wp-config.php and add row define( \"DISABLE_WP_CRON\", true );. Access your server and config with command:", 'woo-abandoned-cart-recovery' ); ?>
                            </p>
                            <div class="wacv-input-readonly-block">
                                <input type="text" class="wacv-readonly"
                                       value="<?php echo esc_attr( ' * * * * * curl ' . $cron_command ); ?>" readonly>
                                <span class="wacv-copy-icon"><i class="copy outline icon"> </i></span>
                            </div>
						<?php }
						$this->field_description( __( 'Reminders run on WP-Cron about every minute, but WP-Cron only runs when someone visits the site. On a quiet store, use a real server cron that hits wp-cron.php every minute as shown. DISABLE_WP_CRON in wp-config.php turns off visitor-triggered cron; then the server command is required.', 'woo-abandoned-cart-recovery' ) );
						?>
                    </td>
                </tr>
            </table>
        </div>
		<?php
	}

}
