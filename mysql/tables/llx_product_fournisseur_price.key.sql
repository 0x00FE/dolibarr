-- ============================================================================
-- Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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


ALTER TABLE llx_product_fournisseur_price ADD INDEX idx_fk_user (fk_user);
ALTER TABLE llx_product_fournisseur_price ADD INDEX idx_fk_soc (fk_soc);
ALTER TABLE llx_product_fournisseur_price ADD INDEX idx_fk_product (fk_product);

ALTER TABLE llx_product_fournisseur_price ADD FOREIGN KEY (fk_user)    REFERENCES llx_user (rowid);
ALTER TABLE llx_product_fournisseur_price ADD FOREIGN KEY (fk_soc)     REFERENCES llx_societe (idp);
ALTER TABLE llx_product_fournisseur_price ADD FOREIGN KEY (fk_product) REFERENCES llx_product (rowid);


