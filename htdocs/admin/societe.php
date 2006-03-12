<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      �ric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
    	\file       htdocs/admin/societe.php
		\ingroup    propale
		\brief      Page d'administration/configuration du module Societe
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("admin");

if (!$user->admin)
  accessforbidden();


/*
 * Actions
 */
if ($_GET["action"] == 'setcodeclient')
{
	if (dolibarr_set_const($db, "SOCIETE_CODECLIENT_ADDON",$_GET["value"]))
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
	}
	else
	{
		dolibarr_print_error($db);	
	}
}

if ($_GET["action"] == 'setcodecompta')
{
	if (dolibarr_set_const($db, "SOCIETE_CODECOMPTA_ADDON",$_GET["value"]))
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
	}
	else
	{
		dolibarr_print_error($db);	
	}
}


/*
 * 	Affichage page configuration module societe
 *
 */

llxHeader();


print_titre($langs->trans("CompanySetup"));

print "<br>";


// Choix du module de gestion des codes clients / fournisseurs

print_titre($langs->trans("CustomerCodeChecker"));

print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print '  <td>'.$langs->trans("Name").'</td>';
print '  <td>'.$langs->trans("Description").'</td>';
print '  <td align="center">'.$langs->trans("Activated").'</td>';
print '  <td>&nbsp;</td>';
print "</tr>\n";

clearstatcache();

$dir = "../includes/modules/societe/";
$handle = opendir($dir);
if ($handle)
{
  $var = true;
  while (($file = readdir($handle))!==false)
    {
      if (substr($file, 0, 15) == 'mod_codeclient_' && substr($file, -3) == 'php')
	{
	  $file = substr($file, 0, strlen($file)-4);

	  require_once(DOL_DOCUMENT_ROOT ."/includes/modules/societe/".$file.".php");

	  $modCodeClient = new $file;
	  $var = !$var;
	  print "<tr ".$bc[$var].">\n  <td width=\"140\">".$modCodeClient->nom."</td>\n  <td>";
	  print $modCodeClient->info();
	  print "</td>\n";
	  
	  if ($conf->global->SOCIETE_CODECLIENT_ADDON == "$file")
	    {
	      print "  <td align=\"center\">\n";
    	  print img_tick();
	      print "</td>\n  <td>&nbsp;</td>\n";
	    }
	  else
	    {

	      print '<td>&nbsp;</td>';
	      print '<td align="center"><a href="societe.php?action=setcodeclient&amp;value='.$file.'">'.$langs->trans("Activate").'</a></td>';
	    }
	  
	  print '</tr>';
	}
    }
  closedir($handle);
}
print '</table>';


print "<br>";


// Choix du module de gestion des codes compta

print_titre($langs->trans("AccountCodeManager"));

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td align="center">'.$langs->trans("Activated").'</td>';
print '<td>&nbsp;</td>';
print "</tr>\n";

clearstatcache();

$dir = "../includes/modules/societe/";
$handle = opendir($dir);
if ($handle)
{
  $var = true;
  while (($file = readdir($handle))!==false)
    {
      if (substr($file, 0, 15) == 'mod_codecompta_' && substr($file, -3) == 'php')
	{
	  $file = substr($file, 0, strlen($file)-4);

	  require_once(DOL_DOCUMENT_ROOT ."/includes/modules/societe/".$file.".php");

	  $modCodeCompta = new $file;
	  $var = !$var;

	  print '<tr '.$bc[$var].'><td width="140">'.$modCodeCompta->nom."</td><td>\n";
	  print $modCodeCompta->info();
	  print '</td>';
	  
	  if ($conf->global->SOCIETE_CODECOMPTA_ADDON == "$file")
	    {
	      print '<td align="center">';
    	  print img_tick();
	      print '</td><td>&nbsp;</td>';
	    }
	  else
	    {
	      print '<td>&nbsp;</td>';
	      print '<td align="center"><a href="societe.php?action=setcodecompta&amp;value='.$file.'">'.$langs->trans("Activate").'</a></td>';

	    }
	  
	  print "</tr>\n";
	}
    }
  closedir($handle);
}
print "</table>\n";
	


$db->close();

llxFooter();
?>
