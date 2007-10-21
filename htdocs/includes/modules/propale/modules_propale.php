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
 */

/**
        \file       htdocs/includes/modules/propale/modules_propale.php
		\ingroup    propale
		\brief      Fichier contenant la classe m�re de generation des propales en PDF
	                et la classe m�re de num�rotation des propales
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT.'/lib/functions.inc.php');
require_once(DOL_DOCUMENT_ROOT.'/includes/fpdf/fpdfi/fpdi_protection.php');
require_once(DOL_DOCUMENT_ROOT."/compta/bank/account.class.php");   // Requis car utilis� dans les classes qui h�ritent


/**
    	\class      ModelePDFPropales
		\brief      Classe m�re des mod�les de propale
*/

class ModelePDFPropales extends FPDF
{
    var $error='';

    /**
     *      \brief      Renvoi le dernier message d'erreur de cr�ation de propale
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
        $type='propal';
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
            $this->error=$db->error();
            return -1;
        }
        return $liste;
    }
}


/**
        \class      ModeleNumRefPropales
        \brief      Classe m�re des mod�les de num�rotation des r�f�rences de propales
*/

class ModeleNumRefPropales
{
    var $error='';

    /**     \brief      Renvoi la description par defaut du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
        global $langs;
        $langs->load("propale");
        return $langs->trans("NoDescription");
    }

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        global $langs;
        $langs->load("propale");
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
		\brief      Cr�e une propale sur disque en fonction du mod�le de PROPALE_ADDON_PDF
		\param	    db  			objet base de donn�e
		\param	    id				id de la propale � cr�er
		\param	    modele			force le modele � utiliser ('' par defaut)
		\param		outputlangs		objet lang a utiliser pour traduction
        \return     int         	0 si KO, 1 si OK
*/
function propale_pdf_create($db, $id, $modele='', $outputlangs='')
{
	global $langs;
	$langs->load("propale");

	$dir = DOL_DOCUMENT_ROOT."/includes/modules/propale/";
	$modelisok=0;

	// Positionne modele sur le nom du modele de propale � utiliser
	$file = "pdf_propale_".$modele.".modules.php";
	if ($modele && file_exists($dir.$file)) $modelisok=1;

    // Si model pas encore bon 
	if (! $modelisok)
	{
		if ($conf->global->PROPALE_ADDON_PDF) $modele = $conf->global->PROPALE_ADDON_PDF;
      	$file = "pdf_propale_".$modele.".modules.php";
    	if (file_exists($dir.$file)) $modelisok=1;
	}

    // Si model pas encore bon 
	if (! $modelisok)
	{
	    $liste=array();
		$model=new ModelePDFPropales();
		$liste=$model->liste_modeles($db);
        $modele=key($liste);        // Renvoie premiere valeur de cl� trouv� dans le tableau
      	$file = "pdf_propale_".$modele.".modules.php";
    	if (file_exists($dir.$file)) $modelisok=1;
	}
	

	// Charge le modele
    if ($modelisok)
	{
		$classname = "pdf_propale_".$modele;
		require_once($dir.$file);

		$obj = new $classname($db);

		if ($obj->write_file($id, $outputlangs) > 0)
		{
			// on supprime l'image correspondant au preview
			propale_delete_preview($db, $id);
			return 1;
		}
		else
		{
			dolibarr_syslog("Erreur dans propale_pdf_create");
			dolibarr_print_error($db,$obj->pdferror());
			return 0;
		}
	}
	else
	{
        if (! $conf->global->PROPALE_ADDON_PDF)
        {
			print $langs->trans("Error")." ".$langs->trans("Error_PROPALE_ADDON_PDF_NotDefined");
        }
        else
        {
    		print $langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists",$dir.$file);
        }
		return 0;
	}
}

/**
   \brief      Supprime l'image de pr�visualitation, pour le cas de r�g�n�ration de propal
   \param	    db  		objet base de donn�e
   \param	    propalid	id de la propal � effacer
   \param     propalref r�f�rence de la propal si besoin
*/
function propale_delete_preview($db, $propalid, $propalref='')
{
        global $langs,$conf;

        if (!$propalref)
        {
        	$propal = new Propal($db,"",$propalid);
        	$propal->fetch($propalid);
        	$propalref = $propal->ref;
        }

        if ($conf->propal->dir_output)
        {
        	$propalref = sanitize_string($propalref);
        	$dir = $conf->propal->dir_output . "/" . $propalref ;
        	$file = $dir . "/" . $propalref . ".pdf.png";
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
