<?php
/* Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/exports/export.class.php
        \ingroup    core
        \brief      Fichier de la classe des exports
        \version    $Revision$
*/


/**
        \class 		Export
        \brief 		Classe permettant la gestion des exports
*/

class Export
{
    var $array_export_code=array();             // Tableau de "idmodule_numlot"
    var $array_export_module=array();           // Tableau de "nom de modules"
    var $array_export_label=array();            // Tableau de "libell� de lots"
    var $array_export_fields_code=array();
    var $array_export_fields_label=array();
    var $array_export_sql=array();
    
    
    /**
     *    \brief  Constructeur de la classe
     *    \param  DB        Handler acc�s base de donn�es
     */
    function Export($DB)
    {
        $this->db=$DB;
    }
        
    
    /**
     *    \brief  Charge les lots de donn�es exportables
     *    \param  user      Objet utilisateur qui exporte
     *    \param  filter    Code export pour charger un lot de donn�es particulier
     */
    function load_arrays($user,$filter='')
    {
        dolibarr_syslog("Export::load_arrays user=$user filter=$filter");

        $dir=DOL_DOCUMENT_ROOT."/includes/modules";
        $handle=opendir($dir);

        // Recherche des exports disponibles
        $var=True;
        $i=0;
        while (($file = readdir($handle))!==false)
        {
            if (eregi("^(mod.*)\.class\.php",$file,$reg))
            {
                $modulename=$reg[1];
    
                // Chargement de la classe
                $file = $dir."/".$modulename.".class.php";
                $classname = $modulename;
                require_once($file);
                $module = new $classname($db);
                
                if (is_array($module->export_code))
                {
                    foreach($module->export_code as $r => $value)
                    {
                        if ($filter && ($filter != $module->export_code[$r])) continue;
                        
                        // Test si permissions ok \todo tester sur toutes permissions
                        $perm=$module->export_permission[$r][0];
                        //print_r("$perm[0]-$perm[1]-$perm[2]<br>");
                        if ($perm[2])
                        {
                            $bool=$user->rights->$perm[0]->$perm[1]->$perm[2];
                        }
                        else
                        {
                            $bool=$user->rights->$perm[0]->$perm[1];
                        }
                        if ($perm[0]=='user' && $user->admin) $bool=true;
                        //print("$bool<br>");
                        
                        // Permissions ok
                        if ($bool)
                        {
                            // Nom module
                            $this->array_export_module[$i]=$module;
                            // Code du dataset export
                            $this->array_export_code[$i]=$module->export_code[$r];
                            // Libell� du dataset export
                            $this->array_export_label[$i]=$module->export_label[$r];
                            // Tableau des champ � exporter (cl�=champ, valeur=libell�)
                            $this->array_export_fields[$i]=$module->export_fields_array[$r];
                            // Requete sql du dataset
                            $this->array_export_sql[$i]=$module->export_sql[$r];

                            dolibarr_syslog("Export charg� pour le module ".$modulename." en index ".$i.", dataset=".$module->export_code[$r].", nbre de champs=".sizeof($module->export_fields_code[$r]));
                            $i++;
                        }
                    }            
                }
            }
        }
        closedir($handle);
    }

    /**
     *      \brief      Lance la generation du fichier
     *      \param      user                User qui exporte
     *      \param      model               Modele d'export
     *      \param      datatoexport        Lot de donn�e � exporter
     *      \param      array_selected      Tableau des champs � exporter
     *      \remarks    Les tableaux array_export_xxx sont d�j� charg�es pour le bon datatoexport
     *                  aussi le parametre datatoexport est inutilis�
     */ 
    function build_file($user, $model, $datatoexport, $array_selected)
    {
        global $conf,$langs;
        
        $indice=0;
        
        dolibarr_syslog("Export::build_file $model, $datatoexport, $array_selected");
        
        // Creation de la classe d'export du model ExportXXX
        $dir = DOL_DOCUMENT_ROOT . "/includes/modules/export/";
        $file = "export_".$model.".modules.php";
        $classname = "Export".$model;
        require_once($dir.$file);
        $obj = new $classname($db);
        
        // Execute requete export        
        $sql=$this->array_export_sql[$indice];
		$resql = $this->db->query($sql);
		if ($resql)
		{
            //$this->array_export_label[$indice]
            $filename="export_user".$user->id."_set".$datatoexport;
            $filename.='.'.$obj->get_extension();
            $dirname=$conf->export->dir_ouput;
            
            // Open file
            create_exdir($dirname);
            $obj->open_file($dirname."/".$filename);

            // Genere en-tete
            $obj->write_header();		    
		    
            // Genere ligne de titre
            $obj->write_title();

			while ($objp = $this->db->fetch_object($resql))
			{
				$var=!$var;
                $obj->write_record($objp,$array_selected);
            }
            
            // Genere en-tete
            $obj->write_footer();
            
            // Close file
            $obj->close_file();
            
        }
        else
        {
            $this->error=$this->db->error();
            dolibarr_syslog("Error: sql=$sql ".$this->error);
            return -1;
        }
    }
    
}

?>
