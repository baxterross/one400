<!DOCTYPE html>
<!--[if IE 7]><html class="ie ie7 ltie8 ltie9" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 8]><html class="ie ie8 ltie9" <?php language_attributes(); ?>><![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="initial-scale = 1.0" />
	<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	
	<?php 
		global $theme_option, $gdlr_post_option;
		if( !empty($gdlr_post_option) ){ $gdlr_post_option = json_decode($gdlr_post_option, true); }
		
		wp_head(); 
	?>
</head>

<body <?php body_class(); ?>>
<?php
	$body_wrapper = '';
	if($theme_option['enable-boxed-style'] == 'boxed-style'){
		$body_wrapper  = 'gdlr-boxed-style';
		if( !empty($theme_option['boxed-background-image']) && is_numeric($theme_option['boxed-background-image']) ){
			$alt_text = get_post_meta($theme_option['boxed-background-image'] , '_wp_attachment_image_alt', true);	
			$image_src = wp_get_attachment_image_src($theme_option['boxed-background-image'], 'full');
			echo '<img class="gdlr-full-boxed-background" src="' . $image_src[0] . '" alt="' . $alt_text . '" />';
		}else if( !empty($theme_option['boxed-background-image']) ){
			echo '<img class="gdlr-full-boxed-background" src="' . $theme_option['boxed-background-image'] . '" />';
		}
	}
	$header_style = '';
	if( empty($gdlr_post_option['header-style']) || $gdlr_post_option['header-style'] == 'default' ){
		$header_style = $theme_option['header-style'];
	}else{
		$header_style = $gdlr_post_option['header-style'];
	}
	
	$body_wrapper .= ($theme_option['enable-float-menu'] != 'disable')? ' float-menu': '';
	$body_wrapper .= ($header_style != 'transparent')? ' header-style-solid': ' header-style-transparent';
?>
<div class="body-wrapper <?php echo $body_wrapper; ?>" data-home="<?php echo home_url(); ?>" >
	<?php 
		// page style
		if( empty($gdlr_post_option) || empty($gdlr_post_option['page-style']) ||
			  $gdlr_post_option['page-style'] == 'normal' || 
			  $gdlr_post_option['page-style'] == 'no-footer'){ 
	?>
	<header class="gdlr-header-wrapper">
		<!-- top navigation -->
		<?php if( empty($theme_option['enable-top-bar']) || $theme_option['enable-top-bar'] == 'enable' ){ ?>
		<div class="top-navigation-wrapper">
			<div class="top-navigation-container container">
				<div class="top-navigation-left">
					<div class="top-social-wrapper">
						<?php gdlr_print_header_social(); ?>
					</div>
				</div>
				<div class="top-navigation-right">
					<div class="top-navigation-right-text">
						<?php 
							if( !empty($theme_option['top-bar-right-text']) ) 
								echo gdlr_text_filter($theme_option['top-bar-right-text']); 
						?>
					</div>
				</div>
				<div class="clear"></div>
				<div class="top-navigation-divider"></div>
			</div>
		</div>
		<?php } ?>
		
		<!-- logo -->
		<div class="gdlr-header-inner">
			<div class="gdlr-header-container container">
				<!-- logo -->
				<div class="gdlr-logo">
					<?php echo (is_front_page())? '<h1>':''; ?>
					<a href="<?php echo home_url(); ?>" >
						<?php 
							if($header_style != 'transparent'){
								if(empty($theme_option['logo-id'])){ 
									echo gdlr_get_image(GDLR_PATH . '/images/logo.png');
								}else{
									echo gdlr_get_image($theme_option['logo-id']);
								}
							}else{
								if(empty($theme_option['logo-id'])){
									$theme_option['logo-id'] = GDLR_PATH . '/images/logo.png';
								}else if(is_numeric($theme_option['logo-id'])){
									$image_src = wp_get_attachment_image_src($theme_option['logo-id'], 'full');
									$theme_option['logo-id'] = $image_src[0];
								}
							
								$attr = ' data-normal="' . $theme_option['logo-id'] . '" ';
								if(empty($theme_option['logot-id'])){ 
									echo gdlr_get_image(GDLR_PATH . '/images/logot.png', 'full', array(), $attr);
								}else{
									echo gdlr_get_image($theme_option['logot-id'], 'full', array(), $attr);
								}									
							}
						?>						
					</a>
					<?php echo (is_front_page())? '</h1>':''; ?>
					<?php
						// mobile navigation
						if( class_exists('gdlr_dlmenu_walker') && ( empty($theme_option['enable-responsive-mode']) || $theme_option['enable-responsive-mode'] == 'enable' ) ){
							echo '<div class="gdlr-responsive-navigation dl-menuwrapper" id="gdlr-responsive-navigation" >';
							echo '<button class="dl-trigger">Open Menu</button>';
							wp_nav_menu( array(
								'theme_location'=>'main_menu', 
								'container'=> '', 
								'menu_class'=> 'dl-menu gdlr-main-mobile-menu',
								'walker'=> new gdlr_dlmenu_walker() 
							) );						
							echo '</div>';
						}						
					?>						
				</div>
				
				<!-- navigation -->
				<?php get_template_part( 'header', 'nav' ); ?>	
				
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>
	</header>
	<div id="gdlr-header-substitute" ></div>
	<?php get_template_part( 'header', 'title' );
	
	} // page style ?>
	<div class="content-wrapper">