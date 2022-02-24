<?php
/**
 * EAN for WooCommerce - Tools Section Settings
 *
 * @version 3.5.0
 * @since   2.2.5
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Tools' ) ) :

class Alg_WC_EAN_Settings_Tools extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.2.5
	 * @since   2.2.5
	 */
	function __construct() {
		$this->id   = 'tools';
		$this->desc = __( 'Tools', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_product_actions_list.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 */
	function get_product_actions_list() {
		return array(
			''            => __( 'Disabled', 'ean-for-woocommerce' ),
			'generate'    => __( 'Generate', 'ean-for-woocommerce' ),
			'copy_sku'    => __( 'Copy product SKU', 'ean-for-woocommerce' ),
			'copy_id'     => __( 'Copy product ID', 'ean-for-woocommerce' ),
			'copy_meta'   => __( 'Copy product meta', 'ean-for-woocommerce' ),
			'assign_list' => __( 'Assign from the list', 'ean-for-woocommerce' ),
		);
	}

	/**
	 * get_missing_product_cat_title.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 */
	function get_missing_product_cat_title( $term_id ) {
		return sprintf( __( 'Product category #%s', 'ean-for-woocommerce' ), $term_id );
	}

	/**
	 * get_terms.
	 *
	 * @version 3.0.0
	 * @since   3.0.0
	 *
	 * @todo    [maybe] (desc) add term ID?
	 */
	function get_terms( $taxonomy, $option ) {
		$terms  = get_option( $option, array() );
		$terms  = ( ! empty( $terms[ $taxonomy ] ) ? array_combine( $terms[ $taxonomy ], array_map( array( $this, "get_missing_{$taxonomy}_title" ), $terms[ $taxonomy ] ) ) : array() );
		$_terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		$_terms = ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ? array_combine( wp_list_pluck( $_terms, 'term_id' ), wp_list_pluck( $_terms, 'name' ) ) : array() );
		return array_replace( $terms, $_terms );
	}

	/**
	 * get_settings.
	 *
	 * @version 3.5.0
	 * @since   2.2.5
	 *
	 * @todo    [now] (dev) "Product tags" (similar to "Product categories")
	 * @todo    [now] (dev) add "Product categories" (and "Product tags") to all tools (now only in "Assign from the list")
	 * @todo    [now] [!] (desc) add subsections, e.g.: "Automatic and Periodic Actions", "Bulk Actions"
	 * @todo    [now] [!] (desc) Periodic action: rename?
	 * @todo    [now] (desc) `seed_prefix`
	 * @todo    [next] (desc) "Order Tools": add info about "General > Orders" options (i.e. "Add EAN to new order items meta", etc.)
	 * @todo    [maybe] (desc) better desc for all tools?
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'    => __( 'Tools', 'ean-for-woocommerce' ),
				'desc'     => sprintf( __( 'Check the %s box and "Save changes" to run the tool. Please note that there is no undo for these tools.', 'ean-for-woocommerce' ),
					'<span class="dashicons dashicons-admin-generic"></span>' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_tools',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_tools',
			),
			array(
				'title'    => __( 'Product Tools', 'ean-for-woocommerce' ),
				'desc'     => sprintf( __( 'Please note that %s, %s and %s tools will <strong>not</strong> overwrite EANs for products with existing EANs.', 'ean-for-woocommerce' ),
						'<strong>' . __( 'Generate', 'ean-for-woocommerce' ) . '</strong>',
						'<strong>' . __( 'Copy', 'ean-for-woocommerce' ) . '</strong>',
						'<strong>' . __( 'Assign', 'ean-for-woocommerce' ) . '</strong>' ) . ' ' .
					sprintf( __( 'You can use the %s tool to clear the existing EANs before generating or copying.', 'ean-for-woocommerce' ),
						'<strong>' . __( 'Delete', 'ean-for-woocommerce' ) . '</strong>' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_tools_products',
			),
			array(
				'title'    => __( 'Generate', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Generate EAN for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[generate]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Type', 'ean-for-woocommerce' ),
				'desc_tip' =>
					'* ' . __( 'To generate ISBN-13, set this option to "EAN-13", and then set the "Country prefix" option to 978 or 979.', 'ean-for-woocommerce' ) . '<br><br>' .
					'* ' . __( 'To generate JAN, set this option to "EAN-13", and then set the "Country prefix" option to a number in the 450-459 range or in the 490-499 range.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_generate[type]',
				'default'  => 'EAN13',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'EAN8'  => 'EAN-8',
					'UPCA'  => 'UPC-A',
					'EAN13' => 'EAN-13',
				),
			),
			array(
				'desc'     => __( 'Country prefix (from)', 'ean-for-woocommerce' ) . ' ' .
					sprintf( '<a target="_blank" title="%s" style="text-decoration:none;" href="%s">%s</a>',
						__( 'List of GS1 country codes.', 'ean-for-woocommerce' ),
						'https://en.wikipedia.org/wiki/List_of_GS1_country_codes',
						'<span class="dashicons dashicons-external"></span>' ),
				'id'       => 'alg_wc_ean_tool_product_generate[prefix]',
				'default'  => 200,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'max' => 999 ),
			),
			array(
				'desc'     => __( 'County prefix (to)', 'ean-for-woocommerce' ) . ' (' . __( 'optional', 'ean-for-woocommerce' ) . ')',
				'desc_tip' => sprintf( __( 'If set, prefix will be generated randomly between "%s" and "%s" values.', 'ean-for-woocommerce' ),
					__( 'Prefix from', 'ean-for-woocommerce' ), __( 'Prefix to', 'ean-for-woocommerce' ) ),
				'id'       => 'alg_wc_ean_tool_product_generate[prefix_to]',
				'default'  => '',
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'max' => 999 ),
			),
			array(
				'desc'     => __( 'County prefix length', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'For EAN-8 type only. County prefix length for other types will always be 3.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_generate[prefix_length]',
				'default'  => 3,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 2, 'max' => 3 ),
			),
			array(
				'desc'     => __( 'Seed prefix', 'ean-for-woocommerce' ) . ' (' . __( 'optional', 'ean-for-woocommerce' ) . ')',
				'id'       => 'alg_wc_ean_tool_product_generate[seed_prefix]',
				'default'  => '',
				'type'     => 'text',
				'custom_attributes' => array( 'pattern' => '[0-9]+' ),
			),
			array(
				'title'    => __( 'Copy product SKU', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN from product SKU for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_sku]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Copy product ID', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN from product ID for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_id]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Copy product meta', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Copy EAN from product meta for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[copy_meta]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => sprintf( __( 'Meta key, e.g. %s', 'ean-for-woocommerce' ), '<code>_gtin</code>' ),
				'desc_tip' => __( 'Product meta key to copy from.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_copy_meta[key]',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Assign from the list', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Assign EAN from the list for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product[assign_list]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'Product categories', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets which product categories to include when assigning the EANs (i.e. all other categories will be skipped).', 'ean-for-woocommerce' ) . ' ' .
					__( 'Leave blank to use all product categories.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_assign_list_settings[product_cat]',
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => $this->get_terms( 'product_cat', 'alg_wc_ean_tool_product_assign_list_settings' ),
			),
			array(
				'desc'     => __( 'List', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'EAN list to assign from.', 'ean-for-woocommerce' ) . ' ' .
					__( 'One EAN per line.', 'ean-for-woocommerce' ) . ' ' .
					__( 'Used (i.e. assigned) EANs will be automatically removed from the list.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_assign_list',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'height:100px;',
			),
			array(
				'title'    => __( 'Delete', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Delete all EANs for all products', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_delete_product_meta',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_tools_products',
			),
			array(
				'title'    => __( 'Product Actions', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_tools_products_actions',
			),
			array(
				'title'    => __( 'Automatic actions', 'ean-for-woocommerce' ),
				'desc'     => __( 'New product', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets actions to be automatically performed when new product is added.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_action_on_new',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_actions_list(),
			),
			array(
				'desc'     => __( 'Update product', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets actions to be automatically performed when product is updated.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_product_action_on_update',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_actions_list(),
			),
			array(
				'title'    => __( 'Periodic action', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets action to be automatically performed on a periodic basis (for all products).', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_products_periodic_action',
				'default'  => '',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $this->get_product_actions_list(),
			),
			array(
				'desc'     => __( 'Periodic action interval in seconds', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_products_periodic_action_interval',
				'default'  => 3600,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'    => __( '"Products > Bulk actions"', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Sets actions to be added to the "Products > Bulk actions" dropdown.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_product_bulk_actions',
				'default'  => array( 'alg_wc_ean_delete', 'alg_wc_ean_generate' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					'alg_wc_ean_generate' => __( 'Generate EAN', 'ean-for-woocommerce' ),
					'alg_wc_ean_delete'   => __( 'Delete EAN', 'ean-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_tools_products_actions',
			),
			array(
				'title'    => __( 'Order Tools', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_tools_orders',
			),
			array(
				'title'    => __( 'Add', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Add EANs to all order items', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_orders_add',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Delete', 'ean-for-woocommerce' ),
				'desc'     => '<span class="dashicons dashicons-admin-generic"></span> ' . __( 'Delete EANs from all order items', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_tool_orders_delete',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_tools_orders',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_EAN_Settings_Tools();
