<?php
/**
 * Email template for question.
 *
 * Included from Product_Faq_For_Woocommerce_Public::email_template(), which
 * provides $product_id, $title, $content and $button_label.
 *
 * @link       http://profiles.wordpress.org/vishalkakadiya/
 *
 * @package    Product_Faq_For_Woocommerce
 * @subpackage Product_Faq_For_Woocommerce/public/templates
 * @since      1.0.0
 */

// Abort if this file is accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_faq_url   = get_permalink( $product_id );
$product_faq_title = get_the_title( $product_id );
$product_faq_logo  = get_option( 'wc_product_faq_email_logo' );
?>
<!DOCTYPE html>
<html>

<body>
	<header style="text-align:center">
		<h1
			style="background-color:#2F4F4F;border:1px solid rgb(204,204,204);margin-bottom:0;padding:12px;text-align:center;height:50px;">
			<?php if ( $product_faq_logo ) : ?>
				<img src="<?php echo esc_url( $product_faq_logo ); ?>" alt="<?php echo esc_attr( get_option( 'blogname' ) ); ?>" style="height: 50px;" />
			<?php else : ?>
				<?php echo esc_html( get_option( 'blogname' ) ); ?>
			<?php endif; ?>
		</h1>
	</header>

	<div style="border:1px solid #cccccc;border-top:0;padding:35px 35px 15px;position: relative;display: flex;">
		<div style="width: 100%;">
			<?php
			$product_faq_attachment = has_post_thumbnail( $product_id ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'woocommerce_thumbnail' ) : false;

			if ( $product_faq_attachment ) :
				?>
				<div style="width: 25%; float: left;">
					<div>
						<a href="<?php echo esc_url( $product_faq_url ); ?>">
							<img src="<?php echo esc_url( $product_faq_attachment[0] ); ?>" alt="<?php echo esc_attr( $product_faq_title ); ?>" class="card-image" style="max-width: 100%; height: auto;" />
						</a>
					</div>
					<h4 style="text-align: center; margin-top: 10px; font-size: 16px;">
						<?php echo esc_html( $product_faq_title ); ?>
					</h4>
				</div>
			<?php endif; ?>

			<div style="box-sizing: border-box;float: right;width: 70%;">
				<h3 style="font-size: 20px; margin: 0 0 20px 0;">
					<?php echo esc_html( $title ); ?>
				</h3>

				<div style="margin-bottom:20px">
					<p style="font-size:14px">
						<?php echo wp_kses_post( $content ); ?>
					</p>
				</div>

				<div style="margin-top:35px">
					<a href="<?php echo esc_url( $product_faq_url ); ?>"
						style="background-color:rgb(215,73,55);border:1px solid rgb(0,0,0);color:rgb(255,255,255);cursor: pointer;font-weight:bold;padding:6px 12px;text-decoration:none;">
						<?php echo esc_html( $button_label ); ?>
					</a>
				</div>
			</div>
			<br style="clear:both">
		</div>
	</div>
</body>

</html>
