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
add_action('init', 'summon_mage_maps');
add_shortcode('map', 'mage_map');
if(mage_get_option('maps','mage_map_field_type') == 1) add_action('admin_init', 'mage_totems');
$types =  mage_get_option('maps','post_type_maps');
add_filter( 'mage_capture_types', 'mage_map_support_types', 10, 2 );

if (is_array($types)){
	foreach ($types as $type){
		add_filter( 'mage_capture_'.$type, 'mage_process_maps', 10, 2 );
	}
}
function mage_map_support_types($filter){
	$types =  mage_get_option('maps','post_type_maps');
	if (is_array($types)){
		foreach ($types as $type){
			$filter[]= $type;
		}
	}
	return $filter;
}
function mage_process_maps($cast,$form){
	if(mage_get_option('maps','mage_map_address_fields',1) == 2){
		$fields = apply_filters('mage_maps_meta_fields',array('address_1'=>__('Address 1','mage-google-maps'),'address_2'=>__('Address 2','mage-google-maps'),'city'=>__('City','mage-google-maps'),'state'=>__('State','mage-google-maps'),'zip'=>__('Zip','mage-google-maps'),'country'=>__('Country','mage-google-maps')));
		foreach ($fields as $field => $name){
			$cast['mage_map_key_'.$field] = isset($form['mage_map_key_'.$field])? $form['mage_map_key_'.$field]:'';
		}
	} else {
		$cast['mage_map_key_address_full'] = isset($form['mage_map_key_address_full'])? $form['mage_map_key_address_full']: '';
	}
	return $cast;
}
function mage_totems(){	
	$types =  mage_get_option('maps','post_type_maps');
	if (is_array($types)){
		foreach ($types as $type){
			add_meta_box("mage_map_rune", esc_html__( 'Mage Google Maps','mage-google-maps'), "mage_map_rune", $type, "normal","high");
			add_meta_box('mage_map_preview', esc_html__( 'Mage Map Preview', 'mage-forms' ), 'mage_map_preview', $type, 'side','low');
		}
	}
}
function mage_map_rune($callback_args) { 
	global $post; 
	$post_type = $post->post_type;	
	$cast = array();
	$cast = maybe_unserialize(get_post_meta($post->ID,'cast',true));
	$output = '';
	if(mage_get_option('maps','mage_map_address_fields') == 2){
		$fields = apply_filters('mage_maps_meta_fields',array('address_1'=>__('Address 1','mage-google-maps'),'address_2'=>__('Address 2','mage-google-maps'),'city'=>__('City','mage-google-maps'),'state'=>__('State','mage-google-maps'),'zip'=>__('Zip','mage-google-maps'),'country'=>__('Country','mage-google-maps')));
		foreach ($fields as $field => $name){
			if(mage_get_option('maps','mage_map_key_'.$field) == 1){
				$val = isset($cast['mage_map_key_'.$field])? $cast['mage_map_key_'.$field]:'';
				$output .= '<div class="form-group section-text">
				<label class="col-lg-2 control-label" for="mage_map_key_'.$field.'">'.$name.'</label><div class="col-lg-8"><input id="mage_map_key_'.$field.'" name="mage_map_key_'.$field.'" type="text" class="form-control" value="'.$val.'"></div>
				</div>';
			}
		}
	} else {
		$val = isset($cast['mage_map_key_address_full'])? $cast['mage_map_key_address_full']: mage_get_option('maps','mage_maps_region');
		$output .= '<div class="form-group section-text"><label class="col-lg-2 control-label" for="mage_map_key_address_full">'.__( 'Full Address','mage-google-maps').'</label><div class="col-lg-8"><input id="mage_map_key_address_full" name="mage_map_key_address_full" type="text" class="form-control" value="'.$val.'"></div></div>';
	}
	wp_nonce_field( 'mage_'.$post_type.'_save', '_wpnonce_mage_'.$post_type.'_save' );
	echo $output;
}
function mage_map_preview($callback_args) { 
	global $post; 
	$post_type = $post->post_type;	
	$cast = array();
	$cast = maybe_unserialize(get_post_meta($post->ID,'cast',true));
	$output = mage_map_address_output($post->ID);
	echo do_shortcode('[map address="'.$output.'"]');
}
function summon_mage_maps(){	
	global $post;
	$key = mage_get_option('maps','mage_maps_api_key');
	$key = empty($key)? '' : '?key='.$key;
	wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js'.$key,null,null, true);
	wp_register_script('gmaps', MAGECAST_MAPS_SOURCE.'js/gmaps.js',array('google-maps'),null, true);
	add_action('admin_menu', 'summon_mage_maps_admin');	
	add_action( 'wp_enqueue_scripts', 'add_mage_maps_scripts',12);
	if (mage_get_option('maps','mage_maps_css','0') !== '1') add_action('wp_enqueue_scripts','add_mage_maps_styles',20);	
}
function summon_mage_maps_admin(){
	add_action('admin_print_scripts-post.php', 'mage_maps_admin_scripts' );
	add_action('admin_print_scripts-post-new.php', 'mage_maps_admin_scripts' );
}
function mage_maps_admin_scripts() {
	global $post;
	$types = mage_get_option('maps','post_type_maps');
	if(is_array($types) && is_object($post) && in_array($post->post_type,$types)){
		wp_enqueue_script('google-maps');
		wp_enqueue_script('gmaps');
	}
}
function add_mage_maps_scripts() {
	wp_enqueue_script('google-maps');
	wp_enqueue_script('gmaps');
}

function add_mage_maps_styles() {
	wp_enqueue_style('mage-maps', MAGECAST_MAPS_SOURCE.'css/mage-maps.css');
}
function mage_map($atts, $content = null ) {
	$params = apply_filters('mage_maps_parameters',array(
		'width' =>mage_get_option('maps','mage_maps_width','100%'),
		'height' =>mage_get_option('maps','mage_maps_height','300px'),
		'zoom'=>mage_get_option('maps','mage_maps_zoom',14),
		'address' => '',
		'ui'=>mage_get_option('maps','mage_maps_ui'),
		'title' => '',
		'show'=>'',
		'url'=>'',
		'style' => '',
		'class' => '',
		'size'=>'',
		'color'=>'',
		'id'=>'',
		'static'=>false));
	extract(shortcode_atts($params, $atts));
	if (empty($show)) $show = is_singular()? 'single' : 'all'; 
	$style=magex($style,'style="',magex($width,'width:',';').magex($height,'height:',';').'" ','style="'.magex($width,'width:',';').magex($height,'height:',';').'" ');	
	$icon = mage_maps_marker_default(); 
	$ids = array(); 
	$maps = ''; 
	global $query_string, $post;
	if ($show=='single'){
		if (is_object($post)) $ids[] = $map_id = $post->ID;
	} elseif ($show=='all') {	
		$map_query = new WP_Query( $query_string . '&fields=ids' );
		if(isset($map_query->posts) && !empty($map_query->posts)) foreach($map_query->posts as $id) $ids[] = $id;
		$map_id = 'all';
	}
	if (!empty($ids) && empty($address)){
		foreach($ids as $id) {	
			$title = get_the_title($id);
			$marker = array('icon' => apply_filters('mage_maps_marker_output',$icon,$id), 'size'=>$size,'color'=>$color,'title'=>$title);
			if ($show=='all') {
				$post = get_post($id);
				setup_postdata( $post ); 	
				if (mage_get_option('maps','mage_maps_auto_link')) $marker['url'] = get_permalink($id);
			}	
			$address = mage_map_address_output($id);
			$maps .= mage_maps_geocode($address, $marker, ($show=='all'? true : false));
		}
	} else {
		$address = empty($address)? $address: mage_get_option('maps','mage_maps_region','');
		$maps .= mage_maps_geocode($address);
	}
	$output = '<div class="mage-map '.$class.' mage-show-'.$show.'" '.$style.'><div id="map-'.$map_id.'" class="map map-'.$map_id.'" style="width:100%; height:100%;"></div></div>
	<script type="text/javascript">    
		var bounds = [];
		jQuery(document).ready(function(){
			map = new GMaps({
				div: \'#map-'.$map_id.'\',
				lat: -12.043333,
				lng: -77.028333,
				'.($ui? 'disableDefaultUI: true,' : '').'
				zoom: '.$zoom.',		
			});'
			.$maps.'	  
		});	
	  </script>';
	return $output;
}
function mage_maps_add_marker($address,$atts = array()){
	$atts = wp_parse_args($atts,array('lat'=>'latlng.lat()','lng'=>'latlng.lng()'));
	$marker = '';
	foreach ($atts as $att => $val){
		if (!empty($val)) {
			if ($att == 'url') {
				$marker .= "click: function(e) {window.location.href='".$val."';},\n";
			} elseif (!in_array($att, array('lat','lng'))) {
				$val = is_array($val)? '"'.$val['src'].'"' : '"'.$val.'"';
				$marker .= $att.": ".$val.",\n";
			} else {
				$marker .= $att.": ".$val.",\n";
			}
			
		}
	}
	$marker = apply_filters('mage_maps_marker_callback',$marker,$address);
	return 'map.addMarker({
				'.$marker.'
			});'; 
}
function mage_map_address_output($id = null){
	if (is_null($id)) return '';
	$cast = maybe_unserialize(get_post_meta($id,'cast',true));
	if(mage_get_option('maps','mage_map_address_fields') == 2){
		$fields = array('address_1','address_2','city','state','zip','country');
		$address = array();
		foreach($fields as $field){
			$address[$field]=mage_get_option('maps','mage_map_key_'.$field)? mage_get_option('maps','mage_map_key_'.$field) : false;
			if ($address[$field] == 1) {
				$address[$field] = isset($cast['mage_map_key_'.$field])? $cast['mage_map_key_'.$field]: '';
			} else {
				$address[$field] = $address[$field] && get_post_meta($id,$address[$field],true)? get_post_meta($id,$address[$field],true): '';
			}
		}
		$address['address_2'] = !empty($address['address_2'])? ' '.$address['address_2']: '';
		$address['city'] = !empty($address['city'])? $address['city'].', ': '';
		$address['state'] = !empty($address['state'])? $address['state'].' ': '';	
		$address['zip'] = !empty($address['zip'])? $address['zip'].', ': '';
		$streetaddress = !empty($address['address_1'])? $address['address_1'].$address['address_2'].', ':  '';
		return $streetaddress.$address['city'].$address['state'].$address['zip'].$address['country'];
	} else {
		$address = isset($cast['mage_map_key_address_full'])? $cast['mage_map_key_address_full']: mage_get_option('maps','mage_maps_region');
		return $address;	
	}
	
}
function mage_maps_geocode($address,$marker = array(), $fitzoom = false){
	$map = 'GMaps.geocode({
				address: \''.$address.'\',
				callback: function(results, status) {
					if (status == \'OK\') {
						var latlng = results[0].geometry.location;
						map.setCenter(latlng.lat(), latlng.lng());
						'.mage_maps_add_marker($address,$marker).($fitzoom?"\nmap.fitZoom();\n" : '').'
					}
				}
			});';
	return $map;
}
function mage_maps_marker_default(){
	$mark = mage_get_option('maps','mage_maps_marker','');
	if (is_array($mark)) $mark = isset($mark['src'])? $mark['src']:'';
	return apply_filters('mage_maps_marker_default',$mark);
}
