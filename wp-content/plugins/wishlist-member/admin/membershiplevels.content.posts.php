<?php
/*
 * Post, Page and Comment Protection
 */

if (wlm_arrval($_GET,'howmany') > 1) {
	$this->SaveOption('member_content_posts_howmany', $_GET['howmany'] + 0);
}

$howmany = $this->GetOption('member_content_posts_howmany') + 0;
if (!$howmany) {
	$this->SaveOption('member_content_posts_howmany', 15);
	$howmany = 15;
}

function wl_get_posts_total_count($post_type, $excludes, $status) {
	$post_count = wp_count_posts($post_type);

	//count only the valid posts
	$totalcount = 0;
	foreach ($status as $k => $v) {
		$totalcount += $post_count->$k;
	}
	//cleanup the includes, there are 0's 
	//in them sometimes which will mess our total count
	foreach ($excludes as $k => $v) {
		if ($v == 0) {
			unset($excludes[$k]);
		}
	}
	//subtract the exclude pages from the total count
	$totalcount = $post_type == 'page' ? $totalcount - count($excludes) : $totalcount;
	return $totalcount;
}

$status = array(
	'publish' => 'Published',
	'pending' => 'Pending',
	'draft' => 'Unpublished',
	'private' => 'Private',
	'future' => 'Scheduled'
);
switch (wlm_arrval($_GET,'show')) {
	case 'pages':
		$post_type = 'page';
		break;
	case 'posts':
	case 'comments':
		$post_type = 'post';
		break;
	default:
		$post_type = $_GET['show'];
}
$totalcount = wl_get_posts_total_count($post_type, $this->ExcludePages(array()), $status);

$offset = $_GET['offset'] - 1;
if ($offset < 0)
	$offset = 0;
$perpage = $howmany;  // posts per page
$offset = $offset * $perpage;
$args = array(
	'numberposts' => $perpage,
	'post_status' => implode(',', array_keys((array) $status)),
	'post_type' => $post_type,
	'offset' => $offset,
	'exclude' => implode(',', $this->ExcludePages(array()))
);
$objs = get_posts($args);
$objcount = count($objs);

$page_links = paginate_links(array(
	'base' => add_query_arg('offset', '%#%'),
	'format' => '',
	'total' => ceil($totalcount / $perpage),
	'current' => $offset / $perpage + 1
		));
if ($cprotect) {
	$button_text = __('Set Protection', 'wishlist-member');
} elseif ($payperpost) {
	$button_text = __('Set Pay Per Post', 'wishlist-member');
} else {
	$button_text = __('Grant Access', 'wishlist-member');
}
?>
<?php if ($objcount): $Checked = $this->GetMembershipContent($_GET['show'], $_GET['level']); ?>
	<form method="post">
		<div class="tablenav">
			<div class="alignleft"><input type="submit" class="button-secondary" value="<?php echo $button_text; ?>" />
				<?php echo $this->Tooltip("membershiplevels-content-posts-tooltips-Set-Protection"); ?>
			</div>
			<div class='tablenav-pages'>
				Display&nbsp;
				<select name="howmany" onchange="document.location=document.location+'&howmany='+this.value">
					<option <?php if ($howmany == 15) echo 'selected="true"'; ?>>15</option>
					<option <?php if ($howmany == 30) echo 'selected="true"'; ?>>30</option>
					<option <?php if ($howmany == 50) echo 'selected="true"'; ?>>50</option>
					<option <?php if ($howmany == 100) echo 'selected="true"'; ?>>100</option>
					<option <?php if ($howmany == 200) echo 'selected="true"'; ?>>200</option>
				</select>
				&nbsp;<?php _e('Items per Page', 'wishlist-member'); ?>
				<?php
				if ($page_links) {
					echo '&nbsp;&nbsp;&nbsp;' . $page_links;
				}
				?>
			</div>
		</div>
		<table class="widefat" id="wpm_post_page_table">
			<thead>
				<tr valign="top">
					<th class="check-column" scope="row"><input <?php echo $allchecked; ?> type="checkbox" onclick="wpm_selectAll(this,'wpm_post_page_table')" /></th>
					<th scope="row"><?php _e('Date', 'wishlist-member'); ?></th>
					<th scope="row"><?php _e('Title', 'wishlist-member'); ?></th>
					<th scope="row"><?php _e('Author', 'wishlist-member'); ?></th>
					<?php if (wlm_arrval($_GET,'show') != 'pages'): ?><th scope="row"><?php _e('Categories', 'wishlist-member'); ?></th><?php endif; ?>
					<th scope="row"><?php _e('Status', 'wishlist-member'); ?></th>
					<?php if ($payperpost): ?>
						<th scope="row"><?php _e('Allow Free Registration', 'wishlist-member'); ?></th>
	<?php endif; ?>
				</tr>
			</thead>
			<tbody>
																<?php foreach ((array) $objs AS $GLOBALS['post']): setup_postdata($GLOBALS['post']); ?>
					<tr valign="top" class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>">
						<th class="check-column" scope="row"><input type="checkbox" name="Checked[<?php the_ID(); ?>]" value="1" <?php echo $allchecked;
															if ($cprotect)
																$this->Checked($this->Protect(get_the_ID()), true);else
																$this->Checked(get_the_ID(), $Checked);
																	?> /><input type="hidden" name="ID[<?php the_ID(); ?>]" value="0" /></th>
						<td><?php echo the_time('m/d/Y'); ?></td>
						<td><a href="<?php the_permalink(); ?>"><b><?php echo the_title(); ?></b></a></td>
						<td><?php the_author(); ?></td>
						<?php if (wlm_arrval($_GET,'show') != 'pages'): ?><td><?php the_category(', '); ?></td><?php endif; ?>
						<td><?php echo $status[$GLOBALS['post']->post_status]; ?></td>
						<?php
						if ($payperpost) :
							$freeppp = $this->Free_PayPerPost(get_the_ID());
							if ($freeppp) {
								$checked_yes = ' checked="checked"';
								$checked_no = '';
							} else {
								$checked_yes = '';
								$checked_no = ' checked="checked"';
							}
							?>
							<td>
								<label><input type="radio" name="enable_free_payperpost[<?php the_ID(); ?>]" value="0"<?php echo $checked_no; ?>> No</label>
								&nbsp;
								<label><input type="radio" name="enable_free_payperpost[<?php the_ID(); ?>]" value="1"<?php echo $checked_yes; ?>> Yes</label>
							</td>
		<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft"><input type="submit" class="button-secondary" value="<?php echo $button_text; ?>" /></div>
	<?php if ($page_links) echo "<div class='tablenav-pages'>$page_links</div>"; ?>
		</div>
		<br clear="all" />
		<input type="hidden" name="WishListMemberAction" value="<?php echo $payperpost ? 'SaveMembershipContentPayPerPost' : 'SaveMembershipContent'; ?>" />
		<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
		<input type="hidden" name="ContentType" value="<?php echo $_GET['show']; ?>" />
	</form>
<?php endif; ?>
