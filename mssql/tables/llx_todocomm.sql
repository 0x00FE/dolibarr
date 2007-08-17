-- ========================================================================
-- Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
--
-- $Id$
-- $Source$
--
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
-- Actions commerciales a effectuer
--
-- ========================================================================

create table llx_todocomm
(
  id             int IDENTITY PRIMARY KEY,
  datea          datetime,     -- date de l'action
  label          varchar(50),  -- libelle de l'action
  fk_user_action int,      -- id de la personne qui doit effectuer l'action
  fk_user_author int,      -- id auteur de l'action
  fk_soc         int,      -- id de la societe auquel est rattachee l'action
  fk_contact     int,      -- id du contact sur laquelle l'action 
                               -- doit etre effectuee
  note           text
);

