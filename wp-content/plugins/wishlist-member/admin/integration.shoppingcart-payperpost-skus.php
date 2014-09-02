<?php
/*
 * Pay Per Post SKUs Table
 * Original Author : Mike Lopez
 */
?>
<?php if (isset($ppph2)) : ?>
	<h2 class="small"><?php echo $ppph2; ?></h2>
<?php else : ?>
	<h2 class="small"><?php _e('Pay Per Post SKUs', 'wishlist-member'); ?></h2>
<?php endif; ?>

<?php if (isset($pppdesc)) : ?>
	<p><?php echo $pppdesc; ?></p>
<?php else : ?>
	<p><?php _e('The Pay Per Post SKUs specifies the post that should be tied to each transaction.', 'wishlist-member'); ?></p>
<?php endif; ?>

<?php
if (!isset($ppptitle_header)) {
	$ppptitle_header = __('Post Title', 'wishlist-member');
}
if (!isset($pppsku_header)) {
	$pppsku_header = __('SKU', 'wishlist-member');
}
if (!isset($pppsku_text)) {
	$pppsku_text = '%s';
}

if(empty($ppp_colset)) {
	$ppp_colset = '<col width="200"></col>';
}

$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
$post_types = get_post_types('', 'objects');
?>
<?php foreach ($xposts AS $post_type => $posts) : ?>
	<?php if(count($posts)) : ?>
	<h3><?php echo $post_types[$post_type]->labels->name; ?></h3>
	<table class="widefat">
		<?php echo $ppp_colset; ?>
		<thead>
			<tr>
				<th scope="col"><?php echo $ppptitle_header; ?></th>
				<th scope="col"><?php echo $pppsku_header; ?></th>
				<?php
				if (isset($ppp_extraheaders) && is_array($ppp_extraheaders) && !empty($ppp_extraheaders)) {
					foreach ($ppp_extraheaders AS $ppp_extraheader) {
						printf('<th scope="col">%s</th>', $ppp_extraheader);
					}
				}
				?>
			</tr>
		</thead>
	</table>
	<div style="max-height:130px;overflow:auto;">
		<table class="widefat" style="border-top:none">
			<?php echo $ppp_colset; ?>
			<tbody>
				<?php
				$alt = 0;
				foreach ($posts AS $post) :
					?>
					<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" >
						<td><b><?php echo $post->post_title; ?></b></td>
						<td><u style="font-size:1.2em"><?php printf($pppsku_text, 'payperpost-' . $post->ID); ?></u></td>
						<?php
						if (isset($ppp_extracolumns) && is_array($ppp_extracolumns) && !empty($ppp_extracolumns)) {
							foreach ($ppp_extracolumns AS $ppp_extracolumn) {
								printf('<td>%s</th>', sprintf($ppp_extracolumn, 'payperpost-' . $post->ID));
							}
						}
						?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php echo $ppp_table_end; ?>
	<?php endif; ?>
<?php endforeach; ?>