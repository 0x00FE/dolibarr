<?PHP
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
require("./pre.inc.php");

llxHeader('','Compta - Export');
/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}


/*
 *
 *
 */

$page = $_GET["page"];
$sortorder = $_GET["sortorder"];
$sortfield = $_GET["sortfield"];

$offset = $conf->liste_limit * $page ;
if ($sortorder == "") $sortorder="DESC";
if ($sortfield == "") $sortfield="ec.date_export";

/*
 * Mode Liste
 *
 *
 *
 */

$sql = "SELECT ec.date_export, ec.ref";
$sql .= " FROM ".MAIN_DB_PREFIX."export_compta as ec";

$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit($conf->liste_limit+1, $offset);

$result = $db->query($sql);
if ($result)
{
  $num = $db->num_rows($result);
  $i = 0;
  
  print_barre_liste($langs->trans("Exports"), $page, "liste.php", $urladd, $sortfield, $sortorder, '', $num);

  print"\n<!-- debut table -->\n";
  print '<table class="noborder" width="100%">';
  print '<tr class="liste_titre">';

  print_liste_field_titre($langs->trans("Ref"),"liste.php","ec.ref");

  print "</tr>\n";

  $var=True;

  $dir = DOL_DATA_ROOT."/compta/export/";

  while ($i < min($num,$conf->liste_limit))
    {
      $obj = $db->fetch_object($result);	
      $var=!$var;

      print "<tr $bc[$var]>";

      print '<td>'.stripslashes($obj->ref).'</td>';

      print "</tr>\n";
      $i++;
    }
  print "</table>";
  $db->free($result);
}
else 
{
  dolibarr_print_error($db);
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
