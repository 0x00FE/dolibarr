-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ===================================================================
-- Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
-- $Id: llx_osc_categories.sql,v 1.3 2007/08/26 19:21:40 eldy Exp $
-- $Source: /cvsroot/dolibarr/dolibarr/mysql/tables/llx_osc_categories.sql,v $
--
-- ===================================================================

CREATE TABLE llx_osc_categories (
  rowid SERIAL PRIMARY KEY,
  "dolicatid" int4 NOT NULL default '0',
  "osccatid" int4 NOT NULL default '0',
  UNIQUE(dolicatid),
  UNIQUE(osccatid)
) TYPE=InnoDB COMMENT='Correspondance categorie Dolibarr categorie OSC';

CREATE INDEX idx_llx_osc_categories_rowid ON llx_osc_categories (rowid);
CREATE INDEX dolicatid ON llx_osc_categories (dolicatid);
CREATE INDEX osccatid ON llx_osc_categories (osccatid);
