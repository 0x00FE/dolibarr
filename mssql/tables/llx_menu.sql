-- ========================================================================
-- Copyright (C) 2007 Patrick Raguin      <patrick.raguin@gmail.com>
-- Copyright (C) 2007 Laurent Destailleur <eldy@users.sourceforge.net>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- $Id$
-- $Source$
--
-- ========================================================================

CREATE TABLE llx_menu (
  rowid integer PRIMARY KEY NOT NULL ,

  menu_handler varchar(16) NOT NULL default 'auguria',	-- Menu handler name
  type varchar(4) NOT NULL check (type in ('top','left')) default 'left',		-- Menu top or left
  
  mainmenu varchar(100) NOT NULL,				-- Name family/module (home, companies, ...)
  fk_menu integer NOT NULL,					-- 0 or Id of mother menu line
  [order] SMALLINT NOT NULL,					-- Order of entry

  url varchar(255) NOT NULL,					-- Relative (or absolute) url to go
  target varchar(100) NULL,					-- Target of Url link

  titre varchar(255) NOT NULL,				-- Key for menu translation 
  langs varchar(100),							-- Lang file to load for translation

  level SMALLINT,							-- ???

  leftmenu varchar(100) NULL,					-- Condition to show or hide
  [right] varchar(255),							-- Condition to show enabled or disabled
  [user] SMALLINT NOT NULL default '0',		-- 0 if menu for all users, 1 for external only, 2 for internal only
);
