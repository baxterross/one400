<table>
	<thead>
		<tr>
			<th><?php echo __("ID", "wishlist-member") ?></th>
			<th><?php echo __("Date", "wishlist-member") ?></th>
			<th><?php echo __("Total", "wishlist-member") ?></th>
		</tr>
	</thead>
	<?php if (!empty($invoices)): ?>
		<?php foreach ($invoices as $i): ?>
			<?php if ($i['object'] == 'invoice'): ?>
				<tr>
					<td><a data-id="<?php echo $i['id'] ?>" class="stripe-invoice-detail" href="#stripe-invoice-detail"><?php echo $i['id'] ?></a></td>
					<td><?php echo date('M d, Y', $i['date']) ?></td>
					<td><?php echo strtoupper($i['currency'])?> <?php echo number_format($i['total'] / 100, 2) ?></td>
				</tr>
			<?php elseif ($i['object'] == 'charge'): ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<tr>
			<td colspan="3"><p style="text-align: center"><?php echo __("No previous invoices", "wishlist-member") ?></p></td>
		</tr>
	<?php endif; ?>
</table>
<p style="text-align: right; font-size: 11px;"><a href="#" class="stripe-invoices-close"><?php echo __("Close", "wishlist-member") ?></a></p>