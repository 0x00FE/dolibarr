<?PHP
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003 �ric Seigne <erics@rycks.com>
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
  $sortfield="p.name";
}

if ($page < 0) { $page = 0 ; }
$limit = $conf->liste_limit;
$offset = $limit * $page ;

print_barre_liste("Liste des contacts",$page, $PHP_SELF, '', $sortfield, $sortorder);


/*
 *
 * Mode liste
 *
 *
 */


$sql = "SELECT s.idp, s.nom,  st.libelle as stcomm, p.idp as cidp, p.name, p.firstname, p.email, p.phone ";
$sql .= "FROM llx_societe as s, llx_socpeople as p, c_stcomm as st WHERE s.fk_stcomm = st.id AND s.idp = p.fk_soc";

if (strlen($stcomm))  // statut commercial
{
  $sql .= " AND s.fk_stcomm=$stcomm";
}

if (strlen($begin)) // filtre sur la premiere lettre du nom
{
  $sql .= " AND upper(p.name) like '$begin%'";
}

if ($contactname) // acces a partir du module de recherche
{
  $sql .= " AND ( lower(p.name) like '%".strtolower($contactname)."%' OR lower(p.firstname) like '%".strtolower($contactname)."%') ";
  $sortfield = "lower(p.name)";
  $sortorder = "ASC";
}

if ($socid) 
{
  $sql .= " AND s.idp = $socid";
}

$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit( $limit, $offset);

$result = $db->query($sql);

if ($result) 
{
  $num = $db->num_rows();
  $i = 0;
  
  if ($sortorder == "DESC") 
    {
      $sortorder="ASC";
    } 
  else
    {
      $sortorder="DESC";
    }
  print "<p><TABLE border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">";
  print "<TR class=\"liste_titre\">";
  print "<TD>";
  print_liste_field_titre("Nom",$PHP_SELF,"lower(p.name)", $begin);
  print "</td><td>";
  print_liste_field_titre("Pr�nom",$PHP_SELF,"lower(p.firstname)", $begin);
  print "</td><td>";
  print_liste_field_titre("Soci�t�",$PHP_SELF,"lower(s.nom)", $begin);
  print "</td><TD>email</TD>";
  print '<TD>T�l�phone</TD>';
  print "</TR>\n";
  $var=True;
  while ($i < $num) 
    {
      $obj = $db->fetch_object( $i);
    
      $var=!$var;

      print "<TR $bc[$var]>";

      print '<TD><a href="'.DOL_URL_ROOT.'/comm/people.php?contactid='.$obj->cidp.'&socid='.$obj->idp.'">'.$obj->name.'</a></TD>';
      print "<TD>$obj->firstname</TD>";
      
      print '<TD><a href="contact.php?socid='.$obj->idp.'"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filter.png" border="0"></a>&nbsp;';
      print "<a href=\"".DOL_URL_ROOT."/comm/fiche.php?socid=$obj->idp\">$obj->nom</A></td>\n";
      
      print '<td><a href="action/fiche.php?action=create&actionid=4&contactid='.$obj->cidp.'&socid='.$obj->idp.'">'.$obj->email.'</a>&nbsp;</td>';
      
      print '<td><a href="action/fiche.php?action=create&actionid=1&contactid='.$obj->cidp.'&socid='.$obj->idp.'">'.$obj->phone.'</a>&nbsp;</td>';
      
      print "</TR>\n";
      $i++;
    }
  print "</TABLE>";
  $db->free();
} else {
  print $db->error();
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
