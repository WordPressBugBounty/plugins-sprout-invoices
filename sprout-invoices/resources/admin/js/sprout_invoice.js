var $ = jQuery.noConflict();

function si_format_money ( value ) {

	if ( typeof Intl !== 'object' ) {
		var parts = value.toString().split(si_js_object.localeconv.mon_decimal_point);
		parts[0] = parts[0].replace( /\B(?=(\d{3})+(?!\d))/g, si_js_object.localeconv.mon_thousands_sep );
		return si_js_object.localeconv.currency_symbol + parts.join(si_js_object.localeconv.mon_decimal_point);
	}
	var cformatter = new Intl.NumberFormat( si_js_object.locale_standard, {
		style: 'currency',
		currency: si_js_object.localeconv.int_curr_symbol.trim(),
		maximumFractionDigits: si_js_object.localeconv.int_frac_digits,
		minimumFractionDigits: si_js_object.localeconv.int_frac_digits,
	});
	return cformatter.format( value );
}

jQuery(function($) {

	/**
	 * Tooltip with qtip
	 * @type {}
	 */
	$('.si_tooltip[title!=""], .helptip[title!=""]').qtip({
		style: {
			classes: 'qtip-bootstrap'
		}
	});

	/**
	 * System Health check button
	 */
	$( '#si_system_health_check' ).on( 'click', function(e) {
		var nonce                = $( '#si_system_health_check' ).data( 'nonce' );
		var completedHealthCheck = ( data ) => {
			if ( data.error ) {
				$( '#si_health_check_error' ).show();
				$( '#si_health_check_text' ).show();
				$( '#si_health_check_textarea' ).val( data.response );
			}
			else {
				$( '#si_health_check_text' ).show();
				$( '#si_health_check_textarea' ).val( data );

				let si_system_health_check_txt = document.getElementById( "si_health_check_textarea" );
				si_system_health_check_txt.select();
				si_system_health_check_txt.setSelectionRange( 0, 99999 ); //Range for mobile devices
				navigator.clipboard.writeText( si_system_health_check_txt.value );
				alert( "Copied the text to the clipboard." );
			};
			$( '#si_health_check' ).removeAttr( 'disabled' );
			return data;
		}
		e.preventDefault();
		$(' #si_health_check' ).attr( 'disabled', 'disabled' );
		$.post(
			ajaxurl,
			{ action: 'si_system_health_check', si_nonce: nonce },
			completedHealthCheck
		);
	});

    jQuery("#the-list .doc_status_change").on('click', function(e) {
		e.preventDefault();
		var $status_change_link = $( this ),
			$status_button = $( this ).closest('.quick_status_update'),
			$row_actions = $status_change_link.closest( '.row-actions' ),
			$new_status = $status_change_link.data( 'status-change' ),
			$id = $status_change_link.data( 'id' ),
			$nonce = $status_change_link.data( 'nonce' ),
			$status_span = $( '#status_' + $id );

		$status_button.html(si_js_object.inline_spinner);

		$.post( ajaxurl, { action: 'si_change_doc_status', id: $id, status: $new_status, change_status_nonce: $nonce },
			function( data ) {
				if ( data.error ) {
					$status_span.html( data.response );
				}
				else {
					$button_html = $( data.new_button ).html();
					// swap out the button with the new one
					$status_button.html( $button_html );
				};
				return data;
			}
		);
	});

});
