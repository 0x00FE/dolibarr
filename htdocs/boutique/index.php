<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/boutique/index.php
        \ingroup    boutique
		\brief      Page accueil zone boutique
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("boutique");


llxHeader("",$langs->trans("Orders"),"Commande");

print_titre($langs->trans("BoutiqueWeb"));

print '<table class="noborder" width="100%">';

print '<tr><td valign="top" width="40%">';


/* 
/* Chiffre d'affaire 
*/
//print_barre_liste("Chiffre d'affaire", $page, "ca.php");

print_titre($langs->trans("Chiffre d'affaire"));

print '<table class="noborder" cellspacing="0" cellpadding="3" width="100%">';
print '<tr class="liste_titre"><td>'.$langs->trans("Description").'</td>';
print '<td align="right">'.$langs->trans("Total").'</td></tr>';

$sql = "SELECT sum(t.value) as value, MONTH(o.date_purchased) as mois";
$sql .= " FROM ".OSC_DB_NAME.".orders_total as t";
$sql .= " JOIN ".OSC_DB_NAME.".orders as o ON o.orders_id = t.orders_id";
$sql .= " WHERE t.class = 'ot_subtotal' AND YEAR(o.date_purchased) = YEAR(now()) ";
$sql .= " GROUP BY mois ORDER BY mois";
 
if ( $db->query($sql) )
{
  $num = $db->num_rows();

  $var=True;
  $i=0;
  if ($num > 0)
    {
	   while ($i < $num)
		{
      	$objp = $db->fetch_object();
      	$var=!$var;
      	print "<tr $bc[$var]>";
      	print '<td align="left">'.$objp->mois.'</td>';
      	print '<td align="right">'.price($objp->value).'</td>';

      	print "</tr>\n";
      	$i++;
    	}
	}

  $db->free();
}
else
{
  dolibarr_print_error($db);
}

/* mensuel

$sql = "SELECT sum(t.value) as value";
$sql .= " FROM ".OSC_DB_NAME.".orders_total as t";
$sql .= " JOIN ".OSC_DB_NAME.".orders as o ON o.orders_id = t.orders_id";
$sql .= " WHERE t.class = 'ot_subtotal' AND YEAR(o.date_purchased) = YEAR(now()) AND MONTH(o.date_purchased) = MONTH(now())";
 
if ( $db->query($sql) )
{
  $num = $db->num_rows();

  $var=True;
  if ($num > 0)
    {
      $objp = $db->fetch_object();
      $var=!$var;
      print "<tr $bc[$var]>";
      print '<td>CA du mois en cours  </td>';
      print '<td align="right">'.price($objp->value).'</td></tr>';
      $i++;
    }

  $db->free();
}
else
{
  dolibarr_print_error($db);
}
*/

print "</table>";
print '</td><td valign="top" width="60%">';
print_titre($langs->trans("Commandes"));

/*
 * 5 derni�res commandes re�ues
 select o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.payement_method, o.status, t.value
from orders_total as t
join orders as o on o.orders_id = t.orders_id where t.class = 'ot_subtotal' order by o.date_purchased desc
 */
$sql = "SELECT o.orders_id, o.customers_name, o.date_purchased, t.value, o.payment_method";
$sql .= " FROM ".OSC_DB_NAME.".orders_total as t JOIN ".OSC_DB_NAME.".orders as o on o.orders_id = t.orders_id ";
$sql .= " WHERE t.class = 'ot_subtotal' ORDER BY o.date_purchased desc";

if ( $db->query($sql) ) 
{
  $langs->load("orders");
  $num = $db->num_rows();
  if ($num > 0)
    {
      $i = 0;
      print '<table class="noborder" width="100%">';
      print '<tr class="liste_titre">';
      print '<td colspan="4">'.$langs->trans("Derni�res commandes").'</td></tr>';
		
		$num = min($num,OSC_MAXNBCOM);
      while ($i < $num)
	{
	  
	  $obj = $db->fetch_object();
	  print "<tr><td>$obj->orders_id</td><td>$obj->customers_name</td><td>".price($obj->value)."</td><td>$obj->payment_method</td></tr>";
	  $i++;
	}
      print "</table><br>";
    }
}
else
{
  dolibarr_print_error($db);
}

/*
 * 5 derni�res commandes en attente
*/
$sql = "SELECT o.orders_id, o.customers_name, o.date_purchased, t.value, o.payment_method";
$sql .= " FROM ".OSC_DB_NAME.".orders_total as t JOIN ".OSC_DB_NAME.".orders as o on o.orders_id = t.orders_id ";
$sql .= " WHERE t.class = 'ot_subtotal' and o.orders_status = 5 order by o.date_purchased desc";

if ( $db->query($sql) ) 
{
  $langs->load("orders");
  $num = $db->num_rows();
  if ($num > 0)
    {
      $i = 0;
      print '<table class="noborder" width="100%">';
      print '<tr class="liste_titre">';
      print '<td colspan="4">'.$langs->trans("En Attente").'</td></tr>';
		
		$num = min($num,OSC_MAXNBCOM);
      while ($i < $num)
	{
	  
	  $obj = $db->fetch_object();
	  print "<tr><td>$obj->orders_id</td><td>$obj->customers_name</td><td>".price($obj->value)."</td><td>$obj->payment_method</td></tr>";
	  $i++;
	}
      print "</table><br>";
    }
}
else
{
  dolibarr_print_error($db);
}

/*
 * Commandes � traiter
 */
$sql = "SELECT o.orders_id, o.customers_name, o.date_purchased, t.value, o.payment_method";
$sql .= " FROM ".OSC_DB_NAME.".orders_total as t JOIN ".OSC_DB_NAME.".orders as o on o.orders_id = t.orders_id ";
$sql .= " WHERE t.class = 'ot_subtotal' and o.orders_status = 2 order by o.date_purchased desc";

if ( $db->query($sql) ) 
{
  $langs->load("orders");
  $num = $db->num_rows();
  if ($num > 0)
    {
      $i = 0;
      print '<table class="noborder" width="100%">';
      print '<tr class="liste_titre">';
      print '<td colspan="4">'.$langs->trans("Traitement en cours").'</td></tr>';

		$num = min($num,OSC_MAXNBCOM);
      while ($i < $num)
	{
	  
	  $obj = $db->fetch_object();
	  print "<tr><td>$obj->orders_id</td><td>$obj->customers_name</td><td>".price($obj->value)."</td><td>$obj->payment_method</td></tr>";
	  $i++;
	}
      print "</table><br>";
    }
}
else
{
  dolibarr_print_error($db);
}


print '</td></tr><tr>';
/*
* Derniers clients qui ont command�
*/
$sql = "SELECT o.orders_id, o.customers_name, o.delivery_country, o.date_purchased, t.value, s.orders_status_name as statut";
$sql .= " FROM ".OSC_DB_NAME.".orders_total as t JOIN ".OSC_DB_NAME.".orders as o on o.orders_id = t.orders_id ";
$sql .= " JOIN ".OSC_DB_NAME.".orders_status as s on o.orders_status = s.orders_status_id and s.language_id = 1";
$sql .= " WHERE t.class = 'ot_subtotal' order by o.date_purchased desc";

if ( $db->query($sql) ) 
{
  $langs->load("orders");
  $num = $db->num_rows();
  if ($num > 0)
    {
      $i = 0;
      print '<table class="noborder" width="100%">';
      print '<tr class="liste_titre">';
      print '<td colspan="7">'.$langs->trans("Derniers clients").'</td></tr>';
      
		$num = min($num,OSC_MAXNBCOM);
      while ($i < $num)
	{
	  
	  $obj = $db->fetch_object();
	  print "<tr><td>$obj->date_purchased</td><td>$obj->customers_name</td><td>$obj->delivery_country</td><td>".price($obj->value)."</td><td>$obj->payment_method</td><td>$obj->orders_id</td><td>$obj->statut</td></tr>";
	  $i++;
	}
      print "</table><br>";
    }
}
else
{
  dolibarr_print_error($db);
}
print '</tr></table>';

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
