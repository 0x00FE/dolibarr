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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 */

/**
   \file       htdocs/includes/modules/facture/pdf_bernique.modules.php
   \ingroup    facture
   \brief      Fichier de la classe permettant de g�n�rer les factures au mod�le Bernique
   \author	    Laurent Destailleur
   \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/facture/modules_facture.php");


/** 	
    \class      pdf_bernique
    \brief      Classe permettant de g�n�rer les factures au mod�le Bernique
*/

class pdf_bernique extends ModelePDFFactures  {

  function pdf_bernique($db=0)
    { 
        global $langs;
		  
		  $langs->load("main");
		  $langs->load("bills");
		  $langs->load("products");
		  
		  $this->db = $db;
        $this->description = $langs->trans('PDFBerniqueDescription');

        // Dimension page pour format A4
        $this->page_largeur = 210;
        $this->page_hauteur = 297;
        $this->format = array($this->page_largeur,$this->page_hauteur);
    }

  function write_pdf_file($facid)
    {
      global $user,$langs,$conf;
      
      $fac = new Facture($this->db,"",$facid);
      $fac->fetch($facid);  
        if ($conf->facture->dir_output)
	{
	  
	  $facref = sanitize_string($fac->ref);
	  $dir = $conf->facture->dir_output . "/" . $facref . "/" ;
	  $file = $dir . $facref . ".pdf";
	  
	  if (! file_exists($dir))
        {
            if (create_exdir($dir) < 0)
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
	      
	      $pdf->SetFont('Arial','', 9);

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

	      $this->_tableau_tot($pdf, $fac, $tab_top, $tab_height);
	  
	      $this->_tableau_compl($pdf, $fac);	      

	      /*
	       *
	       */
	      if (defined("FACTURE_RIB_NUMBER"))
		{
		  if (FACTURE_RIB_NUMBER > 0)
		    {
		      $account = new Account($this->db);
		      $account->fetch(FACTURE_RIB_NUMBER);
		      
		      $pdf->SetXY (10, 49);  
		      $pdf->SetFont('Arial','U',8);
		      $pdf->MultiCell(40, 4, $langs->trans("BankDetails"), 0, 'L', 0);
		      $pdf->SetFont('Arial','',8);
		      $pdf->MultiCell(40, 4, $langs->trans("BankCode").' : ' . $account->code_banque, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, $langs->trans("DeskCode").' : ' . $account->code_guichet, 0, 'L', 0);
		      $pdf->MultiCell(50, 4, $langs->trans("BankAccountNumber").' : ' . $account->number, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, $langs->trans("BankAccountNumberKey").' : ' . $account->cle_rib, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, $langs->trans("Residence").' : ' . $account->domiciliation, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, $langs->trans("IbanPrefix").' : ' . $account->iban_prefix, 0, 'L', 0);
		      $pdf->MultiCell(40, 4, $langs->trans("BIC").' : ' . $account->bic, 0, 'L', 0);
		    }
		}
	      
	      /*
	       *
	       *
	       */
	      	      
	      $pdf->SetFont('Arial','',9);
	      $pdf->SetXY(10, 260);
	      $pdf->MultiCell(190, 5, $langs->trans("IntracommunityVATNumber").' : '.MAIN_INFO_TVAINTRA, 0, 'J');
	      $pdf->MultiCell(190, 5, $langs->trans("PrettyLittleSentence"), 0, 'J');

	      $pdf->Close();
	      
	      $pdf->Output($file);

	      return 1;
	    }
	  else
	    {
                $this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
                return 0;
	    }
	}
      else
	{
            $this->error=$langs->trans("ErrorConstantNotDefined","FAC_OUTPUTDIR");
            return 0;
	}
    }
  /*
   *
   *
   *
   */
  function _tableau_compl(&$pdf, $fac)
    {
    	global $langs;
    	$langs->load("main");
    	$langs->load("bills");
    	
      $tab3_top = 240;
      $tab3_height = 18;
      $tab3_width = 60;
      
      $pdf->Rect(10, $tab3_top, $tab3_width, $tab3_height);
      
      $pdf->line(10, $tab3_top + 6, $tab3_width+10, $tab3_top + 6 );
      $pdf->line(10, $tab3_top + 12, $tab3_width+10, $tab3_top + 12 );
      
      $pdf->line(30, $tab3_top, 30, $tab3_top + $tab3_height );
      
      $pdf->SetFont('Arial','',8);
      $pdf->SetXY (10, $tab3_top - 6);
      $pdf->MultiCell(60, 6, $langs->trans("ExtraInfos"), 0, 'L', 0);
      $pdf->SetXY (10, $tab3_top );
      $pdf->MultiCell(20, 6, $langs->trans("RegulatedOn"), 0, 'L', 0);
      $pdf->SetXY (10, $tab3_top + 6);
      $pdf->MultiCell(20, 6, $langs->trans("ChequeNumber"), 0, 'L', 0);
      $pdf->SetXY (10, $tab3_top + 12);
      $pdf->MultiCell(20, 6, $langs->trans("Bank"), 0, 'L', 0);

      $pdf->SetFont('Arial','U',12);
      $titre = $langs->trans("PaymentConditions").' : '.$fac->cond_reglement_facture;
      $pdf->SetXY(200 - $pdf->GetStringWidth($titre), $tab3_top + 10);
      $pdf->MultiCell(120, 5, $titre, 0, 'J');
    }

  function _tableau_tot(&$pdf, $fac, $top, $height)
  {
    global $langs;
    $langs->load("main");
    $langs->load("bills"); 
    
    
    $tab2_top = $top + $height;
    $tab2_hl = 5;
    $tab2_height = $tab2_hl * 4;
    $pdf->SetFont('Arial','', 9);
    
    $tvas = $fac->getSumTva();
    $i = 0;
    
    $tab4_top = $tab2_top + 2 + ($tab2_hl * 2);
    
    foreach ($tvas as $key => $value)
      {
	$pdf->SetXY (10, $tab4_top + ( $i * $tab2_hl));
	$pdf->MultiCell(25, $tab2_hl, "TVA � ". $key."%", 0, 'L', 0);
	$pdf->SetXY (35, $tab4_top + ( $i * $tab2_hl));
	$pdf->MultiCell(20, $tab2_hl, price($tvas[$key]), 0, 'R', 0);
	$i++;
	$pdf->line(10, $tab4_top + ($tab2_hl * $i), 55, $tab4_top + ($tab2_hl * $i) );
      }
    $pdf->line(10, $tab4_top, 55, $tab4_top );
    $pdf->line(10, $tab4_top, 10, $tab4_top + ($tab2_hl * $i));
    $pdf->line(35, $tab4_top, 35, $tab4_top + ($tab2_hl * $i));
    $pdf->line(55, $tab4_top, 55, $tab4_top + ($tab2_hl * $i));
    
      
    $pdf->Rect(10, $tab2_top, 190, $tab2_hl * 2);
    $pdf->line(10, $tab2_top + $tab2_hl, 200, $tab2_top + $tab2_hl);
      
    
    $pdf->SetXY (132, $tab2_top + 0);
    $pdf->MultiCell(42, $tab2_hl, $langs->trans("TotalHT"), 0, 'R', 0);
      
      $pdf->SetXY (11, $tab2_top + $tab2_hl);
      $pdf->MultiCell(42, $tab2_hl, $langs->trans("Discount")." ". $fac->remise_percent . " %", 0, 'L', 0);
      
      $pdf->SetXY (132, $tab2_top + $tab2_hl * 2);
      $pdf->MultiCell(42, $tab2_hl, $langs->trans("WithDiscountTotalHT"), 0, 'R', 0);
      
      $pdf->SetXY (132, $tab2_top + $tab2_hl * 3);
      $pdf->MultiCell(42, $tab2_hl, $langs->trans("TotalVAT"), 0, 'R', 0);
      
      $pdf->SetXY (132, $tab2_top + $tab2_hl * 4);
      $pdf->MultiCell(42, $tab2_hl, $langs->trans("TotalTTC"), 0, 'R', 1);
      
      $pdf->SetXY (174, $tab2_top + 0);
      $pdf->MultiCell(26, $tab2_hl, price($fac->total_ht + $fac->remise), 0, 'R', 0);
      
      $pdf->SetXY (174, $tab2_top + $tab2_hl);
      $pdf->MultiCell(26, $tab2_hl, price($fac->remise), 0, 'R', 0);
      
      $pdf->SetXY (174, $tab2_top + $tab2_hl * 2);
      $pdf->MultiCell(26, $tab2_hl, price($fac->total_ht), 0, 'R', 0);
      
      $pdf->SetXY (174, $tab2_top + $tab2_hl * 3);
      $pdf->MultiCell(26, $tab2_hl, price($fac->total_tva), 0, 'R', 0);
      
      $pdf->SetXY (174, $tab2_top + $tab2_hl * 4);
      $pdf->MultiCell(26, $tab2_hl, price($fac->total_ttc), 0, 'R', 1);
    }
  /*
   *
   */
  function _tableau(&$pdf, $tab_top, $tab_height, $nexY)
    {
      global $langs;
      $langs->load("main");
      $langs->load("bills");
      
      $pdf->SetFont('Arial','',10);
      
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
        global $langs,$conf;
        $langs->load("main");
        $langs->load("bills");
      
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
	  $pdf->MultiCell(40, 5, $langs->trans('PhoneNumber').' : '.FAC_PDF_TEL);
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
      $pdf->SetXY(102,$pdf->GetY());
      $pdf->MultiCell(66,5, $fac->client->adresse . "\n" . $fac->client->cp . " " . $fac->client->ville);
      $pdf->rect(100, 40, 100, 40);
      
      
      $pdf->SetTextColor(200,0,0);
      $pdf->SetFont('Arial','B',14);
      $pdf->Text(11, 88, $langs->trans('Date').' : ' . strftime("%d %b %Y", $fac->date));
      $pdf->Text(11, 94, $langs->trans('Invoice').' : '.$fac->ref);
      
      /*
       */
      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',10);
      $titre = $langs->trans("AmountInCurrency",$langs->trans("Currency".$conf->monnaie));
      $pdf->Text(200 - $pdf->GetStringWidth($titre), 98, $titre);
      /*
       */
      
    }  
}

?>
