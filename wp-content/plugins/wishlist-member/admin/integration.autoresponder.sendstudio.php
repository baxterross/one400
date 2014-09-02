<?php
/*
 * SendStudio Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.sendstudio.php 559 2010-06-03 02:05:06Z mike $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: This is the UI part of the code. This is displayed as the admin area for SendStudio Integration in WishList Member Dashboard.
  Location: admin/
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.sendstudio.php)
  and displays current or default settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, integration.autoresponder.php call this script again with $wpm_levels contains the membership levels and $data contains the SendStudio Integration settings for each membership level.
 */

$__index__ = 'sendstudio';
$__ar_options__[$__index__] = 'Interspire Email Marketer';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/interspire-emkt';
$__ar_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/46-interspire-email-marketing-integration/';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><strong>API Request Credentials</strong></th>
					<td>
						In you Admin Area Top Menu:  User &amp; Groups > View User Acounts > (Edit User) > Advance User Settings' tab <br />
						Make sure you check the "<u>Enable the XML API</u>".
				</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Your XML Path', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[sspath]" value="<?php echo $data[$__index__]['sspath']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-XML-Path"); ?>

						<br />
						<strong><?php _e('Ex. ', 'wishlist-member'); ?><a href="http://www.yourdomain.com/path/to/IEM/installation/xml.php" target="_blank">http://www.yourdomain.com/[<i>path/to/IEM/installation</i>]/xml.php</a></strong>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Your XML Username', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[ssuname]" value="<?php echo $data[$__index__]['ssuname']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-XML-Username"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Your XML Token ', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[sstoken]" value="<?php echo $data[$__index__]['sstoken']; ?>" size="60" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-XML-Token"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><strong>Custom Fields IDs</strong></th>
					<td>
						In you Admin Area:  Contact Lists Tab > View Custom Fields > (Click Edit) in your browser, look for the ID in the URL<br />
						<?php _e('Ex. ', 'wishlist-member'); ?>http://www.yourdomain.com/[<i>path/to/IEM/installation</i>]/admin/index.php?Page=CustomFields&Action=Edit&id=<u><strong>2</strong></u>
				</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('First Name:', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[ssfnameid]" value="<?php echo ($data[$__index__]['ssfnameid'] == "" ? "2" : $data[$__index__]['ssfnameid']); ?>" size="10" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-FName"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Last Name:', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[sslnameid]" value="<?php echo ($data[$__index__]['sslnameid'] == "" ? "3" : $data[$__index__]['sslnameid']); ?>" size="10" />
						<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-LName"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">&nbsp;</th>
					<td>&nbsp;</td>
				</tr>
			</table>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('List\'s Id', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-sendstudio-tooltips-Lists-Id"); ?>
						</th>
						<th class="num"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr>
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="ar[ssID][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['ssID'][$levelid]; ?>" size="10" /></td>
							<?php $ssUnsub = ($data[$__index__]['ssUnsub'][$levelid] == 1 ? true : false); ?>
							<td class="num"><input type="checkbox" name="ar[ssUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $ssUnsub ? "checked='checked'" : ""; ?> /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><strong>Where to get the List ID</strong><br />
				In you Admin Area:  Contact Lists Tab > View Contact Lists > (Edit the Contact List) in your browser, look for the ID in the URL<br /><br />
				<?php _e('Ex. ', 'wishlist-member'); ?>http://www.yourdomain.com/[<i>path/to/IEM/installation</i>]admin/index.php?Page=Lists&Action=Edit&id=<u><strong>1</strong></u>
		</p>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Update SendStudio Settings', 'wishlist-member'); ?>" />
		</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.sendstudio.tooltips.php');
	endif;
endif;
?>
