<?php
/*
	Plugin Name: Simple Google Map
	Plugin URI: http://clarknikdelpowell.com/wordpress/simple-google-map/
	Description: This plugin will embed a google map using shortcode or as a widget.
	Author: Taylor Gorman
	Author URI: http://clarknikdelpowell.com
	Version: 2.0

	Copyright 2009  Clark Nikdel Powell  (email : taylor@cnpstudio.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*
===============================================================================

   set default options when plugin is activated, remove options when deactivated

===============================================================================
*/

global $SGMdefaults;
$SGMdefaults = array('zoom'=>'12', 'type'=>'ROADMAP', 'directionsto'=>'', 'content'=>'');

register_activation_hook( __FILE__, 'onActivation' );
register_deactivation_hook( __FILE__, 'onDeactivation' );

function onActivation() {
	global $SGMdefaults;
	update_option('SGMoptions', $SGMdefaults);
	$SGMcss = "#SGM {width:100%; height:300px;}
#SGM .infoWindow {line-height:13px; font-size:10px;}
#SGM input {margin:4px 4px 0 0; font-size:10px;}
#SGM input.text {border:solid 1px #ccc; background-color:#fff; padding:2px;}";
	update_option('SGMcss', $SGMcss);
}
function onDeactivation() { delete_option('SGMoptions'); }

/*
===============================================================================

   insert styles into <head>

===============================================================================
*/

$SGMoptions = get_option('SGMoptions');

if (!$SGMoptions['nostyle']) {
	
	add_action ('wp_head', 'printStyles');
	
	function printStyles() { 
		print "<!-- styles for Simple Google Map -->\n<style type='text/css'>\n";
		print get_option('SGMcss');
		print "\n</style>\n<!-- end styles for Simple Google Map -->\n";
	}

}

/*
===============================================================================

   the map code (so it's not here twice)

===============================================================================
*/

function SGMprintmap($lat, $lng, $zoom, $type, $content, $directionsto) {
	
	$SGMoptions = get_option('SGMoptions'); // get options defined in admin page
	
	if (!$lat) {$lat = '0';}
	if (!$lng) {$lng = '0';}
	if (!$zoom) {$zoom = $SGMoptions['zoom'];} // 1-19
	if (!$type) {$type = $SGMoptions['type'];} // ROADMAP, SATELLITE, HYBRID, TERRAIN
	if (!$content) {$content = $SGMoptions['content'];}
	
	$content = str_replace('&lt;', '<', $content);
	$content = str_replace('&gt;', '>', $content);
	$content = mysql_escape_string($content);
	if ($directionsto) { $directionsForm = "<form method=\"get\" action=\"http://maps.google.com/maps\"><input type=\"hidden\" name=\"daddr\" value=\"".$directionsto."\" /><input type=\"text\" class=\"text\" name=\"saddr\" /><input type=\"submit\" class=\"submit\" value=\"Directions\" /></form>"; }

	return "
	<script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=false'></script>
	<script type='text/javascript'>
		function makeMap() {
			var latlng = new google.maps.LatLng(".$lat.", ".$lng.")
			
			var myOptions = {
				zoom: ".$zoom.",
				center: latlng,
				mapTypeControl: true,
				mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
				navigationControl: true,
				navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
				mapTypeId: google.maps.MapTypeId.".$type."
			};
			var map = new google.maps.Map(document.getElementById('SGM'), myOptions);
			
			var contentString = '<div class=\"infoWindow\">".$content.$directionsForm."</div>';
			var infowindow = new google.maps.InfoWindow({
				content: contentString
			});
			
			var marker = new google.maps.Marker({
				position: latlng,
				map: map,
				title: ''
			});
			
			google.maps.event.addListener(marker, 'click', function() {
			  infowindow.open(map,marker);
			});
		}
		window.onload = makeMap;
	</script>
	
	<div id='SGM'></div>
	";
	
}

/*
===============================================================================

   simple google map shortcode

===============================================================================
*/

add_shortcode('SGM', 'googleMap');

function googleMap($atts) {
	return SGMprintmap($atts['lat'], $atts['lng'], $atts['zoom'], $atts['type'], $atts['content'], $atts['directionsto']);
}

/*
===============================================================================

   simple google map widget

===============================================================================
*/

add_action('widgets_init', create_function('', 'return register_widget("SGMwidget");'));

class SGMwidget extends WP_Widget {

	// constructor
	function SGMwidget() {
		$widget_ops = array('classname' => 'SGMwidget', 'description' => __( 'Add a google map to your blog or site') );
		$this->WP_Widget('module', __('Simple Google Map'), $widget_ops);
	}
	
	// output the content of the widget
	function widget($args, $instance) {		
		extract( $args );
		print $before_widget;
		if ($instance['title']) { print $before_title.$instance['title'].$after_title; }
		print SGMprintmap($instance['lat'], $instance['lng'], $instance['zoom'], $instance['type'], $instance['content'], $instance['directionsto']);
		print $after_widget;
	}
	
	// process widget options to be saved
	function update($new_instance, $old_instance) {		
		print_r($old_instance);
		print_r($new_instance);
		return $new_instance;
	}
	
	// output the options form on admin
	function form($instance) {
		global $wpdb;
		$title = esc_attr($instance['title']);
		$lat = esc_attr($instance['lat']);
		$lng = esc_attr($instance['lng']);
		$zoom = esc_attr($instance['zoom']);
		$type = esc_attr($instance['type']);
		$directionsto = esc_attr($instance['directionsto']);
		$content = esc_attr($instance['content']);
		?>
			<p>
			<label for="<?php print $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('title'); ?>" name="<?php print $this->get_field_name('title'); ?>" type="text" value="<?php print $title; ?>" />
			</p>
			<p>
			<label for="<?php print $this->get_field_id('lat'); ?>"><?php _e('Latitude:'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('lat'); ?>" name="<?php print $this->get_field_name('lat'); ?>" type="text" value="<?php print $lat; ?>" />
			</p>
			<p>
			<label for="<?php print $this->get_field_id('lng'); ?>"><?php _e('Longitude:'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('lng'); ?>" name="<?php print $this->get_field_name('lng'); ?>" type="text" value="<?php print $lng; ?>" />
			</p>
			<p>
			<label for="<?php print $this->get_field_id('zoom'); ?>"><?php _e('Zoom Level: <small>(1-19)</small>'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('zoom'); ?>" name="<?php print $this->get_field_name('zoom'); ?>" type="text" value="<?php print $zoom; ?>" />
			</p>
			<p>
			<label for="<?php print $this->get_field_id('type'); ?>"><?php _e('Map Type:<br /><small>(ROADMAP, SATELLITE, HYBRID, TERRAIN)</small>'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('type'); ?>" name="<?php print $this->get_field_name('type'); ?>" type="text" value="<?php print $type; ?>" />
			</p>
			<p>
			<label for="<?php print $this->get_field_id('directionsto'); ?>"><?php _e('Address for directions:'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('directionsto'); ?>" name="<?php print $this->get_field_name('directionsto'); ?>" type="text" value="<?php print $directionsto; ?>" />
			</p>
			<p>
			<label for="<?php print $this->get_field_id('content'); ?>"><?php _e('Info Bubble Content:'); ?></label>
			<textarea rows="7" class="widefat" id="<?php print $this->get_field_id('content'); ?>" name="<?php print $this->get_field_name('content'); ?>"><?php print $content; ?></textarea>
			</p>
		<?php 
	}
	
} // SGMwidget widget

/*
===============================================================================

   simple google map settings

===============================================================================
*/

add_action('admin_menu', 'pluginMenu', 1);

function pluginMenu() { // Add a new submenu under settings
	add_options_page('Simple Google Map', 'Simple Google Map', 'activate_plugins', 'simple-google-map', 'SGMoptions');
}

function SGMoptions() { ?>

	<?php
	global $SGMdefaults;
	if ($_POST['submit']) {
		$_POST['content'] = stripslashes($_POST['content']);
		$SGMoptions = array();
		foreach ($SGMdefaults as $name => $value) {
			$SGMoptions[$name] = $_POST[$name];
		}
		$SGMoptions['editCSS'] = $_POST['editCSS'];
		$SGMoptions['nostyle'] = $_POST['nostyle'];
		$SGMcss = $_POST['css'];
		
		update_option('SGMoptions', $SGMoptions);
		update_option('SGMcss', $SGMcss);
		$message = '<div id="message" class="updated"><p>Simple Google Map settings updated.</p></div>';
	} else {
		$SGMoptions = get_option('SGMoptions');
		$SGMcss = get_option('SGMcss');
	}
	?>

	<style type="text/css">
	.formField {
		width: 60%;
		padding: 5px 0px 10px 0px;
	}
	.form-table input[type="text"], .form-table textarea {
		width: 50%;
	}
	.form-table th {
		width: 150px;
	}
	#SGMcss {
		display: block;
		margin-top: 8px;
		width: 93%;
	}
	#CNPlink {
		display: block;
		float: left;
		background-image: url(/wp-content/plugins/simple-google-map/CNP.gif);
		width: 31px;
		height: 29px;
		margin: 16px 8px 0 0;
	}
	#CNPlink:hover {
		background-position: 0px -29px;
	}
	</style>
	<script type="text/javascript">
		jQuery(function($) {
			$('#message').fadeTo(1500, 1).slideUp();
			if (!$('#editCSS').attr('checked')) {
				$('#SGMcss').hide();
			}
			$('#editCSS').change(function() {
				$('#SGMcss').slideToggle(200);
			});
		});
	</script>

	<div class="wrap">
	
		<a href="http://www.clarknikdelpowell.com" target="_blank" id="CNPlink"></a><h2>Simple Google Map</h2>
		<?php print $message; ?>
		<p>Here you can set the default options for every Simple Google Map on your pages/posts/sidebars. You can override these settings for any one Simple Google Map by simply adding the proper options to the shortcode/widget of that map. Leave them undefined or blank and these settings will apply.</p>
		<p>If you need help getting the latitude and longitude of your location try <a href="http://stevemorse.org/jcal/latlon.php" target="_blank">this site</a>.</p>

		<form action="" method="post">

		<table class="form-table">
		
			<tr valign="top">
			<th><label for="zoom">Zoom Level</label></th>
			<td>
			<input name="zoom" type="text" value="<?php print $SGMoptions['zoom']; ?>" /><br />
			<span class="description">integer from 1 to 19</span>
			</td>
			</tr>
			
			<tr valign="top">
			<th><label for="type">Map Type</label></th>
			<td>
			<input type="text" name="type" value="<?php print $SGMoptions['type']; ?>" /><br />
			<span class="description">ROADMAP, SATELLITE, HYBRID, or TERRAIN (must be in uppercase)</span>
			</td>
			</tr>
			
			<tr valign="top">
			<th><label for="content">Info Bubble Content</label></th>
			<td><textarea name="content" /><?php print $SGMoptions['content']; ?></textarea></td>
			</tr>
			
			<tr>
			<th scope="row" colspan="2" class="th-full">
			<label for="editCSS"><input type="checkbox" name="editCSS" id="editCSS" <?php if ($SGMoptions['editCSS']) {print 'checked="checked" ';} ?>/> I want to edit the Simple Google Map CSS</label>
			<textarea name="css" id="SGMcss" rows="6"><?php print $SGMcss ?></textarea>
			</th>
			</tr>
			
			<tr>
			<th scope="row" colspan="2" class="th-full">
			<label for="nostyle"><input type="checkbox" name="nostyle" <?php if ($SGMoptions['nostyle']) {print 'checked="checked" ';} ?>/> Remove the Simple Google Map CSS, I will style it in the theme's stylesheet.</label>
			</th>
			</tr>
			
		</table><!-- form-table -->
		
		<p class="submit"><input type="submit" class="button-primary" name="submit" value="Save Changes" /></p>
		
		</form>

	</div><!-- wrap -->

<?php }

?>