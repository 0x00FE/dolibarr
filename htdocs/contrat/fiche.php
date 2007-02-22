<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
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
        \file       htdocs/contrat/fiche.php
        \ingroup    contrat
        \brief      Fiche contrat
        \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/lib/contract.lib.php');
if ($conf->projet->enabled)  require_once(DOL_DOCUMENT_ROOT."/project.class.php");
if ($conf->propal->enabled)  require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
if ($conf->contrat->enabled) require_once(DOL_DOCUMENT_ROOT."/contrat/contrat.class.php");

$langs->load("contracts");
$langs->load("orders");
$langs->load("companies");
$langs->load("bills");

$user->getrights('contrat');
$user->getrights('commercial');

if (! $user->rights->contrat->lire)
accessforbidden();

// S�curit� acc�s client et commerciaux
$contratid = isset($_GET["id"])?$_GET["id"]:'';

if ($user->societe_id > 0)
{
    $action = '';
    $socid = $user->societe_id;
}

// Protection restriction commercial
if ($contratid && !$user->rights->commercial->client->voir)
{
        $sql = "SELECT sc.fk_soc, c.fk_soc";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe_commerciaux as sc, ".MAIN_DB_PREFIX."contrat as c";
        $sql .= " WHERE c.rowid = ".$contratid;
        if (!$user->rights->commercial->client->voir && !$user->societe_id > 0)
        {
        	$sql .= " AND sc.fk_soc = c.fk_soc AND sc.fk_user = ".$user->id;
        }
        if ($user->societe_id > 0) $sql .= " AND c.fk_soc = ".$socid;

        if ( $db->query($sql) )
        {
          if ( $db->num_rows() == 0) accessforbidden();
        }
}


// Si ajout champ produit pr�d�fini
if ($_POST["mode"]=='predefined')
{
	$date_start='';
	$date_end='';
  if ($_POST["date_startmonth"] && $_POST["date_startday"] && $_POST["date_startyear"])
  {
    $date_start=dolibarr_mktime(12, 0 , 0, $_POST["date_startmonth"], $_POST["date_startday"], $_POST["date_startyear"]);
  }
  if ($_POST["date_endmonth"] && $_POST["date_endday"] && $_POST["date_endyear"])
  {
    $date_end=dolibarr_mktime(12, 0 , 0, $_POST["date_endmonth"], $_POST["date_endday"], $_POST["date_endyear"]);
  }
}

// Si ajout champ produit libre
if ($_POST["mode"]=='libre')
{
	$date_start_sl='';
	$date_end_sl='';
  if ($_POST["date_start_slmonth"] && $_POST["date_start_slday"] && $_POST["date_start_slyear"])
  {
    $date_start_sl=dolibarr_mktime(12, 0 , 0, $_POST["date_start_slmonth"], $_POST["date_start_slday"], $_POST["date_start_slyear"]);
  }
  if ($_POST["date_end_slmonth"] && $_POST["date_end_slday"] && $_POST["date_end_slyear"])
  {
    $date_end_sl=dolibarr_mktime(12, 0 , 0, $_POST["date_end_slmonth"], $_POST["date_end_slday"], $_POST["date_end_slyear"]);
  }
}

// Param si updateligne
$date_start_update='';
$date_end_update='';
$date_start_real_update='';
$date_end_real_update='';
if ($_POST["date_start_updatemonth"] && $_POST["date_start_updateday"] && $_POST["date_start_updateyear"])
{
    $date_start_update=dolibarr_mktime(12, 0 , 0, $_POST["date_start_updatemonth"], $_POST["date_start_updateday"], $_POST["date_start_updateyear"]);
}
if ($_POST["date_end_updatemonth"] && $_POST["date_end_updateday"] && $_POST["date_end_updateyear"])
{
    $date_end_update=dolibarr_mktime(12, 0 , 0, $_POST["date_end_updatemonth"], $_POST["date_end_updateday"], $_POST["date_end_updateyear"]);
}
if ($_POST["date_start_real_updatemonth"] && $_POST["date_start_real_updateday"] && $_POST["date_start_real_updateyear"])
{
    $date_start_real_update=dolibarr_mktime(12, 0 , 0, $_POST["date_start_real_updatemonth"], $_POST["date_start_real_updateday"], $_POST["date_start_real_updateyear"]);
}
if ($_POST["date_end_real_updatemonth"] && $_POST["date_end_real_updateday"] && $_POST["date_end_real_updateyear"])
{
    $date_end_real_update=dolibarr_mktime(12, 0 , 0, $_POST["date_end_real_updatemonth"], $_POST["date_end_real_updateday"], $_POST["date_end_real_updateyear"]);
}


/*
 * Actions
 */
if ($_POST["action"] == 'add')
{
    $datecontrat = dolibarr_mktime(12, 0 , 0, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]);

    $contrat = new Contrat($db);

    $contrat->socid         = $_POST["socid"];
    $contrat->date_contrat   = $datecontrat;

    $contrat->commercial_suivi_id      = $_POST["commercial_suivi_id"];
    $contrat->commercial_signature_id  = $_POST["commercial_signature_id"];

    $contrat->note           = trim($_POST["note"]);
    $contrat->projetid       = trim($_POST["projetid"]);
    $contrat->remise_percent = trim($_POST["remise_percent"]);
    $contrat->ref            = trim($_POST["ref"]);

    $result = $contrat->create($user,$langs,$conf);
    if ($result > 0)
    {
        Header("Location: fiche.php?id=".$contrat->id);
        exit;
    }
    else {
        $mesg='<div class="error">'.$contrat->error.'</div>';
    }
    $_GET["socid"]=$_POST["socid"];
    $_GET["action"]='create';
    $action = '';
}

if ($_POST["action"] == 'classin')
{
    $contrat = new Contrat($db);
    $contrat->fetch($_GET["id"]);
    $contrat->classin($_POST["projetid"]);
}

if ($_POST["action"] == 'addligne' && $user->rights->contrat->creer)
{
    if ($_POST["pqty"] && (($_POST["pu"] && $_POST["desc"]) || $_POST["p_idprod"]))
    {
        $result = 0;
        $contrat = new Contrat($db);
        $result=$contrat->fetch($_GET["id"]);
        if ($_POST["p_idprod"] > 0 && $_POST["mode"]=='predefined')
        {
            //print $_POST["desc"]." - ".$_POST["pu"]." - ".$_POST["pqty"]." - ".$_POST["tva_tx"]." - ".$_POST["p_idprod"]." - ".$_POST["premise"]; exit;
            $result = $contrat->addline(
                $_POST["desc"],
                $_POST["pu"],
                $_POST["pqty"],
                $_POST["tva_tx"],
                $_POST["p_idprod"],
                $_POST["premise"],
                $date_start,
                $date_end
                );
        }
        elseif ($_POST["mode"]=='libre')
        {
		$result = $contrat->addline(
                $_POST["desc"],
                $_POST["pu"],
                $_POST["pqty"],
                $_POST["tva_tx"],
                0,
                $_POST["premise"],
                $date_start_sl,
                $date_end_sl
                );
        }
    
        if ($result >= 0)
        {
            Header("Location: fiche.php?id=".$contrat->id);
            exit;
        }
        else
        {
            $mesg='<div class="error">'.$contrat->error.'</div>';
        }
    }
}

if ($_POST["action"] == 'updateligne' && $user->rights->contrat->creer && $_POST["save"])
{
    $contrat = new Contrat($db,"",$_GET["id"]);
    if ($contrat->fetch($_GET["id"]))
    {
        $result = $contrat->updateline($_POST["elrowid"],
            $_POST["eldesc"],
            $_POST["elprice"],
            $_POST["elqty"],
            $_POST["elremise_percent"],
            $date_start_update,
            $date_end_update,
            $_POST["eltva_tx"],
            $date_start_real_update,
            $date_end_real_update
            );
            
        if ($result > 0)
        {
            $db->commit();
        }
        else
        {
            dolibarr_print_error($db,"result=$result");
            $db->rollback();
        }        
    }
    else
    {
        dolibarr_print_error($db);
    }
}

if ($_POST["action"] == 'updateligne' && $user->rights->contrat->creer && $_POST["cancel"])
{
    Header("Location: fiche.php?id=".$_GET["id"]);
    exit;
}

if ($_GET["action"] == 'deleteline' && $user->rights->contrat->creer)
{
    $contrat = new Contrat($db);
    $contrat->fetch($_GET["id"]);
    $result = $contrat->delete_line($_GET["lineid"]);

    if ($result == 0)
    {
        Header("Location: fiche.php?id=".$contrat->id);
        exit;
    }
}

if ($_POST["action"] == 'confirm_valid' && $_POST["confirm"] == 'yes' && $user->rights->contrat->creer)
{
    $contrat = new Contrat($db);
    $contrat->fetch($_GET["id"]);
    $result = $contrat->validate($user,$langs,$conf);
}

if ($_POST["action"] == 'confirm_close' && $_POST["confirm"] == 'yes' && $user->rights->contrat->creer)
{
    $contrat = new Contrat($db);
    $contrat->fetch($_GET["id"]);
    $result = $contrat->cloture($user,$langs,$conf);
}

if ($_POST["action"] == 'confirm_delete' && $_POST["confirm"] == 'yes')
{
    if ($user->rights->contrat->supprimer )
    {
        $contrat = new Contrat($db);
        $contrat->id = $_GET["id"];
        $result=$contrat->delete($user,$langs,$conf);
        if ($result >= 0)
		{
			Header("Location: index.php");
			return;
		}
		else
		{
			$mesg='<div class="error">'.$contrat->error.'</div>';
		}
    }
}




llxHeader('',$langs->trans("ContractCard"),"Contrat");

$html = new Form($db);


/*********************************************************************
 *
 * Mode creation
 *
 *********************************************************************/
if ($_GET["action"] == 'create')
{
    dolibarr_fiche_head($head, $a, $langs->trans("AddContract"));

    if ($mesg) print $mesg;

    $new_contrat = new Contrat($db);

    $sql = "SELECT s.nom, s.prefix_comm, s.idp ";
    $sql .= "FROM ".MAIN_DB_PREFIX."societe as s ";
    $sql .= "WHERE s.idp = ".$_GET["socid"];

    $resql=$db->query($sql);
    if ($resql)
    {
        $num = $db->num_rows($resql);
        if ($num)
        {
            $obj = $db->fetch_object($resql);

            $soc = new Societe($db);
            $soc->fetch($obj->idp);

            print '<form name="contrat" action="fiche.php" method="post">';

            print '<input type="hidden" name="action" value="add">';
            print '<input type="hidden" name="socid" value="'.$soc->id.'">'."\n";
            print '<input type="hidden" name="remise_percent" value="0">';

            print '<table class="border" width="100%">';

			// Ref
			print '<tr><td>'.$langs->trans("Ref").'</td>';
			print '<td><input type="text" maxlength="30" name="ref" size="20"></td></tr>';
			
            // Customer
            print '<tr><td>'.$langs->trans("Customer").'</td><td>'.$soc->getNomUrl(1).'</td></tr>';

			// Ligne info remises tiers
            print '<tr><td>'.$langs->trans('Discount').'</td><td>';
			if ($soc->remise_client) print $langs->trans("CompanyHasRelativeDiscount",$soc->remise_client);
			else print $langs->trans("CompanyHasNoRelativeDiscount");
			$absolute_discount=$soc->getCurrentDiscount();
			print '. ';
			if ($absolute_discount) print $langs->trans("CompanyHasAbsoluteDiscount",$absolute_discount,$langs->trans("Currency".$conf->monnaie));
			else print $langs->trans("CompanyHasNoAbsoluteDiscount");
			print '.';
			print '</td></tr>';
    
            // Commercial suivi
            print '<tr><td width="20%" nowrap>'.$langs->trans("TypeContact_contrat_internal_SALESREPFOLL").'</td><td>';
            print '<select name="commercial_suivi_id">';
            print '<option value="-1">&nbsp;</option>';

            $sql = "SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user";
            $sql.= " ORDER BY name ";
            $resql=$db->query( $sql);
            if ($resql)
            {
                $num = $db->num_rows($resql);
                if ( $num > 0 )
                {
                    $i = 0;
                    while ($i < $num)
                    {
                        $row = $db->fetch_row($resql);
                        print '<option value="'.$row[0].'">'.$row[1] . " " . $row[2].'</option>';
                        $i++;
                    }
                }
                $db->free($resql);

            }
            print '</select></td></tr>';

            // Commercial signature
            print '<tr><td width="20%" nowrap>'.$langs->trans("TypeContact_contrat_internal_SALESREPSIGN").'</td><td>';
            print '<select name="commercial_signature_id">';
            print '<option value="-1">&nbsp;</option>';
            $sql = "SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user";
            $sql.= " ORDER BY name";
            $resql=$db->query( $sql);
            if ($resql)
            {
                $num = $db->num_rows($resql);
                if ( $num > 0 )
                {
                    $i = 0;
                    while ($i < $num)
                    {
                        $row = $db->fetch_row($resql);
                        print '<option value="'.$row[0].'">'.$row[1] . " " . $row[2].'</option>';
                        $i++;
                    }
                }
                $db->free($resql);

            }
            print '</select></td></tr>';

            print '<tr><td>'.$langs->trans("Date").'</td><td>';
            $html->select_date('','','','','',"contrat");
            print "</td></tr>";

            if ($conf->projet->enabled)
            {
                print '<tr><td>'.$langs->trans("Project").'</td><td>';
                $proj = new Project($db);
                $html->select_array("projetid",$proj->liste_array($soc->id),0,1);
                print "</td></tr>";
            }
 
            print '<tr><td>'.$langs->trans("NotePublic").'</td><td valign="top">';
            print '<textarea name="note_public" wrap="soft" cols="70" rows="'.ROWS_3.'"></textarea></td></tr>';

			if (! $user->societe_id)
			{
	            print '<tr><td>'.$langs->trans("NotePrivate").'</td><td valign="top">';
	            print '<textarea name="note" wrap="soft" cols="70" rows="'.ROWS_3.'"></textarea></td></tr>';
			}
			
            print '<tr><td colspan="2" align="center"><input type="submit" class="button" value="'.$langs->trans("Create").'"></td></tr>';

            print "</table>\n";

            print "</form>\n";

            if ($propalid)
            {
                /*
                 * Produits
                 */
                print '<br>';
                print_titre($langs->trans("Products"));

                print '<table class="noborder" width="100%">';
                print '<tr class="liste_titre"><td>'.$langs->trans("Ref").'</td><td>'.$langs->trans("Product").'</td>';
                print '<td align="right">'.$langs->trans("Price").'</td>';
                print '<td align="center">'.$langs->trans("Qty").'</td>';
                print '<td align="center">'.$langs->trans("ReductionShort").'</td>';
                print '</tr>';

                $sql = "SELECT pt.rowid, p.label as product, p.ref, pt.price, pt.qty, p.rowid as prodid, pt.remise_percent";
                $sql .= " FROM ".MAIN_DB_PREFIX."propaldet as pt, ".MAIN_DB_PREFIX."product as p WHERE pt.fk_product = p.rowid AND pt.fk_propal = $propalid";
                $sql .= " ORDER BY pt.rowid ASC";
                $result = $db->query($sql);
                if ($result)
                {
                    $num = $db->num_rows($result);
                    $i = 0;
                    $var=True;
                    while ($i < $num)
                    {
                        $objp = $db->fetch_object($result);
                        $var=!$var;
                        print "<tr $bc[$var]><td>[$objp->ref]</td>\n";
                        print '<td>'.$objp->product.'</td>';
                        print "<td align=\"right\">".price($objp->price).'</td>';
                        print '<td align="center">'.$objp->qty.'</td>';
                        print '<td align="center">'.$objp->remise_percent.'%</td>';
                        print "</tr>\n";
                        $i++;
                    }
                }
                $sql = "SELECT pt.rowid, pt.description as product, pt.price, pt.qty, pt.remise_percent";
                $sql.= " FROM ".MAIN_DB_PREFIX."propaldet as pt";
                $sql.= " WHERE  pt.fk_propal = $propalid AND pt.fk_product = 0";
                $sql.= " ORDER BY pt.rowid ASC";
                $result=$db->query($sql);
                if ($result)
                {
                    $num = $db->num_rows($result);
                    $i = 0;
                    while ($i < $num)
                    {
                        $objp = $db->fetch_object($result);
                        $var=!$var;
                        print "<tr $bc[$var]><td>&nbsp;</td>\n";
                        print '<td>'.$objp->product.'</td>';
                        print '<td align="right">'.price($objp->price).'</td>';
                        print '<td align="center">'.$objp->remise_percent.'%</td>';
                        print '<td align="center">'.$objp->qty.'</td>';
                        print "</tr>\n";
                        $i++;
                    }
                }
                else
                {
                    dolibarr_print_error($db);
                }

                print '</table>';
            }
        }
    }
    else
    {
        dolibarr_print_error($db);
    }
    
    print '</div>';
}
else
/* *************************************************************************** */
/*                                                                             */
/* Mode vue et edition                                                         */
/*                                                                             */
/* *************************************************************************** */
{
    $id = $_GET["id"];
    if ($id > 0)
    {
        $contrat = New Contrat($db);
        $result=$contrat->fetch($id);
        if ($result < 0)
        {
            dolibarr_print_error($db,$contrat->error);
            exit;
        }

        if ($mesg) print $mesg;
		
        $author = new User($db);
        $author->id = $contrat->user_author_id;
        $author->fetch();
		
        $commercial_signature = new User($db);
        $commercial_signature->id = $contrat->commercial_signature_id;
        $commercial_signature->fetch();

        $commercial_suivi = new User($db);
        $commercial_suivi->id = $contrat->commercial_suivi_id;
        $commercial_suivi->fetch();

	    $head = contract_prepare_head($contrat);

        $hselected = 0;

        dolibarr_fiche_head($head, $hselected, $langs->trans("Contract"));


        /*
         * Confirmation de la suppression du contrat
         */
        if ($_GET["action"] == 'delete')
        {
            $html->form_confirm("fiche.php?id=$id",$langs->trans("DeleteAContract"),$langs->trans("ConfirmDeleteAContract"),"confirm_delete");
            print '<br>';
        }

        /*
         * Confirmation de la validation
         */
        if ($_GET["action"] == 'valid')
        {
            //$numfa = contrat_get_num($soc);
            $html->form_confirm("fiche.php?id=$id",$langs->trans("ValidateAContract"),$langs->trans("ConfirmValidateContract"),"confirm_valid");
            print '<br>';
        }

        /*
         * Confirmation de la fermeture
         */
        if ($_GET["action"] == 'close')
        {
            $html->form_confirm("fiche.php?id=$id",$langs->trans("CloseAContract"),$langs->trans("ConfirmCloseContract"),"confirm_close");
            print '<br>';
        }

        /*
         *   Contrat
         */
        if ($contrat->brouillon && $user->rights->contrat->creer)
        {
            print '<form action="fiche.php?id='.$id.'" method="post">';
            print '<input type="hidden" name="action" value="setremise">';
        }

        print '<table class="border" width="100%">';

        // Ref du contrat
        print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
        print $contrat->ref;
        print "</td></tr>";

        // Customer
        print "<tr><td>".$langs->trans("Customer")."</td>";
        print '<td colspan="3">'.$contrat->societe->getNomUrl(1).'</td></tr>';

		// Ligne info remises tiers
        print '<tr><td>'.$langs->trans('Discount').'</td><td>';
		if ($contrat->societe->remise_client) print $langs->trans("CompanyHasRelativeDiscount",$contrat->societe->remise_client);
		else print $langs->trans("CompanyHasNoRelativeDiscount");
		$absolute_discount=$contrat->societe->getCurrentDiscount();
		print '. ';
		if ($absolute_discount) print $langs->trans("CompanyHasAbsoluteDiscount",$absolute_discount,$langs->trans("Currency".$conf->monnaie));
		else print $langs->trans("CompanyHasNoAbsoluteDiscount");
		print '.';
		print '</td></tr>';

        // Statut contrat
        print '<tr><td>'.$langs->trans("Status").'</td><td colspan="3">';
        print $contrat->getLibStatut(2);
        print "</td></tr>";

        // Date
        print '<tr><td>'.$langs->trans("Date").'</td>';
        print '<td colspan="3">'.dolibarr_print_date($contrat->date_contrat,"%A %d %B %Y")."</td></tr>\n";

        // Factures associ�es
        /*
        TODO
        */

        // Projet
        if ($conf->projet->enabled)
        {
            $langs->load("projects");
            print '<tr><td>';
            print '<table width="100%" class="nobordernopadding"><tr><td>';
            print $langs->trans("Project");
            print '</td>';
            if ($_GET["action"] != "classer" && $user->rights->projet->creer) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=classer&amp;id='.$id.'">'.img_edit($langs->trans("SetProject")).'</a></td>';
            print '</tr></table>';
            print '</td><td colspan="3">';
            if ($_GET["action"] == "classer")
            {
                $html->form_project("fiche.php?id=$id",$contrat->socid,$contrat->fk_projet,"projetid");
            }
            else
            {
                $html->form_project("fiche.php?id=$id",$contrat->socid,$contrat->fk_projet,"none");
            }
            print "</td></tr>";
        }

        print "</table>";

        if ($contrat->brouillon == 1 && $user->rights->contrat->creer)
        {
            print '</form>';
        }

        /*
         * Lignes de contrats
         */
        echo '<br><table class="noborder" width="100%">';

        $sql = "SELECT cd.statut, cd.label as label_det, cd.fk_product, cd.description, cd.price_ht, cd.qty, cd.rowid, cd.tva_tx, cd.remise_percent, cd.subprice,";
        $sql.= " ".$db->pdate("cd.date_ouverture_prevue")." as date_debut, ".$db->pdate("cd.date_ouverture")." as date_debut_reelle,";
        $sql.= " ".$db->pdate("cd.date_fin_validite")." as date_fin, ".$db->pdate("cd.date_cloture")." as date_fin_reelle,";
        $sql.= " p.ref, p.label";
        $sql.= " FROM ".MAIN_DB_PREFIX."contratdet as cd";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON cd.fk_product = p.rowid";
        $sql.= " WHERE cd.fk_contrat = ".$id;
        $sql.= " ORDER BY cd .rowid";

        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            $i = 0; $total = 0;

            if ($num)
            {
                print '<tr class="liste_titre">';
                print '<td>'.$langs->trans("Service").'</td>';
                print '<td width="50" align="center">'.$langs->trans("VAT").'</td>';
                print '<td width="50" align="right">'.$langs->trans("PriceUHT").'</td>';
                print '<td width="30" align="center">'.$langs->trans("Qty").'</td>';
                print '<td width="50" align="right">'.$langs->trans("ReductionShort").'</td>';
                print '<td width="30">&nbsp;</td>';
                print '<td width="30" align="center">'.$langs->trans("Status").'</td>';
                print "</tr>\n";
            }
            $var=true;
            while ($i < $num)
            {
                $objp = $db->fetch_object($result);

                $var=!$var;

                if ($_GET["action"] != 'editline' || $_GET["rowid"] != $objp->rowid)
                {

                    print '<tr '.$bc[$var].' valign="top">';
                    // Libelle
                    if ($objp->fk_product > 0)
                    {
                        print '<td>';
                        print '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">';
                        print img_object($langs->trans("ShowService"),"service").' '.$objp->ref.'</a>';
                        print $objp->label?' - '.$objp->label:'';
                        if ($objp->description) print '<br />'.nl2br($objp->description);
                        print '</td>';
                    }
                    else
                    {
                        print "<td>".nl2br($objp->description)."</td>\n";
                    }
                    // TVA
                    print '<td align="center">'.$objp->tva_tx.'%</td>';
                    // Prix
                    print '<td align="right">'.price($objp->subprice)."</td>\n";
                    // Quantit�
                    print '<td align="center">'.$objp->qty.'</td>';
                    // Remise
                    if ($objp->remise_percent > 0)
                    {
                        print '<td align="right">'.$objp->remise_percent."%</td>\n";
                    }
                    else
                    {
                        print '<td>&nbsp;</td>';
                    }
                    // Icon update et delete (statut contrat 0=brouillon,1=valid�,2=ferm�)
                    print '<td align="center" nowrap>';
                    if ($contrat->statut != 2  && $user->rights->contrat->creer)
                    {
                        print '<a href="fiche.php?id='.$id.'&amp;action=editline&amp;rowid='.$objp->rowid.'">';
                        print img_edit();
                        print '</a>';
                    }
                    else {
                        print '&nbsp;';
                    }
                    if ($contrat->statut == 0  && $user->rights->contrat->creer)
                    {
                        print '&nbsp;';
                        print '<a href="fiche.php?id='.$id.'&amp;action=deleteline&amp;lineid='.$objp->rowid.'">';
                        print img_delete();
                        print '</a>';
                    }
                    print '</td>';
        
                    // Statut
                    print '<td align="center">';
                    if ($contrat->statut > 0) print '<a href="'.DOL_URL_ROOT.'/contrat/ligne.php?id='.$contrat->id.'&amp;ligne='.$objp->rowid.'">';;
                    print img_statut($objp->statut);
                    if ($contrat->statut > 0) print '</a>';
                    print '</td>';

                    print "</tr>\n";

                    // Dates de en service pr�vues et effectives
                    if ($objp->subprice >= 0)
                    {
	                    print '<tr '.$bc[$var].'>';
	                    print '<td colspan="7">';
	
	                    // Date pr�vues
	                    print $langs->trans("DateStartPlanned").': ';
	                    if ($objp->date_debut) {
	                        print dolibarr_print_date($objp->date_debut);
	                        // Warning si date prevu pass�e et pas en service
	                        if ($objp->statut == 0 && $objp->date_debut < time() - $conf->contrat->warning_delay) { print " ".img_warning($langs->trans("Late")); }
	                    }
	                    else print $langs->trans("Unknown");
	                    print ' &nbsp;-&nbsp; ';
	                    print $langs->trans("DateEndPlanned").': ';
	                    if ($objp->date_fin) {
	                        print dolibarr_print_date($objp->date_fin);
	                        if ($objp->statut == 4 && $objp->date_fin < time() - $conf->contrat->warning_delay) { print " ".img_warning($langs->trans("Late")); }
	                    }
	                    else print $langs->trans("Unknown");
	
	                    print '<br>';
	
	                    // Si pas encore activ�
	                    if (! $objp->date_debut_reelle) {
	                        print $langs->trans("DateStartReal").': ';
	                        if ($objp->date_debut_reelle) print dolibarr_print_date($objp->date_debut_reelle);
	                        else print $langs->trans("ContractStatusNotRunning");
	                    }
	                    // Si activ� et en cours
	                    if ($objp->date_debut_reelle && ! $objp->date_fin_reelle) {
	                        print $langs->trans("DateStartReal").': ';
	                        print dolibarr_print_date($objp->date_debut_reelle);
	                    }
	                    // Si d�sactiv�
	                    if ($objp->date_debut_reelle && $objp->date_fin_reelle) {
	                        print $langs->trans("DateStartReal").': ';
	                        print dolibarr_print_date($objp->date_debut_reelle);
	                        print ' &nbsp;-&nbsp; ';
	                        print $langs->trans("DateEndReal").': ';
	                        print dolibarr_print_date($objp->date_fin_reelle);
	                    }
	                    print '</td>';
	                    print '</tr>';
	            	}                  
                }

                // Ligne en mode update
                else
                {
                    print "<form name='update' action=\"fiche.php?id=$id\" method=\"post\">";
                    print '<input type="hidden" name="action" value="updateligne">';
                    print '<input type="hidden" name="elrowid" value="'.$_GET["rowid"].'">';
                    // Ligne carac
                    print "<tr $bc[$var]>";
                    print '<td>';
                    if ($objp->fk_product)
                    {
                        print '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">';
                        print img_object($langs->trans("ShowService"),"service").' '.$objp->ref.'</a>';
                        print $objp->label?' - '.$objp->label:'';
                        print '</br>';
                    }
                    else
                    {
                        print $objp->label?$objp->label.'<br>':'';
                    }
                    print '<textarea name="eldesc" cols="70" rows="1">'.$objp->description.'</textarea></td>';
                    print '<td align="right">';
                    print $html->select_tva("eltva_tx",$objp->tva_tx,$mysoc,$contrat->societe);
                    print '</td>';
                    print '<td align="right"><input size="5" type="text" name="elprice" value="'.price($objp->subprice).'"></td>';
                    print '<td align="center"><input size="2" type="text" name="elqty" value="'.$objp->qty.'"></td>';
                    print '<td align="right"><input size="1" type="text" name="elremise_percent" value="'.$objp->remise_percent.'">%</td>';
                    print '<td align="center" colspan="3" rowspan="2" valign="middle"><input type="submit" class="button" name="save" value="'.$langs->trans("Modify").'">';
                    print '<br><input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
                    print '</td>';
                    // Ligne dates pr�vues
                    print "<tr $bc[$var]>";
                    print '<td colspan="5">';
                    print $langs->trans("DateStartPlanned").' ';
                    $html->select_date($objp->date_debut,"date_start_update",0,0,($objp->date_debut>0?0:1),"update");
                    print ' &nbsp; '.$langs->trans("DateEndPlanned").' ';
                    $html->select_date($objp->date_fin,"date_end_update",0,0,($objp->date_fin>0?0:1),"update");
                    if ($objp->statut >= 4)
                    {
                        print '<br>';
                        print $langs->trans("DateStartReal").' ';
                        $html->select_date($objp->date_debut_reelle,"date_start_real_update",0,0,($objp->date_debut_reelle>0?0:1),"update");
                        print ' &nbsp; ';
                        if ($objp->statut == 5)
                        {
                            print $langs->trans("DateEndReal").' ';
                            $html->select_date($objp->date_fin_reelle,"date_end_real_update",0,0,($objp->date_fin_reelle>0?0:1),"update");
                        }
                    }
                    print '</td>';
                    print '</tr>';

                    print "</form>\n";
                }
                $i++;
            }
            $db->free($result);
        }
        else
        {
            dolibarr_print_error($db);
        }


        /*
         * Ajouter une ligne produit/service
         */
        if ($user->rights->contrat->creer && $contrat->statut == 0)
        {
            print "<tr class=\"liste_titre\">";
            print '<td>'.$langs->trans("Service").'</td>';
            print '<td align="center">'.$langs->trans("VAT").'</td>';
            print '<td align="right">'.$langs->trans("PriceUHT").'</td>';
            print '<td align="center">'.$langs->trans("Qty").'</td>';
            print '<td align="right">'.$langs->trans("ReductionShort").'</td>';
            print '<td>&nbsp;</td>';
            print '<td>&nbsp;</td>';
            print "</tr>\n";

            $var=false;

            // Service sur produit pr�d�fini
            print '<form name="addligne" action="fiche.php?id='.$id.'" method="post">';
            print '<input type="hidden" name="action" value="addligne">';
            print '<input type="hidden" name="mode" value="predefined">';
            print '<input type="hidden" name="id" value="'.$id.'">';

            print "<tr $bc[$var]>";
            print '<td colspan="3">';
			// multiprix
			if($conf->global->PRODUIT_MULTIPRICES == 1)
				$html->select_produits('','p_idprod','',$conf->produit->limit_size,$contrat->societe->price_level);
			else
            	$html->select_produits('','p_idprod','',$conf->produit->limit_size);
            if (! $conf->global->PRODUIT_USE_SEARCH_TO_SELECT) print '<br>';
            print '<textarea name="desc" cols="70" rows="'.ROWS_2.'"></textarea>';
            print '</td>';

            print '<td align="center"><input type="text" class="flat" size="2" name="pqty" value="1"></td>';
            print '<td align="right" nowrap><input type="text" class="flat" size="1" name="premise" value="'.$contrat->societe->remise_client.'">%</td>';
            print '<td align="center" colspan="2" rowspan="2"><input type="submit" class="button" value="'.$langs->trans("Add").'"></td>';
            print '</tr>'."\n";
            
            print "<tr $bc[$var]>";
            print '<td colspan="8">';
            print $langs->trans("DateStartPlanned").' ';
            $html->select_date('',"date_start",0,0,1,"addligne");
            print ' &nbsp; '.$langs->trans("DateEndPlanned").' ';
            $html->select_date('',"date_end",0,0,1,"addligne");
            print '</td>';
            print '</tr>';
            
            print '</form>';

            $var=!$var;
            
            // Service libre
            print '<form name="addligne_sl" action="fiche.php?id='.$id.'" method="post">';
            print '<input type="hidden" name="action" value="addligne">';
            print '<input type="hidden" name="mode" value="libre">';
            print '<input type="hidden" name="id" value="'.$id.'">';

            print "<tr $bc[$var]>";
            print '<td><textarea name="desc" cols="70" rows="'.ROWS_2.'"></textarea></td>';

            print '<td>';
            $html->select_tva("tva_tx",$conf->defaulttx,$mysoc,$contrat->societe);
            print '</td>';
            print '<td align="right"><input type="text" class="flat" size="4" name="pu" value=""></td>';
            print '<td align="center"><input type="text" class="flat" size="2" name="pqty" value="1"></td>';
            print '<td align="right" nowrap><input type="text" class="flat" size="1" name="premise" value="'.$contrat->societe->remise_client.'">%</td>';
            print '<td align="center" rowspan="2" colspan="2"><input type="submit" class="button" value="'.$langs->trans("Add").'"></td>';

            print '</tr>'."\n";

            print "<tr $bc[$var]>";
            print '<td colspan="8">';
            print $langs->trans("DateStartPlanned").' ';
            $html->select_date('',"date_start_sl",0,0,1,"addligne_sl");
            print ' &nbsp; '.$langs->trans("DateEndPlanned").' ';
            $html->select_date('',"date_end_sl",0,0,1,"addligne_sl");
            print '</td>';
            print '</tr>';
            
            print '</form>';
        }
        print "</table>";
        /*
         * Fin Ajout ligne
         */

        print '</div>';


        /*************************************************************
         * Boutons Actions
         *************************************************************/

        if ($user->societe_id == 0)
        {
            print '<div class="tabsAction">';

            if ($contrat->statut == 0 && $num)
            {
                print '<a class="butAction" href="fiche.php?id='.$id.'&amp;action=valid">'.$langs->trans("Validate").'</a>';
            }

            if ($contrat->statut > 0 && $user->rights->facture->creer)
            {
                $langs->load("bills");
                print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/facture.php?action=create&amp;contratid='.$contrat->id.'&amp;socid='.$contrat->societe->id.'">'.$langs->trans("CreateBill").'</a>';
            }

            $numclos=$contrat->array_detail(5); // Tableau des lignes au statut clos
            if ($contrat->statut == 1 && $num == sizeof($numclos))
            {
                print '<a class="butAction" href="fiche.php?id='.$id.'&amp;action=close">'.$langs->trans("Close").'</a>';
            }

            // On peut supprimer entite si
			// - Droit de creer + mode brouillon (erreur creation)
			// - Droit de supprimer
			if (($user->rights->contrat->creer && $contrat->statut == 0) || $user->rights->contrat->supprimer)
            {
                print '<a class="butActionDelete" href="fiche.php?id='.$id.'&amp;action=delete">'.$langs->trans("Delete").'</a>';
            }

            print "</div>";
        }

    }
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
