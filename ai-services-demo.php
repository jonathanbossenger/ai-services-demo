<?php
/**
 * Plugin Name:       Ai Services Demo
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-services-demo
 *
 * @package Jonathanbossenger
 */

define( 'PLUGIN_URL', trailingslashit( plugin_dir_url(__FILE__) ) );
define( 'PLUGIN_PATH', trailingslashit( plugin_dir_path(__FILE__) ) );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require PLUGIN_PATH . 'php/block-registration.php';
require PLUGIN_PATH . 'php/localize-vars.php';
require PLUGIN_PATH . 'php/rest-api-registration.php';
