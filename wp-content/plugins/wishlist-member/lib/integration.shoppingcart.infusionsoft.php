<?php

/*
 * InfusionSoft Shopping Cart Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.infusionsoft.php 2037 2014-02-18 15:49:17Z mike $
 */
//this line is already set on integration.shoppingcarts.php
// $__classname__ = 'WLM_INTEGRATION_INFUSIONSOFT';
// $__optionname__ = 'isthankyou';
// $__methodname__ = 'InfusionSoft';

if (!class_exists('WLM_INTEGRATION_INFUSIONSOFT')) {

	class WLM_INTEGRATION_INFUSIONSOFT {	

		//The Main Function Being Called
		function InfusionSoft($that) {

			global $wpdb;

			if (!class_exists('xmlrpcmsg') || !class_exists('xmlrpcval') || !class_exists('xmlrpc_client')) {
				include_once($x = $that->pluginDir . '/extlib/xmlrpc.php');
			}

		//All FUNCTIONS Starts Here
			//function for getting the product sku using the product id in infusionsoft
			function getProductSku($Id, $con, $key) {

				$msg = new xmlrpcmsg('DataService.findByField');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('Product'));
				$msg->addParam(new xmlrpcval(1, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(new xmlrpcval('Id'));
				$msg->addParam(new xmlrpcval($Id));
				$msg->addParam(new xmlrpcval(array(new xmlrpcval('Sku')), 'array'));
				$product = $con->send($msg);
				$product = $product->value();

				if ($product)
					return $product[0];
				else
					return false;
			}

			//function for getting the contact info from infusionsoft
			function getContact($Id, $con, $key) {

				$msg = new xmlrpcmsg('ContactService.load');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval($Id, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('FirstName'),
									new xmlrpcval('LastName'),
									new xmlrpcval('Email'),
									new xmlrpcval('Company'),
									new xmlrpcval('StreetAddress1'),
									new xmlrpcval('StreetAddress2'),
									new xmlrpcval('City'),
									new xmlrpcval('State'),
									new xmlrpcval('PostalCode'),
									new xmlrpcval('Country')
								),
								'array')
				);
				$user = $con->send($msg);
				$user = $user->value();
				return $user;
			}

			//function for getting the invoice using invoice id
			function getInvoice($Id, $con, $key) {

				$msg = new xmlrpcmsg('DataService.findByField');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('Invoice'));
				$msg->addParam(new xmlrpcval(1, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(new xmlrpcval('Id'));
				$msg->addParam(new xmlrpcval($Id));
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('ContactId'),
									new xmlrpcval('JobId'),
									new xmlrpcval('DateCreated'),
									new xmlrpcval('TotalDue'),
									new xmlrpcval('PayStatus'),
									new xmlrpcval('CreditStatus'),
									new xmlrpcval('RefundStatus'),
									new xmlrpcval('PayPlanStatus'),
									new xmlrpcval('InvoiceType'),
									new xmlrpcval('ProductSold')
								),
								'array')
				);
				$invoice = $con->send($msg);
				$invoice = $invoice->value();

				if ($invoice)
					return $invoice[0];
				else
					return false;
			}

			//function for getting the invoice using jobid
			function getInvoiceByJobId($JobId, $con, $key) {

				$msg = new xmlrpcmsg('DataService.findByField');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('Invoice'));
				$msg->addParam(new xmlrpcval(1, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(new xmlrpcval('JobId'));
				$msg->addParam(new xmlrpcval($JobId, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('ContactId'),
									new xmlrpcval('DateCreated'),
									new xmlrpcval('TotalDue'),
									new xmlrpcval('JobId'),
									new xmlrpcval('PayStatus'),
									new xmlrpcval('CreditStatus'),
									new xmlrpcval('RefundStatus'),
									new xmlrpcval('PayPlanStatus'),
									new xmlrpcval('InvoiceType'),
									new xmlrpcval('ProductSold')
								),
								'array')
				);
				$invoices = $con->send($msg);
				$invoices = $invoices->value();

				if (empty($invoices))
					return false;

				return $invoices[0];
			}

			//function for getting the Job using order id passed in url
			function getJob($orderId, $con, $key) {

				$msg = new xmlrpcmsg('DataService.findByField');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('Job'));
				$msg->addParam(new xmlrpcval(1, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(new xmlrpcval('Id'));
				$msg->addParam(new xmlrpcval($orderId, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('JobTitle'),
									new xmlrpcval('ContactId'),
									new xmlrpcval('StartDate'),
									new xmlrpcval('DueDate'),
									new xmlrpcval('JobNotes'),
									new xmlrpcval('ProductId'),
									new xmlrpcval('JobStatus'),
									new xmlrpcval('DateCreated'),
									new xmlrpcval('JobRecurringId'),
									new xmlrpcval('OrderType'),
									new xmlrpcval('OrderStatus')
								),
								'array')
				);

				$jobs = $con->send($msg);
				$jobs = $jobs->value();

				if (empty($jobs))
					return false;

				return $jobs[0];
			}

			//function for getting the payplan items
			function GetPayplanItems($payplan_id, $con, $key) {

				// retrieve Payplan Items
				$msg = new xmlrpcmsg('DataService.findByField');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('PayPlanItem'));
				$msg->addParam(new xmlrpcval(10, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(new xmlrpcval('PayPlanId'));
				$msg->addParam(new xmlrpcval($payplan_id));
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('PayPlanId'),
									new xmlrpcval('DateDue'),
									new xmlrpcval('AmtDue'),
									new xmlrpcval('Status')
								),
								'array')
				);

				$ppi = $con->send($msg);
				$ppi = $ppi->value();

				if ($ppi)
					$ret = $ppi;
				else
					$ret = false;

				return $ret;
			}

			//function for getting the payplan status
			function GetPayplanStatus($invoice_id, $con, $key) {

				// retrieve Payplan
				$msg = new xmlrpcmsg('DataService.findByField');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('PayPlan'));
				$msg->addParam(new xmlrpcval(1, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(new xmlrpcval('InvoiceId'));
				$msg->addParam(new xmlrpcval($invoice_id));
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('InvoiceId'),
									new xmlrpcval('AmtDue'),
									new xmlrpcval('DateDue'),
									new xmlrpcval('InitDate'),
									new xmlrpcval('StartDate')
								),
								'array')
				);

				$pp = $con->send($msg);
				$pp = $pp->value();

				if ($pp) {
					$pp = $pp[0];
					if ($pp['StartDate'] > date('Ymd\TH:i:s', strtotime('EST')) && !empty($pp['StartDate'])) {
						$ret = array("PayPlanId" => $pp['Id'], "OverDue" => false);
					} else {
						$ret = array("PayPlanId" => $pp['Id'], "OverDue" => true);
					}
				} else {
					$ret = false;
				}
				return $ret;
			}

			//function for getting the subscription using product id
			function GetSubscriptionStatusByPID($contactID, $PId, $con, $key) {
				$msg = new xmlrpcmsg('DataService.query');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('RecurringOrder'));
				$msg->addParam(new xmlrpcval(1, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(
									'ContactId' => new xmlrpcval($contactID),
									'ProductId' => new xmlrpcval($PId, 'int')
								),
								'struct')
				);
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('ContactId'),
									new xmlrpcval('OriginatingOrderId'),
									new xmlrpcval('SubscriptionPlanId'),
									new xmlrpcval('ProductId'),
									new xmlrpcval('StartDate'),
									new xmlrpcval('EndDate'),
									new xmlrpcval('LastBillDate'),
									new xmlrpcval('NextBillDate'),
									new xmlrpcval('ReasonStopped'),
									new xmlrpcval('Status')
								),
								'array')
				);
				$recur = $con->send($msg);
				$recur = $recur->value();

				if ($recur)
					return $recur[0];
				else
					return false;
			}

			//function for get the subscription using subscription id
			function GetSubscriptionStatusBySID($SId, $con, $key) {
				$msg = new xmlrpcmsg('DataService.query');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('RecurringOrder'));
				$msg->addParam(new xmlrpcval(1, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(
									'Id' => new xmlrpcval($SId, 'int')
								),
								'struct')
				);
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('ContactId'),
									new xmlrpcval('OriginatingOrderId'),
									new xmlrpcval('SubscriptionPlanId'),
									new xmlrpcval('ProductId'),
									new xmlrpcval('StartDate'),
									new xmlrpcval('EndDate'),
									new xmlrpcval('LastBillDate'),
									new xmlrpcval('NextBillDate'),
									new xmlrpcval('ReasonStopped'),
									new xmlrpcval('Status')
								),
								'array')
				);

				$recur = $con->send($msg);
				$recur = $recur->value();

				if ($recur)
					return $recur[0];
				else
					return false;
			}

			//function for getting subscription using jobid
			function GetSubscriptionStatusByJID($contactID, $JId, $con, $key) {
				$msg = new xmlrpcmsg('DataService.query');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('RecurringOrder'));
				$msg->addParam(new xmlrpcval(1, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(
									'ContactId' => new xmlrpcval($contactID),
									'OriginatingOrderId' => new xmlrpcval($JId, 'int')
								),
								'struct')
				);
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('ContactId'),
									new xmlrpcval('OriginatingOrderId'),
									new xmlrpcval('SubscriptionPlanId'),
									new xmlrpcval('ProductId'),
									new xmlrpcval('StartDate'),
									new xmlrpcval('EndDate'),
									new xmlrpcval('LastBillDate'),
									new xmlrpcval('NextBillDate'),
									new xmlrpcval('ReasonStopped'),
									new xmlrpcval('Status')
								),
								'array')
				);

				$recur = $con->send($msg);
				$recur = $recur->value();

				if ($recur)
					return $recur[0];
				else
					return false;
			}

			function isRefundedInvoice($con,$key,$invid){

				$con->return_type = 'phpvals';
				$con->setSSLVerifyHost(0);
				$con->setSSLVerifyPeer(0);

				$msg = new xmlrpcmsg('DataService.query');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('InvoicePayment'));
				$msg->addParam(new xmlrpcval(1000, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(
									'InvoiceId' => new xmlrpcval($invid,'int')
								),
								'struct')
				);			
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('InvoiceId'),
									new xmlrpcval('Amt'),
									new xmlrpcval('PayStatus'),
									new xmlrpcval('PaymentId'),
									new xmlrpcval('SkipCommission')
								),
								'array')
				);

				$inv_payments = $con->send($msg);
				$inv_payments = $inv_payments->value();
				$ret = false;

				if($inv_payments){
					foreach($inv_payments as $inv_payment){
						if($inv_payment['PayStatus'] == 'Refunded'){
							$ret = true;
							break;
						}
					}
				}

				return $ret;
			}

			function isLastInvoicePaid($con,$key,$invoice){
				//lets get the jobs for the subscription
				$jobs = GetSubscriptionJobs($invoice['SubscriptionId'], $con, $key);
				if( ! $jobs ) return false; //no job then unpaid
				$job_ids = array_map(create_function('$arr', 'return $arr["Id"];'), $jobs); //we only need the ids
				$latest_jobid = max($job_ids); //get the latest invoice of this subscription

				$latest_invoice = getInvoiceByJobId($latest_jobid, $con, $key); //get the invoice of this job
				if( ! $latest_invoice ) return false; //if invoice unpaid
				return (boolean) $latest_invoice["PayStatus"];
			}

			//get jobs related for this subscription/recurringorder
			//in order to get the invoices for this subscription
			function GetSubscriptionJobs($recurid, $con, $key) {

				$con->return_type = 'phpvals';
				$con->setSSLVerifyHost(0);
				$con->setSSLVerifyPeer(0);

				$msg = new xmlrpcmsg('DataService.query');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('Job'));
				$msg->addParam(new xmlrpcval(1000, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(
									'JobRecurringId' => new xmlrpcval($recurid,'int')
								),
								'struct')
				);			
				$msg->addParam(
						new xmlrpcval(
								array(
									new xmlrpcval('Id'),
									new xmlrpcval('JobTitle'),
									new xmlrpcval('ContactId'),
									new xmlrpcval('JobRecurringId'),
									new xmlrpcval('ProductId')
								),
								'array')
				);

				$jobs = $con->send($msg);
				$jobs = $jobs->value();

				return $jobs;
			}

			//function for getting the status of the invoice or subscription
			function GetStatus($invoice, $con, $key) {

				$sid = isset($invoice['SubscriptionId']) ? $invoice['SubscriptionId'] : "";
				$pid = $invoice['ProductSold'];

				if ($sid == "") { //old transaction id, base the search from contact id and product id
					//THIS IS FOR OLD VERSION OF IF INTEGRATION
					$invoice['Status'] = ($invoice['PayStatus'] == 1 && $invoice['RefundStatus'] == 0) ? 'active' : 'inactive';

					$recur = GetSubscriptionStatusByPID($invoice['ContactId'], $pid, $con, $key);

					if ($recur && !empty($recur['Status'])) { // make sure that we're not processing an empty field. fixes issue with complete recurring subscriptions
						$invoice['Status'] = strtolower($recur['Status']);
					}
				} else {
					//NEW INFUSIONSOFT UPDATES AFTER THE SPRING RELEASE, we added subscription id for subscriptions
					//non subscriptions have 00 values

					$invoice['Status'] = ($invoice['PayStatus'] == 1 && $invoice['RefundStatus'] == 0) ? 'active' : 'inactive';

					//process subscriptions
					if ($sid != "00") { // subscriptions have number values
						$recur = GetSubscriptionStatusBySID($sid, $con, $key);
					} else { // if subscription is not available, use the job id
						$recur = GetSubscriptionStatusByJID($invoice['ContactId'], $invoice['JobId'], $con, $key);
					}

					if ($recur && !empty($recur['Status'])) { // make sure that we're not processing an empty field. fixes issue with complete recurring subscriptions
						
						//assign the subscription id
						$invoice['SubscriptionId'] = $recur['Id'];
						unset($recur['Id']);

						$invoice = array_merge($invoice, $recur);

						if ($recur['Status'] != "Active") {
							$is_refund = isRefundedInvoice($con,$key,$invoice['Id']);
							if ($is_refund || strtolower(trim($recur['ReasonStopped'])) == "refund") { 
								$invoice['Status'] = "inactive";
							} else if ($recur['NextBillDate'] > date('Ymd\TH:i:s', strtotime('EST'))) { //if no active, lets cancel them only when the next bill date has passed already
								if ( isLastInvoicePaid($con,$key,$invoice) ) { //if last invoice is paid, wait for next bill date because he paid
									$invoice['Status'] = "active";
								}
							} else {
								$invoice['Status'] = strtolower($recur['Status']);
							}
						} else {
							$invoice['Status'] = strtolower($recur['Status']);
						}
					}
					
				}

				//if invoice is inactive, lets check if its has payment plan
				if ($invoice['Status'] == "inactive") {

					$invstat = "inactive";
					//lets get the payment plan for this invoice
					$pp = GetPayplanStatus($invoice['Id'], $con, $key);

					if ($pp) {
						if ($pp['OverDue']) { //if it has overdue payment plan
							//get the payment plan items
							$ppi = GetPayplanItems($pp['PayPlanId'], $con, $key);

							if ($ppi) {
								//get the payment plan items with unpaid status
								foreach ((array) $ppi AS $ppitems) {
									if ($ppitems['Status'] == 1) {
										//if it has unpaid payment plan items and its not yet due
										if ($ppitems['DateDue'] > date('Ymd\TH:i:s', strtotime('EST'))) {
											$invstat = "active";
										} else {//else its due
											$invstat = "inactive";
										}
										break;
									}
								}
							}
						} else {
							//if payment plan has number of days before charging and its not overdue
							$invstat = "active";
						}
					}
					$invoice['Status'] = $invstat;
				}

				return $invoice;
			}

		//End of FUNTCTIONS
		// START PROCESSING HERE
			$url = 'https://' . $that->GetOption('ismachine') . '.infusionsoft.com:443/api/xmlrpc';
			$key = $that->GetOption('isapikey');


			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

			$invmarker = 'InfusionSoft';

			$_GET['iscron'] = isset($_GET['iscron']) ? $_GET['iscron'] : "";
			$_GET['iscron'] = isset($_POST['contactId']) ? "ProcessContact" : $_GET['iscron'];

			switch (wlm_arrval($_GET,'iscron')) {
				case 'ProcessContact':
					$contactid = $_POST['contactId'];
					$add_level = isset($_POST['add']) ? $_POST['add'] : false;
					$remove_level = isset($_POST['remove']) ? $_POST['remove'] : false;
					$cancel_level = isset($_POST['cancel']) ? $_POST['cancel'] : false;

					//if none of these are present, we stop
					if(!$add_level && !$remove_level && !$cancel_level){exit;break;}
					//check if contact exist in infusionsoft
					$contact = getContact($contactid, $con, $key);
					if(!$contact){exit;break;}

					//first we get check if this user exist using txnid
					$wpm_user =  $that->GetUserIDFromTxnID("IFContact-{$contactid}");
					if(!$wpm_user){ //if not, check if it exist using the email address						
						$wpm_user = email_exists($contact["Email"]);
					}

					//if the user does not exist yet and its adding to level
					//lets create a new user using api
					if(!$wpm_user && $add_level){

							$wlm_api_key = $that->GetOption("WLMAPIKey");
							$wlm_site_url = home_url('/');
							$wlm_apiclass = new wlmapiclass($wlm_site_url,$wlm_api_key);
							$wlm_apiclass->return_format = "php";
							
							$uname = isset($_POST['WLMUserName']) && $_POST['WLMUserName'] != "" ? $_POST['WLMUserName'] : $contact['Email'];
							$pword = isset($_POST['WLMPassWord']) && $_POST['WLMPassWord'] != "" ? $_POST['WLMPassWord'] : $that->PassGen();
							$regemail = isset($_POST['WLMRegEmail']) && strtolower($_POST['WLMRegEmail']) == "no" ? false : true;
							$sequential = isset($_POST['WLMSequential']) && strtolower($_POST['WLMSequential']) == "no" ? false : true;
							// prepare data
							$data = array();
							$data['last_name'] = $contact['LastName'];
							$data['first_name'] = $contact['FirstName'];
							$data['user_login'] = $uname;
							$data['user_email'] = $contact['Email'];
							$data['user_pass'] = $pword;
							$data['display_name'] ="{$contact['FirstName']} {$contact['LastName']}";
							$data['Sequential'] = $sequential;
							$address['address1'] = $contact['StreetAddress1'];
							$address['address2'] = $contact['StreetAddress2'];
							$address['city'] = $contact['City'];
							$address['state'] = $contact['State'];
							$address['zip'] = $contact['PostalCode'];
							$address['country'] = $contact['Country'];
							$data["SendMail"] = $regemail;							
							$wpm_errmsg = '';

							$ret = unserialize($wlm_apiclass->post("/members",$data));

							if($ret["success"] && isset($ret["member"][0]["ID"])){
								$wpm_user = $ret["member"][0]["ID"];
							}
							
					}

					//assign infusiom contact id if none is assigned to this user
					if($wpm_user){
						$ifcontact = $that->Get_UserMeta($wpm_user,"wlminfusionsoft_contactid");
						if(!$ifcontact){
							$that->Update_UserMeta($wpm_user,"wlminfusionsoft_contactid",$contactid);
						}
					}

					$current_user_mlevels = $that->GetMembershipLevels($wpm_user);
					$wpm_levels = $that->GetOption('wpm_levels');

					//add
					if($wpm_user && $add_level){
						$user_mlevels = $current_user_mlevels;
						$add_level_arr = explode(",", $add_level);
						if(in_array("all",$add_level_arr)){
							$add_level_arr = array_merge($add_level_arr,array_keys($wpm_levels));
							$add_level_arr = array_unique($add_level_arr);
						}

						foreach($add_level_arr as $id=>$add_level){
							if(isset($wpm_levels[$add_level])){ //check if valid level
								if(!in_array($add_level,$user_mlevels)){
									$user_mlevels[] = $add_level;
									$that->SetMembershipLevels($wpm_user,$user_mlevels);
									$that->SetMembershipLevelTxnID($wpm_user, $add_level,"IFContact-{$contactid}");
								}else{
									//just uncancel the user
									$ret = $that->LevelCancelled($add_level, $wpm_user,false);
								}
							}elseif(strrpos($add_level,"payperpost") !== false){
								$that->SetPayPerPost($wpm_user, $add_level);
							}					
						}
					}
					//cancel
					if($wpm_user && $cancel_level){
						$user_mlevels = $current_user_mlevels;
						$cancel_level_arr = explode(",", $cancel_level);
						if(in_array("all",$cancel_level_arr)){
							$cancel_level_arr = array_merge($cancel_level_arr,array_keys($wpm_levels));
							$cancel_level_arr = array_unique($cancel_level_arr);
						}

						foreach($cancel_level_arr as $id=>$cancel_level){
							if(isset($wpm_levels[$cancel_level])){ //check if valid level
								if(in_array($cancel_level,$user_mlevels)){
									$ret = $that->LevelCancelled($cancel_level, $wpm_user,true);
								}	
							}						
						}
					}
					//remove
					if($wpm_user && $remove_level){
						$user_mlevels = $current_user_mlevels;
						$remove_level_arr = explode(",", $remove_level);
						if(in_array("all",$remove_level_arr)){
							$remove_level_arr = array_merge($remove_level_arr,array_keys($wpm_levels));
							$remove_level_arr = array_unique($remove_level_arr);
						}							

						foreach($remove_level_arr as $id=>$remove_level){
							$arr_index = array_search($remove_level,$user_mlevels);
							if($arr_index !== false){
								unset($user_mlevels[$arr_index]);								
							}elseif(strrpos($remove_level,"payperpost") !== false){
								list($marker,$pid) = explode("-",$remove_level);
								$post_type = get_post_type($pid);
								$that->RemovePostUsers($post_type, $pid, $wpm_user);
							}								
						}
						$that->SetMembershipLevels($wpm_user,$user_mlevels);
					}
					usleep(1000000);
					exit;
					break;
				case '1':
					set_time_limit(0); //override max execution time
					//get all the infusionsoft txn_id
					$query = "SELECT `option_value` FROM `{$that->Tables->userlevel_options}` WHERE `option_value` LIKE '{$invmarker}%'";
					$trans = $wpdb->get_results($query);

					$istrans = array();
					foreach ($trans as $t) {
						$txn_id = $t->option_value; //format {marker}-{invoice#}-{subcriptionid}
						list($marker, $tid) = explode('-', $txn_id, 2); //seperate the marker from the others
						$istrans[] = $tid; //{invoice#}-{subcriptionid} left
					}

					$istrans = array_unique($istrans);
					$cnt = count($istrans);

					echo "Syncing Infusionsoft Transactions with WLM<br />";
					echo "<i>You should see a message below saying that all records were processed.<br />If not some records might not been processed due to lack of computer resources or an error occured.</i>";
					if ($cnt > 0) {
						echo "<br /><br />Processing record#:<br />";
					} else {
						echo "<br /><br />No Records to Sync.";
					}
					//loop through the txn_ids
					$rec = 1;
					foreach ((array) $istrans AS $invid) {
						list($iid, $sid) = explode('-', $invid, 2);  // retrieve Invoice id and Sub id

						$invoice = getInvoice($iid, $con, $key);
						// do we have a valid invoice? if so, retrieve the status
						if ($invoice) {
							$invoice["SubscriptionId"] = $sid; //include the subscription id

							$invoice = GetStatus($invoice, $con, $key); //get invoice status

							// update level status based on invoice status
							$_POST['sctxnid'] = "{$invmarker}-" . $invid;
							switch ($invoice['Status']) {
								case 'active':
									$that->ShoppingCartReactivate();
									
									// Add hook for Shoppingcart reactivate so that other plugins can hook into this
									$_POST['sc_type'] = 'Infusionsoft';
									do_action('wlm_shoppingcart_rebill', $_POST);
									
									break;
								default://'inactive':
									$that->ShoppingCartDeactivate();
							}
						}
						echo $rec++ . ($invoice ? "(OK)" : "(Invalid)") . ", ";
					}
					//lets end the cron job here
					echo "<br /><br /><b>FINISHED</b>.<i>All {$cnt} records were processed.</i>";
					exit;
					break;

				default: // catch the data from infusionsoft after payment
					//get the productid to be used for free trial subscriptions
					$SubscriptionPlanProductId = isset($_GET['SubscriptionPlanProductId']) ? $_GET['SubscriptionPlanProductId'] : false;
					//get the subscription id
					$SubscriptionId = isset($_GET['SubscriptionId']) ? $_GET['SubscriptionId'] : "00";
					//if its a free trial
					$isTrial = isset($_GET['SubscriptionPlanWait']) ? true : false;

					// retrieve Job using OrderID passed
					$job = getJob($_GET['orderId'], $con, $key);
					if (!$job)
						return false; //if job(OrderID) does not exist, end

					/*
					 * fix for new order form
					 * new order form does not pass the contactId
					 */
					if (empty($_GET['contactId'])) {
						//get the contact id from the job
						$contactId = $job['ContactId'];
					} else {
						//get the contact id from $_GET
						$contactId = $_GET['contactId'];
					}

					//retrieve the user info
					$user = getContact($contactId, $con, $key);
					if (!$user)
						return false; //if no user exist, end


						
					//retrieve invoice using our job Id
					$invoice = getInvoiceByJobId($job['Id'], $con, $key);
					if (!$invoice)
						return false; //if no invoice for that job, end


						
					//if its a subscription plan with free trial
					//populate the ProductSold field of invoice
					if ($SubscriptionPlanProductId && $isTrial) {
						$invoice['ProductSold'] = (int) $SubscriptionPlanProductId; //set the product id to SubscriptionPlanProductId, they have the same value
					}

					//set the $invoice Subscription Id
					$invoice['SubscriptionId'] = $SubscriptionId;

					//process the invoice and get its status
					$invoice = GetStatus($invoice, $con, $key);
					// fetch Sku for the product of the invoice
					// product id is used to search for the sku
					// we loop through each product sold and break the loop if we find a sku that matches a WishList Member level ID
					$wpm_levels = $that->GetOption('wpm_levels');
					foreach (explode(',', $invoice['ProductSold']) AS $psold) {

						$product = getProductSku($psold, $con, $key);
						$valid_sku = $that->IsPPPLevel($product['Sku']) || isset($wpm_levels[$product['Sku']]) ? true:false;
						if ($product && $valid_sku) {
							if (!$invoice['Sku']) {
								$invoice['Sku'] = $product['Sku'];
							} else {
								$_POST['additional_levels'][] = $product['Sku'];
							}
						}
					}
					
					//if no product sku then lets end here
					if (!isset($invoice['Sku']) || $invoice['Sku'] == "" || empty($invoice['Sku']))
						return false;

					// if we're active, then good.
					if ($invoice['Status'] != 'active')
						return false;

					// prepare data
					$_POST['lastname'] = $user['LastName'];
					$_POST['firstname'] = $user['FirstName'];
					$_POST['action'] = 'wpm_register';
					$_POST['wpm_id'] = $invoice['Sku'];
					$_POST['username'] = $user['Email'];
					$_POST['email'] = $user['Email'];
					$_POST['password1'] = $_POST['password2'] = $that->PassGen();
					$_POST['sctxnid'] = "{$invmarker}-" . $invoice['Id'] . "-{$SubscriptionId}";

					//prepare the address fields using info from shopping cart
					$address['company'] = $user['Company'];
					$address['address1'] = $user['StreetAddress1'];
					$address['address2'] = $user['StreetAddress2'];
					$address['city'] = $user['City'];
					$address['state'] = $user['State'];
					$address['zip'] = $user['PostalCode'];
					$address['country'] = $user['Country'];

					$_POST['wpm_useraddress'] = $address;

					// do registration
					$that->ShoppingCartRegistration();

			}//END OF SWITCH CASE
		}
	// END OF PROCESSING HERE
	}
}
