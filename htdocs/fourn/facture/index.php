<?php
/* Copyright (C) 2002-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
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
require("../../contact.class.php");
require("../../facturefourn.class.php");

llxHeader();

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $_GET["action"] = '';
  $socid = $user->societe_id;
}

if ($_GET["action"] == 'delete')
{
  $fac = new FactureFourn($db);
  $fac->delete($_GET["facid"]);
  
  $facid = 0 ;
}


if ($page == -1) { $page = 0 ; }
$limit = $conf->liste_limit;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;


/*
 * Recherche
 *
 *
 */
if ($mode == 'search')
{
  if ($mode-search == 'soc')
    {
      $sql = "SELECT s.idp FROM ".MAIN_DB_PREFIX."societe as s ";
      $sql .= " WHERE lower(s.nom) like '%".strtolower($socname)."%'";
    }
      
  if ( $db->query($sql) )
    {
      if ( $db->num_rows() == 1)
	{
	  $obj = $db->fetch_object(0);
	  $socid = $obj->idp;
	}
      $db->free();
    }
}
  
/*
 * Mode Liste
 *
 */
 
$sortorder = $_GET["sortorder"];
$sortfield = $_GET["sortfield"];
 
if ($sortorder == "")
{
  $sortorder="DESC";
}
if ($sortfield == "")
{
  $sortfield="fac.datef";
}


$sql = "SELECT s.idp as socid, s.nom, ".$db->pdate("s.datec")." as datec, ".$db->pdate("s.datea")." as datea,  s.prefix_comm, fac.total_ht, fac.total_ttc, fac.paye as paye, fac.fk_statut as fk_statut, fac.libelle, ".$db->pdate("fac.datef")." as datef, fac.rowid as facid, fac.facnumber";
$sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."facture_fourn as fac ";
$sql .= " WHERE fac.fk_soc = s.idp";

if ($socid)
{
  $sql .= " AND s.idp = $socid";
}
if ($_GET["filtre"])
  {
    $filtrearr = split(",", $_GET["filtre"]);
    foreach ($filtrearr as $fil)
      {
	$filt = split(":", $fil);
	$sql .= " AND " . $filt[0] . " = " . $filt[1];
      }
  }

$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit( $limit+1, $offset);

$result = $db->query($sql);

if ($result)
{
  $num = $db->num_rows();
  $i = 0;
  
  print_barre_liste("Liste des factures fournisseurs", $page, "index.php",'', $sortfield, $sortorder,'',$num);


  print '<table class="noborder" width="100%" cellspacing="0" cellpadding="3">';
  print '<tr class="liste_titre">';
  print '<td>';
  print_liste_field_titre("Num�ro","index.php","facnumber");
  print '</td>';
  print '<td>';
  print_liste_field_titre("Date","index.php","fac.datef");
  print '</td>';
  print '<td>'.$langs->trans("Label").'</td>';
  print '<td>';
  print_liste_field_titre("Soci�t�","index.php","s.nom");
  print '</td>';
  print '<td align="right">';
  print_liste_field_titre("Montant HT","index.php","fac.total_ht");
  print '</td>';
  print '<td align="right">';
  print_liste_field_titre("Montant TTC","index.php","fac.total_ttc");
  print '</td>';
  print '<td align="center">';
  print_liste_field_titre($langs->trans("Status"),"index.php","fk_statut,paye");
  print '</td>';
  print "</tr>\n";
  $var=True;
  while ($i < min($num,$limit))
    {
      $obj = $db->fetch_object($i);      
      $var=!$var;
      
      print "<tr $bc[$var]>";
      print "<td><a href=\"fiche.php?facid=$obj->facid\">".img_file()."</a>\n";
      print "&nbsp;<a href=\"fiche.php?facid=$obj->facid\">$obj->facnumber</A></td>\n";
      print "<td>".strftime("%d %b %Y",$obj->datef)."</td>\n";
      print '<td>'.stripslashes("$obj->libelle").'</td>';
      print "<td><a href=\"../fiche.php?socid=$obj->socid\">$obj->nom</A></td>\n";
      print '<td align="right">'.price($obj->total_ht).'</td>';
      print '<td align="right">'.price($obj->total_ttc).'</td>';
      // Affiche statut de la facture
      if ($obj->paye)
	{
	  $class = "normal";
	}
      else
	{
	  if ($obj->fk_statut == 0)
	    {
	      $class = "normal";
	    }
	  else
	    {
	      $class = "impayee";
	    }
	}
      if (! $obj->paye)
        {
          if ($obj->fk_statut == 0)
            {
	      print '<td align="center">brouillon</td>';
            }
          elseif ($obj->fk_statut == 3)
            {
	      print '<td align="center">annul�e</td>';
            }
          else
            {
	      print '<td align="center"><a class="'.$class.'" href=""index.php?filtre=paye:0,fk_statut:1">'.($obj->am?"commenc�":"impay�e").'</a></td>';
            }
        }
      else
        {
          print '<td align="center">pay�e</td>';
        }
      
      print "</tr>\n";
      $i++;
    }
    print "</table>";
    $db->free();
}
else
{
  print $db->error();
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
