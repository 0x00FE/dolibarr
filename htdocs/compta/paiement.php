<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/*!
	    \file       htdocs/compta/paiement.php
		\ingroup    compta
		\brief      Page de cr�ation d'un paiement
		\version    $Revision$
*/

include_once("./pre.inc.php");
include_once("../paiement.class.php");
include_once(DOL_DOCUMENT_ROOT."/facture.class.php");
include_once(DOL_DOCUMENT_ROOT."/compta/bank/account.class.php");

$facid=isset($_GET["facid"])?$_GET["facid"]:$_POST["facid"];


$socname=isset($_GET["socname"])?$_GET["socname"]:$_POST["socname"];
$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
$page=isset($_GET["page"])?$_GET["page"]:$_POST["page"];


if ($_POST["action"] == 'add_paiement')
{
  if ($_POST["paiementid"] > 0)
    {
      
      $datepaye = $db->idate(mktime(12, 0 , 0,
				    $_POST["remonth"], 
				    $_POST["reday"],
				    $_POST["reyear"]));

      $paiement_id = 0;
      $amounts = array();
      foreach ($_POST as $key => $value)
	{
	  if (substr($key,0,7) == 'amount_')
	    {
	      $other_facid = substr($key,7);
	      
	      $amounts[$other_facid] = $_POST[$key];
	    }
	}
      
      // TODO Mettre toute la chaine dans une m�me transaction

      // Creation de la ligne paiement
      $paiement = new Paiement($db);
      $paiement->datepaye     = $datepaye;
      $paiement->amounts      = $amounts;   // Tableau de montant
      $paiement->paiementid   = $_POST["paiementid"];
      $paiement->num_paiement = $_POST["num_paiement"];
      $paiement->note         = $_POST["comment"];
      $paiement_id = $paiement->create($user);

      if ($paiement_id > 0)
	{
          // On determine le montant total du paiement
          $total=0;
          foreach ($paiement->amounts as $key => $value)
          {
	    $facid = $key;
	    $value = trim($value);
	    $amount = round(ereg_replace(",",".",$value), 2);
	    if (is_numeric($amount))
	      {
                $total += $amount;
	      }
          }
	  
          // Insertion dans llx_bank
          $label = "R�glement facture";
	  $acc = new Account($db, $_POST["accountid"]);
	  //paiementid contient "CHQ ou VIR par exemple"
	  $bank_line_id = $acc->addline($paiement->datepaye, $paiement->paiementid, $label, $total, $paiement->num_paiement, '', $user);
	  
	  // Mise a jour fk_bank dans llx_paiement. On connait ainsi le paiement qui a g�n�r� l'�criture bancaire
	  if ($bank_line_id)
	    {
	      $paiement->update_fk_bank($bank_line_id);
	    }
	  
          // Mise a jour liens (pour chaque facture concern�es par le paiement)
          foreach ($paiement->amounts as $key => $value)
	    {
              $facid = $key;
      	      $fac = new Facture($db);
	      $fac->fetch($facid);
	      $fac->fetch_client();
	      $acc->add_url_line($bank_line_id, $paiement_id, DOL_URL_ROOT.'/compta/paiement/fiche.php?id=', "(paiement)");
	      $acc->add_url_line($bank_line_id, $fac->client->id, DOL_URL_ROOT.'/compta/fiche.php?socid=', $fac->client->nom);	  
	    }
	  
	  $loc = DOL_URL_ROOT.'/compta/paiement/fiche.php?id='.$paiement_id;
	  Header("Location: $loc");
	}
      else
	{
	  // Il y a eu erreur
	  $fiche_erreur_message = "Echec de la cr�ation du paiement";
	}
    }
  else
    {
      $fiche_erreur_message = "Vous devez s�lectionner un mode de paiement";
    }
}

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}

/*
 * Affichage
 */

llxHeader();

if ($_GET["action"] == 'create') 
{
  $facture = new Facture($db);
  $facture->fetch($facid);

  $sql = "SELECT s.nom,s.idp, f.amount, f.total_ttc as total, f.facnumber";
  $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture as f WHERE f.fk_soc = s.idp";
  $sql .= " AND f.rowid = $facid";

  $result = $db->query($sql);
  if ($result)
    {
      $num = $db->num_rows();
      if ($num)
	{
	  $obj = $db->fetch_object($result);

	  $total = $obj->total;

	  print_titre("Emettre un paiement");

	  print '<form action="paiement.php?action=create&facid='.$facid.'" method="post">';
	  print '<input type="hidden" name="action" value="add_paiement">';
	  print '<table class="border" width="100%">';
	  
	  print "<tr><td>".$langs->trans("Company")." :</td><td colspan=\"2\">$obj->nom</td></tr>\n";
	  
	  print "<input type=\"hidden\" name=\"facid\" value=\"$facid\">";
	  print "<input type=\"hidden\" name=\"facnumber\" value=\"$obj->facnumber\">";
	  print "<input type=\"hidden\" name=\"socid\" value=\"$obj->idp\">";
	  print "<input type=\"hidden\" name=\"societe\" value=\"$obj->nom\">";
	  
	  print "<tr><td>".$langs->trans("Date")." :</td><td>";
	  print_date_select();
	  print '</td>';
	  print '<td>'.$langs->trans("Comments").'</td></tr>';
	  
	  print '<tr><td>'.$langs->trans("Type").' :</td><td><select name="paiementid">';
	  
	  $sql = "SELECT id, libelle FROM ".MAIN_DB_PREFIX."c_paiement ORDER BY id";
	  
	  $result = $db->query($sql);
	  if ($result)
	    {
	      $num = $db->num_rows();
	      $i = 0; 
	      while ($i < $num)
		{
		  $objopt = $db->fetch_object($result);
		  print "<option value=\"$objopt->id\">$objopt->libelle</option>\n";
		  $i++;
		}
	    }
	  print "</select>";
	  print "</td>\n";

	  print '<td rowspan="3" valign="top">';
	  print '<textarea name="comment" wrap="soft" cols="40" rows="6"></textarea></td></tr>';	  

	  print "<tr><td>Num�ro :</td><td><input name=\"num_paiement\" type=\"text\"><br><em>Num�ro du ch�que / virement</em></td></tr>\n";

	  print "<tr><td>Compte � cr�diter :</td><td><select name=\"accountid\">\n";
	  
	  $sql = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."bank_account ORDER BY rowid";
	  
	  $result = $db->query($sql);
	  if ($result)
	    {
	      $num = $db->num_rows();
	      $i = 0; 
	      while ($i < $num)
		{
		  $objopt = $db->fetch_object($result);
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
	  	  
	  /*
	   * Autres factures impay�es
	   */

	  $sql = "SELECT f.rowid as facid,f.facnumber,f.total_ttc,".$db->pdate("f.datef")." as df";
	  $sql .= ", sum(pf.amount) as am";
	  $sql .= " FROM ".MAIN_DB_PREFIX."facture as f";
	  $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."paiement_facture as pf ON pf.fk_facture = f.rowid";
	  $sql .= " WHERE f.fk_soc = ".$facture->socidp;
	  $sql .= " AND f.paye = 0";
	  $sql .= " AND f.fk_statut = 1";  // Statut=0 => non valid�e, Statut=2 => annul�e
	  $sql .= " GROUP BY f.facnumber";  

	  if ($db->query($sql))
	    {
	      $num = $db->num_rows();
	      
	      if ($num > 0)
		{
		  $i = 0;
		  print '<tr><td colspan="3">';
		  print '<table class="noborder" width="100%">';
		  print '<tr class="liste_titre">';
		  print '<td>Facture</td><td align="center">'.$langs->trans("Date").'</td>';
		  print '<td align="right">'.$langs->trans("AmountTTC").'</td>';	      
		  print '<td align="right">Re�u</td>';
		  print '<td align="right">Reste � payer</td>';
		  print '<td align="center">'.$langs->trans("Amount").'</td>';
		  print "</tr>\n";
	      
		  $var=True;
		  $total=0;
		  $totalrecu=0;
		  
		  while ($i < $num)
		    {
		      $objp = $db->fetch_object();
		      $var=!$var;
		      
		      print "<tr $bc[$var]>";
		  
		      print '<td><a href="facture.php?facid='.$objp->facid.'">' . $objp->facnumber;
		      print "</a></td>\n";
		      
		      if ($objp->df > 0 )
			{
			  print "<td align=\"center\">";
			  print strftime("%d %b %Y",$objp->df)."</td>\n";
			}
		      else
			{
			  print "<td align=\"center\"><b>!!!</b></td>\n";
			}
		      
		      print '<td align="right">'.price($objp->total_ttc)."</td>";
		      print '<td align="right">'.price($objp->am)."</td>";
		      print '<td align="right">'.price($objp->total_ttc - $objp->am)."</td>";
		      
		      print '<td align="center">';

		      $namef = "amount_".$objp->facid;
		      print '<input type="text" size="8" name="'.$namef.'">';

		      print "</td></tr>\n";
		      $total+=$objp->total;
		      $total_ttc+=$objp->total_ttc;
		      $totalrecu+=$objp->am;
		      $i++;
		    }
		  if ($i > 1)
		    {
		      // Print total
		      print "<tr ".$bc[!$var].">";
		      print '<td colspan="2" align="left">'.$langs->trans("TotalTTC").':</td>';
		      print "<td align=\"right\"><b>".price($total_ttc)."</b></td>";
		      print "<td align=\"right\"><b>".price($totalrecu)."</b></td>";
		      print "<td align=\"right\"><b>".price($total_ttc - $totalrecu)."</b></td>";
		      print '<td align="center">&nbsp;</td>';
		      print "</tr>\n";
		    }
		  print "</table></td></tr>\n";
		}
	      $db->free();	
	    }
	  else
	    {
	      dolibarr_print_error($db);
	    }
	  /*
	   *
	   */

	  if ($fiche_erreur_message)
	    {
	      print '<tr><td colspan="3" align="center">'.$fiche_erreur_message.'</td></tr>';
	    }

	  print '<tr><td colspan="3" align="center"><input type="submit" value="'.$langs->trans("Save").'"></td></tr>';
	  print "</table>";
	  print "</form>\n";	  
	}
    }
} 


/**
 *  \brief      Affichage de la liste des paiement
 */
if (! $_GET["action"])
{
  
  if ($page == -1)
    $page = 0 ;
  
  $limit = $conf->liste_limit;
  $offset = $limit * $page ;
  
  if ($sortorder == "")
    $sortorder="DESC";
  
  if ($sortfield == "")
    $sortfield="p.datep";
  
  $sql = "SELECT ".$db->pdate("p.datep")." as dp, p.amount, f.amount as fa_amount, f.facnumber";
  $sql .=", f.rowid as facid, c.libelle as paiement_type, p.num_paiement";
  $sql .= " FROM ".MAIN_DB_PREFIX."paiement as p, ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."c_paiement as c";
  $sql .= " WHERE p.fk_facture = f.rowid AND p.fk_paiement = c.id";
  
  if ($socidp)
    {
      $sql .= " AND f.fk_soc = $socidp";
    }
  
  $sql .= " ORDER BY $sortfield $sortorder";
  $sql .= $db->plimit( $limit +1 ,$offset);
  $result = $db->query($sql);
  
  if ($result)
    {
      $num = $db->num_rows();
      $i = 0; 
      $var=True;
      
      print_barre_liste("Paiements", $page, "paiement.php","",$sortfield,$sortorder,'',$num);

      print '<table class="noborder" width="100%">';
      print '<tr class="liste_titre">';
      print_liste_field_titre($langs->trans("Bill"),"paiement.php","facnumber","","","",$sortfield);
      print_liste_field_titre($langs->trans("Date"),"paiement.php","dp","","","",$sortfield);
      print_liste_field_titre($langs->trans("Type"),"paiement.php","libelle","","","",$sortfield);
      print_liste_field_titre($langs->trans("Amount"),"paiement.php","fa_amount","","",'align="right"',$sortfield);
      print "<td>&nbsp;</td>";
      print "</tr>\n";
      
      while ($i < min($num,$limit))
	{
	  $objp = $db->fetch_object();
	  $var=!$var;
	  print "<tr $bc[$var]>";
	  print "<td><a href=\"facture.php?facid=$objp->facid\">$objp->facnumber</a></td>\n";
	  print "<td>".strftime("%d %B %Y",$objp->dp)."</td>\n";
	  print "<td>$objp->paiement_type $objp->num_paiement</td>\n";
	  print '<td align="right">'.price($objp->amount).'</td><td>&nbsp;</td>';	
	  print "</tr>";
	  $i++;
	}
      print "</table>";
    }  
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
