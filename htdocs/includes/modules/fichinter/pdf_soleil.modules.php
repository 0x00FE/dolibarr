<?php
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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 *
 */

/*!	\file htdocs/includes/modules/fichinter/pdf_soleil.modules.php
		\ingroup    ficheinter
		\brief      Fichier de la classe permettant de g�n�rer les fiches d'intervention au mod�le Soleil
		\version    $Revision$
*/


/*!	\class pdf_soleil
		\brief  Classe permettant de g�n�rer les fiches d'intervention au mod�le Soleil
*/

class pdf_soleil extends ModelePDFFicheinter
{

    /*!		\brief  Constructeur
    		\param	db		handler acc�s base de donn�e
    */
  function pdf_soleil($db=0)
    { 
      $this->db = $db;
      $this->description = "Mod�le de fiche d'intervention stantdard";
    }

    /*!
    		\brief  Fonction g�n�rant la fiche d'intervention sur le disque
    		\param	id		id de la fiche intervention � g�n�rer
    */
  function write_pdf_file($id)
    {
      global $conf;

      $fich = new Fichinter($this->db,"",$id);
      if ($fich->fetch($id))
	{
      $dir = $conf->fichinter->dir_output . "/" . $fich->ref . "/" ;
      $file = $dir . $fich->ref . ".pdf";
	      
	  if (! file_exists($dir))
		{
		  umask(0);
		  mkdir($dir, 0755);
		}
	  
	  if (file_exists($dir))
	    {

	      $pdf=new FPDF('P','mm','A4');
	      $pdf->Open();
	      $pdf->AddPage();
	      
	      $pdf->SetXY(10,5);
	      if (defined("FAC_PDF_INTITULE"))
		{
		  $pdf->SetTextColor(0,0,200);
		  $pdf->SetFont('Arial','B',14);
		  $pdf->MultiCell(60, 8, FAC_PDF_INTITULE, 0, 'L');
		}
	      
	      $pdf->SetTextColor(70,70,170);
	      if (defined("FAC_PDF_ADRESSE"))
		{
		  $pdf->SetFont('Arial','',12);
		  $pdf->MultiCell(40, 5, FAC_PDF_ADRESSE);
		}
	      if (defined("FAC_PDF_TEL"))
		{
		  $pdf->SetFont('Arial','',10);
		  $pdf->MultiCell(40, 5, "T�l : ".FAC_PDF_TEL);
		}  
	      if (defined("MAIN_INFO_SIREN"))
		{
		  $pdf->SetFont('Arial','',10);
		  $pdf->MultiCell(40, 5, "SIREN : ".MAIN_INFO_SIREN);
		}  
	      
	      if (defined("FAC_PDF_INTITULE2"))
		{
		  $pdf->SetXY(100,5);
		  $pdf->SetFont('Arial','B',14);
		  $pdf->SetTextColor(0,0,200);
		  $pdf->MultiCell(100, 10, FAC_PDF_INTITULE2, '' , 'R');
		}
	      /*
	       * Adresse Client
	       */
	      $pdf->SetTextColor(0,0,0);
	      $pdf->SetFont('Arial','B',12);
	      $fich->fetch_client();
	      $pdf->SetXY(102,42);
	      $pdf->MultiCell(66,5, $fich->client->nom);
	      $pdf->SetFont('Arial','B',11);
	      $pdf->SetXY(102,47);
	      $pdf->MultiCell(66,5, $fich->client->adresse . "\n" . $fich->client->cp . " " . $fich->client->ville);
	      $pdf->rect(100, 40, 100, 40);
	      
	      
	      $pdf->SetTextColor(200,0,0);
	      $pdf->SetFont('Arial','B',14);
	      $pdf->Text(11, 88, "Date : " . strftime("%d %b %Y", $fich->date));
	      $pdf->Text(11, 94, "Fiche d'intervention : ".$fich->ref);
	      
	      /*
	       */

	      /*
	       */
	      $pdf->SetFillColor(220,220,220);
	      $pdf->SetTextColor(0,0,0);
	      $pdf->SetFont('Arial','',12);
	      
	      $tab_top = 100;
	      $tab_height = 110;

	      $pdf->SetXY (10, $tab_top);
	      $pdf->MultiCell(190,8,'D�signation',0,'L',0);
	      $pdf->line(10, $tab_top + 8, 200, $tab_top + 8 );	      
	      
	      $pdf->Rect(10, $tab_top, 190, $tab_height);	      
	      /*
	       *
	       */  	      	      
	      $pdf->SetFont('Arial','', 10);

	      $pdf->SetXY (10, $tab_top + 8 );
	      $pdf->MultiCell(190, 5, $fich->note, 0, 'J', 0);
		  
	      /*
	       *
	       */
	      $pdf->Output($file);	      
	    }
	}
    }
}

?>
