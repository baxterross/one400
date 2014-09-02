<?php
if( !class_exists('OAuthStore')) {
	require_once dirname(__FILE__).'/../extlib/oauth/OAuthStore.php';
}

if( !class_exists('OAuthRequester')) {
	require_once dirname(__FILE__).'/../extlib/oauth/OAuthRequester.php';
}
class LinkedInLoginHandler2 extends SocialLoginHandler2{
	protected $name = 'linkedin';
	protected $consumer_key;
	protected $consumer_secret;
	protected $store;


	protected $oauth_host = 'https://www.linkedin.com';
	protected $request_token_uri = '/uas/oauth/requestToken';
	protected $authorize_uri  = '/uas/oauth/authenticate';
	protected $access_token_uri  = '/uas/oauth/accessToken';

	public function __construct($wlm, $params = array()) {
		parent::__construct($wlm, $params);
		$this->name = 'linkedin';
		$this->consumer_key = $params['consumer_key'];
		$this->consumer_secret = $params['consumer_secret'];

		if(!empty($this->consumer_key)
				&& !empty($this->consumer_secret)) {

			$opts = array(
				'consumer_key'      => $this->consumer_key,
				'consumer_secret'   => $this->consumer_secret,
				'server_uri'        => $this->oauth_host,
				'request_token_uri' => $this->oauth_host.$this->request_token_uri,
				'authorize_uri'     => $this->oauth_host.$this->authorize_uri,
				'access_token_uri'  => $this->oauth_host.$this->access_token_uri
			);
			$store = OAuthStore::instance('Session', $opts);
		}

	}
	public function init() {
	}
	public function send_login_request() {
		if(empty($this->consumer_key) || empty($this->consumer_secret)) {
			wp_die("WL Login2 Error: $this->name consumer_key and consumer secret are required.");
			die();
		}
		$params = array(
			'oauth_callback' => $this->build_callback_query(array('loginaction' => 'login'))
		);
		//requestRequestToken ( $consumer_key, $usr_id, $params = null, $method = 'POST', $options = array(), $curl_options = array() )
		$res  = OAuthRequester::requestRequestToken($this->consumer_key, 0, $params, 'POST', array(), array(CURLOPT_SSL_VERIFYPEER => false) );
		header("Location: ".$res['authorize_uri'].'?oauth_token='.$res['token']);
		die();
	}
	public function handle_callback() {
		if($_REQUEST['state'] != $this->get_csrfstring()) {
			return;
		}
		try {
			$params = array(
				'oauth_token' => $_GET['oauth_token']
			);
			$res = OAuthRequester::requestAccessToken($this->consumer_key, $params['oauth_token'], 0, 'POST', $_GET, array(CURLOPT_SSL_VERIFYPEER => false));


			$req = new OAuthRequester("http://api.linkedin.com/v1/people/~:(id,first-name,last-name)?format=json", 'GET', $_GET);
			$result = $req->doRequest(0, array(CURLOPT_SSL_VERIFYPEER => false));
			$user = json_decode($result['body']);

			if(empty($user)) {
				return;
			}
			$wp_user = $this->get_wp_user($user->id);
			if(empty($wp_user)) {
				$this->connect($user->id);
			} else {
				$metas = array(
					'first_name'    => $user->firstName,
					'last_name'     => $user->lastName
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
		}
	}
}