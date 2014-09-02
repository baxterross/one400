<?php
$mergecode = '<b>' . __('Merge Codes', 'wishlist-member') . '</b><br /><br />';
$mergecode.='[level] : ' . __('Membership Level', 'wishlist-member') . '<br /><br />' . __('Registration Links', 'wishlist-member') . '<br />';
$mergecode.='[newlink] : ' . __('New Member', 'wishlist-member') . '<br />';
$mergecode.='[existinglink] : ' . __('Existing Member', 'wishlist-member') . '<br />';

$activate = false;
if (wlm_arrval($_POST,'reg_instructions_new_reset')) {
	$activate = true;
	$this->DeleteOption('reg_instructions_new');
}
if (wlm_arrval($_POST,'reg_instructions_new_noexisting_reset')) {
	$activate = true;
	$this->DeleteOption('reg_instructions_new_noexisting');
}
if (wlm_arrval($_POST,'reg_instructions_existing_reset')) {
	$activate = true;
	$this->DeleteOption('reg_instructions_existing');
}
if ($activate) {
	$this->Activate();
}
?>
<form method="post">
	<h2><?php _e('Registration Instructions', 'wishlist-member'); ?></h2>
	<h3><?php _e('New Member Registration', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Full Instructions', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-advanced-tooltips-New-Member-Registration-Full-Instructions"); ?><p><?php echo $mergecode; ?></p></th>
		<td>
			<textarea name="<?php $this->Option('reg_instructions_new', true); ?>" cols="70" rows="10"><?php $this->OptionValue(); ?></textarea>
			<br />
			<label><input type="checkbox" name="reg_instructions_new_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>
		</td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Instructions if "Existing Users Link" is Disabled', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-advanced-tooltips-Instructions-if-Existing-Users-Link-is-Disabled"); ?><p><?php echo $mergecode; ?></p></th>
		<td>
			<textarea name="<?php $this->Option('reg_instructions_new_noexisting', true); ?>" cols="70" rows="10"><?php $this->OptionValue(); ?></textarea>
			<br />
			<label><input type="checkbox" name="reg_instructions_new_noexisting_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>
		</td>
		</tr>
	</table>
	<h3><?php _e('Existing Member Registration', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Full Instructions', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-advanced-tooltips-Existing-Member-Registration-Full-Instructions"); ?><p><?php echo $mergecode; ?></p></th>
		<td>
			<textarea name="<?php $this->Option('reg_instructions_existing', true); ?>" cols="70" rows="10"><?php $this->OptionValue(); ?></textarea>
			<br />
			<label><input type="checkbox" name="reg_instructions_existing_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>
		</td>
		</tr>
	</table>
	<p class="submit">
		<?php
		$this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
	</p>
</form>
