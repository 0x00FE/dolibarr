<?PHP
/* Copyright (C) 2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
require("./pre.inc.php3");
require("../../tva.class.php3");
require("../../chargesociales.class.php3");
/*
 *
 */
llxHeader();
?>
<style type="text/css">
td.border { border: 1px solid #000000}
</style>


<?PHP
$db = new Db();

print_titre("R�sultat $year");

print "<TABLE border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">";
print '<TR class="liste_titre">';
print '<td width="10%">&nbsp;</td><TD>El�ment</TD>';
print "<TD align=\"center\">Montant</TD><td align=\"right\">Solde</td>";
print "</TR>\n";

$sql = "SELECT s.nom,sum(f.amount) as amount";
$sql .= " FROM societe as s,llx_facture as f WHERE f.fk_soc = s.idp";
  
if ($year > 0) {
  $sql .= " AND date_format(f.datef, '%Y') = $year";
}
  
$sql .= " GROUP BY s.nom ASC";
  
print '<tr><td colspan="4">Factures</td></tr>';

$result = $db->query($sql);
if ($result) {
  $num = $db->num_rows();
    
  $i = 0;
  
  if ($num > 0) {
    $var=True;
    while ($i < $num) {
      $objp = $db->fetch_object( $i);
      $var=!$var;
            
      print "<TR $bc[$var]><td>&nbsp</td>";
      print "<td>Facture <a href=\"/compta/facture.php3?facid=$objp->facid\">$objp->facnumber</a> $objp->nom</TD>\n";
      
      print "<TD align=\"right\">".price($objp->amount)."</TD>\n";
      
      $total = $total + $objp->amount;
      print "<TD align=\"right\">".price($total)."</TD>\n";
      
      print "</TR>\n";
      $i++;
    }
  }
  $db->free();
} else {
  print $db->error();
}
print '<tr><td colspan="3" align="right">'.price($total).'</td></tr>';
/*
 * Charges sociales
 *
 */
$subtotal = 0;
print '<tr><td colspan="4">Prestations d�ductibles</td></tr>';

$sql = "SELECT c.libelle as nom, sum(s.amount) as amount";
$sql .= " FROM c_chargesociales as c, llx_chargesociales as s";
$sql .= " WHERE s.fk_type = c.id AND c.deductible=1";
if ($year > 0) {
  $sql .= " AND date_format(s.periode, '%Y') = $year";
}
$sql .= " GROUP BY c.libelle DESC";

if ( $db->query($sql) ) {
  $num = $db->num_rows();
  $i = 0;

  while ($i < $num) {
    $obj = $db->fetch_object( $i);

    $total = $total - $obj->amount;
    $subtotal = $subtotal + $obj->amount;

    $var = !$var;
    print "<tr $bc[$var]><td>&nbsp</td>";
    print '<td>'.$obj->nom.'</td>';
    print '<td align="right">'.price($obj->amount).'</td>';
    print "<TD align=\"right\">".price($total)."</TD>\n";
    print '</tr>';
    $i++;
  }
} else {
  print $db->error();
}
print '<tr><td colspan="3" align="right">'.price($subtotal).'</td></tr>';

print '<tr><td align="right" colspan="2">R�sultat</td><td class="border" align="right">'.price($total).'</td></tr>';
/*
 * Charges sociales non d�ductibles
 *
 */
$subtotal = 0;
print '<tr><td colspan="4">Prestations NON d�ductibles</td></tr>';

$sql = "SELECT c.libelle as nom, sum(s.amount) as amount";
$sql .= " FROM c_chargesociales as c, llx_chargesociales as s";
$sql .= " WHERE s.fk_type = c.id AND c.deductible=0";
if ($year > 0) {
  $sql .= " AND date_format(s.periode, '%Y') = $year";
}
$sql .= " GROUP BY c.libelle DESC";

if ( $db->query($sql) ) {
  $num = $db->num_rows();
  $i = 0;

  while ($i < $num) {
    $obj = $db->fetch_object( $i);

    $total = $total - $obj->amount;
    $subtotal = $subtotal + $obj->amount;

    $var = !$var;
    print "<tr $bc[$var]><td>&nbsp</td>";
    print '<td>'.$obj->nom.'</td>';
    print '<td align="right">'.price($obj->amount).'</td>';
    print "<TD align=\"right\">".price($total)."</TD>\n";
    print '</tr>';
    $i++;
  }
} else {
  print $db->error();
}
print '<tr><td colspan="3" align="right">'.price($subtotal).'</td></tr>';

print '<tr><td align="right" colspan="2">R�sultat</td><td class="border" align="right">'.price($total).'</td></tr>';


print "</TABLE>";



$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
