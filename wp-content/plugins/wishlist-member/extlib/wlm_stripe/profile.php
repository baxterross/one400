<div id="stripe-invoice-detail">
	<div class="stripe-invoice-container">
		<div class="stripe-invoice-header">
			<h2>
				Invoice
			</h2>
			<a class="stripe-close" href="#"></a>
		</div>
		<span class="stripe-waiting">...</span>
		<div id="stripe-invoice-content"></div>
		<div style="float: right; padding-right: 10px;"><button class="stripe-button stripe-invoice-print">Print</button></div>
	</div>
</div>


<!-- fake frame for printing -->
<iframe id="print_frame" name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


<div id="stripe-membership-status">

	<table>
		<tr>
			<td style="text-align: left"><strong><?php echo __("Membership Status", "wishlist-member") ?></strong></td>
			<td style="text-align: right"><strong><a class="stripe_invoices"  href="<?php echo $stripethankyou_url ?>" data-id=""><?php echo __("View Past Invoices", "wishlist-member") ?> <span class="stripe-waiting">...</span></a></strong></td>
		</tr>
	</table>
	<div id="stripe-invoice-list"></div>

	<table>
		<thead>
			<tr>
				<th><?php echo __("Item", "wishlist-member") ?></th>
				<th><?php echo __("Status", "wishlist-member") ?></th>
				<th><?php echo __("Payment Info", "wishlist-member") ?></th>
				<th><?php echo __("Cancel", "wishlist-member") ?></th>
				<!--<th><?php echo __("Invoices", "wishlist-member") ?></th>-->
			</tr>
		</thead>
		<tbody>
			<?php foreach ($txnids as $txn): ?>
				<?php $level = $wlm_user->Levels[$txn['level_id']]; ?>
				<?php if (!empty($txn['txn'])): ?>
					<tr>
						<td><?php echo $txn['level']['name'] ?></td>
						<td>
						<?php if($txn['type'] == 'membership'): ?>
							<?php echo implode(',', $level->Status) ?>
						<?php else: ?>
							Active
						<?php endif; ?>

						</td>
						<td>
							<a href="#" class="update-payment-info"><?php echo __("Update Payment Info", 'wishlist-member') ?></a>
							<div id="update-stripe-info" class="update-stripe-info">
								<form method="post" action="<?php echo $stripethankyou_url ?>">
									<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('stripe-do-update_payment') ?>"/>
									<input type="hidden" name="stripe_action" value="update_payment"/>
									<input type="hidden" name="wlm_level" value="<?php echo $txn['level_id']?>"/>
									<input type="hidden" name="redirect_to" value="<?php echo get_permalink() ?>"/>
									<input type="hidden" name="txn_id" value="<?php echo $WishListMemberInstance->Get_UserMeta($current_user->ID, 'stripe_cust_id'); ?>"/>
									<payment data-name key="<?php echo $stripepublishablekey ?>"></payment>
									<p style="margin-top: 8px;"><input class="update-payment-info-cancel" type="submit" name="cancel" value="cancel"> <input type="submit" name="Submit" value="Save"/></p>
								</form>
							</div>
						</td>
						<td>
							<?php if($txn['type'] == 'membership'): ?>
								<?php if ($level->Active): ?>
									<form method="post" action="<?php echo $stripethankyou_url ?>">
										<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('stripe-do-cancel') ?>"/>
										<input type="hidden" name="stripe_action" value="cancel"/>
										<input type="hidden" name="wlm_level" value="<?php echo $txn['level_id']?>"/>
										<input type="hidden" name="redirect_to" value="<?php echo get_permalink() ?>"/>
										<input type="hidden" name="txn_id" value="<?php echo $txn['txn'] ?>"/>
										<input type="submit" class="stripe-cancel" name="Cancel" value="<?php echo __("Cancel Subscription", "wishlist-member") ?>"/>
									</form>
								<?php else: ?>
									<em>Inactive</em>
								<?php endif; ?>
							<?php else: ?>

							<?php endif; ?>
						</td>
						<!--<td><a href="#">View</a></td>-->
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
