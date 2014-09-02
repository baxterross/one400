<?php
/*
 * New Folder Protection
 * @author Andy <onephpcoder@gmail.com>
 * @author Mike Lopez <e@mikelopez.com>
 */


$level = $_POST['Level'];
$Folders = (array) $_POST['Folders'];
$Protect = (array) $_POST['Protect'];
$ForceDownload = (array) $_POST['ForceDownload'];

$uploads = wp_upload_dir(); // Array of key => value pairs

$doubleBackSlash = chr(92) . chr(92);
$rootOfFolders = $this->GetOption('rootOfFolders');
$parentFolder = $this->GetOption('parentFolder');

$niceABSPATH = ABSPATH;
$niceABSPATH = addslashes($niceABSPATH);
$niceABSPATH = str_replace($doubleBackSlash, '/', $niceABSPATH);

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
<br />
<?php if (empty($parentFolder)) : ?>
	<div class="updated"><p><?php _e('It seems that folder protection is not yet configured. What would you want to do?', 'wishlist-member'); ?></p></div>

	<ol>
		<li>
			<a href="javascript:;" onclick="jQuery('#manuallyconfigure').hide('100');
					jQuery('#autoconfigure').show(100)">Automatically Configure Folder Protection</a>
			<form method="post" id="autoconfigure" style="display:none">
				<?php _e('<p>Clicking "Auto-Configure" below will run the following:</p>', 'wishlist-member'); ?>
				<ol> 					  	
					<li><?php _e('Create a  folder with name "files" at your WordPress installation path and set it as the "Parent Folder".', 'wishlist-member'); ?></li>
					<li><?php _e('Create  sub-folders for each of your membership levels.', 'wishlist-member'); ?></li>
					<li><?php _e('Grant access for each sub-folder to its matching membership level.', 'wishlist-member'); ?></li>
					<li><?php _e('Create an "examplefile.txt" inside your protected folders that you can use to test your Folder Protection.', 'wishlist-member'); ?></li>
				</ol>
				<br />
				<?php
				$this->Options();
				$this->RequiredOptions();
				?>
				<input type="hidden" name="WishListMemberAction" value="EasyFolderProtection" />
				<input type="submit" class="button-secondary" value="<?php _e('Auto-Configure', 'wishlist-member'); ?>" />
				<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" /> 
				<br />
				<br />
			</form>
		</li>
		<li>
			<a href="javascript:;" onclick="jQuery('#autoconfigure').hide(100);
					jQuery('#manuallyconfigure').show(100)">Manually Configure Folder Protection</a>
			<form method="post" id="manuallyconfigure" style="display:none">
				<?php _e('<p>Specify the path to the parent of the folders that you want to protect.</p>
<p>For example:</p>
<code>/home/account/public_html/downloads</code>
<p>Once you specify the parent folder and click Save, all subfolders will be displayed in a list below.</p>', 'wishlist-member'); ?>
				<table>
					<colgroup>
						<col width="120" />
						<col/>
					</colgroup>
					<tr>
						<td>
							<?php _e('Parent Folder:', 'wishlist-member'); ?>
						</td>
						<td>
							<span style="font-family: monospace;"><?php echo $niceABSPATH; ?></span><input type="text" name="<?php $this->Option('parentFolder'); ?>" value="<?php echo $parentFolder; ?>" size="6" /> 
							<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
							<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
						</td>
					</tr>
				</table>
				<?php
				$this->Options();
				$this->RequiredOptions();
				?>
				<input type="hidden" name="WishListMemberAction" value="FolderProtectionParentFolder" />
			</form>
		</li>
	</ol>



	<?php
	return;
endif;
?>

<form method="post">
	<table>
		<colgroup>
			<col width="120" />
			<col/>
		</colgroup>
		<tr>
			<td>
				<?php _e('Parent Folder:', 'wishlist-member'); ?>
			</td>
			<td>
				<span style="font-family: monospace;"><?php echo $niceABSPATH; ?><strong><?php echo $parentFolder; ?></strong></span>
				<?php if (!empty($parentFolder)): ?>
					(<a href="javascript:;" onclick="jQuery('#parentFolderChange').toggle()"><?php _e('change', 'wishlist-member'); ?></a>)
				<?php endif; ?>
				<?php echo $this->Tooltip("membershiplevels-content-folders-tooltips-ParentFolder"); ?>
			</td>
		</tr>
		<tr id="parentFolderChange" style="display:none">
			<td></td>
			<td>
				<span style="font-family: monospace;"><?php echo $niceABSPATH; ?></span><input type="text" name="<?php $this->Option('parentFolder'); ?>" value="<?php echo $parentFolder; ?>" size="6" /> 
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
				<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
			</td>
		</tr>
	</table>
	<?php
	$this->Options();
	$this->RequiredOptions();
	?>
	<input type="hidden" name="WishListMemberAction" value="FolderProtectionParentFolder" />
</form>
<br />

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
				<th nowrap class="check-column" scope="row">
					<input type="checkbox" onclick="wpm_selectAll(this, 'wpm_folders_table', 'check-column1')" />
				</th>
				<th scope="col" ><?php _e('Name', 'wishlist-member'); ?> </th>
				<th scope="col" ><?php _e('Folder', 'wishlist-member'); ?> </th>
				<th scope="col" ><?php _e('Htacess', 'wishlist-member'); ?> </th>					 
				<th scope="col" ><?php _e('Path', 'wishlist-member'); ?> </th>
				<th scope="col" ><input type="checkbox" onclick="wpm_selectAll(this, 'wpm_folders_table', 'check-column2')"  />
					<?php _e('Force Download', 'wishlist-member'); ?> </th>
			</tr>
		</thead>
		<tbody>
			<?php
			for ($i = 0; $i < count($folders); $i++) {
				$folder = $folders[$i];
				$folderName = str_replace($rootOfFolders, '', $folder);
				$folderBase = str_replace($folderName, '', $folder);
				$folderLink = get_option('siteurl') . '/' . str_replace(str_replace($doubleBackSlash . $doubleBackSlash, '/', $niceABSPATH), '', $folder);
				?>
				<tr>
					<th class="check-column" scope="row"><input type="checkbox" name="Protect[<?php echo $i; ?>]" value="<?php echo $folder; ?>" <?php echo $this->Checked(true, $this->GetFolderProtect($folder, $_GET['level'])); ?> />					
						<input type="hidden" name="Folders[<?php echo $i; ?>]" value="<?php echo $folder; ?>" />
					</th>

					<td > 
						<a href="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin.php?height=300&width=800&wlmfolderinfo=<?php echo $folder; ?>&wlmfolderlevelinfo=<?php echo $folderName; ?>&wlmfolderLinkinfo=<?php echo $folderLink; ?>&TB_iframe=true" class="axdd-new-h2 thickbox"
						   title="URL of the files at folder  <?php echo $folder; ?>"
						   >

							<?php echo $folderName; ?></a>
						<input type="hidden" name="folderName[<?php echo $i; ?>]" value="<?php echo $folderName; ?>" />
					</td>
					<td> <?php
						if (is_writable($folder)) {
							_e('Writable', 'wishlist-member');
						} else {
							_e('<span style="color:red;"> NOT Writable </span>', 'wishlist-member');
						}
						?>
					</td> 
					<td> <?php
						if (file_exists($folder . '/.htaccess')) {
							//htaccess exist;
							_e(' ', 'wishlist-member');
							if (is_writable($folder . '/.htaccess')) {
								_e('Writable', 'wishlist-member');
							} else {
								_e('<span style="color:red;"> NOT Writable </span>', 'wishlist-member');
							}
						} else {
							//htaccess is not exist;
							_e(' - ', 'wishlist-member');
						}
						?> </td>
					<td>  <?php echo $folder; ?></td>
					<th class="check-column2"  scope="row">


						<input type="checkbox" name="ForceDownload[<?php echo $i; ?>]" value="<?php echo $folder; ?>" 
						<?php echo $this->Checked(true, $this->GetFolderProtectForceDownload($folder, $_GET['level']), true); ?>
							   />				 
					</th>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<input type="hidden" name="WishListMemberAction" value="SaveMembershipFolders" />
	<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
	<div class="tablenav">
		<div class="alignleft"><input type="submit" class="button-secondary" value="<?php echo $cprotect ? __('Set Protection', 'wishlist-member') : __('Grant Access', 'wishlist-member'); ?>" />
		</div>
	</div>
	<br clear="all" />
</form>

<br />
<form method="post" onsubmit="return confirm('This will reset and reconfigure all your folder protection settings.\n\nAre you sure you want to continue?')">
	<?php
	$this->Options();
	$this->RequiredOptions();
	?>
	<input type="hidden" name="WishListMemberAction" value="EasyFolderProtection" />
	<input type="submit" class="button-secondary" value="<?php _e('Reset and Reconfigure', 'wishlist-member'); ?>" />
	<?php echo $this->Tooltip('membershiplevels-content-folders-tooltips-reset'); ?>
	<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" /> 

</form>
