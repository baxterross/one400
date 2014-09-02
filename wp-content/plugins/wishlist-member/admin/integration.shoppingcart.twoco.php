<?php
/*
 * 2CheckOut Shopping Cart Integration
 * Original Author : Glen Barnhardt
 * Version: $Id: integration.shoppingcart.2co.php 537 2010-04-19 05:56:25Z andy $
 */

$__index__ = '2CheckOut';
$__sc_options__[$__index__] = '2CheckOut';
$__sc_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/2checkout-integration/';

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$twocothankyou = $this->GetOption('twocothankyou');
		if (!$twocothankyou) {
			$this->SaveOption('twocothankyou', $twocothankyou = $this->MakeRegURL());
		}
		$twocosecret = $this->GetOption('twocosecret');
		if (!$twocosecret) {
			$this->SaveOption('twocosecret', $twocosecret = $this->PassGen() . $this->PassGen());
		}

		$twocovendorid = $this->GetOption('twocovendorid');
		// save POST URL
		if (wlm_arrval($_POST,'twocothankyou')) {
			$_POST['twocothankyou'] = trim(wlm_arrval($_POST,'twocothankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['twocothankyou']));
			if ($wpmx == $_POST['twocothankyou']) {
				if ($this->RegURLExists($wpmx, null, 'twocothankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post to URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('twocothankyou', $twocothankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'twocosecret')) {
			$_POST['twocosecret'] = trim(wlm_arrval($_POST,'twocosecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['twocosecret']));
			if ($wpmy == $_POST['twocosecret']) {
				$this->SaveOption('twocosecret', $twocosecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Changed.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save vendor id
		if (wlm_arrval($_POST,'twocovendorid')) {
			$_POST['twocovendorid'] = trim(wlm_arrval($_POST,'twocovendorid'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['twocovendorid']));
			if ($wpmy == $_POST['twocovendorid']) {
				$this->SaveOption('twocovendorid', $twocovendorid = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Vendor ID Changed.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Vendor ID may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		// save Demo ModeL
		if (isset($_POST['twocodemo'])) {
			$x = $this->SaveOption('twocodemo', $_POST['twocodemo'] + 0);
			echo "<div class='updated fade'>" . __('<p>2CheckOut Demo Mode Updated.</p>', 'wishlist-member') . "</div>";
		}
		$twocodemo = $this->GetOption('twocodemo') + 0;

		$twocothankyou_url = $wpm_scregister . $twocothankyou;
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- 2CheckOut -->
		<h2 style="font-size:18px;width:100%"><?php _e('2CheckOut System Integration', 'wishlist-member'); ?></h2>
		<p><?php _e('The 2CheckOut System Integration allows you to integrate 2CheckOut to your shopping cart with WishList Member.', 'wishlist-member'); ?></p>
		<p>&raquo; <a href="http://customers.wishlistproducts.com/2checkout-integration/"><?php _e('Integration instructions can found downloaded here.', 'wishlist-member'); ?></a></p>
		<blockquote>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Post URL', 'wishlist-member'); ?></h2>
				<p><?php _e('The Post URL is where you send your information to.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<a href="<?php echo $twocothankyou_url ?>" onclick="return false"><?php echo $twocothankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('twocothankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-thankyouurl"); ?>

				</p>
				<div id="twocothankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="twocothankyou" value="<?php echo $twocothankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Secret Word', 'wishlist-member'); ?></h2>
				<p><?php _e('The Secret Word is used to generate a hash key for security purposes.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<input type="text" name="twocosecret" value="<?php echo $twocosecret ?>" size="20" maxlength='16' /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-secret"); ?>

				</p>
			</form>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Vendor ID', 'wishlist-member'); ?></h2>
				<p><?php _e('The Vendor ID is used to generate a hash key for security purposes.', 'wishlist-member'); ?></p>
				<p>&nbsp;&nbsp;<input type="text" name="twocovendorid" value="<?php echo $twocovendorid ?>" size="20" maxlength='16' /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-vendorid"); ?>

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
			<?php echo $this->Tooltip("integration-shoppingcart-2co-tooltips-sku"); ?>

					</td>
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('2CheckOut Demo Mode', 'wishlist-member'); ?></h2>
				<blockquote>
					<label>
						<input type="radio" name="twocodemo" value="1" <?php $this->Checked($twocodemo, 1); ?> />
		<?php _e('Enable Demo Mode', 'wishlist-member'); ?>
					</label>
					&nbsp;
					<label>
						<input type="radio" name="twocodemo" value="0" <?php $this->Checked($twocodemo, 0); ?> />
		<?php _e('Disable Demo Mode', 'wishlist-member'); ?>
					</label>
					&nbsp;
					<input type="submit" class="button-secondary" value="<?php _e('Save Demo Mode Settings', 'wishlist-member'); ?>" />
				</blockquote>
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.2co.tooltips.php');
		// END Interface
	}
}
?>
