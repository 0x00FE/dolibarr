<?PHP
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 */
require("./pre.inc.php");

if (!$user->admin)
  accessforbidden();


llxHeader();

print_titre("Liste de tous les param�tres de configuration Dolibarr");

print '<br>';
print '<table class="noborder" cellpadding="3" cellspacing="0">';
print '<tr class="liste_titre">';
print '<td>Param�tre</td>';
print '<td>Valeur</td>';
print "</tr>\n";

$sql = "SELECT rowid, name, value, type, note FROM ".MAIN_DB_PREFIX."const ORDER BY name ASC";
$result = $db->query($sql);
if ($result) 
{
  $num = $db->num_rows();
  $i = 0;
  $var=True;

  while ($i < $num)
    {
      $obj = $db->fetch_object( $i);
      $var=!$var;

      print "<tr $bc[$var]><td>$obj->name</td>\n";

      print '<td>';
      print $obj->value;
      print "</td></tr>\n";

      $i++;
    }
}
$var=!$var;


print '</table>';

$db->close();

llxFooter();
?>
