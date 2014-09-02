<h2><?php _e('WishListLogin &raquo; Display Settings &raquo; General','wishlist-login2'); ?></h2>
<form method="post">
<!-- your form goes here -->
<p class="submit">
	<?php $this->Options(); $this->RequiredOptions(); ?>
	<input type="hidden" name="WishListLogin2Action" value="Save" />
	<input type="submit" value="<?php _e('Save Settings','wishlist-login2'); ?>" />
</p>
</form>