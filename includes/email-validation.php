<?php
/**
 * Email validation for Atomic Newsletter For Elementor plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ALNC_Email_Validation {

	/**
	 * Known disposable email domains to block.
	 *
	 * @var string[]
	 */
	private static $disposable_domains = array(
		'mailinator.com',
		'10minutemail.com',
		'yopmail.com',
		'guerrillamail.com',
		'trashmail.com',
		'temp-mail.org',
		'temp-mail.io',
		'maildrop.cc',
		'dispostable.com',
		'tempmail.net',
		'getnada.com',
		'mailnesia.com',
		'fakemail.net',
		'sharklasers.com',
		'spamgourmet.com',
		'spam4.me',
		'mintemail.com',
		'throwawaymail.com',
		'mailcatch.com',
		'fakeinbox.com',
	);

	/**
	 * Validate an email address.
	 *
	 * Uses WordPress is_email() for format validation, then checks
	 * length limits, disposable domain list, and an optional DNS lookup.
	 *
	 * @param  string $email Raw email value.
	 * @return bool
	 */
	public static function is_valid_email( $email ) {
		$email = sanitize_email( wp_unslash( trim( $email ) ) );

		// WordPress built-in format check.
		if ( ! is_email( $email ) ) {
			return false;
		}

		list( $local, $domain ) = explode( '@', $email, 2 );
		$domain = strtolower( $domain );

		// RFC 5321 length limits.
		if ( strlen( $local ) > 64 || strlen( $domain ) > 255 ) {
			return false;
		}

		// Block known disposable domains.
		if ( in_array( $domain, self::$disposable_domains, true ) ) {
			return false;
		}

		// Optional DNS check (disabled by default — enable via filter if needed).
		if ( apply_filters( 'alnc_enable_dns_check', false ) && function_exists( 'checkdnsrr' ) ) {
			if ( ! checkdnsrr( $domain, 'MX' ) && ! checkdnsrr( $domain, 'A' ) ) {
				return false;
			}
		}

		return true;
	}
}
