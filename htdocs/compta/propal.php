<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Destailleur Laurent  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
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
 
/**
\todo Ce fichier ne semble plus utilis�. A virer ?
*/

require("./pre.inc.php");

$user->getrights('facture');
$user->getrights('propale');
if (!$user->rights->propale->lire)
  accessforbidden();


require("../lib/CMailFile.class.php");
/*
 *  Modules optionnels
 */
require("../project.class.php");
require("../propal.class.php");
require("../actioncomm.class.php");

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}

llxHeader();

if ($action == 'setstatut')
{
  /*
   *  Class�e la facture comme factur�e
   */
  $propal = new Propal($db);
  $propal->id = $propalid;
  $propal->cloture($user->id, $statut, $note);

}

if ( $action == 'delete' )
{
  $sql = "DELETE FROM ".MAIN_DB_PREFIX."propal WHERE rowid = $propalid;";
  if ( $db->query($sql) )
    {

      $sql = "DELETE FROM ".MAIN_DB_PREFIX."propaldet WHERE fk_propal = $propalid ;";
      if ( $db->query($sql) )
	{
	  print "<b><font color=\"red\">Propal supprim�e</font></b>";
	}
      else
	{
	  print $db->error();
	  print "<p>$sql";
	} 
    }
  else
    {
      print $db->error();
      print "<p>$sql";
    }
  $propalid = 0;
  $brouillon = 1;
}

/*
 *
 * Mode fiche
 *
 *
 */
if ($_GET["propalid"])
{
  $propal = new Propal($db);
  $propal->fetch($_GET["propalid"]);

  if ($valid == 1)
    {
      $propal->valid($user->id);
    }
  /*
   *
   */
  print "<table width=\"100%\">";
  print "<tr><td><div class=\"titre\">Proposition commerciale : $propal->ref</div></td>";
  print "</table>";
  /*
   *
   */
  $sql = "SELECT s.nom, s.idp, p.price, p.fk_projet,p.remise, p.tva, p.total, p.ref,".$db->pdate("p.datep")." as dp, c.id as statut, c.label as lst, p.note, x.firstname, x.name, x.fax, x.phone, x.email, p.fk_user_author, p.fk_user_valid, p.fk_user_cloture, p.datec, p.date_valid, p.date_cloture";
  $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."propal as p, ".MAIN_DB_PREFIX."c_propalst as c, ".MAIN_DB_PREFIX."socpeople as x";
  $sql .= " WHERE p.fk_soc = s.idp AND p.fk_statut = c.id AND x.idp = p.fk_soc_contact AND p.rowid = ".$propal->id;

  $result = $db->query($sql);

  if ( $result ) {
    $obj = $db->fetch_object($result);
    
    if ($db->num_rows()) {
            
      $color1 = "#e0e0e0";

      print '<table class="border" width="100%">';

      print '<tr><td>'.$langs->trans("Company").'</td><td colspan="2"><a href="fiche.php?socid='.$obj->idp.'">'.$obj->nom.'</a></td>';
      print "<td valign=\"top\" width=\"50%\" rowspan=\"9\">Note :<br>". nl2br($obj->note)."</td></tr>";
      //

      print '<tr><td>'.$langs->trans("Date").'</td><td colspan="2">'.dolibarr_print_date($obj->dp).'</td></tr>';

      if ($obj->fk_projet) 
	{
	  $projet = new Project($db);
	  $projet->fetch($obj->fk_projet); 
	  print '<tr><td>'.$langs->trans("Project").'</td><td colspan="1">';
	  print '<a href="'.DOL_URL_ROOT.'/projet/fiche.php?id='.$projet->id.'">';
	  print $projet->title.'</a></td></tr>';
	}
      print "<tr><td>Destinataire</td><td colspan=\"2\">$obj->firstname $obj->name &lt;$obj->email&gt;</td></tr>";
      /*
       *
       */

      print "<tr><td bgcolor=\"$color1\">".$langs->trans("AmountHT")."</td><td colspan=\"2\" bgcolor=\"$color1\" align=\"right\">".price($obj->price + $obj->remise)." euros</td></tr>";
      /*
       *
       */

      print "<tr><td bgcolor=\"$color1\">Remise</td><td colspan=\"2\" bgcolor=\"$color1\" align=\"right\">".price($obj->remise)." euros</td></tr>";

      /*
       *
       */

      $totalht = $propal->price ;

      print "<tr><td bgcolor=\"$color1\">".$langs->trans("TotalHT")."</td><td colspan=\"2\" bgcolor=\"$color1\" align=\"right\"><b>".price($totalht)."</b> euros</td></tr>";
      /*
       *
       */
      print '<tr><td>'.$langs->trans("Author").'</td><td colspan="2">';
      $author = new User($db, $obj->fk_user_author);
      $author->fetch('');
      print $author->fullname.'</td></tr>';
      /*
       *
       */
      print "<tr><td>".$langs->trans("Propal")." PDF</a></td>";
      $file = $conf->propal->dir_output. "/$obj->ref/$obj->ref.pdf";
      $relativepath = "$obj->ref/$obj->ref.pdf";

      if (file_exists($file)) {
	    print '<td colspan="2"><a href="'.DOL_URL_ROOT.'/document.php?type=propal&file='.urlencode($relativepath).'">'.$obj->ref.'.pdf</a></td></tr>';
      }
      print '</tr>';
      /*
       *
       */
      print '<tr bgcolor="#f0f0f0"><td>'.$langs->trans("Status").' :</td><td colspan=2 align=center><b>'.$obj->lst.'</b></td>';

      print '</tr>';


      print "</table>";

      if ($action == 'statut')
	{
	  print "<form action=\"propal.php?propalid=".$propal->id."\" method=\"post\">";
	  print "<input type=\"hidden\" name=\"action\" value=\"setstatut\">";
	  print "<select name=\"statut\">";
	  print "<option value=\"2\">Sign�e";
	  print "<option value=\"3\">Non Sign�e";
	  print '</select>';
	  print '<br><textarea cols="60" rows="6" wrap="soft" name="note">';
	  print $obj->note . "\n----------\n";
	  print '</textarea><br><input type="submit" value="Valider">';
	  print "</form>";
	}
      

      print "<table width=\"100%\" cellspacing=2>";
      /*
       *
       */
      print "<td valign=\"top\" width=\"50%\">";
      /*
       * Factures associees
       */
      $sql = "SELECT f.facnumber, f.total,".$db->pdate("f.datef")." as df, f.rowid as facid, f.fk_user_author, f.paye";
      $sql .= " FROM ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."fa_pr as fp WHERE fp.fk_facture = f.rowid AND fp.fk_propal = ".$propal->id;

      $result = $db->query($sql);
      if ($result)
	{
	  $num_fac_asso = $db->num_rows();
	  $i = 0; $total = 0;
	  print "<br>";
	  if ($num_fac_asso > 1)
	    {
	      print_titre("Factures associ�es");
	    }
	  else
	    {
	      print_titre("Facture associ�e");
	    }
	  print '<table class="border" width="100%">';
	  print "<tr>";
	  print '<td>'.$langs->trans("Ref").'</td>';
	  print '<td>'.$langs->trans("Date").'</td>';
	  print '<td>'.$langs->trans("Author").'</td>';
	  print '<td align="right">'.$langs->trans("Price").'</td>';
	  print "</tr>\n";
	  
	  $var=True;
	  while ($i < $num_fac_asso)
	    {
	      $objp = $db->fetch_object();
	      $var=!$var;
	      print "<TR bgcolor=\"#e0e0e0\">";
	      print "<TD><a href=\"../compta/facture.php?facid=$objp->facid\">$objp->facnumber</a>";
	      if ($objp->paye)
		{ 
		  print " (<b>pay&eacute;e</b>)";
		} 
	      print "</TD>\n";
	      print "<TD>".strftime("%d %B %Y",$objp->df)."</td>\n";
	      if ($objp->fk_user_author <> $user->id)
		{
		  $fuser = new User($db, $objp->fk_user_author);
		  $fuser->fetch();
		  print "<td>".$fuser->fullname."</td>\n";
		}
	      else
		{
		  print "<td>".$user->fullname."</td>\n";
		}
	      print '<TD align="right">'.price($objp->total).'</TD>';
	      print "</tr>";
	      $total = $total + $objp->total;
	      $i++;
	    }
	  print "<tr><td align=\"right\" colspan=\"4\">".$langs->trans("TotalHT").": <b>$total</b> ".$conf->monnaie."</td></tr>\n";
	  print "</table>";
	  $db->free();
	}
      print "</table>";
      /*
       * Que si le module commande est actif !
       *
       */
      if($conf->commande->enabled) {
	$nb_commande = sizeof($propal->commande_liste_array());
	if ($nb_commande > 0)
	  {
	    $coms = $propal->commande_liste_array();
	    print '<br><table class="border" width="100%">';
	    
	    if ($nb_commande == 1)
	      
	      {
		print "<tr><td>Commande rattach�e : ";
		print '<a href="'.DOL_URL_ROOT.'/commande/fiche.php?id='.$coms[$i].'">';
		print img_file();
		print '</a>&nbsp;<a href="'.DOL_URL_ROOT.'/commande/fiche.php?id='.$coms[$i].'">'.$coms[$i]."</a>";
		print "</td></tr>\n";
	      }
	    else
	      {
		print "<tr><td>Commandes rattach�es</td></tr>\n";
		
		for ($i = 0 ; $i < $nb_commande ; $i++)
		  {
		    print '<tr><td><a href="'.DOL_URL_ROOT.'/commande/fiche.php?id='.$coms[$i].'">'.$coms[$i]."</a></td>\n";
		    print "</tr>\n";
		  }
	      }
	    print "</table>";
	  }
      }

      /*
       *
       * Actions
       *
       */
      if ($obj->statut <> 4 && $user->societe_id == 0)
	{  
	  print '<br><table id="actions" width="100%"><tr>';
	  
	  if ($obj->statut == 2 && $user->rights->facture->creer)
	    {
	      print '<td width="20%">';
	      print "<a href=\"facture.php?propalid=".$propal->id."&action=create\">Emettre une facture</td>";
	    }
	  else
	    {
	      print '<td width="20%">-</td>';
	    }
	  
	  print '<td width="20%">-</td>';
	  print '<td width="20%">-</td>';
	  print '<td width="20%">-</td>';
	  
	  if ($obj->statut == 2 && $num_fac_asso)
	    {
	      print "<td width=\"20%\">[<a href=\"propal.php?propalid=".$propal->id."&action=setstatut&statut=4\">Factur�e</a>]</td>";
	    }
	  else	
	    {
	      print '<td width="20%">-</td>';
	    }
	  print "</tr></table>";
	}
      /*
       *
       */
      
    } else {
      print "Num rows = " . $db->num_rows();
      print "<p><b>$sql";
    }

    /*
     * Produits
     */
    print_titre("Produits");
    
    print '<table class="noborder" width="100%">';
    print "<tr class=\"liste_titre\">";
    print "<td>".$langs->trans("Ref")."</td><td>Produit</td>";
    print '<td align="right">'.$langs->trans("Price").'</td><td align="center">Remise</td><td align="center">Qt�.</td></tr>';
    
    $sql = "SELECT pt.rowid, p.label as product, p.ref, pt.price, pt.qty, p.rowid as prodid, pt.remise_percent";
    $sql .= " FROM ".MAIN_DB_PREFIX."propaldet as pt, ".MAIN_DB_PREFIX."product as p WHERE pt.fk_product = p.rowid AND pt.fk_propal =".$propal->id;
    $sql .= " ORDER BY pt.rowid ASC";
    if ($db->query($sql)) 
      {
	$num = $db->num_rows();
	$i = 0;	
	$var=True;	
	while ($i < $num) 
	  {
	    $objp = $db->fetch_object();
	    $var=!$var;
	    print "<tr $bc[$var]><td>[$objp->ref]</TD>\n";
	    print '<td><a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$objp->prodid.'">'.$objp->product.'</td>';
	    print "<td align=\"right\">".price($objp->price)."</TD>";
	    print '<td align="center">'.$objp->remise_percent.' %</td>';
	    print "<td align=\"center\">".$objp->qty."</td></tr>\n";
	    $i++;
	  }
      }

    $sql = "SELECT pt.rowid, pt.description as product,  pt.price, pt.qty, pt.remise_percent";
    $sql .= " FROM ".MAIN_DB_PREFIX."propaldet as pt  WHERE  pt.fk_propal = ".$propal->id." AND pt.fk_product = 0";
    $sql .= " ORDER BY pt.rowid ASC";
    if ($db->query($sql)) 
      {
	$num = $db->num_rows();
	$i = 0;	
	while ($i < $num) 
	  {
	    $objp = $db->fetch_object();
	    $var=!$var;
	    print "<tr $bc[$var]><td>&nbsp;</td>\n";
	    print '<td>'.$objp->product.'</td>';
	    print '<td align="right">'.price($objp->price).'</td>';
	    print '<td align="center">'.$objp->remise_percent.' %</td>';
	    print "<td align=\"center\">".$objp->qty."</td></tr>\n";
	    $i++;
	  }
      }
    else
      {
	print $sql;
      }

    print '</table>';
    

    /*
     * Voir le suivi des actions
     *
     *
     *
     */
    if ($suivi)
      {
	$validor = new User($db, $obj->fk_user_valid);
	$validor->fetch('');
	$cloturor = new User($db, $obj->fk_user_cloture);
	$cloturor->fetch('');
	
	print '<p><a href="propal.php?propalid='.$propal->id.'">Cacher le suivi des actions </a>';
	print '<table cellspacing=0 border=1 cellpadding=3>';
	print '<tr><td>&nbsp;</td><td>Nom</td><td>Date</td></tr>';
	print '<tr><td>Cr�ation</td><td>'.$author->fullname.'</td>';
	print '<td>'.$obj->datec.'</td></tr>';
	
	print '<tr><td>Validation</td><td>'.$validor->fullname.'&nbsp;</td>';
	print '<td>'.$obj->date_valid.'&nbsp;</td></tr>';
	
	print '<tr><td>Cloture</td><td>'.$cloturor->fullname.'&nbsp;</td>';
	print '<td>'.$obj->date_cloture.'&nbsp;</td></tr>';      
	print '</table>';
      }
    else
      {
	print '<p><a href="propal.php?propalid='.$propal->id.'&suivi=1">Voir le suivi des actions </a>';
      }
    
  } else {
    print $db->error();
    print "<p><b>$sql";
  }
  

  /*
   *
   *
   *
   */
} else {
  /*
   *
   * Mode Liste des propales
   *
   */

  if ($sortfield == "")
    {
      $sortfield="p.datep";
    }
  if ($sortorder == "")
    {
      $sortorder="DESC";
    }

  if ($page == -1)
    {
      $page = 0 ;
    }

  $pageprev = $page - 1;
  $pagenext = $page + 1;
  $limit = $conf->liste_limit;
  $offset = $limit * $page ;

  $sql = "SELECT s.nom, s.idp, p.rowid as propalid, p.price, p.ref,".$db->pdate("p.datep")." as dp, c.label as statut, c.id as statutid";
  $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."propal as p, ".MAIN_DB_PREFIX."c_propalst as c ";
  $sql .= " WHERE p.fk_soc = s.idp AND p.fk_statut = c.id AND p.fk_statut in(2,4)";

  if ($socidp)
    { 
      $sql .= " AND s.idp = $socidp"; 
    }

  if ($viewstatut <> '')
    {
      $sql .= " AND c.id = $viewstatut"; 
    }

  if ($month > 0)
    {
      $sql .= " AND date_format(p.datep, '%Y-%m') = '$year-$month'";
    }
  
  if ($year > 0)
    {
      $sql .= " AND date_format(p.datep, '%Y') = $year";
    }
  
  $sql .= " ORDER BY $sortfield $sortorder, p.rowid DESC ";
  $sql .= $db->plimit($limit + 1,$offset);

  if ( $db->query($sql) )
    {
      $num = $db->num_rows();

      print_barre_liste("Propositions commerciales", $page, "propal.php","&socidp=$socidp",$sortfield,$sortorder,'',$num);

      $i = 0;
      print "<table class=\"noborder\" width=\"100%\">";
      print '<tr class="liste_titre">';
      print_liste_field_titre($langs->trans("Ref"),"propal.php","p.ref","","&year=$year&viewstatut=$viewstatut",'',$sortfield);
      print_liste_field_titre($langs->trans("Company"),"propal.php","s.nom","&viewstatut=$viewstatut","",'',$sortfield);
      print_liste_field_titre($langs->trans("Date"),"propal.php","p.datep","&viewstatut=$viewstatut","",'align="right" colspan="2"',$sortfield);
      print_liste_field_titre($langs->trans("Price"),"propal.php","p.price","&viewstatut=$viewstatut","",'align="right"',$sortfield);
      print_liste_field_titre($langs->trans("Status"),"propal.php","p.fk_statut","&viewstatut=$viewstatut","",'align="center"',$sortfield);
      print "</tr>\n";

      while ($i < min($num, $limit))
	{
	  $objp = $db->fetch_object();
	
	  $var=!$var;
	  print "<tr $bc[$var]>";
	  print "<td><a href=\"propal.php?propalid=$objp->propalid\">$objp->ref</a></td>\n";
	  print "<td><a href=\"fiche.php?socid=$objp->idp\">$objp->nom</a></td>\n";
	  
	  $now = time();
	  $lim = 3600 * 24 * 15 ;
	  
	  if ( ($now - $objp->dp) > $lim && $objp->statutid == 1 )
	    {
	      print "<td><b> &gt; 15 jours</b></td>";
	    }
	  else
	    {
	      print "<td>&nbsp;</td>";
	    }
	
	  print "<td align=\"right\">";
	  $y = strftime("%Y",$objp->dp);
	  $m = strftime("%m",$objp->dp);
	  
	  print strftime("%d",$objp->dp)."\n";
	  print " <a href=\"propal.php?year=$y&month=$m\">";
	  print strftime("%B",$objp->dp)."</a>\n";
	  print " <a href=\"propal.php?year=$y\">";
	  print strftime("%Y",$objp->dp)."</a></td>\n";      
	  
	  print "<td align=\"right\">".price($objp->price)."</td>\n";
	  print "<td align=\"center\">$objp->statut</td>\n";
	  print "</tr>\n";
	  
	  $i++;
	}
      
      print "</table>";
      $db->free();
    }
  else
    {
      print $db->error();
      print "<br>$sql";
    }
}
$db->close();
llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
