<?php
/**
 * The admin-specific functionality of the plugin.
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
 * Manage the Product column in the Product QA table.
 *
 * @since 1.0.0
 *
 * @package    Product_Faq_For_Woocommerce
 * @subpackage Product_Faq_For_Woocommerce/admin
 * @author     Vishal Kakadiya <vishalkakadiya123@gmail.com>
 */
class Product_Faq_For_Woocommerce_Admin {

	/**
	 * The post type for questions and answers.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string custom-post-type slug.
	 */
	private $post_type = 'wc_product_faq';

	/**
	 * Initialize the class and set hooks.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function __construct() {
		add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'column_title' ) );
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'column_content' ), 1, 2 );
	}

	/**
	 * Add the product column to the table.
	 *
	 * Callback function for manage_{$this->post_type}_posts_columns (filter).
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param   array $defaults Column list.
	 *
	 * @return  array Column list.
	 */
	public function column_title( $defaults ) {
		$defaults['product'] = __( 'Product', 'product-qa-for-woocommerce' );

		return $defaults;
	}

	/**
	 * Render content in the product column.
	 *
	 * Callback function for manage_{$this->post_type}_posts_custom_column (action).
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post id.
	 */
	public function column_content( $column_name, $post_id ) {
		if ( 'product' === $column_name ) {
			$product_id = wp_get_post_parent_id( absint( $post_id ) );

			// Questions without a parent product have nothing to link to.
			if ( ! $product_id ) {
				echo '&mdash;';
				return;
			}
			?>
			<a href="<?php echo esc_url( get_the_permalink( $product_id ) ); ?>">
				<?php echo esc_html( get_the_title( $product_id ) ); ?>
			</a>
			<?php
		}
	}
}
