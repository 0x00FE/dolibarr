<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/**
        \file       htdocs/boutique/client/pre.inc.php
		\brief      Fichier gestionnaire du menu de gauche de l'espace boutique client
		\version    $Revision$
*/

require("../../main.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/client.class.php');


function llxHeader($head = "", $urlp = "")
{
  global $user, $conf;

  /*
   *
   *
   */
  top_menu($head);

  $menu = new Menu();

  $menu->add(DOL_URL_ROOT."/boutique/produits/osc-liste.php", "Produits");

  $menu->add(DOL_URL_ROOT."/boutique/client/", "Clients");

  $menu->add(DOL_URL_ROOT."/boutique/commande/", "Commandes");

/*  $menu->add(DOL_URL_ROOT."/boutique/notification/", "Notifications");

  $menu->add_submenu(DOL_URL_ROOT."/boutique/notification/produits.php", "Produits");

  $menu->add(DOL_URL_ROOT."/boutique/newsletter/", "Newsletter");

  $menu->add_submenu(DOL_URL_ROOT."/boutique/newsletter/fiche.php?action=create", "Nouvelle newsletter");
*/
  left_menu($menu->liste);
  /*
   *
   *
   */

}
?>
