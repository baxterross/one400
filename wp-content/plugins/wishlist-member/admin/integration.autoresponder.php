<?php
/*
 * Autoresponder Interface
 * Original Author : Mike Lopez
 * Version: $Id$
 */

$__integrations__ = glob($this->pluginDir . '/admin/integration.autoresponder.*.php');
$__INTERFACE__ = false;
foreach ((array) $__integrations__ AS $__integration__) {
	include($__integration__);
}

$data = $this->GetOption('Autoresponders');
if (wlm_arrval($_POST,'saveAR') == 'saveAR' && $_POST['ar']) {
	$data[$data['ARProvider']] = $_POST['ar'];
	$this->SaveOption('Autoresponders', $data);
	echo "<div class='updated fade'>" . __('<p>Your autoresponder settings have been updated.</p>', 'wishlist-member') . "</div>";
} elseif (isset($_POST['ARProvider'])) {
	$data['ARProvider'] = $_POST['ARProvider'];
	$this->SaveOption('Autoresponders', $data);
	echo "<div class='updated fade'>" . __('<p>Your autoresponder provider has been changed.</p>', 'wishlist-member') . "</div>";
}

if($data['ARProvider'] == 'aweber') {
	echo '<div class="updated fade">';
	echo '<form method="post">';
	echo '<p>We noticed that you are using the regular AWeber integration. We recommend that you use the <xbutton type="submit" name="ARProvider" value="aweberapi">AWeber API Integration</xbutton> instead.</p>';
	echo '</form>';
	echo '</div>';
}
?>

<h2 style="font-size:18px;border-bottom:none"><?php _e('AutoResponder Integration', 'wishlist-member'); ?></h2>
<p><?php _e('Automatically sign-up newly registered members to your autoresponder.', 'wishlist-member'); ?></p>
<form method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('AutoResponder Provider', 'wishlist-member'); ?></th>
			<td width="1" style="white-space:nowrap">
				<select name="ARProvider">
					<option value=""><?php _e('None', 'wishlist-member'); ?></option>
					<?php
					// sort by Name
					asort($__ar_options__);

					// Generic integration always goes last
					if (isset($__ar_options__['generic'])) {
						$x = $__ar_options__['generic'];
						unset($__ar_options__['generic']);
						$__ar_options__['generic'] = $x;
					}

					// display dropdown options
					foreach ((array) $__ar_options__ AS $key => $value) {
						$selected = ($data['ARProvider'] == $key) ? ' selected="true" ' : '';
						echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
					}
					?>
				</select> <?php echo $this->Tooltip("integration-autoresponder-tooltips-AR-Provider"); ?>
			</td>
			<td>
				<p class="submit" style="margin:0;padding:0"><input type="submit" class="button-secondary" value="<?php _e('Set AutoResponder Provider', 'wishlist-member'); ?>" /></p>
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
$__INTERFACE__ = true;
foreach ((array) $__integrations__ AS $__integration__) {
	include($__integration__);
}
include_once($this->pluginDir . '/admin/tooltips/integration.autoresponder.tooltips.php');
?>