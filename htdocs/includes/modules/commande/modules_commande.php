<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
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
            \file       htdocs/includes/modules/commande/modules_commande.php
                \ingroup    commande
                \brief      Fichier contenant la classe m�re de generation des commandes en PDF
                            et la classe m�re de num�rotation des commandes
                \version    $Revision$
*/

require_once(FPDF_PATH.'fpdf.php');


/**
            \class      ModelePDFCommandes
                \brief      Classe m�re des mod�les de commandes
*/

class ModelePDFCommandes extends FPDF
{
    var $error='';

   /** 
        \brief Renvoi le dernier message d'erreur de cr�ation de PDF de commande
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
        $type='order';
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
        \class      ModeleNumRefCommandes
            \brief      Classe m�re des mod�les de num�rotation des r�f�rences de commandes
*/

class ModeleNumRefCommandes
{
    var $error='';

    /**     \brief      Renvoi la description par defaut du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
        global $langs;
        $langs->load("orders");
        return $langs->trans("NoDescription");
    }

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        global $langs;
        $langs->load("orders");
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
    
}


/*
		\brief      Cr�e un bon de commande sur disque en fonction du mod�le de COMMANDE_ADDON_PDF
		\param	    db  			objet base de donn�e
		\param	    id				id de la propale � cr�er
		\param	    modele			force le modele � utiliser ('' par defaut)
		\param		outputlangs		objet lang a utiliser pour traduction
*/
function commande_pdf_create($db, $id, $modele='', $outputlangs='')
{
	global $conf,$langs;
	$langs->load("orders");
	
	$dir = DOL_DOCUMENT_ROOT."/includes/modules/commande/";
	
	// Positionne modele sur le nom du modele de facture � utiliser
	if (! strlen($modele))
	{
		if ($conf->global->COMMANDE_ADDON_PDF)
		{
			$modele = $conf->global->COMMANDE_ADDON_PDF;
		}
		else
		{
			print $langs->trans("Error")." ".$langs->trans("Error_COMMANDE_ADDON_PDF_NotDefined");
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
	
		if ( $obj->write_pdf_file($id, $outputlangs) > 0)
		{
			// on supprime l'image correspondant au preview
			commande_delete_preview($db, $id);
			return 1;
		}
		else
		{
			dolibarr_syslog("Erreur dans commande_pdf_create");
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


function commande_delete_preview($db, $propalid)
{
        global $langs,$conf;

        $com = new Commande($db,"",$propalid);
        $com->fetch($propalid);  
        $client = new Societe($db);
    $client->fetch($com->soc_id);

        if ($conf->commande->dir_output)
                {
                $comref = sanitize_string($com->ref); 
                $dir = $conf->commande->dir_output . "/" . $comref ; 
                $file = $dir . "/" . $comref . ".pdf.png";

                if ( file_exists( $file ) && is_writable( $file ) )
                        {
                        if ( ! unlink($file) )
                                {
                                $this->error=$langs->trans("ErrorFailedToOpenFile",$file);
                                return 0;
                                }
                        }
                }
}
?>
