<?php

/*
 * Infusionsoft Autoresponder Integration Init
 * Original Author : Fel Jun Palawan
 */

if (!class_exists('xmlrpcmsg') || !class_exists('xmlrpcval') || !class_exists('xmlrpc_client')) {
	global $WishListMemberInstance;
	include_once($x = $WishListMemberInstance->pluginDir . '/extlib/xmlrpc.php');
}

if (!class_exists('WLM_AUTORESPONDER_INFUSIONSOFT_INIT')) {

	class WLM_AUTORESPONDER_INFUSIONSOFT_INIT {

	    function load_hooks() {
			global $WishListMemberInstance;
			if(isset($WishListMemberInstance) && $WishListMemberInstance->GetOption('auto_ismachine') && $WishListMemberInstance->GetOption('auto_isapikey')){
					add_action('wishlistmember_user_registered', array($this, 'NewUserTagsHookQueue'),99,2);
					add_action('wishlistmember_add_user_levels', array($this, 'AddUserTagsHookQueue'),99,2);
					add_action('wishlistmember_pre_remove_user_levels', array($this, 'RemoveUserTagsHookQueue'),99,2);
					add_action('wishlistmember_cancel_user_levels', array($this, 'CancelUserTagsHookQueue'),99,2);	
				
				//check if this settings is handled by shopping card integration of infusionsoft
				if ( ! $WishListMemberInstance->GetOption('ismachine') || ! $WishListMemberInstance->GetOption('isapikey') ) {
					add_action('edit_user_profile', array($this, 'ProfileForm'));
					add_action('show_user_profile', array($this, 'ProfileForm'));
					add_action('profile_update', array($this, 'UpdateProfile'), 9, 2);	
				}
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


		/* End of main Function */
		function getContactIDbyEmail($that,$ismachine,$key,$email){

			$url = 'https://' .$ismachine . '.infusionsoft.com:443/api/xmlrpc';
			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

			$msg = new xmlrpcmsg('ContactService.findByEmail');
			$msg->addParam(new xmlrpcval($key));
			$msg->addParam(new xmlrpcval($email));
			$msg->addParam(
					new xmlrpcval(
							array(	
								new xmlrpcval('Id'),
							),
							'array')
			);
			$contact = $con->send($msg);
			$contact = $contact->value();

			if($contact){
				return $contact[0]["Id"];
			}else{
				return false;
			}
		}
		function optinContactEmail($that,$ismachine,$key,$email){
			$url = 'https://' .$ismachine . '.infusionsoft.com:443/api/xmlrpc';
			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

			$msg = new xmlrpcmsg('APIEmailService.optIn');
			$msg->addParam(new xmlrpcval($key));
			$msg->addParam(new xmlrpcval($email));
			$msg->addParam(new xmlrpcval("Added Via WLM INF AR Integration API."));
			$res = $con->send($msg);	
		}
		function createContactIDbyEmail($that,$ismachine,$key,$user){

			$url = 'https://' .$ismachine . '.infusionsoft.com:443/api/xmlrpc';
			$con = new xmlrpc_client($url);
			$con->return_type = 'phpvals';
			$con->setSSLVerifyHost(0);
			$con->setSSLVerifyPeer(0);

			$msg = new xmlrpcmsg('ContactService.add');
			$msg->addParam(new xmlrpcval($key));
			$msg->addParam(
					new xmlrpcval(
							array(
								'Email' => new xmlrpcval( stripslashes( $user["Email"] ) ),
								'FirstName' => new xmlrpcval( stripslashes( $user["FirstName"] ) ) ,
								'LastName' => new xmlrpcval( stripslashes( $user["LastName"] ) )
							),
							'struct')
			);
			$contact = $con->send($msg);

			if(!$contact->faultCode()){
				return $contact->value();
			}else{
				return false;
			}
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
		public function applyUserTags($that,$ismachine,$key,$contact,$tags){

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
		public function removeUserTags($that,$ismachine,$key,$contact,$tags){

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

		public function NewUserTagsHook($uid=null,$data=null){			
			if(!$uid && !isset($data['wpm_id'])) return;
			$tempacct = $data['email'] == 'temp_' . md5($data['orig_email']);
			if($tempacct) return;
			
			global $WishListMemberInstance;
			$ismachine = $WishListMemberInstance->GetOption('auto_ismachine');
			$isapikey = $WishListMemberInstance->GetOption('auto_isapikey');
			$contactid = $WishListMemberInstance->Get_UserMeta($uid,"wlminfusionsoft_contactid");

			$levels = (array) $data['wpm_id'];

			$user_info = get_userdata($uid);
			$email = $user_info->user_email;
			if(!$contactid){
				if($email && filter_var($email, FILTER_VALIDATE_EMAIL)){
					$contactid = $this->getContactIDbyEmail($WishListMemberInstance,$ismachine,$isapikey,$email);
					if(!$contactid){
						$user = array(
						'Email' => $email,
						'FirstName' =>$user_info->user_firstname,
						'LastName' => $user_info->user_lastname
						);
						$contactid = $this->createContactIDbyEmail($WishListMemberInstance,$ismachine,$isapikey,$user);
						$this->optinContactEmail($WishListMemberInstance,$ismachine,$isapikey,$email);
						$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);
					}
				}				
			}
			
			if($contactid){
				foreach($levels as $level){	
					//add the contact to a tag/group
					$istags_add_app = $WishListMemberInstance->GetOption('auto_istags_add_app');
					if($istags_add_app) $istags_add_app = maybe_unserialize($istags_add_app);
					else $istags_add_app = array();
					if(isset($istags_add_app[$level])){
						foreach($istags_add_app[$level] as $k=>$val){
							$ret = $this->applyUserTags($WishListMemberInstance,$ismachine,$isapikey,$contactid,$val);
							if(isset($ret["errno"])) return $ret;
						}
					}

					//remove the contact from tag/group
					$istags_add_rem = $WishListMemberInstance->GetOption('auto_istags_add_rem');
					if($istags_add_rem) $istags_add_rem = maybe_unserialize($istags_add_rem);
					else $istags_add_rem = array();
					if(isset($istags_add_rem[$level])){
						foreach($istags_add_rem[$level] as $k=>$val){
							$ret = $this->removeUserTags($WishListMemberInstance,$ismachine,$isapikey,$contactid,$val);
							if(isset($ret["errno"])) return $ret;
						}
					}					
				}
			}else{
				return array("errstr"=>"No Contact ID","errno"=>1);
			}
		}

		public function AddUserTagsHook($uid, $newlevels = ''){
			global $WishListMemberInstance;
			$ismachine = $WishListMemberInstance->GetOption('auto_ismachine');
			$isapikey = $WishListMemberInstance->GetOption('auto_isapikey');
			$contactid = $WishListMemberInstance->Get_UserMeta($uid,"wlminfusionsoft_contactid");

			$levels = (array) $newlevels;

			$user_info = get_userdata($uid);
			$email = $user_info->user_email;

			if(!$contactid){
				if($email  && filter_var($email, FILTER_VALIDATE_EMAIL)){
					$contactid = $this->getContactIDbyEmail($WishListMemberInstance,$ismachine,$isapikey,$email);
					if(!$contactid){
						$user = array(
						'Email' => $email,
						'FirstName' =>$user_info->user_firstname,
						'LastName' => $user_info->user_lastname
						);
						$contactid = $this->createContactIDbyEmail($WishListMemberInstance,$ismachine,$isapikey,$user);
						$this->optinContactEmail($WishListMemberInstance,$ismachine,$isapikey,$email);
						$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);
					}
				}				
			}
			
			if($contactid){
				foreach($levels as $level){	
					//add the contact to a tag/group
					$istags_add_app = $WishListMemberInstance->GetOption('auto_istags_add_app');
					if($istags_add_app) $istags_add_app = maybe_unserialize($istags_add_app);
					else $istags_add_app = array();
					if(isset($istags_add_app[$level])){
						foreach($istags_add_app[$level] as $k=>$val){
							$ret = $this->applyUserTags($WishListMemberInstance,$ismachine,$isapikey,$contactid,$val);
							if(isset($ret["errno"])) return $ret;
						}
					}

					//remove the contact from tag/group
					$istags_add_rem = $WishListMemberInstance->GetOption('auto_istags_add_rem');
					if($istags_add_rem) $istags_add_rem = maybe_unserialize($istags_add_rem);
					else $istags_add_rem = array();
					if(isset($istags_add_rem[$level])){
						foreach($istags_add_rem[$level] as $k=>$val){
							$ret = $this->removeUserTags($WishListMemberInstance,$ismachine,$isapikey,$contactid,$val);
							if(isset($ret["errno"])) return $ret;
						}
					}					
				}
			}else{
				return array("errstr"=>"No Contact ID","errno"=>1);
			}
		}

		public function RemoveUserTagsHook($uid, $removedlevels = ''){
			global $WishListMemberInstance;
			$ismachine = $WishListMemberInstance->GetOption('auto_ismachine');
			$isapikey = $WishListMemberInstance->GetOption('auto_isapikey');
			$contactid = $WishListMemberInstance->Get_UserMeta($uid,"wlminfusionsoft_contactid");

			$levels = (array) $removedlevels;

			$user_info = get_userdata($uid);
			$email = $user_info->user_email;

			if(!$contactid){
				if($email && filter_var($email, FILTER_VALIDATE_EMAIL)){
					$contactid = $this->getContactIDbyEmail($WishListMemberInstance,$ismachine,$isapikey,$email);
					if(!$contactid){
						$user = array(
						'Email' => $email,
						'FirstName' =>$user_info->user_firstname,
						'LastName' => $user_info->user_lastname
						);
						$contactid = $this->createContactIDbyEmail($WishListMemberInstance,$ismachine,$isapikey,$user);
						$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);
					}
				}				
			}

			if($contactid){
				foreach($levels as $level){				
					//add the contact to a tag/group
					$istags_add_app = $WishListMemberInstance->GetOption('auto_istags_remove_app');
					if($istags_add_app) $istags_add_app = maybe_unserialize($istags_add_app);
					else $istags_add_app = array();
					if(isset($istags_add_app[$level])){
						foreach($istags_add_app[$level] as $k=>$val){
							$ret = $this->applyUserTags($WishListMemberInstance,$ismachine,$isapikey,$contactid,$val);
							if(isset($ret["errno"])) return $ret;
						}
					}

					//remove the contact from tag/group
					$istags_add_rem = $WishListMemberInstance->GetOption('auto_istags_remove_rem');
					if($istags_add_rem) $istags_add_rem = maybe_unserialize($istags_add_rem);
					else $istags_add_rem = array();
					if(isset($istags_add_rem[$level])){
						foreach($istags_add_rem[$level] as $k=>$val){
							$ret = $this->removeUserTags($WishListMemberInstance,$ismachine,$isapikey,$contactid,$val);
							if(isset($ret["errno"])) return $ret;
						}
					}
				}
			}else{
				return array("errstr"=>"No Contact ID","errno"=>1);
			}
		}			

		public function CancelUserTagsHook($uid, $removedlevels = ''){
			global $WishListMemberInstance;
			$ismachine = $WishListMemberInstance->GetOption('auto_ismachine');
			$isapikey = $WishListMemberInstance->GetOption('auto_isapikey');
			$contactid = $WishListMemberInstance->Get_UserMeta($uid,"wlminfusionsoft_contactid");

			$levels = (array) $removedlevels;

			$user_info = get_userdata($uid);
			$email = $user_info->user_email;

			if(!$contactid){
				if($email && filter_var($email, FILTER_VALIDATE_EMAIL)){
					$contactid = $this->getContactIDbyEmail($WishListMemberInstance,$ismachine,$isapikey,$email);
					if(!$contactid){
						$user = array(
						'Email' => $email,
						'FirstName' =>$user_info->user_firstname,
						'LastName' => $user_info->user_lastname
						);
						$contactid = $this->createContactIDbyEmail($WishListMemberInstance,$ismachine,$isapikey,$user);
						$WishListMemberInstance->Update_UserMeta($uid,"wlminfusionsoft_contactid",$contactid);
					}
				}				
			}

			if($contactid){
				foreach($levels as $level){				
					//add the contact to a tag/group
					$istags_add_app = $WishListMemberInstance->GetOption('auto_istags_cancelled_app');
					if($istags_add_app) $istags_add_app = maybe_unserialize($istags_add_app);
					else $istags_add_app = array();
					if(isset($istags_add_app[$level])){
						foreach($istags_add_app[$level] as $k=>$val){
							$ret = $this->applyUserTags($WishListMemberInstance,$ismachine,$isapikey,$contactid,$val);
							if(isset($ret["errno"])) return $ret;
						}
					}

					//remove the contact from tag/group
					$istags_add_rem = $WishListMemberInstance->GetOption('auto_istags_cancelled_rem');
					if($istags_add_rem) $istags_add_rem = maybe_unserialize($istags_add_rem);
					else $istags_add_rem = array();
					if(isset($istags_add_rem[$level])){
						foreach($istags_add_rem[$level] as $k=>$val){
							$ret = $this->removeUserTags($WishListMemberInstance,$ismachine,$isapikey,$contactid,$val);
							if(isset($ret["errno"])) return $ret;
						}
					}
				}
			}else{
				return array("errstr"=>"No Contact ID","errno"=>1);
			}
		}
		public function NewUserTagsHookQueue($uid=null,$udata=null){
			$data = array(
				"uid"=>$uid,
				"action"=>"new",
				"data"=>$udata
			);
			$this->ifarAddQueue($data);
		}
		public function AddUserTagsHookQueue($uid, $addlevels = ''){
			$data = array(
				"uid"=>$uid,
				"action"=>"add",
				"addlevels"=>$addlevels
			);
			$this->ifarAddQueue($data);	
		}
		public function RemoveUserTagsHookQueue($uid, $removedlevels = ''){
			$data = array(
				"uid"=>$uid,
				"action"=>"remove",
				"removedlevels"=>$removedlevels
			);
			$this->ifarAddQueue($data);		
		}
		public function CancelUserTagsHookQueue($uid, $cancellevels = ''){
			$data = array(
				"uid"=>$uid,
				"action"=>"cancel",
				"cancellevels"=>$cancellevels
			);
			$this->ifarAddQueue($data);
		}

		function ifarAddQueue($data,$process=true){
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$qname = "infusionsoftar_" .time();
			$data = maybe_serialize($data);
			$WishlistAPIQueueInstance->add_queue($qname,$data,"For Queueing");
			if($process){
				$this->ifarProcessQueue();
			}			
		}

		public function ifarProcessQueue($recnum = 10,$tries = 5){
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$last_process = get_option("WLM_InfusionsoftARAPI_LastProcess");
			$current_time = time();
			$tries = $tries > 1 ? (int)$tries:5;
			$error = false;
			//lets process every 10 seconds
			if(!$last_process || ($current_time - $last_process) > 10){
				$queues = $WishlistAPIQueueInstance->get_queue("infusionsoftar",$recnum,$tries,"tries,name");
				foreach($queues as $queue){
					$data = maybe_unserialize($queue->value);
					if($data['action'] == 'new'){
						$res = $this->NewUserTagsHook($data['uid'],$data['data']);
					}elseif($data['action'] == 'add'){
						$res = $this->AddUserTagsHook($data['uid'],$data['addlevels']);
					}elseif($data['action'] == 'remove'){
						$res = $this->RemoveUserTagsHook($data['uid'],$data['removedlevels']);
					}elseif($data['action'] == 'cancel'){
						$res = $this->CancelUserTagsHook($data['uid'],$data['cancellevels']);
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
				//save the last processing time
				if($error){
					$current_time = time();
					if($last_process){
						update_option("WLM_InfusionsoftARAPI_LastProcess",$current_time);
					}else{
						add_option("WLM_InfusionsoftARAPI_LastProcess",$current_time);
					}					
				}
			}
		}			
		/* End of Functions*/		
	}
}

$ar = new WLM_AUTORESPONDER_INFUSIONSOFT_INIT();
$ar->load_hooks();