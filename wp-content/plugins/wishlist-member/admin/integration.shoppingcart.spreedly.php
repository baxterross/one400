<?php
/*
 * Pin Payments Shopping Cart Integration (Formerly known as Spreedly)
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.shoppingcart.spreedly.php 1113 2011-10-24 20:43:22Z mike $
 */

$__index__ = 'spreedly';
$__sc_options__[$__index__] = 'Pin Payments';
$__sc_affiliates__[$__index__] = '#';
$__sc_videotutorial__[$__index__] = '#';

if (wlm_arrval($_GET, 'cart') == $__index__) {
	include_once($x = $this->pluginDir . '/extlib/class.spreedly.inc');
	if (!$__INTERFACE__) {
		// BEGIN Initialization
		$spreedlythankyou = $this->GetOption('spreedlythankyou');
		if (!$spreedlythankyou) {
			$this->SaveOption('spreedlythankyou', $spreedlythankyou = $this->MakeRegURL());
		}

		// save POST URL
		if (wlm_arrval($_POST, 'spreedlythankyou')) {
			$_POST['spreedlythankyou'] = trim(wlm_arrval($_POST, 'spreedlythankyou'));
			$wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['spreedlythankyou']));
			if ($wpmx == $_POST['spreedlythankyou']) {
				if ($this->RegURLExists($wpmx, null, 'spreedlythankyou')) {
					echo "<div class='error fade'>" . __('<p><b>Error:</b> Pin Payments Thank You URL (' . $wpmx . ') is already in use by a Membership Level or another Shopping Cart.  Please try a different one.</p>', 'wishlist-member') . "</div>";
				} else {
					$this->SaveOption('spreedlythankyou', $spreedlythankyou = $wpmx);
					echo "<div class='updated fade'>" . __('<p>Thank You URL Changed.&nbsp; Make sure to update Pin Payments with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
				}
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save API Key
		if (wlm_arrval($_POST, 'spreedlytoken')) {
			$_POST['spreedlytoken'] = trim(wlm_arrval($_POST, 'spreedlytoken'));
			$wpmy = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['spreedlytoken']));
			if ($wpmy == $_POST['spreedlytoken']) {
				$this->SaveOption('spreedlytoken', $spreedlytoken = $wpmy);
				echo "<div class='updated fade'>" . __('<p>API Token Changed.&nbsp; Make sure that your API Token matches the one specified in your Pin Payments site configuration.</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p><b>Error:</b> Pin Payments API Token may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
			}
		}
		// save Machine Name
		if (wlm_arrval($_POST, 'spreedlyname')) {
			$_POST['spreedlyname'] = trim(wlm_arrval($_POST, 'spreedlyname'));
			$this->SaveOption('spreedlyname', $_POST['spreedlyname']);
			echo "<div class='updated fade'>" . __('<p>Site Name Changed.</p>', 'wishlist-member') . "</div>";
		}

		$spreedlythankyou_url = $wpm_scregister . $spreedlythankyou;
		$spreedlytoken = $this->GetOption('spreedlytoken');
		$spreedlyname = $this->GetOption('spreedlyname');

		// END Initialization
	} else {
		// START Interface
		$r = array();
		if ($spreedlytoken && $spreedlyname) {
			Spreedly::configure($spreedlyname, $spreedlytoken);
			$r = SpreedlySubscriptionPlan::get_all();
			if (isset($r['ErrorCode'])) {
				if ($r['ErrorCode'] == '401') {
					echo "<div class='error fade'>" . __('<p>Invalid Pin Payments API Credentials.</p>', 'wishlist-member') . "</div>";
				} else {
					echo "<div class='error fade'>" . __("<p>{$r['Response']}</p>", 'wishlist-member') . "</div>";
				}
			}
		}
		//get Pay Per Post
		$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
		$post_types = get_post_types('', 'objects');
		//get for_approval_registrations
		$for_approval_registration = $this->GetOption('wlm_for_approval_registration');
		if (!$for_approval_registration) {
			$for_approval_registration = array();
		} else {
			$for_approval_registration = unserialize($for_approval_registration);
		}
		$regurl = WLMREGISTERURL;

		?>
		<!-- Spreedly -->
		<h2 style="font-size:18px;width:100%"><?php _e('Pin Payments Integration <em>(formerly known as Spreedly)</em>', 'wishlist-member'); ?></h2>
		<p><?php _e('Integrating WishList Member to Pin Payments can be done in 5 steps', 'wishlist-member'); ?></p>
		<blockquote>
			<h2 style="font-size:18px;"><?php _e('Step 1. Provide the API Credentials', 'wishlist-member'); ?></h2>
			<blockquote>
				<form method="post">
					<table class="form-table">
						<tr>
							<th scope="row">Short Site Name:</th>
							<td>
								<input type="text" name="spreedlyname" value="<?php echo $spreedlyname ?>" size="40" />
								<?php echo $this->Tooltip("integration-shoppingcart-spreedly-site-name"); ?>
								<br /><span class="small">Found in your <strong>Pin Payments Site Configuration &raquo; Short site name</strong></span>
							</td>
						</tr>
						<tr>
							<th scope="row">API Authentication Token:</th>
							<td>
								<input type="text" name="spreedlytoken" value="<?php echo $spreedlytoken ?>" size="40" />
								<?php echo $this->Tooltip("integration-shoppingcart-spreedly-token"); ?>
								<br /><span class="small">Found in your <strong>Pin Payments Site Configuration &raquo; API Authentication Token</strong></span>
							</td>
						</tr>
					</table>
					<p class="submit">
						<input type="submit" class="button-primary" id="updatecredentials" value="<?php _e('Update API Credentials', 'wishlist-member'); ?>" />
					</p>
				</form>
			</blockquote>
			<h2 style="font-size:18px;"><?php _e('Step 2.Set the "Subscribers Changed Notification URL" to the link below:', 'wishlist-member'); ?></h2>
			<span class="small">Found in your <strong>Pin Payments Site Configuration &raquo; Subscribers Changed Notification URL</strong></span>
			<p>&nbsp;<a href="<?php echo $spreedlythankyou_url ?>" onclick="return false"><?php echo $spreedlythankyou_url ?></a></p>

				<h2 style="font-size:18px;"><?php _e('Step 3. Create a Plan for your Membership Level using the details below.', 'wishlist-member'); ?></h2>
				<span class="small">After you created your Plan, reload the page or click the button at the bottom to generate the subscription link in Step 4.</span>
				<blockquote>
					<h3>Membership Levels</h3>
					<table class="widefat">
						<thead>
							<tr>
								<th scope="col" width="35%"><?php _e('Level Name', 'wishlist-member'); ?></th>
								<th scope="col" width="17%"><?php _e('Feature Level', 'wishlist-member'); ?></th>
								<th scope="col"  width="48%"><?php _e('URL a customer is returned to on sale', 'wishlist-member'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
							$alt = 0;
							foreach ((array) $wpm_levels AS $sku => $level):
						?>
								<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
									<td><b><?php echo $level['name'] ?></b></td>
									<td><u style="font-size:1.2em"><?php echo $sku ?></u></td>
									<td><u><?php echo $spreedlythankyou_url . "?sku=" . $sku ?></u></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</blockquote>
				<!-- Pay Per Post -->
				<?php
				foreach ($xposts AS $post_type => $posts) : ?>
					<?php if(count($posts)) : ?>
					<blockquote>
					<h3><?php echo $post_types[$post_type]->labels->name; ?></h3>
						<table class="widefat" style="border-top:none">
							<thead>
								<tr>
									<th scope="col" width="35%">Post Title</th>
									<th scope="col" width="17%">Featured Level</th>
									<th scope="col" width="48%">URL a customer is returned to on sale</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$alt = 0;
									foreach ($posts AS $post) :
								?>
										<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" >
											<td><b><?php echo $post->post_title; ?></b></td>
											<td><u style="font-size:1.2em"><?php printf("%s", 'payperpost-' . $post->ID); ?></u></td>
											<td><u><?php echo $spreedlythankyou_url . "?sku="; ?><?php printf("%s", 'payperpost-' . $post->ID); ?></u></td>
										</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</blockquote>
					<?php endif; ?>
				<?php endforeach; ?>

				<h2 style="font-size:18px;"><?php _e('Step 4. Copy and Paste the Subscription Link below.', 'wishlist-member'); ?></h2>
				<span class="small">Paste the Subscription Link in your Sales page. You need to setup your Plan in Step 3 first for the subscription link to appear.</span>
				<form method="post" style="text-align:right;">
					<input type="submit" class="button-secondary" id="refresh" value="<?php _e('Refresh Subscription Link', 'wishlist-member'); ?>" />
				</form>
				<blockquote>
				<?php
					$subscription_url = array();

					foreach ((array) $r AS $id => $data):
						$return_url = $spreedlythankyou_url . "?sku=" . $data->feature_level;
						//settings to override after registration
						$level_settings = array(
							"afterregredirect" => get_bloginfo('url') ."/index.php/register/{$spreedlythankyou}?reg_id={$data->id}",
							"requireemailconfirmation" => 0
						);
						if(strpos($data->feature_level,'payperpost') !== false && $return_url == trim($data->return_url)){
							$pp = $this->IsPPPLevel($data->feature_level);
							if($pp){
								$level_settings["name"] = $pp->post_title;
								$for_approval_registration[$data->id] = array(
									"level" => $data->feature_level,
									"name" => "Pin Payments",
									"txnmark"=>"PinPay",
									"level_settings"=>$level_settings
								);
								$subscription_url[$data->feature_level] = array ( "dataid" => $data->id );
							}
							
						}else if(array_key_exists($data->feature_level, $wpm_levels) && $return_url == trim($data->return_url)){
								$level_settings["name"] = $wpm_levels[$data->feature_level]["name"];
								$for_approval_registration[$data->id] = array(
									"level" => $data->feature_level,
								 	"name" => "Pin Payments",
								 	"txnmark"=>"PinPay",
								 	"level_settings"=>$level_settings
								);
								$subscription_url[$data->feature_level] = array ( "dataid" => $data->id );
						}
						
					endforeach; 
				?>				
					<h3>Membership Levels</h3>
					<table class="widefat">
						<thead>
							<tr>
								<th scope="col" width="35%"><?php _e('Level Name', 'wishlist-member'); ?></th>
								<th scope="col" width="65%"><?php _e('Subscription Link', 'wishlist-member'); ?><?php echo $this->Tooltip("integration-shoppingcart-spreedly-sub-link"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$alt = 0;
							$subscription_count = 0;
							foreach ((array) $wpm_levels AS $id => $level):
								?>
								<?php
								if (array_key_exists($id, $subscription_url)):
									$subscription_count+=1;
								?>
									<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>">
										<td><b><?php echo $level['name'] ?></b></td>
										<td >
											<?php echo $regurl . "/" . $subscription_url[$id]["dataid"]; ?>
										</td>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>

							<?php if (!$spreedlytoken || !$spreedlyname): ?>
								<tr >
									<td colspan="2" style="text-align:center;"><p>Please provide your API Details in Step 1.</p></td>
								</tr>
							<?php elseif (isset($r['ErrorCode'])): ?>
								<tr >
									<td colspan="2" ><p style="text-align:center;color:red;">You have an invalid Pin Payments API credentials</p></td>
								</tr>
							<?php elseif ($subscription_count <= 0): ?>
								<tr >
									<td colspan="2" style="text-align:center;"><p>Please create a Plan using the data above and click the "Refresh Subscription Link" button.</p></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</blockquote>
				<!-- Pay Per Post -->
				<?php
				foreach ($xposts AS $post_type => $posts) : ?>
					<?php if(count($posts)) : ?>
					<blockquote>
					<h3><?php echo $post_types[$post_type]->labels->name; ?></h3>
						<table class="widefat" style="border-top:none">
							<thead>
								<tr>
									<th scope="col" width="35%"><?php _e('Post Title', 'wishlist-member'); ?></th>
									<th scope="col" width="65%"><?php _e('Subscription Link', 'wishlist-member'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
									$alt = 0;
									$subscription_count = 0;
									foreach ($posts AS $post) :									
								?>
										<?php
										$key_id = "payperpost-" .$post->ID;
										if (array_key_exists($key_id, $subscription_url)):
											$subscription_count+=1;
										?>
											<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>">
												<td><b><?php echo $post->post_title; ?></b></td>
												<td >
													<?php echo $regurl . "/" . $subscription_url[$key_id]["dataid"]; ?>
												</td>
											</tr>
										<?php endif; ?>

								<?php endforeach; ?>
									<?php if (!$spreedlytoken || !$spreedlyname): ?>
										<tr >
											<td colspan="2" style="text-align:center;"><p>Please provide your API Details in Step 1.</p></td>
										</tr>
									<?php elseif (isset($r['ErrorCode'])): ?>
										<tr >
											<td colspan="2" ><p style="text-align:center;color:red;">You have an invalid Pin Payments API credentials</p></td>
										</tr>
									<?php elseif ($subscription_count <= 0): ?>
										<tr >
											<td colspan="2" style="text-align:center;"><p>Please create a Plan using the data above and click the "Refresh Subscription Link" button.</p></td>
										</tr>
									<?php endif; ?>								
							</tbody>
						</table>
					</blockquote>
					<?php endif; ?>
				<?php endforeach; ?>
				<form method="post">
					<p class="submit" style="text-align:right;">
						<input type="submit" class="button-secondary" id="refresh" value="<?php _e('Refresh Subscription Link', 'wishlist-member'); ?>" />
					</p>
				</form>
<?php
	//save the for_approval_registration
	$this->SaveOption('wlm_for_approval_registration', serialize($for_approval_registration));
	include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.spreedly.tooltips.php');
	// END Interface
	}
}
?>
