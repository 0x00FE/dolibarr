<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 */

/**
    	\file htdocs/admin/perms.php
		\brief      Page d'administration/configuration des permissions par defaut
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("admin");
$langs->load("users");

if (!$user->admin)
  accessforbidden();


if ($_GET["action"] == 'add')
{
  $sql = "UPDATE ".MAIN_DB_PREFIX."rights_def SET bydefault=1 WHERE id =".$_GET["pid"];
  $db->query($sql);
}

if ($_GET["action"] == 'remove')
{
  $sql = "UPDATE ".MAIN_DB_PREFIX."rights_def SET bydefault=0 WHERE id =".$_GET["pid"];
  $db->query($sql);
}



llxHeader();

print_titre($langs->trans("DefaultRights"));

print "<br>".$langs->trans("DefaultRightsDesc")."<br><br>\n";


print '<table class="noborder" width="100%">';


// Charge les modules soumis a permissions
$db->begin();

$dir = DOL_DOCUMENT_ROOT . "/includes/modules/";
$handle=opendir($dir);
$modules = array();
while (($file = readdir($handle))!==false)
{
    if (is_readable($dir.$file) && substr($file, 0, 3) == 'mod' && substr($file, strlen($file) - 10) == '.class.php')
    {
        $modName = substr($file, 0, strlen($file) - 10);

        if ($modName)
        {
            include_once("../includes/modules/$file");
            $objMod = new $modName($db);
            if ($objMod->rights_class) {

                $ret=$objMod->insert_permissions();

                $modules[$objMod->rights_class]=$objMod;
                //print "modules[".$objMod->rights_class."]=$objMod;";
            }
        }
    }
}

$db->commit();


// Affiche lignes des permissions
$sql ="SELECT r.id, r.libelle, r.module, r.bydefault";
$sql.=" FROM ".MAIN_DB_PREFIX."rights_def as r";
$sql.=" WHERE r.libelle NOT LIKE 'tou%'";    // On ignore droits "tous"
$sql.=" ORDER BY r.id, r.module";

$result = $db->query($sql);
if ($result) 
{
  $num = $db->num_rows();
  $i = 0;
  $var=True;
  $old = "";
  while ($i < $num)
    {
      $obj = $db->fetch_object($result);
      $var=!$var;

      if ($old <> $obj->module)
	{
       // Rupture d�tect�e, on r�cup�re objMod
        $objMod=$modules[$obj->module];
        $picto=($objMod->picto?$objMod->picto:'generic');

	  print '<tr class="liste_titre">';
	  print '<td>'.$langs->trans("Module").'</td>';
	  print '<td>'.$langs->trans("Permission").'</td>';
	  print '<td align="center">'.$langs->trans("Default").'</td>';
	  print '<td align="center">&nbsp;</td>';
	  print "</tr>\n";
	  $old = $obj->module;
	}

      print '<tr '. $bc[$var].'>';

      print '<td>'.img_object('',$picto).' '.$objMod->getName();

      $perm_libelle=(($langs->trans("Permission".$obj->id)!=("Permission".$obj->id))?$langs->trans("Permission".$obj->id):$obj->libelle);
      print '<td>'.$perm_libelle. '</td>';
     
      print '<td align="center">';
      if ($obj->bydefault == 1)
	{

	  print img_tick();
	  print '</td><td>';
	  print '<a href="perms.php?pid='.$obj->id.'&amp;action=remove">'.img_edit_remove().'</a>';
	}
      else
	{
	  print '&nbsp;';
	  print '</td><td>';
	  print '<a href="perms.php?pid='.$obj->id.'&amp;action=add">'.img_edit_add().'</a>';
	}

      print '</td></tr>';
      $i++;
    }
}

print '</table>';
print '<br>';

$db->close();

llxFooter();
?>
