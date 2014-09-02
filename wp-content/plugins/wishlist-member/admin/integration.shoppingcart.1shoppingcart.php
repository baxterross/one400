<?php
/*
 * 1ShoppingCart Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.1shoppingcart.php 1608 2013-06-18 20:21:37Z mike $
 */

$__index__ = '1sc';
$__sc_options__[$__index__] = '1ShoppingCart';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/1sc';
$__sc_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/24-1shoppingcart-integration/';

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$scthankyou = $this->GetOption('scthankyou');
		if (!$scthankyou) {
			$this->SaveOption('scthankyou', $scthankyou = $this->MakeRegURL());
		}
		if (wlm_arrval($_POST,'scthankyou')) {
			$_POST['scthankyou'] = trim(wlm_arrval($_POST,'scthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['scthankyou']));
			if ($wpmx == $_POST['scthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'scthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> 1ShoppingCart Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('scthankyou', $scthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update 1ShoppingCart with the new Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		$scthankyou_url = $wpm_scregister . $scthankyou . '.php';

		// API Settings
		if (wlm_arrval($_POST,'onescapisave')) {
			$this->SaveOption('onescmerchantid', trim(wlm_arrval($_POST,'onescmerchantid')));
			$this->SaveOption('onescapikey', trim(wlm_arrval($_POST,'onescapikey')));
			$this->SaveOption('onescgraceperiod', $onescgraceperiod = abs(wlm_arrval($_POST,'onescgraceperiod')));
			$this->SaveOption('onesc_include_upsells', $onesc_include_upsells = (int) (bool) $_POST['onesc_include_upsells']);
			echo "<div class='updated fade'>" . __('<p>API Settings Updated.</p>', 'wishlist-member') . "</div>";
		}

		$onescmerchantid = $this->GetOption('onescmerchantid');
		$onescapikey = $this->GetOption('onescapikey');

		// Other Settings
		$onescgraceperiod = $this->GetOption('onescgraceperiod');
		if (!$onescgraceperiod) {
			$this->SaveOption('onescgraceperiod', $onescgraceperiod = 3);
		}
		$onesc_include_upsells = (int) (bool) $this->GetOption('onesc_include_upsells');
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- 1ShoppingCart -->
		<h2 style="font-size:18px;width:100%"><?php _e('1ShoppingCart Integration', 'wishlist-member'); ?></h2>
		<p><?php _e('Integrating WishList Member to 1ShoppingCart can be done in 3 steps', 'wishlist-member'); ?></p>
		<blockquote>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Step 1. Set the "Thank You URL" of your 1ShoppingCart account<br />or the "Thank You URL" of each product to the following URL:', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<a href="<?php echo $scthankyou_url ?>" onclick="return false"><?php echo $scthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('scthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-1shoppingcart-tooltips-thankyouurl"); ?>

				</p>
				<div id="scthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="scthankyou" value="<?php echo $scthankyou ?>" size="8" />.php <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>

				</div>
			</form>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 2. Create a product for each membership level using the SKU specified below', 'wishlist-member'); ?></h2>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('SKU', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $alt = 0;
					foreach ((array) $wpm_levels AS $sku => $level):
						?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td><b><?php echo $level['name'] ?></b></td>
							<td><u style="font-size:1.2em"><?php echo $sku ?></u>
			<?php echo $this->Tooltip("integration-shoppingcart-1shoppingcart-tooltips-sku"); ?>

					</td>
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>

			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 3. (Optional) Setup 1ShoppingCart API Settings', 'wishlist-member'); ?></h2>
			<p><?php _e('Entering your API Settings below allows WishList Member to fully integrate with 1ShoppingCart.', 'wishlist-member'); ?></p>
			<form method="post">
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('Merchant ID'); ?></th>
						<td>
							<input type="text" name="onescmerchantid" value="<?php echo $onescmerchantid; ?>" size="10" />
		<?php echo $this->Tooltip("integration-shoppingcart-1shoppingcart-tooltips-Merchant-ID"); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('API Key'); ?></th>
						<td>
							<input type="text" name="onescapikey" value="<?php echo $onescapikey; ?>" size="50" />
		<?php echo $this->Tooltip("integration-shoppingcart-1shoppingcart-tooltips-API-Key"); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Retry Grace Period', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="onescgraceperiod" value="<?php echo $onescgraceperiod; ?>" size="5" />
								<?php _e('Days', 'wishlist-member'); ?>
							<div>
		<?php _e('Enter the number of days that a failed credit card should be retried for recurring payments.', 'wishlist-member'); ?>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Process Upsells', 'wishlist-member'); ?></th>
						<td>
							<label><input type="radio" name="<?php $this->Option('onesc_include_upsells'); ?>" value="1"<?php $this->OptionChecked(1); ?> />
		<?php _e('Yes', 'wishlist-member'); ?></label>
							&nbsp;
							<label><input type="radio" name="<?php $this->Option(); ?>" value="0"<?php $this->OptionChecked(0); ?> />
		<?php _e('No', 'wishlist-member'); ?></label>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<p class="submit" style="margin:0;padding:0">
								<input type="submit" class="button-primary" name="onescapisave" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
							</p>
						</td>
					</tr>
				</table>
			</form>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 4. Setup Cron Job', 'wishlist-member'); ?></h2>
			<p><?php _e('In order for WishList Member to be properly sync with 1ShoppingCart, you must create a Cron job on your server.', 'wishlist-member'); ?></p>
			<p><?php _e("If you are unfamiliar or uncomfortable setting up a Cron Job, please contact your hosting provider and give them the information below.  Your hosting provider can then set this up for you.<br/>Setting the Cron Job for Once a day is recommended.", "wishlist-member")?></p>
			<h3><?php _e('Cron Job Details', 'wishlist-member'); ?></h3>
			<p><?php _e('Settings:', 'wishlist-member'); ?></p>
			<pre style="margin-left:25px">0 * * * *</pre>
			<p><?php _e('Command:', 'wishlist-member'); ?></p>
			<pre style="margin-left:25px">/usr/bin/wget -O - -q -t 1 <?php echo $scthankyou_url ?>?forcecheck=1</pre>
			<p>&middot; <?php _e('Copy the line above and paste it into the command line of your Cron job.', 'wishlist-member'); ?></p>
			<p>&middot; <?php _e('Note: If the above command doesn\'t work, please try the following instead:', 'wishlist-member'); ?></p>
			<pre style="margin-left:25px">/usr/bin/GET -d <?php echo $scthankyou_url ?>?forcecheck=1</pre>
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.1shoppingcart.tooltips.php');
		// END Interface
	}
}
?>