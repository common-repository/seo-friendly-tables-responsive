<?php
/*
Plugin Name: Responsive SEO Friendly Tables
Description: Responsive & SEO friendly tables with schema mark-up plugin for WordPress. 10+ beautiful designs. Unlimited tables. Easy to use. Shortcodes.
Version: 1
Author: Hellofriday
License: GPL2

This plugin is a fork of Table Maker 1.6 created by Wpsoul.
It has been fully redesigned, seo optimized and much more.
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'inc/class-hmg-table-builder.php';

function hmg_run_table_builder() {
	$plugin_instance = new hmg_Table_builder('1.6');
	register_activation_hook( __FILE__, array($plugin_instance, 'initialize') );
	register_uninstall_hook( __FILE__, array('hmg_Table_builder', 'rollback') );
}

hmg_run_table_builder();

function hmg_get_table($id)
{
	$db = hmg_DB_Table::get_instance();
	$table = $db->get($id);
	return $table['tvalues'];
}

function hmg_load_plugin_textdomain() {
	load_plugin_textdomain( 'hmg-tableplugin', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'hmg_load_plugin_textdomain' );

?>