<?php

class WishListLogin2Widget extends WP_Widget {
	function __construct() {
		// widget actual processes
        parent::WP_Widget( 'wishlistlogin2_widget', 'WishList Login 2.0', array( 'description' => 'A widget that allows users to login using their facebook/twitter or linkedin account'));
	}

	function form($instance) {
        global $WishListLogin2Instance;
        // outputs the options form on admin
        $defaults = array(
            'title_logged_in'       => 'Membership Detail',
            'title_logged_out'      => 'Login Status',
			'layout' => 'compact',
			'skin' => 'black',
            'show_on_login'         => 0,
            'include_facebook'      => 1,
            'include_twitter'       => 1,
            'include_linkedin'      => 1,
            'include_google'        => 1
        );
		
        $instance = wp_parse_args( (array) $instance, $defaults);
        ?>
        <p>
			<label for="<?php echo $this->get_field_id('title_logged_in') ?>"><?php echo __('Title when logged in:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title_logged_in') ?>" name="<?php echo $this->get_field_name( 'title_logged_in' ); ?>" type="text" value="<?php echo attribute_escape( $instance['title_logged_in'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('title_logged_out') ?>"><?php echo __('Title when logged out:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title_logged_out') ?>" name="<?php echo $this->get_field_name( 'title_logged_out' ); ?>" type="text" value="<?php echo attribute_escape( $instance['title_logged_out'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('layout') ?>"><?php echo __('Select a layout:'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('layout') ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>">
				<?php $layouts = $WishListLogin2Instance->layouts; ?>
				<?php foreach ( $layouts as $value=>$text ) : ?>
				<option value="<?php echo $value; ?>" <?php selected($value, $instance['layout']); ?>><?php echo $text['text']; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('skin') ?>"><?php echo __('Select a color:'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('skin') ?>" name="<?php echo $this->get_field_name( 'skin' ); ?>">
				<?php $skins = $WishListLogin2Instance->skins; ?>
				<?php foreach ( $skins as $value=>$text ) : ?>
				<option value="<?php echo $value; ?>" <?php selected($value, $instance['skin']); ?>><?php echo $text['text']; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
        <ul>
			<li>
				<input <?php checked($instance['show_on_login'], 1); ?> type="checkbox" id="<?php echo $this->get_field_id('show_on_login') ?>" name="<?php echo $this->get_field_name( 'show_on_login' ); ?>" type="text" value="1" />
				<label for="<?php echo $this->get_field_id('show_on_login') ?>"><?php  echo __('Only display if member is logged out'); ?></label>
			</li>
			<li>
				<input <?php checked($instance['include_facebook'], 1); ?> type="checkbox" id="<?php echo $this->get_field_id('include_facebook') ?>" name="<?php echo $this->get_field_name( 'include_facebook' ); ?>" type="text" value="1" />
				<label for="<?php echo $this->get_field_id('include_facebook') ?>"><?php  echo __('Enable Facebook login'); ?></label>
			</li>
			<li>
				<input <?php checked($instance['include_twitter'], 1); ?> type="checkbox" id="<?php echo $this->get_field_id('include_twitter') ?>" name="<?php echo $this->get_field_name( 'include_twitter' ); ?>" type="text" value="1" />
				<label for="<?php echo $this->get_field_id('include_twitter') ?>"><?php  echo __('Enable Twitter login'); ?></label>
			</li>
			<li>
				<input <?php checked($instance['include_linkedin'], 1); ?> type="checkbox" id="<?php echo $this->get_field_id('include_linkedin') ?>" name="<?php echo $this->get_field_name( 'include_linkedin' ); ?>" type="text" value="1" />
				<label for="<?php echo $this->get_field_id('include_linkedin') ?>"><?php  echo __('Enable LinkedIn login'); ?></label>
			</li>
			<li>
				<input <?php checked($instance['include_google'], 1); ?> type="checkbox" id="<?php echo $this->get_field_id('include_google') ?>" name="<?php echo $this->get_field_name( 'include_google' ); ?>" type="text" value="1" />
				<label for="<?php echo $this->get_field_id('include_google') ?>"><?php  echo __('Enable Google login'); ?></label>
			</li>
		</ul>
        <?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
        $instance['title_logged_in'] = $new_instance['title_logged_in'];
        $instance['title_logged_out'] = $new_instance['title_logged_out'];
		$instance['show_on_login'] = $new_instance['show_on_login'];
        $instance['include_facebook'] = $new_instance['include_facebook'];
        $instance['include_twitter'] = $new_instance['include_twitter'];
        $instance['include_linkedin'] = $new_instance['include_linkedin'];
        $instance['include_google'] = $new_instance['include_google'];
        $instance['layout'] = $new_instance['layout'];
		$instance['skin'] = $new_instance['skin'];
		
		return $instance;
	}

    /** currently disabled **/
	function widget($args, $instance) {		
		if ( $instance['show_on_login'] ) {
			if ( is_user_logged_in() ) {
				return;
			}
		}
		
        extract( $args );

        $title = $instance['title_logged_out'];

        if(is_user_logged_in()) {
            $title = $instance['title_logged_in'];
        }

        echo $before_widget;

        if($title)
            echo $before_title . $title . $after_title;

        if(!is_user_logged_in()) {
            global $WishListLogin2Instance;

            $settings = $WishListLogin2Instance->prepare_form_settings();
			$settings['include_facebook'] = $instance['include_facebook'];
			$settings['include_google'] = $instance['include_google'];
			$settings['include_twitter'] = $instance['include_twitter'];
			$settings['include_linkedin'] = $instance['include_linkedin'];
			$settings['skin'] = $instance['skin'];
            $settings['form_type'] = $instance['layout'];
			
            echo $WishListLogin2Instance->render_form($settings);
        } else {
            $this->display_membership_details($instance);
        }
		
        echo $after_widget;
	}
    public function display_membership_details($settings) {
        global $WishListMemberInstance, $current_user;
        $wpm_current_user = $current_user;
        $name = $wpm_current_user->first_name;
        if (!$name) {
            $name = $wpm_current_user->user_nicename;
        }
        if (!$name) {
            $name = $wpm_current_user->user_login;
        }

        echo '<p>'.trim(sprintf(__('Welcome %1$s', 'wishlist-social-login'), $name)) .'</p>';
        if(!$api = new WLMAPI()) {
            echo "This plugin requires wishlist-member";
            return;
        }
        $wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');
        $levels = $WishListMemberInstance->GetMembershipLevels($wpm_current_user->ID, null, null, null, true);
        $inactivelevels = $WishListMemberInstance->GetMemberInactiveLevels($wpm_current_user->ID);
        sort($levels); // <- we sort the levels

        if (!$settings['hide_levels']) {
            $clevels = count($levels);
            if ($clevels) {
                echo __("&raquo; Level", "&raquo; Levels", $clevels, 'wishlist-social-login');
                echo ': ';
                if ($clevels > 1)
                    echo '<br /><div id="" style="margin-left:1em">';
                    $morelevels = false;
                    $maxmorelevels = $return ? 1000000000 : 2;
                    for ($i = 0; $i < $clevels; $i++) {
                        if ($i > $maxmorelevels && !$morelevels) {
                            echo '<div id="wlm_morelevels" style="display:none">';
                            $morelevels = true;
                        }
                        if ($clevels > 1 ) {
                            echo '&middot; ';
                            $strike = '';
                        }
                        if (in_array($levels[$i], $inactivelevels)) {
                            echo '<strike>';
                            $strike = '</strike>';
                        }
                        echo $wpm_levels[$levels[$i]]['name'];
						echo $strike;
                        echo '<br />';
                    }
                    if ($morelevels) {
                        echo '</div>';
						echo '&middot; <label style="cursor:pointer;" onclick="wlmml=document.getElementById(\'wlm_morelevels\');wlmml.style.display=wlmml.style.display==\'none\'?\'block\':\'none\';this.innerHTML=wlmml.style.display==\'none\'?\'' . __('More levels', 'wishlist-social-login') . ' <small>&nabla;</small>\':\'' . __('Less levels', 'wishlist-social-login') . ' <small>&Delta;</small>\';this.blur()">' . __('More levels', 'wishlist-social-login') . ' <small>&nabla;</small></label>';
                    }
                    if ($clevels > 1) {
                        echo '</div>';
                    }
                }
            }

            if (function_exists('wp_logout_url')) {
                $logout = wp_logout_url(get_bloginfo('url'));
            } else {
                $logout = wp_nonce_url(site_url('wp-login.php?action=logout&redirect_to=' . urlencode(get_bloginfo('url')), 'login'), 'log-out');
            }
            echo '&raquo; <a href="' . $logout . '">' . __('Logout', 'wishlist-social-login') . '</a><br />';
    }
}
