<?php
/*
 * Paypal Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.paypal.php 1877 2013-11-25 18:16:11Z mike $
 */

$__index__ = 'pp';
$__sc_options__[$__index__] = 'Paypal';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/paypal';
$__sc_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/23-paypal-integration-wp27/';

if (wlm_arrval($_GET,'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$pptoken = $this->GetOption('pptoken');
		if (isset($_POST['pptoken'])) {
			if(trim(wlm_arrval($_POST,'pptoken')) != $pptoken)
				echo "<div class='updated fade'>" . __('<p>PayPal PDT Identity Token Changed.</p>', 'wishlist-member') . "</div>";
			$this->SaveOption('pptoken', $pptoken = trim(wlm_arrval($_POST,'pptoken')));
		}

		$ppthankyou = $this->GetOption('ppthankyou');
		if (!$ppthankyou) {
			$this->SaveOption('ppthankyou', $ppthankyou = $this->MakeRegURL());
		}

		$ppsandbox = $this->GetOption('ppsandbox');
		if (isset($_POST['ppsandbox'])) {
			if($ppsandbox != trim(wlm_arrval($_POST,'ppsandbox'))){
				if (wlm_arrval($_POST,'ppsandbox') == 1) {
					echo "<div class='updated fade'>" . __('<p>PayPal Sandbox Enabled.</p>', 'wishlist-member') . "</div>";
				} else {
					echo "<div class='updated fade'>" . __('<p>PayPal Sandbox Disabled.</p>', 'wishlist-member') . "</div>";
				}
			}
			$this->SaveOption('ppsandbox', $ppsandbox = trim(wlm_arrval($_POST,'ppsandbox')));
		}
		$ppsandbox = (int) $ppsandbox;

		// save POST URL
		if (wlm_arrval($_POST,'ppthankyou')) {
			$_POST['ppthankyou'] = trim(wlm_arrval($_POST,'ppthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['ppthankyou']));
			if ($wpmx == $_POST['ppthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'ppthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Paypal Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					if($wpmx != $ppthankyou){
						echo "<div class='updated fade'>" . __('<p>PayPal Thank You URL Changed.&nbsp; Make sure to update your PayPal products with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
					}				
					$this->SaveOption('ppthankyou', $ppthankyou = $wpmx);				
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b>PayPal Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		$ppthankyou_url = $wpm_scregister . $ppthankyou;
		$cancellation_settings_msg = false;
		$eotcancel = array();
		if(wlm_arrval($_POST,"eot_cancel")){
			if(is_array(wlm_arrval($_POST,"eot_cancel"))) $eotcancel = $_POST["eot_cancel"];
			else $eotcancel = array();
			$this->SaveOption('eotcancel', maybe_serialize($eotcancel));
			$cancellation_settings_msg = true;
		}elseif($_POST){
			$this->SaveOption('eotcancel', maybe_serialize(array()));
		}
		$subscr_cancel = array();
		if(wlm_arrval($_POST,"subscr_cancel")){
			if(is_array(wlm_arrval($_POST,"subscr_cancel"))) $subscr_cancel = $_POST["subscr_cancel"];
			else $eotcancel = array();
			$this->SaveOption('subscrcancel', maybe_serialize($subscr_cancel));
			$cancellation_settings_msg = true;
		}elseif($_POST){
			$this->SaveOption('subscrcancel', maybe_serialize(array()));
		}	

		if($cancellation_settings_msg != ""){
			echo "<div class='updated fade'>" . __('<p>Cancellation Settings saved!.</p>', 'wishlist-member') . "</div>";
		}

		$eotcancel = $this->GetOption('eotcancel');
		if($eotcancel) $eotcancel = maybe_unserialize($eotcancel);
		else $eotcancel = array();

		$subscrcancel = $this->GetOption('subscrcancel');
		if($subscrcancel) $subscrcancel = maybe_unserialize($subscrcancel);
		else $subscrcancel = false; //if false its default to checked

		// END Initialization
	} else {
		// START Interface
		?>
		<!-- PayPal -->
	<form method="post">
		<h2 style="font-size:18px;width:100%"><?php _e('Paypal Integration', 'wishlist-member'); ?></h2>
		<p id="pppersonalx">* <a href="javascript:void(0)" onclick="document.getElementById('pppersonal').style.display='block';document.getElementById('pppersonalx').style.display='none'"><?php _e('If you have a Paypal Personal Account, Click here', 'wishlist-member'); ?></a>
			<?php echo $this->Tooltip("integration-shoppingcart-paypal-tooltips-If-you-have-a-Paypal-Personal-Account"); ?>

		</p>
		<p id="pppersonal" style="display:none">
			<b><?php _e('Upgrade Instructions for Paypal Personal Account Users', 'wishlist-member'); ?></b><br /><br />
			<?php printf(__('1. Go to <a href="%1$s" target="_blank">%1$s</a>', 'wishlist-member'), 'https://www.paypal.com/cgi-bin/webscr?cmd=_registration-run'); ?><br />
			<?php _e('2. Click on the Upgrade your Account link', 'wishlist-member'); ?><br />
			<?php _e('3. Click on the Upgrade Now button', 'wishlist-member'); ?><br />
			<?php _e('4. If your account was a personal account, you will get a choice to upgrade to a Premier or Business account.', 'wishlist-member'); ?><br />
			<?php _e('5. Choose the type of account to upgrade to and follow instructions.', 'wishlist-member'); ?><br />
			<?php _e('6. If you were a Premier account holder, you will be sent to the Business account upgrade. Follow the instructions.', 'wishlist-member'); ?><br />
		</p>
		<p><?php _e('Integrating WishList Member to PayPal can be done in 5 steps', 'wishlist-member'); ?></p>
		<blockquote>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 1. Configure Paypal Settings.', 'wishlist-member'); ?></h2>
			<p><?php _e('Set the following configurations under "Profile" &raquo; "My Selling Tools" &raquo; "Website preferences" in your Paypal account:', 'wishlist-member'); ?></p>
			<table style="margin-left:15px" cellspacing="5">
				<tr valign="top">
					<td style="width:220px">Auto Return</td>
					<td>On</td>
				</tr>
				<tr valign="top">
					<td>Return URL</td>
					<td><?php _e('Must not be blank.  You can use any link you want but cannot be blank.<br />We recommend using the homepage of your site.', 'wishlist-member'); ?></td>
				</tr>
				<tr valign="top">
					<td>Payment Data Transfer</td>
					<td>On</td>
				</tr>
			</table>
			<p><?php _e('Set the following configurations under "Profile" &raquo; "My Selling Tools" &raquo; "Instant payment notifications" &raquo; "Choose IPN Settings"  OR "Edit Settings  in your Paypal account:', 'wishlist-member'); ?></p>
			<table style="margin-left:15px" cellspacing="5">
				<tr valign="top">
					<td style="width:220px">Notification URL</td>
					<td><?php _e('Must not be blank.  You can use any link you want but cannot be blank.<br />We recommend using the homepage of your site.', 'wishlist-member'); ?></td>
				</tr>
				<tr valign="top">
					<td>IPN messages</td>
					<td>Receive IPN messages (Enabled)</td>
				</tr>
			</table>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 2. Paste your Paypal "PDT Identity Token" below.', 'wishlist-member'); ?></h2>
			<p><?php _e('You may retrieve your Paypal PDT Identity Token by going to "My Account" &raquo; "My Selling Tools" &raquo; "Website preferences" in your Paypal account.', 'wishlist-member'); ?></p>
				<table class="form-table">
					<tr>
						<th scope="row">PDT Identity Token</th>
						<td><input type="text" size="65" name="pptoken" value="<?php echo $pptoken; ?>" />
							<?php echo $this->Tooltip("integration-shoppingcart-paypal-tooltips-pdt"); ?>
						</td>
					</tr>
				</table>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 3. Create a "Buy Now" or "Subscribe" Button in Merchant Services Section of PayPal.<br />Create a button for each membership level using the Item/Subscription ID specified below.', 'wishlist-member'); ?></h2>
			<p>* <?php _e('If you are creating a subscribe button and are using a Trial offer, please include the code in Step 7.<br />&nbsp;&nbsp;', 'wishlist-member'); ?></p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
						<th scope="col"><?php _e('Item/Subscription ID', 'wishlist-member'); ?></th>
						<th scope="col"  class="num"><?php _e('Cancel When Subscription Ends', 'wishlist-member'); ?></th>
						<th scope="col"  class="num"><?php _e('Immediately Cancel on Subscription Cancellation', 'wishlist-member'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $alt = 0;
					foreach ((array) $wpm_levels AS $sku => $level):
						?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td><b><?php echo $level['name'] ?></b></td>
							<td><u style="font-size:1.2em"><?php echo $sku ?></u>
			<?php echo $this->Tooltip("integration-shoppingcart-paypal-tooltips-sku"); ?>
							</td>
							<td class="num">
								<?php $ischecked = isset($eotcancel[$sku]) && $eotcancel[$sku] == 1 ? true : false; ?>
								<input type="checkbox" name="eot_cancel[<?php echo $sku; ?>]" value="1" <?php echo $ischecked ? "checked='checked'" : ""; ?> />
							</td>
							<td class="num">
								<?php 
									if($subscrcancel === false){
										$ischecked = true;
									}else{
										$ischecked = isset($subscrcancel[$sku]) && $subscrcancel[$sku] == 1 ? true : false; 
									}									
								?>
								<input type="checkbox" name="subscr_cancel[<?php echo $sku; ?>]" value="1" <?php echo $ischecked ? "checked='checked'" : ""; ?> />
							</td>							
					</tr>
		<?php endforeach; ?>
				</tbody>
			</table>
			<?php
			$pppsku_header = __('Item/Subscription ID', 'wishlist-member');
			include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus.php');
			?>

				<h2 style="font-size:18px;width:100%"><?php _e('Step 4. Set the "Thank You URL" of each product to the following URL:', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<a href="<?php echo $ppthankyou_url ?>" onclick="return false"><?php echo $ppthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('ppthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)  <?php echo $this->Tooltip("integration-shoppingcart-paypal-tooltips-thankyouurl"); ?></p>
				<div id="ppthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="ppthankyou" value="<?php echo $ppthankyou ?>" size="8" /></p>
				</div>
				<h2 style="font-size:18px;width:100%"><?php _e('Step 5. Paste the code below in the "Add advanced variables" field:', 'wishlist-member'); ?></h2>
				<p><b>rm=2<br />notify_url=<?php echo $ppthankyou_url ?><br />return=<?php echo $ppthankyou_url ?></b></p>
				<p>* <?php _e('This is located in PayPal\'s step 3 of creating a "Buy Now" button. It will say "Customize advanced features (optional)".', 'wishlist-member'); ?></p>

			<h2 style="font-size:18px;width:100%"><?php _e('Step 6. Paste the button code', 'wishlist-member'); ?></h2>
			<p><?php _e('Paste the button code generated by Paypal to your sales Page.', 'wishlist-member'); ?></p>
			<h2 style="font-size:18px;width:100%"><?php _e('Step 7. Support for $0 Trial Subscriptions (Optional)', 'wishlist-member'); ?></h2>
			<p><?php _e('Add the code below just before the &lt;/form&gt; tag of the Paypal button code to add support for $0 trial subscriptions.', 'wishlist-member'); ?></p>
			<pre><strong>&lt;script type="text/javascript" src="<?php echo get_bloginfo('url'); ?>/?wlm_th=field:custom"&gt;&lt;/script&gt;</strong></pre>
			<p><?php _e('Note: This also fixes the Paypal "Guest Payment" bug.', 'wishlist-member'); ?></p>
		</blockquote>
		<h2 style="font-size:18px;width:100%"><?php _e('Sandbox Testing', 'wishlist-member'); ?></h2>
		<p><?php printf(__('You can choose to enable <a href="%1$s" target="_blank">Paypal Sandbox</a> to test your Paypal integration', 'wishlist-member'), 'http://www.sandbox.paypal.com/'); ?></p>
			<blockquote>
				<p>
					<label>
						<input type="radio" name="ppsandbox" value="1" <?php $this->Checked($ppsandbox, 1); ?> />
		<?php _e('Enabled', 'wishlist-member'); ?>
					</label><br />
					<label>
						<input type="radio" name="ppsandbox" value="0" <?php $this->Checked($ppsandbox, 0); ?> />
		<?php _e('Disabled', 'wishlist-member'); ?>
					</label>
				</p>
			</blockquote>
			<p><input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" /></p>
	</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.paypal.tooltips.php');
		// END Interface
	}
}
?>
