<?php

define("PROD_VERSION", "0.2.2");

$events = unserialize(base64_decode($_POST['dataForExport']));

/*echo "<pre>";
print_r($_POST);
echo "</pre>";*/

/*echo "<pre>";
print_r($events);
echo "</pre>";*/

$filename = "timetable.ics";

//****************************************************************************************
// Layout and the functions were inspired by Jake Bellacera's PHPtoICS GitHub repository.
//****************************************************************************************

// Sets the correct header for this file.
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Converts a timestamp to the appropriate string format.
function dateToCal($timestamp) {
  return date('Ymd\THis', $timestamp);
}

// Escapes a string of characters.
function escapeString($string) {
  return preg_replace('/([\,;])/','\\\$1', $string);
}

function formatTime($time) {
	return str_pad(str_replace(":", "", $time), 4, "0", STR_PAD_LEFT);
}

// Prints the formatted ICS file content.
?>
BEGIN:VCALENDAR
PRODID:-//Adam Blakey//Scientia Course Planner to ICS <?php echo PROD_VERSION ?>//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:Default Calendar
X-WR-TIMEZONE:Europe/London

BEGIN:VTIMEZONE
TZID:Europe/London
X-LIC-LOCATION:Europe/London
BEGIN:DAYLIGHT
TZOFFSETFROM:+0000
TZOFFSETTO:+0100
TZNAME:BST
DTSTART:19700329T010000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0100
TZOFFSETTO:+0000
TZNAME:GMT
DTSTART:19701025T020000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE

<?php
	function createSingleEvent($singleEvent)
	{
		date_default_timezone_set("Europe/London");

		$offset = 38;
		$startYear = 2016;

		$output = preg_split("/(,|-)/", $singleEvent["weeks"]);
		$weekStart = str_pad((($offset + $output[0]) % 52), 2, "0", STR_PAD_LEFT);
		$yearAddOn = floor(($offset + $output[0]) / 52);
		$year = $startYear + $yearAddOn;
		$weekStartDate = date("Y-m-d", strtotime($year."W".$weekStart));
		//echo "<br>".$singleEvent["day"];
		$DTSTART = strtotime("next ".$singleEvent["day"], strtotime($weekStartDate) - 2*60*60*24) + strtotime("19700101T".formatTime($singleEvent["start"])."00Z"); // Takes 2 days off $weekStartDate so that the first day of the week is always included.
		$DTEND = $DTSTART + strtotime("19700101T".formatTime($singleEvent["duration"])."00Z");

		/*$splitIndividuals = explode(",", $singleEvent["weeks"])
		foreach ($splitIndividuals as $split)
		{
			if (preg_match(".*-.*", $split))
			{
				$counters = explode("-", $split);
				for ($counter[0])
			}
		}
		echo "<br><br>";
		print_r();
		echo "<br><br>";*/

		// Author of routine: hakre, http://stackoverflow.com/questions/7698664/converting-a-range-or-partial-array-in-the-form-3-6-or-3-6-12-into-an-arra
		$weekNumbers = explode(",", preg_replace_callback('/(\d+)-(\d+)/', function($m) {
    		return implode(',', range($m[1], $m[2]));
		}, $singleEvent["weeks"]));
		// End of Routine by hakre

		$firstWeekNumber = min($weekNumbers);
		$lastWeekNumber = max($weekNumbers);

		$exdates = array();

		for($i = $firstWeekNumber; $i <= $lastWeekNumber; $i++)
		{
			if (!(in_array($i, $weekNumbers)))
			{
				$weekNumber = str_pad((($offset + $i) % 52), 2, "0", STR_PAD_LEFT);
				$yearAddOn = floor(($offset + $i) / 52);
				$year = $startYear + $yearAddOn;
				$excludedWeekTimestamp = strtotime(date("Y-m-d", strtotime($year."W".$weekNumber)));

				$excludedEventDate = strtotime("next ".$singleEvent["day"], $excludedWeekTimestamp - 2*60*60*24) + strtotime("19700101T".formatTime($singleEvent["start"])."00Z"); // Takes 2 days off $weekStartDate so that the first day of the week is always included.

				$exdates[] = $excludedEventDate;
			}
		}

		// Repeats to:
		$weekEnd = str_pad((($offset + max($weekNumbers)) % 52), 2, "0", STR_PAD_LEFT);
		$yearAddOn = floor(($offset + max($weekNumbers)) / 52);
		$year = $startYear + $yearAddOn;
		$weekEndDate = date("Y-m-d", strtotime($year."W".$weekEnd));
		$WEEKUNTIL = strtotime("next ".$singleEvent["day"], strtotime($weekEndDate) + (7-1)*24*60*60); // Adds on a week, less a day so that the final week's events are included.

		$outputForFunction = "";

		$outputForFunction .= "BEGIN:VEVENT\n";

		$outputForFunction .= "DTSTART;TZID=Europe/London:".dateToCal($DTSTART)."\n";

		$outputForFunction .= "DTEND;TZID=Europe/London:".dateToCal($DTEND)."\n";

		$outputForFunction .= "RRULE:FREQ=WEEKLY;WKST=MO;UNTIL=".dateToCal($WEEKUNTIL).";BYDAY=".strtoupper(substr(date("D", $DTSTART), 0, 2))."\n";

		//if (false)
		foreach($exdates as $excludedDate)
		{
			$outputForFunction .= "EXDATE;TZID=Europe/London:".dateToCal($excludedDate)."\n";
		}

		$outputForFunction .= "DTSTAMP:".dateToCal(time())."\n";

		$outputForFunction .= "UID:".uniqid()."\n";

		$outputForFunction .= "CREATED:".dateToCal(time())."\n";

		$outputForFunction .= "DESCRIPTION:".escapeString($singleEvent["size"]." person ".strtolower($singleEvent["nameOfType"])." with ".$singleEvent["staff"].", lasting for ".$singleEvent["duration"]." hours.".$singleEvent["roomDescription"])."\n";

		$outputForFunction .= "LAST-MODIFIED:".dateToCal(time())."\n";

		$outputForFunction .= "LOCATION:".escapeString($singleEvent["room"].", University of Nottingham")."\n";

		$outputForFunction .= "SEQUENCE:0"."\n";

		$outputForFunction .= "STATUS:TENTATIVE"."\n";

		$outputForFunction .= "SUMMARY: ".escapeString($singleEvent["activity"]." — ".$singleEvent["module"])."\n";

		$outputForFunction .= "TRANSP:OPAQUE"."\n";

		if(false) { $outputForFunction .= "URL;VALUE=URI:".escapeString($_POST["URLForImport"])."\n"; }

		$outputForFunction .= "END:VEVENT"."\n\n";

		return $outputForFunction;
	}

	foreach($events as $singleEvent)
	{
		echo createSingleEvent($singleEvent);
	} ?>

END:VCALENDAR
