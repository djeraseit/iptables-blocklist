#!/usr/bin/php
<?php
// Load the Blocklist class
require_once('Blocklist.php');

// Initiate the Blocklist class
try {
	$Blocklist = new Blocklist();
} catch (Exception $e) {
	echo "Cannot initiate Blocklist class: " . $e->getMessage() . "\n";
}

// Parse our blocklist files
try {
	$Blocklist->parse('bt_level1.gz');
	$Blocklist->parse('bt_level2.gz');
} catch (Exception $e) {
	echo "Error while parsing blocklists: " . $e->getMessage() . "\n";
}
