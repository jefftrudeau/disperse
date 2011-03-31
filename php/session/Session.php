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
 * Disperse's PHP session implementation.
 * 
 * @package Disperse
 */
class Session {
  
  /**
   * @var Cache
   */
  protected $cache;

  /**
   * Class constructor
   */
  public function __construct() {
    $this->cache =& CacheFactory::getInstance();
  }
  
  /**
   * Class destructor
   */
  public function __destruct() {
    session_write_close();
  }
  
  /**
   * Session 'close' callback.
   * 
   * @return boolean
   */
  public function close() {
    return true;
  }
  
  /**
   * Session 'destroy' callback.
   * 
   * @param string $id
   */
  public function destroy($id) {
    $this->cache->delete($id, 'session');
  }
  
  /**
   * Session 'gc' callback.
   */
  public function garbageCollect() {
    $this->cache->expire('session');
  }
  
  /**
   * Session 'open' callback.
   * 
   * @return boolean
   */
  public function open() {
    return true;
  }
  
  /**
   * Session 'read' callback.
   * 
   * @param string $id
   * @return mixed
   */
  public function &read($id) {
    $data = null;
    if ($data =& $this->cache->get($id, 'session')) {
      $data = unserialize($data);
    }
    return $data;
  }
  
  /**
   * Session 'write' callback.
   * 
   * @param string $id
   * @param mixed &$data
   */
  public function write($id, &$data) {
  	$this->cache->set($id,
                      serialize($data),
                      'session',
                      CACHE_COMPRESSION,
                      time() + Disperse::getSessionLifetime());
  }
}

?>