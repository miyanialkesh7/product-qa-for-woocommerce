<?php
/**
 * Display question and answer list on woocommerce product detail page
 *
 * Included from Product_Faq_For_Woocommerce_Public::faq_tab_content().
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

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}
?>

<h2><?php esc_html_e( 'Question Answers', 'product-qa-for-woocommerce' ); ?></h2>

<?php
// Only customers who bought the product may answer questions.
$product_faq_bought = false;

if ( is_user_logged_in() ) {
	$product_faq_current_user = wp_get_current_user();

	if ( wc_customer_bought_product( $product_faq_current_user->user_email, $product_faq_current_user->ID, $product->get_id() ) ) {
		$product_faq_bought = true;
	}
}

// Published questions attached to the current product.
$product_faq_question_query = new WP_Query(
	array(
		'post_type'      => 'wc_product_faq',
		'post_status'    => 'publish',
		'post_parent'    => $product->get_id(),
		'posts_per_page' => 50,
		'no_found_rows'  => true,
	)
);

if ( $product_faq_question_query->have_posts() ) {
	?>
	<ul class="wc-faq-questions">
		<?php
		/*
		 * Loop over the posts directly instead of calling the_post(): WooCommerce
		 * unsets the global $product on the_post for non-product posts, which
		 * breaks any later render of this tab in the same request.
		 */
		foreach ( $product_faq_question_query->posts as $product_faq_question ) {
			$product_faq_question_id = $product_faq_question->ID;
			?>

			<li class="wc-questions">
				<div class="question-content">
					<span class="question-symbol"><?php esc_html_e( 'Q', 'product-qa-for-woocommerce' ); ?></span>
					<?php echo esc_html( get_the_title( $product_faq_question ) ); ?>
					<?php if ( $product_faq_bought ) { ?>
						<button type="button" data-id="<?php echo esc_attr( $product_faq_question_id ); ?>" class="answer-button" aria-controls="question-<?php echo esc_attr( $product_faq_question_id ); ?>" aria-expanded="false">
							<?php esc_html_e( 'Answer Now', 'product-qa-for-woocommerce' ); ?>
						</button>
						<div id="question-<?php echo esc_attr( $product_faq_question_id ); ?>" class="answer-slide">
							<form method="post">
								<?php wp_nonce_field( 'wc_give_answer_action', 'wc_give_answer_nonce' ); ?>
								<input type="text"
									placeholder="<?php esc_attr_e( 'Your answer...', 'product-qa-for-woocommerce' ); ?>"
									aria-label="<?php esc_attr_e( 'Your answer', 'product-qa-for-woocommerce' ); ?>"
									name="product_answer" class="field-answer" maxlength="2000" required />
								<input type="hidden" name="question_id" value="<?php echo esc_attr( $product_faq_question_id ); ?>" />
								<input type="submit" name="wc_give_answer"
									value="<?php esc_attr_e( 'Answer', 'product-qa-for-woocommerce' ); ?>" />
							</form>
						</div>
					<?php } ?>
				</div>
				<?php
				$product_faq_answers = get_approved_comments( $product_faq_question_id );

				if ( ! empty( $product_faq_answers ) ) {
					?>
					<ul class="wc-faq-answers">
						<?php foreach ( $product_faq_answers as $product_faq_answer ) { ?>
							<li class="wc-answers">
								<span class="answer-symbol"><?php esc_html_e( 'A', 'product-qa-for-woocommerce' ); ?></span>
								<span class="answer"><?php echo esc_html( $product_faq_answer->comment_content ); ?></span>
							</li>
						<?php } ?>
					</ul>
					<?php
				}
				?>
			</li>
		<?php } ?>
	</ul>
	<?php
} else {
	echo '<p>' . esc_html__( 'No questions yet. Be the first to ask a question!', 'product-qa-for-woocommerce' ) . '</p>';
}

if ( is_user_logged_in() ) :
	?>
	<h3><?php esc_html_e( 'Ask Question Now!', 'product-qa-for-woocommerce' ); ?></h3>
	<form method="post">
		<?php wp_nonce_field( 'wc_ask_question_action', 'wc_ask_question_nonce' ); ?>
		<input type="text" placeholder="<?php esc_attr_e( 'Your question ?', 'product-qa-for-woocommerce' ); ?>"
			aria-label="<?php esc_attr_e( 'Your question', 'product-qa-for-woocommerce' ); ?>"
			name="product_question" class="field-question" maxlength="500" required />
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>" />
		<input type="submit" name="wc_ask_question" value="<?php esc_attr_e( 'Ask Now', 'product-qa-for-woocommerce' ); ?>" />
	</form>
	<?php
else :
	echo '<p>' . esc_html__( 'Please login to ask questions.', 'product-qa-for-woocommerce' ) . '</p>';
endif;
