<?php
/* Copyright (C) 2003-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
   \file       htdocs/includes/modules/facture/pdf_bulot.modules.php
   \ingroup    facture
   \brief      Fichier de la classe permettant de g�n�rer les factures au mod�le Bulot
   \author	    Laurent Destailleur
   \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/facture/modules_facture.php");

/**
   \class      pdf_bulot
   \brief      Classe permettant de g�n�rer les factures au mod�le Bulot
*/

class pdf_bulot extends ModelePDFFactures
{
  var $emetteur;	// Objet societe qui emet

  
  /**
     \brief  Constructeur
     \param	db		handler acc�s base de donn�e
  */
  function pdf_bulot($db)
  {
    global $conf,$langs,$mysoc;
    
    $langs->load("main");
    $langs->load("bills");
    $langs->load("products");
    
    $this->db = $db;
    $this->name = "bulot";
    $this->description = $langs->transnoentities('PDFBulotDescription');
    
    // Dimension page pour format A4
    $this->type = 'pdf';
    $this->page_largeur = 210;
    $this->page_hauteur = 297;
    $this->format = array($this->page_largeur,$this->page_hauteur);
    
    // Recupere emmetteur
    $this->emetteur=$mysoc;
    if (! $this->emetteur->pays_code) $this->emetteur->pays_code=substr($langs->defaultlang,-2);    // Par defaut, si n'�tait pas d�fini
  }
  
  /**
     \brief      Fonction g�n�rant la facture sur le disque
     \param	    fac		Objet facture � g�n�rer (ou id si ancienne methode)
     \return	    int     1=ok, 0=ko
   */
  function write_pdf_file($fac,$outputlangs='')
  {
    global $user,$langs,$conf;
    
    if ($conf->facture->dir_output)
      {
	// D�finition de l'objet $fac (pour compatibilite ascendante)
	if (! is_object($fac))
	  {
	    $id = $fac;
	    $fac = new Facture($this->db,"",$id);
	    $ret=$fac->fetch($id);
	  }
	
	// D�finition de $dir et $file
	if ($fac->specimen)
	  {
	    $dir = $conf->facture->dir_output;
	    $file = $dir . "/SPECIMEN.pdf";
	  }
	else
	  {
	    $facref = sanitize_string($fac->ref);
	    $dir = $conf->facture->dir_output . "/" . $facref;
	    $file = $dir . "/" . $facref . ".pdf";
	  }
	
	if (! file_exists($dir))
	  {
	    if (create_exdir($dir) < 0)
	      {
		$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
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
	    $pdf->SetSubject($langs->transnoentities("Bill"));
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
		$pdf->MultiCell(10, 5, $fac->lignes[$i]->tva_tx, 0, 'C');
		
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
		    
		    $pdf->SetXY (10, 40);
		    $pdf->SetFont('Arial','U',8);
		    $pdf->MultiCell(40, 4, $langs->transnoentities("BankDetails"), 0, 'L', 0);
		    $pdf->SetFont('Arial','',8);
		    $pdf->MultiCell(40, 4, $langs->transnoentities("BankCode").' : '. $account->code_banque, 0, 'L', 0);
		    $pdf->MultiCell(40, 4, $langs->transnoentities("DeskCode").' : ' . $account->code_guichet, 0, 'L', 0);
		    $pdf->MultiCell(50, 4, $langs->transnoentities("BankAccountNumber").' : ' . $account->number, 0, 'L', 0);
		    $pdf->MultiCell(40, 4, $langs->transnoentities("BankAccountNumberKey").' : ' . $account->cle_rib, 0, 'L', 0);
		    $pdf->MultiCell(40, 4, $langs->transnoentities("Residence").' : ' . $account->domiciliation, 0, 'L', 0);
		    $pdf->MultiCell(40, 4, $langs->transnoentities("IbanPrefix").' : ' . $account->iban_prefix, 0, 'L', 0);
		    $pdf->MultiCell(40, 4, $langs->transnoentities("BIC").' : ' . $account->bic, 0, 'L', 0);
		  }
	      }
	    
	    /*
	     *
	     *
	     */
	    
	    $pdf->SetFont('Arial','U',12);
	    $pdf->SetXY(10, 220);
	    $titre = $langs->transnoentities("PaymentConditions").' : '.$fac->cond_reglement_facture;
	    $pdf->MultiCell(190, 5, $titre, 0, 'J');
	    
	    $pdf->SetFont('Arial','',9);
	    $pdf->SetXY(10, 265);
	    $pdf->MultiCell(190, 5, $langs->transnoentities("PrettyLittleSentence"), 0, 'J');
	    
	    $pdf->Close();
	    
	    $pdf->Output($file);
	    
	    return 1;
	  }
	else
	  {
	    $this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
	    return 0;
	  }
      }
    else
      {
	$this->error=$langs->transnoentities("ErrorConstantNotDefined","FAC_OUTPUTDIR");
	return 0;
      }
    $this->error=$langs->transnoentities("ErrorUnknown");
    return 0;   // Erreur par defaut
  }
  
  /**
   *   \brief      Affiche tableau des versement
   *   \param      pdf     objet PDF
   *   \param      fac     objet facture
   */
  function _tableau_compl(&$pdf, $fac)
  {
    global $langs;
    
    $tab3_top = 240;
    $tab3_height = 18;
    $tab3_width = 60;
    
    $pdf->Rect(10, $tab3_top, $tab3_width, $tab3_height);
    
    $pdf->line(10, $tab3_top + 6, $tab3_width+10, $tab3_top + 6 );
    $pdf->line(10, $tab3_top + 12, $tab3_width+10, $tab3_top + 12 );
    
    $pdf->line(30, $tab3_top, 30, $tab3_top + $tab3_height );
    
    $pdf->SetFont('Arial','',8);
    $pdf->SetXY (10, $tab3_top - 6);
    $pdf->MultiCell(60, 6, $langs->transnoentities("ExtraInfos"), 0, 'L', 0);
    $pdf->SetXY (10, $tab3_top );
    $pdf->MultiCell(20, 6, $langs->transnoentities("RegulatedOn"), 0, 'L', 0);
    $pdf->SetXY (10, $tab3_top + 6);
    $pdf->MultiCell(20, 6, $langs->transnoentities("ChequeNumber"), 0, 'L', 0);
    $pdf->SetXY (10, $tab3_top + 12);
    $pdf->MultiCell(20, 6, $langs->transnoentities("Bank"), 0, 'L', 0);
  }
  
  /**
     \brief      Affiche le total � payer
     \param      pdf         objet PDF
     \param      fac         objet facture
     \param      deja_regle  montant deja regle
  */
  function _tableau_tot(&$pdf, $fac, $top, $height)
  {
    global $langs;
    $langs->load("main");
    $langs->load("bills");
    
    $tab2_top = $top + $height;
    $tab2_hl = 5;
    $tab2_height = $tab2_hl * 4;
    $pdf->SetFont('Arial','', 9);
    $pdf->SetFont('Arial','', 9);
    
    //	      $pdf->Rect(132, $tab2_top, 68, $tab2_height);
    //	      $pdf->line(174, $tab2_top, 174, $tab2_top + $tab2_height);
    
    //	      $pdf->line(132, $tab2_top + $tab2_height - 21, 200, $tab2_top + $tab2_height - 21 );
    //	      $pdf->line(132, $tab2_top + $tab2_height - 14, 200, $tab2_top + $tab2_height - 14 );
    //	      $pdf->line(132, $tab2_top + $tab2_height - 7, 200, $tab2_top + $tab2_height - 7 );
    
    $pdf->SetXY (132, $tab2_top + 0);
    $pdf->MultiCell(42, $tab2_hl, $langs->transnoentities("TotalHT"), 0, 'R', 0);
    
    $pdf->SetXY (174, $tab2_top + 0);
    $pdf->MultiCell(26, $tab2_hl, price($fac->total_ht + $fac->remise), 0, 'R', 0);
    
    if ($fac->remise > 0)
      {
	$pdf->SetXY (132, $tab2_top + $tab2_hl);
	$pdf->MultiCell(42, $tab2_hl, $langs->transnoentities("GlobalDiscount"), 0, 'R', 0);
	
	$pdf->SetXY (174, $tab2_top + $tab2_hl);
	$pdf->MultiCell(26, $tab2_hl, price($fac->remise), 0, 'R', 0);
	
	$pdf->SetXY (132, $tab2_top + $tab2_hl * 2);
	$pdf->MultiCell(42, $tab2_hl, $langs->transnoentities("WithDiscountTotalHT"), 0, 'R', 0);
	
	$pdf->SetXY (174, $tab2_top + $tab2_hl * 2);
	$pdf->MultiCell(26, $tab2_hl, price($fac->total_ht), 0, 'R', 0);
	
	$index = 3;
      }
    else
      {
	$index = 1;
      }
    
    $pdf->SetXY (132, $tab2_top + $tab2_hl * $index);
    $pdf->MultiCell(42, $tab2_hl, $langs->transnoentities("TotalVAT"), 0, 'R', 0);
    
    $pdf->SetXY (174, $tab2_top + $tab2_hl * $index);
    $pdf->MultiCell(26, $tab2_hl, price($fac->total_tva), 0, 'R', 0);
    
    $pdf->SetXY (132, $tab2_top + $tab2_hl * ($index+1));
    $pdf->MultiCell(42, $tab2_hl, $langs->transnoentities("TotalTTC"), 0, 'R', 1);
    
    $pdf->SetXY (174, $tab2_top + $tab2_hl * ($index+1));
    $pdf->MultiCell(26, $tab2_hl, price($fac->total_ttc), 0, 'R', 1);
    
    $deja_regle = $fac->getSommePaiement();
    
    if ($deja_regle > 0)
      {
	$pdf->SetXY (132, $tab2_top + $tab2_hl * ($index+2));
	$pdf->MultiCell(42, $tab2_hl, $langs->transnoentities("AlreadyPayed"), 0, 'R', 0);
	
	$pdf->SetXY (174, $tab2_top + $tab2_hl * ($index+2));
	$pdf->MultiCell(26, $tab2_hl, price($deja_regle), 0, 'R', 0);
	
	$pdf->SetXY (132, $tab2_top + $tab2_hl * ($index+3));
	$pdf->MultiCell(42, $tab2_hl, $langs->transnoentities("RemainderToPay"), 0, 'R', 1);
	
	$pdf->SetXY (174, $tab2_top + $tab2_hl * ($index+3));
	$pdf->MultiCell(26, $tab2_hl, price($fac->total_ttc - $deja_regle), 0, 'R', 1);
      }
  }
  
  
  /**
     \brief      Affiche la grille des lignes de factures
     \param      pdf     objet PDF
  */
  function _tableau(&$pdf, $tab_top, $tab_height, $nexY)
  {
    global $langs;
    $langs->load("main");
    $langs->load("bills");
    
    $pdf->SetFont('Arial','',10);
    
    $pdf->Text(11,$tab_top + 5,$langs->transnoentities("Designation"));
    
    $pdf->line(132, $tab_top, 132, $tab_top + $tab_height);
    $pdf->Text(134,$tab_top + 5,$langs->transnoentities("VAT"));
    
    $pdf->line(144, $tab_top, 144, $tab_top + $tab_height);
    $pdf->Text(147,$tab_top + 5,$langs->transnoentities("Qty"));
    
    $pdf->line(156, $tab_top, 156, $tab_top + $tab_height);
    $pdf->Text(160,$tab_top + 5,$langs->transnoentities("PriceU"));
    
    $pdf->line(174, $tab_top, 174, $tab_top + $tab_height);
    $pdf->Text(187,$tab_top + 5,$langs->transnoentities("Total"));
    
    $pdf->Rect(10, $tab_top, 190, $tab_height);
    $pdf->line(10, $tab_top + 10, 200, $tab_top + 10 );
  }
  
  
  /**
     \brief      Affiche en-t�te facture
     \param      pdf     objet PDF
     \param      fac     objet facture
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
	$pdf->MultiCell(40, 5, $langs->transnoentities('PhoneNumber').' : '.FAC_PDF_TEL);
      }
    if ($this->emetteur->profid1)
      {
	$pdf->SetFont('Arial','',10);
	$pdf->MultiCell(40, 5, "SIREN : ".$this->emetteur->profid1);
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
    $pdf->Text(11, 88, $langs->transnoentities('Date').' : ' . strftime("%d %b %Y", $fac->date));
    $pdf->Text(11, 94, $langs->transnoentities('Invoice').' : '.$fac->ref);
    
    /*
     */
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',10);
    $titre = $langs->transnoentities("AmountInCurrency",$langs->transnoentities("Currency".$conf->monnaie));
    $pdf->Text(200 - $pdf->GetStringWidth($titre), 98, $titre);
    /*
     */
    
  }  
}
?>
