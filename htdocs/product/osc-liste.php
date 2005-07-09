<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005 �ric Seigne <eric.seigne@ryxeo.com>
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

if ($_GET['sortfield'] == "") {
  $sortfield="lower(p.label),p.price";
}
if ($_GET['sortorder'] == "") {
  $sortorder="ASC";
}


if ($_GET['page'] == -1) {
  $page = 0 ;
}
else {
  $page = $_GET['page'];
}
$limit = $conf->liste_limit;
$offset = $limit * $page ;


print_barre_liste("Liste des produits oscommerce", $page, "osc-liste.php");

$sql = "SELECT p.products_id, p.products_model, p.products_quantity, p.products_status, d.products_name, m.manufacturers_name, m.manufacturers_id";
$sql .= " FROM ".OSC_DB_NAME.".products as p, ".OSC_DB_NAME.".products_description as d, ".OSC_DB_NAME.".manufacturers as m";
$sql .= " WHERE p.products_id = d.products_id AND d.language_id =" . OSC_LANGUAGE_ID;
$sql .= " AND p.manufacturers_id=m.manufacturers_id";
if ($_GET['reqstock']=='epuise')
{
  $sql .= " AND p.products_quantity <= 0";
}  

//$sql .= " ORDER BY $sortfield $sortorder ";
$sql .= $db->plimit( $limit ,$offset);

print "<p><TABLE border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">";
print '<TR class="liste_titre">';
print "<td>id</td>";
print "<td>Ref</td>";
print "<td>Titre</td>";
print "<td>Groupe</td>";
print '<td align="center">Stock</td>';
print '<TD align="center">Status</TD>';
  print "</TR>\n";
 
if ( $db->query($sql) ) {
  $num = $db->num_rows();
  $i = 0;

  $var=True;
  while ($i < $num) {
    $objp = $db->fetch_object( $i);
    $var=!$var;
    print "<TR $bc[$var]>";
    print "<TD>$objp->products_id</TD>\n";
    print "<TD>$objp->products_model</TD>\n";
    print "<TD>$objp->products_name</TD>\n";
    print "<TD>$objp->manufacturers_name</TD>\n";
    print '<TD align="center">'.$objp->products_quantity."</TD>\n";
    print '<TD align="center">'.$objp->products_status."</TD>\n";
    print "</TR>\n";
    $i++;
  }
  $db->free();
}

print "</TABLE>";


$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
