<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
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
        \file       htdocs/install/etape1.php
        \brief      G�n�re le fichier conf.php avec les informations issues de l'�tape pr�c�dente
        \version    $Revision$
*/

include_once("./inc.php");

$setuplang=isset($_POST["selectlang"])?$_POST["selectlang"]:(isset($_GET["selectlang"])?$_GET["selectlang"]:$langcode);
$langs->setDefaultLang($setuplang);

$langs->load("admin");
$langs->load("install");


pHeader($langs->trans("ConfigurationFile"),"etape2");


$error = 0;


// R�pertoire des pages dolibarr
$main_dir=isset($_POST["main_dir"])?trim($_POST["main_dir"]):'';

// On supprime /  de fin dans main_dir
if (substr($main_dir, strlen($main_dir) -1) == "/")
{
    $main_dir = substr($main_dir, 0, strlen($main_dir)-1);
}

// On supprime /  de fin dans main_url
if (substr($_POST["main_url"], strlen($_POST["main_url"]) -1) == "/")
{
    $_POST["main_url"] = substr($_POST["main_url"], 0, strlen($_POST["main_url"])-1);
}

// R�pertoire des documents g�n�r�s (factures, etc...)
$main_data_dir=isset($_POST["main_data_dir"])?$_POST["main_data_dir"]:'';
if (! $main_data_dir) { $main_data_dir="$main_dir/documents"; }



/*
 * Actions
 */
if ($_POST["action"] == "set")
{
    umask(0);

    print '<h2>'.$langs->trans("SaveConfigurationFile").'</h2>';

    print '<table cellspacing="0" width="100%" cellpadding="1" border="0">';

    // Verification validite parametre main_dir
    if (! $error)
    {
        if (! is_dir($main_dir))
        {
            dolibarr_syslog ("Le dossier '".$main_dir."' n'existe pas");

            print "<tr><td>";
            print $langs->trans("ErrorDirDoesNotExists",$main_dir).'<br>';;
            print "Vous avez saisie une mauvaise valeur pour le param�tre '".$langs->trans("WebPagesDirectory")."'.<br>";
            print $langs->trans("ErrorGoBackAndCorrectParameters").'<br><br>';
            print '</td><td>';
            print $langs->trans("Error");
            print "</td></tr>";
            $error++;
        }
    }

    // Sauvegarde fichier configuration
    if (! $error)
    {
        $fp = fopen("$conffile", "w");
    
        if($fp)
        {
            clearstatcache();
    
            fwrite($fp, '<?php');
            fputs($fp,"\n");
    
            fputs($fp, '$dolibarr_main_url_root="'.$_POST["main_url"].'";');
            fputs($fp,"\n");
    
            fputs($fp, '$dolibarr_main_document_root="'.$main_dir.'";');
            fputs($fp,"\n");
    
            fputs($fp, '$dolibarr_main_data_root="'.$main_data_dir.'";');
            fputs($fp,"\n");
    
            fputs($fp, '$dolibarr_main_db_host="'.$_POST["db_host"].'";');
            fputs($fp,"\n");
    
            fputs($fp, '$dolibarr_main_db_name="'.$_POST["db_name"].'";');
            fputs($fp,"\n");
    
            fputs($fp, '$dolibarr_main_db_user="'.$_POST["db_user"].'";');
            fputs($fp,"\n");
    
            fputs($fp, '$dolibarr_main_db_pass="'.$_POST["db_pass"].'";');
            fputs($fp,"\n");
    
            fputs($fp, '$dolibarr_main_db_type="'.$_POST["db_type"].'";');
            fputs($fp,"\n");
    
            fputs($fp, '?>');
            fclose($fp);
    
            if (file_exists("$conffile"))
            {
                include ("$conffile");
                print "<tr><td>".$langs->trans("ConfigurationSaving")."</td><td>".$langs->trans("OK")."</td>";
            }
            else
            {
                $error++;
            }
        }
    
        if($dolibarr_main_db_type == "mysql")
        {
            $choix=1;
        }
        else
        {
            $choix=2;
        }

        // Chargement driver acces bases
        include_once("../lib/".$dolibarr_main_db_type.".lib.php");

    }

    /***************************************************************************
    *
    * Creation des r�pertoires
    *
    ***************************************************************************/

    // Creation des sous-r�pertoires main_data_dir
    if (! $error)
    {
        dolibarr_syslog ("Le dossier '".$main_dir."' existe");

        // R�pertoire des documents
        if (! is_dir($main_data_dir))
        {
            @mkdir($main_data_dir, 0755);
        }

        if (! is_dir($main_data_dir))
        {
            print "<tr><td>".$langs->trans("DirDoesNotExists",$main_data_dir);
            print "Vous devez cr�er ce dossier et permettre au serveur web d'�crire dans celui-ci";
            print '</td><td>';
            print $langs->trans("Error");
            print "</td></tr>";
            $error++;
        }
        else
        {
            // Les documents sont en dehors de htdocs car ne doivent pas pouvoir etre t�l�charg�s en passant outre l'authentification
            $dir[0] = "$main_data_dir/facture";
            $dir[1] = "$main_data_dir/users";
            $dir[2] = "$main_data_dir/propale";
            $dir[3] = "$main_data_dir/societe";
            $dir[4] = "$main_data_dir/ficheinter";
            $dir[5] = "$main_data_dir/produit";
            $dir[6] = "$main_data_dir/rapport";
            $dir[7] = "$main_data_dir/rsscache";
            $dir[8] = "$main_data_dir/logo";

            // Boucle sur chaque r�pertoire de dir[] pour les cr�er s'ils nexistent pas
            for ($i = 0 ; $i < sizeof($dir) ; $i++)
            {
                if (is_dir($dir[$i]))
                {
                    dolibarr_syslog ("Le dossier '".$dir[$i]."' existe");
                }
                else
                {
                    if (! @mkdir($dir[$i], 0755))
                    {
                        print "<tr><td>";
                        print "Impossible de cr�er : ".$dir[$i];
                        print '</td><td>';
                        print $langs->trans("Error");
                        print "</td></tr>";
                        $error++;
                    }
                    else
                    {
                        dolibarr_syslog ("Le dossier '".$dir[$i]."' a ete cree");
                    }
                }
            }
        }
    }


    /*
     * Base de donn�es
     *
     */
    if (! $error)
    {
        include_once($dolibarr_main_document_root . "/conf/conf.class.php");

        $conf = new Conf();
        $conf->db->type = trim($dolibarr_main_db_type);
        $conf->db->host = trim($dolibarr_main_db_host);
        $conf->db->name = trim($dolibarr_main_db_name);
        $conf->db->user = trim($dolibarr_main_db_user);
        $conf->db->pass = trim($dolibarr_main_db_pass);

        $userroot=isset($_POST["db_user_root"])?$_POST["db_user_root"]:"";
        $passroot=isset($_POST["db_pass_root"])?$_POST["db_pass_root"]:"";


        /*
         * Si creation utilisateur admin demand�e, on le cr�e
         */
        if (isset($_POST["db_create_user"]) && $_POST["db_create_user"] == "on")
        {
            dolibarr_syslog("Creation de l'utilisateur: ".$dolibarr_main_db_user." choix base: ".$choix);

            if ($choix == 1)     //choix 1=mysql
            {
                //print $conf->db->host." , ".$conf->db->name." , ".$conf->db->user." , ".$conf->db->pass;

                // Creation handler de base, verification du support et connexion
                $db = new DoliDb($conf->db->type,$conf->db->host,$userroot,$passroot,'mysql');
                if ($db->error)
                {
                    print $langs->trans("ThisPHPDoesNotSupportTypeBase",'mysql');
                    $error++;
                }
                
                if (! $error)
                {
                    if ($db->connected)
                    {
                        $sql = "INSERT INTO user ";
                        $sql.= "(Host,User,password,Select_priv,Insert_priv,Update_priv,Delete_priv,Create_priv,Drop_priv,Index_Priv,Alter_priv)";
                        $sql.= " VALUES ('$dolibarr_main_db_host','$dolibarr_main_db_user',password('$dolibarr_main_db_pass')";
                        $sql.= ",'Y','Y','Y','Y','Y','Y','Y','Y');";
    
                        //print "$sql<br>\n";
    
                        $db->query($sql);
    
                        $sql = "INSERT INTO db ";
                        $sql.= "(Host,Db,User,Select_priv,Insert_priv,Update_priv,Delete_priv,Create_priv,Drop_priv,Index_Priv,Alter_priv)";
                        $sql.= " VALUES ('$dolibarr_main_db_host','$dolibarr_main_db_name','$dolibarr_main_db_user'";
                        $sql.= ",'Y','Y','Y','Y','Y','Y','Y','Y');";
    
                        //print "$sql<br>\n";
    
                        $resql=$db->query($sql);
                        if ($resql)
                        {
                            dolibarr_syslog("flush privileges");
                            $db->query("FLUSH Privileges;");
    
                            print '<tr><td>';
                            print $langs->trans("UserCreation").' : ';
                            print $dolibarr_main_db_user;
                            print '</td>';
                            print '<td>'.$langs->trans("OK").'</td></tr>';
                        }
                        else
                        {
                            if ($db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS')
                            {
                                dolibarr_syslog("Utilisateur deja existant");
                                print '<tr><td>';
                                print $langs->trans("UserCreation").' : ';
                                print $dolibarr_main_db_user;
                                print '</td>';
                                print '<td>'.$langs->trans("LoginAlreadyExists").'</td></tr>';
                            }
                            else
                            {
                                dolibarr_syslog("impossible de creer l'utilisateur");
                                print '<tr><td>';
                                print $langs->trans("UserCreation").' : ';
                                print $dolibarr_main_db_user;
                                print '</td>';
                                print '<td>'.$langs->trans("Error").' '.$db->error()."</td></tr>";
                            }
                        }
    
                        $db->close();
                    }
                    else
                    {
                        print '<tr><td>';
                        print $langs->trans("UserCreation").' : ';
                        print $dolibarr_main_db_user;
                        print '</td>';
                        print '<td>'.$langs->trans("Error").'</td>';
                        print '</tr>';
    
                        // Affiche aide diagnostique
                        print '<tr><td colspan="2"><br>Vous avez demand� la cr�ation du login Dolibarr "<b>'.$dolibarr_main_db_user.'</b>", mais pour cela, ';
                        print 'Dolibarr doit se connecter sur le serveur "<b>'.$dolibarr_main_db_host.'</b>" via le super utilisateur "<b>'.$userroot.'</b>", mot de passe "<b>'.$passroot.'</b>".<br>';
                        print 'La connexion ayant �chou�, les param�tres du serveur ou du super utilisateur sont peut-etre incorrects. ';
                        print $langs->trans("ErrorGoBackAndCorrectParameters").'<br><br>';
                        print '</td></tr>';
    
                        $error++;
                    }
                }
            }
            else        //choix 2=postgresql
            {
                //print $conf->db->host." , ".$conf->db->name." , ".$conf->db->user." , ".$conf->db->pass;

                // Creation handler de base, verification du support et connexion
                $db = new DoliDb($conf->db->type,$conf->db->host,$userroot,$passroot,$conf->db->name);
                if ($db->error)
                {
                    print $langs->trans("ThisPHPDoesNotSupportTypeBase",'mysql');
                    $error++;
                }

                if (! $error)
                {
                    if ($db->connected)
                    {
                        $nom = $dolibarr_main_db_user;
                        $sql = "create user \"".$nom."\" with password '".$dolibarr_main_db_pass."';";
                        //print $query_str;
                        $resql = $db->query($sql);
                        if ($resql)
                        {
                            print '<tr><td>';
                            print $langs->trans("UserCreation").' : ';
                            print $dolibarr_main_db_user;
                            print '</td>';
                            print '<td>'.$langs->trans("OK").'</td>';
                            print '</tr>';
                        }
                        else
                        {
                            if ($db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS')
                            {
                                dolibarr_syslog("Utilisateur deja existant");
                                print '<tr><td>';
                                print $langs->trans("UserCreation").' : ';
                                print $dolibarr_main_db_user;
                                print '</td>';
                                print '<td>'.$langs->trans("LoginAlreadyExists").'</td></tr>';
                            }
                            else
                            {
                                dolibarr_syslog("impossible de creer l'utilisateur");
                                print '<tr><td>';
                                print $langs->trans("UserCreation").' : ';
                                print $dolibarr_main_db_user;
                                print '</td>';
                                print '<td>'.$langs->trans("Error").' '.$db->error()."</td></tr>";
                            }
                        }
                    }
                    else
                    {
                        print '<tr><td>';
                        print $langs->trans("UserCreation").' : ';
                        print $dolibarr_main_db_user;
                        print '</td>';
                        print '<td>'.$langs->trans("Error").'</td>';
                        print '</tr>';
    
                        // Affiche aide diagnostique
                        print '<tr><td colspan="2"><br>Vous avez demand� la cr�ation du login Dolibarr "<b>'.$dolibarr_main_db_user.'</b>", mais pour cela, ';
                        print 'Dolibarr doit se connecter sur le serveur "<b>'.$dolibarr_main_db_host.'</b>" via le super utilisateur "<b>'.$userroot.'</b>", mot de passe "<b>'.$passroot.'</b>".<br>';
                        print 'La connexion ayant �chou�, les param�tres du serveur ou du super utilisateur sont peut-etre incorrects. ';
                        print $langs->trans("ErrorGoBackAndCorrectParameters").'<br><br>';
                        print '</td></tr>';
    
                        $error++;
                    }
                }
            }

        }   // Fin si "creation utilisateur"


        /*
        * Si creation database demand�e, on la cr�e
        */
        if (! $error && (isset($_POST["db_create_database"]) && $_POST["db_create_database"] == "on"))
        {
            dolibarr_syslog ("Creation de la base : ".$dolibarr_main_db_name);

            $db = new DoliDb($conf->db->type,$conf->db->host,$userroot,$passroot);

            if ($db->connected)
            {
                if ($db->create_db($dolibarr_main_db_name))
                {
                    print '<tr><td>';
                    print $langs->trans("DatabaseCreation").' : ';
                    print $dolibarr_main_db_name;
                    print '</td>';
                    print "<td>".$langs->trans("OK")."</td></tr>";
                }
                else
                {
                    print '<tr><td>';
                    print $langs->trans("DatabaseCreation").' : ';
                    print $dolibarr_main_db_name;
                    print '</td>';
                    print '<td>'.$langs->trans("Error").' '.$db->errno().'</td></tr>';

                    // Affiche aide diagnostique
                    print '<tr><td colspan="2"><br>La cr�ation de la base Dolibarr "<b>'.$dolibarr_main_db_name.'</b>" a �chou�.';
                    print 'Si la base existe d�j�, revenez en arri�re et d�sactiver l\'option "Cr�er la base de donn�e".<br>';
                    print '</td></tr>';

                    $error++;
                }
                $db->close();
            }
            else {
                print '<tr><td>';
                print $langs->trans("DatabaseCreation").' : ';
                print $dolibarr_main_db_name;
                print '</td>';
                print '<td>'.$langs->trans("Error").'</td>';
                print '</tr>';

                // Affiche aide diagnostique
                print '<tr><td colspan="2"><br>Vous avez demand� la cr�ation de la base Dolibarr "<b>'.$dolibarr_main_db_name.'</b>", mais pour cela, ';
                print 'Dolibarr doit se connecter sur le serveur "<b>'.$dolibarr_main_db_host.'</b>" via le super utilisateur "<b>'.$userroot.'</b>", mot de passe "<b>'.$passroot.'</b>".<br>';
                print 'La connexion ayant �chou�, les param�tres du serveur ou du super utilisateur sont peut-etre incorrects. ';
                print $langs->trans("ErrorGoBackAndCorrectParameters").'<br><br>';
                print '</td></tr>';

                $error++;
            }
        }   // Fin si "creation database"


        /*
         * On test maintenant l'acc�s par le user admin dolibarr
         */
        if (! $error)
        {
            dolibarr_syslog("connexion de type=".$conf->db->type." sur host=".$conf->db->host." user=".$conf->db->user." name=".$conf->db->name);
            //print "connexion de type=".$conf->db->type." sur host=".$conf->db->host." user=".$conf->db->user." name=".$conf->db->name;

            $db = new DoliDb($conf->db->type,$conf->db->host,$conf->db->user,$conf->db->pass,$conf->db->name);

            if ($db->connected == 1)
            {
                // si acc�s serveur ok et acc�s base ok, tout est ok, on ne va pas plus loin, on a m�me pas utilis� le compte root.
                if ($db->database_selected == 1)
                {
                    dolibarr_syslog("la connexion au serveur par le user ".$conf->db->user." est reussie");
                    print "<tr><td>";
                    print $langs->trans("ServerConnection")." : ";
                    print $dolibarr_main_db_host;
                    print "</td><td>";
                    print $langs->trans("OK");
                    print "</td></tr>";

                    dolibarr_syslog("la connexion a la base : ".$conf->db->name.",par le user : ".$conf->db->user." est reussie");
                    print "<tr><td>";
                    print $langs->trans("DatabaseConnection")." : ";
                    print $dolibarr_main_db_name;
                    print "</td><td>";
                    print $langs->trans("OK");
                    print "</td></tr>";

                    $error = 0;
                }
                else
                {
                    dolibarr_syslog("la connection au serveur par le user ".$conf->db->user." est reussie");
                    print "<tr><td>";
                    print $langs->trans("ServerConnection")." : ";
                    print $dolibarr_main_db_host;
                    print "</td><td>";
                    print $langs->trans("OK");
                    print "</td></tr>";

                    dolibarr_syslog("la connexion a la base ".$conf->db->name.",par le user ".$conf->db->user." a �chou�");
                    print "<tr><td>";
                    print $langs->trans("DatabaseConnection")." : ";
                    print $dolibarr_main_db_name;
                    print '</td><td>';
                    print $langs->trans("Error");
                    print "</td></tr>";

                    // Affiche aide diagnostique
                    print '<tr><td colspan="2"><br>V�rifier que le nom de base "<b>'.$dolibarr_main_db_name.'</b>" est correct.<br>';
                    print 'Si ce nom est correct et que cette base n\'existe pas d�j�, vous devez cocher l\'option "Cr�er la base de donn�e". ';
                    print $langs->trans("ErrorGoBackAndCorrectParameters").'<br><br>';
                    print '</td></tr>';

                    $error++;
                }
            }
            else
            {
                dolibarr_syslog("la connection au serveur par le user ".$conf->db->user." est rate");
                print "<tr><td>";
                print $langs->trans("ServerConnection")." : ";
                print $dolibarr_main_db_host;
                print '</td><td>';
                print $langs->trans("Error");
                print "</td></tr>";

                // Affiche aide diagnostique
                print '<tr><td colspan="2"><br>Le serveur "<b>'.$conf->db->host.'</b>", nom de base "<b>'.$conf->db->name.'</b>", login "<b>'.$conf->db->user.'</b>", ou mot de passe <b>"'.$conf->db->pass.'</b>" de la base de donn�e est peut-�tre incorrect ou la version du client PHP trop ancienne par rapport � la version de la base de donn�e.<br>';
                print 'Si le login n\'existe pas encore, vous devez cocher l\'option "Cr�er l\'utilisateur".<br>';
                print $langs->trans("ErrorGoBackAndCorrectParameters").'<br><br>';
                print '</td></tr>';

                $error++;
            }
        }
    }

    print '</table>';
}

pFooter($error,$setuplang);

?>
