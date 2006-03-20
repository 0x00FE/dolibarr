<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006 Regis Houssin         <regis.houssin@cap-networks.com>
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
	    \file       htdocs/admin/avoir.php
		\ingroup    avoir
		\brief      Page d'administration/configuration du module Avoir
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("admin");
$langs->load("discount");

if (!$user->admin)
  accessforbidden();


if ($_GET["action"] == 'set')
{
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."avoir_model_pdf (nom) VALUES ('".$_GET["value"]."')";

    if ($db->query($sql))
    {

    }
}
if ($_GET["action"] == 'del')
{
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."avoir_model_pdf WHERE nom='".$_GET["value"]."'";

    if ($db->query($sql))
    {

    }
}


$avoir_addon_var_pdf = $conf->global->AVOIR_ADDON_PDF;

if ($_GET["action"] == 'setpdf')
{
    if (dolibarr_set_const($db, "AVOIR_ADDON_PDF",$_GET["value"]))
    {
        // La constante qui a �t� lue en avant du nouveau set
        // on passe donc par une variable pour avoir un affichage coh�rent
        $avoir_addon_var_pdf = $_GET["value"];
    }

    // On active le modele
    $sql_del = "delete from ".MAIN_DB_PREFIX."avoir_model_pdf where nom = '".$_GET["value"]."';";
    $db->query($sql_del);

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."avoir_model_pdf (nom) VALUES ('".$_GET["value"]."')";
    if ($db->query($sql))
    {

    }
}

$avoir_addon_var = $conf->global->AVOIR_ADDON;

if ($_GET["action"] == 'setmod')
{
    // \todo Verifier si module numerotation choisi peut etre activ�
    // par appel methode canBeActivated



	if (dolibarr_set_const($db, "AVOIR_ADDON",$_GET["value"]))
    {
      // la constante qui a �t� lue en avant du nouveau set
      // on passe donc par une variable pour avoir un affichage coh�rent
      $avoir_addon_var = $_GET["value"];
    }
}


/*
 * Affiche page
 */

$dir = DOL_DOCUMENT_ROOT .'/avoir/modules/';


//llxHeader('',$langs->trans("DiscountSetup"));

//print_titre($langs->trans("DiscountSetup"));

llxHeader("","");

$h = 0;

$head[$h][0] = DOL_URL_ROOT."/admin/facture.php";
$head[$h][1] = $langs->trans("Bills");
$h++;

$head[$h][0] = DOL_URL_ROOT."/admin/avoir.php";
$head[$h][1] = $langs->trans("Discounts");
$hselected=$h;
$h++;

dolibarr_fiche_head($head, $hselected, $langs->trans("DiscountSetup"));

/*
 *  Module num�rotation
 */
print "<br>";
print_titre($langs->trans("DiscountsNumberingModules"));

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name")."</td>\n";
print '<td>'.$langs->trans("Description")."</td>\n";
print '<td nowrap>'.$langs->trans("Example")."</td>\n";
print '<td align="center" width="60">'.$langs->trans("Activated").'</td>';
print '</tr>'."\n";

clearstatcache();

$handle = opendir($dir);
if ($handle)
{
    $var=true;
    while (($file = readdir($handle))!==false)
    {
        if (substr($file, 0, 10) == 'mod_avoir_' && substr($file, strlen($file)-3, 3) == 'php')
        {
            $file = substr($file, 0, strlen($file)-4);

            require_once(DOL_DOCUMENT_ROOT ."/avoir/modules/".$file.".php");

            $modAvoir = new $file;

            $var=!$var;
            print "<tr ".$bc[$var].">\n  <td width=\"140\">".$file."</td>";
            print "\n  <td>".$modAvoir->info()."</td>\n";
            print "\n  <td nowrap>".$modAvoir->getExample()."</td>\n";

            print '<td align="center">';
            if ($avoir_addon_var == "$file")
            {
                $title='';
                if ($modAvoir->getNextValue() != $langs->trans("NotAvailable"))
                {
                    $title=$langs->trans("NextValue").': '.$modAvoir->getNextValue();
                }
                print img_tick($title);
            }
            else
            {
                print "<a href=\"avoir.php?action=setmod&amp;value=".$file."\">".$langs->trans("Activate")."</a>";
            }
            print '</td>';


            print "</tr>\n";
        }
    }
    closedir($handle);
}
print "</table><br>\n";


/*
 * PDF
 */

print_titre($langs->trans("DiscountsPDFModules"));

$def = array();

$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."avoir_model_pdf";
$resql=$db->query($sql);
if ($resql)
{
  $i = 0;
  $num_rows=$db->num_rows($resql);
  while ($i < $num_rows)
    {
      $array = $db->fetch_array($resql);
      array_push($def, $array[0]);
      $i++;
    }
}
else
{
  dolibarr_print_error($db);
}

$dir = DOL_DOCUMENT_ROOT .'/avoir/modules/pdf/';

print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print "  <td width=\"140\">".$langs->trans("Name")."</td>\n";
print "  <td>".$langs->trans("Description")."</td>\n";
print '  <td align="center" colspan="2">'.$langs->trans("Activated")."</td>\n";
print '  <td align="center">'.$langs->trans("Default")."</td>\n";
print "</tr>\n";

clearstatcache();

$handle=opendir($dir);

$var=true;
while (($file = readdir($handle))!==false)
{
  if (substr($file, strlen($file) -12) == '.modules.php' && substr($file,0,10) == 'pdf_avoir_')
    {
      $name = substr($file, 10, strlen($file) - 24);
      $classname = substr($file, 0, strlen($file) -12);

      $var=!$var;
      print "<tr ".$bc[$var].">\n  <td>";
      print "$name";
      print "</td>\n  <td>\n";
      require_once($dir.$file);
      $obj = new $classname($db);
      
      print $obj->description;

      print "</td>\n  <td align=\"center\">\n";

      if (in_array($name, $def))
	{
	  print img_tick();
	  print "</td>\n  <td>";
	  print '<a href="avoir.php?action=del&amp;value='.$name.'">'.$langs->trans("Disable").'</a>';
	}
      else
	{
	  print "&nbsp;";
	  print "</td>\n  <td>";
	  print '<a href="avoir.php?action=set&amp;value='.$name.'">'.$langs->trans("Activate").'</a>';
	}

      print "</td>\n  <td align=\"center\">";

      if ($avoir_addon_var_pdf == "$name")
	{
	  print img_tick();
	}
      else
	{
      print '<a href="avoir.php?action=setpdf&amp;value='.$name.'">'.$langs->trans("Activate").'</a>';
	}
      print '</td></tr>';
    }
}
closedir($handle);

print '</table>';

/*
 *  Repertoire
 */
print '<br>';
print_titre($langs->trans("PathToDocuments"));

print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print "  <td>".$langs->trans("Name")."</td>\n";
print "  <td>".$langs->trans("Value")."</td>\n";
print "</tr>\n";
print "<tr ".$bc[false].">\n  <td width=\"140\">".$langs->trans("Directory")."</td>\n  <td>".$conf->avoir->dir_output."</td>\n</tr>\n";
print "</table>\n<br>";


$db->close();

llxFooter();
?>
