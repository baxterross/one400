<form method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Notify Admin on Exceeded Logins:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('login_limit_notify'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Notify-Admin-on-Exceeded-Logins"); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Notify admin of new user registration:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('notify_admin_of_newuser'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Notify-admin-of-new-user-registration"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Disable passwords in administrator emails:', 'wishlist-member'); ?></th>
			<td>
				<?php $mask = $this->GetOption('mask_passwords_in_emails'); ?>
				<?php if ($mask === false) $this->SaveOption('mask_passwords_in_emails', 1); ?>

				<label><input id="mask-passwords" type="radio" name="<?php $this->Option('mask_passwords_in_emails'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input id="unmask-passwords" type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Mask-Passwords"); ?>

				<script type="text/javascript">
					jQuery(function($) {
						function do_change() {
							if(confirm("<?php _e("understand that I am putting my members' passwords at risk by having them sent to me via email. I accept this risk and I assume all liability for any damages that may occur to my members as a result of exposing their passwords to this risk", "wishlist-member") ?>")) {
								return confirm("<?php _e("Are you REALLY sure you want send member passwords in your admin notification emails?", "wishlist-member") ?>");
							}
							return false;
						}
						$('#unmask-passwords').change(function(ev) {
							if($(this).val() == 0 && !do_change()) {
								$(this).removeAttr('checked');
								$('#mask-passwords').attr('checked', true);
							}
						});
					});
				</script>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Prevent duplicate shopping cart registrations:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('PreventDuplicatePosts'); ?>" value="1"<?php $this->OptionChecked(1); ?> onclick="document.getElementById('duplicate_post_error_page').style.display=this.checked?'':'none';" />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> onclick="document.getElementById('duplicate_post_error_page').style.display=this.checked?'none':'';"" />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Prevent-duplicate-shopping-cart-registrations"); ?>
			</td>
		</tr>
		<tr valign="top" id="duplicate_post_error_page" style="<?php echo $this->GetOption('PreventDuplicatePosts') == 1 ? '' : 'display:none'; ?>">
			<th scope="row"><?php _e('Duplicate shopping cart registration Page:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('duplicate_post_error_page_internal') ?>" onchange="this.form.duplicate_post_error_page.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Duplicate-Post-Error-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('duplicate_post_error_page_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('duplicate_post_error_page'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed when a duplicate registration post is detected.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Enable Admin Approval for shopping cart integrations:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('admin_approval_shoppingcart_reg'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-enaable-admin-approval-shoppingcart"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Members can update their info:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('members_can_update_info'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Members-can-update-their-info"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Show Affiliate Link in Footer:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('show_linkback'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Show-Affiliate-Link-in-Footer"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Unsubscribe expired members:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('unsubscribe_expired_members'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Unsubscribe-Expired-Members"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Redirect to Existing Member Registration:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('redirect_existing_member'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Redirect-Existing-Member"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Prevent deletion of Posts/Pages assigned as Pay Per Post :', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('prevent_ppp_deletion'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-prevent-ppp-deletion"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Minimum Password Length:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('min_passlength'); ?>" value="<?php $this->OptionValue(false, 8); ?>" size="4" />
				<?php _e('Characters', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-default-tooltips-Minimum-Password-Length"); ?><br />
				<?php _e('This sets the minimum password length when registering or importing users.  Default is 8.', 'wishlist-member'); ?><br/>
				<?php _e('Merge Code: You can insert [wlm_min_passlength] merge code in your post '); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Enable Password Hinting:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('password_hinting'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Password-Hinting"); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Enable Short Registration Links:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('enable_short_registration_links'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-shortlinks"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none;white-space:nowrap"><?php _e('Allow WishList Member To Handle Login Redirect:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('enable_login_redirect_override'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-login-redirect-override"); ?>
			</td>
		</tr>

		<tr valign="top">
			<td colspan="2" style="border:none">
				<hr />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Default Login Limit:', 'wishlist-member'); ?></th>
			<td style="border:none"><input type="text" name="<?php $this->Option('login_limit'); ?>" value="<?php $this->OptionValue(); ?>" size="3" /> IPs per day <?php echo $this->Tooltip("settings-default-tooltips-Default-Login-Limit"); ?><br />Enter 0 (zero) or leave it blank to disable</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Login Limit Message:', 'wishlist-member'); ?></th>
			<td><input type="text" name="<?php $this->Option('login_limit_error'); ?>" value="<?php $this->OptionValue(false, '<b>Error:</b> You have reached your daily login limit.'); ?>" size="80" /><?php echo $this->Tooltip("settings-default-tooltips-Login-Limit-Message"); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Registration Session Timeout:', 'wishlist-member'); ?></th>
			<td><input type="text" name="<?php $this->Option('reg_cookie_timeout'); ?>" value="<?php $this->OptionValue(false, 600); ?>" size="3" /> <?php _e('Seconds', 'wishlist-member'); ?> <?php echo $this->Tooltip("settings-default-tooltips-Registration-Session-Limit"); ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Registration Form Layout:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('FormVersion'); ?>" value=""<?php $this->OptionChecked(""); ?> />
					<?php _e('Legacy', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="improved"<?php $this->OptionChecked("improved"); ?> />
					<?php _e('Improved', 'wishlist-member'); ?></label>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<hr />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('RSS Secret Key:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('rss_secret_key'); ?>" value="<?php $this->OptionValue(false, md5(time())); ?>" size="60" /><?php echo $this->Tooltip("settings-default-tooltips-RSS-Secret-Key"); ?><br />
				<?php _e('This key will be used to generate the unique RSS Feed URL for each member.  Do not give this key to anyone.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Disable RSS Enclosures for non-authenticated feeds:', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="<?php $this->Option('disable_rss_enclosures'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Disable-RSS-Enclosures"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('API Key:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('WLMAPIKey'); ?>" value="<?php $this->OptionValue(false, md5(microtime())); ?>" size="60" /><?php echo $this->Tooltip("settings-default-tooltips-API-Key"); ?><br />
				<?php _e('This key will be used to integrate with the WishList Member API', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<hr />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="white-space:nowrap"><?php _e('Affiliate ID:', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('affiliate_id'); ?>" value="<?php $this->OptionValue(); ?>" /> <a href="http://wishlistproducts.com/affiliates/" target="_blank">Sign Up Now</a> <?php echo $this->Tooltip("settings-default-tooltips-Show-Affiliate-ID"); ?>
			</td>
		</tr>
	</table>
	<p class="submit">
		<?php $this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
	</p>
</form>