<?php
/*
  Plugin Name: WishList Login 2.0
  Plugin URI: http://wishlistproducts.com/support-options
  Description: WishList Login 2.0 combines the functionality of WishList Login, WishList Post Login, and WishList Social Login. Now, you can display socially-enabled logins throughout your membership site... giving your members several different ways to easily login to your site.
  Version: 2.0.121
  Author: WishList Products
  Author URI: http://wishlistproducts.com/support-options/
  Text Domain: wishlist-login2
  SVN: 121
  License: GPLv2
 */

  require_once(dirname(__FILE__) . '/core/Class.php');
  require_once(dirname(__FILE__) . '/core/DB.php');
  require_once(dirname(__FILE__) . '/core/PluginMethods.php');
  require_once(dirname(__FILE__) . '/core/WishListLogin2Debug.php');
  require_once(dirname(__FILE__) . '/core/SocialLoginHandler2.php');
  require_once(dirname(__FILE__) . '/core/TinyMCEPlugin.php');
// -----------------------------------------
// Our plugin class
  if (!class_exists('WishListLogin2')) {

	class WishListLogin2 extends WishListLogin2_PluginMethods {

		var $handlers = array(
			'facebook'  => 'FacebookLoginHandler2',
			'twitter'   => 'TwitterLoginHandler2',
			'linkedin'  => 'LinkedInLoginHandler2',
			'google'    => 'GoogleLoginHandler2',
		);
		var $handlers_prettyname = array(
			'facebook'  => 'Facebook',
			'twitter'   => 'Twitter',
			'linkedin'  => 'LinkedIn',
			'google'    => 'Google',
		);

		var $layouts = array(
			'full' => array('text' => 'Full Layout', 'img' => array('url' =>'', 'height' => '', 'width' => '')),
			'compact' => array('text' => 'Compact Layout', 'img' => array('url' =>'', 'height' => '', 'width' => '')),
			'horizontal' => array('text' => 'Horizontal Layout', 'img' => array('url' =>'', 'height' => '', 'width' => ''))
		);

		var $skins = array(
			'black' => array('text' => 'Black', 'hex' => '262626'),
			'blue' => array('text' => 'Blue', 'hex' => '387edc'),
			'teal' => array('text' => 'Teal', 'hex' => '00b2cb'),
			'light_green' => array('text' => 'Light Green', 'hex' => '9ad80b'),
			'green' => array('text' => 'Green', 'hex' => '199200'),
			'orange' => array('text' => 'Orange', 'hex' => 'fc7f01'),
			'purple' => array('text' => 'Purple', 'hex' => '8b2bb1'),
			'pink' => array('text' => 'Pink', 'hex' => 'd910d8'),
			'red' => array('text' => 'Red', 'hex' => 'c90000'),
			'yellow' => array('text' => 'Yellow', 'hex' => 'efec00')
		);
		/**
		 * Constructor
		 */
		function WishListLogin2() {
			$x = func_get_args();
			$this->Constructor(__FILE__, $x[0], $x[1], $x[2], $x[3], $x[4],$x[5]);

			$this->layouts['full']['img']['url'] = plugins_url('/images/layout-full.png', __FILE__);
			$this->layouts['full']['img']['height'] = 158;
			$this->layouts['full']['img']['width'] = 114;
			$this->layouts['compact']['img']['url'] = plugins_url('/images/layout-compact.png', __FILE__);
			$this->layouts['compact']['img']['height'] = 111;
			$this->layouts['compact']['img']['width'] = 121;
			$this->layouts['horizontal']['img']['url'] = plugins_url('/images/layout-horizontal.png', __FILE__);
			$this->layouts['horizontal']['img']['height'] = 53;
			$this->layouts['horizontal']['img']['width'] = 277;
		}

		/**
		 * Plugin Activation Call
		 */
		function Activate() {
			/*
			 * Call Core Activation
			 */
			$this->CoreActivate();
		}

		/**
		 * Plugin Deactivation
		 */
		function Deactivate() {
			/*
			 * Call Core Deactivation
			 */
			$this->CoreDeactivate();
		}

		/**
		 * WP Admin Header section
		 */
		function AdminHead() {
			global $wp_version;

			if (!isset($_GET['page']))
				return;

			if ($_GET['page'] == $this->MenuID) {
				if ( 3.5 <= $wp_version ) {
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wp-color-picker' );
				} else {
					wp_enqueue_style( 'farbtastic' );
					wp_enqueue_script( 'farbtastic' );
				}

				wp_enqueue_script('thickbox');
				wp_enqueue_script('flowplayer', 'http://wishlist-products.s3.amazonaws.com/videos/flowplayer-3.2.6.min.js');
				wp_enqueue_script('wl-admin-js', plugins_url('/js/wllogin.admin.js', __FILE__), array('jquery','flowplayer'));

				if ( $wp_version >= 3.8 )
					wp_enqueue_style( 'wl-admin-main', plugins_url('/css/admin_main.css', __FILE__) , '', $this->Version);
				else
					wp_enqueue_style( 'wl-admin-main', plugins_url('/css/admin_main_3_7_below.css', __FILE__), '', $this->Version);
				
				wp_enqueue_style( 'wl-admin-more', plugins_url('/css/admin_more.css', __FILE__) );
				wp_enqueue_style( 'wl-admin-tooltips', plugins_url('/css/jquery.tooltip.css', __FILE__) );
            	wp_enqueue_style('thickbox');
			}
		}

		/**
		 * Get skins
		 */
		function Get_Skins() {
			$skins = apply_filters('wl_login_skins', $this->skins);

			return $skins;
		}

		/**
		 * Get layouts
		 */
		function Get_Layouts() {
			$layouts = apply_filters('wl_login_layouts', $this->layouts);

			return $layouts;
		}

		function load_handler($h) {
			$name = sprintf(dirname(__FILE__) . "/core/%s.php", $h);
			require_once($name);
		}
		/**
		 * WP Init Hook
		 */
		function Init() {
			$this->migrate();

			if(!isset($_GET['wllogin2'])) {
				return;
			}

			$login_handler = null;
			$handlers = $this->handlers;

			$opts = $this->GetOption('wllogin2settings');

			$h = $_GET['handler'];
			if(!empty($h) && isset($handlers[$h])) {
				$cname = $handlers[$h];
				$this->load_handler($cname);
				$params = $opts[$h];
				$params['redirect_to'] = $_REQUEST['redirect_to'];
				$login_handler = new $cname($this, $params);
				$login_handler->set_raw_params($opts);
			}

			$action = $_GET['loginaction'];
			switch($action) {
				case 'send':
				$login_handler->send_login_request();
				break;
				case 'login':
				setcookie("wlsocloginlast", $h, time()+60*60*24*30, '/');
				$login_handler->handle_callback();
				break;
			}
			die();
		}

		public function connect_login($login, $user) {
			$handlers = $this->handlers;
			$connect_data = $_COOKIE['wllogin2connect'];

			$opts = $this->GetOption('wllogin2settings');
			list($h, $foreign_id) = explode(',', $connect_data);
			if(!empty($h) && isset($handlers[$h])) {
				$cname = $handlers[$h];
				$this->load_handler($cname);
				$params = $opts[$h];
				$login_handler = new $cname($this, $params);
				$login_handler->connect_login($foreign_id, $user->ID);
			}

		}
		public function login_message() {
			if(isset($_GET['wllogin2connect'])) {
				$services = array(
					'facebook' => 'Facebook',
					'twitter'  => 'Twitter',
					'google'   => 'Google',
					'linkedin' => 'LinkedIn'
					);
				$s = $_GET['handler'];
				if(!isset($services[$s])) {
					return $content;
				}
				$str = sprintf(__("<p class='message'>It looks like this is the first time you are logging in with your %s account, please login with your existing account to continue</p>", 'wishlist-social-login'), $services[$s]);
				echo $str;
			}
		}
		public function postlogin_init() {
			$permalink_struct   = get_option('permalink_structure');
			$suffix             = $this->GetOption('suffix');
			$suffix             = trim($suffix);
			if(empty($suffix)) {
				$suffix = 'login';
			}
			$ru = $_SERVER['REQUEST_URI'];
			if(empty($suffix)) {
				return;
			}

			if(preg_match("/\/$suffix\/?$/", $ru)) {
				$_SERVER['REQUEST_URI'] = preg_replace("/$suffix\/?$/", '', $ru);
				$permalink_has_trailing_slash = preg_match('#\/$#', $permalink_struct);
				//no trailing slash, let's also trim the trailing slash
				//from the request_uri
				if(!$permalink_has_trailing_slash) {
					$_SERVER['REQUEST_URI'] = rtrim($_SERVER['REQUEST_URI'], '/');
				}
				$this->do_login_box = true;
			}
			if(isset($_GET['wlmplsuffix']) && $_GET['wlmplsuffix'] == $suffix) {
				$this->do_login_box = true;
			}
		}
		public function remove_comments($tpl) {
			// we cannot return an empty string. wp will fallback
			// to a default comments template so instead
			// we will give it an empty file
			return dirname(__FILE__).'/comments.php';
		}
		public function show_login() {
			global $post;
			global $current_user;
			if($current_user->ID) {
				return false;
			}

			$show_login = is_single() || is_page();
			if(!$show_login) {
				return false;
			}

			// wishlist-member is not installed/ no point :D
			if (!class_exists('WLMAPI')) {
				return false;
			}

			$wlmapi     = new WLMAPI();
			$protected  = $wlmapi->IsProtected($post->ID);
			$protected = $protected || $this->protected_via_more();

			//ok this is protected
			if($protected && $this->do_login_box) {
				return true;
			}
			return false;
		}
		public function postlogin_protect($tpl) {
			if($this->show_login()) {
				/*
				 * remove succeeding template filters
				 */
				remove_filter('the_content', 'wpautop');
				remove_all_filters('page_template');
				remove_all_filters('single_template');


				global $wp_filter;
				$wp_filter['single_template']   = array(false);
				$wp_filter['page_template']     = array(false);
				add_filter('comments_template', array($this, 'remove_comments'));
			}
			return $tpl;
		}
		public function protected_via_more() {
			global $post;

			// wishlist-member is not installed/ no point :D
			if (!class_exists('WLMAPI')) {
				return false;
			}

			$wlmapi     = new WLMAPI();
			$has_more_tag       =  preg_match('/<!--more(.*?)?-->/', $post->post_content, $matches);
			$protect_after_more = $wlmapi->GetOption('protect_after_more');
			return $has_more_tag && $protect_after_more;
		}
		public function the_content_hook($content) {
			global $post, $more;

			$option = $this->GetOption('wllogin2postloginsettings');
			if ( empty( $option['layout'] ) ) { $option['layout'] = 'horizontal'; }

			$permalink = get_permalink();
			if(!$this->show_login()) {
				return $content;
			}

			$has_more_tag =  preg_match('/<!--more(.*?)?-->/', $post->post_content, $matches);
			if(!$has_more_tag) {
				$content = "";
				$params = $this->prepare_form_settings();
				$params['form_type'] = $option['layout'];
				$params['skin'] = $option['skin'];
                                $content .= '<div class="wl_login_form_postlogin_' . $option['layout'] . '">';
				$content .= $this->render_form($params);
				$content .= '</div>';
                                
				return $content;
			}

			$more = false;
			$content = wpautop(get_the_content("", 0));
			$params = $this->prepare_form_settings();
			$params['form_type'] = $option['layout'];
			$params['skin'] = $option['skin'];
                        $before = do_action('wllogin_postlogin_before');
                        $content .= $before;
			$content .= '<div class="wl_login_form_postlogin_' . $option['layout'] . '">';
			$content .= $this->render_form($params);
			$content .= '</div>';

			return $content;

		}
		public function prepare_form_settings() {
			$opts = $this->GetOption('formsettings');
			$settings['include_facebook'] = $this->GetOption('include_facebook');
			$settings['include_google'] = $this->GetOption('include_google');
			$settings['include_twitter'] = $this->GetOption('include_twitter');
			$settings['include_linkedin'] = $this->GetOption('include_linkedin');
			$settings['skin'] = 'black';
			$settings['enable_socials'] = true;

			if(isset($_GET['loginaction']) && $_GET['loginaction']=='connect') {
				$settings['enable_socials'] = false;
			}

			$settings['form_type'] = 'full';

			return $settings;
		}
		public function render_form($params) {
			$path = dirname(__FILE__).'/resources/login_form.php';
			ob_start();

			//@todo move this out or render so we have more control
			$redirect_to = $this->GetOption('redirect_to');
			if($redirect_to != 'wishlistmember') {
				global $wp;
				$redirect_to = add_query_arg( $wp->query_string, '', home_url( $wp->request ));
				$redirect_to = trailingslashit($redirect_to);
				//get current url
				//credits to http://kovshenin.com/2012/current-url-in-wordpress/
				if(isset($_GET['wlfrom'])) {
					$r = urldecode($_GET['wlfrom']);
					$redirect_to = get_bloginfo( 'wpurl') . $r;
				}
			}

			//note that if you pass a $params['redirect_to']
			//this will override the default redirect
			extract($params, EXTR_OVERWRITE);

			//pre-check the settings
			$s = $this->GetOption('wllogin2settings');
			if(empty($s['facebook']['appid']) || empty($s['facebook']['appsecret'])) {
				$include_facebook = false;
			}
			if(empty($s['twitter']['consumer_key']) || empty($s['twitter']['consumer_secret'])) {
				$include_twitter = false;
			}
			if(empty($s['linkedin']['consumer_key']) || empty($s['linkedin']['consumer_secret'])) {
				$include_linkedin = false;
			}
			if(empty($s['google']['client_id']) || empty($s['google']['client_secret']) || empty($s['google']['redirect_uri'])) {
				$include_google = false;
			}



			include $path;
			$str = ob_get_clean();
			return $str;
		}
		public function create_service_login_uri($service, $redirect_to=null) {
			$q = array(
				'wllogin2'		=> 1,
				'loginaction' 	=> 'send',
				'handler'		=> $service,

			);

			if(!empty($redirect_to)) {
				$q['redirect_to']	=  $redirect_to;
			}

			return get_option('wpurl') . '?' . http_build_query($q);
		}
		public function register_widgets() {
			require_once $this->pluginDir.'/core/WishListLogin2Widget.php';
            register_widget('WishListLogin2Widget');
		}
		/**
		 * Our main popup
		 */
		public function main_popup() {?>
			<?php $option = $this->GetOption('wllogin2popupsettings'); ?>
			<?php if ( empty( $option['layout'] ) ) { $option['layout'] = 'full'; } ?>
			<?php if ( empty( $option['skin'] ) ) { $option['skin'] = 'black'; } ?>
			<div id="wl_login_popup_container">
				<div id="wl_login_popup_inner" class="wl_login_popup_inner_layout_<?php echo $option['layout']; ?>">
					<span class="wl_login_popup_close"><a href="#"><div class="genericon genericon-close"></div></a></span>
					<?php $params = $this->prepare_form_settings(); ?>
					<?php $params['form_type'] = $option['layout'];?>
					<?php $params['skin'] = $option['skin']; ?>
					<?php echo $this->render_form($params); ?>
				</div>
			</div>
		<?php }
		/**
		 * Our floating image
		 */
		public function floater() {?>
			<?php $option = $this->GetOption('wllogin2floatersettings'); ?>
			<?php if ( is_user_logged_in() && $option['hide_if_logged_in']) { return; } ?>
			<?php if ( !$option['display'] ) { return; } ?>
			<?php if ( empty( $option['text'] ) ) { $option['text'] = '+'; } ?>
			<div class="wl_login_floater">
				<?php if(is_user_logged_in()): ?>
					<p class="wl-logout-pop">
					<a style="background-color:<?php echo $option['background_color']; ?>" href="<?php echo wp_logout_url( $redirect );?>">
						<?php _e($option['logout_text']); ?>
					</a>
					</p>
				<?php else: ?>
					<p class="wl-login-pop">
						<a style="background-color:<?php echo $option['background_color']; ?>" href="#">
							<?php _e($option['text']); ?>
						</a>
					</p>

				<?php endif; ?>
			</div>
		<?php }
		public function hijack_menu($objects) {
			/**
			 * If user isn't logged in, we return the link as normal
			 */
			if ( !is_user_logged_in() ) {
				return $objects;
			}
			/**
			 * If they are logged in, we search through the objects for items with the
			 * class wl-login-pop and we change the text and url into a logout link
			 */
			foreach ( $objects as $k=>$object ) {
				if ( in_array( 'wl-login-pop', $object->classes ) ) {
					$objects[$k]->title = 'Logout';
					$objects[$k]->url = wp_logout_url();
					$remove_key = array_search( 'wl-login-pop', $object->classes );
					unset($objects[$k]->classes[$remove_key]);
				}
			}

			return $objects;
		}
		public function add_nav_menu_meta_boxes() {
			add_meta_box(
				'wl_login_nav_link',
				__('WishList Login'),
				array( $this, 'nav_menu_link'),
				'nav-menus',
				'side',
				'low'
			);
		}
		public function nav_menu_link() {?>
			<div id="posttype-wl-login" class="posttypediv">
				<div id="tabs-panel-wishlist-login" class="tabs-panel tabs-panel-active">
					<ul id ="wishlist-login-checklist" class="categorychecklist form-no-clear">
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"> Login/Logout Link
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="Login">
							<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="<?php echo wp_login_url(); ?>">
							<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="wl-login-pop">
						</li>
					</ul>
				</div>
				<p class="button-controls">
					<span class="list-controls">
						<a href="/wordpress/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#posttype-page" class="select-all">Select All</a>
					</span>
					<span class="add-to-menu">
						<input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-wl-login">
						<span class="spinner"></span>
					</span>
				</p>
			</div>
		<?php }
		/**
		 * Load JQuery Libraries
		 */
		function Load_JQuery() {
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-tooltip-wlp-custom', $this->pluginURL . '/js/jquery.tooltip.js', array('jquery'), '1.3');
			wp_enqueue_script('jquery-ui-tooltip-wlp', $this->pluginURL . '/js/jquery.tooltip.wlp.js', array('jquery'));
		}
		public function external_scripts() {
			wp_enqueue_script('wl-login-external', plugins_url('/js/wllogin.external.js', __FILE__), array('jquery'));
			wp_enqueue_style('genericons', plugins_url('/css/genericons.css', __FILE__));
			wp_enqueue_style('wl-login-global', plugins_url('/css/style-global.css', __FILE__));
			wp_enqueue_style('wl-login-full', plugins_url('/css/layouts/style-layout-full.css', __FILE__));
			wp_enqueue_style('wl-login-compact', plugins_url('/css/layouts/style-layout-compact.css', __FILE__));
			wp_enqueue_style('wl-login-horizontal', plugins_url('/css/layouts/style-layout-horizontal.css', __FILE__));
			wp_enqueue_style('wl-login-blue', plugins_url('/css/skins/style-skin-blue.css', __FILE__));
			wp_enqueue_style('wl-login-teal', plugins_url('/css/skins/style-skin-teal.css', __FILE__));
			wp_enqueue_style('wl-login-light_green', plugins_url('/css/skins/style-skin-light_green.css', __FILE__));
			wp_enqueue_style('wl-login-green', plugins_url('/css/skins/style-skin-green.css', __FILE__));
			wp_enqueue_style('wl-login-orange', plugins_url('/css/skins/style-skin-orange.css', __FILE__));
			wp_enqueue_style('wl-login-pink', plugins_url('/css/skins/style-skin-pink.css', __FILE__));
			wp_enqueue_style('wl-login-purple', plugins_url('/css/skins/style-skin-purple.css', __FILE__));
			wp_enqueue_style('wl-login-red', plugins_url('/css/skins/style-skin-red.css', __FILE__));
			wp_enqueue_style('wl-login-yellow', plugins_url('/css/skins/style-skin-yellow.css', __FILE__));

			do_action('wl_enqueue_layout_style');
			do_action('wl_enqueue_skin_style');
		}
		public function Translate() {
			$pd = basename($this->pluginDir) . '/lang';
			load_plugin_textdomain('wishlist-login2', PLUGINDIR . '/' . $pd, $pd);
		}

		//shortcodes
		public function shortcode_button($atts) {
			extract( shortcode_atts( array(
				'skin' => 'black'
			), $atts ) );

			if(is_user_logged_in()) {
				return sprintf('<span class="wl-login-shortcode-login wl-login-shortcode-skin_' . $skin . '"><a href="%s">Logout</a></span>', wp_logout_url(get_bloginfo('url')));
			}
			return '<span class="wl-login-pop wl-login-shortcode-login wl-login-shortcode-skin_' . $skin . '"><a href="">Login</a></span>';
		}

		public function shortcode_full($atts) {
			extract( shortcode_atts( array(
				'skin' => 'black'
			), $atts ) );

			$params = $this->prepare_form_settings();
			$params['skin'] = $skin;
			$params['form_type'] = 'full';
			$output = '<div class="wl_login_form_embed">';
			$output .= $this->render_form($params);
			$output .= '</div>';

			return $output;
		}

		public function shortcode_compact($atts) {
			extract( shortcode_atts( array(
				'skin' => 'black'
			), $atts ) );

			$params = $this->prepare_form_settings();
			$params['skin'] = $skin;
			$params['form_type'] = 'compact';
			$output = '<div class="wl_login_form_embed">';
			$output .= $this->render_form($params);
			$output .= '</div>';

			return $output;
		}

		public function shortcode_horizontal($atts) {
			extract( shortcode_atts( array(
				'skin' => 'black'
			), $atts ) );

			$params = $this->prepare_form_settings();
			$params['skin'] = $skin;
			$params['form_type'] = 'horizontal';
			$output = '<div class="wl_login_form_embed wl_login_form_embed_horizontal">';
			$output .= $this->render_form($params);
			$output .= '</div>';

			return $output;
		}
	}

}

/*
 * initiate our plugin
 */
if (class_exists('WishListLogin2')) {
	global $WishListLogin2Instance;
	$WishListLogin2Instance = &new WishListLogin2(8937, 'WishListLogin2', 'WishListLogin', 'WL Login 2.0', 'wlm_login2', false);
	/* add menus */
	$WishListLogin2Instance->AddMenu('settings', 'Settings', 'settings.php');
}

/*
 * WP hooks
 */
if (isset($WishListLogin2Instance)) {
	/*
	 * bind plugin activation and deactivation hooks
	 */
	register_activation_hook(__FILE__, array(&$WishListLogin2Instance, 'Activate'));
	register_deactivation_hook(__FILE__, array(&$WishListLogin2Instance, 'Deactivate'));

	/*
	 * bind WP admin header hook
	 */
	add_action('admin_enqueue_scripts', array(&$WishListLogin2Instance, 'AdminHead'), 1);

	/*
	 * check if license is OK
	 */

	if ($WishListLogin2Instance->GetOption('LicenseStatus') == '1' && $WishListLogin2Instance->RequireWLM) {
		/*
		 * bind WP init hook
		*/
		add_action('init', array(&$WishListLogin2Instance, 'Init'));
		
		if (isset($_GET['page']) && $_GET['page'] == 'WishListLogin2') {
				add_action('init', array(&$WishListLogin2Instance, 'Load_JQuery'));
			}
			
		add_action('plugins_loaded', array(&$WishListLogin2Instance, 'Translate'));
		add_action('wp_login', array(&$WishListLogin2Instance, 'connect_login'), 8, 2);
		add_action('init', array($WishListLogin2Instance, 'postlogin_init'));
		add_action('wp_footer', array($WishListLogin2Instance, 'main_popup'));
		add_action('wp_footer', array($WishListLogin2Instance, 'floater'));
		add_action('wp_enqueue_scripts', array($WishListLogin2Instance, 'external_scripts'));
		add_action('widgets_init', array(&$WishListLogin2Instance,'register_widgets'));
		add_action('admin_init', array(&$WishListLogin2Instance, 'add_nav_menu_meta_boxes'));
		add_filter('page_template', array($WishListLogin2Instance,'postlogin_protect'), 1);
		add_filter('single_template', array($WishListLogin2Instance,'postlogin_protect'), 1);
		add_filter('the_content', array($WishListLogin2Instance, 'the_content_hook'), 1);
		add_filter('login_message', array(&$WishListLogin2Instance, 'login_message'));
		add_filter('wp_nav_menu_objects', array(&$WishListLogin2Instance, 'hijack_menu'), 10, 2);
		add_shortcode('wllogin2_button', array(&$WishListLogin2Instance,'shortcode_button'));
		add_shortcode('wllogin2_full', array(&$WishListLogin2Instance,'shortcode_full'));
		add_shortcode('wllogin2_compact', array(&$WishListLogin2Instance,'shortcode_compact'));
		add_shortcode('wllogin2_horizontal', array(&$WishListLogin2Instance,'shortcode_horizontal'));


		//support old shortcodes
		add_shortcode('wl_sociallogin_mini', 'wllogin2_shortcode_mini');
  		add_shortcode('wl_sociallogin_com', 'wllogin2_shortcode_com');
  		add_shortcode('wl_sociallogin_exp', 'wllogin2_shortcode_exp');
  		add_shortcode('wl_sociallogin_network', 'wllogin2_shortcode_network');
  	}



}
/*
* Create a template function
*/
function wllogin2_shortcode_network() {
	global $WishListLogin2Instance;
	$network = $WishListLogin2Instance->handlers_prettyname[$_GET['handler']];
	return $network;
}
function wllogin2_shortcode_exp($atts) {
	global $WishListLogin2Instance;
	extract(shortcode_atts(array(
		'include' => array(),
	), $atts));

	$r = array();
	foreach(explode(',', $include) as $i) {
		$r['include_'.$i] = true;
	}

	$settings = $WishListLogin2Instance->prepare_form_settings();
	$settings['include_facebook'] = null;
	$settings['include_twitter'] = null;
	$settings['include_linkedin'] = null;
	$settings['include_google'] = null;
	$settings = array_merge($settings, $r);
	$settings['form_type'] = 'full';
	$settings['redirect_to'] = $_REQUEST['redirect_to'];
	return $WishListLogin2Instance->render_form($settings);
}
function wllogin2_shortcode_com($atts) {
	global $WishListLogin2Instance;
	extract(shortcode_atts(array(
		'include' => array()
	), $atts));

	$r = array();
	foreach(explode(',', $include) as $i) {
		$r['include_'.$i] = true;
	}

	$settings = $WishListLogin2Instance->prepare_form_settings();
	$settings['include_facebook'] = null;
	$settings['include_twitter'] = null;
	$settings['include_linkedin'] = null;
	$settings['include_google'] = null;
	$settings = array_merge($settings, $r);
	$settings['form_type'] = 'compact';
	$settings['redirect_to'] = $_REQUEST['redirect_to'];
	return $WishListLogin2Instance->render_form($settings);
}
function wllogin2_shortcode_mini($atts) {
	global $WishListLogin2Instance;
	extract(shortcode_atts(array(
		'include' => array()
	), $atts));

	$r = array();
	foreach(explode(',', $include) as $i) {
		$r['include_'.$i] = true;
	}

	$settings = $WishListLogin2Instance->prepare_form_settings();
	$settings['include_facebook'] = null;
	$settings['include_twitter'] = null;
	$settings['include_linkedin'] = null;
	$settings['include_google'] = null;
	$settings = array_merge($settings, $r);
	$settings['form_type'] = 'compact';
	$settings['redirect_to'] = $_REQUEST['redirect_to'];
	return $WishListLogin2Instance->render_form($settings);
}

