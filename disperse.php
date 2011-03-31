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
 * 'Base' object, responsible for providing an access mechanism to the configuration.
 * 
 * @package Disperse
 */
class Disperse {
  
  /**
   * @var array
   */
  private static $cache;
  
  /**
   * @var array
   */
  private static $datasource;
  
  /**
   * @var string
   */
  private static $home = null;
  
  /**
   * @var array
   */
  private static $session;
  
  /**
   * Class 'constructor'.  You can pass an absolute path to any valid XML file which will be loaded
   * as the active configuration.
   * 
   * @param string $file = null
   */
  public function initialize($file = null) {
    self::$home = dirname(__FILE__);
    if (!$file) {
      $file = self::$home.'/disperse.xml';
    }
    
    // load configuration
    require_once(self::$home.'/php/cache/CacheConfigurator.php');
    $configurator = new CacheConfigurator();
    $configurator->parse($file);
    
    // cache support
    require_once(self::$home.'/php/cache/CacheFactory.php');
    
    // datasource support (if configured)
    if (Disperse::isDatasourceEnabled()) {
      require_once(self::$home.'/php/datasource/DatasourceFactory.php');
    }
    
    // session support (if configured)
    if (Disperse::isSessionEnabled()) {
      require_once(self::$home.'/php/session/Session.php');
      ini_set('session.save_handler', 'user');
      $session = new Session();
      session_set_save_handler(array($session, 'open'),
                               array($session, 'close'),
                               array($session, 'read'),
                               array($session, 'write'),
                               array($session, 'destroy'),
                               array($session, 'garbageCollect'));
    }
    
    // client support (if configured)
    if (is_array(self::$cache['clients'])) {
      foreach (self::$cache['clients'] as $client) {
        require_once(self::$home.'/php/'.$client.'.php');
      }
    }
  }
  
  /**
   * Returns the enabled cache clients.
   * 
   * @return array
   */
  public static function getCacheClients() {
    return self::$cache['clients'];
  }
  
  /**
   * Returns the cache expiration.
   * 
   * @return int
   */
  public static function getCacheExpiration() {
    return CACHE_EXPIRATION;
  }
  
  /**
   * Returns the cache implementation.
   * 
   * @return string
   */
  public static function getCacheImplementation() {
    return self::$cache['implementation'];
  }
  
  /**
   * Returns the enabled cache replication groups.
   * 
   * @return array
   */
  public static function getCacheReplicationGroups() {
    return self::$cache['replication_groups'];
  }
  
  /**
   * Returns the cache retry interval.
   * 
   * @return int
   */
  public static function getCacheRetryInterval() {
    return self::$cache['retry_interval'];
  }
  
  /**
   * Returns the enabled cache servers.
   * 
   * @return array
   */
  public static function getCacheServers() {
    return self::$cache['servers'];
  }
  
  /**
   * Returns the enabled cache tables.
   * 
   * @return array
   */
  public static function getCacheTables() {
    return self::$cache['tables'];
  }
  
  /**
   * Returns details regarding the datasource connection.
   * 
   * @return array
   */
  public static function getDatasourceConnection() {
    return self::$datasource['connection'];
  }
  
  /**
   * Returns the datasource connection options.
   * 
   * @return array
   */
  public static function getDatasourceOptions() {
    return self::$datasource['options'];
  }
  
  /**
   * Returns the SQL used to build the prepared statement corresponding to the specified type.
   * 
   * @param string $type
   * @return string
   */
  public static function getDatasourceStatement($type) {
    return self::$datasource['statements'][$type];
  }
  
  /**
   * Returns the current working directory for the disperse project.
   * 
   * @return string
   */
  public static function getHome() {
    return self::$home;
  }
  
  /**
   * Returns the session lifetime.
   * 
   * @return int
   */
  public static function getSessionLifetime() {
    return self::$session['lifetime'];
  }
  
  /**
   * Returns true if the cache is using compression, false otherwise.  Optionally enables or
   * disables the use of cache compression.
   * 
   * @param boolean $compressed = null
   * @return boolean
   */
  public static function isCacheCompressed($compressed = null) {
    if ($compressed != null && !defined('CACHE_COMPRESSION')) {
      define('CACHE_COMPRESSION', $compressed ? MEMCACHE_COMPRESSED : 0);
    }
    return CACHE_COMPRESSION;
  }
  
  /**
   * Returns true if the cache is using locking, false otherwise.  Optionally enables or disables
   * the use of cache locking.
   * 
   * @param boolean $locking = null
   * @return boolean
   */
  public static function isCacheLocking($locking = null) {
    if ($locking != null) {
      self::$cache['locking'] = $locking;
    }
    return self::$cache['locking'];
  }
  
  /**
   * Returns true if the cache is using persistence, false otherwise.
   * 
   * @return boolean
   */
  public static function isDatasourceEnabled() {
    return (strpos(self::$cache['implementation'], 'Persisted') !== false);
  }
  
  /**
   * Returns true if the cache is providing session support, false otherwise.  Optionally enables or
   * disables session support.
   * 
   * @param boolean $enabled = null
   * @return boolean
   */
  public static function isSessionEnabled($enabled = null) {
    if ($enabled != null) {
      self::$session['enabled'] = $enabled;
    }
    return self::$session['enabled'];
  }
  
  /**
   * Enables a cache client.
   * 
   * @param string $client
   */
  public static function setCacheClient($client) {
    self::$cache['clients'][] = $client;
  }
  
  /**
   * Sets the cache expiration.
   * 
   * @param int $expiration
   */
  public static function setCacheExpiration($expiration) {
    if (!defined('CACHE_EXPIRATION')) {
      define('CACHE_EXPIRATION', $expiration);
    }
  }
  
  /**
   * Sets the cache implementation.
   * 
   * @param string $implementation
   */
  public static function setCacheImplementation($implementation) {
    self::$cache['implementation'] = $implementation;
  }
  
  /**
   * Enables or updates a cache replication group.
   * 
   * @param string $id
   * @param array $group
   */
  public static function setCacheReplicationGroup($id, $group) {
    self::$cache['replication_groups'][$id] = $group;
  }
  
  /**
   * Sets the cache retry interval.
   * 
   * @param int $interval
   */
  public static function setCacheRetryInterval($interval) {
    self::$cache['retry_interval'] = $interval;
  }
  
  /**
   * Enables or updates a cache server.
   * 
   * @param string $id
   * @param array $server
   */
  public static function setCacheServer($id, $server) {
    self::$cache['servers'][$id] = $server;
  }
  
  /**
   * Enables a cache table.
   * 
   * @param string $table
   */
  public static function setCacheTable($table) {
    self::$cache['tables'][] = $table;
  }
  
  /**
   * Sets a property of the datasource connection.
   * 
   * @param string $name
   * @param string $value
   */
  public static function setDatasourceConnection($name, $value) {
    self::$datasource['connection'][$name] = $value;
  }
  
  /**
   * Sets an option of the datasource connection.
   * 
   * @param string $name
   * @param string $value
   */
  public static function setDatasourceOption($name, $value) {
    self::$datasource['options'][$name] = $value;
  }
  
  /**
   * Sets the SQL used to build the prepared statement corresponding to the specified type.
   * 
   * @param string $type
   * @param string $statement
   */
  public static function setDatasourceStatement($type, $statement) {
    self::$datasource['statements'][$type] = $statement;
  }
  
  /**
   * Sets the session lifetime.
   * 
   * @param int $lifetime
   */
  public static function setSessionLifetime($lifetime) {
    self::$session['lifetime'] = $lifetime;
  }
}

?>
