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

require_once(Disperse::getHome().'/php/cache/CacheObject.php');

/**
 * Replicates cached data among replication groups defined in include.php.  Differs from Cache in
 * that get() reads randomly from each replication group until a value is found (or all replication
 * groups have been queried), and set() updates all replication groups sequentially.
 * 
 * @package Disperse
 */
class ReplicatedCache extends CacheObject implements Cacheable {
  
  /**
   * Class constructor
   */
  public function __construct() {
    $this->isConnected = false;
    
    try {
      foreach (Disperse::getCacheReplicationGroups() as $name => $servers) {
        $this->connection[$name] =& new Memcache();
        foreach ($servers as $server) {
          $cache_servers = Disperse::getCacheServers();
          $this->connection[$name]->addServer($cache_servers[$server]['host'],
                                              $cache_servers[$server]['port']);
        }
        if (!$this->isConnected && $this->connection[$name]->getStats()) {
          $this->isConnected = true;
        }
      }
    }
    catch (Exception $e) {
      print($e->getMessage());
    }
  }
  
  /**
   * Class destructor
   */
  public function __destruct() {
    if ($this->isConnected) {
      foreach ($this->connection as &$connection) {
        $connection->close();
      }
      $this->isConnected = false;
    }
  }
  
  /**
   * Deletes the specified element from the cache.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   * @param int $expiration = 0
   */
  public function delete($key, $table = 'data', $expiration = 0) {
    if ($this->isConnected) {
      foreach ($this->connection as &$connection) {
        $connection->delete($table.'/'.$key, $expiration);
      }
    }
  }
  
  /**
   * Deletes items from the cache if they are due to expire.
   * 
   * @param string $table = 'data'
   */
  public function expire($table = 'data') {}
  
  /**
   * Flushes the entire cache.
   */
  public function flush() {
    if ($this->isConnected) {
      foreach ($this->connection as &$connection) {
        $connection->flush();
      }
    }
  }
  
  /**
   * Retrieves the specified element from the cache, or null if the element does not exist.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   * @return mixed
   */
  public function &get($key, $table = 'data') {
    $value = null;
    if ($this->isConnected) {
      uasort($this->connection, array('self', 'randomize'));
      foreach ($this->connection as &$connection) {
        if ($value = $connection->get($table.'/'.$key)) {
          break;
        }
      }
    }
    return $value;
  }
  
  /**
   * Returns true if the cache is connected, false otherwise.
   * 
   * @return boolean
   */
  public function isConnected() {
    return $this->isConnected;
  }
  
  /**
   * Array sorting callback which randomizes the order of an array, preserving keys.
   * 
   * @param mixed $a
   * @param mixed $b
   * @return int
   */
  public function randomize($a, $b) {
    if (mt_rand(1, 2) % 2 === 0) {
      return 1;
    }
    return -1;
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
    if ($this->isConnected) {
      foreach ($this->connection as &$connection) {
        $connection->set($table.'/'.$key, $value, $compress, $expire);
      }
    }
  }
}

?>
