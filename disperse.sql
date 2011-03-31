/**
 * Enhanced client libraries for memcached.  Provides optional persistence to generic datasources,
 * replication among groups of memcached servers and support for sessions (depending on language).
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
 */

/**
 * The DDL for the disperse_data table.  This is purposely very generic; feel free to markup with
 * RDBMS-specific options as needed, then run the script within your favorite SQL client.
 */
CREATE TABLE disperse_data (
  id     varchar(255)   NOT NULL,
  data   varchar(32767) NULL,
  expire int(16)        NOT NULL
);

/**
 * The DDL for the disperse_lock table.  This is purposely very generic; feel free to markup with
 * RDBMS-specific options as needed, then run the script within your favorite SQL client.
 */
CREATE TABLE disperse_lock (
  id     varchar(255) NOT NULL,
  data   varchar(1)   NULL,
  expire int(16)      NOT NULL
);

/**
 * The DDL for the disperse_session table.  This is purposely very generic; feel free to markup with
 * RDBMS-specific options as needed, then run the script within your favorite SQL client.
 */
CREATE TABLE disperse_session (
  id     varchar(255)   NOT NULL,
  data   varchar(32767) NULL,
  expire int(16)        NOT NULL
);
