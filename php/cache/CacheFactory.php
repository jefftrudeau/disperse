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
 * Factory class for providing access to a static Cache object.  This provides a nice way to
 * instantiate only one instance of Cache per request.
 * 
 * @package Disperse
 */
class CacheFactory {
  
  /**
   * @staticvar Cache
   */
  private static $cache;
  
  /**
   * Returns an instance of Cache.
   * 
   * @return object
   */
  public static function &getInstance() {
    if (!self::$cache) {
      if (($implementation = Disperse::getCacheImplementation()) != 'Normal') {
        $class = $implementation;
      }
      $class .= 'Cache';
      require_once(Disperse::getHome().'/php/cache/'.$class.'.php');
      self::$cache =& new $class();
    }
    return self::$cache;
  }
}

?>
