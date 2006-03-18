<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
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
        \file       htdocs/comm/fiche.php
        \ingroup    commercial
        \brief      Onglet client de la fiche societe
        \version    $Revision$
*/

require_once("./pre.inc.php");

if (!$user->rights->societe->lire) accessforbidden();

require_once(DOL_DOCUMENT_ROOT."/contact.class.php");
require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
require_once(DOL_DOCUMENT_ROOT."/actioncomm.class.php");
require_once(DOL_DOCUMENT_ROOT."/commande/commande.class.php");
require_once(DOL_DOCUMENT_ROOT."/contrat/contrat.class.php");

$langs->load("companies");
$langs->load("orders");
$langs->load("bills");
$langs->load("contracts");
if ($conf->fichinter->enabled) $langs->load("interventions");

$user->getrights("commercial");

$socidp = isset($_GET["socid"])?$_GET["socid"]:'';
if ($socidp == '') accessforbidden();

// Protection quand utilisateur externe
if ($user->societe_id > 0)
{
    $socidp = $user->societe_id;
}

// Protection restriction commercial
if (!$user->rights->commercial->client->voir && $socidp && !$user->societe_id > 0)
{
        $sql = "SELECT sc.fk_soc, s.client";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe_commerciaux as sc, ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE sc.fk_soc = ".$socidp." AND sc.fk_user = ".$user->id." AND s.client = 1";

        if ( $db->query($sql) )
        {
          if ( $db->num_rows() == 0) accessforbidden();
        }
}


$sortorder=$_GET["sortorder"];
$sortfield=$_GET["sortfield"];
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="nom";


if ($_GET["action"] == 'attribute_prefix')
{
    $societe = new Societe($db, $_GET["socid"]);
    $societe->attribute_prefix($db, $_GET["socid"]);
}
// conditions de r�glement
if ($_POST["action"] == 'setconditions')
{
    
	$societe = new Societe($db, $_GET["socid"]);
    $societe->cond_reglement=$_POST['cond_reglement_id'];
	$sql = "UPDATE ".MAIN_DB_PREFIX."societe SET cond_reglement='".$_POST['cond_reglement_id'];
	$sql.= "' WHERE idp='".$_GET["socid"]."'";
    $result = $db->query($sql);
    if (! $result) dolibarr_print_error($result);
}
// mode de r�glement
if ($_POST["action"] == 'setmode')
{
    $societe = new Societe($db, $_GET["socid"]);
    $societe->mode_reglement=$_POST['mode_reglement_id'];
	$sql = "UPDATE ".MAIN_DB_PREFIX."societe SET mode_reglement='".$_POST['mode_reglement_id'];
	$sql.= "' WHERE idp='".$_GET["socid"]."'";
    $result = $db->query($sql);
    if (! $result) dolibarr_print_error($result);
}
// assuj�tissement � la TVA
if ($_POST["action"] == 'setassujtva')
{
	$societe = new Societe($db, $_GET["socid"]);
    $societe->tva_assuj=$_POST['assujtva_value'];
	$sql = "UPDATE ".MAIN_DB_PREFIX."societe SET tva_assuj='".$_POST['assujtva_value']."' WHERE idp='".$socidp."'";
    $result = $db->query($sql);
    if (! $result) dolibarr_print_error($result);
}

if ($action == 'stcomm')
{
    if ($stcommid <> 'null' && $stcommid <> $oldstcomm)
    {
        $sql = "INSERT INTO socstatutlog (datel, fk_soc, fk_statut, author) ";
        $sql .= " VALUES ('$dateaction',$socidp,$stcommid,'" . $user->login . "')";
        $result = @$db->query($sql);

        if ($result)
        {
            $sql = "UPDATE ".MAIN_DB_PREFIX."societe SET fk_stcomm=$stcommid WHERE idp=".$socidp;
            $result = $db->query($sql);
        }
        else
        {
            $errmesg = "ERREUR DE DATE !";
        }
    }

    if ($actioncommid)
    {
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (datea, fk_action, fk_soc, fk_user_author) VALUES ('$dateaction',$actioncommid,$socidp,'" . $user->id . "')";
        $result = @$db->query($sql);

        if (!$result)
        {
            $errmesg = "ERREUR DE DATE !";
        }
    }
}

/*
 * Recherche
 *
 */
if ($mode == 'search') {
    if ($mode-search == 'soc') {
        $sql = "SELECT s.idp";
        if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", sc.fk_soc, sc.fk_user ";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe as s";
        if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql .= " WHERE lower(s.nom) like '%".strtolower($socname)."%'";
        if (!$user->rights->commercial->client->voir && !$socidp) $sql .= " AND s.idp = sc.fk_soc AND sc.fk_user = " .$user->id;
    }

    if ( $db->query($sql) ) {
        if ( $db->num_rows() == 1) {
            $obj = $db->fetch_object();
            $socidp = $obj->idp;
        }
        $db->free();
    }
}



llxHeader('',$langs->trans('CustomerCard'));


/*********************************************************************************
 *
 * Mode fiche
 *
 *********************************************************************************/
if ($socidp > 0)
{
    // On recupere les donnees societes par l'objet
    $objsoc = new Societe($db);
    $objsoc->id=$socidp;
    $objsoc->fetch($socidp,$to);

    $dac = strftime("%Y-%m-%d %H:%M", time());
    if ($errmesg)
    {
        print "<b>$errmesg</b><br>";
    }

    /*
     * Affichage onglets
     */
    $h = 0;

    $head[$h][0] = DOL_URL_ROOT.'/soc.php?socid='.$objsoc->id;
    $head[$h][1] = $langs->trans("Company");
    $h++;

    if ($objsoc->client==1)
    {
        $hselected=$h;
        $head[$h][0] = DOL_URL_ROOT.'/comm/fiche.php?socid='.$objsoc->id;
        $head[$h][1] = $langs->trans("Customer");;
        $h++;
    }
    if ($objsoc->client==2)
    {
        $hselected=$h;
        $head[$h][0] = DOL_URL_ROOT.'/comm/prospect/fiche.php?id='.$obj->socid;
        $head[$h][1] = $langs->trans("Prospect");
        $h++;
    }
    if ($objsoc->fournisseur)
    {
        $head[$h][0] = DOL_URL_ROOT.'/fourn/fiche.php?socid='.$objsoc->id;
        $head[$h][1] = $langs->trans("Supplier");
        $h++;
    }

    if ($conf->compta->enabled || $conf->comptaexpert->enabled)
    {
        $langs->load("compta");
        $head[$h][0] = DOL_URL_ROOT.'/compta/fiche.php?socid='.$objsoc->id;
        $head[$h][1] = $langs->trans("Accountancy");
        $h++;
    }

    $head[$h][0] = DOL_URL_ROOT.'/socnote.php?socid='.$objsoc->id;
    $head[$h][1] = $langs->trans("Note");
    $h++;

    if ($user->societe_id == 0)
    {
        $head[$h][0] = DOL_URL_ROOT.'/docsoc.php?socid='.$objsoc->id;
        $head[$h][1] = $langs->trans("Documents");
        $h++;
    }

    $head[$h][0] = DOL_URL_ROOT.'/societe/notify/fiche.php?socid='.$objsoc->id;
    $head[$h][1] = $langs->trans("Notifications");
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/societe/info.php?socid='.$objsoc->id;
    $head[$h][1] = $langs->trans("Info");
    $h++;

    if ($user->societe_id == 0)
    {
        $head[$h][0] = DOL_URL_ROOT."/bookmarks/fiche.php?action=add&amp;socid=".$objsoc->id."&amp;urlsource=".$_SERVER["PHP_SELF"]."?socid=".$objsoc->id;
        $head[$h][1] = img_object($langs->trans("BookmarkThisPage"),'bookmark');
        $head[$h][2] = 'image';
        $h++;
    }

    dolibarr_fiche_head($head, $hselected, $objsoc->nom);


    /*
     *
     *
     */
    print '<table width="100%" border="0">';
    print '<tr><td valign="top">';
    print '<table class="border" width="100%">';

    print '<tr><td width="30%">'.$langs->trans("Name").'</td><td width="70%" colspan="3">';
    print $objsoc->nom;
    print '</td></tr>';

    print '<tr><td>'.$langs->trans('Prefix').'</td><td colspan="3">'.$objsoc->prefix_comm.'</td></tr>';

    if ($objsoc->client)
    {
        print '<tr><td>';
        print $langs->trans('CustomerCode').'</td><td colspan="3">';
        print $objsoc->code_client;
        if ($objsoc->check_codeclient() <> 0) print ' '.$langs->trans("WrongCustomerCode");
        print '</td></tr>';
    }
    if ($conf->compta->enabled || $conf->comptaexpert->enabled)
    {
        print '<tr>';
        print '<td nowrap>'.$langs->trans("CustomerAccountancyCode").'</td><td colspan="3">'.$objsoc->code_compta.'</td>';
        print '</tr>';
    }

    /*
    if ($objsoc->fournisseur) {
        print '<tr><td>';
        print $langs->trans('SupplierCode').'</td><td colspan="3">';
        print $objsoc->code_fournisseur;
        if ($objsoc->check_codefournisseur() <> 0) print ' '.$langs->trans("WrongSupplierCode");
        print '</td></tr>';
    }
    */
    
    print "<tr><td valign=\"top\">".$langs->trans('Address')."</td><td colspan=\"3\">".nl2br($objsoc->adresse)."</td></tr>";

    print '<tr><td>'.$langs->trans('Zip').'</td><td>'.$objsoc->cp."</td>";
    print '<td>'.$langs->trans('Town').'</td><td>'.$objsoc->ville."</td></tr>";
    if ($objsoc->pays) {
    	print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">'.$objsoc->pays.'</td></tr>';
    }

    print '<tr><td>'.$langs->trans('Phone').'</td><td>'.dolibarr_print_phone($objsoc->tel,$objsoc->pays_code).'</td>';
    print '<td>'.$langs->trans('Fax').'</td><td>'.dolibarr_print_phone($objsoc->fax,$objsoc->pays_code).'</td></tr>';

    print '<tr><td>'.$langs->trans("Web")."</td><td colspan=\"3\"><a href=\"http://$objsoc->url\" target=\"_blank\">".$objsoc->url."</a>&nbsp;</td></tr>";
	if ($objsoc->siren || $objsoc->siret) {
    	print "<tr><td nowrap>".$langs->transcountry("ProfId1",$objsoc->pays_code)."</td><td><a href=\"http://www.societe.com/cgi-bin/recherche?rncs=".$objsoc->siren."\" target=\"_blank\">".$objsoc->siren."</a>&nbsp;</td>";
    print '<td>'.$langs->transcountry('ProfId2',$objsoc->pays_code).'</td><td>'.$objsoc->siret.'</td></tr>';
    }
	if ($objsoc->ape) {
    	print '<tr><td>'.$langs->transcountry('ProfId3',$objsoc->pays_code).'</td><td>'.$objsoc->ape.'</td><td colspan="2">&nbsp;</td></tr>';
    }

    // Type + Staff
    $arr = $objsoc->typent_array($objsoc->typent_id);
    $objsoc->typent= $arr[$objsoc->typent_id];
    if ($objsoc->typent || $objsoc->effectif) {
    	print '<tr><td>'.$langs->trans("Type").'</td><td>'.$objsoc->typent.'</td><td>'.$langs->trans("Staff").'</td><td nowrap>'.$objsoc->effectif.'</td></tr>';
    }

	// Assujeti � TVA ou pas
	print '<tr>';
/*	print '<table width="100%" class="nobordernopadding"><tr><td nowrap>';
	print $langs->trans('VATIsUsed');
	print '<td>';
	if ($_GET['action'] != 'editassujtva') print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editassujtva&amp;socid='.$objsoc->id.'">'.img_edit($langs->trans('SetMode'),1).'</a></td>';
		print '</tr></table>';
		print '</td><td colspan="3">';
	if ($_GET['action'] == 'editassujtva')
		{
			$html->form_assujetti_tva($_SERVER['PHP_SELF'].'?socid='.$objsoc->id,$objsoc->tva_assuj,'assujtva_value');
		}
		else
		{
			$html->form_assujetti_tva($_SERVER['PHP_SELF'].'?socid='.$objsoc->id,$objsoc->tva_assuj,'none');
		}
	print "</td>";
*/
	print '<td nowrap="nowrap">'.$langs->trans('VATIsUsed').'</td><td colspan="3">';
	print yn($objsoc->tva_assuj);
	print '</td>';
	print '</tr>';

	// Conditions de r�glement par d�faut
	$langs->load('bills');
	$html = new Form($db);
	print '<tr><td nowrap>';
	print '<table width="100%" class="nobordernopadding"><tr><td nowrap>';
	print $langs->trans('PaymentConditions');
	print '<td>';
	if ($_GET['action'] != 'editconditions') print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editconditions&amp;socid='.$objsoc->id.'">'.img_edit($langs->trans('SetConditions'),1).'</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($_GET['action'] == 'editconditions')
	{
		$html->form_conditions_reglement($_SERVER['PHP_SELF'].'?socid='.$objsoc->id,$objsoc->cond_reglement,'cond_reglement_id',-1,1);
	}
	else
	{
		$html->form_conditions_reglement($_SERVER['PHP_SELF'].'?socid='.$objsoc->id,$objsoc->cond_reglement,'none');
	}
	print "</td>";
	print '</tr>';

	// Mode de r�glement
	print '<tr><td nowrap>';
	print '<table width="100%" class="nobordernopadding"><tr><td nowrap>';
	print $langs->trans('PaymentMode');
	print '<td>';
	if ($_GET['action'] != 'editmode') print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editmode&amp;socid='.$objsoc->id.'">'.img_edit($langs->trans('SetMode'),1).'</a></td>';
	print '</tr></table>';
	print '</td><td colspan="3">';
	if ($_GET['action'] == 'editmode')
	{
		$html->form_modes_reglement($_SERVER['PHP_SELF'].'?socid='.$objsoc->id,$objsoc->mode_reglement,'mode_reglement_id');
	}
	else
	{
		$html->form_modes_reglement($_SERVER['PHP_SELF'].'?socid='.$objsoc->id,$objsoc->mode_reglement,'none');
	}
	print "</td>";
	print '</tr>';

    // Remise permanente
    print '<tr><td nowrap>';
    print '<table width="100%" class="nobordernopadding"><tr><td nowrap>';
    print $langs->trans("CustomerRelativeDiscount");
    print '<td><td align="right">';
    print '<a href="'.DOL_URL_ROOT.'/comm/remise.php?id='.$objsoc->id.'">'.img_edit($langs->trans("Modify")).'</a>';
    print '</td></tr></table>';
    print '</td><td colspan="3">'.$objsoc->remise_client."&nbsp;%</td>";
    print '</tr>';
    
    // Remise avoirs
    print '<tr><td nowrap>';
    print '<table width="100%" class="nobordernopadding">';
    print '<tr><td nowrap>';
    print $langs->trans("CustomerAbsoluteDiscount");
    print '<td><td align="right">';
    print '<a href="'.DOL_URL_ROOT.'/comm/remx.php?id='.$objsoc->id.'">'.img_edit($langs->trans("Modify")).'</a>';
    print '</td></tr></table>';
    print '</td>';
    print '<td colspan="3">';
    $sql  = "SELECT rc.amount_ht,".$db->pdate("rc.datec")." as dc";
    $sql .= " FROM ".MAIN_DB_PREFIX."societe_remise_except as rc";
    $sql .= " WHERE rc.fk_soc =". $objsoc->id;
    $sql .= " AND rc.fk_user = ".$user->id." AND fk_facture IS NULL";
    $resql=$db->query($sql);
    if ($resql)
    {
        $obj = $db->fetch_object($resql);
        if ($obj->amount_ht) print $obj->amount_ht.'&nbsp;'.$langs->trans("Currency".$conf->monnaie);
        else print $langs->trans("None");
    }
    print '</td>';
    print '</tr>';

	// multiprix
	if($conf->global->PRODUIT_MULTIPRICES == 1)
	{
		print '<tr><td nowrap>';
		print '<table width="100%" class="nobordernopadding"><tr><td nowrap>';
		print $langs->trans("PriceLevel");
		print '<td><td align="right">';
		print '<a href="'.DOL_URL_ROOT.'/comm/multiprix.php?id='.$objsoc->id.'">'.img_edit($langs->trans("Modify")).'</a>';
		print '</td></tr></table>';
		print '</td><td colspan="3">'.$objsoc->price_level."</td>";
		print '</tr>';
	}

    print "</table>";

    print "</td>\n";


    print '<td valign="top" width="50%">';

    // Nbre max d'�l�ments des petites listes
    $MAXLIST=4;


    /*
     * Dernieres propales
     */
    if ($conf->propal->enabled)
    {
        $propal_static=new Propal($db);

        print '<table class="noborder" width="100%">';

        $sql = "SELECT s.nom, s.idp, p.rowid as propalid, p.fk_statut, p.price, p.ref, p.remise, ".$db->pdate("p.datep")." as dp, c.label as statut, c.id as statutid";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."propal as p, ".MAIN_DB_PREFIX."c_propalst as c";
        $sql .= " WHERE p.fk_soc = s.idp AND p.fk_statut = c.id";
        $sql .= " AND s.idp = ".$objsoc->id;
        $sql .= " ORDER BY p.datep DESC";
        
        $resql=$db->query($sql);
        if ($resql)
        {
            $var=true;
            $num = $db->num_rows($resql);
            if ($num > 0)
            {
                print '<tr class="liste_titre">';
                print '<td colspan="4"><table width="100%" class="noborder"><tr><td>'.$langs->trans("LastPropals",($num<=$MAXLIST?"":$MAXLIST)).'</td><td align="right"><a href="'.DOL_URL_ROOT.'/comm/propal.php?socidp='.$objsoc->id.'">'.$langs->trans("AllPropals").' ('.$num.')</td></tr></table></td>';
                print '</tr>';
                $var=!$var;
            }
            $i = 0;
            while ($i < $num && $i < $MAXLIST)
            {
                $objp = $db->fetch_object($resql);
                print "<tr $bc[$var]>";
                print "<td nowrap><a href=\"propal.php?propalid=$objp->propalid\">".img_object($langs->trans("ShowPropal"),"propal")." ".$objp->ref."</a>\n";
                if ( ($objp->dp < time() - $conf->propal->cloture->warning_delay) && $objp->statutid == 1 )
                {
                    print " ".img_warning();
                }
                print '</td><td align="right" width="80">'.dolibarr_print_date($objp->dp)."</td>\n";
                print '<td align="right" width="120">'.price($objp->price).'</td>';
                print '<td align="center" width="100">'.$propal_static->labelstatut_short[$objp->fk_statut].'</td></tr>';
                $var=!$var;
                $i++;
            }
            $db->free($resql);
        }
        else {
            dolibarr_print_error($db);
        }
        print "</table>";
    }

    /*
     * Dernieres commandes
     */
    if($conf->commande->enabled)
    {
        $commande_static=new Commande($db);
        
        print '<table class="noborder" width="100%">';

        $sql = "SELECT s.nom, s.idp, c.rowid as cid, c.total_ht, c.ref, c.fk_statut, ".$db->pdate("c.date_commande")." as dc";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."commande as c";
        $sql .= " WHERE c.fk_soc = s.idp ";
        $sql .= " AND s.idp = $objsoc->id";
        $sql .= " ORDER BY c.date_commande DESC";

        $resql=$db->query($sql);
        if ($resql)
        {
            $var=true;
            $num = $db->num_rows($resql);
            if ($num >0 )
            {
                print '<tr class="liste_titre">';
                print '<td colspan="4"><table width="100%" class="noborder"><tr><td>'.$langs->trans("LastOrders",($num<=$MAXLIST?"":$MAXLIST)).'</td><td align="right"><a href="'.DOL_URL_ROOT.'/commande/liste.php?socidp='.$objsoc->id.'">'.$langs->trans("AllOrders").' ('.$num.')</a></td></tr></table></td>';
                print '</tr>';
            }
            $i = 0;
            while ($i < $num && $i < $MAXLIST)
            {
                $objp = $db->fetch_object($resql);
                $var=!$var;
                print "<tr $bc[$var]>";
                print '<td><a href="'.DOL_URL_ROOT.'/commande/fiche.php?id='.$objp->cid.'">'.img_object($langs->trans("ShowOrder"),"order").' '.$objp->ref."</a>\n";
                print '</td><td align="right" width="80">'.dolibarr_print_date($objp->dc)."</td>\n";
                print '<td align="right" width="120">'.price($objp->total_ht).'</td>';
                print '<td align="center" width="100">'.$commande_static->status_label_short[$objp->fk_statut].'</td></tr>';
                $i++;
            }
            $db->free($resql);
        }
        else {
            dolibarr_print_error($db);
        }
        print "</table>";
    }

    /*
     * Derniers contrats
     */
    if($conf->contrat->enabled)
    {
        $contratstatic=new Contrat($db);
        
        print '<table class="noborder" width="100%">';

        $sql = "SELECT s.nom, s.idp, c.rowid as id, c.ref as ref, c.statut, ".$db->pdate("c.datec")." as dc";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."contrat as c";
        $sql .= " WHERE c.fk_soc = s.idp ";
        $sql .= " AND s.idp = $objsoc->id";
        $sql .= " ORDER BY c.datec DESC";

        $resql=$db->query($sql);
        if ($resql)
        {
            $var=true;
            $num = $db->num_rows($resql);
            if ($num >0 )
            {
                print '<tr class="liste_titre">';
                print '<td colspan="4"><table width="100%" class="noborder"><tr><td>'.$langs->trans("LastContracts",($num<=$MAXLIST?"":$MAXLIST)).'</td>';
                print '<td align="right"><a href="'.DOL_URL_ROOT.'/contrat/liste.php?socid='.$objsoc->id.'">'.$langs->trans("AllContracts").' ('.$num.')</td></tr></table></td>';
                print '</tr>';
            }
            $i = 0;
            while ($i < $num && $i < $MAXLIST)
            {
                $objp = $db->fetch_object($resql);
                $var=!$var;
                print "<tr $bc[$var]>";
                print '<td><a href="'.DOL_URL_ROOT.'/contrat/fiche.php?id='.$objp->id.'">'.img_object($langs->trans("ShowContract"),"contract").' '
                .(!isset($objp->ref) ? $objp->id : $objp->ref) ."</a></td>\n";
                print '<td align="right" width="80">'.dolibarr_print_date($objp->dc)."</td>\n";
                print '<td width="20">&nbsp;</td>';
                print '<td align="center" width="100">'.$contratstatic->LibStatut($objp->statut,2)."</td>\n";
                print '</tr>';
                $i++;
            }
            $db->free($resql);
        }
        else {
            dolibarr_print_error($db);
        }
        print "</table>";
    }
    
    /*
     * Dernieres interventions
     */
    if ($conf->fichinter->enabled)
    {
        print '<table class="noborder" width="100%">';

        $sql = "SELECT s.nom, s.idp, f.rowid as id, f.ref, ".$db->pdate("f.datei")." as di";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."fichinter as f";
        $sql .= " WHERE f.fk_soc = s.idp";
        $sql .= " AND s.idp = ".$objsoc->id;
        $sql .= " ORDER BY f.datei DESC";
        
        $resql=$db->query($sql);
        if ($resql)
        {
            $var=true;
            $num = $db->num_rows($resql);
            if ($num >0 )
            {
                print '<tr class="liste_titre">';
                print '<td colspan="4"><table width="100%" class="noborder"><tr><td>'.$langs->trans("LastInterventions",($num<=$MAXLIST?"":$MAXLIST)).'</td><td align="right"><a href="'.DOL_URL_ROOT.'/fichinter/index.php?socidp='.$objsoc->id.'">'.$langs->trans("AllInterventions").' ('.$num.')</td></tr></table></td>';
                print '</tr>';
                $var=!$var;
            }
            $i = 0;
            while ($i < $num && $i < $MAXLIST)
            {
                $objp = $db->fetch_object($resql);
                print "<tr $bc[$var]>";
                print '<td nowrap><a href="'.DOL_URL_ROOT."/fichinter/fiche.php?id=".$objp->id."\">".img_object($langs->trans("ShowPropal"),"propal")." ".$objp->ref."</a>\n";
                print "</td><td align=\"right\">".dolibarr_print_date($objp->di)."</td>\n";
                print '</tr>';
                $var=!$var;
                $i++;
            }
            $db->free($resql);
        }
        else {
            dolibarr_print_error($db);
        }
        print "</table>";
    }
    
    /*
     * Derniers projets associ�s
     */
    if ($conf->projet->enabled)
    {
        print '<table class="noborder" width=100%>';

        $sql  = "SELECT p.rowid,p.title,p.ref,".$db->pdate("p.dateo")." as do";
        $sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
        $sql .= " WHERE p.fk_soc = $objsoc->id";
        $sql .= " ORDER BY p.dateo DESC";

        $result=$db->query($sql);
        if ($result) {
            $var=true;
            $i = 0 ;
            $num = $db->num_rows($result);
            if ($num > 0) {
                print '<tr class="liste_titre">';
                print '<td colspan="2"><table width="100%" class="noborder"><tr><td>'.$langs->trans("LastProjects",($num<=$MAXLIST?"":$MAXLIST)).'</td><td align="right"><a href="'.DOL_URL_ROOT.'/projet/liste.php?socid='.$objsoc->id.'">'.$langs->trans("AllProjects").' ('.$num.')</td></tr></table></td>';
                print '</tr>';
            }
            while ($i < $num && $i < $MAXLIST) {
                $obj = $db->fetch_object($result);
                $var = !$var;
                print "<tr $bc[$var]>";
                print '<td><a href="../projet/fiche.php?id='.$obj->rowid.'">'.img_object($langs->trans("ShowProject"),"project")." ".$obj->title.'</a></td>';

                print "<td align=\"right\">".$obj->ref ."</td></tr>";
                $i++;
            }
            $db->free($result);
        }
        else
        {
            dolibarr_print_error($db);
        }
        print "</table>";
    }

    print "</td></tr>";
    print "</table></div>\n";


    /*
     * Barre d'action
     *
     */
    print '<div class="tabsAction">';

    if ($conf->propal->enabled && $user->rights->propale->creer)
    {
        $langs->load("propal");
        print '<a class="butAction" href="addpropal.php?socidp='.$objsoc->id.'&amp;action=create">'.$langs->trans("AddProp").'</a>';
    }

    if ($conf->commande->enabled && $user->rights->commande->creer)
    {
        $langs->load("orders");
        print '<a class="butAction" href="'.DOL_URL_ROOT.'/commande/fiche.php?socidp='.$objsoc->id.'&amp;action=create">'.$langs->trans("AddOrder").'</a>';
    }

    if ($user->rights->contrat->creer)
    {
        $langs->load("contracts");
        print '<a class="butAction" href="'.DOL_URL_ROOT.'/contrat/fiche.php?socid='.$objsoc->id.'&amp;action=create">'.$langs->trans("AddContract").'</a>';
    }

    if ($conf->fichinter->enabled)
    {
        $langs->load("fichinter");
        print '<a class="butAction" href="../fichinter/fiche.php?socidp='.$objsoc->id.'&amp;action=create">'.$langs->trans("AddIntervention").'</a>';
    }

    print '<a class="butAction" href="action/fiche.php?action=create&socid='.$objsoc->id.'">'.$langs->trans("AddAction").'</a>';

    print '<a class="butAction" href="'.DOL_URL_ROOT.'/contact/fiche.php?socid='.$objsoc->id.'&amp;action=create">'.$langs->trans("AddContact").'</a>';

    print '</div>';
    print '<br>';

    if ($action == 'changevalue')
    {
        print "<hr noshade>";
        print "<form action=\"index.php?socid=$objsoc->id\" method=\"post\">";
        print "<input type=\"hidden\" name=\"action\" value=\"cabrecrut\">";
        print "Cette soci�t� est un cabinet de recrutement : ";
        print "<select name=\"selectvalue\">";
        print "<option value=\"\">";
        print "<option value=\"t\">Oui";
        print "<option value=\"f\">Non";
        print "</select>";
        print "<input type=\"submit\" value=\"".$langs->trans("Valid")."\">";
        print "</form>\n";
    }
    else
    {
        /*
         *
         * Liste des contacts
         *
         */
        if ($conf->clicktodial->enabled)
        {
            $user->fetch_clicktodial(); // lecture des infos de clicktodial
        }

        print '<table class="noborder" width="100%">';

        print '<tr class="liste_titre"><td>'.$langs->trans("Firstname").' '.$langs->trans("Lastname").'</td>';
        print '<td>'.$langs->trans("Poste").'</td><td colspan="2">'.$langs->trans("Tel").'</td>';
        print '<td>'.$langs->trans("Fax").'</td><td>'.$langs->trans("EMail").'</td>';
        print "<td>&nbsp;</td>";
        print '<td>&nbsp;</td>';
        print "</tr>";

        $sql = "SELECT p.idp, p.name, p.firstname, p.poste, p.phone, p.fax, p.email, p.note ";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as p";
        $sql .= " WHERE p.fk_soc = $objsoc->id";
        $sql .= " ORDER by p.datec";

        $result = $db->query($sql);
        $i = 0;
        $num = $db->num_rows($result);
        $var=true;

        while ($i < $num)
        {
            $obj = $db->fetch_object($result);
            $var = !$var;
            print "<tr $bc[$var]>";

            print '<td>';
            print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$obj->idp.'">';
            print img_object($langs->trans("Show"),"contact");
            print '&nbsp;'.$obj->firstname.' '. $obj->name.'</a>&nbsp;';

            if (trim($obj->note))
            {
                print '<br>'.nl2br(trim($obj->note));
            }
            print '</td>';
            print '<td>'.$obj->poste.'&nbsp;</td>';
            print '<td>';

            // Lien click to dial
            if (strlen($obj->phone) && $user->clicktodial_enabled == 1)
            {
                print '<a href="'.DOL_URL_ROOT.'/comm/action/fiche.php?action=create&actionid=1&contactid='.$obj->idp.'&amp;socid='.$objsoc->id.'&amp;call='.$obj->phone.'">';
                print img_phone_out("Appel �mis") ;
            }
            print '</td><td>';
            print '<a href="action/fiche.php?action=create&actionid=1&contactid='.$obj->idp.'&socid='.$objsoc->id.'">'.dolibarr_print_phone($obj->phone).'</a>&nbsp;</td>';
            print '<td><a href="action/fiche.php?action=create&actionid=2&contactid='.$obj->idp.'&socid='.$objsoc->id.'">'.dolibarr_print_phone($obj->fax).'</a>&nbsp;</td>';
            print '<td><a href="action/fiche.php?action=create&actionid=4&contactid='.$obj->idp.'&socid='.$objsoc->id.'">'.$obj->email.'</a>&nbsp;</td>';

            print '<td align="center">';
            print "<a href=\"../contact/fiche.php?action=edit&amp;id=$obj->idp\">";
            print img_edit();
            print '</a></td>';

            print '<td align="center"><a href="action/fiche.php?action=create&actionid=5&contactid='.$obj->idp.'&socid='.$objsoc->id.'">';
            print img_object($langs->trans("Rendez-Vous"),"action");
            print '</a></td>';

            print "</tr>\n";
            $i++;
        }
        print "</table>";

        print "<br>";

        /*
         *      Listes des actions a faire
         *
         */
        print '<table width="100%" class="noborder">';
        print '<tr class="liste_titre"><td colspan="9"><a href="action/index.php?socid='.$objsoc->id.'">'.$langs->trans("ActionsToDo").'</a></td><td align="right">&nbsp;</td></tr>';

        $sql = "SELECT a.id, a.label, ".$db->pdate("a.datea")." as da, c.code as acode, c.libelle, u.code, a.propalrowid, a.fk_user_author, fk_contact, u.rowid ";
        $sql .= " FROM ".MAIN_DB_PREFIX."actioncomm as a, ".MAIN_DB_PREFIX."c_actioncomm as c, ".MAIN_DB_PREFIX."user as u ";
        $sql .= " WHERE a.fk_soc = ".$objsoc->id;
        $sql .= " AND u.rowid = a.fk_user_author";
        $sql .= " AND c.id=a.fk_action AND a.percent < 100";
        $sql .= " ORDER BY a.datea DESC, a.id DESC";

        $result=$db->query($sql);
        if ($result)
        {
            $i = 0 ;
            $num = $db->num_rows($result);
            $var=true;
            
            while ($i < $num)
            {
                $var = !$var;

                $obj = $db->fetch_object($result);
                print "<tr $bc[$var]>";

                if ($oldyear == strftime("%Y",$obj->da) )
                {
                    print '<td width="30" align="center">|</td>';
                }
                else
                {
                    print '<td width="30" align="center">'.strftime("%Y",$obj->da)."</td>\n";
                    $oldyear = strftime("%Y",$obj->da);
                }

                if ($oldmonth == strftime("%Y%b",$obj->da) )
                {
                    print '<td width="30" align="center">|</td>';
                }
                else
                {
                    print '<td width="30" align="center">' .strftime("%b",$obj->da)."</td>\n";
                    $oldmonth = strftime("%Y%b",$obj->da);
                }

                print '<td width="20">'.strftime("%d",$obj->da)."</td>\n";
                print '<td width="30">'.strftime("%H:%M",$obj->da)."</td>\n";
				if (date("U",$obj->da) < time())
				{
					print "<td>".img_warning("Late")."</td>";
				}
				else
				{
					print '<td>&nbsp;</td>';
				}

                // Status/Percent
                print '<td width="30">&nbsp;</td>';

                if ($obj->propalrowid)
                {
                    print '<td><a href="propal.php?propalid='.$obj->propalrowid.'">'.img_object($langs->trans("ShowAction"),"task");
                      $transcode=$langs->trans("Action".$obj->acode);
                      $libelle=($transcode!="Action".$obj->acode?$transcode:$obj->libelle);
                      print $libelle;
                    print '</a></td>';
                }
                else
                {
                    print '<td><a href="action/fiche.php?id='.$obj->id.'">'.img_object($langs->trans("ShowAction"),"task");
                      $transcode=$langs->trans("Action".$obj->acode);
                      $libelle=($transcode!="Action".$obj->acode?$transcode:$obj->libelle);
                      print $libelle;
                    print '</a></td>';
                }
                print "<td>$obj->label</td>";

                // Contact pour cette action
                if ($obj->fk_contact) {
                    $contact = new Contact($db);
                    $contact->fetch($obj->fk_contact);
                    print '<td><a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$contact->id.'">'.img_object($langs->trans("ShowContact"),"contact").' '.$contact->fullname.'</a></td>';
                } else {
                    print '<td>&nbsp;</td>';
                }

                print '<td width="50"><a href="'.DOL_URL_ROOT.'/user/fiche.php?id='.$obj->fk_user_author.'">'.img_object($langs->trans("ShowUser"),"user").' '.$obj->code.'</a></td>';
                print "</tr>\n";
                $i++;
            }
            print "</table>";

            $db->free($result);
        } else {
            dolibarr_print_error($db);
        }
        print "</table><br>";


        /*
         *      Listes des actions effectuees
         */
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre"><td colspan="11"><a href="action/index.php?socid='.$objsoc->id.'">'.$langs->trans("ActionsDone").'</a></td></tr>';

        $sql = "SELECT a.id, a.label, ".$db->pdate("a.datea")." as da, c.code as acode, c.libelle, u.code, a.propalrowid, a.fk_user_author, fk_contact, u.rowid ";
        $sql .= " FROM ".MAIN_DB_PREFIX."actioncomm as a, ".MAIN_DB_PREFIX."c_actioncomm as c, ".MAIN_DB_PREFIX."user as u ";
        $sql .= " WHERE a.fk_soc = ".$objsoc->id;
        $sql .= " AND u.rowid = a.fk_user_author";
        $sql .= " AND c.id=a.fk_action AND a.percent = 100";
        $sql .= " ORDER BY a.datea DESC, a.id DESC";

        $result=$db->query($sql);
        if ($result)
        {
            $i = 0 ;
            $num = $db->num_rows($result);
            $oldyear='';
            $oldmonth='';
            $var=true;
            
            while ($i < $num)
            {
                $var = !$var;

                $obj = $db->fetch_object($result);
                print "<tr $bc[$var]>";

                // Champ date
                if ($oldyear == strftime("%Y",$obj->da) )
                {
                    print '<td width="30" align="center">|</td>';
                }
                else
                {
                    print '<td width="30" align="center">'.strftime("%Y",$obj->da)."</td>\n";
                    $oldyear = strftime("%Y",$obj->da);
                }

                if ($oldmonth == strftime("%Y%b",$obj->da) )
                {
                    print '<td width="30" align="center">|</td>';
                }
                else
                {
                    print '<td width="30" align="center">'.strftime("%b",$obj->da)."</td>\n";
                    $oldmonth = strftime("%Y%b",$obj->da);
                }
                print '<td width="20">'.strftime("%d",$obj->da)."</td>\n";
                print '<td width="30">'.strftime("%H:%M",$obj->da)."</td>\n";
				print "<td>&nbsp;</td>";

                // Espace
                print '<td width="30">&nbsp;</td>';

				// Action
        		print '<td>';
				print '<a href="'.DOL_URL_ROOT.'/comm/action/fiche.php?id='.$obj->id.'">'.img_object($langs->trans("ShowTask"),"task");
				$transcode=$langs->trans("Action".$obj->acode);
				$libelle=($transcode!="Action".$obj->acode?$transcode:$obj->libelle);
				print $libelle;
				print '</a>';
				print '</td>';

        		print '<td>';
				if ($obj->propalrowid)
				{
					print '<a href="'.DOL_URL_ROOT.'/comm/propal.php?propalid='.$obj->propalrowid.'">'.img_object($langs->trans("ShowPropal"),"propal");
					print $langs->trans("Propal");
					print '</a>';
				}
				else print '&nbsp;';
        		print '</td>';

                print "<td>$obj->label</td>";

                // Contact pour cette action
                if ($obj->fk_contact)
                {
                    $contact = new Contact($db);
                    $contact->fetch($obj->fk_contact);
                    print '<td><a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$contact->id.'">'.img_object($langs->trans("ShowContact"),"contact").' '.$contact->fullname.'</a></td>';
                }
                else
                {
                    print '<td>&nbsp;</td>';
                }

                print '<td><a href="'.DOL_URL_ROOT.'/user/fiche.php?id='.$obj->rowid.'">'.img_object($langs->trans("ShowUser"),'user').' '.$obj->code.'</a></td>';
                print "</tr>\n";
                $i++;
            }

            $db->free($result);
        }
        else
        {
            dolibarr_print_error($db);
        }
        print "</table>";

    }
} else {
    dolibarr_print_error($db);
}

$db->close();


llxFooter('$Date$ - $Revision$');
?>
