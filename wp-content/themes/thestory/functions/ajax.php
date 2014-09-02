<?php
/**
 * This file contains AJAX request function handlers.
 *
 * @author Pexeto
 */


add_action('wp_ajax_pexeto_send_email', 'pexeto_ajax_send_email');
add_action('wp_ajax_nopriv_pexeto_send_email', 'pexeto_ajax_send_email');


/*******************************************************************************
 *   SEND EMAIL
 ******************************************************************************/

if(!function_exists('pexeto_ajax_send_email')){

	/**
	 * Sends an emal. Requires the GET request to contain the following parameters:
	 * - name - the name of the sender
	 * - email - the email of the sender
	 * - question - the content of the email
	 * Echoes back a json object containg a "success" key which is true when the 
	 * email is sent successfully and false if it is not.
	 */
	function pexeto_ajax_send_email(){
		$res = array();

		$validated = true;

		if(pexeto_option('captcha')==true && !(isset($_POST['widget']) && $_POST['widget']=='true')){
			//CAPTCHA VALIDATION
			pexeto_load_captcha_lib();
			$privatekey = pexeto_option('captcha_private_key');
			$resp = recaptcha_check_answer ($privatekey,
			                            $_SERVER['REMOTE_ADDR'],
			                            $_POST['recaptcha_challenge_field'],
			                            $_POST['recaptcha_response_field']);

			if (!$resp->is_valid) {
				// CAPTCHA text not valid
				$res['success']=false;
				$res['captcha_failed']=true;
				$validated = false;
			} 

		}

		if($validated){
			if(isset($_POST['name']) && $_POST['name'] && isset($_POST['email']) 
				&& $_POST['email'] && isset($_POST['question']) && $_POST['question']){
				
				$name=urldecode(stripcslashes($_POST['name']));
				$subject = "A message from ".$name;
				
				$notes = urldecode(stripcslashes($_POST['question']));
				$from = $_POST['email'];
				
				
				$message = "From: $from
				Message: $notes \r\n";
				
				
				$emailToSend=pexeto_option('email');
				$headers = 'From: '.$name.' <'.$from.'>' . "\r\n";
				$mail_res=wp_mail($emailToSend, $subject, $message, $headers);
				$res['success']=$mail_res;
			}
		}

		$json_res = json_encode($res);
		echo($json_res);
		exit();
	}
}
