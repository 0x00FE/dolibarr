<?php
/* Copyright (C) 2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/adherents/fiche_subscription.php
        \ingroup    adherent
        \brief      Page d'ajout, edition, suppression d'une fiche adh�sion
        \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/member.lib.php");
require_once(DOL_DOCUMENT_ROOT."/adherents/adherent.class.php");
require_once(DOL_DOCUMENT_ROOT."/adherents/cotisation.class.php");
require_once(DOL_DOCUMENT_ROOT."/compta/bank/account.class.php");

$langs->load("companies");
$langs->load("bills");
$langs->load("members");
$langs->load("users");

$user->getrights('adherent');

$adh = new Adherent($db);
$subscription = new Cotisation($db);
$errmsg='';

$action=isset($_GET["action"])?$_GET["action"]:$_POST["action"];
$rowid=isset($_GET["rowid"])?$_GET["rowid"]:$_POST["rowid"];
$typeid=isset($_GET["typeid"])?$_GET["typeid"]:$_POST["typeid"];

if (! $user->rights->adherent->cotisation->lire)
	 accessforbidden();


/*
 * 	Actions
 */

if ($user->rights->adherent->cotisation->creer && $_REQUEST["action"] == 'update' && ! $_POST["cancel"])
{
	// Charge objet actuel
	$result=$subscription->fetch($_POST["rowid"]);
	if ($result > 0)
	{
		$db->begin();
		
		$errmsg='';
		
		if ($subscription->fk_bank)
		{
			$accountline=new AccountLine($db);
			$result=$accountline->fetch($subscription->fk_bank);

			// If transaction consolidated
			if ($accountline->rappro)
			{
				$errmsg=$langs->trans("SubscriptionLinkedToConciliatedTrnasaction");
			}
			else
			{
				$accountline->datev=dolibarr_mktime($_POST['datesubhour'], $_POST['datesubmin'], 0, $_POST['datesubmonth'], $_POST['datesubday'], $_POST['datesubyear']);
				$accountline->dateo=dolibarr_mktime($_POST['datesubhour'], $_POST['datesubmin'], 0, $_POST['datesubmonth'], $_POST['datesubday'], $_POST['datesubyear']);
				$accountline->amount=$_POST["amount"];
				$result=$accountline->update($user);
				if ($result < 0)
				{
					$errmsg=$accountline->error;
				}
			}
		}
		
		if (! $errmsg)
		{
			// Modifie valeures
			$subscription->dateh=dolibarr_mktime($_POST['datesubhour'], $_POST['datesubmin'], 0, $_POST['datesubmonth'], $_POST['datesubday'], $_POST['datesubyear']);
			$subscription->amount=$_POST["amount"];

			$result=$subscription->update($user);
			if ($result >= 0 && ! sizeof($subscription->errors))
			{
				$db->commit();

				header("Location: fiche_subscription.php?rowid=".$subscription->id);
				exit;
			}
			else
			{
				$db->rollback();

			    if ($subscription->error)
				{
					$errmsg=$subscription->error;
				}
				else
				{
					foreach($subscription->errors as $error)
					{
						if ($errmsg) $errmsg.='<br>';
						$errmsg.=$error;
					}
				}
				$action='';
			}
		}
		else
		{
			$db->rollback();
		}
	}
}

if ($user->rights->adherent->cotisation->creer && $_POST["action"] == 'confirm_delete' && $_POST["confirm"] == 'yes')
{
	$result=$subscription->fetch($rowid);
    $result=$subscription->delete($rowid);
    if ($result > 0)
    {
    	Header("Location: card_subscriptions.php?rowid=".$subscription->fk_adherent);
    	exit;
    }
    else
    {
    	$mesg=$adh->error;
    }
}



/*
 * 
 */

llxHeader();


if ($errmsg)
{
    print '<div class="error">'.$errmsg.'</div>';
    print "\n";
}


if ($user->rights->adherent->cotisation->creer && $action == 'edit')
{
	/********************************************
	 *
	 * Fiche en mode edition
	 *
	 ********************************************/

    $subscription->fetch($rowid);
	 
	/*
	 * Affichage onglets
	 */
	$h = 0;
	$head = array();
	
	$head[$h][0] = DOL_URL_ROOT.'/adherents/fiche_subscription.php?rowid='.$subscription->id;
	$head[$h][1] = $langs->trans("SubscriptionCard");
	$head[$h][2] = 'general';
	$h++;

	dolibarr_fiche_head($head, 'general', $langs->trans("Subscription"));

	print "\n";
	print '<form name="update" action="'.$_SERVER["PHP_SELF"].'" method="post">';
	print "<input type=\"hidden\" name=\"action\" value=\"update\">";
	print "<input type=\"hidden\" name=\"rowid\" value=\"$rowid\">";
	print "<input type=\"hidden\" name=\"fk_bank\" value=\"".$subscription->fk_bank."\">";
	print '<table class="border" width="100%">';
	
	$htmls = new Form($db);

    // Ref
    print '<tr><td width="20%">'.$langs->trans("Ref").'</td><td class="valeur" colspan="2">'.$subscription->ref.'&nbsp;</td></tr>';
	
    // Date start subscription
    print '<tr><td>'.$langs->trans("DateSubscription").'</td><td class="valeur" colspan="2">';
	$htmls->select_date($subscription->dateh,'datesub',1,1,0,'update',1);
	print '</td>';
    print '</tr>';

    // Date end subscription
    print '<tr><td>'.$langs->trans("DateEndSubscription").'</td><td class="valeur" colspan="2">';
	$htmls->select_date($subscription->datef,'datesubend',0,0,0,'update',1);
	print '</td>';
    print '</tr>';

    // Amount
    print '<tr><td>'.$langs->trans("Amount").'</td><td class="valeur" colspan="2">';
	print '<input type="text" class="flat" size="10" name="amount" value="'.price($subscription->amount).'"></td></tr>';

	print '<tr><td colspan="3" align="center">';
	print '<input type="submit" class="button" name="submit" value="'.$langs->trans("Save").'">';
	print ' &nbsp; &nbsp; &nbsp; ';
	print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</td></tr>';

	print '</table>';
	print '</form>';
	print "\n";
	
	print '</div>'; 
	print "\n";
}

if ($rowid && $action != 'edit')
{
	/* ************************************************************************** */
	/*                                                                            */
	/* Mode affichage                                                             */
	/*                                                                            */
	/* ************************************************************************** */

    $result=$subscription->fetch($rowid);
	$result=$adh->fetch($subscription->fk_adherent);
	
    $html = new Form($db);

	/*
	 * Affichage onglets
	 */
	$h = 0;
	$head = array();
	
	$head[$h][0] = DOL_URL_ROOT.'/adherents/fiche_subscription.php?rowid='.$subscription->id;
	$head[$h][1] = $langs->trans("SubscriptionCard");
	$head[$h][2] = 'general';
	$h++;

	dolibarr_fiche_head($head, 'general', $langs->trans("Subscription"));

	if ($msg) print '<div class="error">'.$msg.'</div>';

	//$result=$subscription->load_previous_next_id($adh->next_prev_filter);
	//if ($result < 0) dolibarr_print_error($db,$subscription->error);
	//$previous_id = $adh->id_previous?'<a href="'.$_SERVER["PHP_SELF"].'?rowid='.urlencode($adh->id_previous).'">'.img_previous().'</a>':'';
	//$next_id     = $adh->id_next?'<a href="'.$_SERVER["PHP_SELF"].'?rowid='.urlencode($adh->id_next).'">'.img_next().'</a>':'';

    // Confirmation de la suppression de l'adh�rent
    if ($action == 'delete')
    {
		//$formquestion=array();
        //$formquestion['text']='<b>'.$langs->trans("ThisWillAlsoDeleteBankRecord").'</b>';
		$text=$langs->trans("ConfirmDeleteSubscription");
		if ($conf->banque->enabled && $conf->global->ADHERENT_BANK_USE) $text.='<br>'.img_warning().' '.$langs->trans("ThisWillAlsoDeleteBankRecord");
		$html->form_confirm($_SERVER["PHP_SELF"]."?rowid=".$subscription->id,$langs->trans("DeleteSubscription"),$text,"confirm_delete",$formquestion);
        print '<br>';
    }

    print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
    print '<table class="border" width="100%">';

    // Ref
    print '<tr><td width="20%">'.$langs->trans("Ref").'</td>';
	print '<td class="valeur" colspan="2">';
	if ($previous_id || $next_id) print '<table class="nobordernopadding" width="100%"><tr class="nobordernopadding"><td class="nobordernopadding">';
	print $subscription->ref;
	if ($previous_id || $next_id) print '</td><td class="nobordernopadding" align="center" width="20">'.$previous_id.'</td><td class="nobordernopadding" align="center" width="20">'.$next_id.'</td></tr></table>';
	print '</td></tr>';

    // Member
	$adh->ref=$adh->fullname;
    print '<tr>';
	print '<td>'.$langs->trans("Member").'</td><td class="valeur">'.$adh->getNomUrl(1,0,'subscription').'</td>';
    print '</tr>';

    // Date subscription
    print '<tr>';
	print '<td>'.$langs->trans("DateSubscription").'</td><td class="valeur">'.dolibarr_print_date($subscription->dateh,'dayhour').'</td>';
    print '</tr>';

    // Date end subscription
    print '<tr>';
	print '<td>'.$langs->trans("DateEndSubscription").'</td><td class="valeur">'.dolibarr_print_date($subscription->datef,'day').'</td>';
    print '</tr>';

    // Amount
    print '<tr><td>'.$langs->trans("Amount").'</td><td class="valeur">'.price($subscription->amount).'</td></tr>';

    print "</table>\n";
    print '</form>';
    
    print "</div>\n";

    
    /*
     * Barre d'actions
     *
     */
    print '<div class="tabsAction">';
    
    if ($user->rights->adherent->cotisation->creer)
	{
		print "<a class=\"butAction\" href=\"".$_SERVER["PHP_SELF"]."?rowid=".$subscription->id."&action=edit\">".$langs->trans("Edit")."</a>";
    }
	
    // Supprimer
    if ($user->rights->adherent->cotisation->creer)
    {
        print "<a class=\"butActionDelete\" href=\"".$_SERVER["PHP_SELF"]."?rowid=".$subscription->id."&action=delete\">".$langs->trans("Delete")."</a>\n";
    }
        
    print '</div>';
    print "<br>\n";
    
}


$db->close();

llxFooter('$Date$ - $Revision$');
?>
