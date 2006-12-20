<?php
/* Copyright (C) 2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
   \file       htdocs/compta/paiement/cheque/liste.php
    \ingroup    compta
     \brief      Page liste des bordereau de remise de cheque
      \version    $Revision$
*/

require("./pre.inc.php");

$langs->load("bills");

$user->getrights("facture");

// S�curit� acc�s client
if (! $user->rights->facture->lire)
  accessforbidden();

$socid=0;
if ($user->societe_id > 0) 
{
  $action = '';
  $socid = $user->societe_id;
}


/*
 * Affichage
 */

llxHeader('',$langs->trans("CheckReceipt"));

$page=$_GET["page"];
$sortorder=$_GET["sortorder"];
$sortfield=$_GET["sortfield"];
 
$limit = $conf->liste_limit;
$offset = $limit * $page ;
if (! $sortorder) $sortorder="DESC";
if (! $sortfield) $sortfield="p.rowid";
  
$sql = "SELECT p.rowid,".$db->pdate("p.datep")." as dp, p.amount,";
$sql.= " p.statut, p.num_paiement,";
$sql.= " c.code as paiement_code,"; 
$sql.= " ba.rowid as bid, ba.label";
$sql.= " FROM ".MAIN_DB_PREFIX."c_paiement as c,";
$sql.= " ".MAIN_DB_PREFIX."paiement as p";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."bank as b ON p.fk_bank = b.rowid";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."bank_account as ba ON b.fk_account = ba.rowid";
if ($socid)
{
    $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."paiement_facture as pf ON p.rowid = pf.fk_paiement";
    $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."facture as f ON pf.fk_facture = f.rowid";
}
$sql.= " WHERE p.fk_paiement = c.id AND c.code = 'CHQ'";
if ($socid)
{
    $sql.= " AND f.fk_soc = ".$socid;
}
if ($_GET["search_montant"])
{
  $sql .=" AND p.amount=".price2num($_GET["search_montant"]);
}

$sql .= " ORDER BY $sortfield $sortorder";
$sql .= $db->plimit( $limit+1 ,$offset);
//print "$sql";

$resql = $db->query($sql);

if ($resql)
{
    $num = $db->num_rows($resql);
    $i = 0;

    $paramlist=($_GET["orphelins"]?"&orphelins=1":"");
    print_barre_liste($langs->trans("CheckReceipt"), $page, "liste.php",$paramlist,$sortfield,$sortorder,'',$num);

    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("Ref"),"liste.php","p.rowid","",$paramlist,"",$sortfield);
    print_liste_field_titre($langs->trans("Date"),"liste.php","dp","",$paramlist,'align="center"',$sortfield);
    print_liste_field_titre($langs->trans("Type"),"liste.php","c.libelle","",$paramlist,"",$sortfield);
    print_liste_field_titre($langs->trans("Account"),"liste.php","ba.label","",$paramlist,"",$sortfield);
    print_liste_field_titre($langs->trans("Amount"),"liste.php","p.amount","",$paramlist,'align="right"',$sortfield);
    print_liste_field_titre($langs->trans("Status"),"liste.php","p.statut","",$paramlist,'align="center"',$sortfield);
    print "</tr>\n";

    // Lignes des champs de filtre
    print '<form method="get" action="liste.php">';
    print '<tr class="liste_titre">';
    print '<td colspan="4">&nbsp;</td>';
    print '<td align="right">';
    print '<input class="fat" type="text" size="6" name="search_montant" value="'.$_GET["search_montant"].'">';
    print '</td><td align="right">';
    print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" alt="'.$langs->trans("Search").'">';
    print '</td>';
    print "</tr>\n";
    print '</form>';

    $var=true;
    while ($i < min($num,$limit))
      {
        $objp = $db->fetch_object($resql);
        $var=!$var;
        print "<tr $bc[$var]>";
        print '<td width="40"><a href="'.DOL_URL_ROOT.'/compta/paiement/fiche.php?id='.$objp->rowid.'">'.img_object($langs->trans("ShowPayment"),"payment").'</a>';
	
        print '&nbsp;<a href="'.DOL_URL_ROOT.'/compta/paiement/fiche.php?id='.$objp->rowid.'">'.$objp->rowid.'</a></td>';

        print '<td align="center">'.dolibarr_print_date($objp->dp).'</td>';
        //print '<td>'.$objp->paiement_type.' '.$objp->num_paiement.'</td>';
	print '<td>'.$langs->trans("PaymentTypeShort".$objp->paiement_code).' '.$objp->num_paiement.'</td>';
        print '<td>';
        if ($objp->bid) print '<a href="'.DOL_URL_ROOT.'/compta/bank/account.php?account='.$objp->bid.'">'.img_object($langs->trans("ShowAccount"),'account').' '.$objp->label.'</a>';
        else print '&nbsp;';
        print '</td>';
        print '<td align="right">'.price($objp->amount).'</td>';
        print '<td align="center">';

        if ($objp->statut == 0)
        {
            print '<a href="fiche.php?id='.$objp->rowid.'&amp;action=valide">'.$langs->trans("ToValidate").'</a>';
        }
        else
        {
            print img_tick();
        }

        print '</td></tr>';
        $i++;
    }
    print "</table>";
}
else
{
  dolibarr_print_error($db);
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
