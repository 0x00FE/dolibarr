<?PHP
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/*!	\file htdocs/includes/modules/propale/modules_propale.php
		\ingroup    propale
		\brief      Fichier contenant la classe m�re de generation des propales en PDF
		            et la classe m�re de num�rotation des propales
		\version    $Revision$
*/



/*!	\class ModelePDFPropales
		\brief  Classe m�re des mod�les de propale
*/

class ModelePDFPropales extends FPDF
{
    var $error='';

   /*! 
        \brief Renvoi le dernier message d'erreur de cr�ation de propale
    */
    function pdferror()
    {
        return $this->error;
    }

}


/*!	\class ModeleNumRefPropales
		\brief  Classe m�re des mod�les de num�rotation des r�f�rences de propales
*/

class ModeleNumRefPropales
{
    var $error='';

    /*!     \brief      Renvoi la description par defaut du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
        global $langs;
        $langs->load("propale");
        return $langs->trans("NoDescription");
    }

    /*!     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        global $langs;
        $langs->load("propale");
        return $langs->trans("NoExample");
    }

   /*! 
        \brief Renvoi le dernier message d'erreur de cr�ation de propale
    */
    function numreferror()
    {
        return $this->error;
    }

}


/*!
		\brief      Cr�e une propale sur disque en fonction du mod�le de PROPALE_ADDON_PDF
		\param	    db  		objet base de donn�e
		\param	    facid		id de la facture � cr�er
		\param	    modele		force le modele � utiliser ('' par defaut)
*/
function propale_pdf_create($db, $facid, $modele='')
{
  global $langs;
  $langs->load("propale");
 
  $dir = DOL_DOCUMENT_ROOT."/includes/modules/propale/";

  // Positionne modele sur le nom du modele de facture � utiliser
  if (! strlen($modele))
    {
      if (defined("PROPALE_ADDON_PDF") && PROPALE_ADDON_PDF)
	{
	  $modele = PROPALE_ADDON_PDF;
	}
      else
	{
      print $langs->trans("Error")." ".$langs->trans("Error_PROPALE_ADDON_PDF_NotDefined");
	  return 0;
	}
    }

  // Charge le modele
  $file = "pdf_propale_".$modele.".modules.php";
  if (file_exists($dir.$file))
    {
      $classname = "pdf_propale_".$modele;
      require_once($dir.$file);
  
      $obj = new $classname($db);

      if ( $obj->write_pdf_file($facid) > 0)
	{
	  return 1;
	}
      else
	{
	  dolibarr_print_error($db,$obj->pdferror());
	  return 0;
	}
    }
  else
    {
      print $langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists",$dir.$file);
      return 0;
    }
}
?>
