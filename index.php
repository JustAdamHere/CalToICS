<?php

//****************************************************************************************
// Layout and the functions were inspired by Jake Bellacera's PHPtoICS GitHub repository.
//****************************************************************************************

// Sets the correct header for this file.
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Converts a timestamp to the appropriate string format.
function dateToCal($timestamp) {
  return date('Ymd\THis\Z', $timestamp);
}

// Escapes a string of characters.
function escapeString($string) {
  return preg_replace('/([\,;])/','\\\$1', $string);
}

// Prints the formatted ICS file content.
?>
BEGIN:VCALENDAR
PRODID:-//Adam Blakey//Scientia Course Planner to ICS 0.0.1//EN
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
	foreach($eventList as $singleEvent)
	{ ?>
		BEGIN:VEVENT
			UID:<?= uniqid() ?>
			SUMMARY:<?= escapeString($singleEvent[summary]) ?>
			DTSTART:<?= dateToCal($singleEvent[dateStart]) ?>
			DTEND:<?= dateToCal($singleEvent[dateEnd]) ??
			DTSTAMP:<?= dateToCal(time()) ?>
			LOCATION:<?= escapeString($singleEvent[location]) ?>
			DESCRIPTION:<?= escapeString($singleEvent[description]) ?>
			URL;VALUE=URI:<?= escapeString($singleEvent[uri]) ?>
			SEQUENCE:0
			STATUS:TENTATIVE
			TRANSP:OPAQUE
			RRULE:FREQ=WEEKLY;WKST=MO;BYWEEKNO=20;UNTIL=<?= dateToCal($singleEvent[repeatEnd]) ?>
		END:VEVENT
	<?php } ?>
END:VCALENDAR
