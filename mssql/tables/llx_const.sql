-- ============================================================================
-- Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2003 Jean-Louis Bergamo <jlb@j1b.org>
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
--
-- ===========================================================================
--
-- Definitions des constantes utilis�s comme parametres de configuration
--

create table llx_const
(
  rowid       int IDENTITY PRIMARY KEY,
  name        varchar(255),
  value       text, -- max 65535 caracteres
  type varchar(6) check (type in ('yesno','texte','chaine')),
  visible     tinyint DEFAULT 1 NOT NULL,
  note        text,
);

CREATE UNIQUE INDEX name ON llx_const(name)
