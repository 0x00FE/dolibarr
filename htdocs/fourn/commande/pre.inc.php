<?PHP
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 * $Id$
 * $Source$
 *
 */

/**     \file   	htdocs/fourn/commande/pre.inc.php
        \ingroup    compta
        \brief  	Fichier gestionnaire du menu commandes fournisseurs
*/

require("../../main.inc.php");

require_once DOL_DOCUMENT_ROOT."/fournisseur.commande.class.php";

function llxHeader($head = "", $title = "")
{
  global $user, $langs;
  $langs->load("orders");
  /*
   *
   *
   */
  top_menu($head, $title);

  $menu = new Menu();

  $menu->add(DOL_URL_ROOT."/fourn/index.php", "Fournisseurs");

  $menu->add(DOL_URL_ROOT."/fourn/commande/", $langs->trans("Orders"));
  $menu->add_submenu(DOL_URL_ROOT."/fourn/commande/liste.php", $langs->trans("List"));

  left_menu($menu->liste);
}

?>
