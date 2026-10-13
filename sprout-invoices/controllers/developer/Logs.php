<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Notification Model
 *
 *
 * @package Sprout_Invoices
 * @subpackage Records
 */
class SI_Dev_Logs extends SI_Controller {
	const LOG_TYPE = 'dev_log';
	const ERROR_TYPE = 'si_error';
	const LOG_OPTION = 'si_record_logs_option_v1';
	const REDACTED_VALUE = '[REDACTED]';

	private static $record_logs;
	private static $recorded_logs = array();
	private static $recorded_errors = array();

	public static function init() {
		// Admin option
		self::$record_logs = get_option( self::LOG_OPTION, 0 );

		// Register Settings
		add_filter( 'si_settings', array( __CLASS__, 'register_settings' ) );
		add_action( 'si_settings_saved', array( static::class, 'save_log_option' ) );

		// after
		add_action( 'init', array( __CLASS__, 'record_stored_logs_and_errors' ), PHP_INT_MAX );

		// action to log
		add_action( 'si_log', array( __CLASS__, 'log' ), 10, 2 );
		add_action( 'si_error', array( __CLASS__, 'error' ), 10, 3 );

		// purge old logs
		add_action( self::DAILY_CRON_HOOK, array( __CLASS__, 'purge_old_logs' ) );
	}

	/**
	 * Register the settings for Developer log in Sprout Invoices.
	 *
	 * @param array $settings default is an empty array.
	 * @return array
	 */
	public static function register_settings( $settings = array() ) {
		// Settings
		$settings['si_developer'] = array(
			'title'       => __( 'Logs', 'sprout-invoices' ),
			'description' => __( 'Developer settings.', 'sprout-invoices' ),
			'weight'      => 2000,
			'tab'         => 'tools',
			'link'        => admin_url( 'tools.php?page=si_records' ),
			'settings'    => array(
				self::LOG_OPTION => array(
					'label'  => __( 'Save Logs', 'sprout-invoices' ),
					'option' => array(
						'label'       => __( 'Save all logs as a sprout apps records (dev_log).', 'sprout-invoices' ),
						'type'        => 'checkbox',
						'default'     => ( get_option( self::LOG_OPTION, 0 ) ) ? true : false,
						'value'       => false,
						'description' => __( 'Note: This should only be used for testing and troubleshooting. Records are found under Tools.', 'sprout-invoices' ),
					),
				),
			),
		);
		return $settings;
	}

	public static function save_log_option() {
		$log_option = isset( $_POST[ self::LOG_OPTION ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::LOG_OPTION ] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by WP Settings API before this callback fires
		$dolog = 'true' === $log_option ? 1 : 0;
		update_option( self::LOG_OPTION, $dolog );
	}

	/**
	 * Log
	 * Writes to PHP error logs if DEBUG is enabled.
	 *
	 * @param  string $subject short title
	 * @param  array  $data    data
	 * @return null
	 */
	public static function log( $subject = '', $data = array() ) {
		$subject = self::redact_log_string( $subject );
		$data    = self::redact_log_data( $data, $subject );

		if ( self::DEBUG ) {
			error_log( '+++' . $subject . ' +++++++++++++++++++++' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional: DEBUG-gated logging in dedicated Logs class
			if ( ! empty( $data ) ) {
				error_log( wp_json_encode( $data ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'backtrace: ' . wp_json_encode( wp_debug_backtrace_summary( null, 0, false ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary
				error_log( '--------------------- ' . $subject . ' END ---------------------' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		if ( self::$record_logs ) {
			if ( function_exists( 'wp_get_current_user' ) ) {
				self::record_log( $subject, $data );
			} else {
				self::$recorded_logs[ $subject ] = $data;
			}
		}
	}

	/**
	 * Records log error
	 * Writes to PHP error logs if DEBUG is enabled.
	 *
	 * @param  string $subject short title
	 * @param  array  $data    data
	 * @return null
	 */
	public static function error( $subject = '', $data = array(), $record_error = true ) {
		$subject = self::redact_log_string( $subject );
		$data    = self::redact_log_data( $data, $subject );

		if ( self::DEBUG ) {
			error_log( '--- ' . $subject . ' ---------------------' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional: DEBUG-gated logging in dedicated Logs class
			if ( ! empty( $data ) ) {
				error_log( wp_json_encode( $data ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				// error_log( '--------------------- ' . $subject . ' END ---------------------' );
			}
		}

		if ( $record_error && self::$record_logs ) {
			if ( function_exists( 'wp_get_current_user' ) ) {
				self::record_log( $subject, $data, true );
			} else {
				self::$recorded_errors[ $subject ] = $data;
			}
		}
	}

	/**
	 * Fired on init(), logs are recorded if stored.
	 *
	 * @return null
	 */
	public static function record_stored_logs_and_errors() {
		// record logs
		if ( ! empty( self::$recorded_logs ) ) {
			foreach ( self::$recorded_logs as $subject => $data ) {
				self::record_log( $subject, $data );
			}
			// empty
			self::$recorded_logs = array();
		}
		// records errors
		if ( ! empty( self::$recorded_errors ) ) {
			foreach ( self::$recorded_errors as $subject => $data ) {
				self::record_log( $subject, $data , true );
			}
			// empty
			self::$recorded_errors = array();
		}
	}

	/**
	 * Utility function for si_new_record
	 * @param  string  $subject      short title
	 * @param  array   $data         array of data for the record
	 * @param  boolean $error        is the type an error or log
	 * @param  integer $associate_id NOT USED
	 * @return null
	 */
	public static function record_log( $subject = '', $data = array(), $error = false, $associate_id = 0 ) {
		$subject = self::redact_log_string( $subject );
		$data    = self::redact_log_data( $data, $subject );
		if ( ! is_array( $data ) ) {
			$data = array(
				'redacted' => is_scalar( $data ) ? (string) $data : self::REDACTED_VALUE,
			);
		}
		$type = ( $error ) ? self::ERROR_TYPE : self::LOG_TYPE ;
		do_action( 'si_new_record',
			$data,
			$type,
			$associate_id,
			$subject,
		1 );
	}

	/**
	 * Recursively remove secrets and payment data from a log payload.
	 *
	 * Raw gateway requests and responses are intentionally discarded instead
	 * of attempting to enumerate every field used by every payment provider.
	 *
	 * @since 20.8.19
	 *
	 * @param mixed  $data    Log payload.
	 * @param string $subject Log subject.
	 * @return mixed
	 */
	public static function redact_log_data( $data, $subject = '' ) {
		if ( self::is_gateway_payload_subject( $subject ) ) {
			return self::REDACTED_VALUE;
		}

		if ( is_array( $data ) ) {
			$redacted = array();
			foreach ( $data as $key => $value ) {
				$redacted[ $key ] = self::is_sensitive_key( $key )
					? self::REDACTED_VALUE
					: self::redact_log_data( $value );
			}
			return $redacted;
		}

		if ( is_object( $data ) || is_resource( $data ) ) {
			return self::REDACTED_VALUE;
		}

		if ( is_string( $data ) ) {
			return self::redact_log_string( $data );
		}

		return $data;
	}

	/**
	 * Remove secrets embedded in an unstructured log string.
	 *
	 * @since 20.8.19
	 *
	 * @param mixed $value Log value.
	 * @return string
	 */
	public static function redact_log_string( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		// Match whole query keys, then use the same boundary rules as array keys.
		// A raw pattern alternative would treat the "cc" inside success= as a card field.
		$value = (string) preg_replace_callback(
			'/(?<![A-Za-z0-9_])([A-Za-z0-9_]+)=([^&\s\]]*)/',
			static function ( $matches ) {
				if ( ! self::is_sensitive_key( $matches[1] ) ) {
					return $matches[0];
				}

				return $matches[1] . '=' . self::REDACTED_VALUE;
			},
			$value
		);
		$value = self::redact_structured_secrets( $value );
		$value = (string) preg_replace(
			'/(\bAuthorization\s*:\s*(?:Bearer|Basic)\s+)\S+/i',
			'$1' . self::REDACTED_VALUE,
			$value
		);
		$value = (string) preg_replace(
			"/define\(\s*(['\"])(DB_PASSWORD|AUTH_KEY|SECURE_AUTH_KEY|LOGGED_IN_KEY|NONCE_KEY|AUTH_SALT|SECURE_AUTH_SALT|LOGGED_IN_SALT|NONCE_SALT)(\\1)\s*,\s*(['\"])([^'\"]*)(\\4)\s*\)\s*;/i",
			"define( '$2', '" . self::REDACTED_VALUE . "' );",
			$value
		);

		// Remove potential primary account numbers, including spaced/dashed forms.
		return (string) preg_replace( '/(?<!\d)(?:\d[ -]?){12,18}\d(?!\d)/', self::REDACTED_VALUE, $value );
	}

	/**
	 * Redact JSON and XML values whose names match a sensitive key.
	 *
	 * Quoted JSON keys can be longer than one pattern alternative, values can
	 * be bare numbers, and gateway blobs can use XML elements.
	 *
	 * @since 20.8.19
	 *
	 * @param string $value Log text.
	 * @return string
	 */
	private static function redact_structured_secrets( $value ) {
		$value = (string) preg_replace_callback(
			'/(["\'])([A-Za-z0-9_]+)\1\s*:\s*(?:"[^"]*"|\'[^\']*\'|-?\d+(?:\.\d+)?|true|false|null)/',
			static function ( $matches ) {
				if ( ! self::is_sensitive_key( $matches[2] ) ) {
					return $matches[0];
				}

				return $matches[1] . $matches[2] . $matches[1] . ': "' . self::REDACTED_VALUE . '"';
			},
			$value
		);

		return (string) preg_replace_callback(
			'/<([A-Za-z0-9_:-]+)>([^<]*)<\/\1>/',
			static function ( $matches ) {
				if ( ! self::is_sensitive_key( $matches[1] ) ) {
					return $matches[0];
				}

				return '<' . $matches[1] . '>' . self::REDACTED_VALUE . '</' . $matches[1] . '>';
			},
			$value
		);
	}

	/**
	 * Whether an array key identifies data that must never be logged.
	 *
	 * @since 20.8.19
	 *
	 * @param mixed $key Array key.
	 * @return bool
	 */
	private static function is_sensitive_key( $key ) {
		if ( ! is_string( $key ) ) {
			return false;
		}

		$key = strtolower( preg_replace( '/[^a-z0-9]+/i', '_', $key ) );

		return (bool) preg_match(
			'/(?:^|_)' . self::sensitive_key_pattern() . '(?=_|$)/i',
			$key
		);
	}

	/**
	 * Pattern for secret and payment field names found in structured or
	 * unstructured logs.
	 *
	 * Includes the concatenated field names used by the bundled Beanstream,
	 * NMI, Authorize.Net-compatible, and PaymentExpress integrations.
	 *
	 * @since 20.8.19
	 *
	 * @return string
	 */
	private static function sensitive_key_pattern() {
		return '(?:_?wpnonce|nonce|authorization|auth(?:orization)?_?code|password|passwd|pwd|secret|token|api_?key|consumer_?(?:key|secret)|private_?key|signature|cookie|session|card(?:_?(?:holder_?)?(?:number|num|code|name|owner))?|cc_?[a-z_]*|ccnumber|ccexp|cvv|cvc|cvc2|pan|account_?(?:number|num)|routing_?(?:number|num)|bank_?(?:name|account|routing)|check(?:name|aba|account)|trncard(?:owner|number|cvd)|trnexp(?:month|year)|dateexpiry|x_exp_date|email|api_?response|raw_?response|gateway_?response|transaction_?id|trans_?id|x_login|x_tran_key)';
	}

	/**
	 * Whether a subject indicates an opaque payment-provider request or
	 * response.
	 *
	 * @since 20.8.19
	 *
	 * @param string $subject Log subject.
	 * @return bool
	 */
	private static function is_gateway_payload_subject( $subject ) {
		return is_string( $subject ) && (bool) preg_match(
			'/^(?=.*(?:^|[^a-z0-9])(?:authorize\s*net|auth\.?\s*net|usaepay|bluepay|paypal|stripe|square|nmi|eway|beanstream|braintree|payment\s*express|mercadopago|pagseguro|2checkout|sa_2co)(?:[^a-z0-9]|$))(?=.*(?:post[_\s-]?(?:data|string)|\bresponse\b|error\s+message|payment\s+status\s+lookup\s+failed)).*$/i',
			$subject
		);
	}

	/**
	 * Periodically query for logs older than si_logs_purge_filter_delay (defaults to 15 days) and delete them.
	 *
	 * @return null
	 */
	public static function purge_old_logs() {
		$args = array(
			'post_type' => SI_Record::POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids',
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Standard WP_Query usage, no alternative without custom table
					array(
						'taxonomy' => SI_Record::TAXONOMY,
						'field'    => 'slug',
						'terms'    => self::LOG_TYPE,
					),
				),
		);

		add_filter( 'posts_where', array( __CLASS__, 'filter_where_with_when' ) ); // add filter to base return on dates
		$records = new WP_Query( $args );

		remove_filter( 'posts_where', array( __CLASS__, 'filter_where_with_when' ) ); // Remove filter
		foreach ( $records->posts as $record_id ) {
			if ( has_term( self::LOG_TYPE, SI_Record::TAXONOMY, $record_id ) ) { // confirm
				wp_delete_post( $record_id, true );
			}
		}
	}

	/**
	 * Filter WP_Query with a post_date
	 * @param  string $where query
	 * @return string        full query
	 */
	public static function filter_where_with_when( $where = '' ) {
		// posts 15+ old
		$offset = apply_filters( 'si_logs_purge_filter_delay', date_i18n( 'Y-m-d', strtotime( '-1 days' ) ), $where );
		$where .= " AND post_date <= '" . $offset . "'";
		return $where;
	}
}
