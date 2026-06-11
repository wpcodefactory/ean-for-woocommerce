/**
 * EAN for WooCommerce - Generate Button JS
 *
 * @version 5.5.5
 * @since   5.5.5
 *
 * @author  Algoritmika Ltd
 *
 * @todo    (dev) recheck `return false;`
 * @todo    (dev) recheck `ajaxurl`
 */

jQuery( document ).ready( function () {
	jQuery( 'body' ).on( 'click', '.alg_wc_ean_generate_ajax', function () {
		var product = jQuery( this ).data( 'product' )
		var input   = jQuery( this ).data( 'input' );
		jQuery( '#spinner-' + input ).addClass( 'is-active' );
		var data = {
			'action':  'alg_wc_ean_generate_ajax',
			'product': product,
			'input':   input,
		};
		jQuery.post( ajaxurl, data, function( response ) {
			if ( response ) {
				jQuery( '#' + data['input'] ).val( response ).trigger( 'change' );
			}
			jQuery( '#spinner-' + data['input'] ).removeClass( 'is-active' );
		} );
		return false;
	} );
} );
