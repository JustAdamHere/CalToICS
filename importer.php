<html>
	<head>
		<title>Adam's CalToICS | Importer</title>
	</head>
	<body>
		<form id="pageChooserForm" name="pageChooserForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
			<label for="pageChooser">Enter the URL for the page which contains the calendar:</label>
			<input id="pageChooser" name="pageChooser" type="text">

			<input id="pageChooserSubmit" name="pageChooserSubmit" type="submit" value="Find Events" form="pageChooserForm">
		</form>
	</body>
</html>





<?php
// If the form has been submitted:
if (isset($_POST['pageChooserSubmit']))
{
	// Includes file made by Jose Solorzano to extract some of the data from the HTML source of the page provided below.
	include_once("./simple_html_dom.php");

	// Example page:
	//$page = file_get_html('http://uiwwwsylp01.nottingham.ac.uk:8001/reporting/TextSpreadsheet;programme+of+study;id;0003091%0D%0A?days=1-5&weeks=1-52&periods=1-32&template=SWSCUST+programme+of+study+TextSpreadsheet&height=100&week=100');

	// Entered page:
	$page = file_get_html($_POST['pageChooser']);

	echo $page;

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
}

?>