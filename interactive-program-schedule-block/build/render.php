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
$data_table_name = $wpdb->prefix . 'scheduler_data';
$results = $wpdb->get_results( "SELECT * FROM " . $data_table_name . " WHERE airing = 1;", OBJECT ); //airing
// Sample data to be loaded into cards
echo "<script>var result_data = " . json_encode($results) . ";</script>"; // for passing from back to front incredibly easily
?>

<script>
  // Function to load data into cards
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  const dayElements = document.querySelectorAll('.day');
  cardDeck = [];
  function loadCard(content, dayIndex) {
    if (dayElements.length > dayIndex) {
      const dayElement = dayElements[dayIndex];
      const card = document.createElement('div');
      card.classList.add('card');
      
		cardTitle = document.createElement('div');
		cardTitle.classList.add('card-title');
		cardTitle.innerHTML = content[0]["name"];

		cardDescription = document.createElement('div');
		cardDescription.classList.add('card-description');
		cardDescription.innerHTML = content[0]["description"];

		cardTime = document.createElement('div');
		cardTime.classList.add('card-time');
		
		var dst = getTime(content[1][0]);
		var det = getTime(content[1][1]);
		//is dst after end date?
		if(content[0]["end_date"] != null) { //if there is an end date
			let endDate = new Date(content[0]["end_date"]);
			if(dst > endDate) { //if start time is greater than end date
				console.log(content[0]["name"] + "--> Past end date!");
				return;
			}
		}
		//is it on this week (every-x-weeks)
		if(content[0]["every_x_weeks"] > 1) {
			let today = new Date();
			let start_date = new Date(content[0]["start_date"]);
			let diff = Math.floor((today - start_date) / (1000 * 60 * 60 * 24 * 7));
			if(diff % content[0]["every_x_weeks"] != 0) {
				console.log(content[0]["name"] + "--> Not this week!");
				return;
			}
		}
		//is it a one-time show?
		if(content[0]["one_time"] == 1) {
			let today = new Date();
			let start_date = new Date(content[0]["start_date"]);
			if(today > start_date) {
				console.log(content[0]["name"] + "--> One-time show!");
				return;
			}
		}

		cardTime.innerHTML = timeDisplay(dst) + " - " + timeDisplay(det);

		card.appendChild(cardTitle);
		card.appendChild(cardDescription);	
		card.appendChild(cardTime);

		cardDeck.push({"card":card, "day_index":dayIndex, "start_time":dst});
    }
	sortCardsByStartTime(cardDeck).forEach((element) => {
		dayElements[element['day_index']].appendChild(element['card']);
	});
  }

  function sortCardsByStartTime(cardDeck) {
	returnDeck = cardDeck.sort((a, b) => {
		if(a['start_time'].getTime() > b['start_time'].getTime()) {
			return 1;
		} else if(a['start_time'].getTime() < b['start_time'].getTime()) {
			return -1;
		} else {
			return 0;
		}
	});
	return returnDeck;
  }

  function dayIndex(day) {
	return days.indexOf(day);
  }

  function timeDisplay(time) {
	let return_string = "";
	let am_pm = "";
	if(time.getHours() > 12) {
		return_string += (time.getHours() - 12) + ":";
		am_pm = " PM";
	} else {
		return_string += time.getHours() + ":";
		am_pm = " AM";
	}
	let prefix = time.getMinutes() < 10 ? '0' : '';
	return return_string + prefix + time.getMinutes() + am_pm;
  }

  function getTime(time) {
	let [h, m] = time.split(':');
	let date = new Date();
	date.setHours(h, m, 0);
	return date;	
}

  if(result_data) { //if we have shows
	result_data.forEach((element) => { //for all the result data
		schedule = JSON.parse(element['schedule']); //convert schedule to json
		days.forEach((day) => { //for each day
			if(schedule[day][0]) { //if on that day, load the card
				//console.log(schedule[day]);
				loadCard([element, [schedule[day][1], schedule[day][2]]], dayIndex(day));
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
	justify-content: center;
  }
  .day {
    border: 1px solid #ccc;
    padding: 10px;
  }
  .card {
    background-color: #f0f0f0;
    padding: 5px;
    margin-bottom: 5px;
	height: 5rem;
	width: min-content;
	min-width: 5rem;
	overflow: hidden;
	position: relative;
  }

  .card>.card-title {
	font-size: medium;
	font-weight: bold;
	max-height: 2rem;
	white-space: nowrap;
  }

  .card>.card-description {
	font-size: small;
	max-height: 2.5rem;
	overflow-y: auto;
	overflow-x: hidden;
  }

  .card>.card-time {
	position: absolute;
	font-style: italic;
	font-size: x-small;
	white-space: nowrap;
	bottom: 0;
  }
</style>

</html>