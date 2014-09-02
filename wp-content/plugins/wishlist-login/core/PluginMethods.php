<?php

/**
 * Plugin Methods Class for WishListLogin
 * @author Author Name <author@email.com>
 * @package WishListLogin2
 *
 * @version $Rev$
 * $LastChangedBy$
 * $LastChangedDate$
 */
if (!defined('ABSPATH'))
	die();
if (!class_exists('WishListLogin2_PluginMethods')) {

	/**
	 * Plugin Methods WishListLogin Class
	 * @package WishListLogin2
	 * @subpackage classes
	 */
	class WishListLogin2_PluginMethods extends WishListLogin2_DBMethods {
		/*
		 * add your additional methods here
		 *
		 * DO NOT PUT WORDPRESS HOOKS HERE
		 */
		public function migrate_post_login() {
			if($this->GetOption('sociallogin-migrated')) {
				return;
			}
			$settings = get_option('wlmpl-options');
			$this->SaveOption('suffix', $settings['suffix']);

			if($settings['redirect'] == 'use_wlm_redirect') {
				$this->SaveOption('redirect_to', 'wishlistmember');
			} else {
				$this->SaveOption('redirect_to', 'post');
			}

			$this->SaveOption('postlogin-migrated', 1);

		}
		public function migrate_social_login() {
			if($this->GetOption('sociallogin-migrated')) {
				return;
			}
			global $wpdb;
			$res = $wpdb->get_results("SELECT * FROM $wpdb->options WHERE option_name LIKE 'wlsocialconnect%'");
			foreach($res as $r) {
				$this->SaveOption($r->option_name, $r->option_value);
			}

			//migrate enabled social logins
			$soclogin_options = $wpdb->prefix . 'wlm_soclogin_options';
			$settings =$wpdb->get_col("SELECT option_value FROM $soclogin_options WHERE option_name = 'wlsocloginsettings' LIMIT 1");
			$settings = unserialize($settings[0]);
			$this->SaveOption('wllogin2settings', $settings);



			$settings = $wpdb->get_col("SELECT option_value FROM $soclogin_options WHERE option_name = 'social_login_page' LIMIT 1");
			$this->SaveOption('connect_page', $settings[0]);
			$this->SaveOption('sociallogin-migrated', 1);
		}
		public function migrate() {
			$this->migrate_social_login();
			$this->migrate_post_login();

			$removes = array(
				'wishlist-post-login/wishlist-post-login.php',
				'wishlist-social-login/wishlist-social-login.php',
				'wishlist-login/wishlist-login.php'
			);

			foreach($removes as $r) {
				if(is_plugin_active($r)) {
					deactivate_plugins($r, true);
				}
			}

		}
		function Plugin_Update_Url() {
      		return wp_nonce_url('update.php?action=upgrade-plugin&plugin=' . $this->PluginFile, 'upgrade-plugin_' . $this->PluginFile);
    	}
	}
}
?>