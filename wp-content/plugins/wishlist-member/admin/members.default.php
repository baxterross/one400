<?php if (wlm_arrval($_POST, 'WishListMemberAction') != 'WPMRegister' && !wlm_arrval($_POST, 'err')): ?>
	<?php
	$wpdb = &$GLOBALS['wpdb'];
	require_once($this->pluginDir . '/core/UserSearch.php');
	$usersearch = stripslashes(wlm_arrval($_GET, 'usersearch'));

	if (isset($_POST['advance_usersearch'])) {
		$usersearch = stripslashes(wlm_arrval($_POST, 'advance_usersearch'));
	}
	// sorting
	
	$sort_request = wlm_arrval($_GET, 's');
	
	if(empty($sort_request)) {
		$sort_request = 'r;d';
	}
	
	list($sort_request, $sortorder) = explode(';', $sort_request);
	
	switch ($sort_request) {
		case 'n':
			$sortby = 'display_name';
			break;
		case 'u':
			$sortby = 'user_login';
			break;
		case 'e':
			$sortby = 'user_email';
			break;
		case 'r':
			$sortby = 'user_registered';
			break;
		default:
			$sortby = '';
	}

	if ($sortorder != 'd'){
		$sortorder = 'a';
	}
	
	$sortorderflip = ($sortorder == 'd') ? 'a' : 'd';
	
	$sortord = $sortorder == 'd' ? 'DESC' : 'ASC';

	// grouping
	$lvl = $_GET['level'];
	if (!$lvl)
		$lvl = '%';
	switch ($lvl) {
		case 'nonmembers':
			$ids = array('-');
			$ids = array_merge($ids, $this->MemberIDs());
			break;

		case 'incomplete':
			$ids = $wpdb->get_col("SELECT `ID` FROM `{$wpdb->users}` WHERE `user_login` LIKE 'temp\_%' AND `user_login`=`user_email`");
			break;

		default:
			if ($lvl != '%') {
				$ids = $this->MemberIDs($lvl);
			} else {
				$ids = '';
			}
	}
	//Filter by Status
	$status = isset($_POST['filter_status']) ? $_POST['filter_status'] : false;
	if ($status) {
		switch ($status) {
			case "cancelled":
				$ids = $this->MemberIDsByStatus($status);
				break;
			case "unconfirmed":
				$ids = $this->MemberIDsByStatus($status);
				break;
			case "forapproval":
				$ids = $this->MemberIDsByStatus($status);
				break;
			case "expired":
				//Get ID's of users that have expired levels
				$expired_user_ids = array();
				$m_levels = WishListMember_Level::GetAllLevels();
				$expiredmembers = $this->ExpiredMembersID();
				foreach($m_levels as $m_level) {
					// If client is searching by expirattion dates
					if(isset($_POST['filter_dates']) && ($_POST['filter_dates'] ==  'expiration_date')) {
						
						//if either from or to dates are empty, return no ids
						if(($_POST['from_date'] == '') || ($_POST['to_date'] == '')) {
							$expired_user_ids = '';
						} else {
							
							$expired_ts_from = strtotime($_POST['from_date']);
							$expired_ts_to = strtotime($_POST['to_date']);
							
							foreach($expiredmembers[$m_level] as $id) {
								$expired_ts = $this->LevelExpireDate($m_level, $id);
							
								if(($expired_ts >= $expired_ts_from) && ($expired_ts <= $expired_ts_to)) {
									$expired_user_ids = array_merge($expired_user_ids, (array) $id);
								}
							}
						}
					} else {
						$expired_user_ids = array_merge($expired_user_ids, $expiredmembers[$m_level]);
					}
				}
				
				if(!empty($expired_user_ids)) 
					$ids = array_unique($expired_user_ids);
				else 
					$ids = array();

				break;
			default:
				$ids = '';
				break;
		}
	}

	// Filter by Sequential Status
	$sequential_filter = isset($_POST['filter_sequential']) ? $_POST['filter_sequential'] : false;
	if ($sequential_filter) {
		$filter = $sequential_filter == 'on' ? 1 : 0;
		$ids = $wpdb->get_col("SELECT user_id FROM {$this->Tables->user_options} WHERE option_name = 'sequential' AND option_value = {$filter} ");
	}

	//Filter by Date Ranges
	$date_meta = (isset($_POST['filter_dates']) && ($_POST['filter_dates'] != 'expiration_date')) ? $_POST['filter_dates'] : false;
	if ($date_meta) {
		$ids = $this->GetMembersIDByDateRange($date_meta, $_POST['from_date'], $_POST['to_date']);
	}

	$incomplete_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->users}` WHERE `user_login` LIKE 'temp\_%' AND `user_login`=`user_email`");

	$howmany = $this->GetOption('member_page_pagination');
	$show_latest_reg = $this->GetOption('show_latest_reg');
	if (is_numeric(wlm_arrval($_GET, 'howmany')) || !$howmany) {
		if (wlm_arrval($_GET, 'howmany')) {
			$howmany = (int) $_GET['howmany'];
		}
		if (!$howmany)
			$howmany = 15;
		$this->SaveOption('member_page_pagination', $howmany);
	}
	if (isset($_GET['show_latest_reg'])) {
		$show_latest_reg = $_GET['show_latest_reg'];
		$show_latest_reg = (strtolower($show_latest_reg) == 'checked') ?
				1 : 0;
		$this->SaveOption('show_latest_reg', $show_latest_reg);
	}
	$show_latest_reg_class = ($show_latest_reg) ? "wlm_hide_previous_levels" : "";

	//Check if user wants to save the search
	$save_search = isset($_POST['save_search']) ? $_POST['save_search'] : false;

	if ($save_search) {
		$to_insert = array('search_term' => $usersearch, 'offset' => $_GET['offset'], 'ids' => $ids, 'sortby' => $sortby, 'sortorder' => $sortord, 'howmany' => $howmany);
		$data = array(
			'option_name' => 'SaveSearch - ' . $_POST['save_searchname'],
			'option_value' => maybe_serialize($to_insert)
		);
		$wpdb->insert($this->Tables->options, $data);
	}

	//Check and Query saved searches
	if (isset($_GET['saved_search']) && wlm_arrval($_GET, 'saved_search') != '') {
		$results = $this->GetSavedSearch(wlm_arrval($_GET, 'saved_search'));
		$usersearch = $results[0]["search_term"];
		$_GET['offset'] = $results[0]["offset"];
		$ids = $results[0]["ids"];
		$sortby = $results[0]["sortby"];
		$sortord = $results[0]["sortord"];
		$howmany = $results[0]["howmany"];
	}

	$wp_user_search = new WishListMemberUserSearch($usersearch, $_GET['offset'], '', $ids, $sortby, $sortord, $howmany);
	// pagination
	$offset = $_GET['offset'] - 1;
	if ($offset < 0)
		$offset = 0;
	$perpage = $wp_user_search->users_per_page;  // posts per page
	$offset = $offset * $perpage;
	$page_links = paginate_links(array(
		'base' => add_query_arg('offset', '%#%'),
		'format' => '',
		'total' => ceil($wp_user_search->total_users_for_query / $perpage),
		'current' => $offset / $perpage + 1
	));

	// > Shopping Cart Generic API
	// generic cart secret key
	$genericsecret = $this->GetOption('genericsecret');
	if (!$genericsecret)
		$this->SaveOption('genericsecret', $genericsecret = $this->PassGen() . $this->PassGen());
	// Shopping Cart Generic API <

	$this->Preload_UserLevelsMeta($wp_user_search->results);

	$manage_content_url = $this->GetMenu('managecontent')->URL;
	?>
	<style type="text/css">
		.wlm_hide_previous_levels ul li{ display: none; }
		.wlm_hide_previous_levels ul li.first_level{ display: list-item; }
		.advance-search {
			text-decoration: none;
			font-size: 12px;
			border: 0 none;
			font-family: sans-serif;
			float: left;
		}
	</style>
	<script type="text/javascript">

		jQuery(document).ready(function($) {
			jQuery("#datepicker, #dp_add_level, #dp_move_level, #dp_remove_level, #to_date, #from_date").datepicker();
			jQuery('#wpm_payperposts_to').select2({
				allowClear: true,
				ajax: {
					type: 'POST',
					url: ajaxurl,
					dataType: "jsonp",
					quietMillis: 100,
					data: function(term, page) {
						return {
							action: 'wlm_payperpost_search',
							search: '%' + term + '%',
							page: page,
							page_limit: 15
						}
					},
					results: function(data, page) {
						var more = (page * 15) < data.total;
						return {results: data.posts, more: more};
					}

				},
				formatResult: function(data) {
					return data.post_title;
				},
				formatSelection: function(data) {
					return data.post_title;
				},
				id: function(data) {
					return data.ID;
				}
			});

			jQuery("#filter_dates").change(function() {
				if ($(this).attr('selected', true).val() != '') {
					jQuery("#date_ranges").show("fast");
				} else {
					jQuery("#date_ranges").hide("fast");
				}
			});

			jQuery("#save_search").click(function() {
				jQuery("#save_searchname").toggle(this.checked);
			});
			function collapsify_levels() {
				o = this;
				o.t = $('.collapse-levels');
				o.el = $('ul.subsubsub');
				o.max_h = 46;

				o.toggle = function() {
					el = o.el;
					t = o.t;

					if (t.attr('rel') == 'show') {
						t.html('[-] collapse');
						t.attr('rel', 'hide');
						el.css('height', '100%');
					} else {
						t.html('[+] expand');
						t.attr('rel', 'show');
						el.css('height', o.max_h).css('overflow', 'hidden');
					}
				}
				o.t.click(function() {
					o.toggle();
					return false;
				});

				o.init = function() {
					if (o.el.height() > o.max_h) {
						o.t.show();
						o.t.click();
					} else {
						o.t.hide();
					}
				}
				//handle a resize event
				$(window).resize(function() {
					/**
					 * Resize the list to 100%
					 * So that we will get an accurate
					 * height later on
					 */
					o.el.css('height', '100%');
					o.init();
				});
				o.init();
			}
			collapsify_levels();

			$('#update-filters').click(function() {
				var q = '&howmany=' + $('select[name=howmany]').val() + '&show_latest_reg=' + $('input[name=show_latest_reg]').attr('checked')
				var url = "?<?php echo $this->QueryString('show_latest_reg', 'howmany'); ?>" + q;
				window.location.href = url;
				return false;

			});

			// Delete current user's save search
			var selected_search = $('#save-search').has('[selected]');
			if(selected_search.val() !== undefined){
				$('#remove-save-search').show();
			}

			$('#remove-save-search').on('click', function(e) {
				e.preventDefault();
				var selected_item = selected_search.val();
				if(confirm('Are you sure you want to delete the selected saved search?')){
					$('#save-search :selected').remove();
					$.post(ajaxurl, {option_name: selected_item, action:'wlm_delete_saved_search'}, function(){
					})
					.done(function() {
						$('#save-search').val('');
						$('#remove-save-search').hide();
					})
				}
			});
		});
	</script>
	<form onsubmit="document.location = '?<?php echo $this->QueryString('usersearch', 'offset'); ?>&usersearch=' + this.usersearch.value;
				return false;">
		<p class="search-box" style="margin-top:1em">
			<label for="post-search-input" class="hidden"><?php _e('Search Users:', 'wishlist-member'); ?></label>
			<input type="text" value="<?php echo esc_attr(stripslashes(wlm_arrval($_GET, 'usersearch'))) ?>" name="usersearch" id="post-search-input" placeholder="<?php _e('Search Members', 'wishlist-member'); ?>" onchange="jQuery('#advanced_search_field').val(this.value)" />
			<input type="submit" class="button-secondary" value="<?php _e('Search', 'wishlist-member'); ?>" />

			<a class="thickbox button" title="<?php _e('Advanced Members Search', 'wishlist-member'); ?>" href="#TB_inline?width=200&amp;inlineId=AdvanceSearchPopup"><?php _e('Advanced Search', 'wishlist-member'); ?></a>
			<?php if ($this->GetAllSavedSearch()): ?>
				&nbsp;&nbsp;
				<select id="save-search" onchange="top.location = '?<?php echo $this->QueryString('saved_search') ?>&saved_search=' + this.value">
					<option value="">Saved Searches</option>
					<?php foreach ($this->GetAllSavedSearch() as $value): ?>
						<option value="<?php echo $value['name']; ?>" <?php if (wlm_arrval($_GET, 'saved_search') == $value['name']) echo " selected='true'"; ?>><?php echo $value['name'] ?></option>
					<?php endforeach; ?>
				</select>
				<a href="#" id="remove-save-search" style="display:none;" title="<?php _e('Delete Saved Search','wishlist-member'); ?>"><i class="icon-remove icon-large"></i>
			<?php endif; ?>
		</p>
	</form>
	<h2><?php _e('Members &raquo; Manage Members', 'wishlist-member'); ?>
		<?php if (count($wpm_levels)) : ?> <a href="#TB_inline?height=440&amp;width=400&amp;inlineId=NewMemberPopup" class="add-new-h2 thickbox"><?php _e('Add New Member', 'wishlist-member'); ?></a><?php echo $this->tooltip("members-default-tooltips-add-new-member"); ?><?php endif; ?>
	</h2> 
	<?php if ($page_links) echo '<div style="display:block;float:right;line-height:2em;margin:7px 0 0 30px;">' . $page_links . '</div>'; ?>

	<ul class="subsubsub" style="white-space:normal;">
		<li><a href="?<?php echo $this->QueryString('usersearch', 'level', 'mode', 'offset') ?>&level=nonmembers"<?php echo $lvl == 'nonmembers' ? ' class="current"' : ''; ?>><?php _e('Non-Members', 'wishlist-member'); ?> (<?php echo $this->NonMemberCount(); ?>)</a></li>
		<li> | <a href="?<?php echo $this->QueryString('usersearch', 'level', 'mode', 'offset') ?>&level=incomplete"<?php echo $lvl == 'incomplete' ? ' class="current"' : ''; ?>><?php _e('Incomplete Registrations', 'wishlist-member'); ?> (<?php echo $incomplete_count; ?>)</a></li>
		<li> |
			<select onchange="top.location = '?<?php echo $this->QueryString('usersearch', 'level', 'mode', 'offset') ?>&level=' + this.value">
				<option value="">All Users</option>
				<?php foreach ((array) $wpm_levels AS $id => $level): ?>
					<option value="<?php echo $id; ?>" <?php if (wlm_arrval($_GET, 'level') == $id) echo " selected='true'"; ?>><?php echo $level['name'] ?> (<?php echo (int) $level['count'] ?>)</option>
				<?php endforeach; ?>
			</select>
		</li> 
	</ul>

	<form method="post" action="?<?php echo $this->QueryString('msg'); ?>">
		<div class="tablenav">
			<div style="display:block;float:right;line-height:2em">
				<input type="checkbox" name="show_latest_reg" value="1" <?php if ($show_latest_reg) echo 'checked="checked"' ?>/> &nbsp;Show Only Latest Level&nbsp;&nbsp;
				Display&nbsp;
				<select name="howmany">
					<option <?php if ($howmany == 15) echo 'selected="true"'; ?>>15</option>
					<option <?php if ($howmany == 30) echo 'selected="true"'; ?>>30</option>
					<option <?php if ($howmany == 50) echo 'selected="true"'; ?>>50</option>
					<option <?php if ($howmany == 100) echo 'selected="true"'; ?>>100</option>
					<option <?php if ($howmany == 200) echo 'selected="true"'; ?>>200</option>
				</select>
				&nbsp;Rows per Page
				&nbsp;&nbsp;<button href="#" id="update-filters" class="button-secondary" xstyle="display:inline">Update</button>

			</div>

			<select name="wpm_action" onchange="wpm_showHideLevels(this)">
				<option>-- Select an Action --</option>
				<option value="wpm_change_membership">Move to Level</option>
				<option value="wpm_add_membership">Add to Level</option>
				<option value="wpm_del_membership">Remove from Level</option>
				<option disabled="disabled">------</option>
				<option value="wpm_add_payperposts">Add Pay Per Post</option>
				<option value="wpm_del_payperposts">Remove Pay Per Post</option>
				<option disabled="disabled">------</option>
				<option value="wpm_cancel_membership">Cancel from Level</option>
				<option value="wpm_uncancel_membership">UnCancel from Level</option>
				<option disabled="disabled">------</option>
				<option value="wpm_confirm_membership">Confirm Subscription to Level</option>
				<option value="wpm_unconfirm_membership">Unconfirm Subscription to Level</option>
				<option disabled="disabled">------</option>
				<option value="wpm_approve_membership">Approve Registration to Level</option>
				<option value="wpm_unapprove_membership">UnApprove Registration to Level</option>
				<option disabled="disabled">------</option>
				<option value="wpm_enable_sequential">Turn ON Sequential Upgrade</option>
				<option value="wpm_disable_sequential">Turn OFF Sequential Upgrade</option>
				<option disabled="disabled">------</option>
				<option value="wpm_delete_member">Delete Selected Users</option>
			</select> <?php echo $this->Tooltip("members-default-tooltips-Select-an-Action"); ?>

			<span id="levels" style="display:none" class="wpm_action_options">
				<select class="postform" name="wpm_membership_to" style="width:80px">
					<option value="-"><?php _e('Levels...', 'wishlist-member'); ?></option>
					<?php foreach ((array) $wpm_levels AS $id => $level): ?>
						<option value="<?php echo $id ?>"><?php echo $level['name'] ?></option>
					<?php endforeach; ?>
				</select>
			</span>
			<span id="wpm_payperposts" style="display:none" class="wpm_action_options">
				<input type="hidden" class="postform" name="wpm_payperposts_to" id="wpm_payperposts_to" style="width:200px" data-placeholder="Choose Post or Page">
				&nbsp;
			</span>
			<span id="cancel_date" style="display:none" class="wpm_action_options">
				<input style="font-size:small;" type="text" id="datepicker" name="cancel_date" value="##/##/####" size="10">
				<?php echo $this->Tooltip("members-default-tooltips-cancelation-date"); ?>
			</span>
			<span id="add_to_date" style="display:none" class="wpm_action_options">
				<input style="font-size:small;" type="text" id="dp_add_level" name="dp_add_level" value="##/##/####" size="10">
				<?php echo $this->Tooltip("members-default-tooltips-add-level-date"); ?>
			</span>
			<span id="remove_to_date" style="display:none" class="wpm_action_options">
				<input style="font-size:small;" type="text" id="dp_move_level" name="dp_remove_level" value="##/##/####" size="10">
				<?php echo $this->Tooltip("members-default-tooltips-move-level-date"); ?>
			</span>
			<span id="move_to_date" style="display:none" class="wpm_action_options">
				<input style="font-size:small;" type="text" id="dp_remove_level" name="dp_move_level" value="##/##/####" size="10">
				<?php echo $this->Tooltip("members-default-tooltips-remove-level-date"); ?>
			</span>
			<input class="button-secondary" type="button" value="Go" onclick="wpm_doConfirm(this.form)" /> <?php echo $this->Tooltip("members-default-tooltips-go"); ?>
		</div>
		<table class="widefat" id='wpm_members'>
			<thead>
				<tr>
					<th  nowrap scope="col" class="check-column" style="white-space:nowrap">
						<input type="checkbox" onclick="wpm_selectAll(this, 'wpm_members')" />
						<?php echo $this->Tooltip("members-default-tooltips-select-user-checkbox"); ?>
					</th>
					<th scope="col"><a class="wpm_header_link<?php echo $sort_request == 'n' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=n<?php echo $sort_request == 'n' ? ';' . $sortorderflip : ''; ?>"><?php _e('Name', 'wishlist-member'); ?></a></th>
					<th scope="col"><a class="wpm_header_link<?php echo $sort_request == 'u' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=u<?php echo $sort_request == 'u' ? ';' . $sortorderflip : ''; ?>"><?php _e('Username', 'wishlist-member'); ?></a></th>
					<th scope="col"><a class="wpm_header_link<?php echo $sort_request == 'e' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=e<?php echo $sort_request == 'e' ? ';' . $sortorderflip : ''; ?>"><?php _e('Email', 'wishlist-member'); ?></a></th>
					<th scope="col" class="num"><?php _e('Subscribed', 'wishlist-member'); ?></th>
					<th scope="col" class="num"><?php _e('Seq.', 'wishlist-member'); ?></th>
					<th scope="col" class="num"><?php _e('User Posts', 'wishlist-member'); ?></th>
					<th scope="col" class="num"><?php _e('Levels', 'wishlist-member'); ?></th>
					<th scope="col" class="num" colspan="5">
						<?php _e('Status', 'wishlist-member'); ?>
						<?php echo $this->Tooltip("members-default-tooltips-status-column"); ?>
					</th>
					<th scope="col" class="num"><?php _e('Cancel', 'wishlist-member'); ?></th>
					<th scope="col" class="num"><?php _e('Expiration', 'wishlist-member'); ?></th>
					<th scope="col" class="num"><a class="wpm_header_link<?php echo $sort_request == 'r' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=r<?php echo $sort_request == 'r' ? ';' . $sortorderflip : ''; ?>"><?php _e('Registered', 'wishlist-member'); ?></a></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ((array) $wp_user_search->results AS $uid): $user = $this->Get_UserData($uid); ?>
					<?php
					/*
					 * WP 2.8 Change
					 * We no longer check for user_email in WordPress 2.8 because WP 2.8's sanitize_email won't save our temp email format
					 * frickin WP 2.8!!!  Oh well, it's for the better anyway...
					 * But since we use the same string for emails and usernames when creating temporary accounts,
					 * we'll just use user_login instead
					 */
					$tempuser = substr($user->user_login, 0, 5) == 'temp_' && $user->user_login == 'temp_' . md5($user->wlm_origemail);
					$xemail = $tempuser ? $user->wlm_origemail : $user->user_email;

					$wlUser = new WishListMemberUser($user->ID);
					wlm_add_metadata($wlUser->Levels);
					?>
					<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?> member-row">
						<th scope="row" class="check-column"><input type="checkbox" name="wpm_member_id[]" value="<?php echo $user->ID ?>" /></th>
						<td><?php echo $user->display_name ?></td>
						<td>
							<?php if ($tempuser): ?>
								<?php _e('Incomplete Registration', 'wishlist-member'); ?><br /><a href="<?php echo $this->GetContinueRegistrationURL($xemail); ?>"><?php _e('Click here to complete.', 'wishlist-member'); ?></a>
							<?php else: ?>
								<strong><a href="<?php echo get_bloginfo('wpurl') ?>/wp-admin/user-edit.php?user_id=<?php echo $user->ID ?>&wp_http_referer=wlm"><?php echo $user->user_login ?></a></strong>
							<?php endif; ?>
						</td>
						<td><a href="mailto:<?php echo $xemail ?>"><?php echo $xemail ?></a></td>
						<td class="num"><?php echo $user->wlm_unsubscribe == 1 ? __('No', 'wishlist-member') : __('Yes', 'wishlist-member'); ?></td>
						<td class="num"><?php echo $wlUser->Sequential ? __('On', 'wishlist-member') : __('Off', 'wishlist-member') ?></td>
						<td class="num"><a href="<?php echo $manage_content_url; ?>&level=U-<?php echo $user->ID; ?>"><?php echo $this->CountUserPosts($user->ID); ?></a></td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>"><?php echo $level->Name ?></li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>"><?php
										if ($level->Active)
											echo 'A';
										else
											echo '-'
											?></li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>"><?php
										if ($level->UnConfirmed)
											echo 'U';
										else
											echo '-'
											?></li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>"><?php
										if ($level->Pending == 1)
											echo 'N'; elseif ($level->Pending && !is_int($level->Pending))
											echo 'N: (' . $level->Pending . ')';
										else
											echo '-'
											?></li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>"><?php
										if (($level->Cancelled) || ($level->SequentialCancelled))
											echo 'C';
										else
											echo '-'
											?></li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>">
										<?php
										if ($level->Expired)
											echo 'E';
										else
											echo '-'
											?>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>">
										<?php
										if ($level->CancelDate == false)
											echo '-';
										else
											echo date_i18n('m/d/y', $level->CancelDate + $this->GMT)
											?>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>">
										<?php
										if ($level->ExpiryDate == false)
											echo '-';
										else
											echo date_i18n('m/d/y', $level->ExpiryDate + $this->GMT)
											?>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td class="num <?php echo $show_latest_reg_class ?>">
							<ul style="margin:0">
								<?php foreach ($wlUser->Levels AS $level): ?>
									<li class="<?php if ($level->is_latest_registration) echo "first_level"; ?>">
										<?php
										if ($level->Timestamp == false)
											echo '-';
										else
											echo date_i18n('m/d/y', $level->Timestamp + $this->GMT)
											?>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<input type="hidden" name="WishListMemberAction" value="SaveMembersData" />
	</form>
<?php endif; ?>
<br />
<?php if (count($wpm_levels)): ?>
	<?php if ($_POST) extract($_POST); ?>
	<div id="NewMemberPopup" <?php if (wlm_arrval($_POST, 'WishListMemberAction') != 'WPMRegister') echo'style="display:none"'; ?>>
		<div class="popupWindow">
			<h3><?php _e('Add New Member', 'wishlist-member'); ?> <?php echo $this->Tooltip("members-default-tooltips-Add-New-Member-form"); ?></h3>


			<form method="post" action="?<?php echo $this->QueryString(); ?>">
				<input type="hidden" name="WishListMemberAction" value="WPMRegister" />
				<table class="describe">
					<tr valign="top">
						<th scope="col" class="label" style="width:130px">
							<span class="alignleft"><label><?php _e('Username', 'wishlist-member'); ?></label></span>
							<span class="alignright"><abbr title="required" class="required">*</abbr></span>
						</th>
						<td class="field"><input type="text" name="username" value="<?php echo $username ?>" style="width:50% !important" <?php echo 'autocomplete="off"'; ?> /></td>
					</tr>
					<tr valign="top">
						<th scope="col" class="label">
							<span class="alignleft"><label><?php _e('First Name', 'wishlist-member'); ?></label></span>
							<span class="alignright"><abbr title="required" class="required">*</abbr></span>
						</th>
						<td class="field"><input type="text" name="firstname" value="<?php echo $firstname ?>" style="width:75% !important" <?php echo 'autocomplete="off"'; ?> /></td>
					</tr>
					<tr valign="top">
						<th scope="col" class="label">
							<span class="alignleft"><label><?php _e('Last Name', 'wishlist-member'); ?></label></span>
							<span class="alignright"><abbr title="required" class="required">*</abbr></span>
						</th>
						<td class="field"><input type="text" name="lastname" value="<?php echo $lastname ?>" style="width:75% !important" <?php echo 'autocomplete="off"'; ?> /></td>
					</tr>
					<tr valign="top">
						<th scope="col" class="label">
							<span class="alignleft"><label><?php _e('Email', 'wishlist-member'); ?></label></span>
							<span class="alignright"><abbr title="required" class="required">*</abbr></span>
						</th>
						<td class="field"><input type="text" name="email" value="<?php echo $email ?>" style="width:100% !important" <?php echo 'autocomplete="off"'; ?> /></td>
					</tr>
					<tr valign="top">
						<th scope="col" class="label">
							<span class="alignleft"><label><?php _e('Password (twice)', 'wishlist-member'); ?></label></span>
							<span class="alignright"><abbr title="required" class="required">*</abbr></span>
						</th>
						<td class="field"><input type="password" name="password1" style="width:50% !important" <?php echo 'autocomplete="off"'; ?> /><br /><input type="password" name="password2" style="width:50% !important" <?php echo 'autocomplete="off"'; ?> /></td>
					</tr>
					<tr valign="top">
						<th scope="col" class="label">
							<span class="alignleft"><label><?php _e('Membership Level', 'wishlist-member'); ?></label></span>
							<span class="alignright"><abbr title="required" class="required">*</abbr></span>
						</th>
						<td class="field">
							<select name="wpm_id" style="width:50%">
								<?php foreach ((array) $wpm_levels AS $id => $level): ?>
									<option value="<?php echo $id ?>"<?php echo ($id == $wpm_id) ? ' selected="true"' : ''; ?>><?php echo $level['name'] ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Add Member', 'wishlist-member'); ?>" /></p>
			</form>
		</div>
	</div>
<?php endif; ?>

<div id="AdvanceSearchPopup" style="display:none">
	<br />
	<form method="post" action="?<?php echo $this->QueryString('advance_usersearch'); ?>">
		<table class="form-table" width="100%">
			<tr>
				<td style="width:130px">Search</td>
				<td><input type="text" value="<?php echo esc_attr(stripslashes(wlm_arrval($_POST, 'advance_usersearch'))) ?>" name="advance_usersearch" id="advanced_search_field" style="width:98%" /></td>
			</tr>
			<tr>
				<td>Status</td>
				<td>
					<select name="filter_status">
						<option value="">---</option>
						<option value="active">Active</option>
						<option value="cancelled">Canceled</option>
						<option value="expired">Expired</option>
						<option value="unconfirmed">Unconfirmed</option>
						<option value="forapproval">Needs Approval</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Sequential</td>
				<td>
					<select name="filter_sequential">
						<option value="">---</option>
						<option value="on">On</option>
						<option value="of">Off</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Date Range</td>
				<td>
					<select name="filter_dates" id="filter_dates">
						<option value="">---</option>
						<option value="registration_date">Registraion Date</option>
						<option value="cancelled_date">Cancelation Date</option>
						<option value="expiration_date">Expiry Date</option>
					</select>
					<div class="field" id="date_ranges" style="display:none">
						<label for="from_date">From: </label>
						<input type="text" id="from_date" name="from_date"></input>
						<label for="to_date">To: </label>
						<input type="text" id="to_date" name="to_date"></input>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<label for="save_search">
						<?php _e('Save Search', 'wishlist-member'); ?>
					</label>				
				</td>
				<td>
					<input type="checkbox" name="save_search" id="save_search">
				</td>
			</tr>
			<tr id="save_searchname" style="display:none">
				<td><?php _e('Name of Saved Search', 'wishlist-member'); ?></td>
				<td><input type="text" name="save_searchname" id="save_searchname" style="width:98%" /></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="submit" class="button-secondary" value="<?php _e('Search Users', 'wishlist-member'); ?>" />
				</td>
			</tr>
		</table>
	</form>
</div>
