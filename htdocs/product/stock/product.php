<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 */


/*!
  \file       htdocs/product/stock/product.php
  \ingroup    product
  \brief      Page de la fiche produit
  \version    $Revision$
*/

require("./pre.inc.php");

$langs->load("products");

$user->getrights('produit');
$mesg = '';

if (!$user->rights->produit->lire)
{
  accessforbidden();
}


$types[0] = $langs->trans("Product");
$types[1] = $langs->trans("Service");


llxHeader("","",$langs->trans("ProductCard"));

if ($_POST["action"] == "create_stock")
{
  $product = new Product($db);
  $product->id = $_GET["id"];
  $product->create_stock($_POST["id_entrepot"], $_POST["nbpiece"]);
}

if ($_POST["action"] == "correct_stock" && $_POST["cancel"] <> $langs->trans("Cancel"))
{
  if (is_numeric($_POST["nbpiece"]))
    {

      $product = new Product($db);
      $product->id = $_GET["id"];
      $product->correct_stock($user, 
			      $_POST["id_entrepot"], 
			      $_POST["nbpiece"],
			      $_POST["mouvement"]);
    }
}

if ($_POST["action"] == "transfert_stock" && $_POST["cancel"] <> $langs->trans("Cancel"))
{
  if ($_POST["id_entrepot_source"] <> $_POST["id_entrepot_destination"])
    {
      if (is_numeric($_POST["nbpiece"]))
	{
	  
	  $product = new Product($db);
	  $product->id = $_GET["id"];

	  $product->correct_stock($user, 
				  $_POST["id_entrepot_source"], 
				  $_POST["nbpiece"],
				  1);

	  $product->correct_stock($user, 
				  $_POST["id_entrepot_destination"], 
				  $_POST["nbpiece"],
				  0);
	}
    }
}

/*
 * Fiche stock
 *
 */
if ($_GET["id"])
{

  $product = new Product($db);
  
  if ( $product->fetch($_GET["id"]))
    {
        $h=0;
        
        $head[$h][0] = DOL_URL_ROOT."/product/fiche.php?id=".$product->id;
        $head[$h][1] = $langs->trans("Card");
        $h++;
        
        $head[$h][0] = DOL_URL_ROOT."/product/price.php?id=".$product->id;
        $head[$h][1] = $langs->trans("Price");
        $h++;
        
        if($product->type == 0)
        {
            $head[$h][0] = DOL_URL_ROOT."/product/stock/product.php?id=".$product->id;
            $head[$h][1] = $langs->trans("Stock");
            $hselected=$h;
            $h++;
	    
	    $head[$h][0] = DOL_URL_ROOT."/product/fournisseurs.php?id=".$product->id;
	    $head[$h][1] = 'Fournisseurs';
	    $h++;
        }
	if ($conf->fournisseur->enabled) {
	  $head[$h][0] = DOL_URL_ROOT."/product/fournisseurs.php?id=".$product->id;
	  $head[$h][1] = $langs->trans("Suppliers");
	  $h++;
	}
	
        $head[$h][0] = DOL_URL_ROOT."/product/stats/fiche.php?id=".$product->id;
        $head[$h][1] = $langs->trans("Statistics");
        $h++;
      
        dolibarr_fiche_head($head, $hselected, $langs->trans("CardProduct".$product->type).' : '.$product->ref);
      
      print($mesg);    	      
      
      print '<table class="border" width="100%">';
      print "<tr>";
      print '<td width="20%">'.$langs->trans("Ref").'</td><td width="40%">'.$product->ref.'</td>';
      print '<td width="40%">';
      if ($product->envente)
	{
	  print $langs->trans("OnSell");
	}
      else
	{
	  print $langs->trans("NotOnSell");
	}
      print '</td></tr>';
      print '<tr><td>'.$langs->trans("Label").'</td><td>'.$product->libelle.'</td>';
      print '<td><a href="'.DOL_URL_ROOT.'/product/stats/fiche.php?id='.$product->id.'">'.$langs->trans("Statistics").'</a></td></tr>';
      print '<tr><td>'.$langs->trans("SellingPrice").'</td><td>'.price($product->price).'</td>';
      print '<td valign="top" rowspan="2">';
      print $langs->trans("Suppliers").' [<a href="../fiche.php?id='.$product->id.'&amp;action=ajout_fourn">'.$langs->trans("Add").'</a>]';
      
      $sql = "SELECT s.nom, s.idp";
      $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."product_fournisseur as pf";
      $sql .=" WHERE pf.fk_soc = s.idp AND pf.fk_product =$product->id";
      $sql .= " ORDER BY lower(s.nom)";
      
      if ( $db->query($sql) )
	{
	  $num = $db->num_rows();
	  $i = 0;
	  print '<table class="noborder" width="100%">';
	  $var=True;      
	  while ($i < $num)
	    {
	      $objp = $db->fetch_object( $i);	  
	      $var=!$var;
	      print "<tr $bc[$var]>";
	      print '<td><a href="../fourn/fiche.php?socid='.$objp->idp.'">'.$objp->nom.'</a></td></tr>';
	      $i++;
	    }
	  print '</table>';
	  $db->free();
	}
      
      print '</td></tr>';	  
      print '<tr><td>Stock seuil</td><td>'.$product->seuil_stock_alerte.'</td></tr>';	  
      print "</table>";
      
      /* 
       * Contenu des stocks
       *
       */
      print '<br><table class="border" width="100%">';
      print '<tr class="liste_titre"><td width="40%">'.$langs->trans("Warehouse").'</td><td width="60%">Valeur du stock</td></tr>';
      $sql = "SELECT e.rowid, e.label, ps.reel FROM ".MAIN_DB_PREFIX."entrepot as e, ".MAIN_DB_PREFIX."product_stock as ps";
      $sql .= " WHERE ps.fk_entrepot = e.rowid AND ps.fk_product = ".$product->id;
      $sql .= " ORDER BY lower(e.label)";
      
      if ($db->query($sql))
	{
	  $num = $db->num_rows();
	  $i = 0; $total = 0;  
	  while ($i < $num)
	    {
	      $obj = $db->fetch_object( $i);
	      print '<tr><td width="40%">'.$obj->label.'</td><td>'.$obj->reel.'</td></tr>'; ;
	      $total = $total + $obj->reel;
	      $i++;
	    }
	}      
      print '<tr><td align="right">'.$langs->trans("Total").':</td><td>'.$total."</td></tr></table>";
      print '<br>';

    }
  print '</div>';

  /*
   * Correction du stock
   *
   */
  if ($_GET["action"] == "correction")
    {
      print_titre ("Correction du stock");
      print "<form action=\"product.php?id=$product->id\" method=\"post\">\n";
      print '<input type="hidden" name="action" value="correct_stock">';
      print '<table class="border" width="100%"><tr>';
      print '<td width="20%">'.$langs->trans("Warehouse").'</td><td width="20%"><select name="id_entrepot">';
      
      $sql  = "SELECT e.rowid, e.label FROM ".MAIN_DB_PREFIX."entrepot as e";
      $sql .= " WHERE statut = 1";    
      $sql .= " ORDER BY lower(e.label)";
      
      if ($db->query($sql))
	{
	  $num = $db->num_rows();
	  $i = 0;		  		  
	  while ($i < $num)
	    {
	      $obj = $db->fetch_object( $i);
	      print '<option value="'.$obj->rowid.'">'.$obj->label ;
	      $i++;
	    }
	}
      print '</select></td>';
      print '<td width="20%"><select name="mouvement">';
      print '<option value="0">'.$langs->trans("Add").'</option>';
      print '<option value="1">'.$langs->trans("Delete").'</option>';
      print '</select></td>';
      print '<td width="20%">Nb de pi�ce</td><td width="20%"><input name="nbpiece" size="10" value=""></td></tr>';
      print '<tr><td colspan="5" align="center"><input type="submit" value="'.$langs->trans('Save').'">&nbsp;';
      print '<input type="submit" name="cancel" value="'.$langs->trans("Cancel").'"></td></tr>';
      print '</table>';
      print '</form>';

    }
  /*
   * Transfert de pi�ces 
   *
   */
  if ($_GET["action"] == "transfert")
    {
      print_titre ("Transfert de stock");
      print "<form action=\"product.php?id=$product->id\" method=\"post\">\n";
      print '<input type="hidden" name="action" value="transfert_stock">';
      print '<table class="border" width="100%"><tr>';
      print '<td width="20%">'.$langs->trans("Source").'</td><td width="20%"><select name="id_entrepot_source">';
      
      $sql  = "SELECT e.rowid, e.label FROM ".MAIN_DB_PREFIX."entrepot as e";
      $sql .= " WHERE statut = 1";    
      $sql .= " ORDER BY lower(e.label)";
      
      if ($db->query($sql))
	{
	  $num = $db->num_rows();
	  $i = 0;		  		  
	  while ($i < $num)
	    {
	      $obj = $db->fetch_object( $i);
	      print '<option value="'.$obj->rowid.'">'.$obj->label ;
	      $i++;
	    }
	}
      print '</select></td>';

      print '<td width="20%">'.$langs->trans("Target").'</td><td width="20%"><select name="id_entrepot_destination">';
      
      $sql  = "SELECT e.rowid, e.label FROM ".MAIN_DB_PREFIX."entrepot as e";
      $sql .= " WHERE statut = 1";    
      $sql .= " ORDER BY lower(e.label)";
      
      if ($db->query($sql))
	{
	  $num = $db->num_rows();
	  $i = 0;		  		  
	  while ($i < $num)
	    {
	      $obj = $db->fetch_object( $i);
	      print '<option value="'.$obj->rowid.'">'.$obj->label ;
	      $i++;
	    }
	}
      print '</select></td>';
      print '<td width="20%">Nb de pi�ce</td><td width="20%"><input name="nbpiece" size="10" value=""></td></tr>';
      print '<tr><td colspan="6" align="center"><input type="submit" value="'.$langs->trans('Save').'">&nbsp;';
      print '<input type="submit" name="cancel" value="'.$langs->trans("Cancel").'"></td></tr>';
      print '</table>';
      print '</form>';

    }
  /*
   * 
   *
   */
  if ($_GET["action"] == "definir")
    {
      print_titre ("Cr�er un stock");
      print "<form action=\"product.php?id=$product->id\" method=\"post\">\n";
      print '<input type="hidden" name="action" value="create_stock">';
      print '<table class="border" width="100%"><tr>';
      print '<td width="20%">'.$langs->trans("Warehouse").'</td><td width="40%"><select name="id_entrepot">';
      
      $sql = "SELECT e.rowid, e.label FROM ".MAIN_DB_PREFIX."entrepot as e";    
      $sql .= " ORDER BY lower(e.label)";
      
      if ($db->query($sql))
	{
	  $num = $db->num_rows();
	  $i = 0;		  		  
	  while ($i < $num)
	    {
	      $obj = $db->fetch_object( $i);
	      print '<option value="'.$obj->rowid.'">'.$obj->label ;
	      $i++;
	    }
	}
      print '</select></td><td width="20%">Nb de pi�ce</td><td width="20%"><input name="nbpiece" size="10" value=""></td></tr>';
      print '<tr><td colspan="4" align="center"><input type="submit" value="'.$langs->trans('Save').'">&nbsp;';
      print '<input type="submit" name="cancel" value="'.$langs->trans('Cancel').'"></td></tr>';
      print '</table>';
      print '</form>';
    }
}
else
{
  dolibarr_print_error();
}


/* ************************************************************************** */
/*                                                                            */ 
/* Barre d'action                                                             */ 
/*                                                                            */ 
/* ************************************************************************** */

print "<div class=\"tabsAction\">\n";

if ($_GET["action"] == '' )
{
  print '<a class="tabAction" href="product.php?id='.$product->id.'&amp;action=transfert">Transfert</a>';
  print '<a class="tabAction" href="product.php?id='.$product->id.'&amp;action=correction">Correction stock</a>';
}
print '</div>';


$db->close();


llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
