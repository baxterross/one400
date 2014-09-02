<?php
/**
 * This file contains HTML generation functions.
 *
 * @author  Pexeto
 */


if ( !function_exists( 'pexeto_build_portfolio_carousel_html' ) ) {

	/**
	 * Generates the portfolio carousel HTML code.
	 *
	 * @param array   $posts containing all the post objects that will be displayed
	 * in the carousel
	 * @param string  $title the title of the carousel
	 * @return string        the generated HTML code of the carousel
	 */
	function pexeto_build_portfolio_carousel_html( $posts, $title, $options ) {
		global $pexeto_page;
		$full_layout = true;

		if(isset($pexeto_page['layout']) && ($pexeto_page['layout']=='left' || $pexeto_page['layout']=='right')){
			$full_layout = false;
		}

		$link = empty($options['link']) ? null : $options['link'];
		$link_title = empty($options['link_title']) ? null : $options['link_title'];
		$spacing = empty($options['add_spacing']) ? null : $options['add_spacing'];
		
		$columns = 2;
		$i=0;
		$add_class = empty($title) ? ' pc-no-title':'';
		if(!$link && !$link_title){
			$add_class.=' pc-no-link';
		}
		$add_class.=$spacing ? ' pc-spacing':' pc-no-spacing';
		$html= '<div class="portfolio-carousel'.$add_class.'"><div class="pc-header">';
		$html.='<div class="carousel-title"> <h4 class="small-title">'.$title.'</h4>';
		

		$html.='</div></div><div class="pc-wrapper"><div class="pc-holder">';
		$height = $full_layout ? 230 : 150;
		foreach ( $posts as $post ) {
			$preview = pexeto_get_portfolio_preview_img( $post->ID );
			//open a page wrapper div on each first image of the page/slide
			if ( $i%$columns==0 ) {
				$html.='<div class="pc-page-wrapper">';
			}
			//pexeto_build_portfolio_image_html() is located in lib/functions/gallery.php
			$html.=pexeto_get_gallery_thumbnail_html( $post, 3, $height, 'pc-item' );
			if ( ( $i+1 )%$columns==0 || $i+1==sizeof( $posts ) ) {
				//close the page wrapper div on the last image
				$html.='</div>';
			}

			$i++;
		}
		$html.='</div></div><div class="clear"></div>';

		if($link_title){
			$html.='<a class="link-title" href="'.$link.'">'.$link_title.'<span class="more-arrow">&rsaquo;</span></a>';
		}
		$html.='<div class="clear"></div></div>';
		return $html;
	}
}


if ( !function_exists( 'pexeto_get_share_btns_html' ) ) {

	/**
	 * Generates the sharing buttons HTML code.
	 *
	 * @param int     $post_id      the ID of the post that the buttons will be
	 * added to
	 * @param string  $content_type the type of the containing element - can
	 * be a post, page, portfolio or slider
	 * @return string               the HTML code of the buttons
	 */
	function pexeto_get_share_btns_html( $post_id, $content_type ) {
		if ( !in_array( $content_type, pexeto_option( 'show_share_buttons' ) ) ) {
			return '';
		}
		$display_buttons = pexeto_option( 'share_buttons' );
		$permalink = get_permalink( $post_id );
		$title = get_the_title( $post_id );
		$html = '<div class="social-share"><div class="share-title">'
			.__( 'Share', 'pexeto' ).'</div><ul>';

		foreach ( $display_buttons as $btn ) {
			switch ( $btn ) {
			case 'facebook':
				$html.='<li title="Facebook" class="share-item share-fb" data-url="'.$permalink
					.'" data-type="'.$btn.'" data-title="'.$title.'"></li>';
				break;

			case 'googlePlus':
				$html.='<li title="Google+" class="share-item share-gp" data-url="'.$permalink
					.'" data-lang="'.pexeto_option( 'gplus_lang' ).'" data-title="'.$title
					.'" data-type="'.$btn.'"></li>';
				break;

			case 'twitter':
				$html.='<li title="Twitter" class="share-item share-tw" data-url="'.$permalink
					.'" data-title="'.$title.'" data-type="'.$btn.'"></li>';
				break;

			case 'pinterest':
				$img = pexeto_get_portfolio_preview_img( $post_id );
				$img = $img['img'];
				$html.='<li title="Pinterest" class="share-item share-pn" data-url="'.$permalink
					.'" data-title="'.$title.'" data-media="'.$img.'" data-type="'.$btn.'"></li>';
				break;
			}
		}

		$html.='</ul></div><div class="clear"></div>';

		return $html;
	}
}


if ( !function_exists( 'pexeto_get_video_html' ) ) {

	/**
	 * Generates a video HTML. For Flash videos uses the standard flash embed code
	 * and for other videos uses the WordPress embed tag.
	 *
	 * @param string  $video_url the URL of the video
	 * @param string  $width     the width to set to the video
	 */
	function pexeto_get_video_html( $video_url, $width ) {
		$video_html='';
		//check if it is a swf file
		if ( strstr( $video_url, '.swf' ) ) {
			//print embed code for swf file
			$video_html .= '<div class="video-wrap"><OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
			codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
			WIDTH="'.$width.'" id="pexeto-flash" ALIGN=""><PARAM NAME=movie VALUE="'.$video_url.'">
			<PARAM NAME=quality VALUE=high> <PARAM NAME=bgcolor VALUE=#333399> <EMBED src="'.$video_url.'"
			quality=high bgcolor=#333399 WIDTH="'.$width.'" NAME="pexeto-flash" ALIGN=""
			TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer">
			</EMBED> </OBJECT></div>';
		}else {
			$video_html .= apply_filters( 'the_content', '[embed width="'.$width.'"]' . $video_url . '[/embed]' );
		}
		return $video_html;
	}
}


if(!function_exists('pexeto_get_nivo_slider_html')){
	/**
	 * Generates the Nivo slider HTML.
	 * @param  array  $images        array containing the slider images
	 * @param  object  $options       slider options
	 * @param  string  $slider_div_id the ID of the slider div
	 * @param  int  $height        the height of the slider
	 * @param  boolean $static_height sets whether to set a static height or the
	 * height will be dynamic depending on the original image ratio
	 * @return string                 the HTML code of the slider
	 */
	function pexeto_get_nivo_slider_html($images, $options, $slider_div_id, $height, $static_height=true){
		global $pexeto_scripts;

		$style=$static_height ? 'style="max-height:'.$height.'px;"' : 'style="min-height:'.$height.'px;"';
		$html='<div class="nivo-wrapper"><div class="nivo-slider" id="'.$slider_div_id.'" '.$style.'>';	

		foreach ($images as $image) {
			if ( !empty( $image['link'] ) ) { 
				$html.='<a href="'.esc_url( $image['link'] ).'">';
			}
			$html.='<img src="'.$image['url'].'" title="'.$image['description'].'" alt="" />';
			if ( !empty( $image['link'] ) ) { 
				$html.='</a>';
			}
		}

		$html.='</div></div>';

		if ( !isset( $pexeto_scripts['nivo'] ) ) {
			$pexeto_scripts['nivo'] = array();
		}
		$pexeto_scripts['nivo'][]=array( 'selector'=>'#'.$slider_div_id, 'options'=>$options );

		return $html;
	}
}


if(!function_exists('pexeto_get_services_standard_style_html')){

	/**
	 * Generates the HTML code for services boxes of style Photo, Icon and thumbnail.
	 * @param  array $boxes    the boxes data
	 * @param  string $layout   the layout of the boxes : photo, icon, thumbnail
	 * @param  string $title    the title of the section
	 * @param  string $desc     the description of the section
	 * @param  int $columns  the number of columns to display the boxes in
	 * @param  boolean $parallax sets whether to display the boxes in a parallax
	 * style or not
	 * @return string           the HTML code of the boxes
	 */
	function pexeto_get_services_standard_style_html($boxes, $layout, $title, $desc, $columns, $parallax, $link=null){
		$services_class = 'services-wrapper services-'.$layout;
		if($layout != 'thumbnail'){
			$services_class.=' cols-wrapper cols-'.($columns);
		}
		if($parallax){
			$services_class.=' pexeto-parallax';
		}
		$html='<div class="'.$services_class.'">';

		$title_box_included = false;
		if ( !empty( $title ) || !empty( $desc ) || !empty($link)) {
			$title_box_included = true;

			$html.='<div class="services-title-box col">';
			if ( !empty( $title ) ) $html.='<h2>'.$title.'</h2>';
			if ( !empty( $desc ) ) $html.='<p>'.$desc.'</p>';
			if(!empty($link)){
				$html.='<a href="'.esc_url( $link['url'] ).'" class="read-more">'.$link['text'].'<span class="more-arrow">&rsaquo;</span></a>';
			}
			$html.='</div>';
		}

		for ( $i=0; $i<sizeof( $boxes ); $i+=$columns ) {
			$max_index = min( $i+$columns, sizeof( $boxes ) );
			$add_class = ( $i==0 && $title_box_included==true ) ? ' small-wrapper':'';

			for ( $j=$i; $j<$max_index; $j++ ) {

				//print the single box
				$box = $boxes[$j];
				$open_link = empty( $box['box_link'] )?'':'<a href="'.esc_url( $box['box_link'] ).'">';
				$close_link = empty( $box['box_link'] )?'':'</a>';
				$add_class = $j==$max_index-1 && $layout!=='thumbnail'?' nomargin':'';


				$html.='<div class="services-box col'.$add_class.'">';


				if ( !empty( $box['box_image'] ) && $layout!=='thumbnail') {
					//print the box image
					$html.=$open_link;
					$html.='<div class="img-container"><img src="'.$box['box_image'].'" alt="'.esc_attr($box['box_title']).'"/></div>';
					$html.=$close_link;
				}else if($layout=='thumbnail'){
					$html.='<div class="thumbnail-wrapper">';
					$html.='<div class="services-img" style="background-image:url('.$box['box_image'].')"></div>';
				}


				$html.='<div class="services-content"><div class="sc-wraper">';
				$html.='<h3>'.$open_link.$box['box_title'].$close_link.'</h3>';

				if ( !empty( $box['box_desc'] ) ){
					$html.='<p>'.$box['box_desc'].'</p>';
				}

				// if($layout=='thumbnail'){
				// 	$html.=$close_link;
				// }

				$html.='</div>';

				if($layout=='thumbnail'){
					$html.='</div>'; //close the thumbnail-wrapper div
				}

				$html.='</div></div>';
			}

		}

		$html.='<div class="clear"></div></div>';

		return $html;
	}
}


if(!function_exists('pexeto_get_recent_posts_html')){

	/**
	 * Generates the recent posts element HTML.
	 * @param  string $title   the title of the section
	 * @param  int $number  the number of posts to display
	 * @param  int $columns the number of columns to display the posts in
	 * @param  int $cat     the category of the posts to load, set to -1 to
	 * load all the categories
	 * @param  string $layout the layout of the posts : columns | list
	 * @return string          the generated HTML code
	 */
	function pexeto_get_recent_posts_html($title, $number, $columns, $cat, $layout = 'columns'){
		$number = (int)$number;
		$columns = (int)$columns;
		$cat = (int)$cat;

		if(!$number){
			$number = $columns;
		}

		$args = array('posts_per_page'=>$number, 'suppress_filters'=>false);
		if($cat && $cat!=-1){
			$args['cat']=$cat;
		}

		$recent_posts = get_posts($args);

		$html = sprintf('<div class="pexeto-recent-posts rp-%s">', $layout);
		if($title){
			$html.=sprintf('<h3 class="rp-title">%s</h3>', $title);
		}

		if($layout=='columns'){
			$html.= pexeto_get_recent_posts_column_layout_html($recent_posts, $columns);
		}else{
			$html.= pexeto_get_recent_posts_list_layout_html($recent_posts);
		}
		

		$html .= '</div>';

		wp_reset_postdata();

		return $html;
	}
}


if(!function_exists('pexeto_get_recent_posts_column_layout_html')){
	function pexeto_get_recent_posts_column_layout_html($recent_posts, $columns){
		$html='';
		$img_size = pexeto_get_image_size_options($columns, 'blog');

		foreach ($recent_posts as $i=>$p) {
			setup_postdata( $p );
			$format = get_post_format( $p );
			$add_class = ($i%$columns==$columns-1) ? ' nomargin':'';

			if($i%$columns==0){
				$html.=sprintf('<div class="cols-wrapper cols-%s">', $columns);
				$opened = true;
			}

			$html.=sprintf('<div class="col rp%s">', $add_class);

			if(!in_array($format, array('quote', 'aside'))){
				$html.='<div class="rp-header">';
				if($format=='video'){
					//print video
					$video_url = pexeto_get_single_meta( $p->ID, 'video' );
					if ( $video_url ) {
						$html.=pexeto_get_video_html( $video_url, $img_size['width'] );
					}
				}elseif($format=='gallery'){
					//print Nivo slider
					$images = pexeto_get_nivo_post_images($p, $img_size);
					
					$options = pexeto_get_nivo_args('_post');
					
					$slider_div_id = 'post-gallery-'.$p->ID.pexeto_generate_nivo_id();

					$html.=pexeto_get_nivo_slider_html($images, $options, $slider_div_id, $img_size['height'], $img_size['crop']);

				}elseif(has_post_thumbnail($p->ID)){
					//print thumbnail image
					$thumb_id = get_post_thumbnail_id( $p->ID );
					$thumb = wp_get_attachment_image_src( $thumb_id, 'full' );
					$html.=sprintf('<a href="%s"><img src="%s" alt="%s" /></a>',
						get_permalink( $p->ID),
						pexeto_get_resized_image( $thumb[0], $img_size['width'], $img_size['height'], $img_size['crop'] ),
						esc_attr(get_post_meta($thumb_id, '_wp_attachment_image_alt', true)));
				}

				$html.='</div>';
			}

			
			if(!in_array($format, array('quote', 'aside'))){
				//print title and excerpt
				$html.=sprintf('<h4 class="rp-post-title"><a href="%s">%s</a></h4>', get_permalink( $p->ID ), $p->post_title);
				$html.=sprintf('<p>%s</p>', get_the_excerpt());
				//add a read more link
				$html.=sprintf('<a class="read-more" href="%s">%s <span class="more-arrow">&rsaquo;</span></a>', 
					get_permalink( $p->ID ), 
					__('Read More', 'pexeto'));
			}else{
				if($format=='quote'){
					//print quote
					$html.=sprintf('<div class="format-quote"><blockquote>%s</blockquote></div>', $p->post_content);
				}else{
					//print aside
					$html.=sprintf('<div class="format-aside"><aside><p>%s</p></aside></div>', $p->post_content);
				}
			}

			$html.='</div>';  //close rp div

			if($i%$columns==$columns-1){
				$html.='</div>';  //close cols wrapper div
				$opened = false;
			}
		}

		if($opened){
			$html.='</div>'; //close cols wrapper div
		}

		return $html;
	}
}


if(!function_exists('pexeto_get_recent_posts_list_layout_html')){
	function pexeto_get_recent_posts_list_layout_html($recent_posts){
		$html='<ul>';

		foreach ($recent_posts as $i=>$p) {
			setup_postdata( $p );

			$cats = get_the_category( $p->ID );
			$cat_names = array();
			$cat_string = '';

			if(sizeof($cats)>0){
				foreach ($cats as $cat) {
					$cat_names[] = sprintf('<a href="%s">%s</a>', get_category_link( $cat->term_id ), $cat->cat_name);
				}
				$cat_string = '<span class="rp-cat">'.implode(' / ', $cat_names).'</span>';
			}

			$html.=sprintf('<li><span class="rp-date">%1$s</span><div class="rp-info"><h3><a href="%2$s" data-hover="%3$s">%3$s</a></h3></div>%4$s</li>', 
				get_the_time(get_option('date_format'), $p->ID), get_permalink( $p->ID), $p->post_title, $cat_string);
		}

		$html.='</ul>';

		return $html;
	}
}


if(!function_exists('pexeto_get_testimonial_slider_html')){
	function pexeto_get_testimonial_slider_html($set, $autoplay=true){
		$data_string = '';
		if($autoplay){
			$data_string.='data-autoplay="true"';
		}

		$html = sprintf('<div class="testimonial-slider" %s>', $data_string);

		$tm_set = PexetoCustomPageHelper::get_instance_data( PEXETO_TESTIMONIALS_POSTTYPE, $set, 'slug' );
		$tms = $tm_set['posts'];
		$data_keys=array( 'name', 'testimonial', 'image', 'occupation', 'organization', 'organization_link');

		foreach ($tms as $tm) {
			$tm_data=pexeto_get_multi_meta_values( $tm->ID, $data_keys, PEXETO_CUSTOM_PREFIX );

			$addClass=$tm_data['image']?'':' no-image';
			
			$html.='<div class="testimonial-container'.$addClass.'">';
			$html.='<div class="testimonial-wrapper"><div class="testimonial-info">';
			if ( $tm_data['image'] ) $html.='<img class="img-frame testimonial-img" src="'.
				pexeto_get_resized_image($tm_data['image'], 150, 150, true).'" alt="'.esc_attr($tm_data['name']).'" />';
			$html.='<div class="testimonials-details"><h2>'.$tm_data['name'].'</h2><span>'.$tm_data['occupation'];
			if ( $tm_data['organization'] ) {
				$html.=' / ';
				if ( $tm_data['organization_link'] ) $html.='<a href="'.esc_url( $tm_data['organization_link'] ).'">';
				$html.=$tm_data['organization'];
				if ( $tm_data['organization_link'] ) $html.='</a>';
			}
			$html.='</span></div></div>';
			$html.='<blockquote><p>'.do_shortcode( $tm_data['testimonial'] ).'</p></blockquote>';
			$html.='</div></div>';
		}

		$html.='</div>';
		return $html;
	}
}
