<?php // Silence is golden
function init_schedule_database() { //create the tables on wordpress plugin activation
	global $wpdb; //global wordpress database object, used to interact with the database
    $charset_collate = $wpdb->get_charset_collate(); //get the charset collate for the wordpress database
    $data_table_name = $wpdb->prefix . 'scheduler_data'; // get the table name with the wordpress prefix (wp_ by default)
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
        ) " . $charset_collate . ";"; //sql command to create the data table
    
    $log_table_name = $wpdb->prefix . 'scheduler_log';
    $sql .= "CREATE TABLE ". $log_table_name . " ( 
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user VARCHAR(255) NOT NULL,
        show_name VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        action VARCHAR(255) NOT NULL
        ) " . $charset_collate . ";"; //sql command to create the log table, note the .= to append to the previous sql command

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //include the wordpress upgrade file
    dbDelta( $sql ); //execute the wordpress php function dbDelta with the sql command to create the tables
}

function drop_schedule_database() { //drop the tables on wordpress plugin uninstallation
    global $wpdb; //global wordpress database object, used to interact with the database
    $data_table_name = $wpdb->prefix . 'scheduler_data'; // get the table name with the wordpress prefix (wp_ by default)
    $sql = "DROP TABLE IF EXISTS $data_table_name;"; //sql command to drop the data table
    $wpdb->query($sql); //execute the sql command -> repeat
    $log_table_name = $wpdb->prefix . 'scheduler_log';
    $sql = "DROP TABLE IF EXISTS $log_table_name;";
    $wpdb->query($sql);
}