<?php $option = $this->GetOption('wllogin2popupsettings'); ?>
<?php $layouts = $this->layouts; ?>
<?php $skins = $this->skins; ?>
<h2><?php _e('WishListLogin &raquo; Display Settings &raquo; Popup','wishlist-login2'); ?></h2>
<form method="post">
	<h3>Select the layout to use:</h3>
	<div class="wllogin2-content">
		<p>
		<select class="" id="wllogin_popup_select_layout" name="wllogin2popupsettings[layout]">
			<?php foreach ( $layouts as $value=>$text ) : ?>
				<option value="<?php echo $value; ?>" <?php selected($value, $option['layout']); ?>><?php echo $text; ?></option>
			<?php endforeach; ?>
		</select>
		</p>
	</div>
	<h3>Select a color use:</h3>
	<div class="wllogin2-content">
		<p>
		<select class="" id="wllogin_popup_select_skin" name="wllogin2popupsettings[skin]">
			<?php foreach ( $skins as $value=>$text ) : ?>
				<option value="<?php echo $value; ?>" <?php selected($value, $option['skin']); ?>><?php echo $text; ?></option>
			<?php endforeach; ?>
		</select>
		</p>
	</div>
	<input type="hidden" name="WLOptions" value="wllogin2popupsettings,<?php echo $this->Options(false); ?>"/>
	<input type="hidden" name="WishListLogin2Action" value="Save" />
	<?php submit_button('Save Settings'); ?>
</form>