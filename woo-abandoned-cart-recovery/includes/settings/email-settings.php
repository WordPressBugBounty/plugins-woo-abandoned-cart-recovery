<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 08-06-19
 * Time: 12:01 PM
 */

namespace WACV\Inc\Settings;

use WACV\Inc\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Settings extends Admin_Settings {

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
        <div class="vi-ui bottom attached tab segment tab-admin" data-tab="email">
            <div class="vi-ui info message">
                <div class="header"><?php esc_html_e( 'How reminder emails work', 'woo-abandoned-cart-recovery' ); ?></div>
                <p><?php esc_html_e( 'WP-Cron sends emails after the cart is abandoned and each rule delay has passed. Create templates under Email Templates first, then pick them in Send mail rules. Recover links restore the cart; they do not work if you are logged in as Administrator.', 'woo-abandoned-cart-recovery' ); ?></p>
            </div>
            <h4><?php esc_html_e( 'Email for Abandoned Cart', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->checkbox_option( 'send_email_to_member', esc_html__( "Send mail reminder to members", 'woo-abandoned-cart-recovery' ), esc_html__( 'Send the abandoned-cart sequence to logged-in customers using their account email. Requires Tracking Member\'s Cart on the General tab.', 'woo-abandoned-cart-recovery' ) );
				$this->checkbox_option( 'send_email_to_guest', esc_html__( "Send mail reminder to guest", 'woo-abandoned-cart-recovery' ), esc_html__( 'Send the sequence to guests who have a valid email on the cart record (checkout). Requires Tracking Guest\'s Cart on the General tab.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->text_option( 'email_reply_address', esc_html__( "Reply Emails to", 'woo-abandoned-cart-recovery' ), '', false, false, esc_html__( 'Reply-To address on reminder emails so customer replies go to your support inbox instead of the WordPress From address. Leave empty to use the site default.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->send_email_rules_settings( 'email_rules', true );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( 'Add Cc email when send abandoned email', 'woo-abandoned-cart-recovery' ), esc_html__( 'Carbon-copy address for every abandoned-cart reminder. Use a monitoring inbox.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( 'Add Bcc email when send abandoned email', 'woo-abandoned-cart-recovery' ), esc_html__( 'Blind carbon-copy for abandoned-cart reminders. Recipients do not see this address.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->checkbox_option( 'email_to_admin_when_cart_recover', esc_html__( "Notification to Admin", 'woo-abandoned-cart-recovery' ), esc_html__( 'Email the store admin when a customer recovers a cart and places an order. The link in that email opens the WooCommerce order screen (HPOS-compatible).', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'email_to_admin_when_cart_recover' );
				$this->text_option( 'email_custom_when_cart_recover', esc_html__( 'Notification to custom email', 'woo-abandoned-cart-recovery' ), '', false, true, esc_html__( 'Extra addresses that also receive the recovered-cart notice. Leave empty to notify only the admin. Supports translated copies when WPML/Polylang is active.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->checkbox_option( 'email_item_link', esc_html__( "Product link", 'woo-abandoned-cart-recovery' ), esc_html__( 'Make product names/images in the cart block clickable to the product page. Disable if you only want a recover-cart button.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( "One-time abandoned cart email", 'woo-abandoned-cart-recovery' ), esc_html__( 'Each email address receives at most one abandoned-cart reminder sequence. Later abandoned carts for the same visitor are not emailed.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( "Price include tax", 'woo-abandoned-cart-recovery' ), esc_html__( 'Show prices including tax in reminder emails when WooCommerce tax is enabled and catalog prices are exclusive of tax.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( "Product price", 'woo-abandoned-cart-recovery' ), esc_html__( 'Show each line item price in the email cart table. Disable for a cleaner layout when you only show product names and a recover button.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( "Display current price", 'woo-abandoned-cart-recovery' ), esc_html__( 'On: use the live product price when the email is sent (sales/price changes apply). Off: use the price stored when the cart was abandoned.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( "Template use new cart block", 'woo-abandoned-cart-recovery' ), esc_html__( 'Use the editable cart block in Email Templates. Older templates must replace the old cart shortcode with the new block or product rows will not update.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( "Active Campaign", 'woo-abandoned-cart-recovery' ), esc_html__( 'After a reminder email is sent, add the customer to ActiveCampaign.', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( "Sendinblue", 'woo-abandoned-cart-recovery' ), esc_html__( 'After a reminder email is sent, add the customer to Brevo (Sendinblue).', 'woo-abandoned-cart-recovery' ) );
				$this->depends_on( 'send_email_to_member,send_email_to_guest' );
				$this->get_pro_version( esc_html__( "GetResponse", 'woo-abandoned-cart-recovery' ), esc_html__( 'After a reminder email is sent, add the customer to a GetResponse campaign.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
            <hr>

            <h4><?php esc_html_e( 'Email for Abandoned Order', 'woo-abandoned-cart-recovery' ) ?></h4>
            <div class="vi-ui info message">
                <p><?php esc_html_e( 'These emails target WooCommerce orders that never completed payment (failed, cancelled, pending, on-hold, or checkout draft). They are separate from abandoned-cart emails. HPOS stores are supported.', 'woo-abandoned-cart-recovery' ); ?></p>
            </div>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'Enable', 'woo-abandoned-cart-recovery' ), esc_html__( 'Send abandoned emails to unpaid WooCommerce orders. Limit by order status and how many days back to check modified orders.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Use Coupon', 'woo-abandoned-cart-recovery' ), esc_html__( "Include a coupon when sending an abandoned order email. Coupon settings are based on the email template's coupon configuration.", 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Coupon Usage limit', 'woo-abandoned-cart-recovery' ), esc_html__( 'Based on the order item quantity, this option sets how many times the coupon can be used before it becomes invalid.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Check orders within', 'woo-abandoned-cart-recovery' ), esc_html__( 'Only orders modified within this many days are eligible. Enter 0 for no time limit.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Order status', 'woo-abandoned-cart-recovery' ), esc_html__( 'Only these WooCommerce statuses receive abandoned-order emails. Typical set: Pending payment, Failed, On hold.', 'woo-abandoned-cart-recovery' ) );
				$this->get_pro_version( esc_html__( 'Send mail rules', 'woo-abandoned-cart-recovery' ), esc_html__( 'Each row is one reminder. Send after is counted from when the order became abandoned.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
            <hr>
            <table class="wacv-table">
				<?php
				$this->get_pro_version( esc_html__( 'Image size', 'woo-abandoned-cart-recovery' ), esc_html__( 'Product image size used in reminder emails. Smaller sizes load faster in inboxes; Original is sharpest but heavier.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
            <p class="vi-ui yellow message"><?php esc_html_e( 'Note: Recover link will not work with Admin role', 'woo-abandoned-cart-recovery' ); ?></p>
        </div>
		<?php
	}

	public function send_email_rules_settings( $slug, $multilingual = '' ) {
		$data          = self::get_field( $slug );
		$list_template = Functions::get_email_template();
		$explain       = esc_html__( 'Each row is one reminder. Send after is counted from when the cart became abandoned, not from the last email. Use increasing delays (for example 1 hour, then 24 hours). Pick an Email Template per row; with WPML/Polylang you can choose a template per language. Delete unused rows so customers are not over-emailed.', 'woo-abandoned-cart-recovery' );
		?>
        <tr<?php echo $this->depends_attr(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <td class="col-1">
                <label><?php esc_html_e( 'Send mail rules', 'woo-abandoned-cart-recovery' ) ?></label>
            </td>

            <td class="col-2">
                <table class="vi-ui celled table wacv-email-rules-table">
                    <thead>
                    <tr>
                        <th><?php esc_html_e( 'Send after', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th><?php esc_html_e( 'Unit', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th><?php esc_html_e( 'Email template', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th><?php esc_html_e( 'Action', 'woo-abandoned-cart-recovery' ); ?></th>
                    </tr>
                    </thead>
                    <tbody class="wacv-<?php echo esc_attr( $slug ) ?>-row-target">
					<?php
					if ( isset( $data['time_to_send'] ) ) {
						$loop = count( $data['time_to_send'] );

						for ( $i = 0; $i < $loop; $i ++ ) { ?>
                            <tr class="wacv-<?php echo esc_attr( $slug ) ?>-row-target" data-index="<?php echo esc_attr( $i ) ?>">
                                <td class="vlt-padding-small cols-1">
                                    <input type="number" name="wacv_params[<?php echo esc_attr( $slug ) ?>][time_to_send][]"
                                           class="vlt-input vlt-border vlt-none-shadow vlt-round"
                                           value="<?php echo esc_attr( $data['time_to_send'][ $i ] ) ?>" min="1">
                                </td>
                                <td class="vlt-padding-small cols-2">
                                    <select name="wacv_params[<?php echo esc_attr( $slug ) ?>][unit][]"
                                            class="vlt-input vlt-border vlt-none-shadow vlt-round">
                                        <option value="minutes" <?php selected( $data['unit'][ $i ], 'minutes' ); ?>>
											<?php esc_html_e( 'minutes', 'woo-abandoned-cart-recovery' ); ?>
                                        </option>
                                        <option value="hours" <?php selected( $data['unit'][ $i ], 'hours' ); ?>>
											<?php esc_html_e( 'hours', 'woo-abandoned-cart-recovery' ); ?>
                                        </option>
                                    </select>
                                </td>
                                <td class="vlt-padding-small cols-3">
                                    <select name="wacv_params[<?php echo esc_attr( $slug ) ?>][template][]"
                                            class="wacv-select-email-template vlt-input vlt-border vlt-none-shadow vlt-round">
										<?php
										foreach ( $list_template as $template ) {
											$selected = '';
											if ( isset( $data['template'][ $i ] ) ) {
												$selected = $template['id'] == $data['template'][ $i ] ? 'selected' : '';
											}
											printf( '<option value="%s" %s>%s</option>', esc_attr( $template['id'] ), esc_attr( $selected ), esc_html( $template['value'] ) );
										}
										?>
                                    </select>
									<?php
									if ( $multilingual ) {
										if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
											global $sitepress;
											$default_lang = $sitepress->get_default_language();
											$languages    = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
											if ( count( $languages ) ) {
												foreach ( $languages as $key => $language ) {
													if ( $key == $default_lang ) {
														continue;
													}
													?>
                                                    <p class="wacv-mlg-label"><?php echo esc_html( $language['native_name'] . ':' ) ?></p>
                                                    <select name="wacv_params[<?php echo esc_attr( $slug ) ?>][template<?php echo esc_attr( '_' . $key ) ?>][]"
                                                            class="wacv-select-email-template vlt-input vlt-border vlt-none-shadow vlt-round">
														<?php
														foreach ( $list_template as $template ) {
															$selected = '';
															if ( isset( $data[ 'template_' . $key ][ $i ] ) ) {
																$selected = $template['id'] == $data[ 'template_' . $key ][ $i ] ? 'selected' : '';
															}
															echo "<option value='" . esc_attr( $template['id'] ) . "' " . esc_attr( $selected ) . ">" . esc_html( $template['value'] ) . "</option>";
														}
														?>
                                                    </select>
													<?php
												}
											}
										} elseif ( class_exists( 'Polylang' ) ) {
											$languages = pll_languages_list();
											foreach ( $languages as $language ) {
												$default_lang = pll_default_language( 'slug' );

												if ( $language == $default_lang ) {
													continue;
												}
												?>
                                                <p class="wacv-mlg-label"><?php echo esc_html( $language . ':' ) ?></p>
                                                <select name="wacv_params[<?php echo esc_attr( $slug ) ?>][template<?php echo esc_attr( '_' . $language ) ?>][]"
                                                        class="wacv-select-email-template vlt-input vlt-border vlt-none-shadow vlt-round">
													<?php
													foreach ( $list_template as $template ) {
														$selected = '';
														if ( isset( $data[ 'template_' . $language ][ $i ] ) ) {
															$selected = $template['id'] == $data[ 'template_' . $language ][ $i ] ? 'selected' : '';
														}
														echo "<option value='" . esc_attr( $template['id'] ) . "' " . esc_attr( $selected ) . ">" . esc_html( $template['value'] ) . "</option>";
													}
													?>
                                                </select>
												<?php
											}
										}
									}
									?>
                                </td>
                                <td align="center" class="vlt-padding-small cols-4">
                                    <button class="wacv-delete-<?php echo esc_attr( $slug ) ?> vi-ui small icon red button"
                                            type="button">
                                        <i class="trash icon"> </i>
                                    </button>
                                </td>
                            </tr>
						<?php }
					} ?>
                    </tbody>
                </table>
                <button type="button" class="wacv-add-<?php echo esc_attr( $slug ) ?> vi-ui small icon green button">
					<?php esc_html_e( 'Add rule', 'woo-abandoned-cart-recovery' ); ?>
                </button>
                <a style="display: none" class="wacv-get-pro-version" target="_blank"
                   href="<?php echo esc_url( WACV_PRO_URL ) ?>">
					<?php esc_html_e( 'Unlock limit', 'woo-abandoned-cart-recovery' ) ?></a>
				<?php $this->field_description( $explain ); ?>
            </td>
        </tr>
		<?php
	}
}
