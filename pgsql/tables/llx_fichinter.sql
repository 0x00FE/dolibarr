-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ===================================================================
-- Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
-- ===================================================================


create table llx_fichinter
(
  rowid SERIAL PRIMARY KEY,
  "fk_soc"          integer NOT NULL,
  "fk_projet"       integer DEFAULT 0,     -- projet auquel est rattache la fiche
  "fk_contrat"      integer DEFAULT 0,     -- contrat auquel est rattache la fiche
  "ref"             varchar(30) NOT NULL,  -- number
  "tms"             timestamp,
  "datec"           timestamp,              -- date de creation 
  "date_valid"      timestamp,              -- date de validation
  "datei"           date,                  -- date de l'intervention
  "fk_user_author"  integer,               -- createur de la fiche
  "fk_user_valid"   integer,               -- valideur de la fiche
  "fk_statut"       smallint  DEFAULT 0,
  "duree"           real,
  "description"     text,
  "note_private"    text,
  "note_public"     text,
  UNIQUE(ref)
);

CREATE INDEX idx_llx_fichinter_ref ON llx_fichinter (ref);
