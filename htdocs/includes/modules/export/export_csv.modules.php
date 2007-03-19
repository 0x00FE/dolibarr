<?php
/* Copyright (C) 2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
       	\file       htdocs/includes/modules/export/export_csv.modules.php
		\ingroup    export
		\brief      Fichier de la classe permettant de g�n�rer les export au format CSV
		\author	    Laurent Destailleur
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/export/modules_export.php");


/**
	    \class      ExportCsv
		\brief      Classe permettant de g�n�rer les factures au mod�le Crabe
*/

class ExportCsv extends ModeleExports
{
    var $id;
    var $label;
    var $extension;
    var $version;

    var $label_lib;
    var $version_lib;

    var $handle;    // Handle fichier

    
    /**
    		\brief      Constructeur
    		\param	    db      Handler acc�s base de donn�e
    */
    function ExportCsv($db)
    {
        global $conf,$langs;
        $this->db = $db;

        $this->id='csv';                // Same value then xxx in file name export_xxx.modules.php
        $this->label='Csv';             // Label of driver
        $this->extension='csv';         // Extension for generated file by this driver
        $ver=split(' ','$Revision$');
        $this->version=$ver[2];         // Driver version

        // If driver use an external library, put its name here
        $this->label_lib='Dolibarr';            
        $this->version_lib=DOL_VERSION;
    }

    function getDriverId()
    {
        return $this->id;
    }

    function getDriverLabel()
    {
        return $this->label;
    }

    function getDriverExtension()
    {
        return $this->extension;
    }

    function getDriverVersion()
    {
        return $this->version;
    }

    function getLibLabel()
    {
        return $this->label_lib;
    }

    function getLibVersion()
    {
        return $this->version_lib;
    }


    function open_file($file)
    {
        dolibarr_syslog("ExportCsv::open_file file=$file");
        $this->handle = fopen($file, "wt");
        return 0;
    }


    function write_header($langs)
    {
        return 0;
    }


    function write_title($array_export_fields_label,$array_selected_sorted,$langs)
    {
        foreach($array_selected_sorted as $code => $value)
        {
            fwrite($this->handle,$langs->transnoentities($array_export_fields_label[$code]).";");
        }
        fwrite($this->handle,"\n");
        return 0;
    }


    function write_record($array_alias,$array_selected_sorted,$objp)
    {
        foreach($array_selected_sorted as $code => $value)
        {
            $alias=$array_alias[$code];
            //print "dd".$alias;
            $newvalue=ereg_replace(';',',',$objp->$alias);
            $newvalue=ereg_replace("\r",'',$newvalue);
            $newvalue=ereg_replace("\n",'\n',$newvalue);
            fwrite($this->handle,$newvalue.";");
        }
        fwrite($this->handle,"\n");
        return 0;
    }


    function write_footer($langs)
    {
        return 0;
    }
    

    function close_file()
    {
        fclose($this->handle);
        return 0;
    }

}

?>
