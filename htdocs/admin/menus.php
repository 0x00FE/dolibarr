<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/admin/menus.php
        \ingroup    core,menudb
        \brief      Page de configuration des gestionnaires de menu
        \version    $Revision$
*/

require("./pre.inc.php");

$langs->load("companies");
$langs->load("products");
$langs->load("admin");

if (!$user->admin)
  accessforbidden();

$dirtop = "../includes/menus/barre_top";
$dirleft = "../includes/menus/barre_left";


/*
* Actions
*/

if (isset($_POST["action"]) && $_POST["action"] == 'update')
{
	dolibarr_set_const($db, "MAIN_MENU_BARRETOP",      $_POST["main_menu_barretop"]);
	dolibarr_set_const($db, "MAIN_MENU_BARRELEFT",     $_POST["main_menu_barreleft"]);
	
	dolibarr_set_const($db, "MAIN_MENUFRONT_BARRETOP", $_POST["main_menufront_barretop"]);
	dolibarr_set_const($db, "MAIN_MENUFRONT_BARRELEFT",$_POST["main_menufront_barreleft"]);
	
	$_SESSION["mainmenu"]="";   // Le gestionnaire de menu a pu changer
	
	Header("Location: ".$_SERVER["PHP_SELF"]."?mainmenu=home&leftmenu=setup");
	exit;
}


/*
* Affichage
*/

$html=new Form($db);

llxHeader();

print_fiche_titre($langs->trans("Menus"),'','setup');

print $langs->trans("MenusDesc")."<br>\n";
print "<br>\n";

$h = 0;

$head[$h][0] = DOL_URL_ROOT."/admin/menus.php";
$head[$h][1] = $langs->trans("MenuHandlers");
$head[$h][2] = 'handler';
$h++;

$head[$h][0] = DOL_URL_ROOT."/admin/menus/index.php";
$head[$h][1] = $langs->trans("MenuAdmin");
$head[$h][2] = 'editor';
$h++;

dolibarr_fiche_head($head, 'handler', $langs->trans("Menus"));



if (isset($_GET["action"]) && $_GET["action"] == 'edit')
{
    print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="action" value="update">';

    clearstatcache();

    // Gestionnaires de menu
    $var=true;

    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td width="35%">'.$langs->trans("Menu").'</td>';
    print '<td>';
	print $html->textwithhelp($langs->trans("InternalUsers"),$langs->trans("InternalExternalDesc"));
    print '</td>';
    print '<td>';
	print $html->textwithhelp($langs->trans("ExternalUsers"),$langs->trans("InternalExternalDesc"));
    print '</td>';
    print '</tr>';

    // Menu top
    $var=!$var;
    print '<tr '.$bc[$var].'><td>'.$langs->trans("DefaultMenuTopManager").'</td>';
    print '<td>';
    print $html->select_menu($conf->global->MAIN_MENU_BARRETOP,'main_menu_barretop',$dirtop);
    print '</td>';
    print '<td>';
    print $html->select_menu($conf->global->MAIN_MENUFRONT_BARRETOP,'main_menufront_barretop',$dirtop);
    print '</td>';
    print '</tr>';

    // Menu left
    $var=!$var;
    print '<tr '.$bc[$var].'><td>'.$langs->trans("DefaultMenuLeftManager").'</td>';
    print '<td>';
    print $html->select_menu($conf->global->MAIN_MENU_BARRELEFT,'main_menu_barreleft',$dirleft);
    print '</td>';
    print '<td>';
    print $html->select_menu($conf->global->MAIN_MENUFRONT_BARRELEFT,'main_menufront_barreleft',$dirleft);
    print '</td>';
    print '</tr>';

    print '</table>';
	
	print '<br><center>';
    print '<input class="button" type="submit" value="'.$langs->trans("Save").'">';
    print '</center>';

    print '</form>';
}
else
{
    // Gestionnaires de menu
    $var=true;

    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td width="35%">'.$langs->trans("Menu").'</td>';
    print '<td>';
	print $html->textwithhelp($langs->trans("InternalUsers"),$langs->trans("InternalExternalDesc"));
    print '</td>';
    print '<td>';
	print $html->textwithhelp($langs->trans("ExternalUsers"),$langs->trans("InternalExternalDesc"));
    print '</td>';
    print '</tr>';
    
    $var=!$var;
    print '<tr '.$bc[$var].'><td>'.$langs->trans("DefaultMenuTopManager").'</td>';
    print '<td>';
    $filelib=eregi_replace('\.php$','',$conf->global->MAIN_MENU_BARRETOP);
    print $filelib;
    print '</td>';
    print '<td>';
    $filelib=eregi_replace('\.php$','',$conf->global->MAIN_MENUFRONT_BARRETOP);
    print $filelib;
    print '</td>';
    print '</tr>';

    $var=!$var;
    print '<tr '.$bc[$var].'>';
    print '<td>'.$langs->trans("DefaultMenuLeftManager").'</td>';
    print '<td>';
    $filelib=eregi_replace('\.php$','',$conf->global->MAIN_MENU_BARRELEFT);
    print $filelib;
    print '</td>';
    print '<td>';
    $filelib=eregi_replace('\.php$','',$conf->global->MAIN_MENUFRONT_BARRELEFT);
    print $filelib;
    print '</td>';
    print '</tr>';

    print '</table>';
}

print '</div>';


if (! isset($_GET["action"]) || $_GET["action"] != 'edit')
{
    print '<div class="tabsAction">';
    print '<a class="tabAction" href="'.$_SERVER["PHP_SELF"].'?action=edit">'.$langs->trans("Edit").'</a>';
    print '</div>';
}
	
$db->close();

llxFooter('$Date$ - $Revision$');
?>
