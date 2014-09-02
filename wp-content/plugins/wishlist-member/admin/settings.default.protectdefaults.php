<form method="post">
	<?php $pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true))); ?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" style="white-space:nowrap;"><nobr><?php _e('Only show content for each membership level:', 'wishlist-member'); ?></nobr></th>
		<td>
			<label><input type="radio" name="<?php $this->Option('only_show_content_for_level'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
				<?php _e('Yes', 'wishlist-member'); ?></label>
			&nbsp;
			<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
				<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Only-show-content-for-each-membership-level"); ?>
		</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="white-space:nowrap;"><nobr><?php _e('Hide protected posts from public RSS:', 'wishlist-member'); ?></nobr></th>
		<td>
			<label><input type="radio" name="<?php $this->Option('rss_hide_protected'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
				<?php _e('Yes', 'wishlist-member'); ?></label>
			&nbsp;
			<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
				<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Hide-protected-posts-from-RSS"); ?>
		</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Hide protected content from search results:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('hide_from_search'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Hide-protected-content-from-search-results"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Protect all content after the "more" tags:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('protect_after_more'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Protect-all-content-after-the-more-tags"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Do you want to automatically protect content by inserting the "more" tag if the "more" tag is not inserted into any post?', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input onclick="document.getElementById('insert_more_at').style.display='block';" type="radio" name="<?php $this->Option('auto_insert_more'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input onclick="document.getElementById('insert_more_at').style.display='none';" type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-automatically-protect-content-by-inserting-the-more-tag"); ?>
				<div id="insert_more_at" style="display:<?php if (!$this->OptionValue(true)) echo 'none'; ?>">
					<?php _e('Insert more tag after the first', 'wishlist-member'); ?>
					<input type="text" name="<?php $this->Option('auto_insert_more_at'); ?>" value="<?php $this->OptionValue(false, 50); ?>" size="3" />
					<?php _e('words', 'wishlist-member'); ?>
				</div>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Hide after login page and after registration page of each levels:', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('exclude_pages'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-exclude-pages"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Default Content Protection', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('default_protect'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('On', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('Off', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Default-Content-Protection"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Default Pay Per Post Setting', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('default_ppp'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('On', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('Off', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Default-PayPerPost"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Enable Folder Protection', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('folder_protection'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Enable-Folder-Protection"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Enable File Protection', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('file_protection'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Enable-File-Protection"); ?>
			</td>
		</tr>
        <tr valign="top">
			<th scope="row" style="border:none"><?php _e('Consider Pay Per Post in [ismember] Merge Code', 'wishlist-member'); ?></th>
			<td style="border:none">
				<label><input type="radio" name="<?php $this->Option('payperpost_ismember'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
					<?php _e('Yes', 'wishlist-member'); ?></label>
				&nbsp;
				<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
					<?php _e('No', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("settings-default-tooltips-Consider-Payperpost-for-ismember"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('File Protection Ignore List', 'wishlist-member'); ?></th>
			<td>
				<input type="text" name="<?php $this->Option('file_protection_ignore'); ?>" value="<?php $this->OptionValue(); ?>" size="80" /><br />
				<?php _e('Add the filename extension of files that you do not want to protect.<br />Separate each filename extension with a comma. (i.e. txt, css)', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Text to display for content protected with private tags', 'wishlist-member'); ?></th>
			<td style="border:none">
				<input type="text" name="<?php $this->Option('private_tag_protect_msg'); ?>" value="<?php $this->OptionValue(); ?>" style="width:90%" /><?php echo $this->Tooltip("settings-default-tooltips-Text-to-display-for-content-protected-with-private-tags"); ?><br />
				<?php _e('Merge code [level] will be automatically replaced with the level of membership.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2">
				<?php _e('If you would like to protect part of your posts you can use the "private tags".  Just place [private_level] before and [/private_level] after the text you wish to protect.  Please note that these tags will differ depending on your membership levels.  For example, if you have a membership level called "silver", the tags would be [private_silver] and [/private_silver].  Therefore only anyone in the silver would be able to view this content.', 'wishlist-member'); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border:none"><?php _e('Text to display when comments are closed', 'wishlist-member'); ?></th>
			<td style="border:none">
				<input type="text" name="<?php $this->Option('closed_comments_msg'); ?>" value="<?php $this->OptionValue(); ?>" style="width:90%" />
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