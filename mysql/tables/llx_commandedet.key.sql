-- ===================================================================
-- Copyright (C) 2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
-- ===================================================================


-- Supprimme orhpelins pour permettre mont�e de la cl�
-- V4 DELETE llx_commandedet FROM llx_commandedet LEFT JOIN llx_commande ON llx_commandedet.fk_commande = llx_commande.rowid WHERE llx_commande.rowid IS NULL;

ALTER TABLE llx_commandedet ADD INDEX idx_commandedet_fk_commande (fk_commande);
ALTER TABLE llx_commandedet ADD CONSTRAINT fk_commandedet_fk_commande FOREIGN KEY (fk_commande) REFERENCES llx_commande (rowid);
