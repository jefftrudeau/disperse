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

require_once(Disperse::getHome().'/php/cache/Cacheable.php');

/**
 * Abstract class providing some basic shared properties to its children.
 * 
 * @package Disperse
 */
abstract class CacheObject implements Cacheable {
  
  /**
   * @var mixed
   */
  protected $connection;
  
  /**
   * @var mixed
   */
  protected $isConnected;
  
  /**
   * Deletes the specified element from the cache.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   * @param int $expiration = 0
   */
  public function delete($key, $table = 'data', $expiration = 0) {}
  
  /**
   * Deletes items from the cache if they are due to expire.
   * 
   * @param string $table = 'data'
   */
  public function expire($table = 'data') {}
  
  /**
   * Flushes the entire cache.
   */
  public function flush() {}
  
  /**
   * Retrieves the specified element from the cache, or null if the element does not exist.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   * @return mixed
   */
  public function &get($key, $table = 'data') {}
  
  /**
   * Returns true if the cache is connected, false otherwise.
   * 
   * @return boolean
   */
  public function isConnected() {}
  
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
                      $expire = CACHE_EXPIRATION) {}
}

?>
