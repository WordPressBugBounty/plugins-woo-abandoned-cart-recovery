<?php
/**
 * Authenticated recovery-link tokens (HMAC-SHA256).
 *
 * @package WACV
 */

namespace WACV\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Recovery_Token {

	const TOKEN_VERSION = 1;

	const DEFAULT_TTL = 7776000; // 90 days.

	/**
	 * Token action types.
	 */
	const TYPE_CART        = 'cart';
	const TYPE_ORDER       = 'order';
	const TYPE_OPEN        = 'open';
	const TYPE_UNSUB_CART  = 'unsub_cart';
	const TYPE_UNSUB_ORDER = 'unsub_order';

	/**
	 * Get or create the per-install signing key.
	 *
	 * @return string
	 */
	public static function get_signing_key() {
		$key = get_option( 'wacv_signing_key' );

		if ( ! is_string( $key ) || strlen( $key ) < 32 ) {
			$key = wp_generate_password( 64, true, true );
			update_option( 'wacv_signing_key', $key, false );
		}

		return $key;
	}

	/**
	 * Build a signed recovery token.
	 *
	 * @param string $type   Token type constant.
	 * @param array  $fields Payload fields (type-specific).
	 *
	 * @return string
	 */
	public static function create( $type, array $fields ) {
		$ttl    = (int) apply_filters( 'wacv_recovery_token_ttl', self::DEFAULT_TTL );
		$expiry = time() + max( $ttl, 3600 );

		$data = array_merge(
			array(
				'v'   => self::TOKEN_VERSION,
				't'   => $type,
				'exp' => $expiry,
			),
			$fields
		);

		$payload = wp_json_encode( $data );
		if ( ! $payload ) {
			return '';
		}

		$mac = hash_hmac( 'sha256', $payload, self::get_signing_key(), true );

		return self::urlsafe_b64encode( $payload ) . '.' . self::urlsafe_b64encode( $mac );
	}

	/**
	 * Verify a token and return its payload, or null when invalid.
	 *
	 * @param string $token   Raw token from the request.
	 * @param string $type    Expected token type.
	 *
	 * @return array|null
	 */
	public static function verify( $token, $type ) {
		$token = self::normalize_token( $token );

		if ( '' === $token || false === strpos( $token, '.' ) ) {
			return null;
		}

		$parts = explode( '.', $token, 2 );
		if ( 2 !== count( $parts ) ) {
			return null;
		}

		$payload_json = self::urlsafe_b64decode( $parts[0] );
		$mac_received = self::urlsafe_b64decode( $parts[1] );

		if ( '' === $payload_json || '' === $mac_received ) {
			return null;
		}

		$mac_expected = hash_hmac( 'sha256', $payload_json, self::get_signing_key(), true );

		if ( ! hash_equals( $mac_expected, $mac_received ) ) {
			return null;
		}

		$data = json_decode( $payload_json, true );

		if ( ! is_array( $data ) ) {
			return null;
		}

		if ( empty( $data['v'] ) || (int) $data['v'] !== self::TOKEN_VERSION ) {
			return null;
		}

		if ( empty( $data['t'] ) || $data['t'] !== $type ) {
			return null;
		}

		if ( empty( $data['exp'] ) || time() > (int) $data['exp'] ) {
			return null;
		}

		return $data;
	}

	/**
	 * Create a cart recovery token.
	 *
	 * @param int    $acr_id        Abandoned cart record ID.
	 * @param string $sent_email_id Sent email tracking ID.
	 * @param int    $temp_id       Email template ID.
	 * @param string $coupon        Optional coupon code.
	 *
	 * @return string
	 */
	public static function create_cart_link( $acr_id, $sent_email_id, $temp_id, $coupon = '' ) {
		return self::create(
			self::TYPE_CART,
			array(
				'acr_id'        => (int) $acr_id,
				'sent_email_id' => (string) $sent_email_id,
				'temp_id'       => (int) $temp_id,
				'coupon'        => (string) $coupon,
			)
		);
	}

	/**
	 * Create an email open-tracking token.
	 *
	 * @param int    $ref_id        Cart record ID or order ID.
	 * @param string $sent_email_id Sent email tracking ID.
	 *
	 * @return string
	 */
	public static function create_open_link( $ref_id, $sent_email_id ) {
		return self::create(
			self::TYPE_OPEN,
			array(
				'ref_id'        => (int) $ref_id,
				'sent_email_id' => (string) $sent_email_id,
			)
		);
	}

	/**
	 * Create a cart unsubscribe token.
	 *
	 * @param int $acr_id Abandoned cart record ID.
	 *
	 * @return string
	 */
	public static function create_unsub_cart_link( $acr_id ) {
		return self::create(
			self::TYPE_UNSUB_CART,
			array(
				'acr_id' => (int) $acr_id,
			)
		);
	}

	/**
	 * Create an order recovery token.
	 *
	 * @param int    $order_id      WooCommerce order ID.
	 * @param string $sent_email_id Sent email tracking ID.
	 *
	 * @return string
	 */
	public static function create_order_link( $order_id, $sent_email_id ) {
		return self::create(
			self::TYPE_ORDER,
			array(
				'order_id'      => (int) $order_id,
				'sent_email_id' => (string) $sent_email_id,
			)
		);
	}

	/**
	 * Create an order unsubscribe token.
	 *
	 * @param int $order_id WooCommerce order ID.
	 *
	 * @return string
	 */
	public static function create_unsub_order_link( $order_id ) {
		return self::create(
			self::TYPE_UNSUB_ORDER,
			array(
				'order_id' => (int) $order_id,
			)
		);
	}

	/**
	 * Sanitize a token value from a request parameter.
	 *
	 * @param string $token Raw request value.
	 *
	 * @return string
	 */
	public static function normalize_token( $token ) {
		$token = rawurldecode( (string) $token );
		$token = str_replace( ' ', '+', $token );

		return sanitize_text_field( $token );
	}

	/**
	 * URL-safe base64 encode (same alphabet as legacy AES tokens).
	 *
	 * @param string $data Raw data.
	 *
	 * @return string
	 */
	public static function urlsafe_b64encode( $data ) {
		return strtr( base64_encode( $data ), '+/=', '-_,' );
	}

	/**
	 * URL-safe base64 decode.
	 *
	 * @param string $data Encoded data.
	 *
	 * @return string
	 */
	public static function urlsafe_b64decode( $data ) {
		$data = strtr( $data, '-_,', '+/=' );
		$decoded = base64_decode( $data, true );

		return false === $decoded ? '' : $decoded;
	}
}
