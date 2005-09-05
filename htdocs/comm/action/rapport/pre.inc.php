<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Laurent Destailleur  <eldy@users.sourceforge.net>
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
 */

/**
	    \file       htdocs/comm/action/rapport/pre.inc.php
		\brief      Fichier gestionnaire du menu de gauche de la zone rapport des actions
		\version    $Revision$
*/

require("../../../main.inc.php");
require("./rapport.pdf.php");

function llxHeader($head = "", $urlp = "")
{
    global $conf,$user,$langs;
    
    top_menu($head);
    
    $menu = new Menu();
    
    
    $langs->load("commercial");
    $menu->add(DOL_URL_ROOT."/comm/action/", $langs->trans("Actions"));
    
    $menu->add_submenu(DOL_URL_ROOT."/comm/action/index.php?time=today", $langs->trans("Today"));
    $menu->add_submenu(DOL_URL_ROOT."/comm/action/index.php?status=todo", $langs->trans("MenuToDoActions"), 1);
    $menu->add_submenu(DOL_URL_ROOT."/comm/action/rapport/index.php", $langs->trans("Reportings"));
    
    if ($conf->societe->enabled) {
        $langs->load("companies");
        $menu->add(DOL_URL_ROOT."/comm/clients.php", $langs->trans("Customers"));
        $menu->add_submenu(DOL_URL_ROOT."/comm/contact.php?type=c", $langs->trans("Contacts"));
    }
    
    if ($conf->commercial->enabled) {
        $langs->load("commercial");
        $menu->add(DOL_URL_ROOT."/comm/prospect/prospects.php", $langs->trans("Prospects"));
        $menu->add_submenu(DOL_URL_ROOT."/comm/contact.php?type=p", $langs->trans("Contacts"));
    }
    
    if ($conf->propal->enabled) {
        $langs->load("propal");
        $menu->add(DOL_URL_ROOT."/comm/propal.php", $langs->trans("Prop"));
    }
    
    if ($conf->projet->enabled) {
        $langs->load("projects");
        $menu->add(DOL_URL_ROOT."/projet/index.php", $langs->trans("Projects"));
    }
    
    left_menu($menu->liste);
}

?>
