-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ===================================================================
-- Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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



create table llx_propal
(
  rowid SERIAL PRIMARY KEY,
  "fk_soc"          integer,
  "fk_projet"       integer DEFAULT 0,     -- projet auquel est rattache la propale
  "ref"             varchar(30) NOT NULL,  -- propal number
  "ref_client"      varchar(30),           -- customer order number
  "datec"           timestamp,              -- date de creation 
  "datep"           date,                  -- date de la propal
  "fin_validite"    timestamp,              -- date de fin de validite
  "date_valid"      timestamp,              -- date de validation
  "date_cloture"    timestamp,              -- date de cloture
  "fk_user_author"  integer,               -- createur de la propale
  "fk_user_valid"   integer,               -- valideur de la propale
  "fk_user_cloture" integer,               -- cloture de la propale signee ou non signee
  "fk_statut"       smallint  DEFAULT 0 NOT NULL,
  "price"           real      DEFAULT 0,
  "remise_percent"  real      DEFAULT 0,  -- remise globale relative en pourcent
  "remise_absolue"  real      DEFAULT 0,  -- remise globale absolue
  "remise"          real      DEFAULT 0,  -- remise calculee
  "tva"             real      DEFAULT 0,  -- montant tva apres remise globale
  "total_ht"        real      DEFAULT 0,  -- montant total ht apres remise globale
  "total"           real      DEFAULT 0,  -- montant total ttc apres remise globale
  "fk_cond_reglement"   integer,  -- condition de reglement (30 jours, fin de mois ...)
  "fk_mode_reglement"   integer,  -- mode de reglement (Virement, Pr�l�vement)
 
  "note"            text,
  "note_public"     text,
  "model_pdf"       varchar(50),
  "date_livraison" date default NULL,
  "fk_adresse_livraison"  integer,  -- adresse de livraison
  
  UNIQUE(ref)
);

CREATE INDEX idx_llx_propal_ref ON llx_propal (ref);
