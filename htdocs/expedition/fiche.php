<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Simon TOSSER  <simon@kornog-computing.com>
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

// Code identique a /expedition/commande.php

/**
        \file       htdocs/expedition/fiche.php
        \ingroup    expedition
        \brief      Fiche descriptive d'une expedition
        \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT ."/expedition/mods/pdf/ModelePdfExpedition.class.php");
require_once(DOL_DOCUMENT_ROOT."/product.class.php");
require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
require_once(DOL_DOCUMENT_ROOT."/product/stock/entrepot.class.php");

$langs->load("companies");
$langs->load("bills");
$langs->load('deliveries');
$langs->load('orders');
$langs->load('stocks');

$user->getrights('expedition');
if (!$user->rights->expedition->lire)
  accessforbidden();


// S�curit� acc�s client
if ($user->societe_id > 0)
{
  $action = '';
  $socid = $user->societe_id;
}


/*
 * Actions
 */

if ($_POST["action"] == 'add')
{
	// \todo Mettre id entrepot sur ligne detail expedition et non sur fiche expedition

    $db->begin();

    // Creation de l'objet expedition
    $expedition = new Expedition($db);

    $expedition->date_expedition  = time();
    $expedition->note             = $_POST["note"];
    $expedition->commande_id      = $_POST["commande_id"];
    $expedition->entrepot_id      = $_POST["entrepot_id"];

    // On boucle sur chaque ligne de commande pour compl�ter objet expedition
    // avec qt� � livrer
    $commande = new Commande($db);
    $commande->fetch($expedition->commande_id);
    $commande->fetch_lignes();
    for ($i = 0 ; $i < sizeof($commande->lignes) ; $i++)
    {
        $qty = "qtyl".$i;
        $idl = "idl".$i;
        if ($_POST[$qty] > 0)
        {
            $expedition->addline($_POST[$idl],$_POST[$qty]);
        }
    }

    $ret=$expedition->create($user);
    if ($ret > 0)
    {
        $db->commit();
        Header("Location: fiche.php?id=".$expedition->id);
        exit;
    }
    else
    {
        $db->rollback();
        $mesg='<div class="error">'.$expedition->error.'</div>';
        $_GET["commande_id"]=$_POST["commande_id"];
        $_GET["action"]='create';
    }
}

/*
 * G�n�re un bon de livraison
 */
if ($_GET["action"] == 'create_delivery' && $conf->livraison->enabled && $user->rights->expedition->livraison->creer)
{
  $expedition = new Expedition($db);
  $expedition->fetch($_GET["id"]);
  $result = $expedition->create_delivery($user);
  Header("Location: ".DOL_URL_ROOT.'/livraison/fiche.php?id='.$result);
}

if ($_POST["action"] == 'confirm_valid' && $_POST["confirm"] == 'yes' && $user->rights->expedition->valider)
{
  $expedition = new Expedition($db);
  $expedition->fetch($_GET["id"]);
  $result = $expedition->valid($user);
  //$expedition->PdfWrite();
}

if ($_POST["action"] == 'confirm_delete' && $_POST["confirm"] == 'yes')
{
  if ($user->rights->expedition->supprimer )
    {
      $expedition = new Expedition($db);
      $expedition->fetch($_GET["id"]);
      $expedition->delete();
      Header("Location: liste.php");
    }
}

/*
 * G�n�rer ou reg�n�rer le PDF
 */
if ($_REQUEST['action'] == 'builddoc')	// En get ou en post
{
	// Sauvegarde le dernier mod�le choisi pour g�n�rer un document
	$expedition = new Expedition($db, 0, $_REQUEST['id']);
	$expedition->fetch($_REQUEST['id']);

	if ($_REQUEST['model'])
	{
		$expedition->set_pdf_model($user, $_REQUEST['model']);
	}

	if ($_REQUEST['lang_id'])
	{
		$outputlangs = new Translate(DOL_DOCUMENT_ROOT ."/langs");
		$outputlangs->setDefaultLang($_REQUEST['lang_id']);
	}
	$result=expedition_pdf_create($db,$expedition->id,$expedition->modelpdf,$outputlangs);
    if ($result <= 0)
    {
    	dolibarr_print_error($db,$result);
        exit;
    }
}


/*
 *
 */

llxHeader('','Fiche expedition','ch-expedition.html',$form_search);

$html = new Form($db);

/*********************************************************************
 *
 * Mode creation
 *
 *********************************************************************/
if ($_GET["action"] == 'create')
{

	print_titre($langs->trans("CreateASending"));

	if ($mesg)
	{
		print $mesg.'<br>';
	}

	$commande = new Commande($db);
	$commande->loadExpeditions();

	if ( $commande->fetch($_GET["commande_id"]))
	{
		$soc = new Societe($db);
		$soc->fetch($commande->socid);

		$author = new User($db);
		$author->id = $commande->user_author_id;
		$author->fetch();

		$entrepot = new Entrepot($db);

		/*
		 *   Commande
		 */
		print '<form action="fiche.php" method="post">';
		print '<input type="hidden" name="action" value="add">';
		print '<input type="hidden" name="commande_id" value="'.$commande->id.'">';
		if ($_GET["entrepot_id"])
		{
			print '<input type="hidden" name="entrepot_id" value="'.$_GET["entrepot_id"].'">';
		}

		print '<table class="border" width="100%">';

		// Ref commande
		print '<tr><td>'.$langs->trans("RefOrder").'</td><td colspan="3"><a href="'.DOL_URL_ROOT.'/commande/fiche.php?id='.$commande->id.'">'.img_object($langs->trans("ShowOrder"),'order').' '.$commande->ref.'</a></td>';
		print "</tr>\n";

		// Ref commande client
		print '<tr><td>';
		print '<table class="nobordernopadding" width="100%"><tr><td nowrap>';
		print $langs->trans('RefCustomerOrderShort').'</td><td align="left">';
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

		// Soci�t
		print '<tr><td>'.$langs->trans('Company').'</td>';
		print '<td colspan="3">'.$soc->getNomUrl(1).'</td>';
		print '</tr>';

		// Date
		print "<tr><td>".$langs->trans("Date")."</td>";
		print '<td colspan="3">'.dolibarr_print_date($commande->date,"%A %d %B %Y")."</td></tr>\n";

		// Entrepot (si forc�)
		if ($conf->stock->enabled && $_GET["entrepot_id"])
		{
			print '<tr><td>'.$langs->trans("Warehouse").'</td>';
			print '<td colspan="3">';
			$ents = $entrepot->list_array();
			print '<a href="'.DOL_URL_ROOT.'/product/stock/fiche.php?id='.$_GET["entrepot_id"].'">'.img_object($langs->trans("ShowWarehouse"),'stock').' '.$ents[$_GET["entrepot_id"]].'</a>';
			print '</td></tr>';
		}

		if ($commande->note && ! $user->societe_id)
		{
			print '<tr><td colspan="3">'.$langs->trans("NotePrivate").': '.nl2br($commande->note)."</td></tr>";
		}

		print "</table>";

		/*
		* Lignes de commandes
		*
		*/
		echo '<br><table class="noborder" width="100%">';

		$lignes = $commande->fetch_lignes(1);

		/* Lecture des expeditions d�j� effectu�es */
		$commande->loadExpeditions();

		$num = sizeof($commande->lignes);
		$i = 0;

		if ($num)
		{
			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("Description").'</td>';
			print '<td align="center">'.$langs->trans("QtyOrdered").'</td>';
			print '<td align="center">'.$langs->trans("QtyShipped").'</td>';
			print '<td align="center">'.$langs->trans("QtyToShip").'</td>';
			if ($conf->stock->enabled)
			{
				if ($_GET["entrepot_id"])
				{
					print '<td align="right">'.$langs->trans("Stock").'</td>';
				}
				else
				{
					print '<td align="left">'.$langs->trans("Warehouse").'</td>';
				}
			}
			print "</tr>\n";
		}
		$var=true;
		while ($i < $num)
		{
			$ligne = $commande->lignes[$i];
			$var=!$var;
			print "<tr $bc[$var]>\n";
			if ($ligne->fk_product > 0)
			{
				$product = new Product($db);
				$product->fetch($ligne->fk_product);

				print '<td>';
				print '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$ligne->fk_product.'">'.img_object($langs->trans("ShowProduct"),"product").' '.$product->ref.'</a> - '.$product->libelle;
				if ($ligne->description) print nl2br($ligne->description);
				print '</td>';
			}
			else
			{
				print "<td>".nl2br($ligne->description)."</td>\n";
			}

			print '<td align="center">'.$ligne->qty.'</td>';

			print '<td align="center">';
			$quantite_livree = $commande->expeditions[$ligne->fk_product];
			print $quantite_livree;;
			print '</td>';

			$quantite_commandee = $ligne->qty;
			$quantite_a_livrer = $quantite_commandee - $quantite_livree;

			if ($conf->stock->enabled)
			{
				$defaultqty=0;
				if ($_GET["entrepot_id"])
				{
					$stock = $product->stock_entrepot[$_GET["entrepot_id"]];
					$stock+=0;  // Convertit en num�rique
					$defaultqty=min($quantite_a_livrer, $stock);
				}

				// Quantit� � livrer
				print '<td align="center">';
				print '<input name="idl'.$i.'" type="hidden" value="'.$ligne->id.'">';
				print '<input name="qtyl'.$i.'" type="text" size="4" value="'.$defaultqty.'">';
				print '</td>';

				// Stock
				if ($_GET["entrepot_id"])
				{
					print '<td align="right">'.$stock;
					if ($stock < $quantite_a_livrer)
					{
						print ' '.img_warning($langs->trans("StockTooLow"));
					}
					print '</td>';
				}
				else
				{
					$array=array();

			        $sql = "SELECT e.rowid, e.label, ps.reel";
			        $sql.= " FROM ".MAIN_DB_PREFIX."product_stock as ps, ".MAIN_DB_PREFIX."entrepot as e";
			        $sql.= " WHERE ps.fk_entrepot = e.rowid AND fk_product = '".$product->id."'";
			        $result = $db->query($sql) ;
			        if ($result)
			        {
			            $num = $db->num_rows($result);
			            $i=0;
			            if ($num > 0)
			            {
			                while ($i < $num)
			                {
			                    $obj = $db->fetch_object($result);
			                    $array[$obj->rowid] = $obj->label.' ('.$obj->reel.')';
			                    $i++;
			                }
			            }
			            $db->free($result);
			        }
			        else
			        {
			            $this->error=$db->error();
			            return -1;
			        }

					print '<td align="left">';
					$html->select_array('warehouse'.$i,$array,'',1,0,0);
					print '</td>';
				}
			}
			else
			{
				// Quantit� � livrer
				print '<td align="center">';
				print '<input name="idl'.$i.'" type="hidden" value="'.$ligne->id.'">';
				print '<input name="qtyl'.$i.'" type="text" size="6" value="'.$quantite_a_livrer.'">';
				print '</td>';
			}

			print "</tr>\n";

			$i++;
			$var=!$var;
		}

		/*
		*
		*/

		print '<tr><td align="center" colspan="5"><br><input type="submit" class="button" value="'.$langs->trans("Create").'"></td></tr>';
		print "</table>";
		print '</form>';
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
    if ($_GET["id"] > 0)
    {
        $expedition = New Expedition($db);
        $result = $expedition->fetch($_GET["id"]);

        if ($expedition->id > 0)
        {
            $author = new User($db);
            $author->id = $expedition->user_author_id;
            $author->fetch();

            $commande = New Commande($db);
            $commande->fetch($expedition->commande_id);

            $soc = new Societe($db);
            $soc->fetch($commande->socid);

            $h=0;
            $head[$h][0] = DOL_URL_ROOT."/expedition/fiche.php?id=".$expedition->id;
            $head[$h][1] = $langs->trans("SendingCard");
            $hselected = $h;
            $h++;

            if ($conf->livraison->enabled && $expedition->livraison_id)
            {
            	$head[$h][0] = DOL_URL_ROOT."/livraison/fiche.php?id=".$expedition->livraison_id;
            	$head[$h][1] = $langs->trans("DeliveryCard");
            	$h++;
            }

            dolibarr_fiche_head($head, $hselected, $langs->trans("Sending"));

            /*
            * Confirmation de la suppression
            *
            */
            if ($_GET["action"] == 'delete')
            {
                $html->form_confirm("fiche.php?id=$expedition->id",$langs->trans("DeleteSending"),"Etes-vous s�r de vouloir supprimer cette expedition ?","confirm_delete");
                print '<br>';
            }

            /*
            * Confirmation de la validation
            *
            */
            if ($_GET["action"] == 'valid')
            {
                $html->form_confirm("fiche.php?id=$expedition->id",$langs->trans("ValidateSending"),"Etes-vous s�r de vouloir valider cette exp�dition ?","confirm_valid");
                print '<br>';
            }
            /*
            * Confirmation de l'annulation
            *
            */
            if ($_GET["action"] == 'annuler')
            {
                $html->form_confirm("fiche.php?id=$expedition->id",$langs->trans("CancelSending"),"Etes-vous s�r de vouloir annuler cette commande ?","confirm_cancel");
                print '<br>';
            }

            /*
            *   Commande
            */
            if ($commande->brouillon && $user->rights->commande->creer)
            {
                print '<form action="fiche.php?id='.$expedition->id.'" method="post">';
                print '<input type="hidden" name="action" value="setremise">';
            }

            print '<table class="border" width="100%">';

            // Ref
            print '<tr><td width="20%">'.$langs->trans("Ref").'</td>';
            print '<td colspan="3">'.$expedition->ref.'</td></tr>';

            // Client
            print '<tr><td width="20%">'.$langs->trans("Customer").'</td>';
            print '<td colspan="3">'.$soc->getNomUrl(1).'</td>';
            print "</tr>";

            // Commande li�e
            print '<tr><td>'.$langs->trans("RefOrder").'</td>';
            print '<td colspan="3"><a href="'.DOL_URL_ROOT.'/expedition/commande.php?id='.$commande->id.'">'.img_object($langs->trans("ShowOrder"),'order').' '.$commande->ref."</a></td>\n";
            print '</tr>';

            // Commande li�e
            print '<tr><td>'.$langs->trans("RefCustomerOrderShort").'</td>';
            print '<td colspan="3"><a href="'.DOL_URL_ROOT.'/expedition/commande.php?id='.$commande->id.'">'.$commande->ref_client."</a></td>\n";
            print '</tr>';

            // Date
            print '<tr><td>'.$langs->trans("Date").'</td>';
            print '<td colspan="3">'.dolibarr_print_date($expedition->date,"%A %d %B %Y")."</td>\n";
   			print '</tr>';

            // Statut
            print '<tr><td>'.$langs->trans("Status").'</td>';
            print '<td colspan="3">'.$expedition->getLibStatut(4)."</td>\n";
   			print '</tr>';

            print "</table>\n";

            /*
             * Lignes produits
             */
            echo '<br><table class="noborder" width="100%">';

            $sql = "SELECT cd.fk_product, cd.description, cd.rowid, cd.qty as qty_commande,";
            $sql .= " e.fk_statut, ed.qty as qty_livre";
            $sql .= " FROM ".MAIN_DB_PREFIX."commandedet as cd , ".MAIN_DB_PREFIX."expeditiondet as ed, ".MAIN_DB_PREFIX."expedition as e";
            $sql .= " WHERE e.rowid = ".$expedition->id." AND e.rowid = ed.fk_expedition AND cd.rowid = ed.fk_commande_ligne";

            $resql = $db->query($sql);

            if ($resql)
            {
                $num_prod = $db->num_rows($resql);
                $i = 0;

                print '<tr class="liste_titre">';
                print '<td>'.$langs->trans("Products").'</td>';
            	print '<td align="center">'.$langs->trans("QtyOrdered").'</td>';
            	if ($obj->fk_statut <= 1)
            	{
            	   	print '<td align="center">'.$langs->trans("QtyToShip").'</td>';
            	}
            	else
            	{
            	   	print '<td align="center">'.$langs->trans("QtyShipped").'</td>';
            	}
				if ($conf->stock->enabled)
				{
					print '<td align="left">'.$langs->trans("WarehouseSource").'</td>';
				}
                print "</tr>\n";

                $var=true;
                while ($i < $num_prod)
                {
                    $objp = $db->fetch_object($resql);

                    $var=!$var;
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
                    
                    // Qte command�
                    print '<td align="center">'.$objp->qty_commande.'</td>';
                    
                    // Qte a expedier ou expedier
                    print '<td align="center">'.$objp->qty_livre.'</td>';

	            	// Entrepot source
		            if ($conf->stock->enabled)
		            {
						$entrepot = new Entrepot($db);
						$entrepot->fetch($expedition->entrepot_id);
						print '<td align="left">'.$entrepot->getNomUrl(1).'</td>';
					}
		

                    print "</tr>";

                    $i++;
                    $var=!$var;
                }
                $db->free($resql);
            }
            else
            {
                dolibarr_print_error($db);
            }

            print "</table>\n";

            print "\n</div>\n";


            /*
            *    Boutons actions
            */

            if ($user->societe_id == 0)
            {
                print '<div class="tabsAction">';

                if (! eregi('^(valid|delete)',$_REQUEST["action"]))
                {
	                if ($expedition->statut == 0 && $user->rights->expedition->valider && $num_prod > 0)
	                {
	                    print '<a class="butAction" href="fiche.php?id='.$expedition->id.'&amp;action=valid">'.$langs->trans("Validate").'</a>';
	                }
	
	                if ($conf->livraison->enabled && $expedition->statut == 1 && $user->rights->expedition->livraison->creer && !$expedition->livraison_id)
	                {
	                    print '<a class="butAction" href="fiche.php?id='.$expedition->id.'&amp;action=create_delivery">'.$langs->trans("DeliveryOrder").'</a>';
	                }
	
	    			if ($user->rights->expedition->lire && ($expedition->statut > 0))
	    			{
	                	print '<a class="butAction" href="fiche.php?id='.$expedition->id.'&amp;action=builddoc">'.$langs->trans('BuildPDF').'</a>';
	    			}
	
	                if ($expedition->brouillon && $user->rights->expedition->supprimer)
	                {
	                    print '<a class="butActionDelete" href="fiche.php?id='.$expedition->id.'&amp;action=delete">'.$langs->trans("Delete").'</a>';
	                }
				}
				
                print '</div>';
            }
			print "\n";

            print "<table width=\"100%\" cellspacing=2><tr><td width=\"50%\" valign=\"top\">";

            /*
             * Documents g�n�r�s
             */

            $expeditionref = sanitize_string($expedition->ref);
            $filedir = $conf->expedition->dir_output . "/" .$expeditionref;

            $urlsource = $_SERVER["PHP_SELF"]."?id=".$expedition->id;

            $genallowed=$user->rights->expedition->lire && ($expedition->statut > 0);
            $delallowed=$user->rights->expedition->supprimer;
            //$genallowed=1;
            //$delallowed=0;

            $somethingshown=$html->show_documents('expedition',$expeditionref,$filedir,$urlsource,$genallowed,$delallowed,$expedition->modelpdf);
			if ($genallowed && ! $somethingshown) $somethingshown=1;

            /*
             * Autres expeditions
             */
            $sql = "SELECT cd.fk_product, cd.description, cd.rowid, cd.qty as qty_commande";
            $sql .= " , ed.qty as qty_livre, e.ref, ed.fk_expedition as expedition_id";
            $sql .= ",".$db->pdate("e.date_expedition")." as date_expedition";
            $sql .= " FROM ".MAIN_DB_PREFIX."commandedet as cd";
            $sql .= " , ".MAIN_DB_PREFIX."expeditiondet as ed, ".MAIN_DB_PREFIX."expedition as e";
            $sql .= " WHERE cd.fk_commande = ".$expedition->commande_id;
            $sql .= " AND e.rowid <> ".$expedition->id;
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

                    print_titre($langs->trans("OtherSendingsForSameOrder"));
                    print '<table class="liste" width="100%">';
                    print '<tr class="liste_titre">';
                    print '<td align="left">'.$langs->trans("Ref").'</td>';
                    print '<td>'.$langs->trans("Description").'</td>';
                    print '<td align="center">'.$langs->trans("Qty").'</td>';
                    print '<td align="center">'.$langs->trans("Date").'</td>';
                    print "</tr>\n";

                    $var=True;
                    while ($i < $num)
                    {
                        $var=!$var;
                        $objp = $db->fetch_object($resql);
                        print "<tr $bc[$var]>";
                        print '<td align="left" nowrap="nowrap"><a href="'.DOL_URL_ROOT.'/expedition/fiche.php?id='.$objp->expedition_id.'">'.img_object($langs->trans("ShowSending"),'sending').' '.$objp->ref.'<a></td>';
                        if ($objp->fk_product > 0)
                        {
                            $product = new Product($db);
                            $product->fetch($objp->fk_product);

                            print '<td>';
                            print '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">'.img_object($langs->trans("ShowProduct"),"product").' '.$product->ref.'</a> - '.dolibarr_trunc($product->libelle,20);
                            if ($objp->description) print nl2br(dolibarr_trunc($objp->description,24));
                            print '</td>';
                        }
                        else
                        {
                            print "<td>".nl2br(dolibarr_trunc($objp->description,24))."</td>\n";
                        }
                        print '<td align="center">'.$objp->qty_livre.'</td>';
                        print '<td align="center" nowrap="nowrap">'.dolibarr_print_date($objp->date_expedition).'</td>';
                        print '</tr>';
                        $i++;
                    }

                    print '</table>';
                }
                $db->free($resql);
            }
            else {
                dolibarr_print_error($db);
            }


            print '</td><td valign="top" width="50%">';

			// Rien a droite

            print '</td></tr></table>';

        }
        else
        {
            dolibarr_print_error($db);
        }
    }
    else
    {
        print "Expedition inexistante ou acc�s refus�";
    }
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
