<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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
	    \file       htdocs/contact/info.php
        \ingroup    societe
		\brief      Onglet info d'un contact
		\version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/contact.class.php");

$langs->load("companies");

llxHeader();


$contact = new Contact($db);
$contact->fetch($_GET["id"], $user);


$h=0;
$head[$h][0] = DOL_URL_ROOT.'/contact/fiche.php?id='.$_GET["id"];
$head[$h][1] = $langs->trans("General");
$h++;

$head[$h][0] = DOL_URL_ROOT.'/contact/perso.php?id='.$_GET["id"];
$head[$h][1] = $langs->trans("PersonalInformations");
$h++;

$head[$h][0] = DOL_URL_ROOT.'/contact/exportimport.php?id='.$_GET["id"];
$head[$h][1] = $langs->trans("ExportImport");
$h++;

$head[$h][0] = DOL_URL_ROOT.'/contact/info.php?id='.$_GET["id"];
$head[$h][1] = $langs->trans("Info");
$hselected=$h;
$h++;

dolibarr_fiche_head($head, $hselected, $langs->trans("Contact").": ".$contact->firstname.' '.$contact->name);

/*
 * Visualisation de la fiche
 *
 */

print '<table width="100%"><tr><td>';
$contact->info($_GET["id"]);
print '</td></tr></table>';
  
if ($contact->socid > 0)
{
  $objsoc = new Societe($db);
  $objsoc->fetch($contact->socid);
  
  print $langs->trans("Company").' : '.$objsoc->nom_url.'<br>';
}

dolibarr_print_object_info($contact);

print "</div>";

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
