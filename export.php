<?php

define("PROD_VERSION", "0.0.2");

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
  return date('Ymd\THisT', $timestamp);
}

// Escapes a string of characters.
function escapeString($string) {
  return preg_replace('/([\,;])/','\\\$1', $string);
}

$offset = 37;
$startYear = 2016;

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
	foreach($events as $singleEvent)
	{
		$output = preg_split("/(,|-)/", $singleEvent["weeks"]);
		$weekStart = str_pad((($offset + $output[0]) % 52), 2, "0", STR_PAD_LEFT);
		$yearAddOn = floor(($offset + $output[0]) / 52);
		$year = $startYear + $yearAddOn;
		$weekStartDate = date("Y-m-d", strtotime($year."W".$weekStart));
		//echo "<br>".$singleEvent["day"];
		$DTSTART = strtotime("next ".$singleEvent["day"], strtotime($weekStartDate)) + strtotime("1970-01-01T".$singleEvent["start"]);
		$DTEND = $DTSTART + str_pad(strtotime("1970-01-01T".$singleEvent["duration"]), "0", STR_PAD_LEFT);
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

		// Repeats to:
		$weekEnd = str_pad((($offset + max($weekNumbers)) % 52), 2, "0", STR_PAD_LEFT);
		$yearAddOn = floor(($offset + max($weekNumbers)) / 52);
		$year = $startYear + $yearAddOn;
		$weekEndDate = date("Y-m-d", strtotime($year."W".$weekEnd));
		$WEEKUNTIL = strtotime("next ".$singleEvent["day"], strtotime($weekEndDate) + 60*60*24); // Adds a day after the UNTIL, so that the last event will definitely be included.


		//echo "<br>STRTOTIME: ".strtotime($year." week ".$weekStart." at ".$singleEvent['start']." on ".$singleEvent['day']);
		//echo "<br>STRTOTIME: ".strtotime($singleEvent['day'].", ".$weekStartDate);
		/*echo "<br>";
		echo "<br>DATE: ".date("Y-m-d", strtotime($year."W".$weekStart));
		echo "<br>YEAR: ".$year;
		echo "<br>WEEKSTART: ".$weekStart;
		echo "<br>Data for STRTOTIME: ".$year."W".$weekStart;
		echo "<br>STRTOTIME: ".strtotime($year."W".$weekStart);
		echo "<br><br>";*/
/*
?>
		<br><br>
		BEGIN:VEVENT
			<br><b>UID:</b><?= uniqid() ?>
			<br><b>SUMMARY:</b><?= escapeString($singleEvent["activity"]." — ".$singleEvent["module"]) ?>
			<br><b>DTSTART:</b><?= DateToCal($DTSTART) ?>
			<br><b>DTEND:</b><?= dateToCal($DTEND) ?>
			<br><b>DTSTAMP:</b><?= dateToCal(time()) ?>
			<br><b>LOCATION:</b><?= escapeString($singleEvent["room"].", University of Nottingham") ?>
			<br><b>DESCRIPTION:</b><?= escapeString($singleEvent["size"]." person ".strtolower($singleEvent["nameOfType"])." with ".$singleEvent["staff"].", lasting for ".$singleEvent["duration"]." hours.\n\n".$singleEvent["roomDescription"]) ?>
			<br><b>URL;VALUE=URI:</b><?= escapeString($_POST["URLForImport"]) ?>
			<br><b>SEQUENCE:</b>
			<br><b>STATUS:</b></TENTATIVE
			<br><b>TRANSP:</b></OPAQUE
			<!-- Try to do this with only BYWEEKNO and not UNTIL. -->
			<br>RRULE:FREQ=WEEKLY;WKST=MO;BYWEEKNO=<?= escapeString($singleEvent["weeks"]) ?>
		END:VEVENT
	<?php
	*/
?>

BEGIN:VEVENT

TZID:BST

UID:<?= uniqid() ?>

SUMMARY:<?= escapeString($singleEvent["activity"]." — ".$singleEvent["module"]) ?>

DTSTART;TZID=Europe/London:<?= DateToCal($DTSTART) ?>

DTEND;TZID=Europe/London:<?= dateToCal($DTEND) ?>

DTSTAMP;TZID=Europe/London:<?= dateToCal(time()) ?>

LOCATION:<?= escapeString($singleEvent["room"].", University of Nottingham") ?>

DESCRIPTION:<?= escapeString($singleEvent["size"]." person ".strtolower($singleEvent["nameOfType"])." with ".$singleEvent["staff"].", lasting for ".$singleEvent["duration"]." hours.".$singleEvent["roomDescription"]) ?>

URL;VALUE=URI:<?= escapeString($_POST["URLForImport"]) ?>

SEQUENCE:0

STATUS:TENTATIVE

TRANSP:OPAQUE

RRULE:FREQ=WEEKLY;WKST=MO;UNTIL=<?= dateToCal($WEEKUNTIL) ?>

END:VEVENT
	<?php } ?>

END:VCALENDAR
