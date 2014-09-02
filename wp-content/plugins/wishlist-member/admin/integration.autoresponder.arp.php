<?php
/*
 * AutoResponse Plus Autoresponder Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.arp.php 1682 2013-08-20 06:55:29Z mike $
 */

$__index__ = 'arp';
$__ar_options__[$__index__] = 'AutoResponse Plus';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/arp';
$__ar_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/27-autoresponse-plus-integration/';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		if (function_exists('curl_init')):
			?>
			<form method="post">
				<input type="hidden" name="saveAR" value="saveAR" />
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('ARP Application URL', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="ar[arpurl]" value="<?php echo $data['arp']['arpurl']; ?>" size="60" />
							<?php echo $this->Tooltip("integration-autoresponder-arp-tooltips-ARP-Application-URL"); ?>

							<br />
							<small><?php _e('Example:', 'wishlist-member'); ?> http://www.yourdomain.com/cgi-bin/arp3/arp3-formcapture.pl</small>
						</td>
					</tr>
				</table>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col"><?php _e('Autoresponder ID', 'wishlist-member'); ?>
								<?php echo $this->Tooltip("integration-autoresponder-arp-tooltips-Autoresponder-ID"); ?>
							</th>
							<th class="num"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
							<tr>
								<th scope="row"><?php echo $level['name']; ?></th>
								<td><input type="text" name="ar[arID][<?php echo $levelid; ?>]" value="<?php echo $data['arp']['arID'][$levelid]; ?>" size="10" /></td>
								<?php $arUnsub = ($data[$__index__]['arUnsub'][$levelid] == 1 ? true : false); ?>
								<td class="num"><input type="checkbox" name="ar[arUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $arUnsub ? "checked='checked'" : ""; ?> /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p><?php _e('To get the value for the Autoresponder ID field:', 'wishlist-member'); ?></p>
				<ul style="list-style:disc;margin-left:20px">
					<li><?php _e('Go into your AutoResponse Plus system and view the autoresponder list', 'wishlist-member'); ?></li>
					<li><?php _e('Move your mouse over any of the options in the \'actions\' column and look at the URL in the status bar.', 'wishlist-member'); ?></li>
					<li><?php _e('The ID number is shown as id= in the URL', 'wishlist-member'); ?></li>
					<li><?php _e('The URL will look something like this:', 'wishlist-member'); ?><br /><strong>http://yourdomain.com/cgi-bin/arp3/arp3.pl?a0=aut&amp;a1=edi&amp;a2=pro&amp;<span style="background:yellow;">id=1</span></strong></li>
				</ul>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Update AutoResponder Settings', 'wishlist-member'); ?>" />
				</p>
			</form>
			<?php
			include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.arp.tooltips.php');
		else:
			?>
			<p><?php _e('AutoResponse Plus requires PHP to have the CURL extension enabled.  Please contact your system administrator.', 'wishlist-member'); ?></p>
		<?php
		endif;
	endif;
endif;
?>
