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

/* Basic plugin definitions */
/*
 * @level 		Casting
 * @author		Mage Cast 
 * @url			http://magecast.com
 * @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
?>
<?php
if (!defined('MAGECAST_MAPS')) exit;
add_action('init', 'summon_magecast_maps');
function summon_magecast_maps(){	
	if (current_user_can('switch_themes')) {	
		add_action('admin_init', 'mage_maps_init' );	
		add_action('admin_menu', 'summon_magecast_maps_dashboard');				
	}
}
function summon_magecast_maps_dashboard(){
	global $themename, $shortname, $submenu, $menu;
	$mage_options_page = add_submenu_page('mage_cast','Maps','Maps','manage_options','mage_maps','mage_maps_page');	
	add_action('admin_print_scripts-'.$mage_options_page, 'mage_load_admin_scripts');			
	add_action('admin_print_styles-'.$mage_options_page, 'mage_load_admin_styles' );	
}
function mage_maps_init() {			
	global $pagenow;	
	if('media-upload.php' == $pagenow || 'async-upload.php' == $pagenow )add_filter( 'gettext', 'replace_mage_upload_text',1,3);
	add_filter( 'mage_sanitize_text', 'sanitize_text_field' );
	add_filter( 'mage_sanitize_select', 'mage_sanitize_enum', 10, 2);
	add_filter( 'mage_sanitize_radio', 'mage_sanitize_enum', 10, 2);
	add_filter( 'mage_sanitize_legend', 'mage_sanitize_enum', 10, 2);
	add_filter( 'mage_sanitize_images', 'mage_sanitize_enum', 10, 2);
	add_filter( 'mage_sanitize_checkbox', 'mage_sanitize_checkbox' );
	add_filter( 'mage_sanitize_multicheck', 'mage_sanitize_multicheck', 10, 2 );
	add_filter( 'mage_sanitize_upload', 'mage_sanitize_upload' );
	$mage_settings = get_option('mage_maps');
	$id = 'magecast_maps';
	if (isset($mage_settings['id'])){
		if ($mage_settings['id'] !== $id) { 
			$mage_settings['id'] = $id;
			update_option('mage_maps',$mage_settings);		
		}
	} else { 
		$mage_settings['id'] = $id;
		update_option('mage_maps' ,$mage_settings);
	}
	if (get_option($mage_settings['id']) === false) mage_setdefaults('maps');
	register_setting('mage_maps' ,$mage_settings['id'],'mage_maps_validate' );
}
function mage_maps_page() {
	$directory = plugin_dir_path(dirname( __FILE__));	
	$icons = mage_core_get_icons($directory);
?>
<script type="text/javascript">
var mageURL = "<?php echo plugins_url( '/', dirname(__FILE__) ); ?>";
var icons = [<?php echo $icons; ?>];
</script>
<div id="mage-wrap">
<?php settings_errors(); ?>
<div id="container" class="row">  
    <form id="mage-form" method="post" class="form-horizontal" action="options.php">
		<?php settings_fields('mage_maps'); ?>
		<div id="magecast-content" class="magecast-content tab-content"><?php mage_summon_fields(mage_maps_options(),'maps'); ?></div>
	<!-- Footer Navbar and Submit -->         
		<div class="navbar navbar-static-bottom">
            	<input type="submit" class="btn btn-brown" name="update" id="update" value="<?php esc_attr_e( 'Save Options', 'mage_maps' ); ?>" />        	
 		</div>
    </form>
</div>
</div><?php
}
function mage_maps_validate($input) {
	if (!current_user_can('manage_options'))die('Insufficient Permissions');
	$clean = array();
	$options = mage_maps_options();	
	foreach ($options as $option ){
		if (!isset($option['id']))continue;
		if (!isset($option['type']))continue;
		$id = cog($option['id']);
		if (!isset($input[$id])) {
			if (in_array($option['type'], array('text','textarea','select','radio','color','upload','legend')))$input[$id] = isset($option['std'])? $option['std']:'';		
			if ('checkbox' == $option['type'])$input[$id] = false;				
			if ('multicheck' == $option['type'])$input[$id] = array();					
		}
		if (has_filter('mage_sanitize_' .$option['type'])){				
			$clean[$id] = apply_filters('mage_sanitize_' . $option['type'], $input[$id], $option);	
		} 
	}
	add_settings_error('mage_maps','save_options', __( 'Options saved.', 'mage-google-maps' ), 'updated modal fade in' );
	return $clean;
} 
function mage_map_post_type($check){
	$post_type = get_post_type($check);
	if (!$post_type) return false;
	//$post_type = $check->post_type;
	$types = mage_get_option('maps','post_type_maps');
	if (is_array($types) && in_array($post_type,$types)) {
  		return true;
	}
	return false;
}
function mage_maps_options(){
	$options = array();			
	$types = mage_get_option('maps','post_type_maps',array('post'));	
	$options[] = array('name' => __('Maps','mage-google-maps'),'icon' => 'map-marker','type' => 'heading','icons'=>dirname( __FILE__));		
	$options[] = array('name' => __('Address Data', 'mage-google-maps'),'parent' => 'maps','type' => 'subheading');	
	$keys = mage_select_meta_keys($types, array(0=>'Disabled',1=>'Show in Meta Box'));

	$options[] = array(
		'name' => __('Default Address', 'mage-google-maps'),
		'desc' => __('The region to display if no location is identified.', 'mage-google-maps'),
		'id' => 'mage_maps_region',
		'std' => '',
		'type' => 'text');	
	$options[] = array(
		'name' => __('Attach to Post Types', 'mage-google-maps'),
		'desc' => __('Choose the maximum rating that a user can rate content with.', 'mage-google-maps'),
		'id' => 'post_type_maps',		
		'type' => 'multicheck',
		'std'=>array('post'),
		'options'=>mage_post_type_options());
	$options[] = array(
		'name' => __('Meta Box', 'mage-google-maps'),
		'desc' => __('Display a Meta Box with address fields under the content editor of corresponding post types.', 'mage-google-maps'),
		'type' => 'legend',
		'id'=>'mage_map_field_type',
		'options'=> array(1=>'Show',0=>'Hide'),
		'std' => 1);
	$options[] = array(
		'name' => __('Address Fields', 'mage-google-maps'),
		'id' => 'mage_map_address_fields',
		'std' => 1,
		'type' => 'radio',
		'options'=>array(1=>'Single Address Field',2=>'Custom Fields'));
	$options[] = array(
		'name' => __('Custom Fields', 'mage-google-maps'),
		'content' => __('<h4>Custom Fields</h4><p>To use the following options, <strong>Custom Fields</strong> must be selected on the <strong>Address Fields</strong> option above.</p><p>You may display a text field for each address component by choosing <strong>Show in Meta Box</strong><p><p>or assign an <strong>existing custom field</strong> for each component to automatically generate an address from, if available. Find out more about WordPress <a href="http://codex.wordpress.org/Custom_Fields" target="_blank">Custom Fields</a></p>', 'mage-google-maps'),
		'type' => 'html');	
	$options[] = array(
		'name' => __('Address 1', 'mage-google-maps'),
		'id' => 'mage_map_key_address_1',
		'std' => 1,
		'type' => 'select',
		'options'=>$keys);
	$options[] = array(
		'name' => __('Address 2', 'mage-google-maps'),
		'id' => 'mage_map_key_address_2',
		'std' => 1,
		'type' => 'select',
		'options'=>$keys);
	$options[] = array(
		'name' => __('City', 'mage-google-maps'),
		'id' => 'mage_map_key_city',
		'std' => 1,
		'type' => 'select',
		'options'=>$keys);
	$options[] = array(
		'name' => __('State', 'mage-google-maps'),
		'id' => 'mage_map_key_state',
		'std' => 1,
		'type' => 'select',
		'options'=>$keys);
	$options[] = array(
		'name' => __('Zip Code', 'mage-google-maps'),
		'id' => 'mage_map_key_zip',
		'std' => 1,
		'type' => 'select',
		'options'=>$keys);
	$options[] = array(
		'name' => __('Country', 'mage-google-maps'),
		'id' => 'mage_map_key_country',
		'std' => 1,
		'type' => 'select',
		'options'=>$keys);
	$options = apply_filters('mage_maps_attributes_meta_box',$options);
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$options = apply_filters('mage_maps_attributes_tab_1',$options);
	$options[] = array('name' => __('Map Design', 'mage-google-maps'),'parent' => 'maps','type' => 'subheading');
	$options[] = array(
		'name' => __('Disable Maps CSS', 'mage-google-maps'),
		'desc' => __('Activate to remove the Maps default styling (white border and shadow).', 'mage-google-maps'),
		'id' => 'mage_maps_css',
		'type' => 'checkbox',
		'std' => '0');
	$options[] = array(
		'name' => __('Map Width', 'mage-google-maps'),
		'desc' => __('Default Map width.', 'mage-google-maps'),
		'id' => 'mage_maps_width',
		'std' => '100%',
		'type' => 'text');	
	$options[] = array(
		'name' => __('Map Height', 'mage-google-maps'),
		'desc' => __('Default Map Height', 'mage-google-maps'),
		'id' => 'mage_maps_height',
		'std' => '300px',
		'type' => 'text');	
	$options[] = array(
		'name' => __('Default Zoom', 'mage-google-maps'),
		'desc' => __('The region to display if no location is identified.', 'mage-google-maps'),
		'id' => 'mage_maps_zoom',
		'std' => '14',
		'type' => 'select',
		'options'=>mage_number_select(1,20));	
	$options[] = array(
		'name' => __('Toggle User Interface', 'mage-google-maps'),
		'desc' => __('Activate to hide the default Google Maps UI.', 'mage-google-maps'),
		'id' => 'mage_maps_ui',
		'type' => 'checkbox',
		'std' => '1');
	$options[] = array(
		'name' => __('Google Maps API Key', 'mage-google-maps'),
		'content' => __('<div class="alert alert-info">If, for some reason, you want to use a <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" rel="nofollow external">Google Maps API Key</a>, you can enter it below.</div>', 'mage-google-maps'),
		'type' => 'html');	
	$options[] = array(
		'name' => __('Googe Maps API Key', 'mage-google-maps'),
		'id' => 'mage_maps_api_key',
		'std' => '',
		'type' => 'text');	
	$options[] = array('name' => __('Map Markers', 'mage-google-maps'),'parent' => 'maps','type' => 'subheading');
		$options[] = array(
		'name' => __('Default Map Marker', 'mage-google-maps'),
		'desc' => __('Upload a new default map marker, or leave blank to use the default Google Map Marker.', 'mage-google-maps'),
		'id' => 'mage_maps_marker',
		//'icons'=>true,
		'type' => 'upload');
	$options[] = array(
		'name' => __('Enable Marker Click Redirect', 'mage-google-maps'),
		'desc' => __('On an archive, clicking a Maps Marker will redirect to the post.', 'mage-google-maps'),
		'id' => 'mage_maps_auto_link',
		'type' => 'checkbox',
		'std' => '1');
	$options = apply_filters('mage_maps_attributes_tab_2',$options);
	$options[] = array('name' => __('Help', 'mage-google-maps'),'parent' => 'maps','type' => 'subheading');	
	$options[] = array(
		'name' => __('Static Map Display', 'mage-google-maps'),
		'type' => 'legend');
	$options[] = array(
		'type' => 'html',
		'content'=>__('<h4>Via Shortcode</h4><p>The fastest way to display a map is with the <code>[map]</code> shortcode, with the <code>address=""</code> parameter, for example <code>[map address="Los Angeles, CA"]</code>. If you dont add an address at all, the default address listed in <code>Map Design</code>-><code>DEFAULT ADDRESS</code>.</p><hr /><h4>Via Widget</h4><p>Another way to display the map is via the <code>Mage Google Maps</code> <strong>Widget</strong>, which has been added to <code>Appearance</code>-><code>Widgets</code>.</p>', 'mage-google-maps'));
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');	
	$options[] = array(
		'name' => __('Shortcode [map] Parameters', 'mage-google-maps'),
		'shortcode'=>'[map]',
		'type' => 'legend');
	$options[] = array(
		'type' => 'html',
		'content'=>__('<p>The below parameters should only be used to overwrite the default map behavior. It\'s best to just use <code>[map]</code> by itself, without any parameters. Defaults are taken from the settings.</p><table class="table">
          <thead>
            <tr>
              <th>Parameter</th>
              <th>Type</th>
			  <th>Description</th>
              <th>Defaults</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>width</code></td>
              <td><div class="label label-success">string</div></td>
			  <td>Overwrite the maps width.</td>
              <td>"100%"</td>
            </tr>
            <tr>
              <td><code>height</code></td>
              <td><div class="label label-success">string</div></td>
			  <td>Overwrite the maps height.</td>
              <td>"300px"</td>
            </tr>
            <tr>
              <td><code>zoom</code></td>
              <td><div class="label label-primary">int</div></td>
			  <td>The maps zoom level.</td>
              <td>14</td>
            </tr>
			<tr>
              <td><code>address</code></td>
              <td><div class="label label-success">string</div></td>
			  <td>The address you want to show on the map.</td>
              <td>empty</td>
            </tr>
			<tr>
              <td><code>ui</code></td>
              <td><div class="label label-warning">bool</div></td>
			  <td>Toggle the Google maps user interface on or off.</td>
              <td>0</td>
            </tr>
			<tr>
              <td><code>title</code></td>
              <td><div class="label label-success">string</div></td>
			  <td>Change the title of the map marker, which defaults to the posts title.</td>
              <td>empty</td>
            </tr>
			<tr>
              <td><code>class</code></td>
              <td><div class="label label-success">string</div></td>
			  <td>Additional CSS classes to add to the form element.</td>
              <td>empty</td>
            </tr>
			<tr>
              <td><code>style</code></td>
              <td><div class="label label-success">string</div></td>
			  <td>This parameter behaves identical to that of any HTML tags "style" parameter. Use this to implement inline CSS styles directly into the element.</td>
              <td>empty</td>
            </tr>
          </tbody>
        </table><p><strong>Usage:</strong><br /><code>[map address="1600 Amphitheatre Pkwy, Mountain View, CA 94043" width="250px" ui=0 zoom=10]</code></p>', 'mage-google-maps'));
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$options[] = array(
		'name' => __('Auto Map Display & Multi Address', 'mage-google-maps'),
		'type' => 'legend');
	$options[] = array(
		'type' => 'html',
		'content'=>__('<h4>Address Field Order Priority</h4><p><strong>Mage Google Maps</strong> retrieves the address in the following order, until an address value is returned and not empty.</p><ol><li>The <code>address</code> parameter in the shortcode, or <code>address</code> field when using the widget. For <strong>Auto-Displaying</strong> of addresses, these should be left empty.</li><li>When displayed on selected Post Types, both shortcode and widget will take address data from the fields specified on the <code>Address Data</code> settings page.</li><li>Lastly, if no address data is received from the above options, Mage Google Maps will use the <code>Default Address</code> option as a fallback.</li></ol><h4>Multi-Address Archives</h4><p>Whenever the <code>[map]</code> shortcode or widget are on an archive page, Mage Google Maps will attempt to crawl all archive items and add a map marker for each supported post type with an address.</p>', 'mage-google-maps'));
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$wp_support = 'http://wordpress.org/support/plugin/mage-reviews';
	$mb_article = 'http://blog.maximusbusiness.com/2013/11/wordpress-review-rating-plugin/';
	//$options[] = array(
		//'name' => __('Support', 'mage-google-maps'),
		//'desc' => __('<p>Please refer to <a href="'.$mb_article.'" target="_blank">this article</a> and to <a href="'.$wp_support.'" target="_blank">WordPress Support Forums</a> if you need help, find any bugs, have suggestions or would like help us with a rating :) .</p>', 'mage-google-maps'),
		//'type' => 'legend');
	return $options;	
}
add_filter('mage_maps_attributes_tab_2','mage_maps_pro_options_placeholder');
add_filter('mage_maps_attributes_meta_box','mage_maps_pro_options_placeholder');

function mage_maps_pro_options_placeholder($options){
	$url = '<a href="http://www.maximusbusiness.com/plugins/mage-google-maps-pro/" target="_blank" rel="nofollow">Mage Google Maps Pro</a>';
	$options[] = array(
		'content' => __('<div class="alert">Additional options are available from '.$url.'.</div>', 'mage-google-maps'),
		'type' => 'html');	
	return $options;
}
