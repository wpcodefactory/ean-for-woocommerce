<?php
/**
 * EAN for WooCommerce - Display Class
 *
 * @version 3.9.0
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
	 * @version 3.7.0
	 * @since   2.0.0
	 *
	 * @todo    [next] (dev) Admin products list column: move to `class-alg-wc-ean-display-admin.php` or `class-alg-wc-ean-admin.php`?
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
				// "Rank Math SEO" plugin
				if ( 'yes' === get_option( 'alg_wc_ean_frontend_product_structured_data_rank_math_seo', 'no' ) ) {
					add_filter( 'rank_math/json_ld', array( $this, 'add_ean_to_product_structured_data_rank_math_seo' ), PHP_INT_MAX, 2 );
				}
			}
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
	}

	/**
	 * add_ean_to_product_structured_data_rank_math_seo.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 *
	 * @see     https://wordpress.org/plugins/seo-by-rank-math/
	 *
	 * @todo    [next] (dev) simplify?
	 * @todo    [next] (dev) move to the "Compatibility" class (and settings section)?
	 */
	function add_ean_to_product_structured_data_rank_math_seo( $data, $json_ld = false ) {
		if (
			! empty( $data['richSnippet']['@type'] ) && 'Product' === $data['richSnippet']['@type'] &&
			( ! empty( $json_ld->post_id ) || ! empty( $json_ld->post->ID ) )
		) {
			$product_id = ( ! empty( $json_ld->post_id ) ? $json_ld->post_id : $json_ld->post->ID );
			$product    = wc_get_product( $product_id );
			if ( $product ) {
				$_data = $this->add_ean_to_product_structured_data( array(), $product );
				if ( ! empty( $_data ) ) {
					$data['richSnippet'] = array_merge( $data['richSnippet'], $_data );
				}
			}
		}
		return $data;
	}

	/**
	 * product_columns_style.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [next] (dev) make this optional? (same for barcodes)
	 * @todo    [next] (dev) load only on `edit.php?post_type=product` etc.? (same for barcodes)
	 */
	function product_columns_style() {
		?><style>
		.column-ean {
			width: 10%;
		}
		</style><?php
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
	 * add_ean_to_product_structured_data.
	 *
	 * @version 3.8.0
	 * @since   1.0.0
	 *
	 * @see     https://schema.org/Product
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.3.1/plugins/woocommerce/includes/class-wc-structured-data.php#L328
	 *
	 * @todo    [next] (dev) what to do if there is no markup data? see: https://github.com/woocommerce/woocommerce/blob/6.3.1/plugins/woocommerce/includes/class-wc-structured-data.php#L324
	 * @todo    [next] (dev) maybe always use `gtin` (... all-numeric string of either 8, 12, 13 or 14 digits...)
	 * @todo    [next] (dev) `default` (`C128`): maybe no markup then?
	 */
	function add_ean_to_product_structured_data( $markup, $product ) {
		if ( '' !== ( $value = alg_wc_ean()->core->get_ean( $product->get_id() ) ) ) {
			if ( 'yes' === get_option( 'alg_wc_ean_frontend_product_structured_data_key_auto', 'yes' ) ) {
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
						$key = apply_filters( 'alg_wc_ean_product_structured_data_markup_key', 'gtin', $type, $product );
				}
			} else {
				$key = get_option( 'alg_wc_ean_frontend_product_structured_data_key', 'gtin' );
				$key = apply_filters( 'alg_wc_ean_product_structured_data_markup_key', $key, false, $product );
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
	 * @version 3.9.0
	 * @since   1.0.0
	 *
	 * @todo    [next] (dev) template: shortcode vs placeholder?
	 * @todo    [maybe] customizable wrapping HTML (same for all frontend/backend options) - `ean` class must be present though (for the variations' JS)
	 * @todo    [maybe] `esc_html__( 'N/A', 'ean-for-woocommerce' )`
	 */
	function add_ean( $template, $single_or_loop ) {
		$output_data = $this->get_ean_output_data();
		if ( $output_data['do_output'] ) {
			$ean_html = '<span class="ean">' . $output_data['value'] . '</span>';
			$output   = '<span class="sku_wrapper ean_wrapper"' . $output_data['style'] . '>' . str_replace( '%ean%', $ean_html, $template ) . '</span>';
			echo apply_filters( 'alg_wc_ean_display', $output, $output_data['value'], $output_data['style'], $template, $single_or_loop );
		}
	}

	/**
	 * add_ean_single.
	 *
	 * @version 3.9.0
	 * @since   2.1.0
	 */
	function add_ean_single() {
		$this->add_ean( get_option( 'alg_wc_ean_template', __( 'EAN: %ean%', 'ean-for-woocommerce' ) ), 'single' );
	}

	/**
	 * add_ean_loop.
	 *
	 * @version 3.9.0
	 * @since   2.0.0
	 *
	 * @todo    [maybe] (feature) customizable template; position(s)?
	 * @todo    [maybe] (dev) variable: implode variations' EANs?
	 */
	function add_ean_loop() {
		$this->add_ean( get_option( 'alg_wc_ean_title', __( 'EAN', 'ean-for-woocommerce' ) ) . ': %ean%', 'loop' );
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

}

endif;

return new Alg_WC_EAN_Display();
