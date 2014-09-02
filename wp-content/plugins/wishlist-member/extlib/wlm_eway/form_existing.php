<?php
global $current_user;
$class = empty($regform_cust_id)? 'regform-form' : null;

$regform_cust_id = 0;
?>
<div id="regform-<?php echo $sku ?>" class="regform">
	<div class="regform-container">
		<div class="regform-header">
			<?php if (!empty($logo)): ?>
				<img class="regform-logo" src="<?php echo $logo ?>"></img>
			<?php endif; ?>
			<h2>

				<?php $heading = empty($settings['formheading']) ? "Register to %level" : $settings['formheading'] ?>
				<?php echo str_replace('%level', $level_name, $heading) ?>
			</h2>

			<?php if(!is_user_logged_in()): ?>
			<p style="margin-bottom: 5px;">
				Existing users please <a href="" class="regform-open-login">login</a> before purchasing
			</p>
			<?php endif; ?>
			<a class="regform-close" href="javascript:void(0)"></a>
		</div>


		<div class="regform-error">
			<?php if (isset($_GET['status']) && $_GET['status'] == 'fail') echo __("An error has occured while processing payment, please try again", "wishlist-member") ?>
			<?php if (!empty($_GET['reason'])) echo '<br/><em>Reason: ' . strip_tags(wlm_arrval($_GET,'reason')) . '</em>' ?>
		</div>
		<form action="<?php echo $thankyouurl ?>" class="regform-logged <?php echo $class?>" method="post">
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('regform-do-charge') ?>"/>
			<input type="hidden" name="regform_action" value="charge"/>
			<input type="hidden" name="charge_type" value="existing"/>
			<input type="hidden" name="subscription" value="<?php echo $settings['subscription'] ?>"/>
			<input type="hidden" name="redirect_to" value="<?php echo get_permalink() ?>"/>
			<input type="hidden" name="sku" value="<?php echo $sku ?>"/>

			<div class="txt-fld" style="display:none">
				<label for="">First Name:</label>
				<input id="" class="regform-first_name" name="first_name" type="text" value="<?php echo $current_user->first_name;?>" />
			</div>
			<div class="txt-fld"  style="display:none">
				<label for="">Last Name:</label>
				<input id="" class="regform-last_name" name="last_name" type="text" value="<?php echo $current_user->last_name;?>"/>
			</div>
			<div class="txt-fld"  style="display:none">
				<label for="">Email address:</label>
				<input id="" class="regform-email" name="email" type="text" value="<?php echo $current_user->user_email;?>" />
			</div>
			<div class="txt-fld">
				<label for="">Card Number:</label>
				<input autocomplete="false" placeholder="●●●● ●●●● ●●●● ●●●●" class="regform-cardnumber" name="cc_number" type="text" />
			</div>
			<div class="widefield">
				<div class="expires">
					<label for="">Expires:</label>
					<input autocomplete="false" placeholder="MM" maxlength="2"  class="regform-expmonth"  name="cc_expmonth" type="text" />
					<input autocomplete="false" placeholder="YY" maxlength="2"  class="regform-expyear" name="cc_expyear" type="text" />
				</div>

				<div class="code" style="display:none;">
					<label for="">Card Code:</label>
					<input autocomplete="false" maxlength="4" placeholder="CVC" id="" class="regform-cvc" name="cc_cvc" type="text" />
				</div>
			</div>
			<div class="btn-fld">
				<button class="regform-button"><?php echo $panel_btn_label ?><span class="regform-waiting">...</span> &nbsp;<?php echo $currency?> <?php echo $amt ?> </button>
			</div>
		</form>
	</div>
</div>