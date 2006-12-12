<?php
/* Copyright (C) 2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/**
 \file       htdocs/admin/stock.php
  \ingroup    stock
   \brief      Page d'administration/configuration du module gestion de stock
    \version    $Revision$
*/
require("./pre.inc.php");

$langs->load("admin");
$langs->load("stocks");

if (!$user->admin)
  accessforbidden();

/*
* Actions
*/
if ($_POST["action"] == 'stock_userstock')
{
  dolibarr_set_const($db, "STOCK_USERSTOCK", $_POST["stock_userstock"]);
  Header("Location: stock.php");
  exit;
}
elseif ($_POST["action"] == 'stock_userstock_autocreate')
{
  dolibarr_set_const($db, "STOCK_USERSTOCK_AUTOCREATE", $_POST["stock_userstock_autocreate"]);
  Header("Location: stock.php");
  exit;
}
elseif ($_POST["action"] == 'stock_bill')
{
  dolibarr_set_const($db, "STOCK_CALCULATE_ON_BILL", $_POST["stock_bill"]);
  Header("Location: stock.php");
  exit;
}

/*
 * Affiche page
 */
llxHeader('',$langs->trans("StockSetup"));

print_fiche_titre($langs->trans("StockSetup"),'','setup');

$html=new Form($db);
$var=true;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print "  <td>".$langs->trans("Parameters")."</td>\n";
print "  <td align=\"right\" width=\"160\">".$langs->trans("Value")."</td>\n";
print "  </tr>\n";

/*
 * Formulaire parametres divers
 */
// sousproduits activation/desactivation
$var=!$var;

print "<tr ".$bc[$var].">";
print '<td width="60%">'.$langs->trans("UserWarehouse").'</td>';
print '<td width="160" align="right">';
print "<form method=\"post\" action=\"stock.php\">";
print "<input type=\"hidden\" name=\"action\" value=\"stock_userstock\">";
print $html->selectyesno("stock_userstock",$conf->global->STOCK_USERSTOCK,1);
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print "</form>\n</td>\n</tr>\n";

if ($conf->global->STOCK_USERSTOCK == 1)
{
  $var=!$var;

  print "<tr ".$bc[$var].">";
  print '<td width="60%">'.$langs->trans("UserWarehouseAutoCreate").'</td>';
  
  print '<td width="160" align="right">';
  print "<form method=\"post\" action=\"stock.php\">";
  print "<input type=\"hidden\" name=\"action\" value=\"stock_userstock_autocreate\">";
  print $html->selectyesno("stock_userstock_autocreate",$conf->global->STOCK_USERSTOCK_AUTOCREATE,1);
  
  print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
  print '</form>';
  print "</td>\n";   
  print "</tr>\n";
}
$var=!$var;

print "<tr ".$bc[$var].">";
print '<td width="60%">'.$langs->trans("DeStockReStockOnBill").'</td>';
print '<td width="160" align="right">';
print "<form method=\"post\" action=\"stock.php\">";
print "<input type=\"hidden\" name=\"action\" value=\"stock_bill\">";
print $html->selectyesno("stock_bill",$conf->global->STOCK_CALCULATE_ON_BILL,1);
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print "</form>\n</td>\n</tr>\n";

print '</table>';
$db->close();

llxFooter('$Date$ - $Revision$');
?>
