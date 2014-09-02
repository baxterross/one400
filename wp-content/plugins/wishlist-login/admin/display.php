<?php
$base_url = $this->QueryString('mode', 'level', 'mode2', 'action', 'id');
$regpage_base_url = $this->QueryString('mode2', 'action', 'id', 'level', 'form_id');
$config_base_url = $this->QueryString('mode2');
$mode = @$_GET['mode'];
?>
<?php if ($show_page_menu) : ?>
	<ul class="wlm-sub-menu">
		<li<?php echo (!$_GET['mode']) ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>"><?php _e('General', 'wishlist-member'); ?></a></li>
		<li<?php echo ($_GET['mode'] == 'popup') ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=popup"><?php _e('Popup', 'wishlist-member'); ?></a></li>
		<li<?php echo ($_GET['mode'] == 'postlogin') ? ' class="current"' : '' ?>><a href="?<?php echo $base_url; ?>&mode=postlogin"><?php _e('Post Login', 'wishlist-member'); ?></a></li>
	</ul>
<?php return; endif; ?>

<?php 
if ( empty($mode)  ) {
	include('display-general.php');
} elseif ( $mode == 'postlogin'  ) {
	include('display-postlogin.php');
} elseif ( $mode == 'widget'  ) {
	include('display-widget.php');
} elseif ( $mode == 'popup'  ) {
	include('display-popup.php');
} elseif ( $mode == 'merge'  ) {
	include('display-mergecode.php');
}