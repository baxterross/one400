<?php
$base_url = $this->QueryString('mode', 'level', 'mode2', 'action', 'id');
$regpage_base_url = $this->QueryString('mode2', 'action', 'id', 'level', 'form_id');
$config_base_url = $this->QueryString('mode2');
$mode = @$_GET['mode'];
?>
<?php if ($show_page_menu) : ?>
	<ul class="wlm-sub-menu">
		<li<?php echo (!$_GET['mode']) ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>"><?php _e('Configuration', 'wishlist-member'); ?></a></li>
		<li<?php echo ($_GET['mode'] == 'display') ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=display"><?php _e('Display Settings', 'wishlist-member'); ?></a></li>
	</ul>
<?php return; endif; ?>
<?php if ($show_page_menu) : ?>
<?php return; endif; ?>
<?php 
if ( empty($mode)  ) {
	include('settings-configuration.php');
} elseif ( $mode == 'display'  ) {
	include('settings-display.php');
}

require_once('tooltips/settings.tooltips.php');