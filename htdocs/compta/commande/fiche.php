<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
       \file       htdocs/compta/commande/fiche.php
       \ingroup    commande
       \brief      Fiche commande
       \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/order.lib.php");
require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
if ($conf->projet->enabled) require_once(DOL_DOCUMENT_ROOT."/project.class.php");

$langs->load("orders");
$langs->load("companies");
$langs->load("bills");
$langs->load('deliveries');

$user->getrights('commande');

if (! $user->rights->commande->lire) accessforbidden();

// S�curit� acc�s client
if ($user->societe_id > 0)
{
  $action = '';
  $socid = $user->societe_id;
}


/*
 *	Actions
 */

if ($_GET["action"] == 'facturee')
{
  $commande = new Commande($db);
  $commande->fetch($_GET["id"]);
  $commande->classer_facturee();
}


llxHeader('',$langs->trans("OrderCard"),"Commande");



$html = new Form($db);

/* *************************************************************************** */
/*                                                                             */
/* Mode vue et edition                                                         */
/*                                                                             */
/* *************************************************************************** */

if ($_GET["id"] > 0)
{
    $commande = new Commande($db);
    if ( $commande->fetch($_GET["id"]) > 0)
    {
        $soc = new Societe($db);
        $soc->fetch($commande->socid);

        $author = new User($db);
        $author->id = $commande->user_author_id;
        $author->fetch();

		$head = commande_prepare_head($commande);
        dolibarr_fiche_head($head, 'accountancy', $langs->trans("CustomerOrder"));

        /*
         *   Commande
         */
			$nbrow=8;
			if ($conf->projet->enabled) $nbrow++;

			print '<table class="border" width="100%">';

            // Ref
			print '<tr><td width="18%">'.$langs->trans('Ref').'</td>';
			print '<td colspan="3">'.$commande->ref.'</td>';
			print '</tr>';

			// Ref commande client
			print '<tr><td>';
            print '<table class="nobordernopadding" width="100%"><tr><td nowrap>';
			print $langs->trans('RefCustomer').'</td><td align="left">';
            print '</td>';
            if ($_GET['action'] != 'RefCustomerOrder' && $commande->brouillon) print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=RefCustomerOrder&amp;id='.$commande->id.'">'.img_edit($langs->trans('Edit')).'</a></td>';
            print '</tr></table>';
            print '</td><td colspan="3">';
			if ($user->rights->commande->creer && $_GET['action'] == 'RefCustomerOrder')
			{
				print '<form action="fiche.php?id='.$id.'" method="post">';
				print '<input type="hidden" name="action" value="set_ref_client">';
				print '<input type="text" class="flat" size="20" name="ref_client" value="'.$commande->ref_client.'">';
				print ' <input type="submit" class="button" value="'.$langs->trans('Modify').'">';
				print '</form>';
			}
			else
			{
				print $commande->ref_client;
			}
			print '</td>';
			print '</tr>';


			// Soci�t�
			print '<tr><td>'.$langs->trans('Company').'</td>';
			print '<td colspan="3">'.$soc->getNomUrl(1,'compta').'</td>';
			print '</tr>';

			// Ligne info remises tiers
            print '<tr><td>'.$langs->trans('Discounts').'</td><td colspan="3">';
			if ($soc->remise_client) print $langs->trans("CompanyHasRelativeDiscount",$soc->remise_client);
			else print $langs->trans("CompanyHasNoRelativeDiscount");
			$absolute_discount=$soc->getCurrentDiscount();
			print '. ';
			if ($absolute_discount)
			{
				if ($commande->statut > 0)
				{
					print $langs->trans("CompanyHasAbsoluteDiscount",$absolute_discount,$langs->trans("Currency".$conf->monnaie));
				}
				else
				{
					print '<br>';
					print $html->form_remise_dispo($_SERVER["PHP_SELF"].'?id='.$commande->id,0,'remise_id',$soc->id,$absolute_discount);
				}
			}
			else print $langs->trans("CompanyHasNoAbsoluteDiscount").'.';
			print '</td></tr>';

			// Date
			print '<tr><td>'.$langs->trans('Date').'</td>';
			print '<td colspan="2">'.dolibarr_print_date($commande->date,'%A %d %B %Y').'</td>';
			print '<td width="50%">'.$langs->trans('Source').' : ' . $commande->sources[$commande->source] ;
			if ($commande->source == 0)
			{
				// Si source = propal
				$propal = new Propal($db);
				$propal->fetch($commande->propale_id);
				print ' -> <a href="'.DOL_URL_ROOT.'/comm/propal.php?propalid='.$propal->id.'">'.$propal->ref.'</a>';
			}
			print '</td>';
			print '</tr>';

			// Date de livraison
			print '<tr><td height="10">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('DateDelivery');
			print '</td>';

			if (1 == 2 && $_GET['action'] != 'editdate_livraison' && $commande->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editdate_livraison&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetDateDelivery'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="2">';
			if ($_GET['action'] == 'editdate_livraison')
			{
				print '<form name="setdate_livraison" action="'.$_SERVER["PHP_SELF"].'?id='.$commande->id.'" method="post">';
                print '<input type="hidden" name="action" value="setdate_livraison">';
                $html->select_date($commande->date_livraison,'liv_','','','',"setdate_livraison");
                print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
                print '</form>';
			}
			else
			{
				print dolibarr_print_date($commande->date_livraison,'%A %d %B %Y');
			}
			print '</td>';
			print '<td rowspan="'.$nbrow.'" valign="top">'.$langs->trans('NotePublic').' :<br>';
      		print nl2br($commande->note_public);
			print '</td>';
			print '</tr>';


			// Adresse de livraison
			print '<tr><td height="10">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('DeliveryAddress');
			print '</td>';

			if (1 == 2 && $_GET['action'] != 'editdelivery_adress' && $commande->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editdelivery_adress&amp;socid='.$commande->socid.'&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetDeliveryAddress'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="2">';

			if ($_GET['action'] == 'editdelivery_adress')
			{
				$html->form_adresse_livraison($_SERVER['PHP_SELF'].'?id='.$commande->id,$commande->adresse_livraison_id,$_GET['socid'],'adresse_livraison_id','commande',$commande->id);
			}
			else
			{
				$html->form_adresse_livraison($_SERVER['PHP_SELF'].'?id='.$commande->id,$commande->adresse_livraison_id,$_GET['socid'],'none','commande',$commande->id);
			}
			print '</td></tr>';

			// Conditions et modes de r�glement
			print '<tr><td height="10">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('PaymentConditionsShort');
			print '</td>';

			if ($_GET['action'] != 'editconditions' && $commande->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editconditions&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetConditions'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="2">';
			if ($_GET['action'] == 'editconditions')
			{
				$html->form_conditions_reglement($_SERVER['PHP_SELF'].'?id='.$commande->id,$commande->cond_reglement_id,'cond_reglement_id');
			}
			else
			{
				$html->form_conditions_reglement($_SERVER['PHP_SELF'].'?id='.$commande->id,$commande->cond_reglement_id,'none');
			}
			print '</td></tr>';
			print '<tr><td height="10">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('PaymentMode');
			print '</td>';
			if ($_GET['action'] != 'editmode' && $commande->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editmode&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetMode'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="2">';
			if ($_GET['action'] == 'editmode')
			{
				$html->form_modes_reglement($_SERVER['PHP_SELF'].'?id='.$commande->id,$commande->mode_reglement_id,'mode_reglement_id');
			}
			else
			{
				$html->form_modes_reglement($_SERVER['PHP_SELF'].'?id='.$commande->id,$commande->mode_reglement_id,'none');
			}
			print '</td></tr>';

            // Projet
            if ($conf->projet->enabled)
            {
                $langs->load('projects');
                print '<tr><td height="10">';
                print '<table class="nobordernopadding" width="100%"><tr><td>';
                print $langs->trans('Project');
                print '</td>';
                if ($_GET['action'] != 'classer' && $commande->brouillon) print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=classer&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetProject')).'</a></td>';
                print '</tr></table>';
                print '</td><td colspan="2">';
                if ($_GET['action'] == 'classer')
                {
                    $html->form_project($_SERVER['PHP_SELF'].'?id='.$commande->id, $commande->socid, $commande->projet_id, 'projetid');
                }
                else
                {
                    $html->form_project($_SERVER['PHP_SELF'].'?id='.$commande->id, $commande->socid, $commande->projet_id, 'none');
                }
                print '</td></tr>';
            }

			// Lignes de 3 colonnes

            // Total HT
			print '<tr><td>'.$langs->trans('AmountHT').'</td>';
			print '<td align="right"><b>'.price($commande->total_ht).'</b></td>';
			print '<td>'.$langs->trans('Currency'.$conf->monnaie).'</td></tr>';

			// Total TVA
			print '<tr><td>'.$langs->trans('AmountVAT').'</td><td align="right">'.price($commande->total_tva).'</td>';
			print '<td>'.$langs->trans('Currency'.$conf->monnaie).'</td></tr>';

			// Total TTC
			print '<tr><td>'.$langs->trans('AmountTTC').'</td><td align="right">'.price($commande->total_ttc).'</td>';
			print '<td>'.$langs->trans('Currency'.$conf->monnaie).'</td></tr>';

			// Statut
			print '<tr><td>'.$langs->trans('Status').'</td>';
			print '<td colspan="2">'.$commande->getLibStatut(4).'</td>';
			print '</tr>';

			print '</table>';

		/*
		 * Lignes de commandes
		 */
		$sql = 'SELECT l.fk_product, l.description, l.price, l.qty, l.rowid, l.tva_tx, l.remise_percent, l.subprice, l.info_bits,';
		$sql.= ' p.label as product, p.ref, p.fk_product_type, p.rowid as prodid,';
		$sql.= ' p.description as product_desc';
		$sql.= ' FROM '.MAIN_DB_PREFIX."commandedet as l";
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON l.fk_product=p.rowid';
		$sql.= " WHERE l.fk_commande = ".$commande->id;
		$sql.= " ORDER BY l.rang, l.rowid";
		
        $resql = $db->query($sql);
        if ($resql)
        {
            $num = $db->num_rows($resql);
            $i = 0; $total = 0;

            if ($num) print '<br>';
            print '<table class="noborder" width="100%">';
            if ($num)
            {
                print '<tr class="liste_titre">';
                print '<td>'.$langs->trans('Description').'</td>';
                print '<td align="right" width="50">'.$langs->trans('VAT').'</td>';
                print '<td align="right" width="80">'.$langs->trans('PriceUHT').'</td>';
                print '<td align="right" width="50">'.$langs->trans('Qty').'</td>';
				print '<td align="right" width="50">'.$langs->trans('ReductionShort').'</td>';
				print '<td align="right" width="50">'.$langs->trans('AmountHT').'</td>';
                print '<td>&nbsp;</td>';
                print '<td>&nbsp;</td>';
                print '<td>&nbsp;</td>';
                print "</tr>\n";
            }

            $var=true;
            while ($i < $num)
            {
                $objp = $db->fetch_object($resql);

                $var=!$var;
                print '<tr '.$bc[$var].'>';
                if ($objp->fk_product > 0)
                {
                    print '<td><a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">';
                    if ($objp->fk_product_type==1) print img_object($langs->trans('ShowService'),'service');
                    else print img_object($langs->trans('ShowProduct'),'product');
                    print ' '.$objp->ref.'</a> - '.nl2br($objp->product);
                    print ($objp->description && $objp->description!=$objp->product)?'<br>'.stripslashes(nl2br($objp->description)):'';
                    print '</td>';
                }
                else
                {
                    print '<td>';
					if (($objp->info_bits & 2) == 2)
					{
						print '<a href="'.DOL_URL_ROOT.'/comm/remx.php?id='.$commande->socid.'">';
						print img_object($langs->trans("ShowReduc"),'reduc').' '.$langs->trans("Discount");
						print '</a>';
						if ($objp->description) print ' - '.nl2br($objp->description);
					}
					else
					{
						print nl2br($objp->description);
					}
                    print "</td>\n";
                }
                print '<td align="right">'.$objp->tva_tx.'%</td>';

                print '<td align="right">'.price($objp->subprice)."</td>\n";

                print '<td align="right">';
				if (($objp->info_bits & 2) != 2)
				{
					print $objp->qty;
				}
				else print '&nbsp;';
				print '</td>';
									
                if ($objp->remise_percent > 0)
                {
                    print '<td align="right">'.$objp->remise_percent."%</td>\n";
                }
                else
                {
                    print '<td>&nbsp;</td>';
                }

                print '<td align="right">'.price($objp->subprice*$objp->qty*(100-$objp->remise_percent)/100)."</td>\n";

                print '<td>&nbsp;</td>';
                print '<td>&nbsp;</td>';
                print '<td>&nbsp;</td>';
                print '</tr>';

				$total = $total + ($objp->qty * $objp->price);
                $i++;
            }
            $db->free($resql);
        }
        else
        {
            dolibarr_print_error($db);
        }

			/*
			 * Lignes de remise
			 */

    		// R�ductions relatives (Remises-Ristournes-Rabbais)
/* Une r�duction doit s'appliquer obligatoirement sur des lignes de factures
   et non globalement
			$var=!$var;
			print '<form name="updateligne" action="'.$_SERVER["PHP_SELF"].'" method="post">';
			print '<input type="hidden" name="action" value="setremisepercent">';
			print '<input type="hidden" name="id" value="'.$commande->id.'">';
			print '<tr class="liste_total"><td>';
			print $langs->trans('CustomerRelativeDiscount');
			if ($commande->brouillon) print ' <font style="font-weight: normal">('.($soc->remise_client?$langs->trans("CompanyHasRelativeDiscount",$soc->remise_client):$langs->trans("CompanyHasNoRelativeDiscount")).')</font>';
			print '</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td align="right"><font style="font-weight: normal">';
			if ($_GET['action'] == 'editrelativediscount')
			{
				print '<input type="text" name="remise_percent" size="2" value="'.$commande->remise_percent.'">%';
			}
			else
			{
				print $commande->remise_percent?$commande->remise_percent.'%':'&nbsp;';
			}
			print '</font></td>';
			print '<td align="right"><font style="font-weight: normal">';
			if ($_GET['action'] != 'editrelativediscount') print $commande->remise_percent?'-'.price($commande->remise_percent*$total/100):$langs->trans("DiscountNone");
			else print '&nbsp;';
			print '</font></td>';
			if ($_GET['action'] != 'editrelativediscount')
			{
				if (1 == 2 && $commande->brouillon && $user->rights->facture->creer)
				{
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editrelativediscount&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetRelativeDiscount'),1).'</a></td>';
				}
				else
				{
					print '<td>&nbsp;</td>';
				}
				if (1 == 2 && $commande->brouillon && $user->rights->facture->creer && $commande->remise_percent)
				{
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?id='.$commande->id.'&amp;action=setremisepercent&amp;rowid='.$objp->rowid.'">';
					print img_delete();
					print '</a></td>';
				}
				else
				{
					print '<td>&nbsp;</td>';
				}
				print '<td>&nbsp;</td>';
			}
			else
			{
				print '<td colspan="3"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
			}
			print '</tr>';
			print '</form>';
*/

	        // Remises absolue
/* Les remises absolues doivent s'appliquer par ajout de lignes sp�cialis�es
			$var=!$var;
			print '<form name="updateligne" action="'.$_SERVER["PHP_SELF"].'" method="post">';
			print '<input type="hidden" name="action" value="setremiseabsolue">';
			print '<input type="hidden" name="id" value="'.$commande->id.'">';
			print '<tr class="liste_total"><td>';
			print $langs->trans('CustomerAbsoluteDiscount');
			if ($commande->brouillon) print ' <font style="font-weight: normal">('.($avoir_en_cours?$langs->trans("CompanyHasAbsoluteDiscount",$avoir_en_cours,$langs->trans("Currency".$conf->monnaie)):$langs->trans("CompanyHasNoAbsoluteDiscount")).')</font>';
			print '</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td align="right"><font style="font-weight: normal">';
			if ($_GET['action'] == 'editabsolutediscount')
			{
				print '-<input type="text" name="remise_absolue" size="2" value="'.$commande->remise_absolue.'">';
			}
			else
			{
				print $commande->remise_absolue?'-'.price($commande->remise_absolue):$langs->trans("DiscountNone");
			}
			print '</font></td>';
			if ($_GET['action'] != 'editabsolutediscount')
			{
				if (1 == 2 && $commande->brouillon && $user->rights->facture->creer)
				{
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editabsolutediscount&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetAbsoluteDiscount'),1).'</a></td>';
				}
				else
				{
					print '<td>&nbsp;</td>';
				}
				if (1 == 2 && $commande->brouillon && $user->rights->facture->creer && $commande->remise_absolue)
				{
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?id='.$commande->id.'&amp;action=setremiseabsolue&amp;rowid='.$objp->rowid.'">';
					print img_delete();
					print '</a></td>';
				}
				else
				{
					print '<td>&nbsp;</td>';
				}
				print '<td>&nbsp;</td>';
			}
			else
			{
				print '<td colspan="3"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
			}
			print '</tr>';
			print '</form>';
*/

        print '</table>';

        print '</div>';


        /*
        * Boutons actions
        */

        if (! $user->societe_id && ! $commande->facturee)
        {
            print "<div class=\"tabsAction\">\n";

            if ($commande->statut > 0 && $user->rights->facture->creer)
            {
                print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/facture.php?action=create&amp;commandeid='.$commande->id.'&amp;socid='.$commande->socid.'">'.$langs->trans("CreateBill").'</a>';
            }

            if ($commande->statut > 0 && $user->rights->commande->creer)
            {
                print '<a class="butAction" href="'.DOL_URL_ROOT.'/compta/commande/fiche.php?action=facturee&amp;id='.$commande->id.'">'.$langs->trans("ClassifyBilled").'</a>';
            }
            print '</div>';
        }


		print '<table width="100%"><tr><td width="50%" valign="top">';
		
		
		/*
		* Documents g�n�r�s
		*
		*/
		$comref = sanitize_string($commande->ref);
		$file = $conf->commande->dir_output . '/' . $comref . '/' . $comref . '.pdf';
		$relativepath = $comref.'/'.$comref.'.pdf';
		$filedir = $conf->commande->dir_output . '/' . $comref;
		$urlsource=$_SERVER["PHP_SELF"]."?id=".$commande->id;
		$genallowed=0;
		$delallowed=0;
		
		$somethingshown=$html->show_documents('commande',$comref,$filedir,$urlsource,$genallowed,$delallowed,$commande->modelpdf);

        /*
        * Liste des factures
        */
        $sql = "SELECT f.rowid,f.facnumber, f.total_ttc, ".$db->pdate("f.datef")." as df";
        $sql .= " FROM ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."co_fa as cf";
        $sql .= " WHERE f.rowid = cf.fk_facture AND cf.fk_commande = ". $commande->id;

        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            if ($num)
            {
                print '<br>';
                print_titre($langs->trans("RelatedBills"));
                $i = 0; $total = 0;
                print '<table class="noborder" width="100%">';
                print '<tr class="liste_titre"><td>'.$langs->trans("Ref")."</td>";
                print '<td align="center">'.$langs->trans("Date").'</td>';
                print '<td align="right">'.$langs->trans("Price").'</td>';
                print "</tr>\n";

                $var=True;
                while ($i < $num)
                {
                    $objp = $db->fetch_object($result);
                    $var=!$var;
                    print "<tr $bc[$var]>";
                    print '<td><a href="../facture.php?facid='.$objp->rowid.'">'.img_object($langs->trans("ShowBill"),"bill").' '.$objp->facnumber.'</a></td>';
                    print '<td align="center">'.dolibarr_print_date($objp->df).'</td>';
                    print '<td align="right">'.$objp->total_ttc.'</td></tr>';
                    $i++;
                }
                print "</table>";
            }
        }
        else
        {
            dolibarr_print_error($db);
        }

        print '</td><td valign="top" width="50%">';

		// Rien a droite

        print "</td></tr></table>";
        
        
        /*
         * 	Liste des exp�ditions
         */
        $sql = "SELECT cd.fk_product, cd.description, cd.rowid, cd.qty as qty_commande";
        $sql .= " , ed.qty as qty_livre, e.ref, ed.fk_expedition as expedition_id";
        $sql .= ",".$db->pdate("e.date_expedition")." as date_expedition";
        if ($conf->livraison->enabled) $sql .= ", l.rowid as livraison_id, l.ref as livraison_ref";
        $sql .= " FROM ".MAIN_DB_PREFIX."commandedet as cd";
        $sql .= " , ".MAIN_DB_PREFIX."expeditiondet as ed, ".MAIN_DB_PREFIX."expedition as e";
        if ($conf->livraison->enabled) $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."livraison as l ON l.fk_expedition = e.rowid";
        $sql .= " WHERE cd.fk_commande = ".$commande->id;
        $sql .= " AND cd.rowid = ed.fk_commande_ligne";
        $sql .= " AND ed.fk_expedition = e.rowid";
        $sql .= " ORDER BY cd.fk_product";

        $resql = $db->query($sql);
        if ($resql)
        {
            $num = $db->num_rows($resql);
	        $i = 0;

            if ($num)
            {
                if ($somethingshown) print '<br>';
                
                print_titre($langs->trans("SendingsAndReceivingForSameOrder"));
                print '<table class="liste" width="100%">';
                print '<tr class="liste_titre">';
                print '<td>'.$langs->trans("Product").'</td>';
                print '<td align="center">'.$langs->trans("QtyShipped").'</td>';
                print '<td align="center">'.$langs->trans("DateSending").'</td>';
                if ($conf->expedition->enabled)
                {
                	print '<td>'.$langs->trans("SendingSheet").'</td>';
                }
                if ($conf->livraison->enabled)
                {
                	print '<td>'.$langs->trans("DeliveryOrder").'</td>';
                }
                print "</tr>\n";

                $var=True;
                while ($i < $num)
                {
                    $var=!$var;
                    $objp = $db->fetch_object($resql);
                    print "<tr $bc[$var]>";
                    
                    if ($objp->fk_product > 0)
                    {
            	      $product = new Product($db);
            	      $product->fetch($objp->fk_product);
            	      
            	      print '<td>';
            	      print '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">'.img_object($langs->trans("ShowProduct"),"product").' '.$product->ref.'</a> - '.$product->libelle;
            	      if ($objp->description) print '<br>'.nl2br($objp->description);
            	      print '</td>';
                    }
                    else
                    {
                        print "<td>".nl2br($objp->description)."</td>\n";
                    }
                    print '<td align="center">'.$objp->qty_livre.'</td>';
                    print '<td align="center">'.dolibarr_print_date($objp->date_expedition).'</td>';
                    if ($conf->expedition->enabled)
                    {
	                    print '<td align="left"><a href="'.DOL_URL_ROOT.'/expedition/fiche.php?id='.$objp->expedition_id.'">'.img_object($langs->trans("ShowSending"),'sending').' '.$objp->ref.'<a></td>';
                    }
                    if ($conf->livraison->enabled)
                    {
                    	if ($objp->livraison_id)
                    	{
                    		print '<td><a href="'.DOL_URL_ROOT.'/livraison/fiche.php?id='.$objp->livraison_id.'">'.img_object($langs->trans("ShowSending"),'generic').' '.$objp->livraison_ref.'<a></td>';
                    	}
                    	else
                    	{
                    		print '<td>&nbsp;</td>';
                    	}
                    }
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
        
    }
    else
    {
        // Commande non trouv�e
        print "Commande inexistante";
    }
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
