-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ===================================================================
-- Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
-- ===================================================================


create table llx_facture_fourn_det
(
  rowid SERIAL PRIMARY KEY,
  "fk_facture_fourn"  integer NOT NULL,
  "fk_product"        integer NULL,
  "description"       text,
  "pu_ht"             real DEFAULT 0,
  "qty"               smallint DEFAULT 1,
  "total_ht"          real DEFAULT 0,
  "tva_taux"          real DEFAULT 0,
  "tva"               real DEFAULT 0,
  "total_ttc"         real DEFAULT 0
);
