/**
 * EAN for WooCommerce - WCFM Variation Generate Button JS
 *
 * @version 5.5.5
 * @since   5.5.5
 *
 * @author  Algoritmika Ltd
 */

jQuery( document ).ready( function () {
	jQuery( '.variation_id' ).each( function () {
		var variation_id = jQuery( this ).val();
		var input_id     = jQuery( this )
			.attr( 'id' )
			.replace(
				'variations_id_',
				'variations_wcfm_' + algWCEANWCFMVariationGenerateButton.eanKey + '_'
			);
		jQuery( '#' + input_id ).after(
			'<p class="alg_wc_ean_generate_button_wrapper">' +
				'<button' +
					' type="button"' +
					' class="button alg_wc_ean_generate_ajax wcfm_ele variable variable-subscription pw-gift-card"' +
					' data-product="' + variation_id + '"' +
					' data-input="' + input_id + '"' +
				'>' +
					algWCEANWCFMVariationGenerateButton.buttonLabel +
				'</button>' +
			'</p>'
		);
	} );
} );
