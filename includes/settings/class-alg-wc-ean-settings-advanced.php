<?php
/**
 * EAN for WooCommerce - Advanced Section Settings
 *
 * @version 3.1.0
 * @since   2.2.9
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Advanced' ) ) :

class Alg_WC_EAN_Settings_Advanced extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.2.9
	 * @since   2.2.9
	 */
	function __construct() {
		$this->id   = 'advanced';
		$this->desc = __( 'Advanced', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 3.1.0
	 * @since   2.2.9
	 *
	 * @todo    [now] [!] (dev) Import/Export: move to "Tools", and/or add dashicons?
	 * @todo    [now] [!] (desc) `alg_wc_ean_meta_key`
	 * @todo    [later] (desc) `alg_wc_ean_js_variations_form_closest`: better desc
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Advanced Options', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_advanced_options',
			),
			array(
				'title'    => __( 'Meta key', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_meta_key',
				'default'  => '_alg_ean',
				'type'     => 'text',
				'custom_attributes' => array( 'required' => 'required' ),
			),
			array(
				'title'    => __( 'JS selector in variation', 'ean-for-woocommerce' ),
				'desc'     => sprintf( __( 'This is used only if "%s" option is set to "%s".', 'ean-for-woocommerce' ),
						__( 'Variable products: Position in variation', 'ean-for-woocommerce' ), __( 'Product meta', 'ean-for-woocommerce' ) ) . ' ' .
					__( 'Leave at the default value if unsure.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_js_variations_form_closest',
				'default'  => '.summary',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Force remote image', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Forces remote images in Barcodes > Order items table > Pages.', 'ean-for-woocommerce' ) . ' ' .
					__( '<strong>Please note</strong> that this option won\'t work on <code>localhost</code> environment.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_order_items_table_barcode_force_remote_img',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_advanced_options',
			),
			array(
				'title'    => __( 'Export/Import Plugin Settings', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_export_import_settings_options',
			),
			array(
				'title'    => __( 'Export', 'ean-for-woocommerce' ),
				'desc'     => __( 'Export', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Check the box and "Save changes" to export.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_export_settings',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Import', 'ean-for-woocommerce' ),
				'desc'     => __( 'Choose file and "Save changes" to import.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_import_settings',
				'type'     => 'alg_wc_ean_file',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_export_import_settings_options',
			),
		);
	}

}

endif;

return new Alg_WC_EAN_Settings_Advanced();
