<?php
/* Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2007 Regis Houssin        <regis.houssin@cap-networks.com>
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
        \ingroup    export
        \brief      Fichier de la classe des exports
        \version    $Revision$
*/


/**
        \class 		Export
        \brief 		Classe permettant la gestion des exports
*/

class Export
{
    var $db;
	
	var $array_export_code=array();             // Tableau de "idmodule_numlot"
    var $array_export_module=array();           // Tableau de "nom de modules"
    var $array_export_label=array();            // Tableau de "libell� de lots"
    var $array_export_sql=array();              // Tableau des "requetes sql"
    var $array_export_fields=array();           // Tableau des liste de champ+libell� � exporter
    var $array_export_alias=array();            // Tableau des liste de champ+alias � exporter
    
    // Cr�ation des mod�les d'export
    var $hexa;
    var $datatoexport;
    var $model_name;
    
    
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
        global $langs;
        
        dolibarr_syslog("Export::load_arrays user=".$user->id." filter=".$filter);

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
                $module = new $classname($this->db);
                
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
                            // Charge fichier lang en rapport
                            $langtoload=$module->getLangFilesArray();
                            if (is_array($langtoload))
                            {
                                foreach($langtoload as $key) 
                                {
                                    $langs->load($key);
                                }
                            }

                            // Nom module
                            $this->array_export_module[$i]=$module;
                            // Code du dataset export
                            $this->array_export_code[$i]=$module->export_code[$r];
                            // Libell� du dataset export
                            $this->array_export_label[$i]=$module->getDatasetLabel($r);
                            // Requete sql du dataset
                            $this->array_export_sql[$i]=$module->export_sql[$r];
                            // Tableau des champ � exporter (cl�=champ, valeur=libell�)
                            $this->array_export_fields[$i]=$module->export_fields_array[$r];
                            // Tableau des entites � exporter (cl�=champ, valeur=entite)
                            $this->array_export_entities[$i]=$module->export_entities_array[$r];
                            // Tableau des alias � exporter (cl�=champ, valeur=alias)
                            $this->array_export_alias[$i]=$module->export_alias_array[$r];

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
        asort($array_selected);
        
        dolibarr_syslog("Export::build_file $model, $datatoexport, $array_selected");
        
        // Creation de la classe d'export du model ExportXXX
        $dir = DOL_DOCUMENT_ROOT . "/includes/modules/export/";
        $file = "export_".$model.".modules.php";
        $classname = "Export".$model;
        require_once($dir.$file);
        $objmodel = new $classname($db);
        
        // Execute requete export        
        $sql=$this->array_export_sql[$indice];
		$resql = $this->db->query($sql);
		if ($resql)
		{
            //$this->array_export_label[$indice]
            $filename="export_".$datatoexport;
            $filename.='.'.$objmodel->getDriverExtension();
            $dirname=$conf->export->dir_temp.'/'.$user->id;

            // Open file
            create_exdir($dirname);
            $objmodel->open_file($dirname."/".$filename);

            // Genere en-tete
            $objmodel->write_header($langs);

            // Genere ligne de titre
            $objmodel->write_title($this->array_export_fields[$indice],$array_selected,$langs);

			while ($objp = $this->db->fetch_object($resql))
			{
				$var=!$var;
                $objmodel->write_record($this->array_export_alias[$indice],$array_selected,$objp);
            }
            
            // Genere en-tete
            $objmodel->write_footer($langs);
            
            // Close file
            $objmodel->close_file();
            
        }
        else
        {
            $this->error=$this->db->error()." - sql=".$sql;
            dolibarr_syslog("Error: ".$this->error);
            return -1;
        }
    }
    
	/**
	*  \brief	Cr�� un mod�le d'export
	*  \param	user Objet utilisateur qui cr�e
	*/
	function create($user)
	{
		global $conf;
		
		dolibarr_syslog("Export.class.php::create");
		
		$this->db->begin();
		
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'export_model (';
		$sql.= 'label, type, field)';
		$sql.= " VALUES ('".$this->model_name."', '".$this->datatoexport."', '".$this->hexa."')";
		
		dolibarr_syslog("Export.class.php::create sql=".$sql);
		
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error=$this->db->error()." sql=".$sql;
			$this->db->rollback();
			return -1;
		}
	}

	/**
	*    \brief      Recup�re de la base les caract�ristiques d'un modele d'export
	*    \param      rowid       id du mod�le � r�cup�rer
	*/
	function fetch($id)
	{
		$sql = 'SELECT em.rowid, em.field, em.label, em.type';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'export_model as em';
		$sql.= ' WHERE em.rowid = '.$id;
		
		dolibarr_syslog("Export::fetch sql=$sql");
		
		$result = $this->db->query($sql) ;
		if ($result)
		{
			$obj = $this->db->fetch_object($result);
			if ($obj)
			{
				$this->id                   = $obj->rowid;
				$this->hexa                 = $obj->field;
				$this->model_name           = $obj->label;
				$this->datatoexport         = $obj->type;
				
				return 1;
			}
			else
			{
				$this->error="Model not found";
				return -2;	
			}
		}
		else
		{
			dolibarr_print_error($this->db);
			return -3;
		}
	}
    
}

?>
