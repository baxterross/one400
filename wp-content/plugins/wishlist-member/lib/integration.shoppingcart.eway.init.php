<?php

require_once dirname(__FILE__) . '/../extlib/eway/EwayWebServiceClient.php';
require_once dirname(__FILE__) . '/../extlib/eway/EwayRecurWebserviceClient.php';
require_once dirname(__FILE__) . '/../lib/integration.shoppingcart.eway.php';


class WishListEwayIntegrationInit {
	public function __construct() {
		add_action('wishlistmember_pre_remove_user_levels', array($this, 'user_levels_removed'), 10, 2);
		add_action('eway_sync', array($this, 'eway_sync'));

		if (!wp_next_scheduled('eway_sync' )) {
			wp_schedule_event(time(), 'daily', 'eway_sync');
		}
	}

	public function eway_sync() {
		WLM_INTEGRATION_EWAY::sync();
	}
	public function user_levels_removed($uid, $levels) {
		global $WishListMemberInstance;
		$settings = $WishListMemberInstance->GetOption('ewaysettings');
		$eway_ws  = new EwayRecurWebserviceClient($settings['eway_customer_id'],
						$settings['eway_username'],
						$settings['eway_password'],
						$settings['eway_sandbox']);

		foreach($levels as $lid) {
			//retrieve the trans id
			$txn = $WishListMemberInstance->GetMembershipLevelsTxnID($uid, $lid);
			list($tmp, $rebill_id, $invoice_ref, $cust_id) = explode('-', $txn);

			//do not run the call if this is not an eway rebill
			if($tmp != 'EWAYRB') {
				return;
			}

			$resp = $eway_ws->call('DeleteRebillEvent', array(
				'RebillCustomerID' => $cust_id,
				'RebillID'         => $rebill_id
			));
		}
	}
}

class WLM_Eway_ShortCodes {
	protected $folder = 'wlm_eway';
	public function __construct() {
		add_shortcode('wlm_eway_btn', array($this, 'wlm_eway_btn'));
		//include jquery, we need this
		wp_enqueue_script('jquery');

		//register tinymce shortcodes
		global $pagenow;
		if(in_array($pagenow, array('post.php', 'post-new.php'))) {
			global $WLMTinyMCEPluginInstanceOnly;
			global $WishListMemberInstance;

			$levels = $WishListMemberInstance->GetOption('wpm_levels');

			$wlm_shortcodes = array();
			$str = __(" Registration Button", "wishlist-member");
			foreach($levels as $i => $l) {
				$wlm_shortcodes[] = array('title' => $l['name'] . $str , 'value' => sprintf("[wlm_eway_btn sku=%s]", $i));
			}

			$WLMTinyMCEPluginInstanceOnly->RegisterShortcodes("Eway Integration", $wlm_shortcodes, array());
		}

	}
	public function get_view_path($handle) {
		global $WishListMemberInstance;
		return sprintf($WishListMemberInstance->pluginDir .'/extlib/'.$this->folder.'/%s.php', $handle);
	}
	public function profile_form($user) {
		$user_id = $user;
		if(is_object($user)) {
			$user_id = $user->ID;
		}

		global $WishListMemberInstance;
		global $pagenow;
		if($pagenow == 'profile.php' || $pagenow == 'user-edit.php') {
			$stripe_cust_id = $WishListMemberInstance->Get_UserMeta($user_id, 'stripe_cust_id');
			include $this->get_view_path('stripe_user_profile');
		}
	}
	public function update_profile($user) {
		$user_id = $user;
		if(is_object($user)) {
			$user_id = $user->ID;
		}
		if(current_user_can('manage_options')) {
			global $WishListMemberInstance;
			if(isset($_POST['stripe_cust_id'])) {
				$WishListMemberInstance->Update_UserMeta($user_id, 'stripe_cust_id', trim($_POST['stripe_cust_id']));
			}
		}
	}
	public function notices() {
		if(extension_loaded('curl')) {
			return;
		}

		if($_GET['page'] == 'WishListMember' && $_GET['wl'] =='integration') {
			$msg = '<div class="error fade"><p>';
			$msg .= __('<strong>WishList Member Notice:</strong> The <strong>Stripe</strong> integration will not work properly. Please enable <strong>Curl</strong>.', 'wishlist-member');
			$msg .= '</p></div>';
			echo $msg;
		}
	}



	public function wlm_eway_btn($atts, $content) {
		$form = new WLM_Eway_Forms();
		return $form->generate_popup_form($atts, $content);
	}

	public function footer() {
		global $WishListMemberInstance;
		$stripethankyou = $WishListMemberInstance->GetOption('stripethankyou');
		$wpm_scregister = get_bloginfo('url') . '/index.php/register/';
		$stripethankyou_url = $wpm_scregister . $stripethankyou;

		$wlmstripevars['cancelmessage'] = __("Are you sure you want to cancel your subscription?", 'wishlist-member');
		$wlmstripevars['nonceinvoices'] = wp_create_nonce('stripe-do-invoices');
		$wlmstripevars['nonceinvoicedetail'] = wp_create_nonce('stripe-do-invoice');
		$wlmstripevars['stripethankyouurl'] = $stripethankyou_url;
		?>
		<script type="text/javascript">
			function get_stripe_vars() {
				return eval( '(' + '<?php echo json_encode($wlmstripevars)?>' +')');
			}
		</script>
		<?php
	}
}

class WLM_Eway_Forms {
	protected $folder = 'wlm_eway';
	protected $forms;

	public function get_view_path($handle) {
		global $WishListMemberInstance;
		return sprintf($WishListMemberInstance->pluginDir .'/extlib/'.$this->folder.'/%s.php', $handle);
	}
	public function footer() {
		foreach($this->forms as $f) {
			echo $f;
		}
	}
	public function generate_popup_form($atts, $content) {
		global $WishListMemberInstance;
		wp_enqueue_script('leanModal', $WishListMemberInstance->pluginURL.'/js/jquery.leanModal.js', array('jquery'), $WishListMemberInstance->Version, true);
		wp_enqueue_script('wlm-popup-regform-card-validation', 'https://js.stripe.com/v2/', array('jquery', 'leanModal'), $WishListMemberInstance->Version, true);
		wp_enqueue_script('wlm-popup-regform', $WishListMemberInstance->pluginURL.'/js/wlm.popup-regform.js', array('wlm-popup-regform-card-validation'), $WishListMemberInstance->Version, true);
		wp_enqueue_style('wlm-popup-regform-style', $WishListMemberInstance->pluginURL.'/css/wlm.popup-regform.css', array(), $WishListMemberInstance->Version);
		//hook in late
		add_action('wp_footer', array($this, 'footer'), 100);

		global $current_user;
		extract(shortcode_atts(array(
					'sku' => null,
						), $atts));

		if (empty($sku)) {
			return null;
		}

		$wpm_levels               = $WishListMemberInstance->GetOption('wpm_levels');
		$ewaysettings             = $WishListMemberInstance->GetOption('ewaysettings');
		$ewaysettings['skip_cvc'] = true;
		extract($ewaysettings);


		$ppp_level    = $WishListMemberInstance->IsPPPLevel($sku);
		$level_name   = $wpm_levels[$sku]['name'];

		if($ppp_level) {
			$level_name = $ppp_level->post_title;
		}



		$btn_label       = empty($buttonlabel) ? "Join %level" : $buttonlabel;
		$btn_label       = str_replace('%level', $level_name, $btn_label);
		$panel_btn_label = empty($stripesettings['panelbuttonlabel']) ? "Pay" : $stripesettings['panelbuttonlabel'];
		$panel_btn_label = str_replace('%level', $level_name, $panel_btn_label);
		$settings        = $connections[$sku];
		$amt             = $settings['rebill_init_amount'];
		$currency        = empty($stripesettings['currency'])? 'USD' : $stripesettings['currency'];
		$wpm_scregister  = get_site_url() . '/index.php/register/';
		$thankyouurl     = $wpm_scregister . $WishListMemberInstance->GetOption('ewaythankyouurl');


		ob_start();
		?>
		<?php if (empty($content)) : ?>
			<button class="regform-button go-regform" style="width: auto" id="go-regform-<?php echo $sku ?>" class="" href="#regform-<?php echo $sku ?>"><?php echo $btn_label ?></button>
		<?php else: ?>
			<a id="go-regform-<?php echo $sku ?>" class="go-regform" href="#regform-<?php echo $sku ?>"><?php echo $content ?></a>
		<?php endif; ?>

		<?php
		$btn = ob_get_clean();
		ob_start();
		?>


		<?php
		if(!is_user_logged_in()){
			wp_enqueue_script('wlm-eway-regform', $WishListMemberInstance->pluginURL.'/extlib/wlm_eway/js/wlm-eway-form-new.js', array('wlm-popup-regform'), $WishListMemberInstance->Version, true);
			include $this->get_view_path('form_new');
		} else {
			wp_enqueue_script('wlm-eway-regform', $WishListMemberInstance->pluginURL.'/extlib/wlm_eway/js/wlm-eway-form-existing.js', array('wlm-popup-regform'), $WishListMemberInstance->Version, true);
			global $current_user;
			$stripe_cust_id = $WishListMemberInstance->Get_UserMeta($current_user->ID, 'stripe_cust_id');
			include $this->get_view_path('form_existing');
		}
		include $this->get_view_path('form_css');
		?>

		<?php
		$form = ob_get_clean();
		$form = preg_replace('/\s+/', ' ', $form);
		$this->forms[] = $form;
		return $btn;
	}
}
$sc = new WLM_Eway_ShortCodes();
$eway_init = new WishListEwayIntegrationInit();
?>
