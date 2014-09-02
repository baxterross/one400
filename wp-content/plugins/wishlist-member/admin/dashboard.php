<?php if ($show_page_menu) : ?>
	<?php
	return;
endif;
?>
<?php
$latest_wpm_ver = $this->Plugin_Latest_Version();
if (!$latest_wpm_ver)
	$latest_wpm_ver = $this->Version;

$wpm_levels = (array) $this->GetOption('wpm_levels');
$reversion = preg_split('/[ \.-]/', $this->Version);
$wlm_version = wlm_arrval($reversion, 0) . '.' . wlm_arrval($reversion, 1);
$wlm_build = wlm_arrval($reversion, 2);
$wlm_stage = wlm_arrval($reversion, 3);
?>
<?php if ($this->access_control->current_user_can('wishlistmember_members_manage')): ?>
<form onsubmit="document.location = '?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>&wl=members&<?php echo $this->QueryString('usersearch', 'offset'); ?>&usersearch=' + this.usersearch.value;
			return false;">
		  <?php endif; ?>
	<p class="search-box" style="float:right;margin-top:1em">
		<label for="post-search-input" class="hidden"><?php _e('Search Users:', 'wishlist-member'); ?></label>
		<input type="text" value="<?php echo esc_attr(stripslashes(wlm_arrval($_GET, 'usersearch'))) ?>" name="usersearch" id="post-search-input"/>
		<input type="submit" class="button-secondary" value="<?php _e('Search Users', 'wishlist-member'); ?>" /><br/>
	</p>
	<h2><?php _e('WishList Member Dashboard', 'wishlist-member'); ?></h2> 
</form>

<div id="dashboard-widgets-wrap">
	<div id="dashboard-widgets" class="metabox-holder">
		
		<!-- BEGIN LEFT POSTBOX CONTAINER -->
		<div class='postbox-container' style='width:49%;margin-right:1%'>			
			<!-- BEGIN NEW POSTBOX -->
			<div id="wl_dashboard_right_now" class="postbox">
				<h3><?php _e('Your Membership Stats', 'wishlist-member'); ?></h3>
				<!-- begin inside content -->
				<div class="inside">
					<?php
					if ($this->access_control->current_user_can('wishlistmember_dashboard_stats')):
						$members_link = $this->GetMenu('members');
					?>
						<table class="widefat">
							<tr class="first">
								<td width="80%"><a href="<?php echo $members_link->URL; ?>">
									<strong><?php _e('Total Registered Users', 'wishlist-member') ?></a></strong>
								</td>
								<td width="10%" style="text-align:right;"><a href="<?php echo $members_link->URL; ?>"><?php echo $this->MemberIDs(null, null, true); ?></a></td>
								<td width="10%"></td>
							</tr>

							<tr class="wlmlevels">
								<td colspan="3">								
									<strong><?php _e('Users per Membership Level', 'wishlist-member'); ?></strong>
								</td>
							</tr>
							<?php
							$totalmembers = $cancelmembers = 0;
							foreach (array_keys($wpm_levels) AS $level):
								$level = new WishListMember_Level($level);
								$lcount = $level->CountMembers();
								$xcount = $level->CountMembers(true);
								$clcount = $lcount - $xcount;
								$totalmembers+=$xcount;
								$cancelmembers+=$clcount;
								$level_link = $members_link->URL . '&level=' . $level->ID;
								?>
								<tr class="wlmlevels">
									<td class="levelname"><a href="<?php echo $level_link; ?>"><?php echo $level->name ?></a></td>
									<td style="text-align:right;"><a href="<?php echo $level_link; ?>"><?php echo $xcount; ?></a></td>
									<td style="text-align:right;"><a style="color:red;"><?php echo $clcount; ?></a></td>
								</tr>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>
					
					<hr class="clear" />
					<p>
						<?php if ($this->access_control->current_user_can('wishlistmember_settings')): ?>
							<strong><?php $this->GetMenu('settings', true); ?></strong> - <?php _e('Adjust the main settings for your membership', 'wishlist-member'); ?><br />
						<?php endif; ?>

						<?php if ($this->access_control->current_user_can('wishlistmember_members')): ?>
							<strong><?php $this->GetMenu('members', true); ?></strong> - <?php _e('See and manage your members', 'wishlist-member'); ?><br />
						<?php endif; ?>

						<?php if ($this->access_control->current_user_can('wishlistmember_membershiplevels')): ?>
							<strong><?php $this->GetMenu('membershiplevels', true); ?></strong> - <?php _e('Control the content your members see', 'wishlist-member'); ?><br />
						<?php endif; ?>

						<?php if ($this->access_control->current_user_can('wishlistmember_managecontent')): ?>
							<strong><?php $this->GetMenu('managecontent', true); ?></strong> - <?php _e('Manage content for Membership Levels and User Posts', 'wishlist-member'); ?><br />
						<?php endif; ?>

						<?php if ($this->access_control->current_user_can('wishlistmember_sequential')): ?>
							<strong><?php $this->GetMenu('sequential', true); ?></strong> - <?php _e('Setup sequential upgrading of members', 'wishlist-member'); ?><br />
						<?php endif; ?>

						<?php if ($this->access_control->current_user_can('wishlistmember_integration')): ?>
							<strong><?php $this->GetMenu('integration', true); ?></strong> - <?php _e('Integrate with shopping carts and autoresponders', 'wishlist-member'); ?><br />
						<?php endif; ?>
					</p>

					<hr class="clear" />
					<?php if ($this->Plugin_Is_Latest()): ?>
						<p>
							<a style="float:right" href="?<?php echo $_SERVER['QUERY_STRING']; ?>&checkversion=1"><?php _e('Check for Updates', 'wishlist-member'); ?></a>
							<?php printf(__('You have the latest version of <strong>WishList Member&trade;</strong> (v%1$s)', 'wishlist-member'), $wlm_version); ?>
						</p>
					<?php else: ?>
						<p><?php printf(__('You are currently running on <strong>WishList Member&trade;</strong> v%1$s.', 'wishlist-member'), $wlm_version); ?>
							<br />
							<span style="color:red"><?php printf(__('* The most current version is v%1$s.', 'wishlist-member'), $latest_wpm_ver); ?></span></p>
						<p style="text-align:left; ">
							<?php printf(__('<a href="%2$s" class="button-primary">Upgrade</a> &nbsp;&nbsp; <a href="%1$s" class="button-secondary">Download</a>', 'wishlist-member'), $this->Plugin_Download_Url(), $this->Plugin_Update_Url()); ?></p>
					<?php endif; ?>
				</div>
				<!-- end inside -->
			</div>
			<!-- END THIS POSTBOX -->

			<?php if ($this->access_control->current_user_can('wishlistmember_dashboard_activation_settings')): ?>
				<?php if (!$this->isURLExempted(strtolower(get_bloginfo('url')))): ?>
					<!-- BEGIN NEW POSTBOX -->
					<div class="postbox">
						<h3><?php _e('Deactivate WishList Member&trade;', 'wishlist-member'); ?></h3>
						<!-- begin inside content -->
						<div class="inside">
							<form method="post" onsubmit="return confirm('<?php _e('Are you sure that you want to deactivate the license of this plugin for this site?', 'wishlist-member'); ?>')">
								<p class="submit"><?php _e("If you're migrating your site to a new server, or just need to cancel your license for this site, click the button below to deactivate the license of this plugin for this site.", 'wishlist-member'); ?><br /><br />
									<input type="hidden" name="wordpress_wishlist_deactivate" value="<?php echo $this->ProductSKU; ?>" />
									<input type="submit" class="button-secondary" value="Deactivate License For This Site" name="Submit" />
								</p>
							</form>
						</div>
						<!-- end inside -->
					</div>
					<!-- END THIS POSTBOX -->
				<?php endif; ?>
			<?php endif; ?>
			<p>
				<small><strong>WishList Member&trade;</strong> v<?php echo $wlm_version; ?> |  Build  <?php echo $wlm_build; ?> <?php echo $wlm_stage; ?> | WordPress <?php echo get_bloginfo('version'); ?> | PHP <?php echo phpversion(); ?> on <?php echo php_sapi_name(); ?></small>
			</p>
		</div>
		<!-- END LEFT POSTBOX CONTAINER -->

		<!-- BEGIN RIGHT POSTBOX CONTAINER -->
		<div class="postbox-container" style="width:49%;">

			<!-- BEGIN SUPPORT POSTBOX -->
			<div class="postbox">
				<h3><?php _e('Support', 'wishlist-member'); ?></h3>
				<!-- begin inside content -->
				<div class="inside wlmsuppport-widget">
					<?php if ($this->access_control->current_user_can('wishlistmember_dashboard_support_links')): ?>
						<?php 
							//links, I have small screen so I want a line to be shorter
							$support_lnk = "http://customers.wishlistproducts.com/support/";
							$videotut_lnk = "http://customers.wishlistproducts.com/wishlist-member-tutorials/";
							$help_lnk = "http://customers.wishlistproducts.com/wishlist-member-documentation/";
							$faq_lnk = "http://customers.wishlistproducts.com/faq/";
							$api_lnk = "http://wishlistproducts.com/api";
							$release_lnk = "http://wishlistproducts.com/category/release-notes/";

						?>
						<table class="widefat">
							<tr class="first">
								<td>
									<a href="<?php echo $support_lnk; ?>" target="_blank"><?php _e('Customer Support', 'wishlist-member'); ?></a>
								</td>
								<td>
									<a href="<?php echo $videotut_lnk; ?>" target="_blank"><?php _e('Video Tutorials', 'wishlist-member'); ?></a>
								</td>
							</tr>									
							<tr>
								<td>
									<a href="<?php echo $help_lnk; ?>" target="_blank"><?php _e('Help Guide', 'wishlist-member'); ?></a>
								</td>	
								<td>
									<a href="<?php echo $faq_lnk; ?>" target="_blank"><?php _e('FAQ\'s', 'wishlist-member'); ?></a>
								</td>								
							</tr>								
							<tr>
								<td>
									<a href="<?php echo $api_lnk; ?>" target="_blank"><?php _e('API Documents', 'wishlist-member'); ?></a>
								</td>
								<td>
									<a href="<?php echo $release_lnk; ?>" target="_blank"><?php _e('Release Notes', 'wishlist-member'); ?></a>
								</td>
							</tr>
						</table>
					<?php endif; ?>
				</div>
				<!-- end inside -->
			</div>
			<!-- END SUPPORT POSTBOX -->

			<?php if ($this->access_control->current_user_can('wishlistmember_dashboard_news')): ?>
				<!-- BEGIN NEWS POSTBOX -->
				<div class="postbox" id="wlrss-postbox">
					<h3><?php _e('WishList Member&trade; News', 'wishlist-member'); ?></h3>
					<!-- begin inside content -->
					<div class="inside wlrss-widget">
						<p><?php _e('<a href="http://wishlistproducts.com/?cat=23" target="_blank">Click Here</a> to see all the Member News.', 'wishlist-member'); ?></p>
					</div>
					<!-- end inside -->
				</div>
				<!-- END NEWS POSTBOX -->
			<?php endif; ?>
		</div>
		<!-- END RIGHT POSTBOX CONTAINER -->

	</div><!-- END dashboard-widgets-wrap -->
</div>