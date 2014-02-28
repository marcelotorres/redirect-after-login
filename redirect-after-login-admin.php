<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Include classes for option page
require_once(MTRAL_PATH.'/classes/options.php');

//New object for create settings
$mtral_options = new MTRAL_Options( 'mtral', __( 'Redirect After Login', 'redirect-after-login' ), 'activate_plugins' );

if ( $wp_version < 3.6 && $_GET['page'] == 'mtral') {
    echo '<div class="error"><p><strong>';
	echo __( 'This plugin not is supported in current WordPress version. <a href="./update-core.php">Please update the WordPress for version 3.6 or above.</a>', 'mtral' );
	echo '</strong></p></div><style type="text/css">p.submit{display:none}</style>';
}else{
	//Create tabs
	$mtral_options->set_tabs(
		array(
			array(
				'id' => 'mtral_settings',
				'title' => __( 'Settings', 'mtral' )
			)
		)
	);

	//Create fields of the corresponding tabs
	$mtral_options->set_fields(
		array(
			'setting_tab' => array(
				'tab'   => 'mtral_settings',
				'title' => __( 'Redirect After Login Settings', 'mtral' ),
				'fields' => array(
					array(
						'id' => 'mtral_field',
						'label' => __( 'Set for each role the page to which the user will be redirected after login', 'mtral' ),
						'type' => 'mtral'
					)
				)
			)
		)
	);
}
?>
