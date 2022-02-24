<?php
/**
 * EAN for WooCommerce - Print Section Settings
 *
 * @version 3.5.0
 * @since   2.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_EAN_Settings_Print' ) ) :

class Alg_WC_EAN_Settings_Print extends Alg_WC_EAN_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function __construct() {
		$this->id   = 'print';
		$this->desc = __( 'Print', 'ean-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * maybe_add_deprecated_settings.
	 *
	 * @version 3.5.0
	 * @since   3.5.0
	 */
	function maybe_add_deprecated_settings( $settings, $options ) {

		// Barcode Options
		if ( ! empty( $options['template'] ) && false !== strpos( $options['template'], '%barcode%' ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Barcode Options', 'ean-for-woocommerce' ),
					'desc'     => sprintf( __( 'For the %s placeholder.', 'ean-for-woocommerce' ), '<code>' . '%barcode%' . '</code>' ),
					'type'     => 'title',
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_barcode_options',
				),
				array(
					'title'    => __( 'Barcode width', 'ean-for-woocommerce' ),
					'desc_tip' => __( 'Width of a single bar element in pixels.', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[barcode_width_px]',
					'default'  => 2,
					'type'     => 'number',
					'custom_attributes' => array( 'min' => 1 ),
				),
				array(
					'title'    => __( 'Barcode height', 'ean-for-woocommerce' ),
					'desc_tip' => __( 'Height of a single bar element in pixels.', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[barcode_height_px]',
					'default'  => 30,
					'type'     => 'number',
					'custom_attributes' => array( 'min' => 1 ),
				),
				array(
					'title'    => __( 'Barcode color', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[barcode_color]',
					'default'  => '#000000',
					'type'     => 'color',
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_barcode_options',
				),
			) );
		}

		// 2D Barcode Options
		if ( ! empty( $options['template'] ) && false !== strpos( $options['template'], '%barcode_2d%' ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( '2D Barcode Options', 'ean-for-woocommerce' ),
					'desc'     => sprintf( __( 'For the %s placeholder.', 'ean-for-woocommerce' ), '<code>' . '%barcode_2d%' . '</code>' ),
					'type'     => 'title',
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_barcode_2d_options',
				),
				array(
					'title'    => __( '2D barcode width', 'ean-for-woocommerce' ),
					'desc_tip' => __( 'Width of a single rectangle element in pixels.', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[barcode_2d_width_px]',
					'default'  => 3,
					'type'     => 'number',
					'custom_attributes' => array( 'min' => 1 ),
				),
				array(
					'title'    => __( '2D barcode height', 'ean-for-woocommerce' ),
					'desc_tip' => __( 'Height of a single rectangle element in pixels.', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[barcode_2d_height_px]',
					'default'  => 3,
					'type'     => 'number',
					'custom_attributes' => array( 'min' => 1 ),
				),
				array(
					'title'    => __( '2D barcode color', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[barcode_2d_color]',
					'default'  => '#000000',
					'type'     => 'color',
				),
				array(
					'title'    => __( '2D barcode content', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[barcode_2d_content]',
					'default'  => 'ean',
					'type'     => 'select',
					'class'    => 'chosen_select',
					'options'  => array(
						'ean' => __( 'Product EAN', 'ean-for-woocommerce' ),
						'url' => __( 'Product URL', 'ean-for-woocommerce' ),
					),
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_barcode_2d_options',
				),
			) );
		}

		// Product Image Options
		if ( ! empty( $options['template'] ) && false !== strpos( $options['template'], '%product_image%' ) ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Product Image Options', 'ean-for-woocommerce' ),
					'desc'     => sprintf( __( 'For the %s placeholder.', 'ean-for-woocommerce' ), '<code>' . '%product_image%' . '</code>' ),
					'type'     => 'title',
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_product_image_options',
				),
				array(
					'title'    => __( 'Product image width', 'ean-for-woocommerce' ),
					'desc_tip' => __( 'In pixels.', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[product_image_width_px]',
					'default'  => 30,
					'type'     => 'number',
					'custom_attributes' => array( 'min' => 1 ),
				),
				array(
					'title'    => __( 'Product image height', 'ean-for-woocommerce' ),
					'desc_tip' => __( 'In pixels.', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[product_image_height_px]',
					'default'  => 30,
					'type'     => 'number',
					'custom_attributes' => array( 'min' => 1 ),
				),
				array(
					'title'    => __( 'Product image size', 'ean-for-woocommerce' ),
					'desc_tip' => __( 'Accepts any registered image size name.', 'ean-for-woocommerce' ),
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[product_image_size]',
					'default'  => 'woocommerce_thumbnail',
					'type'     => 'text',
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_wc_ean_print_barcodes_to_pdf_product_image_options',
				),
			) );
		}

		return $settings;
	}

	/**
	 * get_settings.
	 *
	 * @version 3.5.0
	 * @since   2.0.0
	 *
	 * @see     https://www.avery.com/templates/6879 (default margins etc.)
	 *
	 * @todo    [next] (feature) Cell border: add customization options
	 * @todo    [now] [!] (desc) "Deprecated placeholders" desc tip: remove?
	 * @todo    [now] [!!] (feature) `wpautop()`?
	 * @todo    [next] (desc) `children`: better desc?
	 * @todo    [next] (desc) better section desc?
	 * @todo    [maybe] (desc) Page format: better desc?
	 */
	function get_settings() {

		$options = get_option( 'alg_wc_ean_print_barcodes_to_pdf_settings', array() );

		$unit       = ( isset( $options['unit'] ) ? $options['unit'] : 'in' );
		$units      = array(
			'mm' => __( 'millimeters', 'ean-for-woocommerce' ),
			'cm' => __( 'centimeters', 'ean-for-woocommerce' ),
			'in' => __( 'inches', 'ean-for-woocommerce' ),
			'pt' => __( 'points', 'ean-for-woocommerce' ),
		);
		$unit_title = $units[ $unit ];

		$point_desc = __( 'A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.', 'ean-for-woocommerce' );

		$pdf_page_formats = require( 'alg-wc-ean-pdf-page-formats.php' );
		foreach ( $pdf_page_formats as $format_id => $format_dim ) {
			// Converting from points to `$unit`
			switch ( $unit ) {
				case 'mm':
					$format_dim_w = round( $format_dim[0] / 2.835 );
					$format_dim_h = round( $format_dim[1] / 2.835 );
					break;
				case 'cm':
					$format_dim_w = round( $format_dim[0] / 28.35, 1 );
					$format_dim_h = round( $format_dim[1] / 28.35, 1 );
					break;
				default: // 'in'
					$format_dim_w = round( $format_dim[0] / 72, 1 );
					$format_dim_h = round( $format_dim[1] / 72, 1 );
			}
			$pdf_page_formats[ $format_id ] = "{$format_id} ({$format_dim_w} x {$format_dim_h} {$unit_title})";
		}

		$settings = array(
			array(
				'title'    => __( 'Print Barcodes (PDF)', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_options',
			),
			array(
				'title'    => __( 'Print barcodes (PDF)', 'ean-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'ean-for-woocommerce' ) . '</strong>',
				'desc_tip' => sprintf( __( 'This will add "Print barcodes" to the "Bulk actions" in %s.', 'ean-for-woocommerce' ),
						'<a href="' . admin_url( 'edit.php?post_type=product' ) . '">' . __( 'admin products list', 'ean-for-woocommerce' ) . '</a>' ) . $this->pro_msg(),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf',
				'default'  => 'no',
				'type'     => 'checkbox',
				'custom_attributes' => apply_filters( 'alg_wc_ean_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_options',
			),
			array(
				'title'    => __( 'General Options', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_general_options',
			),
			array(
				'title'    => __( 'Page orientation', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_orientation]',
				'default'  => 'P',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'P' => __( 'Portrait', 'ean-for-woocommerce' ),
					'L' => __( 'Landscape', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Unit', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'User measure unit.', 'ean-for-woocommerce' ) . '<br><br>' .
					sprintf( __( 'Used in %s options.', 'ean-for-woocommerce' ), '"' . implode( '", "', array(
							__( 'Page format: Custom: Width', 'ean-for-woocommerce' ),
							__( 'Page format: Custom: Height', 'ean-for-woocommerce' ),
							__( 'Cell width', 'ean-for-woocommerce' ),
							__( 'Cell height', 'ean-for-woocommerce' ),
							__( 'Top margin', 'ean-for-woocommerce' ),
							__( 'Left margin', 'ean-for-woocommerce' ),
							__( 'Right margin', 'ean-for-woocommerce' ),
							__( 'Page break margin', 'ean-for-woocommerce' ),
						) ) . '"' ) . '<br><br>' .
					'* ' . $point_desc,
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[unit]',
				'default'  => 'in',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => $units,
			),
			array(
				'title'    => __( 'Page format', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_format]',
				'default'  => 'LETTER',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array_merge( array( 'custom' => __( 'Custom', 'ean-for-woocommerce' ) ), $pdf_page_formats ),
			),
			array(
				'desc'     => __( 'Page format: Custom: Width', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ) . '<br><br>' .
					sprintf( __( 'Ignored unless "%s" option is set to "%s".', 'ean-for-woocommerce' ),
						__( 'Page format', 'ean-for-woocommerce' ), __( 'Custom', 'ean-for-woocommerce' ) ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_format_custom_width]',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'desc'     => __( 'Page format: Custom: Height', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ) . '<br><br>' .
					sprintf( __( 'Ignored unless "%s" option is set to "%s".', 'ean-for-woocommerce' ),
						__( 'Page format', 'ean-for-woocommerce' ), __( 'Custom', 'ean-for-woocommerce' ) ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_format_custom_height]',
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'title'    => __( 'Max barcodes per page', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[per_page]',
				'default'  => 12,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'    => __( 'Columns', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[columns]',
				'default'  => 2,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'    => __( 'Cell width', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_width]',
				'default'  => 4,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0.000001, 'step' => 0.000001 ),
			),
			array(
				'title'    => __( 'Cell height', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_height]',
				'default'  => 1.5,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0.000001, 'step' => 0.000001 ),
			),
			array(
				'title'    => __( 'Cell border', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[cell_border]',
				'default'  => 0,
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					0 => __( 'No', 'ean-for-woocommerce' ),
					1 => __( 'Yes', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Top margin', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[margin_top]',
				'default'  => 1.13,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'title'    => __( 'Left margin', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[margin_left]',
				'default'  => 0.46,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'title'    => __( 'Right margin', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[margin_right]',
				'default'  => 0.31,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'title'    => __( 'Page break margin', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'In %s.', 'ean-for-woocommerce' ), $unit_title ) . '<br><br>' .
					__( 'Distance from the bottom of the page that defines the automatic page breaking triggering limit.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[page_break_margin]',
				'default'  => 0.79,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.000001 ),
			),
			array(
				'title'    => __( 'Font', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'If you are having issues displaying your language specific letters, select "%s" font.', 'ean-for-woocommerce' ),
					'DejaVu Sans (Unicode)' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[font_family]',
				'default'  => 'dejavusans',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'dejavusans' => 'DejaVu Sans (Unicode)',
					'times'      => 'Times New Roman',
					'helvetica'  => 'Helvetica',
					'courier'    => 'Courier',
				),
			),
			array(
				'title'    => __( 'Font size', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[font_size]',
				'default'  => 11,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'    => __( 'Template', 'ean-for-woocommerce' ),
				'desc'     => sprintf( __( 'You should use shortcodes here: %s.', 'ean-for-woocommerce' ),
					'<code>' . implode( '</code>, <code>', array(
						'[alg_wc_ean]',
						'[alg_wc_ean_barcode]',
						'[alg_wc_ean_barcode_2d]',
						'[alg_wc_ean_product_image]',
						'[alg_wc_ean_product_name]',
						'[alg_wc_ean_product_price]',
						'[alg_wc_ean_product_sku]',
						'[alg_wc_ean_product_attr]',
						'[alg_wc_ean_product_id]',
					) ) . '</code>' ),
				'desc_tip' => sprintf( __( 'Deprecated placeholders: %s.', 'ean-for-woocommerce' ), '<strong>' . implode( '</strong>, <strong>', array(
						'%barcode%',
						'%barcode_2d%',
						'%ean%',
						'%product_image%',
						'%product_name%',
						'%product_title%',
						'%product_sku%',
						'%product_price%',
						'%product_price_regular%',
						'%product_price_sale%',
						'%product_price_raw%',
						'%product_price_regular_raw%',
						'%product_price_sale_raw%',
						'%product_id%',
						'%product_parent_title%',
						'%product_parent_sku%',
					) ) . '</strong>' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[template]',
				'default'  => '[alg_wc_ean_barcode]<br>[alg_wc_ean]',
				'type'     => 'textarea',
				'css'      => 'width:100%;height:150px;',
			),
			array(
				'title'    => __( 'Variations', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[children]',
				'default'  => 'no',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'no'      => __( 'Do not include', 'ean-for-woocommerce' ),
					'yes'     => __( 'Add', 'ean-for-woocommerce' ),
					'replace' => __( 'Replace', 'ean-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Use stock quantity', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Print separate label for each product inventory item.', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[use_qty]',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_general_options',
			),
		);

		$settings = $this->maybe_add_deprecated_settings( $settings, $options );

		$settings = array_merge( $settings, array(
			array(
				'title'    => __( 'Admin Options', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_admin_options',
			),
			array(
				'title'    => __( 'Print buttons', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings_print_buttons',
				'default'  => array( 'bulk_actions' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					'bulk_actions'   => __( 'Products > Bulk actions', 'ean-for-woocommerce' ),
					'single_product' => __( 'Single product', 'ean-for-woocommerce' ),
					'single_order'   => __( 'Single order', 'ean-for-woocommerce' ),
				),
			),
			array(
				'desc'     => sprintf( __( 'Print buttons style, e.g. %s', 'ean-for-woocommerce' ), '<code>font-size: 40px; width: 40px; height: 40px;</code>' ),
				'desc_tip' => sprintf( __( 'Applied to the "%s" and "%s" print buttons.', 'ean-for-woocommerce' ),
					__( 'Single product', 'ean-for-woocommerce' ), __( 'Single order', 'ean-for-woocommerce' ) ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings_print_buttons_style',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Variations print buttons', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Applied to the "%s" print buttons.', 'ean-for-woocommerce' ),
					__( 'Single product', 'ean-for-woocommerce' ) ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings_buttons_variations',
				'default'  => array( 'variations_tab' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => array(
					'variations_tab' => __( 'Variations tab', 'ean-for-woocommerce' ),
					'meta_box'       => __( 'Meta box', 'ean-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_admin_options',
			),
			array(
				'title'    => __( 'Advanced Options', 'ean-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_advanced_options',
			),
			array(
				'title'    => __( 'Skip products without EAN', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => __( 'Skip products without EAN when generating PDF.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings_skip_empty_ean',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Use Print.js', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Use %s library for printing PDFs.', 'ean-for-woocommerce' ),
					'<a href="https://printjs.crabbly.com/" target="_blank">Print.js</a>' ),
				'id'       => 'alg_wc_ean_print_use_print_js',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Suppress errors', 'ean-for-woocommerce' ),
				'desc'     => __( 'Enable', 'ean-for-woocommerce' ) . ' (' . __( 'recommended', 'ean-for-woocommerce' ) . ')',
				'desc_tip' => __( 'Suppress PHP errors when generating PDF.', 'ean-for-woocommerce' ),
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_settings[suppress_errors]',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_ean_print_barcodes_to_pdf_advanced_options',
			),
		) );

		return $settings;
	}

}

endif;

return new Alg_WC_EAN_Settings_Print();
