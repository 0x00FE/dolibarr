<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
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

/**     \file       htdocs/product/popuprop.php
		\ingroup    propal, produit
		\brief      Liste des produits/services par popularit�
		\version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/product.class.php');

$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
$page = $_GET["page"];
if ($page < 0) $page = 0;
if (! $sortfield) $sortfield="c";
if (! $sortorder) $sortorder="DESC";

if ($page == -1) { $page = 0 ; }
$limit = $conf->liste_limit;
$offset = $limit * $page ;


$staticproduct=new Product($db);


llxHeader();

//On n'affiche le lien page suivante que s'il y a une page suivante ...
$sql = "select count(*) as c from ".MAIN_DB_PREFIX."product";
$result=$db->query($sql);
if ($result)
{
    $obj = $db->fetch_object($result);
    $num = $obj->c;
}

print_barre_liste("Liste des produits et services par popularit�", $page, "popuprop.php","","","","",$num);

print '<table class="noborder" width="100%">';

print "<tr class=\"liste_titre\">";
print_liste_field_titre($langs->trans("Ref"),"popuprop.php", "p.ref","","","",$sortfield);
print_liste_field_titre($langs->trans("Type"),"popuprop.php", "p.type","","","",$sortfield);
print_liste_field_titre($langs->trans("Label"),"popuprop.php", "p.label","","","",$sortfield);
print_liste_field_titre("Nb. de proposition","popuprop.php", "c","","",'align="right"',$sortfield);
print "</tr>\n";

$sql  = "SELECT p.rowid, p.label, p.ref, fk_product_type, count(*) as c";
$sql .= " FROM ".MAIN_DB_PREFIX."propaldet as pd, ".MAIN_DB_PREFIX."product as p";
$sql .= " WHERE p.rowid = pd.fk_product group by (p.rowid)";
$sql .= " ORDER BY $sortfield $sortorder ";
$sql .= $db->plimit( $limit ,$offset);

$result=$db->query($sql) ;
if ($result)
{
  $num = $db->num_rows($result);
  $i = 0;

  $var=True;
  while ($i < $num)
    {
      $objp = $db->fetch_object($result);
      $var=!$var;
      print "<tr $bc[$var]>";
      print '<td><a href="'.DOL_URL_ROOT.'/product/stats/fiche.php?id='.$objp->rowid.'">';
	  if ($objp->fk_product_type) print img_object($langs->trans("ShowService"),"service");
	  else print img_object($langs->trans("ShowProduct"),"product");
      print " ";
      print $objp->ref.'</a></td>';
      print '<td>'.$staticproduct->typeprodser[$objp->fk_product_type].'</td>';
      print '<td>'.$objp->label.'</td>';
      print '<td align="right">'.$objp->c.'</td>';
      print "</tr>\n";
      $i++;
    }
  $db->free();
}
print "</table>";

$db->close();

llxFooter('$Date$ - $Revision$');
?>
