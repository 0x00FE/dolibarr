<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 *
 */

/**
        \file       htdocs/product/stock/mouvement.php
        \ingroup    stock
        \brief      Page liste des mouvements de stocks
        \version    $Revision$
*/

require("./pre.inc.php");
$user->getrights('produit');

$langs->load("products");

if (!$user->rights->produit->lire) accessforbidden();


$page = $_GET["page"];
$sortfield = $_GET["sortfield"];
$sortorder = $_GET["sortorder"];

if ($page < 0) { 
  $page = 0 ; }

$limit = $conf->liste_limit;
$offset = $limit * $page ;
  
if (! $sortfield) $sortfield="m.datem";
if (! $sortorder) $sortorder="DESC";
  
$sql = "SELECT p.rowid, p.label as produit, s.label as stock, m.value, ".$db->pdate("m.datem")." as datem, s.rowid as entrepot_id";
$sql .= " FROM llx_product as p, llx_entrepot as s, llx_stock_mouvement as m";
$sql .= " WHERE m.fk_product = p.rowid AND m.fk_entrepot = s.rowid";

$sql .= " ORDER BY $sortfield $sortorder ";
$sql .= $db->plimit($limit + 1 ,$offset);
$result = $db->query($sql) ;

if ($result)
{
  $num = $db->num_rows($result);

  $i = 0;
  
  $texte = $langs->trans("ListOfStockMovements");
  llxHeader("","",$texte);
  
  print_barre_liste($texte, $page, "mouvement.php", "&sref=$sref&snom=$snom", $sortfield, $sortorder,'',$num);

  print '<table class="noborder" width="100%">';
  print "<tr class=\"liste_titre\">";
  print_liste_field_titre($langs->trans("Ref"),"mouvement.php", "p.ref","");
  print "<td align=\"center\">Unit�s</td>";
  print_liste_field_titre($langs->trans("Date"),"mouvement.php", "m.datem","");
  print_liste_field_titre($langs->trans("Warehouse"),"mouvement.php", "s.label","");
  print "</tr>\n";
    
  $var=True;
  while ($i < min($num,$limit))
    {
      $objp = $db->fetch_object($result);
      $var=!$var;
      print "<tr $bc[$var]>";
      print "<td><a href=\"../fiche.php?id=$objp->rowid\">$objp->produit</a></td>\n";
      print '<td align="center">'.$objp->value.'</td>';
      print '<td>'.strftime("%d %b %Y",$objp->datem).'</td>';
      print "<td><a href=\"fiche.php?id=$objp->entrepot_id\">$objp->stock</a></td>\n";
      print "</tr>\n";
      $i++;
    }
  $db->free($result);

  print "</table>";

}
else
{
  dolibarr_print_error($db);
}


$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
