<script type="text/javascript">
	var instapage_post_type_area = true;

	<?php
		if( !$user_id )
		{
			echo 'window.location="'. admin_url( 'options-general.php?page='. $plugin_file ) .'";' ;
		}
	?>
</script>
<div class="bootstrap-wpadmin">
	<input type="hidden" name="instapage_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>" />
	<div class="row-fluid form-horizontal">
		<br/>
		<div class="control-group">
			<label class="control-label">instapage type</label>
			<div class="controls">
				<div class="btn-group multichoice subsection" data-subsection="instapage_url" data-target="instapage-post-type">

					<select name="post-type">
						<option value="">Normal Page</option>
						<option value="home" <?php if( $instapage_post_type == 'home' ): ?>selected=""<?php endif; ?>>Home Page</option>
						<option value="404" <?php if( $instapage_post_type == '404' ): ?>selected=""<?php endif; ?>>404 Page</option>
					</select>

				</div>

			</div>
		</div>

		<div class="control-group">
			<label for="instapage_my_selected_page" class="control-label">instapage to display</label>
			<div class="controls">
				<select name="instapage_my_selected_page" id="instapage_my_selected_page" class="input-xlarge">
					<?php foreach ($field['options'] as $option): ?>
						<option <?php echo (($meta == $option['value']) ? ' selected="selected"' : '') ?> value="<?php echo $option['value'] ?>">
							<?php echo $option['label'] ?>
						</option>
					<?php endforeach; ?>
				</select>
				<a data-content="Select one of the instapage that you've created on &lt;strong&gt;http://app.instapage.com/dashboard/&lt;/strong&gt;" data-original-title="instapage to be displayed" rel="popover" class="instapage-help-ico">&nbsp;</a>
			</div>
		</div>

		<div class="subsection_instapage_url control-group">
			<label for="instapage_slug" class="control-label">Custom url</label>
			<div class="controls <?php if ($missing_slug) echo 'lp-error' ?>" id="instapage-wp-path">
				<div class="input-prepend">
					<span class="add-on"><?php echo site_url() ?>/</span><input type="textbox" class="input-xlarge" id="instapage_slug" name="instapage_slug" value="<?php echo $meta_slug ?>" />
				</div>
				<a class="instapage-help-ico" rel="popover" data-original-title="instapage url" data-content="Pick your own url based on your Wordpress site. It will work as if the selected instapage was a &quot;Page&quot; on your site.">&nbsp;</a>
				<?php if ($missing_slug): ?>
					<label for="instapage_slug" generated="true" class="error" style="color:#ff3300" id="lp-error-path">Valid path is required.</label>
				<?php endif; ?>
			</div>
		</div>


		<hr/>

		<div class="row-fluid lpgs-submit-controls">
			<input type="submit" name="publish" id="publish" class="btn btn-primary btn-large" value="Publish" accesskey="p"> &nbsp;&nbsp;
		<?php if ( $is_page_active_mode ) { ?>
			<a href="<?php echo $delete_link ?>" type="submit" class="btn btn-warning">Delete</a>
		<?php } ?>
			<a href="<?php echo admin_url('edit.php?post_type=instapage_post') ?>" type="submit" class="btn">Back</a>
		</div>

	</div>

	<div>
		<hr />
		<a href="http://app.instapage.com/dashboard" target="_blank">Manage your instapage account</a>
	</div>
</div>
