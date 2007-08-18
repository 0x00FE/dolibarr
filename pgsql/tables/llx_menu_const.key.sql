-- Generated from dolibarr_mysql2pgsql
-- (c) 2004, PostgreSQL Inc.
-- (c) 2005, Laurent Destailleur.

-- ========================================================================
-- Copyright (C) 2007 Patrick Raguin <patrick.raguin@gmail.com>
-- Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
-- $Id: llx_menu_const.key.sql,v 1.2 2007/02/08 14:40:31 patrickrgn Exp $
-- $Source: /sources/dolibarr/dolibarr/mysql/tables/llx_menu_const.key.sql,v $
--
-- ========================================================================


ALTER TABLE `llx_menu_const` ADD INDEX `idx_menu_const_fk_menu` (`fk_menu`);
ALTER TABLE `llx_menu_const` ADD INDEX `idx_menu_const_fk_constraint` (`fk_constraint`);

ALTER TABLE `llx_menu_const` ADD CONSTRAINT `fk_menu_const_fk_menu` FOREIGN KEY (`fk_menu`) REFERENCES `llx_menu` (`rowid`);
ALTER TABLE `llx_menu_const` ADD CONSTRAINT `fk_menu_const_fk_constraint` FOREIGN KEY (`fk_constraint`) REFERENCES `llx_menu_constraint` (`rowid`);
