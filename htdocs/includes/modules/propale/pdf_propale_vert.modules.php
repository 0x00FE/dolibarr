<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/*!	\file htdocs/includes/modules/propale/pdf_propale_vert.modules.php
		\ingroup    propale
		\brief      Fichier de la classe permettant de g�n�rer les propales au mod�le Vert
		\version    $Revision$
*/


/*!	\class pdf_propale_vert
		\brief  Classe permettant de g�n�rer les propales au mod�le Vert
*/

class pdf_propale_vert extends ModelePDFPropales
{

    /*!		\brief  Constructeur
    		\param	db		handler acc�s base de donn�e
    */
  function pdf_propale_vert($db=0)
    { 
      $this->db = $db;
      $this->name = "vert";
      $this->description = "Affichage de la remise par produit";
      $this->error = "";
    }


  function pdferror() 
  {
      return $this->error();
  }
  
  /**
    		\brief  Fonction g�n�rant la propale sur le disque
    		\param	id		id de la propale � g�n�rer
   		\return	    int     1=ok, 0=ko
    */
  function write_pdf_file($id)
    {
      global $user,$conf;
      
      $propale = new Propal($this->db,"",$id);
      if ($propale->fetch($id))
	{

	  if ($conf->propal->dir_output)
	    {
	      $dir = $conf->propal->dir_output . "/" . $propale->ref ;
          if (! file_exists($dir))
	    {
	      umask(0);
                if (! mkdir($dir, 0755))
		{
                    $this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
                    return 0;
                }
		}
	    }
	  else
	    {
            $this->error=$langs->trans("ErrorConstantNotDefined","PROPALE_OUTPUTDIR");
            return 0;
	    }
	  
	  $file = $dir . "/" . $propale->ref . ".pdf";
	  
	  if (file_exists($dir))
	    {

	      $pdf=new FPDF('P','mm','A4');
	      $pdf->Open();
	      $pdf->AddPage();

	      $pdf->SetTitle($propale->ref);
	      $pdf->SetSubject("Proposition commerciale");
	      $pdf->SetCreator("Dolibarr ".DOL_VERSION);
	      $pdf->SetAuthor($user->fullname);

	      $this->_pagehead($pdf, $propale);

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
	      $nblignes = sizeof($propale->lignes);

	      for ($i = 0 ; $i < $nblignes ; $i++)
		{

		  $curY = $nexY;

		  $pdf->SetXY (40, $curY );

		  $pdf->MultiCell(90, 5, $propale->lignes[$i]->desc, 0, 'J', 0);

		  $nexY = $pdf->GetY();
		 
		  $pdf->SetXY (10, $curY );
		  $pdf->SetFont('Arial','', 8);
		  $pdf->MultiCell(30, 5, $propale->lignes[$i]->ref, 0, 'L', 0);

		  $pdf->SetFont('Arial','', 10);
		  $pdf->SetXY (132, $curY );		  
		  $pdf->MultiCell(10, 5, $propale->lignes[$i]->tva_tx, 0, 'C', 0);
		  
		  $pdf->SetXY (142, $curY );
		  $pdf->MultiCell(8, 5, $propale->lignes[$i]->qty, 0, 'C');

		  $pdf->SetXY (150, $curY );
		  $pdf->MultiCell(16, 5, price($propale->lignes[$i]->subprice), 0, 'R', 0);
	      
		  $pdf->SetXY (166, $curY );
		  $pdf->MultiCell(14, 5, $propale->lignes[$i]->remise_percent."%", 0, 'R', 0);

		  $pdf->SetXY (180, $curY );
		  $total = price($propale->lignes[$i]->price * $propale->lignes[$i]->qty);
		  $pdf->MultiCell(20, 5, $total, 0, 'R', 0);
		  
		  $pdf->line(10, $curY, 200, $curY );

		  if ($nexY > 240 && $i < $nblignes - 1)
		    {
		      $this->_tableau($pdf, $tab_top, $tab_height, $nexY);
		      $pdf->AddPage();
		      $nexY = $iniY;
		      $this->_pagehead($pdf, $propale);
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
	      $pdf->MultiCell(42, $tab2_lh, "Total HT", 0, 'R', 0);
	      
	      $pdf->SetXY (132, $tab2_top + $tab2_lh);
	      $pdf->MultiCell(42, $tab2_lh, "Remise globale HT", 0, 'R', 0);

	      $pdf->SetXY (132, $tab2_top + $tab2_lh*2);
	      $pdf->MultiCell(42, $tab2_lh, "Total HT apr�s remise", 0, 'R', 0);

	      $pdf->SetXY (132, $tab2_top + $tab2_lh*3);
	      $pdf->MultiCell(42, $tab2_lh, "Total TVA", 0, 'R', 0);
	      
	      $pdf->SetXY (132, $tab2_top + ($tab2_lh*4));
	      $pdf->MultiCell(42, $tab2_lh, "Total TTC", 1, 'R', 1);

	      $pdf->SetXY (174, $tab2_top + 0);
	      $pdf->MultiCell(26, $tab2_lh, price($propale->total_ht + $propale->remise), 0, 'R', 0);
	      
	      $pdf->SetXY (174, $tab2_top + $tab2_lh);
	      $pdf->MultiCell(26, $tab2_lh, price($propale->remise), 0, 'R', 0);

	      $pdf->SetXY (174, $tab2_top + $tab2_lh*2);
	      $pdf->MultiCell(26, $tab2_lh, price($propale->total_ht), 0, 'R', 0);

	      $pdf->SetXY (174, $tab2_top + $tab2_lh*3);
	      $pdf->MultiCell(26, $tab2_lh, price($propale->total_tva), 0, 'R', 0);
	      
	      $pdf->SetXY (174, $tab2_top + ($tab2_lh*4));
	      $pdf->MultiCell(26, $tab2_lh, price($propale->total_ttc), 1, 'R', 1);

	      /*
	       *
	       */
	      	      
	      $pdf->Output($file);
	  return 1;
	    }
	}
    }

  function _tableau(&$pdf, $tab_top, $tab_height, $nexY)
    {
      $yt = 100;
      $pdf->SetFont('Arial','',10);
            
      $pdf->SetXY(10, $yt);
      $pdf->MultiCell(30,5,'R�f�rence',0,'L');

      $pdf->SetXY(40, $yt);
      $pdf->MultiCell(90,5,'D�signation',0,'L');

      $pdf->SetXY(132, $yt);
      $pdf->line(132, $tab_top, 132, $tab_top + $tab_height);
      $pdf->MultiCell(10,5,'TVA',0,'C');
      
      $pdf->line(142, $tab_top, 142, $tab_top + $tab_height);
      $pdf->SetXY(142, $yt);
      $pdf->MultiCell(8,5,'Qt�',0,'C');
      
      $pdf->line(150, $tab_top, 150, $tab_top + $tab_height);
      $pdf->SetXY(150, $yt);
      $pdf->MultiCell(16,5,'P.U.',0,'C');
      
      $pdf->line(166, $tab_top, 166, $tab_top + $tab_height);
      $pdf->SetXY(166, $yt);
      $pdf->MultiCell(14,5,'Remise',0,'C');

      $pdf->line(180, $tab_top, 180, $tab_top + $tab_height);
      $pdf->SetXY(180, $yt);
      $pdf->MultiCell(20,5,'Total',0,'R');
      
      //      $pdf->Rect(10, $tab_top, 190, $nexY - $tab_top);
      $pdf->Rect(10, $tab_top, 190, $tab_height);


      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',10);
      $titre = "Montants exprim�s en euros";
      $pdf->Text(200 - $pdf->GetStringWidth($titre), 98, $titre);

      $pdf->SetXY(10, ($tab_top + $tab_height + 6));
      $pdf->SetFont('Arial','',8);
      $texte = "En conformit� avec la loi 92-1442 du 31/12/92 modifi�e, une p�nalit� sera appliqu�e pour un retard de paiement au taux d'int�r�t l�gal multipli� par 5. LE mat�riel reste l'enti�re propri�t� de ".MAIN_INFO_SOCIETE_NOM." jusqu'� son paiement int�gral. Les configurations sont garanties trois ans (1 an pi�ce et main d'oeuvre, 2 ans (souris, micro-ventilateurs, claviers, non garanties). Pi�ces d�tach�es non garanties si montage hors de nos ateliers. La validation d'un devis est soumise � sa signature et encaissement d'un accompte de 30% du montant TTC.";
      $pdf->MultiCell(120,3,$texte,0,'J');
    }

  function _pagehead(&$pdf, $propale)
    {
      $pdf->SetXY(10,5);
      if (defined("FAC_PDF_INTITULE"))
	{
	  $pdf->SetTextColor(0,0,200);
	  $pdf->SetFont('Arial','B',14);
	  $pdf->MultiCell(76, 8, FAC_PDF_INTITULE, 0, 'L');
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
      if (defined("FAC_PDF_SIREN"))
	{
	  $pdf->SetFont('Arial','',10);
	  $pdf->MultiCell(76, 5, "SIREN : ".FAC_PDF_SIREN);
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
      $propale->fetch_client();
      $pdf->SetXY(102,42);
      $pdf->MultiCell(96,5, $propale->client->nom);
      $pdf->SetFont('Arial','B',11);
      $pdf->SetXY(102,47);
      $pdf->MultiCell(96,5, $propale->client->adresse . "\n" . $propale->client->cp . " " . $propale->client->ville);
      $pdf->rect(100, 40, 100, 40);
      
      
      $pdf->SetTextColor(200,0,0);
      $pdf->SetFont('Arial','B',12);
      $pdf->Text(11, 88, "Date : " . strftime("%d %b %Y", $propale->date));
      $pdf->Text(11, 94, "Proposition commerciale : ".$propale->ref);
      
      
    }

}

?>
