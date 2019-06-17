"use strict";

jQuery( document ).ready( function( $ ) {
	$( '.wooscp_color_picker' ).wpColorPicker();

	$( '.wooscp-fields-item' ).arrangeable( {
		dragSelector: '.label'
	} );

	$( '.wooscp-attributes-item' ).arrangeable( {
		dragSelector: '.label'
	} );
} );