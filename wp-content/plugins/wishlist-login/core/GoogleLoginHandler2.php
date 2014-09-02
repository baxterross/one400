<?php
set_include_path(get_include_path().PATH_SEPARATOR. dirname(__FILE__).'/../extlib/g/src/');
require_once dirname(__FILE__) .'/../extlib/g/src/Google/Client.php';
require_once dirname(__FILE__) .'/../extlib/g/src/Google/Service/Oauth2.php';

class GoogleLoginHandler2 extends SocialLoginHandler2{
	protected $name = 'google';
	protected $user_svc;
	protected $client;
	public function __construct($wlm, $params = array()) {
		parent::__construct($wlm, $params);
		$this->name = 'google';
		$client = new Google_Client();
		$client->setApplicationName("WishList Social Login");
		// Visit https://code.google.com/apis/console?api=plus to generate your
		//oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
		$client->setClientId($params['client_id']);
		$client->setClientSecret($params['client_secret']);
		$client->setRedirectUri($params['redirect_uri']);
		//$client->setDeveloperKey($params['developer_key']);

		$client->setScopes(array('email', 'profile'));

		$this->client = $client;
		$this->user_svc = new Google_Service_Oauth2($client);
	}
	public function set_redirect_to($redirect_to) {
		$status = setcookie("redirec_to", $redirect_to, time() + 3600*24, '/');
	}
	public function get_redirect_to() {
		$redirect_to = $_COOKIE['redirect_to'];
		return $redirect_to;
	}

	public function init() {
	}
	public function send_login_request() {
		$client = $this->client;
		$client->setState($this->csrfstring());
		$authUrl = $client->createAuthUrl();
		$this->set_redirect_to($_REQUEST['redirect_to']);
		header('Location: '.$authUrl);
		die();
	}
	public function handle_callback() {


		if($this->get_csrfstring() != $_GET['state']) {
			throw new Exception("Invalid access");
		}

		if($_GET['code']) {
			$this->client->authenticate($_GET['code']);
			$_SESSION['google_oauth2_access_token'] = $this->client->getAccessToken();
		}


		if (isset($_SESSION['google_oauth2_access_token']) && $_SESSION['google_oauth2_access_token']) {
			$this->client->setAccessToken($_SESSION['google_oauth2_access_token']);
		} else {
			$this->send_login_request();
		}

		$user = $this->user_svc->userinfo->get();
		$wp_user = $this->get_wp_user($user->id);

		if(empty($wp_user)) {
			$this->connect($user->id);
		} else {
			$metas = array(
				'first_name'    => $user->given_name,
				'last_name'     => $user->family_name
			);
			$this->update_user_meta($wp_user->ID, $metas);
			wp_set_auth_cookie($wp_user->ID);

			//redirect fix to allow wishlist-member redirect
			$redirect_to = $this->get_redirect_to();
			if(empty($redirect_to)) {
				$redirect_to = 'wishlistmember';
			}
			$_POST['wlm_redirect_to'] = $redirect_to;
			$_POST['log'] = $wp_user->user_login;
			do_action('wp_login', $wp_user->user_login, $wp_user);
		}
	}
}
