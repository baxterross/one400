<?php
/*
 * GetResponse Autoresponder API
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.getresponse.php 1682 2013-08-20 06:55:29Z mike $
 */

$__index__ = 'getresponse';
$__ar_options__[$__index__] = 'GetResponse';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/getresponse';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<p class="error" style="color:red;font-weight:bold">
			Notice: This integration is now deprecated. Please use the GetResponse API Integration instead.
		</p>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Autoresponder Email', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-getresponse-tooltips-Autoresponder-Email"); ?>

						</th>
						<th scope="col"><?php _e('Unsubscribe Email', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-getresponse-tooltips-Unsubscribe-Email"); ?>

						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr>
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="ar[email][<?php echo $levelid; ?>]" value="<?php echo $data['getresponse']['email'][$levelid]; ?>" size="40" /></td>
							<td><input type="text" name="ar[remove][<?php echo $levelid; ?>]" value="<?php echo $data['getresponse']['remove'][$levelid]; ?>" size="40" /></td>
						<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update AutoResponder Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.getresponse.tooltips.php');
	endif;
endif;
?>