<?php

/*
 * InfusionSoft Shopping Cart Integration Init
 * Original Author : Fel Jun Palawan
 */

if (!class_exists('xmlrpcmsg') || !class_exists('xmlrpcval') || !class_exists('xmlrpc_client')) {
	global $WishListMemberInstance;
	include_once($x = $WishListMemberInstance->pluginDir . '/extlib/xmlrpc.php');
}

if (!class_exists('WLM_INTEGRATION_INFUSIONSOFT_INIT')) {

	class WLM_INTEGRATION_INFUSIONSOFT_INIT {

		function load_hooks(){
			global $WishListMemberInstance;
			if(isset($WishListMemberInstance) && $WishListMemberInstance->GetOption('ismachine') && $WishListMemberInstance->GetOption('isapikey')){				
				add_action('wishlistmember_user_registered', array($this, 'NewUserTagsQueue'),99,2);
				add_action('wishlistmember_add_user_levels', array($this, 'AddUserTagsQueue'),99,2);
				add_action('wishlistmember_pre_remove_user_levels', array($this, 'RemoveUserTagsQueue'),99,2);
				add_action('wishlistmember_cancel_user_levels', array($this, 'CancelUserTagsQueue'),99,2);

				add_action('wishlistmember_addpp_posts_user', array($this, 'PPAddUserTagsQueue'),99,2);
				add_action('wishlistmember_removepp_posts_user', array($this, 'PPRemoveUserTagsQueue'),99,2);

				add_action('wishlistmember_addpp_pages_user', array($this, 'PPAddUserTagsQueue'),99,2);
				add_action('wishlistmember_removepp_pages_user', array($this, 'PPRemoveUserTagsQueue'),99,2);

				add_action('edit_user_profile', array($this, 'ProfileForm'));
				add_action('show_user_profile', array($this, 'ProfileForm'));
				add_action('profile_update', array($this, 'UpdateProfile'), 9, 2);						
			}
		}

		function ProfileForm( $user ) {
			global $WishListMemberInstance, $pagenow;
			if ( ! current_user_can( 'manage_options' ) ) { return; }
			$user_id = $user;
			if(is_object($user)) {
				$user_id = $user->ID;
			}
			if ( $pagenow != 'profile.php' && $pagenow != 'user-edit.php') return;

			$contactid = $WishListMemberInstance->Get_UserMeta( $user_id, "wlminfusionsoft_contactid" );
			echo '<h3>Infusionsoft Info</h3>';
			echo '<table class="form-table">';
			echo '<tbody>';
			echo 	'<tr>';
			echo 		'<th><label for="wlminfusionsoft_contactid">Infusionsoft Contact ID</label></th>';
			echo 		'<td>';
			echo 			'<input type="text" name="wlminfusionsoft_contactid" id="wlminfusionsoft_contactid" value="' .$contactid .'" class="regular-text">';
			echo 		'</td>';
			echo 	'</tr>';
			echo '</tbody>';
			echo '</table>';
		}

		public function UpdateProfile($user) {
			global $WishListMemberInstance;
			if ( ! current_user_can( 'manage_options' ) ) { return; }
			$user_id = $user;
			if(is_object($user)) {
				$user_id = $user->ID;
			}

			if(isset($_POST['wlminfusionsoft_contactid'])) {
				$WishListMemberInstance->Update_UserMeta($user_id, 'wlminfusionsoft_contactid', (int) trim($_POST['wlminfusionsoft_contactid']));
			}
		}

		public function getContactIDbyInvoice($that, $Id, $ismachine, $key) {

			$url = 'https://' .$ismachine . '.infusionsoft.com:443/api/xmlrpc';
			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

			$msg = new xmlrpcmsg('DataService.findByField');
			$msg->addParam(new xmlrpcval($key));
			$msg->addParam(new xmlrpcval('Invoice'));
			$msg->addParam(new xmlrpcval(1, 'int'));
			$msg->addParam(new xmlrpcval(0, 'int'));
			$msg->addParam(new xmlrpcval('Id'));
			$msg->addParam(new xmlrpcval((int)$Id));
			$msg->addParam(
					new xmlrpcval(
							array(
								new xmlrpcval('ContactId')
							),
							'array')
			);
			$invoice = $con->send($msg);
			$invoice = $invoice->value();

			if ($invoice)
				return isset($invoice[0]['ContactId']) ? $invoice[0]['ContactId']:false;
			else
				return false;
		}	
			
		public function getTagsCategory($that,$ismachine,$key){

			$url = 'https://' .$ismachine . '.infusionsoft.com:443/api/xmlrpc';
			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

				//get tags category
				$msg = new xmlrpcmsg('DataService.query');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('ContactGroupCategory'));
				$msg->addParam(new xmlrpcval(1000, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(),
								'struct')
				);
				$msg->addParam(
						new xmlrpcval(
								array(	
									new xmlrpcval('Id'),
									new xmlrpcval('CategoryName'),
									new xmlrpcval('CategoryDescription')
								),
								'array')
				);
				$tcategory = $con->send($msg);
				$tcategory = $tcategory->value();
			$tags_category = array();
			foreach($tcategory as $id=>$data){
				$tags_category[$data["Id"]] = $data["CategoryName"];
			}
			return $tags_category;
		}

		public function getTags($that,$ismachine,$key){

			$url = 'https://' .$ismachine . '.infusionsoft.com:443/api/xmlrpc';
			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

				//get the tags
				$msg = new xmlrpcmsg('DataService.query');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval('ContactGroup'));
				$msg->addParam(new xmlrpcval(1000, 'int'));
				$msg->addParam(new xmlrpcval(0, 'int'));
				$msg->addParam(
						new xmlrpcval(
								array(),
								'struct')
				);
				$msg->addParam(
						new xmlrpcval(
								array(	
									new xmlrpcval('Id'),
									new xmlrpcval('GroupName'),
									new xmlrpcval('GroupCategoryId')
								),
								'array')
				);
				$t = $con->send($msg);
				$t = $t->value();
			$tags = array();
			foreach($t as $id=>$data){
				$tags[$data["GroupCategoryId"]][] = array(
					"Id" => $data["Id"],
					"Name" => $data["GroupName"]
				);
			}
			return $tags;				
		}

		public function applyUserTags($ismachine,$key,$contact,$tags){

			$url = 'https://' .$ismachine . '.infusionsoft.com:443/api/xmlrpc';
			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

				//get the tags
				$msg = new xmlrpcmsg('ContactService.addToGroup');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval((int)$contact, 'int'));				
				$msg->addParam(new xmlrpcval((int)$tags, 'int'));
				$t = $con->send($msg);

				if($t->errno){
					$t = array("errno"=>$t->errno,"errstr"=>$t->errstr);	
				}else{
					$t = array("value"=>$t->value());	
				}
				return $t;		
		}
		public function removeUserTags($ismachine,$key,$contact,$tags){

			$url = 'https://' .$ismachine . '.infusionsoft.com:443/api/xmlrpc';
			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

				//get the tags
				$msg = new xmlrpcmsg('ContactService.removeFromGroup');
				$msg->addParam(new xmlrpcval($key));
				$msg->addParam(new xmlrpcval((int)$contact, 'int'));				
				$msg->addParam(new xmlrpcval((int)$tags, 'int'));
				$t = $con->send($msg);

				if($t->errno){
					$t = array("errno"=>$t->errno,"errstr"=>$t->errstr);	
				}else{
					$t = array("value"=>$t->value());	
				}
				return $t;			
		}

		//function to tag/untag user in IF when he is registered
		public function NewUserTagsHook($uid=null,$data=null){			
			if(!$uid && !isset($data['wpm_id'])) return;
			$tempacct = $data['email'] == 'temp_' . md5($data['orig_email']);
			if($tempacct) return;
			
			global $WishListMemberInstance;
			$ismachine = $WishListMemberInstance->GetOption('ismachine');
			$key = $WishListMemberInstance->GetOption('isapikey');
			$contactid = $WishListMemberInstance->Get_UserMeta($uid,"wlminfusionsoft_contactid");

			$levels = (array) $data['wpm_id'];

			/*If no Contact id, lets get it from txnid*/
			if(!$contactid){
				$txnids = $WishListMemberInstance->GetMembershipLevelsTxnIDs($uid);
				$txnids = (array)$txnids;
				//lets get the a txnid for Infusionsoft if he have for use later use
				$wlm_txnid = "";
				foreach($txnids as $id=>$txnid){
					if(strpos($txnid,'InfusionSoft') !== false){
						$wlm_txnid = $txnid;
					}elseif(strpos($txnid,'IFContact') !== false){
						$wlm_txnid = $txnid;
						break;
					}
				}
				if($wlm_txnid == ""){
					$wlm_txnid = $data['sctxnid'];
				}				
			}

			foreach($levels as $level){

				if(!$contactid){
					$txid = "";
					if(isset($txnids[$level]) && (strpos($txnids[$level],'InfusionSoft') !== false || strpos($txnids[$level],'IFContact') !== false)){
						$txid = $txnids[$level];
					}elseif(strpos($wlm_txnid,'InfusionSoft') !== false){
						$txid = $wlm_txnid;
					}elseif(strpos($wlm_txnid,'IFContact') !== false){
						$txid = $wlm_txnid;
					}

					if($txid != ""){
						if(strpos($wlm_txnid,'IFContact') !== false){
							list($marker,$contactid) = explode('-', $txid, 2);
							$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);
						}elseif(strpos($wlm_txnid,'InfusionSoft') !== false){
							list($marker, $tid) = explode('-', $txid, 2);
							list($iid, $sid) = explode('-', $tid, 2);  // retrieve Invoice id and Sub id
							$contactid = $this->getContactIDbyInvoice($WishListMemberInstance,$iid, $ismachine, $key);
							$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);						
						}
					}else{
						return array("errstr"=>"No Contact ID","errno"=>1);
					}	
				}

				if(strpos($level,"payperpost") ===false){
					$istags_add_app = $WishListMemberInstance->GetOption('istags_add_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('istags_add_rem');
				}else{
					$istags_add_app = $WishListMemberInstance->GetOption('istagspp_add_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('istagspp_add_rem');
				}

				//add the contact to a tag/group
				if($istags_add_app) $istags_add_app = maybe_unserialize($istags_add_app);
				else $istags_add_app = array();
				if(isset($istags_add_app[$level])){
					foreach($istags_add_app[$level] as $k=>$val){
						$ret = $this->applyUserTags($ismachine,$key,$contactid,$val);
						if(isset($ret["errno"])) return $ret;
					}
				}

				//remove the contact from tag/group
				if($istags_add_rem) $istags_add_rem = maybe_unserialize($istags_add_rem);
				else $istags_add_rem = array();
				if(isset($istags_add_rem[$level])){
					foreach($istags_add_rem[$level] as $k=>$val){
						$ret = $this->removeUserTags($ismachine,$key,$contactid,$val);
						if(isset($ret["errno"])) return $ret;
					}
				}					
			}
		}		
		//function to tag/untag user in IF when he is added to membership level
		public function AddUserTagsHook($uid, $newlevels = ''){
			global $WishListMemberInstance;
			$ismachine = $WishListMemberInstance->GetOption('ismachine');
			$key = $WishListMemberInstance->GetOption('isapikey');
			$contactid = $WishListMemberInstance->Get_UserMeta($uid,"wlminfusionsoft_contactid");

			$levels = (array) $newlevels;

			if(!$contactid){
				$txnids = $WishListMemberInstance->GetMembershipLevelsTxnIDs($uid);
				$txnids = (array)$txnids;
				//lets get the a txnid for Infusionsoft if he have for use later use
				$wlm_txnid = "";
				foreach($txnids as $id=>$txnid){
					if(strpos($txnid,'InfusionSoft') !== false){
						$wlm_txnid = $txnid;
					}elseif(strpos($txnid,'IFContact') !== false){
						$wlm_txnid = $txnid;
						break;
					}
				}
			}

			foreach($levels as $level){

				if(!$contactid){
					$txid = "";
					if(isset($txnids[$level]) && (strpos($txnids[$level],'InfusionSoft') !== false || strpos($txnids[$level],'IFContact') !== false)){
						$txid = $txnids[$level];
					}elseif(strpos($wlm_txnid,'InfusionSoft') !== false){
						$txid = $wlm_txnid;
					}elseif(strpos($wlm_txnid,'IFContact') !== false){
						$txid = $wlm_txnid;
					}

					if($txid != ""){
						if(strpos($wlm_txnid,'IFContact') !== false){
							list($marker,$contactid) = explode('-', $txid, 2);
							$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);
						}elseif(strpos($wlm_txnid,'InfusionSoft') !== false){
							list($marker, $tid) = explode('-', $txid, 2);
							list($iid, $sid) = explode('-', $tid, 2);  // retrieve Invoice id and Sub id
							$contactid = $this->getContactIDbyInvoice($WishListMemberInstance,$iid, $ismachine, $key);
							$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);						
						}
					}else{
						return array("errstr"=>"No Contact ID","errno"=>1);
					}			
				}

				//get tag settings
				if(strpos($level,"payperpost") ===false){
					$istags_add_app = $WishListMemberInstance->GetOption('istags_add_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('istags_add_rem');
				}else{
					$istags_add_app = $WishListMemberInstance->GetOption('istagspp_add_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('istagspp_add_rem');
				}
				//add the contact to a tag/group
				if($istags_add_app) $istags_add_app = maybe_unserialize($istags_add_app);
				else $istags_add_app = array();
				if(isset($istags_add_app[$level])){
					foreach($istags_add_app[$level] as $k=>$val){
						$ret = $this->applyUserTags($ismachine,$key,$contactid,$val);
						if(isset($ret["errno"])) return $ret;
					}
				}

				//remove the contact from tag/group
				if($istags_add_rem) $istags_add_rem = maybe_unserialize($istags_add_rem);
				else $istags_add_rem = array();
				if(isset($istags_add_rem[$level])){
					foreach($istags_add_rem[$level] as $k=>$val){
						$ret = $this->removeUserTags($ismachine,$key,$contactid,$val);
						if(isset($ret["errno"])) return $ret;
					}
				}			
			}
		}
		//function to tag/untag user in IF when he is remove  from membership level
		public function RemoveUserTagsHook($uid, $removedlevels = ''){
			global $WishListMemberInstance;
			$ismachine = $WishListMemberInstance->GetOption('ismachine');
			$key = $WishListMemberInstance->GetOption('isapikey');
			$contactid = $WishListMemberInstance->Get_UserMeta($uid,"wlminfusionsoft_contactid");

			$levels = (array) $removedlevels;

			if(!$contactid){
				$txnids = $WishListMemberInstance->GetMembershipLevelsTxnIDs($uid);
				$txnids = (array)$txnids;
				//lets get the a txnid for Infusionsoft if he have for use later use
				$wlm_txnid = "";
				foreach($txnids as $id=>$txnid){
					if(strpos($txnid,'InfusionSoft') !== false){
						$wlm_txnid = $txnid;
					}elseif(strpos($txnid,'IFContact') !== false){
						$wlm_txnid = $txnid;
						break;
					}
				}
			}

			foreach($levels as $level){
				if(!$contactid){
					$txid = "";
					if(isset($txnids[$level]) && (strpos($txnids[$level],'InfusionSoft') !== false || strpos($txnids[$level],'IFContact') !== false)){
						$txid = $txnids[$level];
					}elseif(strpos($wlm_txnid,'InfusionSoft') !== false){
						$txid = $wlm_txnid;
					}elseif(strpos($wlm_txnid,'IFContact') !== false){
						$txid = $wlm_txnid;
					}

					if($txid != ""){
						if(strpos($wlm_txnid,'IFContact') !== false){
							list($marker,$contactid) = explode('-', $txid, 2);
							$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);
						}elseif(strpos($wlm_txnid,'InfusionSoft') !== false){
							list($marker, $tid) = explode('-', $txid, 2);
							list($iid, $sid) = explode('-', $tid, 2);  // retrieve Invoice id and Sub id
							$contactid = $this->getContactIDbyInvoice($WishListMemberInstance,$iid, $ismachine, $key);
							$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);						
						}
					}else{
						return array("errstr"=>"No Contact ID","errno"=>1);
					}			
				}
					
				//get tag settings
				if(strpos($level,"payperpost") ===false){
					$istags_add_app = $WishListMemberInstance->GetOption('istags_remove_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('istags_remove_rem');
				}else{
					$istags_add_app = $WishListMemberInstance->GetOption('istagspp_remove_app');
					$istags_add_rem = $WishListMemberInstance->GetOption('istagspp_remove_rem');
				}						
				//add the contact to a tag/group
				if($istags_add_app) $istags_add_app = maybe_unserialize($istags_add_app);
				else $istags_add_app = array();
				if(isset($istags_add_app[$level])){
					foreach($istags_add_app[$level] as $k=>$val){
						$ret = $this->applyUserTags($ismachine,$key,$contactid,$val);
						if(isset($ret["errno"])) return $ret;
					}
				}

				//remove the contact from tag/group
				if($istags_add_rem) $istags_add_rem = maybe_unserialize($istags_add_rem);
				else $istags_add_rem = array();
				if(isset($istags_add_rem[$level])){
					foreach($istags_add_rem[$level] as $k=>$val){
						$ret = $this->removeUserTags($ismachine,$key,$contactid,$val);
						if(isset($ret["errno"])) return $ret;
					}
				}		
			}
		}
		//function to tag/untag user in IF when he is cancelled  from membership level
		public function CancelUserTagsHook($uid, $removedlevels = ''){
			global $WishListMemberInstance;
			$ismachine = $WishListMemberInstance->GetOption('ismachine');
			$key = $WishListMemberInstance->GetOption('isapikey');
			$contactid = $WishListMemberInstance->Get_UserMeta($uid,"wlminfusionsoft_contactid");

			$levels = (array) $removedlevels;

			if(!$contactid){
				$txnids = $WishListMemberInstance->GetMembershipLevelsTxnIDs($uid);
				$txnids = (array)$txnids;
				//lets get the a txnid for Infusionsoft if he have for use later use
				$wlm_txnid = "";
				foreach($txnids as $id=>$txnid){
					if(strpos($txnid,'InfusionSoft') !== false){
						$wlm_txnid = $txnid;
					}elseif(strpos($txnid,'IFContact') !== false){
						$wlm_txnid = $txnid;
						break;
					}
				}
			}

			foreach($levels as $level){
				if(!$contactid){
					$txid = "";
					if(isset($txnids[$level]) && (strpos($txnids[$level],'InfusionSoft') !== false || strpos($txnids[$level],'IFContact') !== false)){
						$txid = $txnids[$level];
					}elseif(strpos($wlm_txnid,'InfusionSoft') !== false){
						$txid = $wlm_txnid;
					}elseif(strpos($wlm_txnid,'IFContact') !== false){
						$txid = $wlm_txnid;
					}

					if($txid != ""){
						if(strpos($wlm_txnid,'IFContact') !== false){
							list($marker,$contactid) = explode('-', $txid, 2);
							$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);
						}elseif(strpos($wlm_txnid,'InfusionSoft') !== false){
							list($marker, $tid) = explode('-', $txid, 2);
							list($iid, $sid) = explode('-', $tid, 2);  // retrieve Invoice id and Sub id
							$contactid = $this->getContactIDbyInvoice($WishListMemberInstance,$iid, $ismachine, $key);
							$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);						
						}
					}else{
						return array("errstr"=>"No Contact ID","errno"=>1);
					}			
				}

				//add the contact to a tag/group
				$istags_add_app = $WishListMemberInstance->GetOption('istags_cancelled_app');
				if($istags_add_app) $istags_add_app = maybe_unserialize($istags_add_app);
				else $istags_add_app = array();
				if(isset($istags_add_app[$level])){
					foreach($istags_add_app[$level] as $k=>$val){
						$ret = $this->applyUserTags($ismachine,$key,$contactid,$val);
						if(isset($ret["errno"])) return $ret;
					}
				}

				//remove the contact from tag/group
				$istags_add_rem = $WishListMemberInstance->GetOption('istags_cancelled_rem');
				if($istags_add_rem) $istags_add_rem = maybe_unserialize($istags_add_rem);
				else $istags_add_rem = array();
				if(isset($istags_add_rem[$level])){
					foreach($istags_add_rem[$level] as $k=>$val){
						$ret = $this->removeUserTags($ismachine,$key,$contactid,$val);
						if(isset($ret["errno"])) return $ret;
					}
				}				
			}
		}
		public function NewUserTagsQueue($uid=null,$udata=null){
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$data = array(
				"uid"=>$uid,
				"action"=>"new",
				"data"=>$udata
			);
			$this->ifscAddQueue($data);
		}
		public function AddUserTagsQueue($uid, $addlevels = ''){
			$data = array(
				"uid"=>$uid,
				"action"=>"add",
				"levels"=>$addlevels
			);
			$this->ifscAddQueue($data);		
		}
		public function RemoveUserTagsQueue($uid, $removedlevels = ''){
			$data = array(
				"uid"=>$uid,
				"action"=>"remove",
				"levels"=>$removedlevels
			);
			$this->ifscAddQueue($data);			
		}
		public function CancelUserTagsQueue($uid, $cancellevels = ''){
			$data = array(
				"uid"=>$uid,
				"action"=>"cancel",
				"levels"=>$cancellevels
			);
			$this->ifscAddQueue($data);
		}
		public function PPAddUserTagsQueue($contentid,$levelid){
			global $WishListMemberInstance;
			$uid = substr($levelid,2);
			$user = get_userdata($uid);
			if(!$user) return;
			if(strpos($user->user_email,"temp_") !== false && strlen($user->user_email) == 37 && strpos($user->user_email,"@") === false) return;

			$data = array(
				"uid"=>$uid,
				"action"=>"add",
				"levels"=>"payperpost-{$contentid}"
			);
			$this->ifscAddQueue($data);			
		}	
		public function PPRemoveUserTagsQueue($contentid,$levelid){
			global $WishListMemberInstance;
			$uid = substr($levelid,2);
			$data = array(
				"uid"=>$uid,
				"action"=>"remove",
				"levels"=>"payperpost-{$contentid}"
			);
			$this->ifscAddQueue($data);
		}

		function ifscAddQueue($data,$process=true){
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$qname = "infusionsoftsc_" .time();
			$data = maybe_serialize($data);
			$WishlistAPIQueueInstance->add_queue($qname,$data,"For Queueing");
			if($process){
				$this->ifscProcessQueue();
			}			
		}

		public function ifscProcessQueue($recnum = 10,$tries = 5){
			global $WishListMemberInstance;
			$txnids = $WishListMemberInstance->GetMembershipLevelsTxnIDs(9);
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$last_process = get_option("WLM_InfusionsoftSCAPI_LastProcess");
			$current_time = time();
			$tries = $tries > 1 ? (int)$tries:5;
			$error = false;
			//lets process every 10 seconds
			if(!$last_process || ($current_time - $last_process) > 10){
				$queues = $WishlistAPIQueueInstance->get_queue("infusionsoftsc",$recnum,$tries,"tries,name");
				foreach($queues as $queue){
					$data = maybe_unserialize($queue->value);
					if($data['action'] == 'new'){
						$res = $this->NewUserTagsHook($data['uid'],$data['data']);
					}elseif($data['action'] == 'add'){
						$res = $this->AddUserTagsHook($data['uid'],$data['levels']);
					}elseif($data['action'] == 'remove'){
						$res = $this->RemoveUserTagsHook($data['uid'],$data['levels']);
					}elseif($data['action'] == 'cancel'){
						$res = $this->CancelUserTagsHook($data['uid'],$data['levels']);
					}

					if(isset($res['errstr'])){
						$res['error'] = strip_tags($res['errstr']);
						$res['error'] = str_replace(array("\n", "\t", "\r"), '',$res['error']);
						$d = array(
							'notes'=> "{$res['errno']}:{$res['error']}",
							'tries'=> $queue->tries + 1
							);
						$WishlistAPIQueueInstance->update_queue($queue->ID,$d);
						$error = true;
					}else{
						$WishlistAPIQueueInstance->delete_queue($queue->ID);
						$error = false;
					}
				}
				//save the last processing time when error has occured on last transaction
				if($error){
					$current_time = time();
					if($last_process){
						update_option("WLM_InfusionsoftSCAPI_LastProcess",$current_time);
					}else{
						add_option("WLM_InfusionsoftSCAPI_LastProcess",$current_time);
					}
				}
			}
		}
		//end of functions here
	}
}

$sc = new WLM_INTEGRATION_INFUSIONSOFT_INIT();
$sc->load_hooks();