-- ===================================================================
-- Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
--
-- $Id$
-- $Source$
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General [public] License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General [public] License for more details.
--
-- You should have received a copy of the GNU General [public] License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- ===================================================================

create table llx_voyage_reduc
(
  rowid           integer IDENTITY PRIMARY KEY,
  datec           datetime,
  datev           datetime,           -- SMALLDATETIME de valeur
  date_debut      datetime,           -- SMALLDATETIME operation
  date_fin        datetime,
  amount          real NOT NULL DEFAULT 0,
  label           varchar(255),
  numero          varchar(255),
  fk_type         smallint,       -- Train, Avion, Bateaux
  note            text
);
