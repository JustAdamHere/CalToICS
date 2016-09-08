<?php

// Includes file made by Jose Solorzano to extract some of the data from the HTML source of the page provided below.
include_once("./simple_html_dom.php");

$page = file_get_html('http://uiwwwsylp01.nottingham.ac.uk:8001/reporting/TextSpreadsheet;programme+of+study;id;0003091%0D%0A?days=1-5&weeks=1-52&periods=1-32&template=SWSCUST+programme+of+study+TextSpreadsheet&height=100&week=100');

// Finds all data in 'td' HTML elements.
$individualCells = $page->find('td');

// Counts through each of the elements in $individualCells.
$arrayCounter = 0;

// Loops through all elements in $individualCells.
foreach($individualCells as $cell)
{
	// If the regular expression is found, is fits the description for an activity name (module code).
	if (preg_match('/G[0-9][0-9](.*)/', $cell->plaintext))
	{
		// Keeps the $arrayCounter value for the start of each module.
		$eventStartIDs[] = $arrayCounter;
	}

	$arrayCounter++;
}

// A counter to count through each event.
$eventCounter = 0;

foreach($eventStartIDs as $singleStartID)
{
	$event[$eventCounter]["activity"] = $individualCells[$singleStartID + 0]->plaintext;
	$event[$eventCounter]["module"] = $individualCells[$singleStartID + 1]->plaintext;
	$event[$eventCounter]["nameOfType"] = $individualCells[$singleStartID + 2]->plaintext;
	$event[$eventCounter]["size"] = $individualCells[$singleStartID + 3]->plaintext;
	$event[$eventCounter]["day"] = $individualCells[$singleStartID + 4]->plaintext;
	$event[$eventCounter]["start"] = $individualCells[$singleStartID + 5]->plaintext;
	$event[$eventCounter]["end"] = $individualCells[$singleStartID + 6]->plaintext;
	$event[$eventCounter]["duration"] = $individualCells[$singleStartID + 7]->plaintext;
	$event[$eventCounter]["room"] = $individualCells[$singleStartID + 8]->plaintext;
	$event[$eventCounter]["roomDescription"] = $individualCells[$singleStartID + 9]->plaintext;
	$event[$eventCounter]["roomSize"] = $individualCells[$singleStartID + 10]->plaintext;
	$event[$eventCounter]["staff"] = $individualCells[$singleStartID + 11]->plaintext;
	$event[$eventCounter]["weeks"] = $individualCells[$singleStartID + 12]->plaintext;

	$eventCounter++;
}

echo "<pre>";
print_r($event);
echo "</pre>";

?>