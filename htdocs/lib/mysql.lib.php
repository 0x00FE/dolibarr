<?php
/* Copyright (C) 2001      Fabien Seisen        <seisen@linuxfr.org>
 * Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**	    
        \file       htdocs/lib/mysql.lib.php
        \brief      Fichier de la classe permettant de g�rer une base mysql
        \author     Fabien Seisen
        \author     Rodolphe Quiedeville.
        \author     Laurent Destailleur.
        \version    $Revision$
*/


/**
        \class      DoliDb
        \brief      Classe permettant de g�r�r la database de dolibarr
*/

class DoliDb
{
  var $db;                      // Handler de base
  var $type='mysql';            // Nom du gestionnaire
  
  var $results;                 // Resultset de la derni�re requete
  
  var $connected;               // 1 si connect�, 0 sinon
  var $database_selected;       // 1 si base s�lectionn�, 0 sinon
  var $transaction_opened;      // 1 si une transaction est en cours, 0 sinon
  
  var $ok;
  
  // Constantes pour conversion code erreur MySql en code erreur g�n�rique
  var $errorcode_map = array(
            1004 => DB_ERROR_CANNOT_CREATE,
            1005 => DB_ERROR_CANNOT_CREATE,
            1006 => DB_ERROR_CANNOT_CREATE,
            1007 => DB_ERROR_ALREADY_EXISTS,
            1008 => DB_ERROR_CANNOT_DROP,
            1046 => DB_ERROR_NODBSELECTED,
            1050 => DB_ERROR_TABLE_ALREADY_EXISTS,
            1051 => DB_ERROR_NOSUCHTABLE,
            1054 => DB_ERROR_NOSUCHFIELD,
            1062 => DB_ERROR_RECORD_ALREADY_EXISTS,
            1064 => DB_ERROR_SYNTAX,
            1100 => DB_ERROR_NOT_LOCKED,
            1136 => DB_ERROR_VALUE_COUNT_ON_ROW,
            1146 => DB_ERROR_NOSUCHTABLE,
            1048 => DB_ERROR_CONSTRAINT,
        );  
  
  /**
     \brief      Ouverture d'une connection vers le serveur et �ventuellement une database.
     \param	    type		type de base de donn�es (mysql ou pgsql)
     \param	    host		addresse de la base de donn�es
     \param	    user		nom de l'utilisateur autoris�
     \param	    pass		mot de passe
     \param	    name		nom de la database
     \return        int			1 en cas de succ�s, 0 sinon
  */
  function DoliDb($type = 'mysql', $host = '', $user = '', $pass = '', $name = '', $newlink=0)
  {
    global $conf;
    $this->transaction_opened=0;
    
    if ($host == '') $host = $conf->db->host;
    if ($user == '') $user = $conf->db->user;
    if ($pass == '') $pass = $conf->db->pass;
    if ($name == '') $name = $conf->db->name;
    
    //print "Name DB: $host,$user,$pass,$name<br>";
    
    // Essai connexion serveur
    
    $this->db = $this->connect($host, $user, $pass, $newlink);
    
    if ($this->db)
      {
	$this->connected = 1;
	$this->ok = 1;
      }
    else
      {
	$this->connected = 0;
	$this->ok = 0;
	dolibarr_syslog("DoliDB::DoliDB : Erreur Connect");
      }
    
    // Si connexion serveur ok et si connexion base demand�e, on essaie connexion base
    
    if ($this->connected && $name)
      {
	if ($this->select_db($name) == 1)
	  {
	    $this->database_selected = 1;
	    $this->ok = 1;
	  }
	else
	  {
	    $this->database_selected = 0;
	    $this->ok = 0;
	    dolibarr_syslog("DoliDB::DoliDB : Erreur Select_db");
	  }
      }
    else
      {
	// Pas de selection de base demand�e, ok ou ko
	$this->database_selected = 0;
      }
    
    return $this->ok;
  }
  
  /**
     \brief      Selectionne une database.
     \param	    database		nom de la database
     \return	    resource
  */
  
  function select_db($database)
  {
    return mysql_select_db($database, $this->db);
  }
  
  /**
     \brief      Connection vers le serveur
     \param	    host		addresse de la base de donn�es
     \param	    login		nom de l'utilisateur autoris
     \param	    passwd		mot de passe
     \return		resource	handler d'acc�s � la base
  */
  
  function connect($host, $login, $passwd, $newlink=0)
  {
    $this->db  = @mysql_connect($host, $login, $passwd, $newlink);
    //print "Resultat fonction connect: ".$this->db;
    return $this->db;
  }
  
  /**
     \brief      Connexion sur une base de donn�e
     \param	    database		nom de la database
     \return	    result			resultat 1 pour ok, 0 pour non ok
  */
  
  function create_db($database)
  {
    if (mysql_create_db ($database, $this->db))
      {
	return 1;
      }
    else
      {
	return 0;
      }
  }
  
  /**
     \brief      Copie d'un handler de database.
     \return	    resource
  */
  
  function dbclone()
  {
    $db2 = new DoliDb("", "", "", "", "");
    $db2->db = $this->db;
    return $db2;
  }
  
  /**
     \brief      Ouverture d'une connection vers une database.
     \param	    host		Adresse de la base de donn�es
     \param	    login		Nom de l'utilisateur autoris�
     \param	    passwd		Mot de passe
     \return		resource	handler d'acc�s � la base
  */
  
  function pconnect($host, $login, $passwd)
  {
    $this->db  = mysql_pconnect($host, $login, $passwd);
    return $this->db;
  }
  
  /**
     \brief      Fermeture d'une connection vers une database.
     \return	    resource
  */
  
  function close()
  {
    return mysql_close($this->db);
  }
  
  
  /**
     \brief      Debut d'une transaction.
     \return	    int         1 si ouverture transaction ok ou deja ouverte, 0 en cas d'erreur
  */
  
  function begin()
  {
    if (! $this->transaction_opened)
      {
	$ret=$this->query("BEGIN");
	if ($ret) $this->transaction_opened++;
	return $ret;
      }
    else
      {
	$this->transaction_opened++;
	return 1;
      }
  }
  
  /**
     \brief      Validation d'une transaction
     \return	    int         1 si validation ok ou niveau de transaction non ouverte, 0 en cas d'erreur
  */
  
  function commit()
  {
    if ($this->transaction_opened==1)
      {
	$ret=$this->query("COMMIT");
	if ($ret) $this->transaction_opened=0;
	return $ret;
      }
    else
      {
	$this->transaction_opened--;
	return 1;
      }
  }
  
  /**
     \brief      Annulation d'une transaction et retour aux anciennes valeurs
     \return	    int         1 si annulation ok ou transaction non ouverte, 0 en cas d'erreur
  */
  
  function rollback()
  {
    if ($this->transaction_opened)
      {
	$ret=$this->query("ROLLBACK");
	$this->transaction_opened=0;
	return $ret;
      }
    else
      {
	return 1;
      }
  }
  
  /**
     \brief      Effectue une requete et renvoi le resultset de r�ponse de la base
     \param	    query	    Contenu de la query
     \return	    resource    Resultset de la reponse
  */
  
  function query($query)
  {
    $query = trim($query);
    $ret = mysql_query($query, $this->db);
    
    if (! eregi("^COMMIT",$query) && ! eregi("^ROLLBACK",$query)) {
      // Si requete utilisateur, on la sauvegarde ainsi que son resultset
      $this->lastquery=$query;
      $this->results = $ret;
    }
    
    return $ret;
  }
  
  /**
     \brief      Renvoie les donn�es de la requete.
     \param	    nb			Contenu de la query
     \param	    fieldname	Nom du champ
     \return	    resource
  */
  
  function result($nb, $fieldname)
  {
    return mysql_result($this->results, $nb, $fieldname);
  }
  
  /**
     \brief      Renvoie la ligne courante (comme un objet) pour le curseur resultset.
     \param      resultset   Curseur de la requete voulue
     \return	    resource
  */
  
  function fetch_object($resultset=0)
  {
    // Si le resultset n'est pas fourni, on prend le dernier utilis� sur cette connexion
    if (! is_resource($resultset)) { $resultset=$this->results; }
    return mysql_fetch_object($resultset);
  }
  
  /**
     \brief      Renvoie les donn�es dans un tableau.
     \param      resultset   Curseur de la requete voulue
     \return	    array
  */
  
  function fetch_array($resultset=0)
  {
    // Si le resultset n'est pas fourni, on prend le dernier utilis� sur cette connexion
    if (! is_resource($resultset)) { $resultset=$this->results; }
    return mysql_fetch_array($resultset);
  }
  
  /**
     \brief      Renvoie les donn�es comme un tableau.
     \param      resultset   Curseur de la requete voulue
     \return	    array
  */
  
  function fetch_row($resultset=0)
  {
    // Si le resultset n'est pas fourni, on prend le dernier utilis� sur cette connexion
    if (! is_resource($resultset)) { $resultset=$this->results; }
    return mysql_fetch_row($resultset);
  }
  
  /**
     \brief      Obtient les donn�es d'un colonne et renvoie les donn�es sous forme d'objet.
     \param      resultset   Curseur de la requete voulue
     \return     array
  */
  
  function fetch_field($resultset=0)
  {
    // Si le resultset n'est pas fourni, on prend le dernier utilis� sur cette connexion
    if (! is_resource($resultset)) { $resultset=$this->results; }
    return mysql_fetch_field($resultset);
  }
  
  /**
     \brief      Renvoie le nombre de lignes dans le resultat d'une requete SELECT
     \see    	affected_rows
     \param      resultset   Curseur de la requete voulue
     \return     int		    Nombre de lignes
  */
  
  function num_rows($resultset=0)
  {
    // Si le resultset n'est pas fourni, on prend le dernier utilis� sur cette connexion
    if (! is_resource($resultset)) { $resultset=$this->results; }
    return mysql_num_rows($resultset);
  }
  
  /**
     \brief      Renvoie le nombre de lignes dans le resultat d'une requete INSERT, DELETE ou UPDATE
     \see    	num_rows
     \param      resultset   Curseur de la requete voulue
     \return     int		    Nombre de lignes
  */
  
  function affected_rows($resultset=0)
  {
    // Si le resultset n'est pas fourni, on prend le dernier utilis� sur cette connexion
    if (! is_resource($resultset)) { $resultset=$this->results; }
    // mysql necessite un link de base pour cette fonction contrairement
    // a pqsql qui prend un resultset
    return mysql_affected_rows($this->db);
  }
  
  
  /**
     \brief      Renvoie le nombre de champs dans le resultat de la requete.
     \param      resultset   Curseur de la requete voulue
     \return	    int
  */
  
  function num_fields($resultset=0)
  {
    // Si le resultset n'est pas fourni, on prend le dernier utilis� sur cette connexion
    if (! is_resource($resultset)) { $resultset=$this->results; }
    return mysql_num_fields($resultset);
  }
  
  /**
     \brief      Lib�re le dernier resultset utilis� sur cette connexion.
     \param      resultset   Curseur de la requete voulue
     \return	    resource
  */
    
  function free($resultset=0)
  {
    // Si le resultset n'est pas fourni, on prend le dernier utilis� sur cette connexion
    if (! is_resource($resultset)) { $resultset=$this->results; }
    return mysql_free_result($resultset);
  }

  /**
     \brief      D�fini les limites de la requ�te.
     \param	    limit
     \param	    offset
     \return	    int		    Limite
  */
    
  function plimit($limit=0,$offset=0)
  {
    if ($offset > 0)
      {
	return " LIMIT $offset,$limit ";
      }
    else
      {
	return " LIMIT $limit ";
      }
  }
    
    
  /**
     \brief      Formatage par la base de donn�es d'un champ de la base au format Timestamp ou Date (YYYY-MM-DD HH:MM:SS)
     afin de retourner une donn�e toujours au format universel date tms unix.
     \param	    fname
     \return	    date
  */
  function pdate($fname)
  {
    return "unix_timestamp($fname)";
  }
    
  /**
     \brief      Formatage de la date en fonction des locales.
     \param	    fname
     \return	    date
  */
  function idate($fname)
  {
    return strftime("%Y%m%d%H%M%S",$fname);
  }
    
  /**
     \brief      Renvoie la derniere requete soumise par la methode query()
     \return	    lastquery
  */
    
  function lastquery()
  {
    return $this->lastquery;
  }
    
  /**
     \brief     Renvoie le code erreur generique de l'operation precedente.
     \return    error_num       (Exemples: DB_ERROR_TABLE_ALREADY_EXISTS, DB_ERROR_RECORD_ALREADY_EXISTS...)
  */
    
  function errno()
  {
        if (isset($this->errorcode_map[mysql_errno($this->db)])) {
            return $this->errorcode_map[mysql_errno($this->db)];
        }
        return DB_ERROR;
  }
    
  /**
     \brief     Renvoie le texte de l'erreur mysql de l'operation precedente.
     \return    error_text
  */
    
  function error()
  {
    return mysql_error($this->db);
  }
    
  /**
     \brief     R�cup�re l'id gen�r� par le dernier INSERT.
     \param     tab     Nom de la table concern�e par l'insert. Ne sert pas sous MySql mais requis pour compatibilit� avec Postgresql
     \return    int     id
  */
    
  function last_insert_id($tab)
  {
    return mysql_insert_id($this->db);
  }
    
  /**
     \brief      Retourne le dsn pear
     \return     dsn
  */
    
  function getdsn($db_type,$db_user,$db_pass,$db_host,$dbname)
  {
    $pear = $db_type.'://'.$db_user.':'.$db_pass.'@'.
      $db_host.'/'.$db_name;
    
    return $pear;
  }
    
  /**
     \brief      Liste des tables dans une database.
     \param	    database	Nom de la database
     \return	    resource
  */
    
  function list_tables($database)
  {
    $this->results = mysql_list_tables($database, $this->db);
    return  $this->results;
  }
    
}
    
?>
