<?php
$webinars = $webinar_settings['evergreen'];
?>
<p class="alignright"><a href="http://customers.wishlistproducts.com/evergreen-integration/" target="_blank"><?php _e('Watch Integration Video Tutorial', 'wishlist-member'); ?></a></p>
<h2 style="font-size:18px;border:none;"><?php _e('Evergreen Business System Integration', 'wishlist-member'); ?></h2>
<p><a href=" http://wlplink.com/go/evergreen" target="_blank"><?php _e('Learn more about Evergreen Business System', 'wishlist-member'); ?></a></p>
<p><?php _e('Note: Make sure to only have the First Name, Last Name and Email Address as required fields in your Webinar settings.', 'wishlist-member'); ?></p>
<form method="post">
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e('Membership Level', 'wishlist-member'); ?></th>
				<th scope="col"><?php _e('Registration Auto Link', 'wishlist-member'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($wpm_levels AS $levelid => $level): ?>
				<tr>
					<th scope="row"><?php echo $level['name']; ?></th>
					<td><input type="text" name="webinar[evergreen][<?php echo $levelid; ?>]" value="<?php echo $webinars[$levelid]; ?>" size="70" /></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Update Webinar Settings', 'wishlist-member'); ?>" />
	</p>
</form>

