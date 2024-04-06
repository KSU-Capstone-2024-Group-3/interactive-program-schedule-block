<?php // Silence is golden
function init_schedule_database() { //create the scheduler_data table
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $data_table_name = $wpdb->prefix . 'scheduler_data';
    $sql =  "CREATE TABLE ". $data_table_name . " (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE DEFAULT NULL,
        one_time BOOLEAN NOT NULL DEFAULT FALSE,
        same_time BOOLEAN NOT NULL DEFAULT FALSE,
        every_x_weeks INT NOT NULL DEFAULT 1,
        airing BOOLEAN NOT NULL DEFAULT TRUE,
        schedule TEXT NOT NULL
        ) " . $charset_collate . ";";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql );
}

//TODO: add a function to drop the table on plugin deletion