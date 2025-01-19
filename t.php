<?php
// Get the terminal width using `tput cols`
$visibleWidth = intval(exec('tput cols'));

// Define a fallback width for IDEs or unexpected scenarios
$defaultWidth = 120;

// Use the smaller of the detected width or the fallback
$consoleWidth = ($visibleWidth > 0 && $visibleWidth <= $defaultWidth) ? $visibleWidth : $defaultWidth;

// Define the content
$name = "Name";
$age = "Age";

// Calculate the number of dashes needed
$dashesLength = $consoleWidth - strlen($name) - strlen($age) - 1; // Subtract 1 for the space

// Ensure dash length is non-negative
$dashes = str_repeat("-", max(0, $dashesLength));

// Print the formatted line
echo "$name$dashes$age\n";
?>
