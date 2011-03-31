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
 * Quasi-equivalent (superior in some repects) to Java's LinkedHashSet.  Uses memcached as a storage
 * mechanism for persisting set data beyond the scope of the current request.
 * 
 * @package Disperse
 */
class LinkedCacheSet extends CacheSet {
  
  /**
   * Class constructor
   * 
   * @param string $name
   */
  public function __construct($name) {
    parent::__construct($name);
    $this->last();
  }
  
  /**
   * Class destructor
   */
  public function __destruct() {}
  
  /**
   * Adds the specified element to the end of the set.  The internal pointer is updated to the
   * position of the element (last in the set).
   * 
   * @param mixed $key
   * @param mixed $value
   */
  public function add($value) {
    parent::add($value);
    $this->last();
  }
  
  /**
   * Returns the element referenced by the internal pointer, or null if there is no element at the
   * current position.
   * 
   * @return mixed
   */
  public function current() {
    return current($this->set);
  }
  
  /**
   * Deletes and returns the specified element from the set, or returns null if the element does not
   * exist.  The internal pointer is updated to the first element in the set.
   * 
   * @param mixed $key
   * @return mixed
   */
  public function delete($key) {
    $value = parent::delete($key);
    $this->first();
    return $value;
  }
  
  /**
   * Updates the internal pointer to the first element in the set and returns that element, or false
   * if the set is empty.
   * 
   * @return mixed
   */
  public function first() {
    return reset($this->set);
  }
  
  /**
   * Updates the internal pointer to the last element in the set and returns that element, or false
   * if the set is empty.
   * 
   * @return mixed
   */
  public function last() {
    if (!$this->isEmpty()) {
      return end($this->set);
    }
    return false;
  }
  
  /**
   * Moves the internal pointer by a magnitude of $delta.  The default is to move the pointer in a
   * forward direction.
   * 
   * @param int $delta
   * @param boolean $forward = true
   */
  protected function move($delta, $forward = true) {
    for ($i = 0; $i < $delta; $i++) {
      $forward ? $this->next() : $this->previous();
    }
  }
  
  /**
   * Updates the internal pointer to the next element in the set and returns that element, or false
   * if there is no element at that location.
   * 
   * @return mixed
   */
  public function next() {
    return next($this->set);
  }
  
  /**
   * Updates the internal pointer to the previous element in the set and returns that element, or
   * false if there is no element at that location.
   * 
   * @return mixed
   */
  public function previous() {
    return prev($this->set);
  }
  
  /**
   * Updates the internal pointer to the specified element in the set and returns that element, or
   * returns false if key does not exist.
   * 
   * @param mixed $key
   * @return mixed
   */
  public function seek($key) {
    $keyIndex = $this->indexOf($key);
    if ($keyIndex > -1) {
      $index = $this->indexOf(key($this->set));
      if ($index < $keyIndex) {
        $this->move($keyIndex - $index);
      }
      else if ($index > $keyIndex) {
        $this->move($index - $keyIndex, false);
      }
      return $this->current();
    }
    return false;
  }
  
  /**
   * Updates the specified element's value within the set.  The internal pointer is updated to the
   * position of the element.
   * 
   * @param mixed $key
   * @param mixed $value
   */
  public function set($key, $value) {
    parent::set($key, $value);
    $this->seek($key);
  }
}

?>