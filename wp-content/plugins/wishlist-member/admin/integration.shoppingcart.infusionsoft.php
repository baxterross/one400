<?php
/*
 * InfusionSoft Shopping Cart Integration
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.infusionsoft.php 2086 2014-03-27 08:40:45Z mike $
 */

$__index__ = 'is';
$__sc_options__[$__index__] = 'Infusionsoft';
$__sc_affiliates__[$__index__] = 'http://wlplink.com/go/infusionsoft';
$__sc_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/45-infusionsoft-integration/';

if (wlm_arrval($_GET, 'cart') == $__index__) {
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$isthankyou = $this->GetOption('isthankyou');
		if (!$isthankyou) {
			$this->SaveOption('isthankyou', $isthankyou = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'isthankyou')) {
			$_POST['isthankyou'] = trim(wlm_arrval($_POST, 'isthankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['isthankyou']));
			if ($wpmx == $_POST['isthankyou']) {
				if ($this->RegURLExists($wpmx, null, 'isthankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Infusionsoft Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('isthankyou', $isthankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update Infusionsoft with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}

		if( isset( $_POST['save_api_connection']) ) {
			// save Machine Name
				$_POST['ismachine'] = trim(wlm_arrval($_POST, 'ismachine'));
				$ismachine = $this->GetOption('ismachine');
				$ismachine = $ismachine ? $ismachine : "";
				if($ismachine != $_POST['ismachine']){
					$this->SaveOption('ismachine', $_POST['ismachine']);
					echo "<div class='updated fade'>" . __('<p>Machine Name Changed.</p>', 'wishlist-member') . "</div>";
				}
			// save API Key
				$_POST['isapikey'] = trim(wlm_arrval($_POST, 'isapikey'));
				$isapikey = $this->GetOption('isapikey');
				$isapikey = $isapikey ? $isapikey: "";
				if($isapikey != $_POST['isapikey']){
					$this->SaveOption('isapikey',$_POST['isapikey']);
					echo "<div class='updated fade'>" . __('<p>API Key Changed.&nbsp; Make sure that your API Key matches the one specified in your Infusionsoft account to make it work.</p>', 'wishlist-member') . "</div>";
				}	
		}


		if (wlm_arrval($_POST, 'update_tags')) {
			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_add_app' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_add_app', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_add_rem' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_add_rem', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_remove_app' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_remove_app', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_remove_rem' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_remove_rem', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_cancelled_app' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_cancelled_app', $istags);

			$tagsSelections = array();
			foreach ((array) $wpm_levels AS $sku => $level) {
				$n = 'istag_cancelled_rem' . $sku;
				if (isset($_POST[$n])) {
					$tagsSelections[$sku] = $_POST[$n];
				}
			}
			$istags = maybe_serialize($tagsSelections);
			$this->SaveOption('istags_cancelled_rem', $istags);

			echo "<div class='updated fade'>" . __('<p>Membership Level tag settings updated.</p>', 'wishlist-member') . "</div>";
		}

		//pay per post tag settings
		if (wlm_arrval($_POST, 'update_tags_pp')) {
			$posts = $this->GetPayPerPosts(array('post_title', 'post_type'),false);

			$istagspp_add_app = array();
			$istagspp_add_rem = array();
			$istagspp_remove_app = array();
			$istagspp_remove_rem = array();
			foreach($posts  as $post){
				$sku = 'payperpost-' . $post->ID;
				
				$n = 'istagpp_add_app' . $sku;
				if (isset($_POST[$n])) {
					$istagspp_add_app[$sku] = $_POST[$n];
				}

				$n = 'istagpp_add_rem' . $sku;
				if (isset($_POST[$n])) {
					$istagspp_add_rem[$sku] = $_POST[$n];
				}

				$n = 'istagpp_remove_app' . $sku;
				if (isset($_POST[$n])) {
					$istagspp_remove_app[$sku] = $_POST[$n];
				}	

				$n = 'istagpp_remove_rem' . $sku;
				if (isset($_POST[$n])) {
					$istagspp_remove_rem[$sku] = $_POST[$n];
				}			
			}
			$istags = maybe_serialize($istagspp_add_app);
			$this->SaveOption('istagspp_add_app', $istags);

			$istags = maybe_serialize($istagspp_add_rem);
			$this->SaveOption('istagspp_add_rem', $istags);

			$istags = maybe_serialize($istagspp_remove_app);
			$this->SaveOption('istagspp_remove_app', $istags);

			$istags = maybe_serialize($istagspp_remove_rem);
			$this->SaveOption('istagspp_remove_rem', $istags);

			echo "<div class='updated fade'>" . __('<p>Pay Per Post tag settings updated.</p>', 'wishlist-member') . "</div>";
		}

		$isthankyou_url = $wpm_scregister . $isthankyou;
		$isapikey = $this->GetOption('isapikey');
		$ismachine = $this->GetOption('ismachine');

		$isTagsCategory = array();
		$isTags = array();
		if (class_exists('WLM_INTEGRATION_INFUSIONSOFT_INIT')) {
			if ($isapikey && $ismachine) {
				$WLM_INTEGRATION_INFUSIONSOFT_INIT = new WLM_INTEGRATION_INFUSIONSOFT_INIT;
				$isTagsCategory = $WLM_INTEGRATION_INFUSIONSOFT_INIT->getTagsCategory($this, $ismachine, $isapikey);
				$isTagsCategory[0] = "- No Category -";
				asort($isTagsCategory);
				$isTags = $WLM_INTEGRATION_INFUSIONSOFT_INIT->getTags($this, $ismachine, $isapikey);
			}
		}
		$tag_placeholder = count($isTags) > 0 ? "Select tags..." : "No tags available";

		$istags_add_app = $this->GetOption('istags_add_app');
		if ($istags_add_app)
			$istags_add_app = maybe_unserialize($istags_add_app);
		else
			$istags_add_app = array();

		$istags_add_rem = $this->GetOption('istags_add_rem');
		if ($istags_add_rem)
			$istags_add_rem = maybe_unserialize($istags_add_rem);
		else
			$istags_add_rem = array();

		$istags_remove_app = $this->GetOption('istags_remove_app');
		if ($istags_remove_app)
			$istags_remove_app = maybe_unserialize($istags_remove_app);
		else
			$istags_remove_app = array();

		$istags_remove_rem = $this->GetOption('istags_remove_rem');
		if ($istags_remove_rem)
			$istags_remove_rem = maybe_unserialize($istags_remove_rem);
		else
			$istags_remove_rem = array();

		$istags_cancelled_app = $this->GetOption('istags_cancelled_app');
		if ($istags_cancelled_app)
			$istags_cancelled_app = maybe_unserialize($istags_cancelled_app);
		else
			$istags_cancelled_app = array();

		$istags_cancelled_rem = $this->GetOption('istags_cancelled_rem');
		if ($istags_cancelled_rem)
			$istags_cancelled_rem = maybe_unserialize($istags_cancelled_rem);
		else
			$istags_cancelled_rem = array();

		//pay per post tag settings
		$istagspp_add_app = $this->GetOption('istagspp_add_app');
		if ($istagspp_add_app)
			$istagspp_add_app = maybe_unserialize($istagspp_add_app);
		else
			$istagspp_add_app = array();

		$istagspp_add_rem = $this->GetOption('istagspp_add_rem');
		if ($istagspp_add_rem)
			$istagspp_add_rem = maybe_unserialize($istagspp_add_rem);
		else
			$istagspp_add_rem = array();

		$istagspp_remove_app = $this->GetOption('istagspp_remove_app');
		if ($istagspp_remove_app)
			$istagspp_remove_app = maybe_unserialize($istagspp_remove_app);
		else
			$istagspp_remove_app = array();

		$istagspp_remove_rem = $this->GetOption('istagspp_remove_rem');
		if ($istagspp_remove_rem)
			$istagspp_remove_rem = maybe_unserialize($istagspp_remove_rem);
		else
			$istagspp_remove_rem = array();		


		// END Initialization
	} else {
		// START Interface
		?>
		<!-- Infusionsoft -->
		<h2 style="font-size:18px;width:100%"><?php _e('Infusionsoft Integration', 'wishlist-member'); ?></h2>
		<p><?php _e('Integrating WishList Member to Infusionsoft can be done in 4 steps', 'wishlist-member'); ?></p>
		<blockquote>
			<form method="post">
				<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 1. Infusionsoft API Connection', 'wishlist-member'); ?></h2>
				
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('Machine Name', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="ismachine" value="<?php echo $ismachine ?>" size="20" />
							<?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-Machine-Name"); ?><br />
							<i><b><span style="background:#ffff00">appname</span></b>.infusionsoft.com</i>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Encrypted Key', 'wishlist-member'); ?></th>
						<td>
							<input type="text" name="isapikey" value="<?php echo $isapikey ?>" size="40" />
							<?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-API-Key"); ?><br />
							<i>The Encrypted Key can be found by going to: <b>Admin -> Settings -> Application -> Encrypted Key</b></i>
						</td>
					</tr>
				</table>
				<p class="submit">
					&nbsp;&nbsp;<input name="save_api_connection" type="submit" class="button-secondary" value="<?php _e('Save API Connection','wishlist-member'); ?>" />
				</p>
			</form>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 2. Create a product for each membership level using the SKUs specified below', 'wishlist-member'); ?></h2>
			<form method="post">
				<table class="widefat" style="z-index:0;">
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col" ><?php _e('SKU', 'wishlist-member'); ?><?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-sku"); ?></th>
							<th scope="col" >&nbsp;</th>
						</tr>
					</thead>
					<tbody>
		<?php
		$alt = 0;
		foreach ((array) $wpm_levels AS $sku => $level):
			?>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
							<td width="35%"><b><?php echo $level['name'] ?></b></td>
							<td width="35%"><u style="font-size:1.2em"><?php echo $sku ?></u></td>
							<td><a class="if_edit_tag_level ifshow" href="javascript:void(0);">[+] Edit Level Tag Settings</a></td>		
						</tr>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?> hidden" id="wpm_level_row_<?php echo $sku ?>">

							<td style="z-index:0;overflow:visible;">
								<p><b>When Added:</b></p>
								<p>
									Apply tags:<br />
									<select name="istag_add_app<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_add_app[$sku]) && in_array($data['Id'], $istags_add_app[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>
								<p>
									Remove tags:<br />
									<select name="istag_add_rem<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_add_rem[$sku]) && in_array($data['Id'], $istags_add_rem[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>								
							</td>
							<td style="z-index:0;overflow:visible;">
								<p><b>When Removed:</b></p>
								<p>
									Apply tags:<br />
									<select name="istag_remove_app<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_remove_app[$sku]) && in_array($data['Id'], $istags_remove_app[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>
								<p>
									Remove tags:<br />
									<select name="istag_remove_rem<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_remove_rem[$sku]) && in_array($data['Id'], $istags_remove_rem[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>			
							</td>
							<td style="z-index:0;overflow:visible;">
								<p><b>When Cancelled:</b></p>
								<p>
									Apply tags:<br />
									<select name="istag_cancelled_app<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_cancelled_app[$sku]) && in_array($data['Id'], $istags_cancelled_app[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>
								<p>
									Remove tags:<br />
									<select name="istag_cancelled_rem<?php echo $sku; ?>[]" data-placeholder='<?php echo $tag_placeholder; ?>' style="width:300px;" class='chzn-select' multiple="multiple" >
										<?php
										foreach ($isTagsCategory as $catid => $name) {
											if (isset($isTags[$catid]) && count($isTags[$catid]) > 0) {
												asort($isTags[$catid]);
												echo "<optgroup label='{$name}'>";
												foreach ($isTags[$catid] as $id => $data) {
													$selected = "";
													if (isset($istags_cancelled_rem[$sku]) && in_array($data['Id'], $istags_cancelled_rem[$sku])) {
														$selected = "selected='selected'";
													}

													echo "<option value='{$data['Id']}' {$selected}>{$data['Name']}</option>";
												}
												echo "</optgroup>";
											}
										}
										?>
									</select>
								</p>			
							</td>
						</tr>					
		<?php endforeach; ?>
					</tbody>
				</table>
				<p style="text-align:right;">
					<input type="submit" class="button-secondary" name="update_tags" value="<?php _e('Update Tags Settings', 'wishlist-member'); ?>" />
				</p>
			</form>
		<?php include_once($this->pluginDir . '/admin/integration.shoppingcart-payperpost-skus-if.php'); ?>
			<form method="post">
				<h2 style="font-size:18px;"><?php _e('Step 3. Create an Order Form for each product created and set the "Web Page URL" to the ff:', 'wishlist-member'); ?></h2>
				<p>&nbsp;&nbsp;<a href="<?php echo $isthankyou_url ?>" onclick="return false"><?php echo $isthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('isthankyou').style.display = 'block';"><?php _e('change', 'wishlist-member'); ?></a>)
		<?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-thankyouurlsku"); ?>
				</p>
				<div id="isthankyou" style="display:none">
					<p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="isthankyou" value="<?php echo $isthankyou ?>" size="8" /> <input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
				</div>
				<p><?php _e('The field for the "Web Page URL" can be found by selecting "Web Address" under "Other Options" -> "Thank you page settings".'); ?></p>
				<p><?php _e('* Note: You must check "Pass Person\'s info to "Thank You" page url (This is for techies)" for this to work properly.'); ?></p>
			</form>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 4. Setup Cron Job', 'wishlist-member'); ?> <?php echo $this->Tooltip("integration-shoppingcart-infusionsoft-tooltips-Setup-Cron-Job"); ?></h2>
			<p><?php _e('In order for WishList Member to work in sync with Infusionsoft, you must create a Cron job on your server.', 'wishlist-member'); ?></p>
			<h3><?php _e('Cron Job Details', 'wishlist-member'); ?></h3>
			<p><?php _e('Settings:', 'wishlist-member'); ?></p>
			<pre style="margin-left:25px">0 * * * *</pre>
			<p><?php _e('Command:', 'wishlist-member'); ?></p>
			<pre style="margin-left:25px">/usr/bin/wget -O - -q -t 1 <?php echo $isthankyou_url ?>?iscron=1</pre>
			<p>&middot; <?php _e('Copy the line above and paste it into the command line of your Cron job.', 'wishlist-member'); ?></p>
			<p>&middot; <?php _e('Note: If the above command doesn\'t work, please try the following instead:', 'wishlist-member'); ?></p>
			<pre style="margin-left:25px">/usr/bin/GET -d <?php echo $isthankyou_url ?>?iscron=1</pre>
		</blockquote>
		<script type="text/javascript">
					jQuery(".chzn-select").chosen({width:'300px'});
		</script>		
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.infusionsoft.tooltips.php');
		// END Interface
	}
}
