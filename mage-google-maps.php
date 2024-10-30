<?php
/**
 * Plugin Name: Mage Google Maps
 * Plugin URI:  http://www.maximusbusiness.com/plugins/mage-google-maps-pro/
 * Description: WordPress Google Maps plugin with automatic single/multi address display via custom meta & shortcode.
 * Author:      Mage Cast
 * Author URI:  http://www.maximusbusiness.com/plugins/mage-google-maps-pro/
 * Version:     1.1.2
 * Text Domain: mage-google-maps
 * Domain Path: /lang/
 * License:     GPLv2 or later (license.txt)
 */
?>
<?php
if (!defined('ABSPATH')) exit;
define('MAGECAST_MAPS_VER', '1.1.2');
define('MAGECAST_MAPS', dirname( __FILE__ ). '/');
define('MAGECAST_MAPS_URL',plugins_url('/',__FILE__));
define('MAGECAST_MAPS_SOURCE',MAGECAST_MAPS_URL.'source/');
add_action('after_setup_theme','load_magecast_maps');
function load_magecast_maps(){
	require_once MAGECAST_MAPS.'core/mage-cast.php';
	require_once MAGECAST_MAPS.'cast/attributes.php';
	if (file_exists(MAGECAST_MAPS.'mage-google-maps-pro.php')) require_once MAGECAST_MAPS.'mage-google-maps-pro.php';
	require_once MAGECAST_MAPS.'cast/mage-maps.php';
	require_once MAGECAST_MAPS.'cast/craft.php';
	add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'mage_google_maps_settings_link' );
	add_filter( 'mage_core_plugin_mage_google_maps', 'mage_google_maps_dashboard' );
}
function mage_google_maps_settings_link( $links ) {
   $links[] = '<a href="'. get_admin_url(null, 'admin.php?page=mage_maps') .'">Settings</a>';
   return $links;
}
function mage_google_maps_dashboard() {
	$data = array(
   'name'=>'Mage Google Maps',
   'active'=>true,
   'version'=>MAGECAST_MAPS_VER,
   'settings'=>get_admin_url(null, 'admin.php?page=mage_maps'),
   'support'=>'http://wordpress.org/support/plugin/mage-google-maps',
   'pro'=>'http://www.maximusbusiness.com/plugins/mage-google-maps-pro/',
   );
   if(defined('MAGECAST_MAPS_PRO')) $data['pro_active'] = true;
   return $data;
}