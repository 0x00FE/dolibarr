-- ========================================================================
-- Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
--
-- $Id$
-- $Source$
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
-- ========================================================================

create table llx_c_ape
(
  rowid       integer AUTO_INCREMENT UNIQUE,
  code_ape    varchar(5) PRIMARY KEY,
  libelle     varchar(255),
  active      tinyint default 1
)type=innodb;

insert into llx_c_ape (code_ape,libelle) values
('721Z','Conseil en syst�mes informatiques');
insert into llx_c_ape (code_ape,libelle) values
('722A','Edition de logiciels (non personnalis�s)');
insert into llx_c_ape (code_ape,libelle) values
('722C','Autres activit�s de r�alisation de logiciels');
insert into llx_c_ape (code_ape,libelle) values
('723Z','Traitement de donn�es');
insert into llx_c_ape (code_ape,libelle) values
('724Z','Activit�s de banques de donn�es');
insert into llx_c_ape (code_ape,libelle) values
('725Z','Entretien et r�paration de machines de bureau et de mat�riel informatique');
insert into llx_c_ape (code_ape,libelle) values
('726Z','Autres activit�s rattach�es � l\'informatique (L\'utilisation de cette classe est diff�r�e jusqu\'� nouvel avis)');
insert into llx_c_ape (code_ape,libelle) values
('731Z','Recherche-d�veloppement en sciences physiques et naturelles');
insert into llx_c_ape (code_ape,libelle) values
('732Z','Recherche-d�veloppement en sciences humaines et sociales');
