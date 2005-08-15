<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
        \file       htdocs/fourn/product/liste.php
        \ingroup    produit
        \brief      Page liste des produits ou services
        \version    $Revision$
*/

require("./pre.inc.php");

$langs->load("products");

$user->getrights('produit');

if (!$user->rights->produit->lire) accessforbidden();

$sref=isset($_GET["sref"])?$_GET["sref"]:$_POST["sref"];
$snom=isset($_GET["snom"])?$_GET["snom"]:$_POST["snom"];

$type=isset($_GET["type"])?$_GET["type"]:$_POST["type"];

$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
$page = $_GET["page"];
if ($page < 0) { 
  $page = 0 ; }

$limit = $conf->liste_limit;
$offset = $limit * $page ;
  
if (! $sortfield) $sortfield="p.ref";
if (! $sortorder) $sortorder="DESC";

if ($_POST["button_removefilter"] == $langs->trans("RemoveFilter")) {
    $sref="";
    $snom="";
}

if ($_GET["fourn_id"] > 0)
{
    $fourn_id = $_GET["fourn_id"];
}

if (isset($_REQUEST['catid']))
{
    $catid = $_REQUEST['catid'];
}

/*
 * Mode Liste
 *
 */

$title=$langs->trans("ProductsAndServices");

$sql = "SELECT p.rowid, p.label, p.ref, p.fk_product_type";
$sql .= ", pf.fk_soc";
$sql .= ", min(ppf.price) as price";
$sql .= ", s.nom";
$sql .= " FROM ".MAIN_DB_PREFIX."product as p";

if ($catid)
{
  $sql .= ", ".MAIN_DB_PREFIX."categorie_product as cp";
}

$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur as pf ON p.rowid = pf.fk_product";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.idp = pf.fk_soc";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_fournisseur_price as ppf ON ppf.fk_soc = pf.fk_soc AND ppf.fk_product = p.rowid AND ppf.quantity = 1";

if ($_POST["mode"] == 'search')
{
  $sql .= " WHERE p.ref like '%".$_POST["sall"]."%'";
  $sql .= " OR p.label like '%".$_POST["sall"]."%'";
}
else
{
  $sql .= " WHERE 1=1";
  if (isset($_GET["type"]) || isset($_POST["type"]))
    {
      $sql .= " AND p.fk_product_type = ".(isset($_GET["type"])?$_GET["type"]:$_POST["type"]);
    }
  if ($sref)
    {
      $sql .= " AND p.ref like '%".$sref."%'";
    }
  if ($snom)
    {
      $sql .= " AND p.label like '%".$snom."%'";
    }
  if($catid)
    {
      $sql .= " AND cp.fk_product = p.rowid";
      $sql .= " AND cp.fk_categorie = ".$catid;
    }

}

if ($fourn_id > 0)
{
  $sql .= " AND p.rowid = pf.fk_product AND pf.fk_soc = $fourn_id";
}
$sql .= " GROUP BY p.rowid";
$sql .= " ORDER BY $sortfield $sortorder ";
$sql .= $db->plimit($limit + 1 ,$offset);

$resql = $db->query($sql) ;
if ($resql)
{
  $num = $db->num_rows($resql);
  
  $i = 0;
  
  if ($num == 1 && (isset($_POST["sall"]) or $snom or $sref))
    {
      $objp = $db->fetch_object($resql);
      Header("Location: fiche.php?id=$objp->rowid");
    }
    
  $texte = $langs->trans("List");
  


  llxHeader("","",$texte);

  if ($sref || $snom || $_POST["sall"] || $_POST["search"])
    {
      print_barre_liste($texte, $page, "liste.php", "&sref=".$sref."&snom=".$snom, $sortfield, $sortorder,'',$num);
    }
  else
    {
      print_barre_liste($texte, $page, "liste.php", "&sref=$sref&snom=$snom&fourn_id=$fourn_id".(isset($type)?"&amp;type=$type":""), $sortfield, $sortorder,'',$num);
    }
  

    if (isset($catid))
    {
        print "<div id='ways'>";
        $c = new Categorie ($db, $catid);
        $ways = $c->print_all_ways(' &gt; ','fourn/product/liste.php');
        print " &gt; ".$ways[0]."<br />\n";
        print "</div><br />";
    }


  print '<table class="liste" width="100%">';

  // Lignes des titres
  print "<tr class=\"liste_titre\">";
  print_liste_field_titre($langs->trans("Ref"),"liste.php", "p.ref","&amp;envente=$envente".(isset($type)?"&amp;type=$type":"")."&fourn_id=$fourn_id&amp;snom=$snom&amp;sref=$sref","","",$sortfield);
  print_liste_field_titre($langs->trans("Label"),"liste.php", "p.label","&envente=$envente&".(isset($type)?"&amp;type=$type":"")."&fourn_id=$fourn_id&amp;snom=$snom&amp;sref=$sref","","",$sortfield);
  print_liste_field_titre($langs->trans("Supplier"),"liste.php", "pf.fk_soc","","","",$sortfield);
  print_liste_field_titre($langs->trans("BuyingPrice"),"liste.php", "ppf.price","&envente=$envente&".(isset($type)?"&amp;type=$type":"")."&fourn_id=$fourn_id&amp;snom=$snom&amp;sref=$sref","",'align="right"',$sortfield);
  print "</tr>\n";
  
  // Lignes des champs de filtre
  print '<form action="liste.php" method="post">';
  print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
  print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
  print '<input type="hidden" name="type" value="'.$type.'">';
  print '<tr class="liste_titre">';
  print '<td class="liste_titre">';
  print '<input class="flat" type="text" name="sref" value="'.$sref.'">';
  print '</td>';
  print '<td class="liste_titre" valign="right">';
  print '<input class="flat" type="text" name="snom" value="'.$snom.'">';
  print '</td>';
  print '<td class="liste_titre" colspan="2" align="right">';
  print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" alt="'.$langs->trans("Search").'">';
  print '&nbsp; <input type="image" class="liste_titre" name="button_removefilter" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/searchclear.png" alt="'.$langs->trans("RemoveFilter").'">';
  print '</td>';
  print '</tr>';
  print '</form>';
  
  $oldid = ''; 
  $var=True;
  while ($i < min($num,$limit))
    {
      $objp = $db->fetch_object( $i);
      $var=!$var;
      print "<tr $bc[$var]>";

      if ($oldid <> $objp->rowid)
	{
	  $oldid = $objp->rowid;
	  print "<td><a href=\"../../product/fiche.php?id=$objp->rowid\">";
	  if ($objp->fk_product_type) print img_object($langs->trans("ShowService"),"service");
	  else print img_object($langs->trans("ShowProduct"),"product");
	  print "</a> ";
	  print "<a href=\"fiche.php?id=$objp->rowid\">$objp->ref</a></td>\n";
	  print "<td>$objp->label</td>\n";
	}
      else
	{
	  print '<td colspan="2">&nbsp;</td>';
	}

      print '<td>'.$objp->nom.'</td>';
      print '<td align="right">'.price($objp->price).'</td>';
      print "</tr>\n";
      $i++;
    }
  $db->free($resql);

  print "</table>";


}
else
{
  dolibarr_print_error($db);
}


$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
