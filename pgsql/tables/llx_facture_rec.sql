-- ===========================================================================
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
-- ===========================================================================

create table llx_facture_rec
(
  rowid              SERIAL PRIMARY KEY,
  titre              varchar(50) NOT NULL,
  fk_soc             integer NOT NULL,
  datec              timestamp,  -- date de creation
  amount             real     default 0 NOT NULL,
  remise             real     default 0,
  remise_percent     real     default 0,
  tva                real     default 0,
  total              real     default 0,
  total_ttc          real     default 0,
  fk_user_author     integer,   -- createur
  fk_projet          integer,   -- projet auquel est associ� la facture
  fk_cond_reglement  integer,   -- condition de reglement
  note               text
);

CREATE INDEX llx_facture_rec_fksoc ON llx_facture_rec (fk_soc);
