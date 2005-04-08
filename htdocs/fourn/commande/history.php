<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/**	        \file       htdocs/fourn/commande/history.php
	        \ingroup    commande
	        \brief      Fiche commande
	        \version    $Revision$
*/

require("./pre.inc.php");

$langs->load("orders");
$langs->load("suppliers");
$langs->load("companies");

$user->getrights('fournisseur');

if (!$user->rights->fournisseur->commande->lire) accessforbidden();

require_once DOL_DOCUMENT_ROOT."/project.class.php";
require_once DOL_DOCUMENT_ROOT."/propal.class.php";
require_once DOL_DOCUMENT_ROOT."/fournisseur.class.php";
require_once DOL_DOCUMENT_ROOT."/fournisseur.commande.class.php";

/*
 *
 */	

llxHeader('',$langs->trans("History"),"Commande");

$html = new Form($db);

/* *************************************************************************** */
/*                                                                             */
/* Mode vue et edition                                                         */
/*                                                                             */
/* *************************************************************************** */

if ($_GET["id"] > 0)
{
  $commande = new CommandeFournisseur($db);
  if ( $commande->fetch($_GET["id"]) == 0)
    {	  
      $soc = new Societe($db);
      $soc->fetch($commande->soc_id);
      $author = new User($db);
      $author->id = $commande->user_author_id;
      $author->fetch();
      
      $h = 0;
      $head[$h][0] = DOL_URL_ROOT.'/fourn/commande/fiche.php?id='.$commande->id;
      $head[$h][1] = $langs->trans("Order").": $commande->ref";
      $h++;

      $head[$h][0] = DOL_URL_ROOT.'/fourn/commande/history.php?id='.$commande->id;
      $head[$h][1] = $langs->trans("History");
      $a = $h;

      $h++;

      dolibarr_fiche_head($head, $a, $soc->nom);	  
      
      /*
       *   Commande
       */

      print '<table class="border" width="100%">';
      print "<tr><td>".$langs->trans("Supplier")."</td>";
      print '<td colspan="2">';
      print '<b><a href="'.DOL_URL_ROOT.'/fourn/fiche.php?socid='.$soc->id.'">'.$soc->nom.'</a></b></td>';
	  
      print '<td width="50%">';
      print '<img src="statut'.$commande->statut.'.png">&nbsp;';
      print $commande->statuts[$commande->statut];
      print "</td></tr>";

      if ($commande->methode_commande_id > 0) 
	{	  
	  print '<tr><td>'.$langs->trans("Date").'</td>';
	  print '<td colspan="2">'.strftime("%A %e %B %Y",$commande->date_commande)."</td>\n";
	  print '<td width="50%">&nbsp;';
	  print "</td></tr>";
	}

      print '<tr><td>'.$langs->trans("Author").'</td><td colspan="2">'.$author->fullname.'</td>';
	
      print '<td>&nbsp;</td></tr>';  
      print "</table>\n";
	  
      /*
       * Historique
       *
       */
      echo '<br><table class="border" width="100%">';	  

      $sql = "SELECT l.fk_statut, ".$db->pdate("l.datelog") ."as dl, u.firstname, u.name";
      $sql .= " FROM ".MAIN_DB_PREFIX."commande_fournisseur_log as l ";
      $sql .= " , ".MAIN_DB_PREFIX."user as u ";
      $sql .= " WHERE l.fk_commande = ".$commande->id." AND u.rowid = l.fk_user";
      $sql .= " ORDER BY l.rowid DESC";
	  
      $resql = $db->query($sql);

      if ($resql)
	{
	  $num = $db->num_rows($resql);
	  $i = 0;

	  $var=True;
	  while ($i < $num)
	    {
	      $obj = $db->fetch_object($resql);
	      print "<tr $bc[$var]>";

	      print '<td width="20%">'.strftime("%a %d %B %Y %H:%M:%S",$obj->dl)."</td>\n";

	      print '<td width="10%"><img src="statut'.$obj->fk_statut.'.png">&nbsp;';

	      print $commande->statuts[$obj->fk_statut]."</td>\n";

	      print '<td width="70%">'.$obj->firstname. " " . $obj->name.'</td>';

	      print "</tr>";
		  

	      $i++;
	      $var=!$var;
	    }	      
	  $db->free($resql);
	} 
      else
	{
	  print $db->error();
	}

      print "</table>";
      print '<br /></div>';
    }
  else
    {
      /* Commande non trouv�e */
      print "Commande inexistante ou acc�s refus�";
    }
}  

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
