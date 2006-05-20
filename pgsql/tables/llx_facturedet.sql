-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ===================================================================
-- Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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

create table llx_facturedet
(
  rowid SERIAL PRIMARY KEY,
  "fk_facture"      integer NOT NULL,
  "fk_product"      integer NULL,      -- doit pouvoir etre nul pour ligne detail sans produits
  "description"     text,
  "tva_taux"        real DEFAULT 19.6, -- taux tva
  "qty"             real,              -- quantit�
  "remise_percent"  real DEFAULT 0,    -- pourcentage de remise
  "remise"          real DEFAULT 0,    -- montant de la remise
  "subprice"        real,              -- prix avant remise
  "price"           real,              -- prix final
  "date_start"      timestamp,          -- date debut si service
  "date_end"        timestamp,          -- date fin si service
  "info_bits"		  integer DEFAULT 0,
  "fk_code_ventilation" integer DEFAULT 0 NOT NULL,
  "fk_export_compta"    integer DEFAULT 0 NOT NULL,
  "rang"                integer DEFAULT 0
);
