<?php if ($show_page_menu) : ?>
<?php return;
endif; ?>
<?php
$latest_wpm_ver = $this->Plugin_Latest_Version();
if (!$latest_wpm_ver)
	$latest_wpm_ver = $this->Version;

$reversion = explode(".", $this->Version);
$wlm_version = $reversion[0] . '.' . $reversion[1];
$wlm_build = $reversion[2];
?>
<h2><?php _e('WishList Login 2.0 Dashboard', 'wishlist-login2'); ?></h2>
<div id="dashboard-widgets-wrap">

	<div id="dashboard-widgets" class="metabox-holder">
		<div class='postbox-container' style='width:49%;margin-right:1%'><!-- BEGIN LEFT POSTBOX CONTAINER -->


			<!-- BEGIN NEW POSTBOX -->
			<div id="wl_dashboard_right_now" class="postbox">
				<h3><span><?php _e('Overview', 'wishlist-login2'); ?></span></h3>
				<div class="inside"><!-- begin inside content -->

					<div class="table table_content">
						<p class="sub">Description</p>
						<p>WishList Login 2.0 combines the functionality of WishList Login, WishList Post Login, and WishList Social Login.</p>
						<p>Now, you can display socially-enabled logins throughout your membership site... giving your members several different ways to easily login to your site.</p>
						</div>

						<div class="table table_discussion">
							<p class="sub">Support</p>
							<table>
								<tr class="first">
									<td class="last t"><a href="http://support.wishlistproducts.com" target="_blank"><?php _e('Customer Support', 'wishlist-login2'); ?></a></td>
								</tr>
								<tr>
									<td class="last t"><a href="http://customers.wishlistproducts.com/video-tutorials/wishlist-login2" target="_blank"><?php _e('Video Tutorials', 'wishlist-login2'); ?></a></td>
								</tr>
								<tr>
									<td class="last t"><a href="http://wishlistproducts.com/release-notes/wishlist-login2" target="_blank"><?php _e('Release Notes', 'wishlist-login2'); ?></a></td>
								</tr>
							</table>
						</div>

						<hr class="clear" />

						<p>
							<strong><?php $this->GetMenu('settings', true); ?></strong> - <?php _e('Adjust the main settings for WishList Login 2.0', 'wishlist-login2'); ?><br />
						</p>

						<hr class="clear" />

					<?php if ($this->Plugin_Is_Latest()): ?>
									<p>
										<a style="float:right" href="?<?php echo $_SERVER['QUERY_STRING']; ?>&checkversion=1"><?php _e('Check for Updates','wishlist-login2'); ?></a>
										<?php printf(__('You have the latest version of WishListLogin (v%1$s)', 'wishlist-login2'), $wlm_version); ?>
									</p>

					<?php else: ?>
										<p><?php printf(__('You are currently running on WishListLogin v%1$s.', 'wishlist-login2'), $wlm_version); ?>
											<br />
											<span style="color:red"><?php printf(__('* The most current version is v%1$s.', 'wishlist-login2'), $latest_wpm_ver); ?></span></p>
										<p style="text-align:left;" class="upgrade">
						<?php printf(__('<a href="%2$s" class="button button-primary">Upgrade</a> &nbsp;&nbsp; <a href="%1$s" class="button">Download</a>', 'wishlist-login2'), $this->Plugin_Download_Url(), $this->Plugin_Update_Url()); ?></p>
					<?php endif; ?>
									</div><!-- end inside -->
								</div><!-- end this postbox -->


								<?php if($this->ProductSKU > 0 && !$this->isLocal(strtolower(get_bloginfo('url')))) : ?>
								<!-- BEGIN NEW POSTBOX -->
								<div class="postbox">
									<h3>Deactivate WishListLogin</h3>
									<div class="inside"><!-- begin inside content -->
										<form method="post" onsubmit="return confirm('<?php _e('Are you sure that you want to deactivate the license of this plugin for this site?', 'wishlist-login2'); ?>')">
											<p class="submit"><?php _e("If you're migrating your site to a new server, or just need to cancel your license for this site, click the button below to deactivate the license of this plugin for this site.", 'wishlist-login2'); ?><br /><br />
												<input type="hidden" name="wordpress_wishlist_deactivate" value="<?php echo $this->ProductSKU; ?>" />
												<input type="submit" value="Deactivate License For This Site" name="Submit" />
											</p>
										</form>

									</div><!-- end inside -->
								</div><!-- end this postbox -->
								<?php endif; ?>


							</div><!-- END LEFT POSTBOX CONTAINER -->
							<div class="postbox-container" style="width:49%;"><!-- BEGIN RIGHT POSTBOX CONTAINER -->

								<!-- BEGIN NEW POSTBOX -->
								<div class="postbox">
									<h3><?php _e('WishList Products News', 'wishlist-login2'); ?></h3>
									<div class="inside wlrss-widget"><!-- begin inside content -->

									<p><?php _e('<a href="http://wishlistproducts.com/?cat=23" target="_blank">Click Here</a> to see all the Member News.', 'wishlist-login2'); ?></p>
								</div><!-- end inside -->
							</div><!-- end this postbox -->

						</div><!-- END RIGHT POSTBOX CONTAINER -->
					</div><!-- END dashboard-widgets-wrap -->

					<div class="clear"></div>

					<p>
						<small>WishListLogin v<?php echo $wlm_version; ?> |  Build  <?php echo $wlm_build; ?> | WordPress <?php echo get_bloginfo('version'); ?> | PHP <?php echo phpversion(); ?> on <?php echo php_sapi_name(); ?></small>
	</p>
</div>
<script type="text/javascript">
jQuery(function($) {
	data = {
		action: 'wlm_feeds'
	}
	$.ajax({
		type: 'POST',
		url: '<?php echo admin_url('admin-ajax.php');?>',
		data: data,
		success: function(response) {
			$('.wlrss-widget').html(response);
		}
	});
});
</script>
