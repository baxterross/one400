<?php
/*
 * Call Loop Autoresponder Interface
 * Original Author :Andy
 * Version: $Id:  
 */

$__index__ = 'callloop';

$__other_options__[$__index__] = 'Call Loop';

$__other_affiliates__[$__index__] = 'http://wlplink.com/go/callloop';
$__other_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/callloop/';

if ($_GET['other_integration'] == $__index__):
	if ($__INTERFACE__):
		
		if($_POST['saveCallloop'] == 'saveCallloop') {
			$this->SaveOption('callloop_settings', $_POST['callloop']);
			echo "<div class='updated fade'>" . __('<p>Your Call Loop settings have been saved</p>', 'wishlist-member') . "</div>";
			if(isset($_POST['enableCallLoop'])) {
				$this->IntegrationActive('integration.other.callloop.php', true);
			}else{
				$this->IntegrationActive('integration.other.callloop.php', false);
			}
		}
		
		$callloop_settings = (array) $this->GetOption('callloop_settings'); 
		$callloop_active = $this->IntegrationActive('integration.other.callloop.php');
		
		?>
		<h2 style="font-size:18px;width:100%">Call Loop Settings</h2>
		<form method="post">
			<input type="hidden" name="saveCallloop" value="saveCallloop" />
			<?php if(!$callloop_active) : ?>
			<div class="error fade">
				<p><?php _e('Call Loop is currently disabled. Enable it by checking the box below.'); ?></p>
			</div>
			<?php endif; ?>
			<p>
				<label><input type="checkbox" name="enableCallLoop" value="1" <?php echo $callloop_active ? 'checked="checked"' : ''; ?> /> <?php _e('Enable Call Loop', 'wishlist-member'); ?></label>
			</p>
			
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Call Loop List URL', 'wishlist-member'); ?>
							<?php echo $this->Tooltip("integration-autoresponder-calllooplisturl-tooltips"); ?>
						</th>
						<th class="num"><?php _e('Unsubscribe if Removed from Level', 'wishlist-member'); ?>

						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
						<tr>
							<th scope="row"><?php echo $level['name']; ?></th>
							<td><input type="text" name="callloop[URL][<?php echo $levelid; ?>]" value="<?php echo $callloop_settings['URL'][$levelid]; ?>" size="80" /></td>
							<?php $callloopUnsub = ($callloop_settings['callloopUnsub'][$levelid] == 1 ? true : false); ?>
							<td class="num"><input type="checkbox" name="callloop[callloopUnsub][<?php echo $levelid; ?>]" value="1" <?php echo $callloopUnsub ? "checked='checked'" : ""; ?> /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update Call Loop Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.callloop.tooltips.php');
	endif;
endif;
?>