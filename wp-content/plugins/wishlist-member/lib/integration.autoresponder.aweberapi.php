<?php

/*
 * Generic Autoresponder Integration Functions
 * Original Author : Erwin Atuli
 * Version: $Id: integration.autoresponder.aweberapi.php 1672 2013-08-15 03:52:05Z mike $
 */

if (!class_exists('AWeberAPI')) {
	require_once dirname(__FILE__) . '/../extlib/aweber_api/aweber_api.php';
}

//$__classname__ = 'WLM_AUTORESPONDER_AWEBERAPI';
//$__optionname__ = 'aweberapi';
//$__methodname__ = 'AutoResponderAweberAPI';

if (!class_exists('WLM_AUTORESPONDER_AWEBERAPI')) {

	class WLM_AUTORESPONDER_AWEBERAPI {

		private $app_id = '2d8307c8';
		private $api_ver = '1.0';
		private $api_key = '';
		private $api_secret = '';
		private $auth_key = "";
		private $debug = false;
		private $wlm;

		/**
		 *
		 * @param $access_tokens list containing access_token & access_token_secret
		 */
		private $access_tokens = '';

		function set_wlm($wlm) {
			$this->wlm = $wlm;
		}

		function set_auth_key($auth_key) {
			$this->auth_key = $auth_key;
		}

		function get_authkey_url() {
			return sprintf("https://auth.aweber.com/%s/oauth/authorize_app/%s", $this->api_ver, $this->app_id);
		}

		/*
		 * Retreives current access tokens
		 * returns false if the access tokens are not usable
		 */

		function get_access_tokens() {
			$auth_key = $this->auth_key;

			if (empty($auth_key)) {
				return false;
			}
			/**
			 * @todo retrieve current access token from db
			 */
			$options = $this->wlm->GetOption('Autoresponders');
			$access_tokens = $options['aweberapi']['access_tokens'];
			if (empty($access_tokens)) {
				return false;
			}

			//test our access token
			$auth = $this->parse_authkey($auth_key);

			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			list($access_token, $access_token_secret) = $access_tokens;
			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				return $access_tokens;
			} catch (Exception $e) {
				return false;
			}
		}

		function parse_authkey($key) {
			if (empty($key)) {
				return array();
			}
			list($api_key,
					$api_secret,
					$request_token,
					$token_secret,
					$auth_verifier) = explode('|', $key);

			$parsed = array(
				'api_key' => $api_key,
				'api_secret' => $api_secret,
				'request_token' => $request_token,
				'token_secret' => $token_secret,
				'auth_verifier' => $auth_verifier,
			);
			return $parsed;
		}

		/**
		 * Creates access tokens
		 */
		function renew_access_tokens() {
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];
			try {
				$access_tokens = $api->getAccessToken();
				return $access_tokens;
			} catch (Exception $e) {
				return false;
			}
		}

		function unsubscribe($aweber_uid, $list_id) {
			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				throw new Exception("Auth keys have already expired");
			}

			list($access_token, $access_token_secret) = $access_tokens;
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$list = $account->lists->getById($list_id);
				$subs = $list->subscribers;

				$sub = $subs->getById($aweber_uid);
				$res = $sub->delete();
			} catch (Exception $e) {
				error_log("An error occured while deleting: " . $e->getMessage());
				return false;
			}
		}

		function get_lists() {
			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				throw new Exception("Auth keys have already expired");
			}

			list($access_token, $access_token_secret) = $access_tokens;
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$lists = array();
				foreach ($account->lists as $l) {
					$lists[] = $l->attrs();
				}
				return $lists;
			} catch (Exception $e) {
				error_log("An error occured while getting list: " . $e->getMessage());
				return false;
			}
		}

		/**
		 * Returns id of the subscriber
		 */
		function subscribe($list_id, $sub) {
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			if (empty($auth)) {
				throw new Exception("Invalid Auth");
			}

			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				throw new Exception("Auth keys have already expired");
			}

			list($access_token, $access_token_secret) = $access_tokens;
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$list = $account->lists->getById($list_id);
				$subs = $list->subscribers;
				// now create a new subscriber
				$sub = $subs->create($sub);
				$attr = $sub->attrs();
				return $attr['id'];
			} catch (Exception $e) {
				error_log("An error occured while subscribing: " . $e->getMessage());
				return false;
			}
		}

		function AutoResponderAweberAPI($that, $ar, $wpm_id, $email, $unsub = false) {
			$this->set_wlm($that);
			$options = $this->wlm->GetOption('Autoresponders');
			$this->set_auth_key($options['aweberapi']['auth_key']);

			$list_id = $ar['connections'][$wpm_id];
			$autounsub = $ar['autounsub'][$wpm_id] == 'yes';


			if (empty($list_id)) {
				// exit if we don't have anything to sub/unsub to
				return;
			}

			$user = get_user_by_email($that->ARSender['email']);
			if ($unsub === false) {
				$params = array(
					'email' => $that->ARSender['email'],
					'name' => $that->ARSender['name'],
					'ip_address' => $_SERVER['REMOTE_ADDR']
				);

				$res = $this->subscribe($list_id, $params);
				//perist the user_id given by aweber
				if (!empty($res)) {
					add_user_meta($user->ID, 'integration.autoresponder.aweberapi.uid', $res);
				}
			} else {
				if (!$autounsub) {
					return;
				}
				$aweber_uid = get_user_meta($user->ID, 'integration.autoresponder.aweberapi.uid', true);
				$this->unsubscribe($aweber_uid, $list_id);
			}
		}

	}

}