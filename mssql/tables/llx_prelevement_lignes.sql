-- ===================================================================
-- Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
-- $Id$
-- $Source$
-- ===================================================================
--
--

create table llx_prelevement_lignes
(
  rowid               int IDENTITY PRIMARY KEY,
  fk_prelevement_bons int,
  fk_soc              int NOT NULL,
  statut              smallint DEFAULT 0,

  client_nom          varchar(255),
  amount              real DEFAULT 0,
  code_banque         varchar(7),
  code_guichet        varchar(6),
  number              varchar(255),
  cle_rib             varchar(5),

  note                text

);
