<?PHP
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
require("./pre.inc.php");

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socid = $user->societe_id;
}


llxHeader();

if ($sortorder == "")
{
  $sortorder="ASC";
}
if ($sortfield == "")
{
  $sortfield="s.nom";
}

if ($page == -1) { $page = 0 ; }

$offset = $conf->liste_limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

/*
 * Mode Liste
 *
 *
 *
 */
print_barre_liste("Liste des societes", $page, $PHP_SELF);

$sql = "SELECT s.nom, s.idp, c.name, c.firstname, a.titre,n.rowid FROM llx_socpeople as c, llx_action_def as a, llx_notify_def as n, llx_societe as s";
$sql .= " WHERE n.fk_contact = c.idp AND a.rowid = n.fk_action";
$sql .= " AND n.fk_soc = s.idp";

if ($socid > 0) {
  $sql .= " AND s.idp = " . $user->societe_id;
}

$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit($conf->liste_limit, $offset);

$result = $db->query($sql);
if ($result)
{
  $num = $db->num_rows();
  $i = 0;
    

  print "<TABLE border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">";
  print '<TR class="liste_titre">';
  print "<TD valign=\"center\">";
  print_liste_field_titre("Soci�t�",$PHP_SELF,"s.nom");
  print "</td><td>";
  print_liste_field_titre("Contact",$PHP_SELF,"c.name");
  print "</td><td>";
  print_liste_field_titre("Action",$PHP_SELF,"a.titre");
  print "</td></tr>\n";
  $var=True;
  while ($i < $num)
    {
      $obj = $db->fetch_object( $i);
    
      $var=!$var;
    
      print "<TR $bc[$var]>";
      print "<TD><a href=\"fiche.php?socid=$obj->idp\">$obj->nom</A></td>\n";
      print "<td>".$obj->firstname." ".$obj->name."</td>\n";
      print "<td>".$obj->titre."</td>\n";      
      print "</tr>\n";
      $i++;
    }
  print "</TABLE>";
  $db->free();
}
else
{
  print $db->error() . ' ' . $sql;
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
