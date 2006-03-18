<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Marc Barilley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2006 Regis Houssin         <regis.houssin@cap-networks.com>
 * Copyright (C) 2006      Andre Cianfarani      <acianfa@free.fr>
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
       \file       htdocs/commande/fiche.php
       \ingroup    commande
       \brief      Fiche commande
       \version    $Revision$
*/

require('./pre.inc.php');
require_once(DOL_DOCUMENT_ROOT ."/includes/modules/commande/modules_commande.php");
if ($conf->projet->enabled) require_once(DOL_DOCUMENT_ROOT.'/project.class.php');
require_once(DOL_DOCUMENT_ROOT.'/propal.class.php');
require_once(DOL_DOCUMENT_ROOT.'/commande/commande.class.php');

$langs->load('orders');
$langs->load('sendings');
$langs->load('companies');
$langs->load('bills');
$langs->load('propal');

$user->getrights('commande');
$user->getrights('expedition');

if (!$user->rights->commande->lire) accessforbidden();


// S�curit� acc�s client
$socidp=0;
if ($user->societe_id > 0)
{
	$action = '';
	$socidp = $user->societe_id;
}


/*
 * Actions
 */

// Categorisation dans projet
if ($_POST['action'] == 'classin' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$commande->classin($_POST['projetid']);
}

// Ajout commande
if ($_POST['action'] == 'add' && $user->rights->commande->creer)
{
	$datecommande='';
	$datecommande  = @mktime(12, 0, 0, $_POST['remonth'],  $_POST['reday'],  $_POST['reyear']);
	$datelivraison = @mktime(12, 0, 0, $_POST['liv_month'],$_POST['liv_day'],$_POST['liv_year']);
	
	$commande = new Commande($db);

	$commande->soc_id            = $_POST['soc_id'];
	$commande->date_commande     = $datecommande;
	$commande->note              = $_POST['note'];
	$commande->source            = $_POST['source_id'];
	$commande->projetid          = $_POST['projetid'];
	$commande->remise_percent    = $_POST['remise_percent'];
	$commande->ref_client        = $_POST['ref_client'];
	$commande->modelpdf          = $_POST['model'];
	$commande->cond_reglement_id = $_POST['cond_reglement_id'];
    $commande->mode_reglement_id = $_POST['mode_reglement_id'];
    $commande->date_livraison    = $datelivraison;

	$commande->add_product($_POST['idprod1'],$_POST['qty1'],$_POST['remise_percent1']);
	$commande->add_product($_POST['idprod2'],$_POST['qty2'],$_POST['remise_percent2']);
	$commande->add_product($_POST['idprod3'],$_POST['qty3'],$_POST['remise_percent3']);
	$commande->add_product($_POST['idprod4'],$_POST['qty4'],$_POST['remise_percent4']);
	$commande->add_product($_POST['idprod5'],$_POST['qty5'],$_POST['remise_percent5']);
	$commande->add_product($_POST['idprod6'],$_POST['qty6'],$_POST['remise_percent6']);
	$commande->add_product($_POST['idprod7'],$_POST['qty7'],$_POST['remise_percent7']);
	$commande->add_product($_POST['idprod8'],$_POST['qty8'],$_POST['remise_percent8']);

	$commande_id = $commande->create($user);

	if ($commande_id <= 0)
	{
		$_GET['action']='create';
		$_GET['socidp']=$_POST['soc_id'];
		$mesg='<div class="error">'.$commande->error.'</div>';
	}
	else
	{
		$_GET['id'] = $commande->id;
		$action = '';
	}
}

// Positionne ref commande client
if ($_POST['action'] == 'set_ref_client' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$commande->set_ref_client($user, $_POST['ref_client']);
}

if ($_POST['action'] == 'setremise' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$commande->set_remise($user, $_POST['remise']);
}

if ($_POST['action'] == 'setnote' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$commande->set_note($user, $_POST['note']);
}
if ($_POST['action'] == 'setdate_livraison' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$commande->set_date_livraison($user,$_POST['liv_year']."-".$_POST['liv_month']."-".$_POST['liv_day']);
}

if ($_POST['action'] == 'setmode' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$result=$commande->mode_reglement($_POST['mode_reglement_id']);
	if ($result < 0) dolibarr_print_error($db,$commande->error);
}

if ($_POST['action'] == 'setconditions' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$result=$commande->cond_reglement($_POST['cond_reglement_id']);
	if ($result < 0) dolibarr_print_error($db,$commande->error);
}

if ($_POST['action'] == 'addligne' && $user->rights->commande->creer)
{
	/*
	 *  Ajout d'une ligne produit dans la commande
	 */
	if ($_POST['qty'] && (($_POST['pu'] && $_POST['desc']) || $_POST['p_idprod']))
	{
		$commande = new Commande($db);
		$ret=$commande->fetch($_POST['id']);

		if (isset($_POST['p_idprod']))
		{
			$result = $commande->addline(
			$_POST['np_desc'],
			$_POST['product_desc'],
			$_POST['pu'],
			$_POST['qty'],
			$_POST['tva_tx'],
			$_POST['p_idprod'],
			$_POST['remise_percent']);
		}
		else
		{
			$result = $commande->addline_libre(
			$_POST['desc'],
			$_POST['pu'],
			$_POST['qty'],
			$_POST['tva_tx'],
			0,
			$_POST['remise_percent']);
		}
	}
}

if ($_POST['action'] == 'updateligne' && $user->rights->commande->creer && $_POST['save'] == $langs->trans('Save'))
{
	$commande = new Commande($db,'',$_POST['id']);
	if (! $commande->fetch($_POST['id']) > 0)
		dolibarr_print_error($db);
	$result = $commande->update_line($_POST['elrowid'],
		$_POST['eldesc'],
		$_POST['elprice'],
		$_POST['elqty'],
		$_POST['elremise_percent'],
		$_POST['eltva_tx']
		);

	$_GET['id']=$_POST['id'];   // Pour r�affichage de la fiche en cours d'�dition
}

if ($_POST['action'] == 'updateligne' && $user->rights->commande->creer && $_POST['cancel'] == $langs->trans('Cancel'))
{
	Header('Location: fiche.php?id='.$_POST['id']);   // Pour r�affichage de la fiche en cours d'�dition
	exit;
}

if ($_GET['action'] == 'deleteline' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$result = $commande->delete_line($_GET['lineid']);
	Header('Location: fiche.php?id='.$_GET['id']);
	exit;
}

if ($_POST['action'] == 'confirm_valid' && $_POST['confirm'] == 'yes' && $user->rights->commande->valider)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$soc = new Societe($db);
	$soc->fetch($commande->soc_id);
	$result = $commande->valid($user);
}

if ($_POST['action'] == 'confirm_close' && $_POST['confirm'] == 'yes' && $user->rights->commande->creer)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$result = $commande->cloture($user);
}

if ($_POST['action'] == 'confirm_cancel' && $_POST['confirm'] == 'yes' && $user->rights->commande->valider)
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	$result = $commande->cancel($user);
}

if ($_POST['action'] == 'confirm_delete' && $_POST['confirm'] == 'yes')
{
	if ($user->rights->commande->supprimer )
	{
		$commande = new Commande($db);
		$commande->id = $_GET['id'];
		$commande->delete();
		Header('Location: index.php');
		exit;
	}
}

if ($_POST['action'] == 'builddoc')
{
	/*
	 * Generation de la commande
	 * d�finit dans /includes/modules/commande/modules_commande.php
	 */
	commande_pdf_create($db, $_GET['id'],$_POST['model']);
		
}
if($_GET['action'] == 'builddoc')
{
	$commande = new Commande($db);
	$commande->fetch($_GET['id']);
	commande_pdf_create($db, $_GET['id'],$commande->modelpdf);
}


llxHeader('',$langs->trans('OrderCard'),'Commande');


$html = new Form($db);

/*********************************************************************
 *
 * Mode creation
 *
 *********************************************************************/
if ($_GET['action'] == 'create' && $user->rights->commande->creer)
{
	print_titre($langs->trans('CreateOrder'));

	if ($mesg) print $mesg.'<br>';
	
	$new_commande = new Commande($db);

	if ($propalid)
	{
		$sql = 'SELECT s.nom, s.prefix_comm, s.idp, p.price, p.remise, p.remise_percent, p.tva, p.total, p.ref, p.fk_cond_reglement, p.fk_mode_reglement, '.$db->pdate('p.datep').' as dp, c.id as statut, c.label as lst';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'societe as s, '.MAIN_DB_PREFIX.'propal as p, '.MAIN_DB_PREFIX.'c_propalst as c';
		$sql .= ' WHERE p.fk_soc = s.idp AND p.fk_statut = c.id';
		$sql .= ' AND p.rowid = '.$propalid;
	}
	else
	{
		$sql = 'SELECT s.nom, s.prefix_comm, s.idp, s.mode_reglement, s.cond_reglement ';
		$sql .= 'FROM '.MAIN_DB_PREFIX.'societe as s ';
		$sql .= 'WHERE s.idp = '.$_GET['socidp'];
	}
	$resql = $db->query($sql);
	if ( $resql )
    {
		$num = $db->num_rows($resql);
		if ($num)
		{
			$obj = $db->fetch_object($resql);

			$soc = new Societe($db);
			$soc->fetch($obj->idp);
			
			$nbrow=8;
			if ($conf->projet->enabled) $nbrow++;

			print '<form name="crea_commande" action="fiche.php" method="post">';
			print '<input type="hidden" name="action" value="add">';
			print '<input type="hidden" name="soc_id" value="'.$soc->id.'">' ."\n";
			print '<input type="hidden" name="remise_percent" value="0">';
			print '<input name="facnumber" type="hidden" value="provisoire">';

			print '<table class="border" width="100%">';

			// Reference
			print '<tr><td>'.$langs->trans('Ref').'</td><td>Provisoire</td>';
			print '<td>'.$langs->trans('Comments').'</td></tr>';

			// Reference client
			print '<tr><td>'.$langs->trans('RefCustomer').'</td><td>';
			print '<input type="text" name="ref_client" value=""></td>';
			print '<td rowspan="'.$nbrow.'" valign="top"><textarea name="note" wrap="soft" cols="50" rows="12"></textarea></td>';
			print '</tr>';

			// Client
			print '<tr><td>'.$langs->trans('Customer').'</td><td>'.$soc->nom_url.'</td>';
			print '</tr>';

			print '<tr><td>'.$langs->trans('Date').'</td><td>';
			$html->select_date('','re','','','',"crea_commande");
			print '</td></tr>';
			
			// Date de livraison
			print "<tr><td>".$langs->trans("DateDelivery")."</td><td>";
			if ($conf->global->DATE_LIVRAISON_WEEK_DELAY)
			{
    			$tmpdte = time() + ((7*$conf->global->DATE_LIVRAISON_WEEK_DELAY) * 24 * 60 * 60);
				$html->select_date($tmpdte,'liv_','','',1,"crea_commande");
			}
			else
			{
				$html->select_date(-1,'liv_','','',1,"crea_commande");
    		}
    		print "</td></tr>";
	
			
			// Conditions de r�glement
		    print '<tr><td nowrap>'.$langs->trans('PaymentConditions').'</td><td>';
		    $html->select_conditions_paiements($soc->cond_reglement,'cond_reglement_id',-1,1);
		    print '</td></tr>';
	
		    // Mode de r�glement
		    print '<tr><td>'.$langs->trans('PaymentMode').'</td><td>';
		    $html->select_types_paiements($soc->mode_reglement,'mode_reglement_id');
		    print '</td></tr>';

			if ($conf->projet->enabled)
			{
				print '<tr><td>'.$langs->trans('Project').'</td><td>';
				$html->select_projects($soc->id,'','projetid');
				print '</td></tr>';
			}

			print '<tr><td>'.$langs->trans('Source').'</td><td>';
			$html->selectSourcesCommande('','source_id',1);
			print '</td></tr>';
			print '<tr><td>Mod�le</td>';
			print '<td>';
			// pdf
			include_once(DOL_DOCUMENT_ROOT.'/includes/modules/commande/modules_commande.php');
			$model=new ModelePDFCommandes();
			$liste=$model->liste_modeles($db);
			$html->select_array("model",$liste,$conf->global->COMMANDE_ADDON_PDF);
			print "</td></tr>";
			
			if ($propalid > 0)
			{
				$amount = ($obj->price);
				print '<input type="hidden" name="amount"   value="'.$amount.'">'."\n";
				print '<input type="hidden" name="total"    value="'.$obj->total.'">'."\n";
				print '<input type="hidden" name="remise"   value="'.$obj->remise.'">'."\n";
				print '<input type="hidden" name="remise_percent"   value="'.$obj->remise_percent.'">'."\n";
				print '<input type="hidden" name="tva"      value="'.$obj->tva.'">'."\n";
				print '<input type="hidden" name="propalid" value="'.$propalid.'">';

				print '<tr><td>'.$langs->trans('Ref').'</td><td colspan="2">'.$obj->ref.'</td></tr>';
				print '<tr><td>'.$langs->trans('TotalTTC').'</td><td colspan="2">'.price($amount).'</td></tr>';
				print '<tr><td>'.$langs->trans('VAT').'</td><td colspan="2">'.price($obj->tva).'</td></tr>';
				print '<tr><td>'.$langs->trans('TotalTTC').'</td><td colspan="2">'.price($obj->total).'</td></tr>';
			}
			else
			{
				/*
				* Services/produits pr�d�finis
				*/
				$NBLINES=8;

				print '<tr><td colspan="3">';

				print '<table class="noborder">';
				print '<tr><td>'.$langs->trans('ProductsAndServices').'</td><td>'.$langs->trans('Qty').'</td><td>'.$langs->trans('Discount').'</td></tr>';
				for ($i = 1 ; $i <= $NBLINES ; $i++)
				{
					print '<tr><td>';
					// multiprix
					if($conf->global->PRODUIT_MULTIPRICES == 1)
						print $html->select_produits('','idprod'.$i,'',$conf->produit->limit_size,$soc->price_level);
					else
						print $html->select_produits('','idprod'.$i,'',$conf->produit->limit_size);
					print '</td>';
					print '<td><input type="text" size="3" name="qty'.$i.'" value="1"></td>';
					print '<td><input type="text" size="3" name="remise_percent'.$i.'" value="0">%</td></tr>';
				}

				print '</table>';
				print '</td></tr>';
			}

			/*
			 *
			 */
			print '<tr><td colspan="3" align="center"><input type="submit" class="button" value="'.$langs->trans('CreateDraft').'"></td></tr>';
			print '</form>';
			print '</table>';

			if ($propalid)
			{
				/*
				* Produits
				*/
				print_titre($langs->trans('Products'));
				print '<table class="noborder" width="100%">';
				print '<tr class="liste_titre"><td>'.$langs->trans('Ref').'</td><td>'.$langs->trans('Product').'</td>';
				print '<td align="right">'.$langs->trans('Price').'</td><td align="center">'.$langs->trans('Discount').'</td><td align="center">'.$langs->trans('Qty').'</td></tr>';

				$var=false;

				$sql = 'SELECT pt.rowid, p.label as product, p.ref, pt.price, pt.qty, p.rowid as prodid, pt.remise_percent';
				$sql .= ' FROM '.MAIN_DB_PREFIX.'propaldet as pt, '.MAIN_DB_PREFIX.'product as p WHERE pt.fk_product = p.rowid AND pt.fk_propal = '.$propalid;
				$sql .= ' ORDER BY pt.rowid ASC';
				$result = $db->query($sql);
				if ($result)
				{
					$num = $db->num_rows($result);
					$i = 0;
					while ($i < $num)
					{
						$objp = $db->fetch_object($result);
						$var=!$var;
						print '<tr '.$bc[$var].'><td>['.$objp->ref.']</td>';
						print '<td>'.img_object($langs->trans('ShowProduct'),'product').' '.$objp->product.'</td>';
						print '<td align="right">'.price($objp->price).'</td>';
						print '<td align="center">'.$objp->remise_percent.'%</td>';
						print '<td align="center">'.$objp->qty.'</td></tr>';
						$i++;
					}
				}

				$sql = 'SELECT pt.rowid, pt.description as product,  pt.price, pt.qty, pt.remise_percent';
				$sql .= ' FROM '.MAIN_DB_PREFIX.'propaldet as pt  WHERE  pt.fk_propal = '.$propalid.' AND pt.fk_product = 0';
				$sql .= ' ORDER BY pt.rowid ASC';
				if ($db->query($sql))
				{
					$num = $db->num_rows();
					$i = 0;
					while ($i < $num)
					{
						$objp = $db->fetch_object();
						$var=!$var;
						print '<tr '.$bc[$var].'><td>&nbsp;</td>';
						print '<td>'.img_object($langs->trans('ShowProduct'),'product').' '.$objp->product.'</td>';
						print '<td align="right">'.price($objp->price).'</td>';
						print '<td align="center">'.$objp->remise_percent.'%</td>';
						print '<td align="center">'.$objp->qty.'</td></tr>';
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
}
else
/* *************************************************************************** */
/*                                                                             */
/* Mode vue et edition                                                         */
/*                                                                             */
/* *************************************************************************** */
{
	$id = $_GET['id'];
	if ($id > 0)
	{
		$commande = new Commande($db);
		if ( $commande->fetch($_GET['id']) > 0)
		{
			$soc = new Societe($db);
			$soc->fetch($commande->soc_id);

			$author = new User($db);
			$author->id = $commande->user_author_id;
			$author->fetch();

			$h=0;

			if ($conf->commande->enabled && $user->rights->commande->lire)
			{
				$head[$h][0] = DOL_URL_ROOT.'/commande/fiche.php?id='.$commande->id;
				$head[$h][1] = $langs->trans('OrderCard');
				$hselected = $h;
				$h++;
			}

			if ($conf->expedition->enabled && $user->rights->expedition->lire)
			{
				$head[$h][0] = DOL_URL_ROOT.'/expedition/commande.php?id='.$commande->id;
				$head[$h][1] = $langs->trans('SendingCard');
				$h++;
			}

			if ($conf->compta->enabled || $conf->comptaexpert->enabled)
			{
				$head[$h][0] = DOL_URL_ROOT.'/compta/commande/fiche.php?id='.$commande->id;
				$head[$h][1] = $langs->trans('ComptaCard');
				$h++;
			}
			
			if ($conf->use_preview_tabs)
		  	{
	    		$head[$h][0] = DOL_URL_ROOT.'/commande/apercu.php?id='.$commande->id;
	    		$head[$h][1] = $langs->trans("Preview");
	    		$h++;
     		}

			$head[$h][0] = DOL_URL_ROOT.'/commande/info.php?id='.$commande->id;
			$head[$h][1] = $langs->trans('Info');
			$h++;

			dolibarr_fiche_head($head, $hselected, $langs->trans('Order').': '.$commande->ref);

			/*
			 * Confirmation de la suppression de la commande
			 */
			if ($_GET['action'] == 'delete')
			{
				$html->form_confirm('fiche.php?id='.$id, $langs->trans('DeleteOrder'), $langs->trans('ConfirmDeleteOrder'), 'confirm_delete');
				print '<br />';
			}

			/*
			 * Confirmation de la validation
			 */
			if ($_GET['action'] == 'valid')
			{
				//$numfa = commande_get_num($soc);
				$html->form_confirm('fiche.php?id='.$id, $langs->trans('ValidateOrder'), $langs->trans('ConfirmValidateOrder'), 'confirm_valid');
				print '<br />';
			}

			/*
			 * Confirmation de la cloture
			 */
			if ($_GET['action'] == 'cloture')
			{
				//$numfa = commande_get_num($soc);
				$html->form_confirm('fiche.php?id='.$id, $langs->trans('CloseOrder'), $langs->trans('ConfirmCloseOrder'), 'confirm_close');
				print '<br />';
			}

			/*
			 * Confirmation de l'annulation
			 */
			if ($_GET['action'] == 'annuler')
			{
				$html->form_confirm('fiche.php?id='.$id, $langs->trans('Cancel'), $langs->trans('ConfirmCancelOrder'), 'confirm_cancel');
				print '<br />';
			}

			/*
			 *   Commande
			 */
			$nbrow=8;
			if ($conf->projet->enabled) $nbrow++;

			print '<table class="border" width="100%">';

            // Ref
			print '<tr><td width="18%">'.$langs->trans('Ref').'</td>';
			print '<td colspan="2">'.$commande->ref.'</td>';
			print '<td width="50%">'.$langs->trans('Source').' : ' . $commande->sources[$commande->source] ;
			if ($commande->source == 0)
			{
				// Si source = propal
				$propal = new Propal($db);
				$propal->fetch($commande->propale_id);
				print ' -> <a href="'.DOL_URL_ROOT.'/comm/propal.php?propalid='.$propal->id.'">'.$propal->ref.'</a>';
			}
			print '</td></tr>';

			// Ref commande client
			print '<tr><td>';
            print '<table class="nobordernopadding" width="100%"><tr><td nowrap>';
			print $langs->trans('RefCustomer').'</td><td align="left">';
            print '</td>';
            if ($_GET['action'] != 'refcdeclient') print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=refcdeclient&amp;id='.$commande->id.'">'.img_edit($langs->trans('Edit')).'</a></td>';
            print '</tr></table>';
            print '</td><td colspan="2">';
			if ($user->rights->commande->creer && $_GET['action'] == 'refcdeclient')
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
			print '<td rowspan="'.$nbrow.'" valign="top">'.$langs->trans('Note').' :<br>';
			if ($commande->brouillon == 1 && $user->rights->commande->creer)
			{
				print '<form action="fiche.php?id='.$id.'" method="post">';
				print '<input type="hidden" name="action" value="setnote">';
				print '<textarea name="note" rows="4" style="width:95%;">'.$commande->note.'</textarea><br>';
				print '<center><input type="submit" class="button" value="'.$langs->trans('Save').'"></center>';
				print '</form>';
			}
			else
			{
				print nl2br($commande->note);
			}
			
			print '</td>';
			print '</tr>';
			

			// Soci�t�
			print '<tr><td>'.$langs->trans('Customer').'</td>';
			print '<td colspan="2">';
			print '<a href="'.DOL_URL_ROOT.'/comm/fiche.php?socid='.$soc->id.'">'.$soc->nom.'</a></td>';
			print '</tr>';

			print '<tr><td>'.$langs->trans('Status').'</td>';
			print '<td colspan="2">'.$commande->statuts[$commande->statut].'</td>';
			print '</tr>';

			print '<tr><td>'.$langs->trans('Date').'</td>';
			print '<td colspan="2">'.dolibarr_print_date($commande->date,'%A %d %B %Y').'</td>';
			print '</tr>';
			
			// date de livraison
			print '<tr><td height="10">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('DateDelivery');
			print '</td>';
					
			if ($_GET['action'] != 'editdate_livraison' && $commande->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editdate_livraison&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetConditions'),1).'</a></td>';
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
				print dolibarr_print_date($commande->date_livraison,'%a %d %B %Y');
			}
			print '</td></tr>';
			
						// Conditions et modes de r�glement
			print '<tr><td height="10">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('PaymentConditions');
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
                if ($_GET['action'] != 'classer') print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=classer&amp;id='.$commande->id.'">'.img_edit($langs->trans('SetProject')).'</a></td>';
                print '</tr></table>';
                print '</td><td colspan="2">';
                if ($_GET['action'] == 'classer')
                {
                    $html->form_project($_SERVER['PHP_SELF'].'?id='.$commande->id, $commande->soc_id, $commande->projet_id, 'projetid');
                }
                else
                {
                    $html->form_project($_SERVER['PHP_SELF'].'?id='.$commande->id, $commande->soc_id, $commande->projet_id, 'none');
                }
                print '</td></tr>';
            }

			// Lignes de 3 colonnes

			print '<tr><td>';
            print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('GlobalDiscount').'</td><td align="right">';
            print '</td>';
            if ($_GET['action'] != 'setdiscount' && $commande->brouillon) print '<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=setdiscount&amp;id='.$commande->id.'">'.img_edit($langs->trans('Edit')).'</a></td>';
            print '</tr></table>';
            print '</td>';
			if ($user->rights->commande->creer && $_GET['action'] == 'setdiscount')
			{
				print '<td align="right">';
				print '<form action="fiche.php?id='.$id.'" method="post">';
				print '<input type="hidden" name="action" value="setremise">';
				print '<input type="text" class="flat" name="remise" size="3" value="'.$commande->remise_percent.'">%';
				print '</td><td><input type="submit" class="button" value="'.$langs->trans('Modify').'">';
				print '</form>';
				print '</td>';
			}
			else
			{
				print '<td align="right">'.$commande->remise_percent.'</td><td>%</td>';
			}
			print '</tr>';

            // Total HT
			print '<tr><td>'.$langs->trans('TotalHT').'</td>';
			print '<td align="right"><b>'.price($commande->total_ht).'</b></td>';
			print '<td>'.$langs->trans('Currency'.$conf->monnaie).'</td></tr>';

			// Total TVA
			print '<tr><td>'.$langs->trans('TotalVAT').'</td><td align="right">'.price($commande->total_tva).'</td>';
			print '<td>'.$langs->trans('Currency'.$conf->monnaie).'</td></tr>';
			
			// Total TTC
			print '<tr><td>'.$langs->trans('TotalTTC').'</td><td align="right">'.price($commande->total_ttc).'</td>';
			print '<td>'.$langs->trans('Currency'.$conf->monnaie).'</td></tr>';

			print '</table><br>';

			/*
		 	 * Lignes de commandes
			 */
			$sql = 'SELECT l.fk_product, l.description, l.price, l.qty, l.rowid, l.tva_tx, l.remise_percent, l.subprice,';
			$sql.= ' p.label as product, p.ref, p.fk_product_type, p.rowid as prodid';
			
			if ($conf->global->COM_ADD_PROD_DESC && !$conf->global->CHANGE_PROD_DESC)
                        {
                        	$sql.= ', p.description as product_desc';
                        }
			
			$sql.= ' FROM '.MAIN_DB_PREFIX.'commandedet as l';
			$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON l.fk_product=p.rowid';
			$sql.= ' WHERE l.fk_commande = '.$commande->id;
			$sql.= ' ORDER BY l.rowid';

			$resql = $db->query($sql);
			if ($resql)
			{
				$num = $db->num_rows($resql);
				$i = 0; $total = 0;

				print '<table class="noborder" width="100%">';
				if ($num)
				{
					print '<tr class="liste_titre">';
					print '<td>'.$langs->trans('Description').'</td>';
					print '<td align="right" width="50">'.$langs->trans('VAT').'</td>';
					print '<td align="right" width="80">'.$langs->trans('PriceUHT').'</td>';
					print '<td align="right" width="50">'.$langs->trans('Qty').'</td>';
					print '<td align="right" width="50">'.$langs->trans('Discount').'</td>';
					print '<td align="right" width="50">'.$langs->trans('AmountHT').'</td>';
					print '<td>&nbsp;</td><td>&nbsp;</td>';
					print '</tr>';
				}
				$var=true;
				while ($i < $num)
				{
					$objp = $db->fetch_object($resql);
					$var=!$var;

					// Ligne en mode visu
					if ($_GET['action'] != 'editline' || $_GET['rowid'] != $objp->rowid)
					{
						print '<tr '.$bc[$var].'>';
						if ($objp->fk_product > 0)
						{
							print '<td><a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">';
							if ($objp->fk_product_type) print img_object($langs->trans('ShowService'),'service');
							else print img_object($langs->trans('ShowProduct'),'product');
							print ' '.$objp->ref.'</a> - '.stripslashes(nl2br($objp->product));
							print ($objp->description && $objp->description!=$objp->product)?'<br>'.stripslashes(nl2br($objp->description)):'';
							
							if ($conf->global->COM_ADD_PROD_DESC && !$conf->global->CHANGE_PROD_DESC)
                        {
                        	print '<br>'.nl2br(stripslashes($objp->product_desc));
                        }
							
							print '</td>';
						}
						else
						{
							print '<td>'.stripslashes(nl2br($objp->description));
							print '</td>';
						}
						
						print '<td align="right">'.$objp->tva_tx.'%</td>';
						print '<td align="right">'.price($objp->subprice).'</td>';
						print '<td align="right">'.$objp->qty.'</td>';
						if ($objp->remise_percent > 0)
						{
							print '<td align="right">'.$objp->remise_percent.'%</td>';
						}
						else
						{
							print '<td>&nbsp;</td>';
						}
						print '<td align="right">'.price($objp->subprice*$objp->qty*(100-$objp->remise_percent)/100).'</td>';

						// Icone d'edition et suppression
						if ($commande->statut == 0  && $user->rights->commande->creer && $_GET['action'] == '')
						{
							print '<td align="right"><a href="fiche.php?id='.$id.'&amp;action=editline&amp;rowid='.$objp->rowid.'">';
							print img_edit();
							print '</a></td>';
							print '<td align="right"><a href="fiche.php?id='.$id.'&amp;action=deleteline&amp;lineid='.$objp->rowid.'">';
							print img_delete();
							print '</a></td>';
						}
						else
						{
							print '<td>&nbsp;</td><td>&nbsp;</td>';
						}
						print '</tr>';
					}

					// Ligne en mode update
					if ($_GET['action'] == 'editline' && $user->rights->commande->creer && $_GET['rowid'] == $objp->rowid)
					{
						print '<form action="fiche.php" method="post">';
						print '<input type="hidden" name="action" value="updateligne">';
						print '<input type="hidden" name="id" value="'.$id.'">';
						print '<input type="hidden" name="elrowid" value="'.$_GET['rowid'].'">';
						print '<tr '.$bc[$var].'>';
						print '<td>';
						if ($objp->fk_product > 0)
						{
							print '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">';
							if ($objp->fk_product_type) print img_object($langs->trans('ShowService'),'service');
							else print img_object($langs->trans('ShowProduct'),'product');
							print ' '.$objp->ref.'</a>';
							print ' - '.stripslashes(nl2br($objp->product));
							print '<br>';
						}
						print '<textarea name="eldesc" cols="50" rows="1">'.stripslashes($objp->description).'</textarea></td>';
						print '<td align="right">';
						if($soc->tva_assuj == "0")
							print '<input type="hidden" name="eltva_tx" value="0">0';
						else
							print $html->select_tva('eltva_tx',$objp->tva_tx,$mysoc,$soc);
						print '</td>';
						print '<td align="right"><input size="5" type="text" class="flat" name="elprice" value="'.price($objp->subprice).'"></td>';
						print '<td align="right"><input size="2" type="text" class="flat" name="elqty" value="'.$objp->qty.'"></td>';
						print '<td align="right" nowrap><input size="1" type="text" class="flat" name="elremise_percent" value="'.$objp->remise_percent.'">%</td>';
						print '<td align="center" colspan="3"><input type="submit" class="button" name="save" value="'.$langs->trans('Save').'">';
						print '<br /><input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'"></td>';
						print '</tr>';
						print '</form>';
					}

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
			 * Ajouter une ligne
			 */
			if ($commande->statut == 0 && $user->rights->commande->creer && ($_GET['action'] == '' || $_GET['action'] == 'builddoc'))
			{
				print '<tr class="liste_titre">';
				print '<td>'.$langs->trans('Description').'</td>';
				print '<td align="right">'.$langs->trans('VAT').'</td>';
				print '<td align="right">'.$langs->trans('PriceUHT').'</td>';
				print '<td align="right">'.$langs->trans('Qty').'</td>';
				print '<td align="right">'.$langs->trans('Discount').'</td>';
				print '<td>&nbsp;</td>';
				print '<td>&nbsp;</td>';
				print '<td>&nbsp;</td>';
				print '</tr>';

				// Ajout produit produits/services personalis�s
				print '<form action="fiche.php?id='.$id.'" method="post">';
				print '<input type="hidden" name="id" value="'.$id.'">';
				print '<input type="hidden" name="action" value="addligne">';

				$var=true;
				print '<tr '.$bc[$var].'>';
				print '  <td><textarea cols="50" name="desc" rows="1"></textarea></td>';
				print '<td align="center">';
				if($soc->tva_assuj == "0")
					print '<input type="hidden" name="tva_tx" value="0">0';
				else
					print $html->select_tva('tva_tx',$conf->defaulttx,$mysoc,$soc);
				print '</td>';
				print '<td align="right"><input type="text" name="pu" size="5"></td>';
				print '<td align="right"><input type="text" name="qty" value="1" size="2"></td>';
				print '<td align="right" nowrap><input type="text" name="remise_percent" size="2" value="0">%</td>';
				print '<td align="center" colspan="3"><input type="submit" class="button" value="'.$langs->trans('Add').'"></td>';
				print '</tr>';

				print '</form>';

				// Ajout de produits/services pr�d�finis
				print '<form action="fiche.php?id='.$id.'" method="post">';
				print '<input type="hidden" name="id" value="'.$id.'">';
				print '<input type="hidden" name="action" value="addligne">';

				$var=!$var;
				print '<tr '.$bc[$var].'>';
				print '<td colspan="2">';
				// multiprix
				if($conf->global->PRODUIT_MULTIPRICES == 1)
						$html->select_produits('','p_idprod','',$conf->produit->limit_size,$soc->price_level);
				else
					$html->select_produits('','p_idprod','',$conf->produit->limit_size);
				print '<br>';
				print '<textarea cols="50" name="np_desc" rows="1"></textarea>';
				print '</td>';
				print '<td>&nbsp;</td>';
				print '<td align="right"><input type="text" size="2" name="qty" value="1"></td>';
				print '<td align="right" nowrap><input type="text" size="2" name="remise_percent" value="0">%</td>';
				print '<td align="center" colspan="3"><input type="submit" class="button" value="'.$langs->trans('Add').'"></td></tr>';
				print '</tr>';

				print '</form>';
			}
			print '</table>';
			print '</div>';

			/*
			 * Boutons actions
			 */
			if ($user->societe_id == 0 && $commande->statut < 3 && ($_GET['action'] == '' || $_GET['action'] == 'builddoc'))
			{
				print '<div class="tabsAction">';

				if ($conf->expedition->enabled && $commande->statut > 0 && $commande->statut < 3 && $user->rights->expedition->creer)
				{
					print '<a class="butAction" href="'.DOL_URL_ROOT.'/expedition/commande.php?id='.$_GET['id'].'">'.$langs->trans('Send').'</a>';
				}

				if ($commande->statut == 0)
				{
					if ($user->rights->commande->valider)
					{
						print '<a class="butAction" href="fiche.php?id='.$id.'&amp;action=valid">'.$langs->trans('Valid').'</a>';
					}
				}

				if ($commande->statut == 1 || $commande->statut == 2)
				{
					if ($user->rights->commande->creer)
					{
						print '<a class="butAction" href="fiche.php?id='.$id.'&amp;action=cloture">'.$langs->trans('Close').'</a>';
					}
				}

				if ($commande->statut == 0 && $user->rights->commande->supprimer)
				{
					print '<a class="butActionDelete" href="fiche.php?id='.$id.'&amp;action=delete">'.$langs->trans('Delete').'</a>';
				}

				if ($commande->statut == 1)
				{
					$nb_expedition = $commande->nb_expedition();
					if ($user->rights->commande->valider && $nb_expedition == 0)
					{
						print '<a class="butActionDelete" href="fiche.php?id='.$id.'&amp;action=annuler">'.$langs->trans('CancelOrder').'</a>';
					}
				}
				  // Build PDF
					if ($user->rights->commande->creer)
					{
						print '<a class="butAction" href="fiche.php?id='.$commande->id.'&amp;action=builddoc">'.$langs->trans("BuildPDF").'</a>';
					}

				print '</div>';
			}
			print '<br>';


			print '<table width="100%"><tr><td width="50%" valign="top">';

			/*
			 * Documents g�n�r�s
			 *
			 */
			$comref = sanitize_string($commande->ref);
			$comref = str_replace("(","",$comref);
			$comref = str_replace(")","",$comref);
			$file = $conf->commande->dir_output . '/' . $comref . '/' . $comref . '.pdf';
			$relativepath = $comref.'/'.$comref.'.pdf';
			$filedir = $conf->commande->dir_output . '/' . $comref;
			$urlsource=$_SERVER["PHP_SELF"]."?id=".$commande->id;
			$genallowed=$user->rights->commande->creer;
    		$delallowed=$user->rights->commande->supprimer;

			$var=true;
			/*
			if (file_exists($file))
			{
				print_titre($langs->trans('Documents'));
				print '<table width="100%" class="border">';
				print '<tr '.$bc[$var].'><td>'.$langs->trans('Order').' PDF</td>';
				print '<td><a href="'.DOL_URL_ROOT . '/document.php?modulepart=commande&file='.urlencode($relativepath).'">'.$commande->ref.'.pdf</a></td>';
				print '<td align="right">'.filesize($file). ' bytes</td>';
				print '<td align="right">'.strftime('%d %b %Y %H:%M:%S',filemtime($file)).'</td>';
				print '</tr>';
				print '</table>';
				print '<br>';
			}
			*/
			//$html->show_documents('propal',$filename,$filedir,$urlsource,$genallowed,$delallowed,$propal->modelpdf);
			$html->show_documents('commande',$comref,$filedir,$urlsource,$genallowed,$delallowed,$commande->modelpdf);
			/*
			 * Liste des factures
			 */
			$sql = 'SELECT f.rowid,f.facnumber, f.total_ttc, '.$db->pdate('f.datef').' as df';
			$sql .= ' FROM '.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'co_fa as cf';
			$sql .= ' WHERE f.rowid = cf.fk_facture AND cf.fk_commande = '. $commande->id;

			$result = $db->query($sql);
			if ($result)
			{
				$num = $db->num_rows($result);
				if ($num)
				{
					print_titre($langs->trans('RelatedBills'));
					$i = 0; $total = 0;
					print '<table class="noborder" width="100%">';
					print '<tr class="liste_titre"><td>'.$langs->trans('Ref')."</td>";
					print '<td align="center">'.$langs->trans('Date').'</td>';
					print '<td align="right">'.$langs->trans('Price').'</td>';
					print '</tr>';

					$var=True;
					while ($i < $num)
					{
						$objp = $db->fetch_object($result);
						$var=!$var;
						print '<tr '.$bc[$var].'>';
						print '<td><a href="../compta/facture.php?facid='.$objp->rowid.'">'.img_object($langs->trans('ShowBill'),'bill').' '.$objp->facnumber.'</a></td>';
						print '<td align="center">'.dolibarr_print_date($objp->df).'</td>';
						print '<td align="right">'.$objp->total_ttc.'</td></tr>';
						$i++;
					}
					print '</table>';
				}
			}
			else
			{
				dolibarr_print_error($db);
			}
			print '</td><td valign="top" width="50%">';

			/*
			 * Liste des exp�ditions
			 */
			$sql = 'SELECT e.rowid,e.ref,'.$db->pdate('e.date_expedition').' as de';
			$sql .= ' FROM '.MAIN_DB_PREFIX.'expedition as e';
			$sql .= ' WHERE e.fk_commande = '. $commande->id;

			$result = $db->query($sql);
			if ($result)
			{
				$num = $db->num_rows($result);
				if ($num)
				{
					print_titre($langs->trans('Sendings'));
					$i = 0; $total = 0;
					print '<table class="border" width="100%">';
					print '<tr '.$bc[$var].'><td>'.$langs->trans('Sendings').'</td><td>'.$langs->trans('Date').'</td></tr>';

					$var=True;
					while ($i < $num)
					{
						$objp = $db->fetch_object($result);
						$var=!$var;
						print '<tr '.$bc[$var].'>';
						print '<td><a href="../expedition/fiche.php?id='.$objp->rowid.'">'.img_object($langs->trans('ShowSending'),'sending').' '.$objp->ref.'</a></td>';
						print '<td>'.dolibarr_print_date($objp->de).'</td></tr>';
						$i++;
					}
					print '</table>';
				}
			}
			else
			{
				dolibarr_print_error($db);
			}
			print '</td></tr></table>';
		}
		else
		{
			/* Commande non trouv�e */
			print 'Commande inexistante ou acc�s refus�';
		}
	}
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
