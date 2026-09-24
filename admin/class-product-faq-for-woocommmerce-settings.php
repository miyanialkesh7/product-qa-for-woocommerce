<?php
/**
 * WooCommerce settings for the plugin.
 *
 * @link       http://profiles.wordpress.org/vishalkakadiya/
 * @since      1.0.0
 *
 * @package    Product_Faq_For_Woocommerce
 * @subpackage Product_Faq_For_Woocommerce/admin
 */

// Abort if this file is accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add WooCommerce settings under WooCommerce -> Settings -> Products.
 *
 * @since 1.0.0
 *
 * @package    Product_Faq_For_Woocommerce
 * @subpackage Product_Faq_For_Woocommerce/admin
 * @author     Vishal Kakadiya <vishalkakadiya123@gmail.com>
 */
class Product_Faq_For_Woocommmerce_Settings {

	/**
	 * Initialize the class and register hooks.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {
		add_filter( 'woocommerce_get_sections_products', array( $this, 'section' ), 10 );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'settings' ), 10, 2 );
	}

	/**
	 * Add a new section in WooCommerce -> Settings -> Products.
	 *
	 * This function is the callback for woocommerce_get_sections_products.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $sections WooCommerce settings sections.
	 *
	 * @return array WooCommerce settings sections.
	 */
	public function section( $sections ) {
		$sections['product_faq'] = __( 'Product QA', 'product-qa-for-woocommerce' );

		return $sections;
	}

	/**
	 * Add settings fields to the section.
	 *
	 * This function is the callback for woocommerce_get_settings_products.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $settings         Existing settings array.
	 * @param string $current_section  Current product settings section.
	 *
	 * @return array Updated settings array.
	 */
	public function settings( $settings, $current_section ) {
		if ( 'product_faq' === $current_section ) {
			$settings = array();

			$settings[] = array(
				'name' => __( 'Product QA Settings', 'product-qa-for-woocommerce' ),
				'type' => 'title',
				'desc' => __( 'Configure the product question and answer feature.', 'product-qa-for-woocommerce' ),
				'id'   => 'product_quotation',
			);

			$settings[] = array(
				'name'     => __( 'Enable qa feature', 'product-qa-for-woocommerce' ),
				'desc_tip' => __( 'You want to enable qa feature on front-end side product detail page', 'product-qa-for-woocommerce' ),
				'id'       => 'wc_product_faq_feature',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable qa feature', 'product-qa-for-woocommerce' ),
			);

			// Product categories keyed by term ID, used as multiselect options.
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'fields'     => 'id=>name',
				)
			);

			if ( is_wp_error( $terms ) ) {
				$terms = array();
			}

			$settings[] = array(
				'name'     => __( 'Select Categories', 'product-qa-for-woocommerce' ),
				'desc_tip' => __( 'Multi-select categories, products with selected categories have QA feature. (Note: Empty selection enables QA feature for all products.)', 'product-qa-for-woocommerce' ),
				'id'       => 'wc_product_faq_category_list',
				'type'     => 'multiselect',
				'options'  => $terms,
				'css'      => 'height: 150px; width:50%;',
				'desc'     => __( 'Multi-select categories, products in selected categories have QA feature. (Note: Empty selection enables QA feature for all products.)', 'product-qa-for-woocommerce' ),
			);

			$settings[] = array(
				'name' => __( 'Sender email', 'product-qa-for-woocommerce' ),
				'desc' => __( 'Add a sender email address for communications.', 'product-qa-for-woocommerce' ),
				'id'   => 'wc_product_faq_sender_email',
				'type' => 'email',
				'css'  => 'min-width:300px;',
			);

			$settings[] = array(
				'name' => __( 'Logo URL', 'product-qa-for-woocommerce' ),
				'desc' => __( 'Add a logo URL to include in the email.', 'product-qa-for-woocommerce' ),
				'id'   => 'wc_product_faq_email_logo',
				'css'  => 'width:50%;',
				'type' => 'url',
			);

			$settings[] = array(
				'name' => __( "Question email's title", 'product-qa-for-woocommerce' ),
				'desc' => __( 'Title displayed at the top of the question email.', 'product-qa-for-woocommerce' ),
				'id'   => 'wc_product_faq_question_title',
				'type' => 'text',
				'css'  => 'min-width:300px;',
			);
			$settings[] = array(
				'name' => __( "Question email's subject", 'product-qa-for-woocommerce' ),
				'desc' => __( 'Subject displayed for the question email.', 'product-qa-for-woocommerce' ),
				'id'   => 'wc_product_faq_question_subject',
				'type' => 'text',
				'css'  => 'min-width:300px;',
			);
			$settings[] = array(
				'name' => __( "Question email's button text", 'product-qa-for-woocommerce' ),
				'desc' => __( 'Button label shown in the question email.', 'product-qa-for-woocommerce' ),
				'id'   => 'wc_product_faq_question_button_text',
				'type' => 'text',
				'css'  => 'min-width:300px;',
			);

			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'product_quotation',
			);
		}

		return $settings;
	}
}
