<?PHP
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
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
require("../../propal.class.php");
//require("../../graph.class.php");

llxHeader();

$mesg = '';

/*
 *
 *
 */

if ($id)
{
  $product = new Product($db);
  $result = $product->fetch($id);
  
  if ( $result )
    { 
      $dir = DOL_DOCUMENT_ROOT."/document/produit/".$product->id;
      if (! file_exists($dir))
	{
	  umask(0);
	  if (! mkdir($dir, 0755))
	    {
	      $mesg = "Impossible de cr�er $dir !";
	    }
	}

      $filenbvente = $dir . "/vente12mois.png";
      $filenbpiece = $dir . "/vendu12mois.png";
        
        if (! file_exists($filenbvente) or $action == 'recalcul')
        {
            $px = new BarGraph();
            $mesg = $px->isGraphKo();
            if (! $mesg) {
                $graph_data = $product->get_num_vente();
                $px->draw($filenbvente, $graph_data);
                $px = new BarGraph();
                $graph_data = $product->get_nb_vente();
                $px->draw($filenbpiece, $graph_data);
                $mesg = "Graphiques g�n�r�s";
            }
        }
        
      print_fiche_titre('Fiche produit : '.$product->ref, $mesg);
      
      print '<table class="border" width="100%" cellspacing="0" cellpadding="4"><tr>';
      print '<td width="20%">R�f�rence</td><td width="40%"><a href="../fiche.php?id='.$product->id.'">'.$product->ref.'</a></td>';
      print '<td>Statistiques</td></tr>';
      print "<tr><td>Libell�</td><td>$product->libelle</td>";
      print '<td valign="top" rowspan="2">';
      print "Propositions commerciales : ".$product->count_propale();
      print "<br>Propos� � <b>".$product->count_propale_client()."</b> clients";
      print '<br><a href="facture.php?id='.$id.'">Factures</a> : '.$product->count_facture();
      print '</td></tr>';
      print '<tr><td>Prix</td><td>'.price($product->price).'</td></tr>';
      print "</table>";

      print '<br><table class="border" width="100%" cellspacing="0" cellpadding="4">';
      print '<tr class="liste_titre"><td width="50%" colspan="2" align="center">Nombre de ventes<br>sur les 12 derniers mois</td>';
      print '<td align="center" width="50%" colspan="2">Nombre de pi�ces vendues</td></tr>';
      print '<tr><td align="center" colspan="2">';

      print '<img src="'.DOL_URL_ROOT.'/document/produit/'.$product->id.'/vente12mois.png" alt="Ventes sur les 12 derniers mois">';
      
      print '</td><td align="center" colspan="2">';
      print '<img src="'.DOL_URL_ROOT.'/document/produit/'.$product->id.'/vendu12mois.png" alt="Ventes sur les 12 derniers mois">';
      
      print '</td></tr><tr>';
      if (file_exists($filenbvente) && filemtime($filenbvente)) {
        print '<td>G�n�r� le '.dolibarr_print_date(filemtime($filenbvente),"%d %b %Y %H:%M:%S").'</td>';
      } else {
        print '<td>Graphique non g�n�r�</td>';
      }
      print '<td align="center">[<a href="fiche.php?id='.$id.'&amp;action=recalcul">Re-calculer</a>]</td>';
      if (file_exists($filenbpiece) && filemtime($filenbpiece)) {
        print '<td>G�n�r� le '.dolibarr_print_date(filemtime($filenbpiece),"%d %b %Y %H:%M:%S").'</td>';
      } else {
        print '<td>Graphique non g�n�r�</td>';
      }
      print '<td align="center">[<a href="fiche.php?id='.$id.'&amp;action=recalcul">Re-calculer</a>]</td>';
      print '</tr></table>';

    }
}
else
{
  print "Error";
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
