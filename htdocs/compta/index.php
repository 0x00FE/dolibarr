<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006 Regis Houssin        <regis.houssin@cap-networks.com>
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
      \file       htdocs/compta/index.php
      \ingroup    compta
      \brief      Page accueil zone comptabilit�
      \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/facture.class.php');
require_once(DOL_DOCUMENT_ROOT.'/fourn/fournisseur.facture.class.php');
if ($conf->commande->enabled) require_once(DOL_DOCUMENT_ROOT.'/commande/commande.class.php');
if ($conf->commande->enabled) require_once(DOL_DOCUMENT_ROOT.'/fourn/fournisseur.commande.class.php');
if ($conf->tax->enabled) require_once(DOL_DOCUMENT_ROOT.'/chargesociales.class.php');

$user->getrights(); // On a besoin des permissions sur plusieurs modules

// L'espace compta/tr�so doit toujours etre actif car c'est un espace partag�
// par de nombreux modules (banque, facture, commande � facturer, etc...) ind�pendemment
// de l'utilisation de la compta ou non. C'est au sein de cet espace que chaque sous fonction
// est prot�g� par le droit qui va bien du module concern�.
//if (!$user->rights->compta->general->lire)
//  accessforbidden();

$langs->load("compta");
$langs->load("bills");
if ($conf->commande->enabled) $langs->load("orders");

// S�curit� acc�s client
$socid='';
if ($user->societe_id > 0)
{
  $action = '';
  $socid = $user->societe_id;
}


/*
 * Actions
 */

if (isset($_GET["action"]) && $_GET["action"] == 'add_bookmark')
{
  $sql = "DELETE FROM ".MAIN_DB_PREFIX."bookmark WHERE fk_soc = ".$socid." AND fk_user=".$user->id;
  if (! $db->query($sql) )
    {
      dolibarr_print_error($db);
    }
  $sql = "INSERT INTO ".MAIN_DB_PREFIX."bookmark (fk_soc, dateb, fk_user) VALUES ($socid, now(),".$user->id.");";
  if (! $db->query($sql) )
    {
      dolibarr_print_error($db);
    }
}

if (isset($_GET["action"]) && $_GET["action"] == 'del_bookmark')
{
  $sql = "DELETE FROM ".MAIN_DB_PREFIX."bookmark WHERE rowid=".$_GET["bid"];
  $result = $db->query($sql);
}




/*
 * Affichage page
 *
 */

$facturestatic=new Facture($db);
$facturesupplierstatic=new FactureFournisseur($db);

llxHeader("",$langs->trans("AccountancyTreasuryArea"));

print_fiche_titre($langs->trans("AccountancyTreasuryArea"));

print '<table border="0" width="100%" class="notopnoleftnoright">';

print '<tr><td valign="top" width="30%" class="notopnoleft">';

/*
 * Zone recherche facture
 */
if ($conf->facture->enabled)
{
	print '<form method="post" action="'.DOL_URL_ROOT.'/facture.php">';
	print '<table class="noborder" width="100%">';
	print "<tr class=\"liste_titre\">";
	print '<td colspan="3">'.$langs->trans("SearchACustomerInvoice").'</td></tr>';
	print "<tr $bc[0]><td>".$langs->trans("Ref").':</td><td><input type="text" name="sf_ref" class="flat" size="18"></td>';
	print '<td rowspan="2"><input type="submit" value="'.$langs->trans("Search").'" class="button"></td></tr>';
	print "<tr $bc[0]><td>".$langs->trans("Other").':</td><td><input type="text" name="sall" class="flat" size="18"></td>';
	print '</tr>';
	print "</table></form><br>";

	print '<form method="post" action="'.DOL_URL_ROOT.'/fourn/facture/index.php">';
	print '<table class="noborder" width="100%">';
	print "<tr class=\"liste_titre\">";
	print '<td colspan="3">'.$langs->trans("SearchASupplierInvoice").'</td></tr>';
	print "<tr $bc[0]><td>".$langs->trans("Ref").':</td><td><input type="text" name="search_ref" class="flat" size="18"></td>';
	print '<td><input type="submit" value="'.$langs->trans("Search").'" class="button"></td></tr>';
	//print "<tr $bc[0]><td>".$langs->trans("Other").':</td><td><input type="text" name="sall" class="flat" size="18"></td>';
	print '</tr>';
	print "</table></form><br>";
}


/**
 * Factures clients brouillons
 */
if ($conf->facture->enabled && $user->rights->facture->lire)
{
	$sql  = "SELECT f.facnumber, f.rowid, f.total_ttc, f.type,";
	$sql.= " s.nom, s.rowid as socid";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user ";
	$sql .= " FROM ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."societe as s";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql .= " WHERE s.rowid = f.fk_soc AND f.fk_statut = 0";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;

	if ($socid)
	{
		$sql .= " AND f.fk_soc = $socid";
	}

	$resql = $db->query($sql);

	if ( $resql )
	{
		$var = false;
		$num = $db->num_rows($resql);

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td colspan="3">'.$langs->trans("CustomersDraftInvoices").($num?' ('.$num.')':'').'</td></tr>';
		if ($num)
		{
			$companystatic=new Societe($db);

			$i = 0;
			$tot_ttc = 0;
			while ($i < $num && $i < 20)
			{
				$obj = $db->fetch_object($resql);
				print '<tr '.$bc[$var].'><td nowrap>';
				$facturestatic->ref=$obj->facnumber;
				$facturestatic->id=$obj->rowid;
				$facturestatic->type=$obj->type;
				print $facturestatic->getNomUrl(1,'');
				print '</td>';
				print '<td>';
				$companystatic->id=$obj->socid;
				$companystatic->nom=$obj->nom;
				$companystatic->client=1;
				print $companystatic->getNomUrl(1,'',16);
				print '</td>';
				print '<td align="right">'.price($obj->total_ttc).'</td>';
				print '</tr>';
				$tot_ttc+=$obj->total_ttc;
				$i++;
				$var=!$var;
			}

			print '<tr class="liste_total"><td align="left">'.$langs->trans("Total").'</td>';
			print '<td colspan="2" align="right">'.price($tot_ttc).'</td>';
			print '</tr>';
		}
		else
		{
			print '<tr colspan="3" '.$bc[$var].'><td>'.$langs->trans("NoInvoice").'</td></tr>';
		}
		print "</table><br>";
		$db->free($resql);
	}
	else
	{
		dolibarr_print_error($db);
	}
}

/**
 * Factures fournisseurs brouillons
 */
if ($conf->facture->enabled && $user->rights->facture->lire)
{
	$sql  = "SELECT f.facnumber, f.rowid, f.total_ttc, f.type,";
	$sql.= " s.nom, s.rowid as socid";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user ";
	$sql .= " FROM ".MAIN_DB_PREFIX."facture_fourn as f, ".MAIN_DB_PREFIX."societe as s";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql .= " WHERE s.rowid = f.fk_soc AND f.fk_statut = 0";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;

	if ($socid)
	{
		$sql .= " AND f.fk_soc = $socid";
	}

	$resql = $db->query($sql);

	if ( $resql )
	{
		$var = false;
		$num = $db->num_rows($resql);

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td colspan="3">'.$langs->trans("SuppliersDraftInvoices").($num?' ('.$num.')':'').'</td></tr>';
		if ($num)
		{
			$companystatic=new Societe($db);

			$i = 0;
			$tot_ttc = 0;
			while ($i < $num && $i < 20)
			{
				$obj = $db->fetch_object($resql);
				print '<tr '.$bc[$var].'><td nowrap>';
				$facturesupplierstatic->ref=$obj->facnumber;
				$facturesupplierstatic->id=$obj->rowid;
				$facturesupplierstatic->type=$obj->type;
				print $facturesupplierstatic->getNomUrl(1,'');
				print '</td>';
				print '<td>';
				$facturesupplierstatic->id=$obj->socid;
				$facturesupplierstatic->nom=$obj->nom;
				$facturesupplierstatic->fournisseur=1;
				print $facturesupplierstatic->getNomUrl(1,'',16);
				print '</td>';
				print '<td align="right">'.price($obj->total_ttc).'</td>';
				print '</tr>';
				$tot_ttc+=$obj->total_ttc;
				$i++;
				$var=!$var;
			}

			print '<tr class="liste_total"><td align="left">'.$langs->trans("Total").'</td>';
			print '<td colspan="2" align="right">'.price($tot_ttc).'</td>';
			print '</tr>';
		}
		else
		{
			print '<tr colspan="3" '.$bc[$var].'><td>'.$langs->trans("NoInvoice").'</td></tr>';
		}
		print "</table><br>";
		$db->free($resql);
	}
	else
	{
		dolibarr_print_error($db);
	}
}

/**
 * Charges a payer
 */
if ($conf->tax->enabled)
{
    if ($user->societe_id == 0)
    {
		$chargestatic=new ChargeSociales($db);

        $sql = "SELECT c.rowid, c.amount, ".$db->pdate("c.date_ech")." as date_ech,";
		$sql.= " cc.libelle";
        $sql.= " FROM ".MAIN_DB_PREFIX."chargesociales as c, ".MAIN_DB_PREFIX."c_chargesociales as cc";
        $sql.= " WHERE c.fk_type = cc.id AND c.paye=0";

        $resql = $db->query($sql);
        if ( $resql )
        {
            $var = false;
			$num = $db->num_rows($resql);

			print '<table class="noborder" width="100%">';
			print '<tr class="liste_titre">';
			print '<td colspan="2">'.$langs->trans("ContributionsToPay").($num?' ('.$num.')':'').'</td></tr>';
            if ($num)
            {
                $i = 0;
                $tot_ttc=0;
                while ($i < $num)
                {
                    $obj = $db->fetch_object($resql);
                    print "<tr $bc[$var]>";
					$chargestatic->id=$obj->rowid;
                    $chargestatic->lib=$obj->libelle;
                    print '<td>'.$chargestatic->getNomUrl(1).'</td>';
                    print '<td align="right">'.price($obj->amount).'</td>';
                    print '</tr>';
                    $tot_ttc+=$obj->amount;
                    $var = !$var;
                    $i++;
                }

                print '<tr class="liste_total"><td align="left">'.$langs->trans("Total").'</td>';
                print '<td align="right">'.price($tot_ttc).'</td>';
                print '</tr>';
            }
			else
			{
				print '<tr colspan="2" '.$bc[$var].'><td>'.$langs->trans("None").'</td></tr>';
			}
			print "</table><br>";
            $db->free($resql);
        }
        else
        {
            dolibarr_print_error($db);
        }
    }
}


/**
 * Bookmark
 */
$sql = "SELECT s.rowid as socid, s.nom, b.rowid as bid";
$sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."bookmark as b";
$sql .= " WHERE b.fk_soc = s.rowid AND b.fk_user = ".$user->id;
$sql .= " ORDER BY lower(s.nom) ASC";

$resql = $db->query($sql);

if ( $resql )
{
  $num = $db->num_rows($resql);
  $i = 0;
  if ($num)
    {
      print '<table class="noborder" width="100%">';
      print "<tr class=\"liste_titre\"><td colspan=\"2\">".$langs->trans("Bookmarks")."</td></tr>\n";
      $var = True;
      while ($i < $num)
	{
	  $obj = $db->fetch_object($resql);
	  $var = !$var;
	  print "<tr $bc[$var]>";
	  print '<td><a href="fiche.php?socid='.$obj->socid.'">'.$obj->nom.'</a></td>';
	  print '<td align="right"><a href="index.php?action=del_bookmark&amp;bid='.$obj->bid.'">'.img_delete().'</a></td>';
	  print '</tr>';
	  $i++;
	}
      print '</table>';
    }
  $db->free($resql);
}
else
{
  dolibarr_print_error($db);
}


print '</td><td valign="top" width="70%" class="notopnoleftnoright">';


// Derniers clients
if ($user->rights->societe->lire)
{
	$langs->load("boxes");
	$max=5;

	$sql = "SELECT s.nom, s.rowid, ".$db->pdate("s.datec")." as dc";
	if (!$user->rights->commercial->client->voir && !$user->societe_id) $sql .= ", sc.fk_soc, sc.fk_user";
	$sql .= " FROM ".MAIN_DB_PREFIX."societe as s";
	if (!$user->rights->commercial->client->voir && !$user->societe_id) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql .= " WHERE s.client = 1";
	if (!$user->rights->commercial->client->voir && !$user->societe_id) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($user->societe_id > 0)
	{
		$sql .= " AND s.rowid = ".$user->societe_id;
	}
	$sql .= " ORDER BY s.datec DESC ";
	$sql .= $db->plimit($max, 0);

	$result = $db->query($sql);

	if ($result)
	{
		$var=false;
		$num = $db->num_rows($result);

		$i = 0;

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre"><td>'.$langs->trans("BoxTitleLastCustomers",min($max,$num)).'</td>';
		print '<td align="right">'.$langs->trans("Date").'</td>';
		print '</tr>';
		if ($num)
		{
			$var = True;
			$total_ttc = $totalam = $total = 0;

			$customerstatic=new Client($db);
			$var=true;
			while ($i < $num && $i < $max)
			{
				$objp = $db->fetch_object($result);
				$customerstatic->id=$objp->rowid;
				$customerstatic->nom=$objp->nom;
				$var=!$var;
				print '<tr '.$bc[$var].'>';
				print '<td>'.$customerstatic->getNomUrl(1).'</td>';
				print '<td align="right">'.dolibarr_print_date($objp->dc,'day').'</td>';
				print '</tr>';

				$i++;
			}
			
		}
		else
		{
			print '<tr colspan="2" '.$bc[$var].'><td>'.$langs->trans("None").'</td></tr>';
		}
		print '</table><br>';
	}
}


// Derniers fournisseurs
if ($user->rights->societe->lire)
{
	$langs->load("boxes");
	$max=5;

	$sql = "SELECT s.nom, s.rowid, ".$db->pdate("s.datec")." as dc";
	if (!$user->rights->commercial->client->voir && !$user->societe_id) $sql .= ", sc.fk_soc, sc.fk_user";
	$sql .= " FROM ".MAIN_DB_PREFIX."societe as s";
	if (!$user->rights->commercial->client->voir && !$user->societe_id) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql .= " WHERE s.fournisseur = 1";
	if (!$user->rights->commercial->client->voir && !$user->societe_id) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($user->societe_id > 0)
	{
		$sql .= " AND s.rowid = ".$user->societe_id;
	}
	$sql .= " ORDER BY s.datec DESC ";
	$sql .= $db->plimit($max, 0);

	$result = $db->query($sql);

	if ($result)
	{
		$var=false;
		$num = $db->num_rows($result);

		$i = 0;

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre"><td>'.$langs->trans("BoxTitleLastSuppliers",min($max,$num)).'</td>';
		print '<td align="right">'.$langs->trans("Date").'</td>';
		print '</tr>';
		if ($num)
		{
			$total_ttc = $totalam = $total = 0;

			$customerstatic=new Client($db);
			while ($i < $num && $i < $max)
			{
				$objp = $db->fetch_object($result);
				$customerstatic->id=$objp->rowid;
				$customerstatic->nom=$objp->nom;
				print '<tr '.$bc[$var].'>';
				print '<td>'.$customerstatic->getNomUrl(1).'</td>';
				print '<td align="right">'.dolibarr_print_date($objp->dc,'day').'</td>';
				print '</tr>';
				$var=!$var;
				$i++;
			}
			
		}
		else
		{
			print '<tr colspan="2" '.$bc[$var].'><td>'.$langs->trans("None").'</td></tr>';
		}
		print '</table><br>';
	}
}
	
/*
 * Commandes clients � facturer
 */
if ($conf->facture->enabled && $conf->commande->enabled && $user->rights->commande->lire)
{
	$commandestatic=new Commande($db);
	$langs->load("orders");

	$sql = "SELECT sum(f.total) as tot_fht, sum(f.total_ttc) as tot_fttc,";
	$sql.= " s.nom, s.rowid as socid,";
	$sql.= " p.rowid, p.ref, p.facture, p.fk_statut, p.total_ht, p.total_ttc";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user ";
	$sql.= " FROM (".MAIN_DB_PREFIX."societe AS s, ".MAIN_DB_PREFIX."commande AS p";
	if ($user->rights->commercial->client->voir || $socid) $sql .= ")";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc)";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."co_fa AS co_fa ON co_fa.fk_commande = p.rowid";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."facture AS f ON co_fa.fk_facture = f.rowid";
	$sql.= " WHERE p.fk_soc = s.rowid";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid)
	{
		$sql.= " AND p.fk_soc = ".$socid;
	}
	$sql.= " AND p.fk_statut = 3 AND p.facture=0";
	$sql.= " GROUP BY p.rowid";

	$resql = $db->query($sql);

	if ( $resql )
	{
		$var=false;
		$num = $db->num_rows($resql);
		
		if ($num)
		{
			$i = 0;
			print '<table class="noborder" width="100%">';
			print "<tr class=\"liste_titre\">";
			print '<td colspan="2"><a href="'.DOL_URL_ROOT.'/compta/commande/liste.php?status=3&afacturer=1">'.$langs->trans("OrdersToBill").' ('.$num.')</a></td>';
			if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.$langs->trans("AmountHT").'</td>';
			print '<td align="right">'.$langs->trans("AmountTTC").'</td>';
			print '<td align="right">'.$langs->trans("ToBill").'</td>';
			print '<td align="center" width="16">&nbsp;</td>';
			print '</tr>';
			$tot_ht=$tot_ttc=$tot_tobill=0;
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				print "<tr $bc[$var]>";
				print "<td width=\"20%\"><a href=\"commande/fiche.php?id=$obj->rowid\">".img_object($langs->trans("ShowOrder"),"order").'</a>&nbsp;';
				print "<a href=\"commande/fiche.php?id=$obj->rowid\">".$obj->ref.'</a></td>';

				print '<td><a href="fiche.php?socid='.$obj->socid.'">'.img_object($langs->trans("ShowCompany"),"company").'</a>&nbsp;';
				print '<a href="fiche.php?socid='.$obj->socid.'">'.dolibarr_trunc($obj->nom,44).'</a></td>';
				if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.price($obj->total_ht).'</td>';
				print '<td align="right">'.price($obj->total_ttc).'</td>';
				print '<td align="right">'.price($obj->total_ttc-$obj->tot_fttc).'</td>';
				print '<td>'.$commandestatic->LibStatut($obj->fk_statut,$obj->facture,3).'</td>';
				print '</tr>';
				$tot_ht += $obj->total_ht;
				$tot_ttc += $obj->total_ttc;
				//print "x".$tot_ttc."z".$obj->tot_fttc;
				$tot_tobill += ($obj->total_ttc-$obj->tot_fttc);
				$i++;
				$var=!$var;
			}

			print '<tr class="liste_total"><td colspan="2" align="right">'.$langs->trans("Total").' &nbsp; <font style="font-weight: normal">('.$langs->trans("RemainderToBill").': '.price($tot_tobill).')</font> </td>';
			if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.price($tot_ht).'</td>';
			print '<td align="right">'.price($tot_ttc).'</td>';
			print '<td align="right">'.price($tot_tobill).'</td>';
			print '<td>&nbsp;</td>';
			print '</tr>';
			print '</table><br>';
		}
		$db->free($resql);
	}
	else
	{
		dolibarr_print_error($db);
	}
}

/*
 * Factures clients impay�es
 */
if ($conf->facture->enabled && $user->rights->facture->lire)
{
	$facstatic=new Facture($db);

	$sql = "SELECT f.rowid, f.facnumber, f.fk_statut, f.type, f.total, f.total_ttc, ";
	$sql.= $db->pdate("f.date_lim_reglement")." as datelimite,";
	$sql.= " sum(pf.amount) as am,";
	$sql.= " s.nom, s.rowid as socid";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user ";
	$sql.= " FROM ".MAIN_DB_PREFIX."societe as s,".MAIN_DB_PREFIX."facture as f";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."paiement_facture as pf on f.rowid=pf.fk_facture";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE s.rowid = f.fk_soc AND f.paye = 0 AND f.fk_statut = 1";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid) $sql .= " AND f.fk_soc = ".$socid;
	$sql.= " GROUP BY f.rowid, f.facnumber, f.fk_statut, f.total, f.total_ttc, s.nom, s.rowid";
	$sql.= " ORDER BY f.datef ASC, f.facnumber ASC";

	$resql = $db->query($sql);
	if ($resql)
	{
		$var=true;
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre"><td colspan="2"><a href="'.DOL_URL_ROOT.'/compta/facture/impayees.php">'.$langs->trans("BillsCustomersUnpayed",min($conf->liste_limit,$num)).' ('.$num.')</a></td>';
		if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.$langs->trans("AmountHT").'</td>';
		print '<td align="right">'.$langs->trans("AmountTTC").'</td>';
		print '<td align="right">'.$langs->trans("Received").'</td>';
		print '<td width="16">&nbsp;</td>';
		print '</tr>';
		if ($num)
		{
			$total_ttc = $totalam = $total = 0;
			while ($i < $num && $i < $conf->liste_limit)
			{
				$obj = $db->fetch_object($resql);

				print '<tr '.$bc[$var].'>';
				print '<td nowrap>';
				$facturestatic->ref=$obj->facnumber;
				$facturestatic->id=$obj->rowid;
				$facturestatic->type=$obj->type;
				print $facturestatic->getNomUrl(1,'');
				if ($obj->datelimite < (time() - $conf->facture->client->warning_delay)) print img_warning($langs->trans("Late"));
				print '</td>';
				print '<td><a href="fiche.php?socid='.$obj->socid.'">'.img_object($langs->trans("ShowCustomer"),"company").' '.dolibarr_trunc($obj->nom,44).'</a></td>';
				if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.price($obj->total).'</td>';
				print '<td align="right">'.price($obj->total_ttc).'</td>';
				print '<td align="right">'.price($obj->am).'</td>';
				print '<td>'.$facstatic->LibStatut($obj->paye,$obj->fk_statut,3,$obj->am).'</td>';
				print '</tr>';

				$total_ttc +=  $obj->total_ttc;
				$total += $obj->total;
				$totalam +=  $obj->am;
				$var=!$var;
				$i++;
			}

			print '<tr class="liste_total"><td colspan="2" align="right">'.$langs->trans("Total").' &nbsp; <font style="font-weight: normal">('.$langs->trans("RemainderToTake").': '.price($total_ttc-$totalam).')</font> </td>';
			if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.price($total).'</td>';
			print '<td align="right">'.price($total_ttc).'</td>';
			print '<td align="right">'.price($totalam).'</td>';
			print '<td>&nbsp;</td>';
			print '</tr>';
		}
		else
		{
			$colspan=5;
			if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) $colspan++;
			print '<tr '.$bc[$var].'><td colspan="'.$colspan.'">'.$langs->trans("NoInvoice").'</td></tr>';
		}
		print '</table><br>';
		$db->free($resql);
	}
	else
	{
		dolibarr_print_error($db);
	}
}

/*
 * Factures fournisseurs impay�es
 */
if ($conf->facture->enabled && $user->rights->facture->lire)
{
	$facstatic=new FactureFournisseur($db);

	$sql = "SELECT ff.rowid, ff.facnumber, ff.fk_statut, ff.fk_statut, ff.libelle, ff.total_ht, ff.total_ttc,";
	$sql.= " sum(pf.amount) as am,";
	$sql.= " s.nom, s.rowid as socid";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user ";
	$sql.= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture_fourn as ff";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."paiementfourn_facturefourn as pf on ff.rowid=pf.fk_facturefourn";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE s.rowid = ff.fk_soc";
	$sql.= " AND ff.paye=0 AND ff.fk_statut = 1";
	if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid) $sql .= " AND f.fk_soc = ".$socid;
	$sql.= " GROUP BY ff.rowid, ff.facnumber, ff.fk_statut, ff.total, ff.total_ttc, s.nom, s.rowid";

	$resql=$db->query($sql);
	if ($resql)
	{
		$var=false;
		$num = $db->num_rows($resql);

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("BillsSuppliersUnpayed").' ('.$num.')</td>';
		if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.$langs->trans("AmountHT").'</td>';
		print '<td align="right">'.$langs->trans("AmountTTC").'</td>';
		print '<td align="right">'.$langs->trans("Payed").'</td>';
		print '<td width="16">&nbsp;</td>';
		print "</tr>\n";
		if ($num)
		{
			$i = 0;
			$total = $total_ttc = $totalam = 0;
			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				print '<tr '.$bc[$var].'><td>';
				$facstatic->ref=$obj->facnumber;
				$facstatic->id=$obj->rowid;
				print $facstatic->getNomUrl(1,'');
				print '</td>';
				print '<td><a href="fiche.php?socid='.$obj->socid.'">'.img_object($langs->trans("ShowSupplier"),"company").' '.dolibarr_trunc($obj->nom,44).'</a></td>';
				if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.price($obj->total_ht).'</td>';
				print '<td align="right">'.price($obj->total_ttc).'</td>';
				print '<td align="right">'.price($obj->am).'</td>';
				print '<td>'.$facstatic->LibStatut($obj->paye,$obj->fk_statut,3).'</td>';
				print '</tr>';
				$total += $obj->total_ht;
				$total_ttc +=  $obj->total_ttc;
				$totalam +=  $obj->am;
				$i++;
				$var = !$var;
			}

			print '<tr class="liste_total"><td colspan="2" align="right">'.$langs->trans("Total").' &nbsp; <font style="font-weight: normal">('.$langs->trans("RemainderToPay").': '.price($total_ttc-$totalam).')</font> </td>';
			if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) print '<td align="right">'.price($total).'</td>';
			print '<td align="right">'.price($total_ttc).'</td>';
			print '<td align="right">'.price($totalam).'</td>';
			print '<td>&nbsp;</td>';
			print '</tr>';
		}
		else
		{
			$colspan=5;
			if ($conf->global->MAIN_SHOW_HT_ON_SUMMARY) $colspan++;
			print '<tr '.$bc[$var].'><td colspan="'.$colspan.'">'.$langs->trans("NoInvoice").'</td></tr>';
		}
		print '</table><br>';
	}
	else
	{
		dolibarr_print_error($db);
	}
}



// \todo Mettre ici recup des actions en rapport avec la compta
$resql = 0;
if ($resql)
{
  print '<table class="noborder" width="100%">';
  print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("TasksToDo").'</td>';
  print "</tr>\n";
  $var = True;
  $i = 0;
  while ($i < $db->num_rows($resql) )
    {
      $obj = $db->fetch_object($resql);
      $var=!$var;

      print "<tr $bc[$var]><td>".dolibarr_print_date($obj->da,"day")."</td>";
      print "<td><a href=\"action/fiche.php\">$obj->libelle $obj->label</a></td></tr>";
      $i++;
    }
  $db->free($resql);
  print "</table><br>";
}

print '</td></tr>';

print '</table>';

$db->close();


llxFooter('$Date$ - $Revision$');
?>
