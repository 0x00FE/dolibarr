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
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  fk_soc          integer,
  fk_soc_contact  integer,
  fk_projet       integer DEFAULT 0,     -- projet auquel est rattache la propale
  ref             varchar(30) NOT NULL,  -- propal number
  datec           datetime,              -- date de creation 
  datep           date,                  -- date de la propal
  fin_validite    datetime,              -- date de fin de validite
  date_valid      datetime,              -- date de validation
  date_cloture    datetime,              -- date de cloture
  fk_user_author  integer,               -- createur de la propale
  fk_user_valid   integer,               -- valideur de la propale
  fk_user_cloture integer,               -- cloture de la propale signee ou non signee
  fk_statut       smallint  DEFAULT 0 NOT NULL,
  price           real      DEFAULT 0,
  remise_percent  real      DEFAULT 0,  -- remise globale en pourcent
  remise          real      DEFAULT 0,
  tva             real      DEFAULT 0,  -- montant tva apres remise globale
  total_ht        real      DEFAULT 0,  -- montant total ht apres remise globale
  total           real      DEFAULT 0,  -- montant total ttc apres remise globale

  fk_cond_reglement   integer,  -- condition de reglement (30 jours, fin de mois ...)
  fk_mode_reglement   integer,  -- mode de reglement (Virement, Pr�l�vement)

  note            text,
  model_pdf       varchar(50),

  UNIQUE INDEX (ref)
)type=innodb;
