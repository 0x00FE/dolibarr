-- =============================================================================
-- Copyright (C) 2000-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2004      Benoit Mortier <benoit.mortier@opensides.be>
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
-- =============================================================================

create table llx_societe_rib
(
  rowid           serial PRIMARY KEY,
  fk_soc          integer NOT NULL,
  datec           timestamp without time zone,
  tms             timestamp,
  label           varchar(30),
  bank            varchar(255),
  code_banque     varchar(7),
  code_guichet    varchar(6),
  number          varchar(255),
  cle_rib         varchar(5),
  bic             varchar(10),
  iban_prefix     varchar(5),
  domiciliation   varchar(255),
  proprio         varchar(60),
  adresse_proprio varchar(255)

);
