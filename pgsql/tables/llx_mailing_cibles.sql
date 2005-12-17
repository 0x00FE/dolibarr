-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ========================================================================
-- Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
--


create table llx_mailing_cibles
(
  rowid SERIAL PRIMARY KEY,
  "fk_mailing"         integer NOT NULL,
  "fk_contact"         integer NOT NULL,
  "nom"                varchar(160),
  "prenom"             varchar(160),
  "email"              varchar(160) NOT NULL,
  "statut"             smallint NOT NULL DEFAULT 0,
  "url"                varchar(160),
  "date_envoi"         timestamp
);

