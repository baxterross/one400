<?php
$ptype = $_GET['post_type'];

if (!$ptype)
	$ptype = 'post';
if ($this->PostTypeEnabled($ptype)) :
	?>
	<script type="text/javascript">
		function wpm_toggleAllLevels(o){
			jQuery('.allLevels').attr('checked',o.checked);
		}
		var wlm_radioshack = '';
		jQuery(document).ready(function(){
			jQuery('.meta-box-sortables').sortable({beforeStop:wlm_save_radios,stop:wlm_restore_radios});
		});

		function wlm_save_radios(){
			wlm_radioshack = jQuery('input[type=radio][name=wpm_protect]:checked').val();
		}
		function wlm_restore_radios(){
			jQuery('input[type=radio][name=wpm_protect][value='+wlm_radioshack+']').attr('checked','checked');
		}
	</script>
	<style type="text/css">
		.wlm_levels{
			float:left;
		}
		.wlm_levels li{
			margin: 0;
			padding: 0;
		}
		.wlm_levels li.first{
			padding: 0 0 2px 0;
			margin: 0 0 2px 0;
			border-bottom:1px solid #ddd;
		}
		#wpm_options_div h2{
			margin:0;
		}
		#wpm_options_div blockquote{
			margin-top:0;
		}

		.user_post_exists td{
			text-decoration:line-through;
		}
	</style>
	<div class="inside">
		<!-- Content Protection -->
		<h2><?php _e('Content Protection', 'wishlist-member'); ?></h2>
		<blockquote>
			<p><?php _e('Do you want to protect this content?', 'wishlist-member'); ?></p>
			<ul class="wlm_levels">
				<li><label><input type="radio" name="wpm_protect" value="N"<?php echo!$wpm_protect ? ' checked="checked"' : ''; ?> /> <?php _e('No, do not protect this content (non-members can access it)', 'wishlist-member'); ?></label></li>
				<li><label><input type="radio" name="wpm_protect" value="Y"<?php echo $wpm_protect ? ' checked="checked"' : ''; ?> /> <?php _e('Yes, protect this content (members only)', 'wishlist-member'); ?></label></li>
			</ul>
			<br clear="all" />
		</blockquote>

		<!-- Membership Levels -->
		<?php if (count($wpm_levels)): ?>
			<h2><?php _e('Membership Levels', 'wishlist-member'); ?></h2>
			<blockquote>
				<p><?php _e('Select the membership level that can access this content:', 'wishlist-member'); ?></p>
				<ul class="wlm_levels">
					<li class="first"><label><input type="checkbox" onclick="wpm_toggleAllLevels(this)" /> <?php _e('Select/Unselect All Levels', 'wishlist-member'); ?></label></li>
					<?php foreach ((array) $wpm_levels AS $id => $level): ?>
						<li><label>
								<input class="allLevels" type="checkbox" name="wpm_access[<?php echo $id ?>]" 
									<?php echo ($this->GetFileProtect($post->ID, $id)) ? ' checked="true"' : '';	?>  value="<?php echo $post->ID ?>" /> <?php echo $level['name'] ?>
						</label></li>
					<?php endforeach; ?>
				</ul>
				<br clear="all" />
			</blockquote>
	<?php endif; ?>

<?php endif; ?>		