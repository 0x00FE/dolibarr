<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/**    	\file       htdocs/includes/modules/facture/pdf_bulot.modules.php
		\ingroup    facture
		\brief      Fichier de la classe permettant de g�n�rer les factures au mod�le Bulot
		\author	    Laurent Destailleur
		\version    $Revision$
*/


/**	    \class      pdf_bulot
		\brief      Classe permettant de g�n�rer les factures au mod�le Bulot
*/

class pdf_bulot extends ModelePDFFactures {

    /**		\brief  Constructeur
    		\param	db		handler acc�s base de donn�e
    */
  function pdf_bulot($db)
    { 
      $this->db = $db;
      $this->description = "Mod�le de facture avec remise et infos r�glement";
    }


  function write_pdf_file($facid)
    {
      global $user,$langs,$conf;
      
        $langs->load("main");
        $langs->load("bills");
        $langs->load("products");

        if ($conf->facture->dir_output)
	{
			$fac = new Facture($this->db,"",$facid);
			$fac->fetch($facid);  

			$forbidden_chars=array("/","\\",":","*","?","\"","<",">","|","[","]",",",";","=");
			$facref = str_replace($forbidden_chars,"_",$fac->ref);
			$dir = $conf->facture->dir_output . "/" . $facref;
			$file = $dir . "/" . $facref . ".pdf";
	  
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

	      $this->_tableau_tot($pdf, $fac);
	  
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
                    $this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
                    return 0;
	    }
	}
      else
	{
            $this->error=$langs->trans("ErrorConstantNotDefined","FAC_OUTPUTDIR");
            return 0;
	}
        $this->error=$langs->trans("ErrorUnknown");
        return 0;   // Erreur par defaut
    }
  /*
    *   \brief      Affiche tableau des versement
    *   \param      pdf     objet PDF
    *   \param      fac     objet facture
   */
  function _tableau_compl(&$pdf, $fac)
    {
      $tab3_top = 240;
      $tab3_height = 18;
      $tab3_width = 60;
      
      $pdf->Rect(10, $tab3_top, $tab3_width, $tab3_height);
      
      $pdf->line(10, $tab3_top + 6, $tab3_width+10, $tab3_top + 6 );
      $pdf->line(10, $tab3_top + 12, $tab3_width+10, $tab3_top + 12 );
      
      $pdf->line(30, $tab3_top, 30, $tab3_top + $tab3_height );
      
      $pdf->SetFont('Arial','',8);
      $pdf->SetXY (10, $tab3_top - 6);
      $pdf->MultiCell(60, 6, "Informations compl�mentaires", 0, 'L', 0);
      $pdf->SetXY (10, $tab3_top );
      $pdf->MultiCell(20, 6, "R�gl� le", 0, 'L', 0);
      $pdf->SetXY (10, $tab3_top + 6);
      $pdf->MultiCell(20, 6, "Ch�que N�", 0, 'L', 0);
      $pdf->SetXY (10, $tab3_top + 12);
      $pdf->MultiCell(20, 6, "Banque", 0, 'L', 0);
    }

    /*
    *   \brief      Affiche le total � payer
    *   \param      pdf         objet PDF
    *   \param      fac         objet facture
    *   \param      deja_regle  montant deja regle
    */
  function _tableau_tot(&$pdf, $fac)
    {
        global $langs;
        $langs->load("main");
        $langs->load("bills");

      $tab2_top = 212;
      $tab2_hl = 5;
      $tab2_height = $tab2_hl * 4;
      $pdf->SetFont('Arial','', 9);
      
      //	      $pdf->Rect(132, $tab2_top, 68, $tab2_height);
      //	      $pdf->line(174, $tab2_top, 174, $tab2_top + $tab2_height);
      
      //	      $pdf->line(132, $tab2_top + $tab2_height - 21, 200, $tab2_top + $tab2_height - 21 );
      //	      $pdf->line(132, $tab2_top + $tab2_height - 14, 200, $tab2_top + $tab2_height - 14 );
      //	      $pdf->line(132, $tab2_top + $tab2_height - 7, 200, $tab2_top + $tab2_height - 7 );
      
      $pdf->SetXY (132, $tab2_top + 0);
      $pdf->MultiCell(42, $tab2_hl, $langs->trans("TotalHT"), 0, 'R', 0);

      $pdf->SetXY (174, $tab2_top + 0);
      $pdf->MultiCell(26, $tab2_hl, price($fac->total_ht + $fac->remise), 0, 'R', 0);
      
      if ($fac->remise > 0)
	{
	  $pdf->SetXY (132, $tab2_top + $tab2_hl);
	  $pdf->MultiCell(42, $tab2_hl, $langs->trans("GlobalDiscount"), 0, 'R', 0);
	  
	  $pdf->SetXY (174, $tab2_top + $tab2_hl);
	  $pdf->MultiCell(26, $tab2_hl, price($fac->remise), 0, 'R', 0);
	  
	  $pdf->SetXY (132, $tab2_top + $tab2_hl * 2);
	  $pdf->MultiCell(42, $tab2_hl, "Total HT apr�s remise", 0, 'R', 0);
      
	  $pdf->SetXY (174, $tab2_top + $tab2_hl * 2);
	  $pdf->MultiCell(26, $tab2_hl, price($fac->total_ht), 0, 'R', 0);

	  $index = 3;
	}
      else
	{
	  $index = 1;
	}

      $pdf->SetXY (132, $tab2_top + $tab2_hl * $index);
      $pdf->MultiCell(42, $tab2_hl, $langs->trans("TotalVAT"), 0, 'R', 0);

      $pdf->SetXY (174, $tab2_top + $tab2_hl * $index);
      $pdf->MultiCell(26, $tab2_hl, price($fac->total_tva), 0, 'R', 0);
            
      $pdf->SetXY (132, $tab2_top + $tab2_hl * ($index+1));
      $pdf->MultiCell(42, $tab2_hl, $langs->trans("TotalTTC"), 0, 'R', 1);
      
      $pdf->SetXY (174, $tab2_top + $tab2_hl * ($index+1));
      $pdf->MultiCell(26, $tab2_hl, price($fac->total_ttc), 0, 'R', 1);

      $deja_regle = $fac->getSommePaiement();

      if ($deja_regle > 0)
	{
	  $pdf->SetXY (132, $tab2_top + $tab2_hl * ($index+2));
	  $pdf->MultiCell(42, $tab2_hl, $langs->trans("AlreadyPayed"), 0, 'R', 0);

	  $pdf->SetXY (174, $tab2_top + $tab2_hl * ($index+2));
	  $pdf->MultiCell(26, $tab2_hl, price($deja_regle), 0, 'R', 0);

	  $pdf->SetXY (132, $tab2_top + $tab2_hl * ($index+3));
	  $pdf->MultiCell(42, $tab2_hl, $langs->trans("RemainderToPay"), 0, 'R', 1);

	  $pdf->SetXY (174, $tab2_top + $tab2_hl * ($index+3));
	  $pdf->MultiCell(26, $tab2_hl, price($fac->total_ttc - $deja_regle), 0, 'R', 1);
	}
    }
  /*
    *   \brief      Affiche la grille des lignes de factures
    *   \param      pdf     objet PDF
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
    *   \brief      Affiche en-t�te facture
    *   \param      pdf     objet PDF
    *   \param      fac     objet facture
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
      $pdf->SetXY(102,$pdf->GetY());
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
      $titre = $langs->trans("AmountInCurrency")." ".$conf->monnaie;
      $pdf->Text(200 - $pdf->GetStringWidth($titre), 98, $titre);
      /*
       */
      
    }
  
}

?>
