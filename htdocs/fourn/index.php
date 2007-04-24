<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2007 Regis Houssin        <regis.houssin@cap-networks.com>
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
   \file       htdocs/fourn/index.php
   \ingroup    fournisseur
   \brief      Page accueil de la zone fournisseurs
   \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/fourn/fournisseur.commande.class.php');
require_once(DOL_DOCUMENT_ROOT.'/fourn/fournisseur.facture.class.php');

if (!$user->rights->societe->lire)
  accessforbidden();

$langs->load("suppliers");
$langs->load("orders");
$langs->load("companies");

// S�curit� acc�s client
$socid='';
if ($user->societe_id > 0) 
{
  $action = '';
  $socid = $user->societe_id;
}


/*
* Affichage page
*/

$commandestatic=new CommandeFournisseur($db);
$facturestatic=new FactureFournisseur($db);
$companystatic=new Societe($db);

llxHeader("",$langs->trans("SuppliersArea"));

print_fiche_titre($langs->trans("SuppliersArea"));

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';


/*
 * Liste des categories
 * \TODO Il n'y a aucun �cran pour les saisir !
 */
$sql = "SELECT rowid, label";
$sql.= " FROM ".MAIN_DB_PREFIX."fournisseur_categorie";
$sql.= " ORDER BY label ASC";

$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
	$i = 0;

	if ($num)
	{
		print '<table class="liste" width="100%">';
		print '<tr class="liste_titre"><td colspan="2">';
		print $langs->trans("Category");
		print "</td></tr>\n";
		$var=True;

		while ($obj = $db->fetch_object($resql))
		{
			$var=!$var;
			print "<tr $bc[$var]>\n";
			print '<td><a href="liste.php?cat='.$obj->rowid.'">'.stripslashes($obj->label).'</a>';
			print '</td><td align="right">';
			print '<a href="stats.php?cat='.$obj->rowid.'">('.$langs->trans("Stats").')</a>';
			print "</tr>\n";
		}
		print "</table>\n";
		print "<br>\n";
	}
	
	$db->free($resql);
}
else 
{
	dolibarr_print_error($db);
}


/*
 *
 */
$commande = new CommandeFournisseur($db);
$sql = "SELECT count(cf.rowid), fk_statut,";
$sql.= " cf.rowid,cf.ref";
$sql.= " FROM ".MAIN_DB_PREFIX."societe as s,";
$sql.= " ".MAIN_DB_PREFIX."commande_fournisseur as cf";
$sql.= " WHERE cf.fk_soc = s.idp ";
$sql.= " GROUP BY cf.fk_statut";

$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
	$i = 0;

	print '<table class="liste" width="100%">';
	print '<tr class="liste_titre"><td>'.$langs->trans("Orders").'</td><td align="center">'.$langs->trans("Nb").'</td><td>&nbsp;</td>';
	print "</tr>\n";
	$var=True;

	while ($i < $num)
	{
		$row = $db->fetch_row($resql);
		$var=!$var;

		print "<tr $bc[$var]>";
		print '<td>'.$commande->statuts[$row[1]].'</td>';
		print '<td align="center">'.$row[0].'</td>';
		print '<td align="center"><a href="'.DOL_URL_ROOT.'/fourn/commande/liste.php?statut='.$row[1].'">'.$commande->LibStatut($row[1],3).'</a></td>';

		print "</tr>\n";
		$i++;
	}
	print "</table>";
	print "<br>\n";
	$db->free($resql);
}
else 
{
  dolibarr_print_error($db);
}


/*
 * Commandes brouillons
 */
if ($conf->fournisseur->enabled)
{
    $langs->load("orders");
    $sql = "SELECT c.rowid, c.ref, c.total_ttc, s.nom, s.idp";
    $sql.= " FROM ".MAIN_DB_PREFIX."commande_fournisseur as c, ".MAIN_DB_PREFIX."societe as s";
    $sql.= " WHERE c.fk_soc = s.idp AND c.fk_statut = 0";
    if ($socid)
    {
        $sql .= " AND c.fk_soc = ".$socid;
    }

    $resql = $db->query($sql);
    if ($resql)
    {
        $total = 0;
        $num = $db->num_rows($resql);
        if ($num)
        {
            print '<table class="noborder" width="100%">';
            print '<tr class="liste_titre">';
            print '<td colspan="3">'.$langs->trans("DraftOrders").'</td></tr>';

            $i = 0;
            $var = true;
            while ($i < $num)
            {
                $var=!$var;
                $obj = $db->fetch_object($resql);
                print "<tr $bc[$var]><td nowrap>";
				$commandestatic->id=$obj->rowid;
				$commandestatic->ref=$obj->ref;
				print $commandestatic->getNomUrl(1,'',16);
				print '</td>';
                print '<td>';
				$companystatic->id=$obj->idp;
				$companystatic->nom=$obj->nom;
				$companystatic->client=0;
				print $companystatic->getNomUrl(1,'',16);
				print '</td>';
                print '<td align="right" nowrap="nowrap">'.price($obj->total_ttc).'</td></tr>';
                $i++;
                $total += $obj->total_ttc;
            }
            if ($total>0)
            {
                $var=!$var;
                print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" align="right">'.price($total)."</td></tr>";
            }
            print "</table>";
			print "<br>\n";
        }
    }
}

/**
 * Factures brouillons
 */
if ($conf->fournisseur->enabled && $user->rights->fournisseur->facture->lire)
{
	$sql  = "SELECT f.facnumber, f.rowid, f.total_ttc, f.type,";
	$sql.= " s.nom, s.idp";
	$sql .= " FROM ".MAIN_DB_PREFIX."facture_fourn as f, ".MAIN_DB_PREFIX."societe as s";
	$sql .= " WHERE s.idp = f.fk_soc AND f.fk_statut = 0";
	if ($socid)
	{
		$sql .= " AND f.fk_soc = ".$socid;
	}

	$resql = $db->query($sql);

	if ( $resql )
	{
		$num = $db->num_rows($resql);
		if ($num)
		{
			print '<table class="noborder" width="100%">';
			print '<tr class="liste_titre">';
			print '<td colspan="3">'.$langs->trans("DraftBills").' ('.$num.')</td></tr>';
			$i = 0;
			$tot_ttc = 0;
			$var = True;
			while ($i < $num && $i < 20)
			{
				$obj = $db->fetch_object($resql);
				$var=!$var;
				print '<tr '.$bc[$var].'><td nowrap>';
				$facturestatic->ref=$obj->facnumber;
				$facturestatic->id=$obj->rowid;
				$facturestatic->type=$obj->type;
				print $facturestatic->getNomUrl(1,'');
				print '</td>';
				print '<td>';
				$companystatic->id=$obj->idp;
				$companystatic->nom=$obj->nom;
				$companystatic->client=0;
				print $companystatic->getNomUrl(1,'',16);
				print '</td>';
				print '<td align="right">'.price($obj->total_ttc).'</td>';
				print '</tr>';
				$tot_ttc+=$obj->total_ttc;
				$i++;
			}

			print '<tr class="liste_total"><td align="left">'.$langs->trans("Total").'</td>';
			print '<td colspan="2" align="right">'.price($tot_ttc).'</td>';
			print '</tr>';

			print "</table>";
			print "<br>\n";
		}
		$db->free($resql);
	}
	else
	{
		dolibarr_print_error($db);
	}
}

/*
 *
 *
 */
print "</td>\n";
print '<td valign="top" width="70%" class="notopnoleft">';

/*
 * Liste des 10 derniers saisis
 *
 */
$sql = "SELECT s.idp, s.nom, s.ville,".$db->pdate("s.datec")." as datec, ".$db->pdate("s.datea")." as datea,  st.libelle as stcomm, s.prefix_comm";
$sql.= " , code_fournisseur, code_compta_fournisseur";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user ";
$sql.= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."c_stcomm as st";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql.= " WHERE s.fk_stcomm = st.id AND s.fournisseur=1";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND s.idp = sc.fk_soc AND sc.fk_user = " .$user->id;
if ($socid) $sql .= " AND s.idp=".$socid;

$sql .= " ORDER BY s.datec DESC LIMIT 10; ";

$resql = $db->query($sql);
if ($resql)
{
  $num = $db->num_rows($resql);
  $i = 0;
  
  print '<table class="liste" width="100%">';
  print '<tr class="liste_titre">';
  print "<td>".$langs->trans("Company")."</td>\n";
  print "<td>".$langs->trans("SupplierCode")."</td>\n";
  print '<td align="right">'.$langs->trans("DateCreation")."</td>\n";
  print "</tr>\n";

  $var=True;

  while ($obj = $db->fetch_object($resql) )
    {
      $var=!$var;

      print "<tr $bc[$var]>";
      print '<td><a href="fiche.php?socid='.$obj->idp.'">'.img_object($langs->trans("ShowSupplier"),"company").'</a>';
      print "&nbsp;<a href=\"fiche.php?socid=$obj->idp\">$obj->nom</a></td>\n";
      print '<td align="left">'.$obj->code_fournisseur.'&nbsp;</td>';
      print '<td align="right">'.dolibarr_print_date($obj->datec,'day').'</td>';
      print "</tr>\n";
    }
  print "</table>\n";

  $db->free($resql);
}
else 
{
  dolibarr_print_error($db);
}

print "</td></tr>\n";
print "</table>\n";

$db->close();

llxFooter('$Date$ - $Revision$');
?>
