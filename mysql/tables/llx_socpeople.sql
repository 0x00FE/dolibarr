-- ============================================================================
-- Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
-- ============================================================================

create table llx_socpeople
(
  idp            integer AUTO_INCREMENT PRIMARY KEY,
  datec          datetime,
  tms            timestamp,
  fk_soc         integer,           -- lien vers la societe
  civilite       varchar(6),
  name           varchar(50),
  firstname      varchar(50),
  address        varchar(255),
  birthday       date,
  poste          varchar(80),
  phone          varchar(30),
  phone_perso    varchar(30),
  phone_mobile   varchar(30),
  fax            varchar(30),
  email          varchar(255),
  jabberid       varchar(255),
  fk_user        integer default 0, -- user qui a cr�� l'enregistrement
  fk_user_modif  integer,
  note           text
)type=innodb;
