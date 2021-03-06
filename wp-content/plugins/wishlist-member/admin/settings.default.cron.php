<p><?php _e('A Cron Job allows WishList Member to execute its scheduled tasks such as sequential upgrades and sending of queued mails more reliably.', 'wishlist-member'); ?></p>
<p><?php _e("If you are unfamiliar or uncomfortable setting up a Cron Job, please contact your hosting provider and give them the information below.  Your hosting provider can then set this up for you.<br/>Setting the Cron Job for Once a day is recommended.", "wishlist-member")?></p>
<h3><?php _e('Cron Job Details', 'wishlist-member'); ?></h3>
<p><?php _e('Settings:', 'wishlist-member'); ?></p>
<pre style="margin-left:25px">0 * * * *</pre>
<p><?php _e('Command:', 'wishlist-member'); ?></p>
<pre style="margin-left:25px">/usr/bin/wget -O - -q -t 1 <?php echo get_bloginfo('url'); ?>/?wlmcron=1</pre>
<p>&middot; <?php _e('Copy the line above and paste it into the command line of your Cron job.', 'wishlist-member'); ?></p>
<p>&middot; <?php _e('Note: If the above command doesn\'t work, please try the following instead:', 'wishlist-member'); ?></p>
<pre style="margin-left:25px">/usr/bin/GET -d <?php echo get_bloginfo('url'); ?>/?wlmcron=1</pre>
<br /><br />
<?php
include_once($this->pluginDir . '/admin/tooltips/settings.cron.tooltips.php');
?>