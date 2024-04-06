<?php // Silence is golden

//TODO: bitfield with time for those specific bits - foreign key it
//TODO: metadata for show - stuff added afterwards

$null_string = 'null_scheduler_placeholder';
$scheduler_data_table = 'scheduler_data';
function scheduler_callback() { //main scheduler page
    echo "Scheduler page baby! <hr>";
}

function add_callback() //add submenu callback
{
    if (!empty($_POST)) { //handles post data if there is some
        $data = addedit_post_format_data();
        schedule_add($data);
    }
    echo "Add page baby! <hr>";
    include("admin-menu-addedit.html");
}

function schedule_add($data) //add a show to the database with the data formatted by addedit_post_format_data
{
    global $wpdb, $null_string, $scheduler_data_table;
    $data_table_name = $wpdb->prefix . $scheduler_data_table;
    // Prepare the SQL statement with placeholders
    $sql = "INSERT INTO " . $data_table_name . " (name, description, start_date, end_date, one_time, same_time, every_x_weeks, airing, schedule) VALUES (%s, %s, %s, %s, %d, %d, %d, %d, %s)";
    // Use $wpdb->prepare to prevent SQL injection
    $prepared_sql = $wpdb->prepare($sql, $data);
    $prepared_sql = str_replace("'" . $null_string . "'",'NULL', $prepared_sql); //get rid of placeholder nulls
    echo "<br>" . $prepared_sql;
    // Execute the prepared statement
    $result = $wpdb->query($prepared_sql);
    if($result) {
        echo "<br>Success!";
    } else {
        echo "<br>Failure!";
    }
}

//$sql = "SELECT *  FROM `wp_scheduler_data` WHERE `airing` = 1;"; --airing
//$sql = "SELECT *  FROM `wp_scheduler_data` WHERE `airing` = 0;"; --non airing

function edit_callback() //edit submenu callback - edit or archive a show
{
    if (!empty($_POST)) {
        if($_POST["archive"]) {
            archive_show($_POST["archive"]);
            echo "Archiving show!";
        } else {
            $data = addedit_post_format_data();
            edit_show($data);
        }
    }
    global $wpdb, $scheduler_data_table;
    $data_table_name = $wpdb->prefix . $scheduler_data_table;
    $results = $wpdb->get_results( "SELECT * FROM " . $data_table_name . " WHERE airing = 1;", OBJECT ); //airing
    echo "<script>var result_data = " . json_encode($results) . ";</script>"; // for passing from back to front incredibly easily
    echo "Edit page baby! <hr>";
    //echo json_encode($results);
    include("admin-menu-addedit.html");
}

//UPDATE `wp_scheduler_data` SET `name` = '%s', `description` = '%s', `start_date` = '%s', 
//`end_date` = '%s', `one_time` = '%d', `same_time` = '%d', `every_x_weeks` = '%d', `airing` = '%d', 
//`schedule` = '%s' WHERE `wp_scheduler_data`.`id` = %d; --to update a whole show with id 2

// (%s, %s, %s, %s, %d, %d, %d, %d, %s)
function edit_show($new_data) //edit a show with the new data formatted by addedit_post_format_data
{
    global $wpdb, $null_string , $scheduler_data_table;
    $data_table_name = $wpdb->prefix . $scheduler_data_table;
    $sql = "UPDATE `". $data_table_name . "` SET `name` = '%s', `description` = '%s', `start_date` = '%s', `end_date` = '%s', `one_time` = '%d', `same_time` = '%d', `every_x_weeks` = '%d', `airing` = '%d', `schedule` = '%s' WHERE `".$data_table_name ."`.`id` = %d;";
    $new_data['id'] = $_POST['show-name-id']; //set id into new data
    $prepared_sql = $wpdb->prepare($sql, $new_data);
    $prepared_sql = str_replace("'" . $null_string . "'",'NULL', $prepared_sql); //get rid of placeholder nulls
    
    echo "<br>" . $prepared_sql;
    // Execute the prepared statement
    $result = $wpdb->query($prepared_sql);
    if($result) {
        echo "<br>Success!";
    } else {
        echo "<br>Failure!";
    }
}

function addedit_post_format_data() //format post data into a data array for insertion
{
    // handle post data
    $days = array(
        'monday'    =>  day_tuple($_POST['monday-start-time'], $_POST['monday-end-time']),
        'tuesday'   =>  day_tuple($_POST['tuesday-start-time'], $_POST['tuesday-end-time']),
        'wednesday' =>  day_tuple($_POST['wednesday-start-time'], $_POST['wednesday-end-time']),
        'thursday'  =>  day_tuple($_POST['thursday-start-time'], $_POST['thursday-end-time']),
        'friday'    =>  day_tuple($_POST['friday-start-time'], $_POST['friday-end-time']),
        'saturday'  =>  day_tuple($_POST['saturday-start-time'], $_POST['saturday-end-time']),
        'sunday'    =>  day_tuple($_POST['sunday-start-time'], $_POST['sunday-end-time'])
    );

    $data = array(
        'name' => $_POST['show-name'],
        'description' => $_POST['show-description'],
        'start_date' => $_POST['start-date'],
        'end_date' => check_end_date($_POST['end-date'], check_to_bool($_POST['end-date-checkbox'])),
        'one_time' => check_to_bool($_POST['one-time']),
        'same_time' => check_to_bool($_POST['same-time']),
        'every_x_weeks' => $_POST['every-x-weeks'],
        'airing' => TRUE,
        'schedule' => json_encode($days) // Encode schedule before insertion
    );

    //echo "<fieldset class='post-data' style='clear: left;'> <legend> POST data </legend>";
    //echo json_encode($data);
    //echo "</fieldset>";

    //schedule_add($data);
    return $data;

}

function archive_callback() //archive submenu callback
{
    if (!empty($_POST)) {
        unarchive($_POST['id']);
    }
    global $wpdb, $scheduler_data_table;
    $data_table_name = $wpdb->prefix . $scheduler_data_table;
    $results = $wpdb->get_results( "SELECT * FROM ". $data_table_name . " WHERE airing = 0;", OBJECT ); //non airing
    echo "Archive page baby! <hr>";
    echo "<script>var result_data = " . json_encode($results) . ";</script>"; // for passing from back to front incredibly easily
    //echo json_encode($results);
    include("archive.html");
}

function unarchive($id) { //unarchive a show with id
    global $wpdb, $scheduler_data_table;
    $data_table_name = $wpdb->prefix . $scheduler_data_table;
    // UPDATE `wp_scheduler_data` SET `end_date` = NULL, `airing` = '1' WHERE `wp_scheduler_data`.`id` = 2; --to archive a show with id in prepare
    $sql = "UPDATE " . $data_table_name . " SET `end_date` = NULL, `airing` = '1'  WHERE `" . $data_table_name . "`.`id` = %d;";
    $prepared_sql = $wpdb->prepare($sql, $id);

    echo $prepared_sql;
    // Execute the prepared statement
    $result = $wpdb->query($prepared_sql);
    if($result) {
        echo "<br>Success!";
    } else {
        echo "<br>Failure!";
    }
}

function archive_show($id) //archive a show with id
{
    global $wpdb, $scheduler_data_table;
    $data_table_name = $wpdb->prefix . $scheduler_data_table;
    // UPDATE `wp_scheduler_data` SET `end_date` = NULL, `airing` = '1' WHERE `wp_scheduler_data`.`id` = 2; --to archive a show with id in prepare
    $today = date("Y-m-d"); //set end_date to today as we're archiving it today.
    $sql = "UPDATE " . $data_table_name . " SET `end_date` = '" . $today . "', `airing` = '0'  WHERE `" . $data_table_name . "`.`id` = %d;";
    $prepared_sql = $wpdb->prepare($sql, $id);

    echo $prepared_sql;
    // Execute the prepared statement
    $result = $wpdb->query($prepared_sql);
    if($result) {
        echo "<br>Success!";
    } else {
        echo "<br>Failure!";
    }
}

function day_tuple($start_time, $end_time) { //return a tuple of boolean on that day, start, and end time for a day
    if ($start_time != '' && $end_time != '') {
        return [TRUE, $start_time, $end_time];
    } else {
        return [FALSE, null, null];
    }
}

function check_to_bool($value) { //converts an html checkbox value to a boolean
    return $value == 'on' ? TRUE : FALSE;
}

function check_end_date($end_date, $notexist) { //checks if the end date exists and returns it or a null placeholder
    global $null_string;
    if(!$notexist) { //if does not notexist -> does exist
        return $end_date == '' ? $null_string : $end_date;
    } else {
        return $null_string;
    }
}

// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
//add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = ”, int|float $position = null ): string|false
function register_admin_page() //register the admin pages and subpages
{
    add_menu_page('Scheduler', 'Scheduler', 'manage_options', 'schedule_panel', 'scheduler_callback', 'dashicons-welcome-widgets-menus', 90);
    add_submenu_page('schedule_panel', 'Add Show', 'Add Show', 'manage_options', 'schedule_panel_add', 'add_callback');
    add_submenu_page('schedule_panel', 'Edit Show', 'Edit Show', 'manage_options', 'schedule_panel_edit', 'edit_callback');
    add_submenu_page('schedule_panel', 'Archive', 'Archive', 'manage_options', 'schedule_panel_archive', 'archive_callback');
}
