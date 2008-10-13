<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 */

/**
	    \file       htdocs/compta/tva/fiche.php
        \ingroup    tax
		\brief      Page des r�glements de TVA
		\version    $Id$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/compta/tva/tva.class.php");
require_once(DOL_DOCUMENT_ROOT."/compta/bank/account.class.php");

$langs->load("compta");
$langs->load("banks");

$id=$_REQUEST["id"];

$mesg = '';

// Security check
$socid = isset($_GET["socid"])?$_GET["socid"]:'';
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'tax', '', '', 'charges');


/**
 * Action ajout paiement tva
 */
if ($_POST["action"] == 'add' && $_POST["cancel"] <> $langs->trans("Cancel"))
{
    $tva = new Tva($db);
    
    $db->begin();
    
    $tva->accountid=$_POST["accountid"];
    $tva->paymenttype=$_POST["paiementtype"];
    $tva->datev=dolibarr_mktime(12,0,0, $_POST["datevmonth"], $_POST["datevday"], $_POST["datevyear"]);
    $tva->datep=dolibarr_mktime(12,0,0, $_POST["datepmonth"], $_POST["datepday"], $_POST["datepyear"]);
    $tva->amount=$_POST["amount"];
	$tva->label=$_POST["label"];

    $ret=$tva->addPayment($user);
    if ($ret > 0)
    {
        $db->commit();
        Header ("Location: reglement.php");
        exit;
    }
    else
    {
        $db->rollback();
        $mesg='<div class="error">'.$tva->error.'</div>';
        $_GET["action"]="create";
    }
}

if ($_GET["action"] == 'delete')
{
    $tva = new Tva($db);
    $result=$tva->fetch($_GET['id']);
	
	if ($tva->rappro == 0)
	{
	    $db->begin();
	    
	    $ret=$tva->delete($user);
	    if ($ret > 0)
	    {
			if ($tva->fk_bank)
			{
				$accountline=new AccountLine($db);
				$result=$accountline->fetch($vatpayment->fk_bank);
				$result=$accountline->delete($user);
			}
			
			if ($result > 0)
			{
				$db->commit();
				header("Location: ".DOL_URL_ROOT.'/compta/tva/reglement.php');
				exit;
			}
			else
			{
				$tva->error=$accountline->error;
				$db->rollback();
				$mesg='<div class="error">'.$tva->error.'</div>';
			}
	    }
	    else
	    {
	        $db->rollback();
	        $mesg='<div class="error">'.$tva->error.'</div>';
	    }
	}
	else
	{
        $mesg='<div class="error">Error try do delete a line linked to a conciliated bank transaction</div>';
	}
}


/*
*	View
*/

llxHeader();

$html = new Form($db);

if ($id)
{
    $vatpayment = new Tva($db);
	$result = $vatpayment->fetch($id);
	if ($result <= 0)
	{
		dolibarr_print_error($db);
		exit;
	}
}

// Formulaire saisie tva
if ($_GET["action"] == 'create')
{
    print "<form name='add' action=\"fiche.php\" method=\"post\">\n";
    print '<input type="hidden" name="action" value="add">';
    
    print_fiche_titre($langs->trans("NewVATPayment"));
      
    if ($mesg) print $mesg;
    
    print '<table class="border" width="100%">';
    
    print "<tr>";
    print '<td>'.$langs->trans("DatePayment").'</td><td>';
    print $html->select_date("","datev",'','','','add');
    print '</td></tr>';

    print '<tr><td>'.$langs->trans("DateValue").'</td><td>';
    print $html->select_date("","datep",'','','','add');
    print '</td></tr>';

	// Label
	print '<tr><td>'.$langs->trans("Label").'</td><td><input name="label" size="40" value="'.$langs->trans("VATPayment").'"></td></tr>';    

	// Amount
	print '<tr><td>'.$langs->trans("Amount").'</td><td><input name="amount" size="10" value=""></td></tr>';    

    if ($conf->banque->enabled)
    {
		print '<tr><td>'.$langs->trans("Account").'</td><td>';
        $html->select_comptes($vatpayment->fk_account,"accountid",0,"courant=1",1);  // Affiche liste des comptes courant
        print '</td></tr>';

	    print '<tr><td>'.$langs->trans("PaymentMode").'</td><td>';
	    $html->select_types_paiements($vatpayment->fk_type, "paiementtype");
	    print "</td>\n";
	}
        
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="'.$langs->trans("Save").'"> &nbsp; ';
    print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></td></tr>';
    print '</table>';
    print '</form>';      
}


/* ************************************************************************** */
/*                                                                            */ 
/* Barre d'action                                                             */ 
/*                                                                            */ 
/* ************************************************************************** */

if ($id)
{
    if ($mesg) print $mesg;

	$h = 0;
	$head[$h][0] = DOL_URL_ROOT.'/compta/tva/fiche.php?id='.$vatpayment->id;
	$head[$h][1] = $langs->trans('Card');
	$head[$h][2] = 'card';
	$h++;

	dolibarr_fiche_head($head, 'card', $langs->trans("VATPayment"));


	print '<table class="border" width="100%">';
	
	print "<tr>";
	print '<td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
	print $vatpayment->ref;
	print '</td></tr>';

	print "<tr>";
	print '<td>'.$langs->trans("DatePayment").'</td><td colspan="3">';
	print dolibarr_print_date($vatpayment->datep,'day');
	print '</td></tr>';

	print '<tr><td>'.$langs->trans("DateValue").'</td><td colspan="3">';
	print dolibarr_print_date($vatpayment->datev,'day');
	print '</td></tr>';

	if ($conf->banque->enabled)
	{
		print '<tr><td>'.$langs->trans("Account").'</td>';
		if ($vatpayment->fk_account > 0)
		{
			$account=new Account($db);
			$result=$account->fetch($vatpayment->fk_account);
			print '<td>'.$account->getNomUrl(1).'</td>';
			print '<td width="25%">'.$langs->trans("BankLineConciliated").'</td><td width="25%">'.yn($vatpayment->rappro).'</td>';
		}
		else
		{
			print '<td colspan="3">&nbsp;</td>';
		}
		print '</tr>';

		print '<tr><td>'.$langs->trans("PaymentMode").'</td><td colspan="3">';
		print $vatpayment->fk_type ? $langs->trans("PaymentTypeShort".$vatpayment->fk_type) : '&nbsp;';
		print "</td>\n";
	}

	print '<tr><td>'.$langs->trans("Amount").'</td><td colspan="3">'.price($vatpayment->amount).'</td></tr>';
	
	print '</table>';
	
	print '</div>';
	
	/*
	* Boutons d'actions
	*/
	print "<div class=\"tabsAction\">\n";
	if ($vatpayment->rappro == 0)
		print '<a class="butActionDelete" href="fiche.php?id='.$vatpayment->id.'&action=delete">'.$langs->trans("Delete").'</a>';
	else
		print '<a class="butActionRefused" href="#" title="'.$langs->trans("LinkedToAConcialitedTransaction").'">'.$langs->trans("Delete").'</a>';
	print "</div>";
}


$db->close();

llxFooter('$Date$ - $Revision$');

?>
