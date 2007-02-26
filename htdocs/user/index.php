<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/user/index.php
        \brief      Page d'accueil de la gestion des utilisateurs
        \version    $Revision$
*/

require("./pre.inc.php");

$user->getrights();

if (! $user->rights->user->user->lire && ! $user->admin) accessforbidden();

$langs->load("users");
$langs->load("companies");

$sall=isset($_GET["sall"])?$_GET["sall"]:$_POST["sall"];

$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
$page=isset($_GET["page"])?$_GET["page"]:$_POST["page"];
if ($page < 0) $page = 0;

$limit = $conf->liste_limit;
$offset = $limit * $page ;

if (! $sortfield) $sortfield="u.login";
if (! $sortorder) $sortorder="ASC";

$userstatic=new User($db);



llxHeader();

print_titre($langs->trans("ListOfUsers"));

$sql = "SELECT u.rowid, u.name, u.firstname, u.admin, u.fk_societe, u.login, ".$db->pdate("u.datec")." as datec,";
$sql.= " u.ldap_sid, u.statut,";
$sql.= " s.nom";
$sql.= " FROM ".MAIN_DB_PREFIX."user as u";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON u.fk_societe = s.idp";
$sql.= " WHERE 1=1";
if ($_POST["search_user"])
{
    $sql.= " AND (u.name like '%".$_POST["search_user"]."%' OR u.firstname like '%".$_POST["search_user"]."%')";
}
if ($sall) $sql.= " AND (u.login like '%".$sall."%' OR u.name like '%".$sall."%' OR u.firstname like '%".$sall."%' OR u.code like '%".$sall."%' OR u.email like '%".$sall."%' OR u.note like '%".$sall."%')";
if ($sortfield) $sql.=" ORDER BY $sortfield $sortorder";

$result = $db->query($sql);
if ($result)
{
    $num = $db->num_rows($result);
    $i = 0;

    print "<br>";

    $param="search_user=$search_user&amp;sall=$sall";
    print "<table class=\"noborder\" width=\"100%\">";
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("Login"),"index.php","u.login",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("LastName"),"index.php","u.name",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("FirstName"),"index.php","u.firstname",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("Company"),"index.php","u.fk_societe",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("DateCreation"),"index.php","u.datec",$param,"","",$sortfield);
    print_liste_field_titre($langs->trans("Status"),"index.php","u.status",$param,"",'align="right"',$sortfield);
    print "</tr>\n";
    $var=True;
    while ($i < $num)
    {
        $obj = $db->fetch_object($result);
        $var=!$var;

        print "<tr $bc[$var]>";
        print '<td><a href="fiche.php?id='.$obj->rowid.'">'.img_object($langs->trans("ShowUser"),"user").' '.$obj->login.'</a>';
        if ($obj->admin) 
        {
          	print img_picto($langs->trans("Administrator"),'star');
        }
        print '</td>';
        print '<td>'.ucfirst($obj->name).'</td>';
        print '<td>'.ucfirst($obj->firstname).'</td>';
        print "<td>";
        if ($obj->fk_societe)
        {
            print '<a href="'.DOL_URL_ROOT.'/soc.php?socid='.$obj->fk_societe.'">'.img_object($langs->trans("ShowCompany"),"company").' '.$obj->nom.'</a>';
        }
        else if ($obj->ldap_sid)
        {
        	print $langs->trans("DomainUser");
        }
        else print $langs->trans("InternalUser");
        print '</td>';
        print '<td width="100" align="center">'.dolibarr_print_date($obj->datec,"%d %b %Y").'</td>';

		// Statut
		$userstatic->statut=$obj->statut;
        print '<td width="100" align="right">'.$userstatic->getLibStatut(5).'</td>';
        print "</tr>\n";
        $i++;
    }
    print "</table>";
    $db->free($result);
}
else
{
    dolibarr_print_error($db);
}

$db->close();

llxFooter('$Date$ - $Revision$');
?>
