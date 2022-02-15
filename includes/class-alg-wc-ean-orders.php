<?php
/**
 * EAN for WooCommerce - Orders Class
 *
 * @version 3.2.0
 * @since   2.1.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_EAN_Orders' ) ) :

class Alg_WC_EAN_Orders {

	/**
	 * Constructor.
	 *
	 * @version 2.9.0
	 * @since   2.1.0
	 *
	 * @todo    [next] (feature) option to add it to `woocommerce_hidden_order_itemmeta`
	 */
	function __construct() {
		// Orders
		if ( 'yes' === get_option( 'alg_wc_ean_order_items_meta', 'no' ) ) {
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_ean_to_order_items_meta' ), PHP_INT_MAX, 1 );
		}
		// Admin new order (AJAX)
		if ( 'yes' === get_option( 'alg_wc_ean_order_items_meta_admin', 'no' ) ) {
			add_action( 'woocommerce_new_order_item', array( $this, 'new_order_item_ajax' ), PHP_INT_MAX, 2 );
		}
		// REST API
		if ( 'yes' === get_option( 'alg_wc_ean_order_items_meta_rest', 'no' ) ) {
			add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'rest_order_add_ean' ), PHP_INT_MAX, 3 );
		}
	}

	/**
	 * rest_order_add_ean.
	 *
	 * @version 3.2.0
	 * @since   2.8.0
	 */
	function rest_order_add_ean( $response, $order, $request ) {
		foreach ( $response->data['line_items'] as $item_key => &$item ) {
			$is_in_meta = false;
			if ( ! empty( $item['meta_data'] ) ) {
				foreach ( $item['meta_data'] as $meta_data ) {
					if ( isset( $meta_data['key'], $meta_data['value'] ) && alg_wc_ean()->core->ean_key === $meta_data['key'] ) {
						$item['ean'] = $meta_data['value'];
						$is_in_meta = true;
						break;
					}
				}
			}
			if ( ! $is_in_meta && isset( $item['product_id'] ) ) {
				$product_id = ( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] );
				if ( '' !== ( $ean = alg_wc_ean()->core->get_ean( $product_id ) ) ) {
					$item['ean'] = $ean;
				}
			}
		}
		return $response;
	}

	/**
	 * new_order_item_ajax.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    [next] (fix) EAN meta is not displayed until order page is reloaded
	 */
	function new_order_item_ajax( $item_id, $item ) {
		if (
			defined( 'DOING_AJAX' ) && DOING_AJAX &&
			'WC_Order_Item_Product' === get_class( $item ) &&
			'' === wc_get_order_item_meta( $item_id, alg_wc_ean()->core->ean_key ) &&
			( $product_id = ( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] ) ) &&
			'' !== ( $ean = alg_wc_ean()->core->get_ean( $product_id, true ) )
		) {
			wc_update_order_item_meta( $item_id, alg_wc_ean()->core->ean_key, $ean );
		}
	}

	/**
	 * add_ean_to_order_items_meta.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    [maybe] (feature) editable field?
	 * @todo    [maybe] (dev) `( $do_overwrite || '' === wc_get_order_item_meta( $item_id, alg_wc_ean()->core->ean_key, true )`
	 */
	function add_ean_to_order_items_meta( $order_id ) {
		$count = 0;
		$order = wc_get_order( $order_id );
		if ( $order ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				if (
					0 != ( $product_id = ( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] ) ) &&
					'' !== ( $ean = alg_wc_ean()->core->get_ean( $product_id, true ) )
				) {
					wc_update_order_item_meta( $item_id, alg_wc_ean()->core->ean_key, $ean );
					$count++;
				}
			}
		}
		return $count;
	}

}

endif;

return new Alg_WC_EAN_Orders();
