<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 */

/**     \file       htdocs/projet/index.php
        \ingroup    projet
		\brief      Page d'accueil du module projet
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("projects");

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}

llxHeader("",$langs->trans("Projects"),"Projet");

print_titre($langs->trans("Projects"));

$sortfield = isset($_GET["sortfield"])?$_GET["sortfield"]:$_POST["sortfield"];
$sortorder = isset($_GET["sortorder"])?$_GET["sortorder"]:$_POST["sortorder"];
$page=isset($_GET["page"])?$_GET["page"]:$_POST["page"];
if ($sortfield == "")
{
  $sortfield="p.ref";
}
if ($sortorder == "")
{
  $sortorder="ASC";
}

if ($page == -1) { $page = 0 ; }
$limit = 26;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;


/*
 *
 * Affichage de la liste des projets
 * 
 */
print '<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print_liste_field_titre($langs->trans("Ref"),"index.php","p.ref","","","",$sortfield);
print_liste_field_titre($langs->trans("Label"),"index.php","p.title","","","",$sortfield);
print_liste_field_titre($langs->trans("Company"),"index.php","s.nom","","","",$sortfield);
print "</tr>\n";

$sql = "SELECT s.nom, s.idp, p.rowid as projectid, p.ref, p.title, s.client,".$db->pdate("p.dateo")." as do";
$sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."projet as p";
$sql .= " WHERE p.fk_soc = s.idp";

if ($socidp)
{ 
  $sql .= " AND s.idp = $socidp"; 
}

$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit($conf->liste_limit, $offset);

$var=true;
if ( $db->query($sql) )
{
  $num = $db->num_rows();
  $i = 0;

  while ($i < $num)
    {
      $objp = $db->fetch_object( $i);    
      $var=!$var;
      print "<tr $bc[$var]>";
      print "<td><a href=\"fiche.php?id=$objp->projectid\">$objp->title</a></td>\n";
      print "<td><a href=\"fiche.php?id=$objp->projectid\">$objp->ref</a></td>\n";
	  print '<td><a href="'.DOL_URL_ROOT.'/soc.php?socid='.$objp->idp.'">'.$objp->nom.'</a></td>';
      print "</tr>\n";
    
      $i++;
    }
  
  $db->free();
}
else
{
  dolibarr_print_error($db);
}

print "</table>";

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
