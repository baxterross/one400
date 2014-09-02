<?php $pl_option = $this->GetOption('wllogin2postloginsettings'); ?>
<?php $pop_option = $this->GetOption('wllogin2popupsettings'); ?>
<?php $img_option = $this->GetOption('wllogin2floatersettings'); ?>
<?php $layouts = $this->Get_Layouts(); ?>
<?php $skins = $this->Get_Skins(); ?>
<h2><?php _e('WishList Login 2.0 &raquo; Display Settings','wishlist-login2'); ?></h2>
<form method="post">
	<h3>Post Login Display Settings</h3>
	<p class="wl_setting_title">Select a Layout:<?php echo $this->Tooltip("settings-tooltips-postlogin-layout"); ?></p>
	<p class="wl_setting_description">
	Set the layout you'd like to use for the Post Login form.
	</p>
	<div class="wllogin2-content">
		<p>
			<?php foreach ( $layouts as $value=>$text ) : ?>
				<input <?php checked($value, $pl_option['layout']); ?> type="radio" class="wl_login_radio_layout" id="wllogin_postlogin_radio_layout_<?php echo $value; ?>" name="wllogin2postloginsettings[layout]" value="<?php echo $value; ?>" />
				<label style="background-image: url('<?php echo $text['img']['url']; ?>'); width: <?php echo $text['img']['width']; ?>px; height: <?php echo $text['img']['height']; ?>px; background-size: <?php echo $text['img']['width']; ?>px <?php echo $text['img']['height']; ?>px;" for="wllogin_postlogin_radio_layout_<?php echo $value; ?>"><span><?php echo $text['text']; ?></span></label>
			<?php endforeach; ?>
		</p>
	</div>
	<p class="wl_setting_title">Select a Color:<?php echo $this->Tooltip("settings-tooltips-postlogin-color"); ?></p>
	<p class="wl_setting_description">
	Set the color scheme you'd like to use for the Post Login form.
	</p>
	<div class="wllogin2-content">
		<p>
			<?php foreach ( $skins as $value=>$text ) : ?>
				<input <?php checked($value, $pl_option['skin']); ?> type="radio" class="wl_login_radio_skin" id="wllogin_postlogin_radio_skin_<?php echo $value; ?>" name="wllogin2postloginsettings[skin]" value="<?php echo $value; ?>" />
				<label style="background-color: <?php echo '#' . $text['hex']; ?>;" class="wl_login_skin_label" for="wllogin_postlogin_radio_skin_<?php echo $value; ?>"><span><?php echo $text['text']; ?></span></label>
			<?php endforeach; ?>
		</p>
	</div>
	<h3>Pop-Up Display Settings</h3>
	<p class="wl_setting_title">Select a Layout:<?php echo $this->Tooltip("settings-tooltips-popup-layout"); ?></p>
	<p class="wl_setting_description">
	Set the layout you'd like to use for the Popup form.
	</p>
	<div class="wllogin2-content">
		<p>
			<?php foreach ( $layouts as $value=>$text ) : ?>
				<input <?php checked($value, $pop_option['layout']); ?>type="radio" class="wl_login_radio_layout" id="wllogin_popup_radio_layout_<?php echo $value; ?>" name="wllogin2popupsettings[layout]" value="<?php echo $value; ?>" />
				<label style="background-image: url('<?php echo $text['img']['url']; ?>'); width: <?php echo $text['img']['width']; ?>px; height: <?php echo $text['img']['height']; ?>px; background-size: <?php echo $text['img']['width']; ?>px <?php echo $text['img']['height']; ?>px;" for="wllogin_popup_radio_layout_<?php echo $value; ?>"><span><?php echo $text['text']; ?></span></label>
			<?php endforeach; ?>
		</p>
	</div>
	<p class="wl_setting_title">Select a Color:<?php echo $this->Tooltip("settings-tooltips-popup-color"); ?></p>
	<p class="wl_setting_description">
	Set the color scheme you'd like to use for the Popup form.
	</p>
	<div class="wllogin2-content">
		<p>
			<?php foreach ( $skins as $value=>$text ) : ?>
				<input <?php checked($value, $pop_option['skin']); ?> type="radio" class="wl_login_radio_skin" id="wllogin_popup_radio_skin_<?php echo $value; ?>" name="wllogin2popupsettings[skin]" value="<?php echo $value; ?>" />
				<label style="background-color: <?php echo '#' . $text['hex']; ?>;" class="wl_login_skin_label" for="wllogin_popup_radio_skin_<?php echo $value; ?>"><span><?php echo $text['text']; ?></span></label>
			<?php endforeach; ?>
		</p>
	</div>
	<h3>Floating Image Display Settings</h3>
	<p class="wl_setting_title">Set the floating image display:<?php echo $this->Tooltip("settings-tooltips-floating-image-display"); ?></p>
	<p class="wl_setting_description">
	Select whether or not to show the floating image.
	</p>
	<div class="wllogin2-content">
		<input <?php checked(1, $img_option['display']); ?> type="radio" class="wl_login_radio_floater" id="wllogin_popup_radio_floater_show" name="wllogin2floatersettings[display]" value="1" />
		<label class="wl_login_radio_floater" for="wllogin_popup_radio_floater_show"><span>Show</span></label>
		<span class="wl_login_spacer">&nbsp;</span>
		<input <?php checked(0, $img_option['display']); ?> type="radio" class="wl_login_radio_floater" id="wllogin_popup_radio_floater_dont_show" name="wllogin2floatersettings[display]" value="0" />
		<label class="wl_login_radio_floater" for="wllogin_popup_radio_floater_dont_show"><span>Don't Show</span></label>
	</div>
	<p class="wl_setting_description">
	Hide when logged in
	</p>
	<div class="wllogin2-content">
		<input <?php checked(1, $img_option['hide_if_logged_in']); ?> type="radio" class="wl_login_radio_floater" id="wllogin_popup_radio_floater_show" name="wllogin2floatersettings[hide_if_logged_in]" value="1" />
		<label class="wl_login_radio_floater" for="wllogin_popup_radio_floater_show"><span>Yes</span></label>
		<span class="wl_login_spacer">&nbsp;</span>
		<input <?php checked(0, $img_option['hide_if_logged_in']); ?> type="radio" class="wl_login_radio_floater" id="wllogin_popup_radio_floater_dont_show" name="wllogin2floatersettings[hide_if_logged_in]" value="0" />
		<label class="wl_login_radio_floater" for="wllogin_popup_radio_floater_dont_show"><span>No</span></label>
	</div>
	<p class="wl_setting_title">Set the background color:<?php echo $this->Tooltip("settings-tooltips-floating-image-color"); ?></p>
	<p class="wl_setting_description">
	Set the background color for the floating image.
	</p>
	<div class="wllogin2-content">
		<input name="wllogin2floatersettings[background_color]" type="text" class="wllogin_colorpicker" value="<?php echo $img_option['background_color']; ?>" />
		<div class="wllogin_colorpicker_trigger"></div>
	</div>

	<p class="wl_setting_title">Set the text:<?php echo $this->Tooltip("settings-tooltips-floating-image-text"); ?></p>
	<p class="wl_setting_description">
	Set the text for the floating image.
	</p>
	<div class="wllogin2-content">
		<input name="wllogin2floatersettings[text]" type="text" class="wllogin_text" value="<?php echo $img_option['text']; ?>" />
	</div>
	<p class="wl_setting_description">
	Set the logout text for the floating image.
	</p>
	<div class="wllogin2-content">
		<input name="wllogin2floatersettings[logout_text]" type="text" class="wllogin_text" value="<?php echo $img_option['logout_text']; ?>" />
	</div>
	<input type="hidden" name="WLOptions" value="wllogin2postloginsettings,wllogin2popupsettings,wllogin2floatersettings,<?php echo $this->Options(false); ?>"/>
	<input type="hidden" name="WishListLogin2Action" value="Save" />
	<?php submit_button('Save Settings'); ?>
</form>
<h3>Shortcodes</h3>
<div class="wllogin2-content">
	<p><strong>Use the shortcodes below to embed a form into a post/page.</strong></p>
	<ul class="wl_login_needs_style">
		<li>[wllogin2_button]</li>
		<li>[wllogin2_full]</li>
		<li>[wllogin2_compact]</li>
		<li>[wllogin2_horizontal]</li>
	</ul>
	<p><strong>Add the "skin" parameter to any shortcode to change the color scheme:</strong></p>
	<ul class="wl_login_needs_style">
		<li>[wllogin2_full skin="blue"]</li>
	</ul>
	<p><strong>The following are accepted values for the "skin" parameter</strong></p>
	<ul class="wl_login_needs_style">
		<li>black</li>
		<li>blue</li>
		<li>teal</li>
		<li>light_green</li>
		<li>green</li>
		<li>orange</li>
		<li>purple</li>
		<li>pink</li>
		<li>red</li>
		<li>yellow</li>
	</ul>
	<p>Black is the default.</p>
</div>