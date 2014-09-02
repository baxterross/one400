<?php
/*
 * MailChimp Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.mailchimp.php 1777 2013-10-11 16:42:39Z feljun $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: This is the UI part of the code. This is displayed as the admin area for MailChimp Integration in WishList Member Dashboard.
  Location: admin/
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.mailchimp.php)
  and displays current or default settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, integration.autoresponder.php call this script again with $wpm_levels contains the membership levels and $data contains the MailChimp Integration settings for each membership level.
 */

$__index__ = 'mailchimp';
$__ar_options__[$__index__] = 'MailChimp';
$__ar_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/47-mailchimp-integration/';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		
		if (class_exists('WLM_AUTORESPONDER_MAILCHIMP_INIT')) {
			$api_key = $data[$__index__]['mcapi'];
			if ($api_key != "") {
				$WLM_AUTORESPONDER_MAILCHIMP_INIT = new WLM_AUTORESPONDER_MAILCHIMP_INIT;
				$lists = $WLM_AUTORESPONDER_MAILCHIMP_INIT->mcCallServer("lists", array(), $api_key);
				if (!isset($lists['error']) && $lists['total'] > 0) {
					$lists = $lists['data'];
				} else {
					$lists = array();
				}
			}	
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('.wlmmcAction').change(function(){
					var selected = jQuery(this).val();
					if(selected == "unsub" || selected == ""){
						jQuery(this).parent().find("input").val("");
						jQuery(this).parent().find("input").prop("disabled",true);
						jQuery(this).parent().find("input").addClass("disabled");
					}else{
						jQuery(this).parent().find("input").removeClass("disabled");
						jQuery(this).parent().find("input").prop("disabled",false);
					}
				});
			});
		</script>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('MailChimp API Key', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[mcapi]" value="<?php echo $data[$__index__]['mcapi']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-API-Key"); ?>
						<br />
						<strong><?php _e('Get your API Key from ', 'wishlist-member'); ?><a href="http://admin.mailchimp.com/account/api/" target="_blank">http://admin.mailchimp.com/account/api/</a></strong>
					</td>
				</tr>
				<tr valign="top">
					<td scope="row"><?php _e('Double Opt-in:', 'wishlist-member'); ?></td>
					<td colspan="2">
						<p>
							<?php $optin = ($data[$__index__]['optin'] == 1 ? true : false); ?>
							<input type="checkbox" name="ar[optin]" value="1" <?php echo $optin ? "checked='checked'" : ""; ?> /> Disable Double Opt-in <?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-optin"); ?>
						</p>
					</td>
				</tr>
			</table>
			<br />
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('List\'s Unique Id', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-Lists-Unique-Id"); ?>
						</th>
						<th class="col"><?php _e('Grouping <i>(optional)</i>', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings"); ?>
						</th>
						<th class="num"><?php _e('If Removed from Level', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr>
							<th scope="row"><?php echo $level['name']; ?></th>
							<td>
								<select style="margin-top:15px;" name="ar[mcID][<?php echo $levelid; ?>]">
									<option value='' >- Select a List -</option>
									<?php
									foreach ((array)$lists as $list) {
										$selected = $data[$__index__]['mcID'][$levelid] == $list['id'] ? "selected='selected'" : "";
										echo "<option value='{$list['id']}' {$selected}>{$list['name']}</option>";
									}
									?>
								</select>
							</td>
							<td>
								Group Title:&nbsp;&nbsp;<input type="text" name="ar[mcGp][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcGp'][$levelid]; ?>" size="50" /><?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-title"); ?><br />
								Groups:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="ar[mcGping][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcGping'][$levelid]; ?>" size="50" /><?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-group"); ?>
							</td>
							<?php $mcOnRemCan = isset($data[$__index__]['mcOnRemCan'][$levelid]) ? $data[$__index__]['mcOnRemCan'][$levelid] : ""; ?>
							<td >Action:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<select class='wlmmcAction' name="ar[mcOnRemCan][<?php echo $levelid; ?>]">
									<option value='' <?php echo $mcOnRemCan == "" ? "selected='selected'" : ""; ?> >- Select a Action -</option>
									<option value='unsub' <?php echo $mcOnRemCan == "unsub" ? "selected='selected'" : ""; ?> >Unsubscribe from List</option>
									<option value='move' <?php echo $mcOnRemCan == "move" ? "selected='selected'" : ""; ?> >Move to Group</option>
									<option value='add' <?php echo $mcOnRemCan == "add" ? "selected='selected'" : ""; ?> >Add to Group</option>
								</select><br />
								<?php $isDisabled = ($mcOnRemCan == "" || $mcOnRemCan == "unsub") ? true : false; ?>
								Group Title:&nbsp;&nbsp;<input type="text" name="ar[mcRCGp][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcRCGp'][$levelid]; ?>" size="50" <?php echo $isDisabled ? "disabled='disabled' class='disabled'" : ""; ?> /><?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-title"); ?><br />
								Groups:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="ar[mcRCGping][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['mcRCGping'][$levelid]; ?>" size="50" <?php echo $isDisabled ? "disabled='disabled' class='disabled'" : ""; ?> /><?php echo $this->Tooltip("integration-autoresponder-mailchimp-tooltips-groupings-group"); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update MailChimp Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.mailchimp.tooltips.php');
	endif;
endif;
