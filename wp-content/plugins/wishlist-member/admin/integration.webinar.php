<?php
$webinars = $this->WebinarIntegrations;
$webinar_provider = $this->GetOption('WebinarProvider');
$webinar_settings = $this->GetOption('webinar');
if(isset($_POST['WebinarProvider'])) {
	$webinar_provider = $_POST['WebinarProvider'];
	$this->SaveOption('WebinarProvider', $webinar_provider);
}


if (!empty($_POST['webinar'][$webinar_provider])) {
	$webinar_settings[$webinar_provider] = $_POST['webinar'][$webinar_provider];
	$this->SaveOption('webinar', $webinar_settings);
}
?>

<h2 style="font-size:18px;border-bottom:none"><?php _e('Webinar Integration', 'wishlist-member'); ?></h2>
<p><?php _e('Automatically sign-up newly registered members to your webinar.', 'wishlist-member'); ?></p>
<form method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Webinar Provider', 'wishlist-member'); ?></th>
			<td width="1" style="white-space:nowrap">
				<select name="WebinarProvider">
					<option value=""><?php _e('None', 'wishlist-member'); ?></option>
					<?php foreach($GLOBALS['wishlist_member_webinars'] as $w): ?>
					<?php $selected = $w['optionname'] == $webinar_provider? 'selected="selected"' : false; ?>
					<option <?php echo $selected?> value="<?php echo $w['optionname']?>"><?php echo $w['name']?></option>
					<?php endforeach; ?>
				</select> <?php echo $this->Tooltip("integration-autoresponder-tooltips-AR-Provider"); ?>
			</td>
			<td>
				<p class="submit" style="margin:0;padding:0"><input type="submit" class="button-secondary" value="<?php _e('Set Webinar Provider', 'wishlist-member'); ?>" /></p>
			</td>
			<td>
				<?php if (isset($__ar_affiliates__[$data['ARProvider']])): ?>
					<a href="<?php echo $__ar_affiliates__[$data['ARProvider']]; ?>" target="_blank" style="font-size:1.2em"><?php printf(__('Learn more about %1$s', 'wishlist-member'), $__ar_options__[$data['ARProvider']]); ?></a>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<hr />
	<?php if (!empty($__ar_videotutorial__[$data['ARProvider']])): ?>
		<p class="alignright" style="margin-top:0"><a href="<?php echo $__ar_videotutorial__[$data['ARProvider']]; ?>" target="_blank"><?php _e('Watch Integration Video Tutorial', 'wishlist-member'); ?></a></p>
	<?php endif; ?>
	<br />
</form>
<?php
$__integrations__ = glob($this->pluginDir . '/admin/integration.webinar.*.php');
foreach ((array) $__integrations__ AS $__integration__) {
	if(stripos($__integration__, 'webinar.'.$webinar_provider.'.php') > 0) {
		include $__integration__;
	}
}
include_once($this->pluginDir . '/admin/tooltips/integration.webinar.tooltips.php');
?>