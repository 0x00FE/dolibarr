-- ========================================================================
-- Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
--
-- redaction : 0
-- valide    : 1
-- approuv�  : 2
-- envoye    : 3

create table llx_mailing
(
  rowid              integer AUTO_INCREMENT PRIMARY KEY,

  statut             smallint       DEFAULT 0,            --

  date_envoi         datetime,                            -- date d'envoi
  titre              varchar(60),                         -- company name
  sujet              varchar(60),                         -- company name
  body               text,
  cible              varchar(60),

  nbemail            integer,

  email_from         varchar(160),                        -- company name
  email_replyto      varchar(160),                        -- company name
  email_errorsto     varchar(160),                        -- company name

  date_creat         datetime,                            -- creation date
  date_valid         datetime,                            -- creation date
  date_appro         datetime,                            -- creation date

  fk_user_creat      integer,                             -- utilisateur qui a cr�� l'info
  fk_user_valid      integer,                             -- utilisateur qui a cr�� l'info
  fk_user_appro      integer                              -- utilisateur qui a cr�� l'info

)type=innodb;

