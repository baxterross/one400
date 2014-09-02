<?php
/*
 * QuickPayPro Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.quickpaypro.php 1608 2013-06-18 20:21:37Z mike $
 */

$__index__ = 'qpp';
$__sc_options__[$__index__] = 'Quick Pay Pro';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/quickpaypro';
$__sc_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/25-quick-pay-pro-integration/';

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$qppthankyou = $this->GetOption('qppthankyou');
		if (!$qppthankyou) {
			$this->SaveOption('qppthankyou', $qppthankyou = $this->MakeRegURL());
		}
		$qppsecret = $this->GetOption('qppsecret');
		if (!$qppsecret) {
			$this->SaveOption('qppsecret', $qppsecret = $this->PassGen() . $this->PassGen());
		}

		// save POST URL
		if (wlm_arrval($_POST,'qppthankyou')) {
			$_POST['qppthankyou'] = trim(wlm_arrval($_POST,'qppthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['qppthankyou']));
			if ($wpmx == $_POST['qppthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'qppthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('qppthankyou', $qppthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.&nbsp; Make sure to update Cydec with the same Post To URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'qppsecret')) {
			$_POST['qppsecret'] = trim(wlm_arrval($_POST,'qppsecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['qppsecret']));
			if ($wpmy == $_POST['qppsecret']) {
				$this->SaveOption('qppsecret', $qppsecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Changed.&nbsp; Make sure to update Cydec with the same Secret key to make it work.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		$qppthankyou_url = $wpm_scregister . $qppthankyou;
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Cydec / Quick Pay Pro -->
		<h2 style="font-size:18px;width:100%"><?php _e('Cydec Integration', 'wishlist-member'); ?></h2>
		<p><?php _e('Integrating WishList Member to Cydec can be done in 2 steps', 'wishlist-member'); ?></p>
		<blockquote>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Step 1. Set the "Post To URL" of your Cydec account<br />or the "Post To URL" of each product to the following URL:', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<a href="<?php echo $qppthankyou_url ?>" onclick="return false"><?php echo $qppthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('qppthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-quickpaypro-tooltips-thankyouurl"); ?>
				</p>
				<div id="qppthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="qppthankyou" value="<?php echo $qppthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 2. Specify a Secret Word.', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<input type="text" name="qppsecret" value="<?php echo $qppsecret ?>" size="20" maxlength='16' /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-quickpaypro-tooltips-qppsecret"); ?>

				</p>
			</form>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 3. Create a product for each membership level using the SKUs specified below', 'wishlist-member'); ?></h2>
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
			<?php echo $this->Tooltip("integration-shoppingcart-quickpaypro-tooltips-sku"); ?>
					</td>
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.quickpaypro.tooltips.php');
		// END Interface
	}
}
?>
