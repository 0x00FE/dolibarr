<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      �ric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Christophe Combelles <ccomb@free.fr>
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

/**     \file       htdocs/fourn/facture/paiement.php
        \ingroup    fournisseur,facture
        \brief      Paiements des factures fournisseurs
		\version    $Revision$
*/


require("./pre.inc.php");
require("./paiementfourn.class.php");

$facid=isset($_GET["facid"])?$_GET["facid"]:$_POST["facid"];
$action=isset($_GET["action"])?$_GET["action"]:$_POST["action"];

$page=$_GET["page"];
$sortorder=$_GET["sortorder"];
$sortfield=$_GET["sortfield"];


/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}
/*
 *
 */
if ($action == 'add') {
  $paiementfourn = new PaiementFourn($db);

  $paiementfourn->facid        = $facid;
  $paiementfourn->facnumber    = $_POST['facnumber'];
  $paiementfourn->datepaye     = $db->idate(mktime(12, 0 , 0,
					      $_POST["remonth"], 
					      $_POST["reday"], 
					      $_POST["reyear"])); 
  $paiementfourn->amount       = $_POST['amount'];
  $paiementfourn->accountid    = $_POST['accountid'];
  $paiementfourn->societe      = $_POST['societe'];
  $paiementfourn->author       = $_POST['author'];
  $paiementfourn->paiementid   = $_POST['paiementid'];
  $paiementfourn->num_paiement = $_POST['num_paiement'];
  $paiementfourn->note         = $_POST['note'];

  if ( $paiementfourn->create($user) )
    {
      Header("Location: fiche.php?facid=$facid");
    }

  $action = '';
}

/*
 *
 *
 */

llxHeader();

if ($action == 'create')
{

  $sql = "SELECT s.nom,s.idp, f.amount, f.total_ttc, f.facnumber";
  $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture_fourn as f WHERE f.fk_soc = s.idp";
  $sql .= " AND f.rowid = $facid";

  $result = $db->query($sql);
  if ($result)
    {
      $num = $db->num_rows();
      if ($num)
	{
	  $obj = $db->fetch_object($result);

	  $total = $obj->total_ttc;

      print_titre($langs->trans("DoPayment"));
      print '<form action="paiement.php?facid='.$facid.'" method="post">';
      print '<input type="hidden" name="action" value="add">';
      print '<table class="border" width="100%">';

      print "<tr class=\"liste_titre\"><td colspan=\"3\">".$langs->trans("Bill")."</td>";

      print '<tr><td>'.$langs->trans("Ref").' :</td><td colspan="2">';
      print '<a href="fiche.php?facid='.$facid.'">'.$obj->facnumber.'</a></td></tr>';
      print "<tr><td>".$langs->trans("Company")." :</td><td colspan=\"2\">$obj->nom</td></tr>";

      print "<tr><td>".$langs->trans("AmountTTC")." :</td><td colspan=\"2\">".price($obj->total_ttc)." euros</td></tr>";

      $sql = "SELECT sum(p.amount) FROM ".MAIN_DB_PREFIX."paiementfourn as p WHERE p.fk_facture_fourn = $facid;";
      $result = $db->query($sql);
      if ($result) {
    	$sumpayed = $db->result(0,0);
	    $db->free();
      }
      print '<tr><td>'.$langs->trans("AlreadyPayed").' :</td><td colspan="2"><b>'.price($sumpayed).'</b> euros</td></tr>';

      print "<tr class=\"liste_titre\"><td colspan=\"3\">".$langs->trans("Payment")."</td>";

      print "<input type=\"hidden\" name=\"facid\" value=\"$facid\">";
      print "<input type=\"hidden\" name=\"facnumber\" value=\"$obj->facnumber\">";
      print "<input type=\"hidden\" name=\"socid\" value=\"$obj->idp\">";
      print "<input type=\"hidden\" name=\"societe\" value=\"$obj->nom\">";
      
      $html = new Form($db);

      print "<tr><td>".$langs->trans("Date")." :</td><td>";
      $html->select_date();
      print "</td>";

      print '<td>'.$langs->trans("Comments").' :</td></tr>';

      print "<input type=\"hidden\" name=\"author\" value=\"$author\">\n";

      print "<tr><td>".$langs->trans("Type")." :</td><td><select name=\"paiementid\">\n";

      $sql = "SELECT id, libelle FROM ".MAIN_DB_PREFIX."c_paiement ORDER BY id";
  
      if ($db->query($sql))
	{
	  $num = $db->num_rows();
	  $i = 0; 
	  while ($i < $num)
	    {
	      $objopt = $db->fetch_object();
	      print "<option value=\"$objopt->id\">$objopt->libelle</option>\n";
	      $i++;
	    }
      }
      print "</select><br>";
      print "</td>\n";

      print "<td rowspan=\"4\">";
      print '<textarea name="comment" wrap="soft" cols="40" rows="10"></textarea></td></tr>';

      print "<tr><td>Num�ro :</td><td><input name=\"num_paiement\" type=\"text\"><br><em>N� du ch�que ou du virement</em></td></tr>\n";

      print "<tr><td>Compte � d�biter :</td><td><select name=\"accountid\"><option value=\"\">-</option>\n";
      $sql = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."bank_account ORDER BY rowid";
      $result = $db->query($sql);
      if ($result)
	{
	  $num = $db->num_rows();
	  $i = 0; 
	  while ($i < $num)
	    {
	      $objopt = $db->fetch_object();
	      print '<option value="'.$objopt->rowid.'"';
	      if (defined("FACTURE_RIB_NUMBER") && FACTURE_RIB_NUMBER == $objopt->rowid)
		{
		  print ' selected';
		}
	      print '>'.$objopt->label.'</option>';
	      $i++;
	    }
	}
      print "</select>";
      print "</td></tr>\n";

      print "<tr><td valign=\"top\">".$langs->trans("RemainderToPay")." :</td><td><b>".price($total - $sumpayed)."</b> euros</td></tr>\n";
      print "<tr><td valign=\"top\">".$langs->trans("AmountTTC")." :</td><td><input name=\"amount\" type=\"text\" value=\"".($total - $sumpayed)."\"></td></tr>\n";
      print '<tr><td colspan="3" align="center"><input type="submit" value="'.$langs->trans("Save").'"></td></tr>';
      print "</form>\n";
      print "</table>\n";

    }
  }
} 

if ($action == '') {

  if ($page == -1)
    {
      $page = 0 ;
    }
  $limit = $conf->liste_limit;
  $offset = $limit * $page ;

  $sql = "SELECT ".$db->pdate("p.datep")." as dp, p.amount, f.amount as fa_amount, f.facnumber, s.nom";
  $sql .=", f.rowid as facid, c.libelle as paiement_type, p.num_paiement";
  $sql .= " FROM ".MAIN_DB_PREFIX."paiementfourn as p, ".MAIN_DB_PREFIX."facture_fourn as f, ".MAIN_DB_PREFIX."c_paiement as c, ".MAIN_DB_PREFIX."societe as s";
  $sql .= " WHERE p.fk_facture_fourn = f.rowid AND p.fk_paiement = c.id AND s.idp = f.fk_soc";

  if ($socidp)
    {
      $sql .= " AND f.fk_soc = $socidp";
    }

  $sql .= " ORDER BY datep DESC";
  $sql .= $db->plimit($limit + 1 ,$offset);
  $result = $db->query($sql);

  if ($result)
    {
      $num = $db->num_rows();
      $i = 0; 
      $var=True;

      print_barre_liste($langs->trans("Payments"), $page, "paiement.php","&amp;socidp=$socidp",$sortfield,$sortorder,'',$num);

      print '<table class="noborder" width="100%">';
      print '<tr class="liste_titre">';
      print '<td>'.$langs->trans("Bill").'</td>';
      print '<td>'.$langs->trans("Company").'</td>';
      print '<td>'.$langs->trans("Date").'</td>';
      print '<td>'.$langs->trans("Type").'</td>';
      print '<td align="right">'.$langs->trans("AmountTTC").'</td>';
      print "<td>&nbsp;</td></tr>";
    
      while ($i < min($num,$limit))
	{
	  $objp = $db->fetch_object($result);
	  $var=!$var;
	  print "<tr $bc[$var]>";
	  print "<td><a href=\"fiche.php?facid=$objp->facid\">".img_object($langs->trans("ShowBill"),"bill")."</a> ";
	  print "<a href=\"fiche.php?facid=$objp->facid\">$objp->facnumber</a></td>\n";
	  print '<td>'.$objp->nom.'</td>';
	  print "<td>".dolibarr_print_date($objp->dp)."</td>\n";
	  print "<td>$objp->paiement_type $objp->num_paiement</td>\n";
	  print '<td align="right">'.price($objp->amount).'</td><td>&nbsp;</td>';	
	  print "</tr>";
	  $i++;
	}
      print "</table>";
    }
  else
    {
      dolibarr_print_error($db);
    }

}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
