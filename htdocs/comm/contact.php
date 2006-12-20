<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      �ric Seigne          <erics@rycks.com>
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
        \file       htdocs/comm/contact.php
        \ingroup    commercial
        \brief      Liste des contacts
        \version    $Revision$
*/

require("./pre.inc.php");

if (!$user->rights->societe->lire) accessforbidden();

$langs->load("companies");

llxHeader('','Contacts');

$sortfield=isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder=isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
$page=$_GET["page"];

$socid=$_GET["socid"];
$type=$_GET["type"];


/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socid = $user->societe_id;
}


if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="p.name";
if ($page < 0) { $page = 0 ; }
$limit = $conf->liste_limit;
$offset = $limit * $page ;


if ($type == "c")
{
  $label = $langs->trans("Customers");
  $urlfiche="fiche.php";
}
if ($type == "p")
{
  $label = $langs->trans("Prospects");
  $urlfiche="prospect/fiche.php";
}
if ($type == "f")
{
  $label = $langs->trans("Suppliers");
  $urlfiche="fiche.php";
}

/*
 * Mode liste
 *
 */

$sql = "SELECT s.idp, s.nom,  st.libelle as stcomm";
$sql .= ", p.idp as cidp, p.name, p.firstname, p.email, p.phone";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= ", sc.fk_soc, sc.fk_user";
$sql .= " FROM ".MAIN_DB_PREFIX."c_stcomm as st,";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= " ".MAIN_DB_PREFIX."societe_commerciaux as sc,";
$sql .= " ".MAIN_DB_PREFIX."socpeople as p";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.idp = p.fk_soc";
$sql .= " WHERE s.fk_stcomm = st.id";
if (!$user->rights->commercial->client->voir && !$socid) $sql .= " AND s.idp = sc.fk_soc AND sc.fk_user = " .$user->id;
if ($type == "c") $sql .= " AND s.client = 1";
if ($type == "p") $sql .= " AND s.client = 2";
if ($type == "f") $sql .= " AND s.fournisseur = 1";


if (strlen($stcomm))
{
  $sql .= " AND s.fk_stcomm=$stcomm";
}

if (strlen($begin)) // filtre sur la premiere lettre du nom
{
  $sql .= " AND upper(p.name) like '$begin%'";
}

if (trim($_GET["search_nom"]))
{
  $sql .= " AND p.name like '%".trim($_GET["search_nom"])."%'";
}

if (trim($_GET["search_prenom"]))
{
  $sql .= " AND p.firstname like '%".trim($_GET["search_prenom"])."%'";
}

if (trim($_GET["search_societe"]))
{
  $sql .= " AND s.nom like '%".trim($_GET["search_societe"])."%'";
}

if ($_GET["contactname"]) // acces a partir du module de recherche
{
  $sql .= " AND ( p.name like '%".strtolower($_GET[contactname])."%' OR lower(p.firstname) like '%".strtolower($_GET[contactname])."%') ";
  $sortfield = "p.name";
  $sortorder = "ASC";
}

if ($socid) {
  $sql .= " AND s.idp = $socid";
}

$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit($limit+1, $offset);

$result = $db->query($sql);
if ($result) 
{
  $num = $db->num_rows();
  
  print_barre_liste($langs->trans("ListOfContacts").($label?" (".$label.")":""),$page, "contact.php", "&amp;type=$type",$sortfield,$sortorder,"",$num);
  
  print '<table class="liste" width="100%">';
  print '<tr class="liste_titre">';
  print_liste_field_titre($langs->trans("Lastname"),"contact.php","p.name", $begin,"&amp;type=$type","",$sortfield);
  print_liste_field_titre($langs->trans("Firstname"),"contact.php","p.firstname", $begin,"&amp;type=$type","",$sortfield);
  print_liste_field_titre($langs->trans("Company"),"contact.php","s.nom", $begin,"&amp;type=$type","",$sortfield);
  print '<td class="liste_titre">'.$langs->trans("Email").'</td>';
  print '<td class="liste_titre">'.$langs->trans("Phone").'</td>';
  print "</tr>\n";

  print '<form action="contact.php?type='.$_GET["type"].'" method="GET">';
  print '<tr class="liste_titre">';
  print '<td class="liste_titre"><input class="flat" name="search_nom" size="12" value="'.$_GET["search_nom"].'"></td>';
  print '<td class="liste_titre"><input class="flat" name="search_prenom" size="12"  value="'.$_GET["search_prenom"].'"></td>';
  print '<td class="liste_titre"><input class="flat" name="search_societe" size="12"  value="'.$_GET["search_societe"].'"></td>';
  print '<td class="liste_titre">&nbsp;</td>';
  print '<td class="liste_titre" align="right"><input type="image" class="liste_titre" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" alt='.$langs->trans("Search").'></td>';
  print "</tr>\n";
  print '</form>';

  $var=True;
  $i = 0;
  while ($i < min($num,$limit))
    {
      $obj = $db->fetch_object();
    
      $var=!$var;

      print "<tr $bc[$var]>";
      print '<td><a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$obj->cidp.'&socid='.$obj->idp.'">'.img_object($langs->trans("ShowContact"),"contact");
      print '</a>&nbsp;<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$obj->cidp.'&socid='.$obj->idp.'">'.$obj->name.'</a></td>';
      print "<td>$obj->firstname</TD>";
      
      print '<td><a href="contact.php?type='.$type.'&socid='.$obj->idp.'">'.img_object($langs->trans("ShowCompany"),"company").'</a>&nbsp;';
      print "<a href=\"".$urlfiche."?socid=$obj->idp\">$obj->nom</a></td>\n";
      
      print '<td><a href="action/fiche.php?action=create&actioncode=AC_EMAIL&contactid='.$obj->cidp.'&socid='.$obj->idp.'">'.$obj->email.'</a>&nbsp;</td>';
      
      print '<td><a href="action/fiche.php?action=create&actioncode=AC_TEL&contactid='.$obj->cidp.'&socid='.$obj->idp.'">'.dolibarr_print_phone($obj->phone).'</a>&nbsp;</td>';
      
      print "</tr>\n";
      $i++;
    }
  print "</table></p>";
  $db->free();
}
else
{
    dolibarr_print_error($db);
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
