<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003 Jean-Louis Bergamo <jlb@j1b.org>
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

/*! \file htdocs/adherents/cartes/carte.php
        \ingroup    adherent
		\brief      Page de creation d'une carte PDF
		\version    $Revision$
*/

require("./pre.inc.php");
require(DOL_DOCUMENT_ROOT."/adherents/adherent.class.php");

require_once('PDF_card.class.php');

// liste des patterns remplacable dans le texte a imprimer
$patterns = array (
		   '/%PRENOM%/',
		   '/%NOM%/',
		   '/%SERVEUR%/',
		   '/%SOCIETE%/',
		   '/%ADRESSE%/',
		   '/%CP%/',
		   '/%VILLE%/',
		   '/%PAYS%/',
		   '/%EMAIL%/',
		   '/%NAISS%/',
		   '/%PHOTO%/',
		   '/%TYPE%/',
		   '/%ID%/',
		   '/%ANNEE%/'
		   );
/*
 *-------------------------------------------------
 * Pour cr�er l'objet on a 2 moyens :
 * Soit on donne les valeurs en les passant dans un tableau (sert pour un format personnel)
 * Soit on donne le type d'�tiquette au format AVERY
 *-------------------------------------------------
*/

//$pdf = new PDF_Label(array('name'=>'perso1', 'marginLeft'=>1, 'marginTop'=>1, 'NX'=>2, 'NY'=>7, 'SpaceX'=>0, 'SpaceY'=>0, 'width'=>99.1, 'height'=>'38.1', 'metric'=>'mm', 'font-size'=>14), 1, 2);
$pdf = new PDF_card('CARD', 1, 1);

$pdf->Open();
$pdf->AddPage();

// Choix de l'annee d'impression ou annee courante.
if (!isset($annee)){
  $now = getdate();
  $annee=$now['year'];
}

// requete en prenant que les adherents a jour de cotisation
$sql = "SELECT d.rowid, d.prenom, d.nom, d.societe, ".$db->pdate("d.datefin")." as datefin, adresse,cp,ville,pays, t.libelle as type, d.naiss, d.email, d.photo";
$sql .= " FROM ".MAIN_DB_PREFIX."adherent as d, ".MAIN_DB_PREFIX."adherent_type as t";
$sql .= " WHERE d.fk_adherent_type = t.rowid AND d.statut = 1 AND datefin > now()";
$sql .= " ORDER BY d.rowid ASC ";

$result = $db->query($sql);
if ($result) 
{
  $num = $db->num_rows();
  $i = 0;
  while ($i < $num)
    {
      $objp = $db->fetch_object($result);
      // attribut a remplacer
      $replace = array (
			ucfirst(strtolower($objp->prenom)),
			strtoupper($objp->nom),
			"http://".$_SERVER["SERVER_NAME"]."/",
			$objp->societe,
			ucwords(strtolower($objp->adresse)),
			$objp->cp,
			strtoupper($objp->ville),
			ucfirst(strtolower($objp->pays)),
			$objp->email,
			$objp->naiss,
			$objp->photo,
			$objp->type,
			$objp->rowid,
			$annee
			);
      // imprime le texte specifique sur la carte
      //$pdf->Add_PDF_card(sprintf("%s\n%s\n%s\n%s\n%s, %s\n%s", $objp->type." n� ".$objp->rowid,ucfirst(strtolower($objp->prenom))." ".strtoupper($objp->nom),"<".$objp->email.">", ucwords(strtolower($objp->adresse)), $objp->cp, strtoupper($objp->ville), ucfirst(strtolower($objp->pays))),$annee,"Association FreeLUG http://www.freelug.org/");
      $pdf->Add_PDF_card(preg_replace ($patterns, $replace, ADHERENT_CARD_TEXT),preg_replace ($patterns, $replace, ADHERENT_CARD_HEADER_TEXT),preg_replace ($patterns, $replace, ADHERENT_CARD_FOOTER_TEXT));
      $i++;
    }

  $db->close();
  $pdf->Output();
}else{
  llxHeader();
  print "Erreur de la base de donn�es";
  llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
}
?> 
