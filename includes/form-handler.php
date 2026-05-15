<?php
/**
 * Form submission handler for Atomic Newsletter For Elementor plugin.
 *
 * Supports:
 *   - Elementor Pro (elementor_pro/forms/new_record hook)
 *   - Pro Elements (https://proelements.org/) — free alternative to Elementor Pro
 *   - Elementor Atomic Forms AJAX (wp_ajax_elementor_pro_atomic_forms_send_form)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ALNC_Form_Handler {

	public static function init() {
		// Only run if Elementor Pro or a compatible alternative is active.
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' )
			&& ! defined( 'PRO_ELEMENTS_VERSION' )
			&& ! class_exists( 'ElementorPro\\Modules\\AtomicForm\\Atomic_Form_Controller' )
			&& ! class_exists( 'ElementorPro\\Modules\\Forms\\Classes\\Ajax_Handler' )
			&& ! class_exists( 'ProElements\\Modules\\AtomicForm\\Atomic_Form_Controller' )
		) {
			return;
		}

		// Server-side hook — fires after Elementor Pro / Pro Elements verifies its own nonce.
		add_action( 'elementor_pro/forms/new_record', array( __CLASS__, 'handle_elementor_form_submission' ), 10, 2 );

		// Atomic Forms AJAX — nonce verified against Elementor's own nonce token.
		add_action( 'wp_ajax_elementor_pro_atomic_forms_send_form',        array( __CLASS__, 'handle_atomic_form_ajax' ) );
		add_action( 'wp_ajax_nopriv_elementor_pro_atomic_forms_send_form', array( __CLASS__, 'handle_atomic_form_ajax' ) );
	}

	/**
	 * Handle Elementor Pro / Pro Elements form submission via server-side hook.
	 * The form plugin has already verified its nonce before this action fires.
	 *
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public static function handle_elementor_form_submission( $record, $ajax_handler ) {
		$fields = is_object( $record ) ? self::get_fields_from_record( $record ) : array();
		$email  = self::find_email_in_fields( $fields );

		if ( $email ) {
			ALNC_Database::insert_email( $email );
		}
	}

	/**
	 * Handle Elementor Atomic Forms AJAX submission.
	 * Verifies Elementor's own nonce before processing.
	 */
	public static function handle_atomic_form_ajax() {
		if ( empty( $_POST['_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ),
				'elementor_pro_atomic_forms_send_form'
			)
		) {
			return;
		}

		$raw_fields = isset( $_POST['form_fields'] ) && is_array( $_POST['form_fields'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			? map_deep( wp_unslash( $_POST['form_fields'] ), 'sanitize_text_field' ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		$fields = self::normalise_post_fields( $raw_fields );
		$email  = self::find_email_in_fields( $fields );

		if ( $email ) {
			ALNC_Database::insert_email( $email );
		}
	}

	/* ── Field extraction helpers ── */

	/**
	 * Pull fields array out of an Elementor / Pro Elements Form_Record object.
	 *
	 * @param  object $record
	 * @return array  Normalised field rows.
	 */
	private static function get_fields_from_record( $record ) {
		try {
			$raw = $record->get( 'fields' );
		} catch ( Exception $e ) {
			return array();
		}

		if ( empty( $raw ) || ! is_array( $raw ) ) {
			return array();
		}

		$fields = array();
		foreach ( $raw as $id => $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$fields[] = array(
				'type'  => $field['type']  ?? '',
				'id'    => (string) $id,
				'label' => $field['title'] ?? $field['label'] ?? '',
				'value' => $field['value'] ?? '',
			);
		}
		return $fields;
	}

	/**
	 * Normalise the raw $_POST['form_fields'] array into the same shape
	 * used by get_fields_from_record().
	 *
	 * @param  array $raw  Already un-slashed POST data.
	 * @return array  Normalised field rows.
	 */
	private static function normalise_post_fields( $raw ) {
		$fields = array();
		foreach ( $raw as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$fields[] = array(
				'type'  => $field['type']  ?? '',
				'id'    => $field['id']    ?? '',
				'label' => $field['label'] ?? '',
				'value' => $field['value'] ?? '',
			);
		}
		return $fields;
	}

	/**
	 * Scan a normalised fields array for an email address.
	 * Matches on field type = "email", or the word "email" anywhere
	 * in the field id or label.
	 *
	 * @param  array $fields  Output of get_fields_from_record() or normalise_post_fields().
	 * @return string|false   Sanitized, validated email or false.
	 */
	private static function find_email_in_fields( $fields ) {
		foreach ( $fields as $field ) {
			$type  = strtolower( sanitize_text_field( $field['type']  ) );
			$id    = strtolower( sanitize_text_field( $field['id']    ) );
			$label = strtolower( sanitize_text_field( $field['label'] ) );
			$value = $field['value'];

			// Flatten array values (e.g. multi-select).
			if ( is_array( $value ) ) {
				$value = implode( ', ', array_map( 'sanitize_text_field', $value ) );
			} else {
				$value = sanitize_text_field( wp_unslash( (string) $value ) );
			}

			$is_email_field = ( 'email' === $type )
				|| ( false !== strpos( $id,    'email' ) )
				|| ( false !== strpos( $label, 'email' ) );

			if ( $is_email_field ) {
				$email = sanitize_email( $value );
				if ( ! empty( $email )
					&& is_email( $email )
					&& ALNC_Email_Validation::is_valid_email( $email )
				) {
					return $email;
				}
			}
		}

		return false;
	}
}
