/**
 * The main Vue instance for our plugin settings page
 * @link https://vuejs.org/v2/guide/instance.html
 */
new Vue( {

	// DOM selector for our app's main wrapper element
	el: '#si_settings',

	// Data that will be proxied by Vue.js to provide reactivity to our template
	data: {
		isSaving: false,
		isLoading: false,
		viewNotifications: false,
		message: '',
		addonAdminToggle: '',
		vm: SI_Settings.options,
		addons: SI_Settings.addons,
		paymentGateways: SI_Settings.payment_gateways,
		ccPP: SI_Settings.ccPP,
	},
	mounted: function(){
		// var up vue for callback
		var self = this;
		for ( var addon in self.addons ) {
			if ( 1 === self.addons[addon]['active'] && true === self.addons[addon]['settings'] ) {
				jQuery( '.settings_' + self.addons[addon]['settingID'] ).show();
			} else {
				jQuery( '.settings_' + self.addons[addon]['settingID'] ).hide();
			}
		}
		this.addonAdminToggle = window.location.href.split(	'#' )[1];
		if ( undefined === this.addonAdminToggle ) {
			this.addonAdminToggle = 'start';
		}
		// after vue is completed
		this.$nextTick(function () {
			jQuery(function() {
				if ( si_js_object.redactor ) {
					// setup redactor
					jQuery('.si_wysiwyg').redactor({
						callbacks: {
							// after mouse leaves
					        blur: function(e) {
					        	var name = this.source.getName(),
									html = this.source.getCode();
								// prop the vue data
								self.vm[name] = html;
					        }
					    }

					});
				}
			});
		});
	},
	// Methods that can be invoked from within our template
	methods: {

		// Call the browser's history back
		goBack: function() {
			window.history.back();
		},

		// Load Settings Page
		loadSettingsPage: function( val ) {
			if ( val in this.addons ) {
				window.location.href = '/wp-admin/admin.php?page=sprout-invoices&tab=addons&section=' + val + '#addon_settings';
			}

			if ( val in this.paymentGateways ) {
				window.location.href = '/wp-admin/admin.php?page=sprout-invoices&tab=payment_processors&section=' + val + '#payment_processor_settings';
			}

		},

		siWizardNext: function( val ) {
			this.saveOptions();
			this.scrollTo( 'wpbody-content' );
			this.addonAdminToggle = val;
			this.removeSumbit( val );
		},

		makeFinishTabActive: function() {
			jQuery.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: { action: 'si_wizard_finished' },

				// callback to run upon successful completion of our request
				success: () => {
					this.scrollTo( 'wpbody-content' );
					this.addonAdminToggle = 'finish';
					this.removeSumbit( 'finish' );
				},

				// callback to run if our request caused an error
				error: ( data ) => this.message = data.responseText,

				// when our request is complete (successful or not), reset the state to indicate we are no longer saving
				complete: () => this.isLoading = false,
			} );
		},

		toggleNotifications: function() {
			if ( true === this.viewNotifications ) {
				this.viewNotifications = false;
			}
			else {
				this.viewNotifications = true;
			}
		},

		makeTabActive: function( val ) {
			this.scrollTo( 'wpbody-content' );
			this.addonAdminToggle = val;
			this.removeSumbit( val );
		},

		isActiveTab: function( val ) {
			return this.addonAdminToggle === val;
		},

		removeSumbit: function( val ) {
			if( val === 'licensing' ) {
				jQuery( '.si-controls' ).hide();
			} else {
				jQuery( '.si-controls' ).show();
			}
		},

		scrollTo: function( elId ) {
			var elmnt = document.getElementById( elId );
    		elmnt.scrollIntoView();
		},

		activateLicense: function( action ) {
			var response = '',
				$license_key = jQuery('#si_license_key').val(),
				$license_message = jQuery('#license_message');

			this.isSaving = true;

			this.vm['si_license_key'] = $license_key;

			jQuery.ajax( {

					url: ajaxurl,
					method: 'POST',
					data: { action: action, license: $license_key, security: si_js_object.security },

					// set the nonce in the request header
					beforeSend: function( request ) {
						request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
					},

					// callback to run upon successful completion of our request
					success: ( data ) => {
						this.refreshProgress();
						if ( data.error ) {

							response = '<span class="inline_error_message">' + data.response + '</span>';

						}
						else { // success
							if ( 'si_deactivate_license' === action ) {
								jQuery('#deactivate_license').hide();
								jQuery('#activate_license').show();
							}
							else {
								jQuery('#activate_license').hide();
							}

							response = '<span class="inline_success_message">' + data.response + '</span>';
						}

						// display message
						jQuery('#si_html_message').html(response);
					},

					// callback to run if our request caused an error
					error: ( data ) => this.message = data.responseText,

					// when our request is complete (successful or not), reset the state to indicate we are no longer saving
					complete: () => this.isSaving = false,
				});
		},

		refreshNotificationViews: function () {
			jQuery( ".notification_content iframe" ).each(function() {
				jQuery( this ).attr('src', function () { return jQuery(this).contents().get(0).location.href });
			});
		},

		refreshProgress: function( ) {


			jQuery( "#si_progress_track" ).fadeOut();

			jQuery.ajax( {

					url: ajaxurl,
					method: 'POST',
					data: { action: 'si_progress_view' },

					// set the nonce in the request header
					beforeSend: function( request ) {
						request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
					},

					// callback to run upon successful completion of our request
					success: ( html ) => {
						jQuery( "#si_progress_track" ).replaceWith( html ).fadeIn();
					},

					// callback to run if our request caused an error
					error: ( data ) => this.message = data.responseText,

					// when our request is complete (successful or not), reset the state to indicate we are no longer saving
					complete: () => this.isLoading = false,
				});
		},

		addSettingsContent: function ( addOn ) {
			jQuery.ajax( {

				url: ajaxurl,
				method: 'POST',
				data: { action: 'render_addon_settings_content', addon: this.addons[addOn] },

				// set the nonce in the request header
				beforeSend: function( request ) {
					request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
				},

				// callback to run upon successful completion of our request
				success: ( html ) => {
					var section_div = jQuery( "#section_" + addOn + "" ).length;
					if (  section_div === 0 ) {
						jQuery( "#" + this.addons[addOn]['settingID'] + "" ).prepend( html );
						if ( jQuery( "#section_" + addOn ).length > 0 ) {
							jQuery( '.settings_' + this.addons[addOn]['settingID'] ).show();
						}
					}
				},

				// callback to run if our request caused an error
				error: ( data ) => this.message = data.responseText,

				// when our request is complete (successful or not), reset the state to indicate we are no longer saving
				complete: () => {
					this.isSaving = false;
				}
			});

		},

		// Save the Credit Card Processor to the database
		activateCCPP: function( event ) {
			var processor = event.target.value

			// Only one Credit Card Processor can be active at a time. Deactivate all other options.
			let disable_ccpp = '';
			for ( let [ key, value ] of Object.entries( this.ccPP ) ) {
				if ( event.target.name != key  ) {
					disable_ccpp += value + ', ';
					this.vm[key] = false;
				}
			}
			// deactivate if not checked
			if ( ! event.target.checked ) {
				action = { 'deactivate': processor };
			}

			// set the state so that another save cannot happen while processing
			this.isSaving = true;

			// handle checkboxes
			if ( event.target.type == 'checkbox' ) {
				// processor is the checkbox name
				processor = event.target.name;

				// unchecking sets value to 0
				if ( ! event.target.checked ) {
					processor = 'false';
				}
			}

			processor_name = document.getElementById(event.target.name).textContent;

			if ( event.target.checked ) {
				alert("Only one credit card processor can be active at a time. " + processor_name + " will be activated. " + disable_ccpp.slice(0, -2) + " will be deactivated.");
			}


			// prop the data
			this.vm[processor] = true;
			this.vm.si_cc_pp_select = processor;

			// Make a POST request to the REST API route that we registered in our PHP file
			jQuery.ajax( {

				url: SI_Settings.restURL + 'si-settings/v1/manage-pp',
				method: 'POST',
				data: { 'activate': processor, 'update_cc': true },

				// set the nonce in the request header
				beforeSend: function( request ) {
					request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
				},

				// callback to run upon successful completion of our request
				success: ( result ) => {
					this.refreshProgress();
				},

				// callback to run if our request caused an error
				error: ( data, status ) => {
					this.message = data.responseText;
					if ( 404 === data.status ) {
						alert( 'The JSON API was not found. 404: ' + SI_Settings.restURL );
						return false;
					}
					if ( 500 === data.status ) {
						alert( 'The JSON API is not reachable. 500: ' + SI_Settings.restURL );
						return false;
					}
				},

				// when our request is complete (successful or not), reset the state to indicate we are no longer saving
				complete: () => this.isSaving = false,
			});

		}, // end: saveOptions

		// Save the Additonal Payment Processor options to the database
		activatePP: function( event ) {

			var processor = event.target.name,
				action = { 'activate': processor };

			// deactivate if not checked
			if ( ! event.target.checked ) {
				action = { 'deactivate': processor };
			}

			// set the state so that another save cannot happen while processing
			this.isSaving = true;

			// Make a POST request to the REST API route that we registered in our PHP file
			jQuery.ajax( {

				url: SI_Settings.restURL + 'si-settings/v1/manage-pp',
				method: 'POST',
				data: action,

				// set the nonce in the request header
				beforeSend: function( request ) {
					request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
				},

				// callback to run upon successful completion of our request
				success: () => {
					this.refreshProgress();
				},

				// callback to run if our request caused an error
				error: ( data, status ) => {
					this.message = data.responseText;
					if ( 404 === data.status ) {
						alert( 'The JSON API was not found. 404: ' + SI_Settings.restURL );
						return false;
					}
					if ( 500 === data.status ) {
						alert( 'The JSON API is not reachable. 500: ' + SI_Settings.restURL );
						return false;
					}
				},

				// when our request is complete (successful or not), reset the state to indicate we are no longer saving
				complete: () => this.isSaving = false,
			});

		}, // end: saveOptions

		/**
		 * Activates the addon selected.
		 *
		 * @param {String} addOn The addon to be activated.
		 * @param {PointerEvent} event The properties of the event.
		 *
		 */
		activateAddOn: function( addOn, event ) {

			var addOnEl = jQuery(event.target),
				action = { 'activate': addOn };
				this.addons[addOn]['active'] = 1;

			// If addon is active, show settings button.
			if ( false !== this.addons[addOn]['settings'] ) {
				jQuery( '.settings_' + this.addons[addOn]['settingID'] ).show();
			}

			// Set the state so that another save cannot happen while processing
			jQuery( '.si-action-addon-' + addOn ).text( 'Enabling...' );
			this.isSaving = true;

			// Make a POST request to the REST API route that we registered in our PHP file
			jQuery.ajax( {

				url: SI_Settings.restURL + 'si-settings/v1/manage-addon',
				method: 'POST',
				data: action,

				// Set the nonce in the request header
				beforeSend: function( request ) {
					request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
				},

				// Callback to run upon successful completion of our request
				success: () => {
					if ( window.location.href.includes( 'page=sprout-invoices' ) ) {
						window.location.reload();
					}
					if ( action.activate ) {
						this.addSettingsContent( addOn );
					}
				},

				// Callback to run if our request caused an error
				error: ( data, status ) => {
					this.message = data.responseText;
					if ( 404 === data.status ) {
						alert( 'The JSON API was not found. 404: ' + SI_Settings.restURL );
						return false;
					}
					if ( 500 === data.status ) {
						alert( 'The JSON API is not reachable. 500: ' + SI_Settings.restURL );
						return false;
					}
				},

				// When our request is complete (successful or not), reset the state to indicate we are no longer saving
				complete: (data) => {
					this.isSaving = false;
				}
			});

		}, // end: saveOptions

		/**
		 * Deactivates the addon selected.
		 *
		 * @param {String} addOn The addon to be deactivated.
		 * @param {PointerEvent} event The properties of the event.
		 *
		 */
		deactivateAddOn: function( addOn, event ) {

			var addOnEl = jQuery(event.target),
				action = { 'deactivate': addOn };
				this.addons[addOn]['active'] = 0;

			if ( false !== this.addons[addOn]['settings'] ) {
				jQuery( '.settings_' + this.addons[addOn]['settingID'] ).hide();
			}

			// Set the state so that another save cannot happen while processing
			jQuery( '.si-action-addon-' + addOn ).text( 'Disabling...' );
			this.isSaving = true;

			// Make a POST request to the REST API route that we registered in our PHP file
			jQuery.ajax( {

				url: SI_Settings.restURL + 'si-settings/v1/manage-addon',
				method: 'POST',
				data: action,

				// Set the nonce in the request header
				beforeSend: function( request ) {
					request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
				},

				// Callback to run upon successful completion of our request
				success: () => {
					if ( window.location.href.includes( 'page=sprout-invoices' ) ) {
						window.location.reload();
					}
					if ( action.activate ) {
						this.addSettingsContent( addOn );
					}
				},

				// Callback to run if our request caused an error
				error: ( data, status ) => {
					this.message = data.responseText;
					if ( 404 === data.status ) {
						alert( 'The JSON API was not found. 404: ' + SI_Settings.restURL );
						return false;
					}
					if ( 500 === data.status ) {
						alert( 'The JSON API is not reachable. 500: ' + SI_Settings.restURL );
						return false;
					}
				},

				// When our request is complete (successful or not), reset the state to indicate we are no longer saving
				complete: (data) => {
					this.isSaving = false;
				}
			});

		}, // end: saveOptions

		// Update notification templates to use HTML templates
		loadHTMLTemplates: function() {
			// set the state so that another save cannot happen while processing
			this.isLoading = true;


			if( confirm( 'Are you sure? This will delete any customized notifications and replace them with the default HTML templates.' ) ) {

				jQuery.ajax( {

					url: ajaxurl,
					method: 'POST',
					data: { action: 'si_load_html_templates' },

					// set the nonce in the request header
					beforeSend: function( request ) {
						request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
					},

					// callback to run upon successful completion of our request
					success: () => {
						location.reload();
					},

					// callback to run if our request caused an error
					error: ( data ) => this.message = data.responseText,

					// when our request is complete (successful or not), reset the state to indicate we are no longer saving
					complete: () => this.isLoading = false,
				});
			}

		},

		// Reset Notification Templates to defaults.
		resetNotificationTemplates: function() {
			// set the state so that another save cannot happen while processing
			this.isLoading = true;


			if( confirm( 'Are you sure? This will reset all notifications to their default values.' ) ) {

				jQuery.ajax( {

					url: ajaxurl,
					method: 'POST',
					data: { action: 'reset_notificaitons' },

					// set the nonce in the request header
					beforeSend: function( request ) {
						request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
					},

					// callback to run upon successful completion of our request
					success: () => {
						location.reload();
					},

					// callback to run if our request caused an error
					error: ( data ) => this.message = data.responseText,

					// when our request is complete (successful or not), reset the state to indicate we are no longer saving
					complete: () => this.isLoading = false,
				});
			}

		},

		// Save the options to the database
		saveOptions: function() {

			// set the state so that another save cannot happen while processing
			this.isSaving = true;

			// Make a POST request to the REST API route that we registered in our PHP file
			jQuery.ajax( {

				url: SI_Settings.restURL + 'si-settings/v1/save',
				method: 'POST',
				data: this.vm,

				// set the nonce in the request header
				beforeSend: function( request ) {
					request.setRequestHeader( 'X-WP-Nonce', SI_Settings.nonce );
				},

				// callback to run upon successful completion of our request
				success: () => {
					this.refreshProgress();
					this.refreshNotificationViews();
					this.message = 'Options saved';
					setTimeout( () => this.message = '', 1000 );
				},

				// callback to run if our request caused an error
				error: ( data, status ) => {
					if ( 404 === data.status ) {
						alert( 'The JSON API was not found. 404: ' + SI_Settings.restURL );
						return false;
					}
					if ( 500 === data.status ) {
						alert( 'The JSON API is not reachable. 500: ' + SI_Settings.restURL );
						return false;
					}
					if ( 401 === data.status ) {
						alert( data.responseText );
						return false;
					}
				},

				// when our request is complete (successful or not), reset the state to indicate we are no longer saving
				complete: () => this.isSaving = false,
			});

		}, // end: saveOptions

	}, // end: methods

}); // end: Vue()


;( function( $, window, document, undefined )
{
	$("#destroy_everything").on('click', function(event) {
		if( confirm( si_js_object.destroy_confirm ) ) {
			si_destroy_everything( 0 );
		}
	});

	$("#si-reset-notifications").on('click', function(event) {
		alert( "This will reset all notifications to their default values." );
	});

	function si_destroy_everything ( count ) {
		var $button = $("#destroy_everything");
		if ( count > 50 ) { // some sanity in case things get outta hand.
			return;
		}
		$button.after(si_js_object.inline_spinner);
		$.post( ajaxurl, { action: si_js_object.destroy_action, nonce: si_js_object.destroy_nonce },
			function( response ) {
				if ( response.error ) {
					$button.after( response.message );
				}
				else {
					$('.spinner').hide();
					$button.after( response.data.message );
					if ( response.data.runagain !== false ) {
						count++;
						si_destroy_everything( count );
						return;
					}
				}
			}
		);
	}

	$("#si-gtag-option").on('click', function(event) {
		$.post( ajaxurl, { action: 'si_gtag_option_action', data: { 'gtag_option': 'true' }, nonce: si_js_object.security },
			function( response ) {
				if ( response.error ) {
					alert( response.message );
				} else {
					$("div.si-gtag-notice").hide();
					window.location.reload();
				}
			}
		);
	});
	$("div.si-gtag-notice, button.notice-dismiss").on('click', function(event) {
		$.post( ajaxurl, { action: 'si_gtag_option_action', data: { 'gtag_option': 'false' }, nonce: si_js_object.security },
			function( response ) {
				if ( response.error ) {
					alert( response.message );
				}
			}
		);
	});

	/**
	 * Stripe Notice for removal. Will be removed in 2023.
	 * @todo remove in 2023
	 */
	$( "div.si-stripe-notice, button.notice-dismiss" ).on( 'click', function( event ) {
		$.post( ajaxurl, { action: 'si_stripe_option_action', data: { 'si_stripe_option': 'false' }, nonce: si_js_object.security },
			function( response ) {
				if ( response.error ) {
					alert( response.message );
				}
			}
		);
	});

})( jQuery, window, document );

/**
 * jQuery for file input
 */
;( function( $, window, document, undefined )
{
	$( '.si_input_file' ).each( function()
	{
		var $input	 = $( this ),
			$label	 = $input.next( 'label' ),
			labelVal = $label.html();

		$input.on( 'change', function( e )
		{
			var fileName = '';

			if( this.files && this.files.length > 1 )
				fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
			else if( e.target.value )
				fileName = e.target.value.split( '\\' ).pop();

			if( fileName )
				$label.find( 'span' ).html( fileName );
			else
				$label.html( labelVal );
		});

		// Firefox bug fix
		$input
		.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
		.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
	});
})( jQuery, window, document );
