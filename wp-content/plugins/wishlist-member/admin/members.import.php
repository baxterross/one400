<?php
/*
 * Import Members
 */
?>

<h2>WL Member &raquo; <?php _e('Import Members', 'wishlist-member'); ?></h2>

<p><?php _e('Use the form below to import members into your membership site by uploading a .csv file.', 'wishlist-member'); ?></p>
<p><?php _e('It is important that your file follows the format of our sample file below.', 'wishlist-member'); ?></p>
<p><?php printf(__('<a href="?%1$s&wpm_download_sample_csv=1">Click here to download the sample .csv file</a>', 'wishlist-member'), $this->QueryString()); ?></p>
<br>
<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
	<table class="form-table">
		<tr valign="top">
			<th scope="row" style="border-bottom:none"><?php _e('Select CSV File', 'wishlist-member'); ?></th>
			<td style="border-bottom:none">
				<input type="file" name="File" id="importml"/> <?php echo $this->Tooltip("members-import-tooltips-Select-CSV-File"); ?>
				<p><?php _e('Click the "Browse..." button to select a .csv file that contains the information of the members you would like to import.', 'wishlist-member'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border-bottom:none"><?php _e('Default Password for New Users', 'wishlist-member'); ?></th>
			<td style="border-bottom:none">
				<input type="text" name="password" />  <?php echo $this->Tooltip("members-import-tooltips-Default-Password-for-New-Users"); ?>
				<p><?php _e('Leave this field blank to generate random passwords for each member.', 'wishlist-member'); ?></p>
				<p><?php _e('Note: This field will be ignored if your CSV file contains passwords for each user.', 'wishlist-member'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" style="border-bottom:none" ><?php _e('Membership Levels to Import to', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="importmlevels" value="0" checked="checked" class ="importlevels" /> <?php _e('Import users into the selected levels', 'wishlist-member'); ?></label><br />
				<label><input type="radio" name="importmlevels" value="1" class ="importlevels"/> <?php _e('Auto-detect levels from import file', 'wishlist-member'); ?></label>
			</td>

		</tr>
		<tr valign="top">
			<th scope="row"></th>
			<td class="selectedlevel">
				<select data-placeholder="Select a level..." multiple="multiple" style="width:312px;padding:0px !important;" class="select_mlevels" id="mySelect" name="wpm_to[]" >
					<option class="select_all" value="select_all" >Select All</option>                        
					<?php foreach ($wpm_levels as $id => $level): ?>
						<option value="<?php echo $id; ?>"><?php echo $level['name']; ?></option>                                                                                                                                        
					<?php endforeach; ?>
				</select> <?php echo $this->Tooltip("members-import-tooltips-Membership-Level-to-Import"); ?>
				<br /><br />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('How to handle duplicate<br>usernames or emails', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="duplicates" value="skip" checked="checked" /> <?php _e('Skip rows with duplicates', 'wishlist-member'); ?></label>  <?php echo $this->Tooltip("members-import-tooltips-How-to-handle-Duplicates"); ?><br />
				<label><input type="radio" name="duplicates" value="replace" /> <?php _e('Replace ALL information and membership levels', 'wishlist-member'); ?></label><br />
				<label><input type="radio" name="duplicates" value="update" /> <?php _e('Update ALL information and membership levels', 'wishlist-member'); ?></label><br />
				<label><input type="radio" name="duplicates" value="replace_levels" /> <?php _e('Replace membership levels ONLY', 'wishlist-member'); ?></label><br />
				<label><input type="radio" name="duplicates" value="update_levels" /> <?php _e('Update membership levels ONLY', 'wishlist-member'); ?></label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Integrarions to process', 'wishlist-member'); ?></th>
			<td>
				<label><input type="checkbox" name="process_autoresponders" value="1" /> <?php _e('Process AutoResponder integration', 'wishlist-member'); ?></label><br />
				<label><input type="checkbox" name="process_webinars" value="1" /> <?php _e('Process Webinars integration', 'wishlist-member'); ?></label><br />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('Notify New Users via Email', 'wishlist-member'); ?></th>
			<td>
				<label><input type="radio" name="notify" value="1" checked="checked"> <?php _e('Yes, send email notification to new users.', 'wishlist-member'); ?></label> <?php echo $this->Tooltip("members-import-tooltips-Notify-New-Users-via-Email"); ?><br />
				<label><input type="radio" name="notify" value="0"> <?php _e('No. (Email notification will still be sent to new users with randomly generated passwords)', 'wishlist-member'); ?></label>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input type="hidden" name="WishListMemberAction" value="ImportMembers" />
		<input type="submit" class="button-primary" value="<?php _e('Import Members', 'wishlist-member'); ?>" />
	</p>

</form>
<script type="text/javascript">
	jQuery(document).ready(function() {

		jQuery('.select_mlevels').chosen();

		jQuery('.select_mlevels').chosen().change(function() {
			$str_selected = jQuery(this).val();
			if ($str_selected != null) {
				$pos = $str_selected.lastIndexOf("select_all");
				if ($pos >= 0) {
					jQuery(this).find('option').each(function() {
						if (jQuery(this).val() == "select_all") {
							jQuery(this).prop("selected", false);
						} else {
							jQuery(this).prop("selected", "selected");
						}
						jQuery(this).trigger("liszt:updated");
					});

				}
			}
		});

		jQuery(".importlevels").bind('change', function() {

			var lvalue = jQuery(this).val();

			if (lvalue == '0') {
				jQuery(".selectedlevel").show();

			}
			if (lvalue == '1') {
				jQuery(".selectedlevel").fadeOut();
				clearselectedmlevels();
			}
		});
		function clearselectedmlevels() {
			jQuery('.select_mlevels').find('option').each(function() {
				jQuery(this).prop("selected", false);
				jQuery(this).trigger("liszt:updated");
			});
		}
	});
</script>