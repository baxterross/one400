<?php
/*
 * Generic Autoresponder Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.generic.php 1682 2013-08-20 06:55:29Z mike $
 */

$__index__ = 'generic';
$__ar_options__[$__index__] = 'Generic';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Subscribe Email', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-generic-tooltips-Subscribe-Email"); ?>

						</th>
						<th scope="col"><?php _e('Unsubscribe Email', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-generic-tooltips-Unsubscribe-Email"); ?>

						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr>
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="ar[email][<?php echo $levelid; ?>]" value="<?php echo $data['generic']['email'][$levelid]; ?>" size="40" /></td>
							<td><input type="text" name="ar[remove][<?php echo $levelid; ?>]" value="<?php echo $data['generic']['remove'][$levelid]; ?>" size="40" /></td>
						<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update AutoResponder Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.generic.tooltips.php');
	endif;
endif;
?>