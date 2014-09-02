<?php
$option = $this->GetOption('wllogin2settings');
if(!is_array($option)) {
	$option = array();
}

//autocreate page here because

if(isset($_POST['create_social_connect']) && $_POST['create_social_connect'] == 'yes') {
	$blogname = get_option('blogname');
	$post = array(
		'post_title'    => 'Social Login Connect',
		'post_content'  => "<p>It looks like this is the first time you are logging in with your <strong>[wl_sociallogin_network]</strong> account, "
		."please login with your <strong>$blogname</strong> account to continue. This will connect your social media login info with this site.  ".
		"\nOnce completed, you will be able to login to this site using your social media login info.</p>".
		'<div style="width: 202px;margin: 0px auto;">[wl_sociallogin_exp include=facebook,twitter,linkedin,google]</div>',
		'post_status'   => 'publish',
		'post_type'     => 'page',
		'comment_status' => 'closed'
	);
	$p = wp_insert_post($post, $wp_error);
	$this->SaveOption('connect_page', $p);
	$this->PreloadOptions();
}
$pages = get_pages();


?>
<h2><?php _e('WishList Login 2.0 &raquo; Configuration','wishlist-login2'); ?></h2>
<form method="post">
<!-- your form goes here -->
<h3>Social Settings<?php echo $this->Tooltip("settings-tooltips-activate-login"); ?></h3>
<p class="wl_setting_title">Select Activated Social Logins</p>
<p class="wl_setting_description">Configure and enable the social accounts you'd like to use in
	your login forms. Select the "Configure" link or the gear icon in order to configure
	each social account. Then, select the checkbox to enable that social account.
	You can access step-by-step video instructions by selecting the video icon.
</p>
<div class="wllogin2-content">
	<ul class="socials">
		<li>
			<?php
			if ( empty( $option['facebook']['appid'] ) || empty( $option['facebook']['appsecret'] ) ) {
				$fb_configured = false;
			} else {
				$fb_configured = true;

				if ( !$this->GetOption('include_facebook') ) {
					$fb_enabled = false;
				} else {
					$fb_enabled = true;
				}
			}

			if ( !$fb_configured ) {
				$fb_icon_class = 'social-status-icon-disabled';
				$fb_status_title = 'Status: Disabled. Please configure Facebook API settings to enable.';
			} else {
				if ( !$fb_enabled ) {
					$fb_icon_class = 'social-status-icon-configured';
					$fb_status_title = 'Status: Configured. Click the checkbox to enable.';
				} else {
					$fb_icon_class = 'social-status-icon-enabled';
					$fb_status_title = 'Status: Enabled.';
				}
			}
			?>
			<p class="social-title-container">
				<input title="<?php echo $fb_status_title; ?>" <?php if ( !$fb_configured ) { echo 'disabled="disabled"'; } ?> type="checkbox" value="1" class="enable_social" id="enable_facebook" name="<?php $this->Option('include_facebook')?>" <?php echo $this->OptionChecked(1)?>/>
				<label for="enable_facebook" title="<?php echo $fb_status_title; ?>">
					<span>Facebook</span>
					<?php if ( !$fb_configured ) : ?>
						(<a title="Facebook API Settings" class="social-settings-trigger thickbox" href="?TB_inline=true&height=160&width=500&inlineId=social-settings-facebook"><span>Configure</span></a>)
					<?php endif; ?>
				</label>
			</p>
			<p class="social-settings-link-container"><a title="Facebook API Settings" class="social-settings-trigger thickbox" href="?TB_inline=true&height=160&width=500&inlineId=social-settings-facebook"><span>Settings</span></a></p>
			<p class="social-tutorial-link-container"><script type="text/javascript" src="http://wishlistproducts.evsuite.com/player/d2lzaGxpc3Qtc29jaWFsLWxvZ2luLWZhY2Vib29rLTIubXA0/?container=evp-HLAQ9B64CJ"></script><div id="evp-HLAQ9B64CJ" data-role="evp-video" data-evp-id="d2lzaGxpc3Qtc29jaWFsLWxvZ2luLWZhY2Vib29rLTIubXA0"></div></p>
			<p class="social-status-icon <?php echo $fb_icon_class; ?>" title="<?php echo $fb_status_title; ?>"></p>
			<div class="social-settings-wrap">
				<div id="social-settings-facebook">
					<table class="social-settings form-table">
						<tr>
							<th>Facebook APP ID</th>
							<td><input type="text" style="width: 300px;" name="wllogin2settings[facebook][appid]" value="<?php echo $option['facebook']['appid']?>"/></td>
						</tr>
						<tr>
							<th>Facebook API Secret</th>
							<td><input type="text" style="width: 300px;" name="wllogin2settings[facebook][appsecret]" value="<?php echo $option['facebook']['appsecret']?>"/></td>
						</tr>
					</table>
				</div>
			</div>
		</li>
		<li>
			<?php
			if ( empty( $option['twitter']['consumer_key'] ) || empty( $option['twitter']['consumer_secret'] ) ) {
				$twit_configured = false;
			} else {
				$twit_configured = true;

				if ( !$this->GetOption('include_twitter') ) {
					$$twit_enabled = false;
				} else {
					$twit_enabled = true;
				}
			}

			if ( !$twit_configured ) {
				$twit_icon_class = 'social-status-icon-disabled';
				$twit_status_title = 'Status: Disabled. Please configure Twitter API settings to enable.';
			} else {
				if ( !$twit_enabled ) {
					$twit_icon_class = 'social-status-icon-configured';
					$twit_status_title = 'Status: Configured. Click the checkbox to enable.';
				} else {
					$twit_icon_class = 'social-status-icon-enabled';
					$twit_status_title = 'Status: Enabled.';
				}
			}
			?>
			<p class="social-title-container">
				<input title="<?php echo $twit_status_title; ?>" <?php if ( !$twit_configured ) { echo 'disabled="disabled"'; } ?> type="checkbox" value="1" class="enable_social" id="enable_twitter" name="<?php $this->Option('include_twitter')?>" <?php echo $this->OptionChecked(1)?>/>
				<label for="enable_twitter" title="<?php echo $twit_status_title; ?>">
					<span>Twitter</span>
					<?php if ( !$twit_configured ) : ?>
						(<a title="Twitter API Settings" class="social-settings-trigger thickbox" href="?TB_inline=true&height=160&width=500&inlineId=social-settings-twitter"><span>Configure</span></a>)
					<?php endif; ?>
				</label>
			</p>
			<p class="social-settings-link-container"><a title="Twitter API Settings" class="social-settings-trigger thickbox" href="?TB_inline=true&height=160&width=500&inlineId=social-settings-twitter"><span>Settings</span></a></p>
			<p class="social-tutorial-link-container"><script type="text/javascript" src="http://wishlistproducts.evsuite.com/player/d2lzaGxpc3Qtc29jaWFsLWxvZ2luLXR3aXR0ZXIubXA0/?container=evp-0A5R40L38R"></script><div id="evp-0A5R40L38R" data-role="evp-video" data-evp-id="d2lzaGxpc3Qtc29jaWFsLWxvZ2luLXR3aXR0ZXIubXA0"></div></p>
			<p class="social-status-icon <?php echo $twit_icon_class; ?>" title="<?php echo $twit_status_title; ?>"></p>
			<div class="social-settings-wrap">
				<div id="social-settings-twitter">
					<table class="social-settings form-table">
						<tr>
							<th>Twitter Consumer Key</th>
							<td><input type="text" style="width: 300px;" name="wllogin2settings[twitter][consumer_key]" value="<?php echo $option['twitter']['consumer_key']?>"/></td>
						</tr>
						<tr>
							<th>Twitter Consumer Secret</th>
							<td><input type="text" style="width: 300px;" name="wllogin2settings[twitter][consumer_secret]" value="<?php echo $option['twitter']['consumer_secret']?>"/></td>
						</tr>
					</table>
				</div>
			</div>
		</li>
		<li>
			<?php
			if ( empty( $option['linkedin']['consumer_key'] ) || empty( $option['linkedin']['consumer_secret'] ) ) {
				$linkedin_configured = false;
			} else {
				$linkedin_configured = true;

				if ( !$this->GetOption('include_linkedin') ) {
					$linkedin_enabled = false;
				} else {
					$linkedin_enabled = true;
				}
			}

			if ( !$linkedin_configured ) {
				$linkedin_icon_class = 'social-status-icon-disabled';
				$linkedin_status_title = 'Status: Disabled. Please configure LinkedIn API settings to enable.';
			} else {
				if ( !$linkedin_enabled ) {
					$linkedin_icon_class = 'social-status-icon-configured';
					$linkedin_status_title = 'Status: Configured. Click the checkbox to enable.';
				} else {
					$linkedin_icon_class = 'social-status-icon-enabled';
					$linkedin_status_title = 'Status: Enabled.';
				}
			}
			?>
			<p class="social-title-container">
				<input title="<?php echo $linkedin_status_title; ?>" <?php if ( !$linkedin_configured ) { echo 'disabled="disabled"'; } ?> type="checkbox" value="1" class="enable_social" id="enable_linkedin" name="<?php $this->Option('include_linkedin')?>" <?php echo $this->OptionChecked(1)?>/>
				<label for="enable_linkedin" title="<?php echo $linkedin_status_title; ?>">
					<span>LinkedIn</span>
					<?php if ( !$linkedin_configured ) : ?>
						(<a title="LinkedIn API Settings" class="social-settings-trigger thickbox" href="?TB_inline=true&height=160&width=500&inlineId=social-settings-linkedin"><span>Configure</span></a>)
					<?php endif; ?>
				</label>
			</p>
			<p class="social-settings-link-container"><a title="LinkedIn API Settings" class="social-settings-trigger thickbox" href="?TB_inline=true&height=160&width=500&inlineId=social-settings-linkedin"><span>Settings</span></a></p>
			<p class="social-tutorial-link-container"><script type="text/javascript" src="http://wishlistproducts.evsuite.com/player/d2lzaGxpc3Qtc29jaWFsLWxvZ2luLWxpbmtlZGluLm1wNA==/?container=evp-U7L8F9RZAM"></script><div id="evp-U7L8F9RZAM" data-role="evp-video" data-evp-id="d2lzaGxpc3Qtc29jaWFsLWxvZ2luLWxpbmtlZGluLm1wNA=="></div></p>
			<p class="social-status-icon <?php echo $linkedin_icon_class; ?>" title="<?php echo $linkedin_status_title; ?>"></p>
			<div class="social-settings-wrap">
				<div id="social-settings-linkedin">
					<table class="social-settings form-table">
						<tr>
							<th>LinkedIn Consumer Key</th>
							<td><input type="text" style="width: 300px;" name="wllogin2settings[linkedin][consumer_key]" value="<?php echo $option['linkedin']['consumer_key']?>"/></td>
						</tr>
						<tr>
							<th>LinkedIn Consumer Secret</th>
							<td><input type="text" style="width: 300px;" name="wllogin2settings[linkedin][consumer_secret]" value="<?php echo $option['linkedin']['consumer_secret']?>"/></td>
						</tr>
					</table>
				</div>
			</div>
		</li>
		<li>
			<?php
			if ( empty( $option['google']['client_id'] ) || empty( $option['google']['client_secret'] ) || empty( $option['google']['redirect_uri'] ) ) {
				$google_configured = false;
			} else {
				$google_configured = true;

				if ( !$this->GetOption('include_google') ) {
					$google_enabled = false;
				} else {
					$google_enabled = true;
				}
			}

			if ( !$google_configured ) {
				$google_icon_class = 'social-status-icon-disabled';
				$google_status_title = 'Status: Disabled. Please configure Google API settings to enable.';
			} else {
				if ( !$google_enabled ) {
					$google_icon_class = 'social-status-icon-configured';
					$google_status_title = 'Status: Configured. Click the checkbox to enable.';
				} else {
					$google_icon_class = 'social-status-icon-enabled';
					$google_status_title = 'Status: Enabled.';
				}
			}
			?>
			<p class="social-title-container">
				<input title="<?php echo $google_status_title; ?>" <?php if ( !$google_configured ) { echo 'disabled="disabled"'; } ?> type="checkbox" value="1" class="enable_social" id="enable_google" name="<?php $this->Option('include_google')?>" <?php echo $this->OptionChecked(1)?>/>
				<label for="enable_google" title="<?php echo $google_status_title; ?>">
					<span>Google</span>
					<?php if ( !$google_configured ) : ?>
						(<a title="Google API Settings" class="social-settings-trigger thickbox" href="?TB_inline=true&height=160&width=500&inlineId=social-settings-google"><span>Configure</span></a>)
					<?php endif; ?>
				</label>
			</p>
			<p class="social-settings-link-container"><a title="Google API Settings" class="social-settings-trigger thickbox" href="?TB_inline=true&height=160&width=500&inlineId=social-settings-google"><span>Settings</span></a></p>
			<p class="social-tutorial-link-container">

<script type="text/javascript" src="http://wishlistproducts.evsuite.com/player/d2lzaGxpc3Qtc29jaWFsLWxvZ2luLWdvb2dsZS5tcDQ=/?container=evp-RLR4YNSTYA"></script><div id="evp-RLR4YNSTYA" data-role="evp-video" data-evp-id="d2lzaGxpc3Qtc29jaWFsLWxvZ2luLWdvb2dsZS5tcDQ="></div></p>
			<p class="social-status-icon <?php echo $google_icon_class; ?>" title="<?php echo $google_status_title; ?>"></p>
			<div class="social-settings-wrap">
				<div id="social-settings-google">
					<table class="social-settings form-table">
						<tr>
							<th>Google Client ID</th>
							<td><input type="text" style="width: 300px;" name="wllogin2settings[google][client_id]" value="<?php echo $option['google']['client_id']?>"/></td>
						</tr>
						<tr>
							<th>Google Consumer Secret</th>
							<td><input type="text" style="width: 300px;" name="wllogin2settings[google][client_secret]" value="<?php echo $option['google']['client_secret']?>"/></td>
						</tr>
						<tr>
							<th>Google Redirect URI</th>
							<!--<td><?php echo get_option('home')?>/index.php?wllogin2=1&loginaction=login&handler=google-->
								<td><input type="text" style="width: 300px;" name="wllogin2settings[google][redirect_uri]" value="<?php echo get_option('home')?>/index.php?wllogin2=1&loginaction=login&handler=google"/></td>
							<!--</td> -->
						</tr>
					</table>
				</div>
			</div>
		</li>
	</ul>
</div>
<p class="wl_setting_title">Social Connect Page<?php echo $this->Tooltip("settings-tooltips-social-connect"); ?></p>
<p class="wl_setting_description">
Set the Social Login connection page. This is the page a member will see when they
first attempt to login using a social network.
</p>
<div class="wllogin2-content">
	<select name="<?php echo $this->Option('connect_page')?>">
		<option value="">Select A Page</option>
		<?php foreach($pages as $p): ?>
		<option value="<?php echo $p->ID?>" <?php echo $this->OptionSelected($p->ID)?>><?php echo $p->post_title?></option>
		<?php endforeach; ?>
	</select> &nbsp;&nbsp;OR&nbsp;&nbsp; <input type="checkbox" name="<?php echo $this->Option('create_social_connect')?>" value="yes"/>&nbsp;&nbsp;Auto Create A Social Connect Page
</div>
<h3>Post Login Settings</h3>
<p class="wl_setting_title">Login Suffix<?php echo $this->Tooltip("settings-tooltips-login-suffix"); ?></p>
<p class="wl_setting_description">
Set the suffix you'd like to use for the Post Login functionality. This is the
suffix you will append to any links you send out where you'd like a login form
automatically displayed to users who are not logged into your membershp site.
</p>
<div class="wllogin2-content">
	/<input type="text" name="<?php echo $this->Option('suffix')?>" value="<?php echo $this->OptionValue(false, 'login')?>"/>
</div>
<p class="wl_setting_title">Login Redirect<?php echo $this->Tooltip("settings-tooltips-login-redirect"); ?></p>
<p class="wl_setting_description">
Set your Post Login redirect settings. You can choose to redirect users to the
post/page reference in the link they clicked OR to simply use the WishList Member
redirect settings.
</p>
<div class="wllogin2-content">
	<input type="radio" name="<?php echo $this->Option('redirect_to')?>" value="post" <?php $this->OptionChecked('post')?>/>&nbsp;&nbsp;&nbsp;Go directly to post/page
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="<?php echo $this->Option('redirect_to')?>" value="wishlistmember" <?php $this->OptionChecked('wishlistmember')?>/>&nbsp;&nbsp;&nbsp;Go to WishList Member Redirect
</div>
<input type="hidden" name="WLOptions" value="wllogin2settings,<?php echo $this->Options(false)?>"/>
<input type="hidden" name="WishListLogin2Action" value="Save" />
<?php submit_button('Save Settings'); ?>
</form>