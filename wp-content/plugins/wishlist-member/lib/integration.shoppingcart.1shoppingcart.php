<?php

/*
 * 1ShoppingCart Shopping Cart Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.1shoppingcart.php 1986 2014-01-30 01:34:07Z mike $
 */

//information below is now loaded in integration.shoppingcarts.php
//$__classname__ = 'WLM_INTEGRATION_1SHOPPINGCART';
//$__optionname__ = 'scthankyou';
//$__methodname__ = 'OneShoppingCart';

if (!class_exists('WLM_INTEGRATION_1SHOPPINGCART')) {

	class WLM_INTEGRATION_1SHOPPINGCART {

		function OneShoppingCart($that) {
			if (in_array(strtolower(trim(wlm_arrval($_POST,'status'))), array('accepted', 'approved', 'authorized', 'pending'))) { //accept even PENDING, let checkstatus handle it later
//				add_filter('rewrite_rules_array',array(&$that,'RewriteRules'));
//				$GLOBALS['wp_rewrite']->flush_rules();
				if (!trim(wlm_arrval($_POST,'name')))
					$_POST['name'] = 'Firstname Lastname';
				$name = explode(' ', $_POST['name']);
				$_POST['lastname'] = array_pop($name);
				$_POST['firstname'] = implode(' ', $name);
				$_POST['action'] = 'wpm_register';
				$_POST['wpm_id'] = $_POST['sku1'];
				$_POST['username'] = $_POST['email1'];
				$orig_email = $_POST['email'] = $_POST['email1'];
				$_POST['password1'] = $_POST['password2'] = $that->PassGen();

				$address = array();
				$address['company'] = $_POST['shipCompany'];
				$address['address1'] = $_POST['shipAddress1'];
				$address['address2'] = $_POST['shipAddress2'];
				$address['city'] = $_POST['shipCity'];
				$address['state'] = $_POST['shipState'];
				$address['zip'] = $_POST['shipZip'];
				$address['country'] = $_POST['shipCountry'];

				$_POST['sctxnid'] = $_POST['orderID'];

				$_POST['wpm_useraddress'] = $address;


				//cache the order
				$onescmerchantid = trim($that->GetOption('onescmerchantid'));
				$onescapikey = trim($that->GetOption('onescapikey'));
				if ($onescmerchantid && $onescapikey) {
					require_once($that->pluginDir . '/extlib/OneShopAPI.php');
					require_once($that->pluginDir . '/extlib/WLMOneShopAPI.php');
					$api = new WLMOneShopAPI($onescmerchantid, $onescapikey, 'https://www.mcssl.com');
					$order = $api->get_order_by_id($_POST['orderID'], true);
					$that->SaveOption("1scorder_".$_POST['orderID'], $order);
				}

				// support 1SC upsells
				if (trim($that->GetOption('onesc_include_upsells'))) {
					if (count($order['upsells'])) {
						$_POST['additional_levels'] = $order['upsells'];
					}
				}

				$that->ShoppingCartRegistration();
			} else {
				$onescmerchantid = trim($that->GetOption('onescmerchantid'));
				$onescapikey = trim($that->GetOption('onescapikey'));

				if ($onescmerchantid && $onescapikey) {
					$raw_post_data = file_get_contents('php://input');
					require_once($that->pluginDir . '/extlib/OneShopAPI.php');
					$API = new OneShopAPI($that->GetOption('onescmerchantid'), $that->GetOption('onescapikey'), 'https://www.mcssl.com');

					$requestBodyXML = new DOMDocument();

					# Load the request body into XML and check that the result has been parsed into XML
					if ($requestBodyXML->loadXML($raw_post_data) == true) {
						$notificationType = $requestBodyXML->documentElement->nodeName;
						$tokenNode = $requestBodyXML->getElementsByTagName('Token')->item(0)->nodeValue;

						switch ($notificationType) {
							case "NewOrder":
								$apiResult = $API->GetOrderById($tokenNode);
								break;

							default:
								# May have other types of notifications in the future
								break;
						}

						$apiResultXML = new DOMDocument();

						if ($apiResultXML->loadXML($apiResult) == true) {
							# Check if the API returned an error
							$apiSuccess = $apiResultXML->getElementsByTagName('Response')->item(0)->getAttribute('success');
							if ($apiSuccess == 'true') {

								$orderXML = &$apiResultXML;

								$sku = $orderXML->getElementsByTagName('Sku')->item(0)->nodeValue;
								$status = strtolower($orderXML->getElementsByTagName('OrderStatusType')->item(0)->nodeValue);
								$levels = array_keys($that->GetOption('wpm_levels'));

								$_POST['sctxnid'] = $orderXML->getElementsByTagName('OrderId')->item(0)->nodeValue;
								if ($status == 'accepted') {
									$that->ShoppingCartReactivate();

									// Add hook for Shoppingcart reactivate so that other plugins can hook into this
									$_POST['sc_type'] = '1ShoppingCart';
									do_action('wlm_shoppingcart_rebill', $_POST);

								} else {
									$that->ShoppingCartDeactivate();
								}
							}
						}
					}
				}
			}
		}

		function CheckStatus($norun = false) {
			global $wpdb, $WishListMemberInstance;
			if (!is_object($WishListMemberInstance))
				return;

			$onescmerchantid = trim($WishListMemberInstance->GetOption('onescmerchantid'));
			$onescapikey = trim($WishListMemberInstance->GetOption('onescapikey'));

			if (!$onescmerchantid || !$onescapikey)
				return;

			if (!wp_next_scheduled('wishlistmember_1shoppingcart_api_statuscheck')) {
				wp_schedule_event(time(), 'daily', 'wishlistmember_1shoppingcart_api_statuscheck');
			}


			if ($norun === false) {
				//make sure all orders have been cached
				self::CacheOrders();

				// let's load the 1ShoppingCart API Class
				require_once($WishListMemberInstance->pluginDir . '/extlib/OneShopAPI.php');
				require_once($WishListMemberInstance->pluginDir . '/extlib/WLMOneShopAPI.php');
				//require_once($that->pluginDir.'/extlib/OneShopAPI.php');
				$levels = $WishListMemberInstance->GetOption('wpm_levels');
				$api = new WLMOneShopAPI($onescmerchantid, $onescapikey, 'https://www.mcssl.com');
				$orderIDs = (array) $wpdb->get_col($q = "SELECT DISTINCT `option_value` FROM `{$WishListMemberInstance->Tables->userlevel_options}` WHERE `option_name`='transaction_id' AND `option_value` REGEXP '^[0-9]+$'");

				//start sync'ing
				//process order items for the last 3 days.
				//this will allow for other orders at least another try
				//if previous cron failed.
				$startdate = date('m/d/Y', time() - (3600 * 24 * 2));
				$enddate = date('m/d/Y');

				$last_day_orders = $api->get_orders($startdate, $enddate);
				$initial_orders = array();

				while ($orderID = array_shift($orderIDs)) {
					$initial_orders[] = $WishListMemberInstance->GetOption("1scorder_$orderID");
				}

				foreach ($initial_orders as $o) {
					$o_id = sprintf('%s-%s', $o['product_id'], $o['client_id']);
					foreach ($last_day_orders as $l) {
						$l_id = sprintf('%s-%s', $l['product_id'], $l['client_id']);
						//this is a child order, let's now check for the status or the order
						if ($o_id == $l_id) {
							$_POST['sctxnid'] = $o['id'];
							if (in_array($l['status'], array('accepted', 'approved', 'authorized'))) {
								$WishListMemberInstance->ShoppingCartReactivate();
							} else {
								$WishListMemberInstance->ShoppingCartDeactivate();
							}
						}
					}
				}
			}
		}
		static function CacheOrders() {
			global $wpdb;
			global $WishListMemberInstance;

			require_once($WishListMemberInstance->pluginDir . '/extlib/OneShopAPI.php');
			require_once($WishListMemberInstance->pluginDir . '/extlib/WLMOneShopAPI.php');

			$onescmerchantid = trim($WishListMemberInstance->GetOption('onescmerchantid'));
			$onescapikey = trim($WishListMemberInstance->GetOption('onescapikey'));

			$orderIDs = (array) $wpdb->get_col($q = "SELECT DISTINCT `option_value` FROM `{$WishListMemberInstance->Tables->userlevel_options}` WHERE `option_name`='transaction_id' AND `option_value` REGEXP '^[0-9]+$'");


			$api = new WLMOneShopAPI($onescmerchantid, $onescapikey, 'https://www.mcssl.com');
			foreach($orderIDs as $oid) {
				if(!is_array($WishListMemberInstance->GetOption("1scorder_$oid"))) {
					$order = $api->get_order_by_id($oid);
					$WishListMemberInstance->SaveOption("1scorder_$oid", $order);
				} else {
					echo 'cache_hit';
				}
			}
			echo 'all orders have been cached';
		}
	}



	add_action('wishlistmember_1shoppingcart_api_statuscheck', array('WLM_INTEGRATION_1SHOPPINGCART', 'CheckStatus'));
	WLM_INTEGRATION_1SHOPPINGCART::CheckStatus(true);

	if (isset($_GET['forcecheck'])) {
		WLM_INTEGRATION_1SHOPPINGCART::CheckStatus();
	}

	if(isset($_GET['cacheorders'])) {
		WLM_INTEGRATION_1SHOPPINGCART::CacheOrders();
	}
}

// When order is created:
// 1. Add an entry in option table
// 2. Entry looks like 1scorder_xxxxxx where xxxxxx is the order_id
// 3. 1scorder_xxxxxx VALUE is the 1sc ORDER
// Purpose is to save us a lot of calls to orders
?>
