<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 *
 */

/**    	\file       htdocs/includes/modules/facture/pdf_bigorneau.modules.php
		\ingroup    facture
		\brief      Fichier de la classe permettant de g�n�rer les factures au mod�le Bigorneau
		\author	    Laurent Destailleur
		\version    $Revision$
*/


/**    	\class      pdf_bigorneau
		\brief      Classe permettant de g�n�rer les factures au mod�le Bigorneau
*/

class pdf_bigorneau extends ModelePDFFactures {

  function pdf_bigorneau($db=0)
    { 
      $this->db = $db;
      $this->description = "Mod�le de facture sans boite info r�glement";
    }

  function write_pdf_file($facid)
    {
      global $user,$langs,$conf;
      
        $langs->load("main");
        $langs->load("bills");
        $langs->load("products");

      $fac = new Facture($this->db,"",$facid);
      $fac->fetch($facid);  
        if ($conf->facture->dir_output)
	{

			$forbidden_chars=array("/","\\",":","*","?","\"","<",">","|","[","]",",",";","=");
			$facref = str_replace($forbidden_chars,"_",$fac->ref);
			$dir = $conf->facture->dir_output . "/" . $facref . "/" ;
			$file = $dir . $facref . ".pdf";
	  
	  if (! file_exists($dir))
	    {
	      umask(0);
	      if (! mkdir($dir, 0755))
		{
                    $this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
                    return 0;
		}
	    }
	  
	  if (file_exists($dir))
	    {
                // Initialisation facture vierge
	      $pdf=new FPDF('P','mm','A4');
	      $pdf->Open();
	      $pdf->AddPage();

	      $this->_pagehead($pdf, $fac);

	      $pdf->SetTitle($fac->ref);
                $pdf->SetSubject($langs->trans("Bill"));
	      $pdf->SetCreator("Dolibarr ".DOL_VERSION);
	      $pdf->SetAuthor($user->fullname);
	      
	      $tab_top = 100;
	      $tab_height = 110;      	      

	      /*
	       *
	       */  
	      
	      $pdf->SetFillColor(220,220,220);
	      
	      $pdf->SetFont('Arial','', 10);

	      $pdf->SetXY (10, $tab_top + 10 );

	      $iniY = $pdf->GetY();
	      $curY = $pdf->GetY();
	      $nexY = $pdf->GetY();
	      $nblignes = sizeof($fac->lignes);

	      for ($i = 0 ; $i < $nblignes ; $i++)
		{
		  $curY = $nexY;

		  $pdf->SetXY (11, $curY );
		  $pdf->MultiCell(118, 5, $fac->lignes[$i]->desc, 0, 'J');

		  $nexY = $pdf->GetY();
		  
		  $pdf->SetXY (133, $curY);
		  $pdf->MultiCell(10, 5, $fac->lignes[$i]->tva_taux, 0, 'C');
		  
		  $pdf->SetXY (145, $curY);
		  $pdf->MultiCell(10, 5, $fac->lignes[$i]->qty, 0, 'C');
		  
		  $pdf->SetXY (156, $curY);
		  $pdf->MultiCell(18, 5, price($fac->lignes[$i]->price), 0, 'R', 0);
	      
		  $pdf->SetXY (174, $curY);
		  $total = price($fac->lignes[$i]->price * $fac->lignes[$i]->qty);
		  $pdf->MultiCell(26, 5, $total, 0, 'R', 0);


		  if ($nexY > 200 && $i < $nblignes - 1)
		    {
		      $this->_tableau($pdf, $tab_top, $tab_height, $nexY);
		      $pdf->AddPage();
		      $nexY = $iniY;
		      $this->_pagehead($pdf, $fac);
		      $pdf->SetTextColor(0,0,0);
		      $pdf->SetFont('Arial','', 10);
		    }
		  
		}
	      $this->_tableau($pdf, $tab_top, $tab_height, $nexY);
	      /*
	       *
	       */
	      
	      $tab2_top = 212;
	      $tab2_height = 24;
	      $pdf->SetFont('Arial','', 12);
	      
	      $pdf->Rect(132, $tab2_top, 68, $tab2_height);
	      
	      $pdf->line(132, $tab2_top + $tab2_height - 24, 200, $tab2_top + $tab2_height - 24 );
	      $pdf->line(132, $tab2_top + $tab2_height - 16, 200, $tab2_top + $tab2_height - 16 );
	      $pdf->line(132, $tab2_top + $tab2_height - 8, 200, $tab2_top + $tab2_height - 8 );
	      
	      $pdf->line(174, $tab2_top, 174, $tab2_top + $tab2_height);
	      
	      $pdf->SetXY (132, $tab2_top + 0);
	      $pdf->MultiCell(42, 8, "Total HT", 0, 'R', 0);
	      
	      $pdf->SetXY (132, $tab2_top + 8);
	      $pdf->MultiCell(42, 8, "Total TVA", 0, 'R', 0);
	      
	      $pdf->SetXY (132, $tab2_top + 16);
	      $pdf->MultiCell(42, 8, "Total TTC", 1, 'R', 1);
	      
	      $pdf->SetXY (174, $tab2_top + 0);
	      $pdf->MultiCell(26, 8, price($fac->total_ht), 0, 'R', 0);
	  
	      $pdf->SetXY (174, $tab2_top + 8);
	      $pdf->MultiCell(26, 8, price($fac->total_tva), 0, 'R', 0);
	  
	      $pdf->SetXY (174, $tab2_top + 16);
	      $pdf->MultiCell(26, 8, price($fac->total_ttc), 1, 'R', 1);
	  
	      /*
	       *
	       */
	      
	      /*
	       *
	       */
	      if (defined("FACTURE_RIB_NUMBER"))
		{
		  if (FACTURE_RIB_NUMBER > 0)
		    {
		      $account = new Account($this->db);
		      $account->fetch(FACTURE_RIB_NUMBER);
		      
		      $pdf->SetXY (10, 40);		  
		      $pdf->SetFont('Arial','U',8);
		      $pdf->MultiCell(40, 4, "Coordonn�es bancaire", 0, 'L', 0);
		      $pdf->SetFont('Arial','',8);
		      $pdf->MultiCell(40, 4, "Code banque : " . $account->code_banque, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, "Code guichet : " . $account->code_guichet, 0, 'L', 0);
		      $pdf->MultiCell(50, 4, "Num�ro compte : " . $account->number, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, "Cl� RIB : " . $account->cle_rib, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, "Domiciliation : " . $account->domiciliation, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, "Prefix IBAN : " . $account->iban_prefix, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, "BIC : " . $account->bic, 0, 'L', 0);
		    }
		}
	      
	      /*
	       *
	       *
	       */
	      
	      $pdf->SetFont('Arial','U',12);
	      $pdf->SetXY(10, 220);
	      $titre = "Conditions de r�glement : ".$fac->cond_reglement_facture;
	      $pdf->MultiCell(190, 5, $titre, 0, 'J');
	      
	      $pdf->SetFont('Arial','',9);
	      $pdf->SetXY(10, 265);
	      $pdf->MultiCell(190, 5, "Accepte le r�glement des sommes dues par ch�ques libell�s � mon nom en ma qualit� de Membre d'une Association de Gestion agr��e par l'Administration Fiscale.", 0, 'J');

	      $pdf->Close();
	      
	      $pdf->Output($file);
	      return 1;
	    }
	  else
	    {
                    $this->error="Erreur: Le r�pertoire '$dir' n'existe pas et Dolibarr n'a pu le cr�er.";
                    return 0;
	    }
	}
      else
	{
            $this->error="Erreur: FAC_OUTPUTDIR non d�fini !";
            return 0;
	}
    }
  /*
   *
   *
   *
   */
  function _tableau(&$pdf, $tab_top, $tab_height, $nexY)
    {
        global $langs;
        $langs->load("main");
        $langs->load("bills");
        
      $pdf->SetFont('Arial','',12);
      
      $pdf->Text(11,$tab_top + 5,$langs->trans("Designation"));
      
      $pdf->line(132, $tab_top, 132, $tab_top + $tab_height);
      $pdf->Text(134,$tab_top + 5,$langs->trans("VAT"));
      
      $pdf->line(144, $tab_top, 144, $tab_top + $tab_height);
      $pdf->Text(147,$tab_top + 5,$langs->trans("Qty"));
      
      $pdf->line(156, $tab_top, 156, $tab_top + $tab_height);
      $pdf->Text(160,$tab_top + 5,$langs->trans("PriceU"));
      
      $pdf->line(174, $tab_top, 174, $tab_top + $tab_height);
      $pdf->Text(187,$tab_top + 5,$langs->trans("Total"));
      
      $pdf->Rect(10, $tab_top, 190, $tab_height);
      $pdf->line(10, $tab_top + 10, 200, $tab_top + 10 );
    }
  /*
   *
   *
   *
   *
   */
  function _pagehead(&$pdf, $fac)
    {
      
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
      $fac->fetch_client();
      $pdf->SetXY(102,42);
      $pdf->MultiCell(66,5, $fac->client->nom);
      $pdf->SetFont('Arial','B',11);
      $pdf->SetXY(102,47);
      $pdf->MultiCell(66,5, $fac->client->adresse . "\n" . $fac->client->cp . " " . $fac->client->ville);
      $pdf->rect(100, 40, 100, 40);
      
      
      $pdf->SetTextColor(200,0,0);
      $pdf->SetFont('Arial','B',14);
      $pdf->Text(11, 88, "Date : " . strftime("%d %b %Y", $fac->date));
      $pdf->Text(11, 94, "Facture : ".$fac->ref);
      
      /*
       */
      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',10);
      $titre = "Montants exprim�s en euros";
      $pdf->Text(200 - $pdf->GetStringWidth($titre), 98, $titre);
      /*
       */
      
    }
  
}

?>
