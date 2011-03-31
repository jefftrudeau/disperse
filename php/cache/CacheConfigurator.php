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
 * Parses and stores the XML configuration for Disperse.
 * 
 * @package Disperse
 */
class CacheConfigurator {
  
  /**
   * @var string
   */
  private $_element = null;
  
  /**
   * Class constructor
   */
  public function __construct() {}
  
  /**
   * Class destructor
   */
  public function __destruct() {}
  
  /**
   * Read and parse the XML configuration.
   * 
   * @param string $file
   */
  public function parse($file) {
    try {
      if ($fp = @fopen($file, 'r')) {
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, '_elementBegin', '_elementEnd');
        xml_set_character_data_handler($parser, '_elementContents');
        while ($xml = fread($fp, 8192)) {
          if (!xml_parse($parser, $xml, feof($fp))) {
            break;
          }
        }
        xml_parser_free($parser);
        fclose($fp);
      }
    }
    catch (Exception $e) {
      print($e->getMessage());
    }
  }
  
  /**
   * 'start_element_handler' callback for use with xml_set_element_handler().
   * 
   * @param resource $parser
   * @param string $name
   * @param array $attributes
   */
  private function _elementBegin($parser, $name, $attributes) {
    switch ($name) {
      case 'CACHE':
        Disperse::isCacheCompressed(strval($attributes['COMPRESSION']) == 'true' ? true : false);
        Disperse::isCacheLocking(strval($attributes['LOCKING']) == 'true' ? true : false);
        Disperse::setCacheExpiration(intval($attributes['EXPIRATION']));
        Disperse::setCacheImplementation($attributes['IMPLEMENTATION']);
        Disperse::setCacheRetryInterval(intval($attributes['RETRY_INTERVAL']));
        break;
      case 'CLIENT':
        if ($attributes['LANGUAGE'] == 'php') {
          Disperse::setCacheClient($attributes['LIBRARY']);
        }
        break;
      case 'OPTION':
        if ($attributes['LANGUAGE'] == 'php') {
          Disperse::setDatasourceOption($attributes['NAME'], $attributes['VALUE']);
        }
        break;
      case 'REPLICATION_GROUP':
        Disperse::setCacheReplicationGroup($attributes['ID'], explode(' ', $attributes['SERVERS']));
        break;
      case 'SERVER':
        Disperse::setCacheServer($attributes['ID'],
                                 array('host' => $attributes['HOST'],
                                       'port' => $attributes['PORT']));
        break;
      case 'SESSION':
        if ($attributes['LANGUAGE'] == 'php') {
          Disperse::isSessionEnabled(strval($attributes['ENABLED']) == 'true' ? true : false);
          Disperse::setSessionLifetime(intval($attributes['LIFETIME']));
        }
        break;
      case 'STATEMENT':
        Disperse::setDatasourceStatement($attributes['TYPE'], $attributes['SQL']);
        break;
    }
    $this->_element = $name;
  }
  
  /**
   * 'handler' callback for use with xml_set_character_data_handler().
   * 
   * @param resource $parser
   * @param string $data
   */
  private function _elementContents($parser, $data) {
    switch ($this->_element) {
      case 'DSN':
        Disperse::setDatasourceConnection('dsn', $data);
        break;
      case 'PASSWORD':
        Disperse::setDatasourceConnection('password', $data);
        break;
      case 'TABLE':
        Disperse::setCacheTable($data);
        break;
      case 'USERNAME':
        Disperse::setDatasourceConnection('username', $data);
        break;
    }
  }
  
  /**
   * 'end_element_handler' callback for use with xml_set_element_handler().
   * 
   * @param resource $parser
   * @param string $name
   */
  private function _elementEnd($parser, $name) {
    $this->_element = null;
  }
}

?>
