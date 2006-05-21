<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne           <eric.seigne@ryxeo.com>
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
        \file       htdocs/comm/propal.php
        \ingroup    propale
        \brief      Page liste des propales (vision commercial)
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT ."/includes/modules/propale/modules_propale.php");

$user->getrights('propale');

if (!$user->rights->propale->lire)
	accessforbidden();

$langs->load('companies');
$langs->load('propal');
$langs->load('compta');
$langs->load('bills');

if ($conf->projet->enabled)   require_once(DOL_DOCUMENT_ROOT.'/project.class.php');
if ($conf->commande->enabled) require_once(DOL_DOCUMENT_ROOT.'/commande/commande.class.php');
require_once(DOL_DOCUMENT_ROOT.'/propal.class.php');
require_once(DOL_DOCUMENT_ROOT.'/actioncomm.class.php');
require_once(DOL_DOCUMENT_ROOT.'/lib/CMailFile.class.php');

$sall=isset($_GET["sall"])?$_GET["sall"]:$_POST["sall"];
if (isset($_GET["msg"])) { $msg=urldecode($_GET["msg"]); }
$year=isset($_GET["year"])?$_GET["year"]:"";
$month=isset($_GET["month"])?$_GET["month"]:"";

// S�curit� acc�s client
$socidp='';
if ($_GET["socidp"]) { $socidp=$_GET["socidp"]; }
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}

// Nombre de ligne pour choix de produit/service pr�d�finis
$NBLINES=4;

$form=new Form($db);


/******************************************************************************/
/*                     Actions                                                */
/******************************************************************************/

if ($_POST['action'] == 'confirm_delete' && $_POST['confirm'] == 'yes')
{
    if ($user->rights->propale->supprimer)
    {
        $propal = new Propal($db, 0, $_GET['propalid']);
        $propal->delete($user);
        $propalid = 0;
        $brouillon = 1;
    }
    Header('Location: '.$_SERVER["PHP_SELF"]);
    exit;
}

if ($_POST['action'] == 'confirm_validate' && $_POST['confirm'] == 'yes')
{
    if ($user->rights->propale->valider)
    {
        $propal = new Propal($db);
        $propal->fetch($_GET['propalid']);
        $result=$propal->update_price($_GET['propalid']);
        propale_pdf_create($db, $_GET['propalid'], $propal->modelpdf);
        $result=$propal->valid($user);
    }
    Header ('Location: '.$_SERVER["PHP_SELF"].'?propalid='.$_GET['propalid']);
    exit;
}

if ($_POST['action'] == 'setecheance')
{
	$propal = new Propal($db);
    $propal->fetch($_GET['propalid']);
	$result=$propal->set_echeance($user,mktime(12, 1, 1, $_POST['echmonth'], $_POST['echday'], $_POST['echyear']));
	if ($result < 0) dolibarr_print_error($db,$propal->error);
}
if ($_POST['action'] == 'setdate_livraison')
{
	$propal = new Propal($db);
    $propal->fetch($_GET['propalid']);
	$result=$propal->set_date_livraison($user,$_POST['liv_year']."-".$_POST['liv_month']."-".$_POST['liv_day']);
	if ($result < 0) dolibarr_print_error($db,$propal->error);
}

if ($_POST['action'] == 'setdeliveryadress' && $user->rights->propale->creer)
{
	$propal = new Propal($db);
	$propal->fetch($_GET['propalid']);
	$result=$propal->set_adresse_livraison($user,$_POST['adresse_livraison_id']);
	if ($result < 0) dolibarr_print_error($db,$propal->error);
}

if ($_POST['action'] == 'add') 
{
    $propal = new Propal($db, $_POST['socidp']);
    
    /*
     * Si on sel�ctionn� une propal � copier, on r�alise la copie
     */
    if($_POST['createmode']=='copy' && $_POST['copie_propal'])
    {
    	if($propal->load_from($_POST['copie_propal']) == -1)
    	{
    		//$msg = '<div class="error">'.$langs->trans('ErrorMailRecipientIsEmpty').' !</div>';
    		$msg = '<div class="error">Impossible de copier la propal Id = ' . $_POST['copie_propal'] . '!</div>';
    	}
    	$propal->datep = mktime(12, 1, 1, $_POST['remonth'], $_POST['reday'], $_POST['reyear']);
      $propal->date_livraison = $_POST['liv_year']."-".$_POST['liv_month']."-".$_POST['liv_day'];
      $propal->adresse_livraison_id = $_POST['adresse_livraison_id'];
	    $propal->duree_validite = $_POST['duree_validite'];
	    $propal->cond_reglement_id = $_POST['cond_reglement_id'];
	    $propal->mode_reglement_id = $_POST['mode_reglement_id'];
	    $propal->remise_percent = $_POST['remise_percent'];
	    $propal->remise_absolue = $_POST['remise_absolue'];
	    $propal->socidp    = $_POST['socidp'];
	    $propal->contactid = $_POST['contactidp'];
	    $propal->projetidp = $_POST['projetidp'];
	    $propal->modelpdf  = $_POST['model'];
	    $propal->author    = $user->id;
	    $propal->note      = $_POST['note'];
	    $propal->ref = $_POST['ref'];
	    $propal->statut = 0;

	    $id = $propal->create_from();
    }
    else
    {
	    $propal->datep = mktime(12, 1, 1, $_POST['remonth'], $_POST['reday'], $_POST['reyear']);
      $propal->date_livraison = $_POST['liv_year']."-".$_POST['liv_month']."-".$_POST['liv_day'];
      $propal->adresse_livraison_id = $_POST['adresse_livraison_id'];
	    $propal->duree_validite = $_POST['duree_validite'];
	    $propal->cond_reglement_id = $_POST['cond_reglement_id'];
	    $propal->mode_reglement_id = $_POST['mode_reglement_id'];
	
	    $propal->contactid = $_POST['contactidp'];
	    $propal->projetidp = $_POST['projetidp'];
	    $propal->modelpdf  = $_POST['model'];
	    $propal->author    = $user->id;
	    $propal->note      = $_POST['note'];
	
	    $propal->ref = $_POST['ref'];
	    
	    for ($i = 1 ; $i <= PROPALE_NEW_FORM_NB_PRODUCT ; $i++)
	    {
			if ($_POST['idprod'.$i])
			{
		        $xid = 'idprod'.$i;
		        $xqty = 'qty'.$i;
		        $xremise = 'remise'.$i;
		        $propal->add_product($_POST[$xid],$_POST[$xqty],$_POST[$xremise]);
			}
	    }
	
	    $id = $propal->create();
    }
    /*
     *   Generation
     */
    if ($id > 0)
    {
        propale_pdf_create($db, $id, $_POST['model']);
        
        Header ('Location: '.$_SERVER["PHP_SELF"].'?propalid='.$id);
        exit;
    }
    else
    {
        dolibarr_print_error($db,$propal->error);
        exit;
    }
}

if ($_GET['action'] == 'builddoc')
{
    $propal = new Propal($db);
    $propal->fetch($_GET['propalid']);
    propale_pdf_create($db, $_GET['propalid'], $propal->modelpdf);
}

/*
 *  Cloture de la propale
 */
if ($_POST['action'] == 'setstatut' && $user->rights->propale->cloturer) 
{
    if (! $_POST['cancel'])
    {
        $propal = new Propal($db);
        $propal->fetch($_GET['propalid']);
        $propal->cloture($user, $_POST['statut'], $_POST['note']);
    }
}

/*
 * Envoi de la propale par mail
 */
if ($_POST['action'] == 'send')
{
    $langs->load('mails');
    $propal= new Propal($db);
    if ( $propal->fetch($_POST['propalid']) )
    {
        $propalref = sanitize_string($propal->ref);
        $file = $conf->propal->dir_output . '/' . $propalref . '/' . $propalref . '.pdf';
        if (is_readable($file))
        {
            $soc = new Societe($db, $propal->socidp);
            if ($_POST['sendto'])
            {
                // Le destinataire a �t� fourni via le champ libre
                $sendto = $_POST['sendto'];
                $sendtoid = 0;
            }
            elseif ($_POST['receiver'])
            {
                // Le destinataire a �t� fourni via la liste d�roulante
                $sendto = $soc->contact_get_email($_POST['receiver']);
                $sendtoid = $_POST['receiver'];
            }

            if (strlen($sendto))
            {
                $from = $_POST['fromname'] . ' <' . $_POST['frommail'] .'>';
                $replyto = $_POST['replytoname']. ' <' . $_POST['replytomail'].'>';
                $message = $_POST['message'];
                $sendtocc = $_POST['sendtocc'];
                
                if ($_POST['action'] == 'send')
                {
                	$subject = $_POST['subject'];
                	
                	if($subject == '')
                	{
                		$subject = $langs->trans('Propal').' '.$propal->ref;
                	}
                  
                  $actiontypeid=3;
                  $actionmsg ='Mail envoy� par '.$from.' � '.$sendto.'.<br>';
                  
                  if ($message)
                  {
                    $actionmsg.='Texte utilis� dans le corps du message:<br>';
                    $actionmsg.=$message;
                  }
                  
                  $actionmsg2='Envoi Propal par mail';
                }

                $filepath[0] = $file;
                $filename[0] = $propal->ref.'.pdf';
                $mimetype[0] = 'application/pdf';
                if ($_FILES['addedfile']['tmp_name'])
                {
                    $filepath[1] = $_FILES['addedfile']['tmp_name'];
                    $filename[1] = $_FILES['addedfile']['name'];
                    $mimetype[1] = $_FILES['addedfile']['type'];
                }
                // Envoi de la propal
                $mailfile = new CMailFile($subject,$sendto,$from,$message,$filepath,$mimetype,$filename,$sendtocc);
                if ($mailfile->sendfile())
                {
                    $msg='<div class="ok">'.$langs->trans('MailSuccessfulySent',$from,$sendto).'.</div>';
                    // Insertion action
                    include_once(DOL_DOCUMENT_ROOT."/contact.class.php");
                    $actioncomm = new ActionComm($db);
                    $actioncomm->type_id     = $actiontypeid;
                    $actioncomm->label       = $actionmsg2;
                    $actioncomm->note        = $actionmsg;
                    $actioncomm->date        = time();  // L'action est faite maintenant
                    $actioncomm->percent     = 100;
                    $actioncomm->contact     = new Contact($db,$sendtoid);
                    $actioncomm->societe     = new Societe($db,$propal->socidp);
                    $actioncomm->user        = $user;   // User qui a fait l'action
                    $actioncomm->propalrowid = $propal->id;
                    $ret=$actioncomm->add($user);       // User qui saisi l'action
                    if ($ret < 0)
                    {
                        dolibarr_print_error($db);
                    }
                    else
                    {
                        // Renvoie sur la fiche
                        Header('Location: '.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&msg='.urlencode($msg));
                        exit;
                    }
                }
                else
                {
                    $msg='<div class="error">'.$langs->trans('ErrorFailedToSendMail',$from,$sendto).' - '.$actioncomm->error.'</div>';
                }
            }
            else
            {
                $msg='<div class="error">'.$langs->trans('ErrorMailRecipientIsEmpty').' !</div>';
                dolibarr_syslog('Le mail du destinataire est vide');
            }
        }
        else
        {
            dolibarr_syslog('Impossible de lire :'.$file);
        }
    }
    else
    {
        dolibarr_syslog('Impossible de lire les donn�es de la propale. Le fichier propal n\'a peut-�tre pas �t� g�n�r�.');
    }
}

if ($_GET['action'] == 'commande')
{
  /*
   *  Cloture de la propale
   */
  $propal = new Propal($db);
  $propal->fetch($propalid);
  $propal->create_commande($user);
}

if ($_GET['action'] == 'modif' && $user->rights->propale->creer) 
{
  /*
   *  Repasse la propale en mode brouillon
   */
  $propal = new Propal($db);
  $propal->fetch($_GET['propalid']);
  $propal->reopen($user->id);
}


/*
 *  Ajout d'une ligne produit dans la propale
 */
if ($_POST['action'] == "addligne" && $user->rights->propale->creer) 
{
	if ($_POST['qty'] && (($_POST['np_price']>=0 && $_POST['np_desc']) || $_POST['idprod']))
	{
	    $propal = new Propal($db);
	    $ret=$propal->fetch($_POST['propalid']);
	
	    if (isset($_POST['np_tva_tx']))
	    {
	        $propal->insert_product_generic(
					    $_POST['np_desc'], 
					    $_POST['np_price'], 
					    $_POST['qty'],
					    $_POST['np_tva_tx'],
					    $_POST['np_remise']);
	    }
	    else 
	    {
	        $propal->insert_product(
	                    $_POST['idprod'],
	                    $_POST['qty'],
	                    $_POST['remise'],
	                    $_POST['np_desc']);
	    }
	    propale_pdf_create($db, $_POST['propalid'], $propal->modelpdf);
	}
}

if ($_POST['action'] == 'updateligne' && $user->rights->propale->creer && $_POST["save"] == $langs->trans("Save")) 
{
    /*
     *  Mise � jour d'une ligne dans la propale
     */
    $propal = new Propal($db);
    $propal->fetch($_GET['propalid']);
    $propal->UpdateLigne($_POST['ligne'], $_POST['subprice'], $_POST['qty'], $_POST['remise_percent'], $_POST['tva_tx'], $_POST['desc']);
    propale_pdf_create($db, $_GET['propalid'], $propal->modelpdf);
}

if ($_POST['action'] == 'builddoc' && $user->rights->propale->creer) 
{
    $propal = new Propal($db, 0, $_GET['propalid']);
    $propal->set_pdf_model($user, $_POST['model']);
    propale_pdf_create($db, $_GET['propalid'], $_POST['model']);
}


if ($_GET['action'] == 'del_ligne' && $user->rights->propale->creer) 
{
  /*
   *  Supprime une ligne produit dans la propale
   */
  $propal = new Propal($db);
  $propal->fetch($_GET['propalid']);
  $propal->delete_product($_GET['ligne']);
  propale_pdf_create($db, $_GET['propalid'], $propal->modelpdf);
}

if ($_POST['action'] == 'set_discount' && $user->rights->propale->creer) 
{
  $propal = new Propal($db);
  $propal->fetch($_GET['propalid']);
  $propal->set_remise($user, $_POST['remise']);
  propale_pdf_create($db, $_GET['propalid'], $propal->modelpdf);
}

if ($_POST['action'] == 'set_project')
{
  $propal = new Propal($db);
  $propal->fetch($_GET['propalid']);
  $propal->set_project($user, $_POST['projetidp']);
}

if ($_POST['action'] == 'set_contact')
{
  $propal = new Propal($db);
  $propal->fetch($_GET['propalid']);
  $propal->set_contact($user, $_POST['contactidp']);
}

// Conditions de r�glement
if ($_POST["action"] == 'setconditions')
{ 
	$propal = new Propal($db, $_GET["propalid"]);
	$propal->cond_reglement_id = $_POST['cond_reglement_id'];
	$sql = "UPDATE ".MAIN_DB_PREFIX."propal";
	$sql.= " SET fk_cond_reglement='".$_POST['cond_reglement_id']."'";
	$sql.= " WHERE rowid='".$_GET["propalid"]."'";
	$resql = $db->query($sql);
	if ($resql < 0) dolibarr_print_error($db);
}

if ($_REQUEST['action'] == 'setremisepercent' && $user->rights->propale->creer)
{
	$propal = new Propal($db);
	$propal->fetch($_REQUEST['propalid']);
	$result = $propal->set_remise_percent($user, $_POST['remise_percent']);
	$_GET['propalid']=$_REQUEST['propalid'];
}

if ($_REQUEST['action'] == 'setremiseabsolue' && $user->rights->propale->creer)
{
	$propal = new Propal($db);
	$propal->fetch($_REQUEST['propalid']);
	$result = $propal->set_remise_absolue($user, $_POST['remise_absolue']);
	$_GET['propalid']=$_REQUEST['propalid'];
}

// Mode de r�glement
if ($_POST["action"] == 'setmode')
{
	$propal = new Propal($db, $_GET["propalid"]);
	$propal->mode_reglement_id = $_POST['mode_reglement_id'];
	// \todo Cr�er une methode propal->cond_reglement
	$sql = "UPDATE ".MAIN_DB_PREFIX."propal";
	$sql.= " SET fk_mode_reglement='".$_POST['mode_reglement_id']."'";
	$sql.= " WHERE rowid='".$_GET["propalid"]."'";
	$resql = $db->query($sql);
	if ($resql < 0) dolibarr_print_error($db);
}


llxHeader();

$html = new Form($db);

/*
 * Affichage fiche propal en mode visu
 *
 */
if ($_GET['propalid'] > 0)
{
  if ($msg) print "$msg<br>";

  $propal = new Propal($db);
  $propal->fetch($_GET['propalid']);

  $societe = new Societe($db);
  $societe->fetch($propal->soc_id);
  $h=0;

  $head[$h][0] = DOL_URL_ROOT.'/comm/propal.php?propalid='.$propal->id;
  $head[$h][1] = $langs->trans('CommercialCard');
  $hselected=$h;
  $h++;

  $head[$h][0] = DOL_URL_ROOT.'/compta/propal.php?propalid='.$propal->id;
  $head[$h][1] = $langs->trans('AccountancyCard');
  $h++;

	if ($conf->use_preview_tabs)
	{
  $head[$h][0] = DOL_URL_ROOT.'/comm/propal/apercu.php?propalid='.$propal->id;
  $head[$h][1] = $langs->trans("Preview");
  $h++;
    }
    
  $head[$h][0] = DOL_URL_ROOT.'/comm/propal/note.php?propalid='.$propal->id;
  $head[$h][1] = $langs->trans('Note');
  $h++;

  $head[$h][0] = DOL_URL_ROOT.'/comm/propal/info.php?propalid='.$propal->id;
  $head[$h][1] = $langs->trans('Info');
  $h++;

  $head[$h][0] = DOL_URL_ROOT.'/comm/propal/document.php?propalid='.$propal->id;
  $head[$h][1] = $langs->trans('Documents');
  $h++;

  dolibarr_fiche_head($head, $hselected, $langs->trans('Proposal'));

  /*
   * Confirmation de la suppression de la propale
   */
  if ($_GET['action'] == 'delete')
    {
      $html->form_confirm($_SERVER["PHP_SELF"].'?propalid='.$propal->id, $langs->trans('DeleteProp'), $langs->trans('ConfirmDeleteProp'), 'confirm_delete');
      print '<br>';
    }

  /*
   * Confirmation de la validation de la propale
   */
  if ($_GET['action'] == 'validate')
    {
      $html->form_confirm($_SERVER["PHP_SELF"].'?propalid='.$propal->id, $langs->trans('ValidateProp'), $langs->trans('ConfirmValidateProp'), 'confirm_validate');
      print '<br>';
    }


  /*
   * Fiche propal
   *
   */
	$sql = 'SELECT s.nom, s.idp, p.price, p.fk_projet, p.remise, p.tva, p.total, p.ref, p.fk_statut, p.fk_cond_reglement, p.fk_mode_reglement, '.$db->pdate('p.datep').' as dp, p.note,';
	$sql.= ' x.firstname, x.name, x.fax, x.phone, x.email, p.fk_user_author, p.fk_user_valid, p.fk_user_cloture, p.datec, p.date_valid, p.date_cloture';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'societe as s, '.MAIN_DB_PREFIX.'propal as p, '.MAIN_DB_PREFIX.'socpeople as x';
	$sql.= ' WHERE p.fk_soc = s.idp AND p.fk_soc_contact = x.idp AND p.rowid = '.$propal->id;
	if ($socidp) $sql .= ' AND s.idp = '.$socidp;
	
	$resql = $db->query($sql);
	if ($resql)
    {
        if ($db->num_rows($resql))
        {
            $obj = $db->fetch_object($resql);
    
            $societe = new Societe($db);
            $societe->fetch($obj->idp);
    
            print '<table class="border" width="100%">';

	        // Ref
	        print '<tr><td>'.$langs->trans('Ref').'</td><td colspan="5">'.$propal->ref_url.'</td></tr>';

            $rowspan=9;
            
            // Soci�t�
            print '<tr><td>'.$langs->trans('Company').'</td><td colspan="5">';
            if ($societe->client == 1)
            {
                $url ='fiche.php?socid='.$societe->id;
            }
            else
            {
                $url = DOL_URL_ROOT.'/comm/prospect/fiche.php?socid='.$societe->id;
            }
            print '<a href="'.$url.'">'.$societe->nom.'</a></td>';
            print '</tr>';
    
            // Dates
            print '<tr><td>'.$langs->trans('Date').'</td><td colspan="3">';
            print dolibarr_print_date($propal->date,'%a %d %B %Y');
            print '</td>';

            if ($conf->projet->enabled) $rowspan++;
            if ($conf->global->PROPAL_ADD_SHIPPING_DATE) $rowspan++;
            if ($conf->global->PROPAL_ADD_DELIVERY_ADDRESS) $rowspan++;
    
            // Notes
            print '<td valign="top" colspan="2" width="50%" rowspan="'.$rowspan.'">'.$langs->trans('NotePublic').' :<br>'. nl2br($propal->note_public).'</td>';
    		print '</tr>';
    
    		// Date fin propal
    		print '<tr>';
			print '<td>';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('DateEndPropal');
			print '</td>';
			if ($_GET['action'] != 'editecheance' && $propal->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editecheance&amp;propalid='.$propal->id.'">'.img_edit($langs->trans('SetConditions'),1).'</a></td>';
			print '</tr></table>';
            print '<td colspan="3">';
            if ($propal->brouillon && $_GET['action'] == 'editecheance')
            {
                print '<form name="editecheance" action="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'" method="post">';
                print '<input type="hidden" name="action" value="setecheance">';
                $html->select_date($propal->fin_validite,'ech','','','',"editecheance");
                print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
                print '</form>';
            }
            else
            {
                if ($propal->fin_validite)
                {
                    print dolibarr_print_date($propal->fin_validite,'%a %d %B %Y');
                    if ($propal->statut == 1 && $propal->fin_validite < (time() - $conf->propal->cloture->warning_delay)) print img_warning($langs->trans("Late"));
                }
                else
                {
                    print $langs->trans("Unknown");
                }
            }
            print '</td>';
            print '</tr>';
    		
			
			// date de livraison (conditonn� sur PROPAL_ADD_SHIPPING_DATE car carac �
			// g�rer par les commandes et non les propal
			if ($conf->global->PROPAL_ADD_SHIPPING_DATE)
			{
				print '<tr><td>';
				print '<table class="nobordernopadding" width="100%"><tr><td>';
				print $langs->trans('DateDelivery');
				print '</td>';
				if ($_GET['action'] != 'editdate_livraison' && $propal->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editdate_livraison&amp;propalid='.$propal->id.'">'.img_edit($langs->trans('SetDateLivraison'),1).'</a></td>';
				print '</tr></table>';
				print '</td><td colspan="3">';
				if ($_GET['action'] == 'editdate_livraison')
				{
					print '<form name="editdate_livraison" action="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'" method="post">';
	                print '<input type="hidden" name="action" value="setdate_livraison">';
	                $html->select_date($propal->date_livraison,'liv_','','','',"editdate_livraison");
	                print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
	                print '</form>';
				}
				else
				{
					print dolibarr_print_date($propal->date_livraison,'%a %d %B %Y');
				}
				print '</td>';
				print '</tr>';
			}
			
			// adresse de livraison
			if ($conf->global->PROPAL_ADD_DELIVERY_ADDRESS)
      {
      	print '<tr><td>';
      	print '<table class="nobordernopadding" width="100%"><tr><td>';
      	print $langs->trans('DeliveryAddress');
      	print '</td>';
					
			  if ($_GET['action'] != 'editdelivery_adress' && $propal->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editdelivery_adress&amp;socidp='.$propal->socidp.'&amp;propalid='.$propal->id.'">'.img_edit($langs->trans('SetDeliveryAddress'),1).'</a></td>';
			  print '</tr></table>';
			  print '</td><td colspan="3">';
			
			  if ($_GET['action'] == 'editdelivery_adress')
			  {
				  $html->form_adresse_livraison($_SERVER['PHP_SELF'].'?propalid='.$propal->id,$propal->adresse_livraison_id,$_GET['socidp'],'adresse_livraison_id','propal',$propal->id);
			  }
			  else
			  {
				  $html->form_adresse_livraison($_SERVER['PHP_SELF'].'?propalid='.$propal->id,$propal->adresse_livraison_id,$_GET['socidp'],'none','propal',$propal->id);
			  }
			  print '</td></tr>';
			}
						
			// Conditions et modes de r�glement
			print '<tr><td>';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('PaymentConditionsShort');
			print '</td>';
			if ($_GET['action'] != 'editconditions' && $propal->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editconditions&amp;propalid='.$propal->id.'">'.img_edit($langs->trans('SetConditions'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="3">';
			if ($_GET['action'] == 'editconditions')
			{
				$html->form_conditions_reglement($_SERVER['PHP_SELF'].'?propalid='.$propal->id,$propal->cond_reglement_id,'cond_reglement_id');
			}
			else
			{
				$html->form_conditions_reglement($_SERVER['PHP_SELF'].'?propalid='.$propal->id,$propal->cond_reglement_id,'none');
			}
			print '</td>';
			print '</tr>';
			
			// Mode paiement
			print '<tr>';
			print '<td width="25%">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('PaymentMode');
			print '</td>';
			if ($_GET['action'] != 'editmode' && $propal->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editmode&amp;propalid='.$propal->id.'">'.img_edit($langs->trans('SetMode'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="3">';
			if ($_GET['action'] == 'editmode')
			{
				$html->form_modes_reglement($_SERVER['PHP_SELF'].'?propalid='.$propal->id,$propal->mode_reglement_id,'mode_reglement_id');
			}
			else
			{
				$html->form_modes_reglement($_SERVER['PHP_SELF'].'?propalid='.$propal->id,$propal->mode_reglement_id,'none');
			}
			print '</td></tr>';

            // Destinataire
            $langs->load('mails');
            print '<tr><td>';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
            print $langs->trans('MailTo');
			print '</td>';
			if ($_GET['action'] != 'editcontact' && $propal->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editcontact&amp;propalid='.$propal->id.'">'.img_edit($langs->trans('SetReceiver'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="3">';
			if ($_GET['action'] == 'editcontact')
			{
				$html->form_contacts($_SERVER['PHP_SELF'].'?propalid='.$propal->id,$societe,$propal->contactid,'contactidp');
			}
			else
			{
				$html->form_contacts($_SERVER['PHP_SELF'].'?propalid='.$propal->id,$societe,$propal->contactid,'none');
			}
			print '</td>';
			print '</tr>';
				    
			// Projet
            if ($conf->projet->enabled)
            {
                $langs->load("projects");
                print '<tr><td>'.$langs->trans('Project').'</td>';
                $numprojet = $societe->has_projects();
                if (! $numprojet)
                {
                    print '<td colspan="2">';
                    print $langs->trans("NoProject").'</td><td>';
                    print '<a href=../projet/fiche.php?socidp='.$societe->id.'&action=create>'.$langs->trans('AddProject').'</a>';
                    print '</td>';
                }
                else
                {
                    if ($propal->statut == 0 && $user->rights->propale->creer)
                    {
                        print '<td colspan="2">';
                        print '<form action="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'" method="post">';
                        print '<input type="hidden" name="action" value="set_project">';
                        $form->select_projects($societe->id, $propal->projetidp, 'projetidp');
                        print '</td><td>';
                        print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
                        print '</form>';
                        print '</td>';
                    }
                    else
                    {
                        if (!empty($propal->projetidp))
                        {
                            print '<td colspan="3">';
                            $proj = new Project($db);
                            $proj->fetch($propal->projetidp);
                            print '<a href="../projet/fiche.php?id='.$propal->projetidp.'" title="'.$langs->trans('ShowProject').'">';
                            print $proj->title;
                            print '</a>';
                            print '</td>';
                        }
                        else {
                            print '<td colspan="3">&nbsp;</td>';
                        }
                    }
                }
                print '</tr>';
            }
    
			// Amount   
            print '<tr><td height="10">'.$langs->trans('AmountHT').'</td>';
            print '<td align="right" colspan="2"><b>'.price($propal->price).'</b></td>';
            print '<td>'.$langs->trans("Currency".$conf->monnaie).'</td></tr>';
    
            print '<tr><td height="10">'.$langs->trans('AmountVAT').'</td><td align="right" colspan="2">'.price($propal->total_tva).'</td>';
            print '<td>'.$langs->trans("Currency".$conf->monnaie).'</td></tr>';
            print '<tr><td height="10">'.$langs->trans('AmountTTC').'</td><td align="right" colspan="2">'.price($propal->total_ttc).'</td>';
            print '<td>'.$langs->trans("Currency".$conf->monnaie).'</td></tr>';
    
            // Statut
            print '<tr><td height="10">'.$langs->trans('Status').'</td><td align="left" colspan="3">'.$propal->getLibStatut(4).'</td></tr>';
            print '</table><br>';
    
            /*
            * Lignes de propale
            *
            */
            print '<table class="noborder" width="100%">';

            $sql = 'SELECT pt.rowid, pt.description, pt.price, pt.fk_product, pt.qty, pt.tva_tx, pt.remise_percent, pt.subprice,';
            $sql.= ' p.label as product, p.ref, p.fk_product_type, p.rowid as prodid,';
           	$sql.= ' p.description as product_desc';
            $sql.= ' FROM '.MAIN_DB_PREFIX.'propaldet as pt';
            $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON pt.fk_product=p.rowid';
            $sql.= ' WHERE pt.fk_propal = '.$propal->id;
            $sql.= ' ORDER BY pt.rowid ASC';
            $resql = $db->query($sql);
            if ($resql)
            {
                $num = $db->num_rows($resql);
                $i = 0; $total = 0;
    
                if ($num)
                {
                    print '<tr class="liste_titre">';
                    print '<td>'.$langs->trans('Description').'</td>';
                    print '<td align="right" width="50">'.$langs->trans('VAT').'</td>';
                    print '<td align="right" width="80">'.$langs->trans('PriceUHT').'</td>';
                    print '<td align="right" width="50">'.$langs->trans('Qty').'</td>';
                    print '<td align="right" width="50">'.$langs->trans('Discount').'</td>';
                    print '<td align="right" width="50">'.$langs->trans('AmountHT').'</td>';
                    print '<td width="16">&nbsp;</td>';
                    print '<td width="16">&nbsp;</td>';
					print '<td width="16">&nbsp;</td>';
                    print "</tr>\n";
                }
                $var=true;
                while ($i < $num)
                {
                    $objp = $db->fetch_object($resql);
                    $var=!$var;
    
                    // Ligne en mode visu
                    if ($_GET['action'] != 'editline' || $_GET['ligne'] != $objp->rowid)
                    {
                        print '<tr '.$bc[$var].'>';
                        if ($objp->fk_product > 0)
                        {
                            print '<td><a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">';
                            if ($objp->fk_product_type) print img_object($langs->trans('ShowService'),'service');
                            else print img_object($langs->trans('ShowProduct'),'product');
                            print ' '.$objp->ref.'</a>';
							print ' - '.nl2br(stripslashes($objp->product));
							              
			              	if ($conf->global->PROP_ADD_PROD_DESC && !$conf->global->PRODUIT_CHANGE_PROD_DESC)
                            {
                            	print '<br>'.nl2br(stripslashes($objp->product_desc));
                            }
							              
                            if ($objp->date_start && $objp->date_end)
                            {
                                print ' (Du '.dolibarr_print_date($objp->date_start).' au '.dolibarr_print_date($objp->date_end).')';
                            }
                            if ($objp->date_start && ! $objp->date_end)
                            {
                                print ' (A partir du '.dolibarr_print_date($objp->date_start).')';
                            }
                            if (! $objp->date_start && $objp->date_end)
                            {
                                print " (Jusqu'au ".dolibarr_print_date($objp->date_end).')';
                            }
                            print ($objp->description && $objp->description!=$objp->product)?'<br>'.stripslashes(nl2br($objp->description)):'';
                            print '</td>';
                        }
                        else
                        {
                            print '<td>'.stripslashes(nl2br($objp->description));
                            if ($objp->date_start && $objp->date_end)
                            {
                                print ' (Du '.dolibarr_print_date($objp->date_start).' au '.dolibarr_print_date($objp->date_end).')';
                            }
                            if ($objp->date_start && ! $objp->date_end)
                            {
                                print ' (A partir du '.dolibarr_print_date($objp->date_start).')';
                            }
                            if (! $objp->date_start && $objp->date_end)
                            {
                                print " (Jusqu'au ".dolibarr_print_date($objp->date_end).')';
                            }
                            print "</td>\n";
                        }
                        print '<td align="right">'.$objp->tva_tx.'%</td>';
                        print '<td align="right">'.price($objp->subprice)."</td>\n";
                        print '<td align="right">'.$objp->qty.'</td>';
                        if ($objp->remise_percent > 0)
                        {
                            print '<td align="right">'.$objp->remise_percent."%</td>\n";
                        }
                        else
                        {
                            print '<td>&nbsp;</td>';
                        }
                        print '<td align="right">'.price($objp->subprice*$objp->qty*(100-$objp->remise_percent)/100)."</td>\n";
    
                        // Icone d'edition et suppression
                        if ($propal->statut == 0  && $user->rights->propale->creer)
                        {
                            print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=editline&amp;ligne='.$objp->rowid.'">';
                            print img_edit();
                            print '</a></td>';
                            print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=del_ligne&amp;ligne='.$objp->rowid.'">';
                            print img_delete();
                            print '</a></td>';
  							print '<td align="right">';
  							print '&nbsp;';		// \todo Mettre critere ordre
							print '</td>';  							
						}
                        else
                        {
							print '<td colspan="3">&nbsp;</td>';
                        }
                        print '</tr>';
                    }
    
                    // Ligne en mode update
                    if ($propal->statut == 0 && $_GET["action"] == 'editline' && $user->rights->propale->creer && $_GET["ligne"] == $objp->rowid)
                    {
                        print '<form action="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'" method="post">';
                        print '<input type="hidden" name="action" value="updateligne">';
                        print '<input type="hidden" name="propalid" value="'.$propal->id.'">';
                        print '<input type="hidden" name="ligne" value="'.$_GET["ligne"].'">';
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
                        print '<textarea name="desc" cols="50" rows="'.ROWS_2.'">'.stripslashes($objp->description).'</textarea></td>';
                        print '<td align="right">';
						if($societe->tva_assuj == "0")
							print '<input type="hidden" name="tva_tx" value="0">0';
						else
                        	print $html->select_tva("tva_tx",$objp->tva_tx,$mysoc,$societe);
                        print '</td>';
                        print '<td align="right"><input size="6" type="text" name="subprice" value="'.price($objp->subprice).'"></td>';
                        print '<td align="right"><input size="2" type="text" name="qty" value="'.$objp->qty.'"></td>';
                        print '<td align="right" nowrap><input size="2" type="text" name="remise_percent" value="'.$objp->remise_percent.'">%</td>';
                        print '<td align="center" colspan="5" valign="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
                        print '<br /><input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></td>';
                        print '</tr>' . "\n";
                        /*
						if ($conf->service->enabled)
                        {
                            print "<tr $bc[$var]>";
                            print '<td colspan="5">Si produit de type service � dur�e limit�e: Du ';
                            print $html->select_date($objp->date_start,"date_start",0,0,$objp->date_start?0:1);
                            print ' au ';
                            print $html->select_date($objp->date_end,"date_end",0,0,$objp->date_end?0:1);
                            print '</td>';
                            print '</tr>' . "\n";
                        }
						*/
                        print "</form>\n";
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
			 * Lignes de remise
			 */
			
			// Remise relative
			$var=!$var;
			print '<form name="updateligne" action="'.$_SERVER["PHP_SELF"].'" method="post">';
			print '<input type="hidden" name="action" value="setremisepercent">';
			print '<input type="hidden" name="propalid" value="'.$propal->id.'">';
			print '<tr class="liste_total"><td>';
			print $langs->trans('CustomerRelativeDiscount');
			if ($propal->brouillon) print ' <font style="font-weight: normal">('.($soc->remise_client?$langs->trans("CompanyHasRelativeDiscount",$soc->remise_client):$langs->trans("CompanyHasNoRelativeDiscount")).')</font>';
			print '</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td align="right"><font style="font-weight: normal">';
			if ($_GET['action'] == 'editrelativediscount')
			{
				print '<input type="text" name="remise_percent" size="2" value="'.$propal->remise_percent.'">%';
			}
			else
			{
				print $propal->remise_percent?$propal->remise_percent.'%':'&nbsp;';
			}
			print '</font></td>';
			print '<td align="right"><font style="font-weight: normal">';
			if ($_GET['action'] != 'editrelativediscount') print $propal->remise_percent?'-'.price($propal->remise_percent*$total/100):$langs->trans("DiscountNone");
			else print '&nbsp;';
			print '</font></td>';
			if ($_GET['action'] != 'editrelativediscount')
			{
				if ($propal->brouillon && $user->rights->propale->creer)
				{
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editrelativediscount&amp;propalid='.$propal->id.'">'.img_edit($langs->trans('SetRelativeDiscount'),1).'</a></td>';
				}
				else
				{
					print '<td>&nbsp;</td>';
				}
				if ($propal->brouillon && $user->rights->propale->creer && $propal->remise_percent)
				{
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=setremisepercent&amp;rowid='.$objp->rowid.'">';
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

			// Remise absolue
			$var=!$var;
			print '<form name="updateligne" action="'.$_SERVER["PHP_SELF"].'" method="post">';
			print '<input type="hidden" name="action" value="setremiseabsolue">';
			print '<input type="hidden" name="propalid" value="'.$propal->id.'">';
			print '<tr class="liste_total"><td>';
			print $langs->trans('CustomerAbsoluteDiscount');
			if ($propal->brouillon) print ' <font style="font-weight: normal">('.($avoir_en_cours?$langs->trans("CompanyHasAbsoluteDiscount",$avoir_en_cours,$langs->trans("Currency".$conf->monnaie)):$langs->trans("CompanyHasNoAbsoluteDiscount")).')</font>';
			print '</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td>&nbsp;</td>';
			print '<td align="right"><font style="font-weight: normal">';
			if ($_GET['action'] == 'editabsolutediscount')
			{
				print '-<input type="text" name="remise_absolue" size="2" value="'.$propal->remise_absolue.'">';
			}
			else
			{
				print $propal->remise_absolue?'-'.price($propal->remise_absolue):$langs->trans("DiscountNone");
			}
			print '</font></td>';
			if ($_GET['action'] != 'editabsolutediscount')
			{
				if ($propal->brouillon && $user->rights->propale->creer)
				{
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editabsolutediscount&amp;propalid='.$propal->id.'">'.img_edit($langs->trans('SetAbsoluteDiscount'),1).'</a></td>';
				}
				else
				{
					print '<td>&nbsp;</td>';
				}
				if ($propal->brouillon && $user->rights->propale->creer && $propal->remise_absolue)
				{
					print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=setremiseabsolue&amp;rowid='.$objp->rowid.'">';
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
			
			
            /*
             * Ajouter une ligne
             */
            if ($propal->statut == 0 && $user->rights->propale->creer && $_GET["action"] <> 'editline')
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
				print '<td>&nbsp;</td>';
                print "</tr>\n";
    
                // Ajout produit produits/services personalis�s
                print '<form action="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'" method="post">';
                print '<input type="hidden" name="propalid" value="'.$propal->id.'">';
                print '<input type="hidden" name="action" value="addligne">';

                $var=true;

                print '<tr '.$bc[$var].">\n";
                print '<td><textarea cols="50" name="np_desc" rows="'.ROWS_2.'"></textarea></td>';
                print '<td align="center">';
				        if($societe->tva_assuj == "0")
				        {
				        	print '<input type="hidden" name="np_tva_tx" value="0">0';
				        }
				        else
				        {
				        	$html->select_tva('np_tva_tx', $conf->defaulttx, $mysoc, $societe);
				        }
                print "</td>\n";
                print '<td align="right"><input type="text" size="5" name="np_price"></td>';
                print '<td align="right"><input type="text" size="2" value="1" name="qty"></td>';
                print '<td align="right" nowrap><input type="text" size="2" value="'.$societe->remise_client.'" name="np_remise">%</td>';
                print '<td align="center" valign="middle" colspan="4"><input type="submit" class="button" value="'.$langs->trans('Add').'" name="addligne"></td>';
                print '</tr>';
    
                print '</form>';

                // Ajout de produits/services pr�d�finis
                if ($conf->produit->enabled)
                {
                    print '<form id="addpredefinedproduct" action="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'" method="post">';
                    print '<input type="hidden" name="propalid" value="'.$propal->id.'">';
                    print '<input type="hidden" name="action" value="addligne">';
        
                    $var=!$var;

                    print '<tr '.$bc[$var].'>';
                    print '<td colspan="2">';
					          // multiprix
					          if($conf->global->PRODUIT_MULTIPRICES == 1)
					          {
					          	$html->select_produits('','idprod','',$conf->produit->limit_size,$societe->price_level);
					          }
					          else
					          {
					          	$html->select_produits('','idprod','',$conf->produit->limit_size);
					          }
                    print '<br>';
                    print '<textarea cols="50" name="np_desc" rows="'.ROWS_2.'"></textarea>';
                    print '</td>';
                    print '<td>&nbsp;</td>';
                    print '<td align="right"><input type="text" size="2" name="qty" value="1"></td>';
                    print '<td align="right" nowrap><input type="text" size="2" name="remise" value="'.$societe->remise_client.'">%</td>';
                    print '<td align="center" valign="middle" colspan="4"><input type="submit" class="button" value="'.$langs->trans("Add").'" name="addligne">';
                    print '</td></tr>'."\n";
      
                    print '</form>';
                }
            }

            print '</table>';
        }
    }
  	else
    {
    	dolibarr_print_error($db);
    }
  
	print '</div>';
	print "\n";

	/*
 	 * Formulaire cloture (sign� ou non)
	 */
  	if ($_GET['action'] == 'statut') 
    {
      print '<form action="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'" method="post">';
      print '<table class="border" width="100%">';
      print '<tr><td>'.$langs->trans('Note').'</td><td><textarea cols="60" rows="'.ROWS_3.'" wrap="soft" name="note">';
      print $propal->note;
      print '</textarea></td></tr>';
      print '<tr><td>'.$langs->trans("CloseAs").'</td><td>';
      print '<input type="hidden" name="action" value="setstatut">';
      print '<select name="statut">';
      print '<option value="2">'.$propal->labelstatut[2].'</option>';
      print '<option value="3">'.$propal->labelstatut[3].'</option>';
      print '</select>';
      print '</td></tr>';
      print '<tr><td align="center" colspan="2">';
      print '<input type="submit" class="button" name="validate" value="'.$langs->trans('Validate').'">';
      print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
      print '</td>';
      print '</tr></table></form>';
    }


    /*
     * Boutons Actions
     */
    print '<div class="tabsAction">';
    
    if ($_GET['action'] != 'statut')
    {
        
        // Valid
        if ($propal->statut == 0)
        {
            if ($user->rights->propale->valider)
            {
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=validate">'.$langs->trans('Validate').'</a>';
            }
        }
        
        // Save
        if ($propal->statut == 1)
        {
            if ($user->rights->propale->creer)
            {
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=modif">'.$langs->trans('Edit').'</a>';
            }
        }
        
        // Build PDF
        if ($user->rights->propale->creer)
        {
            if ($propal->statut < 2)
            {
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=builddoc">'.$langs->trans("BuildPDF").'</a>';
            }
            else
            {
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=builddoc">'.$langs->trans("RebuildPDF").'</a>';
            }
        }
        
        // Send
        if ($propal->statut == 1)
        {
            if ($user->rights->propale->envoyer)
            {
                $propref = sanitize_string($propal->ref);
                $file = $conf->propal->dir_output . '/'.$propref.'/'.$propref.'.pdf';
                if (file_exists($file))
                {
                    print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=presend">'.$langs->trans('Send').'</a>';
                }
            }
        }
        
        // Close
        if ($propal->statut != 0)
        {
            if ($propal->statut == 1 && $user->rights->propale->cloturer)
            {
                print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=statut">'.$langs->trans('Close').'</a>';
            }
        }
        
        // Delete
        if ($propal->statut == 0)
        {
            if ($user->rights->propale->supprimer)
            {
                print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?propalid='.$propal->id.'&amp;action=delete">'.$langs->trans('Delete').'</a>';
            }
        }
    
    }
    
    print '</div>';
    print "<br>\n";
    
    
    print '<table width="100%"><tr><td width="50%" valign="top">';


    /*
     * Documents g�n�r�s
     */
    $filename=sanitize_string($propal->ref);
    $filedir=$conf->propal->dir_output . "/" . sanitize_string($propal->ref);
    $urlsource=$_SERVER["PHP_SELF"]."?propalid=".$propal->id;
    $genallowed=$user->rights->propale->creer;
    $delallowed=$user->rights->propale->supprimer;
    
    $var=true;
    
    $html->show_documents('propal',$filename,$filedir,$urlsource,$genallowed,$delallowed,$propal->modelpdf);


  /*
   * Commandes rattach�es
   */
  if($conf->commande->enabled)
    {
      $coms = $propal->associated_orders();
      if (sizeof($coms) > 0)
	{
	  print '<br>';
	  print_titre($langs->trans('RelatedOrders'));
	  print '<table class="noborder" width="100%">';
	  print '<tr class="liste_titre">';
	  print '<td>'.$langs->trans("Ref").'</td>';
	  print '<td align="center">'.$langs->trans("Date").'</td>';
	  print '<td align="right">'.$langs->trans("Price").'</td>';
	  print '</tr>';
	  $var=true;
	  for ($i = 0 ; $i < sizeof($coms) ; $i++)
	    {
	      $var=!$var;
	      print '<tr '.$bc[$var].'><td>';
	      print '<a href="'.DOL_URL_ROOT.'/commande/fiche.php?id='.$coms[$i]->id.'">'.img_object($langs->trans("ShowOrder"),"order").' '.$coms[$i]->ref."</a></td>\n";
	      print '<td align="center">'.dolibarr_print_date($coms[$i]->date).'</td>';
	      print '<td align="right">'.$coms[$i]->total_ttc.'</td>';
	      print "</tr>\n";
	    }
	  print '</table>';
	}
    }

  print '</td><td valign="top" width="50%">';

  /*
   * Liste des actions propres � la propal
   */
  $sql = 'SELECT id, '.$db->pdate('a.datea'). ' as da, label, note, fk_user_author' ;
  $sql .= ' FROM '.MAIN_DB_PREFIX.'actioncomm as a';
  $sql .= ' WHERE a.propalrowid = '.$propal->id ;
  if ($socidp) $sql .= ' AND a.fk_soc = '.$socidp;
  $resql = $db->query($sql);
  if ($resql)
    {
      $num = $db->num_rows($resql);
      if ($num)
	{
	  print_titre($langs->trans('ActionsOnPropal'));
	  $i = 0;
	  $total = 0;
	  $var=true;

	  print '<table class="border" width="100%">';
	  print '<tr '.$bc[$var].'><td>'.$langs->trans('Ref').'</td><td>'.$langs->trans('Date').'</td><td>'.$langs->trans('Action').'</td><td>'.$langs->trans('By').'</td></tr>';
	  print "\n";

	  while ($i < $num)
	    {
	      $objp = $db->fetch_object($resql);
	      $var=!$var;
	      print '<tr '.$bc[$var].'>';
	      print '<td><a href="'.DOL_URL_ROOT.'/comm/action/fiche.php?id='.$objp->id.'">'.img_object($langs->trans('ShowTask'),'task').' '.$objp->id.'</a></td>';
	      print '<td>'.dolibarr_print_date($objp->da)."</td>\n";
	      print '<td>'.stripslashes($objp->label).'</td>';
	      $authoract = new User($db);
	      $authoract->id = $objp->fk_user_author;
	      $authoract->fetch('');
	      print '<td>'.$authoract->code.'</td>';
	      print "</tr>\n";
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


  /*
   * Action presend
   *
   */
  if ($_GET['action'] == 'presend')
    {
      print '<br>';
      print_titre($langs->trans('SendPropalByMail'));

      $liste[0]="&nbsp;";
      foreach ($societe->contact_email_array() as $key=>$value)
	{
	  $liste[$key]=$value;
	}

      // Cr�� l'objet formulaire mail
      include_once('../html.formmail.class.php');
      $formmail = new FormMail($db);
      $formmail->fromname = $user->fullname;
      $formmail->frommail = $user->email;
      $formmail->withfrom=1;
      $formmail->withto=$liste;
      $formmail->withcc=1;
      $formmail->withtopic=$langs->trans('SendPropalRef','__PROPREF__');
      $formmail->withfile=1;
      $formmail->withbody=1;
      // Tableau des substitutions
      $formmail->substit['__PROPREF__']=$propal->ref;
      // Tableau des param�tres compl�mentaires
      $formmail->param['action']='send';
      $formmail->param['models']='propal_send';
      $formmail->param['propalid']=$propal->id;
      $formmail->param['returnurl']=DOL_URL_ROOT.'/comm/propal.php?propalid='.$propal->id;

      $formmail->show_form();
    }

}
else
{
  /****************************************************************************
   *                                                                          *
   *                         Mode Liste des propales                          *
   *                                                                          *
   ****************************************************************************/

  $sortorder=$_GET['sortorder'];
  $sortfield=$_GET['sortfield'];
  $page=$_GET['page'];
  $viewstatut=$_GET['viewstatut'];

  if (! $sortfield) $sortfield='p.datep';
  if (! $sortorder) $sortorder='DESC';
  $limit = $conf->liste_limit;
  $offset = $limit * $page ;
  $pageprev = $page - 1;
  $pagenext = $page + 1;

  $sql = 'SELECT s.nom, s.idp, s.client, p.rowid as propalid, p.price, p.ref, p.fk_statut, '.$db->pdate('p.datep').' as dp,'.$db->pdate('p.fin_validite').' as dfv';
  if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", sc.fk_soc, sc.fk_user";
  $sql.= ' FROM '.MAIN_DB_PREFIX.'societe as s, '.MAIN_DB_PREFIX.'propal as p';
  if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
  if ($sall) $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'propaldet as pd ON p.rowid=pd.fk_propal';
  $sql.= ' WHERE p.fk_soc = s.idp';
  
  if (!$user->rights->commercial->client->voir && !$socidp) //restriction
    {
	    $sql .= " AND s.idp = sc.fk_soc AND sc.fk_user = " .$user->id;
    }
  if (!empty($_GET['search_ref']))
    {
      $sql .= " AND p.ref LIKE '%".addslashes($_GET['search_ref'])."%'";
    }
  if (!empty($_GET['search_societe']))
    {
      $sql .= " AND s.nom LIKE '%".addslashes($_GET['search_societe'])."%'";
    }
  if (!empty($_GET['search_montant_ht']))
    {
      $sql .= " AND p.price='".addslashes($_GET['search_montant_ht'])."'";
    }
  if ($sall) $sql.= " AND (s.nom like '%".addslashes($sall)."%' OR p.note like '%".addslashes($sall)."%' OR pd.description like '%".addslashes($sall)."%')";
  if ($socidp) $sql .= ' AND s.idp = '.$socidp; 
  if ($_GET['viewstatut'] <> '')
    {
      $sql .= ' AND p.fk_statut in ('.$_GET['viewstatut'].')'; 
    }
  if ($month > 0)
    {
      $sql .= " AND date_format(p.datep, '%Y-%m') = '$year-$month'";
    }
  if ($year > 0)
    {
      $sql .= " AND date_format(p.datep, '%Y') = $year";
    }
  if (strlen($_POST['sf_ref']) > 0)
    {
      $sql .= " AND p.ref like '%".addslashes($_POST["sf_ref"]) . "%'";
    }
  $sql .= ' ORDER BY '.$sortfield.' '.$sortorder.', p.ref DESC';
  $sql .= $db->plimit($limit + 1,$offset);
  $result=$db->query($sql);

  if ($result)
    {
      $num = $db->num_rows($result);
      print_barre_liste($langs->trans('ListOfProposals'), $page,'propal.php','&amp;socidp='.$socidp,$sortfield,$sortorder,'',$num);
      $i = 0;
      print '<table class="liste" width="100%">';
      print '<tr class="liste_titre">';
      print_liste_field_titre($langs->trans('Ref'),$_SERVER["PHP_SELF"],'p.ref','','&amp;socidp='.$socidp.'&amp;viewstatut='.$viewstatut,'',$sortfield);
      print_liste_field_titre($langs->trans('Company'),$_SERVER["PHP_SELF"],'s.nom','','&amp;socidp='.$socidp.'&amp;viewstatut='.$viewstatut,'',$sortfield);
      print_liste_field_titre($langs->trans('Date'),$_SERVER["PHP_SELF"],'p.datep','','&amp;socidp='.$socidp.'&amp;viewstatut='.$viewstatut, 'align="center"',$sortfield);
      print_liste_field_titre($langs->trans('DateEndPropalShort'),$_SERVER["PHP_SELF"],'dfv','','&amp;socidp='.$socidp.'&amp;viewstatut='.$viewstatut, 'align="center"',$sortfield);
      print_liste_field_titre($langs->trans('Price'),$_SERVER["PHP_SELF"],'p.price','','&amp;socidp='.$socidp.'&amp;viewstatut='.$viewstatut, 'align="right"',$sortfield);
      print_liste_field_titre($langs->trans('Status'),$_SERVER["PHP_SELF"],'p.fk_statut','','&amp;socidp='.$socidp.'&amp;viewstatut='.$viewstatut,'align="right"',$sortfield);
      print "</tr>\n";
      // Lignes des champs de filtre
      print '<form method="get" action="'.$_SERVER["PHP_SELF"].'">';

      print '<tr class="liste_titre">';
      print '<td class="liste_titre" valign="right">';
      print '<input class="flat" size="10" type="text" name="search_ref" value="'.$_GET['search_ref'].'">';
      print '</td>';
      print '<td class="liste_titre" align="left">';
      print '<input class="flat" type="text" size="40" name="search_societe" value="'.$_GET['search_societe'].'">';
      print '</td>';
      print '<td class="liste_titre" colspan="2">&nbsp;</td>';
      print '<td class="liste_titre" align="right">';
      print '<input class="flat" type="text" size="10" name="search_montant_ht" value="'.$_GET['search_montant_ht'].'">';
      print '</td>';
      print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" alt="'.$langs->trans("Search").'">';
      print '</td>';
      print "</tr>\n";
      print '</form>';

      $var=true;

      while ($i < min($num,$limit))
        {
            $objp = $db->fetch_object($result);
            $now = time();
            $var=!$var;
            print '<tr '.$bc[$var].'>';
            print '<td><a href="'.$_SERVER["PHP_SELF"].'?propalid='.$objp->propalid.'">'.img_object($langs->trans('ShowPropal'),'propal').' '.$objp->ref."</a></td>\n";
        
            if ($objp->client == 1)
            {
                $url = DOL_URL_ROOT.'/comm/fiche.php?socid='.$objp->idp;
            }
            else
            {
                $url = DOL_URL_ROOT.'/comm/prospect/fiche.php?socid='.$objp->idp;
            }
            print '<td><a href="'.$url.'">'.img_object($langs->trans('ShowCompany'),'company').' '.$objp->nom.'</a></td>';
        
            // Date propale
            print '<td align="center">';
            $y = strftime('%Y',$objp->dp);
            $m = strftime('%m',$objp->dp);
        
            print strftime('%d',$objp->dp)."\n";
            print ' <a href="'.$_SERVER["PHP_SELF"].'?year='.$y.'&amp;month='.$m.'">';
            print dolibarr_print_date($objp->dp,'%b')."</a>\n";
            print ' <a href="'.$_SERVER["PHP_SELF"].'?year='.$y.'">';
            print strftime('%Y',$objp->dp)."</a></td>\n";
        
            // Date fin validite
            if ($objp->dfv)
            {
                print '<td align="center">'.dolibarr_print_date($objp->dfv);
                if ($objp->fk_statut == 1 && $objp->dfv < (time() - $conf->propal->cloture->warning_delay)) print img_warning($langs->trans("Late"));
                print '</td>';
            }
            else
            {
                print '<td>&nbsp;</td>';
            }
        
            print '<td align="right">'.price($objp->price)."</td>\n";
            $propal=New Propal($db);
            print '<td align="right">'.$propal->LibStatut($objp->fk_statut,5)."</td>\n";
            print "</tr>\n";
        
            $total = $total + $objp->price;
            $subtotal = $subtotal + $objp->price;
        
            $i++;
        }
      print '</table>';
      $db->free($result);
    }
  else
    {
      dolibarr_print_error($db);
    }
}
$db->close();

llxFooter('$Date$ - $Revision$');

?>
