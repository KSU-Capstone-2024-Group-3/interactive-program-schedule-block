<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>

<!DOCTYPE html>
<html lang="en">
<div class="calendar">
  <div class="day">Sunday</div>
  <div class="day">Monday</div>
  <div class="day">Tuesday</div>
  <div class="day">Wednesday</div>
  <div class="day">Thursday</div>
  <div class="day">Friday</div>
  <div class="day">Saturday</div>
</div>

<?php
global $wpdb;
$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$data_table_name = $wpdb->prefix . 'scheduler_data';
$results = $wpdb->get_results( "SELECT * FROM " . $data_table_name . " WHERE airing = 1;", OBJECT ); //airing
// Sample data to be loaded into cards
echo "<script>var result_data = " . json_encode($results) . ";</script>"; // for passing from back to front incredibly easily
?>

<script>
  // Function to load data into cards
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  function loadCard(content, dayIndex) {
    const dayElements = document.querySelectorAll('.day');
    if (dayElements.length > dayIndex) {
      const dayElement = dayElements[dayIndex];
      const card = document.createElement('div');
      card.classList.add('card');
      card.textContent = content;
      dayElement.appendChild(card);
    }
  }

  function dayIndex(day) {
	return days.indexOf(day);
  }

  if(result_data) { //if we have shows
	result_data.forEach((element) => { //for all the result data
		schedule = JSON.parse(element['schedule']); //convert schedule to json
		days.forEach((day) => { //for each day
			if(schedule[day][0]) { //if on that day, load the card
				//console.log(schedule[day]);
				loadCard(element['name'], dayIndex(day));
			}
		});
	});
  }
</script>

<style>
  body {
    font-family: Arial, sans-serif;
  }
  .calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
  }
  .day {
    border: 1px solid #ccc;
    padding: 10px;
  }
  .card {
    background-color: #f0f0f0;
    padding: 5px;
    margin-bottom: 5px;
  }
</style>

</html>