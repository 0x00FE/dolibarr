<?PHP
/* Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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


/*!	\file pdf_crabe.modules.php
		\brief Classe permettant de g�n�rer lune facture au mod�le Crabe
		\author	Laurent Destailleur
		\version $Revision$
*/

Class pdf_crabe {
    var $error='';
    
    /*!
    		\brief Constructeur
    		\param	db		objet base de donn�e
    */
    Function pdf_crabe($db=0)
    {
        $this->db = $db;
        $this->description = "Mod�le de facture classique (G�re l'option fiscale de facturation TVA et le choix du mode de r�glement � afficher)";
    }


    /*!
    		\brief Renvoi le dernier message d'erreur de cr�ation de facture
    */
    Function error()
    {
        return $this->error;
    }


    /*!
    		\brief Fonction g�n�rant la facture sur le disque
    		\param	facid		id de la facture � g�n�rer
            \remarks Variables utilis�es
    		\remarks FAC_OUTPUTDIR
    		\remarks FACTURE_CODEPRODUITSERVICE
    		\remarks FACTURE_CHQ_NUMBER
    		\remarks FACTURE_RIB_NUMBER
    		\remarks FAC_OUTPUTDIR
    		\return	1=ok, 0=ko
    */
    Function write_pdf_file($facid)
    {
        global $user;
        $fac = new Facture($this->db,"",$facid);
        $fac->fetch($facid);
        if (defined("FAC_OUTPUTDIR"))
        {

            $dir = FAC_OUTPUTDIR . "/" . $fac->ref . "/" ;
            $file = $dir . $fac->ref . ".pdf";

            if (! file_exists($dir))
            {
                umask(0);
                if (! mkdir($dir, 0755))
                {
                    $this->error="Erreur: Le r�pertoire '$dir' n'existe pas et Dolibarr n'a pu le cr�er.";
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
                $pdf->SetSubject("Facture");
                $pdf->SetCreator("Dolibarr ".DOL_VERSION);
                $pdf->SetAuthor($user->fullname);

                $tab_top = 96;
                $tab_height = 110;

                $pdf->SetFillColor(220,220,220);
                $pdf->SetFont('Arial','', 9);
                $pdf->SetXY (10, $tab_top + 10 );

                $iniY = $pdf->GetY();
                $curY = $pdf->GetY();
                $nexY = $pdf->GetY();
                $nblignes = sizeof($fac->lignes);

                // Boucle sur les lignes de factures
                for ($i = 0 ; $i < $nblignes ; $i++)
                {
                    $curY = $nexY;

                    // Description produit
                    $codeproduitservice="";
                    $pdf->SetXY (11, $curY );
                    if (defined("FACTURE_CODEPRODUITSERVICE") && FACTURE_CODEPRODUITSERVICE) {
                        // Affiche code produit si ligne associ�e � un code produit
                        $codeproduitservice=" (Code produit ".$fac->lignes[$i]->produit_id.")";
                    }
                    if ($fac->lignes[$i]->date_start && $fac->lignes[$i]->date_end) {
                        // Affichage dur�e si il y en a une
                        $codeproduitservice=" (Du ".dolibarr_print_date($fac->lignes[$i]->date_start)." au ".dolibarr_print_date($fac->lignes[$i]->date_end).")";
                    }
                    $pdf->MultiCell(118, 5, $fac->lignes[$i]->desc."$codeproduitservice", 0, 'J');

                    $nexY = $pdf->GetY();

                    // TVA
                    $pdf->SetXY (121, $curY);
                    $pdf->MultiCell(10, 5, $fac->lignes[$i]->tva_taux, 0, 'C');

                    // Prix unitaire HT
                    $pdf->SetXY (133, $curY);
                    $pdf->MultiCell(16, 5, price($fac->lignes[$i]->price), 0, 'R', 0);

                    // Quantit�
                    $pdf->SetXY (151, $curY);
                    $pdf->MultiCell(10, 5, $fac->lignes[$i]->qty, 0, 'R');

                    // Remise sur ligne
                    $pdf->SetXY (163, $curY);
                    if ($fac->lignes[$i]->remise_percent) {
                        $pdf->MultiCell(14, 5, $fac->lignes[$i]->remise_percent."%", 0, 'R');
                    }

                    // Total HT
                    $pdf->SetXY (173, $curY);
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

                $deja_regle = $fac->getSommePaiement();

                $this->_tableau_tot($pdf, $fac, $deja_regle);

                if ($deja_regle) {            
                    $this->_tableau_versements($pdf, $fac);
                }

                /*
                * Mode de r�glement
                */
                if ((! defined("FACTURE_CHQ_NUMBER") || ! FACTURE_CHQ_NUMBER) && (! defined("FACTURE_RIB_NUMBER") || ! FACTURE_RIB_NUMBER)) {
                    $pdf->SetXY (10, 228);
                    $pdf->SetTextColor(200,0,0);
                    $pdf->SetFont('Arial','B',8);
                    $pdf->MultiCell(90, 3, "Aucun mode de r�glement d�fini.",0,'L',0);
                    $pdf->MultiCell(90, 3, "Cr�er un compte bancaire puis aller dans la Configuration du module facture pour d�finir les modes de r�glement.",0,'L',0);
                    $pdf->SetTextColor(0,0,0);
                }

                /*
                * Propose mode r�glement par CHQ
                */
                if (defined("FACTURE_CHQ_NUMBER"))
                {
                    if (FACTURE_CHQ_NUMBER > 0)
                    {
                        $account = new Account($this->db);
                        $account->fetch(FACTURE_CHQ_NUMBER);

                        $pdf->SetXY (10, 228);
                        $pdf->SetFont('Arial','B',8);
                        $pdf->MultiCell(90, 3, "R�glement par ch�que � l'ordre de ".$account->proprio." envoy� �:",0,'L',0);
                        $pdf->SetFont('Arial','',8);
                        $pdf->MultiCell(80, 3, $account->adresse_proprio, 0, 'L', 0);
                    }
                }

                /*
                * Propose mode r�glement par RIB
                */
                if (defined("FACTURE_RIB_NUMBER"))
                {
                    if (FACTURE_RIB_NUMBER > 0)
                    {
                        $account = new Account($this->db);
                        $account->fetch(FACTURE_RIB_NUMBER);

                        $pdf->SetXY (10, 241);
                        $pdf->SetFont('Arial','B',8);
                        $pdf->MultiCell(90, 3, "R�glement par virement sur le compte ci-dessous:", 0, 'L', 0);
                        $pdf->SetFont('Arial','',8);
                        $pdf->MultiCell(90, 3, "Code banque : " . $account->code_banque, 0, 'L', 0);
                        $pdf->MultiCell(90, 3, "Code guichet : " . $account->code_guichet, 0, 'L', 0);
                        $pdf->MultiCell(90, 3, "Num�ro compte : " . $account->number, 0, 'L', 0);
                        $pdf->MultiCell(90, 3, "Cl� RIB : " . $account->cle_rib, 0, 'L', 0);
                        $pdf->MultiCell(90, 3, "Domiciliation : " . $account->domiciliation, 0, 'L', 0);
                        $pdf->MultiCell(90, 3, "Prefix IBAN : " . $account->iban_prefix, 0, 'L', 0);
                        $pdf->MultiCell(90, 3, "BIC : " . $account->bic, 0, 'L', 0);
                    }
                }

                /*
                * Conditions de r�glements
                */
                $pdf->SetFont('Arial','U',10);
                $pdf->SetXY(10, 217);
                $titre = "Conditions de r�glement:";
                $pdf->MultiCell(80, 5, $titre, 0, 'L');
                $pdf->SetFont('Arial','',10);
                $pdf->SetXY(54, 217);
                $pdf->MultiCell(80, 5, $fac->cond_reglement_facture,0,'L');

                $pdf->Close();

                $pdf->Output($file);

                return 1;   // Pas d'erreur
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
        $this->error="Erreur: Erreur inconnue";
        return 0;   // Erreur par defaut
    }


    /*
    *
    *
    *
    */
    Function _tableau_versements(&$pdf, $fac)
    {
        $tab3_posx = 120;
        $tab3_top = 240;
        $tab3_width = 80;
        $tab3_height = 4;

        $pdf->SetFont('Arial','',8);
        $pdf->SetXY ($tab3_posx, $tab3_top - 5);
        $pdf->MultiCell(60, 5, "Versements d�j� effectu�s", 0, 'L', 0);

        $pdf->Rect($tab3_posx, $tab3_top-1, $tab3_width, $tab3_height);

        $pdf->SetXY ($tab3_posx, $tab3_top-1 );
        $pdf->MultiCell(20, 4, "Paiement", 0, 'L', 0);
        $pdf->SetXY ($tab3_posx+21, $tab3_top-1 );
        $pdf->MultiCell(20, 4, "Montant", 0, 'L', 0);
        $pdf->SetXY ($tab3_posx+41, $tab3_top-1 );
        $pdf->MultiCell(20, 4, "Type", 0, 'L', 0);
        $pdf->SetXY ($tab3_posx+60, $tab3_top-1 );
        $pdf->MultiCell(20, 4, "Num", 0, 'L', 0);

        $sql = "SELECT ".$this->db->pdate("p.datep")."as date, p.amount as amount, p.fk_paiement as type, p.num_paiement as num ";
        $sql.= "FROM ".MAIN_DB_PREFIX."paiement as p, ".MAIN_DB_PREFIX."paiement_facture as pf ";
        $sql.= "WHERE pf.fk_paiement = p.rowid and pf.fk_facture = ".$fac->id." ";
        $sql.= "ORDER BY p.datep";
        if ($this->db->query($sql))
        {
            $pdf->SetFont('Arial','',6);
            $num = $this->db->num_rows();
            $i=0; $y=0;
            while ($i < $num) {
                $y+=3;
                $row = $this->db->fetch_row();
    
                $pdf->SetXY ($tab3_posx, $tab3_top+$y );
                $pdf->MultiCell(20, 4, strftime("%d/%m/%y",$row[0]), 0, 'L', 0);
                $pdf->SetXY ($tab3_posx+21, $tab3_top+$y);
                $pdf->MultiCell(20, 4, $row[1], 0, 'L', 0);
                $pdf->SetXY ($tab3_posx+41, $tab3_top+$y);
            	switch ($row[2])
            	  {
            	  case 1:
            	    $oper = 'TIP';
            	    break;
            	  case 2:
            	    $oper = 'VIR';
            	    break;
            	  case 3:
            	    $oper = 'PRE';
            	    break;
            	  case 4:
            	    $oper = 'LIQ';
            	    break;
            	  case 5:
            	    $oper = 'WWW';
            	    break;
            	  case 6:
            	    $oper = 'CB';
            	    break;
            	  case 7:
            	    $oper = 'CHQ';
            	    break;
                  }
                $pdf->MultiCell(20, 4, $oper, 0, 'L', 0);
                $pdf->SetXY ($tab3_posx+60, $tab3_top+$y);
                $pdf->MultiCell(20, 4, $row[3], 0, 'L', 0);
    
                $pdf->line($tab3_posx, $tab3_top+$y+3, $tab3_posx+$tab3_width, $tab3_top+$y+3 );
                $i++;
            }
        }
        else
        {
            $this->error="Echec requete SQL";
            return 0;
        }

    }


    Function _tableau_tot(&$pdf, $fac, $deja_regle)
    {
        $tab2_top = 207;
        $tab2_hl = 5;
        $tab2_height = $tab2_hl * 4;
        $pdf->SetFont('Arial','', 9);

        // Affiche la mention TVA non applicable selon option
        $pdf->SetXY (10, $tab2_top + 0);
        if (defined("FACTURE_TVAOPTION") && FACTURE_TVAOPTION == 'franchise') {
            $pdf->MultiCell(100, $tab2_hl, "* TVA non applicable art-293B du CGI", 0, 'L', 0);
        }
        // Affiche le num�ro de TVA intracommunautaire
        if (defined("MAIN_INFO_TVAINTRA")) {
            if (MAIN_INFO_TVAINTRA == 'MAIN_INFO_TVAINTRA') {
                $pdf->SetTextColor(200,0,0);
                $pdf->SetFont('Arial','B',8);
                $pdf->MultiCell(90, 3, "Num�ro de TVA intracommunautaire pas encore configur�.",0,'L',0);
                $pdf->MultiCell(90, 3, "Aller dans la Configuration g�n�rale pour le d�finir ou l'effacer.",0,'L',0);
                $pdf->SetTextColor(0,0,0);
            }
            elseif (MAIN_INFO_TVAINTRA != '') {
                $pdf->MultiCell(190, 5, "Num�ro de TVA intracommunautaire : ".MAIN_INFO_TVAINTRA, 0, 'L');
            }
        }

        // Tableau total
        $col1x=120; $col2x=174;
        $pdf->SetXY ($col1x, $tab2_top + 0);
        $pdf->MultiCell($col2x-$col1x, $tab2_hl, "Total HT", 0, 'L', 0);

        $pdf->SetXY ($col2x, $tab2_top + 0);
        $pdf->MultiCell(26, $tab2_hl, price($fac->total_ht + $fac->remise), 0, 'R', 0);

        if ($fac->remise > 0)
        {
            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl);
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, "Remise globale", 0, 'L', 0);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl);
            $pdf->MultiCell(26, $tab2_hl, "-".$fac->remise_percent."%", 0, 'R', 0);

            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * 2);
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, "Total HT apr�s remise", 0, 'L', 0);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * 2);
            $pdf->MultiCell(26, $tab2_hl, price($fac->total_ht), 0, 'R', 0);

            $index = 3;
        }
        else
        {
            $index = 1;
        }

        $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
        $pdf->MultiCell($col2x-$col1x, $tab2_hl, "Total TVA", 0, 'L', 0);

        $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
        $pdf->MultiCell(26, $tab2_hl, price($fac->total_tva), 0, 'R', 0);

        $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * ($index+1));
        $pdf->MultiCell($col2x-$col1x, $tab2_hl, "Total TTC", 0, 'L', 1);

        $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * ($index+1));
        $pdf->MultiCell(26, $tab2_hl, price($fac->total_ttc), 0, 'R', 1);

        if ($deja_regle > 0)
        {
            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * ($index+2));
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, "D�j� r�gl�", 0, 'L', 0);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * ($index+2));
            $pdf->MultiCell(26, $tab2_hl, price($deja_regle), 0, 'R', 0);

            $pdf->SetXY ($col1x, $tab2_top + $tab2_hl * ($index+3));
            $pdf->MultiCell($col2x-$col1x, $tab2_hl, "Reste � payer", 0, 'L', 1);

            $pdf->SetXY ($col2x, $tab2_top + $tab2_hl * ($index+3));
            $pdf->MultiCell(26, $tab2_hl, price($fac->total_ttc - $deja_regle), 0, 'R', 1);
        }
    }

    /*
    * Grille des lignes de factures
    */
    Function _tableau(&$pdf, $tab_top, $tab_height, $nexY)
    {
        $pdf->Rect( 10, $tab_top, 190, $tab_height);
        $pdf->line( 10, $tab_top+8, 200, $tab_top+8 );

        $pdf->SetFont('Arial','',10);

        $pdf->Text(11,$tab_top + 5,'D�signation');

        $pdf->line(120, $tab_top, 120, $tab_top + $tab_height);
        $pdf->Text(122, $tab_top + 5,'TVA');

        $pdf->line(132, $tab_top, 132, $tab_top + $tab_height);
        $pdf->Text(135, $tab_top + 5,'P.U. HT');

        $pdf->line(150, $tab_top, 150, $tab_top + $tab_height);
        $pdf->Text(153, $tab_top + 5,'Qt�');

        $pdf->line(162, $tab_top, 162, $tab_top + $tab_height);
        $pdf->Text(163, $tab_top + 5,'Remise');

        $pdf->line(177, $tab_top, 177, $tab_top + $tab_height);
        $pdf->Text(185, $tab_top + 5,'Total HT');

    }

    /*
    *
    *
    */
    Function _pagehead(&$pdf, $fac)
    {

        $pdf->SetXY(10,5);
        if (defined("FAC_PDF_INTITULE"))
        {
            $pdf->SetTextColor(0,0,60);
            $pdf->SetFont('Arial','B',13);
            $pdf->MultiCell(70, 8, FAC_PDF_INTITULE, 0, 'L');
        }

        $pdf->SetFont('Arial','B',13);
        $pdf->SetXY(100,5);
        $pdf->SetTextColor(0,0,60);
        $pdf->MultiCell(100, 10, "Facture no ".$fac->ref, '' , 'R');
        $pdf->SetFont('Arial','',12);
        $pdf->SetXY(100,11);
        $pdf->SetTextColor(0,0,60);
        $pdf->MultiCell(100, 10, "Date : " . strftime("%d %b %Y", mktime()), '', 'R');

        /*
        * Emetteur
        */
        $posy=42;
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',8);
        $pdf->SetXY(10,$posy-5);
        $pdf->MultiCell(66,5, "Emetteur:");


        $pdf->SetXY(10,$posy);
        $pdf->SetFillColor(230,230,230);
        $pdf->MultiCell(82, 34, "", 0, 'R', 1);


        $pdf->SetXY(10,$posy+4);

        if (defined("FAC_PDF_INTITULE2"))
        {
            $pdf->SetTextColor(0,0,60);
            $pdf->SetFont('Arial','B',10);
            $pdf->MultiCell(70, 4, FAC_PDF_INTITULE2, 0, 'L');
        }
        if (defined("FAC_PDF_ADRESSE"))
        {
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(80, 4, FAC_PDF_ADRESSE);
        }
        if (defined("FAC_PDF_TEL"))
        {
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(40, 4, "T�l : ".FAC_PDF_TEL);
        }
        if (defined("FAC_PDF_SIREN"))
        {
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(40, 4, "SIREN : ".FAC_PDF_SIREN);
        }


        /*
        * Client
        */
        $posy=42;
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',8);
        $pdf->SetXY(102,$posy-5);
        $pdf->MultiCell(80,5, "Adress� �:");
        $pdf->SetFont('Arial','B',11);
        $fac->fetch_client();
        $pdf->SetXY(102,$posy+4);
        $pdf->MultiCell(86,4, $fac->client->nom, 0, 'L');
        $pdf->SetFont('Arial','B',10);
        $pdf->SetXY(102,$posy+12);
        $pdf->MultiCell(86,4, $fac->client->adresse . "\n" . $fac->client->cp . " " . $fac->client->ville);
        $pdf->rect(100, $posy, 100, 34);

        /*
        *
        */
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',10);
        $titre = "Montants exprim�s en euros";
        $pdf->Text(200 - $pdf->GetStringWidth($titre), 94, $titre);
        /*
        */

    }

}

?>
