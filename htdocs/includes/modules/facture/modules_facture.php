<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
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

/*!	    \file       htdocs/includes/modules/facture/modules_facture.php
		\ingroup    facture
		\brief      Fichier contenant la classe m�re de generation des factures en PDF
		            et la classe m�re de num�rotation des factures
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/product.class.php");
require_once(DOL_DOCUMENT_ROOT."/compta/bank/account.class.php");   // Requis car utilis� dans les classes qui h�ritent



/*!	    \class  ModelePDFFactures
		\brief  Classe m�re des mod�les de facture
*/

class ModelePDFFactures extends FPDF
{
    var $error='';

   /*! 
        \brief Renvoi le dernier message d'erreur de cr�ation de facture
    */
    function pdferror()
    {
        return $this->error;
    }

}


/*!	\class ModeleNumRefFactures
	\brief  Classe m�re des mod�les de num�rotation des r�f�rences de facture
*/

class ModeleNumRefFactures
{
    var $error='';

    /*!     \brief      Renvoi la description par defaut du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
        global $langs;
        $langs->load("bills");
        return $langs->trans("NoDescription");
    }

    /*!     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        global $langs;
        $langs->load("bills");
        return $langs->trans("NoExample");
    }

   /*! 
        \brief Renvoi le dernier message d'erreur de cr�ation de facture
    */
    function numreferror()
    {
        return $this->error;
    }

}


/*!
  \brief      Cr�e un facture sur disque en fonction du mod�le de FACTURE_ADDON_PDF
  \param	    db  		objet base de donn�e
  \param	    facid		id de la facture � cr�er
*/
function facture_pdf_create($db, $facid, $message="")
{
  global $langs;
  $langs->load("bills");
  
  $dir = DOL_DOCUMENT_ROOT . "/includes/modules/facture/";

  if (defined("FACTURE_ADDON_PDF") && FACTURE_ADDON_PDF)
    {

      $file = "pdf_".FACTURE_ADDON_PDF.".modules.php";

      $classname = "pdf_".FACTURE_ADDON_PDF;
      require_once($dir.$file);

      $obj = new $classname($db);

      $obj->message = $message;

      if ( $obj->write_pdf_file($facid) > 0)
	{
	  return 1;
	}
      else
	{
	  dolibarr_syslog("Erreur dans facture_pdf_create");
	  dolibarr_print_error($db,$obj->pdferror());
	  return 0;
	}
    }
  else
    {
      print $langs->trans("Error")." ".$langs->trans("Error_FACTURE_ADDON_PDF_NotDefined");
      return 0;
    }
}

/*!
  \brief      Cr�� un meta fichier � c�t� de la facture sur le disque pour faciliter les recherches en texte plein. Pourquoi ? tout simplement parcequ'en fin d'exercice quand je suis avec mon comptable je n'ai pas de connexion internet "rapide" pour retrouver en 2 secondes une facture non pay�e ou compliqu�e � g�rer ... avec un rgrep c'est vite fait bien fait [eric seigne]
  \param	    db  		objet base de donn�e
  \param	    facid		id de la facture � cr�er
*/
function facture_meta_create($db, $facid, $message="")
{
  $fac = new Facture($db,"",$facid);
  $fac->fetch($facid);  
  $fac->fetch_client();
  if (defined("FAC_OUTPUTDIR"))
    {
      $dir = FAC_OUTPUTDIR . "/" . $fac->ref . "/" ;
      $file = $dir . $fac->ref . ".meta";
      if (! file_exists($dir))
	{
	  umask(0);
	  if (! mkdir($dir, 0755))
	    {
	      print "Impossible de cr�er $dir !";
	    }
	}
      if (file_exists($dir))
	{
	  $nblignes = sizeof($fac->lignes);
	  $client = $fac->client->nom . " " . $fac->client->adresse . " " . $fac->client->cp . " " . $fac->client->ville;
	  $meta = "REFERENCE=\"" . $fac->ref . "\"
DATE=\"" . strftime("%d/%m/%Y",$fac->date) . "\"
NB_ITEMS=\"" . $nblignes . "\"
CLIENT=\"" . $client . "\"
TOTAL_HT=\"" . $fac->total_ht . "\"
TOTAL_TTC=\"" . $fac->total_ttc . "\"\n";

	  for ($i = 0 ; $i < $nblignes ; $i++) {
	    //Pour les articles
	    $meta .= "ITEM_" . $i . "_QUANTITY=\"" . $fac->lignes[$i]->qty . "\"
ITEM_" . $i . "_UNIT_PRICE=\"" . $fac->lignes[$i]->price . "\"
ITEM_" . $i . "_TVA=\"" .$fac->lignes[$i]->tva_taux . "\"
ITEM_" . $i . "_DESCRIPTION=\"" . str_replace("\r\n","",nl2br($fac->lignes[$i]->desc)) . "\"
";
	  }
	}
      $fp = fopen ($file,"w");
      fputs($fp,$meta);
      fclose($fp);
    }
}


/*!
		\brief      Renvoie la r�f�rence de facture suivante non utilis�e en fonction du module 
		            de num�rotation actif d�fini dans FACTURE_ADDON
		\param	    soc  		objet societe
		\return     string      reference libre pour la facture
*/
function facture_get_num($soc, $prefixe_additionnel='')
{
  global $db, $langs;
  $langs->load("bills");
  
  $dir = DOL_DOCUMENT_ROOT . "/includes/modules/facture/";

  if (defined("FACTURE_ADDON") && FACTURE_ADDON)
    {

      $file = FACTURE_ADDON."/".FACTURE_ADDON.".modules.php";

      // Chargement de la classe de num�rotation
      $classname = "mod_facture_".FACTURE_ADDON;
      require_once($dir.$file);

      $obj = new $classname();

      $numref = "";
      $numref = $obj->getNumRef($soc, $prefixe_additionnel);

      if ( $numref != "")
	{
	  return $numref;
	}
      else
	{
	  dolibarr_syslog("Erreur dans facture_get_num");
	  dolibarr_print_error($db,$obj->numreferror());
	  return "";
	}
    }
  else
    {
      print $langs->trans("Error")." ".$langs->trans("Error_FACTURE_ADDON_NotDefined");
      return "";
    }
}

?>
