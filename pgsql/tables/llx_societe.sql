-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ========================================================================
-- Copyright (C) 2000-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
-- ========================================================================


create table llx_societe
(
  idp SERIAL PRIMARY KEY,
  "id"                 varchar(32),                         -- private id
  "active"             smallint       DEFAULT 0,            --
  "parent"             integer        DEFAULT 0,            --
  "tms"                timestamp,
  "datec"	             timestamp,                            -- creation date
  "datea"	             timestamp,                            -- activation date
  "nom"                varchar(60),                         -- company name
  "code_client"        varchar(15),                         -- code client
  "code_compta"        varchar(15),                         -- code compta
  "address"            varchar(255),                        -- company adresse
  "cp"                 varchar(10),                         -- zipcode
  "ville"              varchar(50),                         -- town
  "fk_departement"     integer        DEFAULT 0,            --
  "fk_pays"            integer        DEFAULT 0,            --
  "tel"                varchar(20),                         -- phone number
  "fax"                varchar(20),                         -- fax number
  "url"                varchar(255),                        --
  "fk_secteur"         integer        DEFAULT 0,            --
  "fk_effectif"        integer        DEFAULT 0,            --
  "fk_typent"          integer        DEFAULT 0,            --
  "fk_forme_juridique" integer        DEFAULT 0,            -- forme juridique INSEE
  "siren"	             varchar(9),                          -- siren ou RCS
  "siret"              varchar(14),                         -- numero de siret
  "ape"                varchar(4),                          -- code ape
  "tva_intra"          varchar(20),                         -- tva intracommunautaire
  "capital"            real,                                -- capital de la soci�t�
  "description"        text,                                --
  "fk_stcomm"          smallint       DEFAULT 0,            -- commercial statut
  "note"               text,                                --
  "services"           integer        DEFAULT 0,            --
  "prefix_comm"        varchar(5),                          -- prefix commercial
  "client"             integer        DEFAULT 0,            -- client oui/non
  "fournisseur"        smallint       DEFAULT 0,            -- fournisseur oui/non
  "rubrique"           varchar(255),                        -- champ rubrique libre
  "fk_user_creat"      integer,                             -- utilisateur qui a cr�� l'info
  "fk_user_modif"      integer,                             -- utilisateur qui a modifi� l'info
  "remise_client"      real           DEFAULT 0,            -- remise syst�matique pour le client
  "UNIQUE" INDEX(prefix_comm)
);

