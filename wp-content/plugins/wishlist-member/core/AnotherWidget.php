<?php

/**
 * Description of AnotherWidget
 * Create clone of WishList Member login widget.
 *
 * @author Andy
 * Version: 1.00
 */
class AnotherWidget extends WP_Widget {

	/**
	 * constructor
	 */
	function AnotherWidget() {
		$widget_ops = array('classname' => 'Wishlist_Member_Login', 'description' => __('Another WishList Member Login'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('Wishlist_Member_Login', __('Another WishList Member'), $widget_ops, $control_ops);
	}

	/**
	 * display widget
	 */
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		$args["title"] = $instance['title'];
		$args["title2"] = $instance['title2'];
		global $WishListMemberInstance;
		$WishListMemberInstance->Widget($args);
	}

	function form($instance) {

		$instance = wp_parse_args((array) $instance, array('title' => '', 'text' => ''));
		$title = strip_tags($instance['title']);
		$text = format_to_edit($instance['text']);



		$field_id_title = $this->get_field_id('title');
		$field_name_title = $this->get_field_name('title');

		$field_id_title2 = $this->get_field_id('title2');
		$field_name_title2 = $this->get_field_name('title2');

		$default = array('title' => 'Membership Detail ' . $this->number);
		$default = array('title' => 'Login Status ' . $this->number);
		$instance2 = wp_parse_args((array) $instance, $default);
		?>
		<p><label for="'.$field_id_title.'"><?php echo __('Title when logged in:'); ?></label>
			<input class="widefat" id="<?php echo $field_id_title ?>" name="<?php echo $field_name_title ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>	

		<p><label for="'.$field_id_title2.'"><?php echo __('Title when logged out::'); ?></label>
			<input class="widefat" id="<?php echo $field_id_title2 ?>" name="<?php echo $field_name_title2 ?>" type="text" value="<?php echo esc_attr($instance['title2']); ?>" />
		</p>

		<?php
		//echo "\r\n".'<p><label for="'.$field_id_title.'">'.__('Title when logged in:').': <input type="text" class="widefat" id="'.$field_id_title.'" name="'.$field_name_title.'" value="'.esc_attr( $instance['title'] ).'" /><label></p>';
		//echo "\r\n".'<p><label for="'.$field_id_title2.'">'.__('Title when logged out:').': <input type="text" class="widefat" id="'.$field_id_title2.'" name="'.$field_name_title2.'" value="'.esc_attr( $instance['title2'] ).'" /><label></p>';
	}

}
?>
