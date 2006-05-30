<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/fourn/facture/index.php
        \ingroup    fournisseur,facture
        \brief      Lsite des factures fournisseurs
        \version    $Revision$
*/

require("./pre.inc.php");

if (!$user->rights->fournisseur->facture->lire)
  accessforbidden();


$socidp = $_GET["socidp"];

// S�curit� acc�s client
if ($user->societe_id > 0) 
{
  $_GET["action"] = '';
  $socidp = $user->societe_id;
}

$page=$_GET["page"];
$sortorder = $_GET["sortorder"];
$sortfield = $_GET["sortfield"];
 
if ($page == -1) { $page = 0 ; }
$limit = $conf->liste_limit;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="DESC";
if (! $sortfield) $sortfield="fac.datef";


/*
 * Recherche
 *
 */
if ($_POST["mode"] == 'search')
{
  if ($_POST["mode-search"] == 'soc')
    {
      $sql = "SELECT s.idp FROM ".MAIN_DB_PREFIX."societe as s ";
      $sql.= " WHERE s.nom like '%".addslashes(strtolower($socname))."%'";
    }
      
  if ( $db->query($sql) )
    {
      if ( $db->num_rows() == 1)
	{
	  $obj = $db->fetch_object();
	  $socidp = $obj->idp;
	}
      $db->free();
    }
}
  



/*
 * Mode Liste
 *
 */
 
llxHeader();

$sql = "SELECT s.idp as socid, s.nom, ".$db->pdate("fac.date_lim_reglement")." as date_echeance, fac.total_ht";
$sql .= ", fac.total_ttc, fac.paye as paye, fac.fk_statut as fk_statut, fac.libelle, fac.rowid as facid, fac.facnumber";
if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", sc.fk_soc, sc.fk_user ";
$sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture_fourn as fac";
if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
$sql .= " WHERE fac.fk_soc = s.idp";
if (!$user->rights->commercial->client->voir && !$socidp) $sql .= " AND s.idp = sc.fk_soc AND sc.fk_user = " .$user->id;
if ($socidp)
{
  $sql .= " AND s.idp = $socidp";
}
if ($_GET["filtre"])
  {
    $filtrearr = split(",", $_GET["filtre"]);
    foreach ($filtrearr as $fil)
      {
	$filt = split(":", $fil);
	$sql .= " AND " . $filt[0] . " = " . $filt[1];
      }
  }

if ($_GET["search_ref"])
  {
    $sql .= " AND fac.facnumber like '%".addslashes($_GET["search_ref"])."%'";
  }

if ($_GET["search_libelle"])
  {
    $sql .= " AND fac.libelle like '%".addslashes($_GET["search_libelle"])."%'";
  }

if ($_GET["search_societe"])
  {
    $sql .= " AND s.nom like '%".addslashes($_GET["search_societe"])."%'";
  }

if ($_GET["search_montant_ht"])
  {
    $sql .= " AND fac.total_ht = '".addslashes($_GET["search_montant_ht"])."'";
  }

if ($_GET["search_montant_ttc"])
  {
    $sql .= " AND fac.total_ttc = '".addslashes($_GET["search_montant_ttc"])."'";
  }

$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit( $limit+1, $offset);

$resql = $db->query($sql);

if ($resql)
{
    $num = $db->num_rows($resql);
    $i = 0;

    if ($socidp) {
        $soc = new Societe($db);
        $soc->fetch($socidp);
    }

    print_barre_liste($langs->trans("BillsSuppliers").($socidp?" $soc->nom":""),$page,"index.php","&amp;socidp=$socidp",$sortfield,$sortorder,'',$num);

    print '<table class="liste" width="100%">';
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("Ref"),"index.php","facnumber","&amp;socidp=$socidp","","",$sortfield);
    print_liste_field_titre($langs->trans("DateEcheance"),"index.php","fac.datef","&amp;socidp=$socidp","","",$sortfield);
    print_liste_field_titre($langs->trans("Label"),"index.php","fac.libelle","&amp;socidp=$socidp","","",$sortfield);
    print_liste_field_titre($langs->trans("Company"),"index.php","s.nom","&amp;socidp=$socidp","","",$sortfield);
    print_liste_field_titre($langs->trans("AmountHT"),"index.php","fac.total_ht","&amp;socidp=$socidp","",'align="right"',$sortfield);
    print_liste_field_titre($langs->trans("AmountTTC"),"index.php","fac.total_ttc","&amp;socidp=$socidp","",'align="right"',$sortfield);
    print_liste_field_titre($langs->trans("Status"),"index.php","fk_statut,paye","&amp;socidp=$socidp","",'align="center"',$sortfield);
    print "</tr>\n";

    // Lignes des champs de filtre
    print '<form method="get" action="index.php">';
    print '<tr class="liste_titre">';
    print '<td class="liste_titre" valign="right">';
    print '<input class="flat" size="10" type="text" name="search_ref" value="'.$_GET["search_ref"].'">';
    print '</td><td class="liste_titre">&nbsp;</td>';
    print '<td class="liste_titre" align="left">';
    print '<input class="flat" type="text" name="search_libelle" value="'.$_GET["search_libelle"].'">';
    print '</td>';
    print '<td class="liste_titre" align="left">';
    print '<input class="flat" type="text" name="search_societe" value="'.$_GET["search_societe"].'" size="12">';
    print '</td><td class="liste_titre" align="right">';
    print '<input class="flat" type="text" size="8" name="search_montant_ht" value="'.$_GET["search_montant_ht"].'">';
    print '</td><td class="liste_titre" align="right">';
    print '<input class="flat" type="text" size="8" name="search_montant_ttc" value="'.$_GET["search_montant_ttc"].'">';
    print '</td><td class="liste_titre" colspan="2" align="center">';
    print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" alt="'.$langs->trans("Search").'">';
    print '</td>';
    print "</tr>\n";
    print '</form>';

    $facturestatic = new FactureFournisseur($db);

    $var=true;
    $total=0;
    $total_ttc=0;
    while ($i < min($num,$limit))
      {
        $obj = $db->fetch_object($resql);
        $var=!$var;

        print "<tr $bc[$var]>";
        print '<td nowrap><a href="'.DOL_URL_ROOT.'/fourn/facture/fiche.php?facid='.$obj->facid.'" title="'.$obj->facnumber.'">'.img_object($langs->trans("ShowBill"),"bill").' '.dolibarr_trunc($obj->facnumber,12)."</a></td>\n";
        print '<td align="center" nowrap>'.dolibarr_print_date($obj->date_echeance);
        if (($obj->fk_statut > 0) && $obj->datefin < (time() - $conf->facture->client->warning_delay)) print img_picto($langs->trans("Late"),"warning");
        print '</td>';
        print '<td>'.dolibarr_trunc(stripslashes("$obj->libelle"),36).'</td>';
        print '<td>';
        print '<a href="'.DOL_URL_ROOT.'/fourn/fiche.php?socid='.$obj->socid.'">'.img_object($langs->trans("ShowSupplier"),"company").' '.$obj->nom.'</a</td>';
        print '<td align="right">'.price($obj->total_ht).'</td>';
        print '<td align="right">'.price($obj->total_ttc).'</td>';
        $total+=$obj->total_ht;
        $total_ttc+=$obj->total_ttc;

        // Affiche statut de la facture
        print '<td align="right" nowrap="nowrap">';
	      // \todo  le montant deja pay� obj->am n'est pas d�finie
		print $facturestatic->LibStatut($obj->paye,$obj->fk_statut,5,$objp->am);
        print '</td>';
	
        print "</tr>\n";
        $i++;
	
        if ($i == min($num,$limit))
        {
		  // Print total
		  print '<tr class="liste_total">';
		  print '<td class="liste_total" colspan="4" align="left">'.$langs->trans("Total").'</td>';
		  print '<td class="liste_total" align="right">'.price($total).'</td>';
		  print '<td class="liste_total" align="right">'.price($total_ttc).'</td>';
		  print '<td class="liste_total" align="center">&nbsp;</td>';
		  print "</tr>\n";
        }
      }
    
    print "</table>";
    $db->free($resql);
}
else
{
  dolibarr_print_error($db);
}

$db->close();


llxFooter('$Date$ - $Revision$');
?>
