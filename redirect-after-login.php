<?php
/**
 * Plugin Name: Redirect After Login
 * Plugin URI: http://www.marcelotorresweb.com/redirect-after-login/
 * Description: Redirect users to specific locations after login, based in the role.
 * Author: marcelotorres
 * Author URI: http://marcelotorresweb.com/
 * Version: 0.1.5
 * License: GPLv2 or later
 * Text Domain: mtral
 * Domain Path: /languages/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Sets the plugin path/url.
$upload_dir = wp_upload_dir();
define( 'MTRAL_URL', plugins_url().'/mtral');
define( 'MTRAL_PATH', plugin_dir_path( __FILE__ ) );

//Add custom meta links for plugins page
add_filter( 'plugin_row_meta', 'mtral_custom_plugin_row_meta', 10, 2 );
function mtral_custom_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'redirect-after-login.php' ) !== false ) {
		$new_links = array(
				'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G85Z9XFXWWHCY" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" alt="PayPal - The safer, easier way to pay online!" border="0"></a>'
			);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}

// Load textdomain.
load_plugin_textdomain( 'mtral', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

// Include admin settings
require_once(MTRAL_PATH.'redirect-after-login-admin.php');

function redirect_after_login_per_role()
{
	//retrieve current user info 
	global $wp_roles, $user;
	    
	$roles = $wp_roles->roles;
	$setting = get_option('mtral_settings');
	
	 //is there a user to check?
	foreach($roles as $role_slug => $role_options){
		if( isset( $user->roles ) && is_array( $user->roles ) ) {
			//check for admins
			if( in_array( $role_slug, $user->roles ) ) {
			
			$admin_pages = $setting['mtral_field_'.$role_slug];
			$admin_custom_pages = $setting['mtral_field_custom_url_'.$role_slug];
			$redirect = (empty($admin_custom_pages)) ? get_admin_url().$admin_pages : $admin_custom_pages;
			
				// redirect them to the default place
				return $redirect;
			}
		}
    }
	
}
add_filter("login_redirect", "redirect_after_login_per_role", 10, 3);