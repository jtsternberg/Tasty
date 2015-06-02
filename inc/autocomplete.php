<?php
/**
 * Tasty child theme.
 *
 * @package      TastyChildTHeme
 * @since        2.0.0
 * @copyright    Copyright (c) 2013, Jared Atchison
 * @author       Jared Atchison <contact@jaredatchison.com>
 * @license      GPL-2.0+
 */

/**
 * Enqueue the javascript for autocomplete
 *
 * @since 2.0.0
 */
function tasty_add_jquery() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'suggest' );

	wp_enqueue_script( 'select2', CHILD_URL . "/js/select2/js/select2.min.js", array( 'jquery' ), '4.0.0', true );
	wp_enqueue_style( 'select2', CHILD_URL . '/js/select2/css/select2.css', array(), '4.0.0' );

	wp_enqueue_script( 'tasty-select2', CHILD_URL . '/js/global.js', array( 'select2' ), CHILD_THEME_VERSION );
	wp_localize_script( 'tasty-select2', 'tasty_l10n', array(
		'ajaxurl'          => admin_url( 'admin-ajax.php' ),
		'placeholder_text' => __( 'enter a category', 'ja-tasty-child' ),
		'nonce'            => wp_create_nonce( 'ja-tasty-child' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'tasty_add_jquery' );

/**
 * Add tag auto complete
 *
 * @since 2.0.0
 */
function tasty_add_autosuggest() {
	?>
    <script type="text/javascript">
    function setSuggest(id) {
        jQuery('#' + id).suggest("<?php echo admin_url( '/admin-ajax.php?action=ajax-tag-search&tax=post_tag' ); ?>", { delay: 500, minchars: 2, multiple: false });
    }
    </script>
	<?php
}
add_action( 'wp_head', 'tasty_add_autosuggest' );

// Enables autocompletion for non-logged-in users
add_action( 'wp_ajax_nopriv_ajax-tag-search', 'tasty_add_autosuggest_links_callback' );
add_action( 'wp_ajax_ajax-tag-search', 'tasty_add_autosuggest_links_callback' );

add_action( 'wp_ajax_nopriv_tasty_ajax_category_search', 'tasty_ajax_category_search' );
add_action( 'wp_ajax_tasty_ajax_category_search', 'tasty_ajax_category_search' );

/**
 * Modified from admin-ajax.php
 *
 * @since  2.0.0
 * @global $wpdb
 */
function tasty_add_autosuggest_links_callback() {

	global $wpdb;

	if ( isset( $_GET['tax'] ) ) {
		$taxonomy = sanitize_key( $_GET['tax'] );
		$tax = get_taxonomy( $taxonomy );
		if ( ! $tax )
			die( '0' );
	} else {
		die('0');
	}

	$s = stripslashes( $_GET['q'] );

	if ( false !== strpos( $s, ',' ) ) {
		$s = explode( ',', $s );
		$s = $s[count( $s ) - 1];
	}
	$s = trim( $s );
	if ( strlen( $s ) < 2 )
		die; // require 2 chars for matching

	$results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . $wpdb->esc_like( $s ) . '%' ) );

	echo join( $results, "\n" );

}

/**
 * Modified from admin-ajax.php
 *
 * @since  2.0.0
 * @global $wpdb
 */
function tasty_ajax_category_search() {
	global $wpdb;

	$security_check_passes = (
		isset( $_GET['nonce'], $_GET['q'] )
		&& wp_verify_nonce( $_GET['nonce'],  'ja-tasty-child' )
	);

	if ( ! $security_check_passes ) {
		wp_send_json_error( $_GET );
	}

	$tax = isset( $_GET['tax'] ) ? sanitize_text_field( $_GET['tax'] ) : 'category';
	$s = stripslashes( $_GET['q'] );

	if ( false !== strpos( $s, ',' ) ) {
		$s = explode( ',', $s );
		$s = $s[count( $s ) - 1];
	}

	$s = trim( $s );

	if ( strlen( $s ) < 2 ) {
		wp_send_json_error( $_GET );
	}

	// add our term clause filter for this iteration
	add_filter( 'terms_clauses', 'tasty_wilcard_term_name' );
	// do term search
	$terms = get_terms( $tax, array(
		'number' => 10,
		'name__like' => sanitize_text_field( $s ),
		'cache_domain' => 'tasty_search',
	) );
	// and remove the filter
	remove_filter( 'terms_clauses', 'tasty_wilcard_term_name' );

	if ( empty( $terms ) ) {
		wp_send_json_error( $_GET );
	}

	$_terms = array();
	foreach ( $terms as $term ) {
		$_terms[] = array(
			'term_id' => $term->term_id,
			'name' => 'category' == $tax ? tasty_category_name_breadcrumb( $term->term_id ) : $term->name,
			// 'name' => $term->name,
			'url' => 'category' == $tax ? get_category_link( $term->term_id ) : get_tag_link( $term->term_id ),
		);
	}

	wp_send_json_success( $_terms );
}

/**
 * Make term search wildcard on front as well as back
 */
function tasty_wilcard_term_name( $clauses ) {
	// add wildcard flag to beginning of term
	$clauses['where'] = str_replace( "name LIKE '", "name LIKE '%", $clauses['where'] );
	return $clauses;
}

function tasty_category_name_breadcrumb( $term_id ) {
	$all_terms = tasty_category_get_parents( $term_id, true );
	$name_breadcrumb = '';
	$names = wp_list_pluck( $all_terms, 'name' );
	return implode( ' / ', array_reverse( $names ) );
}

function tasty_category_get_parents( $term_id, $reset = false ) {
	static $terms = array();
	if ( $reset ) {
		$terms = array();
	}

	$term = get_term( $term_id, 'category' );
	$terms[] = $term;

	if ( $term->parent ) {
		tasty_category_get_parents( $term->parent );
	}

	return $terms;
}
