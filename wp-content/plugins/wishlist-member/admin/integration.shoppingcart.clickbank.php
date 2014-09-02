<?php
/*
 * Clickbank Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.clickbank.php 1719 2013-08-27 19:22:58Z mike $
 */

$__index__ = 'cb';
$__sc_options__[$__index__] = 'Clickbank';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/clickbank';
$__sc_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/29-integrating-clickbank/';

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$cbthankyou = $this->GetOption('cbthankyou');
		if (!$cbthankyou) {
			$this->SaveOption('cbthankyou', $cbthankyou = $this->MakeRegURL());
		}
		$cbsecret = $this->GetOption('cbsecret');
		if (!$cbsecret) {
			$this->SaveOption('cbsecret', $cbsecret = strtoupper($this->PassGen() . $this->PassGen()));
		}
		$cbvendor = $this->GetOption('cbvendor');

		$cbproducts = (array) $this->GetOption('cbproducts');
		if (!$cbproducts) {
			$this->SaveOption('cbproducts', array());
		}

		// save POST URL
		if (wlm_arrval($_POST,'cbthankyou')) {
			$_POST['cbthankyou'] = trim(wlm_arrval($_POST,'cbthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['cbthankyou']));
			if ($wpmx == $_POST['cbthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'cbthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Clickbank Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('cbthankyou', $cbthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update your ClickBank products with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Secret Key and account nickname
		if (wlm_arrval($_POST,'cbsecret')) {
			$_POST['cbsecret'] = trim(strtoupper(wlm_arrval($_POST,'cbsecret')));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['cbsecret']));
			if ($wpmy == $_POST['cbsecret']) {
				$this->SaveOption('cbsecret', $cbsecret = $wpmy);
				echo "<div class='updated fade'>" . __('<p>Secret Key Updated.&nbsp; Make sure to update ClickBank with the same Secret key to make it work.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Secret key may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
			
			$cbvendor = trim($_POST['cbvendor']);
			$this->SaveOption('cbvendor', $cbvendor);
				echo "<div class='updated fade'>" . __('<p>Account Nickname Updated.</p>', 'wishlist-member') . "</div>";
		}
		$cbthankyou_url = $wpm_scregister . $cbthankyou;

		if (wlm_arrval($_POST,'cbproducts')) {
			$this->SaveOption('cbproducts', (array) $_POST['cbproducts']);
			$cbproducts = $_POST['cbproducts'];
			echo "<div class='updated fade'>" . __('<p>Product IDs were updated.</p>', 'wishlist-member') . "</div>";
		}
		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Clickbank -->
		<h2 style="font-size:18px;width:100%"><?php _e('Clickbank Integration', 'wishlist-member'); ?></h2>
		<p><?php _e('Integrating WishList Member to Clickbank can be done in 3 steps', 'wishlist-member'); ?></p>
		<blockquote>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Use the URL below for your ClickBank products "Thank You Page" and "Instant Notification URL":', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<a href="<?php echo $cbthankyou_url ?>" onclick="return false"><?php echo $cbthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('cbthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
					<?php echo $this->Tooltip("integration-shoppingcart-clickbank-tooltips-thankyouurl"); ?>
				</p>
				<div id="cbthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="cbthankyou" value="<?php echo $cbthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
			</form>
			<form method="post">
				<h2 style="font-size:18px;width:100%;border:none;"><?php _e('ClickBank Account Information', 'wishlist-member'); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('ClickBank Secret Key', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="cbsecret" value="<?php echo $cbsecret ?>" size="32" />
					<?php echo $this->Tooltip("integration-shoppingcart-clickbank-tooltips-Specify-a-Secret-Word"); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('ClickBank Account Nickname', 'wishlist-member'); ?></th>
						<td>
					<input type="text" name="cbvendor" value="<?php echo $cbvendor; ?>" size="20" />
					<?php echo $this->Tooltip("integration-shoppingcart-clickbank-tooltips-Clickbank-Vendor"); ?>
						</td>
					</tr>
				</table>
				<p class="submit">
					&nbsp;&nbsp;<input type="submit" class="button-secondary" value="<?php _e('Save Account Information','wishlist-member'); ?>" />
				</p>
			</form>

			<?php
			$cbproducts_json = json_encode($cbproducts);
			?>
			<form method="post">
				<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Specify the ClickBank Product ID that you wish to use for each of your Membership Levels', 'wishlist-member'); ?></h2>
				<table class="widefat">
					<col width="200"></col><col width="200"></col>
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col"><?php _e('Clickbank Item ID', 'wishlist-member'); ?></th>
							<th scope="col"><?php _e('Clickbank Payment Link', 'wishlist-member'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$alt = 0;
						foreach ((array) $wpm_levels AS $sku => $level):
							?>
							<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
								<td><b><?php echo $level['name'] ?></b></td>
								<td><input type="text" name="cbproducts[<?php echo $sku; ?>]" value="" size="5" style="text-align:center"></td>
								<td><span id="cb_pay_link-<?php echo $sku; ?>"></span></td>
							</tr>
							<?php
						endforeach;
						?>
					</tbody>
				</table>
				<?php echo $ppp_table_end = '<p><input class="button-secondary" type="submit" value="Save Product IDs"></p>'; ?>
				<?php
				$ppph2 = __('Pay Per Post Links', 'wishlist-member');
				$pppdesc = '';
				$pppsku_header = __('Clickbank Item ID', 'wishlist-member');
				$pppsku_text = '<input type="text" name="cbproducts[%s]" value="" size="5" style="text-align:center">';
				$ppp_extraheaders = array(__('Clickbank Payment Link', 'wishlist-member'));
				$ppp_extracolumns = array('<span id="cb_pay_link-%s"></span>');
				$ppp_colset = '<col width="200"></col><col width="200"></col>';
				include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php');
				?>
			</form>

		</blockquote>
		<script>
			var cbproducts = <?php echo $cbproducts_json; ?>;
			var cbvendor = '<?php echo trim($cbvendor) ? trim($cbvendor) : '<span style="color:red">ACCOUNT_NICKNAME</span>'; ?>';
			
			for (var index in cbproducts){
				jQuery('input[name="cbproducts['+index+']"]').val(cbproducts[index]);
				if(cbproducts[index]) {
					jQuery('span#cb_pay_link-'+index).html('http://'+cbproducts[index]+'.'+cbvendor+'.pay.clickbank.net');
				}
			}
		</script>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.clickbank.tooltips.php');
		// END Interface
	}
}