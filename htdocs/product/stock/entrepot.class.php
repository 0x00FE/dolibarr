<?PHP
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

class Entrepot
{
  var $db ;

  var $id ;
  var $libelle;
  var $description;

  Function Entrepot($DB)
    {
      $this->db = $DB;
    }
  /*
   *
   *
   */
  Function create($user) 
    {

      if ($this->db->query("BEGIN") )
	{

	  $sql = "INSERT INTO llx_entrepot (datec, fk_user_author)";
	  $sql .= " VALUES (now(),".$user->id.")";

	  if ($this->db->query($sql) )
	    {
	      $id = $this->db->last_insert_id();
	      
	      if ($id > 0)
		{
		  $this->id = $id;
		  if ( $this->update($id, $user) )
		    {
		      $this->db->query("COMMIT") ;
		      return $id;
		    }
		  else
		    {
		      $this->db->query("ROLLBACK") ;
		    }


		}
	    }
	  else
	    {
	      print $this->db->error() . ' in ' . $sql;
	    }
	}
    }
  /*
   *
   */
  Function update($id, $user)
    {
      if (strlen(trim($this->libelle)))
	{
	  $sql = "UPDATE llx_entrepot ";
	  $sql .= " SET label = '" . trim($this->libelle) ."'";
	  $sql .= ",description = '" . trim($this->description) ."'";
	  
	  $sql .= " WHERE rowid = " . $id;
	  
	  if ( $this->db->query($sql) )
	    {
	      return 1;
	    }
	  else
	    {
	      print $this->db->error() . ' in ' . $sql;
	    }
	}
      else
	{
	  $this->mesg_error = "Vous devez indiquer une r�f�rence";
	  return 0;
	}
    }
  /*
   *
   *
   *
   */
  Function fetch ($id)
    {
    
      $sql = "SELECT rowid, label, description";
      $sql .= " FROM llx_entrepot WHERE rowid = $id";

      $result = $this->db->query($sql) ;

      if ( $result )
	{
	  $result = $this->db->fetch_array();

	  $this->id             = $result["rowid"];
	  $this->ref            = $result["ref"];
	  $this->libelle        = stripslashes($result["label"]);
	  $this->description    = stripslashes($result["description"]);

	  $this->db->free();
	  return 1;
	}
      else
	{
	  print $this->db->error();
	  return -1;
	}
  }
  /*
   *
   *
   */
  Function count_propale()
    {
      $sql = "SELECT pd.fk_propal";
      $sql .= " FROM llx_propaldet as pd, llx_product as p";
      $sql .= " WHERE p.rowid = pd.fk_product AND p.rowid = ".$this->id;
      $sql .= " GROUP BY pd.fk_propal";

      $result = $this->db->query($sql) ;

      if ( $result )
	{
	  return $this->db->num_rows();
	}
      else
	{
	  return 0;
	}
    }
  /*
   *
   *
   */
  Function count_propale_client()
    {
      $sql = "SELECT pr.fk_soc";
      $sql .= " FROM llx_propaldet as pd, llx_product as p, llx_propal as pr";
      $sql .= " WHERE p.rowid = pd.fk_product AND pd.fk_propal = pr.rowid AND p.rowid = ".$this->id;
      $sql .= " GROUP BY pr.fk_soc";

      $result = $this->db->query($sql) ;

      if ( $result )
	{
	  return $this->db->num_rows();
	}
      else
	{
	  return 0;
	}
    }
  /*
   *
   *
   */
  Function count_facture()
    {
      $sql = "SELECT pd.fk_facture";
      $sql .= " FROM llx_facturedet as pd, llx_product as p";
      $sql .= " WHERE p.rowid = pd.fk_product AND p.rowid = ".$this->id;
      $sql .= " GROUP BY pd.fk_facture";

      $result = $this->db->query($sql) ;

      if ( $result )
	{
	  return $this->db->num_rows();
	}
      else
	{
	  return 0;
	}
    }
  /*
   *
   *
   */

  Function list_array()
  {
    $liste = array();

    $sql = "SELECT rowid, label FROM llx_entrepot";

      $result = $this->db->query($sql) ;
      $i = 0;
      $num = $this->db->num_rows();

      if ( $result )
	{
	  while ($i < $num)
	    {
	      $row = $this->db->fetch_row($i);
	      $liste[$row[0]] = $row[1];
	      $i++;
	    }
	  $this->db->free();
	}
      return $liste;
  }
}
?>
