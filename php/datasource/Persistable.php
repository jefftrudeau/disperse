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
 * Defines the structure of the various Datasource implementations.
 * 
 * @package Disperse
 */
interface Persistable {
  
  /**
   * Deletes the specified element from the datasource.  If $key is not specified, all data is
   * removed from the datasource.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   */
  public function delete($key, $table = 'data');
  
  /**
   * Deletes items from the datasource if they are due to expire.
   * 
   * @param string $table = 'data'
   */
  public function expire($table = 'data');
  
  /**
   * Flushes the entire datasource.
   */
  public function flush();
  
  /**
   * Retrieves the specified element from the datasource, or null if the element does not exist.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   * @return mixed
   */
  public function &get($key, $table = 'data');
  
  /**
   * Returns true if the datasource is connected, false otherwise.
   * 
   * @return boolean
   */
  public function isConnected();
  
  /**
   * Inserts/updates the specified element in the datasource.
   * 
   * @param mixed $key
   * @param mixed &$value
   * @param string $table = 'data'
   * @param mixed $expire = CACHE_EXPIRATION
   */
  public function set($key, &$value, $table = 'data', $expire = CACHE_EXPIRATION);
}

?>