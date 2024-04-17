<?php

/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>

<!-- 
get the block wrapper attributes from our block.json file
and the styles from style.scss
--> 
<div <?php echo get_block_wrapper_attributes(); ?>>  
	<div class="ips-calendar"><!-- Our calendar wrapper div with day divs inside. -->
		<div class="ips-day"><div class="ips-day-label">Sunday</div></div>
		<div class="ips-day"><div class="ips-day-label">Monday</div></div>
		<div class="ips-day"><div class="ips-day-label">Tuesday</div></div>
		<div class="ips-day"><div class="ips-day-label">Wednesday</div></div>
		<div class="ips-day"><div class="ips-day-label">Thursday</div></div>
		<div class="ips-day"><div class="ips-day-label">Friday</div></div>
		<div class="ips-day"><div class="ips-day-label">Saturday</div></div>
	</div>
	<div class="ips-single-day" style="display: none;"></div>
</div>

<?php
global $wpdb; // global wordpress database object, used to interact with the database
$data_table_name = $wpdb->prefix . 'scheduler_data'; // get the table name with the wordpress prefix (wp_ by default)
$results = $wpdb->get_results("SELECT * FROM " . $data_table_name . " WHERE airing = 1;", OBJECT); //set $results to sql query getting airing shows
echo "<script>var result_data = " . json_encode($results) . ";</script>"; // for passing $results (php) to result_data (js) incredibly easily
?>

<script>
	//**note- content: [element, [start_time, end_time]] - each element is a show on a single day, a single card
	// Function to load a single card into the card deck
	function loadCard(content, dayIndex, cardDeck = []) { 
		//start with checking start and end dates
		var dst = getTime(content[1][0]); //start time today
		var det = getTime(content[1][1]); //end time today
		//is today's start time before start date week?
		if (content[0]["start_date"] != null) { //if there is a start date
			let startDate = new Date(content[0]["start_date"]);
			if (dst < startOfWeek(startDate)) { //if start time is before start date (change to day)
				//console.log(content[0]["name"] + "--> Before start date week! " + startOfWeek(startDate));
				return;
			}
		}

		//is today's end time after end date week?
		if (content[0]["end_date"] != null) { //if there is an end date
			let endDate = new Date(content[0]["end_date"]);
			if (det > endOfWeek(endDate)) { //if the week of the end date has passed, stop displaying. (change to day)
				//console.log(content[0]["name"] + "--> Past end date week! " + endOfWeek(endDate));
				return;
			}
		}

		//is it on this week (every-x-weeks)
		if (content[0]["every_x_weeks"] > 1) { //if it isn't every week
			let today = new Date();
			let start_date = new Date(content[0]["start_date"]);
			let diff = Math.floor((today - start_date) / (1000 * 60 * 60 * 24 * 7));
			if (diff % content[0]["every_x_weeks"] != 0) { //if (start time) to (today) mod% (every-x-weeks) != 0 it is not this week
				//console.log(content[0]["name"] + "--> Not this week!");
				return;
			}
		}

		//is it a one-time show?, if it is, is today after its one week?
		if (content[0]["one_time"] == 1) {
			let today = new Date();
			let start_date = new Date(content[0]["start_date"]);
			if (today > endOfWeek(start_date)) { //after the one week it aired, it stops airing
				//console.log(content[0]["name"] + "--> One-time show!");
				return;
			}
		}
		
		//if it passed all the checks, make the card
		const card = document.createElement('div'); //card is a div
		card.classList.add('ips-card'); 			// with class "ips-card"

		cardTitle = document.createElement('div'); 	//card title is a div
		cardTitle.classList.add('card-title'); 		// with class "card-title"
		cardTitle.innerHTML = content[0]["name"]; 	// the innerHTML is the name of the show

		cardDescription = document.createElement('div');		//card description is a div
		cardDescription.classList.add('card-description');		// with class "card-description"
		cardDescription.innerHTML = content[0]["description"];	// the innerHTML is the description of the show

		cardTime = document.createElement('div'); 							//card time is a div
		cardTime.classList.add('card-time');								// with class "card-time"
		cardTime.innerHTML = timeDisplay(dst) + " - " + timeDisplay(det); 	// the innerHTML is the start time and end time of the show formatted using timeDisplay

		card.appendChild(cardTitle); 		//append the title to the card
		card.appendChild(cardDescription);	//append the description to the card
		card.appendChild(cardTime); 		//append the time to the card

		cardDeck.push({
			"card": card,
			"day_index": dayIndex,
			"start_time": dst
		}); //push the card to the deck with day_index for placing appropriately and start_time for sorting

		//return the appended deck
		return cardDeck;
	}

	//function to sort the cards by start time
	function sortCardsByStartTime(cardDeck) { 
		returnDeck = cardDeck.sort((a, b) => { //use array sort function to sort by start time
			if (a['start_time'].getTime() > b['start_time'].getTime()) {
				return 1; //return one if its "greater"
			} else if (a['start_time'].getTime() < b['start_time'].getTime()) {
				return -1; //return negative one if its "lesser"
			} else {
				return 0; //return zero if they are equal
			}
		});
		//return the sorted deck
		return returnDeck;
	}

	//function to return a key->value pair array of start_time->cards
	function cardDeckLayer(cardDeck) {
		let layerTimes = {};

		cardDeck.forEach((card) => {
			if(timeDisplay(card['start_time']) in layerTimes) {
				layerTimes[timeDisplay(card['start_time'])].push(card);
			} else {
				layerTimes[timeDisplay(card['start_time'])] = [card];
			}
		});
		
		return layerTimes;
	}

	//function to format time for display
	function timeDisplay(time) {
		return time.toLocaleTimeString('en-US', {
			timeStyle: 'short'
		});
	}

	//function to get time from string
	function getTime(time) {
		let [h, m] = time.split(':');
		let date = new Date();
		date.setHours(h, m, 0);
		return date;
	}

	//function to get end of week (saturday) based on date
	function endOfWeek(date) {
		date = new Date(date);
		diff = date.getDate() - date.getDay();
		return_date = new Date(date.setDate(diff + 6));
		return return_date;
	}

	//function to get start of week (sunday) based on date
	function startOfWeek(date) {
		date = new Date(date);
		diff = date.getDate() - date.getDay();
		return_date = new Date(date.setDate(diff));
		return return_date;
	}

	if (result_data) { //if we have shows
		const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
		dayElements = Array.from(document.querySelectorAll('.ips-day'));
		dayElements[new Date().getDay()].style.backgroundColor = "#abd0f26b"; //highlight today 
		cardDeck = [];

		if (dayElements.length > 7) { //for handling more than one block on a page
			dayElements = dayElements.slice(-7);
		}

		result_data.forEach((element) => { //for creating a cardDeck out of our result data
			schedule = JSON.parse(element['schedule']); //convert schedule to json
			days.forEach((day) => { //for each day
				if (schedule[day][0]) { //if on that day, load the card into the deck
					//console.log(schedule[day]);
					cardDeck = loadCard([element, [schedule[day][1], schedule[day][2]]], days.indexOf(day), cardDeck);
				}
			});
		});

		//by this point we have a card deck of airing shows that should be shown this week
		cardDeck = sortCardsByStartTime(cardDeck); //sorted by time
		cardDeckLayered = cardDeckLayer(cardDeck); //layered by time
		layeredKeys = Object.keys(cardDeckLayered); //keys for the layers
		dayElements.forEach((day) => { //style the rows
			rows = "repeat(" + layeredKeys.length + ", 1fr)"
			day.style.gridTemplateRows = "max-content " + rows;
		});
		

		cardDeck.forEach((card) => { //load sorted cards into the days using time layering
			card["row"] = layeredKeys.indexOf(timeDisplay(card['start_time']));
			card['card'].style.gridRow = card['row'] + 2;
			card['card'].onclick = function() { //for big mode at the bottom
				document.querySelector('.ips-single-day').innerHTML = "";
				document.querySelector('.ips-single-day').appendChild(card['card'].cloneNode(true));
				document.querySelector('.ips-single-day').style.display = "block";
			};
			dayElements[card['day_index']].appendChild(card['card']);
		});

		// console.log(cardDeck);
	}
</script>

<style>
	.ips-calendar {
		font-family: Arial, sans-serif;
		display: grid;
		grid-template-columns: repeat(7, 1fr);
		gap: 5px;
		overflow-x: auto;
	}

	.ips-day {
		display: grid;
		border: 1px solid #ccc;
		padding: 10px;
		grid-template-rows: max-content;
	}

	.ips-day-label {
		border-bottom: solid lightgrey 1px;
		margin-bottom: 5px;
	}

	.ips-single-day {
		& >.ips-card:hover {
			background-color: #f0f0f0;
		}

		& >.ips-card>.card-description {
			height: 100%;
			min-height: 2.5rem;
		}
	}	

	.ips-card {
		background-color: #f0f0f0;
		padding: 5px;
		margin-bottom: 5px;
		min-width: 6.5rem;
		position: relative;
		border-radius: 5px;

	}

	.ips-card:hover {
		background-color: #e0e0e0;
	}

	.ips-card>.card-title {
		font-size: medium;
		font-weight: bold;
		background-color: #0693e3;
		color: white;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
		border-radius: 5px;
		padding: 0px 5px;
	}

	.ips-card>.card-description {
		font-size: small;
		height: 2.5rem;
		overflow-y: auto;
		overflow-x: hidden;
		scrollbar-width: thin;
	}

	.ips-card>.card-time {
		font-style: italic;
		font-size: x-small;
		white-space: nowrap;
		border-top: solid #0693e3 1px;
		text-align: center;
		margin: 0px 5px;
	}
</style>

</html>