<h3>Invoice Details</h3>
<table>
	<tr>
		<td>Invoice ID</td>
		<td><?php echo $inv->id ?></td>
	</tr>
	<tr>
		<td>Date</td>
		<td><?php echo date('M d Y', $inv->date) ?></td>
	</tr>
	<tr>
		<td>Customer</td>
		<td><?php echo $cust->description ?></td>
	</tr>
</table>
<h3>Summary</h3>
<table width="100%">
	<tr>
		<td width="50%"></td>
		<td>Subtotal: </td>
		<td><?php echo strtoupper($inv->currency)?> <strong><?php echo number_format($inv->subtotal / 100, 2); ?></strong></td>
	</tr>
	<tr>
		<td width="50%"></td>
		<td>Total: </td>
		<td><?php echo strtoupper($inv->currency)?> <strong><?php echo number_format($inv->total / 100, 2); ?></strong></td>
	</tr>
	<tr>
		<td width="50%"></td>
		<td><strong>Amount Due: </strong></td>
		<td><?php echo strtoupper($inv->currency)?> <strong><?php echo number_format($inv->total / 100, 2); ?></strong></td>
	</tr>
</table>
<h3>Line Items</h3>
<table width="100%">
	<?php foreach ($inv->lines->subscriptions as $s): ?>
		<tr>
			<td width="50%">
				<?php $plan = $s->plan ?>
				<?php echo strtoupper(($s->currency)) ?> <?php echo sprintf("%s (%s/%s)", $plan->name, number_format($plan->amount / 100, 2), $plan->interval) ?>
			</td>
			<td><?php echo sprintf("%s - %s", date("M d, Y", $s->period->start), date("M d, Y", $s->period->end)) ?></td>
			<td><?php echo strtoupper(($s->currency)) ?> <?php echo number_format($s->amount / 100, 2) ?></td>
		</tr>
	<?php endforeach; ?>
	<?php foreach ($inv->lines->invoiceitems as $s): ?>
		<tr>
			<td width="50%">
				<?php echo $s->description ?>
			</td>

			<td><?php echo date('M d, Y', $s->date) ?></td>
			<td><?php echo strtoupper(($s->currency))?> <?php echo number_format($s->amount / 100, 2) ?></td>
		</tr>
	<?php endforeach; ?>

	<?php foreach ($inv->lines->prorations as $s): ?>
		<tr>
			<td width="50%">
				<?php echo $s->description ?>
			</td>

			<td><?php echo date('M d, Y', $s->date) ?></td>
			<td><?php echo strtoupper(($s->currency))?> <?php echo number_format($s->amount / 100, 2) ?></td>
		</tr>
	<?php endforeach; ?>
</table>