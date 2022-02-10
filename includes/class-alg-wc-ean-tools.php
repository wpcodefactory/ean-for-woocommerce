<?php
/**
 * EAN for WooCommerce - Tools Class
 *
 * @version 3.1.0
 * @since   2.1.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Tools' ) ) :

class Alg_WC_EAN_Tools {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   2.1.0
	 *
	 * @todo    [now] [!] (dev) Export/Import Settings: move to a separate class/file?
	 * @todo    [maybe] (feature) Automatic actions: `updated_postmeta`?
	 * @todo    [maybe] (dev) Automatic actions: `woocommerce_after_product_object_save`?
	 */
	function __construct() {
		// Export/Import Settings
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'import_settings' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'export_settings' ) );
		// Products Tools
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'products_delete' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'products_create' ) );
		// Automatic actions
		add_action( 'wp_insert_post', array( $this, 'product_on_insert_post' ), PHP_INT_MAX, 3 );
		// Periodic action
		if ( '' !== get_option( 'alg_wc_ean_products_periodic_action', '' ) ) {
			add_action( 'init', array( $this, 'schedule_products_periodic_action' ) );
			add_action( 'alg_wc_ean_products_periodic_action', array( $this, 'process_products_periodic_action' ) );
		} else {
			add_action( 'init', array( $this, 'unschedule_products_periodic_action' ) );
		}
		// "Products > Bulk actions"
		add_filter( 'bulk_actions-edit-product', array( $this, 'add_product_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-product', array( $this, 'handle_product_bulk_actions' ), 10, 3 );
		// Orders Tools
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'orders_add' ) );
		add_action( 'alg_wc_ean_settings_saved', array( $this, 'orders_delete' ) );
	}

	/**
	 * import_settings.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 *
	 * @todo    [now] [!] (dev) validate data?
	 */
	function import_settings() {
		if ( ! empty( $_FILES['alg_wc_ean_import_settings']['tmp_name'] ) ) {
			$content = file_get_contents( $_FILES['alg_wc_ean_import_settings']['tmp_name'] );
			$content = json_decode( $content, true );
			$counter = 0;
			foreach ( $content as $row ) {
				if ( 'alg_wc_ean_version' !== $row['option_name'] ) {
					if ( update_option( $row['option_name'], $row['option_value'] ) ) {
						$counter++;
					}
				}
			}
			if ( is_callable( array( 'WC_Admin_Settings', 'add_message' ) ) ) {
				WC_Admin_Settings::add_message( sprintf( __( 'Settings imported (%d option(s) updated).', 'ean-for-woocommerce' ), $counter ) );
			}
		}
	}

	/**
	 * export_settings.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 *
	 * @todo    [now] [!] (dev) remove `length`?
	 * @todo    [now] [!] (dev) recheck headers?
	 * @todo    [next] [!] (dev) redirect page?
	 */
	function export_settings() {
		if ( 'yes' === get_option( 'alg_wc_ean_export_settings', 'no' ) ) {
			update_option( 'alg_wc_ean_export_settings', 'no' );
			global $wpdb;
			$content = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'alg_wc_ean%'" );
			foreach ( $content as &$row ) {
				$row->option_value = maybe_unserialize( $row->option_value );
			}
			$content = json_encode( $content );
			$length  = strlen( $content );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename=alg-wc-ean-settings.txt' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Content-Length: ' . $length );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Expires: 0' );
			header( 'Pragma: public' );
			echo $content;
			exit;
		}
	}

	/**
	 * process_products_periodic_action.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 *
	 * @todo    [now] [!] (dev) log
	 */
	function process_products_periodic_action( $args ) {
		if ( '' !== ( $action = get_option( 'alg_wc_ean_products_periodic_action', '' ) ) ) {
			$this->process_action_for_all_products( $action );
		}
	}

	/**
	 * unschedule_products_periodic_action.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 */
	function unschedule_products_periodic_action() {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( 'alg_wc_ean_products_periodic_action' );
		}
	}

	/**
	 * schedule_products_periodic_action.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 */
	function schedule_products_periodic_action() {
		if (
			function_exists( 'as_has_scheduled_action' ) &&
			( $interval_in_seconds = get_option( 'alg_wc_ean_products_periodic_action_interval', 3600 ) ) &&
			false === as_has_scheduled_action( 'alg_wc_ean_products_periodic_action', array( $interval_in_seconds ) )
		) {
			as_unschedule_all_actions( 'alg_wc_ean_products_periodic_action' );
			as_schedule_recurring_action( time(), $interval_in_seconds, 'alg_wc_ean_products_periodic_action', array( $interval_in_seconds ) );
		}
	}

	/**
	 * handle_product_bulk_actions.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 *
	 * @todo    [now] [!] (feature) all other actions, e.g. "Copy EAN from SKU", etc.
	 * @todo    [now] (dev) notices
	 * @todo    [now] [!] (dev) merge with `products_create()`?
	 */
	function handle_product_bulk_actions( $redirect_to, $action, $post_ids ) {
		if ( in_array( $action, array( 'alg_wc_ean_generate', 'alg_wc_ean_delete' ) ) ) {
			$data  = ( 'alg_wc_ean_generate' === $action ? $this->get_generate_data() : false );
			$count = 0;
			foreach ( $post_ids as $post_id ) {
				$variations  = array_keys( get_children( array( 'post_parent' => $post_id, 'posts_per_page' => -1, 'post_type' => 'product_variation' ), 'ARRAY_N' ) );
				$product_ids = array_merge( array( $post_id ), $variations );
				foreach ( $product_ids as $product_id ) {
					switch ( $action ) {
						case 'alg_wc_ean_generate':
							$result = (
									'' === get_post_meta( $product_id, alg_wc_ean()->core->ean_key, true ) &&
									'' !== ( $ean = $this->generate_ean( $product_id, $data ) ) &&
									update_post_meta( $product_id, alg_wc_ean()->core->ean_key, $ean )
								);
							break;
						case 'alg_wc_ean_delete':
							$result = delete_post_meta( $product_id, alg_wc_ean()->core->ean_key );
							break;
					}
					if ( $result ) {
						$count++;
					}
				}
			}
		}
		return $redirect_to;
	}

	/**
	 * add_product_bulk_actions.
	 *
	 * @version 2.9.0
	 * @since   2.7.0
	 */
	function add_product_bulk_actions( $actions ) {
		return array_merge( $actions, array_intersect_key( array(
				'alg_wc_ean_generate' => __( 'Generate EAN', 'ean-for-woocommerce' ),
				'alg_wc_ean_delete'   => __( 'Delete EAN', 'ean-for-woocommerce' ),
			), array_flip( get_option( 'alg_wc_ean_product_bulk_actions', array( 'alg_wc_ean_delete', 'alg_wc_ean_generate' ) ) ) ) );
	}

	/**
	 * get_orders.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query
	 */
	function get_orders() {
		return wc_get_orders( array( 'limit' => -1, 'return' => 'ids' ) );
	}

	/**
	 * orders_delete.
	 *
	 * @version 2.9.0
	 * @since   2.1.0
	 */
	function orders_delete() {
		if ( 'yes' === get_option( 'alg_wc_ean_tool_orders_delete', 'no' ) ) {
			update_option( 'alg_wc_ean_tool_orders_delete', 'no' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$count = 0;
				foreach ( $this->get_orders() as $order_id ) {
					$order = wc_get_order( $order_id );
					if ( ! $order ) {
						continue;
					}
					foreach ( $order->get_items() as $item_id => $item ) {
						if ( wc_delete_order_item_meta( $item_id, alg_wc_ean()->core->ean_key ) ) {
							$count++;
						}
					}
				}
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'EAN deleted for %s order items.', 'ean-for-woocommerce' ), $count ) );
				}
			}
		}
	}

	/**
	 * orders_add.
	 *
	 * @version 2.9.0
	 * @since   2.1.0
	 */
	function orders_add() {
		if ( 'yes' === get_option( 'alg_wc_ean_tool_orders_add', 'no' ) ) {
			update_option( 'alg_wc_ean_tool_orders_add', 'no' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$count = 0;
				foreach ( $this->get_orders() as $order_id ) {
					$count += alg_wc_ean()->core->orders->add_ean_to_order_items_meta( $order_id );
				}
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'EAN added for %s order items.', 'ean-for-woocommerce' ), $count ) );
				}
			}
		}
	}

	/**
	 * get_products.
	 *
	 * @version 3.0.0
	 * @since   2.1.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/wc_get_products-and-WC_Product_Query
	 *
	 * @todo    [now] [!] (dev) `meta_query`: `ean` not empty
	 * @todo    [now] [!] (feature) optional: `meta_query`: `ean` not empty
	 */
	function get_products( $action = false ) {
		// Query args
		$args = array( 'limit' => -1, 'return' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'type' => array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ) );
		// Product options
		$product_options = false;
		switch ( $action ) {
			case 'assign_list':
				$product_options = get_option( 'alg_wc_ean_tool_product_assign_list_settings', array() );
				break;
		}
		if ( $product_options ) {
			foreach ( array( 'product_cat' => 'category' ) as $taxonomy => $arg ) {
				$terms = ( isset( $product_options[ $taxonomy ] ) ? $product_options[ $taxonomy ] : array() );
				if ( ! empty( $terms ) ) {
					$args[ $arg ] = array_map( array( $this, "get_{$taxonomy}_slug" ), $terms );
				}
			}
		}
		// Query
		return wc_get_products( $args );
	}

	/**
	 * get_product_cat_slug.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function get_product_cat_slug( $term_id ) {
		$term = get_term( $term_id, 'product_cat' );
		return ( $term && ! is_wp_error( $term ) ? $term->slug : '' );
	}

	/**
	 * products_delete.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    [next] (dev) delete directly with SQL from the `meta` table
	 * @todo    [maybe] (dev) better notice(s)?
	 */
	function products_delete() {
		if ( 'yes' === get_option( 'alg_wc_ean_tool_delete_product_meta', 'no' ) ) {
			update_option( 'alg_wc_ean_tool_delete_product_meta', 'no' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$count = 0;
				foreach ( $this->get_products() as $product_id ) {
					if ( delete_post_meta( $product_id, alg_wc_ean()->core->ean_key ) ) {
						$count++;
					}
				}
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( sprintf( __( 'EAN deleted for %s products.', 'ean-for-woocommerce' ), $count ) );
				}
			}
		}
	}

	/**
	 * is_valid_product.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function is_valid_product( $post_id, $action ) {
		$product_options = false;
		switch ( $action ) {
			case 'assign_list':
				$product_options = get_option( 'alg_wc_ean_tool_product_assign_list_settings', array() );
				break;
		}
		if ( $product_options ) {
			foreach ( array( 'product_cat' => 'category' ) as $taxonomy => $arg ) {
				$terms = ( isset( $product_options[ $taxonomy ] ) ? $product_options[ $taxonomy ] : array() );
				if ( ! empty( $terms ) && ! has_term( $terms, $taxonomy, $post_id ) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * product_on_insert_post.
	 *
	 * @version 3.0.0
	 * @since   2.2.8
	 *
	 * @todo    [now] [!] (dev) merge with `products_create()`?
	 * @todo    [now] [!] (dev) stop on first `update_post_meta`?
	 * @todo    [next] (feature) `product_variation`: make it optional?
	 */
	function product_on_insert_post( $post_id, $post, $update ) {
		$action = get_option( ( $update ? 'alg_wc_ean_tool_product_action_on_update' : 'alg_wc_ean_tool_product_action_on_new' ), '' );
		if (
			! empty( $action ) &&
			in_array( $post->post_type, array( 'product', 'product_variation' ) ) &&
			'' === get_post_meta( $post_id, alg_wc_ean()->core->ean_key, true ) &&
			$this->is_valid_product( $post_id, $action )
		) {
			$ean = '';
			switch ( $action ) {
				case 'generate':
					$ean = $this->generate_ean( $post_id, $this->get_generate_data() );
					break;
				case 'copy_sku':
					$ean = ( ( $product = wc_get_product( $post_id ) ) ? $product->get_sku() : get_post_meta( $post_id, '_sku', true ) );
					break;
				case 'copy_id':
					$ean = $post_id;
					break;
				case 'copy_meta':
					$data = array_replace( array( 'key' => '' ), get_option( 'alg_wc_ean_tool_product_copy_meta', array() ) );
					if ( '' !== $data['key'] ) {
						$ean = get_post_meta( $post_id, $data['key'], true );
					}
					break;
				case 'assign_list':
					$data = get_option( 'alg_wc_ean_tool_product_assign_list', '' );
					if ( '' !== $data ) {
						$data = array_map( 'trim', explode( PHP_EOL, $data ) );
						$ean  = array_shift( $data );
						update_option( 'alg_wc_ean_tool_product_assign_list', ( empty( $data ) ? '' : implode( PHP_EOL, $data ) ) );
					}
					break;
			}
			if ( '' !== $ean ) {
				update_post_meta( $post_id, alg_wc_ean()->core->ean_key, $ean );
				if ( 'product_variation' === $post->post_type && isset( alg_wc_ean()->core->edit ) ) {
					// Prevents the new EAN from being overwritten by the variation's `input` field on the product edit page
					remove_action( 'woocommerce_save_product_variation', array( alg_wc_ean()->core->edit, 'save_ean_input_variation' ), 10 );
				}
			}
		}
	}

	/**
	 * process_action_for_all_products.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 *
	 * @todo    [now] (dev) `array_shift()` vs `array_reverse()` + `array_pop()`?
	 */
	function process_action_for_all_products( $action ) {
		// Prepare (and validate) data
		switch ( $action ) {
			case 'generate':
				$data = $this->get_generate_data();
				break;
			case 'copy_meta':
				$data = array_replace( array( 'key' => '' ), get_option( 'alg_wc_ean_tool_product_copy_meta', array() ) );
				if ( '' === $data['key'] ) {
					return array( 'result' => false, 'message' => __( 'Please set the "Meta key" option.', 'ean-for-woocommerce' ) );
				}
				break;
			case 'assign_list':
				$data = get_option( 'alg_wc_ean_tool_product_assign_list', '' );
				if ( '' === $data ) {
					return array( 'result' => false, 'message' => __( 'Please set the "List" option.', 'ean-for-woocommerce' ) );
				} else {
					$data = array_map( 'trim', explode( PHP_EOL, $data ) );
				}
				break;
		}
		// Product loop
		$count = 0;
		foreach ( $this->get_products( $action ) as $product_id ) {
			if ( '' !== get_post_meta( $product_id, alg_wc_ean()->core->ean_key, true ) ) {
				continue;
			}
			$ean = '';
			switch ( $action ) {
				case 'generate':
					$ean = $this->generate_ean( $product_id, $data );
					break;
				case 'copy_sku':
					$ean = ( ( $product = wc_get_product( $product_id ) ) ? $product->get_sku() : get_post_meta( $product_id, '_sku', true ) );
					break;
				case 'copy_id':
					$ean = $product_id;
					break;
				case 'copy_meta':
					$ean = get_post_meta( $product_id, $data['key'], true );
					break;
				case 'assign_list':
					$ean = array_shift( $data );
					break;
			}
			if ( '' !== $ean && update_post_meta( $product_id, alg_wc_ean()->core->ean_key, $ean ) ) {
				$count++;
			}
			if ( 'assign_list' === $action && empty( $data ) ) {
				break;
			}
		}
		if ( 'assign_list' === $action ) {
			update_option( 'alg_wc_ean_tool_product_assign_list', ( empty( $data ) ? '' : implode( PHP_EOL, $data ) ) );
		}
		switch ( $action ) {
			case 'generate':
				$message = __( 'EAN generated for %s products.', 'ean-for-woocommerce' );
				break;
			case 'assign_list':
				$message = __( 'EAN assigned for %s products.', 'ean-for-woocommerce' );
				break;
			default:
				$message = __( 'EAN copied for %s products.', 'ean-for-woocommerce' );
		}
		return array( 'result' => true, 'message' => sprintf( $message, $count ) );
	}

	/**
	 * products_create.
	 *
	 * @version 2.9.0
	 * @since   2.1.0
	 *
	 * @todo    [now] (dev) message: "success/error" (i.e. check `$response['result']`)
	 * @todo    [next] (feature) per individual product (JS or AJAX?)
	 * @todo    [maybe] (dev) better notice(s)?
	 */
	function products_create() {
		$tools = array_replace( array( 'generate' => 'no', 'copy_sku' => 'no', 'copy_id' => 'no', 'copy_meta' => 'no', 'assign_list' => 'no' ),
			get_option( 'alg_wc_ean_tool_product', array() ) );
		if ( in_array( 'yes', $tools ) ) {
			delete_option( 'alg_wc_ean_tool_product' );
			if ( current_user_can( 'manage_woocommerce' ) ) {
				foreach ( $tools as $tool => $is_enabled ) {
					if ( 'yes' === $is_enabled ) {
						$action = $tool;
						break;
					}
				}
				$response = $this->process_action_for_all_products( $action );
				if ( ! empty( $response['message'] ) && method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( $response['message'] );
				}
			}
		}
	}

	/**
	 * get_generate_data.
	 *
	 * @version 2.7.0
	 * @since   2.2.8
	 *
	 * @todo    [now] [!] (dev) move to a separate class/file, e.g. `class-alg-wc-ean-generator.php`?
	 */
	function get_generate_data() {
		$res = array();
		$data = array_replace( array( 'type' => 'EAN13', 'prefix' => 200, 'prefix_to' => '', 'seed_prefix' => '' ), get_option( 'alg_wc_ean_tool_product_generate', array() ) );
		// Seed length
		switch ( $data['type'] ) {
			case 'EAN8':
				$length = 8;
				break;
			case 'UPCA':
				$length = 12;
				break;
			default: // 'EAN13'
				$length = 13;
		}
		$res['seed_length'] = ( $length - 3 - 1 );
		// Seed prefix
		$seed_prefix         = ( strlen( $data['seed_prefix'] ) > $res['seed_length'] ? substr( $data['seed_prefix'], 0, $res['seed_length'] ) : $data['seed_prefix'] );
		$res['seed_length'] -= strlen( $seed_prefix );
		$res['seed_prefix']  = $seed_prefix;
		// Prefix
		if ( '' === $data['prefix'] ) {
			$data['prefix'] = 0;
		}
		$res['is_rand_prefix'] = ( '' !== $data['prefix_to'] && $data['prefix'] != $data['prefix_to'] );
		$res['prefix'] = ( $res['is_rand_prefix'] ?
			array(
				'from' => ( $data['prefix_to'] > $data['prefix'] ? $data['prefix'] : $data['prefix_to'] ),
				'to'   => ( $data['prefix_to'] > $data['prefix'] ? $data['prefix_to'] : $data['prefix'] ),
			) :
			str_pad( $data['prefix'], 3, '0', STR_PAD_LEFT )
		);
		return $res;
	}

	/**
	 * generate_ean.
	 *
	 * @version 2.7.0
	 * @since   2.2.8
	 *
	 * @todo    [next] (feature) customizable seed (i.e. not product ID), e.g. random?; etc.
	 */
	function generate_ean( $product_id, $data ) {
		$ean = ( $data['is_rand_prefix'] ? str_pad( rand( $data['prefix']['from'], $data['prefix']['to'] ), 3, '0', STR_PAD_LEFT ) : $data['prefix'] ) .
			$data['seed_prefix'] . str_pad( substr( $product_id, 0, $data['seed_length'] ), $data['seed_length'], '0', STR_PAD_LEFT );
		return $ean . $this->get_checksum( $ean );
	}

	/**
	 * get_checksum.
	 *
	 * @version 2.2.7
	 * @since   2.2.5
	 *
	 * @see     https://stackoverflow.com/questions/19890144/generate-valid-ean13-in-php
	 */
	function get_checksum( $code ) {
		$flag = true;
		$sum  = 0;
		for ( $i = strlen( $code ) - 1; $i >= 0; $i-- ) {
			$sum += (int) $code[ $i ] * ( $flag ? 3 : 1 );
			$flag = ! $flag;
		}
		return ( 10 - ( $sum % 10 ) ) % 10;
	}

}

endif;

return new Alg_WC_EAN_Tools();
