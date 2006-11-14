<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
		\file       htdocs/compta/bank/virement.php
		\ingroup    banque
		\brief      Page de saisie d'un virement
		\version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/bank.lib.php");

$langs->load("banks");

$user->getrights('banque');

if (!$user->rights->banque->modifier)
  accessforbidden();


/*
 * Action ajout d'un virement
 */
if ($_POST["action"] == 'add')
{
	$mesg='';
	$dateo = $_POST["reyear"]."-".$_POST["remonth"]."-".$_POST["reday"];
	$label = $_POST["label"];
	$amount= $_POST["amount"];

	if (! $label)
	{
		$error=1;	
		$mesg.="<div class=\"error\">".$langs->trans("ErrorFieldRequired",$langs->trans("Label"))."</div>";
	}
	if (! $amount)
	{
		$error=1;	
		$mesg.="<div class=\"error\">".$langs->trans("ErrorFieldRequired",$langs->trans("Amount"))."</div>";
	}
	if (! $error)
	{
		$db->begin();
		
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."bank (datec, datev, dateo, label, amount, fk_user_author,fk_account, fk_type)";
		$sql .= " VALUES (now(), '$dateo', '$dateo', '".addslashes($label)."', '".price2num(-$amount)."', $user->id, ".$_POST["account_from"].", 'VIR')";

		dolibarr_syslog("Virement insert bank sql=".$sql);
		$result = $db->query($sql);
		if (!$result)
		{
			$db->rollback();
			dolibarr_print_error($db);
		}

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."bank (datec, datev, dateo, label, amount, fk_user_author,fk_account, fk_type)";
		$sql .= " VALUES (now(), '$dateo', '$dateo', '".addslashes($label)."', '".price2num($amount)."',$user->id, ".$_POST["account_to"].", 'VIR')";

		dolibarr_syslog("Virement insert bank sql=".$sql);
		$result = $db->query($sql);
		if ($result)
		{
			$accountfrom=new Account($db);
			$accountfrom->fetch($_POST["account_from"]);
			$accountto=new Account($db);
			$accountto->fetch($_POST["account_to"]);

			$mesg.="<div class=\"ok\">Le virement depuis �&nbsp;<a href=\"account.php?account=".$accountfrom->id."\">".$accountfrom->label."</a>&nbsp;� vers �&nbsp;<a href=\"account.php?account=".$accountto->id."\">".$accountto->label."</a>&nbsp;� de ".$amount." ".$langs->trans("Currency".$conf->monnaie)." a �t� cr��.</div>";
			$db->commit();
		}
		else
		{
			$mesg.="<div class=\"error\">".$db->lasterror()."</div>";
			$db->rollback();
		}
	}
}



/*
 * Affichage
 */
 
llxHeader();

$html=new Form($db);


print_titre($langs->trans("BankTransfer"));
print '<br>';

if ($mesg) {
    print "$mesg<br>";
}

print $langs->trans("TransferDesc");
print "<br><br>";

print "<form name='add' method=\"post\" action=\"virement.php\">";

print '<input type="hidden" name="action" value="add">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("TransferFrom").'</td><td>'.$langs->trans("TransferTo").'</td><td>'.$langs->trans("Date").'</td><td>'.$langs->trans("Description").'</td><td>'.$langs->trans("Amount").'</td>';
print '</tr>';

$var=false;
print '<tr '.$bc[$var].'><td>';
print "<select class=\"flat\" name=\"account_from\">";
$sql = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."bank_account";
$sql.= " WHERE clos = 0";
$resql = $db->query($sql);
if ($resql)
{
  $var=True;  
  $num = $db->num_rows($resql);
  $i = 0;
  
  while ($i < $num)
    {
      $objp = $db->fetch_object($resql);
      print "<option value=\"$objp->rowid\">$objp->label</option>";
      $i++;
    }
  $db->free($resql);
}
print "</select></td><td>\n";

print '<select class="flat" name="account_to">';
$sql = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."bank_account";
$sql .= " WHERE clos = 0";
$resql = $db->query($sql);
if ($resql)
{
  $var=True;  
  $num = $db->num_rows();
  $i = 0;
  
  while ($i < $num)
    {
      $objp = $db->fetch_object($resql);
      print "<option value=\"$objp->rowid\">$objp->label</option>";
      $i++;
    }
  $db->free($resql);
}
print "</select></td>\n";

print "<td>";
$html->select_date('','','','','','add');
print "</td>\n";
print '<td><input name="label" class="flat" type="text" size="40"></td>';
print '<td><input name="amount" class="flat" type="text" size="8"></td>';

print "</table>";

print '<br><center><input type="submit" class="button" value="'.$langs->trans("Add").'"></center>';

print "</form>";

$db->close();

llxFooter('$Date$ - $Revision$');
?>
