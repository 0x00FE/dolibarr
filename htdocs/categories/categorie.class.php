<?php
/* Copyright (C) 2005 Matthieu Valleton <mv@seeschloss.org>
 * Copyright (C) 2005 Davoleau Brice    <brice.davoleau@gmail.com>
 * Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

require_once(DOL_DOCUMENT_ROOT."/product.class.php");


class Categorie
{
  var $db;

  var $id;
  var $label;
  var $description;

  /**
   * Constructeur
   * db : acc�s base de donn�es
   * id : id de la cat�gorie
   */
  function Categorie ($db, $id=-1)
  {
    $this->db = $db;
    $this->id = $id;

    if ($id != -1) $this->fetch ($this->id);
  }
	
  /**
   * Charge la cat�gorie
   * id : id de la cat�gorie � charger
   */
  function fetch ($id)
  {
    $sql  = "SELECT rowid,label,description ";
    $sql .= "FROM ".MAIN_DB_PREFIX."categorie WHERE rowid = ".$id;

    $resql  = $this->db->query ($sql);

    if ($resql)
      {
	$res = $this->db->fetch_array($resql);

	$this->id		= $res['rowid'];
	$this->label		= $res['label'];
	$this->description	= stripslashes($res['description']);

	$this->db->free($resql);
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }

  /**
   * Ajoute la cat�gorie dans la base de donn�es
   * retour : -1 : erreur SQL
   *          -2 : nouvel ID inconnu
   *          -3 : cat�gorie invalide
   */
  function create ()
  {
    if (!$this->check () || $this->already_exists ($this->label))
      {
	return -3;
      }

    $sql  = "INSERT INTO ".MAIN_DB_PREFIX."categorie (label, description) ";
    $sql .= "VALUES ('".$this->label."', '".$this->description."')";
		
    $res  = $this->db->query ($sql);

    if ($res)
      {
	$id = $this->db->last_insert_id (MAIN_DB_PREFIX."categorie");

	if ($id > 0)
	  {
	    $this->id = $id;
	    return $id;
	  }
	else
	  {
	    return -2;
	  }
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	
  /**
   * Mise � jour de la cat�gorie
   * retour :  1 : OK
   *          -1 : erreur SQL
   *          -2 : cat�gorie invalide
   */
  function update ()
  {
    if (!$this->check () || $this->id < 0)
      {
	return -2;
      }

    $sql  = "UPDATE ".MAIN_DB_PREFIX."categorie ";
    $sql .= "SET label = '".trim ($this->label)."'";
    if (strlen (trim ($this->description)) > 0)
      {
	$sql .= ", description = '".trim ($this->description)."'";
      }
    $sql .= " WHERE rowid = ".$this->id;

    if ($this->db->query ($sql))
      {
	return 1;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }

  /**
   * Supprime la cat�gorie
   * Les produits et sous-cat�gories deviennent orphelins
   * si $all = false, et sont (seront :) supprim�s sinon
   * TODO : imp. $all
   */
  function remove ($all = false)
  {
    if (!$this->check ())
      {
	return -2;
      }

    $sql  = "DELETE FROM ".MAIN_DB_PREFIX."categorie_product ";
    $sql .= "WHERE fk_categorie = ".$this->id;
    if (!$this->db->query ($sql))
      {
	dolibarr_print_error ($this->db);
	return -1;
      }

    $sql  = "DELETE FROM ".MAIN_DB_PREFIX."categorie_association ";
    $sql .= "WHERE fk_categorie_mere  = ".$this->id;
    $sql .= "   OR fk_categorie_fille = ".$this->id;
    if (!$this->db->query ($sql))
      {
	dolibarr_print_error ($this->db);
	return -1;
      }

    $sql  = "DELETE FROM ".MAIN_DB_PREFIX."categorie ";
    $sql .= "WHERE rowid = ".$this->id;
    if (!$this->db->query ($sql))
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
    else
      {
	return 1;
      }

  }
	
  /**
   * V�rifie si la cat�gorie est correcte (pr�te � �tre
   * enregistr�e ou mise � jour
   */
  function check ()
  {
    if (strlen (trim ($this->label)) == 0)
      {
	return false;
      }

    return true;
  }
	
  /**
   * Ajout d'une sous-cat�gorie
   * $fille : objet cat�gorie
   * retour :  1 : OK
   *          -2 : $fille est d�j� une fille de $this
   *          -3 : cat�gorie ($this ou $fille) invalide
   */
  function add_fille ($fille)
  {
    if (!$this->check () || !$fille->check ())
      {
	return -3;
      }
    else if ($this->is_fille ($fille))
      {
	return -2;
      }

    $sql  = "INSERT INTO ".MAIN_DB_PREFIX."categorie_association (fk_categorie_mere, fk_categorie_fille) ";
    $sql .= "VALUES (".$this->id.", ".$fille->id.")";

    if ($this->db->query ($sql))
      {
	return 1;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	 
  /**
   * Suppression d'une sous-cat�gorie (seulement "d�sassociation")
   * $fille : objet cat�gorie
   * retour :  1 : OK
   *          -3 : cat�gorie ($this ou $fille) invalide
   */
  function del_fille ($fille)
  {
    if (!$this->check () || !$fille->check ())
      {
	return -3;
      }

    $sql  = "DELETE FROM ".MAIN_DB_PREFIX."categorie_association ";
    $sql .= "WHERE fk_categorie_mere = ".$this->id." and fk_categorie_fille = ".$fille->id;

    if ($this->db->query ($sql))
      {
	return 1;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	 
  /**
   * Ajout d'un produit � la cat�gorie
   * retour :  1 : OK
   *          -1 : erreur SQL
   *          -2 : id non renseign�
   */
  function add_product ($prod)
  {
    if ($this->id == -1)
      {
	return -2;
      }
		
    $sql  = "INSERT INTO ".MAIN_DB_PREFIX."categorie_product (fk_categorie, fk_product) ";
    $sql .= "VALUES (".$this->id.", ".$prod->id.")";

    if ($this->db->query ($sql))
      {
	return 1;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	
  /**
   * Suppresion d'un produit de la cat�gorie 
   * @param $prod est un objet de type produit
   * retour :  1 : OK
   *          -1 : erreur SQL
   */
  function del_product ($prod)
  {
    $sql  = "DELETE FROM ".MAIN_DB_PREFIX."categorie_product";
    $sql .= " WHERE fk_categorie = ".$this->id;
    $sql .= " AND   fk_product   = ".$prod->id;

    if ($this->db->query ($sql))
      {
	return 1;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	
  /**
   * Retourne les produits de la cat�gorie
   */
  function get_products ()
  {
    $sql  = "SELECT fk_product FROM ".MAIN_DB_PREFIX."categorie_product ";
    $sql .= "WHERE fk_categorie = ".$this->id;

    $res  = $this->db->query ($sql);

    if ($res)
      {
	$prods = array ();
	while ($rec = $this->db->fetch_array ($res))
	  {
	    $prod = new Product ($this->db, $rec['fk_product']);
	    $prod->fetch ($prod->id);
	    $prods[] = $prod;
	  }
	return $prods;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	
  /**
   * Retourne les filles de la cat�gorie
   */
  function get_filles ()
  {
    $sql  = "SELECT fk_categorie_fille FROM ".MAIN_DB_PREFIX."categorie_association ";
    $sql .= "WHERE fk_categorie_mere = ".$this->id;

    $res  = $this->db->query ($sql);

    if ($res)
      {
	$cats = array ();
	while ($rec = $this->db->fetch_array ($res))
	  {
	    $cat = new Categorie ($this->db, $rec['fk_categorie_fille']);
	    $cats[] = $cat;
	  }
	return $cats;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	
  /**
   * La cat�gorie $fille est-elle une fille de cette cat�gorie ?
   */
  function is_fille ($fille)
  {
    $sql  = "SELECT count(fk_categorie_fille) FROM ".MAIN_DB_PREFIX."categorie_association ";
    $sql .= "WHERE fk_categorie_mere = ".$this->id." AND fk_categorie_fille = ".$fille->id;

    $res  = $this->db->query ($sql);
		
    $n    = $this->db->fetch_array ($res);
		
    return ($n[0] > 0);
  }
	
  /**
   * Retourne toutes les cat�gories
   */
  function get_all_categories ()
  {
    $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."categorie";

    $res = $this->db->query ($sql);

    if ($res)
      {
	$cats = array ();
	while ($record = $this->db->fetch_array ($res))
	  {
	    $cat = new Categorie ($this->db, $record['rowid']);
	    $cats[$record['rowid']] = $cat;
	  }
	return $cats;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }

  /**
   * Retourne toutes les cat�gories qui ont au moins 1 fille
   */
  function get_all_meres ()
  {
    $sql  = "SELECT DISTINCT fk_categorie_mere FROM ".MAIN_DB_PREFIX."categorie_association";

    $res = $this->db->query ($sql);

    if ($res)
      {
	$cats = array ();
	while ($record = $this->db->fetch_array ($res))
	  {
	    $cat = new Categorie ($this->db, $record['fk_categorie_mere']);
	    $cats[$record['fk_categorie_mere']] = $cat;
	  }
	return $cats;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	
  /**
   * Retourne le nombre total de cat�gories
   */
  function get_nb_categories ()
  {
    $sql = "SELECT count(rowid) FROM ".MAIN_DB_PREFIX."categorie";
    $res = $this->db->query ($sql);

    if ($res)
      {
	$res = $this->db->fetch_array ();
	return $res[0];
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	
  /**
   * V�rifie si une cat�gorie porte le label $label
   */
  function already_exists ($label)
  {
    $sql  = "SELECT count(rowid) FROM ".MAIN_DB_PREFIX."categorie ";
    $sql .= "WHERE label = '".$label."'";

    $res  = $this->db->query ($sql);
    $res  = $this->db->fetch_array ($res);

    return ($res[0] > 0);
  }

  /**
   * Retourne les cat�gories de premier niveau
   */
  function get_main_categories ()
  {
    $allcats = $this->get_all_categories ();

    /*	$sql  = "SELECT rowid,label,description FROM ".MAIN_DB_PREFIX."categorie ";
	$sql .= "WHERE ".MAIN_DB_PREFIX."categorie.rowid NOT IN (";
	$sql .= "SELECT fk_categorie_fille FROM ".MAIN_DB_PREFIX."categorie_association)";

	$res = $this->db->query ($sql);

	if ($res)
	{
	$cats = array ();
	while ($record = $this->db->fetch_array ($res))
	{
	$cat = array ("rowid" => $record['rowid'],
	"label" => $record['label'],
	"description" => $record['description']);
	$cats[$record['rowid']] = $cat;
	}
	return $cats;
	}
	else
	{
	return -1;
	}
    */	// pas de NOT IN avec MySQL ?

		
    $maincats = array ();
    $filles   = array ();
		
    $sql = "SELECT fk_categorie_fille FROM ".MAIN_DB_PREFIX."categorie_association";
    $res = $this->db->query ($sql);
    while ($res = $this->db->fetch_array ($res))
      {
	$filles[] = $res['fk_categorie_fille'];
      }

    foreach ($allcats as $cat)
      {
	if (!in_array ($cat->id, $filles))
	  {
	    $maincats[] = $cat;
	  }
	else
	  {
	  }
      }
		
    return $maincats;
  }

  /**
   * Retourne les chemin de la cat�gorie, avec les noms des cat�gories
   * s�par�s par $sep (" >> " par d�faut)
   */
  function print_all_ways ($sep = " &gt;&gt; ", $url='')
  {
    $ways = array ();
		
    foreach ($this->get_all_ways () as $way)
      {
	$w = array ();
	foreach ($way as $cat)
	  {
	    if ($url == '')
	      {
		$w[] = "<a href='".DOL_URL_ROOT."/categories/viewcat.php?id=".$cat->id."'>".$cat->label."</a>";
	      }
	    else
	      {
		$w[] = "<a href='".DOL_URL_ROOT."/$url?catid=".$cat->id."'>".$cat->label."</a>";
	      }
	  }
	$ways[] = implode ($sep, $w);
      }

    return $ways;
  }
	

  /**
   * get_primary_way() affiche le chemin le plus court pour se rendre � un produit
   */
  function get_primary_way($id)
  {
    $primary_way = Array("taille"=>-1,"chemin"=>Array());
    $meres = $this->containing($id);
    foreach ($meres as $mere)
      {
	foreach ($mere->get_all_ways() as $way)
	  {
	    if(sizeof($way)<$primary_way["taille"] || $primary_way["taille"]<0)
	      {
		$primary_way["taille"] = sizeOf($way);
		$primary_way["chemin"] = $way;
	      }
	  }
      }
    return $primary_way["chemin"];

  }

  /**
   * print_primary_way() affiche le chemin le plus court pour se rendre � un produit
   */
  function print_primary_way($id, $sep= " &gt;&gt; ",$url)
  {
    $primary_way = Array();
    $way = $this->get_primary_way($id);
    $w = array();
    foreach ($way as $cat)
      {
	if ($url == '')
	  {
	    $w[] = "<a href='".DOL_URL_ROOT."/categories/viewcat.php?id=".$cat->id."'>".$cat->label."</a>";
	  }
	else
	  {
	    $w[] = "<a href='".DOL_URL_ROOT."/$url?catid=".$cat->id."'>".$cat->label."</a>";
	  }
      }
    
    return implode($sep, $w);
  }
  /**
   * Retourne un tableau contenant la liste des cat�gories m�res
   */
  function get_meres ()
  {
    $meres = array ();

    $sql  = "SELECT fk_categorie_mere FROM ".MAIN_DB_PREFIX."categorie_association ";
    $sql .= "WHERE fk_categorie_fille = ".$this->id;

    $res  = $this->db->query ($sql);

    while ($cat = $this->db->fetch_array ($res))
      {
	$meres[] = new Categorie ($this->db, $cat['fk_categorie_mere']);
      }

    return $meres;
  }
	
  /**
   * Retourne dans un tableau tous les chemins possibles pour arriver � la cat�gorie
   * en partant des cat�gories principales, repr�sent�s par des tableaux de cat�gories
   */
  function get_all_ways ()
  {
    $ways = array ();

    foreach ($this->get_meres () as $mere)
      {
	foreach ($mere->get_all_ways () as $way)
	  {
	    $w   = $way;
	    $w[] = $this;

	    $ways[] = $w;
	  }
      }

    if (sizeof ($ways) == 0)
      $ways[0][0] = $this;

    return $ways;
  }
	
  /**
   * Retourne les cat�gories contenant le produit $id
   */
  function containing ($id)
  {
    $cats = array ();
		
    $sql  = "SELECT fk_categorie FROM ".MAIN_DB_PREFIX."categorie_product ";
    $sql .= "WHERE  fk_product = ".$id;

    $res = $this->db->query ($sql);
		
    if ($res)
      {
	while ($cat = $this->db->fetch_array ($res))
	  {
	    $cats[] = new Categorie ($this->db, $cat['fk_categorie']);
	  }

	return $cats;
      }
    else
      {
	dolibarr_print_error ($this->db);
	return -1;
      }
  }
	
	  /**
   * Retourne les cat�gories contenant le produit $ref
   */
  function containing_ref ($ref)
  {
    $cats = array ();
		
    $sql = "SELECT c.fk_categorie, c.fk_product, p.rowid, p.label";
    $sql.= " FROM ".MAIN_DB_PREFIX."categorie_product as c, ".MAIN_DB_PREFIX."product as p";
    $sql.= " WHERE  p.label = '".$ref."' AND c.fk_product = p.rowid";

    $res = $this->db->query ($sql);
		
    if ($res)
    {
	     while ($cat = $this->db->fetch_array ($res))
	     {
	        $cats[] = new Categorie ($this->db, $cat['fk_categorie']);
	     }

	  return $cats;
    }
    else
    {
	     dolibarr_print_error ($this->db);
	     return -1;
    }
  }
	
  /**
   * Retourne les cat�gories dont le nom correspond � $nom
   * ajoute des wildcards sauf si $exact = true
   */
  function rechercher_par_nom ($nom, $exact = false)
  {
    $cats = array ();
		
    if (!$exact)
      {
	$nom = '%'.str_replace ('*', '%', $nom).'%';
      }

    $sql  = "SELECT rowid FROM ".MAIN_DB_PREFIX."categorie ";
    $sql .= "WHERE label LIKE '".$nom."'";

    $res  = $this->db->query ($sql);

    if ($res)
      {
	while ($id = $this->db->fetch_array ($res))
	  {
	    $cats[] = new Categorie ($this->db, $id['rowid']);
	  }

	return $cats;
      }
    else
      {
	return 0;
      }
  }
}
?>
