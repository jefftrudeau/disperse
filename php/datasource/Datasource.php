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

require_once(Disperse::getHome().'/php/datasource/DatasourceObject.php');

/**
 * Wraps some simple functionality of PDO (PHP Data Objects).  An instance of this class should be
 * retrieved using DatasourceFactory, which maintains a static reference throughout the request.
 * 
 * @package Disperse
 */
class Datasource extends DatasourceObject implements Persistable {
  
  /**
   * Class constructor
   */
  public function __construct() {
    $this->isConnected = false;
    try {
      $connection = Disperse::getDatasourceConnection();
      $this->connection =& new PDO($connection['dsn'],
                                   $connection['username'],
                                   $connection['password'],
                                   Disperse::getDatasourceOptions());
      $this->isConnected = true;
    }
    catch (PDOException $e) {
      print($e->getMessage());
    }
  }
  
  /**
   * Class destructor
   */
  public function __destruct() {
    if ($this->isConnected) {
      if (!$this->connection->getAttribute(PDO::ATTR_PERSISTENT)) {
        $this->connection = null;
      }
      $this->isConnected = false;
    }
  }
  
  /**
   * Builds a prepared statement given a statement $type which matches a defined statement of the
   * form 'sql_$type' in persistence.php, and an optional non-associative array containing the
   * replacement values.  This function expects the defined statements to use question mark (?)
   * rather than named parameters.
   * 
   * @param string $type
   * @param string $table = 'data'
   * @param array $parameters = array()
   * @return object
   */
  private function prepareStatement($type, $table = 'data', $parameters = array()) {
    $statement = $this->connection->prepare(sprintf(Disperse::getDatasourceStatement($type),
                                                    $table));
    foreach ($parameters as $key => $value) {
      $statement->bindValue(++$key, $value);
    }
    return $statement;
  }
  
  /**
   * Deletes the specified element from the datasource.  If $key is not specified, all data is
   * removed from the datasource.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   */
  public function delete($key, $table = 'data') {
    if ($this->isConnected) {
      $statement = $this->prepareStatement('delete', $table, array($key));
      $statement->execute();
    }
  }
  
  /**
   * Deletes items from the datasource if they are due to expire.
   * 
   * @param string $table = 'data'
   */
  public function expire($table = 'data') {
    if ($this->isConnected) {
      $statement = $this->prepareStatement('expire', $table, array(time()));
      $statement->execute();
    }
  }
  
  /**
   * Flushes the entire datasource.
   */
  public function flush() {
    if ($this->isConnected) {
      global $cache_tables;
      foreach ($cache_tables as $table) {
        $statement = $this->prepareStatement('flush', $table);
        $statement->execute();
      }
    }
  }
  
  /**
   * Retrieves the specified element from the datasource, or null if the element does not exist.
   * 
   * @param mixed $key
   * @param string $table = 'data'
   * @return mixed
   */
  public function &get($key, $table = 'data') {
    $value = null;
    if ($this->isConnected) {
      $statement = $this->prepareStatement('select', $table, array($key));
      if ($statement->execute()) {
        $value = $statement->fetchColumn();
      }
    }
    return $value;
  }
  
  /**
   * Returns true if the datasource is connected, false otherwise.
   * 
   * @return boolean
   */
  public function isConnected() {
    return $this->isConnected;
  }
  
  /**
   * Inserts/updates the specified element in the datasource.
   * 
   * @param mixed $key
   * @param mixed &$value
   * @param string $table = 'data'
   * @param mixed $expire = CACHE_EXPIRATION
   */
  public function set($key, &$value, $table = 'data', $expire = CACHE_EXPIRATION) {
    if ($this->isConnected) {
      if ($this->get($key)) {
        $statement = $this->prepareStatement('update', $table, array($value, $expire, $key));
      }
      else {
        $statement = $this->prepareStatement('insert', $table, array($key, $value, $expire));
      }
      $statement->execute();
    }
  }
}

?>
