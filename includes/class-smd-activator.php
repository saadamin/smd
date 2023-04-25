<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.saadamin.com
 * @since      1.0.0
 *
 * @package    Smd
 * @subpackage Smd/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Smd
 * @subpackage Smd/includes
 * @author     Saad Amin <saadvi@gmail.com>
 */
class Smd_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// global $wpdb;
		// $table_name = $wpdb->prefix . "smd"; 
		// $charset_collate = $wpdb->get_charset_collate();
		
		// $sql = "CREATE TABLE $table_name (
		// 		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		// 		  name tinytext NOT NULL,
		// 		  email varchar(255) DEFAULT '' NOT NULL,
		// 		  phone_numbers json DEFAULT '' NULL,
		// 		  deleted tinyint DEFAULT 0,
		// 		  PRIMARY KEY  id (id),
    	// 		  UNIQUE KEY email (email)
		// 		) $charset_collate;";
		
		// require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// dbDelta( $sql );
	}

}
