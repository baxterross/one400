<?php
/*
 * View Broadcast message sent to members
 */

global $wpdb;

/* delete an email */
if (isset($_POST['delete'])) {
	$ids = implode(',', $_POST['wpm_broadcast_id']);
	if ($ids == "") {
		echo "<div class='error fade'>" . __('<p>No selection to be deleted.</p>', 'wishlist-member') . "</div>";
	} else {
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "wlm_emailbroadcast WHERE id IN ($ids)");
		$ids = explode(',', $ids);
		foreach ((array) $ids AS $id) {
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "options WHERE option_name LIKE '%" . $id . "wlmember\_email\_queue\_%'");
		}
		echo "<div class='updated fade'>" . __('<p>Email/s was deleted successfully.</p>', 'wishlist-member') . "</div>";
	}
/*Force send an email*/
} else if (isset($_POST['force_send'])) {
	$cnt_sent = $this->ForceSendMail();
	$msg_sent = ($cnt_sent == 1) ? "1 queued email was sent" : $cnt_sent . " queued emails were sent";
} else if (isset($_POST['pause'])) {
	$ids = implode(',', $_POST['wpm_broadcast_id']);
	if ($ids == "") {
		echo "<div class='error fade'>" . __('<p>No selection to be paused.</p>', 'wishlist-member') . "</div>";
	} else {
		$wpdb->query("UPDATE " . $wpdb->prefix . "wlm_emailbroadcast SET status='Paused' WHERE id IN ($ids)");
		$ids = explode(',', $ids);
		foreach ((array) $ids AS $id) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "options SET option_name=CONCAT('p',option_name) WHERE option_name LIKE '" . $id . "wlmember\_email\_queue\_%'");
		}
		echo "<div class='updated fade'>" . __('<p>Selected Queue was paused.</p>', 'wishlist-member') . "</div>";
	}
/*Queue an email*/
} else if (isset($_POST['queue'])) {
	$ids = implode(',', $_POST['wpm_broadcast_id']);
	if ($ids == "") {
		echo "<div class='error fade'>" . __('<p>No selection to be queued.</p>', 'wishlist-member') . "</div>";
	} else {
		$wpdb->query("UPDATE " . $wpdb->prefix . "wlm_emailbroadcast SET status='Queued' WHERE id IN ($ids)");
		$ids = explode(',', $ids);
		foreach ((array) $ids AS $id) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "options SET option_name=TRIM(LEADING 'p' FROM option_name) WHERE option_name LIKE 'p" . $id . "wlmember\_email\_queue\_%'");
		}
		echo "<div class='updated fade'>" . __('<p>Selection was Queued.</p>', 'wishlist-member') . "</div>";
	}
}
//get the number of emails in queue
$email_queue_count = $wpdb->get_results("SELECT option_name FROM " . $wpdb->prefix . "options WHERE option_name LIKE '%wlmember\_email\_queue\_%'");

/* variables for page numbers */
$pagenum = isset($_GET['pagenum']) ? absint(wlm_arrval($_GET,'pagenum')) : 0;
if (empty($pagenum)) $pagenum = 1;
$per_page = 20;
$start = ($pagenum == '' || $pagenum < 0) ? 0 : (($pagenum - 1) * $per_page);

$broadcast_emails = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wlm_emailbroadcast ORDER BY date_added DESC LIMIT " . $start . "," . $per_page);
$emails_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $this->Tables->emailbroadcast);

/* Prepare pagination */
$num_pages = ceil($emails_count / $per_page);
$page_links = paginate_links(array(
	'base' => add_query_arg('pagenum', '%#%'),
	'format' => '',
	'prev_text' => __('&laquo;'),
	'next_text' => __('&raquo;'),
	'total' => $num_pages,
	'current' => $pagenum
));

if (isset($_POST['logon'])) {
	$log = false;
	echo "<div class='updated fade'>" . __('<p>Broadcast Log is Disabled.</p>', 'wishlist-member') . "</div>";
	if (isset($_POST['clear_logs'])) {
		$ret = $this->LogEmailBroadcastActivity("==Empty==", true);
		echo "<div class='updated fade'>" . __('<p>Logs Cleared.</p>', 'wishlist-member') . "</div>";
	} else {
		$ret = $this->LogEmailBroadcastActivity("**Disabled**");
	}
	$this->DeleteOption('WLM_BroadcastLog');
} elseif (isset($_POST['logoff'])) {
	$log = true;
	$this->SaveOption('WLM_BroadcastLog', '1');
	$ret = $this->LogEmailBroadcastActivity("==Log Enabled==");
	if (!$ret) {
		echo "<div class='error fade'>" . __('<p>Error Creating/Opening Log File. Please check folder permission or manually create the file ' . ABSPATH . WLM_BACKUP_PATH . 'broadcast.txt </p>', 'wishlist-member') . "</div>";
		$this->DeleteOption('WLM_BroadcastLog');
		$log = false;
	} else {
		echo "<div class='updated fade'>" . __('<p>Broadcast Log is Enabled.</p>', 'wishlist-member') . "</div>";
	}
} else {
	if ($this->GetOption('WLM_BroadcastLog') == 1) {
		$log = true;
	} else {
		$log = false;
	}
}
?>
<h2>
	<?php _e('Members &raquo; Email Broadcast', 'wishlist-member'); ?>
	<a class="button button-primary" href="?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>&mode=sendbroadcast"><?php _e('Create Email Broadcast', 'wishlist_member') ?></a>
</h2>
<br>
<form id="posts-filter" action="?<?php echo $this->QueryString('usersearch', 'mode', 'level') ?>&mode=broadcast" method="post">
	<p class="search-box">
		&nbsp;&nbsp;<input type="submit" value="<?php echo $log ? 'Disable' : 'Enable'; ?> Broadcast Log" name="<?php echo $log ? 'logon' : 'logoff'; ?>" id="log" class="button-secondary action" />
		&nbsp;<?php echo $log ? '<input type="checkbox" name="clear_logs" value="1" /><label> Clear Logs</label>' : ''; ?>
	</p>
	<p>Emails in queue: <strong><?php echo count($email_queue_count) <= 0 ? '0' : count($email_queue_count); ?></strong>
		<?php if (count($email_queue_count) > 0 || isset($_POST['force_send'])) { ?>
			&nbsp;&nbsp;&nbsp;<input type="submit" value="Send Mails Left in Queue" name="force_send" id="force_send" class="button-secondary action" />&nbsp;&nbsp;<span style="color:#0000FF;"><?php echo $msg_sent; ?></span>
		<?php } ?>
		&nbsp;&nbsp; Last Queued Email Sent:
		<strong><?php
		$Queue_Sent = $this->GetOption('WLM_Last_Queue_Sent');
		echo ($Queue_Sent == '' ? '----' : $Queue_Sent);
		?></strong>
	</p>
	<?php if ($emails_count): /* Display  Pagination */  ?>
		<div class="tablenav"><div class="tablenav-pages"><?php
		$page_links_text = sprintf('<span class="displaying-num">' . __('Displaying %s&#8211;%s of %s') . '</span>%s', number_format_i18n(( $pagenum - 1 ) * $per_page + 1), number_format_i18n(min($pagenum * $per_page, $emails_count)), number_format_i18n($emails_count), $page_links
		);
		echo $page_links_text;
		?></div>
			<input type="submit" value="Delete Selected" name="delete" id="delete" class="button-secondary action" />
			<input type="submit" value="Pause Selected" name="pause" id="pause" class="button-secondary action" />
			<input type="submit" value="Queue Selected" name="queue" id="queue" class="button-secondary action" />
		</div>
	<?php endif; /* Pagination Ends here */ ?>
	<table class="widefat" id='wpm_broadcast'>
		<thead>
            <tr>
				<th  nowrap scope="col" class="check-column"><input type="checkbox" onClick="wpm_selectAll(this,'wpm_broadcast')" /></th>
				<th scope="col" class="num"><?php _e('Subject', 'wishlist-member'); ?></th>
				<th scope="col" class="num"><?php _e('Total Recipients', 'wishlist-member'); ?></th>
				<th scope="col" class="num"><?php _e('Queued/Sent/Failed', 'wishlist-member'); ?></th>
				<th scope="col" class="num"><?php _e('Send To', 'wishlist-member'); ?></th>
				<th scope="col" class="num"><?php _e('Sent As', 'wishlist-member'); ?></th>
				<th scope="col" class="num"><?php _e('Status', 'wishlist-member'); ?></th>
				<th scope="col" class="num"><?php _e('Date Sent', 'wishlist-member'); ?></th>
            </tr>
		</thead>
		<tbody>
		<?php foreach ($broadcast_emails AS $res): ?>
			<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>">
				<th scope="row" class="check-column"><input type="checkbox" name="wpm_broadcast_id[]" value="<?php echo $res->id ?>" /></th>
				<td>
					<a href="?page=WishListMember&wl=members&mode=sendbroadcast&id=<?php echo $res->id ?>"><?php echo cut_string($res->subject, 30, 4); ?></a>
				</td>
				<td class="num"><?php
					if (trim($res->recipients) != "") {
						$recipients = count(explode(',', $res->recipients));
					} else {
						$recipients = 0;
					}
					echo $recipients;
					?>
				</td>
				<td class="num"><?php
					if (trim($res->failed_address) != "") {
						$failed_address = count(explode(',', $res->failed_address));
					} else {
						$failed_address = 0;
					}
					$email_cnt = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "options WHERE option_name LIKE '%" . $res->id . "wlmember\_email\_queue\_%'");
					echo $email_cnt . ' / ' . '<span style="color:green;">' . ($recipients - $email_cnt - $failed_address) . '</span>' . ' / ' . '<span style="color:red;">' . $failed_address . '</span>';
					if ($failed_address > 0) {
						echo '<br /><a title="' . $res->failed_address . '" href="?page=WishListMember&wl=members&mode=sendbroadcast&action=requeue&id=' . $res->id . '">Requeue Failed</a>';
					}
					?>
				</td>
				<td class="num"><?php
					$lvl_id = explode('#', $res->mlevel);
					$em = "";
					foreach ((array) $lvl_id AS $id => $level) {
						if (isset($wpm_levels[$level])) {
							$em .= "<u>" . $wpm_levels[$level]["name"] . "</u>, ";
						}else if(strpos($level,"SaveSearch") !== false){
							$em .= "<u>" . $level . "</u>, ";
						}
					}
					echo substr($em, 0, -2);
					?>
				</td>
				<td class="num"><?php echo strtoupper($res->sent_as); ?></td>
				<td class="num">
					<?php
					if ($email_cnt <= 0) {
						echo '<span style="color:#000099">DONE</span>';
					} elseif ($res->status == 'Queued') {
						echo '<span style="color:#009900">' . strtoupper($res->status) . '</span>';
					} else {
						echo '<span style="color:#999999">' . strtoupper($res->status) . '</span>';
					}
					?>
				</td>
				<td class="num"><?php echo $res->date_added; ?></td>
			</tr >
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ($emails_count): /* Display  Pagination */ ?>
		<div class="tablenav"><div class="tablenav-pages"><?php
			$page_links_text = sprintf('<span class="displaying-num">' . __('Displaying %s&#8211;%s of %s') . '</span>%s', number_format_i18n(( $pagenum - 1 ) * $per_page + 1), number_format_i18n(min($pagenum * $per_page, $emails_count)), number_format_i18n($emails_count), $page_links
			);
			echo $page_links_text;
			?></div>
			<input type="submit" value="Delete Selected" name="delete" id="delete" class="button-secondary action" />
			<input type="submit" value="Pause Selected" name="pause" id="pause" class="button-secondary action" />
			<input type="submit" value="Queue Selected" name="queue" id="queue" class="button-secondary action" />
		</div>
	<?php endif; /* Pagination Ends here */ ?>
</form>

<?php
/* Cut the string */
function cut_string($str, $length, $minword) {
	$sub = '';
	$len = 0;
	foreach (explode(' ', $str) as $word) {
		$part = (($sub != '') ? ' ' : '') . $word;
		$sub .= $part;
		$len += strlen($part);
		if (strlen($word) > $minword && strlen($sub) >= $length)
			break;
	}
	return $sub . (($len < strlen($str)) ? '...' : '');
}
?>