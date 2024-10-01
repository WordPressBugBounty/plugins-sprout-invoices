<?php

/**
 * Estimates Controller
 *
 *
 * @package Sprout_Invoice
 * @subpackage Estimates
 */
class SI_Estimate_Submissions extends SI_Controller {
	const SUBMISSION_SHORTCODE = 'estimate_submission';
	const DEFAULT_NONCE = 'si_estimate_submission';
	const SUBMISSION_UPDATE = 'estimate_submission';
	const SUBMISSION_SUCCESS_QV = 'success';

	/**
	 * Init
	 *
	 * @return void
	 */
	public static function init() {
		// This class may be removed in the future.
	}
}
