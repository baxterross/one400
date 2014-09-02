<?php

$__index__ = 'madmimi';
$__ar_options__[$__index__] = 'Madmimi';
$__ar_affiliates__[$__index__] = 'http://wlplink.com/go/madmimi';
$__ar_videotutorial__[$__index__] = 'http://customers.wishlistproducts.com/madmimi-integration/';

if ($data['ARProvider'] == $__index__):
	if ($__INTERFACE__):
		$class_file = $this->pluginDir . '/extlib/madmimi/madmimi.php';
		include $class_file;
		$mmm = false;

		$lists = array();
		if(!empty($data['madmimi']['username']) && !empty($data['madmimi']['api_key'])) {

			$mmm = new WPMadmimi($data['madmimi']['username'], $data['madmimi']['api_key']);
			$lists = $mmm->get_lists();
		}
		?>
		<form method="post">
			<input type="hidden" name="saveAR" value="saveAR" />

			<h3>API Settings</h3>
			<table class="form-table">
				<tr>
					<th>Username/Email</th>
					<td><input size="50" type="text" name="ar[username]" value="<?php echo $data['madmimi']['username']?>"/></td>
				</tr>
				<tr>
					<th>API Key</th>
					<td><input size="50" type="text" name="ar[api_key]" value="<?php echo $data['madmimi']['api_key']?>"/></td>
				</tr>


			</table>
			<h3>Level/List Mappings</h3>
			<table class="widefat">
				<thead>
					<th>Membership Level</th>
					<th>List</th>
					<th>Auto Unsubscribe</th>
				</thead>
				<?php foreach($wpm_levels as $i => $l): ?>
					<tr>
						<th scope="row"><?php echo $l['name']?></th>
						<td style="overflow:visible;">
							<select name="ar[maps][<?php echo $i?>][]" multiple class="chosen-select" style="width: 150px;" data-placeholder="Select Lists">
								<option></option>
								<?php foreach($lists as $l): ?>
								<?php $selected = in_array($l->name, $data['madmimi']['maps'][$i])? 'selected="selected"' : null ?>
								<option <?php echo $selected?> value="<?php echo $l->name?>"><?php echo $l->name?></option>
							<?php endforeach; ?>
							</select>
						</td>
						<td>
							<input <?php if($data['madmimi'][$i]['autoremove'] == 1) echo 'checked="checked"'?> type="checkbox" name="ar[<?php echo $i?>][autoremove]" value="1"/>
						</td>
					</tr>
				<?php endforeach; ?>

			</table>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Update AutoResponder Settings', 'wishlist-member'); ?>" />
			</p>
		</form>
		<script type="text/javascript">
		jQuery(function($) {
			$('.chosen-select').chosen({disable_search: true});
		});
		</script>
		<style type="text/css">
		.chosen-container-multi .chosen-choices {
			background-color: #fff;
			  background-image: -webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(1%, #eeeeee), color-stop(15%, #ffffff));
			  background-image: -webkit-linear-gradient(#eeeeee 1%, #ffffff 15%);
			  background-image: -moz-linear-gradient(#eeeeee 1%, #ffffff 15%);
			  background-image: -o-linear-gradient(#eeeeee 1%, #ffffff 15%);
			  background-image: linear-gradient(#eeeeee 1%, #ffffff 15%);
		}
		</style>
		<?php
	endif;
endif;
?>

