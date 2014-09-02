<?php
/**
 * Core Class for WishListLogin
 * @author Author Name <author@email.com>
 * @package WishListLogin2
 *
 * @version $Rev$
 * $LastChangedBy$
 * $LastChangedDate$
 */
if (!defined('ABSPATH')) {
	die();
}
if (!class_exists('WishListLogin2_Core')) {

	/**
	 * Core WishListLogin Class
	 * @package WishListLogin2
	 * @subpackage classes
	 */
	class WishListLogin2_Core {
		const ActivationURLs = 'wishlistactivation.com';
		const ActivationMaxRetries = 5;

		/**
		 * Core Constructor
		 * @global <type> $wpdb
		 * @param <type> $pluginfile
		 * @param <type> $sku
		 * @param <type> $menuid
		 * @param <type> $title
		 * @param <type> $link
		 * @param <type> $dbprefix
		 */
		function Constructor($pluginfile, $sku, $menuid, $title, $link, $dbprefix, $ReqWLM = NULL) {
			global $wpdb;
			global $WishListMemberInstance;
			require_once(ABSPATH . '/wp-admin/includes/plugin.php');

			$this->PluginOptionName = 'WishListLogin2Options';
			$this->TablePrefix = $wpdb->prefix . $dbprefix;
			$this->OptionsTable = $this->TablePrefix . 'options';

			$this->BlogCharset = get_option('blog_charset');

			$this->ProductSKU = $sku;

			$this->MenuID = $menuid;
			$this->Title = $title;
			$this->Link = $link;

			$this->PluginInfo = (object) get_plugin_data($pluginfile);
			$this->Version = $this->PluginInfo->Version;
			$this->WPVersion = $GLOBALS['wp_version'] + 0;

			$this->pluginPath = $pluginfile;
			$this->pluginDir = dirname($this->pluginPath);
			$this->PluginFile = basename(dirname($pluginfile)) . '/' . basename($pluginfile);

			$this->wllogin2 = sanitize_title_with_dashes($this->PluginInfo->Name);
			$this->pluginBasename = plugin_basename($this->pluginPath);
			$this->pluginURL = plugins_url('', $this->pluginPath);

			$this->Menus = array();

			$this->ClearOptions();

			$this->RequireWLM = ($ReqWLM == "RequireWLM") ? true:false;
			$this->RequireWLM = ($this->RequireWLM && !isset($WishListMemberInstance))? false:true;

			if (isset($_POST['WishListLogin2Action']) && $_POST['WishListLogin2Action'] == 'Save') {
				$this->SaveOptions();
			}

			$this->PreloadOptions();

			add_action('init', array(&$this, 'WPWLKeyProcess'));
			add_action('admin_menu', array(&$this, 'AdminMenus'));
			add_action('admin_notices', array(&$this, 'SubMenus'), 1);

			add_action('in_plugin_update_message-' . $this->PluginFile, array(&$this, 'Plugin_Info_Link'));

			add_filter('site_transient_update_plugins', array(&$this, 'Plugin_Update_Notice'));

			add_filter('upgrader_pre_install', array(&$this, 'Pre_Upgrade'), 10, 2);
			add_filter('upgrader_post_install', array(&$this, 'Post_Upgrade'), 10, 2);

			$this->LoadTables();


			//init shortcodes
			if (!isset($_GET['page']) || $this->MenuID != $_GET['page']) {
				global $WLMTinyMCEPluginInstance;
				if (!isset($WLMTinyMCEPluginInstance)) { //instantiate the class only once
					$WLMTinyMCEPluginInstance = new WLMTinyMCEPlugin;
					add_action('admin_init', array(&$WLMTinyMCEPluginInstance, 'TNMCE_PluginJS'), 1);
				}

				$shortcodes = array(
					array('title' => 'Login Button', 'value' => '[wllogin2_button]'),
					array('title' => 'Full Login Form', 'value' => '[wllogin2_full]'),
					array('title' => 'Compact Login Form', 'value' => '[wllogin2_compact]'),
					array('title' => 'Horizontal Login Form', 'value' => '[wllogin2_horizontal]')
				);
				$WLMTinyMCEPluginInstance->RegisterShortcodes("WishList Login2.0", $shortcodes, array());
			}
		}

		/**
		 * Load WishListLogin Tables
		 */
		function LoadTables() {
			global $wpdb;
			// prepare table names
			$this->Tables = new stdClass();
			$p = esc_sql($this->TablePrefix);
			$tables = $wpdb->get_results("SHOW TABLES LIKE '{$this->TablePrefix}%'", ARRAY_N);
			$plen = strlen($this->TablePrefix);
			foreach ($tables AS $table) {
				$x = substr($table[0], $plen);
				$this->Tables->$x = $table[0];
			}
		}

		/**
		 * Core Activation Routine
		 */
		function CoreActivate() {
			$this->CreateDBTables();
		}

		/**
		 * Core Deactivation Routine
		 */
		function CoreDeactivate() {
			/* does nothing at the moment */
		}

		/**
		 * Displays Beta Tester Message
		 */
		function BetaTester($return) {
			$aff = $this->GetOption('affiliate_id');
			$url = $aff ? 'http://PluginURL/wlp.php?af=' . $aff : 'http://PluginURL/';
			$message = "This is a <strong><a href='{$url}'>WishListLogin</a></strong> Beta Test Site.";
			if (is_admin()) {
				echo '<div class="error fade"><p>';
				echo $message;
				echo '</p></div>';
			} else {
				echo '<div style="background:#FFEBE8; border:1px solid #CC0000; border-radius:3px; padding:0.2em 0.6em;">';
				echo $message;
				echo '</div>';
			}
			return $return;
		}

		/**
		 * Displays the admin menus for the plugin
		 */
		function AdminMenus() {
			// Top Menu
			$firstMenu = $this->GetOption('LicenseStatus') != '1' ? 'WPWLKey' : 'AdminPage';
			$firstMenu = $this->RequireWLM ? $firstMenu:"RequireWLM";
			if (!defined('WPWLTOPMENU')) {
				add_menu_page('WishList Plugins', 'WishList Plugins', 'manage_options', 'WPWishList', array(&$this, $firstMenu), $this->pluginURL . '/images/WishListIcon.png');
				define('WPWLTOPMENU', 'WPWishList');
			}

			add_submenu_page(WPWLTOPMENU, $this->Title, $this->Link, 'manage_options', $this->MenuID, array(&$this, $firstMenu));

			// Submenu for "Other Tab"
			$found = false;
			foreach ((array) $GLOBALS['submenu'] AS $key => $sm) {
				foreach ($sm AS $k => $m) {
					if ($m[2] == 'WPWLOther') {
						unset($GLOBALS['submenu'][$key][$k]);
						$found = true;
						$GLOBALS['submenu'][$key][] = $m;
						break;
					}
				}
			}
			if (!$found
			)
				add_submenu_page(WPWLTOPMENU, __('Other WishList Products Plugins', 'wishlist-login2'), 'Other', 'manage_options', 'WPWLOther', array(&$this, 'OtherTab'));
			// End of Submenu for "Other Tab"

			unset($GLOBALS['submenu']['WPWishList'][0]);
		}

		/**
		 * Displays the admin sub-menus for this plugin
		 */
		function SubMenus() {
			if ($_GET['page'] == $this->MenuID) {
				echo '<div class="wl_plugin_page">';
				echo '<h2 class="wl-nav-tab-wrapper">';
				echo '<a class="wl-nav-tab' . ($_GET['wl'] == '' ? ' wl-nav-tab-active' : '') . '" href="?page=' . $this->MenuID . '">' . __('Dashboard', 'plugin-name') . '</a>';
				foreach ((array) $this->Menus AS $key => $menu) {
					$hasSubMenu = ($menu['HasSubMenu']) ? ' has-sub-menu' : '';
					echo '<a class="wl-nav-tab' . ($_GET['wl'] == ($key) ? ' wl-nav-tab-active' . $hasSubMenu : '') . '" href="?page=' . $this->MenuID . '&wl=' . $key . '">' . $menu['Name'] . '</a>';
				}
				echo '</h2>';
				if ($_POST['err'])
					echo '<div class="error fade"><p><b>' . __('Error', 'wishlist-login2') . ':</b> ' . $_POST['err'] . '</p></div>';
				if ($_GET['err'])
					echo '<div class="error fade"><p><b>' . __('Error', 'wishlist-login2') . ':</b> ' . $_GET['err'] . '</p></div>';
				if ($_POST['msg'])
					echo '<div class="updated fade"><p>' . $_POST['msg'] . '</p></div>';
				if ($_GET['msg'])
					echo '<div class="updated fade"><p>' . $_GET['msg'] . '</p></div>';
				echo '</div>';
			}
		}

		/**
		 * Adds an admin menu
		 * @param string $key Menu Key
		 * @param string $name Menu Name
		 * @param string $file Menu File
		 * @param bool $hasSubMenu
		 */
		function AddMenu($key, $name, $file, $hasSubMenu=false) {
			$this->Menus[$key] = array('Name' => $name, 'File' => $file, 'HasSubMenu' => (bool) $hasSubMenu);
		}

		/**
		 * Retrieves a menu object.  Also displays an HTML version of the menu if the $html parameter is set to true
		 * @param string $key The index/key of the menu to retrieve
		 * @param boolean $html If true, it echoes the url in as an HTML link
		 * @return object|false Returns the menu object if successful or false on failure
		 */
		function GetMenu($key, $html=false) {
			$obj = $this->Menus[$key];
			if ($obj) {
				$obj = (object) $obj;
				$obj->URL = '?page=' . $this->MenuID . '&wl=' . $key;
				$obj->HTML = '<a href="' . $obj->URL . '">' . $obj->Name . '</a>';
				if ($html)
					echo $obj->HTML;
				return $obj;
			}else {
				return false;
			}
		}

		/**
		 * Includes the correct admin interface baesd on the query variable "wl"
		 */
		function AdminPage() {
			echo '<div class="wl_plugin_page">';
			$menu = $this->Menus[$_GET['wl']];
			$include = $this->pluginDir . '/admin/' . $menu['File'];
			if (!file_exists($include) || !is_file($include)) {
				$include = $this->pluginDir . '/admin/dashboard.php';
			}

			$show_page_menu = true;
			include($include);
			$show_page_menu = false;
			echo '<div class="wrap">';
			include($include);
			if (WP_DEBUG) {
				echo '<p>' . get_num_queries() . ' queries in ';
				timer_stop(1);
				echo 'seconds.</p>';
			}
			echo '</div>';
			echo '</div>';
		}

		/**
		 * Displays the content for the "Other" Tab
		 */
		function OtherTab() {
			if (!@readfile('http://www.wishlistproducts.com/download/list.html')) {
				echo'<div class="wrap">', __('<h2>Other WishList Products Plugins</h2><p>For more Wordpress tools and resources please visit the <a href="http://wishlistproducts.com/blog" target="_blank">WishList Products Blog</a></p>', 'wishlist-login2') . '</div>';
			}
		}
		/**
		 * Displays the message the requires WLM to be activated
		 */
		function RequireWLM() {
			?>
			<div class="wrap">
				<div class="WLMRequireHolder error"><p><strong>WishList Member</strong> is required for this plugin to work.</p></div>
			</div>
			<?php
		}
		/**
		 * Displays the interface where the customer can enter the license information
		 */
		function WPWLKey() {
			?>
			<div class="wrap">
				<h2>WishList Products License Information</h2>
				<form method="post">
					<table class="form-table">
						<tr valign="top">
							<td colspan="3" style="border:none"><?php _e('Please enter your WishList Products Key and Email below to activate this plugin', 'wishlist-login2'); ?></td>
						</tr>
						<tr valign="top">
							<th scope="row" style="border:none;white-space:nowrap" class="WLRequired"><?php _e('WishList Products Email', 'wishlist-login2'); ?></th>
							<td style="border:none"><input type="text" name="<?php $this->Option('LicenseEmail', true); ?>" placeholder="WishList Products Email" value="<?php $this->OptionValue(); ?>" size="32" /></td>
							<td style="border:none"><?php _e('(Please enter the email you used during your registration/purchase)', 'wishlist-login2'); ?></td>
						</tr>
						<tr valign="top">
							<th scope="row" style="border:none;white-space:nowrap;" class="WLRequired"><?php _e('WishList Products Key', 'wishlist-login2'); ?></th>
							<td style="border:none"><input type="text" name="<?php $this->Option('LicenseKey', true); ?>" placeholder="WishList Products Key" value="<?php $this->OptionValue(); ?>" size="32" /></td>
							<td style="border:none"><?php _e('(This was sent to the email you used during your purchase)', 'wishlist-login2'); ?></td>
						</tr>
					</table>
					<p class="submit">
						<input type="hidden" value="0" name="<?php $this->Option('LicenseLastCheck'); ?>" />
						<?php $this->Options();
						$this->RequiredOptions(); ?>
						<input type="hidden" value="<strong>License Information Saved</strong>" name="WLSaveMessage" />
						<input type="hidden" value="Save" name="WishListLogin2Action" />
						<input type="submit" value="Save WishList Products License Information" name="Submit" />
					</p>
				</form>
			</div>
			<?php
		}

		function ActivationWarning() {
			$rets = $this->GetOption('LicenseRets', true, true);
			if (is_admin() && $rets > 0 && $rets < self::ActivationMaxRetries) {
				echo '<div class="error fade"><p>';
				echo __('Warning: Unable to contact License Activation Server. We will keep on trying. <a href="http://wlplink.com/go/activation" target="_blank">Click here for more info.</a>', 'wishlist-login2');
				echo '</p></div>';
			}
		}
		/**
		 * Checks whether a url is possibly local
		 * @param string $url the url to test
		 */
		function isLocal($url) {
			$exceptions = array(
				'home.com',
				'localhost.com',
				'work.com'
			);

			$excludeable_domain = array(
				'home',
				'localhost',
				'work'
			);

			$excludeable_tld = array(
				'loc',
				'dev'
			);

			$res = parse_url($url);

			// not excludeable
			if($res === false) {
				return false;
			}


			$host = $res['host'];
			if(stripos($host, '.')) {

				$parts = explode('.', $host);
				$tld = $parts[count($parts) - 1];
				$domain = $parts[count($parts) - 2];

				//exception to our rules?
				if(in_array($domain.".".$tld, $exceptions)) {
					return false;
				}

				if(in_array($domain, $excludeable_domain)) {
					return true;
				}

				if(in_array($tld, $excludeable_tld)) {
					return true;
				}
			} else {
				//empty tld
				return true;
			}
			return false;
		}
		/**
		 * Processes the license information
		 */
		function WPWLKeyProcess() {
			//bypass activation for
			if ($this->isLocal(strtolower(get_bloginfo('url'))) || $this->ProductSKU == 0) {
				$WPWLCheckResponse = '';
				$this->SaveOption('LicenseLastCheck', time());
				$this->SaveOption('LicenseStatus', 1);
				return;
			}

			$WPWLKey=$this->GetOption('LicenseKey');
			$WPWLEmail=$this->GetOption('LicenseEmail');
			$LicenseStatus=$this->GetOption('LicenseStatus');
			$Retries=$this->GetOption('LicenseRets',true,true)+0;
			$this->isBetaTester=$WPWLEmail=='beta@wishlistproducts.com';
			if($this->isBetaTester){
				add_action('admin_notices',array(&$this,'BetaTester'));
				add_action('the_content',array(&$this,'BetaTester'));
			}
			$WPWLLast=$this->GetOption('LicenseLastCheck');
			$WPWLPID=$this->ProductSKU;
			$WPWLCheck=md5("{$WPWLKey}_{$WPWLPID}_".($WPWLURL=strtolower(get_bloginfo('url'))));
			$WPWLKeyAction=$_POST['wordpress_wishlist_deactivate']==$WPWLPID?'deactivate':'activate';
			$WPWLTime=time();
			$Month=60*60*24*7*30;

			if(empty($WPWLKey) && empty($WPWLEmail)) {
				//do not even try
				return;
			}
			//error_log('Checking again in '. ($WPWLLast - ($WPWLTime-$Month) ));
			//error_log('Retries: '.$Retries);
			if($WPWLTime-$Month>$WPWLLast || $WPWLKeyAction=='deactivate'){
				error_log('rechecking');
				$urls=explode(',',self::ActivationURLs);
				$urlargs=array(
					'',
					'',
					urlencode($WPWLKey),
					urlencode($WPWLPID),
					urlencode($WPWLCheck),
					urlencode($WPWLEmail),
					urlencode($WPWLURL),
					urlencode($WPWLKeyAction),
					urlencode($this->Version)
				);
				foreach($urls AS &$url){
					$urlargs[0]='http://%s/activ8.php?key=%s&pid=%d&check=%s&email=%s&url=%s&%s=1&ver=%s';
					$urlargs[1]=$url;
					$url=call_user_func_array('sprintf',$urlargs);
				}

								$WPWLStatus = $WPWLCheckResponse = 0;
				if ($WPWLKeyAction == 'deactivate' OR (!empty($WPWLKey) && !empty($WPWLEmail) && trim($WPWLKey) != '' && trim($WPWLEmail) != '')) {
					$WPWLStatus = $WPWLCheckResponse = $this->ReadURL($urls, 5);
				}

				if($WPWLStatus===false){
					if($Retries>=self::ActivationMaxRetries || $LicenseStatus!=1){
						$WPWLStatus = $WPWLCheckResponse = 'Unable to contact License Activation Server. <a href="http://wlplink.com/go/activation" target="_blank">Click here for more info.</a>';
					}else{
						$this->SaveOption('LicenseRets', $Retries+1, true);
						$WPWLStatus = $this->GetOption('LicenseStatus');
					}

					//staggered rechecks
					//if there is an error with wlm servers, check after an hour
					//so that we won't keep making requests
					$Month=60*60*24*7*30;
					$checkafter = 60 * 60 * 24 * 7;
					//For testing check after a minute
					//$checkafter = 60;
					$this->SaveOption('LicenseLastCheck',$WPWLTime - $Month + ($checkafter));
				}else{
					$this->SaveOption('LicenseRets', 0, true);
					$this->SaveOption('LicenseLastCheck',$WPWLTime);
				}

				$WPWLStatus = trim($WPWLStatus);
				$this->SaveOption('LicenseStatus',$WPWLStatus);

				if($WPWLKeyAction=='deactivate'){
					$this->DeleteOption('LicenseKey','LicenseEmail');
				}
			}


			$this->WPWLCheckResponse=$WPWLCheckResponse;

			if($Retries>0){
				add_action('admin_notices',array(&$this,'ActivationWarning'));
			}

			if($this->GetOption('LicenseStatus')!='1'){
				add_action('admin_notices',array(&$this,'WPWLKeyResponse'),1);
			}
		}

		/**
		 * Displays the license processing status
		 */
		function WPWLKeyResponse() {
			if (strlen($this->WPWLCheckResponse) > 1
			)
				echo '<div class="updated fade" id="message"><p style="color:#f00"><strong>' . $this->WPWLCheckResponse . '</strong></p></div>';
		}

		/**
		 * Returns the Query String. Pass a GET variable and that gets removed.
		 */
		function QueryString() {
			$args = func_get_args();
			$args[] = 'msg';
			$args[] = 'err';
			$get = array();
			parse_str($_SERVER['QUERY_STRING'], $querystring);
			foreach ((array) $querystring AS $key => $value)
				$get[$key] = "{$key}={$value}";
			foreach ((array) array_keys((array) $get) AS $key) {
				if (in_array($key, $args))
					unset($get[$key]);
			}
			return implode('&', $get);
		}

		/**
		 * Sets up an array of form options
		 * @param string $name of the option
		 * @param boolean $required Specifies if the option is a required option
		 */
		function Option($name='', $required=false) {
			if ($name) {
				$this->FormOption = $name;
				$this->FormOptions[$name] = (bool) $required;
				echo $name;
			} else {
				echo $this->FormOption;
			}
		}

		/**
		 * Retrieves the value of the form option that was previously set with Option method
		 * @param boolean $return Specifies whether to return the value or just output it to the browser
		 * @param string $default Default value to display
		 * @return string The value of the option
		 */
		function OptionValue($return=false, $default='') {
			if ($_POST['err']) {
				$x = $_POST[$this->FormOption];
			} else {
				$x = $this->GetOption($this->FormOption);
			}
			if (!strlen($x)
			)
				$x = $default;
			if ($return
			)
				return $x;
			echo htmlentities($x, ENT_QUOTES, $this->BlogCharset);
		}

		/**
		 * Outputs selected="true" to the browser if $value is equal to the value of the option that was previously set
		 * @param string $value
		 */
		function OptionSelected($value) {
			$x = $this->OptionValue(true);
			if ($x == $value)
				echo ' selected="true"';
		}

		/**
		 * Outputs checked="true" to the browser if $value is equal to the value of the option that was previously set
		 * @param string $value
		 */
		function OptionChecked($value) {
			$x = $this->OptionValue(true);
			if ($x == $value)
				echo ' checked="true"';
		}

		/**
		 * Echoes form options that were set as a comma delimited string
		 * @param boolean $html echoes form options as the value of a hidden input field with the name "WLOptions"
		 */
		function Options($html=true) {
			$value = implode(',', array_keys((array) $this->FormOptions));
			if ($html) {
				echo '<input type="hidden" name="WLOptions" value="' . $value . '" />';
			} else {
				echo $value;
			}
		}

		/**
		 * Echoes REQUIRED form options that were set as a comma delimited string
		 * @param boolean $html echoes form options as the value of a hidden input field with the name "WLRequiredOptions"
		 */
		function RequiredOptions($html=true) {
			$value = implode(',', array_keys((array) $this->FormOptions, true));
			if ($html) {
				echo '<input type="hidden" name="WLRequiredOptions" value="' . $value . '" />';
			} else {
				echo $value;
			}
		}

		/**
		 * Clears the form options array
		 */
		function ClearOptions() {
			$this->FormOptions = array();
		}

		// -----------------------------------------
		// Saves Options
		/**
		 * Saves the form options passed by POST
		 * @param boolean $showmsg whether to display the "Settings Saved" message or not
		 * @return boolean Returns false if a required field is not set
		 */
		function SaveOptions($showmsg=true) {
			foreach ((array) $_POST AS $k => $v) {
				if (!is_array($v)
				)
					$_POST[$k] = trim(stripslashes($v));
			}

			$required = explode(',', $_POST['WLRequiredOptions']);
			foreach ((array) $required AS $req) {
				if ($req && !$_POST[$req]) {
					$_POST['err'] = __('<strong>Error:</strong> Fields marked with an asterisk (*) are required', 'wishlist-login2');
					return false;
				}
			}


			$options = explode(',', $_POST['WLOptions']);
			foreach ((array) $options AS $option) {
				$this->SaveOption($option, $_POST[$option]);
			}
			if ($showmsg
			)
				$_POST['msg'] = $_POST['WLSaveMessage'] ? $_POST['WLSaveMessage'] : __('Settings Saved', 'wishlist-login2');
		}

		/**
		 * Cache all autoload options
		 */
		function PreloadOptions() {
			global $wpdb;
			$results = $wpdb->get_results("SELECT `option_name`, `option_value` FROM `{$this->OptionsTable}` WHERE `autoload`='yes'");
			if (!count($results)
			)
				return;
			foreach ($results AS $result) {
				if (substr($result->option_name, 0, 3) != 'xxx') {
					$value = maybe_unserialize($result->option_value);
					wp_cache_set($result->option_name, $value, $this->OptionsTable);
				}
			}
		}

		/**
		 * Retrieves an option's value
		 * @param string $option The name of the option
		 * @param boolean $dec (optional) True to decrypt the return value
		 * @param boolean $no_cache (optional) True to skip cache data
		 * @return string The option value
		 */
		function GetOption($option, $dec=null, $no_cache=null) {
			global $wpdb;
			$cache_key = $option;
			$cache_group = $this->OptionsTable;

			if (is_null($dec))
				$dec = false;
			if (is_null($no_cache))
				$no_cache = false;

			$value = ($no_cache === true) ? false : wp_cache_get($cache_key, $cache_group);
			if ($value === false) {
				$row = $wpdb->get_row($wpdb->prepare("SELECT `option_value` FROM `{$this->OptionsTable}` WHERE `option_name`='%s'", $option));
				if (!is_object($row))
					return false;
				$value = $row->option_value;

				$value = maybe_unserialize($value);

				wp_cache_set($cache_key, $value, $cache_group);
			}
			if ($dec) {
				$value = $this->WLMDecrypt($value);
			}
			return $value;
		}

		/**
		 * Deletes the option names passed as parameters
		 */
		function DeleteOption() {
			global $wpdb;
			$cache_group = $this->OptionsTable;
			$x = func_get_args();

			foreach ($x as $option) {
				$cache_key = $option;
				$wpdb->query($wpdb->prepare("DELETE FROM `{$this->OptionsTable}` WHERE `option_name`='%s'", $option));
				wp_cache_delete($cache_key, $cache_group);
			}
		}

		/**
		 * Saves an option
		 * @param string $option Name of the option
		 * @param string $value Value of option
		 * @param $enc (default false) True to encrypt $value
		 */
		function SaveOption($option, $value, $enc=false) {
			global $wpdb;
			$cache_key = $option;
			$cache_group = $this->OptionsTable;
			if ($enc)
				$value = $this->WLMEncrypt($value);

			$x = $this->GetOption($option);
			if ($x === false) {
				$x = $this->AddOption($option, $value, $enc);
				return $x ? true : false;
			} elseif ($x != $value) {
				$data = array(
						'option_name' => $option,
						'option_value' => maybe_serialize($value)
				);
				$where = array(
						'option_name' => $option
				);
				$x = $wpdb->update($this->OptionsTable, $data, $where);

				wp_cache_delete($cache_key, $cache_group);
				return $x ? true : false;
			}
		}

		/**
		 * Adds a new option. Will not add it if the option already exists.
		 * @param string $option Name of the option
		 * @param string $value Value of option
		 * @param $enc (default false) True to encrypt $value
		 */
		function AddOption($option, $value, $enc=false) {
			global $wpdb;
			$cache_key = $option;
			$cache_group = $this->OptionsTable;
			$x = $this->GetOption($option);
			if ($x === false) {
				if ($enc)
					$value = $this->WLMEncrypt($value);
				$data = array(
						'option_name' => $option,
						'option_value' => maybe_serialize($value)
				);
				$x = $wpdb->insert($this->OptionsTable, $data);
				wp_cache_delete($cache_key, $cache_group);
			}
			return $x ? true : false;
		}

		/**
		 * Reads the content of a URL using Wordpress WP_Http class if possible
		 * @param string|array $url The URL to read. If array, then each entry is checked if the previous entry fails
		 * @param int $timeout (optional) Optional timeout. defaults to 5
		 * @param bool $file_get_contents_fallback (optional) true to fallback to using file_get_contents if WP_Http fails. defaults to false
		 * @return mixed FALSE on Error or the Content of the URL that was read
		 */
		function ReadURL($url, $timeout=null, $file_get_contents_fallback=null, $wget_fallback=null) {
			$urls = (array) $url;
			if (is_null($timeout))
				$timeout = 30;
			if (is_null($file_get_contents_fallback))
				$file_get_contents_fallback = false;
			if (is_null($wget_fallback))
				$wget_fallback = false;

			$x = false;
			foreach ($urls AS $url) {
				if (class_exists('WP_Http')) {
					$http = new WP_Http;
					$req = $http->request($url, array('timeout' => $timeout));
					$x = (is_wp_error($req) OR is_null($req) OR $req === false) ? false : ($req['response']['code'] == '200' ? $req['body'] . '' : false);
				} else {
					$file_get_contents_fallback = true;
				}

				if ($x === false && ini_get('allow_url_fopen') && $file_get_contents_fallback) {
					$x = file_get_contents($url);
				}

				if ($x === false && $wget_fallback) {
					exec('wget -T ' . $timeout . ' -q -O - "' . $url . '"', $output, $error);
					if ($error) {
						$x = false;
					} else {
						$x = trim(implode("\n", $output));
					}
				}

				if ($x !== false) {
					return $x;
				}
			}
			return $x;
		}

		/**
		 * Just return False
		 * @return boolean Always False
		 */
		function ReturnFalse() {
			return false;
		}

		/**
		 * Retrieves the tooltip id
		 * @return string Tooltip
		 */
		function Tooltip($tooltpid) {
			$thisTooltip = '<a class="help" rel="#' . $tooltpid . '" href="help"><span><img src="' . $this->pluginURL . '/images/helpicon.png"></span></a>';
			return $thisTooltip;
		}

		function Plugin_Download_Url() {
			static $url;
			if ($this->GetOption('LicenseStatus') != 1) {
				return false;
			}
			if (!$url) {
				$url = 'http://wishlistproducts.com/download/' . $this->GetOption('LicenseKey') . '/==' . base64_encode(pack('i', $this->ProductSKU));
			}
			return $url;
		}

		function Plugin_Latest_Version() {
			static $latest_ver;
			$varname = 'WishListLogin2_Latest_Plugin_Version';
			if (empty($latest_ver) OR isset($_GET['checkversion'])) {
				$latest_ver = get_transient($varname);
				if (empty($latest_ver) OR isset($_GET['checkversion'])) {
					$latest_ver = $this->ReadURL('http://wishlistactivation.com/versioncheck/?wllogin2');
					if(empty($latest_ver)){
						$latest_ver=$this->Version;
					}
					set_transient($varname, $latest_ver, 60 * 60 * 24);
				}
			}
			return $latest_ver;
		}

		function Plugin_Is_Latest() {
			$latest_ver = $this->Plugin_Latest_Version();
			$ver = $this->Version;
			if (preg_match('/^(\d+\.\d+)\.{' . 'GLOBALREV}$/', $this->Version, $match)) {
				$ver = $match[1];
				preg_match('/^(\d+\.\d+)\.[^\.]*/', $latest_ver, $match);
				$latest_ver = $match[1];
			}
			return version_compare($latest_ver, $ver, '<=');
		}

		function Plugin_Update_Notice($transient) {
			static $our_transient_response;

			if ($this->Plugin_Is_Latest()) {
				return $transient;
			}

			if (!$our_transient_response) {
				$package = $this->Plugin_Download_Url();
				if ($package === false)
					return $transient;

				$file = $this->PluginFile;

				$our_transient_response = array(
						$file => (object) array(
								'id' => 'wishlist-login2-' . time(),
								'slug' => $this->wllogin2,
								'new_version' => $this->Plugin_Latest_Version(),
								'url' => 'http://wordpress.org/extend/plugins/akismet/',
								'package' => $package
						)
				);
			}

			$transient->response = array_merge((array) $transient->response, (array) $our_transient_response);
			return $transient;
		}

		function Plugin_Info_Link() {
			echo <<<STRING
<span class="wishlist-login2_update-span"></span>
<script type="text/javascript">
	var wishlistproducts_link=jQuery('.wishlist-login2_update-span').siblings('a')[0];
	jQuery(wishlistproducts_link).attr('href','http://wishlistactivation.com/changelog.php?wishlist-login2');
	jQuery(wishlistproducts_link).attr('class','');
	jQuery(wishlistproducts_link).attr('target','_blank');
</script>
STRING;
		}

		function Pre_Upgrade($return, $plugin) {
			$plugin = (isset($plugin['plugin'])) ? $plugin['plugin'] : '';
			if ($plugin == $this->PluginFile) {
				$dir = sys_get_temp_dir() . '/' . 'wishlist-login2-Upgrade';

				$this->Recursive_Delete($dir);

				$this->Recursive_Copy($this->pluginDir . '/lang', $dir . '/lang');
			}
			return $return;
		}

		function Post_Upgrade($return, $plugin) {
			$plugin = (isset($plugin['plugin'])) ? $plugin['plugin'] : '';
			if ($plugin == $this->PluginFile) {
				$dir = sys_get_temp_dir() . '/' . 'wishlist-login2-Upgrade';

				$this->Recursive_Copy($this->pluginDir . '/lang', $dir . '/lang');

				$this->Recursive_Copy($dir . '/lang', $this->pluginDir . '/lang');

				$this->Recursive_Delete($dir);
			}
			return $return;
		}

		/**
		 * Deletes an entire directory tree
		 * @param string $dir Folder Name
		 */
		function Recursive_Delete($dir) {
			if (substr($dir, -1) != '/')
				$dir.='/';
			$files = glob($dir . '*', GLOB_MARK);
			foreach ($files AS $file) {
				if (is_dir($file)) {
					$this->Recursive_Delete($file);
					rmdir($file);
				} else {
					unlink($file);
				}
			}
			rmdir($dir);
		}

		function Recursive_Copy($source, $dest) {
			if (substr($source, -1) != '/')
				$source.='/';
			$files = glob($source . '*', GLOB_MARK);
			if (!file_exists($dest) || !is_dir($dest)) {
				mkdir($dest, 0777, true);
			}
			foreach ($files AS $file) {
				if (is_dir($file)) {
					$this->Recursive_Copy($file, $dest . '/' . basename($file));
				} else {
					copy($file, $dest . '/' . basename($file));
				}
			}
		}

		/**
		 * Simple obfuscation to garble some text
		 * @param string $string String to obfuscate
		 * @return string Obfucated string
		 */
		function WLMEncrypt($string) {
			$string = serialize($string);
			$hash = md5($string);
			$string = base64_encode($string);
			for ($i = 0; $i < strlen($string); $i++) {
				$c = $string[$i];
				$o = ord($c);
				$o = $o << 1;
				$string[$i] = chr($o);
			}
			return str_rot13(base64_encode($string)) . $hash;
		}

		/**
		 * Simple un-obfuscation to restore garbled text
		 * @param string $string String to un-obfuscate
		 * @return string Un-obfucated string
		 */
		function WLMDecrypt($string) {
			/* if $string is not a string then return $string, get it? */
			if (!is_string($string))
				return $string;

			$orig = $string;
			$hash = substr($string, -32);
			$string = base64_decode(str_rot13(substr($string, 0, -32)));
			for ($i = 0; $i < strlen($string); $i++) {
				$c = $string[$i];
				$o = ord($c);
				$o = $o >> 1;
				$string[$i] = chr($o);
			}
			$string = base64_decode($string);

			if (md5($string) == $hash) {
				// call Decrypt again until it can no longer be decrypted
				return $this->WLMDecrypt(unserialize($string));
			} else {
				return $orig;
			}
		}

	}

}
?>
