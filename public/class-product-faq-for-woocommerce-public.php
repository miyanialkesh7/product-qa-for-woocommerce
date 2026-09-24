<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://profiles.wordpress.org/vishalkakadiya/
 * @since      1.0.0
 *
 * @package    Product_Faq_For_Woocommerce
 * @subpackage Product_Faq_For_Woocommerce/public
 */

// Abort if this file is accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Registers the question post type, adds the QA tab to product pages and
 * handles the question and answer form submissions.
 *
 * @package    Product_Faq_For_Woocommerce
 * @subpackage Product_Faq_For_Woocommerce/public
 * @author     Vishal Kakadiya <vishalkakadiya123@gmail.com>
 */
class Product_Faq_For_Woocommerce_Public {

	/**
	 * The post_type for questions and answers.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string custom-post-type slug.
	 */
	private $post_type = 'wc_product_faq';

	/**
	 * Maximum number of characters accepted for a question.
	 *
	 * @since 2.1
	 * @var   int
	 */
	const QUESTION_MAX_LENGTH = 500;

	/**
	 * Maximum number of characters accepted for an answer.
	 *
	 * @since 2.1
	 * @var   int
	 */
	const ANSWER_MAX_LENGTH = 2000;

	/**
	 * Initialize the class and register hooks.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		add_filter( 'woocommerce_product_tabs', array( $this, 'faq_tab' ) );
		add_action( 'init', array( $this, 'save_question' ) );
		add_action( 'init', array( $this, 'save_answer' ) );
		add_filter( 'comments_open', array( $this, 'close_native_comments' ), 10, 2 );
	}

	/**
	 * Register front-end scripts and styles, and enqueue them on product pages.
	 *
	 * The assets are also enqueued from faq_tab_content(), so the tab keeps
	 * working when it is rendered outside a classic single product page, for
	 * example inside an Elementor Pro "Product Data Tabs" widget.
	 *
	 * Callback function for wp_enqueue_scripts (action).
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function enqueue_scripts() {
		wp_register_style(
			'wc-product-faq-public-css',
			plugins_url( 'css/faq-public.css', __FILE__ ),
			array(),
			PRODUCT_QA_FOR_WOOCOMMERCE_VERSION
		);

		wp_register_script(
			'wc-product-faq-public-js',
			plugins_url( 'js/faq-public.js', __FILE__ ),
			array( 'jquery' ),
			PRODUCT_QA_FOR_WOOCOMMERCE_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		if ( function_exists( 'is_product' ) && is_product() ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Enqueue the registered front-end assets.
	 *
	 * @since 2.1
	 * @access private
	 */
	private function enqueue_assets() {
		wp_enqueue_style( 'wc-product-faq-public-css' );
		wp_enqueue_script( 'wc-product-faq-public-js' );
	}

	/**
	 * Register a custom post type called "wc_product_faq".
	 *
	 * Questions are only displayed inside the product QA tab, so the post type
	 * is not publicly queryable: single question URLs, archives and search
	 * results would otherwise expose the native comment form, which bypasses
	 * the "verified buyer" check applied to answers.
	 *
	 * Callback function for init (action).
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @link https://developer.wordpress.org/reference/functions/register_post_type/
	 */
	public function register_custom_post_type() {
		$args = array(
			'label'               => __( 'Product QA', 'product-qa-for-woocommerce' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'menu_icon'           => 'dashicons-format-chat',
			'supports'            => array( 'title', 'editor', 'author', 'comments' ),
		);

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Close the native WordPress comment form for questions.
	 *
	 * Answers must be submitted through save_answer(), which checks that the
	 * user bought the product. This keeps wp-comments-post.php from being used
	 * to bypass that check. Admins can still reply from the dashboard.
	 *
	 * Callback function for comments_open (filter).
	 *
	 * @since 2.1
	 * @access public
	 *
	 * @param bool        $open    Whether the current post is open for comments.
	 * @param int|WP_Post $post_id The post ID or WP_Post object.
	 *
	 * @return bool Whether comments are open.
	 */
	public function close_native_comments( $open, $post_id ) {
		if ( is_admin() ) {
			return $open;
		}

		if ( get_post_type( $post_id ) === $this->post_type ) {
			return false;
		}

		return $open;
	}

	/**
	 * Check whether the QA feature is enabled for a product.
	 *
	 * The feature must be enabled globally. When categories are selected in
	 * the settings, the product must belong to at least one of them.
	 *
	 * @since 2.1
	 * @access private
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool True when QA is enabled for the product.
	 */
	private function is_enabled_for_product( $product_id ) {
		if ( 'yes' !== get_option( 'wc_product_faq_feature' ) ) {
			return false;
		}

		$categories = get_option( 'wc_product_faq_category_list' );

		// An empty selection enables the feature for all products.
		if ( ! is_array( $categories ) || empty( $categories ) ) {
			return true;
		}

		// WooCommerce stores the multiselect values as strings, so normalise them.
		$categories = array_map( 'absint', $categories );
		$terms      = get_the_terms( $product_id, 'product_cat' );

		if ( ! is_array( $terms ) ) {
			return false;
		}

		foreach ( $terms as $term ) {
			if ( in_array( (int) $term->term_id, $categories, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add the QA tab to WooCommerce product tabs.
	 *
	 * Callback function for woocommerce_product_tabs (filter).
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @ref https://docs.woocommerce.com/document/editing-product-data-tabs/
	 *
	 * @param array $tabs WooCommerce product detail page tabs.
	 *
	 * @return array WooCommerce product detail page tabs.
	 */
	public function faq_tab( $tabs ) {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return $tabs;
		}

		if ( $this->is_enabled_for_product( $product->get_id() ) ) {
			/**
			 * Filters the title of the QA tab on the product page.
			 *
			 * @since 1.0.0
			 *
			 * @param string $tab_name Tab title.
			 */
			$tab_name = apply_filters( 'wc_product_faq_tab_title', __( 'QA', 'product-qa-for-woocommerce' ) );

			$tabs['faq_tab'] = array(
				'title'    => $tab_name,
				'priority' => 50,
				'callback' => array( $this, 'faq_tab_content' ),
			);
		}

		return $tabs;
	}

	/**
	 * Render the QA tab content on the product detail page.
	 *
	 * The template is included (not required once) so the tab can be rendered
	 * more than once per request, e.g. in the Elementor editor preview.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see public/templates/list-questions-answers.php
	 */
	public function faq_tab_content() {
		$this->enqueue_assets();

		include plugin_dir_path( __DIR__ ) . 'public/templates/list-questions-answers.php';
	}

	/**
	 * Save a question for a particular product.
	 *
	 * Callback function for init (action).
	 *
	 * @see    wp_insert_post()
	 * @see    set_question_email()
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function save_question() {
		if ( ! isset( $_POST['wc_ask_question'] ) ) {
			return;
		}

		if ( ! isset( $_POST['wc_ask_question_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wc_ask_question_nonce'] ) ), 'wc_ask_question_action' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! isset( $_POST['product_question'], $_POST['product_id'] ) ) {
			return;
		}

		$question   = sanitize_text_field( wp_unslash( $_POST['product_question'] ) );
		$question   = mb_substr( $question, 0, self::QUESTION_MAX_LENGTH );
		$product_id = absint( wp_unslash( $_POST['product_id'] ) );

		if ( '' === $question || 0 === $product_id ) {
			return;
		}

		// Only accept questions for published products that have QA enabled.
		if ( 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
			return;
		}

		if ( ! $this->is_enabled_for_product( $product_id ) ) {
			return;
		}

		// Each question emails past buyers, so throttle how often a user can ask.
		if ( $this->is_rate_limited( 'question' ) ) {
			return;
		}

		/**
		 * Filters the status given to newly submitted questions.
		 *
		 * @since 1.0.0
		 *
		 * @param string $question_status Post status. Default 'publish'.
		 */
		$question_status = apply_filters( 'wc_product_faq_manage_question_status', 'publish' );

		$question_data = array(
			'post_title'     => $question,
			'post_content'   => $question,
			'post_status'    => $question_status,
			'post_author'    => get_current_user_id(),
			'post_type'      => $this->post_type,
			'post_parent'    => $product_id,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		);

		$post_id = wp_insert_post( $question_data, true );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return;
		}

		// Only notify customers about questions that are visible on the site.
		if ( 'publish' === get_post_status( $post_id ) ) {
			$this->set_question_email( $product_id, $question );
		}

		/**
		 * Fires after a question has been saved.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_product_faq_after_question_added' );

		$this->redirect_back( $product_id );
	}

	/**
	 * Check and record a per-user submission throttle.
	 *
	 * @since 2.1
	 * @access private
	 *
	 * @param string $action Throttled action, either 'question' or 'answer'.
	 *
	 * @return bool True when the user must wait before submitting again.
	 */
	private function is_rate_limited( $action ) {
		/**
		 * Filters the minimum number of seconds between two submissions by the same user.
		 *
		 * @since 2.1
		 *
		 * @param int    $seconds Seconds to wait. Default 30. Return 0 to disable.
		 * @param string $action  Throttled action, either 'question' or 'answer'.
		 */
		$seconds = absint( apply_filters( 'wc_product_faq_submission_interval', 30, $action ) );

		if ( 0 === $seconds ) {
			return false;
		}

		$key = 'wc_product_faq_' . $action . '_' . get_current_user_id();

		if ( false !== get_transient( $key ) ) {
			return true;
		}

		set_transient( $key, time(), $seconds );

		return false;
	}

	/**
	 * Redirect back to the product page after a successful submission.
	 *
	 * Prevents the browser from re-submitting the form on refresh.
	 *
	 * @since 2.1
	 * @access private
	 *
	 * @param int $product_id Product ID used as fallback destination.
	 */
	private function redirect_back( $product_id ) {
		$redirect = wp_get_referer();

		if ( ! $redirect ) {
			$redirect = get_permalink( $product_id );
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Build the email content for a new question.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @see   create_mail()
	 *
	 * @param int    $product_id Current product id.
	 * @param string $question   The submitted question.
	 */
	private function set_question_email( $product_id, $question ) {
		$title        = get_option( 'wc_product_faq_question_title' );
		$subject      = get_option( 'wc_product_faq_question_subject' );
		$content      = '<strong>' . esc_html__( 'Question:', 'product-qa-for-woocommerce' ) . '</strong> ' . esc_html( $question );
		$button_label = get_option( 'wc_product_faq_question_button_text' );

		$this->create_mail( $product_id, $title, $subject, $content, $button_label );
	}

	/**
	 * Build and send the question email.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @see customer_emails()
	 * @see email_template()
	 * @see send_mail()
	 *
	 * @param int    $product_id    Current product id.
	 * @param string $title         Title of email.
	 * @param string $subject       Subject of email.
	 * @param string $content       Content of email.
	 * @param string $button_label  Button label text.
	 */
	private function create_mail( $product_id, $title, $subject, $content, $button_label ) {
		$mail_orders = $this->customer_emails( $product_id );

		if ( ! empty( $mail_orders ) ) {
			$message = $this->email_template( $product_id, $title, $content, $button_label );

			foreach ( $mail_orders as $mail_order ) {
				$this->send_mail( $mail_order['email'], $subject, $message );
			}
		}
	}

	/**
	 * Return the customer email list for a product.
	 *
	 * Orders are loaded in batches so large stores do not load every order
	 * into memory at once.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $product_id Current product id.
	 *
	 * @return array Customer list for the product.
	 */
	private function customer_emails( $product_id ) {
		$cache_key        = 'wc_product_faq_customer_emails_' . $product_id;
		$cached_customers = wp_cache_get( $cache_key, 'wc_product_faq' );

		if ( false !== $cached_customers ) {
			/** This filter is documented below. */
			return apply_filters( 'wc_product_faq_manage_customer_emails', $cached_customers );
		}

		$customers = array();

		/**
		 * Filters the order status whose customers receive question emails.
		 *
		 * @since 1.0.0
		 *
		 * @param string $order_status Order status. Default 'wc-completed'.
		 */
		$order_status = apply_filters( 'wc_product_faq_manage_order_status', 'wc-completed' );
		$status_list  = array( $order_status );

		if ( 'wc-' === substr( $order_status, 0, 3 ) ) {
			$status_list[] = substr( $order_status, 3 );
		} else {
			$status_list[] = 'wc-' . $order_status;
		}

		if ( function_exists( 'wc_get_orders' ) ) {
			$page     = 1;
			$per_page = 200;

			do {
				$orders = wc_get_orders(
					array(
						'limit'    => $per_page,
						'page'     => $page,
						'status'   => array_unique( $status_list ),
						'return'   => 'objects',
						'paginate' => false,
					)
				);

				foreach ( $orders as $order ) {
					if ( ! $order instanceof WC_Order ) {
						continue;
					}

					$matched_product = false;

					foreach ( $order->get_items() as $item ) {
						$item_product_id   = (int) $item->get_product_id();
						$item_variation_id = (int) $item->get_variation_id();

						if ( $item_product_id === $product_id || $item_variation_id === $product_id ) {
							$matched_product = true;
							break;
						}
					}

					if ( ! $matched_product ) {
						continue;
					}

					$order_email = $order->get_billing_email();

					if ( $order_email && ! in_array( $order_email, array_column( $customers, 'email' ), true ) ) {
						$customers[] = array(
							'email'      => $order_email,
							'first_name' => $order->get_billing_first_name(),
							'last_name'  => $order->get_billing_last_name(),
						);
					}
				}

				// A full batch means there may be more orders to load.
				$batch_count = count( $orders );
				++$page;
			} while ( $batch_count === $per_page );
		}

		wp_cache_set( $cache_key, $customers, 'wc_product_faq', 300 );

		/**
		 * Filters the list of customers who receive the question email.
		 *
		 * @since 1.0.0
		 *
		 * @param array $customers List of arrays with 'email', 'first_name' and 'last_name' keys.
		 */
		return apply_filters( 'wc_product_faq_manage_customer_emails', $customers );
	}

	/**
	 * Render the email template.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @see public/templates/email/question.php.
	 *
	 * @param int    $product_id   Current product id.
	 * @param string $title        Title of email.
	 * @param string $content      Content of email.
	 * @param string $button_label Button label text.
	 *
	 * @return string Email body including HTML.
	 */
	private function email_template( $product_id, $title, $content, $button_label ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, Generic.CodeAnalysis.UnusedFunctionParameter.Found -- The parameters are used by the included template.
		ob_start();
		include plugin_dir_path( __DIR__ ) . 'public/templates/email/question.php';
		$template = ob_get_clean();

		return $template;
	}

	/**
	 * Send an email message.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $to      Email address.
	 * @param string $subject Email subject.
	 * @param string $message Email content.
	 */
	private function send_mail( $to, $subject, $message ) {
		$to = sanitize_email( $to );

		if ( ! is_email( $to ) ) {
			return;
		}

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$sender = sanitize_email( get_option( 'wc_product_faq_sender_email' ) );

		if ( is_email( $sender ) ) {
			// Strip line breaks and angle brackets so the name cannot inject extra headers.
			$sender_name = str_replace( array( "\r", "\n", '<', '>', '"' ), '', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
			$headers[]   = sprintf( 'From: "%s" <%s>', $sender_name, $sender );
		}

		wp_mail( $to, sanitize_text_field( $subject ), $message, $headers );
	}

	/**
	 * Save an answer to a particular question.
	 *
	 * Only logged-in customers who bought the product can answer.
	 *
	 * Callback function for init (action).
	 *
	 * @see wp_insert_comment()
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function save_answer() {
		if ( ! isset( $_POST['wc_give_answer'] ) ) {
			return;
		}

		if ( ! isset( $_POST['wc_give_answer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wc_give_answer_nonce'] ) ), 'wc_give_answer_action' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! isset( $_POST['product_answer'], $_POST['question_id'] ) ) {
			return;
		}

		$answer      = sanitize_textarea_field( wp_unslash( $_POST['product_answer'] ) );
		$answer      = mb_substr( $answer, 0, self::ANSWER_MAX_LENGTH );
		$question_id = absint( wp_unslash( $_POST['question_id'] ) );

		if ( '' === $answer || 0 === $question_id ) {
			return;
		}

		// The target must be a published question, not any other post.
		if ( get_post_type( $question_id ) !== $this->post_type || 'publish' !== get_post_status( $question_id ) ) {
			return;
		}

		$product_id = wp_get_post_parent_id( $question_id );

		if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
			return;
		}

		$current_user = wp_get_current_user();

		if ( empty( $current_user->ID ) || ! function_exists( 'wc_customer_bought_product' ) ) {
			return;
		}

		if ( ! wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) {
			return;
		}

		if ( $this->is_rate_limited( 'answer' ) ) {
			return;
		}

		/**
		 * Filters the approval status given to newly submitted answers.
		 *
		 * @since 1.0.0
		 *
		 * @param int|string $answer_status 0 (pending), 1 (approved) or 'spam'. Default 0.
		 */
		$answer_status = apply_filters( 'wc_product_faq_manage_answer_status', 0 );

		$data = array(
			'comment_post_ID'      => $question_id,
			'comment_author'       => $current_user->display_name,
			'comment_author_email' => $current_user->user_email,
			'comment_content'      => $answer,
			'user_id'              => $current_user->ID,
			'comment_date'         => current_time( 'mysql' ),
			'comment_approved'     => $answer_status,
		);

		$comment_id = wp_insert_comment( $data );

		if ( ! $comment_id ) {
			return;
		}

		/**
		 * Fires after an answer has been saved.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_product_faq_after_answer_added' );

		$this->redirect_back( $product_id );
	}
}
