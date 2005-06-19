<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      �ric Seigne <eric.seigne@ryxeo.com>
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
        \file       htdocs/admin/facture.php
		\ingroup    facture
		\brief      Page d'administration/configuration du module Facture
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("admin");
$langs->load("bills");

if (!$user->admin)
  accessforbidden();


$facture_addon_var      = FACTURE_ADDON;
$facture_addon_var_pdf  = FACTURE_ADDON_PDF;
$facture_rib_number_var = FACTURE_RIB_NUMBER;
$facture_chq_number_var = FACTURE_CHQ_NUMBER;
$facture_tva_option     = FACTURE_TVAOPTION;

$typeconst=array('yesno','texte','chaine');


if ($_GET["action"] == 'set')
{
  if (dolibarr_set_const($db, "FACTURE_ADDON",$_GET["value"]))
    $facture_addon_var = $_GET["value"];
}

if ($_POST["action"] == 'setribchq')
{
  if (dolibarr_set_const($db, "FACTURE_RIB_NUMBER",$_POST["rib"])) $facture_rib_number_var = $_POST["rib"];
  if (dolibarr_set_const($db, "FACTURE_CHQ_NUMBER",$_POST["chq"])) $facture_chq_number_var = $_POST["chq"];
}

if ($_GET["action"] == 'setpdf')
{
  if (dolibarr_set_const($db, "FACTURE_ADDON_PDF",$_GET["value"])) $facture_addon_var_pdf = $_GET["value"];
}

if ($_POST["action"] == 'setforcedate')
{
    dolibarr_set_const($db, "FAC_FORCE_DATE_VALIDATION",$_POST["forcedate"]);
    Header("Location: facture.php");    
    exit;
}

if ($_POST["action"] == 'settvaoption')
{
  if (dolibarr_set_const($db, "FACTURE_TVAOPTION",$_POST["optiontva"])) $facture_tva_option = $_POST["optiontva"];
}

if ($_POST["action"] == 'update' || $_POST["action"] == 'add')
{
	if (! dolibarr_set_const($db, $_POST["constname"],$_POST["constvalue"],$typeconst[$_POST["consttype"]],0,isset($_POST["constnote"])?$_POST["constnote"]:''));
	{
	  	print $db->error();
	}
}

if ($_GET["action"] == 'delete')
{
  if (! dolibarr_del_const($db, $_GET["rowid"]));
  {
    print $db->error();
  }
}

$dir = "../includes/modules/facture/";


llxHeader('',$langs->trans("BillsSetup"),'FactureConfiguration');

print_titre($langs->trans("BillsSetup"));


/*
 *  Module num�rotation
 */
print "<br>";
print_titre($langs->trans("BillsNumberingModule"));

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("Example").'</td>';
print '<td align="center" width="60">'.$langs->trans("Default").'</td>';
print "</tr>\n";

clearstatcache();

$handle=opendir($dir);

$var=True;
while (($file = readdir($handle))!==false)
{
  if (is_dir($dir.$file) && substr($file, 0, 1) <> '.' && substr($file, 0, 3) <> 'CVS')
    {
      $var = !$var;
      print '<tr '.$bc[$var].'><td width="100">';
      echo "$file";
      print "</td><td>\n";

      $filebis = $file."/".$file.".modules.php";

      // Chargement de la classe de num�rotation
      $classname = "mod_facture_".$file;
      require_once($dir.$filebis);

      $obj = new $classname($db);
      print $obj->info();

      print '</td>';

      // Affiche example
      print '<td>'.$obj->getExample().'</td>';
      
      print '<td align="center">';
      if ($facture_addon_var == "$file")
	{
	  print img_tick();
	}
      else
	{
      print '<a href="facture.php?action=set&amp;value='.$file.'">'.$langs->trans("Default").'</a>';
	}
	print "</td></tr>\n";
    }
}
closedir($handle);

print '</table>';


/*
 *  PDF
 */
print '<br>';
print_titre("Mod�les de facture pdf");

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td align="center" width="60">'.$langs->trans("Activated").'</td>';
print '<td align="center" width="60">'.$langs->trans("Default").'</td>';
print "</tr>\n";

clearstatcache();

$handle=opendir($dir);

$var=True;
while (($file = readdir($handle))!==false)
{
  if (substr($file, strlen($file) -12) == '.modules.php' && substr($file,0,4) == 'pdf_')
    {
	  $var = !$var;
      $name = substr($file, 4, strlen($file) -16);
      $classname = substr($file, 0, strlen($file) -12);

      print '<tr '.$bc[$var].'><td width="100">';
      echo "$name";
      print "</td><td>\n";
      require_once($dir.$file);
      $obj = new $classname($db);
      
      print $obj->description;

      print '</td><td align="center">';

      if ($facture_addon_var_pdf == "$name")
	{
      print '&nbsp;';
      print '</td><td align="center">';
	  print img_tick();
	}
      else
	{
	  print '&nbsp;';
      print '</td><td align="center">';
      print '<a href="facture.php?action=setpdf&amp;value='.$name.'">'.$langs->trans("Default").'</a>';
	}
	print "</td></tr>\n";

    }
}
closedir($handle);

print '</table>';


/*
 *  Modes de r�glement
 *
 */
print '<br>';
print_titre( "Mode de r�glement � afficher sur les factures");

print '<table class="noborder" width="100%">';
$var=True;

print '<form action="facture.php" method="post">';
print '<input type="hidden" name="action" value="setribchq">';
print '<tr class="liste_titre">';
print '<td>Mode r�glement � proposer</td>';
print '<td align="right"><input type="submit" value="'.$langs->trans("Modify").'"></td>';
print "</tr>\n";
$var=!$var;
print '<tr '.$bc[$var].'>';
print "<td>Proposer paiement par RIB sur le compte</td>";
print "<td>";
$sql = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."bank_account where clos = 0";
if ($db->query($sql))
{
  $num = $db->num_rows();
  $i = 0;
  if ($num > 0) {
    print "<select name=\"rib\">";
    print '<option value="0">Ne pas afficher</option>';
    while ($i < $num)
      {
	$var=!$var;
	$row = $db->fetch_row($i);
	
	if ($facture_rib_number_var == $row[0])
	  {
	    print '<option value="'.$row[0].'" selected>'.$row[1].'</option>';
	  }
	else
	  {
	    print '<option value="'.$row[0].'">'.$row[1].'</option>';
	  }
		  $i++;
      }
    print "</select>";
  } else {
    print "<i>Aucun compte bancaire actif cr��</i>";
  }
}
print "</td></tr>";
$var=!$var;
print '<tr '.$bc[$var].'>';
print "<td>Proposer paiement par ch�que � l'ordre et adresse du titulaire du compte</td>";
print "<td>";
$sql = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."bank_account where clos = 0";
$var=True;
if ($db->query($sql))
{
  $num = $db->num_rows();
  $i = 0;
  if ($num > 0)
    {
      print "<select name=\"chq\">";
      print '<option value="0">Ne pas afficher</option>';
      while ($i < $num)
	{
	  $var=!$var;
	  $row = $db->fetch_row($i);
	  
	  if ($facture_chq_number_var == $row[0])
	    {
	      print '<option value="'.$row[0].'" selected>'.$row[1].'</option>';
	    }
	  else
	    {
	      print '<option value="'.$row[0].'">'.$row[1].'</option>';
	    }
	  $i++;
	}
      print "</select>";
    } else {
      print "<i>Aucun compte bancaire actif cr��</i>";
    }
}
print "</td></tr>";
print "</form>";
print "</table>";


/*
 *  Options fiscale
 */
print '<br>';
print_titre("Options fiscales de facturation de la TVA");

print '<table class="noborder" width="100%">';
print '<form action="facture.php" method="post">';
print '<input type="hidden" name="action" value="settvaoption">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Option").'</td><td>'.$langs->trans("Description").'</td>';
print '<td align="right"><input type="submit" value="'.$langs->trans("Modify").'"></td>';
print "</tr>\n";
$var=True;
$var=!$var;
print "<tr ".$bc[$var]."><td width=\"140\"><label><input type=\"radio\" name=\"optiontva\" value=\"reel\"".($facture_tva_option != "franchise"?" checked":"")."> Option r�el</label></td>";
print "<td colspan=\"2\">L'option 'r�el' est la plus courante. Elle est � destination des entreprises et professions lib�rales.\nChaque produits/service vendu est soumis � la TVA (Dolibarr propose le taux standard par d�faut � la cr�ation d'une facture). Cette derni�re est r�cup�r�e l'ann�e suivante suite � la d�claration TVA pour les produits/services achet�s et est revers�e � l'�tat pour les produits/services vendus.";
print "</td></tr>\n";
$var=!$var;
print "<tr ".$bc[$var]."><td width=\"140\"><label><input type=\"radio\" name=\"optiontva\" value=\"facturation\"".($facture_tva_option == "facturation"?" checked":"")."> Option facturation</label></td>";
print "<td colspan=\"2\">L'option 'facturation' est utilis�e par les entreprises qui payent la TVA � facturation (vente de mat�riel).</td></tr>\n";
$var=!$var;
print "<tr ".$bc[$var]."><td width=\"140\"><label><input type=\"radio\" name=\"optiontva\" value=\"franchise\"".($facture_tva_option == "franchise"?" checked":"")."> Option franchise</label></td>";
print "<td colspan=\"2\">L'option 'franchise' est utilis�e par les particuliers ou professions lib�rales � titre occasionnel avec de petits chiffres d'affaires.\nChaque produits/service vendu est soumis � une TVA de 0 (Dolibarr propose le taux 0 par d�faut � la cr�ation d'une facture cliente). Il n'y a pas de d�claration ou r�cup�ration de TVA, et les factures qui g�rent l'option affichent la mention obligatoire \"TVA non applicable - art-293B du CGI\".</td></tr>\n";
$var=!$var;
print "</form>";
print "</table>";


print "<br>";
print_titre($langs->trans("OtherOptions"));

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td align="center" width="60">'.$langs->trans("Value").'</td>';
print '<td width="80">&nbsp;</td>';
print "</tr>\n";

$var=false;
print '<form action="facture.php" method="post">';
print '<input type="hidden" name="action" value="setforcedate">';
print '<tr '.$bc[$var].'><td>';
echo "Forcer la d�finition de la date des factures lors de la validation";
print '</td><td width="60" align="center">';
$forcedate=(defined("FAC_FORCE_DATE_VALIDATION") && FAC_FORCE_DATE_VALIDATION)?1:0;
$html=new Form($db);
print $html->selectyesno("forcedate",$forcedate,1);
print '</td><td align="center">';
print '<input type="submit" value="'.$langs->trans("Modify").'">';
print "</td></tr>\n";
print '</form>';

print '</table>';


print "<br>";

$db->close();

llxFooter('$Date$ - $Revision$');
?>
