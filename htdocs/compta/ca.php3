<?PHP
/* Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 *
 * $Id$
 * $Source$
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
 */
require("./pre.inc.php3");

function pt ($db, $sql, $date) {
  $bc[0]="class=\"pair\"";
  $bc[1]="class=\"impair\"";

  $result = $db->query($sql);
  if ($result) {
    $num = $db->num_rows();
    $i = 0; $total = 0 ;
    print "<p><TABLE border=\"1\" width=\"100%\" cellspacing=\"0\" cellpadding=\"3\">";
    print "<TR class=\"liste_titre\">";
    print "<TD width=\"60%\">$date</TD>";
    print "<TD align=\"right\">Montant</TD>";
    print "<td>&nbsp;</td>\n";
    print "</TR>\n";
    $var=True;
    while ($i < $num) {
      $obj = $db->fetch_object( $i);
      $var=!$var;
      print "<TR $bc[$var]>";
      print "<TD>$obj->dm</TD>\n";
      print "<TD align=\"right\">".price($obj->amount)."</TD><td>&nbsp;</td>\n";
      print "</TR>\n";
      
      $total = $total + $obj->amount;
      
      $i++;
    }
    print '<tr><td align="right">Total :</td><td align="right"><b>'.price($total).'</b></td><td>euros&nbsp;HT</td></tr>';
    
    print "</TABLE>";
    $db->free();
  }
}
/*
 *
 */

llxHeader();


$db = new Db();
if ($sortfield == "") {
  $sortfield="lower(p.label)";
}
if ($sortorder == "") {
  $sortorder="ASC";
}


print_titre("Chiffres d'affaires en euros HT");

print "<table width=\"100%\">";
print "<tr><td valign=\"top\">";

$sql = "SELECT sum(f.amount) as amount , date_format(f.datef,'%Y-%m') as dm";
$sql .= " FROM llx_facture as f WHERE f.paye = 1";
$sql .= " GROUP BY dm DESC";

pt($db, $sql,"Par mois");

print "</td><td valign=\"top\">";

$sql = "SELECT sum(f.amount) as amount, month(f.datef) as dm";
$sql .= " FROM llx_facture as f WHERE f.paye = 1";
$sql .= " GROUP BY dm";

pt($db, $sql,"Mois cumul�s");


print "<P>";

$sql = "SELECT sum(f.amount) as amount, year(f.datef) as dm";
$sql .= " FROM llx_facture as f WHERE f.paye = 1";
$sql .= " GROUP BY dm DESC";

pt($db, $sql,"Ann�e");

print "</td></tr></table>";

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
