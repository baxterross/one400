<?php
include $this->pluginDir . '/lib/integration.webinar.gotomeetingapi.php';

//Save API CODE
if(isset($_POST['savegtmapicode'])) {

	$obj = new OAuth_En();
	$oauth = new OAuth($obj);
	$oauth->authorizeUsingResponseKey(trim($_POST['authorizationcode']));
	
	//if there's no error on api call, we save the access token and organizer key
	if(!$oauth->hasApiError()){
		$_POST['webinar']['accesstoken'] = $obj->getAccessToken();
		$_POST['webinar']['organizerkey'] = $obj->getOrganizerKey();
		$_POST['webinar']['authorizationcode'] = trim($_POST['authorizationcode']);
		$webinar_settings[$webinar_provider] = $_POST['webinar'];

		$this->SaveOption('webinar', $webinar_settings);
		
		$webinar_settings = $this->GetOption('webinar');
		$webinars3 = $webinar_settings['gotomeetingapi'];
		
        $objOAuthEn = $oauth->getOAuthEntityClone();
	 
		$webinars = $oauth->getWebinars();
    } else {
		echo '<font color="red" size="6px">Sorry, incorrect Authorization Code</font>';
	}
} else {
	
	$webinar_settings = $this->GetOption('webinar');
	$webinars3 = $webinar_settings['gotomeetingapi'];
	$obj = new OAuth_En();
	$oauth = new OAuth($obj);
	

	// Check if we updated the gtm webinar info and if the authorization code was changed
	if(isset($_POST['updategtmapi'])) {

		if(trim($_POST['txtauthorizationcode2']) != $webinars3['authorizationcode']) {

			$oauth->authorizeUsingResponseKey(trim($_POST['txtauthorizationcode2']));
			
			//if there's no error on api call, we save the access token and organizer key
			if(!$oauth->hasApiError()){
			
				$_POST['webinar']['accesstoken'] = $obj->getAccessToken();
				$_POST['webinar']['organizerkey'] = $obj->getOrganizerKey();
				$_POST['webinar']['authorizationcode'] = trim($_POST['txtauthorizationcode2']);
				$webinar_settings[$webinar_provider] = $_POST['webinar'];

				$this->SaveOption('webinar', $webinar_settings);

				$webinar_settings = $this->GetOption('webinar');
				$webinars3 = $webinar_settings['gotomeetingapi'];

				$objOAuthEn = $oauth->getOAuthEntityClone();

				$webinars = $oauth->getWebinars();
			}  else {
				echo '<font color="red" size="6px">Sorry, incorrect Authorization Code</font>';
			}
		} else {
			$obj->setAccessToken($webinars3['accesstoken']);
			$obj->setOrganizerKey($webinars3['organizerkey']);
			$webinars = $oauth->getWebinars();
		}
	}
	// If it wasn't changed then we just fetch what's saved in the db
	else 
	{
		$obj->setAccessToken($webinars3['accesstoken']);
		$obj->setOrganizerKey($webinars3['organizerkey']);
		$webinars = $oauth->getWebinars();
	}
	
	
}

if(!$oauth->hasApiError()){
?>
<h2 style="font-size:18px;border:none;"><?php _e('GoToWebinar API<sup><small>&reg;</small></sup> Integration', 'wishlist-member'); ?></h2>
<p><a href=" http://wlplink.com/go/gotowebinar" target="_blank"><?php _e('Learn more about GotoWebinar<sup>&reg;</sup>', 'wishlist-member'); ?></a></p>
<br>
<a target="_blank" href="<?php echo 'https://api.citrixonline.com/oauth/authorize?client_id='.GOTO_WEBINAR_API_KEY.'&redirect_uri=http://wishlist-member.s3.amazonaws.com/gotowebinar/index.html'; ?>">
	Click here to obtain an authorization code and copy it into the box below 
</a>
<br><br>
<form method="post">
	<label>Authorization Code: </label>
		<input type="text" name="txtauthorizationcode2" size="50" value="<?php echo $webinars3['authorizationcode']; ?>">
		<br /><br />
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
				<th scope="col"><?php _e('GoToWebinar<sup><small>&reg;</small></sup>', 'wishlist-member'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($wpm_levels AS $levelid => $level): 
			$webinar4 = explode('---', $webinars3[$levelid]);
			
				?>
				<tr>
					<th scope="row"><?php echo $level['name']; ?></th>
					<td align="left">
						<select name="webinar[gotomeetingapi][<?php echo $levelid; ?>]" />
							<option value="<?php echo $webinar4[0]; ?>---<?php echo $webinar4[1]; ?>"><?php echo $webinar4[1]; ?></option>
							<option> </option>
							<?php foreach($webinars as $key => $webinar): ?>
							<option value="<?php echo $webinar->webinarKey; ?>---<?php echo $webinar->subject; ?>"><?php echo $webinar->subject; ?> </option>
							<?php endforeach; ?>
						
						</select>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="hidden" name="updategtmapi">
		<input type="hidden" name="webinar[gotomeetingapi][authorizationcode]" value="<?php echo $webinars3['authorizationcode']; ?>">
		<input type="hidden" name="webinar[gotomeetingapi][accesstoken]" value="<?php echo $webinars3['accesstoken']; ?>">
		<input type="hidden" name="webinar[gotomeetingapi][organizerkey]" value="<?php echo $webinars3['organizerkey']; ?>">
		<input type="submit" class="button-primary" value="<?php _e('Update Webinar Settings', 'wishlist-member'); ?>" />
	</p>
</form>
<?php
}else{

?>
<form method="post">
	<p>
		<a target="_blank" href="<?php echo 'https://api.citrixonline.com/oauth/authorize?client_id='.GOTO_WEBINAR_API_KEY.'&redirect_uri=http://wishlist-member.s3.amazonaws.com/gotowebinar/index.html'; ?>">
			Click here to obtain an authorization code and copy it into the box below 
		</a>
		<br><br>
		<label>Authorization Code: <input type="text" name="authorizationcode" size="50" value="<?php echo (isset($_POST['authorizationcode'])) ? $_POST['authorizationcode'] : ''; ?>">
	</p>
	<p class="submit">
		<input type="submit" name="savegtmapicode" class="button-primary" value="<?php _e('Update Webinar Settings', 'wishlist-member'); ?>" />
	</p>
</form>
<?php
}
?>
