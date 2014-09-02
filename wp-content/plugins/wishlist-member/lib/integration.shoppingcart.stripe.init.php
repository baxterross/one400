<?php

if(extension_loaded('curl') && !class_exists( 'Stripe', FALSE )) {
	global $WishListMemberInstance;
 	include_once($WishListMemberInstance->pluginDir . '/extlib/Stripe/Stripe.php');
}


if (!class_exists('WLM_Stripe_ShortCodes')) {
	class WLM_Stripe_ShortCodes {
		public function __construct() {
			add_action('edit_user_profile', array($this, 'profile_form'));
			add_action('show_user_profile', array($this, 'profile_form'));
			add_action('profile_update', array($this, 'update_profile'), 9, 2);

			add_action('admin_notices', array($this, 'notices'));

			add_shortcode('wlm_stripe_btn', array($this, 'wlm_stripe_btn'));
			add_shortcode('wlm_stripe_linkback', array($this, 'wlm_stripe_linkback'));
			add_shortcode('wlm_stripe_profile', array($this, 'wlm_stripe_profile'));

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
					$wlm_shortcodes[] = array('title' => $l['name'] . $str , 'value' => sprintf("[wlm_stripe_btn sku=%s]", $i));
				}

				$wlm_shortcodes[] = array('title' => 'Profile Page', 'value' => '[wlm_stripe_profile]');

				//var_dump($wlm_shortcodes);
				$WLMTinyMCEPluginInstanceOnly->RegisterShortcodes("Stripe Integration", $wlm_shortcodes, array());
			}

		}
		public function get_view_path($handle) {
			global $WishListMemberInstance;
			return sprintf($WishListMemberInstance->pluginDir .'/extlib/wlm_stripe/%s.php', $handle);
		}
		public function profile_form($user) {
			if(!current_user_can('manage_options')) {
				return;
			}

			$user_id = $user;
			if(is_object($user)) {
				$user_id = $user->ID;
			}

			global $WishListMemberInstance;
			global $pagenow;

			$stripeapikey         = trim($WishListMemberInstance->GetOption('stripeapikey'));
			$stripepublishablekey = trim($WishListMemberInstance->GetOption('stripepublishablekey'));

			if(empty($stripeapikey) && empty($stripeapikey)) {
				return;
			}

			if($pagenow == 'profile.php' || $pagenow == 'user-edit.php') {
				$stripe_cust_id = $WishListMemberInstance->Get_UserMeta($user_id, 'stripe_cust_id');
				include $this->get_view_path('stripe_user_profile');
			}
		}
		public function update_profile($user) {
			if(!current_user_can('manage_options')) {
				return;
			}

			$user_id = $user;
			if(is_object($user)) {
				$user_id = $user->ID;
			}

			global $WishListMemberInstance;
			if(isset($_POST['stripe_cust_id'])) {
				$WishListMemberInstance->Update_UserMeta($user_id, 'stripe_cust_id', trim($_POST['stripe_cust_id']));
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



		public function wlm_stripe_btn($atts, $content) {
			$form = new WLM_Stripe_Forms();
			return $form->generate_stripe_form($atts, $content);
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

		public function wlm_stripe_profile($atts) {
			global $WishListMemberInstance;
			global $current_user;

			$stripepublishablekey = $WishListMemberInstance->GetOption('stripepublishablekey');
			$stripethankyou = $WishListMemberInstance->GetOption('stripethankyou');
			$wpm_scregister = get_bloginfo('url') . '/index.php/register/';
			$stripethankyou_url = $wpm_scregister . $stripethankyou;

			if (empty($current_user->ID)) {
				return null;
			}

			wp_enqueue_style('wlm-stripe-profile-style', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/css/stripe-profile.css', '', $WishListMemberInstance->Version);
			wp_enqueue_style('stripe-paymenttag-style', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/css/stripe-paymenttag.css', '', $WishListMemberInstance->Version);
			wp_enqueue_script('stripe-paymenttag', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/stripe-paymenttag.js', array('jquery'), $WishListMemberInstance->Version, true);
			wp_enqueue_script('leanModal', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/jquery.leanModal.js', array('jquery'), $WishListMemberInstance->Version, true);
			wp_enqueue_script('wlm-stripe-profile', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/stripe.wlmprofile.js', array('stripe-paymenttag', 'leanModal'), $WishListMemberInstance->Version, true);

			//hook in late
			add_action('wp_footer', array($this, 'footer'));


			$levels = $WishListMemberInstance->GetMembershipLevels($current_user->ID, null, null, null, true);
			$wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');
			$user_posts	= $WishListMemberInstance->GetUser_PayPerPost("U-".$current_user->ID);



			$txnids = array();
			foreach ($wpm_levels as $id => $level) {
				$txn = $WishListMemberInstance->GetMembershipLevelsTxnID($current_user->ID, $id);
				if(empty($txn)) {
					continue;
				}
				$txnids[$id]['txn'] = $txn;
				$txnids[$id]['level'] = $level;
				$txnids[$id]['level_id'] = $id;
				$txnids[$id]['type'] = 'membership';

			}



			foreach($user_posts as $u) {
				$p = get_post($u->content_id);
				$id = 'payperpost-'.$u->content_id;
				$txn = $WishListMemberInstance->Get_ContentLevelMeta("U-".$current_user->ID, $u->content_id, 'transaction_id');
				$txnids[$id]['txn'] = $txn;
				$txnids[$id]['level_id'] = $id;
				$txnids[$id]['type'] = 'post';
				$txnids[$id]['level'] = array(
					'name' => $p->post_title
				);

			}





			$wlm_user = new WishListMemberUser($current_user->ID);
			ob_start();
			?>
			<?php if (isset($_GET['status'])): ?>
				<?php if (wlm_arrval($_GET,'status') == 'ok'): ?>
					<p><span class="stripe-success"><?php echo __("Profile Updated", "wishlist-member") ?></span></p>
				<?php else: ?>
					<span class="stripe-error"><?php echo __("Unable to update your profile, please try again", "wishlist-member") ?></span>
				<?php endif; ?>
			<?php endif; ?>
			<?php
			include $this->get_view_path('profile');
			$str = ob_get_clean();
			$str = preg_replace('/\s+/', ' ', $str);
			return $str;

		}
	}
}
if (!class_exists('WLM_Stripe_Forms')) {

	class WLM_Stripe_Forms {
		protected $forms;
		public function get_view_path($handle) {
			global $WishListMemberInstance;
			return sprintf($WishListMemberInstance->pluginDir .'/extlib/wlm_stripe/%s.php', $handle);
		}
		public function footer() {
			global $WishListMemberInstance;
			$stripepublishablekey = $WishListMemberInstance->GetOption('stripepublishablekey');
			?>
<script type="text/javascript">
	Stripe.setPublishableKey('<?php echo $stripepublishablekey ?>');
</script>
			<?php
			foreach($this->forms as $frm) {
				echo $frm;
			}
		}
		public function generate_stripe_form($atts, $content) {
			global $WishListMemberInstance;
			wp_enqueue_script('leanModal', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/jquery.leanModal.js', array('jquery'), $WishListMemberInstance->Version, true);
			wp_enqueue_script('stripe', 'https://js.stripe.com/v1/', array('leanModal'), '1.0.0');
			wp_enqueue_script('wlm-stripe-form-plugin', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/stripe.wlm.js', array('stripe'), $WishListMemberInstance->Version, true);
			wp_enqueue_script('wlm-stripe-form', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/js/stripe.wlmform.js', array('wlm-stripe-form-plugin'), $WishListMemberInstance->Version, true);
			wp_enqueue_style('wlm-stripe-form-style', $WishListMemberInstance->pluginURL.'/extlib/wlm_stripe/css/stripe-form.css', array(), $WishListMemberInstance->Version);
			//hook in late
			add_action('wp_footer', array($this, 'footer'), 100);

			global $current_user;
			extract(shortcode_atts(array(
						'sku' => null,
							), $atts));

			if (empty($sku)) {
				return null;
			}


			$stripeapikey = $WishListMemberInstance->GetOption('stripeapikey');
			$stripeconnections = $WishListMemberInstance->GetOption('stripeconnections');
			$stripethankyou = $WishListMemberInstance->GetOption('stripethankyou');
			$wpm_scregister = get_site_url() . '/index.php/register/';
			$stripethankyou_url = $wpm_scregister . $stripethankyou;
			$stripesettings = $WishListMemberInstance->GetOption('stripesettings');
			$wpm_levels = $WishListMemberInstance->GetOption('wpm_levels');
			$WishListMemberInstance->InjectPPPSettings($wpm_levels);


			$settings = $stripeconnections[$sku];


			$amt = $settings['amount'];
			$currency = empty($stripesettings['currency'])? 'USD' : $stripesettings['currency'];
			if ($settings['subscription']) {
				try {
					Stripe::setApiKey($stripeapikey);
					$plan = Stripe_Plan::retrieve($settings['plan']);
					$amt = number_format($plan->amount / 100, 2);
				} catch (Exception $e) {
					$msg = __("Error %s");
					return sprintf($msg, $e->getMessage());
				}
			}

			$ppp_level = $WishListMemberInstance->IsPPPLevel($sku);
			$level_name = $wpm_levels[$sku]['name'];

			if($ppp_level) {
				$level_name = $ppp_level->post_title;
			}


			$btn_label = empty($stripesettings['buttonlabel']) ? "Join %level" : $stripesettings['buttonlabel'];
			$btn_label = str_replace('%level', $level_name, $btn_label);

			$panel_btn_label = empty($stripesettings['panelbuttonlabel']) ? "Pay" : $stripesettings['panelbuttonlabel'];
			$panel_btn_label = str_replace('%level', $level_name, $panel_btn_label);
			$logo = $stripesettings['logo'];
			$logo = str_replace('%level', $level_name, $stripesettings['logo']);
			$content = trim($content);
			ob_start();
			?>
			<?php if (empty($content)) : ?>
				<button class="stripe-button go-stripe-signup" style="width: auto" id="go-stripe-signup-<?php echo $sku ?>" class="" href="#stripe-signup-<?php echo $sku ?>"><?php echo $btn_label ?></button>
			<?php else: ?>
				<a id="go-stripe-signup-<?php echo $sku ?>" class="go-stripe-signup" href="#stripe-signup-<?php echo $sku ?>"><?php echo $content ?></a>
			<?php endif; ?>

			<?php

			$btn = ob_get_clean();
			ob_start()

			?>
			<div id="stripe-signup-<?php echo $sku ?>" class="stripe-signup">
				<div class="stripe-signup-container">
					<div class="stripe-signup-header">
						<?php if (!empty($logo)): ?>
							<img class="stripe-logo" src="<?php echo $logo ?>"></img>
						<?php endif; ?>
						<h2>

							<?php $heading = empty($stripesettings['formheading']) ? "Register to %level" : $stripesettings['formheading'] ?>
							<?php echo str_replace('%level', $level_name, $heading) ?>
						</h2>

						<?php if(!is_user_logged_in()): ?>
						<p style="margin-bottom: 5px;">
							<?php echo __('Existing users please <a href="" class="stripe-open-login">login</a> before purchasing', "wishlist-member") ?>
						</p>
						<?php endif; ?>
						<a class="stripe-close" href="javascript:void(0);"></a>
					</div>


					<?php
					if(!is_user_logged_in()){
						include $this->get_view_path('form_new');
					} else {
						global $current_user;
						$stripe_cust_id = $WishListMemberInstance->Get_UserMeta($current_user->ID, 'stripe_cust_id');
						include $this->get_view_path('form_existing');
					}
					include $this->get_view_path('form_css');
					?>

				</div>
			</div>
			<?php
			$form = ob_get_clean();
			$form = preg_replace('/\s+/', ' ', $form);
			$this->forms[] = $form;
			return $btn;
		}

	}

}

$sc = new WLM_Stripe_ShortCodes();

?>
