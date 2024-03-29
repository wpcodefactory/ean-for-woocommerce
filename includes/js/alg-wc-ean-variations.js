/**
 * EAN for WooCommerce - Variations
 *
 * @version 4.4.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

jQuery( document ).ready( function() {

	var variations_form = jQuery( alg_wc_ean_variations_obj.variations_form );
	if ( jQuery( 'body' ).hasClass( 'single-product' ) && variations_form.length > 0 ) { // is single variable product page
		var ean = variations_form.closest( alg_wc_ean_variations_obj.variations_form_closest ).find( '.ean' );
		if ( ean.length > 0 ) { // do ean
			var ean_reset  = ean.text();
			var ean_parent = ean.parent();
			alg_wc_ean_maybe_hide();
			alg_wc_ean_variations();
		}
	}

	/**
	 * alg_wc_ean_variations
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function alg_wc_ean_variations() {
		variations_form.on( 'found_variation', function( event, variation ) {
			if ( variation.ean ) {
				alg_wc_ean_show( variation );
			} else {
				alg_wc_ean_reset();
			}
		} );
		variations_form.on( 'reset_data', alg_wc_ean_reset );
	}

	/**
	 * alg_wc_ean_show
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function alg_wc_ean_show( variation ) {
		if ( variation.ean ) {
			ean.text( variation.ean );
			ean_parent.show();
		}
	}

	/**
	 * alg_wc_ean_reset
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function alg_wc_ean_reset() {
		if ( ean_reset !== ean.text() ) {
			ean.text( ean_reset );
		}
		alg_wc_ean_maybe_hide();
	}

	/**
	 * alg_wc_ean_maybe_hide
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [later] `do_hide`
	 */
	function alg_wc_ean_maybe_hide() {
		if ( '' == ean_reset ) {
			ean_parent.hide();
		}
	}

} );
