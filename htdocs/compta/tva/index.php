<?PHP
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 �ric Seigne <eric.seigne@ryxeo.com>
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
require("../../tva.class.php");

/*
 *
 *
 */
function tva_coll($db, $y,$m) {
  $sql = "SELECT sum(f.tva) as amount"; 
  $sql .= " FROM llx_facture as f WHERE f.paye = 1";
  $sql .= " AND date_format(f.datef,'%Y') = $y";
  $sql .= " AND date_format(f.datef,'%m') = $m";

  $result = $db->query($sql);
  if ($result) {
    $obj = $db->fetch_object ( 0 );
    return $obj->amount;
  }
}
/*
 *
 *
 */
function tva_paye($db, $y,$m)
{
  $sql = "SELECT sum(f.total_tva) as amount"; 
  $sql .= " FROM llx_facture_fourn as f WHERE f.paye = 1";
  $sql .= " AND date_format(f.datef,'%Y') = $y";
  $sql .= " AND date_format(f.datef,'%m') = $m";

  $result = $db->query($sql);
  if ($result)
    {
      $obj = $db->fetch_object ( 0 );
      return $obj->amount;
    }
}

function pt ($db, $sql, $date) {
  global $bc; 

  $result = $db->query($sql);
  if ($result) {
    $num = $db->num_rows();
    $i = 0; 
    $total = 0 ;
    print "<p><TABLE border=\"1\" width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">";
    print "<TR class=\"liste_titre\">";
    print "<TD nowrap width=\"60%\">$date</TD>";
    print "<TD align=\"right\">Montant</TD>";
    print "<td>&nbsp;</td>\n";
    print "</TR>\n";
    $var=True;
    while ($i < $num) {
      $obj = $db->fetch_object( $i);
      $var=!$var;
      print "<TR $bc[$var]>";
      print "<TD nowrap>$obj->dm</TD>\n";
      $total = $total + $obj->amount;

      print "<TD nowrap align=\"right\">".price($obj->amount)."</TD><td nowrap align=\"right\">".$total."</td>\n";
      print "</TR>\n";
            
      $i++;
    }
    print "<tr class=\"total\"><td align=\"right\">Total :</td><td nowrap align=\"right\"><b>".price($total)."</b></td><td>euros&nbsp;HT</td></tr>";
    
    print "</TABLE>";
    $db->free();
  } else {
    print $db->error();
  }
}

/*
 *
 */

llxHeader();

$tva = new Tva($db);

$textprevyear="<a href=\"$PHP_SELF?year=" . ($year-1) . "\">Ann�e pr�c�dente (" . ($year-1) . ")</a>";
// On n'affiche pas "Ann�e suivante" si c'est dans le futur !
if($year < strftime("%Y",time()))
  $textnextyear=" - <a href=\"$PHP_SELF?year=" . ($year+1) . "\">Ann�e suivante (" . ($year+1) . ")</a>";
print_titre( "<table border=\"0\" width=\"100%\"><tr><td nowrap>TVA Solde : " . price($tva->solde($year)) . " euros </td><td align=\"right\">$textprevyear $textnextyear</td></tr></table>");

if ($year == 0 ) {
  $year_current = strftime("%Y",time());
  //$year_start = $conf->years;
  $year_start = $year_current;
} else {
  $year_current = $year;
  $year_start = $year;
}

echo '<table width="100%">';
echo '<tr><td width="50%" valign="top">TVA collect�e</td>';
echo '<td>Tva R�gl�e</td></tr>';

for ($y = $year_current ; $y >= $year_start ; $y=$y-1 ) {

  echo '<tr><td width="50%" valign="top">';

  print "<p><TABLE border=\"1\" width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">";
  print "<TR class=\"liste_titre\">";
  print "<TD width=\"30%\">Ann�e $y</TD>";
  print "<TD align=\"right\">Collect�e</TD>";
  print "<TD align=\"right\">Pay�e</TD>";
  print "<td>&nbsp;</td>\n";
  print "<td>&nbsp;</td>\n";
  print "</TR>\n";
  $var=True;
  $total = 0;  $subtotal = 0;
  $i=0;
  for ($m = 1 ; $m < 13 ; $m++ ) {
    $var=!$var;
    print "<TR $bc[$var]>";
    print '<TD nowrap>'.strftime("%b %Y",mktime(0,0,0,$m,1,$y)).'</TD>';
    
    $x_coll = tva_coll($db, $y, $m);
    print "<TD nowrap align=\"right\">".price($x_coll)."</TD>";
    
    $x_paye = tva_paye($db, $y, $m);
    print "<TD nowrap align=\"right\">".price($x_paye)."</TD>";
    
    $diff = $x_coll - $x_paye;
    $total = $total + $diff;
    $subtotal = $subtotal + $diff;
    
    print "<td nowrap align=\"right\">".price($diff)."</td>\n";
    print "<td>&nbsp;</td>\n";
    print "</TR>\n";
    
    $i++;
    if ($i > 2) {
      print '<tr class="total"><td align="right" colspan="3">Sous total :</td><td nowrap align="right">'.price($subtotal).'</td><td nowrap align="right"><small>'.price($subtotal * 0.8).'</small></td>';
      $i = 0;
      $subtotal = 0;
    }
  }
  print '<tr class="total"><td align="right" colspan="3">Total :</td><td nowrap align="right"><b>'.price($total).'</b></td>';
  print "<td>&nbsp;</td>\n";
  print "</TABLE>";

  echo '</td><td valign="top" width="50%">';

  /*
   * R�gl�e
   */

  print "<table width=\"100%\">";
  print "<tr><td valign=\"top\">";

  $sql = "SELECT amount, date_format(f.datev,'%Y-%m') as dm";
  $sql .= " FROM llx_tva as f WHERE f.datev >= '$y-01-01' AND f.datev <= '$y-12-31' ";
  $sql .= " GROUP BY dm DESC";
  
  pt($db, $sql,"Ann�e $y");
  
  print "</td></tr></table>";

  echo '</td></tr>';
}





echo '</table>';



$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
