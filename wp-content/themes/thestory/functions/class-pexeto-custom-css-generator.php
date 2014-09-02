<?php

class PexetoCustomCssGenerator{

	public static function get_colors_css(){
		global $pexeto;

		return $pexeto->customizer->get_options_css();
	}

	public static function get_logo_css(){
		$width = pexeto_get_saved_option('logo_width');
		$height = pexeto_get_saved_option('logo_height');
		$css = '';

		if(!empty($width)){
			$css.= '#logo-container img{width:'.$width.'px; }';
		}

		if(!empty($height)){
			$css.= '#logo-container img{height:'.$height.'px;}';
		}
		return $css;
	}

	public static function get_fonts_css(){
		$css = '';

		//headings font
		$headings_font = pexeto_get_saved_option('headings_font_family');
		if($headings_font!='' && $headings_font!='default'){
			$font_name = pexeto_get_font_name_by_key($headings_font);
			if(!empty($font_name)){
				$css.= 'h1,h2,h3,h4,h5,h6{font-family:'.$font_name.';}';
			}
		}

		//body font
		$body_font = pexeto_get_saved_option('body_font');
		if(!empty($body_font['family']) && $body_font['family']!='default'){
			$font_name = pexeto_get_font_name_by_key($body_font['family']);
			if(!empty($font_name)){
				$css.= 'body{font-family:'.$font_name.';}';
			}
		}

		//body font size
		if(!empty($body_font['size'])){
			$css.= 'body, #footer, .sidebar-box, .services-box, .ps-content, .page-masonry .post, .services-title-box{font-size:'.$body_font['size'].'px;}';
		}

		//menu font
		$menu_font = pexeto_get_saved_option('menu_font');
		if(!empty($menu_font['family']) && $menu_font['family']!='default'){
			$font_name = pexeto_get_font_name_by_key($menu_font['family']);
			if(!empty($font_name)){
				$css.= '#menu ul li a{font-family:'.$font_name.';}';
			}
		}

		//menu font size
		if(!empty($menu_font['size'])){
			$css.= '#menu ul li a{font-size:'.$menu_font['size'].'px;}';
		}


		//header title font
		$header_title_font = pexeto_get_saved_option('header_title_font');
		if(!empty($header_title_font['family']) && $header_title_font['family']!='default'){
			$font_name = pexeto_get_font_name_by_key($header_title_font['family']);
			if(!empty($font_name)){
				$css.= '.page-title h1{font-family:'.$font_name.';}';
			}
		}

		//header_title font size
		if(!empty($header_title_font['size'])){
			$css.= '.page-title h1{font-size:'.$header_title_font['size'].'px;}';
		}


	
		return $css;
	}

	public static function get_header_size_css(){
		$css='';

		$header_height = pexeto_get_saved_option('header_height');
		if(!empty($header_height)){
			$css.='.page-title-wrapper{min-height:'.$header_height.'px; height:'.$header_height.'px;}';
		}

		$large_header_height = pexeto_get_saved_option('large_header_height');
		if(!empty($large_header_height)){
			$css.='.large-header .page-title-wrapper{min-height:'.$large_header_height.'px; height:'.$large_header_height.'px;}';
		}

		return $css;
	}

	public static function get_additional_css(){
		return pexeto_option('additional_styles');
	}


}