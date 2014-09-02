<?php
/*
 * Export Members
 */
?>
<h2><?php _e('Members &raquo; Export Members', 'wishlist-member'); ?></h2>
<p><?php _e('Please select a Membership Level and click "Export Members" to download a CSV file of all the Members for that Membership Level.', 'wishlist-member'); ?></p>
<form method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Membership Level', 'wishlist-member'); ?></th>
			<td>
				<select data-placeholder="Select a level..." multiple="multiple" style="width:312px;padding:0px !important;" class="select_mlevels" name="wpm_to[]" >
					<option class="select_all" value="select_all" >Select All</option>                        
					<?php foreach ($wpm_levels as $id => $level): ?>
                    <option value="<?php echo $id; ?>"><?php echo $level['name']; ?></option>                                                                                                                                        
					<?php endforeach; ?>
					<option value="nonmember">Non-Members</option>
				</select> <?php echo $this->Tooltip("members-export-tooltips-Export-Members"); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php _e('Additional Options', 'wishlist-member'); ?>
			</th>
			<td>
				<label><input type="checkbox" name="full_data_export" value="1" /> <?php _e('Export full data', 'wishlist-member'); ?></label><br />
				<label><input type="checkbox" name="include_password" value="1" /> <?php _e('Include password (encrypted)', 'wishlist-member'); ?></label><br />
				<label><input type="checkbox" name="include_inactive" value="1" /> <?php _e('Include inactive members', 'wishlist-member'); ?></label>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input type="hidden" name="WishListMemberAction" value="ExportMembers" />
		<input type="submit" class="button-primary" value="<?php _e('Export Members', 'wishlist-member'); ?>" />
	</p>
</form>
<script type="text/javascript">
	jQuery(document).ready(function(){

	jQuery('.select_mlevels').chosen();
	
	jQuery('.select_mlevels').chosen().change(function(){
		$str_selected = jQuery(this).val();
		if($str_selected != null){
			$pos = $str_selected.lastIndexOf("select_all");
			if($pos >= 0){
				jQuery(this).find('option').each(function() {
					if(jQuery(this).val() == "select_all"){
						jQuery(this).prop("selected",false);
					}else{
						jQuery(this).prop("selected","selected");
					}
					jQuery(this).trigger("liszt:updated");
				});

			}
		}
	});	
});
</script>