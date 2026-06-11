/**
 * EAN for WooCommerce - Quick Edit JS
 *
 * @version 5.5.5
 * @since   5.5.5
 *
 * @author  Algoritmika Ltd
 */

jQuery(
	function ( $ ) {
		$( '#the-list' ).on(
			'click',
			'.editinline',
			function () {
				var post_id = $( this ).closest( 'tr' ).attr( 'id' );
				post_id = post_id.replace( 'post-', '' );
				var inline_data = $( '#alg_wc_ean_inline_' + post_id );
				var ean = inline_data.find( '.alg_wc_ean_quick_edit' ).text();
				$( 'input[name="_alg_ean_qb"]', '.inline-edit-row' ).val( ean );
			}
		);
	}
);
