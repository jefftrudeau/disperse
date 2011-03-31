<?php

// returns a nicely formatted timestamp
function get_time() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + (float) $usec;
}

// returns the lesser of two numbers
function least($a, $b) {
  return ($a <= $b ? $a : $b);
}

?>