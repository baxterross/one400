<?php

class FacebookLoginHandler2 extends SocialLoginHandler2{
	private $appid;
	private $appsecret;
	public function __construct($wlm, $params = array()) {
		parent::__construct($wlm, $params);
		$this->name = 'facebook';
		$this->appid = $params['appid'];
		$this->appsecret  = $params['appsecret'];
	}
	public function init() {
	}
	public function send_login_request() {
		$callback = $this->build_callback_query(array('loginaction' => 'login'), false);
		$dialog_url = "https://www.facebook.com/dialog/oauth?client_id="
		. $this->appid . "&redirect_uri=" . urlencode($callback) . "&state="
		. $this->csrfstring();
		header("Location: $dialog_url");
		die();
	}
	public function handle_callback() {
		$state = $this->get_csrfstring();
		if($_REQUEST['state'] == $state) {
			$code = $_REQUEST['code'];
			$callback = $this->build_callback_query(array('loginaction' => 'login'), false);
			$token_url = "https://graph.facebook.com/oauth/access_token?"
			. "client_id=" . $this->appid . "&redirect_uri=" . urlencode($callback)
			. "&client_secret=" . $this->appsecret . "&code=" . $code;
			$response = file_get_contents($token_url);
			$params = null;
			parse_str($response, $params);

			$graph_url = "https://graph.facebook.com/me?access_token="
			. $params['access_token'];

			$user = json_decode(file_get_contents($graph_url));
			if(empty($user)) {
				return;
				//fail
			}

			$wp_user = $this->get_wp_user($user->id);
			if(empty($wp_user)) {
				$this->connect($user->id);
			} else {
				$metas = array(
					'first_name'    => $user->first_name,
					'last_name'     => $user->last_name
				);
				$this->update_user_meta($wp_user->ID, $metas);
				wp_set_auth_cookie($wp_user->ID);

				//redirect fix to allow wishlist-member redirect
				$redirect_to = $_REQUEST['redirect_to'];
				if(empty($redirect_to)) {
					$redirect_to = 'wishlistmember';
				}
				$_POST['wlm_redirect_to'] = $redirect_to;
				$_POST['log'] = $wp_user->user_login;
				do_action('wp_login', $wp_user->user_login, $wp_user);
			}
			die();
		}
	}
}