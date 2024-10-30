<?php
/*
Mage Google Maps
*/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
?>
<?php
if (!defined('MAGECAST_MAPS')) exit;
add_action( 'widgets_init', 'mage_maps_widget' );
function mage_maps_widget() {
	register_widget('Mage_Google_Maps');
}
class Mage_Google_Maps extends WP_Widget {
	function Mage_Google_Maps() {
		$widget_data = array('classname' => 'widget_mage_maps mage_google_maps', 'description' => __( 'Display a Google Map with custom settings.' ) );
		$this->__construct('mage-google-maps', __('Mage Google Maps'), $widget_data);
		$this->alt_option_name = 'widget_mage_maps';
		add_action( 'wp_insert_post', array(&$this, 'flush_widget_cache'));
		add_action( 'transition_comment_status', array(&$this, 'flush_widget_cache') );
	}	

	function flush_widget_cache() {
		wp_cache_delete('widget_mage_maps', 'widget');
	}

	function widget( $args, $instance ) {
		$cache = wp_cache_get('widget_mage_maps', 'widget');
		if (!is_array($cache))$cache = array();
		if (!isset($args['widget_id']))$args['widget_id'] = $this->id;
		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];
			return;
		}
		extract($args, EXTR_SKIP);
 		$output = '';
		$title = isset( $instance['title']) ? $instance['title'] : '';
		$title = apply_filters('widget_title', $title, $instance, $this->id_base );
		$width = isset($instance['width'])? esc_attr($instance['width']) : mage_get_option('maps','mage_maps_width','100%');
		$height = isset($instance['height'])? esc_attr($instance['height']) : mage_get_option('maps','mage_maps_height','300px');
		$zoom = isset($instance['zoom']) && !empty($instance['zoom'])?absint($instance['zoom']) : mage_get_option('maps','mage_maps_zoom',14);
		$address = isset($instance['address'])? esc_attr($instance['address']) : '';	
		$ui = mage_get_option('maps','mage_maps_ui');
		
		$output .= $before_widget;
		if ($title)$output .= $before_title . $title . $after_title;
		$show = is_singular()? 'single' : 'all'; 
		$style= 'style="'.magex($width,'width:',';').magex($height,'height:',';').'" ';
		$icon = apply_filters('mage_maps_marker_default','');
		$ids = array(); 
		$maps = ''; 
		global $query_string, $post;
		if ($show=='single'){
			if (!is_object($post)) return '';
			$ids[] = $map_id = $post->ID;
		} elseif ($show=='all') {	
			$map_query = new WP_Query( $query_string . '&fields=ids' );
			if(isset($map_query->posts) && !empty($map_query->posts))foreach($map_query->posts as $id) $ids[] = $id;
			$map_id = 'all';
		}
		if (!empty($ids) && empty($address)){
			foreach($ids as $id) {	
				$marker = array('icon' => apply_filters('mage_maps_marker_output',$icon,$id),'title'=>get_the_title($id));
				if ($show=='all') {
					$post = get_post($id);
					setup_postdata( $post ); 
					if (mage_get_option('maps','mage_maps_auto_link')) $marker['url'] = get_permalink($id);
				}
				$address = mage_map_address_output($id);				
				$maps .= mage_maps_geocode($address, $marker, ($show=='all'? true : false));
			}
		} else {
			$address = !empty($address)? $address : mage_get_option('maps','mage_maps_region');
			$maps .= mage_maps_geocode($address);
		}
	$output = '<div class="mage-map '.$class.' mage-show-'.$show.'" '.$style.'><div id="widget-map-'.$map_id.'" class="mage-map-inner map-'.$map_id.'" style="width:100%; height:100%;"></div></div><script type="text/javascript">
		var bounds = [];
    	jQuery(document).ready(function(){
      	map = new GMaps({
       		div: \'#widget-map-'.$map_id.'\',
			lat: -12.043333,
        	lng: -77.028333,		
			'.($ui? 'disableDefaultUI: true,' : '').'
			zoom: '.$zoom.',		
      	}); 
	  	'.$maps.'	  
		});</script>';
		$output .= $after_widget;
		echo $output;
		$cache[$args['widget_id']] = $output;
		wp_cache_set('widget_mage_maps', $cache, 'widget');				
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['height'] = strip_tags($new_instance['height']);
		$instance['address'] = strip_tags($new_instance['address']);
		$instance['zoom'] = (int) $new_instance['zoom'];
		$this->flush_widget_cache();
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_mage_maps']) )delete_option('widget_mage_maps');
		return $instance;
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$width = isset($instance['width'])? esc_attr($instance['width']) : mage_get_option('maps','mage_maps_width','100%');
		$height = isset($instance['height'])? esc_attr($instance['height']) : mage_get_option('maps','mage_maps_height','300px');
		$zoom = isset($instance['zoom']) && !empty($instance['zoom'])? absint( $instance['zoom'] ) : mage_get_option('maps','mage_maps_zoom',14);
		$address = isset($instance['address'])? esc_attr($instance['address']) : '';	
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
        <p><label for="<?php echo $this->get_field_id('address'); ?>"><?php _e('Address:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('address'); ?>" name="<?php echo $this->get_field_name('address'); ?>" type="text" value="<?php echo $address; ?>" /><br /><small><?php _e('Leave Blank for Auto-Display'); ?></small></p>
		<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:'); ?></label>
		<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" size="5" /></p>
        <p><label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:'); ?></label>
		<input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" size="5" /></p>
        <p><label for="<?php echo $this->get_field_id('zoom'); ?>"><?php _e('Zoom:'); ?></label>
		<input id="<?php echo $this->get_field_id('zoom'); ?>" name="<?php echo $this->get_field_name('zoom'); ?>" type="text" value="<?php echo $zoom; ?>" size="3" /></p>
<?php
	}
}