<?php

/**
 * Invoice Model
 *
 *
 * @package Sprout_Invoices
 * @subpackage Invoice
 */
class SI_Invoice extends SI_Post_Type {

	const POST_TYPE = 'sa_invoice';
	const REWRITE_SLUG = 'sprout-invoice';

	const STATUS_TEMP = 'temp'; // invoice is in a draft state, can't use 'draft' otherwise a url will not be created
	const STATUS_PENDING = 'publish'; // invoice pending payment
	const STATUS_FUTURE = 'future'; // invoice pending publish
	const STATUS_PARTIAL = 'partial'; // invoice is partially paid for
	const STATUS_PAID = 'complete'; // invoice is complete
	const STATUS_WO = 'write-off'; // invoice is written off
	const STATUS_ARCHIVED = 'archived'; // invoice is partially paid for

	/**
	 * Invoice instances.
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Meta keys for the invoice class.
	 *
	 * @var array
	 */
	private static $meta_keys = array(
		// migrated/match of estimates
		'client_id'       => '_client_id', // int
		'currency'        => '_doc_currency', // string
		'deposit'         => '_deposit', // float
		'discount'        => '_doc_discount', // int
		'due_date'        => '_due_date', // int
		'estimate_id'     => '_estimate_id',
		'expiration_date' => '_expiration_date', // int
		'fees'            => '_fees', // array
		'id'              => '_invoice_id', // string
		'invoice_id'      => '_invoice_id',
		'issue_date'      => '_invoice_issue_date', // int
		'line_items'      => '_doc_line_items', // array
		'notes'           => '_invoice_notes', // string
		'po'              => '_doc_po_number', // string
		'private_notes'   => '_invoice_private_notes', // string
		'project_id'      => '_project_id', // int
		'send_notes'      => '_invoice_send_notes', // string
		'shipping'        => '_doc_shipping', // int
		'submission'      => '_submitted_items', // array
		'tax'             => '_doc_tax', // int
		'tax2'            => '_doc_tax2', // int
		'total'           => '_total', // int
		'terms'           => '_doc_terms', // string
		'user_id'         => '_user_id', // int
	);

	/**
	 * Payment IDs.
	 *
	 * @var array
	 */
	public $payment_ids = array();

	/**
	 * Payment total.
	 *
	 * @var float
	 */
	public $payment_total = 0.00;

	/**
	 * Payments total.
	 *
	 * @var float
	 */
	public $payments_total = 0.00;

	/**
	 * Complete payment total.
	 *
	 * @var float
	 */
	public $complete_payment_total = 0.00;

	/**
	 * Pending payments total.
	 *
	 * @var float
	 */
	public $pending_payments_total = 0.00;

	/**
	 * Invoice subtotal.
	 *
	 * @var float
	 */
	public $subtotal = 0.00;

	/**
	 * Invoice fees total.
	 *
	 * @var null|float
	 */
	public $fees_total = null;

	/**
	 * Calculated invoice total.
	 *
	 * @var float
	 */
	public $calculated_total = 0.00;

	public static function init() {
		// register Invoice post type
		$post_type_args = array(
			'public' => true,
			'exclude_from_search' => true,
			'has_archive' => false,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'rewrite' => array(
				'slug' => self::REWRITE_SLUG,
				'with_front' => false,
			),
			'supports' => array( '' ),
		);
		self::register_post_type( self::POST_TYPE, 'Invoice', 'Invoices', $post_type_args );

		self::register_post_statuses();
	}

	public static function get_statuses() {
		$statuses = array(
			self::STATUS_TEMP => __( 'Draft', 'sprout-invoices' ),
			self::STATUS_PENDING => __( 'Pending', 'sprout-invoices' ),
			self::STATUS_FUTURE => __( 'Scheduled', 'sprout-invoices' ),
			self::STATUS_PARTIAL => __( 'Outstanding Balance', 'sprout-invoices' ),
			self::STATUS_PAID => __( 'Paid', 'sprout-invoices' ),
			self::STATUS_WO => __( 'Written Off', 'sprout-invoices' ),
			self::STATUS_ARCHIVED => __( 'Archived', 'sprout-invoices' ),
		);
		return apply_filters( 'si_invoice_statuses', $statuses );
	}

	/**
	 * Post statuses for payments
	 * @return
	 */
	private static function register_post_statuses() {
		$statuses = self::get_statuses();
		foreach ( $statuses as $status => $label ) {
			register_post_status(
				$status,
				array(
					'label'                     => $label,
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>' ),
				)
			);
		}
	}

	/**
	 * Is the current query for an invoice(s)
	 * @param  object  $query
	 * @return boolean
	 */
	public static function is_invoice_query( WP_Query $query = null ) {
		if ( is_null( $query ) ) {

			if ( function_exists( 'get_post_type' ) ) {
				if ( '' !== get_post_type() && self::POST_TYPE === get_post_type() ) {
					return true;
				}
			}

			global $wp_query;
			$query = $wp_query;
		}
		if ( ! isset( $query->query_vars['post_type'] ) ) {
			return false; // normal posts query
		}
		if ( $query->query_vars['post_type'] == self::POST_TYPE ) {
			return true;
		}
		if ( is_array( $query->query_vars['post_type'] ) && in_array( self::POST_TYPE, $query->query_vars['post_type'] ) ) {
			return true;
		}
		return false;
	}

	public function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 *
	 *
	 * @static
	 * @param int     $id
	 * @return Sprout_Invoices_Invoice
	 */
	public static function get_instance( $id = 0 ) {
		if ( ! $id ) {
			return null; }

		if ( ! isset( self::$instances[ $id ] ) || ! self::$instances[ $id ] instanceof self ) {
			self::$instances[ $id ] = new self( $id ); }

		if ( ! isset( self::$instances[ $id ]->post->post_type ) ) {
			return null; }

		if ( self::$instances[ $id ]->post->post_type != self::POST_TYPE ) {
			return null; }

		return self::$instances[ $id ];
	}

	public static function create_invoice( $passed_args, $status = '' ) {
		$defaults = array(
			'subject' => sprintf( __( 'New Invoice: %s', 'sprout-invoices' ), date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), current_time( 'timestamp' ) ) ),
			'user_id' => '',
			'invoice_id' => '',
			'estimate_id' => '',
			'client_id' => '',
			'project_id' => '',
			'status' => ( $status ) ? $status : self::STATUS_TEMP,
			'deposit' => (float) 0,
			'total' => (float) 0,
			'currency' => '',
			'po_number' => '',
			'discount' => '',
			'tax' => (float) 0,
			'tax2' => (float) 0,
			'notes' => SI_Invoices_Edit::get_default_notes(),
			'terms' => SI_Invoices_Edit::get_default_terms(),
			'issue_date' => time(),
			'due_date' => 0,
			'expiration_date' => 0,
			'line_items' => array(),
			'fees' => array(),
			'fields' => array(),
		);
		$args = wp_parse_args( $passed_args, $defaults );

		$id = wp_insert_post( array(
			'post_status' => $args['status'],
			'post_type' => self::POST_TYPE,
			'post_title' => $args['subject'],
		) );
		if ( is_wp_error( $id ) ) {
			return 0;
		}

		$invoice = self::get_instance( $id );

		if ( isset( $args['status'] ) ) {
			$invoice->set_submission_fields( $args['status'] );
		}

		// Set the submitted user id if logged in.
		if ( is_user_logged_in() ) {
			$invoice->set_user_id( get_current_user_id() );
		}

		$invoice->set_user_id( $args['user_id'] );
		$invoice->set_invoice_id( $args['invoice_id'] );
		$invoice->set_estimate_id( $args['estimate_id'] );
		$invoice->set_client_id( $args['client_id'] );
		$invoice->set_project_id( $args['project_id'] );
		$invoice->set_status( $args['status'] );
		$invoice->set_deposit( $args['deposit'] );
		$invoice->set_total( $args['total'] );
		$invoice->set_currency( $args['currency'] );
		$invoice->set_po_number( $args['po_number'] );
		$invoice->set_discount( $args['discount'] );
		$invoice->set_tax( $args['tax'] );
		$invoice->set_tax2( $args['tax2'] );
		$invoice->set_notes( $args['notes'] );
		$invoice->set_terms( $args['terms'] );

		$issue_date = ( is_numeric( $args['issue_date'] ) ) ? $args['issue_date'] : strtotime( $args['issue_date'] );
		$invoice->set_issue_date( $issue_date );

		$due_date = ( is_numeric( $args['due_date'] ) ) ? $args['due_date'] : strtotime( $args['due_date'] );
		$invoice->set_due_date( $due_date );

		$expiration_date = ( is_numeric( $args['expiration_date'] ) ) ? $args['expiration_date'] : strtotime( $args['expiration_date'] );
		$invoice->set_expiration_date( $expiration_date );

		$invoice->set_line_items( $args['line_items'] );

		$invoice->set_fees( $args['fees'] );

		do_action( 'sa_new_invoice', $invoice, $args );
		return $id;
	}

	/////////////
	// Status //
	/////////////

	public function get_status() {
		return $this->post->post_status;
	}

	public function set_status( $status ) {
		// Don't do anything if there's no true change
		$current_status = $this->get_status();
		if ( $current_status === $status ) {
			return;
		}

		// confirm the status exists
		if ( ! in_array( $status, array_keys( self::get_statuses() ) ) ) {
			return;
		}

		$this->post->post_status = $status;
		$this->save_post();
		do_action( 'si_invoice_status_updated', $this, $status, $current_status );
	}

	public function set_as_temp() {
		$this->set_status( self::STATUS_TEMP );
	}

	public function set_pending() {
		$this->set_status( self::STATUS_PENDING );
	}

	public function set_as_partial() {
		$this->set_status( self::STATUS_PARTIAL );
	}

	public function set_as_paid() {
		$this->set_status( self::STATUS_PAID );
	}

	public function set_as_written_off() {
		$this->set_status( self::STATUS_WO );
	}

	public function set_archived() {
		$this->set_status( self::STATUS_ARCHIVED );
	}

	public function get_status_label( $status = '' ) {
		if ( '' === $status ) {
			$status = $this->get_status();
		}
		$statuses = self::get_statuses();
		return $statuses[ $status ];
	}

	///////////
	// Meta //
	///////////

	/**
	 * Get the remaining invoice balance
	 * @return
	 */
	public function get_balance() {
		$total = $this->get_calculated_total( false );
		$paid = $this->get_payments_total( false );
		$balance = floatval( $total - $paid );
		if ( apply_filters( 'si_do_attempt_status_update_on_get_balance', true, $this ) ) {
			if ( self::STATUS_PENDING === $this->get_status() ) {
				if ( round( $balance, 2 ) < 0.01 ) {
					$this->set_as_paid();
				}
			}
		}
		return round( $balance, 2 );
	}

	/**
	 * Check if the invoice has a deposit.
	 *
	 * @since 20.4
	 * @return boolean
	 */
	public function has_deposit() {
		$deposit = $this->get_deposit();
		if ( $deposit > 0 && $deposit < $this->get_balance() ) {
			return true;
		}
		return false;
	}

	/**
	 * Deposit Adjustment
	 */
	public function get_deposit( $unfiltered = false ) {
		$balance = $this->get_balance();
		$deposit = floatval( $this->get_post_meta( self::$meta_keys['deposit'] ) );
		if ( $deposit > $balance ) { // check if deposit is more than what's due.
			$deposit = floatval( $balance );
		}
		if ( ! $unfiltered ) {
			$deposit = apply_filters( 'si_get_invoice_deposit', $deposit, $this );
		}
		return round( $deposit, 2 );
	}

	public function set_deposit( $deposit = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['deposit'] => $deposit,
		) );
		return $deposit;
	}

	/**
	 * Get the submission fields
	 * @return array
	 */
	public function get_submission_fields() {
		return $this->get_post_meta( self::$meta_keys['submission'] );
	}

	/**
	 * Save the submitted fields
	 * @param array $fields
	 */
	public function set_submission_fields( $fields = array() ) {
		$this->save_post_meta( array(
			self::$meta_keys['submission'] => $fields,
		) );
		return $fields;
	}

	/**
	 * Issue date
	 */
	public function get_issue_date() {
		$date = strtotime( $this->post->post_date );
		return $date;
	}

	public function set_issue_date( $issue_date = 0 ) {
		if ( is_integer( $issue_date ) ) {
			$issue_date = date( 'Y-m-d h:i:s', $issue_date );
		}
		$this->post->post_date = $issue_date;
		$this->save_post();
		return $issue_date;
	}

	/**
	 * Estimate id
	 */

	public function get_estimate_id() {
		$estimate_id = (int) $this->get_post_meta( self::$meta_keys['estimate_id'] );
		if ( $estimate_id == $this->get_id() ) {
			$estimate_id = 0;
		}
		return $estimate_id;
	}

	public function set_estimate_id( $estimate_id = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['estimate_id'] => $estimate_id,
		) );
		return $estimate_id;
	}

	/**
	 * Due date
	 */
	public function get_due_date() {
		$date = (int) $this->get_post_meta( self::$meta_keys['due_date'] );
		if ( ! $date ) {
			$days = apply_filters( 'si_default_due_in_days', 14, $this );
			$date = strtotime( $this->post->post_date ) + (DAY_IN_SECONDS * $days);
		};
		return $date;
	}

	public function set_due_date( $due_date = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['due_date'] => $due_date,
		) );
		return $due_date;
	}

	/**
	 * Expiration date
	 */
	public function get_expiration_date() {
		$date = (int) $this->get_post_meta( self::$meta_keys['expiration_date'] );
		if ( ! $date ) {
			$date = strtotime( $this->post->post_date ) + (DAY_IN_SECONDS * 30);
		};
		return $date;
	}

	public function set_expiration_date( $expiration_date = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['expiration_date'] => $expiration_date,
		) );
		return $expiration_date;
	}

	/**
	 * PO number
	 */
	public function get_po_number() {
		return $this->get_post_meta( self::$meta_keys['po'] );
	}

	public function set_po_number( $po_number = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['po'] => $po_number,
		) );
		return $po_number;
	}

	/**
	 * Client
	 */

	public function get_client() {
		if ( ! $this->get_client_id() ) {
			return new WP_Error( 'no_client', __( 'No client associated with this invoice.', 'sprout-invoices' ) );
		}
		return SI_Client::get_instance( $this->get_client_id() );
	}

	public function get_client_id() {
		return (int) $this->get_post_meta( self::$meta_keys['client_id'] );
	}


	/**
	 * Set the client id
	 *
	 * @param int $client_id Client ID.
	 *
	 * @return int $client_id Client ID.
	 */
	public function set_client_id( $client_id = 0 ) {
		if ( 'create_client' === $client_id ) {
			// Check if client meta is already set.
			if ( ! empty( $this->get_client_id() ) ) {
				return $this->get_client_id();
			} else {
				$this->save_post_meta(
					array(
						self::$meta_keys['client_id'] => $client_id,
					)
				);
			}
		} else {
			$this->save_post_meta(
				array(
					self::$meta_keys['client_id'] => $client_id,
				)
			);
		}

		return $client_id;
	}

	/**
	 * Invoice id
	 */

	public function get_invoice_id() {
		$id = $this->get_post_meta( self::$meta_keys['invoice_id'] );
		if ( ! $id ) {
			$id = $this->get_id();
			$this->set_invoice_id( $id );
		}
		return $id;
	}

	public function set_invoice_id( $invoice_id = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['invoice_id'] => $invoice_id,
		) );
		return $invoice_id;
	}

	/**
	 * discount
	 */
	public function get_discount() {
		return (float) $this->get_post_meta( self::$meta_keys['discount'] );
	}

	public function set_discount( $discount = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['discount'] => $discount,
		) );
		return $discount;
	}

	public function get_discount_total() {
		$subtotal = (float) $this->get_subtotal();
		if ( $subtotal < 0.01 ) { // In case the line items are zero but the total has a value
			$subtotal = (float) $this->get_total();
		}
		if ( apply_filters( 'si_discount_after_taxes', true, $this ) ) {
			$tax_total = $subtotal * ( ( (float) $this->get_tax() ) / 100 );
			$tax2_total = $subtotal * ( ( (float) $this->get_tax2() ) / 100 );
			$subtotal = $subtotal + $tax_total + $tax2_total;
		}

		$discount = $subtotal * ( (float) $this->get_discount() / 100 );
		return si_get_number_format( (float) $discount );
	}

	/**
	 * Shipping
	 */
	public function get_shipping() {
		return (float) $this->get_post_meta( self::$meta_keys['shipping'] );
	}

	public function set_shipping( $shipping = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['shipping'] => $shipping,
		) );
		return $shipping;
	}

	/**
	 * Tax
	 */
	public function get_tax() {
		return (float) $this->get_post_meta( self::$meta_keys['tax'] );
	}

	public function set_tax( $tax = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['tax'] => $tax,
		) );
		return $tax;
	}

	public function get_tax_total() {
		$tax = $this->get_tax();
		$subtotal = $this->get_subtotal();
		$calculated_total = floatval( $subtotal * ( $tax / 100 ) );
		return round( $calculated_total, 2 );
	}

	/**
	 * Tax
	 */
	public function get_tax2() {
		return (float) $this->get_post_meta( self::$meta_keys['tax2'] );
	}

	public function set_tax2( $tax = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['tax2'] => $tax,
		) );
		return $tax;
	}

	public function get_tax2_total() {
		$tax = $this->get_tax2();
		$subtotal = $this->get_subtotal();
		$calculated_total = floatval( $subtotal * ( $tax / 100 ) );
		return round( $calculated_total, 2 );
	}

	/**
	 * Project
	 */
	public function get_project_id() {
		return (int) $this->get_post_meta( self::$meta_keys['project_id'] );
	}

	public function set_project_id( $project_id = 0 ) {

		if ( ! empty( $project_id ) ) {
			$this->set_client_id( SI_Project::get_instance( $project_id )->get_associated_clients()[0] );
		}

		$this->save_post_meta( array(
			self::$meta_keys['project_id'] => $project_id,
		) );
		return $project_id;
	}

	public function get_project() {
		if ( class_exists( 'SI_Project' ) ) {
			$project_id = $this->get_project_id();
			$project = SI_Project::get_instance( $project_id );
			return $project;
		}
	}

	/**
	 * totals
	 */

	public function get_total() {
		$total = $this->get_post_meta( self::$meta_keys['total'] );
		return $total;
	}

	public function set_total( $total = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['total'] => $total,
		) );
		return $total;
	}

	/**
	 * Calculated total includes any taxes and discounts.
	 * @return
	 */
	public function get_calculated_total( $check_balance = true ) {
		/**
		 * If we've already calculated the calculated_total, return it.  calculated_total default is 0, and if greater than 0
		 * we know we've already calculated it.
		 *
		 * @todo This isnt perfect would like the refactor. This function will run multiple times on the same page load. Ideally it would only run once
		 * when needed. However this comes from the reset totals function which is called multiple times. That needs to be addressed first.
		 */
		if ( 0 < $this->calculated_total ) {
			return $this->calculated_total;
		}
		if ( apply_filters( 'si_use_total_for_calculated_total', false ) ) {
			return (float) $this->get_total();
		}
		if ( $check_balance ) {
			// allow for the status to be updated on the fly.
			// SI_Invoices::change_status_after_payment attempts to do this, however sometimes there's a delay/cache
			$this->get_balance();
		}

		if ( isset( $this->calculated_total ) && 0.00 !== $this->calculated_total ) {
			return (float) $this->calculated_total;
		}

		$subtotal = (float) $this->get_subtotal();
		if ( $subtotal < 0.01 && ! apply_filters( 'si_allow_negative_doc_balance', false ) ) { // In case the line items are zero but the total has a value
			$subtotal = (float) $this->get_total();
		}

		$invoice_total = $subtotal + (float) $this->get_tax_total() + (float) $this->get_tax2_total();
		$total = $invoice_total - (float) $this->get_discount_total();

		$total = (float) $total + (float) $this->get_fees_total();

		$this->calculated_total = (float) $total;

		if ( $total !== $this->get_total() ) {
			$this->set_total( si_get_number_format( (float) $total ) );
		}

		return si_get_number_format( (float) $total );
	}

	public function set_calculated_total() {
		$total = (float) $this->get_calculated_total();
		$this->save_post_meta( array(
			self::$meta_keys['total'] => $total,
		) );
		return $total;
	}

	public function get_subtotal() {
		if ( isset( $this->subtotal ) && 0.00 !== $this->subtotal ) {
			return (float) $this->subtotal;
		}
		$line_items = $this->get_line_items();
		if ( ! empty( $line_items ) ) {
			foreach ( $line_items as $key => $data ) {
				if ( isset( $data['rate'] ) ) {

					if ( si_line_item_is_parent( $key, $line_items ) ) {
						continue;
					}

					$data['rate'] = ( isset( $data['rate'] ) ) ? (float) $data['rate'] : 0 ;
					$qty = ( isset( $data['qty'] ) ) ? (float) $data['qty'] : 1;
					$line_total = (float) ( $data['rate'] * $qty );
					if ( isset( $data['tax'] ) ) {
						$tax = $line_total * ( (float) $data['tax'] / 100 );
						$line_total = $line_total - si_get_number_format( $tax ); // convert so that rounding can occur before discount is removed.
					}

					$this->subtotal += apply_filters( 'si_line_item_total', $line_total, $data );
				}
			}
		}
		return $this->subtotal;
	}

	public function get_fees_total() {
		if ( isset( $this->fees_total ) ) {
			return $this->fees_total;
		}
		$fees_total = 0;
		$fees = $this->get_fees();
		if ( ! empty( $fees ) ) {
			foreach ( $fees as $fee_key => $data ) {

				if ( isset( $data['total_callback'] ) && is_callable( $data['total_callback'] ) ) {
					$fee_total = call_user_func_array( $data['total_callback'], array( $this, $data ) );
					$fees_total += apply_filters( 'si_fee_total', $fee_total, $data, true );
				} elseif ( isset( $data['total'] ) ) {
					$fees_total += apply_filters( 'si_fee_total', $data['total'], $data );
				}
			}
		}
		$this->fees_total = $fees_total;
		return $fees_total;
	}

	/**
	 * Terms
	 */
	public function get_terms() {
		$terms = $this->get_post_meta( self::$meta_keys['terms'] );
		return apply_filters( 'get_invoice_terms', $terms, $this );
	}

	public function set_terms( $terms = '' ) {
		$this->save_post_meta( array(
			self::$meta_keys['terms'] => $terms,
		) );
		return $terms;
	}

	/**
	 * Notes
	 */
	public function get_notes() {
		$notes = $this->get_post_meta( self::$meta_keys['notes'] );
		return apply_filters( 'get_invoice_notes', $notes, $this );
	}

	public function set_notes( $notes = '' ) {
		$this->save_post_meta( array(
			self::$meta_keys['notes'] => $notes,
		) );
		return $notes;
	}

	/**
	 * Send notes
	 */
	public function get_sender_note() {
		$send_notes = $this->get_post_meta( self::$meta_keys['send_notes'] );
		return apply_filters( 'get_sender_note', $send_notes, $this );
	}

	public function set_sender_note( $send_notes = '' ) {
		$this->save_post_meta( array(
			self::$meta_keys['send_notes'] => $send_notes,
		) );
		return $send_notes;
	}

	/**
	 * Line items
	 */
	public function get_line_items() {
		$line_items = $this->get_post_meta( self::$meta_keys['line_items'] );
		if ( ! is_array( $line_items ) ) {
			$line_items = array();
		}
		return $line_items;
	}

	public function set_line_items( $line_items = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['line_items'] => apply_filters( 'si_set_line_items', $line_items, $this ),
		) );
		return $line_items;
	}

	/**
	 * Fees
	 */
	public function get_fees() {
		$fees = $this->get_post_meta( self::$meta_keys['fees'] );
		if ( ! is_array( $fees ) ) {
			$fees = array();
		}
		return apply_filters( 'si_doc_fees', $fees, $this );
	}

	public function set_fees( $fees = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['fees'] => apply_filters( 'si_set_fees', $fees, $this ),
		) );
		return $fees;
	}

	public function remove_fee( $fee_id = '' ) {
		$fees = $this->get_fees();
		if ( isset( $fees[ $fee_id ] ) ) {
			unset( $fees[ $fee_id ] );
			$this->set_fees( $fees );
		}
		$this->reset_totals();
		return $fees;
	}

	/**
	 * Currency
	 */

	public function get_currency() {
		$code = $this->get_post_meta( self::$meta_keys['currency'] );
		if ( ! $code ) {
			$code = 'USD';
			$this->set_currency( $code );
		}
		return $code;
	}

	public function set_currency( $currency = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['currency'] => $currency,
		) );
		return $currency;
	}

	/**
	 * User id
	 */

	public function get_user_id() {
		return (int) $this->get_post_meta( self::$meta_keys['user_id'] );
	}

	public function set_user_id( $user_id = 0 ) {
		$this->save_post_meta( array(
			self::$meta_keys['user_id'] => $user_id,
		) );
		return $user_id;
	}


	//////////////
	// Utility //
	//////////////

	public function reset_totals( $calculate = true ) {
		$this->subtotal               = 0.00;
		$this->fees_total             = 0.00;
		$this->calculated_total       = 0.00;
		$this->payment_ids            = array();
		$this->payments_total         = 0.00;
		$this->complete_payment_total = 0.00;
		$this->pending_payments_total = 0.00;
		if ( $calculate ) {
			$this->get_subtotal();
			$this->get_fees_total();
			$this->get_calculated_total( false );
			$this->get_payments();
			$this->get_pending_payments_total();
			$this->get_payments_total();
		}
	}

	/**
	 * Get the Payment ids for each payment associated with this invoice.
	 *
	 * @since 5.0
	 * @return array Payment ids.
	 */
	public function get_payments() {
		$this->payment_ids = SI_Payment::get_payments( array( 'invoices' => $this->ID ) );
		return $this->payment_ids;
	}

	/**
	 * Get Pending Payments array.
	 *
	 * @since 20.6.0
	 *
	 * @return array Payment ids.
	 */
	public function get_pending_payments() {
		$payment_ids      = $this->get_payments();
		$pending_payments = array();
		foreach ( $payment_ids as $payment_id ) {
			$payment = SI_Payment::get_instance( $payment_id );
			if ( SI_Payment::STATUS_PENDING === $payment->get_status() ) {
				$pending_payments[] = $payment_id;
			}
		}
		return $pending_payments;
	}

	/**
	 * Get the pending payments total for an invoice.
	 *
	 * @since 7.0
	 * @return array Pending payment totals.
	 */
	public function get_pending_payments_total() {
		/**
		 * If we've already calculated the pending payments total, return it.  pending_payments_total default is 0, and if greater than 0
		 * we know we've already calculated it.
		 *
		 * @todo This isnt perfect would like the refactor. This function will run multiple times on the same page load. Ideally it would only run once
		 * when needed. However this comes from the reset totals function which is called multiple times. That needs to be addressed first.
		 */
		if ( 0 < $this->pending_payments_total ) {
			return $this->pending_payments_total;
		}
		$payment_ids = $this->get_payments();
		foreach ( $payment_ids as $payment_id ) {
			$payment = SI_Payment::get_instance( $payment_id );
			if ( SI_Payment::STATUS_PENDING === $payment->get_status() ) {
				$this->pending_payments_total += $payment->get_amount();
			}
		}
		return abs( $this->pending_payments_total );
	}

	/**
	 * Get the total amount of payments made on this invoice.
	 *
	 * @since 6.0
	 * @param  boolean $pending Whether to include pending payments.
	 * @return float   The total amount of payments made on this invoice.
	 */
	public function get_payments_total( $pending = true ) {
		/**
		 * If we've already calculated the payment_total, return it. payment_total default is 0, and if greater than 0
		 * we know we've already calculated it.
		 *
		 * @todo This isnt perfect would like the refactor. This function will run multiple times on the same page load. Ideally it would only run once
		 * when needed. However this comes from the reset totals function which is called multiple times. That needs to be addressed first.
		 */
		if ( 0 < $this->payment_total ) {
			return abs( $this->payment_total );
		}
		$payment_ids = $this->get_payments();
		foreach ( $payment_ids as $payment_id ) {
			$payment = SI_Payment::get_instance( $payment_id );
			if ( ! $pending && SI_Payment::STATUS_PENDING === $payment->get_status() ) {
				continue;
			} elseif ( ! in_array( $payment->get_status(), array( SI_Payment::STATUS_VOID, SI_Payment::STATUS_RECURRING, SI_Payment::STATUS_CANCELLED, SI_Payment::STATUS_PENDING ) ) ) {
				$this->payment_total += (float) $payment->get_amount();
			}
		}
		if ( $pending ) {
			$this->payments_total = abs( $this->payment_total );
		} else {
			$this->complete_payment_total = abs( $this->payment_total );
		}

		return abs( $this->payment_total );
	}

	/**
	 * Get the invoices based on an estimate id.
	 * @param  integer $estimate_id
	 * @return array
	 */
	public static function get_invoices_by_estimate_id( $estimate_id = 0 ) {
		$invoice_ids = self::find_by_meta( self::POST_TYPE, array( self::$meta_keys['estimate_id'] => $estimate_id ) );
		return $invoice_ids;
	}

	public function get_history( $type = '' ) {
		return SI_Record::get_records_by_association( $this->ID );
	}

	/**
	 * Get array of invoices that are overdue within $timestamp and yesterday.
	 * Only check against pending and partial payments since any other status
	 * means there's no expected payments left.
	 *
	 * @static
	 * @param int     $timestamp
	 * @return array
	 */
	public static function get_overdue_invoices( $after = 0, $before = 0 ) {
		if ( ! $after ) {
			$after = apply_filters( 'si_get_overdue_yesterday_timestamp', strtotime( 'Yesterday',  current_time( 'timestamp' ) ) );
		}

		if ( ! $before ) {
			$before = apply_filters( 'si_get_overdue_before_timestamp', $after + ( DAY_IN_SECONDS - 1 ) ); // 24 hours
		}

		$args = array(
				'post_type' => self::POST_TYPE,
				'post_status' => array( self::STATUS_PENDING, self::STATUS_PARTIAL ),
				'posts_per_page' => -1,
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => self::$meta_keys['due_date'],
						'value' => array(
							$after,
							$before,
							), // yesterday
						'compare' => 'BETWEEN',
						),
					),
			);
		return get_posts( $args );
	}
}
