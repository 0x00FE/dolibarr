<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006 Regis Houssin        <regis.houssin@cap-networks.com>
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
        \file       htdocs/includes/modules/commande/pdf_sirocco.modules.php
        \ingroup    livraison
        \brief      Fichier de la classe permettant de g�n�rer les bons de livraison au mod�le Sirocco
        \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/livraison/mods/modules_livraison.php");


/**
        \class      pdf_sirocco
        \brief      Classe permettant de g�n�rer les bons de livraison au mod�le Sirocco
*/

class pdf_sirocco extends ModelePDFCommandes
{

  /**	\brief      Constructeur
        \param	    db	    handler acc�s base de donn�e
  */
  function pdf_sirocco($db=0)
    { 
        $this->db = $db;
        $this->name = "sirocco";
        $this->description = "Mod�le de bon de livraison simple";

        // Dimension page pour format A4
        $this->type = 'pdf';
        $this->page_largeur = 210;
        $this->page_hauteur = 297;
        $this->format = array($this->page_largeur,$this->page_hauteur);

        $this->error = "";
    }
  

  /**	\brief      Renvoi derni�re erreur
        \return     string      Derni�re erreur
  */
  function pdferror() 
  {
      return $this->error;
  }
  
  
  /**
        \brief      Fonction g�n�rant le bon de livraison sur le disque
        \param	    id		id du bon de livraison � g�n�rer
   		\return	    int     1=ok, 0=ko
  */
  function write_pdf_file($id)
    {
      global $user,$conf,$langs;
      
      $delivery = new Livraison($this->db);
      if ($delivery->fetch($id))
	{
	  	$deliveryref = sanitize_string($delivery->ref);
		$deliveryref = str_replace("(","",$deliveryref);
		$deliveryref = str_replace(")","",$deliveryref);
	  if ($conf->livraison->dir_output)
	    {
              
              $dir = $conf->livraison->dir_output . "/" . $deliveryref ;

            if (! file_exists($dir))
            {
                if (create_exdir($dir) < 0)
                {
                    $this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
                    return 0;
                }
            }
	    }
	  else
	    {
            $this->error=$langs->trans("ErrorConstantNotDefined","LIVRAISON_OUTPUTDIR");
            return 0;
	    }

          $file = $dir . "/" . $deliveryref . ".pdf";
	  
	  if (file_exists($dir))
	    {

	      $pdf=new FPDF('P','mm',$this->format);
	      $pdf->Open();
	      $pdf->AddPage();

	      $pdf->SetTitle($delivery->ref);
	      $pdf->SetSubject($langs->trans("DeliveryOrder"));
	      $pdf->SetCreator("Dolibarr ".DOL_VERSION);
	      $pdf->SetAuthor($user->fullname);

	      $this->_pagehead($pdf, $delivery);

	      /*
	       */
	      $tab_top = 100;
	      $tab_height = 140;
	      /*
	       *
	       */  
	      
	      $pdf->SetFillColor(220,220,220);

	      $pdf->SetTextColor(0,0,0);
	      $pdf->SetFont('Arial','', 10);

	      $pdf->SetXY (10, $tab_top + 10 );

	      $iniY = $pdf->GetY();
	      $curY = $pdf->GetY();
	      $nexY = $pdf->GetY();
	      $nblignes = sizeof($delivery->lignes);

	      for ($i = 0 ; $i < $nblignes ; $i++)
		{

		  $curY = $nexY;

		  $pdf->SetXY (30, $curY );

		  $pdf->MultiCell(100, 5, $delivery->lignes[$i]->desc, 0, 'J', 0);

		  $nexY = $pdf->GetY();
		 
		  $pdf->SetXY (10, $curY );

		  $pdf->MultiCell(20, 5, $delivery->lignes[$i]->ref, 0, 'C');

		  $pdf->SetXY (133, $curY );		  
		  $pdf->MultiCell(10, 5, $delivery->lignes[$i]->tva_tx, 0, 'C');
		  
		  $pdf->SetXY (145, $curY );
		  $pdf->MultiCell(10, 5, $delivery->lignes[$i]->qty, 0, 'C');

		  $pdf->SetXY (156, $curY );
		  $pdf->MultiCell(18, 5, price($delivery->lignes[$i]->price), 0, 'R', 0);
	      
		  $pdf->SetXY (174, $curY );
		  $total = price($delivery->lignes[$i]->price * $delivery->lignes[$i]->qty);
		  $pdf->MultiCell(26, 5, $total, 0, 'R', 0);
		  
		  $pdf->line(10, $curY, 200, $curY );

		  if ($nexY > 240 && $i < $nblignes - 1)
		    {
		      $this->_tableau($pdf, $tab_top, $tab_height, $nexY);
		      $pdf->AddPage();
		      $nexY = $iniY;
		      $this->_pagehead($pdf, $delivery);
		      $pdf->SetTextColor(0,0,0);
		      $pdf->SetFont('Arial','', 10);
		    }
		}
	      
	      $this->_tableau($pdf, $tab_top, $tab_height, $nexY);
	      /*
	       *
	       */
	      $tab2_top = 241;
	      $tab2_lh = 7;
	      $tab2_height = $tab2_lh * 4;

	      $pdf->SetFont('Arial','', 11);
	      
	      $pdf->Rect(132, $tab2_top, 68, $tab2_height);
	      
	      $pdf->line(132, $tab2_top + $tab2_height - ($tab2_lh*3), 200, $tab2_top + $tab2_height - ($tab2_lh*3) );
	      $pdf->line(132, $tab2_top + $tab2_height - ($tab2_lh*2), 200, $tab2_top + $tab2_height - ($tab2_lh*2) );
	      $pdf->line(132, $tab2_top + $tab2_height - $tab2_lh, 200, $tab2_top + $tab2_height - $tab2_lh );
	      
	      $pdf->line(174, $tab2_top, 174, $tab2_top + $tab2_height);
	      
	      $pdf->SetXY (132, $tab2_top + 0);
	      $pdf->MultiCell(42, $tab2_lh, $langs->trans("TotalHT"), 0, 'R', 0);
	      
	      $pdf->SetXY (132, $tab2_top + $tab2_lh);
	      $pdf->MultiCell(42, $tab2_lh, $langs->trans("Discount"), 0, 'R', 0);

	      $pdf->SetXY (132, $tab2_top + $tab2_lh*2);
	      $pdf->MultiCell(42, $tab2_lh, "Total HT apr�s remise", 0, 'R', 0);

	      $pdf->SetXY (132, $tab2_top + $tab2_lh*3);
	      $pdf->MultiCell(42, $tab2_lh, $langs->trans("TotalVAT"), 0, 'R', 0);
	      
	      $pdf->SetXY (132, $tab2_top + ($tab2_lh*4));
	      $pdf->MultiCell(42, $tab2_lh, $langs->trans("TotalTTC"), 1, 'R', 1);

	      $pdf->SetXY (174, $tab2_top + 0);
	      $pdf->MultiCell(26, $tab2_lh, price($delivery->total_ht + $delivery->remise), 0, 'R', 0);
	      
	      $pdf->SetXY (174, $tab2_top + $tab2_lh);
	      $pdf->MultiCell(26, $tab2_lh, price($delivery->remise), 0, 'R', 0);

	      $pdf->SetXY (174, $tab2_top + $tab2_lh*2);
	      $pdf->MultiCell(26, $tab2_lh, price($delivery->total_ht), 0, 'R', 0);

	      $pdf->SetXY (174, $tab2_top + $tab2_lh*3);
	      $pdf->MultiCell(26, $tab2_lh, price($delivery->total_tva), 0, 'R', 0);
	      
	      $pdf->SetXY (174, $tab2_top + ($tab2_lh*4));
	      $pdf->MultiCell(26, $tab2_lh, price($delivery->total_ttc), 1, 'R', 1);


	      $pdf->Output($file);
	      return 1;
	    }
	}
    }

  function _tableau(&$pdf, $tab_top, $tab_height, $nexY)
    {
        global $langs,$conf;
        $langs->load("main");
        $langs->load("bills");

      $pdf->SetFont('Arial','',11);
            
      $pdf->Text(30,$tab_top + 5,$langs->trans("Designation"));
      
      $pdf->line(132, $tab_top, 132, $tab_top + $tab_height);
      $pdf->Text(134,$tab_top + 5,$langs->trans("VAT"));
      
      $pdf->line(144, $tab_top, 144, $tab_top + $tab_height);
      $pdf->Text(147,$tab_top + 5,$langs->trans("Qty"));
      
      $pdf->line(156, $tab_top, 156, $tab_top + $tab_height);
      $pdf->Text(160,$tab_top + 5,$langs->trans("PriceU"));
      
      $pdf->line(174, $tab_top, 174, $tab_top + $tab_height);
      $pdf->Text(187,$tab_top + 5,$langs->trans("Total"));
      
      //      $pdf->Rect(10, $tab_top, 190, $nexY - $tab_top);
      $pdf->Rect(10, $tab_top, 190, $tab_height);


      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',10);
      $titre = $langs->trans("AmountInCurrency",$langs->trans("Currency".$conf->monnaie));
      $pdf->Text(200 - $pdf->GetStringWidth($titre), 98, $titre);

    }

  function _pagehead(&$pdf, $delivery)
   {
		global $langs;
	 $langs->load("sendings");
      $pdf->SetXY(10,5);
      if (defined("MAIN_INFO_SOCIETE_NOM"))
	{
	  $pdf->SetTextColor(0,0,200);
	  $pdf->SetFont('Arial','B',14);
	  $pdf->MultiCell(76, 8, MAIN_INFO_SOCIETE_NOM, 0, 'L');
	}
      
      $pdf->SetTextColor(70,70,170);
      if (defined("FAC_PDF_ADRESSE"))
	{
	  $pdf->SetFont('Arial','',12);
	  $pdf->MultiCell(76, 5, FAC_PDF_ADRESSE);
	}
      if (defined("FAC_PDF_TEL"))
	{
	  $pdf->SetFont('Arial','',10);
	  $pdf->MultiCell(76, 5, "T�l : ".FAC_PDF_TEL);
	}  
      if (defined("MAIN_INFO_SIREN"))
	{
	  $pdf->SetFont('Arial','',10);
	  $pdf->MultiCell(76, 5, "SIREN : ".MAIN_INFO_SIREN);
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
	  $client = new Societe($this->db);
      $client->fetch($delivery->soc_id);
	  $delivery->client = $client;
      $pdf->SetXY(102,42);
      $pdf->MultiCell(96,5, $delivery->client->nom);
      $pdf->SetFont('Arial','B',11);
      $pdf->SetXY(102,47);
      $pdf->MultiCell(96,5, $delivery->client->adresse . "\n" . $delivery->client->cp . " " . $delivery->client->ville);
      $pdf->rect(100, 40, 100, 40);
      
      
      $pdf->SetTextColor(200,0,0);
      $pdf->SetFont('Arial','B',12);
      $pdf->Text(11, 88, "Date : " . strftime("%d %b %Y", $delivery->date));
      $pdf->Text(11, 94, $langs->trans("DeliveryOrder")." ".$delivery->ref);
      
      
    }

}

?>
