/**
 * Public-facing js.
 */
jQuery( function ( $ ) {
	'use strict';

	/**
	 * Toggle the answer form for a question.
	 *
	 * Delegated from the document so it also works when the QA tab markup is
	 * rendered after page load, e.g. by page builders such as Elementor.
	 *
	 * Markup @'public/templates/list-questions-answers.php
	 */
	$( document ).on( 'click', '.answer-button', function ( event ) {
		var $button = $( this );
		var id = parseInt( $button.attr( 'data-id' ), 10 );

		event.preventDefault();

		if ( ! id ) {
			return;
		}

		$( '#question-' + id ).slideToggle( 'slow' );
		$button.attr( 'aria-expanded', 'true' === $button.attr( 'aria-expanded' ) ? 'false' : 'true' );
	} );
} );
