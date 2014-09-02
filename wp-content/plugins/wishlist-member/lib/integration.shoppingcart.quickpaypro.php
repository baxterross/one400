<?php

/*
 * QuickPayPro Shopping Cart Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.quickpaypro.php 1671 2013-08-15 01:34:55Z mike $
 */

//$__classname__ = 'WLM_INTEGRATION_QUICKPAYPRO';
//$__optionname__ = 'qppthankyou';
//$__methodname__ = 'QuickPayPro';

if (!class_exists('WLM_INTEGRATION_QUICKPAYPRO')) {

	class WLM_INTEGRATION_QUICKPAYPRO {

		function QuickPayPro($that) {
			$cmd = $_POST['cmd']['cmd'];
			$hash = $_POST['hash']['hash'];
			$secret = $that->GetOption('qppsecret');
			$myhash = md5($cmd . '__' . $secret);
			$_POST['ddate'] = $_POST['cmd']['date'];
			$_POST['action'] = 'wpm_register';
			$_POST['processor'] = 'cydec';
			$_POST['lastname'] = $_POST['info']['last_name'];
			$_POST['firstname'] = $_POST['info']['first_name'];
			$_POST['wpm_id'] = $_POST['info']['level'];
			$_POST['username'] = $_POST['info']['email'];
			$_POST['email'] = $_POST['info']['email'];
			if (empty($_POST['info']['password'])) {
				$_POST['password1'] = $_POST['password2'] = $that->PassGen();
			} else {
				$_POST['password1'] = $_POST['password2'] = $_POST['info']['password'];
			}

			$_POST['sctxnid'] = $_POST['info']['transaction_id'] ? $_POST['info']['transaction_id'] : 'QPPRO_' . $_POST['info']['email'];

			$trans_id = $_POST['info']['transaction_id'];
			$trans_id = str_replace("||", "", $trans_id);

			if ($that->CheckMemberTransID($trans_id) == false & $cmd <> 'add') {
				$order_id = explode("||", $_POST['sctxnid']);
				$_POST['sctxnid'] = $order_id[0];
			} else {
				$_POST['sctxnid'] = $trans_id;
			}

			if ($hash == $myhash) {
//                    add_filter('rewrite_rules_array',array(&$that,'RewriteRules'));
//                    $GLOBALS['wp_rewrite']->flush_rules();
				switch ($cmd) {
					case 'add':
						$that->ShoppingCartRegistration(false); // we ALWAYS auto-create account for QPP because they can't redirect
						break;
					case 'delete':
					case 'deactivate':

						if (!empty($_POST['ddate']) & $_POST['ddate'] <> 'NOW') {
							$that->ScheduleCartDeactivation();
							//$that->CancelScheduledCancelations();
						} else {
							$that->ShoppingCartDeactivate();
						}
						break;
					case 'activate':
						$that->ShoppingCartReactivate();
						break;
					default:
						header("Location:" . get_bloginfo('url'));
						exit;
				}
			}
		}

	}

}
?>
