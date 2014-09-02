<div class="wl_login_horizontal_form wl_login_skin_<?php echo $skin; ?>">
	 <?php $login_url = esc_url(site_url( 'wp-login.php', 'login_post' )); ?>
	 <form method="post" action="<?php echo $login_url; ?>">
                <?php do_action( 'login_form' ); ?>
		<div class="wl_login_horizontal_form_container">
			<div class="wl_login_horizontal_inputs">
				<div class="wl_login_horizontal_form_input_container" id="wl_login_username_input_container">
					<input class="wl_login_input_text" id="wl_login_input_username" type="text" name="log" placeholder="Username" value="">
				</div>
				<div class="wl_login_horizontal_form_input_container" id="wl_login_password_input_container">
					<input class="wl_login_input_text" id="wl_login_input_password" type="password" name="pwd" placeholder="Password" value="">
				</div>
			</div>
			<div class="wl_login_horizontal_submit">
				<div class="wl_login_horizontal_form_input_container" id="wl_login_submit_input_container">
					<input class="wl_login_input_submit" id="wl_login_input_submit" type="submit" value="<?php _e('Login', 'wishlist-login2'); ?>" />
				</div>
			</div>
		</div>
		<div class="wl_login_horizontal_footer">
			<?php if($enable_socials): ?>
			<div class="wl_login_social_links_container">
				<ul class="wl_login_social_links">
					<?php if($include_twitter): ?>
						<li class="wl_login_social_link" id="wl_login_social_link_twitter">
							<a href="<?php echo $this->create_service_login_uri('twitter', $redirect_to)?>"><span><?php _e('Login with Twitter', 'wishlist-login2'); ?></span></a>
						</li>
					<?php endif; ?>
					<?php if($include_facebook): ?>
						<li class="wl_login_social_link" id="wl_login_social_link_facebook">
							<a href="<?php echo $this->create_service_login_uri('facebook', $redirect_to)?>"><span><?php _e('Login with Facebook', 'wishlist-login2'); ?></span></a>
						</li>
					<?php endif; ?>
					<?php if($include_google): ?>
						<li class="wl_login_social_link" id="wl_login_social_link_google">
							<a href="<?php echo $this->create_service_login_uri('google', $redirect_to)?>"><span><?php _e('Login with Google', 'wishlist-login2'); ?></span></a>
						</li>
					<?php endif; ?>
					<?php if($include_linkedin): ?>
						<li class="wl_login_social_link" id="wl_login_social_link_linkedin">
							<a href="<?php echo $this->create_service_login_uri('linkedin', $redirect_to)?>"><span><?php _e('Login with LinkedIn', 'wishlist-login2'); ?></span></a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="wl_login_horizontal_reset_pass">
				<p><a href="<?php echo wp_lostpassword_url(); ?>"><?php _e('Lost your password?', 'wishlist-login2'); ?></a></p>
			</div>
			<?php do_action('wllogin2_afterform') ?>
		</div>
		<input type="hidden" name="redirect_to" value="<?php echo $redirect_to?>"/>
	</form>
</div>