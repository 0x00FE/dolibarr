<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Xavier DUTOIT        <doli@sydesy.com>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Christophe Combelles <ccomb@free.fr>
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
        \file       htdocs/compta/bank/ligne.php
        \ingroup    compta
		\brief      Page �dition d'une �criture bancaire
		\version    $Revision$
*/

require("./pre.inc.php");

if (!$user->rights->banque->modifier)
  accessforbidden();

$langs->load("banks");


$rowid=isset($_GET["rowid"])?$_GET["rowid"]:$_POST["rowid"];
$orig_account=isset($_GET["orig_account"])?$_GET["orig_account"]:$_POST["orig_account"];

$html = new Form($db);


/*
 * Actions
 */

if ($_GET["action"] == 'dvnext')
{
  $ac = new Account($db);
  $ac->datev_next($_GET["rowid"]);
}

if ($_GET["action"] == 'dvprev')
{
  $ac = new Account($db);
  $ac->datev_previous($_GET["rowid"]);
}

if ($_POST["action"] == 'confirm_delete_categ' && $_POST["confirm"] == "yes")
{
  $sql = "DELETE FROM ".MAIN_DB_PREFIX."bank_class WHERE lineid = $rowid AND fk_categ = ".$_GET["cat1"];
  if (! $db->query($sql))
    {
      dolibarr_print_error($db);
    }
}

if ($_POST["action"] == 'class')
{
  $sql = "DELETE FROM ".MAIN_DB_PREFIX."bank_class WHERE lineid = $rowid AND fk_categ = ".$_POST["cat1"];
  if (! $db->query($sql))
    {
      dolibarr_print_error($db);
    }

  $sql = "INSERT INTO ".MAIN_DB_PREFIX."bank_class (lineid, fk_categ) VALUES (".$_GET["rowid"].", ".$_POST["cat1"].")";
  if (! $db->query($sql))
    {
      dolibarr_print_error($db);
    }
}

if ($_POST["action"] == "update")
{
	// Avant de modifier la date ou le montant, on controle si ce n'est pas encore rapproche
	$sql = "SELECT b.rappro FROM ".MAIN_DB_PREFIX."bank as b WHERE rowid=".$rowid;
	$result = $db->query($sql);
	if ($result)
	{
		$objp = $db->fetch_object($result);
		if ($objp->rappro)
			die ("Vous ne pouvez pas modifier une �criture d�j� rapproch�e");
	}

    $db->begin();
    
	$amount = str_replace(' ','',$_POST['amount']);
	$amount = str_replace(',','.',$amount);

	$dateop = $_POST["doyear"].'-'.$_POST["domonth"].'-'.$_POST["doday"];
	$dateval= $_POST["dvyear"].'-'.$_POST["dvmonth"].'-'.$_POST["dvday"];
	$sql = "UPDATE ".MAIN_DB_PREFIX."bank";
	$sql.= " SET label='".$_POST["label"]."',";
	if (isset($_POST['amount'])) $sql.=" amount='$amount',";
	$sql.= " dateo = '".$dateop."', datev = '".$dateval."',";
	$sql.= " fk_account = ".$_POST['accountid'];
	$sql.= " WHERE rowid = $rowid;";

	$result = $db->query($sql);
	if ($result)
	{
        $db->commit();
    }
    else
    {    
        $db->rollback();
		dolibarr_print_error($db);
	}
}

if ($_POST["action"] == 'type')
{
  $sql = "UPDATE ".MAIN_DB_PREFIX."bank set fk_type='".$_POST["value"]."', num_chq='".$_POST["num_chq"]."' WHERE rowid = $rowid;";
  $result = $db->query($sql);
}

if ($_POST["action"] == 'num_releve')
{
    $db->begin();
    $sql = "UPDATE ".MAIN_DB_PREFIX."bank";
    $sql.= " SET num_releve='".$_POST["num_rel"]."'";
    $sql.= " WHERE rowid = ".$rowid;

    $result = $db->query($sql);
	if ($result)
	{
        $db->commit();
    }
    else
    {    
        $db->rollback();
		dolibarr_print_error($db);
	}
}


/*
 * Affichage fiche ligne ecriture en mode edition
 */
 
llxHeader();

// On initialise la liste des categories
$sql = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."bank_categ;";
$result = $db->query($sql);
if ($result)
{
  $var=True;  
  $num = $db->num_rows($result);
  $i = 0;
  $options = "<option value=\"0\" selected=\"true\">&nbsp;</option>";
  while ($i < $num)
    {
      $obj = $db->fetch_object($result);
      $options .= "<option value=\"$obj->rowid\">$obj->label</option>\n";
      $i++;
    }
  $db->free($result);
}

$var=False;
$h=0;


$head[$h][0] = DOL_URL_ROOT.'/compta/bank/ligne.php?rowid='.$_GET["rowid"];
$head[$h][1] = $langs->trans('Card');
$hselected=$h;
$h++;

dolibarr_fiche_head($head, $hselected, $langs->trans('LineRecord').': '.$_GET["rowid"]);


$sql = "SELECT b.rowid,".$db->pdate("b.dateo")." as do,".$db->pdate("b.datev")." as dv, b.amount, b.label, b.rappro,";
$sql.= " b.num_releve, b.fk_user_author, b.num_chq, b.fk_type, b.fk_account";
$sql.= " FROM ".MAIN_DB_PREFIX."bank as b";
$sql.= " WHERE rowid=".$rowid;
$sql.= " ORDER BY dateo ASC";
$result = $db->query($sql);
if ($result)
{
    $i = 0; $total = 0;
    if ($db->num_rows($result))
    {

        // Confirmations
        if ($_GET["action"] == 'delete_categ')
        {
            $html->form_confirm("ligne.php?rowid=".$_GET["rowid"]."&amp;cat1=".$_GET["fk_categ"]."&amp;orig_account=".$orig_account,"Supprimer dans la cat�gorie","Etes-vous s�r de vouloir supprimer le classement dans la cat�gorie ?","confirm_delete_categ");
            print '<br>';
        }

        print '<table class="border" width="100%">';

        $objp = $db->fetch_object($result);
        $total = $total + $objp->amount;

        $acct=new Account($db,$objp->fk_account);
        $acct->fetch($objp->fk_account);
        $account = $acct->id;

        $links=$acct->get_url($rowid);

        // Tableau sur 4 colonne si d�ja rapproch�, sinon sur 5 colonnes

        // Author
        print '<tr><td width="20%">'.$langs->trans("Author")."</td>";
        if ($objp->fk_user_author) 
        {
            $author=new User($db,$objp->fk_user_author);
            $author->fetch();
            print '<td colspan="4"><a href="'.DOL_URL_ROOT.'/user/fiche.php?id='.$author->id.'">';
            print img_object($langs->trans("ShowUser"),'user').' '.$author->fullname.'</td>';
        }
        else
        {
            print '<td colspan="4">&nbsp;</td>';
        }
        print "</tr>";
        
        $i++;

        print "<form method=\"post\" action=\"ligne.php?rowid=$objp->rowid\">";
        print "<input type=\"hidden\" name=\"action\" value=\"update\">";
        print "<input type=\"hidden\" name=\"orig_account\" value=\"".$orig_account."\">";
    
        // Account
        print "<tr><td>".$langs->trans("Account")."</td>";
/*
        if (! $objp->rappro)
        {
            if ($user->rights->banque->modifier && $acct->type != 2 && $acct->rappro)  // Si non compte cash et si rapprochable
            {
                print '<td colspan="3">';
                $html->select_comptes($acct->id,'accountid',0);
                print '</td>';
                //print '<td align="center">';
                //print '<input type="submit" class="button" name="conciliate" value="'.$langs->trans("Conciliate").'">';
                //print '</td>';
            }
            else
            {
                print '<td colspan="3">';
                $html->select_comptes($acct->id,'accountid',0);
                print '</td>';
            }
            print '<td align="center"><input type="submit" class="button" value="'.$langs->trans("Update").'"></td>';
        }
        else
        {
*/
            print '<td colspan="4">';
            print '<a href="account.php?account='.$acct->id.'">'.img_object($langs->trans("ShowAccount"),'account').' '.$acct->label.'</a>';
            print '<input type="hidden" name="accountid" value="'.$acct->id.'">';
            print '</td>';
/*
        }
*/
        print '</tr>';
        
        // Date ope
        print '<tr><td>'.$langs->trans("Date").'</td>';
        if (! $objp->rappro)
        {
            print '<td colspan="3">';
            $html->select_date($objp->do,'do');
            print '</td><td align="center"><input type="submit" class="button" value="'.$langs->trans("Update").'"></td>';
        }
        else
        {
            print '<td colspan="4">';
            print dolibarr_print_date($objp->do);
        }
        print '</td></tr>';
        
        // Value date
        print "<tr><td>".$langs->trans("DateValue")."</td>";
        if (! $objp->rappro)
        {
            print '<td colspan="3">';
            $html->select_date($objp->dv,'dv');
            print ' &nbsp; ';
            print '<a href="ligne.php?action=dvprev&amp;account='.$_GET["account"].'&amp;rowid='.$objp->rowid.'">';
            print img_edit_remove() . "</a> ";
            print '<a href="ligne.php?action=dvnext&amp;account='.$_GET["account"].'&amp;rowid='.$objp->rowid.'">';
            print img_edit_add() ."</a>";
            print '</td><td align="center"><input type="submit" class="button" value="'.$langs->trans("Update").'">';
        }
        else
        {
            print '<td colspan="4">';
            print dolibarr_print_date($objp->dv);
        }
        print "</td></tr>";
        
        // Description
        print "<tr><td>".$langs->trans("Label")."</td>";
        if (! $objp->rappro)
        {
            print '<td colspan="3">';
            print '<input name="label" class="flat" value="'.$objp->label.'" size="50">';
            print '</td>';
            print '<td align="center"><input type="submit" class="button" value="'.$langs->trans("Update").'">';
        }
        else
        {
            print '<td colspan="4">';
            print $objp->label;            
        }
        print '</td></tr>';

        // Affiche liens
        if (sizeof($links)) {
            print "<tr><td>".$langs->trans("Links")."</td>";
            print '<td colspan="3">';
            foreach($links as $key=>$val)
            {
                if ($key) print '<br>';
                print '<a href="'.$links[$key]['url'].$links[$key]['url_id'].'">';
                if ($links[$key]['type']=='payment') { print img_object($langs->trans('ShowPayment'),'payment').' '; }
                if ($links[$key]['type']=='company') { print img_object($langs->trans('ShowCustomer'),'company').' '; }
                print $links[$key]['label'].'</a>';
            }
            print '</td><td>&nbsp;</td></tr>';
        }
            
        // Amount
        print "<tr><td>".$langs->trans("Amount")."</td>";
        if (! $objp->rappro)
        {
            print '<td colspan="3">';
            print '<input name="amount" class="flat" size="10" value="'.price($objp->amount).'"> '.$conf->monnaie;
            print '</td><td align="center"><input type="submit" class="button" value="'.$langs->trans("Update").'">';
        }
        else
        {
            print '<td colspan="4">';
            print price($objp->amount);
        }
        print "</td></tr>";
    
        print "</form>";
    
        // Type paiement
        print "<tr><td>".$langs->trans("Type")."</td><td colspan=\"3\">";
        print "<form method=\"post\" action=\"ligne.php?rowid=$objp->rowid\">";
        print '<input type="hidden" name="action" value="type">';
        print "<input type=\"hidden\" name=\"orig_account\" value=\"".$orig_account."\">";
        print $html->select_types_paiements($objp->fk_type,"value",'',2);
        print '<input type="text" class="flat" name="num_chq" value="'.(empty($objp->num_chq) ? '' : $objp->num_chq).'">';
        print '</td><td align="center"><input type="submit" class="button" value="'.$langs->trans("Update").'">';
        print "</form>";
        print "</td></tr>";
    
        // Releve rappro
        if ($acct->rappro)  // Si compte rapprochable
        {
            print "<tr><td>".$langs->trans("Conciliation")."</td>";
            print "<form method=\"post\" action=\"ligne.php?rowid=$objp->rowid\">";
            print '<input type="hidden" name="action" value="num_releve">';
            print "<input type=\"hidden\" name=\"orig_account\" value=\"".$orig_account."\">";
            print '<td colspan="3">';
            print $langs->trans("AccountStatement").' <input name="num_rel" class="flat" value="'.$objp->num_releve.'">';
            print '</td><td align="center"><input type="submit" class="button" value="'.$langs->trans("Update").'"></td>';
            print '</form>';
            print '</tr>';
        }
    
        print "</table>";
    
    }

    $db->free($result);
}
print '</div>';


/*
 *  Boutons actions
 */
/*
print '<div class="tabsAction">';
    
if ($orig_account)
{
    $acct=new Account($db,$orig_account);
    $acct->fetch($orig_account);
    print '<a class="tabAction" href="rappro.php?account='.$orig_account.'">'.$langs->trans("BackToConciliate",$acct->label).'</a>';
}

print '</div>';
*/

// Liste les categories

print '<br>';
print '<table class="noborder" width="100%">';

print "<form method=\"post\" action=\"ligne.php?rowid=$rowid&amp;account=$account\">";
print "<input type=\"hidden\" name=\"action\" value=\"class\">";
print "<input type=\"hidden\" name=\"orig_account\" value=\"".$orig_account."\">";
print "<tr class=\"liste_titre\"><td>".$langs->trans("Categories")."</td><td colspan=\"2\">";
print "<select class=\"flat\" name=\"cat1\">$options";
print "</select>&nbsp;";
print '<input type="submit" class="button" value="'.$langs->trans("Add").'"></td>';
print "</tr>";
print "</form>";

$sql = "SELECT c.label, c.rowid";
$sql .= " FROM ".MAIN_DB_PREFIX."bank_class as a, ".MAIN_DB_PREFIX."bank_categ as c WHERE a.lineid=$rowid AND a.fk_categ = c.rowid ";
$sql .= " ORDER BY c.label";
$result = $db->query($sql);
if ($result)
{
  $var=True;  
  $num = $db->num_rows($result);
  $i = 0; $total = 0;
  while ($i < $num)
    {
      $objp = $db->fetch_object($result);

      $var=!$var;
      print "<tr $bc[$var]>";
      
      print "<td>$objp->label</td>";
      print "<td align=\"center\"><a href=\"budget.php?bid=$objp->rowid\">".$langs->trans("List")."</a></td>";
      print "<td align=\"center\"><a href=\"ligne.php?action=delete_categ&amp;rowid=$rowid&amp;fk_categ=$objp->rowid\">".img_delete($langs->trans("Remove"))."</a></td>";
      print "</tr>";

      $i++;
    }
  $db->free($result);
}
print "</table>";


$db->close();

llxFooter('$Date$ - $Revision$');
?>
