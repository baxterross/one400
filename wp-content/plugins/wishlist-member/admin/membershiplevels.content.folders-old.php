<?php
/*
 * Folder Protection
 * @author Andy <onephpcoder@gmail.com>
 */


$level = $_POST['Level'];
$Folders = (array) $_POST['Folders'];

//var_dump($Folders);
//echo("<hr>");

$Protect = (array) $_POST['Protect'];

//Var_dump($Protect);
//echo("<hr>");


$ForceDownload = (array) $_POST['ForceDownload'];
//Var_dump($ForceDownload);
//echo("<hr>");
//echo "<br> levels <br><br>";   var_dump($level);
//echo "<br> Folders <br><br>";  var_dump($Folders);
//echo "<br> Protect <br><br>";  var_dump($Protect);
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
$parentFolder = $this->GetOption('parentFolder');

$niceABSPATH = ABSPATH;
$niceABSPATH = addslashes($niceABSPATH);
$niceABSPATH = str_replace($doubleBackSlash, '/', $niceABSPATH);

/*
  if($parentFolder=='' ) {
  $rootOfFolders=ABSPATH."files";
  $this->SaveOption('parentFolder','files');
  $parentFolder="files";
  }



  if (wlm_arrval($_POST,'WishListMemberAction')=='Save') {

  $rootOfFolders=ABSPATH.$parentFolder;
  $rootOfFolders=addslashes($rootOfFolders);
  $rootOfFolders=str_replace( $doubleBackSlash.$doubleBackSlash ,  '/' , $rootOfFolders);
  $this->SaveOption('rootOfFolders',$rootOfFolders);

  }

 */





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




<?php if ($this->GetOption('FolderProtectionMode') == 'easy') {
	?>
	<ul>

		<li>
			<span class='folder-protection-mode'  > <a href="admin.php?page=WishListMember&wl=membershiplevels&mode=content&level=<?php echo $_GET['level']; ?>&show=folders&fp=advanced"><?php _e('Advanced Mode', 'wishlist-member'); ?></a></span> 
		</li>	
		<li>
			<h3><?php _e(' Easy Folder Protection', 'wishlist-member'); ?></h3>
		</li>

		<form method="post">
			<table class="form-table">
				<tr>
					<td width="100%">	

						<div class="submit"  >
							Easy folder protection with one click. 
							<?php $this->Options();
							$this->RequiredOptions();
							?>
							<input type="hidden" name="WishListMemberAction" value="EasyFolderProtection" />
							<input type="submit" value="<?php _e('Run Easy Folder Protection', 'wishlist-member'); ?>" />
							<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" /> 
						</div>
						<b>How easy mode works?</b> <br><br>
						<ul> 					  	

							<li>1) It will create a  folder with name "files" at your WordPress instalation path and set it as "Parent Folder" of protected folders.</li>
							<li>2) It will create some  child folders with  same name of your existing levels name inside Parent Folder. </li>
							<li>3) It will set protection for each child folder for the level with same name. </li>
							<li>4) It will create an examplefile.txt inside your protected folders that you can use to test your Folder Protection.
							<li>5) Thats all. Then all you need is coping your files into protected folders using ftp.</li>
						</ul>


					</td>
				</tr>


			</table>
		</form>

	</li>	
	</ul>
	<?php
}




if ($this->GetOption('FolderProtectionMode') == 'advanced') {
	?>


	<ul>
		<li>
			<span  class='folder-protection-mode'  > <a href="admin.php?page=WishListMember&wl=membershiplevels&mode=content&level=<?php echo $_GET['level']; ?>&show=folders&fp=easy"><?php _e('Easy Mode', 'wishlist-member'); ?></a></span> 
		</li>	
		<li>
	<?php _e('<h3> Advanced Folder Protection</h3>', 'wishlist-member'); ?> 
		</li>

		<li>			 
			<ul>
				<li>1) At advanced mode, you can set protection for a folder to more than one levels.</li>
				<li>2) You can change parent folder to another location.(This will reset current protected folders settings) </li>		 	
			</ul>
		</li>

	</ul>


	<form method="post">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Parent Folder:', 'wishlist-member'); ?> <?php echo $this->Tooltip("membershiplevels-content-folders-tooltips-ParentFolder"); ?></th>
				</th>
				<td width="400" >

	<?php echo $niceABSPATH; ?><input type="text" name="<?php $this->Option('parentFolder'); ?>" value="<?php echo $parentFolder; ?>" size="20" /> 
					<br />
				</td>
				<td class="submit" >
					<div >
						<?php $this->Options();
						$this->RequiredOptions();
						?>
						<input type="hidden" name="WishListMemberAction" value="FolderProtectionParentFolder" />
						<input type="submit" value="<?php _e('Save', 'wishlist-member'); ?>" />
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
					<th scope="col" ><?php _e('Name', 'wishlist-member'); ?> </th>
					<th scope="col" ><?php _e('Folder', 'wishlist-member'); ?> </th>
					<th scope="col" ><?php _e('Htacess', 'wishlist-member'); ?> </th>					 
					<th scope="col" ><?php _e('Path', 'wishlist-member'); ?> </th>
					<th scope="col" ><input type="checkbox" onclick="wpm_selectAll(this,'wpm_folders_table','check-column2')"  />
	<?php _e('Force Download', 'wishlist-member'); ?> </th>
				</tr>
			</thead>
			<tbody>
				<?php
				if (parentFolder == '') {
					?>
					<tr>
						<td> <?php _e('Please specify the Parent Folder.', 'wishlist-member'); ?> </td>
					</tr>

					<?php
				} else {

					for ($i = 0; $i < count($folders); $i++) {
						$folder = $folders[$i];
						$folderName = str_replace($rootOfFolders, '', $folder);
						$folderBase = str_replace($folderName, '', $folder);
						$folderLink = get_option('siteurl') . '/' . str_replace(str_replace($doubleBackSlash . $doubleBackSlash, '/', $niceABSPATH), '', $folder);
						?>
						<tr>
							<th class="check-column1" scope="row" ><input type="checkbox" name="Protect[<?php echo $i; ?>]" value="<?php echo $folder; ?>" <?php echo $this->Checked(true, $this->GetFolderProtect($folder, $_GET['level'])); ?> />					
								<input type="hidden" name="Folders[<?php echo $i; ?>]" value="<?php echo $folder; ?>" />
							</th>

							<td > 
								<a href="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin.php?height=300&width=800&wlmfolderinfo=<?php echo $folder; ?>&wlmfolderlevelinfo=<?php echo $folderName; ?>&wlmfolderLinkinfo=<?php echo $folderLink; ?>&TB_iframe=true" class="add-new-h2 thickbox"
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
				}
				?>
			</tbody>
		</table>
		<br clear="all" />
		<input type="hidden" name="WishListMemberAction" value="SaveMembershipFolders" />
		<input type="hidden" name="Level" value="<?php echo $_GET['level']; ?>" />
	</form>

<?php } ?>
