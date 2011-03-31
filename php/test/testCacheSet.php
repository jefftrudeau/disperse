<?php

// include CacheSets
require(dirname(__FILE__).'/../../disperse.php');
require(dirname(__FILE__).'/functions.php');

// initialize, we could also pass in the path to an XML configuration file
Disperse::initialize();

session_start();
if ($_SESSION['script_name']) {
  printf("%s\n", $_SESSION['script_name']);
}
else {
  $_SESSION['script_name'] = $_SERVER['PHP_SELF'];
}

// see contents of cache?
$verbose = true;

// load cache
$t = get_time();
$set = new CacheSet('TestCacheSet');
printf("> %d items loaded from cache in %f seconds.\n", $set->size(), get_time() - $t);

// add some items to the cache
$t = get_time();
for ($i = 0; $i <= least($set->size(), 25); $i++) {
  $s = '';
  for ($j = 0; $j <= $i; $j++) {
    $s .= chr($j + 65);
  }
  $set->add($s);
}
printf("> %d items persisted to cache in %f seconds.\n", $set->size(), get_time() - $t);

if ($verbose) {
  foreach ($set->copy() as $item) {
    printf("%s\n", $item);
  }
}

?>
