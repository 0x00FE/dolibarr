<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/install/etape2.php
        \brief      Cr�e les tables, cl�s primaires, cl�s �trang�res, index et fonctions en base puis charge les donn�es de r�f�rence
        \version    $Revision$
*/

include_once("./inc.php");
if (file_exists($conffile)) include_once($conffile);
if (! isset($dolibarr_main_db_prefix) || ! $dolibarr_main_db_prefix) $dolibarr_main_db_prefix='llx_'; 
define('MAIN_DB_PREFIX',$dolibarr_main_db_prefix);
require_once($dolibarr_main_document_root . "/lib/databases/".$dolibarr_main_db_type.".lib.php");
require_once($dolibarr_main_document_root . "/conf/conf.class.php");

$etape = 2;
$ok = 0;


// Cette page peut etre longue. On augmente le d�lai autorise.
// Ne fonctionne que si on est pas en safe_mode.
$err=error_reporting();
error_reporting(0);
@set_time_limit(120);         
error_reporting($err);

$setuplang=isset($_POST["selectlang"])?$_POST["selectlang"]:(isset($_GET["selectlang"])?$_GET["selectlang"]:'auto');
$langs->setDefaultLang($setuplang);

$langs->load("admin");
$langs->load("install");

if ($dolibarr_main_db_type == "mysql")  $choix=1;
if ($dolibarr_main_db_type == "mysqli") $choix=1;
if ($dolibarr_main_db_type == "pqsql") $choix=2;



pHeader($langs->trans("CreateDatabaseObjects"),"etape4");


if ($_POST["action"] == "set")
{
    print '<h2>'.$langs->trans("Database").'</h2>';

    print '<table cellspacing="0" cellpadding="4" border="0" width="100%">';
    $error=0;

    $conf = new Conf();// on pourrait s'en passer
    $conf->db->type = $dolibarr_main_db_type;
    $conf->db->host = $dolibarr_main_db_host;
    $conf->db->name = $dolibarr_main_db_name;
    $conf->db->user = $dolibarr_main_db_user;
    $conf->db->pass = $dolibarr_main_db_pass;

    $db = new DoliDb($conf->db->type,$conf->db->host,$conf->db->user,$conf->db->pass,$conf->db->name);
    if ($db->connected == 1)
    {
        print "<tr><td>";
        print $langs->trans("ServerConnection")." : $dolibarr_main_db_host</td><td>".$langs->trans("OK")."</td></tr>";
        $ok = 1 ;
    }
    else
    {
        print "<tr><td>Erreur lors de la cr�ation de : $dolibarr_main_db_name</td><td>".$langs->trans("Error")."</td></tr>";
    }

    if ($ok)
    {
        if($db->database_selected == 1)
        {

            dolibarr_install_syslog("Connexion r�ussie � la base : $dolibarr_main_db_name");
        }
        else
        {
            $ok = 0 ;
        }
    }


    // Affiche version
    if ($ok)
    {
        $version=$db->getVersion();
        $versionarray=$db->getVersionArray();
        print '<tr><td>'.$langs->trans("DatabaseVersion").'</td>';
        print '<td>'.$version.'</td></tr>';
        //print '<td align="right">'.join('.',$versionarray).'</td></tr>';
    }

	$requestnb=0;

    /**************************************************************************************
    *
    * Chargement fichiers tables/*.sql (non *.key.sql)
    * A faire avant les fichiers *.key.sql
    *
    ***************************************************************************************/
    if ($ok)
    {
        if ($choix==1) $dir = "../../mysql/tables/";
        else $dir = "../../pgsql/tables/";

        $ok = 0;
        $handle=opendir($dir);
        $table_exists = 0;
        while (($file = readdir($handle))!==false)
        {
            if (substr($file, strlen($file) - 4) == '.sql' && substr($file,0,4) == 'llx_' && substr($file, -8) <> '.key.sql')
            {
                $name = substr($file, 0, strlen($file) - 4);
                $buffer = '';
                $fp = fopen($dir.$file,"r");
                if ($fp)
                {
                    while (!feof ($fp))
                    {
                        $buf = fgets($fp, 4096);
                        if (substr($buf, 0, 2) <> '--')
                        {
                            $buffer .= $buf;
                        }
                    }
                    fclose($fp);
                }

                //print "<tr><td>Cr�ation de la table $name/td>";
				$requestnb++;
                if ($db->query($buffer))
                {
                    //print "<td>OK requete ==== $buffer</td></tr>";
                }
                else
                {
                    if ($db->errno() == 'DB_ERROR_TABLE_ALREADY_EXISTS')
                    {
                        //print "<td>D�j� existante</td></tr>";
                        $table_exists = 1;
                    }
                    else
                    {
                        print "<tr><td>".$langs->trans("CreateTableAndPrimaryKey",$name);
                        print "<br>".$langs->trans("Request").' '.$requestnb.' : '.$buffer;
                        print "</td>";
                        print "<td>".$langs->trans("Error")." ".$db->errno()." ".$db->error()."</td></tr>";
                        $error++;
                    }
                }
            }

        }
        closedir($handle);

        if ($error == 0)
        {
            print '<tr><td>';
            print $langs->trans("TablesAndPrimaryKeysCreation").'</td><td>'.$langs->trans("OK").'</td></tr>';
            $ok = 1;
        }
    }

    
    /***************************************************************************************
    *
    * Chargement fichiers tables/*.key.sql
    * A faire apr�s les fichiers *.sql
    *
    ***************************************************************************************/
    if ($ok)
    {
        if ($choix==1) $dir = "../../mysql/tables/";
        else $dir = "../../pgsql/tables/";

        $okkeys = 0;
        $handle=opendir($dir);
        $table_exists = 0;
        while (($file = readdir($handle))!==false)
        {
            if (substr($file, strlen($file) - 4) == '.sql' && substr($file,0,4) == 'llx_' && substr($file, -8) == '.key.sql')
            {
                $name = substr($file, 0, strlen($file) - 4);
                //print "<tr><td>Cr�ation de la table $name</td>";
                $buffer = '';
                $fp = fopen($dir.$file,"r");
                if ($fp)
                {
                    while (!feof ($fp))
                    {
                        $buf = fgets($fp, 4096);

                        // Cas special de lignes autorisees pour certaines versions uniquement
                        if (eregi('^-- V([0-9\.]+)',$buf,$reg))
                        {
                            $versioncommande=split('\.',$reg[1]);
							//print var_dump($versioncommande);
							//print var_dump($versionarray);
                            if (sizeof($versioncommande) && sizeof($versionarray)
                            	&& versioncompare($versioncommande,$versionarray) <= 0)
                            {
                            	// Version qualified, delete SQL comments
                                $buf=eregi_replace('^-- V([0-9\.]+)','',$buf);
                                //print "Ligne $i qualifi�e par version: ".$buf.'<br>';
                            }                      
                        }

                        // Ajout ligne si non commentaire
                        if (! eregi('^--',$buf)) $buffer .= $buf;
                    }
                    fclose($fp);
                }

                // Si plusieurs requetes, on boucle sur chaque
                $listesql=split(';',$buffer);
                foreach ($listesql as $buffer)
                {
                    if (trim($buffer))
                    {
		                //print "<tr><td>Cr�ation des cl�s et index de la table $name: '$buffer'</td>";
						$requestnb++;
                        if ($db->query(trim($buffer)))
                        {
                            //print "<td>OK requete ==== $buffer</td></tr>";
                        }
                        else
                        {
                            if ($db->errno() == 'DB_ERROR_KEY_NAME_ALREADY_EXISTS' ||
                                $db->errno() == 'DB_ERROR_CANNOT_CREATE' ||
                                $db->errno() == 'DB_ERROR_PRIMARY_KEY_ALREADY_EXISTS' ||
                                eregi('duplicate key name',$db->error()))
                            {
                                //print "<td>D�j� existante</td></tr>";
                                $key_exists = 1;
                            }
                            else
                            {
                                print "<tr><td>".$langs->trans("CreateOtherKeysForTable",$name);
                                print "<br>".$langs->trans("Request").' '.$requestnb.' : '.$buffer;
                                print "</td>";
                                print "<td>".$langs->trans("Error")." ".$db->errno()." ".$db->error()."</td></tr>";
                                $error++;
                            }
                        }
                    }
                }
            }

        }
        closedir($handle);

        if ($error == 0)
        {
            print '<tr><td>';
            print $langs->trans("OtherKeysCreation").'</td><td>'.$langs->trans("OK").'</td></tr>';
            $okkeys = 1;
        }
    }
    
    
    /***************************************************************************************
    *
    * Positionnement des droits
    *
    ***************************************************************************************/
    if ($ok)
    {
        // Droits sur les tables
        $grant_query=$db->getGrantForUserQuery($dolibarr_main_db_user);
        
        if ($grant_query)   // Seules les bases qui en ont besoin le definisse
        {
            if ($db->query($grant_query))
            {
                print "<tr><td>Grant User</td><td>".$langs->trans("OK")."</td></tr>";
            }
        }
    }   


    /***************************************************************************************
    *
    * Chargement fichier functions.sql
    *
    ***************************************************************************************/
    if ($ok)
    {
        if ($choix==1) $dir = "../../mysql/functions/";
        else $dir = "../../pgsql/functions/";

        // Cr�ation donn�es
        $file = "functions.sql";
        if (file_exists($dir.$file)) {
            $fp = fopen($dir.$file,"r");
            if ($fp)
            {
                while (!feof ($fp))
                {
                    $buffer = fgets($fp, 4096);
                    if (substr($buf, 0, 2) <> '--')
                    {
                        $buffer .= $buf;
                    }
                }
                fclose($fp);
            }

            // Si plusieurs requetes, on boucle sur chaque
            $listesql=split('�',eregi_replace(";';",";'�",$buffer));
            foreach ($listesql as $buffer) {                
                if (trim($buffer)) {
    
                    if ($db->query(trim($buffer)))
                    {
                        $ok = 1;
                    }
                    else
                    {
                        if ($db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS')
                        {
                            // print "<tr><td>Insertion ligne : $buffer</td><td>
                        }
                        else
                        {
                            $ok = 0;
                            print $langs->trans("ErrorSQL")." : ".$db->errno()." - '$buffer' - ".$db->error()."<br>";
                        }
                    }
                }
            }

            print "<tr><td>".$langs->trans("FunctionsCreation")."</td>";
            if ($ok)
            {
                print "<td>".$langs->trans("OK")."</td></tr>";
            }
            else
            {
                print "<td>".$langs->trans("Error")."</td></tr>";
                $ok = 1 ;
            }

        }
    }    


    /***************************************************************************************
    *
    * Chargement fichier data.sql
    *
    ***************************************************************************************/
    if ($ok)
    {
        if ($choix==1) $dir = "../../mysql/data/";
        else $dir = "../../pgsql/data/";

        // Cr�ation donn�es
        $file = "data.sql";
        $fp = fopen($dir.$file,"r");
        if ($fp)
        {
            while (!feof ($fp))
            {
                $buffer = fgets($fp, 4096);

                if (strlen(trim(ereg_replace("--","",$buffer))))
                {
                    if ($db->query($buffer))
                    {
                        $ok = 1;
                    }
                    else
                    {
                        if ($db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS')
                        {
                            // print "<tr><td>Insertion ligne : $buffer</td><td>
                        }
                        else
                        {
                            $ok = 0;
                            print $langs->trans("ErrorSQL")." : ".$db->errno()." - '$buffer' - ".$db->error()."<br>";
                        }
                    }
                }
            }
            fclose($fp);
        }

        print "<tr><td>".$langs->trans("ReferenceDataLoading")."</td>";
        if ($ok)
        {
            print "<td>".$langs->trans("OK")."</td></tr>";
        }
        else
        {
            print "<td>".$langs->trans("Error")."</td></tr>";
            $ok = 1 ;
        }
    }


    /***************************************************************************************
    *
    * Les variables qui ecrase le chemin par defaut sont red�finies
    *
    ***************************************************************************************/
    if ($ok == 1)
    {
        $sql[0] = "UPDATE llx_const SET value = '".$dolibarr_main_data_root."/facture',
        type = 'chaine',
        visible = 0
        where name  ='FAC_OUTPUTDIR';" ;

        $sql[1] = "UPDATE llx_const SET value = '".$dolibarr_main_data_root."/propale',
        type = 'chaine',
        visible = 0
        where name  = 'PROPALE_OUTPUTDIR';" ;

        $sql[2] = "UPDATE llx_const SET value = '".$dolibarr_main_data_root."/ficheinter',
        type = 'chaine',
        visible = 0
        where name  = 'FICHEINTER_OUTPUTDIR';" ;

        $sql[3] = "UPDATE llx_const SET value='".$dolibarr_main_data_root."/societe',
        type = 'chaine',
        visible = 0
        where name  = 'SOCIETE_OUTPUTDIR';" ;

        $sql[4] = "DELETE from llx_const where name like '%_OUTPUT_URL';";


        $sql[5] = "UPDATE llx_const SET value='".$langs->defaultlang."',
        type = 'chaine',
        visible = 0
        where name  = 'MAIN_LANG_DEFAULT';" ;

        $result = 0;

        for ($i=0; $i < sizeof($sql);$i++)
        {
            if ($db->query($sql[$i]))
            {
                $result++;
            }
        }

    }

    print '</table>';

    $db->close();
}

pFooter(!$ok,$setuplang);

?>
