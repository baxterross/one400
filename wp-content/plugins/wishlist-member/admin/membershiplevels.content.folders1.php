<?php
/*
 * Folder Protection
 * @author Andy <onephpcoder@gmail.com>
 */

$uploads = wp_upload_dir(); // Array of key => value pairs
/*
  $uploads now contains something like the following (if successful)
  Array (
  [path] => C:\path\to\wordpress/wp-content/uploads/2010/05
  [url] => http://example.com/wp-content/uploads/2010/05
  [subdir] => /2010/05
  [basedir] => C:\path\to\wordpress/wp-content/uploads
  [baseurl] => http://example.com/wp-content/uploads
  [error] =>
  )
  // Descriptions
  [path] - base directory and sub directory or full path to upload directory.
  [url] - base url and sub directory or absolute URL to upload directory.
  [subdir] - sub directory if uploads use year/month folders option is on.
  [basedir] - path without subdir.
  [baseurl] - URL path without subdir.
  [error] - set to false.
 */
$doubleBackSlash = chr(92) . chr(92);
$rootOfFolders = $this->GetOption('rootOfFolders');
if ($rootOfFolders == '') {
	$rootOfFolders = $uploads['basedir'];

	$rootOfFolders = addslashes($rootOfFolders);
	$rootOfFolders = str_replace($doubleBackSlash . $doubleBackSlash, '/', $rootOfFolders);
	$this->SaveOption('rootOfFolders', $rootOfFolders);
}

$rootOfFolders = addslashes($rootOfFolders);
$rootOfFolders = str_replace($doubleBackSlash . $doubleBackSlash, '/', $rootOfFolders);


$folders = array();
if (is_dir($rootOfFolders)) {
	if ($handle = opendir($rootOfFolders)) {
		while (false !== ($file = readdir($handle))) {
			$fullpath = $rootOfFolders . '/' . $file;
			if ($file != '.' && $file != '..' && is_dir($fullpath)) {
				$folders[] = $fullpath;
			}
		}
		closedir($handle);
	}
}
?>
<form method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Root of folders:', 'wishlist-member'); ?> <?php echo $this->Tooltip("membershiplevels-content-folders-tooltips-rootOfFolders"); ?></th>
			<td width="50" >
				<input type="text" name="<?php $this->Option('rootOfFolders'); ?>" value="<?php echo $rootOfFolders; ?>" size="60" /> 
				<br />
			</td>
			<td class="submit" >
				<div >
					<?php $this->Options();
					$this->RequiredOptions();
					?>
					<input type="hidden" name="WishListMemberAction" value="Save" />
					<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
					<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
				</div>
			</td>
		</tr>

	</table>
</form>


<form method="post">
	<div class="tablenav">
		<div class="alignleft"><input type="submit" class="button-secondary" value="<?php echo $cprotect ? __('Set Protection', 'wishlist-member') : __('Grant Access', 'wishlist-member'); ?>" />
<?php echo $this->Tooltip("membershiplevels-content-folders-tooltips-Set-Protection"); ?>
		</div>
	</div>
	<br clear="all" />
	<table class="widefat" id="wpm_folders_table">
		<thead>
			<tr>
				<th nowrap class="check-column1" scope="row"><input type="checkbox" onclick="wpm_selectAll(this,'wpm_folders_table','check-column1')" />
<?php echo $this->Tooltip("membershiplevels-content-folders-tooltips-file-select-checkbox"); ?>
				</th>
				<th scope="col" ><?php _e('Folder', 'wishlist-member'); ?> </th>
				<th scope="col" ><?php _e('Writable?', 'wishlist-member'); ?> </th>
				<th scope="col" ><?php _e('Path', 'wishlist-member'); ?> </th>

			</tr>
		</thead>
		<tbody>
			<?php
			for ($i = 0; $i < count($folders); $i++) {
				$folder = $folders[$i];
				$folderName = str_replace($rootOfFolders, '', $folder);
				$folderBase = str_replace($folderName, '', $folder);
				$folderLink = get_option('siteurl') . '/' . str_replace(str_replace($doubleBackSlash . $doubleBackSlash, '/', ABSPATH), '', $folder);
				?>
				<tr>
					<th class="check-column1" scope="row" ><input type="checkbox" name="Protect[<?php echo $i; ?>]" value="<?php echo $folder; ?>" <?php echo $this->Checked(true, $this->GetFolderProtect($folder, $_GET['level'])); ?> />					
						<input type="hidden" name="Folders[<?php echo $i; ?>]" value="<?php echo $folder; ?>" />
					</th>
					<td > <a href='<?php echo $folderLink; ?>' target="_blank"> <?php echo $folderName; ?></a>
						<input type="hidden" name="folderName[<?php echo $i; ?>]" value="<?php echo $folderName; ?>" />
					</td>
					<td>  <?php
				if (!is_writable($folder)) {
					_e('No', 'wishlist-member');
				} else {
					_e('Yes', 'wishlist-member');
				}
				?></td>
					<td>  <?php echo $folder; ?></td>
				</tr>
<?php } ?>
		</tbody>
	</table>
	<br clear="all" />
	<input type="hidden" name="WishListMemberAction" value="SaveMembershipFolders" />
	<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
</form>

