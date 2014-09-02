<?php

if( !class_exists('OAuthStore')) {
	require_once dirname(__FILE__).'/../extlib/oauth/OAuthStore.php';
}

if( !class_exists('OAuthRequester')) {
	require_once dirname(__FILE__).'/../extlib/oauth/OAuthRequester.php';
}

class TwitterLoginHandler2 extends SocialLoginHandler2{
	protected $name = 'twitter';
	protected $consumer_key;
	protected $consumer_secret;
	protected $store;

	protected $oauth_host = 'https://api.twitter.com';
	protected $request_token_uri = '/oauth/request_token';
	protected $authorize_uri  = '/oauth/authenticate';
	protected $access_token_uri  = '/oauth/access_token';

	public function __construct($wlm, $params = array()) {
		parent::__construct($wlm, $params);
		$this->name = 'twitter';
		$this->consumer_key = $params['consumer_key'];
		$this->consumer_secret = $params['consumer_secret'];
	}
	public function init() {
	}
	public function send_login_request() {
		$opts = array(
			'consumer_key'      => $this->consumer_key,
			'consumer_secret'   => $this->consumer_secret,
			'server_uri'        => $this->oauth_host,
			'request_token_uri' => $this->oauth_host.$this->request_token_uri,
			'authorize_uri'     => $this->oauth_host.$this->authorize_uri,
			'access_token_uri'  => $this->oauth_host.$this->access_token_uri
		);
		$store = OAuthStore::instance('Session', $opts);

		$params = array(
			'oauth_callback' => $this->build_callback_query(array('loginaction' => 'login'))
		);
		$res  = OAuthRequester::requestRequestToken($this->consumer_key, 0, $params, 'POST', array(), array(CURLOPT_SSL_VERIFYPEER => false) );
		header("Location: ".$res['authorize_uri'].'?oauth_token='.$res['token']);
		die();
	}
	public function handle_callback() {
		$state = $this->get_csrfstring();
		if($_REQUEST['state'] != $state) {
			return;
		}
		try {
			$opts = array(
				'consumer_key'      => $this->consumer_key,
				'consumer_secret'   => $this->consumer_secret,
				'server_uri'        => $this->oauth_host,
				'request_token_uri' => $this->oauth_host.$this->request_token_uri,
				'authorize_uri'     => $this->oauth_host.$this->authorize_uri,
				'access_token_uri'  => $this->oauth_host.$this->access_token_uri
			);
			$store = OAuthStore::instance('Session', $opts);

			$params = array(
				'oauth_token' => $_GET['oauth_token']
			);
			$res = OAuthRequester::requestAccessToken($this->consumer_key, $params['oauth_token'], 0, 'POST', $_GET, array(CURLOPT_SSL_VERIFYPEER => false));
			$req = new OAuthRequester("{$this->oauth_host}/1.1/account/verify_credentials.json", 'GET', $_GET);
			$result = $req->doRequest(0, array(CURLOPT_SSL_VERIFYPEER => false));

			$user = json_decode($result['body']);
			if(empty($user)) {
				return;
			}

			$wp_user = $this->get_wp_user($user->id);
			if(empty($wp_user)) {
				$this->connect($user->id);
			} else {
				list($first_name, $last_name) = explode(' ', $user->name);
				$metas = array(
					'first_name'    => trim($first_name),
					'last_name'     => trim($last_name)
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
		} catch (Exception $e) {
			var_dump($e->getMessage());
		}
	}
}