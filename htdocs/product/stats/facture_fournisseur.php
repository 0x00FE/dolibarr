<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
        \file       htdocs/product/stats/facture_fournisseur.php
        \ingroup    product, service, facture
        \brief      Page des stats des factures fournisseurs pour un produit
        \version    $Revision$
*/


require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/product.lib.php");
require_once(DOL_DOCUMENT_ROOT."/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/product.class.php");

$langs->load("companies");
$langs->load("bills");
$langs->load("products");
$langs->load("companies");

$mesg = '';

$page = $_GET["page"];
$sortfield=$_GET["sortfield"];
$sortorder=$_GET["sortorder"];
if ($page == -1) { $page = 0 ; }
$offset = $conf->liste_limit * $_GET["page"] ;
$pageprev = $_GET["page"] - 1;
$pagenext = $_GET["page"] + 1;
if (! $sortorder) $sortorder="DESC";
if (! $sortfield) $sortfield="f.datef";

// Securite
$socidp = 0;
if ($user->societe_id > 0)
{
    $socidp = $user->societe_id;
}


/*
 * Affiche fiche
 *
 */


if ($_GET["id"] || $_GET["ref"])
{
    $product = new Product($db);
    if ($_GET["ref"]) $result = $product->fetch('',$_GET["ref"]);
    if ($_GET["id"]) $result = $product->fetch($_GET["id"]);
    
    llxHeader("","",$langs->trans("CardProduct".$product->type));

    if ($result > 0)
    {
        /*
         *  En mode visu
         */
		$head=product_prepare_head($product);
		$titre=$langs->trans("CardProduct".$product->type);
		dolibarr_fiche_head($head, 'referers', $titre);


        print '<table class="border" width="100%">';

        // Reference
        print '<tr>';
        print '<td width="15%">'.$langs->trans("Ref").'</td><td colspan="3">';
        $product->load_previous_next_ref();
        $previous_ref = $product->ref_previous?'<a href="'.$_SERVER["PHP_SELF"].'?ref='.$product->ref_previous.'">'.img_previous().'</a>':'';
        $next_ref     = $product->ref_next?'<a href="'.$_SERVER["PHP_SELF"].'?ref='.$product->ref_next.'">'.img_next().'</a>':'';
        if ($previous_ref || $next_ref) print '<table class="nobordernopadding" width="100%"><tr class="nobordernopadding"><td class="nobordernopadding">';
        print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$product->id.'">'.$product->ref.'</a>';
        if ($previous_ref || $next_ref) print '</td><td class="nobordernopadding" align="center" width="20">'.$previous_ref.'</td><td class="nobordernopadding" align="center" width="20">'.$next_ref.'</td></tr></table>';
        print '</td>';
        print '</tr>';

        // Libelle
        print '<tr><td>'.$langs->trans("Label").'</td><td colspan="3">'.$product->libelle.'</td>';
        print '</tr>';
        
        // Prix
        print '<tr><td>'.$langs->trans("SellingPrice").'</td><td colspan="3">'.price($product->price).'</td></tr>';
        
        // Statut
        print '<tr><td>'.$langs->trans("Status").'</td><td colspan="3">';
		print $product->getLibStatut(2);
        print '</td></tr>';

		show_stats_for_company($product,$socidp);
    
        print "</table>";

        print '</div>';
        

        $sql = "SELECT distinct(s.nom), s.idp, s.code_client, f.facnumber, f.amount as amount,";
        $sql.= " ".$db->pdate("f.datef")." as date, f.paye, f.fk_statut as statut, f.rowid as facid";
        if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", sc.fk_soc, sc.fk_user ";
        $sql.= " FROM ".MAIN_DB_PREFIX."societe as s,".MAIN_DB_PREFIX."facture_fourn as f, ".MAIN_DB_PREFIX."facture_fourn_det as d";
        if (!$user->rights->commercial->client->voir && !$socidp) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
        $sql.= " WHERE f.fk_soc = s.idp";
        $sql.= " AND d.fk_facture_fourn = f.rowid AND d.fk_product =".$product->id;
        if (!$user->rights->commercial->client->voir && !$socidp) $sql .= " AND s.idp = sc.fk_soc AND sc.fk_user = " .$user->id;
        if ($socidp)
        {
            $sql .= " AND f.fk_soc = $socidp";
        }
        $sql.= " ORDER BY $sortfield $sortorder ";
        $sql.= $db->plimit($conf->liste_limit +1, $offset);

        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);

            print_barre_liste($langs->trans("SuppliersInvoices"),$page,$_SERVER["PHP_SELF"],"&amp;id=$product->id",$sortfield,$sortorder,'',$num);

            $i = 0;
            print "<table class=\"noborder\" width=\"100%\">";

            print '<tr class="liste_titre">';
            print_liste_field_titre($langs->trans("Ref"),$_SERVER["PHP_SELF"],"s.idp","","&amp;id=".$_GET["id"],'',$sortfield);
            print_liste_field_titre($langs->trans("Company"),$_SERVER["PHP_SELF"],"s.nom","","&amp;id=".$_GET["id"],'',$sortfield);
            print_liste_field_titre($langs->trans("SupplierCode"),$_SERVER["PHP_SELF"],"s.code_client","","&amp;id=".$_GET["id"],'',$sortfield);
            print_liste_field_titre($langs->trans("DateCreation"),$_SERVER["PHP_SELF"],"f.datef","","&amp;id=".$_GET["id"],'align="center"',$sortfield);
            print_liste_field_titre($langs->trans("AmountHT"),$_SERVER["PHP_SELF"],"f.amount","","&amp;id=".$_GET["id"],'align="right"',$sortfield);
            print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"],"f.paye,f.fk_statut","","&amp;id=".$_GET["id"],'align="right"',$sortfield);
            print "</tr>\n";

            if ($num > 0)
            {
                $var=True;
                while ($i < $num && $conf->liste_limit)
                {
                    $objp = $db->fetch_object($result);
                    $var=!$var;

                    print "<tr $bc[$var]>";
                    print '<td><a href="'.DOL_URL_ROOT.'/compta/facture.php?facid='.$objp->facid.'">'.img_object($langs->trans("ShowBill"),"bill").' ';
                    print $objp->facnumber;
                    print "</a></td>\n";
                    print '<td><a href="'.DOL_URL_ROOT.'/compta/fiche.php?socidp='.$objp->idp.'">'.img_object($langs->trans("ShowCompany"),"company").' '.dolibarr_trunc($objp->nom,44).'</a></td>';
                    print "<td>".$objp->code_client."</td>\n";
                    print "<td align=\"center\">";
                    print dolibarr_print_date($objp->date)."</td>";
                    print "<td align=\"right\">".price($objp->amount)."</td>\n";
                    $fac=new Facture($db);
                    print '<td align="right">'.$fac->LibStatut($objp->paye,$objp->statut,5).'</td>';
                    print "</tr>\n";
                    $i++;
                }
            }
        }
        else
        {
            dolibarr_print_error($db);
        }
        print "</table>";
        print '<br>';
        $db->free($result);
    }
}
else
{
    dolibarr_print_error();
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
