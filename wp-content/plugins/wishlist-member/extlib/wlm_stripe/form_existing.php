<?php
global $current_user;
$class = empty($stripe_cust_id)? 'stripe-form' : null;
?>
<form action="<?php echo $stripethankyou_url ?>" class="stripe-form-logged <?php echo $class?>" method="post">
	<div class="stripe-error">
		<?php if (isset($_GET['status']) && $_GET['status'] == 'fail') echo __("An error has occured while processing payment, please try again", "wishlist-member") ?>
		<?php if (!empty($_GET['reason'])) echo '<br/><em>Reason: ' . strip_tags(wlm_arrval($_GET,'reason')) . '</em>' ?>
		<?php if (isset($_GET['status']) && $_GET['status'] == 'fail') echo sprintf(__("<br/>If you continue to have trouble registering, please contact <em><a style='color: red' href='mailto:%s'>%s</a></em>"), $stripesettings['supportemail'], $stripesettings['supportemail']) ?>
	</div>
	<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('stripe-do-charge') ?>"/>
	<input type="hidden" name="stripe_action" value="charge"/>
	<input type="hidden" name="charge_type" value="existing"/>
	<input type="hidden" name="subscription" value="<?php echo $settings['subscription'] ?>"/>
	<input type="hidden" name="redirect_to" value="<?php echo get_permalink() ?>"/>
	<input type="hidden" name="sku" value="<?php echo $sku ?>"/>

	<?php if(empty($stripe_cust_id)): ?>
	<div class="txt-fld">
		<label for="">First Name:</label>
		<input id="" class="stripe-field-first_name" name="first_name" type="text" value="<?php echo $current_user->first_name;?>" />
	</div>
	<div class="txt-fld">
		<label for="">Last Name:</label>
		<input id="" class="stripe-field-last_name" name="last_name" type="text" value="<?php echo $current_user->last_name;?>"/>
	</div>
	<div class="txt-fld">
		<label for="">Email address:</label>
		<input id="" class="stripe-field-email" name="email" type="text" value="<?php echo $current_user->user_email;?>" />
	</div>
	<div class="txt-fld">
		<label for="">Card Number:</label>
		<input autocomplete="false" placeholder="●●●● ●●●● ●●●● ●●●●" class="stripe-field-cardnumber" type="text" />
	</div>
	<div class="widefield">
		<div class="expires">
			<label for="">Expires:</label>
			<input autocomplete="false" placeholder="MM" maxlength="2"  class="stripe-field-expmonth"  type="text" />
			<input autocomplete="false" placeholder="YY" maxlength="2"  class="stripe-field-expyear" type="text" />
		</div>

		<div class="code">
			<label for="">Card Code:</label>
			<input autocomplete="false" maxlength="4" placeholder="CVC" id="" class="stripe-field-cvc" type="text" />
		</div>
	</div>
	<?php endif; ?>
	<div class="btn-fld">
		<button class="stripe-button"><?php echo $panel_btn_label ?><span class="stripe-waiting">...</span> &nbsp;<?php echo $currency?> <?php echo $amt ?> </button>
	</div>
</form>