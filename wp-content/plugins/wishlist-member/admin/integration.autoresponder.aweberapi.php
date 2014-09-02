<?php
/*
 * Aweber Autoresponder Interface
 * Original Author : Mike Lopez
 * Version: $Id: integration.autoresponder.aweber.php 1113 2011-10-24 20:43:22Z mike $
 */

$__index__ = 'aweberapi';
$__ar_options__[$__index__] = 'AWeber API';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/aweber';
$__ar_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/aweber-integration/';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		$connected = false;
		$expired_auth = false;
		$lists = array();

		/** Load the integration */
		$class_file = $this->pluginDir . '/lib/integration.autoresponder.aweberapi.php';
		include $class_file;
		$integration = new WLM_AUTORESPONDER_AWEBERAPI;
		$integration->set_wlm($this);
		$integration->set_auth_key($data['aweberapi']['auth_key']);
		$curl_exists = function_exists('curl_init');

		$access_tokens = $integration->get_access_tokens();
		if (!empty($access_tokens)) {
			$connected = true;
		}

		// !connected but we have an auth key
		// let's try to connect one last time
		if (!$connected && !empty($data['aweberapi']['auth_key'])) {
			$access_tokens = $integration->renew_access_tokens();
			if (!empty($access_tokens)) {
				//save the new access tokens
				$data['aweberapi']['access_tokens'] = $access_tokens;
				$this->SaveOption('Autoresponders', $data);
			} else {
				$expired_auth = true;
				$data['aweberapi']['auth_key'] = null;
			}
		}

		if ($connected) {
			$lists = $integration->get_lists();
			// reformat
			$list_tmp = array();
			foreach ($lists as $item) {
				$list_tmp[$item['id']] = $item;
			}
			$lists = $list_tmp;
		}
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<input type="hidden" name="ar[access_tokens][0]" value="<?php echo $data['aweberapi']['access_tokens'][0] ?>"/>
			<input type="hidden" name="ar[access_tokens][1]" value="<?php echo $data['aweberapi']['access_tokens'][1] ?>"/>


			<a target="_blank" style="font-size: 16px" href="<?php echo $integration->get_authkey_url() ?>"><?php _e("Click here to obtain an authorization key and copy it into the box below") ?></a> <br/>
			<span class="description"><?php _e("You'll be taken to a new page where you'll be prompted to enter your AWeber login information and click Allow Access.") ?></span><br/> <br/>
			<textarea style="width: 450px; height: 90px;" name="ar[auth_key]"><?php echo $data['aweberapi']['auth_key'] ?></textarea>


			<p>&nbsp;</p>
			<?php if ($connected): ?>
				<table class="widefat">
					<thead>
						<tr>
							<th>Membership Level</th>
							<th>Aweber List</th>
							<th>Auto Unsubcribe</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
							<tr>
								<th scope="row"><?php echo $level['name']; ?></th>
								<td>
									<select name="ar[connections][<?php echo $levelid ?>]">
										<option value="">Select a list</option>
										<?php foreach ($lists as $l): ?>
											<?php $selected = ($data['aweberapi']['connections'][$levelid] == $l['id']) ? 'selected="selected"' : null; ?>
											<option <?php echo $selected ?> value="<?php echo $l['id'] ?>"><?php echo $l['name'] ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<?php $checked = $data['aweberapi']['autounsub'][$levelid] == 'yes' ? 'checked="checked"' : null ?>
									<input <?php echo $checked ?> type="checkbox" name="ar[autounsub][<?php echo $levelid ?>]" value="yes">
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else: ?>
				<div class="error fade" id="message">
					<p><?php _e('It seems that you have not yet connected WishList Member to your aweber account.', 'wishlist-member') ?></p>
				</div>
			<?php endif; ?>


			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update AutoResponder Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<?php
	endif;
endif;
?>

