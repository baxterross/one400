<?php
/*
 * Membership Levels
 */

// retrieve pages for login redirection
$pages = get_pages('exclude=' . implode(',', $this->ExcludePages(array(), true)));
$pages_options = '';
foreach ((array) $pages AS $page) {
	$pages_options.='<option value="' . $page->ID . '">' . $page->post_title . '</option>';
}

// sorting part 1
list($_GET['s'], $sortorder) = explode(';', $_GET['s']);
if ($sortorder != 'd')
	$sortorder = 'a';
$sortorderflip = ($sortorder == 'd') ? 'a' : 'd';
$roles = $GLOBALS['wp_roles']->roles;
$caps = array();
foreach ((array) $roles AS $key => $role) {
	if ($role['capabilities']['level_10'] || $role['capabilities']['level_9'] || $role['capabilities']['level_8']) {
		unset($roles[$key]);
	} else {
		list($roles[$key]) = explode('|', $role['name']);

		$caps[$key] = count($role['capabilities']);
	}
}
array_multisort($caps, SORT_ASC, $roles);

$manage_content_url = $this->GetMenu('managecontent');
$manage_content_url = $manage_content_url->URL;
?>
<style type="text/css">
    .link-disabled {
        color: gray;
    }

    .ui-state-highlight{
		height:1.5em;
		background:#fbf9ee !important;
	}
	.ui-state-highlight, .ui-state-highlight td{
		border:1px solid #fcefa1 !important;

	}

	#wpm_membership_levels tr:hover {
		cursor: move;
	}

	#wpm_membership_levels tr{
		cursor: move;
	}
	#wlm-removefrom-tpl, .wlm-removefrom span { display: none;}

</style>
<script type="text/javascript">

	function wpm_show_advanced(a) {
		var x = document.getElementById(jQuery(a).attr('wlm-target'));
		var d = x.style.display
		x.style.display = (d == 'none') ? '' : 'none'

//		var p = x.parentNode.rows[x.rowIndex - 2];
//		for (var i = 0; i < p.cells.length; i++) {
//			p.cells[i].style.borderBottomWidth = (d === 'none') ? '0px' : '1px';
//		}
		if(a.getElementsByTagName('span')[0]) {
			a.getElementsByTagName('span')[0].innerHTML = (d === 'none') ? '&mdash;' : '+';
		}
		return false;
	}

	jQuery(function($) {

		//
		var fixHelper = function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		};
		$.inline_editor = function(tbl) {
			var self = this;
			self.tbl = tbl;
			self.current_editor = null;

			self.init = function() {
				tbl.find('.wpm_row_edit').live('click', function(ev) {
					ev.preventDefault();
					var id = $(this).attr('rel');
					self.edit(id);
				});

				tbl.find('.wpm_row_editor_close').live('click', function(ev) {
					ev.preventDefault();
					var id = $(this).attr('data-id');
					self.cancel(id);
				});
				tbl.find('.wpm_row_editor_submit').live('click', function(ev) {
					ev.preventDefault();
					var id = $(this).attr('data-id');
					self.submit(id);
				});

				tbl.find('.wpm_row_delete').live('click', function(ev) {
					ev.preventDefault();
					var id = $(this).attr('rel');
					self.remove(id);
				});

				self.tbl.find("tbody").sortable({
					opacity: 0.6,
					cursor: 'move',
					items: 'tr.wpm_level_row',
					placeholder: 'ui-state-highlight',
					helper: fixHelper,
					over: function(e, ui) {
						//$('.ui-state-highlight').html('<td colspan="5">&nbsp;</td>');
					},
					start: function(e, ui) {
						if(self.current_editor != null) {
							self.cancel(self.current_editor);
						}
					},
					stop: function(e, ui) {
						var tbody = ui.item.parent();
						var data = {
							reorder: {},
							action: 'wlm_reorder_membership_levels'
						};

						tbody.find('tr').each(function(i, el) {
							var row = $(el);
							var id = row.attr('id').split('-')[1];
							data.reorder[id] = i;
						});

						$.post(ajaxurl, data);
					}
				});//.disableSelection();
			}

			self.remove_editor = function(id) {
				var editor = $('#wlmEditRow-' + id);
				editor.hide('slow', function() {
					editor.remove();
					self.current_editor = null;
				});

				var row = $('#wpm_level_row-' + id);
				var title = row.find('.row-title');
				var title_text = title.html();
				title_text.trim();
				if(title_text.charAt(0) == "-"){
					title_text = title_text.replace("-","+");
				}else if(title_text.charAt(0) != "+"){
					title_text = "+ " +title_text;
				}
				title.html(title_text);
				row.find('.row-edit').addClass("wpm_row_edit");
				row.find('.row-edit').removeClass("link-disabled");
			}
			self.edit = function(id) {

				//ensure only one editor is shown
				if(self.current_editor != null && self.current_editor !=id) {
					self.cancel(self.current_editor);
				}

				if(self.current_editor == id) {
					self.cancel(id);
					return;
				}

				var row = $('#wpm_level_row-' + id);

				var data = {
					'action': 'wlm_form_membership_level',
					'id': id
				}

				$.post(ajaxurl, data, function(res) {
					row.after(res);
					row.next().show('slow', function() {
						zc_initialize(row.next().find('.wlmClipButton'));
					});
					self.current_editor = id;
					//reinitialize tooltips
					initialize_tooltip(jQuery);
				});

				var title = row.find('.row-title');
				var title_text = title.html();
				title_text.trim();
				if(title_text.charAt(0) == "+"){
					title_text = title_text.replace("+","-");
				}
				title.html(title_text);
				row.find('.row-edit').removeClass("wpm_row_edit");
				row.find('.row-edit').addClass("link-disabled");
			}
			self.submit = function(id) {
				var row = $('#wpm_level_row-' + id);
				var form = $('#form-' + id);
				var editor = $('#wlmEditRow-' + id);
				var spinner = editor.find('.spinner');


				spinner.show();
				$.post('<?php echo admin_url()?>', form.serialize(), function(res) {

					//immediately show changes to level name and url
					row.find('.row-title').html(form.find('input[name*="name"]').val());
					var url = row.find('a.wpm_regurl').eq(0);
					var urlparts = url.html().split('/register/')
					var newurl = urlparts[0] + '/register/' + form.find('input[name*=url]').val()

					url.attr('href', newurl);
					url.html(newurl);

					row.find('.wlmClipButton').attr('data-clipboard-text', newurl);
					self.cancel(id);
				});

			}
			self.cancel = function (id) {
				self.remove_editor(id);
			}
			self.remove = function(id) {
				var row     = $('#wpm_level_row-'  +id)
				var editor 	= $('#wlmEditRow-' + id);

				var cont = false;
				cont = confirm('Warning! Delete this membership level?')
				if (!cont) {
					return;
				}
				cont = confirm('Last Warning! Are you really sure?\nDeleting this membership level cannot be undone!');
				if (!cont) {
					return;
				}

				data = {
					'action': 'wlm_del_membership_level',
					'id'	: id
				};
				$.post(ajaxurl, data, function(res) {
					//location.reload();
					editor.hide('slow');
					row.hide('slow', function() {
						row.remove();
						editor.remove();
					});
				});

			}
			self.init();
		}
		$.inline_editor($('#wpm_membership_levels'));

		$('.wpm_row_editor_close').live('click', function(ev) {
			ev.preventDefault();
			var id = $(this).attr('data-id');
		});
});
</script>

	<table class="widefat wpm_nowrap" id="wpm_membership_levels">
		<thead>
			<tr>
				<th scope="col"  style="line-height:20px;width:280px"><a class="wpm_header_link<?php echo wlm_arrval($_GET,'s') == 'n' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=n<?php echo wlm_arrval($_GET,'s') == 'n' ? ';' . $sortorderflip : ''; ?>"><?php _e('Membership Level', 'wishlist-member'); ?></a></th>
				<th scope="col"  style="line-height:20px;" colspan="2"><?php _e('Registration URL', 'wishlist-member'); ?></th>
				<th scope="col"  style="line-height:20px;width:110px;"><a class="wpm_header_link<?php echo wlm_arrval($_GET,'s') == 'c' ? ' wpm_header_sort' . $sortorder : ''; ?>" href="?<?php echo $this->QueryString('s') ?>&s=c<?php echo wlm_arrval($_GET,'s') == 'c' ? ';' . $sortorderflip : ''; ?>"><?php _e('Creation Date', 'wishlist-member'); ?></a></th>
				<th scope="col"  style="line-height:20px;width:110px;">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$prevlevels = $prevurls = array();
			ksort($wpm_levels);
			$sortfield = 'id';
			if (wlm_arrval($_GET,'s') == 'n') {
				$sortfield = 'name';
			} else if (wlm_arrval($_GET,'s') == 'c') {
				$sortfield = 'id';
			} else {
				$sortfield = 'levelOrder';
			}
			$this->SortLevels($wpm_levels, $sortorder, $sortfield);

			$rlevels_options = '';
			foreach ($wpm_levels AS $rid => $rlevel) {
				if (!($rlevel['wpm_newid'] == $rid && !trim($rlevel['name']))) {
					$rlabel = "wpm_remove_from__" . $rid;
					$rlevels_options.='<input id="' . $rlabel . '" type="checkbox" name="wpm_levels[][removeFromLevel][' . $rid . ']" value="1" /> <label for="' . $rlabel . '">' . $rlevel['name'] . '</label><br />' . "\n";
				}
			}

			foreach ((array) $wpm_levels AS $id => $level):
				if(!is_numeric($id)) {
					continue;
				}

				$errlevel = in_array(strtolower($level['name']), $prevlevels);
				$errurl = in_array($level['url'], $prevurls);
				$prevlevels[] = strtolower($level['name']);
				$prevurls[] = $level['url'];

				if ($level[wpm_newid] == $id && (!trim($level['name']) || !trim($level['url']))) {
					unset($wpm_levels[$id]);
					continue;
				}
				if ($level['noexpire']) {
					unset($level['expire']);
					unset($level['calendar']);
				}
				?>
				<tr class="wpm_level_row" id="wpm_level_row-<?php echo $id?>">
					<td class="wpm_row">
						<a class="row-title wpm_row_edit" href="javascript:void(0);" rel="<?php echo $id?>">+ <?php echo esc_attr($level['name']) ?></a>
					</td>
					<td width="1">
						<a class="wpm_regurl" href="<?php echo $registerurl ?>/<?php echo $level['url'] ?>" target="_blank" style="color:#000000"><?php echo $registerurl ?>/<?php echo $level['url'] ?></a>
					</td>
					<td>
						<a class="wlmClipButton" id="wlmClipButton-<?php echo $id?>" data-clipboard-text="<?php echo $registerurl ?>/<?php echo $level['url'] ?>" href="javascript:;"><?php _e('Copy URL', 'wishlist-member'); ?></a></div>
					</td>
					<td><?php echo date_i18n('m/d/Y', $id + $this->GMT) ?></td>
					<td>
						<div class="actions">
							<a href="javascript:void(0);" rel="<?php echo $id?>" class="row-edit wpm_row_edit">Edit</a> |
							<?php if (empty($level['count'])): ?>
								<a href="#" rel="<?php
								if (empty($level['count']))
									echo $id;
								else
									echo -1;
								?>" class="wpm_row_delete">Delete</a>
							   <?php else: ?>
								<span class="link-disabled">Delete</span>
								<?php echo $this->Tooltip("membershiplevels-default-tooltips-cannot-delete"); ?>
							<?php endif; ?>
						</div>
					</td>
				</tr>
				<?php
			endforeach;
			$this->SaveOption('wpm_levels', $wpm_levels);
			?>
		</tbody>
	</table>

	<form id="membership-levels-frm" method="post">
	<h2><?php _e('Add a New Membership Level', 'wishlist-member'); ?></h2>
	<br />
	<!-- start the new membership -->
	<table class="widefat wpm_nowrap">
		<tr class="wlmEditRow">
			<td>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col"  style="line-height:20px;"><?php _e('Membership Level', 'wishlist-member'); ?></th>
							<th scope="col"  style="line-height:20px;"><?php _e('Registration URL', 'wishlist-member'); ?> <?php echo $this->Tooltip("membershiplevels-default-tooltips-Registration-URL"); ?></th>
							<th scope="col"  style="line-height:20px;"><?php _e('Redirects', 'wishlist-member'); ?></th>
							<th scope="col"  style="line-height:20px;"><?php _e('Access to', 'wishlist-member'); ?> <?php echo $this->Tooltip("membershiplevels-default-tooltips-Access-to"); ?> </th>
							<th scope="col" class="num"  nowrap style="line-height:18px;"><?php _e('Length of Subscription', 'wishlist-member'); ?> <?php echo $this->Tooltip("membershiplevels-default-tooltips-Length-of-Subscription"); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?> wpm_level_row_editor" id="wpm_new_row">
							<td>
								<input type="hidden" name="wpm_levels[<?php echo $wpm_newid ?>][wpm_newid]" value="<?php echo $wpm_newid ?>" /><input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][name]" size="20" placeholder="<?php _e('Level Name', 'wishlist-member'); ?>" />
								<br />
								<br />
								<a href="javascript:;" wlm-target="wpm_level_row_advanced_<?php echo $wpm_newid ?>" onclick="wpm_show_advanced(this);
		return false">[<span>+</span>] <?php _e('Advanced Settings', 'wishlist-member'); ?></a>
							</td>
							<td>
								<div>
									<?php echo $registerurl ?>/<input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][url]" value="<?php echo $newurl ?>" size="6" />
								</div>
								<label for="doclone" style="display:block;margin:5px 0 0 1px;"><input style="float:left;margin:1px 5px 0 0" type="checkbox" name="doclone" id="doclone" value="<?php echo $wpm_newid ?>" onclick="wpm_clone_level(this.form)" /> <a><?php _e('Copy an Existing Membership Level', 'wishlist-member'); ?></a></label>
								<div style="margin:0 0 0 18px">
									<select name="clonefrom" style="width:200px" onchange="wpm_clone_level(this.form)">
										<?php foreach ((array) $wpm_levels AS $key => $level): ?>
											<option value="<?php echo $key ?>"><?php echo $level['name'] ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</td>
							<td>
								<?php _e('After Login', 'wishlist-member'); ?><?php echo $this->Tooltip("membershiplevels-default-tooltips-After-Login"); ?><br />
								<select name="wpm_levels[<?php echo $wpm_newid ?>][loginredirect]" style="width:120px">
									<option value='---'>--- <?php _e('Default', 'wishlist-member'); ?> ---</option>
									<option value=''><?php _e('Home Page', 'wishlist-member'); ?></option>
									<?php echo $pages_options; ?>
								</select><br />
								<?php _e('After Registration', 'wishlist-member'); ?><?php echo $this->Tooltip("membershiplevels-default-tooltips-After-Registration"); ?><br />
								<select name="wpm_levels[<?php echo $wpm_newid ?>][afterregredirect]" style="width:120px">
									<option value='---'>--- <?php _e('Default', 'wishlist-member'); ?> ---</option>
									<option value=''><?php _e('Home Page', 'wishlist-member'); ?></option>
									<?php echo $pages_options; ?>
								</select>
							</td>
							<td>
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][allpages]" />
									<?php _e('All Pages', 'wishlist-member'); ?></label><br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][allcategories]" />
									<?php _e('All Categories', 'wishlist-member'); ?></label><br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][allposts]" />
									<?php _e('All Posts', 'wishlist-member'); ?></label><br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][allcomments]" />
									<?php _e('All Comments', 'wishlist-member'); ?></label><br />
							</td>
							<td class="num"><input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][expire]" size="3" /><select name="wpm_levels[<?php echo $wpm_newid ?>][calendar]">
									<option value="Days"><?php _e('Days', 'wishlist-member'); ?></option>
									<option value="Weeks"><?php _e('Weeks', 'wishlist-member'); ?></option>
									<option value="Months"><?php _e('Months', 'wishlist-member'); ?></option>
									<option value="Years"><?php _e('Years', 'wishlist-member'); ?></option>
								</select><br />
								<br />
								<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][noexpire]" value="1" onclick="this.parentNode.parentNode.childNodes[0].disabled = this.parentNode.parentNode.childNodes[1].disabled = this.checked" />
									<?php _e('No Expiration Date', 'wishlist-member'); ?></label>
							</td>
						</tr>
						<?php /* advanced settings for new levels - START */ ?>
						<tr class="wpm_level_row_editor_advanced" id="wpm_level_row_advanced_<?php echo $wpm_newid ?>" style="display:none">
							<td colspan="5">
								<table width="100%" class="MembershipLevelsAdvanced">
									<tr>
										<td style="width:310px;" class="first">
											<b>Registration Requirements</b> <?php echo $this->Tooltip("membershiplevels-default-tooltips-regrequirements"); ?><br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][requirecaptcha]" value="1" /> <?php _e('Require Captcha Image on Registration Page', 'wishlist-member'); ?></label>
											<?php echo $this->Tooltip("membershiplevels-default-tooltips-recaptchasettings"); ?>
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][requireemailconfirmation]" value="1" /> <?php _e('Require Email Confirmation After Registration', 'wishlist-member'); ?></label>
											<?php echo $this->Tooltip("membershiplevels-default-tooltips-requireemailconfirmation"); ?>
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][requireadminapproval]" value="1" /> <?php _e('Require Admin Approval After Registration', 'wishlist-member'); ?></label>
											<?php echo $this->Tooltip("membershiplevels-default-tooltips-requireadminapproval"); ?>

										</td>
										<td style="width:215px;">
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][isfree]" value="1" /> <?php _e('Grant Continued Access', 'wishlist-member'); ?></label>
											<?php echo $this->Tooltip("membershiplevels-default-tooltips-Grant-Continued-Access"); ?>
											<br />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][disableexistinglink]" value="1" /> <?php _e('Disable Existing Users Link', 'wishlist-member'); ?></label>
											<?php echo $this->Tooltip("membershiplevels-default-tooltips-disableexistinglink"); ?>
											<br  />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][registrationdatereset]" value="1" /> <?php _e('Registration Date Reset', 'wishlist-member'); ?></label>
											<?php echo $this->Tooltip("membershiplevels-default-tooltips-registrationdatereset"); ?>
											<br  />
											<label><input type="checkbox" name="wpm_levels[<?php echo $wpm_newid ?>][uncancelonregistration]" value="1" /> <?php _e('Un-cancel on Re-registration', 'wishlist-member'); ?></label>
											<?php echo $this->Tooltip("membershiplevels-default-tooltips-uncancelonregistration"); ?>

										</td>
										<td style="width:220px;">
											<table>
												<tr>
													<td style="border:none" valign="middle"><?php _e('Role', 'wishlist-member'); ?></td>
													<td style="border:none" valign="middle">
														<select name="wpm_levels[<?php echo $wpm_newid ?>][role]">
															<?php foreach ((array) $roles AS $rolekey => $rolename): ?>
																<option value="<?php echo $rolekey; ?>"><?php echo $rolename; ?></option>
															<?php endforeach; ?>
														</select><?php echo $this->Tooltip("membershiplevels-default-tooltips-Role"); ?>
													</td>
												</tr>
												<tr>
													<td style="border:none" valign="middle"><?php _e('Level Order', 'wishlist-member'); ?></td>
													<td style="border:none" valign="middle"><!--2--><input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][levelOrder]" size="4" value="<?php echo count($wpm_levels)?>" /><?php echo $this->Tooltip("membershiplevels-default-tooltips-levelorder"); ?></td>
												</tr>

											</table>
										</td>
										<td>
											<?php if ($rlevels_options): ?>
												<b>Remove From</b> <?php echo $this->Tooltip("membershiplevels-default-tooltips-removefrom"); ?><br />
												<div style="overflow:auto;height:75px;width:100%;border:none">
													<?php
													$removeFromSearch = array();
													$removeFromSearch[] = 'wpm_remove_from__';
													$removeFromSearch[] = 'wpm_levels[][';

													$removeFromReplace = array();
													$removeFromReplace[] = 'wpm_remove_from_' . $wpm_newid . '_';
													$removeFromReplace[] = 'wpm_levels[' . $wpm_newid . '][';

													echo preg_replace('#<input.*\[removeFromLevel\]\[' . $wpm_newid . '\].*><br />#U', '', str_replace($removeFromSearch, $removeFromReplace, $rlevels_options));
													?>
												</div>
											<?php endif; ?>
										</td>
									</tr>
									<tr>
										<td colspan="4" class="first">
											<div>
												<b><?php _e('Sales Page URL:', 'wishlist-member'); ?></b> <i>(optional)</i>
												<input type="text" name="wpm_levels[<?php echo $wpm_newid ?>][salespage]" size="80" />
												<?php echo $this->Tooltip("membershiplevels-default-tooltips-levelsalespage"); ?>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
	<p class="submit">
		<?php
		echo '<!-- ';
		$this->Option('wpm_levels');
		echo ' -->';
		$this->Options();
		$this->RequiredOptions();
		?>

		<input type="hidden" name="WLSaveMessage" value="Membership Levels Updated" />
		<input type="hidden" name="WishListMemberAction" value="SaveMembershipLevels" />
		<input type="submit" class="button button-primary" value="<?php _e('Add New', 'wishlist-member'); ?>" />
	</p>
</form>
