<?php
$webinars = $webinar_settings['easywebinar'];
$webinar_list = array();

if(class_exists('webinar_db_interaction')) {
	$wdb = new webinar_db_interaction();
	$webinar_list = $wdb->get_all_webinar();
}
?>
<p class="alignright"><a href="http://customers.wishlistproducts.com/easywebinar-integration/" target="_blank"><?php _e('Watch Integration Video Tutorial', 'wishlist-member'); ?></a></p>
<h2 style="font-size:18px;border:none;"><?php _e('Easy Webinar Integration', 'wishlist-member'); ?></h2>
<p><a href=" http://wlplink.com/go/easywebinar" target="_blank"><?php _e('Learn more about Easy Webinar', 'wishlist-member'); ?></a></p>
<form method="post">
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
				<th scope="col"><?php _e('Webinar', 'wishlist-member'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($wpm_levels AS $levelid => $level): ?>
				<tr>
					<th scope="row"><?php echo $level['name']; ?></th>
					<td>
						<select name="webinar[easywebinar][<?php echo $levelid; ?>]">
							<option value="">--Select a webinar--</option>
							<?php foreach($webinar_list as $w): ?>
							<?php $selected=$webinars[$levelid]==$w->webinar_id_pk? 'selected="selected"' : null ?>
							<option <?php echo $selected?> value="<?php echo $w->webinar_id_pk?>"><?php echo $w->webinar_event_name?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Update Webinar Settings', 'wishlist-member'); ?>" />
	</p>
</form>

