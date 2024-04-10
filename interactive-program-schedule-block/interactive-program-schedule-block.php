<?php
/**
 * Plugin Name:       Interactive Program Schedule Block
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       interactive-program-schedule-block
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_interactive_program_schedule_block_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_interactive_program_schedule_block_block_init' );

//this is jack's stuff

include 'admin/index.php';
add_action( 'admin_menu', 'register_admin_page');

//include 'database-init.php';

include 'database.php';
register_activation_hook(__FILE__, 'init_schedule_database'); 	//for init our database on plugin activation
register_uninstall_hook( __FILE__, 'drop_schedule_database' ); //for drop our database on plugin uninstall