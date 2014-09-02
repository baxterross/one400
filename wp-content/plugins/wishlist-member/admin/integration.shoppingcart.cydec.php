<?php
/*
 * Cydec Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.cydec.php 1051 2011-08-19 04:18:07Z mike $
 */

$__index__ = 'cydec';
$__sc_options__[$__index__] = 'Cydec';

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$cydecthankyou = $this->GetOption('cydecthankyou');
		if (!$cydecthankyou) {
			$this->SaveOption('cydecthankyou', $cydecthankyou = $this->MakeRegURL());
		}
		$cydecsecret = $this->GetOption('cydecsecret');
		if (!$cydecsecret) {
			$this->SaveOption('cydecsecret', $cydecsecret = $this->PassGen() . $this->PassGen());
		}

		// save POST URL
		if (wlm_arrval($_POST,'cydecthankyou')) {
			$_POST['cydecthankyou'] = trim(wlm_arrval($_POST,'cydecthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['cydecthankyou']));
			if ($wpmx == $_POST['cydecthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'cydecthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('cydecthankyou', $cydecthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Post To URL Changed.&nbsp; Make sure to update Cydec with the same Post To URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Post To URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key
		if (wlm_arrval($_POST,'cydecsecret')) {
			$_POST['cydecsecret'] = trim(wlm_arrval($_POST,'cydecsecret'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['cydecsecret']));
			if ($wpmy == $_POST['cydecsecret']) {
				$this->SaveOption('cydecsecret', $cydecsecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Changed.&nbsp; Make sure to update Cydec with the same Secret key to make it work.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		$cydecthankyou_url = $wpm_scregister . $cydecthankyou;
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
				<p>&nbsp;&nbsp;<a href="<?php echo $cydecthankyou_url ?>" onclick="return false"><?php echo $cydecthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('cydecthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-cydec-tooltips-thankyouurl"); ?>
				</p>
				<div id="cydecthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="cydecthankyou" value="<?php echo $cydecthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 2. Specify a Secret Word.', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<input type="text" name="cydecsecret" value="<?php echo $cydecsecret ?>" size="20" maxlength='16' /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" />
					<?php echo $this->Tooltip("integration-shoppingcart-cydec-tooltips-cydecsecret"); ?>

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
			<?php echo $this->Tooltip("integration-shoppingcart-cydec-tooltips-sku"); ?>
					</td>
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php'); ?>
		</blockquote>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.cydec.tooltips.php');
		// END Interface
	}
}
?>
