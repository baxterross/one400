<?php
$custom_post_types = get_post_types(array('_builtin' => false), 'object');
$wpm_levels = $this->GetOption('wpm_levels');
$cprotect = wlm_arrval($_GET,'level') == 'Protection';
$payperpost = wlm_arrval($_GET,'level') == 'PayPerPost';
$user = false;
$allowed_user_content = array_merge(array('pages', ''), array_keys($custom_post_types));
if (substr($_GET['level'], 0, 2) == 'U-' || $payperpost) {
	$user = get_userdata(substr($_GET['level'], 2));
	if ($user !== false || $payperpost) {
		$name = trim($user->user_firstname . ' ' . $user->user_lastname);
		if ($name == '') {
			$name = $user->user_login;
		}
		if (!in_array($_GET['show'], $allowed_user_content)) {
			$_GET['show'] = '';
		}
	}
}
?>
<?php if ($show_page_menu) : ?>
	<ul class="wlm-sub-menu">
		<?php if ($this->access_control->current_user_can('wishlistmember_managecontent_posts')): ?>
			<li<?php echo (!wlm_arrval($_GET,'show')) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('show', 'offset') ?>"><?php _e('Posts', 'wishlist-member'); ?></a></li>
		<?php endif; ?>

		<?php if ($this->access_control->current_user_can('wishlistmember_managecontent_pages')): ?>
			<li<?php echo (wlm_arrval($_GET,'show') == 'pages') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('show', 'offset') ?>&show=pages"><?php _e('Pages', 'wishlist-member'); ?></a></li>
		<?php endif; ?>
		<?php if (count($custom_post_types)): ?>
			<?php foreach ($custom_post_types AS $custom_post_type): if ($this->PostTypeEnabled($custom_post_type->name)) : ?>
					<li<?php echo (wlm_arrval($_GET,'show') == $custom_post_type->name) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('show', 'offset') ?>&show=<?php echo $custom_post_type->name; ?>"><?php echo $custom_post_type->labels->name; ?></a></li>
				<?php endif;
			endforeach;
			?>
	<?php endif; ?>


		<?php if ($user === false && !$payperpost): ?>
			<?php if (!$cprotect): ?>

				<?php if ($this->access_control->current_user_can('wishlistmember_managecontent_comments')): ?>
					<li<?php echo (wlm_arrval($_GET,'show') == 'comments') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('show', 'offset') ?>&show=comments"><?php _e('Comments', 'wishlist-member'); ?></a></li><?php endif; ?>
			<?php endif; ?>

			<?php if ($this->access_control->current_user_can('wishlistmember_managecontent_categories')): ?>
				<li<?php echo (wlm_arrval($_GET,'show') == 'categories') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('show', 'offset') ?>&show=categories"><?php _e('Categories', 'wishlist-member'); ?></a></li>
			<?php endif; ?>

			<?php if ($this->access_control->current_user_can('wishlistmember_managecontent_files')): ?>
				<li<?php echo (wlm_arrval($_GET,'show') == 'files') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('show', 'offset') ?>&show=files"><?php _e('Files', 'wishlist-member'); ?></a></li>
			<?php endif; ?>

			<?php if ($this->access_control->current_user_can('wishlistmember_managecontent_folders')): ?>
				<li<?php echo (wlm_arrval($_GET,'show') == 'folders') ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString('show', 'offset') ?>&show=folders"><?php _e('Folders', 'wishlist-member'); ?></a></li>
			<?php endif; ?>
	<?php endif; ?>
		<li><?php echo $this->Tooltip("membershiplevels-content-tooltips-Manage-Specific-Membership-Content"); ?></li>
	</ul>
	<?php
	return;
endif;
?>
<h2>
	<?php _e('Manage Content', 'wishlist-member'); ?>
	<?php
	$show_name = $_GET['show'] ? $_GET['show'] : 'posts';
	if ($custom_post_types[$show_name]) {
		$show_name = $custom_post_types[$show_name]->labels->name;
	}
	$show_name = ucwords(strtolower($show_name));

	echo ' &raquo; ' . $show_name;

	if ($wpm_levels[wlm_arrval($_GET,'level')]) {
		echo ' &raquo; ' . $wpm_levels[wlm_arrval($_GET,'level')]['name'];
	} elseif ($cprotect) {
		echo ' &raquo; Content Protection';
	} elseif ($payperpost) {
		echo ' &raquo; Pay Per Post';
	} elseif ($user) {
		echo ' &raquo; User: ' . $name;
	}
	?>
</h2>
<br />
<?php
/*
 * Membership Levels -> Membership Content
 */


$show = $_GET['show'];
$contents = array_merge(array('pages', 'categories', 'comments', 'posts', 'files', 'folders1', 'folders'), array_keys($custom_post_types));
if (!in_array($show, $contents) || ($cprotect && $show == 'comments')) {
	$show = 'posts';
	unset($_GET['show']);
}

// check if folder protection need miggrate the seetings.
$this->FolderProtectionMigrate();

if (wlm_arrval($_GET,'fp') == 'easy') {
	$this->SaveOption('FolderProtectionMode', 'easy');
}
if (wlm_arrval($_GET,'fp') == 'advanced') {
	$this->SaveOption('FolderProtectionMode', 'advanced');
}
?>
<script type="text/javascript">
	function wlm_select_content_level(obj){
		if(obj.value=='---'){
			obj.selectedIndex=0;
			return;
		}
		if(obj.value=='__user_posts__'){
			obj.selectedIndex=0;
			tb_show("Select a User",'#TB_inline?inlineId=wlm_user_search_window');
			return;
		}
		top.location='?<?php echo $this->QueryString('level'); ?>&level='+obj.value;
		return;
	}
</script>
<div id="wlm_user_search_window" style="display:none">
	<div>
		<p>Enter part of a name or an email address then click "Search" to search for users</p>
		<form action='#' onsubmit="return false;" method="GET">
			<input type="search" id="user_search_input" value="" placeholder="User Search" size="30" />
			<input onclick="wlm_user_search('?<?php echo $this->QueryString('level'); ?>&level=U-')" type="button" class="button-secondary" value="Search" />
		</form>
		<hr />
		<div id="wlm_user_search_ajax_output"></div>
	</div>
</div>
<form>
<?php _e('Please select a membership level to manage below or select "Content Protection" to simply manage content protection:', 'wishlist-member'); ?><br />
	<blockquote>
		<select name="wpm_content_level" onchange="wlm_select_content_level(this)">
			<option>---</option>
			<option value="<?php echo $x = 'Protection'; ?>" <?php echo (wlm_arrval($_GET,'level') == $x) ? ' selected="true" ' : ''; ?>><?php _e('Content Protection', 'wishlist-member'); ?>
			<option value="<?php echo $x = 'PayPerPost'; ?>" <?php echo (wlm_arrval($_GET,'level') == $x) ? ' selected="true" ' : ''; ?>><?php _e('Pay Per Post', 'wishlist-member'); ?>
			</option>
			<option>---</option>
			<?php foreach ((array) $wpm_levels AS $id => $level) : ?>
				<option value="<?php echo $id ?>"<?php echo $id == $_GET['level'] ? ' selected="true"' : ''; ?>><?php echo $level['name'] ?></option>
			<?php endforeach; ?>
			<option>---</option>
			<option value="__user_posts__">User Posts</option>
			<?php
			if ($user !== false) {
				echo '<option>---</option>';
				echo '<option value="' . $_GET['level'] . '" selected="true">' . $name . ' (' . $user->user_login . ')</option>';
			}
			?>
		</select> <?php echo $this->Tooltip("membershiplevels-content-tooltips-select-a-membership-level-to-manage"); ?>
	</blockquote>
</form>
<?php
if ($wpm_levels[wlm_arrval($_GET,'level')] || $cprotect || $payperpost || $user !== false) :
	$level = &$wpm_levels[wlm_arrval($_GET,'level')];
	?>
	<h2 style="font-size:18px;"><a name="specific"></a><?php echo $cprotect ? 'Manage Content Protection' : 'Manage Specific Membership Content'; ?></h2>

	<?php
	if (!wlm_arrval($_GET,'show')) {
		$_GET['show'] = 'posts';
	}
	$protect_text = __('Select which %s to protect:', 'wishlist-member');
	$payperpost_text = __('Select which %s to enable Pay Per Post on:', 'wishlist-member');
	$userpost_text = __('Select the %s that this user can specifically access:', 'wishlist-member');
	$content_text = __('Select the %s that members of this level can access:', 'wishlist-member');
	if ($cprotect) {
		$instruction_text = $protect_text;
	} elseif ($payperpost) {
		$instruction_text = $payperpost_text;
	} elseif ($user) {
		$instruction_text = $userpost_text;
	} else {
		$instruction_text = $content_text;
	}
	$instruction_text = '<p>' . $instruction_text . '</p>';
	switch (wlm_arrval($_GET,'show')) {
		case 'posts':
			printf($instruction_text, 'posts');
			if ($level['allposts'])
				$allchecked = ' checked="true" disabled="true" ';
			break;
		case 'pages':
			printf($instruction_text, 'pages');
			if ($level['allpages'])
				$allchecked = ' checked="true" disabled="true" ';
			break;
		case 'categories':
			printf($instruction_text, 'categories');
			if ($level['allcategories'])
				$allchecked = ' checked="true" disabled="true" ';
			break;
		case 'comments':
			echo '<p>' . __('Please select the posts that members of this level can comment on:') . '</p>';
			if ($level['allcomments'])
				$allchecked = ' checked="true" disabled="true" ';
			break;

		case 'files':
			echo $cprotect ? '<p>' . __('Select which files to protect:') . '</p>' : '<p>' . __('Please select the posts that members of this level can access:') . '</p>';
			break;
	}
	$this->SyncContent(wlm_arrval($_GET,'show'));
	?>
	<?php
	if (wlm_arrval($_GET,'show') == 'categories') {
		include($this->pluginDir . '/admin/membershiplevels.content.cats.php');
		include_once($this->pluginDir . '/admin/tooltips/membershiplevels.content.cats.tooltips.php');
	} elseif (wlm_arrval($_GET,'show') == 'files') {
		include($this->pluginDir . '/admin/membershiplevels.content.files.php');
		include_once($this->pluginDir . '/admin/tooltips/membershiplevels.content.files.tooltips.php');
	} elseif (wlm_arrval($_GET,'show') == 'folders1') {
		include($this->pluginDir . '/admin/membershiplevels.content.folders1.php');
		include_once($this->pluginDir . '/admin/tooltips/membershiplevels.content.folders.tooltips.php');
	} elseif (wlm_arrval($_GET,'show') == 'folders') {
		include($this->pluginDir . '/admin/membershiplevels.content.folders.php');
		include_once($this->pluginDir . '/admin/tooltips/membershiplevels.content.folders.tooltips.php');
	} else {
		include($this->pluginDir . '/admin/membershiplevels.content.posts.php');
		include_once($this->pluginDir . '/admin/tooltips/membershiplevels.content.posts.tooltips.php');
	}
	?>
<?php endif; ?>

<?php
include_once($this->pluginDir . '/admin/tooltips/membershiplevels.content.tooltips.php');
?>
