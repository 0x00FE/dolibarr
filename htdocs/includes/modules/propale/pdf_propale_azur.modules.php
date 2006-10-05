<?php
/* Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
       	\file       htdocs/includes/modules/propale/pdf_propale_azur.modules.php
		\ingroup    propale
		\brief      Fichier de la classe permettant de g�n�rer les propales au mod�le Azur
		\author	    Laurent Destailleur
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/propale/modules_propale.php");
require_once(DOL_DOCUMENT_ROOT."/product.class.php");


/**
	    \class      pdf_propale_azur
		\brief      Classe permettant de g�n�rer les propales au mod�le Azur
*/

class pdf_propale_azur extends ModelePDFPropales
{
	var $emetteur;	// Objet societe qui emet


    /**
			\brief      Constructeur
    		\param	    db		Handler acc�s base de donn�e
    */
    function pdf_propale_azur($db)
    {
        global $conf,$langs,$mysoc;

		$langs->load("main");
        $langs->load("bills");

        $this->db = $db;
        $this->name = "azur";
        $this->description = "Mod�le de propositions commerciales complet (logo...)";

        // Dimension page pour format A4
        $this->type = 'pdf';
        $this->page_largeur = 210;
        $this->page_hauteur = 297;
        $this->format = array($this->page_largeur,$this->page_hauteur);
        $this->marge_gauche=10;
        $this->marge_droite=10;
        $this->marge_haute=10;
        $this->marge_basse=10;

        $this->option_logo = 1;                    // Affiche logo
        $this->option_tva = 1;                     // Gere option tva FACTURE_TVAOPTION
        $this->option_modereg = 1;                 // Affiche mode r�glement
        $this->option_condreg = 1;                 // Affiche conditions r�glement
        $this->option_codeproduitservice = 1;      // Affiche code produit-service
        $this->option_multilang = 1;               // Dispo en plusieurs langues

    	if (defined("FACTURE_TVAOPTION") && FACTURE_TVAOPTION == 'franchise')
      		$this->franchise=1;

        // Recupere emmetteur
        $this->emetteur=$mysoc;
        if (! $this->emetteur->pays_code) $this->emetteur->pays_code=substr($langs->defaultlang,-2);    // Par defaut, si n'�tait pas d�fini

        // Defini position des colonnes
        $this->posxdesc=$this->marge_gauche+1;
        $this->posxtva=121;
        $this->posxup=132;
        $this->posxqty=151;
        $this->posxdiscount=162;
        $this->postotalht=177;

        $this->tva=array();
        $this->atleastoneratenotnull=0;
        $this->atleastonediscount=0;
    }

	/**
	    \brief      Fonction g�n�rant la propale sur le disque
	    \param	    propale		Objet propal � g�n�rer (ou id si ancienne methode)
		\return	    int     	1=ok, 0=ko
	*/
	function write_pdf_file($propale,$outputlangs='')
	{
		global $user,$conf,$langs;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		$outputlangs->load("main");
        $outputlangs->load("companies");
        $outputlangs->load("bills");
        $outputlangs->load("propal");
        $outputlangs->load("products");

		$outputlangs->setPhpLang();

		if ($conf->propal->dir_output)
		{
			// D�finition de l'objet $propale (pour compatibilite ascendante)
			if (! is_object($propale))
			{
				$id = $propale;
				$propale = new Propal($this->db,"",$id);
				$ret=$propale->fetch($id);
			}
			$deja_regle = "";

			// D�finition de $dir et $file
			if ($propale->specimen)
			{
				$dir = $conf->propal->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$propref = sanitize_string($propale->ref);
				$dir = $conf->propal->dir_output . "/" . $propref;
				$file = $dir . "/" . $propref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (create_exdir($dir) < 0)
				{
					$this->error=$outputlangs->trans("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				$nblignes = sizeof($propale->lignes);

				// Initialisation document vierge
				$pdf=new FPDF('P','mm',$this->format);
				$pdf->Open();
				$pdf->AddPage();

				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($propale->ref);
				$pdf->SetSubject($outputlangs->trans("CommercialProposal"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($user->fullname);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				$pdf->SetAutoPageBreak(1,0);

				// Positionne $this->atleastonediscount si on a au moins une remise
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					if ($propale->lignes[$i]->remise_percent)
					{
						$this->atleastonediscount++;
					}
				}

                // Tete de page
				$this->_pagehead($pdf, $propale, 1, $outputlangs);

				$pagenb = 1;
				$tab_top = 90;
				$tab_top_newpage = 50;
				$tab_height = 110;

				// Affiche notes
				if ($propale->note_public)
				{
					$tab_top = 88;

					$pdf->SetFont('Arial','', 9);   // Dans boucle pour g�rer multi-page
					$pdf->SetXY ($this->posxdesc-1, $tab_top);
					$pdf->MultiCell(190, 3, $propale->note_public, 0, 'J');
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->Rect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1);

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+6;
				}
				else
				{
					$height_note=0;
				}

				$iniY = $tab_top + 8;
				$curY = $tab_top + 8;
				$nexY = $tab_top + 8;

				// Boucle sur les lignes
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					$curY = $nexY;

					// Description de la ligne produit
					$libelleproduitservice=_dol_htmlentities($propale->lignes[$i]->libelle,0);
					if ($propale->lignes[$i]->desc && $propale->lignes[$i]->desc!=$propale->lignes[$i]->libelle)
					{
						if ($libelleproduitservice) $libelleproduitservice.="\n";
						$libelleproduitservice.=_dol_htmlentities($propale->lignes[$i]->desc,$conf->global->FCKEDITOR_ENABLE_DETAILS);
					}
					// Si ligne associ�e � un code produit
					if ($propale->lignes[$i]->fk_product)
					{
						$prodser = new Product($this->db);
						$prodser->fetch($propale->lignes[$i]->fk_product);

						// On ajoute la ref
						if ($prodser->ref)
						{
							$prefix_prodserv = "";
							if($prodser->type == 0)
							$prefix_prodserv = $outputlangs->trans("Product")." ";
							if($prodser->type == 1)
							$prefix_prodserv = $outputlangs->trans("Service")." ";

							$libelleproduitservice=$prefix_prodserv.$prodser->ref." - ".$libelleproduitservice;
						}

						// Ajoute description compl�te du produit
						if ($conf->global->FORM_ADD_PROD_DESC && !$conf->global->PRODUIT_CHANGE_PROD_DESC)
						{
							if ($propale->lignes[$i]->product_desc && $propale->lignes[$i]->product_desc!=$propale->lignes[$i]->libelle && $propale->lignes[$i]->product_desc!=$propale->lignes[$i]->desc)
							{
								if ($libelleproduitservice) $libelleproduitservice.="\n";
								$libelleproduitservice.=$propale->lignes[$i]->product_desc;
							}
						}
					}
					if ($propale->lignes[$i]->date_start && $propale->lignes[$i]->date_end)
					{
						// Affichage dur�e si il y en a une
						$libelleproduitservice.="\n(".$outputlangs->trans("From")." ".dolibarr_print_date($propale->lignes[$i]->date_start)." ".$outputlangs->trans("to")." ".dolibarr_print_date($propale->lignes[$i]->date_end).")";
					}

					$pdf->SetFont('Arial','', 9);   // Dans boucle pour g�rer multi-page

					if ($conf->fckeditor->enabled)
					{
						$pdf->writeHTMLCell(108, 4, $this->posxdesc-1, $curY, $libelleproduitservice, 0, 1);
					}
					else
					{
						$pdf->SetXY ($this->posxdesc-1, $curY);
						$pdf->MultiCell(108, 4, $libelleproduitservice, 0, 'J');
					}

					$nexY = $pdf->GetY();

					// TVA
					$pdf->SetXY ($this->posxtva, $curY);
					$pdf->MultiCell(10, 4, ($propale->lignes[$i]->tva_tx < 0 ? '*':'').abs($propale->lignes[$i]->tva_tx), 0, 'R');

					// Prix unitaire HT avant remise
					$pdf->SetXY ($this->posxup, $curY);
					$pdf->MultiCell(18, 4, price($propale->lignes[$i]->subprice), 0, 'R', 0);

					// Quantit�
					$pdf->SetXY ($this->posxqty, $curY);
					$pdf->MultiCell(10, 4, $propale->lignes[$i]->qty, 0, 'R');

					// Remise sur ligne
					$pdf->SetXY ($this->posxdiscount, $curY);
					if ($propale->lignes[$i]->remise_percent)
					{
						$pdf->MultiCell(14, 4, $propale->lignes[$i]->remise_percent."%", 0, 'R');
					}

					// Total HT ligne
					$pdf->SetXY ($this->postotalht, $curY);
					$total = price($propale->lignes[$i]->price * $propale->lignes[$i]->qty);
					$pdf->MultiCell(23, 4, $total, 0, 'R', 0);

					// Collecte des totaux par valeur de tva
					// dans le tableau tva["taux"]=total_tva
					$tvaligne=$propale->lignes[$i]->price * $propale->lignes[$i]->qty;
					if ($propale->remise_percent) $tvaligne-=($tvaligne*$propale->remise_percent)/100;
					$this->tva[ (string)$propale->lignes[$i]->tva_tx ] += $tvaligne;

					$nexY+=2;    // Passe espace entre les lignes

                    if ($nexY > ($tab_top+$tab_height) && $i < ($nblignes - 1))
					{
						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top, $nexY - $tab_top + 20, $nexY, $outputlangs);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $nexY - $tab_top_newpage + 20, $nexY, $outputlangs);
						}

						$this->_pagefoot($pdf,$outputlangs);

						// Nouvelle page
						$pdf->AddPage();
						$pagenb++;
						$this->_pagehead($pdf, $propale, 0, $outputlangs);

						$nexY = $tab_top_newpage + 8;
						$pdf->SetTextColor(0,0,0);
						$pdf->SetFont('Arial','', 10);
					}

				}

				// Affiche cadre tableau
				if ($pagenb == 1)
				{
					$this->_tableau($pdf, $tab_top, $nexY - $tab_top + 20, $nexY, $outputlangs);
					$bottomlasttab=$tab_top + $nexY - $tab_top + 20 + 1;
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $nexY - $tab_top_newpage + 20, $nexY, $outputlangs);
					$bottomlasttab=$tab_top_newpage + $nexY - $tab_top_newpage + 20 + 1;
				}

                // Affiche zone infos
                $posy=$this->_tableau_info($pdf, $propale, $bottomlasttab, $outputlangs);

                // Affiche zone totaux
				$posy=$this->_tableau_tot($pdf, $propale, $deja_regle, $bottomlasttab, $outputlangs);

                // Affiche zone versements
				if ($deja_regle) {
					$posy=$this->_tableau_versements($pdf, $propale, $posy, $outputlangs);
				}

				// Pied de page
				$this->_pagefoot($pdf, $outputlangs);
				$pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file);

				$langs->setPhpLang();	// On restaure langue session
				return 1;   // Pas d'erreur
			}
			else
			{
				$this->error=$outputlangs->trans("ErrorCanNotCreateDir",$dir);
				$langs->setPhpLang();	// On restaure langue session
				return 0;
			}
		}
		else
		{
			$this->error=$outputlangs->trans("ErrorConstantNotDefined","PROP_OUTPUTDIR");
			$langs->setPhpLang();	// On restaure langue session
			return 0;
		}

		$this->error=$outputlangs->trans("ErrorUnknown");
		$langs->setPhpLang();	// On restaure langue session
		return 0;   // Erreur par defaut
	}

    /*
     *   \brief      Affiche tableau des versement
     *   \param      pdf     	objet PDF
     *   \param      propale	objet propale
     */
    function _tableau_versements(&$pdf, $object, $posy, $outputlangs)
	{

	}


	/*
     *	\brief      Affiche infos divers
     *	\param      pdf             Objet PDF
     *	\param      object         	Objet propale
     *	\param		posy			Position depart
     *	\param		outputlangs		Objet langs
     *	\return     y               Position pour suite
     */
    function _tableau_info(&$pdf, $object, $posy, $outputlangs)
    {
        global $conf;

        $pdf->SetFont('Arial','', 9);

        /*
        *	Affiche la mention TVA non applicable selon option
        */
    	if ($this->franchise == 1)
      	{
	        $pdf->SetFont('Arial','B',8);
	        $pdf->SetXY($this->marge_gauche, $posy);
            $pdf->MultiCell(100, 3, $outputlangs->trans("VATIsNotUsedForInvoice"), 0, 'L', 0);

            $posy=$pdf->GetY()+4;
        }

        /*
        *	Conditions de r�glements
        */
        if ($object->cond_reglement_code || $object->cond_reglement)
        {
            $pdf->SetFont('Arial','B',8);
            $pdf->SetXY($this->marge_gauche, $posy);
            $titre = $outputlangs->trans("PaymentConditions").':';
            $pdf->MultiCell(80, 5, $titre, 0, 'L');

            $pdf->SetFont('Arial','',8);
            $pdf->SetXY(50, $posy);
            $lib_condition_paiement=$outputlangs->trans("PaymentCondition".$object->cond_reglement_code)!=('PaymentCondition'.$object->cond_reglement_code)?$outputlangs->trans("PaymentCondition".$object->cond_reglement_code):$object->cond_reglement;
            $pdf->MultiCell(80, 5, $lib_condition_paiement,0,'L');

            $posy=$pdf->GetY()+3;
		}

        /*
        *	Check si absence mode de r�glement
        */
        if (! $conf->global->FACTURE_CHQ_NUMBER && ! $conf->global->FACTURE_RIB_NUMBER)
		{
            $pdf->SetXY($this->marge_gauche, $posy);
            $pdf->SetTextColor(200,0,0);
            $pdf->SetFont('Arial','B',8);
            $pdf->MultiCell(90, 3, $outputlangs->trans("ErrorNoPaiementModeConfigured"),0,'L',0);
            $pdf->SetTextColor(0,0,0);

            $posy=$pdf->GetY()+1;
        }

        /*
         * Propose mode r�glement par CHQ
         */
        if (! $object->mode_reglement_code || $object->mode_reglement_code == 'CHQ')
        {
	       	// Si mode reglement non force ou si force a CHQ
	        if ($conf->global->FACTURE_CHQ_NUMBER)
	        {
	            if ($conf->global->FACTURE_CHQ_NUMBER > 0)
	            {
	                $account = new Account($this->db);
	                $account->fetch($conf->global->FACTURE_CHQ_NUMBER);
	
	                $pdf->SetXY($this->marge_gauche, $posy);
	                $pdf->SetFont('Arial','B',8);
	                $pdf->MultiCell(90, 3, $outputlangs->trans('PaymentByChequeOrderedTo',$account->proprio).':',0,'L',0);
		            $posy=$pdf->GetY()+1;
	
	                $pdf->SetXY($this->marge_gauche, $posy);
	                $pdf->SetFont('Arial','',8);
	                $pdf->MultiCell(80, 3, $account->adresse_proprio, 0, 'L', 0);
	
		            $posy=$pdf->GetY()+2;
	            }
	            if ($conf->global->FACTURE_CHQ_NUMBER == -1)
	            {
	                $pdf->SetXY($this->marge_gauche, $posy);
	                $pdf->SetFont('Arial','B',8);
	                $pdf->MultiCell(90, 3, $outputlangs->trans('PaymentByChequeOrderedToShort').' '.$this->emetteur->nom.' '.$outputlangs->trans('SendTo').':',0,'L',0);
		            $posy=$pdf->GetY()+1;
	
	                $pdf->SetXY($this->marge_gauche, $posy);
	                $pdf->SetFont('Arial','',8);
	                $pdf->MultiCell(80, 6, $this->emetteur->adresse_full, 0, 'L', 0);
	
		            $posy=$pdf->GetY()+2;
	            }
	        }
		}
		
        /*
         * Propose mode r�glement par RIB
         */
        if (! $object->mode_reglement_code || $object->mode_reglement_code == 'VIR')
        {
        	// Si mode reglement non force ou si force a VIR
	        if ($conf->global->FACTURE_RIB_NUMBER)
	        {
	            if ($conf->global->FACTURE_RIB_NUMBER)
	            {
	                $account = new Account($this->db);
	                $account->fetch($conf->global->FACTURE_RIB_NUMBER);
	
	                $this->marges['g']=$this->marge_gauche;
	
	                $cury=$posy;
	                $pdf->SetXY ($this->marges['g'], $cury);
	                $pdf->SetFont('Arial','B',8);
	                $pdf->MultiCell(90, 3, $outputlangs->trans('PaymentByTransferOnThisBankAccount').':', 0, 'L', 0);
	                $cury+=4;
	                $pdf->SetFont('Arial','B',6);
	                $pdf->line($this->marges['g']+1, $cury, $this->marges['g']+1, $cury+10 );
	                $pdf->SetXY ($this->marges['g'], $cury);
	                $pdf->MultiCell(18, 3, $outputlangs->trans("BankCode"), 0, 'C', 0);
	                $pdf->line($this->marges['g']+18, $cury, $this->marges['g']+18, $cury+10 );
	                $pdf->SetXY ($this->marges['g']+18, $cury);
	                $pdf->MultiCell(18, 3, $outputlangs->trans("DeskCode"), 0, 'C', 0);
	                $pdf->line($this->marges['g']+36, $cury, $this->marges['g']+36, $cury+10 );
	                $pdf->SetXY ($this->marges['g']+36, $cury);
	                $pdf->MultiCell(24, 3, $outputlangs->trans("BankAccountNumber"), 0, 'C', 0);
	                $pdf->line($this->marges['g']+60, $cury, $this->marges['g']+60, $cury+10 );
	                $pdf->SetXY ($this->marges['g']+60, $cury);
	                $pdf->MultiCell(13, 3, $outputlangs->trans("BankAccountNumberKey"), 0, 'C', 0);
	                $pdf->line($this->marges['g']+73, $cury, $this->marges['g']+73, $cury+10 );
	
	                $pdf->SetFont('Arial','',8);
	                $pdf->SetXY ($this->marges['g'], $cury+5);
	                $pdf->MultiCell(18, 3, $account->code_banque, 0, 'C', 0);
	                $pdf->SetXY ($this->marges['g']+18, $cury+5);
	                $pdf->MultiCell(18, 3, $account->code_guichet, 0, 'C', 0);
	                $pdf->SetXY ($this->marges['g']+36, $cury+5);
	                $pdf->MultiCell(24, 3, $account->number, 0, 'C', 0);
	                $pdf->SetXY ($this->marges['g']+60, $cury+5);
	                $pdf->MultiCell(13, 3, $account->cle_rib, 0, 'C', 0);
	
	                $pdf->SetXY ($this->marges['g'], $cury+12);
	                $pdf->MultiCell(90, 3, $outputlangs->trans("Residence").' : ' . $account->domiciliation, 0, 'L', 0);
	                $pdf->SetXY ($this->marges['g'], $cury+22);
	                $pdf->MultiCell(90, 3, $outputlangs->trans("IbanPrefix").' : ' . $account->iban_prefix, 0, 'L', 0);
	                $pdf->SetXY ($this->marges['g'], $cury+25);
	                $pdf->MultiCell(90, 3, $outputlangs->trans("BIC").' : ' . $account->bic, 0, 'L', 0);
	
		            $posy=$pdf->GetY()+2;
	            }
	        }
		}
		
        return $posy;
    }


    /*
     *	\brief      Affiche le total � payer
     *	\param      pdf         	Objet PDF
     *	\param      prop         	Objet propale
     *	\param      deja_regle  	Montant deja regle
     *	\param		posy			Position depart
     *	\param		outputlangs		Objet langs
     *	\return     y              Position pour suite
    */
    function _tableau_tot(&$pdf, $object, $deja_regle, $posy, $outputlangs)
    {
        $tab2_top = $posy;
        $tab2_hl = 5;
        $tab2_height = $tab2_hl * 4;
        $pdf->SetFont('Arial','', 8);

        // Tableau total
        $lltot = 200; $col1x = 120; $col2x = 182; $largcol2 = $lltot - $col2x;

        // Total HT
        $pdf->SetFillColor(256,256,256);
        $pdf->SetXY ($col1x, $tab2_top + 0);
        $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("TotalHT"), 0, 'L', 1);

        $pdf->SetXY ($col2x, $tab2_top + 0);
        $pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ht + $object->remise), 0, 'R', 1);

        // Remise globale
        if ($object->remise > 0)
        {
            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl);
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("GlobalDiscount"), 0, 'L', 1);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl);
            $pdf->MultiCell($largcol2, $tab2_hl, "-".$object->remise_percent."%", 0, 'R', 1);

            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * 2);
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("WithDiscountTotalHT"), 0, 'L', 1);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * 2);
            $pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ht), 0, 'R', 0);

            $index = 2;
        }
        else
        {
            $index = 0;
        }

        // Affichage des totaux de TVA par taux (conform�ment � r�glementation)
        $pdf->SetFillColor(248,248,248);
        foreach( $this->tva as $tvakey => $tvaval )
        {
            if ($tvakey)    // On affiche pas taux 0
            {
                $this->atleastoneratenotnull++;

                $index++;
            	$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
                $tvacompl = ( (float)$tvakey < 0 ) ? " (".$outputlangs->trans("NonPercuRecuperable").")" : '' ;
                $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("TotalVAT").' '.abs($tvakey).'%'.$tvacompl, 0, 'L', 1);

                $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
                $pdf->MultiCell($largcol2, $tab2_hl, price($tvaval * abs((float)$tvakey) / 100 ), 0, 'R', 1);
            }
        }
        if (! $this->atleastoneratenotnull)
        {
            $index++;
            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("TotalVAT"), 0, 'L', 1);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
            $pdf->MultiCell($largcol2, $tab2_hl, price($object->total_tva), 0, 'R', 1);
        }

        $useborder=0;

        $index++;
        $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
        $pdf->SetTextColor(0,0,60);
        $pdf->SetFillColor(224,224,224);
        $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("TotalTTC"), $useborder, 'L', 1);

        $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
        $pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ttc), $useborder, 'R', 1);
        $pdf->SetTextColor(0,0,0);

        if ($deja_regle > 0)
        {
            $index++;

            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("AlreadyPayed"), 0, 'L', 0);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
            $pdf->MultiCell($largcol2, $tab2_hl, price($deja_regle), 0, 'R', 0);

			$resteapayer = $object->total_ttc - $deja_regle;
			if ($object->paye) $resteapayer=0;

			if ($object->close_code == 'escompte')
			{
	            $index++;
        		$pdf->SetFillColor(256,256,256);

	            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
	            $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("EscompteOffered"), $useborder, 'L', 1);

	            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
	            $pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ttc - $deja_regle), $useborder, 'R', 1);
			}

            $index++;
            $pdf->SetTextColor(0,0,60);
	        $pdf->SetFillColor(224,224,224);
            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->trans("RemainderToPay"), $useborder, 'L', 1);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
            $pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ttc - $deja_regle), $useborder, 'R', 1);

			// Fin
            $pdf->SetFont('Arial','', 9);
            $pdf->SetTextColor(0,0,0);
        }

        $index++;
        return ($tab2_top + ($tab2_hl * $index));
    }

    /*
    *   \brief      Affiche la grille des lignes de propales
    *   \param      pdf     objet PDF
    */
    function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs)
    {
    	global $conf;

        // Montants exprim�s en     (en tab_top - 1)
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',8);
        $titre = $outputlangs->trans("AmountInCurrency",$outputlangs->trans("Currency".$conf->monnaie));
        $pdf->Text($this->page_largeur - $this->marge_droite - $pdf->GetStringWidth($titre), $tab_top-1, $titre);

        $pdf->SetDrawColor(128,128,128);

        // Rect prend une longueur en 3eme param
        $pdf->Rect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height);
        // line prend une position y en 3eme param
        $pdf->line($this->marge_gauche, $tab_top+5, $this->page_largeur-$this->marge_droite, $tab_top+5);

        $pdf->SetFont('Arial','',9);

        $pdf->SetXY ($this->posxdesc-1, $tab_top+2);
        $pdf->MultiCell(108,2, $outputlangs->trans("Designation"),'','L');

        $pdf->line($this->posxtva-1, $tab_top, $this->posxtva-1, $tab_top + $tab_height);
        $pdf->SetXY ($this->posxtva-1, $tab_top+2);
        $pdf->MultiCell(12,2, $outputlangs->trans("VAT"),'','C');

        $pdf->line($this->posxup-1, $tab_top, $this->posxup-1, $tab_top + $tab_height);
        $pdf->SetXY ($this->posxup-1, $tab_top+2);
        $pdf->MultiCell(18,2, $outputlangs->trans("PriceUHT"),'','C');

        $pdf->line($this->posxqty-1, $tab_top, $this->posxqty-1, $tab_top + $tab_height);
        $pdf->SetXY ($this->posxqty-1, $tab_top+2);
        $pdf->MultiCell(11,2, $outputlangs->trans("Qty"),'','C');

        $pdf->line($this->posxdiscount-1, $tab_top, $this->posxdiscount-1, $tab_top + $tab_height);
        if ($this->atleastonediscount)
        {
            $pdf->SetXY ($this->posxdiscount-1, $tab_top+2);
            $pdf->MultiCell(16,2, $outputlangs->trans("ReductionShort"),'','C');
        }

        if ($this->atleastonediscount)
        {
            $pdf->line($this->postotalht, $tab_top, $this->postotalht, $tab_top + $tab_height);
        }
        $pdf->SetXY ($this->postotalht-1, $tab_top+2);
        $pdf->MultiCell(23,2, $outputlangs->trans("TotalHT"),'','C');

    }

    /*
     *   	\brief      Affiche en-t�te propale
     *   	\param      pdf     objet PDF
     *   	\param      fac     objet propale
     *      \param      showadress      0=non, 1=oui
     */
    function _pagehead(&$pdf, $object, $showadress=1, $outputlangs)
    {
        global $conf;

        $outputlangs->load("main");
        $outputlangs->load("bills");
        $outputlangs->load("propal");
        $outputlangs->load("companies");

        $pdf->SetTextColor(0,0,60);
        $pdf->SetFont('Arial','B',13);

        $posy=$this->marge_haute;

        $pdf->SetXY($this->marge_gauche,$posy);

		// Logo
        $logo=$conf->societe->dir_logos.'/'.$this->emetteur->logo;
        if ($this->emetteur->logo)
        {
            if (is_readable($logo))
            {
                $pdf->Image($logo, $this->marge_gauche, $posy, 0, 24);
            }
            else
            {
                $pdf->SetTextColor(200,0,0);
                $pdf->SetFont('Arial','B',8);
                $pdf->MultiCell(100, 3, $outputlangs->trans("ErrorLogoFileNotFound",$logo), 0, 'L');
                $pdf->MultiCell(100, 3, $outputlangs->trans("ErrorGoToModuleSetup"), 0, 'L');
            }
        }
        else if (defined("FAC_PDF_INTITULE"))
        {
            $pdf->MultiCell(100, 4, FAC_PDF_INTITULE, 0, 'L');
        }

        $pdf->SetFont('Arial','B',13);
        $pdf->SetXY(100,$posy);
        $pdf->SetTextColor(0,0,60);
        $pdf->MultiCell(100, 4, $outputlangs->trans("CommercialProposal"), '' , 'R');

        $pdf->SetFont('Arial','B',12);

        $posy+=6;
        $pdf->SetXY(100,$posy);
        $pdf->SetTextColor(0,0,60);
        $pdf->MultiCell(100, 4, $outputlangs->trans("Ref")." : " . $object->ref, '', 'R');

        $pdf->SetFont('Arial','',10);

        $posy+=6;
        $pdf->SetXY(100,$posy);
        $pdf->SetTextColor(0,0,60);
        $pdf->MultiCell(100, 3, $outputlangs->trans("Date")." : " . dolibarr_print_date($object->date,"%d %b %Y"), '', 'R');

        $posy+=5;
        $pdf->SetXY(100,$posy);
        $pdf->SetTextColor(0,0,60);
        $pdf->MultiCell(100, 3, $outputlangs->trans("DateEndPropal")." : " . dolibarr_print_date($object->fin_validite,"%d %b %Y"), '', 'R');

        if ($showadress)
        {
	        // Emetteur
	        $posy=42;
	        $hautcadre=40;
	        $pdf->SetTextColor(0,0,0);
	        $pdf->SetFont('Arial','',8);
	        $pdf->SetXY($this->marge_gauche,$posy-5);
	        $pdf->MultiCell(66,5, $outputlangs->trans("BillFrom").":");


	        $pdf->SetXY($this->marge_gauche,$posy);
	        $pdf->SetFillColor(230,230,230);
	        $pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);


	        $pdf->SetXY($this->marge_gauche+2,$posy+3);

	        // Nom emetteur
	        $pdf->SetTextColor(0,0,60);
	        $pdf->SetFont('Arial','B',11);
	        if (defined("FAC_PDF_SOCIETE_NOM") && FAC_PDF_SOCIETE_NOM) $pdf->MultiCell(80, 4, FAC_PDF_SOCIETE_NOM, 0, 'L');
	        else $pdf->MultiCell(80, 4, $this->emetteur->nom, 0, 'L');

	        // Caract�ristiques emetteur
	        $carac_emetteur = '';
	        if (defined("FAC_PDF_ADRESSE") && FAC_PDF_ADRESSE) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).FAC_PDF_ADRESSE;
	        else {
	            $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$this->emetteur->adresse;
	            $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$this->emetteur->cp.' '.$this->emetteur->ville;
	        }
	        $carac_emetteur .= "\n";
	        // Tel
	        if (defined("FAC_PDF_TEL") && FAC_PDF_TEL) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->trans("Phone").": ".FAC_PDF_TEL;
	        elseif ($this->emetteur->tel) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->trans("Phone").": ".$this->emetteur->tel;
	        // Fax
	        if (defined("FAC_PDF_FAX") && FAC_PDF_FAX) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->trans("Fax").": ".FAC_PDF_FAX;
	        elseif ($this->emetteur->fax) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->trans("Fax").": ".$this->emetteur->fax;
	        // EMail
			if (defined("FAC_PDF_MEL") && FAC_PDF_MEL) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->trans("Email").": ".FAC_PDF_MEL;
	        elseif ($this->emetteur->email) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->trans("Email").": ".$this->emetteur->email;
	        // Web
			if (defined("FAC_PDF_WWW") && FAC_PDF_WWW) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->trans("Web").": ".FAC_PDF_WWW;
	        elseif ($this->emetteur->url) $carac_emetteur .= ($carac_emetteur ? "\n" : '' ).$outputlangs->trans("Web").": ".$this->emetteur->url;

	        $pdf->SetFont('Arial','',9);
	        $pdf->SetXY($this->marge_gauche+2,$posy+8);
	        $pdf->MultiCell(80,4, $carac_emetteur);

	        // Client destinataire
	        $posy=42;
	        $pdf->SetTextColor(0,0,0);
	        $pdf->SetFont('Arial','',8);
	        $pdf->SetXY(102,$posy-5);
	        $pdf->MultiCell(80,5, $outputlangs->trans("BillTo").":");
			$object->fetch_client();

	        // Cadre client destinataire
	        $pdf->rect(100, $posy, 100, $hautcadre);

			// Nom client
	        $pdf->SetXY(102,$posy+3);
	        $pdf->SetFont('Arial','B',11);
	        $pdf->MultiCell(106,4, $object->client->nom, 0, 'L');

			// Caract�ristiques client
	        $carac_client=$object->client->adresse;
	        $carac_client.="\n".$object->client->cp . " " . $object->client->ville."\n";
            if ($this->emetteur->pays_code != $object->client->pays_code)
            {
            	$carac_client.=$object->client->pays."\n";
            }
			if ($object->client->tva_intra) $carac_client.="\n".$outputlangs->trans("VATIntraShort").': '.$object->client->tva_intra;
	        $pdf->SetFont('Arial','',9);
	        $pdf->SetXY(102,$posy+8);
	        $pdf->MultiCell(86,4, $carac_client);
        }

    }

    /*
     *   \brief      Affiche le pied de page
     *   \param      pdf     objet PDF
     */
    function _pagefoot(&$pdf,$outputlangs)
    {
		global $conf;
        $html=new Form($this->db);

        // Premiere ligne d'info r�glementaires
        $ligne1="";
        if ($this->emetteur->forme_juridique_code)
        {
            $ligne1.=($ligne1?" - ":"").$html->forme_juridique_name($this->emetteur->forme_juridique_code);
        }
        if ($this->emetteur->capital)
        {
            $ligne1.=($ligne1?" - ":"").$outputlangs->trans("CapitalOf",$this->emetteur->capital)." ".$outputlangs->trans("Currency".$conf->monnaie);
        }
        if ($this->emetteur->profid2)
        {
            $ligne1.=($ligne1?" - ":"").$outputlangs->transcountry("ProfId2",$this->emetteur->pays_code).": ".$this->emetteur->profid2;
        }
        if ($this->emetteur->profid1 && (! $this->emetteur->profid2 || $this->emetteur->pays_code != 'FR'))
        {
            $ligne1.=($ligne1?" - ":"").$outputlangs->transcountry("ProfId1",$this->emetteur->pays_code).": ".$this->emetteur->profid1;
        }

        // Deuxieme ligne d'info r�glementaires
        $ligne2="";
        if ($this->emetteur->profid3)
        {
            $ligne2.=($ligne2?" - ":"").$outputlangs->transcountry("ProfId3",$this->emetteur->pays_code).": ".$this->emetteur->profid3;
        }
        if ($this->emetteur->profid4)
        {
            $ligne2.=($ligne2?" - ":"").$outputlangs->transcountry("ProfId4",$this->emetteur->pays_code).": ".$this->emetteur->profid4;
        }
        if ($this->emetteur->tva_intra != '')
        {
            $ligne2.=($ligne2?" - ":"").$outputlangs->trans("VATIntraShort").": ".$this->emetteur->tva_intra;
        }

        $pdf->SetFont('Arial','',8);
        $pdf->SetDrawColor(224,224,224);

        // On positionne le debut du bas de page selon nbre de lignes de ce bas de page
        $posy=$this->marge_basse + 1 + ($ligne1?3:0) + ($ligne2?3:0);

        $pdf->SetY(-$posy);
        $pdf->line($this->marge_gauche, $this->page_hauteur-$posy, 200, $this->page_hauteur-$posy);
        $posy--;

        if ($ligne1)
        {
            $pdf->SetXY($this->marge_gauche,-$posy);
            $pdf->MultiCell(200, 2, $ligne1, 0, 'C', 0);
        }

        if ($ligne2)
        {
            $posy-=3;
            $pdf->SetXY($this->marge_gauche,-$posy);
            $pdf->MultiCell(200, 2, $ligne2, 0, 'C', 0);
        }

        $pdf->SetXY(-20,-$posy);
        $pdf->MultiCell(10, 2, $pdf->PageNo().'/{nb}', 0, 'R', 0);
    }

}

// Cette fonction est appel�e pour coder ou non une chaine en html
// selon qu'on compte l'afficher dans le PDF avec:
// writeHTMLCell -> a besoin d'etre encod� en HTML
// MultiCell -> ne doit pas etre encod� en HTML
function _dol_htmlentities($stringtoencode,$isstringalreadyhtml)
{
	global $conf;

	if ($isstringalreadyhtml) return $stringtoencode;
	if ($conf->fckeditor->enabled) return htmlentities($stringtoencode);
	return $stringtoencode;
}

?>
