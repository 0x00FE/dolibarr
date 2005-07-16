<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Regis Houssin        <regis.houssin@cap-networks.com>
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
        \file       htdocs/admin/barcode.php
		\ingroup    barcode
		\brief      Page d'administration/configuration du module Code barre
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("admin");
$langs->load("bills");

if (!$user->admin)
  accessforbidden();

$barcode_encode_type_set = BARCODE_ENCODE_TYPE;

$typeconst=array('yesno','texte','chaine');

if ($_GET["action"] == 'settype' && $user->admin)
{
	if (dolibarr_set_const($db, "BARCODE_ENCODE_TYPE",$_GET["value"]))
	$barcode_encode_type_set = $_GET["value"];
}

/*
  $sql = "DELETE FROM ".MAIN_DB_PREFIX."const WHERE name = 'BARCODE_ENCODE_TYPE';";
  $db->query($sql);

  $sql = "INSERT INTO ".MAIN_DB_PREFIX."const (name,value,visible) VALUES
	('BARCODE_ENCODE_TYPE','".$_POST["host"]."',0);";
	$db->query($sql);
}
*/

llxHeader('',$langs->trans("BarcodeSetup"),'BarcodeConfiguration');

print_titre($langs->trans("BarcodeSetup"));

/*
 *  CHOIX ENCODAGE
 */
 
print '<br>';
print_titre($langs->trans("BarcodeEncodeModule"));

print '<table class="noborder" width="100%">';

//print '<form action="barcode.php" method="post">';
//print '<input type="hidden" name="action" value="settype">';

print '<tr class="liste_titre">';

print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("Example").'</td>';
print '<td align="center" width="60">'.$langs->trans("Default").'</td>';
print "</tr>\n";

clearstatcache();
$var=true;

//EAN13
      $var=!$var;
      print '<tr '.$bc[$var].'><td width="100">';
      print "EAN13";
      print "</td><td>\n";
      
      print "information";
      print '</td>';

      // Affiche example
      print '<td><img src="http://ns32126.ovh.net/barcode/barcode.php?code=123456789012&encoding=EAN&scale=1"></td>';
      
      print '<td align="center">';
      if ($barcode_encode_type_set == "EAN13")
	    {
	        print img_tick();
	    }
      else
	    {
          print '<a href="barcode.php?action=settype&amp;value=EAN13">'.$langs->trans("Default").'</a>';
	    }
	    print "</td></tr>\n";

//UPC
      $var=!$var;
      print '<tr '.$bc[$var].'><td width="100">';
      print "UPC";
      print "</td><td>\n";
      print "information";
      print '</td>';

      // Affiche example
      print '<td><img src="http://ns32126.ovh.net/barcode/barcode.php?code=123456789012&encoding=UPC&scale=1"></td>';
      
      print '<td align="center">';
      if ($barcode_encode_type_set == "UPC")
	    {
	        print img_tick();
	    }
      else
	    {
          print '<a href="barcode.php?action=settype&amp;value=UPC">'.$langs->trans("Default").'</a>';
	    }
	    print "</td></tr>\n";
	    
//ISBN
      $var=!$var;
      print '<tr '.$bc[$var].'><td width="100">';
      print "ISBN";
      print "</td><td>\n";
      print "information";
      print '</td>';

      // Affiche example
      print '<td><img src="http://ns32126.ovh.net/barcode/barcode.php?code=123456789&encoding=ISBN&scale=1"></td>';
      
      print '<td align="center">';
      if ($barcode_encode_type_set == "ISBN")
	    {
	        print img_tick();
	    }
      else
	    {
          print '<a href="barcode.php?action=settype&amp;value=ISBN">'.$langs->trans("Default").'</a>';
	    }
	    print "</td></tr>\n";
	    
//code 39
      $var=!$var;
      print '<tr '.$bc[$var].'><td width="100">';
      print "Code 39";
      print "</td><td>\n";
      print "information";
      print '</td>';

      // Affiche example
      print '<td><img src="http://ns32126.ovh.net/barcode/barcode.php?code=1234567890&encoding=39&scale=1"></td>';
      
      print '<td align="center">';
      if ($barcode_encode_type_set == "code39")
	    {
	        print img_tick();
	    }
      else
	    {
          print '<a href="barcode.php?action=settype&amp;value=code39">'.$langs->trans("Default").'</a>';
	    }
	    print "</td></tr>\n";
	    
	    
//code 128
      $var=!$var;
      print '<tr '.$bc[$var].'><td width="100">';
      print "Code 128";
      print "</td><td>\n";
      print "information";
      print '</td>';

      // Affiche example
      print '<td><img src="http://ns32126.ovh.net/barcode/barcode.php?code=ABCD1234567890&encoding=128&scale=1"></td>';
      
      print '<td align="center">';
      if ($barcode_encode_type_set == "code128")
	    {
	        print img_tick();
	    }
      else
	    {
          print '<a href="barcode.php?action=settype&amp;value=code128">'.$langs->trans("Default").'</a>';
	    }
	    print "</td></tr>\n";
	    
//I25
      $var=!$var;
      print '<tr '.$bc[$var].'><td width="100">';
      print "I25";
      print "</td><td>\n";
      print "information";
      print '</td>';

      // Affiche example
      print '<td><img src="http://ns32126.ovh.net/barcode/barcode.php?code=1234567890&encoding=I25&scale=1"></td>';
      
      print '<td align="center">';
      if ($barcode_encode_type_set == "I25")
	    {
	        print img_tick();
	    }
      else
	    {
          print '<a href="barcode.php?action=settype&amp;value=I25">'.$langs->trans("Default").'</a>';
	    }
	    print "</td></tr>\n";




/*
print '<td>'.$langs->trans('Name').'</td><td>'.$langs->trans('Description').'</td>';
print '<td><input type="submit" value="'.$langs->trans('Modify').'"></td>';
print "</tr>\n";
print '<tr '.$bc[false].'><td width="200"><input type="radio" name="compta_mode" value="RECETTES-DEPENSES"'.($compta_mode != 'CREANCES-DETTES' ? ' checked' : '').'> '.$langs->trans('OptionModeTrue').'</td>';
print '<td colspan="2">'.nl2br($langs->trans('OptionModeTrueDesc'))."</td></tr>\n";
print '<tr '.$bc[true].'><td width="200"><input type="radio" name="compta_mode" value="CREANCES-DETTES"'.($compta_mode == 'CREANCES-DETTES' ? ' checked' : '').'> '.$langs->trans('OptionModeVirtual').'</td>';
print '<td colspan="2">'.$langs->trans('OptionModeVirtualDesc')."</td></tr>\n";
print '</form>';
*/

print "</table>\n";



/*
 * FORMAT PAPIER
 */
/*
print_titre($langs->trans("PaperFormatModule"));

$def = array();

$sql = "SELECT nom FROM ".MAIN_DB_PREFIX."barcode_format_paper_model_pdf";
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

$dir = "../includes/modules/formatpaper/";

print "<table class=\"noborder\" width=\"100%\">\n";
print "<tr class=\"liste_titre\">\n";
print "  <td width=\"140\">".$langs->trans("Name")."</td>\n";
print "  <td>".$langs->trans("Description")."</td>\n";
print '  <td align="center" width="60">'.$langs->trans("Activated")."</td>\n";
print '  <td align="center" width="60">'.$langs->trans("Default")."</td>\n";
print "</tr>\n";

clearstatcache();

$handle=opendir($dir);

$var=true;
while (($file = readdir($handle))!==false)
{
  if (substr($file, strlen($file) -12) == '.modules.php' && substr($file,0,12) == 'pdf_paper_')
    {
      $name = substr($file, 12, strlen($file) - 24);
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
	  print '<a href="barcode.php?action=del&amp;value='.$name.'">'.$langs->trans("Disable").'</a>';
	}
      else
	{
	  print "&nbsp;";
	  print "</td>\n  <td>";
	  print '<a href="barcode.php?action=set&amp;value='.$name.'">'.$langs->trans("Activate").'</a>';
	}

      print "</td>\n  <td align=\"center\">";

      if ($barcode_addon_var_pdf == "$name")
	{
	  print img_tick();
	}
      else
	{
      print '<a href="barcode.php?action=setpdf&amp;value='.$name.'">'.$langs->trans("Activate").'</a>';
	}
      print '</td></tr>';
    }
}
closedir($handle);

print '</table>';

*/

print "<br>";

$db->close();

llxFooter('$Date$ - $Revision$');
?>
