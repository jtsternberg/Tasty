window.Tasty = window.Tasty || {};

(function(window, document, app, $, undefined){
	'use strict';

	var l10n = window.tasty_l10n;

	app.init = function() {
		var $form = $( document.getElementById( 'category-search' ) );
		$form.find( '#category' ).select2( app.select2_ajax_data( 'category' ) ).on( 'change', app.redirect );
		$form.find( '#post_tag' ).select2( app.select2_ajax_data( 'post_tag' ) ).on( 'change', app.redirect );
	};

	app.redirect = function() {
		window.location.href = $(this).val();
	};

	app.select2_ajax_data = function( tax ) {
		return {
			// placeholder : l10n.placeholder_text,
			minimumInputLength: 2,
			ajax: {
				url : l10n.ajaxurl,
				cache : false,
				dataType : 'json',
				delay : 250,
				data : function( params ) {
					return {
						q      : params.term,
						action : 'tasty_ajax_category_search',
						nonce  : l10n.nonce,
						tax    : tax,
					};
				},
				processResults: app.handle_results,
			}
		}
	};
	// Handle setting up our ajax data
	// app.select2_ajax_data = ;

	// Handle our ajax results
	app.handle_results = function( response ) {

		// return early on ajax failure, undefined data, or empty data
		if ( ! response.success || ! response.data || ! response.data.length ) {
			console.warn( 'app.handle_results response.data', response.data );
			return { results: [] };
		}

		var terms = [];

		$.each( response.data, function( i, term ) {
			var new_term = {
				'id'      : term.url,
				'text'    : term.name,
			};

			terms.push( new_term );
		});

		return { results: terms };
	};

	$( app.init );

})(window, document, Tasty, jQuery);
