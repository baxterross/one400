<form method="post">
	<?php $pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true))); ?>
	<table class="form-table">
		<tr valign="top">
			<td colspan="2" style="border:none">
				<?php _e('Please specify the error pages that people will see when they try to access your membership site:', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Non-Members:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<select name="<?php $this->Option('non_members_error_page_internal') ?>" onchange="this.form.non_members_error_page.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-nonmemberspage"); ?>
				<br />
				<input<?php if ($this->GetOption('non_members_error_page_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('non_members_error_page'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('Non-members will see this error page when they try to access content in your members area.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Wrong Membership Level:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('wrong_level_error_page_internal') ?>" onchange="this.form.wrong_level_error_page.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-WrongMembershipLevel"); ?>
				<br />
				<input<?php if ($this->GetOption('wrong_level_error_page_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('wrong_level_error_page'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('If you have more than one level of membership, this is the error page someone will see if they try to access content that their membership level does not have permission to view.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Membership Cancelled:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('membership_cancelled_internal') ?>" onchange="this.form.membership_cancelled.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Membership-Cancelled"); ?>
				<br />
				<input<?php if ($this->GetOption('membership_cancelled_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('membership_cancelled'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed when a user\'s membership has been cancelled by one of the supported shopping carts.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Membership Expired:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('membership_expired_internal') ?>" onchange="this.form.membership_expired.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Membership-Expired"); ?>
				<br />
				<input<?php if ($this->GetOption('membership_expired_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('membership_expired'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed when a user\'s membership has been cancelled by one of the supported shopping carts.', 'wishlist-member'); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Membership For Approval:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('membership_forapproval_internal') ?>" onchange="this.form.membership_cancelled.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Membership-ForApproval"); ?>
				<br />
				<input<?php if ($this->GetOption('membership_forapproval_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('membership_forapproval'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed when a user\'s membership needs admin approval.', 'wishlist-member'); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Membership For Confirmation:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('membership_forconfirmation_internal') ?>" onchange="this.form.membership_cancelled.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Membership-ForConfirmation"); ?>
				<br />
				<input<?php if ($this->GetOption('membership_forconfirmation_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('membership_forconfirmation'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('This page will be displayed when a user\'s membership needs email confirmation.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<?php _e('Please specify the page to which a newly registered member will be redirected to:', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('After Registration Page:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('after_registration_internal') ?>" onchange="this.form.after_registration.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-After-Registration-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('after_registration_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('after_registration'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('Newly registered members will see this page after they register.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2" style="border:none">
				<?php _e('Please specify the the page to which a a member will be redirected to when he/she logs in:', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('After Login Page:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('after_login_internal') ?>" onchange="this.form.after_login.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-After-Login-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('after_login_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('after_login'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('Members will see this page after they login.', 'wishlist-member'); ?>
			</td>
		</tr>
		<!-- start added by Andy -->
		<tr valign="top">
			<td colspan="2" style="border:none">
				<?php _e('Please specify the the page to which a a member will be redirected to when he/she logout:', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('After Logout Page:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('after_logout_internal') ?>" onchange="this.form.after_logout.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-After-Logout-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('after_logout_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('after_logout'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
				<?php _e('Members will see this page after they logout.', 'wishlist-member'); ?>
			</td>
		</tr>
		<!-- end added by Andy -->

		<tr valign="top">
			<th scope="row"><?php _e('Custom Unsubscribe Confirmation Page:', 'wishlist-member'); ?></th>
			<td>
				<select name="<?php $this->Option('unsubscribe_internal') ?>" onchange="this.form.unsubscribe.disabled=this.selectedIndex>0">
					<option value="0"><?php _e('Enter an external URL below', 'wishlist-member'); ?></option>
					<?php foreach ($pages AS $page): ?>
						<option value="<?php echo $page->ID ?>"<?php $this->OptionSelected($page->ID); ?>><?php echo $page->post_title ?></option>
					<?php endforeach; ?>
				</select><?php echo $this->Tooltip("settings-default-tooltips-Custom-Unsubscribe-Confirmation-Page"); ?>
				<br />
				<input<?php if ($this->GetOption('unsubscribe_internal')) echo ' disabled="true"'; ?> type="text" name="<?php $this->Option('unsubscribe'); ?>" value="<?php $this->OptionValue(); ?>" size="60" /><br />
			</td>
		</tr>
		<!-- Pending period is now always disabled -->
		<input type="hidden" name="pending_period" value="" />
	</table>
	<p class="submit">
		<?php $this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
	</p>
</form>