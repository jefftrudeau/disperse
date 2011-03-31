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

require_once(Disperse::getHome().'/php/cache/Cache.php');

/**
 * Extends Cache and provides a persistent storage mechanism for cached data through the use of
 * Datasource and its accompanying objects.
 * 
 * @package Disperse
 */
class PersistedCache extends Cache implements Cacheable {
  
  /**
   * @var Datasource
   */
  protected $datasource;
  
  /**
   * @var int
   */
  protected static $semaphore = 1;
  
  /**
   * Class constructor
   */
  public function __construct() {
    parent::__construct();
    $this->datasource =& DatasourceFactory::getInstance();
  }
  
  /**
   * Class destructor
   */
  public function __destruct() {
    parent::__destruct();
    $this->datasource->__destruct();
  }
  
  /**
   * Deletes the specified element from the cache.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   * @param int $expiration = 0
   */
  public function delete($key, $table = 'data', $expiration = 0) {
    if (Disperse::isCacheLocking()) {
      $this->lock($key, $table);
    }
    parent::delete($key, $table, $expiration);
    $this->datasource->delete($key, $table);
    if (Disperse::isCacheLocking()) {
      $this->unlock($key, $table);
    }
  }
  
  /**
   * Deletes items from the cache if they are due to expire.
   * 
   * @param string $table = 'data'
   */
  public function expire($table = 'data') {
    parent::expire($table);
    $this->datasource->expire($table);
  }
  
  /**
   * Flushes the entire cache.
   */
  public function flush() {
    parent::flush();
    $this->datasource->flush();
  }
  
  /**
   * Retrieves the specified element from the cache, or null if the element does not exist.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   * @return mixed
   */
  public function &get($key, $table = 'data') {
    $value = parent::get($key, $table);
    if (!$value) {
      $value = $this->datasource->get($key, $table);
    }
    return $value;
  }
  
  /**
   * Returns true if the cache is connected, false otherwise.
   * 
   * @return boolean
   */
  public function isConnected() {
    return (parent::isConnected() || $this->datasource->isConnected());
  }
  
  /**
   * Locks a key within the cache to prevent its value from being updated.
   * 
   * @param mixed $key
   * @param string $table
   */
  private function lock($key, $table) {
    while ($lock = parent::get($table.'_'.$key, 'lock')) {
      usleep(Disperse::getCacheLockingInterval());
    }
    parent::set($table.'_'.$key, self::$semaphore, 'lock');
  }
  
  /**
   * Inserts/updates the specified element in the cache.
   * 
   * @param mixed $key
   * @param mixed &$value
   * @param string $table = 'data'
   * @param boolean $compress = CACHE_COMPRESSION
   * @param int $expire = CACHE_EXPIRATION
   */
  public function set($key,
                      &$value,
                      $table = 'data',
                      $compress = CACHE_COMPRESSION,
                      $expire = CACHE_EXPIRATION) {
    if (Disperse::isCacheLocking()) {
      $this->lock($key, $table);
    }
    parent::set($key, $value, $table, $compress, $expire);
    $this->datasource->set($key, $value, $table, $expire);
    if (Disperse::isCacheLocking()) {
      $this->unlock($key, $table);
    }
  }
  
  /**
   * Unlocks a key within the cache to allow its value to be updated.
   * 
   * @param mixed $key
   * @param string $table
   */
  private function unlock($key, $table) {
    parent::delete($table.'_'.$key, 'lock');
  }
}

?>
