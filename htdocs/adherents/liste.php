<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2002-2003 Jean-Louis Bergamo   <jlb@j1b.org>
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
        \file       htdocs/adherents/liste.php
        \ingroup    adherent
		\brief      Page listant les adh�rents
		\version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/adherents/adherent.class.php");

$langs->load("members");
$langs->load("companies");


/*
 * Affiche liste
 */

llxHeader();


$sall=isset($_GET["sall"])?$_GET["sall"]:$_POST["sall"];

$sortorder=$_GET["sortorder"];
$sortfield=$_GET["sortfield"];
$page=$_GET["page"];
$filter=$_GET["filter"];
$statut=isset($_GET["statut"])?$_GET["statut"]:'';

if (! $sortorder) {  $sortorder="ASC"; }
if (! $sortfield) {  $sortfield="d.nom"; }
if ($page == -1) { $page = 0 ; }
$offset = $conf->liste_limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

$sql = "SELECT d.rowid, d.prenom, d.nom, d.societe, ";
$sql.= " ".$db->pdate("d.datefin")." as datefin,";
$sql.= " d.email, d.fk_adherent_type as type_id, d.morphy, d.statut,";
$sql.= " t.libelle as type, t.cotisation";
$sql.= " FROM ".MAIN_DB_PREFIX."adherent as d, ".MAIN_DB_PREFIX."adherent_type as t";
$sql.= " WHERE d.fk_adherent_type = t.rowid ";
if ($sall) 
{
    $sql.=" AND (d.prenom like '%".$sall."%' OR d.nom like '%".$sall."%' OR d.societe like '%".$sall."%'";
    $sql.=" OR d.email like '%".$sall."%' OR d.login like '%".$sall."%' OR d.adresse like '%".$sall."%'";
    $sql.=" OR d.ville like '%".$sall."%' OR d.note like '%".$sall."%')";
}
if ($_GET["type"])
{
    $sql.=" AND t.rowid=".$_GET["type"];
}
if (isset($_GET["statut"]))
{   
    $sql.=" AND d.statut in ($statut)";     // Peut valoir un nombre ou liste de nombre s�par�s par virgules
}
if ( $_POST["action"] == 'search')
{
  if (isset($_POST['search']) && $_POST['search'] != ''){
    $sql.= " AND (d.prenom LIKE '%".$_POST['search']."%' OR d.nom LIKE '%".$_POST['search']."%')";
  }
}
if ($filter == 'uptodate')
{
    $sql.=" AND datefin >= sysdate()";
}
if ($filter == 'outofdate')
{
    $sql.=" AND datefin < sysdate()";
}
$sql.= " ORDER BY $sortfield $sortorder";
$sql.= " ".$db->plimit($conf->liste_limit+1, $offset);

$result = $db->query($sql);
if ($result) 
{
    $num = $db->num_rows($result);
    $i = 0;

    $titre=$langs->trans("MembersList");
    if (isset($_GET["statut"]))
    {
        if ($statut == '-1,1') { $titre=$langs->trans("MembersListQualified"); }
        if ($statut == '-1')   { $titre=$langs->trans("MembersListToValid"); }
        if ($statut == '1' && ! $filter)    		{ $titre=$langs->trans("MembersListValid"); }
        if ($statut == '1' && $filter=='uptodate')  { $titre=$langs->trans("MembersListUpToDate"); }
        if ($statut == '1' && $filter=='outofdate')	{ $titre=$langs->trans("MembersListNotUpToDate"); }
        if ($statut == '0')    { $titre=$langs->trans("MembersListResiliated"); }
    }
    elseif ($_POST["action"] == 'search') {
        $titre=$langs->trans("MembersListQualified");
    }

    if ($_GET["type"]) {
        $objp = $db->fetch_object($result);
        $titre.=" (".$objp->type.")";
    }

    $param="";
    if (isset($_GET["statut"])) $param.="&statut=$statut";
    print_barre_liste($titre,$page,$_SERVER["PHP_SELF"],$param,$sortfield,$sortorder,'',$num);

    print "<table class=\"noborder\" width=\"100%\">";

    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("Name")." / ".$langs->trans("Company"),"liste.php","d.nom",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("Type"),"liste.php","t.libelle",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("Person"),"liste.php","d.morphy",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("EMail"),"liste.php","d.email",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("Status"),"liste.php","d.statut",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("EndSubscription"),"liste.php","t.cotisation",$param,"","",$sortfield);
    print '<td width="60" align="center">'.$langs->trans("Action")."</td>\n";
    print "</tr>\n";

    $var=True;
    while ($i < $num && $i < $conf->liste_limit)
    {
        if ($_GET["type"] && $i==0)
        {
        	# Fetch deja fait
        } else {
            $objp = $db->fetch_object($result);
        }

        $adh=new Adherent($db);

        // Nom
        $var=!$var;
        print "<tr $bc[$var]>";
        if ($objp->societe != '')
        {
            print "<td><a href=\"fiche.php?rowid=$objp->rowid\">".img_object($langs->trans("ShowAdherent"),"user").' '.stripslashes($objp->prenom)." ".stripslashes($objp->nom)." / ".stripslashes($objp->societe)."</a></td>\n";
        }
        else
        {
            print "<td><a href=\"fiche.php?rowid=$objp->rowid\">".img_object($langs->trans("ShowAdherent"),"user").' '.stripslashes($objp->prenom)." ".stripslashes($objp->nom)."</a></td>\n";
        }
        
        // Type
        print '<td><a href="type.php?rowid='.$objp->type_id.'">'.img_object($langs->trans("ShowType"),"group").' '.$objp->type.'</a></td>';
        
        // Moral/Physique
        print "<td>".$adh->getmorphylib($objp->morphy)."</td>\n";

        // EMail
        print "<td>$objp->email</td>\n";
        
        // Statut
        print "<td>";
        print $adh->LibStatut($objp->statut,$objp->cotisation,$objp->datefin,4);
        print "</td>";

        // Date fin cotisation
        if ($objp->datefin)
        {
	        print '<td align="center">';
            if ($objp->datefin < time() && $objp->statut > 0)
            {
                print dolibarr_print_date($objp->datefin)." - ".$langs->trans("SubscriptionLate")." ".img_warning();
            }
            else
            {
                print dolibarr_print_date($objp->datefin);
            }
            print '</td>';
        }
        else
        {
	        print '<td align="left">';
	        if ($objp->cotisation == 'yes')
	        {
                print $langs->trans("SubscriptionNotReceived");
                if ($objp->statut > 0) print " ".img_warning();
	        }
	        else
	        {
	            print '&nbsp;';
	        }
            print '</td>';
        }

        // Actions
        print '<td align="center">';
        print "<a href=\"fiche.php?rowid=$objp->rowid&action=edit&return=liste.php\">".img_edit()."</a>&nbsp;";
        print "<a href=\"fiche.php?rowid=$objp->rowid&action=resign&return=liste.php\">".img_disable($langs->trans("Resiliate"))."</a>";
        print "</td>\n";

        print "</tr>";
        $i++;
    }
    print "</table>\n";

	if ($num > $conf->liste_limit)
	{
	    print "<table class=\"noborder\" width=\"100%\">";
	    print_barre_liste($titre, $page, $_SERVER["PHP_SELF"], "&statut=$statut&sortorder=$sortorder&sortfield=$sortfield",$sortfield,$sortorder,'',$num);
	    print "</table><br>\n";
	}
	print "<br>";
}
else
{
    dolibarr_print_error($db);
}


$db->close();

llxFooter('$Date$ - $Revision$');

?>
