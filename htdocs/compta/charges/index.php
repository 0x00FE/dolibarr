<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
        \file       htdocs/compta/charges/index.php
        \ingroup    compta
		\brief      Page liste des charges
		\version    $Revision$
*/

require("./pre.inc.php");

llxHeader();

$user->getrights('compta');

if (!$user->admin && !$user->rights->compta->charges)
  accessforbidden();


$year=$_GET["year"];
$filtre=$_GET["filtre"];
if (! $year) { $year=date("Y", time()); }

print_fiche_titre($langs->trans("Charges"),($year?"<a href='index.php?year=".($year-1)."'>".img_previous()."</a> ".$langs->trans("Year")." $year <a href='index.php?year=".($year+1)."'>".img_next()."</a>":""));

print "<br>";
print '<table class="noborder" width="100%">';
print "<tr class=\"liste_titre\">";
print "<td>".$langs->trans("Group")."</td>";
print "<td align=\"right\">".$langs->trans("Nb")."</td>";
print "<td align=\"right\">".$langs->trans("AmountTTC")."</td>";
print "<td align=\"right\">".$langs->trans("AlreadyPayed")."</td>";
print "</tr>\n";

/*
 * Charges sociales
 */
$sql = "SELECT c.libelle as lib, s.fk_type as type, count(s.rowid) as nb, sum(s.amount) as total, sum(IF(paye=1,s.amount,0)) as totalpaye";
$sql .= " FROM ".MAIN_DB_PREFIX."c_chargesociales as c, ".MAIN_DB_PREFIX."chargesociales as s";
$sql .= " WHERE s.fk_type = c.id";
if ($year > 0)
{
    $sql .= " AND (";
    // Si period renseign� on l'utilise comme critere de date, sinon on prend date �ch�ance,
    // ceci afin d'etre compatible avec les cas ou la p�riode n'etait pas obligatoire
    $sql .= "   (s.periode is not null and date_format(s.periode, '%Y') = $year) ";
    $sql .= "or (s.periode is null     and date_format(s.date_ech, '%Y') = $year)";
    $sql .= ")";
}
$sql .= " GROUP BY lower(c.libelle) ASC";

if ( $db->query($sql) )
{
  $num = $db->num_rows();
  $i = 0;
  $var=true;
  
  while ($i < $num) {
    $obj = $db->fetch_object();
    $var = !$var;
    print "<tr $bc[$var]>";
    print '<td><a href="../sociales/index.php?filtre=s.fk_type:'.$obj->type.'">'.$obj->lib.'</a></td>';
    print '<td align="right">'.$obj->nb.'</td>';
    print '<td align="right">'.price($obj->total).'</td>';
    print '<td align="right">'.price($obj->totalpaye).'</td>';
    print '</tr>';
    $i++;
  }
} else {
  dolibarr_print_error($db);
}

/**
 * Factures fournisseurs
 */
$sql = "SELECT count(f.rowid) as nb, sum(total_ttc) as total, sum(IF(paye=1,total_ttc,0)) as totalpaye";
$sql .= " FROM ".MAIN_DB_PREFIX."facture_fourn as f";
if ($year > 0)
{
    $sql .= " WHERE date_format(f.datef, '%Y') = $year";
}

if ( $db->query($sql) ) {

  $langs->load("compta");
  
  $num = $db->num_rows();
  $i = 0;

  while ($i < $num) {
    $obj = $db->fetch_object();
    $var = !$var;
    print "<tr $bc[$var]>";
    print '<td>'.$langs->trans("BillsForSuppliers").'</td>';
    print '<td align="right">'.$obj->nb.'</td>';
    print '<td align="right">'.price($obj->total).'</td>';
    print '<td align="right">'.price($obj->totalpaye).'</td>';
    print '</tr>';
    $i++;
  }
} else {
    dolibarr_print_error($db);
}

print "</table><br>";


$db->close();
 
llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
