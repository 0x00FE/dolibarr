<?php
/* Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
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

/**     \file       htdocs/adherents/pre.inc.php
        \ingroup    adherent
		\brief      Fichier de gestion du menu gauche du module adherent
		\version    $Revision$
*/

require("../main.inc.php");

function llxHeader($head = "") {
  global $user, $conf, $langs;

  $langs->load("members");
  
  top_menu($head);

  $menu = new Menu();


  $menu->add("index.php",$langs->trans("Members"));
  $menu->add_submenu("fiche.php?action=create",$langs->trans("NewMember"));
  $menu->add_submenu("liste.php?statut=-1","Adh�sions � valider");
  $menu->add_submenu("liste.php?statut=1","Adh�rents � ce jour");
  $menu->add_submenu("liste.php?statut=0","Adh�sions r�sili�es");

  $menu->add(DOL_URL_ROOT."/public/adherents/","Espace adherents public");

  $menu->add("index.php","Export");
  $menu->add_submenu("htpasswd.php","Format htpasswd");
  $menu->add_submenu("cartes/carte.php","Cartes d'adh�rents");
  $menu->add_submenu("cartes/etiquette.php","Etiquettes d'adh�rents");

  $langs->load("compta");
  $menu->add("index.php",$langs->trans("Accountancy"));
  $menu->add_submenu("cotisations.php","Cotisations");
  $langs->load("banks");
  $menu->add_submenu(DOL_URL_ROOT."/compta/bank/",$langs->trans("Banks"));

  $menu->add("index.php",$langs->trans("Setup"));
  $menu->add_submenu("type.php",$langs->trans("MembersTypes"));
  $menu->add_submenu("options.php",$langs->trans("MembersAttributes"));

  left_menu($menu->liste);

}

?>
