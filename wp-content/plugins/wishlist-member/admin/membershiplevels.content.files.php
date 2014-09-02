<?php
/*
 * File Protection
 */

$post_type = 'attachment';
$totalcount = array_sum((array) wp_count_posts($post_type));
$offset = $_GET['offset'] - 1;
if ($offset < 0)
	$offset = 0;
$perpage = 15;  // posts per page
$offset = $offset * $perpage;
$args = array(
	'numberposts' => $perpage,
	'post_status' => 'inherit',
	'post_type' => $post_type,
	'offset' => $offset
);
$objs = get_posts($args);
$objcount = count($objs);

$page_links = paginate_links(array(
	'base' => add_query_arg('offset', '%#%'),
	'format' => '',
	'total' => ceil($totalcount / $perpage),
	'current' => $offset / $perpage + 1
		));
?>
<form method="post">
	<div class="tablenav">
		<div class="alignleft"><input type="submit" class="button-secondary" value="<?php echo $cprotect ? __('Set Protection', 'wishlist-member') : __('Grant Access', 'wishlist-member'); ?>" />
			<?php echo $this->Tooltip("membershiplevels-content-files-tooltips-Set-Protection"); ?>
		</div>
		<?php if ($page_links) echo "<div class='tablenav-pages'>$page_links</div>"; ?>
	</div>
	<br clear="all" />
	<table class="widefat" id="wpm_files_table">
		<thead>
			<tr>
				<th nowrap class="check-column" scope="row">
					<input type="checkbox" onclick="wpm_selectAll(this, 'wpm_files_table', 'check-column1')" />
				</th>
				<th scope="col" colspan="2"><?php _e('File'); ?></th>
				<th scope="col"><?php _e('Parent Post'); ?></th>
				<th class="check-column2" scope="row">
					<input type="checkbox" onclick="wpm_selectAll(this, 'wpm_files_table', 'check-column2')" />
					<?php _e("Inherit Parent's Protection"); ?>
					<?php echo $this->Tooltip("membershiplevels-content-files-tooltips-file-Inherit-Parents-Protection-checkbox"); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
			for ($i = 0; $i < count($objs); $i++): $file = $objs[$i];
				$parent = get_post($file->post_parent);
				?>
				<tr>
					<th class="check-column" scope="row"><input type="checkbox" name="Protect[<?php echo $i; ?>]" value="<?php echo $file->ID; ?>" <?php echo $this->Checked(true, $this->GetFileProtect($file->ID, $_GET['level'])); ?> />
						<input type="hidden" name="Files[<?php echo $i; ?>]" value="<?php echo $file->ID; ?>" />
					</th>
					<td><?php echo wp_get_attachment_image($file->ID, array(80, 60), true); ?></td>
					<td><a href="<?php echo wp_get_attachment_url($file->ID); ?>" target="_blank"><?php echo str_replace(ABSPATH, '', get_attached_file($file->ID, true)); ?></a></td>
					<td><?php
						if ($file->post_parent) {
							echo '<a href="' . get_permalink($file->ID) . '" target="_blank">' . $parent->post_title . '</a>';
						}
						?></td>
					<th class="check-column2" scope="row" valign="top">
						<?php if ($file->post_parent): ?><input type="checkbox" name="Inherit[<?php echo $i; ?>]" value="<?php echo $file->ID; ?>" <?php echo $this->Checked(true, $this->GetFileInherit($file->ID)); ?> /><?php endif; ?>
					</th>
				</tr>
<?php endfor; ?>
		</tbody>
	</table>
	<div class="tablenav">
		<div class="alignleft"><input type="submit" class="button-secondary" value="<?php echo $cprotect ? __('Set Protection', 'wishlist-member') : __('Grant Access', 'wishlist-member'); ?>" /></div>
<?php if ($page_links) echo "<div class='tablenav-pages'>$page_links</div>"; ?>
	</div>
	<br clear="all" />
	<input type="hidden" name="WishListMemberAction" value="SaveMembershipFiles" />
	<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
</form>