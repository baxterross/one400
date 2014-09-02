<?php
/*
 * IContact Autoresponder API
 * Original Author : Fel Jun Palawan
 * Version: $Id$
 */

/*
  GENERAL PROGRAM NOTES: This script was based on Mike's autoresponder integration.
  Calling program : integration.autoresponder.php
  Logic Flow:
  1. integration.autoresponder.php displays this script (integration.autoresponder.icontact)
  and displays current settings
  2. on user update, this script submits value to integration.autoresponder.php, which in turn save the value
  3. after saving the values, control goes back to this page, and:
  3.1 this script do a curl request to Icontact to get the AccountID from Icontact then;
  3.2 do a curl request to Icontact to get the FolderID from Icontact
  3.3 save these two IDs (Account ID & Folder ID) to WL options using the SaveOption() function.

  Account ID & Folder ID are needed to make request to Icontact for subscribing & unsubscribing contacts
 */

$__index__ = 'icontact';
$__ar_options__[$__index__] = 'IContact';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		?>
		<?php
//after user saves the autoresponder options, script will get AccountID & FolderID
		//this part will attempt to get the AccountID from Icontact
		$icdata = $_POST['ar'];
		$icUserName = $data[$__index__]['icusername']; //$icdata['icusername'];
		$icAppPassword = $data[$__index__]['icapipassword']; //$icdata['icapipassword'];
		$icAppID = $data[$__index__]['icapiid']; //$icdata['icapiid'];

		if (!function_exists("curl_init")) {
			die("cURL extension is not installed");
		}
		$headers = array(
			'Accept: text/xml',
			'Content-Type: text/xml',
			'API-Version: 2.0',
			'API-AppId: ' . $icAppID,
			'API-Username: ' . $icUserName,
			'API-Password: ' . $icAppPassword,
		);
		$url = "https://app.icontact.com/icp/a/";
		$ch1 = curl_init();
		curl_setopt($ch1, CURLOPT_URL, $url);
		curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
		$icresponse = curl_exec($ch1);
		curl_close($ch1);
		preg_match('/<accountId(.*)?>(.*)?<\/accountId>/', $icresponse, $match);
		$icAcctID = $match[2];
		if (!empty($icAcctID)) { // get  the Account ID
			if (!function_exists("curl_init"))
				die("cURL extension is not installed");
			$url = "https://app.icontact.com/icp/a/{$icAcctID}/c";
			$ch2 = curl_init();
			curl_setopt($ch2, CURLOPT_URL, $url);
			curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
			$icresponse = curl_exec($ch2);
			curl_close($ch2);
			preg_match('/<clientFolderId(.*)?>(.*)?<\/clientFolderId>/', $icresponse, $match);
			$icFolderID = $match[2];
		}

		$iclog = $data[$__index__]['iclog'];
		$icID = $data[$__index__]['icID'];
		foreach ((array) $iclog as $key => $value) {
			if ($value == 1 and $icID[$key] != "") {
				$date = date("F j, Y, h:i:s A");
				$logfile = ABSPATH . $icID[$key] . ".txt";
				if (file_exists($logfile)) {
					$logfilehandler = fopen($logfile, 'a');
				} else {
					$logfilehandler = fopen($logfile, 'w');
				}
				if (!$logfilehandler) {
					echo "<div class='error fade'>" . __('<p>Error Creating Log File. Please check folder permission or manually create the file ' . ABSPATH . $logfile . '</p>', 'wishlist-member') . "</div>";
				} else {
					fclose($logfilehandler);
				}
			}
		}

		if (isset($_GET['action']) == 'clear' && isset($_GET['level']) != "" && !isset($_POST['update_icontact'])) {
			$logfile = ABSPATH . $icID[wlm_arrval($_GET,'level')] . ".txt";
			if (file_exists($logfile)) {
				$logfilehandler = fopen($logfile, 'w');
			}
			if (!$logfilehandler) {
				echo "<div class='error fade'>" . __('<p>Error Clearing Log File. Please check folder permission or manually clear the file ' . ABSPATH . $logfile . '</p>', 'wishlist-member') . "</div>";
			} else {
				echo "<div class='error fade'>" . __('<p>Successfully cleared the file ' . ABSPATH . $logfile . '</p>', 'wishlist-member') . "</div>";
				fclose($logfilehandler);
			}
		}
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 1. Create IContact Integration Password', 'wishlist-member'); ?></h2>
			<blockquote><p>
					<?php _e('1.Visit ', 'wishlist-member'); ?> <a href="https://app.icontact.com/icp/core/externallogin" target="_blank">https://app.icontact.com/icp/core/externallogin</a><br />
					<?php _e('2. Enter your iContact username and password to log in to iContact.', 'wishlist-member'); ?> <br />
					<?php _e('3. Enter <strong>60ZU6Al45lBtMmpi1S8tJqsvXdrNK18H</strong> on the Application ID field.', 'wishlist-member'); ?> <br />
					<?php _e('4. Enter your desired APPLICATION PASSWORD (<span style="font-style:italic;">This will be used on the next step</span>).'); ?> <br />
					<?php _e('<span style="font-style:italic;">Note: For security reasons, we recommend that this password be different than your iContact password.</span>', 'wishlist-member'); ?> <br />
					<?php _e('5. Click Save.', 'wishlist-member'); ?> <br />
				</p></blockquote>
			<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 2. Generate Account and Folder ID', 'wishlist-member'); ?></h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('IContact Username: ', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[icusername]" value="<?php echo $data[$__index__]['icusername']; ?>" size="40" />
						<?php echo $this->Tooltip("integration-autoresponder-icontact-tooltips-get-username"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Application Password', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[icapipassword]" value="<?php echo $data[$__index__]['icapipassword']; ?>" size="40" />
						<?php echo $this->Tooltip("integration-autoresponder-icontact-tooltips-get-pass"); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Account ID', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[icaccountid]" value="<?php echo $icAcctID; ?>" size="40"  readonly="readonly"/>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Folder ID', 'wishlist-member'); ?></th>
					<td>
						<input type="text" name="ar[icfolderid]" value="<?php echo $icFolderID; ?>" size="40"  readonly="readonly"/>
					</td>
				</tr>
			</table>
			<?php if ($icAcctID == "" || $icFolderID == "") { ?>
				<p class="submit">
					<input type="hidden" name="saveAR" value="saveAR" />
					<input type="hidden" name="ar[icapiid]" value="60ZU6Al45lBtMmpi1S8tJqsvXdrNK18H" />
					<input type="submit" class="button-secondary" value="<?php _e('Generate Folder and Account ID', 'wishlist-member'); ?>" />&nbsp;&nbsp;&nbsp;
					<span style="font-style:italic;">Generate your Folder and Account ID to proceed on Step 3.</span>
				</p>
			<?php } else { ?>
				<br />
				<h2 style="font-size:18px;width:100%;border:none;"><?php _e('Step 3. Assign your Contact List ID', 'wishlist-member'); ?></h2>
				<span style="color:blue;font-style: italic;">Note: Due to API limitations, IContact Integration does not support unsubscription. Moving or Deleting from a membership level will not unsubcribe the user from your contact list. </span>
				<br />
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col"><?php _e('Contact List ID', 'wishlist-member'); ?></th>
							<th class="num"><?php _e('Create a log file for Unsubscribe Members', 'wishlist-member'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ((array) $wpm_levels AS $levelid => $level): ?>
							<tr>
								<th scope="row"><?php echo $level['name']; ?></th>
								<td><input type="text" name="ar[icID][<?php echo $levelid; ?>]" value="<?php echo $data[$__index__]['icID'][$levelid]; ?>" size="30" /></td>
								<?php $iclog = (($data[$__index__]['iclog'][$levelid] == 1) ? true : false); ?>
								<td class="num"><input type="checkbox" name="ar[iclog][<?php echo $levelid; ?>]" value="1" <?php echo $iclog ? "checked='checked'" : ""; ?> />
									<?php
									if ($iclog && $icID[$levelid] != "") {
										echo '<br />';
										echo '<a href="' . get_bloginfo('wpurl') . '/' . $data[$__index__]['icID'][$levelid] . '.txt" target="_blank">Download Log File</a>';
										echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
										echo '<a href="?page=WishListMember&wl=integration&mode=ar&action=clear&level=' . $levelid . '">Clear Log File</a>';
									} else {
										if ($icID[$levelid] != "") {
											echo '<span style="color:red">Empty List</span>';
										}
									}
									?>
								</td>
							<?php endforeach; ?>
					</tbody>
				</table>
				<p><?php _e('Get your Contact List ID opening your list found in "My Contacts" > "My List". Click the list name to open it.', 'wishlist-member'); ?></p>
				<p><?php _e('In the address bar of your browser check the URL.', 'wishlist-member'); ?> </p>
				<p><?php _e('Sample URL:  ', 'wishlist-member'); ?>https://app.icontact.com/icp/core/mycontacts/lists/edit/<strong>35079</strong>/?token.....</p>
				<p><?php _e('<b>35079</b> is your Contact List ID', 'wishlist-member'); ?> </p>
				<p class="submit">
					<input type="hidden" name="saveAR" value="saveAR" />
					<input type="hidden" name="ar[icapiid]" value="60ZU6Al45lBtMmpi1S8tJqsvXdrNK18H" />
					<input type="submit" class="button-primary" name="update_icontact" value="<?php _e('Update IContact Settings', 'wishlist-member'); ?>" />
				</p>
			<?php } ?>
		</form>
		<?php
		include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.icontact.tooltips.php');
	endif;
endif;
?>
