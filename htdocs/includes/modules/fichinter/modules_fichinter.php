<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**	
        \file       htdocs/includes/modules/fichinter/modules_fichinter.php
		\ingroup    ficheinter
		\brief      Fichier contenant la classe m�re de generation des fiches interventions en PDF
		            et la classe m�re de num�rotation des fiches interventions
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT.'/lib/functions.inc.php');
require_once(DOL_DOCUMENT_ROOT.'/includes/fpdf/fpdfi/fpdi_protection.php');


/**
    	\class      ModelePDFFicheinter
		\brief      Classe m�re des mod�les de fiche intervention
*/
class ModelePDFFicheinter extends FPDF
{
    var $error='';

    /**
        \brief      Constructeur
     */
    function ModelePDFFicheinter()
    {
    
    }

    /** 
        \brief      Renvoi le dernier message d'erreur de cr�ation de fiche intervention
     */
    function pdferror()
    {
        return $this->error;
    }

    /** 
     *      \brief      Renvoi la liste des mod�les actifs
     */
    function liste_modeles($db)
    {
        $type='ficheinter';
        $liste=array();
        $sql ="SELECT nom as id, nom as lib";
        $sql.=" FROM ".MAIN_DB_PREFIX."document_model";
        $sql.=" WHERE type = '".$type."'";
        
        $resql = $db->query($sql);
        if ($resql)
        {
            $num = $db->num_rows($resql);
            $i = 0;
            while ($i < $num)
            {
                $row = $db->fetch_row($resql);
                $liste[$row[0]]=$row[1];
                $i++;
            }
        }
        else
        {
            return -1;
        }
        return $liste;
    }

}


/**
        \class      ModeleNumRefFicheinter
		\brief      Classe m�re des mod�les de num�rotation des r�f�rences de fiches d'intervention
*/

class ModeleNumRefFicheinter
{
    var $error='';

    /**     \brief      Renvoi la description par defaut du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
        global $langs;
        $langs->load("ficheinter");
        return $langs->trans("NoDescription");
    }

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        global $langs;
        $langs->load("ficheinter");
        return $langs->trans("NoExample");
    }

    /**     \brief      Test si les num�ros d�j� en vigueur dans la base ne provoquent pas de
     *                  de conflits qui empechera cette num�rotation de fonctionner.
     *      \return     boolean     false si conflit, true si ok
     */
    function canBeActivated()
    {
        return true;
    }

    /**     \brief      Renvoi prochaine valeur attribu�e
     *      \return     string      Valeur
     */
    function getNextValue()
    {
        global $langs;
        return $langs->trans("NotAvailable");
    }

	/**     \brief      Renvoi version du module numerotation
	*      	\return     string      Valeur
	*/
	function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') return $langs->trans("VersionDevelopment");
		if ($this->version == 'experimental') return $langs->trans("VersionExperimental");
		if ($this->version == 'dolibarr') return DOL_VERSION;
		return $langs->trans("NotAvailable");
	}	
}


/**
		\brief      Cr�e une fiche intervention sur disque en fonction du mod�le de FICHEINTER_ADDON_PDF
		\param	    db  			objet base de donn�e
		\param	    id				id de la fiche � cr�er
		\param	    modele			force le modele � utiliser ('' par defaut)
		\param		outputlangs		objet lang a utiliser pour traduction
        \return     int         	0 si KO, 1 si OK
*/
function fichinter_pdf_create($db, $id, $modele='', $outputlangs='')
{
	global $conf,$langs;
	$langs->load("ficheinter");
	
	$dir = DOL_DOCUMENT_ROOT."/includes/modules/fichinter/";
	
	// Positionne modele sur le nom du modele de facture � utiliser
	if (! strlen($modele))
	{
		if ($conf->global->FICHEINTER_ADDON_PDF)
		{
			$modele = $conf->global->FICHEINTER_ADDON_PDF;
		}
		else
		{
			print $langs->trans("Error")." ".$langs->trans("Error_FICHEINTER_ADDON_PDF_NotDefined");
			return 0;
		}
	}
	
	// Charge le modele
	$file = "pdf_".$modele.".modules.php";
	if (file_exists($dir.$file))
	{
		$classname = "pdf_".$modele;
		require_once($dir.$file);
	
		$obj = new $classname($db);
	
		if ($obj->write_pdf_file($id,$outputlangs) > 0)
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

/**
   \brief     Supprime l'image de pr�visualitation, pour le cas de r�g�n�ration de propal
   \param	    db  		objet base de donn�e
   \param	    propalid	id de la propal � effacer
   \param     propalref r�f�rence de la propal si besoin
*/
function fichinter_delete_preview($db, $fichinterid, $fichinterref='')
{
	global $langs,$conf;
	
	if (!$fichinterref)
  {
  	$fichinter = new Fichinter($db,"",$fichinterid);
    $fichinter->fetch($fichinterid);
    $fichinterref = $fichinter->ref;
   }
   
   if ($conf->fichinter->dir_output)
   {
   	$fichinterref = sanitize_string($fichinterref);
    $dir = $conf->fichinter->dir_output . "/" . $fichinterref ;
    $file = $dir . "/" . $fichinterref . ".pdf.png";
    $multiple = $file . ".";

    if ( file_exists( $file ) && is_writable( $file ) )
    {
    	if ( ! unlink($file) )
    	{
    		$this->error=$langs->trans("ErrorFailedToOpenFile",$file);
    		return 0;
    	}
   	}
   	else
    {
    	for ($i = 0; $i < 20; $i++)
    	{
    		$preview = $multiple.$i;
     		if ( file_exists( $preview ) && is_writable( $preview ) )
     		{
    			if ( ! unlink($preview) )
     			{
    				$this->error=$langs->trans("ErrorFailedToOpenFile",$preview);
     				return 0;
     			}
     		}
     	}
    }
  }
}

?>
