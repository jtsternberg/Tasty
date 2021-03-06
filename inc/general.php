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
 * Query tweaks via pre_get_posts
 *
 * Instead of only showing 10 posts by default (bookmarks), show 100.
 *
 * @since 2.0.0
 * @param object $query
 */
function tasty_pre_get_posts_tweaks( $query ) {
	if ( $query->is_main_query() && ! is_admin() ) {
		$query->set( 'posts_per_page', '100' );
	}
}
add_action( 'pre_get_posts', 'tasty_pre_get_posts_tweaks' );

/**
 * Add post date to posts (bookmarks)
 *
 * @since 2.0.0
 */
function tasty_post_date() {
	the_date( 'j M y', '<div class="day">', '</div>', true );
}
add_action( 'genesis_entry_header', 'tasty_post_date', 1 );

/**
 * Add post class for indention
 *
 * @since  2.0.0
 * @param  array $classes
 * @global $currentday
 * @global $previousday
 * @return array
 */
function tasty_post_class( $classes ) {
	global $currentday, $previousday;
	if ( $currentday == $previousday ) {
		$classes[] = 'same-day';
	}
	return $classes;
}
add_filter( 'post_class', 'tasty_post_class' );

/**
 * Customize entry meta
 *
 * @since  2.0.0
 * @param  string $post_meta
 * @return string
 */
function tasty_entry_meta( $post_meta ) {

	// Tags shortcode
	$post_meta = ' [post_tags sep=" " before=""] [post_categories sep=" " before=""]';

	// Add links to modify the post if user has access
	if ( current_user_can( 'edit_post', get_the_ID() ) ) {
		$links     = '<a href="' . get_edit_post_link() . '" class="edit">' . __( 'Edit', 'ja-tasty-child' ) . '</a>';
		$links    .= ' | <a href="' . get_delete_post_link( get_the_ID(), '', false ) . '" class="delete">' . __( 'Delete', 'ja-tasty-child' ) . '</a>';
		$post_meta = $links . $post_meta;
	}

	return $post_meta;
}
add_filter( 'genesis_post_meta', 'tasty_entry_meta' );

function tasty_tag_archive() {
	if ( is_tag() ) {
		echo '<h1 class="archive-title">' .  __( 'Bookmark Tag: ', 'ja-tasty-child' ) . '<em>' . single_tag_title( '', false ) . '</em></h1>';
	} elseif ( is_category() ) {
		echo '<h1 class="archive-title">' .  __( 'Bookmark Category: ', 'ja-tasty-child' ) . '<em>' . single_tag_title( '', false ) . '</em></h1>';
	}
}
add_action( 'genesis_before_loop', 'tasty_tag_archive', 1 );

/**
 * Search bar area above results
 *
 * @since  2.0.0
 * @global array $wp_query
 */
function tasty_tag_search_area(){
	?>
	<div class="quick-bar">
		<form style="clear:both;" action="<?php echo site_url(); ?>/" method="POST" id="category-search" class="term-search">
			<select name="tag" id="post_tag" class="tagsearchfield" placeholder="<?php _e( 'enter a tag', 'ja-tasty-child' ); ?>" >
				<option value=""><?php _e( 'enter a tag', 'ja-tasty-child' ); ?></option>
			</select>
			<select name="category" id="category" class="categorysearchfield" placeholder="<?php _e( 'enter a category', 'ja-tasty-child' ); ?>" >
				<option value=""><?php _e( 'enter a category', 'ja-tasty-child' ); ?></option>
			</select>
		</form>
		<div class="total-posts">
			<?php echo is_home() ? __( 'Total Bookmarks', 'ja-tasty-child' ) : __( 'Bookmarks', 'ja-tasty-child' ); ?>
			<span class="count"><?php global $wp_query; echo ( is_home() ? wp_count_posts()->publish : $wp_query->found_posts ); ?></span>
			<a href="<?php echo ( isset( $_GET['tag'] ) ? esc_url( $_SERVER["REQUEST_URI"] . '&feed=rss2' ) : get_bloginfo( 'rss2_url' ) ); ?>"><img src="<?php echo CHILD_URL; ?>/images/rss.png" alt="<?php _e( 'RSS', 'ja-tasty-child' ); ?>" /></a>
		</div>
	</div>
	<?php
}
add_action( 'genesis_before_loop', 'tasty_tag_search_area', 3 );

/**
 * Trash alert, if needed
 *
 * @since  2.0.0
 * @author Stephanie Leary
 * @link   http://sillybean.net/code/themes/twenty-links-a-delicious-inspired-child-theme-for-wordpress/
 */
function tasty_trash_alert(){
	if ( isset( $_GET['trashed'] ) ) {
		echo '<p class="notice">';
		$trash_post = get_post( $_GET['ids'] );
		printf( __( '<em>%s</em> has been moved to the <a href="%s">trash</a>.', 'ja-tasty-child' ), $trash_post->post_title, admin_url( '/edit.php?post_status=trash&post_type=post' ) );
		echo '</p>';
	}
}
add_action( 'genesis_before_loop', 'tasty_trash_alert', 2 );

/**
 * Redirect single post pages to the link.
 *
 * @since  2.0.0
 * @author Bill Erickson
 */
function tasty_redirect_on_single() {
	if ( is_single() ) {
		$url = esc_url( tasty_display_url( false ) );
		if ( empty( $url ) ) {
			wp_redirect( site_url() );
		} else {
			wp_redirect( $url, '301' );
		}
		exit;
	}
}
add_action( 'template_redirect', 'tasty_redirect_on_single' );

/**
 * Add search box to header
 *
 * @since 2.0.0
 */
function tasty_add_search() {
	get_search_form();
}
add_action( 'genesis_site_title', 'tasty_add_search' );

/**
 * Customize search form default input value
 *
 * @since  2.0.0
 * @param  string $text
 * @return string
 */
function tasty_custom_search_text( $text ) {
	return esc_attr__( 'Search bookmarks', 'ja-tasty-child' );
}
add_filter( 'genesis_search_text', 'tasty_custom_search_text' );

/**
 * Customize footer to add credit and affilaite link
 *
 * @since  2.0.0
 * @param  string $credits
 * @return striing
 */
function tasty_footer_creds_text( $creds ) {
	$creds = '[footer_copyright] [footer_childtheme_link] by <a href="http://jaredatchison.com">Jared Atchison</a>' . sprintf( __( 'on the <a href="%s">Genesis Framework</a>', 'ja-tasty-child' ), 'http://www.jaredatchison.com/go/genesis/' ) . ' &middot; [footer_wordpress_link] &middot; [footer_loginout]';
	return $creds;
}
add_filter( 'genesis_footer_creds_text', 'tasty_footer_creds_text' );

/**
 * Don't Update Theme.
 *
 * If there is a theme in the repo with the same name, this prevents WP from prompting an update.
 *
 * @since  2.0.0
 * @author Mark Jaquith
 * @link   http://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
 * @param  array $r Existing request arguments
 * @param  string $url Request URL
 * @return array Amended request arguments
 */
function tasty_dont_update_theme( $r, $url ) {
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/themes/update-check' ) ) {
		return $r; // Not a theme update request. Bail immediately.
	}
	$themes = unserialize( $r['body']['themes'] );
	unset( $themes[ get_option( 'template' ) ] );
	unset( $themes[ get_option( 'stylesheet' ) ] );
	$r['body']['themes'] = serialize( $themes );
	return $r;
}
add_filter( 'http_request_args', 'tasty_dont_update_theme', 5, 2 );

function tasty_mod_title_output( $title ) {

	$url = esc_attr( tasty_display_url( false ) );
	$title_attr = 'title="'. $url .'"';

	$title = str_replace( 'class="entry-title"', 'class="entry-title" '. $title_attr, $title );

	if ( false !== strpos( $url, 'javascript:' ) ) {
		$bookmarklet = '<a title="' . __( 'Bookmarklet -- Drag to your Bookmark Bar', 'ja-tasty-child' ) . '" href="'. $url .'">[##]</a> ';
		$title = str_replace( '><a', '>'. $bookmarklet .'<a', $title );

	}
	return $title;
}
add_filter( 'genesis_post_title_output', 'tasty_mod_title_output' );

function tasty_prev_link_text( $text ) {
	$text = '&Larr; ' . __( 'Previous Page', 'ja-tasty-child' );
	return $text;
}
add_filter( 'genesis_prev_link_text', 'tasty_prev_link_text' );

function tasty_next_link_text( $text ) {
	$text = __( 'Next Page', 'ja-tasty-child' ) . ' &Rarr;';
	return $text;
}
add_filter( 'genesis_next_link_text', 'tasty_next_link_text' );


function tasty_breadcrumb_args( $args ) {
	$args['labels']['category'] = $args['labels']['tag'] = __( 'Bookmarks under ', 'ja-tasty-child' );
	return $args;
}
add_filter( 'genesis_breadcrumb_args', 'tasty_breadcrumb_args' );
