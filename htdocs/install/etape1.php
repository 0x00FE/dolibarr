<?PHP
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
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
 *
 * $Id$
 * $Source$
 *
 */

include_once("../lib/mysql.lib.php");
include_once("./inc.php");

pHeader("Fichier de configuration","etape2");

$etape = 1;

$conffile = "../conf/conf.php";

// R�pertoire des pages dolibarr
$main_dir=isset($_POST["main_dir"])?$_POST["main_dir"]:'';
// R�pertoire des documents g�n�r�s (factures, etc...)
$main_data_dir=isset($_POST["main_data_dir"])?$_POST["main_data_dir"]:'';
# En attendant que le main_data_dir soit g�r� de mani�re autonome,
# on le force � sa valeur fixe des anciennes versions.
if (! $main_data_dir) { $main_data_dir="$main_dir/document"; }

if ($_POST["action"] == "set")
{
  umask(0);
  print '<h2>Enregistrement des valeurs</h2>';

  print '<table cellspacing="0" width="100%" cellpadding="4" border="0">';
  $error=0;
  $fp = fopen("$conffile", "w");
  if($fp)
    {
      if (substr($main_dir, strlen($main_dir) -1) == "/")
	{
	  $main_dir = substr($main_dir, 0, strlen($main_dir)-1);
	}

      if (substr($_POST["main_url"], strlen($_POST["main_url"]) -1) == "/")
	{
	  $_POST["main_url"] = substr($_POST["main_url"], 0, strlen($_POST["main_url"])-1);
	}

      clearstatcache();

      fwrite($fp, '<?PHP');
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

      fputs($fp, '?>');
      fclose($fp);

      if (file_exists("$conffile"))
	{
	  include ("$conffile");
	  print "<tr><td>Configuration enregistr�e</td><td>OK</td>";
	  $error = 0;
	}
      else
	{
	  $error = 1;
	}
    }

  /***************************************************************************
   *
   * Creation des r�pertoires
   *
   ***************************************************************************/	  
  
  if ($error == 0)
    {
    
    $dir[0] = "$main_data_dir/facture";
    $dir[1] = "$main_data_dir/propale";
    $dir[2] = "$main_data_dir/societe";
    $dir[3] = "$main_data_dir/ficheinter";
    $dir[4] = "$main_data_dir/produit";
    $dir[5] = "$main_data_dir/rapport";
    $dir[6] = "$main_data_dir/images";
    $dir[7] = "$main_data_dir/rsscache";

      if (! is_dir($main_dir))
	{
	  print "<tr><td>Le dossier '".$main_dir."' n'existe pas !</td><td>Erreur</td></tr>";
	  $error++;
	}
      else
	{	      
	  dolibarr_syslog ("Le dossier '".$main_dir."' existe");
	  /*
	   * R�pertoire des documents
	   */
	  if (! is_dir($main_data_dir))
	    {
	      @mkdir($main_data_dir, 0755);
	    }
	      
	  if (! is_dir($main_data_dir))
	    {
	      print "<tr><td>Le dossier '$main_data_dir' n'existe pas ! ";
	      print "Vous devez cr�er ce dossier et permettre au serveur web d'�crire dans celui-ci";
	      print '</td><td bgcolor="red">Erreur</td></tr>';
	      $error++;
	    }
	  else
	    {	      
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
			  print "<tr><td>Impossible de cr�er : ".$dir[$i]."</td><td bgcolor=\"red\">Erreur</td></tr>";
			  $error++;
			}
		      else
			{
			  dolibarr_syslog ("Le dossier '".$dir[$i]."' create ok");
			}
		    }
		}
	    }
	}
    }
  /*
   * Base de donn�es
   *
   */
  if ($error == 0)
    {
      include_once($dolibarr_main_document_root . "/conf/conf.class.php");

	  // TODO
	  // Il y a encore des pb ds la proc�dure d'install qui ne passe dans pas tous les cas
	  // (exemple, rien n'existe et on veut cr�er une base avec un compte admin != root)
	  // L'algorithme ne semble pas adapt� � tous les cas, il devrait etre remplac� par le suivant:
	  //
	  // On essaie l'acc�s par le user admin dolibarr
	  //   si acc�s serveur ok et acc�s base ok, tout est ok, on ne va pas plus loin, on a m�me pas utilis� le compte root.
	  //   si acc�s serveur ok et acc�s base ko, warning 1
	  //   si acc�s serveur ko, warning 2
	  // Si warning, on essai de se connecter au serveur via le super user root
	  //   Si connexion serveur par root ok et acc�s base ko, on la cr��e
	  //     Si cr�ation ok, on y acc�de
	  //     Si cr�ation ko, erreur
	  //   Si connexion serveur par root ok et si acc�s base ok,
	  //     si compte admin existe deja et db_create_user positionn�, on ajoute les droits,
	  //     si compte admin existe deja et db_create_user non positionn�, erreur compte admin incorrect "Le compte admin indiqu� existe mais n'a pas les droits sur la base. Veuillez cocher pour les ajouter"
	  //     si compte admin n'existe pas deja et db_create_user positionn�, on cr�e le compte
	  //     si compte admin n'existe pas deja et db_create_user non positionn�, erreur compte admin inexistant "Veuillez cocher pour le cr�er"

      // Si creation utilisateur admin demand�e, on le cr�e
      if (isset($_POST["db_create_user"]) && $_POST["db_create_user"] == "on")
	{
	  dolibarr_syslog ("Creation de l'utilisateur : ".$dolibarr_main_db_user);

	  $conf = new Conf();
	  $conf->db->host = $dolibarr_main_db_host;
	  $conf->db->name = "mysql";
	  $conf->db->user = isset($_POST["db_user_root"])?$_POST["db_user_root"]:"";
	  $conf->db->pass = isset($_POST["db_pass_root"])?$_POST["db_pass_root"]:"";
	  //print $conf->db->host." , ".$conf->db->name." , ".$conf->db->user." , ".$conf->db->pass;
	  $db = new DoliDb();
	  
	  $sql = "INSERT INTO user ";
	  $sql .= "(Host,User,password)";
	  $sql .= " VALUES ('$dolibarr_main_db_host','$dolibarr_main_db_user',password('$dolibarr_main_db_pass'))";
	  
	  print $db->query($sql);

	  $sql = "INSERT INTO db ";
	  $sql .= "(Host,Db,User,Select_priv,Insert_priv,Update_priv,Delete_priv,Create_priv,Drop_priv,Index_Priv,Alter_priv)";
	  $sql .= " VALUES ('$dolibarr_main_db_host','$dolibarr_main_db_name','$dolibarr_main_db_user'";
	  $sql .= ",'Y','Y','Y','Y','Y','Y','Y','Y')";
	  
	  print $sql;

	  if ($db->query($sql))
	    {
	      dolibarr_syslog("flush privileges");
	      $db->query("flush privileges");
	      
	      print "<tr><td>Cr�ation de l'utilisateur : $dolibarr_main_db_user</td><td>OK</td></tr>";
	    }
	  else
	    {
	      if ($db->errno() == 1062)
		{
		  print "<tr><td>Cr�ation de l'utilisateur : $dolibarr_main_db_user</td><td>Deja existant</td></tr>";
		}
	      else
		{
		print "<tr><td>Cr�ation de l'utilisateur : $dolibarr_main_db_user</td><td>ERREUR ".$db->error()."</td></tr>";
	      }
	    }
	  
	  $db->close();	  
	}
            
      // Tentative acc�s serveur et base par le user admin dolibarr
      
      $conf = new Conf();
      $conf->db->host = $dolibarr_main_db_host;
      $conf->db->name = $dolibarr_main_db_name;
      $conf->db->user = $dolibarr_main_db_user;
      $conf->db->pass = $dolibarr_main_db_pass;
	  //print $conf->db->host.",".$conf->db->name.",".$conf->db->user.",".$conf->db->pass;
	  $db = new DoliDb();
      $ok = 1;
      
	  if ($db->connected == 1)
	    {
	      print "<tr><td>Connexion au serveur : $dolibarr_main_db_host</td><td>OK</td></tr>";
	    }
	  else
	    {
	      print "<tr><td>Connexion au serveur : $dolibarr_main_db_host</td><td>ERREUR</td></tr>";
	      $ok = 0;
	    }
      
      if ($ok)
	{
	  if($db->database_selected == 1)
	    {
	      //
	      // Connexion base existante
	      // 
	      print "<tr><td>Connexion � la base : $dolibarr_main_db_name</td><td>OK</td></tr>";
	      
	      $ok = 1 ;
	    }
	  else
	    {
	      //
	      // Cr�ation de la base
	      //
	      print "<tr><td>Echec de connexion � la base : $dolibarr_main_db_name</td><td>Warning</td></tr>";
	      print '<tr><td colspan="2">Cr�ation de la base : '.$dolibarr_main_db_name.'</td></tr>';
	      
	      $conf = new Conf();
	      $conf->db->host = $dolibarr_main_db_host;
	      $conf->db->name = "mysql";
	      $conf->db->user = isset($_POST["db_user_root"])?$_POST["db_user_root"]:"";
	      $conf->db->pass = isset($_POST["db_pass_root"])?$_POST["db_pass_root"]:"";

	      $dbt = new DoliDb();  // Ne pas prendre $db comme nom de variable car dej� pris pour connexion pr�c�dente et bug m�moire sur php windows dans ce cas
	      
	      if ($ok)
		{
		  if ($dbt->connected == 1)
		    {
		      print "<tr><td>Connexion au serveur : $dolibarr_main_db_host avec l'utilisateur : ".$_POST["db_user_root"]."</td><td>OK</td></tr>";
		    }
		  else
		    {
		      print "<tr><td>Connexion au serveur : $dolibarr_main_db_host avec l'utilisateur : ".$_POST["db_user_root"]."</td><td>ERREUR</td></tr>";
		      $ok = 0;
		    }
		}
	      
	      if ($ok)
		{  
		  if($dbt->database_selected == 1)
		    {
		    }
		  else
		    {
		      print "<tr><td>V�rification des droits de cr�ation</td><td>ERREUR</td></tr>";
		      print '<tr><td colspna="2">-- Droits insuffissant</td></tr>';
		      $ok = 0;
		    }
		}
	      
	      if ($ok)
		{
		  if ($dbt->create_db ($dolibarr_main_db_name))
		    {			      			      
		      print "<tr><td>Cr�ation de la base : $dolibarr_main_db_name</td><td>OK</td></tr>";
		    }
		  else
		    {
		      print "<tr><td>Cr�ation de la base : $dolibarr_main_db_name</td><td>ERREUR</td></tr>";
		      $ok = 0;
		    }
		}
	      
	    }
	}    
    }
}
?>
</table>
<?PHP
pFooter($err);
?>
