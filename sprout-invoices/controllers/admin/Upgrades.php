<?php
/**
 * Upgrade/Migration Controller
 *
 * Handles automatic database migrations and upgrades when plugin is updated
 *
 * @package Sprout_Invoice
 * @subpackage Admin
 */
class SI_Upgrades extends SI_Controller {
	const OPTION_KEY = 'si_db_version';
	const HASH_MIGRATION_VERSION = '20.8.9'; // Version when hash authentication was added

	public static function init() {
		// Run on plugin activation
		add_action( 'si_plugin_activation_hook', array( __CLASS__, 'maybe_run_upgrades' ), 20 );

		// Check on init to ensure migration runs regardless of whether admin visits site
		// This prevents race condition where clients access invoices before migration completes
		add_action( 'init', array( __CLASS__, 'maybe_run_upgrades' ), 5 );
	}

	/**
	 * Check if upgrades need to run and execute them
	 */
	public static function maybe_run_upgrades() {
		static $did_run = false;

		// Prevent multiple executions within the same request.
		if ( $did_run ) {
			return;
		}

		$did_run = true;

		$current_db_version = get_option( self::OPTION_KEY, '0' );
		$plugin_version = Sprout_Invoices::SI_VERSION;

		// If this is a fresh install or upgrade is needed
		if ( version_compare( $current_db_version, $plugin_version, '<' ) ) {
			self::run_upgrades( $current_db_version, $plugin_version );

			// Update stored version
			update_option( self::OPTION_KEY, $plugin_version );
		}
	}

	/**
	 * Run necessary upgrade routines based on version
	 *
	 * @param string $from_version Version upgrading from
	 * @param string $to_version   Version upgrading to
	 */
	private static function run_upgrades( $from_version, $to_version ) {
		// Hash authentication migration (added in 20.8.9)
		if ( version_compare( $from_version, self::HASH_MIGRATION_VERSION, '<' ) ) {
			self::upgrade_add_access_hashes();
		}

		// Future upgrades can be added here
		// Example:
		// if ( version_compare( $from_version, '21.0.0', '<' ) ) {
		//     self::upgrade_some_new_feature();
		// }

		do_action( 'si_after_upgrades', $from_version, $to_version );
	}

	/**
	 * Upgrade: Generate access hashes for existing documents
	 *
	 * This migration generates secure access hashes for all existing invoices
	 * and estimates to support the hash-based authentication system.
	 */
	private static function upgrade_add_access_hashes() {
		// Prevent timeout on large databases
		set_time_limit( 300 ); // 5 minutes

		// Generate hashes for invoices
		$invoices = get_posts( array(
			'post_type'      => SI_Invoice::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		) );

		$invoice_count = 0;
		foreach ( $invoices as $invoice_id ) {
			$existing_hash = get_post_meta( $invoice_id, '_invoice_access_hash', true );
			if ( empty( $existing_hash ) ) {
				$hash = bin2hex( random_bytes( 32 ) );
				update_post_meta( $invoice_id, '_invoice_access_hash', $hash );
				$invoice_count++;
			}
		}

		// Generate hashes for estimates
		$estimates = get_posts( array(
			'post_type'      => SI_Estimate::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		) );

		$estimate_count = 0;
		foreach ( $estimates as $estimate_id ) {
			$existing_hash = get_post_meta( $estimate_id, '_estimate_access_hash', true );
			if ( empty( $existing_hash ) ) {
				$hash = bin2hex( random_bytes( 32 ) );
				update_post_meta( $estimate_id, '_estimate_access_hash', $hash );
				$estimate_count++;
			}
		}

		// Log the migration
		do_action( 'si_log', 'Hash Migration Complete', sprintf(
			'Generated hashes for %d invoices and %d estimates',
			$invoice_count,
			$estimate_count
		) );

		// Store flag that this specific migration has run
		update_option( 'si_hash_migration_completed', true );
	}

	/**
	 * Check if hash migration has been completed
	 *
	 * @return bool
	 */
	public static function is_hash_migration_complete() {
		return (bool) get_option( 'si_hash_migration_completed', false );
	}

	/**
	 * Get current database version
	 *
	 * @return string
	 */
	public static function get_db_version() {
		return get_option( self::OPTION_KEY, '0' );
	}

	/**
	 * Ensure a document has an access hash, generating one if missing
	 *
	 * This provides a fallback for the race condition where clients access
	 * documents before the migration runs on admin_init.
	 *
	 * @param int $post_id The document ID
	 * @return string The access hash
	 */
	public static function ensure_doc_hash( $post_id ) {
		$post_type = get_post_type( $post_id );
		$hash_meta_key = '';

		if ( $post_type === SI_Invoice::POST_TYPE ) {
			$hash_meta_key = '_invoice_access_hash';
		} elseif ( $post_type === SI_Estimate::POST_TYPE ) {
			$hash_meta_key = '_estimate_access_hash';
		} else {
			return '';
		}

		$hash = get_post_meta( $post_id, $hash_meta_key, true );

		// If hash doesn't exist, generate it now
		if ( empty( $hash ) ) {
			$hash = bin2hex( random_bytes( 32 ) );
			update_post_meta( $post_id, $hash_meta_key, $hash );

			// Log that we had to generate a hash on-the-fly
			do_action( 'si_log', 'Generated Hash On-Demand', sprintf(
				'Generated access hash for %s ID %d (migration may not have run yet)',
				$post_type,
				$post_id
			) );
		}

		return $hash;
	}
}
