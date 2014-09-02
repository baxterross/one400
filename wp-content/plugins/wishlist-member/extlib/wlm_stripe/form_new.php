<div class="stripe-error">
	<?php if (isset($_GET['status']) && $_GET['status'] == 'fail') echo __("An error has occured while processing payment, please try again", "wishlist-member") ?>
	<?php if (!empty($_GET['reason'])) echo '<br/><em>Reason: ' . strip_tags(wlm_arrval($_GET,'reason')) . '</em>' ?>
	<?php if (isset($_GET['status']) && $_GET['status'] == 'fail') echo sprintf(__("<br/>If you continue to have trouble registering, please contact <em><a style='color: red' href='mailto:%s'>%s</a></em>"), $stripesettings['supportemail'], $stripesettings['supportemail']) ?>
</div>

<div class="stripe-form-new">
	<form action="<?php echo $stripethankyou_url ?>" class="stripe-form" method="post">
	<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('stripe-do-charge') ?>"/>
	<input type="hidden" name="stripe_action" value="charge"/>
	<input type="hidden" name="charge_type" value="new"/>
	<input type="hidden" name="subscription" value="<?php echo $settings['subscription'] ?>"/>
	<input type="hidden" name="redirect_to" value="<?php echo get_permalink() ?>"/>
	<input type="hidden" name="sku" value="<?php echo $sku ?>"/>
	<div class="txt-fld">
		<label for="">First Name:</label>
		<input id="" class="stripe-field-first_name" name="first_name" type="text" />
	</div>
	<div class="txt-fld">
		<label for="">Last Name:</label>
		<input id="" class="stripe-field-last_name" name="last_name" type="text" />
	</div>
	<div class="txt-fld">
		<label for="">Email address:</label>
		<input id="" class="stripe-field-email" name="email" type="text" />
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
	<div class="btn-fld">
		<button class="stripe-button"><?php echo $panel_btn_label ?><span class="stripe-waiting">...</span> &nbsp;<?php echo $currency?> <?php echo $amt ?> </button>
	</div>
	</form>
</div>
<div class="stripe-login">
	<form method="post" action="<?php echo get_bloginfo('wpurl')?>/wp-login.php">
		<div class="txt-fld">
			<label for="">Username:</label>
			<input id="" class="stripe-field-username" name="log" type="text" />
		</div>
		<div class="txt-fld">
			<label for="">Password:</label>
			<input id="" class="stripe-field-password" name="pwd" type="password" />
		</div>
		<input type="hidden" name="wlm_redirect_to" value="<?php echo get_permalink()?>#<?php echo $sku ?>" />
		<div class="btn-fld">
			<div style="float: left; padding-left: 12px;"><a href="" class="stripe-close-login">Cancel</a></div>
			<button style="float: right" class="stripe-button"><?php echo __("Login", "wishlist-member")?></button>
		</div>
	</form>
</div>
