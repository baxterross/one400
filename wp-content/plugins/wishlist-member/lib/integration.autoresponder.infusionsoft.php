<?php

/*
 * Infusionsoft Autoresponder Integration Functions
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.infusionsoft.php 1733 2013-09-13 10:52:56Z feljun $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: Containcs functions needed for Infusionsoft Integration
  Location: lib/
  Calling program : ARSubscribe() from PluginMethods.php
 */

//this line is already set at integration.autoresponders.php
// $__classname__ = 'WLM_AUTORESPONDER_INFUSIONSOFT';
// $__optionname__ = 'infusionsoft';
// $__methodname__ = 'AutoResponderInfusionsoft';  // this is the method name being called by the ARSubscribe function

if (!class_exists('WLM_AUTORESPONDER_INFUSIONSOFT')) {

	class WLM_AUTORESPONDER_INFUSIONSOFT {
		
		/* This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above */
		function AutoResponderInfusionsoft($that, $ar, $wpm_id, $email, $unsub = false) {
			global $WishListMemberInstance;
			if (!class_exists('xmlrpcmsg') || !class_exists('xmlrpcval') || !class_exists('xmlrpc_client')) {
				include_once($x = $WishListMemberInstance->pluginDir . '/extlib/xmlrpc.php');
			}

			$iskey = $ar['iskey']; // get the Infusionsoft API
			$ismname = $ar['ismname']; // get the Infusionsoft API
			$cID = $ar['isCID'][$wpm_id]; // get the campaign ID of the Membership Level
			$isUnsub = ($ar['isUnsub'][$wpm_id] == 1 ? true : false); // check if we will unsubscribe or not

			if ($cID) { //$listID should not be empty
				list($fName, $lName) = explode(" ", $that->ARSender['name'], 2); //split the name into First and Last Name
				$emailAddress = $that->ARSender['email'];
				//create connection
				$url = 'https://' . $ismname . '.infusionsoft.com:443/api/xmlrpc';
				$con = new xmlrpc_client($url);
				$con->return_type = 'phpvals';
				$con->setSSLVerifyHost(0);
				$con->setSSLVerifyPeer(0);

				//check if the user is already in the database
				$returnFields = array('Id');
				$carray = array(
					php_xmlrpc_encode($iskey),
					php_xmlrpc_encode($emailAddress),
					php_xmlrpc_encode($returnFields));
				$call = new xmlrpcmsg('ContactService.findByEmail', $carray);
				$dups = $con->send($call);
				if (!$dups->faultCode()) {
					$dups = $dups->value();
					$personId = $dups[0]['Id'];
				}
				if ($unsub) { // if the Unsubscribe
					//if email is found, remove it from campaign and if it will be unsubscribe once remove from level
					if (!empty($personId) && $isUnsub) {
						$msg = new xmlrpcmsg('ContactService.removeFromCampaign');
						$msg->addParam(new xmlrpcval($iskey));
						$msg->addParam(new xmlrpcval((int) $personId, 'int'));
						$msg->addParam(new xmlrpcval((int) $cID, 'int'));
						$res = $con->send($msg);
						if (!$res->faultCode()) {
							$res = $res->value();
						}
					}
				} else { //else Subscribe
					//if email is existing, assign it to the campaign
					if (!empty($personId)) {
						$msg = new xmlrpcmsg('ContactService.addToCampaign');
						$msg->addParam(new xmlrpcval($iskey));
						$msg->addParam(new xmlrpcval((int) $personId, 'int'));
						$msg->addParam(new xmlrpcval((int) $cID, 'int'));
						$res = $con->send($msg);
						if (!$res->faultCode()) {
							$res = $res->value();
						}
					} else {
						//if email is new, assign it to the add it to the database
						$msg = new xmlrpcmsg('ContactService.add');
						$msg->addParam(new xmlrpcval($iskey));
						$msg->addParam(
								new xmlrpcval(
										array(
											'Email' => new xmlrpcval($emailAddress),
											'FirstName' => new xmlrpcval($fName),
											'LastName' => new xmlrpcval($lName)
										),
										'struct')
						);
						$newId = $con->send($msg);
						// if successfully addded, opt-in the contact
						if (!$newId->faultCode()) {
							$newId = $newId->value();

							$msg = new xmlrpcmsg('APIEmailService.optIn');
							$msg->addParam(new xmlrpcval($iskey));
							$msg->addParam(new xmlrpcval($emailAddress));
							$msg->addParam(new xmlrpcval("Will be added to the campaign."));
							$res = $con->send($msg);
							if (!$res->faultCode()) {
								$res = $res->value();
							}
							//then assign it to the campaign
							$msg = new xmlrpcmsg('ContactService.addToCampaign');
							$msg->addParam(new xmlrpcval($iskey));
							$msg->addParam(new xmlrpcval((int) $newId, 'int'));
							$msg->addParam(new xmlrpcval((int) $cID, 'int'));
							$res = $con->send($msg);
							if (!$res->faultCode()) {
								$res = $res->value();
							}
						}
					}
				}
			}
		}
		//end AutoResponderInfusionsoft function	
	}

}