<?php
/*
 * Category Protection
 */
$objs = array();
foreach ($this->taxonomies AS $taxonomy) {
	$objs = array_merge($objs, get_categories('hide_empty=0&taxonomy=' . $taxonomy));
}
$objcount = count($objs);
?>
<?php if ($objcount): $Checked = $this->GetMembershipContent('categories', $_GET['level']); ?>
	<form method="post">
		<div class="tablenav">
			<div class="alignleft"><input type="submit" class="button-secondary" value="<?php echo $cprotect ? __('Set Protection', 'wishlist-member') : __('Grant Access', 'wishlist-member'); ?>" />
				<?php echo $this->Tooltip("membershiplevels-content-cats-tooltips-Set-Protection"); ?>
			</div>
		</div>
		<br clear="all" />
		<table class="widefat" id="wpm_post_page_table">
			<thead>
				<tr valign="top">
					<th class="check-column" scope="row"><input <?php echo $allchecked; ?> type="checkbox" onclick="wpm_selectAll(this,'wpm_post_page_table')" /></th>
					<th scope="row"><?php _e('Name', 'wishlist-member'); ?></th>
					<th scope="row"><?php _e('Type', 'wishlist-member'); ?></th>
					<th scope="row" style="text-align:center"><?php _e('Posts', 'wishlist-member'); ?></th>
					<th scope="row"><?php _e('Description', 'wishlist-member'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $prev = 'category';
				foreach ((array) $objs AS $obj):
					?>
					<?php
					$tax = get_taxonomy($obj->taxonomy);
					if ($obj->taxonomy != $prev) {
						$tr_class = ' wlm_new_taxonomy ';
					} else {
						$tr_class = '';
					}
					?>
					<tr valign="top" class="<?php echo $tr_class; ?><?php echo $alt++ % 2 ? '' : 'alternate'; ?>" <?php echo $tr_style; ?>>
						<th class="check-column" scope="row"><input type="checkbox" name="Checked[<?php echo $obj->cat_ID; ?>]" value="1" <?php echo $allchecked;
															if ($cprotect)
																$this->Checked($this->CatProtected($obj->cat_ID), true);else
																$this->Checked($obj->cat_ID, $Checked);
															?> /><input type="hidden" name="ID[<?php echo $obj->cat_ID; ?>]" value="0" /></th>
						<td><a href="<?php echo get_term_link($obj); ?>"><b><?php echo $obj->name; ?></b></a></td>
						<td><?php echo $tax->labels->singular_name ? $tax->labels->singular_name : $tax->labels->name; ?></td>
						<td style="text-align:center"><?php echo $obj->count; ?></td>
						<td><?php echo $obj->description; ?></td>
					</tr>
		<?php $prev = $obj->taxonomy;
	endforeach;
	?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft"><input type="submit" class="button-secondary" value="<?php echo $cprotect ? __('Set Protection', 'wishlist-member') : __('Grant Access', 'wishlist-member'); ?>" /></div>
		</div>
		<br clear="all" />
		<input type="hidden" name="WishListMemberAction" value="SaveMembershipContent" />
		<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
		<input type="hidden" name="ContentType" value="categories" />
	</form>
<?php endif; ?>
