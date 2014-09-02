<?php
abstract class SocialLoginHandler2 {
	protected $name;
	protected $raw_params;
	protected $wlm;
	protected $params;
	public function __construct($wlm, $params = array()) {
		$this->wlm = $wlm;
		$this->params = $params;
	}
	abstract function init();
	/**
	 * Start the social login process
	 */
	abstract function send_login_request();
	/**
	 * Handle callback processes
	 */
	abstract function handle_callback();
	/**
	 * Begin account connection
	 */
	public function build_callback_query($r, $create_csrftoken=true) {
		$query = array(
			'wllogin2' 		=> 1,
			'handler'		=> $this->name,
			'loginaction'	=> 'send',
		);

		if(!empty($_REQUEST['redirect_to'])) {
			$query['redirect_to'] 	=  $_REQUEST['redirect_to'];
		}

		if($create_csrftoken) {
			$query['state']	= $this->csrfstring();
		}

		$query = array_merge($query, $r);

		$tmp = array();
		foreach($query as $i => $q) {
			$tmp[] = sprintf("%s=%s", $i, urlencode($q));
		}

		$callback = get_option('home').'/index.php?'.implode('&', $tmp);
		return $callback;
	}
	public function connect($foreign_id) {
		$p = $this->wlm->GetOption('connect_page');
		$handler = $this->name;
		$connect_data = "$this->name,$foreign_id";
		$status = setcookie("wllogin2connect", $connect_data, time() + 60*60*24*365*2);

		$q = array(
			'wllogin2connect'	=> 1,
			'loginaction'		=> 'connect',
			'handler'			=> $handler
		);

		if(!empty($_REQUEST['redirect_to'])) {
			$q['redirect_to'] = $_REQUEST['redirect_to'];
		}

		if(!empty($p)) {
			$url = get_permalink($p);
		}
		if(!empty($url)) {
			header("Location: $url?".http_build_query($q));
			die();
		}
		$url = wp_login_url();

		/**
		 * @todo show a message
		 */
		header("Location: $url?".http_build_query($q));
		die();
	}

	public function connect_login($foreign_id,  $user_id) {
		$opt = sprintf('wlsocialconnect-%s-%s', $this->name, $foreign_id);
		$this->wlm->SaveOption($opt, $user_id);
		setcookie("wllogin2connect", $connect_data, time() - 3600);
	}

	public function get_wp_user($foreign_id) {
		$opt = sprintf('wlsocialconnect-%s-%s', $this->name, $foreign_id);
		$user_id = $this->wlm->GetOption($opt);
		if(empty($user_id)) {
			return false;
		}
		return get_user_by('id', $user_id);
	}
	static function get_available_handers() {
		global $WishListMemberSocialLoginClassInstance;
		$icon_url = $WishListMemberSocialLoginClassInstance->pluginURL.'/images/icons/';
		$social_buttons = array(
				'facebook'  => array
					('url' => get_option('home').'/index.php?wlsociallogin=1&handler=facebook&loginaction=send', 'alt' => 'F','icon' => $icon_url.'facebook.png', 'name' => 'facebook'),
				'twitter'  => array
					('url' => get_option('home').'/index.php?wlsociallogin=1&handler=twitter&loginaction=send', 'alt' => 'T','icon' => $icon_url.'twitter.png', 'name' => 'twitter'),
				'google'  => array
					('url' => get_option('home').'/index.php?wlsociallogin=1&handler=google&loginaction=send', 'alt' => 'T','icon' => $icon_url.'google.png', 'name' => 'google'),
				'linkedin'  => array
					('url' => get_option('home').'/index.php?wlsociallogin=1&handler=linkedin&loginaction=send', 'alt' => 'in','icon' => $icon_url.'linkedin.png','name' => 'linkedin'),
			);
		return $social_buttons;
	}

	public function update_user_meta($user_id, $metas) {
		foreach($metas as $key => $v) {
			if(!empty($v)) {
				$meta_val = get_user_meta($user_id, $key, true);
				// only update user meta if it's empty
				if(empty($meta_val)) {
					update_usermeta($user_id, $key, $v);
				}
			}
		}

	}
	public function csrfstring() {
		$csrfstring = md5(uniqid(rand(), TRUE));
		$status = setcookie("wllogin2csrfstring", $csrfstring, time() + 3600*24, '/');
		return $csrfstring;
	}
	public function get_csrfstring() {
		$str = $_COOKIE['wllogin2csrfstring'];
		//remove cookie as soon as we retrieve it
		//error_log('removing cookies');
		//setcookie("wllogin2csrfstring", $csrfstring, time() - 3600*24);
		return $str;
	}
	public function set_raw_params($params) {
		$this->raw_params = $params;
	}
}

?>
