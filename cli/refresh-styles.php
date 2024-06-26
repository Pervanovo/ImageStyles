<?php

/**
 * @file
 * Implements CLI Command for refreshing all image styles of a collection.
 */

if (!COCKPIT_CLI) {
  return;
}

include_once __DIR__ . '/../utils.php';

$name = $app->param('name', TRUE);

if (!$name) {
  return CLI::writeln("--name parameter is missing", FALSE);
}

if (!$collection = $app->module('collections')->collection($name)) {
  return CLI::writeln("Collection '{$name}' doesnt exists!", FALSE);
}

if (!$app->module('imagestyles')->hasStyles($collection)) {
  return CLI::writeln("Collection '{$name}' doesnt have any image/asset fields!", FALSE);
}

$entries = $app->storage->getCollection("collections/{$collection['_id']}")->find();
$entries = $entries->toArray();

CLI::writeln("");
CLI::writeln("Collection '{$name}' - Refreshing image styles...");

foreach ($entries as $idx => $entry) {
  CLI::writeln("Starting {$entry['_id']}...");
  // Flush all image styles.
  $app->module('imagestyles')->deleteEntryStyles($name, $entry);
  // Generate new image styles.
  $entry = $app->module('imagestyles')->updateEntryStyles($collection, $entry);
  // Save entry with style info
  $app->storage->save("collections/{$collection['_id']}", $entry);
  CLI::writeln("Done ".($idx+1)." of ".count($entries)."...");
}

$total = count($entries);
CLI::writeln("Done! Refreshed image styles for {$total} entries...");
