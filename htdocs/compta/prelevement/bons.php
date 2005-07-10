<?PHP
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/compta/prelevement/bons.php
        \brief      Page liste des bons de prelevements
        \version    $Revision$
*/

require("./pre.inc.php");

$langs->load("withdrawals");

$page = $_GET["page"];
$sortorder = $_GET["sortorder"];
$sortfield = $_GET["sortfield"];


llxHeader('','Pr�l�vements - Bons');

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}


if ($page == -1) { $page = 0 ; }
$offset = $conf->liste_limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="DESC";
if (! $sortfield) $sortfield="p.datec";


/*
 * Mode Liste
 *
 */
$sql = "SELECT p.rowid, p.ref, p.amount,".$db->pdate("p.datec")." as datec";
$sql .= ", p.statut";
$sql .= " FROM ".MAIN_DB_PREFIX."prelevement_bons as p";

$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit($conf->liste_limit+1, $offset);

$result = $db->query($sql);
if ($result)
{
  $num = $db->num_rows($result);
  $i = 0;
  
  $urladd= "&amp;statut=".$_GET["statut"];

  print_barre_liste($langs->trans("WithdrawalsReceipts"), $page, "bons.php", $urladd, $sortfield, $sortorder, '', $num);

  print"\n<!-- debut table -->\n";
  print '<table class="noborder" width="100%">';
  print '<tr class="liste_titre">';

  print_liste_field_titre("Bon","bons.php","p.ref");
  print_liste_field_titre($langs->trans("Date"),"bons.php","p.datec","","",'align="center"');

  print '<td align="right">'.$langs->trans("Amount").'</td>';

  print '</tr><tr class="liste_titre">';

  print '<form action="bons.php" method="GET">';
  print '<td><input type="text" class="flat" name="search_ligne" value="'. $_GET["search_ligne"].'" size="10"></td>'; 
  print '<td>&nbsp;</td>';
  print '<td align="right"><input type="image" class="liste_titre" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" name="button_search" alt="'.$langs->trans("Search").'"></td>';
  print '</form>';
  print '</tr>';

  $var=True;

  while ($i < min($num,$conf->liste_limit))
    {
      $obj = $db->fetch_object($result);
      $var=!$var;

      print "<tr $bc[$var]><td>";
      print '<img border="0" src="./statut'.$obj->statut.'.png"></a>&nbsp;';

      print '<a href="fiche.php?id='.$obj->rowid.'">'.$obj->ref."</a></td>\n";

      print '<td align="center">'.strftime("%d/%m/%Y",$obj->datec)."</td>\n";

      print '<td align="right">'.price($obj->amount).' '.$langs->trans("Currency".$conf->monnaie).'</td>\n";

      print "</tr>\n";
      $i++;
    }
  print "</table>";
  $db->free($result);
}
else 
{
  dolibarr_print_error($db);
}

$db->close();

llxFooter('$Date$ - $Revision$');

?>
