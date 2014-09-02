<?php

/*
 * MailChimp Autoresponder Integration Functions
 * Original Author : Fel Jun Palawan
 * Version: $Id: integration.autoresponder.mailchimp.php 1777 2013-10-11 16:42:39Z feljun $
 */

/*
  GENERAL PROGRAM NOTES: (This script was based on Mike's Autoresponder integrations.)
  Purpose: Containcs functions needed for MailChimp Integration
  Location: lib/
  Calling program : ARSubscribe() from PluginMethods.php
 */

//$__classname__ = 'WLM_AUTORESPONDER_MAILCHIMP';
//$__optionname__ = 'mailchimp';
//$__methodname__ = 'AutoResponderMailChimp';  // this is the method name being called by the ARSubscribe function

if (!class_exists('WLM_AUTORESPONDER_MAILCHIMP')) {

	class WLM_AUTORESPONDER_MAILCHIMP {
		/* This is the required function, this is being called by ARSubscibe, function name should be the same with $__methodname__ variable above */

		function AutoResponderMailChimp($that, $ar, $wpm_id, $email, $unsub = false) {
			$listID = $ar['mcID'][$wpm_id]; // get the list ID of the Membership Level
			$mcAPI = $ar['mcapi']; // get the MailChimp API
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$WLM_AUTORESPONDER_MAILCHIMP_INIT = new WLM_AUTORESPONDER_MAILCHIMP_INIT;
			if ($listID) { //$listID should not be empty
				list($fName, $lName) = explode(" ", $that->ARSender['name'], 2); //split the name into First and Last Name
				$emailAddress = $that->ARSender['email'];
				$data = false;
				if ($unsub) { // if the Unsubscribe
					$mcOnRemCan = isset($ar['mcOnRemCan'][$wpm_id]) ? $ar['mcOnRemCan'][$wpm_id] : "";
					if ($mcOnRemCan == "unsub") {
						//$res = $this->mcListUnsubscribe($mcAPI, $listID, $emailAddress, true);
						$data = array(
							"apikey"=> $mcAPI,
							"action"=>"unsubscribe",
							"listID"=> $listID,
							"email"=>$emailAddress,
							"delete_member"=>true
						);						
					} elseif ($mcOnRemCan == "move" || $mcOnRemCan == "add") {

						$gp = $ar['mcRCGp'][$wpm_id];
						$gping = $ar['mcRCGping'][$wpm_id];
						$groupings = array();
						if ($gp != "" && $gping != "") {
							$groupings = array(array('name' => $gp, 'groups' => $gping));
						}
						$replace_interests = $mcOnRemCan == "move" ? true : false;
						#add name or else this will still fail
						$merge_vars = array('FNAME' => $fName, 'LNAME' => $lName, 'NAME' => "$fName $lName", 'GROUPINGS' => $groupings); // populate the merger vars for MailChimp
						//$res = $this->mcListSubscribe($mcAPI, $listID, $emailAddress, $merge_vars, true, true, $replace_interests);
						$data = array(
							"apikey"=> $mcAPI,
							"action"=>"subscribe",
							"listID"=> $listID,
							"email"=>$emailAddress,
							"mergevars"=>$merge_vars,
							"optin"=>true,
							"update_existing"=>true,
							"replace_interests"=>$replace_interests
						);
					}
				} else { //else Subscribe
					$gp = $ar['mcGp'][$wpm_id];
					$gping = $ar['mcGping'][$wpm_id];
					$groupings = array();
					if ($gp != "") {
						$groupings = array(array('name' => $gp, 'groups' => $gping));
					}
					$optin = $ar['optin']; // get the MailChimp API
					$optin = $optin == 1 ? false:true;					
					#add name or else this will still fail
					$merge_vars = array('FNAME' => $fName, 'LNAME' => $lName, 'NAME' => "$fName $lName", 'GROUPINGS' => $groupings); // populate the merger vars for MailChimp
					//$res = $this->mcListSubscribe($mcAPI, $listID, $emailAddress, $merge_vars, $optin, true, false);
					$data = array(
						"apikey"=> $mcAPI,
						"action"=>"subscribe",
						"listID"=> $listID,
						"email"=>$emailAddress,
						"mergevars"=>$merge_vars,
						"optin"=>$optin,
						"update_existing"=>1,
						"replace_interests"=>false
					);				
				}
				if($data){
					$qname = "mailchimp_" .time();
					$data = maybe_serialize($data);
					$WishlistAPIQueueInstance->add_queue($qname,$data,"For Queueing");
					$WLM_AUTORESPONDER_MAILCHIMP_INIT->mcProcessQueue();
				}
			}
		}

		/* End of Functions */
	}

}