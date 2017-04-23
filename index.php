<?php

use WordPress\ACF\Fields\ExternalRelationship;

require_once dirname ( __FILE__ ) . '/vendor/autoload.php';

/**
 * Plugin Name:     ACF External Relationship Field
 * Plugin URI:      https://github.com/unematiii/ACF-External-Relationship-Field
 * Description:     Connect external entitites via ACF Relationship field
 * Author:          Mati Kärner
 * Author URI:      https://adaptive.ee
 * Text Domain:     acf-external-relationship
 * Domain Path:     /languages
 * Version:         1.2.0
 *
 * @package         WordPress\ACF
 */

/**
 * Init
 */
add_action ( 'acf/include_field_types', function () {
	// Initialize
	acf_register_field_type ( new ExternalRelationship() );
} );
