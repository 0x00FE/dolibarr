<?PHP
/* Copyright (C) 2002-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2002-2003 Jean-Louis Bergamo <jlb@j1b.org>
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

/*!	\file adherent_options.class.php
		\brief Classe de gestion de la table des champs optionels adh�rents
		\author Rodolphe Quiedville
		\author	Jean-Louis Bergamo
		\version $Revision$
*/

/*! \class AdherentOptions
		\brief Classe de gestion de la table des champs optionels adh�rents
*/

class AdherentOptions
{
  var $id;
  var $db;
  /*
   * Tableau contenant le nom des champs en clef et la definition de
   * ces champs
   */
  var $attribute_name;
  /*
   * Tableau contenant le nom des champs en clef et le label de ces
   * champs en value
   */
  var $attribute_label;

  var $errorstr;
  /*
   * Constructor
   *
   */

/*!
		\brief AdherentOptions
		\param DB			base de donn�es
		\param id			id de l'adh�rent
*/

  Function AdherentOptions($DB, $id='')
    {
      $this->db = $DB ;
      $this->id = $id;
      $this->errorstr = array();
      $this->attribute_name = array();
      $this->attribute_label = array();
    }

/*!
		\brief fonction qui imprime un liste d'erreurs
*/

  Function print_error_list()
  {
    $num = sizeof($this->errorstr);
    for ($i = 0 ; $i < $num ; $i++)
      {
	print "<li>" . $this->errorstr[$i];
      }
  }

/*!
		\brief fonction qui v�rifie les donn�es entr�es
		\param	minimum
*/

	Function check($minimum=0)
    {
      $err = 0;

      if (strlen(trim($this->societe)) == 0)
	{
	  if ((strlen(trim($this->nom)) + strlen(trim($this->prenom))) == 0)
	    {
	      $error_string[$err] = "Vous devez saisir vos nom et pr�nom ou le nom de votre soci�t�.";
	      $err++;
	    }
	}

      if (strlen(trim($this->adresse)) == 0)
	{
	  $error_string[$err] = "L'adresse saisie est invalide";
	  $err++;
	}

      /*
       * Return errors
       *
       */

      if ($err)
	{
	  $this->errorstr = $error_string;
	  return 0;
	}
      else
	{
	  return 1;
	}

    }

/*!
		\brief fonction qui cr�e un attribut optionnel
		\param	attrname			nom de l'atribut
		\param	type					type de l'attribut
		\param	length				longuer de l'attribut

		\remarks	Ceci correspond a une modification de la table et pas a un rajout d'enregistrement
*/

  Function create($attrname,$type='varchar',$length=255) {
    /*
     *  Insertion dans la base
     */
    if (isset($attrname) && $attrname != '' && preg_match("/^\w[a-zA-Z0-9-]*$/",$attrname)){
      $sql = "ALTER TABLE ".MAIN_DB_PREFIX."adherent_options ";
      switch ($type){
      case 'varchar' :
      case 'interger' :
	$sql .= " ADD $attrname $type($length)";
	break;
      case 'text' :
      case 'date' :
      case 'datetime' :
	$sql .= " ADD $attrname $type";
	break;
      default:
	$sql .= " ADD $attrname $type";
	break;
      }

      if ($this->db->query($sql))
	{
	  return 1;
	}
      else
	{
	  print $this->db->error();
	  print "<h2><br>$sql<br></h2>";
	  return 0;
	}
    }else{
      return 0;
    }
  }

/*!
		\brief fonction qui cr�e un label
		\param	attrname			nom de l'atribut
		\param	label					nom du label
*/

  Function create_label($attrname,$label='') {
    /*
     *  Insertion dans la base
     */
    if (isset($attrname) && $attrname != '' && preg_match("/^\w[a-zA-Z0-9-]*$/",$attrname)){
      $sql = "INSERT INTO ".MAIN_DB_PREFIX."adherent_options_label SET ";
      $escaped_label=mysql_escape_string($label);
      $sql .= " name='$attrname',label='$escaped_label' ";

      if ($this->db->query($sql))
	{
	  return 1;
	}
      else
	{
	  print $this->db->error();
	  print "<h2><br>$sql<br></h2>";
	  return 0;
	}
    }
  }

/*!
		\brief fonction qui supprime un attribut
		\param	attrname			nom de l'atribut
*/

  Function delete($attrname)
  {
    if (isset($attrname) && $attrname != '' && preg_match("/^\w[a-zA-Z0-9-]*$/",$attrname)){
      $sql = "ALTER TABLE ".MAIN_DB_PREFIX."adherent_options DROP COLUMN $attrname";
      
      if ( $this->db->query( $sql) )
	{
	  return $this->delete_label($attrname);
	}
      else
	{
	  print "Err : ".$this->db->error();
	  print "<h2><br>$sql<br></h2>";
	  return 0;
	}
    }else{
      return 0;
    }

  }

/*!
		\brief fonction qui supprime un label
		\param	attrname			nom du label
*/

  Function delete_label($attrname)
  {
    if (isset($attrname) && $attrname != '' && preg_match("/^\w[a-zA-Z0-9-]*$/",$attrname)){
      $sql = "DELETE FROM ".MAIN_DB_PREFIX."adherent_options_label WHERE name='$attrname'";

      if ( $this->db->query( $sql) )
	{
	  return 1;
	}
      else
	{
	  print "Err : ".$this->db->error();
	  print "<h2><br>$sql<br></h2>";
	  return 0;
	}
    }else{
      return 0;
    }

  }

/*!
		\brief fonction qui modifie un attribut optionnel
		\param	attrname			nom de l'atribut
		\param	type					type de l'attribut
		\param	length				longuer de l'attribut
*/

  Function update($attrname,$type='varchar',$length=255)
  {
    if (isset($attrname) && $attrname != '' && preg_match("/^\w[a-zA-Z0-9-]*$/",$attrname)){
      $sql = "ALTER TABLE ".MAIN_DB_PREFIX."adherent_options ";
      switch ($type){
      case 'varchar' :
      case 'interger' :
	$sql .= " MODIFY COLUMN $attrname $type($length)";
	break;
      case 'text' :
      case 'date' :
      case 'datetime' :
	$sql .= " MODIFY COLUMN $attrname $type";
	break;
      default:
	$sql .= " MODIFY COLUMN $attrname $type";
	break;
      }
      //$sql .= "MODIFY COLUMN $attrname $type($length)";
      
      if ( $this->db->query( $sql) )
	{
	  return 1;
	}
      else
	{
	  print "Err : ".$this->db->error();
	  print "<h2><br>$sql<br></h2>";
	  return 0;
	}
    }else{
      return 0;
    }

  }

/*!
		\brief fonction qui modifie un label
		\param	attrname			nom de l'atribut
		\param	label					nom du label
*/

  Function update_label($attrname,$label='')
  {
    if (isset($attrname) && $attrname != '' && preg_match("/^\w[a-zA-Z0-9-]*$/",$attrname)){
      $escaped_label=mysql_escape_string($label);
      $sql = "REPLACE INTO ".MAIN_DB_PREFIX."adherent_options_label SET name='$attrname',label='$escaped_label'";

      if ( $this->db->query( $sql) )
	{
	  return 1;
	}
      else
	{
	  print "Err : ".$this->db->error();
	  print "<h2><br>$sql<br></h2>";
	  return 0;
	}
    }else{
      return 0;
    }

  }

	Function fetch_optionals()
    {
      $this->fetch_name_optionals();
      $this->fetch_name_optionals_label();
    }

	Function fetch_name_optionals()
  {
    $array_name_options=array();
    $sql = "SHOW COLUMNS FROM ".MAIN_DB_PREFIX."adherent_options";

    if ( $this->db->query( $sql) )
      {
      if ($this->db->num_rows())
	{
	while ($tab = $this->db->fetch_object())
	  {
	  if ($tab->Field != 'optid' && $tab->Field != 'tms' && $tab->Field != 'adhid')
	    {
	      // we can add this attribute to adherent object
	      $array_name_options[]=$tab->Field;
	      $this->attribute_name[$tab->Field]=$tab->Type;
	    }
	  }
	return $array_name_options;
      }else{
	return array();
      }
    }else{
      print $this->db->error();
      return array() ;
    }

  }

	Function fetch_name_optionals_label()
  {
    $array_name_label=array();
    $sql = "SELECT name,label FROM ".MAIN_DB_PREFIX."adherent_options_label";

    if ( $this->db->query( $sql) )
      {
      if ($this->db->num_rows())
	{
	while ($tab = $this->db->fetch_object())
	  {
	    // we can add this attribute to adherent object
	    $array_name_label[$tab->name]=stripslashes($tab->label);
	    $this->attribute_label[$tab->name]=stripslashes($tab->label);
	  }
	return $array_name_label;
      }else{
	return array();
      }
    }else{
      print $this->db->error();
      return array() ;
    }

  }
}
?>
