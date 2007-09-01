-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ===================================================================
-- Copyright (C) 2002-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2005-2007 Regis Houssin        <regis.houssin@cap-networks.com>
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
-- $Id: llx_fichinterdet.sql,v 1.1 2007/08/28 07:46:40 hregis Exp $
-- $Source: /cvsroot/dolibarr/dolibarr/mysql/tables/llx_fichinterdet.sql,v $
-- ===================================================================

create table llx_fichinterdet
(
  rowid SERIAL PRIMARY KEY,
  "fk_fichinter"      integer,
  "date"              date,              -- date de la ligne d'intervention
  "description"       text,              -- description de la ligne d'intervention
  "duree"             integer,           -- duree de la ligne d'intervention
  "rang"              integer DEFAULT 0  -- ordre affichage sur la fiche
);
