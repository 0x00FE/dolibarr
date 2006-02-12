<?php
/* Copyright (C) 2001-2003,2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005      Destailleur Laurent  <eldy@users.sourceforge.net>
 * Copyright (C) 2004           Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005           Regis Houssin        <regis.houssin@cap-networks.com>
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
        \file       htdocs/compta/propal.php
        \ingroup    propale
        \brief      Page liste des propales (vision compta)
*/

require("./pre.inc.php");

$user->getrights('facture');
$user->getrights('propale');
if (!$user->rights->propale->lire)
  accessforbidden();

$langs->load('compta');

if ($conf->projet->enabled)   require_once(DOL_DOCUMENT_ROOT.'/project.class.php');
if ($conf->commande->enabled) require_once(DOL_DOCUMENT_ROOT.'/commande/commande.class.php');
require_once(DOL_DOCUMENT_ROOT."/propal.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/CMailFile.class.php");

// S�curit� acc�s client
if ($user->societe_id > 0)
{
  $action = '';
  $socidp = $user->societe_id;
}

if ($_GET["action"] == 'setstatut')
{
  /*
   *  Class�e la facture comme factur�e
   */
  $propal = new Propal($db);
  $propal->id = $_GET["propalid"];
  $propal->cloture($user, $_GET["statut"], $note);

}

if ( $action == 'delete' )
{
  $sql = "DELETE FROM ".MAIN_DB_PREFIX."propal WHERE rowid = $propalid;";
  if ( $db->query($sql) )
    {
      
      $sql = "DELETE FROM ".MAIN_DB_PREFIX."propaldet WHERE fk_propal = $propalid ;";
      if ( $db->query($sql) )
        {
	  print '<div class="ok">'.$langs->trans("Deleted").'</div>';
        }
      else
        {
	  dolibarr_print_error($db);
        }
    }
  else
    {
      dolibarr_print_error($db);
    }
  $propalid = 0;
  $brouillon = 1;
}


llxHeader();

$html = new Form($db);

/*
 *
 * Mode fiche
 *
 */
if ($_GET["propalid"] > 0)
{
  	if ($msg) print "$msg<br>";

    $propal = new Propal($db);
    $propal->fetch($_GET["propalid"]);
    $h=0;
    
    $head[$h][0] = DOL_URL_ROOT.'/comm/propal.php?propalid='.$propal->id;
    $head[$h][1] = $langs->trans('CommercialCard');
    $h++;
    
    $head[$h][0] = DOL_URL_ROOT.'/compta/propal.php?propalid='.$propal->id;
    $head[$h][1] = $langs->trans('AccountancyCard');
    $hselected=$h;
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
    
    dolibarr_fiche_head($head, $hselected, $langs->trans('Proposal').': '.$propal->ref);
    
    
    /*
    * Fiche propal
    *
    */
    $sql = 'SELECT s.nom, s.idp, p.price, p.fk_projet, p.remise, p.tva, p.total, p.ref,'.$db->pdate('p.datep').' as dp, c.id as statut, c.label as lst, p.note,';
    $sql.= ' x.firstname, x.name, x.fax, x.phone, x.email, p.fk_user_author, p.fk_user_valid, p.fk_user_cloture, p.datec, p.date_valid, p.date_cloture';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'societe as s, '.MAIN_DB_PREFIX.'propal as p, '.MAIN_DB_PREFIX.'c_propalst as c, '.MAIN_DB_PREFIX.'socpeople as x';
    $sql.= ' WHERE p.fk_soc = s.idp AND p.fk_statut = c.id AND x.idp = p.fk_soc_contact AND p.rowid = '.$propal->id;
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
            $rowspan=6;
            
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
    
            print '<td>'.$langs->trans('DateEndPropal').'</td><td>';
            if ($propal->fin_validite)
            {
                print dolibarr_print_date($propal->fin_validite,'%a %d %B %Y');
                if ($propal->statut == 1 && $propal->fin_validite < (time() - $conf->propal->cloture->warning_delay)) print img_warning($langs->trans("Late"));
            }
            else
            {
                print $langs->trans("Unknown");
            }
            print '</td>';
            print '</tr>';
    
			// Conditions et modes de r�glement
			print '<tr><td>';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('PaymentConditions');
			print '</td>';
			if ($_GET['action'] != 'editconditions' && $prop->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editconditions&amp;facid='.$prop->id.'">'.img_edit($langs->trans('SetConditions'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td colspan="3">';
			if ($_GET['action'] == 'editconditions')
			{
				$html->form_conditions_reglement($_SERVER['PHP_SELF'].'?facid='.$prop->id,$prop->cond_reglement_id,'cond_reglement_id');
			}
			else
			{
				$html->form_conditions_reglement($_SERVER['PHP_SELF'].'?facid='.$prop->id,$prop->cond_reglement_id,'none');
			}
			print '</td>';
			print '<td width="25%">';
			print '<table class="nobordernopadding" width="100%"><tr><td>';
			print $langs->trans('PaymentMode');
			print '</td>';
			if ($_GET['action'] != 'editmode' && $prop->brouillon) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editmode&amp;facid='.$prop->id.'">'.img_edit($langs->trans('SetMode'),1).'</a></td>';
			print '</tr></table>';
			print '</td><td width="25%">';
			if ($_GET['action'] == 'editmode')
			{
				$html->form_modes_reglement($_SERVER['PHP_SELF'].'?facid='.$prop->id,$prop->mode_reglement_id,'mode_reglement_id');
			}
			else
			{
				$html->form_modes_reglement($_SERVER['PHP_SELF'].'?facid='.$prop->id,$prop->mode_reglement_id,'none');
			}
			print '</td></tr>';

            // Destinataire
            $langs->load('mails');
            print '<tr>';
            print '<td>'.$langs->trans('MailTo').'</td>';
    
            $dests=$societe->contact_array($societe->id);
            $numdest = count($dests);
            if ($numdest==0)
            {
                print '<td colspan="3">';
                print '<font class="error">Cette societe n\'a pas de contact, veuillez en cr�er un avant de faire votre proposition commerciale</font><br>';
                print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?socid='.$societe->id.'&amp;action=create&amp;backtoreferer=1">'.$langs->trans('AddContact').'</a>';
                print '</td>';
            }
            else
            {
                if ($propal->statut == 0 && $user->rights->propale->creer)
                {
                    print '<td colspan="2">';
                    print '<form action="propal.php?propalid='.$propal->id.'" method="post">';
                    print '<input type="hidden" name="action" value="set_contact">';
                    $html->select_contacts($societe->id, $propal->contactid, 'contactidp');
                    print '</td><td>';
                    print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
                    print '</form>';
                    print '</td>';
                }
                else
                {
                    if (!empty($propal->contactid))
                    {
                        print '<td colspan="3">';
                        require_once(DOL_DOCUMENT_ROOT.'/contact.class.php');
                        $contact=new Contact($db);
                        $contact->fetch($propal->contactid);
                        print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$propal->contactid.'" title="'.$langs->trans('ShowContact').'">';
                        print $contact->firstname.' '.$contact->name;
                        print '</a>';
                        print '</td>';
                    }
                    else {
                        print '<td colspan="3">&nbsp;</td>';
                    }
                }
            }
            print '</td>';
    
            if ($conf->projet->enabled)
            $rowspan++;
    
            print '<td valign="top" colspan="2" width="50%" rowspan="'.$rowspan.'">'.$langs->trans('Note').' :<br>'. nl2br($propal->note).'</td></tr>';
    
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
                        print '<form action="propal.php?propalid='.$propal->id.'" method="post">';
                        print '<input type="hidden" name="action" value="set_project">';
                        $html->select_projects($societe->id, $propal->projetidp, 'projetidp');
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
    
            print '<tr><td height="10" nowrap>'.$langs->trans('GlobalDiscount').'</td>';
            if ($propal->brouillon == 1 && $user->rights->propale->creer)
            {
                print '<form action="propal.php?propalid='.$propal->id.'" method="post">';
                print '<input type="hidden" name="action" value="setremise">';
                print '<td colspan="2"><input type="text" name="remise" size="3" value="'.$propal->remise_percent.'">% ';
                print '</td><td>';
                print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
                print ' <a href="propal/aideremise.php?propalid='.$propal->id.'">?</a>';
                print '</td>';
                print '</form>';
            }
            else
            {
                print '<td colspan="3">'.$propal->remise_percent.'%</td>';
            }
            print '</tr>';
    
            print '<tr><td height="10">'.$langs->trans('AmountHT').'</td>';
            print '<td align="right" colspan="2"><b>'.price($propal->price).'</b></td>';
            print '<td>'.$langs->trans("Currency".$conf->monnaie).'</td></tr>';
    
            print '<tr><td height="10">'.$langs->trans('AmountVAT').'</td><td align="right" colspan="2">'.price($propal->total_tva).'</td>';
            print '<td>'.$langs->trans("Currency".$conf->monnaie).'</td></tr>';
            print '<tr><td height="10">'.$langs->trans('AmountTTC').'</td><td align="right" colspan="2">'.price($propal->total_ttc).'</td>';
            print '<td>'.$langs->trans("Currency".$conf->monnaie).'</td></tr>';
    
            // Statut
            print '<tr><td height="10">'.$langs->trans('Status').'</td><td align="left" colspan="3">'.$propal->getLibStatut().'</td></tr>';
            print '</table><br>';
            if ($propal->brouillon == 1 && $user->rights->propale->creer)
            {
                print '</form>';
            }
    
            /*
            * Lignes de propale
            *
            */
            $sql = 'SELECT pt.rowid, pt.description, pt.price, pt.fk_product, pt.qty, pt.tva_tx, pt.remise_percent, pt.subprice, p.label as product, p.ref, p.fk_product_type, p.rowid as prodid';
            $sql .= ' FROM '.MAIN_DB_PREFIX.'propaldet as pt LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON pt.fk_product=p.rowid';
            $sql .= ' WHERE pt.fk_propal = '.$propal->id;
            $sql .= ' ORDER BY pt.rowid ASC';
            $resql = $db->query($sql);
            if ($resql)
            {
                $num_lignes = $db->num_rows($resql);
                $i = 0;
                $total = 0;
    
                print '<table class="noborder" width="100%">';
                if ($num_lignes)
                {
                    print '<tr class="liste_titre">';
                    print '<td>'.$langs->trans('Description').'</td>';
                    print '<td align="right" width="50">'.$langs->trans('VAT').'</td>';
                    print '<td align="right" width="80">'.$langs->trans('PriceUHT').'</td>';
                    print '<td align="right" width="50">'.$langs->trans('Qty').'</td>';
                    print '<td align="right" width="50">'.$langs->trans('Discount').'</td>';
                    print '<td align="right" width="50">'.$langs->trans('AmountHT').'</td>';
                    print '<td>&nbsp;</td><td>&nbsp;</td>';
                    print "</tr>\n";
                }
                $var=true;
                while ($i < $num_lignes)
                {
                    $objp = $db->fetch_object($resql);
                    $var=!$var;
                    if ($_GET['action'] != 'editline' || $_GET['rowid'] != $objp->rowid)
                    {
                        print '<tr '.$bc[$var].'>';
                        if ($objp->fk_product > 0)
                        {
                            print '<td><a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->fk_product.'">';
                            if ($objp->fk_product_type)
                            print img_object($langs->trans('ShowService'),'service');
                            else
                            print img_object($langs->trans('ShowProduct'),'product');
                            print ' '.$objp->ref.'</a> - '.stripslashes(nl2br($objp->product));
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
                            print $objp->description?'<br>'.stripslashes(nl2br($objp->description)):'';
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
    
                        print '<td>&nbsp;</td><td>&nbsp;</td>';
    
                        print '</tr>';
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
    
            print '</table>';
    
        }
    }
    else
    {
        dolibarr_print_error($db);
    }
    
    print '</div>';

            
  /*
   * Boutons Actions
   */
  if ($obj->statut <> 4 && $user->societe_id == 0)
    {
      print '<div class="tabsAction">';

      if ($obj->statut == 2 && $user->rights->facture->creer)
        {
	  print '<a class="butAction" href="facture.php?propalid='.$propal->id."&action=create\">".$langs->trans("BuildBill")."</a>";
        }

      if ($obj->statut == 2 && sizeof($propal->facture_liste_array()))
        {
	  print '<a class="butAction" href="propal.php?propalid='.$propal->id."&action=setstatut&statut=4\">".$langs->trans("ClassifyBilled")."</a>";
        }

      print "</div>";
    }



  print '<table width="100%"><tr><td width="50%" valign="top">';

    /*
     * Documents g�n�r�s
     */
    $filename=sanitize_string($propal->ref);
    $filedir=$conf->propal->dir_output . "/" . sanitize_string($propal->ref);
    $urlsource=$_SERVER["PHP_SELF"]."?propalid=".$propal->id;
    $genallowed=0;
    $delallowed=0;
    
    $var=true;
    
    $html->show_documents('propal',$filename,$filedir,$urlsource,$genallowed,$delallowed);
    

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


  /*
   * Factures associees
   */
  $sql = "SELECT f.facnumber, f.total,".$db->pdate("f.datef")." as df, f.rowid as facid, f.fk_user_author, f.paye";
  $sql .= " FROM ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."fa_pr as fp WHERE fp.fk_facture = f.rowid AND fp.fk_propal = ".$propal->id;

  $resql = $db->query($sql);
  if ($resql)
    {
      $num_fac_asso = $db->num_rows($resql);
      $i = 0; $total = 0;
      print "<br>";
      if ($num_fac_asso > 1) print_titre($langs->trans("RelatedBills"));
      else print_titre($langs->trans("RelatedBill"));
      print '<table class="noborder" width="100%">';
      print "<tr class=\"liste_titre\">";
      print '<td>'.$langs->trans("Ref").'</td>';
      print '<td align="center">'.$langs->trans("Date").'</td>';
      print '<td align="right">'.$langs->trans("Price").'</td>';
      print "</tr>\n";

      $var=True;
      while ($i < $num_fac_asso)
        {
	  $objp = $db->fetch_object();
	  $var=!$var;
	  print "<tr $bc[$var]>";
	  print '<td><a href="../compta/facture.php?facid='.$objp->facid.'">'.img_object($langs->trans("ShowBill"),"bill").' '.$objp->facnumber.'</a>';
	  if ($objp->paye)
            {
	      print " (<b>pay&eacute;e</b>)";
            }
	  print "</td>\n";
	  print '<td align="center">'.dolibarr_print_date($objp->df).'</td>';
	  print '<td align="right">'.price($objp->total).'</td>';
	  print "</tr>";
	  $total = $total + $objp->total;
	  $i++;
        }
      print "<tr class=\"liste_total\"><td align=\"right\" colspan=\"2\">".$langs->trans("TotalHT")."</td><td align=\"right\">".price($total)."</td></tr>\n";
      print "</table>";
      $db->free();
    }


  print '</td><td valign="top" width="50%">';


  /*
   * Liste des actions propres � la propal
   */
  $sql = 'SELECT id, '.$db->pdate('a.datea'). ' as da, label, note, fk_user_author' ;
  $sql .= ' FROM '.MAIN_DB_PREFIX.'actioncomm as a';
  $sql .= ' WHERE a.fk_soc = '.$obj->idp.' AND a.propalrowid = '.$propal->id ;
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
    
    
} else {

  /**
   *
   * Mode Liste des propales
   *
   */

  if (! $sortfield) $sortfield="p.datep";
  if (! $sortorder) $sortorder="DESC";
  if ($page == -1) $page = 0 ;

  $limit = $conf->liste_limit;
  $offset = $limit * $page ;
  $pageprev = $page - 1;
  $pagenext = $page + 1;


  $sql = "SELECT s.nom, s.idp, p.rowid as propalid, p.price, p.ref, p.fk_statut, ".$db->pdate("p.datep")." as dp";
  $sql.= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."propal as p ";
  $sql.= " WHERE p.fk_soc = s.idp";
  if ($socidp)           $sql .= " AND s.idp = $socidp";
  if ($viewstatut <> '') $sql .= " AND p.fk_statut in ($viewstatut)"; // viewstatut peut etre combinaisons s�par� par virgules
  if ($month > 0)        $sql .= " AND date_format(p.datep, '%Y-%m') = '$year-$month'";
  if ($year > 0)         $sql .= " AND date_format(p.datep, '%Y') = $year";
  $sql .= " ORDER BY $sortfield $sortorder, p.rowid DESC ";
  $sql .= $db->plimit($limit + 1,$offset);

  if ( $db->query($sql) )
    {
      $num = $db->num_rows();

      $propalstatic=new Propal($db);

      print_barre_liste($langs->trans("Proposals"), $page, "propal.php","&socidp=$socidp",$sortfield,$sortorder,'',$num);

      $i = 0;
      $var=true;
      
      print "<table class=\"noborder\" width=\"100%\">";
      print '<tr class="liste_titre">';
      print_liste_field_titre($langs->trans("Ref"),"propal.php","p.ref","","&year=$year&viewstatut=$viewstatut",'',$sortfield);
      print_liste_field_titre($langs->trans("Company"),"propal.php","s.nom","&viewstatut=$viewstatut","",'',$sortfield);
      print_liste_field_titre($langs->trans("Date"),"propal.php","p.datep","&viewstatut=$viewstatut","",'align="right" colspan="2"',$sortfield);
      print_liste_field_titre($langs->trans("Price"),"propal.php","p.price","&viewstatut=$viewstatut","",'align="right"',$sortfield);
      print_liste_field_titre($langs->trans("Status"),"propal.php","p.fk_statut","&viewstatut=$viewstatut","",'align="center"',$sortfield);
      print "</tr>\n";

      while ($i < min($num, $limit))
        {
	  $objp = $db->fetch_object();

	  $var=!$var;
	  print "<tr $bc[$var]>";

	  print '<td><a href="propal.php?propalid='.$objp->propalid.'">'.img_object($langs->trans("ShowPropal"),"propal").' ';
	  print $objp->ref."</a></td>\n";

	  print "<td><a href=\"fiche.php?socid=$objp->idp\">".dolibarr_trunc($objp->nom,44)."</a></td>\n";

	  $now = time();
	  $lim = 3600 * 24 * 15 ;

	  if ( ($now - $objp->dp) > $lim && $objp->statutid == 1 )
            {
	      print "<td><b> &gt; 15 jours</b></td>";
            }
	  else
            {
	      print "<td>&nbsp;</td>";
            }

	  print "<td align=\"right\">";
	  $y = strftime("%Y",$objp->dp);
	  $m = strftime("%m",$objp->dp);

	  print strftime("%d",$objp->dp)."\n";
	  print " <a href=\"propal.php?year=$y&month=$m\">";
	  print strftime("%B",$objp->dp)."</a>\n";
	  print " <a href=\"propal.php?year=$y\">";
	  print strftime("%Y",$objp->dp)."</a></td>\n";

	  print "<td align=\"right\">".price($objp->price)."</td>\n";
	  print "<td align=\"center\">".$propalstatic->LibStatut($objp->fk_statut,0)."</td>\n";
	  print "</tr>\n";

	  $i++;
        }

      print "</table>";
      $db->free();
    }
  else
    {
      dolibarr_print_error($db);
    }
}
$db->close();

llxFooter('$Date$ - $Revision$');
?>
