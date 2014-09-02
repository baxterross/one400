<?php
/*
Plugin Name: Instapage Wordpress Plugin
Plugin URI: http://www.instapage.com/
Description: Instapage Wordpress Plugin
Author: instapage
Version: 1.6.2
Author URI: http://www.instapage.com/
License: GPLv2
* Text Domain: instapage
*/

if (!class_exists('instapage')) {

define( 'instapage_ABS_URL', plugin_dir_url( __FILE__ ) );
require_once( 'view.class.php' );

class InstapageApiCallException extends Exception {}

class InstaPage
{
	const wp_version_required = '3.4';
	const php_version_required = '5.2';
	const endpoint = 'http://app.instapage.com';
	//const endpoint = 'http://instapage.me';

	protected $my_pages = false;
	protected $plugin_details = false;
	protected $posts = false;
	protected $message = false;
	protected $cached_service_lifetime = 86400;

	public function __construct()
	{
		$compat_status = $this->compatibility();

		if ($compat_status !== true)
		{
			$this->showMessage( false, $compat_status );
			return;
		}

		if ( get_option( 'permalink_structure' ) == '' )
		{
			$this->showMessage( false, 'instapage plugin needs <a href="options-permalink.php">permalinks</a> enabled!' );
			return;
		}

		if( is_admin() )
		{
			$this->registerAutoUpdate();

			add_action( 'init', array(&$this, 'instapagePostRegister' ) );
			add_action( 'add_meta_boxes', array(&$this, 'addCustomMetaBox' ) );
			add_action( 'save_post', array(&$this, 'saveCustomMeta' ), 10, 2 );
			add_action( 'save_post', array(&$this, 'validateCustomMeta'), 20, 2 );
			add_filter( 'manage_edit-instapage_post_columns', array(&$this, 'editPostsColumns' ) );
			add_action( 'manage_posts_custom_column', array(&$this, 'populateColumns' ) );
			add_filter( 'post_getUpdatedMessages', array(&$this, 'getUpdatedMessage' ) );
			add_filter( 'display_post_states', array(&$this, 'customPostState' ) );
			add_filter( 'post_row_actions', array(&$this, 'removeQuickEdit' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array(&$this, 'customizeAdministration' ), 11 );

			add_action( 'admin_menu', array(&$this, 'pluginOptionsMenu'), 11 );
			add_filter( 'plugin_action_links', array(&$this, 'addPluginActionLink'), 10, 2 );
		}

		add_filter( 'the_posts', array(&$this, 'checkCustomUrl' ), 1 );
		add_action( 'parse_request', array(&$this, 'checkRoot'), 1 );
		add_action( 'template_redirect', array(&$this, 'check404Page'), 1 );
	}

	/**
	 * Add a link to the settings page from the plugins page
	 */
	function addPluginActionLink( $links, $file ) {
		static $this_plugin;

		if( empty($this_plugin) ) $this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="' . admin_url( 'options-general.php?page=' . $this_plugin ) . '">' . __('Settings', 'instapage') . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	public function pluginOptionsMenu()
	{
		add_options_page( 'instapage', 'instapage', 'administrator', __FILE__, array( &$this, 'showSettingsPage' ) );
	}

	// Add the Meta Box
	public function addCustomMetaBox()
	{
		$this->silentUpdateCheck();

		add_meta_box
		(
			'instapage_meta_box',
			'Configure your instapage',
			array(&$this, 'showCustomMetaBox'),
			'instapage_post',
			'normal',
			'high'
		);
	}

	public function check404Page()
	{
		$not_found = $this->get404InstaPage();

		if( $not_found === false )
		{
			return;
		}
		if( is_404() )
		{
			$id = $this->get404InstaPage();
			$this->displayCustom404($id);
		}
	}

	public function checkCustomUrl($posts)
	{
		global $post;

		if( is_admin() )
		{
			return $posts;
		}

		if( $this->isServicesRequest() )
		{
			return $this->processProxyServices();
		}

		if( $_GET[ 'instapage_post' ] )
		{
			// draft mode
			function get_post_id_from_slug( $slug )
			{
				global $wpdb;

				return $wpdb->get_var( "SELECT ID FROM `{$wpdb->posts}` WHERE post_name = '". $wpdb->escape( $slug ) ."' LIMIT 1" );
			}

			$instapage_id = get_post_meta( (int) get_post_id_from_slug( $_GET[ 'instapage_post' ] ), 'instapage_my_selected_page' );
			$html = $this->getPageHtml( $instapage_id[ 0 ] );
		}
		else
		{
			// Determine if request should be handled by this plugin
			$requested_page = $this->parseRequest();

			if( $requested_page == false )
			{
				return $posts;
			}

			$html = $this->getPageHtml( $requested_page['id'] );
		}

		if ( ob_get_length() > 0 )
		{
			ob_end_clean();
		}

		status_header('200');

		header('Access-Control-Allow-Origin: *');

		print $html;
		die();
	}

	protected function checkForDeactivation()
	{
		wp_clear_scheduled_hook('lp_check_event');
	}

	public function checkForPluginUpdate( $option, $cache = true )
	{
		$response = $this->getCachedService( 'update-check', $this->cached_service_lifetime );

		if ( !$response )
		{
			return $option;
		}

		$current_version = $this->pluginGet( 'Version' );

		if ( $current_version == $response->data[ 'current-version' ] )
		{
			return $option;
		}

		if ( version_compare( $current_version, $response->data[ 'current-version' ], '>' ) )
		{
			return $option; // you have the latest version
		}

		$plugin_path = 'instapage/instapage.php';

		if( empty( $option->response[ $plugin_path ] ) )
		{
			$option->response[$plugin_path] = new stdClass();
		}

		$option->response[$plugin_path]->url = 'http://www.instapage.com'; //$response->data[ 'download-url' ];
		$option->response[$plugin_path]->slug = 'instapage';
		$option->response[$plugin_path]->package = $response->data[ 'download-url' ];
		$option->response[$plugin_path]->new_version = $response->data[ 'current-version' ];
		$option->response[$plugin_path]->id = "0";

		return $option;
	}

	protected function checkForUpdates($full = false)
	{
		if (defined('WP_INSTALLING'))
		{
			return false;
		}

		$response = $this->getCachedService( 'update-check', $this->cached_service_lifetime );

		if( $full === true )
		{
			return $response;
		}

		if( !$response )
		{
			return false;
		}

		$current_version = $this->pluginGet( 'Version' );

		if ( $current_version == $response->data[ 'current-version' ] )
		{
			return false;
		}

		if ( version_compare( $current_version, $response->data[ 'current-version' ], '>' ) )
		{
			return false;
		}

		return $response->data[ 'current-version' ];
	}

	public function checkRoot()
	{
		if( is_admin() )
		{
			return;
		}

		// current for front page override
		$front = $this->getFrontInstaPage();

		if ( $front === false )
		{
			return;
		}

		// get current full url
		$current = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		// calculate the path
		$part = substr($current, strlen(site_url()));
		// remove any variables from the url
		$pos = strpos($part, '?');
		$rest_part = false;
		if ($pos !== false) {
			$rest_part = substr($part, $pos, strlen($part));
			$part = substr($part, 0, $pos);
		}

		// display the homepage if enabled
		if ($part === '' || $part == 'index.php' || $part == '/' || $part == '/index.php') {
			if ($front !== false) {
				$mp = $this->getPageById($front);

				if ($mp !== false && $mp->post_status == 'publish') {
					// get and display the page at root
					$html = $this->getPageHtml($mp->lp_id);
					if (ob_get_length() > 0) ob_end_clean();
					// flush previous cache
					if (!(substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && ob_start("ob_gzhandler"))) ob_start();
					status_header('200');
					print $html;
					ob_end_flush();
					die();
				}
			}
		}
	}

	public static function compatibility()
	{
		global $wp_version;
		// Check wordpress version
		if (version_compare(self::wp_version_required, $wp_version, '>')) {
			return 'instapage plugin requires Wordpress minimum version of ' . self::wp_version_required;
		}

		if (version_compare(self::php_version_required, phpversion(), '>')) {
			return 'instapage requires PHP minimum version of ' . self::php_version_required;
		}
		return true;
	}

	public function customPostState($states)
	{
		global $post;
		$show_custom_state = null !== get_post_meta($post->ID, 'instapage_my_selected_page' );
		if ($show_custom_state) {
			$states = array();
		}
		return $states;
	}

	public function customizeAdministration()
	{
		global $post_type;
		if ('instapage_post' == $post_type)
		{
			$this->loadCustomWpAdminStyle();
		}
	}

	public function displayCustom404($id_404)
	{
		// show the InstaPage
		$mp = $this->getPageById($id_404);
		$html = $this->getPageHtml($mp->lp_id);
		if (ob_get_length() > 0) ob_end_clean();
		status_header('404');
		print $html;
		die();
	}

	public function editPostsColumns($columns)
	{
		$cols = array();
		$cols['cb'] = $columns['cb'];
		$cols['instapage_post_name'] = 'Name';
		$cols['instapage_post_type'] = 'Type';
		$cols['instapage_post_path'] = 'Url';
		$cols['date'] = 'Date';
		return $cols;
	}

	/**
	 * Exclude from WP updates
	 **/
	public static function excludeUpdates($r, $url)
	{
		if (0 !== strpos($url, 'http://api.wordpress.org/plugins/update-check'))
		{
			return $r; // Not a plugin update request. Bail immediately.
		}

		if( $r && $r['body'] && $r['body']['plugins'] )
		{
			$plugins = unserialize( $r['body']['plugins'] );

			if( !$plugins )
			{
				return null;
			}

			unset( $plugins->plugins[ 'instapage' ] );
			unset( $plugins->active[ array_search( 'instapage', $plugins->active ) ] );

			$r['body']['plugins'] = serialize( $plugins );

			return $r;
		}
	}

	public function fixHtmlHead($html)
	{
		$html = str_replace( 'PROXY_SERVICES', site_url() ."/instapage-proxy-services?url=", $html );
		return $html;
	}

	public function formatError($msg) {
		return <<<EOT
<!DOCTYPE html>
<html>
  <head>
	<title>Error</title>
	<style type="text/css">
	</style>
  </head>
  <body>
	<div class="container error-box">
		<h3><a href="https://www.instapage.com/">instapage&trade;</a> Alert</h3>
		<div class="error">$msg</div>
		<div>
			<a href="http://www.instapage.com/">instapage&trade;</a>
			<a href="http://www.instapage.com/support/">Support</a>
		</div>
	</div>
  </body>
</html>
EOT;
	}

	public static function get404InstaPage()
	{
		$v = get_option('instapage_404_page_id', false);
		return ($v == '') ? false : $v;
	}

	public function getAllPosts()
	{
		if ($this->posts === false)
		{
			$front = $this->getFrontInstaPage();
			$p = $this->getMyPosts();
			$res = array();
			foreach ($p as $k => $v) {
				if ($front == $k) continue;
				$res[$v['instapage_slug']] = array(
					'id' => $v['instapage_my_selected_page'],
					'name' => $v['instapage_name']
				);
			}

			$this->posts = $res;
		}

		return $this->posts;
	}

	public function getCachedService( $url, $lifetime = null )
	{
		$hash = 'instapage.cached-service.'. md5( $url );

		if( $lifetime )
		{
			$cached_response_object = get_option( $hash, false );

			if( $cached_response_object && !is_object( $cached_response_object ) )
			{
				$cached_response_object = unserialize( $cached_response_object );
			}
		}

		if( !$cached_response_object || time() - $cached_response_object->time - $lifetime > 0  )
		{
			try
			{
				$response = $this->instapageApiCall( $url );

				$cached_response_object = new stdClass();
				$cached_response_object->response = $response;
				$cached_response_object->time = time();

				add_option( $hash, false );
				update_option( $hash, serialize( $cached_response_object ), null, false );
			}
			catch( InstapageApiCallException $e )
			{
			}
		}

		return $cached_response_object->response;
	}

	public function getErrorMessageHTML()
	{
		echo '<div id="message" class="error">';
		echo '<p><strong>' . $this->message . '</strong></p></div>';
	}

	public function getMessageHTML()
	{
		echo '<div id="message" class="updated">';
		echo '<p><strong>' . $this->message . '</strong></p></div>';
	}

	public static function getFrontInstaPage()
	{
		$v = get_option('instapage_front_page_id', false);
		return ($v == '') ? false : $v;
	}

	public function getInstaPageById( $page_id, $cookies = false )
	{
		$url = self::endpoint .'/server/view-by-id/'. $page_id;

		if( $cookies )
		{
			$cookies_we_need = array( "instapage-variant-{$page_id}" );

			foreach( $cookies as $key => $value )
			{
				if( !in_array( $key, $cookies_we_need ) )
				{
					unset( $cookies[ $key ] );
				}
			}
		}

		$response = wp_remote_post(
			$url,
			array(
				'method' => 'POST',
				'timeout' => 70,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array
				(
					'useragent' => $_SERVER[ 'HTTP_USER_AGENT' ],
					'ip' => $_SERVER['REMOTE_ADDR'],
					'cookies' => $cookies,
					'custom' => $_GET[ 'custom' ] ? $_GET[ 'custom' ] : null,
					'variant' => $_GET[ 'variant' ] ? $_GET[ 'variant' ] : null,
					'tags' => $_GET
				),
				'cookies' => array()
			)
		);

		if (is_wp_error($response) || $response['response']['code'] == '500')
		{
			throw new InstapageApiCallException( $response->get_error_message() );
		}

		if( $response[ 'headers' ][ 'instapage-variant' ] )
		{
			setcookie( "instapage-variant-{$page_id}", $response[ 'headers' ][ 'instapage-variant' ], strtotime( '+12 month' ) );
		}

		return $response;
	}

	public function getMyPage( $id )
	{
		try
		{
			if( $this->loadMyPages() ) foreach( $this->loadMyPages() as $page )
			{
				if( $page->id == $id )
				{
					return $page;
				}
			}
		}
		catch( Exception $e )
		{
			echo $e->getMessage();
		}

		return false;
	}

	public function getMyPosts()
	{
		global $wpdb;

		$sql = "SELECT {$wpdb->posts}.ID, {$wpdb->postmeta}.meta_key, {$wpdb->postmeta}.meta_value FROM {$wpdb->posts} INNER JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id) WHERE ({$wpdb->posts}.post_type = %s) AND ({$wpdb->posts}.post_status = 'publish') AND ({$wpdb->postmeta}.meta_key IN ('instapage_my_selected_page', 'instapage_name', 'instapage_my_selected_page', 'instapage_slug'))";

		$rows = $wpdb->get_results($wpdb->prepare($sql, 'instapage_post'));
		$posts = array();
		foreach ($rows as $k => $row) {
			if (!array_key_exists($row->ID, $posts)) {
				$posts[$row->ID] = array();
			}
			$posts[$row->ID][$row->meta_key] = $row->meta_value;
		}
		return $posts;
	}

	public function getPageById($post_id)
	{
		$res = get_post($post_id);
		if (empty($res)) return false;
		$url = get_post_meta($post_id, 'instapage_url', true);
		$slug = get_post_meta($post_id, 'instapage_slug', true);
		$id = get_post_meta($post_id, 'instapage_my_selected_page', true);
		$res->lp_id = $id;
		$res->lp_url = $url;
		$res->slug = $slug;
		return $res;
	}

	public function getPageHtml( $id )
	{
		$cache = get_site_transient('instapage_page_html_cache_' . $id);

		if ( $cache && !is_user_logged_in() )
		{
			return $this->fixHtmlHead($cache);
		}

		try
		{
			$page = $this->getInstaPageById( $id, $_COOKIE );
		}
		catch( InstapageApiCallException $e )
		{
			return $this->formatError( "Can't reach instapage server! ". $e->getMessage() );
		}

		if ($page === false)
		{
			return $this->formatError( "instapage says: Page Not found!");
		}

		return $this->fixHtmlHead( $page['body'] );
	}

	public static function getPluginHash()
	{
		return get_option( 'instapage.plugin_hash' );
	}

	public static function getRedirectMethod()
	{
		$v = get_option('instapage_redirect_method', 'http');
		return ($v == '') ? 'http' : $v;
	}

	public function getUrlVersion()
	{
		return '?url-version=' . $this->pluginGet('Version');
	}

	public static function getUserId()
	{
		return get_option( 'instapage.user_id' );
	}

	public function instapageApiCall( $service, $data = null )
	{
		$url = self::endpoint .'/ajax/services/' . $service . '/';

		$current_ver = $this->pluginGet( 'Version' );

		$response = wp_remote_post(
			$url,
			array(
				'method' => 'POST',
				'timeout' => 70,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array(
					'service-type' => 'Wordpress',
					'service' => $_SERVER[ 'SERVER_NAME' ],
					'version' => $current_ver,
					'user_id' => $instapage_user_id,
					'data' => $data
				),
				'cookies' => array()
			)
		);

		if (is_wp_error($response) || $response['response']['code'] == '500')
		{
			throw new InstapageApiCallException( $response->get_error_message() );
		}

		$res = json_decode( $response['body'], true );

		if ( !is_array( $res ) && !is_object( $res ) )
		{
			throw new InstapageApiCallException( 'instapage Services returned empty response.' );
		}

		$data = new stdClass();

		foreach ($res as $key => $val)
		{
			$data->$key = $val;
		}

		if ( $service == 'update-check' )
		{
			set_site_transient( 'instapage_latest_version', $data, 60 * 60 * 12 );
		}

		return $data;
	}

	public function instapagePostRegister() {
		$this->silentUpdateCheck();
		$labels = array(
			'name' => _x('instapage', 'Post type general name'),
			'singular_name' => _x('instapage','Post type singular name'),
			'add_new' => _x('Add New', 'instapage'),
			'add_new_item' => __('Add New instapage'),
			'edit_item' => __('Edit instapage'),
			'new_item' => __('New instapage'),
			'view_item' => __('View instapage'),
			'search_items' => __('Search instapage'),
			'not_found' => __('Nothing found'),
			'not_found_in_trash' => __('Nothing found in Trash'),
			'parent_item_colon' => ''
		);
		$args = array(
			'labels' => $labels,
			'description' => 'Allows you to have instapage on your WordPress site.',
			'public' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'menu_icon' => 'http://instapage-blog.s3.amazonaws.com/instapage-logo-black-16x16.png',
			'capability_type' => 'page',
			'menu_position' => null,
			'rewrite' => false,
			'can_export' => false,
			'hierarchical' => false,
			'has_archive' => false,
			'supports' => array('instapage_my_selected_page', 'instapage_slug', 'instapage_name', 'instapage_url'),
			'register_meta_box_cb' => array(&$this, 'removeMetaBoxes')
		);

		register_post_type('instapage_post', $args);
	}

	public function is404Page($id)
	{
		$not_found = $this->get404InstaPage();
		return ($id == $not_found && $not_found !== false);
	}

	public function isFrontPage($id)
	{
		$front = $this->getFrontInstaPage();
		return ($id == $front && $front !== false);
	}

	public static function isPageModeActive($new_edit = null)
	{
		global $pagenow;
		// make sure we are on the backend
		if (!is_admin()) return false;

		if ($new_edit == "edit") {
			return in_array($pagenow, array('post.php',));
		} elseif ($new_edit == "new") { // check for new post page
			return in_array($pagenow, array('post-new.php'));
		} else { // check for either new or edit
			return in_array($pagenow, array('post.php', 'post-new.php'));
		}
	}

	public function isServicesRequest()
	{
		// get current url
		$current = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		// calculate the path
		$part = substr($current, strlen(site_url()));

		if( strpos( $part, 'instapage-proxy-services' ) === 1 )
		{
			return true;
		}
	}

	public function loadCustomWpAdminStyle() {
		$v = $this->getUrlVersion();
		$admin_url = instapage_ABS_URL . 'admin/';
		wp_register_script('instapage_jquery', $admin_url . 'js/jquery_instapage.js' . $v);
		wp_register_script('instapage_admin', $admin_url . 'js/admin_instapage.js' . $v, array('instapage_jquery', 'instapage_bootstrap'));
		wp_enqueue_script('instapage_jquery');
		wp_enqueue_script('instapage_bootstrap');
		wp_enqueue_script('instapage_admin');
		wp_register_style('instapage_admin', $admin_url . 'css/admin_instapage.css' . $v);
		wp_enqueue_style('instapage_bootstrap');
		wp_enqueue_style('instapage_admin');
	}

	public function loadMyPages()
	{
		if ( $this->my_pages === false )
		{
			$response = $this->instapageApiCall( 'my-pages',
				array
				(
					'user_id' => get_option( 'instapage.user_id' ),
					'plugin_hash' => get_option( 'instapage.plugin_hash' )
				)
			);

			if( !$response )
			{
				throw new Exception( 'Error connecting to instapage' );
			}

			$pages = array();
			$pages_array_response = $response->data[ 'pages' ];

			if( $pages_array_response ) foreach( $pages_array_response as $page_array )
			{
				$page = new stdClass();

				foreach( $page_array as $key => $value )
				{
					$page->$key = $value;
				}

				$pages[] = $page;
			}

			$this->my_pages = $pages;
		}

		return $this->my_pages;
	}

	public function noCache()
	{
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('X-Random-Header: ' . (rand()+time()));
	}

	public function parseRequest()
	{
		$posts = $this->getAllPosts();

		if (!is_array($posts))
		{
			return false;
		}

		// get current url
		$current = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// calculate the path
		$part = substr($current, strlen(site_url()));

		if ($part[0] == '/')
		{
			$part = substr($part, 1);
		}

		// strip parameters
		$real = explode('?', $part);
		$tokens = $real[ 0 ];

		if (array_key_exists( $tokens, $posts ) )
		{
			if ($tokens == '') return false;
			return $posts[ $tokens ];
		}

		return false;
	}

	protected function pluginGet( $variable )
	{
		if( $this->plugin_details === false )
		{
			if (!function_exists('get_plugins'))
			{
				require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}

			$data = get_plugins('/instapage');
			$this->plugin_details = $data[ 'instapage.php' ];
		}

		return $this->plugin_details[ $variable ];
	}

	public function populateColumns($column)
	{
		if( !self::getUserId() )
		{
			echo '<script type="text/javascript">window.location="'. admin_url( 'options-general.php?page='. plugin_basename( __FILE__ ) ) .'";</script>';
		}

		$path = esc_html(get_post_meta(get_the_ID(), 'instapage_slug', true));

		$isFrontPage = $this->isFrontPage(get_the_ID());
		$is_not_found_page = $this->is404Page(get_the_ID());
		if ('instapage_post_type' == $column) {
			if ($isFrontPage) {
				echo '<strong style="color:#003399">Home Page</strong>';
			} elseif ($is_not_found_page) {
				echo '<strong style="color:#F89406">404 Page</strong>';
			} else {
				echo 'Normal';
			}
		}
		if ('instapage_post_path' == $column) {
			if ($isFrontPage) {
				$url = site_url() . '/';
				echo '<a href="' . $url .'" target="_blank">' . $url . '</a>';
			} elseif ($is_not_found_page) {
				$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$randomString = '';
				$length = 10;
				for ($i = 0; $i < $length; $i++) {
					$randomString .= $characters[rand(0, strlen($characters) - 1)];
				}
				$url = site_url() . '/random-url-' . $randomString;
				echo '<a href="' . $url .'" target="_blank">' . $url . '</a>';
			} else {
				if ($path == '') {
					echo '<strong style="color:#ff3300">Missing path!</strong> <i>Page is not active</i>';
				} else {
					$url = site_url() . '/' . $path;
					echo '<a href="' . $url .'" target="_blank">' . $url . '</a>';
				}
			}
		}
		if ('instapage_post_name' == $column) {
			$url = get_edit_post_link(get_the_ID());
			$p_name = get_post_meta(get_the_ID(), 'instapage_name', true);
			echo '<strong><a href="' . $url .'">' .  $p_name . '</a></strong>';
		}
	}

	public function processProxyServices()
	{
		ob_start();
		$url = $_GET[ 'url' ];

		$url = self::endpoint . $url;

		$response = wp_remote_post(
			$url,
			array(
				'method' => $_POST ? 'POST' : 'GET',
				'timeout' => 70,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $_POST,
				'cookies' => array()
			)
		);

		ob_end_clean();

		header( 'Content-Type: text/json; charset=UTF-8' );

		echo trim( $response[ 'body' ] );
		exit;
	}

	public function registerAutoUpdate()
	{
		// plugin update information
		add_filter('plugins_api', array(&$this, 'updateInformation'), 10, 3);

		// exclude from official updates
		add_filter('http_request_args', array(&$this, 'excludeUpdates'), 5, 2);

		// check for update twice a day (same schedule as normal WP plugins)
		add_action('lp_check_event', array(&$this, 'checkForUpdates'));
		add_filter("transient_update_plugins", array(&$this, 'checkForPluginUpdate'));
		add_filter("site_transient_update_plugins", array(&$this, 'checkForPluginUpdate'));

		// check and schedule next update
		if (!wp_next_scheduled('lp_check_event')) {
			wp_schedule_event(current_time('timestamp'), 'twicedaily', 'lp_check_event');
		}
		// remove cron task upon deactivation
		register_deactivation_hook(__FILE__, array(&$this, 'checkForDeactivation'));
	}


	public function removeMetaBoxes()
	{
		global $wp_meta_boxes;

		foreach ($wp_meta_boxes as $k => $v) {
			foreach ($wp_meta_boxes[$k] as $j => $u) {
				foreach ($wp_meta_boxes[$k][$j] as $l => $y) {
					foreach ($wp_meta_boxes[$k][$j][$l] as $m => $y) {
						if ($m != 'instapage_meta_box') {
							unset($wp_meta_boxes[$k][$j][$l][$m]);
						}
					}
				}
			}
		}
		return;
	}

	public function removeQuickEdit($actions)
	{
		global $post;

		if ($post->post_type == 'instapage_post' )
		{
			unset($actions['inline hide-if-no-js']);
		}

		return $actions;
	}

	public static function setFrontInstaPage($id) {
		update_option('instapage_front_page_id', $id);
	}

	public static function set404InstaPage($id) {
		update_option('instapage_404_page_id', $id);
	}

	public static function setRedirectMethod($val) {
		update_option('instapage_redirect_method', $val);
	}

	// The Callback
	public function showCustomMetaBox()
	{
		global $post;

		if( !self::getUserId() )
		{
			$redirect = new InstapageView( dirname( __FILE__ ) .'/templates/instapage/redirect-to-settings.php' );
			$redirect->plugin_file = plugin_basename(__FILE__);
			echo $redirect->fetch();
			return;
		}

		// Field Array
		$field = array(
			'label' => 'My Page',
			'desc'  => 'Select from your pages.',
			'id'    => 'instapage_my_selected_page',
			'type'  => 'select',
			'options' => array()
		);

		try
		{
			$pages = $this->loadMyPages();
		}
		catch( Exception $e )
		{
			echo $e->getMessage();
			return;
		}

		if ( !$pages )
		{
			echo 'No pages pushed to your wordpress. Please go to your <a href="http://app.instapage.com/dashboard" target="_blank">instapage</a> and push some pages.';
			return;
		}

		if ( $pages === false )
		{
			$redirect = new InstapageView( dirname( __FILE__ ) .'/templates/instapage/redirect-to-settings.php' );
			$redirect->plugin_file = plugin_basename(__FILE__);
			echo $redirect->fetch();

			echo 'Error while loading your pages!';
			return;
		}
		foreach( $pages as $key => $page )
		{
			$field['options'][ $page->id ] = array(
				'label' => $page->title,
				'value' => $page->id
			);
		}

		$isFrontPage = $this->isFrontPage($post->ID);
		$is_not_found_page = $this->is404Page(get_the_ID());
		$meta = get_post_meta($post->ID, 'instapage_my_selected_page', true);
		$meta_slug = get_post_meta($post->ID, 'instapage_slug', true);
		$missing_slug = ($this->isPageModeActive('edit') && $meta_slug == '' && !$isFrontPage);

		$delete_link = get_delete_post_link($post->ID);

		$instapage_post_type = null;
		$redirect_method = 'http';
		if ($isFrontPage)
		{
			$instapage_post_type = 'home';
		}
		elseif( $is_not_found_page )
		{
			$instapage_post_type = '404';
		}

		$form = new InstapageView( dirname( __FILE__ ) .'/templates/instapage/edit.php' );
		$form->instapage_post_type = $instapage_post_type;
		$form->user_id = self::getUserId();
		$form->field = $field;
		$form->meta = $meta;
		$form->meta_slug = $meta_slug;
		$form->missing_slug = $missing_slug;
		$form->redirect_method = $redirect_method;
		$form->delete_link = $delete_link;
		$form->is_page_active_mode = $this->isPageModeActive('edit');
		$form->plugin_file = plugin_basename(__FILE__);
		echo $form->fetch();
	}

	public function showSettingsPage()
	{
		$user_id = get_option( 'instapage.user_id' );
		$form = new InstapageView( dirname( __FILE__ ) .'/templates/instapage/settings.php' );
		$form->plugin_file = plugin_basename(__FILE__);
		$form->user_id = $user_id;

		if( $_POST && !$user_id )
		{
			try
			{
				$response = $this->instapageApiCall
				(
					'user-login',
					array
					(
						'email' => base64_encode( trim( $_POST[ 'email' ] ) ),
						'password' => base64_encode( trim( $_POST[ 'password' ] ) )
					)
				);
			}
			catch( InstapageApiCallException $e )
			{
				$form->error = $e->getMessage();
			}

			if( $response->error )
			{
				$form->error = $response->error_message;
			}

			if( $response->success )
			{
				add_option( 'instapage.user_id', false );
				add_option( 'instapage.plugin_hash', false );
				update_option( 'instapage.user_id', $response->data[ 'user_id' ] );
				update_option( 'instapage.plugin_hash', $response->data[ 'plugin_hash' ] );
				$user_id = $form->user_id = $response->data[ 'user_id' ];
			}
		}

		if( $_POST[ 'action' ] == 'disconnect' )
		{
			update_option( 'instapage.user_id', false );
			update_option( 'instapage.plugin_hash', false );
			$form->user_id = null;
		}

		if( $user_id )
		{
			$url = self::endpoint ."/ajax/services/get_user/?id=". $user_id ."&plugin_hash=". get_option( 'instapage.plugin_hash' );

			try
			{
				$response = $this->instapageApiCall
				(
					'get-user',
					array
					(
						'user_id' => $user_id,
						'plugin_hash' => get_option( 'instapage.plugin_hash' )
					)
				);

				$form->user = $response->data[ 'user' ];
			}
			catch( InstapageApiCallException $e )
			{
				$form->error = $e->getMessage();
			}
		}

		echo $form->fetch();
	}

	public function saveCustomMeta($post_id, $post)
	{
		if (!isset( $_POST['instapage_meta_box_nonce'] ) || !wp_verify_nonce($_POST['instapage_meta_box_nonce'], basename(__FILE__)))
		{
			// return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return $post_id;
		}

		if ( $post->post_type != 'instapage_post' )
		{
			return $post_id;
		}

		$old = get_post_meta( $post_id, 'instapage_my_selected_page', true );
		$new = $_POST['instapage_my_selected_page'];

		$front_page = false;
		$not_found_page = false;

		switch ($_POST['post-type']) {
			case '':
			break;
			case 'home':
				$front_page = true;
			break;
			case '404':
				$not_found_page = true;
			break;
			break;
		}

		// HOME PAGE
		$old_front = $this->getFrontInstaPage();

		if ($front_page)
		{
			$this->setFrontInstaPage($post_id);
		}
		elseif ( $old_front == $post_id )
		{
			$this->setFrontInstaPage( false );
		}

		// 404 PAGE
		$old_nf = $this->get404InstaPage();
		if ($not_found_page) {
			$this->set404InstaPage($post_id);
		}
		elseif ( $old_nf == $post_id )
		{
			$this->set404InstaPage(false);
		}

		if ($new && $new != $old)
		{
			update_post_meta($post_id, 'instapage_my_selected_page', $new);
			$page = $this->getMyPage( $new );

			update_post_meta($post_id, 'instapage_name', $page->title );
		}
		elseif ('' == $new && $old)
		{
			delete_post_meta($post_id, 'instapage_my_selected_page', $old);
		}

		// Custom URL
		$old = get_post_meta($post_id, 'instapage_slug', true);
		$new = trim( strip_tags( $_POST['instapage_slug'] ) );

		if ($new && $new != $old)
		{
			update_post_meta($post_id, 'instapage_slug', $new);
		}
		elseif ('' == $new && $old)
		{
			delete_post_meta($post_id, 'instapage_slug', $old);
		}

		delete_site_transient('instapage_page_html_cache_' . $new);

		try
		{
			$this->updatePageDetails
			(
				array
				(
					'user_id' => get_option( 'instapage.user_id' ),
					'plugin_hash' => get_option( 'instapage.plugin_hash' ),
					'page_id' => $_POST[ 'instapage_my_selected_page' ],
					'url' => str_replace( 'http://', '', str_replace( 'https', 'http', get_option( 'siteurl' ) . '/'. $_POST[ 'instapage_slug' ] ) ),
					'secure' => is_ssl()
				)
			);
		}
		catch( InstapageApiCallException $e )
		{
		}
	}

	public function showMessage($not_error, $message)
	{
		$this->message = $message;

		if ($not_error)
		{
			add_action('admin_notices', array(&$this, 'getMessageHTML'));
		} else
		{
			add_action('admin_notices', array(&$this, 'getErrorMessageHTML'));
		}
	}

	public function silentUpdateCheck()
	{
		return;
		$response = $this->checkForUpdates(true);

		if (!$response)
		{
			$this->showMessage(false, 'Error while checking for update. Can\'t reach instapage server. Please check your connection.');
			return;
		}

		if (isset($response->result) && $response->result == 'ko')
		{
			$this->showMessage(false, $response->message);
			return;
		}

		$vew_version = $response->data[ 'current-version' ];
		$url = $response->data[ 'download-url' ];
		$current_version = $this->pluginGet( 'Version' );

		if ($current_version == $vew_version || version_compare( $current_version, $vew_version, '>' ) )
		{
			return;
		}

		$plugin_file = 'instapage/instapage.php';
		$upgrade_url = wp_nonce_url('update.php?action=upgrade-plugin&amp;plugin=' . urlencode($plugin_file), 'upgrade-plugin_' . $plugin_file);
		$message = 'There is a new version of instapage plugin available! ( ' . $vew_version . ' )<br>You can <a href="' . $upgrade_url . '">update</a> to the latest version automatically or <a href="' . $url . '">download</a> the update and install it manually.';
		$this->showMessage(true, $message);
	}

	public function updateInformation( $false, $action, $args )
	{
		// Check if this plugins API is about this plugin
		if ($args->slug != 'instapage')
		{
			return $false;
		}

		$response = $this->getCachedService( 'update-check', $this->cached_service_lifetime );

		if (!$response) return $false;

		$info_response = new stdClass();
		$info_response->slug = 'instapage';
		$info_response->plugin_name = 'instapage';
		$info_response->sections = $response->data[ 'sections' ];
		$info_response->version = $response->data[ 'current-version' ];
		$info_response->author = $response->data[ 'author' ];
		$info_response->tested = $response->data[ 'tested' ];
		$info_response->homepage = $response->data[ 'homepage' ];
		$info_response->downloaded = $response->data[ 'downloaded' ];

		return $info_response;
	}

	public function updatePageDetails( $details )
	{
		$this->instapageApiCall( 'update-page', $details );
	}

	// Validate the Data
	public function validateCustomMeta( $post_id, $post )
	{

		if ( !isset($_POST['instapage_meta_box_nonce']) || !wp_verify_nonce($_POST['instapage_meta_box_nonce'], basename(__FILE__) ) )
		{
			return $post_id;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		{
			return $post_id;
		}

		if ($post->post_type != 'instapage_post')
		{
			return $post_id;
		}


		$slug = get_post_meta($post_id, 'instapage_slug');

		$isFrontPage = $this->isFrontPage( $post_id );

		$invalid_url = empty( $slug ) && !$isFrontPage;

		// on attempting to publish - check for completion and intervene if necessary
		if ((isset($_POST['publish']) || isset($_POST['save'])) && $_POST['post_status'] == 'publish') {
			// don't allow publishing while any of these are incomplete
			if ($invalid_url) {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array('post_status' => 'pending'), array('ID' => $pid) );
			}
		}
	}

}}

// Instance
$instapage_instance = new InstaPage();
