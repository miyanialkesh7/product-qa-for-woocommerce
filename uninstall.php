<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Deletes the plugin options. Questions (wc_product_faq posts) and their
 * answers (comments) are intentionally kept, because they are store content.
 *
 * @link       http://profiles.wordpress.org/vishalkakadiya/
 * @since      1.0.0
 *
 * @package    Product_Faq_For_Woocommerce
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Remove the plugin settings. Questions and answers are store content, so
 * they are kept and can still be managed after reinstalling the plugin.
 */
$product_faq_options = array(
	'wc_product_faq_feature',
	'wc_product_faq_category_list',
	'wc_product_faq_sender_email',
	'wc_product_faq_email_logo',
	'wc_product_faq_question_title',
	'wc_product_faq_question_subject',
	'wc_product_faq_question_button_text',
);

foreach ( $product_faq_options as $product_faq_option ) {
	delete_option( $product_faq_option );
}
