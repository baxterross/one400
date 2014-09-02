<?php
/*
 * Premium Web Cart Shopping Cart Integration
 * Original Author : Glen Barnhardt
 * Version: $Id: integration.shoppingcart.premiumwebcart.php 537 2010-09-27 05:56:25Z glen $
 */

$__index__ = 'premimwebcart';
$__sc_options__[$__index__] = 'Premium Web Cart';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/premiumwebcart';
$__sc_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/42-premium-webcart-integration/';

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$pwcthankyou = $this->GetOption('pwcthankyou');
		if (!$pwcthankyou) {
			$this->SaveOption('pwcthankyou', $pwcthankyou = $this->MakeRegURL());
		}
		$pwcsecret = $this->GetOption('pwcsecret');
		if (!$pwcsecret) {
			$this->SaveOption('pwcsecret', $pwcsecret = $this->PassGen() . $this->PassGen());
		}

		$pwcmerchantid = $this->GetOption('pwcmerchantid');
		if (!$pwcmerchantid) {
			$this->SaveOption('pwcmerchantid', '');
		}

		$pwcapikey = $this->GetOption('pwcapikey');
		if (!$pwcapikey) {
			$this->SaveOption('pwcapikey', '');
		}

		// save POST URL
		if (wlm_arrval($_POST,'posturl_submit') == 'Change') {
			$_POST['pwcthankyou'] = trim(wlm_arrval($_POST,'pwcthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['pwcthankyou']));
			if ($wpmx == $_POST['pwcthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'pwcthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post to URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('pwcthankyou', $pwcthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'pwcsecret')) {
			$_POST['pwcsecret'] = trim(wlm_arrval($_POST,'pwcsecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['pwcsecret']));
			if ($wpmy == $_POST['pwcsecret']) {
				$this->SaveOption('pwcsecret', $pwcsecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Changed.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		if (wlm_arrval($_POST,'pwcapikey_submit')) {
			$_POST['pwcapikey'] = trim(wlm_arrval($_POST,'pwcapikey'));
			$this->SaveOption('pwcapikey', $_POST['pwcapikey']);
			echo "<div class='updated fade'>" . __('<p>API Key Changed.</p>', 'wishlist-member') . "</div>";
		}

		if (wlm_arrval($_POST,'pwcmerchantid_submit')) {
			$_POST['pwcmerchantid'] = trim(wlm_arrval($_POST,'pwcmerchantid'));
			$this->SaveOption('pwcmerchantid', $_POST['pwcmerchantid']);
			echo "<div class='updated fade'>" . __('<p>Merchant ID Changed.</p>', 'wishlist-member') . "</div>";
		}


		$pwcthankyou_url = $wpm_scregister . $pwcthankyou;
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Premium Web Cart -->
		<h2 style="font-size:18px;width:100%"><?php _e('Premium Web Cart 3rd Party System Integration', 'wishlist-member'); ?></h2>
		<blockquote>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Merchant ID', 'wishlist-member'); ?></h2>

				<p><?php _e('This is the Merchant ID that you find in your Premium Web Cart account under "Account Settings" => "Current Status".', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<input type="text" name="pwcmerchantid" value="<?php echo $pwcmerchantid ?>" size="25" /> <input type="submit" class="button-secondary" name="pwcmerchantid_submit" value="<?php _e('Change', 'wishlist-member'); ?>" />

				<h2 style="font-size:18px;"><?php _e('API Key', 'wishlist-member'); ?></h2>    
				<p><?php _e('This is the API key that you find in your Premium Web Cart account under "Cart Settings" => "Advanced Integration" => "API Integration".', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<input type="text" name="pwcapikey" value="<?php echo $pwcapikey ?>" size="60" /> <input type="submit" class="button-secondary" name="pwcapikey_submit" value="<?php _e('Change', 'wishlist-member'); ?>" />
				<h2 style="font-size:18px;"><?php _e('Post/Callback URL', 'wishlist-member'); ?></h2>
				<p><?php _e('The Post/Callback URL is used in the PremiumWebCart integraton and thank you pages.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<a href="<?php echo $pwcthankyou_url ?>" onclick="return false"><?php echo $pwcthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('pwcthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-pwc-tooltips-thankyouurl"); ?>

				</p>
				<div id="pwcthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="pwcthankyou" value="<?php echo $pwcthankyou ?>" size="8" /> <input type="submit" class="button-secondary" name="posturl_submit" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Secret Word', 'wishlist-member'); ?></h2>
				<p><?php _e('The Secret Word is used to generate a hash key for security purposes.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<input type="text" name="pwcsecret" value="<?php echo $pwcsecret ?>" size="20" maxlength='16' /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-pwc-tooltips-pwcsecret"); ?>

				</p>
			</form>
			<h2 style="font-size:18px;"><?php _e('Membership Level SKUs', 'wishlist-member'); ?></h2>
			<p><?php _e('The Membership Level SKUs specifies the membership level that should be tied to each transaction.', 'wishlist-member'); ?></p>
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
			<?php echo $this->Tooltip("integration-shoppingcart-pwc-tooltips-sku"); ?>

					</td>
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.pwc.tooltips.php');
		// END Interface
	}
}
?>
