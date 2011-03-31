<?php
/**
 * Enhanced client libraries for memcached.  Provides optional persistence to generic datasources,
 * replication among groups of memcached servers and support for sessions (depending on language).
 * 
 * Requires: {@link http://www.danga.com/memcached/ memcached},
 * {@link http://www.php.net/ PHP 5},
 * {@link http://pecl.php.net/memcache pecl_memcache} and
 * {@link http://www.php.net/pdo PDO} (for persistence, if applicable)
 * 
 * Copyright (C) 2009 Jeff Trudeau
 * 
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See
 * the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program.  If
 * not, see {@link http://www.gnu.org/licenses/}.
 * 
 * @package    Disperse
 * @author     Jeff Trudeau
 * @copyright  Copyright (C) Jeff Trudeau {@link http://www.jefftrudeau.org}
 * @license    http://opensource.org/licenses/gpl-3.0.html GPLv3
 * @filesource
 */

/**
 * Quasi-equivalent (superior in some repects) to Java's HashSet.  Uses memcached as a storage
 * mechanism for persisting set data beyond the scope of the current request.
 * 
 * @package Disperse
 */
class CacheSet {
  
  /**
   * @var Cache
   */
  protected $cache;
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var array
   */
  protected $set;
  
  /**
   * Class constructor
   * 
   * @param string $name
   */
  public function __construct($name) {
    $this->cache =& CacheFactory::getInstance();
    $this->name = $name;
    $this->load();
  }
  
  /**
   * Class destructor
   */
  public function __destruct() {}
  
  /**
   * Adds the specified element to the end of the set.
   * 
   * @param mixed $value
   */
  public function add($value) {
    $this->set[] = $value;
    $this->save();
  }
  
  /**
   * Returns true if the specified element is contained within the set, or false otherwise.
   * 
   * @param mixed $key
   * @return boolean
   */
  public function contains($key) {
    if ($this->indexOf($key) > -1) {
      return true;
    }
    return false;
  }
  
  /**
   * Creates a copy (clone) of the internal set.  As changes to the copy are not persisted in the
   * cache, this is used mainly for performing read/sort operations externally on the set.
   * 
   * @return array
   */
  public function &copy() {
    return $this->set;
  }
  
  /**
   * Deletes and returns the specified element from the set, or null if the element does not exist.
   * 
   * @param mixed $key
   * @return mixed
   */
  public function delete($key) {
    if ($value = $this->valueOf($key)) {
      $keys = array_keys($this->set);
      unset($this->set[$keys[$this->indexOf($key)]]);
      $this->save();
    }
    return $value;
  }
  
  /**
   * Returns the specified element from the set, or null if the element does not exist.
   * 
   * @param mixed $key
   * @return mixed
   */
  public function get($key) {
    return $this->valueOf($key);
  }
  
  /**
   * Returns the index of the element referenced by the specified key from the set, or -1 if the key
   * does not exist.
   * 
   * @param mixed $key
   * @return int
   */
  public function indexOf($key) {
    $keys = array_keys($this->set);
    for ($i = 0; $i < $this->size(); $i++) {
      if (strval($key) == strval($i) || strval($key) == strval($keys[$i])) {
        return $i;
      }
    }
    return -1;
  }
  
  /**
   * Returns whether or not the set is empty.
   * 
   * @return boolean
   */
  public function isEmpty() {
    return empty($this->set) || $this->size() == 0;
  }
  
  /**
   * Retrieves the internal set from the cache.
   */
  protected function load() {
    $this->set = unserialize($this->cache->get($this->name));
  }
  
  /**
   * Deletes the internal set from the cache.
   */
  public function purge() {
    $this->cache->delete($this->name);
    $this->load();
  }
  
  /**
   * Persists the internal set to the cache.
   */
  protected function save() {
    $this->cache->set($this->name, serialize($this->set));
  }
  
  /**
   * Updates the specified element's value within the set.
   * 
   * @param mixed $key
   * @param mixed $value
   */
  public function set($key, $value) {
    $this->set[$key] = $value;
    $this->save();
  }
  
  /**
   * Returns the number of elements in the set.
   * 
   * @return int
   */
  public function size() {
    return count($this->set);
  }
  
  /**
   * CacheSet's internal sorting facility, which uses PHP's array value sorting functions.  The
   * default assumes a non-associative set, and sorts in ascending order of values.
   * 
   * @param boolean $keepAssoc = false
   * @param boolean $reverseOrder = false
   * @return boolean
   */
  public function sort($keepAssoc = false, $reverseOrder = false) {
    $func = 'sort';
    if ($keepAssoc && $reverseOrder) {
      $func = 'arsort';
    }
    else if ($keepAssoc) {
      $func = 'asort';
    }
    else if ($reverseOrder) {
      $func = 'rsort';
    }
    if (call_user_func($func, $this->set)) {
      $this->save();
      return true;
    }
    return false;
  }
  
  /**
   * CacheSet's internal sorting facility, which uses PHP's array key sorting functions.  The
   * default sorts in ascending order of keys.
   * 
   * @param boolean $reverseOrder = false
   * @return boolean
   */
  public function sortByKey($reverseOrder = false) {
    $func = ($reverseOrder ? 'krsort' : 'ksort');
    if (call_user_func($func, $this->set)) {
      $this->save();
      return true;
    }
    return false;
  }
  
  /**
   * Updates the internal set.  Useful in conjunction with copy() if advanced sorting operations are
   * required on the set - use copy() to create a local clone of the set, perform the necessary
   * operations on the clone, then use update() to refresh the structure and contents of the set.
   * 
   * @param mixed &$set
   */
  public function update(&$set) {
    $this->set =& $set;
    $this->save();
  }
  
  /**
   * Returns the value of the element referenced by the specified key from the set, or null if the
   * key does not exist.
   * 
   * @param mixed $key
   * @return mixed
   */
  protected function valueOf($key) {
    $index = $this->indexOf($key);
    if ($index > -1) {
      $keys = array_keys($this->set);
      return $this->set[$keys[$index]];
    }
    return null;
  }
}

?>