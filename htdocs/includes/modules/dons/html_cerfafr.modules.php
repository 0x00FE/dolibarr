<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 * $Id$
 * $Source$
 */

/**
			\file       htdocs/includes/dons/html_cerfafr.php
			\ingroup    don
			\brief      Formulaire de don
			\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/master.inc.php");
require_once(DOL_DOCUMENT_ROOT."/projetdon.class.php");
require_once(DOL_DOCUMENT_ROOT."/don.class.php");



/**
	    \class      html_cerfafr
		\brief      Classe permettant de g�n�rer les propales au mod�le Azur
*/

class html_cerfafr extends ModeleDon
{
    /**
			\brief      Constructeur
    		\param	    db		Handler acc�s base de donn�e
    */
    function html_cerfafr($db)
    {
        global $conf,$langs;

        $this->db = $db;
        $this->name = "cerfafr";
        $this->description = "Mod�le de re�u de dons";
    }


    /**
    	    \brief      Renvoi derni�re erreur
            \return     string      Derni�re erreur
    */
    function pdferror() 
    {
      return $this->error;
    }


    /**
    		\brief      Fonction g�n�rant le recu sur le disque
    		\param	    id	        Id du recu � g�n�rer
    		\return	    int         >0 si ok, <0 si ko
    */
    function write_file($id)
    {
        global $conf,$langs,$user,$mysoc;
        $langs->load("main");
        
        $don = new Don($this->db);
        $don->fetch($id);

        $filename = sanitize_string($don->id);
		$dir = $conf->don->dir_output . "/" . get_exdir("${filename}");
		$file = $dir . "/" . $filename . ".html";

        if (! is_dir($dir))
        {
            if (create_exdir($dir) < 0)
            {
                $this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
                return -1;
            }
        }

        // Defini contenu
        $donmodel=DOL_DOCUMENT_ROOT ."/includes/modules/dons/html_cerfafr.html";
        $html = implode('', file($donmodel));
        $html = eregi_replace('__REF__',$id,$html);
        $html = eregi_replace('__DATE__',dolibarr_print_date($don->date),$html);
        $html = eregi_replace('__IP__',$user->ip,$html);
        $html = eregi_replace('__AMOUNT__',$don->amount,$html);
        $html = eregi_replace('__CURRENCY__',$langs->trans("Currency".$conf->monnaie),$html);
        $html = eregi_replace('__CURRENCYCODE__',$conf->monnaie,$html);
        $html = eregi_replace('__MAIN_INFO_SOCIETE_NOM__',$mysoc->nom,$html);
        $html = eregi_replace('__MAIN_INFO_SOCIETE_ADRESSE__',$mysoc->adresse,$html);
        $html = eregi_replace('__MAIN_INFO_SOCIETE_CP__',$mysoc->cp,$html);
        $html = eregi_replace('__MAIN_INFO_SOCIETE_VILLE__',$mysoc->ville,$html);
        $html = eregi_replace('__DONATOR_NAME__',$don->nom,$html);
        $html = eregi_replace('__DONATOR_ADDRESS__',$don->adresse,$html);
        $html = eregi_replace('__DONATOR_ZIP__',$don->cp,$html);
        $html = eregi_replace('__DONATOR_TOWN__',$don->ville,$html);
        $html = eregi_replace('__PAYMENTMODE_LIB__ ',$don->modepaiement,$html);
        $html = eregi_replace('__NOW__',dolibarr_print_date(time()),$html);
        
        // Sauve fichier sur disque
        dolibarr_syslog("html_cerfafr::write_file $file");
        $handle=fopen($file,"w");        
        fwrite($handle,$html);
        fclose($handle);

        return 1;
    }
}

?>
