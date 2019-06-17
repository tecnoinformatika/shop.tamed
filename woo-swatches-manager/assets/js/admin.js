var frame,
	jcsmw = jcsmw || {};

jQuery( document ).ready( function ( $ ) {
	'use strict';
	var wp = window.wp,
		$body = $( 'body' );

	$( '#term-color' ).wpColorPicker();

	// Update attribute image
	$body.on( 'click', '.jcsmw-upload-image-button', function ( event ) {
		event.preventDefault();

		var $button = $( this );

		// If the media frame already exists, reopen it.
		if ( frame ) {
			frame.open();
			return;
		}

		// Create the media frame.
		frame = wp.media.frames.downloadable_file = wp.media( {
			title   : jcsmw.i18n.mediaTitle,
			button  : {
				text: jcsmw.i18n.mediaButton
			},
			multiple: false
		} );

		// When an image is selected, run a callback.
		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			$button.siblings( 'input.jcsmw-term-image' ).val( attachment.id );
			$button.siblings( '.jcsmw-remove-image-button' ).show();
			$button.parent().prev( '.jcsmw-term-image-thumbnail' ).find( 'img' ).attr( 'src', attachment.sizes.thumbnail.url );
		} );

		// Finally, open the modal.
		frame.open();

	} ).on( 'click', '.jcsmw-remove-image-button', function () {
		var $button = $( this );

		$button.siblings( 'input.jcsmw-term-image' ).val( '' );
		$button.siblings( '.jcsmw-remove-image-button' ).show();
		$button.parent().prev( '.jcsmw-term-image-thumbnail' ).find( 'img' ).attr( 'src', jcsmw.placeholder );

		return false;
	} );

	// Toggle add new attribute term modal
	var $modal = $( '#jcsmw-modal-container' ),
		$spinner = $modal.find( '.spinner' ),
		$msg = $modal.find( '.message' ),
		$metabox = null;

	$body.on( 'click', '.jcsmw_add_new_attribute', function ( e ) {
		e.preventDefault();
		var $button = $( this ),
			taxInputTemplate = wp.template( 'jcsmw-input-tax' ),
			data = {
				type: $button.data( 'type' ),
				tax : $button.closest( '.woocommerce_attribute' ).data( 'taxonomy' )
			};

		// Insert input
		$modal.find( '.jcsmw-term-swatch' ).html( $( '#tmpl-jcsmw-input-' + data.type ).html() );
		$modal.find( '.jcsmw-term-tax' ).html( taxInputTemplate( data ) );

		if ( 'color' == data.type ) {
			$modal.find( 'input.jcsmw-input-color' ).wpColorPicker();
		}

		$metabox = $button.closest( '.woocommerce_attribute.wc-metabox' );
		$modal.show();
	} ).on( 'click', '.jcsmw-modal-close, .jcsmw-modal-backdrop', function ( e ) {
		e.preventDefault();

		closeModal();
	} );

	// Send ajax request to add new attribute term
	$body.on( 'click', '.jcsmw-new-attribute-submit', function ( e ) {
		e.preventDefault();

		var $button = $( this ),
			type = $button.data( 'type' ),
			error = false,
			data = {};

		// Validate
		$modal.find( '.jcsmw-input' ).each( function () {
			var $this = $( this );

			if ( $this.attr( 'name' ) != 'slug' && !$this.val() ) {
				$this.addClass( 'error' );
				error = true;
			} else {
				$this.removeClass( 'error' );
			}

			data[$this.attr( 'name' )] = $this.val();
		} );

		if ( error ) {
			return;
		}

		// Send ajax request
		$spinner.addClass( 'is-active' );
		$msg.hide();
		wp.ajax.send( 'jcsmw_add_new_attribute', {
			data   : data,
			error  : function ( res ) {
				$spinner.removeClass( 'is-active' );
				$msg.addClass( 'error' ).text( res ).show();
			},
			success: function ( res ) {
				$spinner.removeClass( 'is-active' );
				$msg.addClass( 'success' ).text( res.msg ).show();

				$metabox.find( 'select.attribute_values' ).append( '<option value="' + res.id + '" selected="selected">' + res.name + '</option>' );
				$metabox.find( 'select.attribute_values' ).change();

				closeModal();
			}
		} );
	} );

	/**
	 * Close modal
	 */
	function closeModal() {
		$modal.find( '.jcsmw-term-name input, .jcsmw-term-slug input' ).val( '' );
		$spinner.removeClass( 'is-active' );
		$msg.removeClass( 'error success' ).hide();
		$modal.hide();
	}
} );

