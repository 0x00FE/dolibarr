<?PHP
/* Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

llxHeader();

print_titre("Gestion des adhesions a l'association");

print '<p><TABLE border="0" cellspacing="0" cellpadding="4">';
print '<tr class="liste_titre"><td colspan=2>Les menus ci-contre correspondent a:</td></tr>';
print '<tr><td>-Inscription :</td><td> Formulaires d\'inscription pour les non-adherents</td></tr>';
print '<tr><td>-Edition de sa fiche :</td><td> Permet d\'editer sa fiche d\'adherent</td></tr>';
print '<tr><td>-Liste des adherents :</td><td> Permet de voir la liste des adherents (reserve aux adherents)</td></tr>';
/*
print '<TR class="liste_titre">';
print "<td>Type</td>";
print "<td>Nb</td>";
print "</TR>\n";

$var=True;


$sql = "SELECT count(*) as somme , t.libelle FROM ".MAIN_DB_PREFIX."adherent as d, ".MAIN_DB_PREFIX."adherent_type as t";
$sql .= " WHERE d.fk_adherent_type = t.rowid  AND d.statut = 1 GROUP BY t.libelle";

$result = $db->query($sql);

if ($result) 
{
  $num = $db->num_rows();
  $i = 0;
  while ($i < $num)
    {
      $objp = $db->fetch_object( $i);

      $var=!$var;
      print "<TR $bc[$var]>";
      print '<TD><a href="liste.php">'.$objp->libelle.'</a></TD>';
      print '<TD align="right">'.$objp->somme.'</TD>';

      print "</tr>";

      $i++;
    }
  $db->free();

}
print "</table>";

print '<form action="liste.php" method="post" name="action" value="search">';
print '<p><TABLE border="0" cellspacing="0" cellpadding="4">';
print '<TR class="liste_titre">';
print "<td>Rechercher un adh�rent</td>";
print "</TR>\n";

print "<TR $bc[$var]>";
print '<td>';

print 'Nom/Pr�nom <input type="text" name="search" class="flat" size="20">';

print '&nbsp; <input class="flat" type="submit" value="Chercher">';
print '</td></tr>';
print "</table></form>";
*/


print '</table>';

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
