<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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
	    \file       htdocs/compta/facture/stats/index.php
        \ingroup    facture
		\brief      Page des stats factures
		\version    $Revision$
*/

require("./pre.inc.php");

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}

llxHeader();

print_fiche_titre($langs->trans("BillsStatistics"), $mesg);

$stats = new FactureStats($db, $socidp);
$year = strftime("%Y", time());
$data = $stats->getNbByMonthWithPrevYear($year);

if (! is_dir($conf->facture->dir_images)) { mkdir($conf->facture->dir_images); }

$filename = $conf->facture->dir_images."/nbfacture2year-$year.png";
$fileurl = $conf->facture->url_images."/nbfacture2year-$year.png";

$px = new BarGraph($data);
$mesg = $px->isGraphKo();
if (! $mesg) {
    $px->SetMaxValue($px->GetMaxValue());
    $px->SetLegend(array($year - 1, $year));
    $px->SetWidth(450);
    $px->SetHeight(280);
    $px->draw($filename, $data, $year);
}
      
$sql = "SELECT count(*), date_format(datef,'%Y') as dm, sum(total) FROM ".MAIN_DB_PREFIX."facture WHERE fk_statut > 0 ";
if ($socidp)
{
  $sql .= " AND fk_soc = $socidp";
}
$sql .= " GROUP BY dm DESC ";
if ($db->query($sql))
{
  $num = $db->num_rows();

  print '<table class="border" width="100%">';
  print '<tr><td align="center">'.$langs->trans("Year").'</td><td width="10%" align="center">'.$langs->trans("NumberOfBills").'</td><td align="center">'.$langs->trans("AmountTotal").'</td>';
  print '<td align="center" valign="top" rowspan="'.($num + 1).'">';
  print $langs->trans("NumberOfBillsByMonth").'<br>';
  if ($mesg) { print $mesg; }
  else { print '<img src="'.$fileurl.'" alt="Nombre de factures par mois">'; }
  print '</td></tr>';
  $i = 0;
  while ($i < $num)
    {
      $row = $db->fetch_row($i);
      $nbproduct = $row[0];
      $year = $row[1];
      print "<tr>";
      print '<td align="center"><a href="month.php?year='.$year.'">'.$year.'</a></td><td align="center">'.$nbproduct.'</td><td align="center">'.price($row[2]).'</td></tr>';
      $i++;
    }

  print '</table>';
  $db->free();
}
else
{
  dolibarr_print_error($db);
}


$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
