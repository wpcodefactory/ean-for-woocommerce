<?php
/**
 * EAN for WooCommerce - Display Class
 *
 * @version 3.3.0
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Display' ) ) :

class Alg_WC_EAN_Display {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) remove `! is_admin()` and `is_admin()`?
	 * @todo    [next] (feature) frontend: customizable position and template for loop, cart, etc. (now implemented for "single product page" only)
	 * @todo    [later] frontend: order?
	 */
	function __construct() {
		// Frontend
		if ( ! is_admin() ) {
			// Single product page
			if ( 'yes' === get_option( 'alg_wc_ean_frontend', 'yes' ) ) {
				$positions_priorities = get_option( 'alg_wc_ean_frontend_positions_priorities', array() );
				foreach ( get_option( 'alg_wc_ean_frontend_positions', array( 'woocommerce_product_meta_start' ) ) as $position ) {
					add_action( $position, array( $this, 'add_ean_single' ), ( isset( $positions_priorities[ $position ] ) ? $positions_priorities[ $position ] : 10 ) );
				}
				// Variations
				add_action( 'wp_enqueue_scripts',              array( $this, 'variations_enqueue_scripts' ) );
				add_filter( 'woocommerce_available_variation', array( $this, 'variations_add_params' ), 10, 3 );
			}
			// Loop
			if ( 'yes' === get_option( 'alg_wc_ean_frontend_loop', 'no' ) ) {
				add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'add_ean_loop' ) );
			}
			// Cart
			if ( 'yes' === get_option( 'alg_wc_ean_frontend_cart', 'no' ) ) {
				add_action( 'woocommerce_after_cart_item_name', array( $this, 'add_ean_cart' ) );
			}
			// Product structured data
			if ( 'yes' === get_option( 'alg_wc_ean_frontend_product_structured_data', 'yes' ) ) {
				add_filter( 'woocommerce_structured_data_product', array( $this, 'add_ean_to_product_structured_data' ), 10, 2 );
			}
			// Shortcodes
			add_shortcode( 'alg_wc_ean', array( $this, 'ean_shortcode' ) );
		}
		// Backend
		if ( is_admin() ) {
			// Admin products list column
			if ( 'yes' === get_option( 'alg_wc_ean_backend_column', 'yes' ) ) {
				add_filter( 'manage_edit-product_columns',          array( $this, 'add_product_columns' ) );
				add_action( 'manage_product_posts_custom_column',   array( $this, 'render_product_columns' ), 10, 2 );
				add_filter( 'manage_edit-product_sortable_columns', array( $this, 'product_sortable_columns' ) );
				add_action( 'pre_get_posts',                        array( $this, 'product_columns_order_by_column' ) );
				add_action( 'admin_head',                           array( $this, 'product_columns_style' ) );
			}
		}
		// Order Items Table
		if (
			'yes' === get_option( 'alg_wc_ean_order_items_table', 'no' ) ||
			'yes' === get_option( 'alg_wc_ean_order_items_table_emails', get_option( 'alg_wc_ean_order_items_table', 'no' ) )
		) {
			add_action( 'woocommerce_order_item_meta_end',      array( $this, 'add_to_order_item_meta_ean' ), 10, 4 );
			add_action( 'woocommerce_email_before_order_table', array( $this, 'save_email_data' ), 10, 4 );
			add_action( 'woocommerce_email_after_order_table',  array( $this, 'reset_email_data' ), 10, 0 );
		}
		// REST API
		if ( 'yes' === get_option( 'alg_wc_ean_product_rest', 'no' ) ) {
			add_filter( 'woocommerce_rest_prepare_product_object', array( $this, 'rest_product_add_ean' ), PHP_INT_MAX, 3 );
		}
	}

	/**
	 * product_columns_style.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [now] (dev) make this optional? (same for barcodes)
	 * @todo    [now] (dev) load only on `edit.php?post_type=product` etc.? (same for barcodes)
	 */
	function product_columns_style() {
		?><style>
		.column-ean {
			width: 10%;
		}
		</style><?php
	}

	/**
	 * rest_product_add_ean.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 */
	function rest_product_add_ean( $response, $product, $request ) {
		$response->data['ean'] = alg_wc_ean()->core->get_ean( $product->get_id() );
		return $response;
	}

	/**
	 * product_sortable_columns.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function product_sortable_columns( $columns ) {
		$columns['ean'] = 'alg_ean';
		return $columns;
	}

	/**
	 * product_columns_order_by_column.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 *
	 * @todo    [maybe] `$do_exclude_empty_lines`?
	 */
	function product_columns_order_by_column( $query ) {
		if (
			$query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) && 'alg_ean' === $orderby &&
			isset( $query->query['post_type'] ) && 'product' === $query->query['post_type'] &&
			isset( $query->is_admin ) && 1 == $query->is_admin
		) {
			$do_exclude_empty_lines = false;
			$key = alg_wc_ean()->core->ean_key;
			if ( $do_exclude_empty_lines ) {
				$query->set( 'meta_key', $key );
			} else {
				$query->set( 'meta_query', array(
					'relation' => 'OR',
					array(
						'key'     => $key,
						'compare' => 'NOT EXISTS'
					),
					array(
						'key'     => $key,
						'compare' => 'EXISTS'
					),
				) );
			}
			$query->set( 'orderby', 'meta_value ID' );
		}
	}

	/**
	 * add_product_columns.
	 *
	 * @version 2.2.7
	 * @since   1.0.0
	 *
	 * @todo    [maybe] `__( 'EAN', 'ean-for-woocommerce' )` -> `'EAN'` (everywhere) (i.e. no translation)?
	 */
	function add_product_columns( $columns ) {
		$is_added = false;
		$_columns = array();
		foreach ( $columns as $column_key => $column_title ) {
			$_columns[ $column_key ] = $column_title;
			if ( 'sku' === $column_key ) {
				$_columns['ean'] = get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) );
				$is_added = true;
			}
		}
		if ( ! $is_added ) {
			// Fallback
			$_columns['ean'] = get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) );
		}
		return $_columns;
	}

	/**
	 * render_product_column_ean.
	 *
	 * @version 2.4.0
	 * @since   1.0.1
	 */
	function render_product_column_ean( $do_validate, $ean, $product_id = false ) {
		return ( $do_validate && ! alg_wc_ean()->core->is_valid_ean( $ean, $product_id ) ? '<span style="color:red;">' . $ean . '</span>' : $ean );
	}

	/**
	 * render_product_columns.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	function render_product_columns( $column, $product_id ) {
		if ( 'ean' === $column ) {
			$product     = wc_get_product( $product_id );
			$values      = array();
			$do_validate = ( 'yes' === get_option( 'alg_wc_ean_backend_column_validate', 'no' ) );
			if ( '' != ( $value = alg_wc_ean()->core->get_ean( $product_id ) ) ) {
				$values[] = $this->render_product_column_ean( $do_validate, $value, $product_id );
			}
			if ( $product->is_type( 'variable' ) ) {
				foreach ( $product->get_children() as $child_id ) {
					if ( '' != ( $value = alg_wc_ean()->core->get_ean( $child_id ) ) ) {
						$values[] = $this->render_product_column_ean( $do_validate, $value, $product_id );
					}
				}
			}
			if ( ! empty( $values ) ) {
				echo implode( ', ', $values );
			}
		}
	}

	/**
	 * ean_shortcode.
	 *
	 * @version 2.1.0
	 * @since   1.5.1
	 *
	 * @todo    [maybe] check if valid?
	 * @todo    [maybe] `on_empty`?
	 * @todo    [next] [!] (dev) variable: implode variations' EANs?
	 */
	function ean_shortcode( $atts, $content = '' ) {
		$default_atts = array(
			'product_id' => false,
			'before'     => '',
			'after'      => '',
		);
		$atts = shortcode_atts( $default_atts, $atts, 'alg_wc_ean' );
		$result = alg_wc_ean()->core->get_ean( $atts['product_id'] );
		return ( '' === $result ? '' : ( $atts['before'] . $result . $atts['after'] ) );
	}

	/**
	 * add_ean_to_product_structured_data.
	 *
	 * @version 3.3.0
	 * @since   1.0.0
	 *
	 * @see     http://schema.org/Product
	 *
	 * @todo    [next] custom key?
	 * @todo    [next] maybe always use `gtin` (... all-numeric string of either 8, 12, 13 or 14 digits...)
	 * @todo    [next] `default` (`C128`): maybe no markup then?
	 */
	function add_ean_to_product_structured_data( $markup, $product ) {
		if ( '' !== ( $value = alg_wc_ean()->core->get_ean( $product->get_id() ) ) ) {
			$type = alg_wc_ean()->core->get_type( $value, false, $product->get_id() );
			switch ( $type ) {
				case 'EAN8':
					$key = 'gtin8';
					break;
				case 'UPCA':
					$key = 'gtin12';
					break;
				case 'EAN13':
				case 'ISBN13':
				case 'JAN':
					$key = 'gtin13';
					break;
				default: // e.g. `AUTO`, `C128`
					$key = apply_filters( 'alg_wc_ean_product_structured_data_markup_key', 'gtin', $type );
			}
			$markup[ $key ] = $value;
		}
		return $markup;
	}

	/**
	 * variations_enqueue_scripts.
	 *
	 * @version 2.2.9
	 * @since   1.0.0
	 */
	function variations_enqueue_scripts() {
		if ( 'product_meta' === get_option( 'alg_wc_ean_frontend_variation_position', 'product_meta' ) ) {
			wp_enqueue_script( 'alg-wc-ean-variations',
				alg_wc_ean()->plugin_url() . '/includes/js/alg-wc-ean-variations' . ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ? '' : '.min' ) . '.js',
				array( 'jquery' ),
				alg_wc_ean()->version,
				true
			);
			wp_localize_script( 'alg-wc-ean-variations',
				'alg_wc_ean_variations_obj', array( 'variations_form_closest' => get_option( 'alg_wc_ean_js_variations_form_closest', '.summary' ) ) );
		}
	}

	/**
	 * variations_add_params.
	 *
	 * @version 3.0.0
	 * @since   1.0.0
	 */
	function variations_add_params( $args, $product = false, $variation = false ) {
		if ( $variation ) {
			$key = alg_wc_ean()->core->ean_key;
			if ( 'product_meta' === get_option( 'alg_wc_ean_frontend_variation_position', 'product_meta' ) ) {
				$args['ean'] = $variation->get_meta( $key );
			} else {
				$args['variation_description'] .= str_replace( '%ean%', $variation->get_meta( $key ), get_option( 'alg_wc_ean_template', __( 'EAN: %ean%', 'ean-for-woocommerce' ) ) );
			}
		}
		return $args;
	}

	/**
	 * get_ean_output_data.
	 *
	 * @version 2.2.5
	 * @since   1.1.0
	 *
	 * @todo    [next] better solution for variable products
	 */
	function get_ean_output_data() {
		$result = array( 'do_output' => false, 'style' => '', 'value' => alg_wc_ean()->core->get_ean() );
		if ( '' !== $result['value'] ) {
			$result['do_output'] = true;
		} else {
			global $product;
			if ( $product && $product->is_type( 'variable' ) ) {
				$result['do_output'] = true;
				$result['style']     = ' style="display:none;"';
			}
		}
		return $result;
	}

	/**
	 * add_ean.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 *
	 * @todo    [next] [!] (dev) template: shortcode vs placeholder?
	 * @todo    [maybe] customizable wrapping HTML (same for all frontend/backend options)
	 * @todo    [maybe] `esc_html__( 'N/A', 'ean-for-woocommerce' )`
	 */
	function add_ean( $template ) {
		$output_data = $this->get_ean_output_data();
		if ( $output_data['do_output'] ) {
			$ean_html = '<span class="ean">' . $output_data['value'] . '</span>';
			echo '<span class="sku_wrapper ean_wrapper"' . $output_data['style'] . '>' . str_replace( '%ean%', $ean_html, $template ) . '</span>';
		}
	}

	/**
	 * add_ean_single.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function add_ean_single() {
		$this->add_ean( get_option( 'alg_wc_ean_template', __( 'EAN: %ean%', 'ean-for-woocommerce' ) ) );
	}

	/**
	 * add_ean_loop.
	 *
	 * @version 2.8.0
	 * @since   2.0.0
	 *
	 * @todo    [maybe] (feature) customizable template; position(s)?
	 * @todo    [maybe] (dev) variable: implode variations' EANs?
	 */
	function add_ean_loop() {
		$this->add_ean( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) . ': %ean%' );
	}

	/**
	 * add_ean_cart.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 *
	 * @todo    [next] use `$this->add_ean()`?
	 */
	function add_ean_cart( $cart_item ) {
		$product_id = ( ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'] );
		if ( $ean = alg_wc_ean()->core->get_ean( $product_id ) ) {
			echo '<div><span class="sku_wrapper ean_wrapper">' . esc_html__( 'EAN:', 'ean-for-woocommerce' ) . ' ' .
				'<span class="ean">' . $ean . '</span>' .
			'</span></div>';
		}
	}

	/**
	 * save_email_data.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function save_email_data( $order, $sent_to_admin, $plain_text, $email ) {
		$this->order_table_email = $email;
	}

	/**
	 * reset_email_data.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function reset_email_data() {
		$this->order_table_email = false;
	}

	/**
	 * add_to_order_item_meta_ean.
	 *
	 * @version 3.1.0
	 * @since   1.2.0
	 */
	function add_to_order_item_meta_ean( $item_id, $item, $order, $plain_text ) {
		if ( false !== ( $ean = alg_wc_ean()->core->get_ean_from_order_item( $item ) ) ) {
			// Do we need to display?
			$do_display_in_emails = ( 'yes' === get_option( 'alg_wc_ean_order_items_table_emails', get_option( 'alg_wc_ean_order_items_table', 'no' ) ) );
			$emails               = get_option( 'alg_wc_ean_order_items_table_emails_list', array() );
			$do_display_on_pages  = ( 'yes' === get_option( 'alg_wc_ean_order_items_table', 'no' ) );
			if ( $do_display_in_emails && empty( $emails ) && $do_display_on_pages ) {
				// Display everywhere, no need to check further...
				$do_display = true;
			} else {
				if ( empty( $this->order_table_email ) ) {
					// "WooCommerce Email Customizer with Drag and Drop Email Builder" plugin by "Flycart Technologies LLP"
					global $woo_email_arguments;
					if ( ! empty( $woo_email_arguments['email'] ) ) {
						$this->order_table_email = $woo_email_arguments['email'];
					}
				}
				if ( ! empty( $this->order_table_email ) ) {
					// It's an email...
					$do_display = ( $do_display_in_emails ? ( empty( $emails ) || in_array( $this->order_table_email->id, $emails ) ) : false );
				} else {
					// It's a page, e.g. "Thank you"
					$do_display = $do_display_on_pages;
				}
			}
			// Display
			if ( $do_display ) {
				$templates = array_replace( array(
						'html'       => '<ul class="wc-item-meta"><li><span class="sku_wrapper ean_wrapper">EAN: <span class="ean">%ean%</span></span></li></ul>',
						'plain_text' => '%new_line%- EAN: %ean%',
					), get_option( 'alg_wc_ean_order_items_table_templates', array() ) );
				echo str_replace( array( '%new_line%', '%ean%' ), array( "\n", $ean ), $templates[ ( ! $plain_text ? 'html' : 'plain_text' ) ] );
			}
		}
	}

}

endif;

return new Alg_WC_EAN_Display();
