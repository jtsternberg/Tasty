<?php
/**
 * Widget API: Tasty_Pressthis_Bookmarklet_Widget class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Implement a widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class Tasty_Pressthis_Bookmarklet_Widget extends WP_Widget {

	/**
	 * Sets up a new widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'tasty-pressthis-bookmarklet',
			'description' => __( 'Adds the WordPress Pressthis bookmarklet button.' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'tasty-pressthis-bookmarklet', __( 'Pressthis bookmarklet', 'ja-tasty-child' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current widget instance.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'        => '',
			'button_title' => __( 'Press This' ),
		) );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<p class="pressthis-bookmarklet-wrapper">
			<a class="pressthis-bookmarklet" onclick="return false;" href="<?php htmlspecialchars( get_shortcut_link() ); ?>" title="<?php esc_attr_e( 'Drag to your Bookmark Bar', 'ja-tasty-child' ); ?>"><span><?php echo $instance['button_title']; ?></span></a>
		</p>
		<?php

		echo $args['after_widget'];
	}

	/**
	 * Outputs the settings form for the widget.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'        => esc_attr__( 'Drag to your Bookmark Bar', 'ja-tasty-child' ),
			'button_title' => __( 'Press This' ),
		) );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ja-tasty-child' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'button_title' ); ?>"><?php _e( 'Button Title:', 'ja-tasty-child' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'button_title' ); ?>" name="<?php echo $this->get_field_name( 'button_title' ); ?>" type="text" value="<?php echo esc_attr( $instance['button_title'] ); ?>" /></label></p>
		<?php
	}

	/**
	 * Handles updating settings for the current widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$defaults = array(
			'title' => esc_attr__( 'Drag to your Bookmark Bar', 'ja-tasty-child' ),
			'button_title' => __( 'Press This' ),
		);
		$new_instance = wp_parse_args( (array) $new_instance, $defaults );

		foreach ( $defaults as $key => $value ) {
			$instance[ $key ] = wp_kses_post( $new_instance[ $key ] );
		}

		return $instance;
	}

}

function tasty_register_pressthis_bookmarklet_widget() {
	register_widget( 'Tasty_Pressthis_Bookmarklet_Widget' );
}
add_action( 'widgets_init', 'tasty_register_pressthis_bookmarklet_widget' );
