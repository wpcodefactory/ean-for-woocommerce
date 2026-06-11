/**
 * EAN for WooCommerce - Bulk Actions Confirmation JS
 *
 * @version 5.5.5
 * @since   5.5.5
 *
 * @author  Algoritmika Ltd
 */

jQuery( '#doaction' ).on( 'click', function () {
	if ( -1 != algWCEANBulkActionsConfirmation.confirmIDs.indexOf( jQuery( 'select[name="action"]' ).val() ) ) {
		if ( ! confirm( algWCEANBulkActionsConfirmation.message ) ) {
			return false;
		}
	}
} );
