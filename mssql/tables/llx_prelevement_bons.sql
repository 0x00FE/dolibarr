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
-- Bons de pr�l�vement
--
-- statut 1 : transmis � la banque
-- statut 2 : credite
--
create table llx_prelevement_bons
(
  rowid          integer IDENTITY PRIMARY KEY,
  ref            varchar(12),        -- reference
  datec          datetime,           -- SMALLDATETIME de creation
  amount         real DEFAULT 0,     -- montant total du pr�l�vement
  statut         smallint DEFAULT 0, -- statut
  credite        smallint DEFAULT 0, -- indique si le prelevement a �t� credit�
  note           text,
  date_trans     datetime,           -- SMALLDATETIME de transmission � la banque
  method_trans   smallint,           -- m�thode de transmission
  fk_user_trans  integer,            -- user qui a effectu� la transmission
  date_credit    datetime,           -- SMALLDATETIME de cr�dit sur le compte
  fk_user_credit integer,            -- user qui a remont� l'info de cr�dit
  
  UNIQUE(ref)
);
