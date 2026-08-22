<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 26-03-19
 * Time: 4:14 PM
 */

namespace WACV\Inc\Execute;

use WACV\Inc\Data;
use WACV\Inc\Recovery_Token;
use WACV\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Recovered {

	protected static $instance = null;
	public $query;
	public $settings;
	protected $coupon;
	protected $unsub_modal;

	private function __construct() {
		$this->query    = Query_DB::get_instance();
		$this->settings = Data::get_instance();

        add_action( 'template_redirect', array( $this, 'handle_callback_link' ), 1 );
        add_filter( 'login_redirect', array( $this, 'filter_login_redirect' ), 99, 3 );
        add_filter( 'woocommerce_login_redirect', array( $this, 'filter_wc_login_redirect' ), 99, 2 );
        add_action( 'woocommerce_before_checkout_form', array( $this, 'add_coupon' ) );
        add_action( 'woocommerce_before_cart', array( $this, 'add_coupon' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_modal_script' ) );
        add_action( 'wp_footer', array( $this, 'load_unsubscribe_modal' ) );
    }

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function handle_callback_link() {

		if ( is_admin() ) {
			return;
		}

        if ( is_user_logged_in() && $this->has_pending_recovery() ) {
            $redirect_url = $this->maybe_complete_pending_recovery( wp_get_current_user() );

            if ( $redirect_url ) {
                wp_safe_redirect( $redirect_url );
                exit;
            }

            $this->deny_recovery_access();
        }

        $this->handle_recover_cart();
        $this->handle_unsubscribe();
        $this->handle_tracking_open();
        $this->handle_recover_order();
    }

    public function handle_recover_cart() {
        if ( isset( $_REQUEST['_wacv_admin_nonce'] ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST['_wacv_admin_nonce'] ) ), 'wacv_admin_nonce' ) ) {
            return;
        }
        if ( isset( $_GET['wacv_recover'] ) && $_GET['wacv_recover'] == 'cart_link' && isset( $_GET['valid'] ) ) {
            if ( '' == session_id() ) {
                @session_start();
            }

            $valid_token = wp_unslash( $_GET['valid'] );
            $token       = Recovery_Token::verify( $valid_token, Recovery_Token::TYPE_CART );

            if ( ! $token ) {
                wp_safe_redirect( home_url() );
                exit;
            }

            $acr_id = isset( $token['acr_id'] ) ? (int) $token['acr_id'] : 0;

            if ( ! $acr_id ) {
                wp_safe_redirect( home_url() );
                exit;
            }

            global $wpdb;
            $query = "SELECT user_id FROM {$this->query->cart_record_tb} WHERE id = %d LIMIT 1";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
            $cart_user_id = $wpdb->get_var( $wpdb->prepare( $query, $acr_id ) );

            if ( ! $cart_user_id ) {
                wp_safe_redirect( home_url() );
                exit;
            }

            if ( (int) $cart_user_id < 100000000 && ! is_user_logged_in() ) {
                $this->redirect_to_login_for_recovery(
                    array(
                        'wacv_recover' => 'cart_link',
                        'valid'        => $valid_token,
                    )
                );
            }

            $redirect_url = $this->complete_cart_recovery( $valid_token );

            if ( $redirect_url ) {
                wp_safe_redirect( $redirect_url );
                exit;
            }

            $this->deny_recovery_access();
        }
    }

	public function recover_get_info( $user_id ) {
		$result = $this->query->get_guest_info( $user_id );
		$result = $result[0];

		return $customer = array(
			"id"                  => $result->id,
			"date_modified"       => '',
			"billing_postcode"    => $result->billing_postcode,
			"billing_city"        => $result->billing_city,
			"billing_address_1"   => $result->billing_address_1,
			"billing_address"     => $result->billing_address_1,
			"billing_address_2"   => $result->billing_address_2,
			"billing_state"       => $result->billing_city,
			"billing_country"     => $result->billing_country,
			"shipping_postcode"   => $result->shipping_postcode,
			"shipping_city"       => $result->shipping_city,
			"shipping_address_1"  => $result->shipping_address_1,
			"shipping_address"    => $result->shipping_address_1,
			"shipping_address_2"  => $result->shipping_address_2,
			"shipping_state"      => $result->shipping_city,
			"shipping_country"    => $result->shipping_country,
			"billing_first_name"  => $result->billing_first_name,
			"billing_last_name"   => $result->billing_last_name,
			"billing_company"     => $result->billing_company,
			"billing_phone"       => $result->billing_phone,
			"billing_email"       => $result->billing_email,
			"shipping_first_name" => $result->shipping_first_name,
			"shipping_last_name"  => $result->shipping_last_name,
			"shipping_company"    => $result->shipping_company,
			"user_ref"            => $result->user_ref
		);
	}

    public function handle_unsubscribe() {
        if ( isset( $_REQUEST['_wacv_admin_nonce'] ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST['_wacv_admin_nonce'] ) ), 'wacv_admin_nonce' ) ) {
            return;
        }
        if ( isset( $_GET['wacv_unsubscribe'] ) ) {
            $token  = Recovery_Token::verify( wp_unslash( $_GET['wacv_unsubscribe'] ), Recovery_Token::TYPE_UNSUB_CART );
            $acr_id = $token && isset( $token['acr_id'] ) ? (int) $token['acr_id'] : 0;

            if ( $acr_id ) {
                WC()->cart->empty_cart();
                $this->query->update_abd_cart_record( array( 'unsubscribe_link' => 1 ), array( 'id' => $acr_id ) );
                $this->unsub_modal = true;
            }
        }
    }

	public function enqueue_modal_script() {
		if ( ! $this->unsub_modal ) {
			return;
		}

		wp_register_script( WACV_SLUG . '-unsubscribe', WACV_JS . 'unsubscribe-modal.js', array( 'jquery' ), WACV_VERSION, false );
		wp_register_style( WACV_SLUG . '-unsubscribe', WACV_CSS . 'unsubscribe-modal.css', '', WACV_VERSION );
	}

	public function load_unsubscribe_modal() {
		if ( ! $this->unsub_modal ) {
			return;
		}

		wp_enqueue_script( WACV_SLUG . '-unsubscribe' );
		wp_enqueue_style( WACV_SLUG . '-unsubscribe' );

		$title   = $this->settings::get_param( 'unsub_title' );
		$content = $this->settings::get_param( 'unsub_content' );
		$button  = $this->settings::get_param( 'unsub_button' );
		$href    = $this->settings::get_param( 'unsub_redirect' );

		$title_color     = $this->settings::get_param( 'popup_title_color' );
		$sub_title_color = $this->settings::get_param( 'popup_sub_title_color' );
		$bg_color        = $this->settings::get_param( 'popup_bg_color' );
		$btn_color       = $this->settings::get_param( 'popup_btn_color' );
		$btn_bg_color    = $this->settings::get_param( 'popup_btn_bg_color' );

		$css = ".wacv-unsub-title{color:$title_color}";
		$css .= ".wacv-unsub-message{color:$sub_title_color}";
		$css .= ".wacv-unsub-content-layer{background-color:$bg_color}";
		$css .= ".wacv-unsub-redirect-button{color:$btn_color; background-color:$btn_bg_color}";

		wp_add_inline_style( WACV_SLUG . '-unsubscribe', $css );
		?>
        <div id="wacv-unsubscribe-modal">
            <div class="wacv-modal-relative-layer">
                <div class="wacv-modal-absolute-layer">
                    <div class="wacv-unsub-content-layer">
                        <div class="wacv-modal-close dashicons dashicons-no-alt"
                             title="<?php esc_html_e( 'Close', 'woo-abandoned-cart-recovery' ); ?>">
                        </div>
                        <div class="wacv-unsub-main-content">
							<?php
							if ( $title ) {
								printf( '<h2 class="wacv-unsub-title">%s</h2>', esc_html( $title ) );
							}

							if ( $content ) {
								printf( '<div class="wacv-unsub-message">%s</div>', esc_html( $content ) );
							}

							if ( $button && $href ) {
								printf( '<a href="%s" class="wacv-unsub-redirect-button">%s</a>', esc_url( $href ), esc_html( $button ) );
							}
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

    public function handle_tracking_open() {
        if ( isset( $_REQUEST['_wacv_admin_nonce'] ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST['_wacv_admin_nonce'] ) ), 'wacv_admin_nonce' ) ) {
            return;
        }
        if ( isset( $_GET['wacv_open_email'] ) ) {
            $token = Recovery_Token::verify( wp_unslash( $_GET['wacv_open_email'] ), Recovery_Token::TYPE_OPEN );

            if ( ! $token ) {
                return;
            }

            $ref_id        = isset( $token['ref_id'] ) ? (int) $token['ref_id'] : 0;
            $sent_email_id = isset( $token['sent_email_id'] ) ? $token['sent_email_id'] : '';

            if ( ! $ref_id || ! $sent_email_id ) {
                return;
            }

            global $wpdb;
            $query = "SELECT id FROM {$this->query->email_history_tb} WHERE sent_email_id = %s AND acr_id = %d LIMIT 1";
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
            $history_id = $wpdb->get_var( $wpdb->prepare( $query, $sent_email_id, $ref_id ) );

            if ( $history_id ) {
                $this->query->update_email_tracking( $sent_email_id, 'opened' );
            }
        }
    }

    public function handle_recover_order() {
        if ( isset( $_REQUEST['_wacv_admin_nonce'] ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST['_wacv_admin_nonce'] ) ), 'wacv_admin_nonce' ) ) {
            return;
        }
        if ( isset( $_GET['wacv_recover'] ) && $_GET['wacv_recover'] == 'order_link' ) {
            if ( isset( $_GET['valid'] ) ) {
                $valid_token = wp_unslash( $_GET['valid'] );
                $token       = Recovery_Token::verify( $valid_token, Recovery_Token::TYPE_ORDER );

                if ( ! $token ) {
                    wp_safe_redirect( home_url() );
                    exit;
                }

                if ( ! is_user_logged_in() ) {
                    $this->redirect_to_login_for_recovery(
                        array(
                            'wacv_recover' => 'order_link',
                            'valid'        => $valid_token,
                        )
                    );
                }

                $redirect_url = $this->complete_order_recovery( $valid_token );

                if ( $redirect_url ) {
                    wp_safe_redirect( $redirect_url );
                    exit;
                }

                $this->deny_recovery_access();

            } elseif ( isset( $_GET['unsubscribe'] ) ) {
                $token    = Recovery_Token::verify( wp_unslash( $_GET['unsubscribe'] ), Recovery_Token::TYPE_UNSUB_ORDER );
                $order_id = $token && isset( $token['order_id'] ) ? (int) $token['order_id'] : 0;
                $wc_order = $order_id ? wc_get_order( $order_id ) : false;

                if ( $wc_order ) {
                    $wc_order->update_meta_data( '_wacv_reminder_unsubscribe', 1 );
                    $wc_order->save_meta_data();
                }

                wp_safe_redirect( home_url() );
                exit;
            }
        }
    }

    public function add_coupon() {
        $coupon = WC()->session->get( 'wacv_coupon_to_add' );
        if ( $coupon ) {
            WC()->cart->apply_coupon( sanitize_text_field( $coupon ) );
            WC()->session->__unset( 'wacv_coupon_to_add' );
            WC()->cart->calculate_totals();
        }
    }

    /**
     * Complete pending recovery after wp-login.php authentication.
     *
     * @param string           $redirect_to           Default redirect URL.
     * @param string           $requested_redirect_to Requested redirect URL.
     * @param \WP_User|\WP_Error $user                Authenticated user.
     *
     * @return string
     */
    public function filter_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
        if ( ! $user || is_wp_error( $user ) || ! $this->has_pending_recovery() ) {
            return $redirect_to;
        }

        $target = $this->maybe_complete_pending_recovery( $user );

        return $target ? $target : $this->get_recovery_failure_redirect();
    }

    /**
     * Complete pending recovery after WooCommerce my-account login.
     *
     * @param string   $redirect Default redirect URL.
     * @param \WP_User $user     Authenticated user.
     *
     * @return string
     */
    public function filter_wc_login_redirect( $redirect, $user ) {
        if ( ! $user || is_wp_error( $user ) || ! $this->has_pending_recovery() ) {
            return $redirect;
        }

        $target = $this->maybe_complete_pending_recovery( $user );

        return $target ? $target : $this->get_recovery_failure_redirect();
    }

    /**
     * Complete cart recovery and return the destination URL.
     *
     * @param string        $valid_token Signed recovery token.
     * @param \WP_User|null $user        Optional user for post-login recovery.
     *
     * @return string
     */
    private function complete_cart_recovery( $valid_token, $user = null ) {
        $token = Recovery_Token::verify( $valid_token, Recovery_Token::TYPE_CART );

        if ( ! $token ) {
            return '';
        }

        $acr_id        = isset( $token['acr_id'] ) ? (int) $token['acr_id'] : 0;
        $sent_email_id = isset( $token['sent_email_id'] ) ? $token['sent_email_id'] : '';
        $temp_id       = isset( $token['temp_id'] ) ? (int) $token['temp_id'] : 0;
        $coupon        = isset( $token['coupon'] ) ? $token['coupon'] : '';

        if ( ! $acr_id || ! $this->query->email_history_matches( $sent_email_id, $acr_id, 'email' ) ) {
            return '';
        }

        global $wpdb;

        $query = "SELECT * FROM {$this->query->cart_record_tb} WHERE id = %d LIMIT 1";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $acr = $wpdb->get_results( $wpdb->prepare( $query, $acr_id ) );

        if ( empty( $acr ) ) {
            return '';
        }

        $user_id = (int) $acr[0]->user_id;

        if ( $user_id < 100000000 ) {
            $current_user_id = $user ? (int) $user->ID : get_current_user_id();

            if ( ! $current_user_id || $current_user_id !== $user_id ) {
                return '';
            }
        } else {
            $guest_info = $this->recover_get_info( $user_id );

            if ( $user || is_user_logged_in() ) {
                $check_user  = $user ? $user : wp_get_current_user();
                $guest_email = isset( $guest_info['billing_email'] ) ? $guest_info['billing_email'] : '';

                if ( ! $guest_email || ! $this->emails_match( $check_user->user_email, $guest_email ) ) {
                    return '';
                }
            }
        }

        $this->ensure_wc_session();
        $this->query->update_email_tracking( $sent_email_id, 'clicked' );

        if ( $user_id < 100000000 ) {
            $abd_cart_info = json_decode( $acr[0]->abandoned_cart_info, true );
            $rec_cart      = ! empty( $abd_cart_info['cart'] ) ? $abd_cart_info['cart'] : array();

            if ( ! empty( $rec_cart ) ) {
                $this->query::set_session( 'cart', $rec_cart );
            } else {
                $saved_cart = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
                if ( empty( $saved_cart['cart'] ) ) {
                    return '';
                }
                WC()->session->cart = $saved_cart['cart'];
            }
        } else {
            $cookie_time = current_time( 'timestamp' ) + 86400;
            setCookie( 'wacv_get_email', true, $cookie_time, '/' );

            $abd_cart_info = json_decode( $acr[0]->abandoned_cart_info, true );
            $rec_cart      = ! empty( $abd_cart_info['cart'] ) ? $abd_cart_info['cart'] : array();

            if ( empty( $rec_cart ) ) {
                return '';
            }

            $guest_info = $this->recover_get_info( $user_id );

            $this->query::set_session( 'cart', $rec_cart );
            $this->query::set_session( 'user_id', $user_id );
            $this->query::set_session( 'guest_info', $guest_info );
        }

        $this->query::set_session( 'wacv_order_type', 1 );
        $this->query::set_session( 'wacv_recover_id', $acr_id );
        $this->query::set_session( 'wacv_temp_id', $temp_id );

        if ( $coupon ) {
            WC()->session->set( 'wacv_coupon_to_add', $coupon );
        }

        return Data::get_param( 'direct_recover_link' ) ? wc_get_checkout_url() : wc_get_cart_url();
    }

    /**
     * Complete order recovery and return the destination URL.
     *
     * @param string        $valid_token Signed recovery token.
     * @param \WP_User|null $user        Optional user for post-login recovery.
     *
     * @return string
     */
    private function complete_order_recovery( $valid_token, $user = null ) {
        $token = Recovery_Token::verify( $valid_token, Recovery_Token::TYPE_ORDER );

        if ( ! $token ) {
            return '';
        }

        $order_id      = isset( $token['order_id'] ) ? (int) $token['order_id'] : 0;
        $sent_email_id = isset( $token['sent_email_id'] ) ? $token['sent_email_id'] : '';

        if ( ! $order_id || ! $this->query->email_history_matches( $sent_email_id, $order_id, 'order' ) ) {
            return '';
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return '';
        }

        if ( $user ) {
            if ( ! $this->user_can_access_order_for_user( $order, $user ) ) {
                return '';
            }
        } elseif ( ! $this->user_can_access_order( $order ) ) {
            return '';
        }

        $this->query->update_email_tracking( $sent_email_id, 'clicked' );

        if ( 'cancelled' === $order->get_status() ) {
            $order->update_status( 'pending' );
        }

        return $order->get_checkout_payment_url();
    }

    /**
     * Check whether a recovery action is waiting to be completed after login.
     *
     * @return bool
     */
    private function has_pending_recovery() {
        return (bool) $this->get_pending_recovery();
    }

    /**
     * Persist recovery intent across the login boundary.
     *
     * @param array $query_args Recovery query arguments.
     */
    private function store_pending_recovery( array $query_args ) {
        if ( function_exists( 'WC' ) && WC()->session ) {
            WC()->session->set( 'wacv_pending_recovery', $query_args );
        }

        $payload = wp_json_encode( $query_args );

        if ( $payload ) {
            setcookie(
                'wacv_pending_recovery',
                base64_encode( $payload ),
                time() + HOUR_IN_SECONDS,
                COOKIEPATH ? COOKIEPATH : '/',
                COOKIE_DOMAIN,
                is_ssl(),
                true
            );
        }
    }

    /**
     * Read pending recovery data from session or cookie.
     *
     * @return array
     */
    private function get_pending_recovery() {
        if ( function_exists( 'WC' ) && WC()->session ) {
            $pending = WC()->session->get( 'wacv_pending_recovery' );

            if ( ! empty( $pending['wacv_recover'] ) && ! empty( $pending['valid'] ) ) {
                return $pending;
            }
        }

        if ( empty( $_COOKIE['wacv_pending_recovery'] ) ) {
            return array();
        }

        $decoded = json_decode( base64_decode( wp_unslash( $_COOKIE['wacv_pending_recovery'] ) ), true );

        return is_array( $decoded ) ? $decoded : array();
    }

    /**
     * Clear stored recovery intent.
     */
    private function clear_pending_recovery() {
        if ( function_exists( 'WC' ) && WC()->session ) {
            WC()->session->__unset( 'wacv_pending_recovery' );
        }

        if ( isset( $_COOKIE['wacv_pending_recovery'] ) ) {
            setcookie(
                'wacv_pending_recovery',
                '',
                time() - HOUR_IN_SECONDS,
                COOKIEPATH ? COOKIEPATH : '/',
                COOKIE_DOMAIN,
                is_ssl(),
                true
            );
        }
    }

    /**
     * Redirect target when post-login recovery fails.
     *
     * @return string
     */
    private function get_recovery_failure_redirect() {
        if ( function_exists( 'WC' ) && WC()->session ) {
            $message = WC()->session->get( 'wacv_recovery_denied_notice' );
            if ( $message && function_exists( 'wc_add_notice' ) ) {
                wc_add_notice( $message, 'error' );
                WC()->session->__unset( 'wacv_recovery_denied_notice' );
            }
        }

        if ( function_exists( 'wc_get_page_permalink' ) ) {
            return wc_get_page_permalink( 'myaccount' );
        }

        return home_url( '/' );
    }

    /**
     * Process recovery stored in the WooCommerce session after login.
     *
     * @param \WP_User $user Authenticated user.
     *
     * @return string
     */
    private function maybe_complete_pending_recovery( $user ) {
        $pending = $this->get_pending_recovery();

        if ( empty( $pending['wacv_recover'] ) || empty( $pending['valid'] ) ) {
            return '';
        }

        $redirect_url = '';

        if ( 'cart_link' === $pending['wacv_recover'] ) {
            $redirect_url = $this->complete_cart_recovery( $pending['valid'], $user );
        } elseif ( 'order_link' === $pending['wacv_recover'] ) {
            $redirect_url = $this->complete_order_recovery( $pending['valid'], $user );
        }

        $this->clear_pending_recovery();

        if ( ! $redirect_url ) {
            $this->stash_recovery_denied_notice();
        }

        return $redirect_url;
    }

    /**
     * Ensure WooCommerce session cookie exists before redirecting to login.
     */
    private function ensure_wc_session() {
        if ( ! function_exists( 'WC' ) || ! WC()->session ) {
            return;
        }

        WC()->session->set_customer_session_cookie( true );
    }

    /**
     * Whether a specific user owns the given order.
     *
     * @param \WC_Order $order Order object.
     * @param \WP_User  $user  User object.
     *
     * @return bool
     */
    private function user_can_access_order_for_user( $order, $user ) {
        $customer_id = (int) $order->get_customer_id();

        if ( $customer_id > 0 ) {
            return $customer_id === (int) $user->ID;
        }

        $order_email = $order->get_billing_email();

        return $order_email && $this->emails_match( $user->user_email, $order_email );
    }

    /**
     * Store a denied-recovery notice for display on the next page load.
     */
    private function stash_recovery_denied_notice() {
        if ( function_exists( 'WC' ) && WC()->session ) {
            WC()->session->set(
                'wacv_recovery_denied_notice',
                esc_html__( 'You do not have permission to access this recovery link.', 'woo-abandoned-cart-recovery' )
            );
        }
    }

    /**
     * Whether the logged-in user owns the given order.
     *
     * @param \WC_Order $order Order object.
     *
     * @return bool
     */
    private function user_can_access_order( $order ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $current_user_id = get_current_user_id();
        $customer_id     = (int) $order->get_customer_id();

        if ( $customer_id > 0 ) {
            return $customer_id === $current_user_id;
        }

        $current_user = wp_get_current_user();
        $order_email  = $order->get_billing_email();

        return $order_email && $this->emails_match( $current_user->user_email, $order_email );
    }

    /**
     * Compare two email addresses case-insensitively.
     *
     * @param string $email_a First email.
     * @param string $email_b Second email.
     *
     * @return bool
     */
    private function emails_match( $email_a, $email_b ) {
        if ( function_exists( 'wc_strcasecmp' ) ) {
            return 0 === wc_strcasecmp( trim( (string) $email_a ), trim( (string) $email_b ) );
        }

        return strtolower( trim( (string) $email_a ) ) === strtolower( trim( (string) $email_b ) );
    }

    /**
     * Redirect unauthenticated users to login, preserving the recovery URL.
     *
     * @param array $query_args Recovery query arguments.
     */
    private function redirect_to_login_for_recovery( array $query_args ) {
        $this->ensure_wc_session();
        $this->store_pending_recovery( $query_args );

        $recovery_url = add_query_arg( $query_args, home_url( '/' ) );
        wp_safe_redirect( wp_login_url( $recovery_url ) );
        exit;
    }

    /**
     * Deny recovery access for the wrong account and redirect home.
     */
    private function deny_recovery_access() {
        $this->stash_recovery_denied_notice();

        if ( function_exists( 'WC' ) && WC()->session ) {
            $message = WC()->session->get( 'wacv_recovery_denied_notice' );
            if ( $message && function_exists( 'wc_add_notice' ) ) {
                wc_add_notice( $message, 'error' );
                WC()->session->__unset( 'wacv_recovery_denied_notice' );
            }
        } elseif ( function_exists( 'wc_add_notice' ) ) {
            wc_add_notice(
                esc_html__( 'You do not have permission to access this recovery link.', 'woo-abandoned-cart-recovery' ),
                'error'
            );
        }

        wp_safe_redirect( home_url() );
        exit;
    }
}
