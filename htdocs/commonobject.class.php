<?php
/* Copyright (C) 2006-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/commonobject.class.php
        \ingroup    core
        \brief      Fichier de la classe mere des classes metiers (facture, contrat, propal, commande, etc...)
        \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/contact.class.php");


/**
		\class 		CommonObject
        \brief 		Classe mere pour h�ritage des classes metiers
*/

class CommonObject
{

	/**
	 *      \brief      Ajoute un contact associ� au l'entit� d�finie dans $this->element
     *      \param      fk_socpeople        Id du contact a ajouter
	 *   	\param 		type_contact 		Type de contact (code ou id)
     *      \param      source              external=Contact externe (llx_socpeople), internal=Contact interne (llx_user)
     *      \return     int                 <0 si erreur, >0 si ok
     */
	function add_contact($fk_socpeople, $type_contact, $source='external')
	{
		global $langs;

        dolibarr_syslog("CommonObject::add_contact $fk_socpeople, $type_contact, $source");

		// V�rification parametres
		if ($fk_socpeople <= 0)
		{
			$this->error=$langs->trans("ErrorWrongValueForParameter","1");
			dolibarr_syslog("CommonObject::add_contact ".$this->error,LOG_ERR);
			return -1;
		}
		if (! $type_contact)
		{
			$this->error=$langs->trans("ErrorWrongValueForParameter","2");
			dolibarr_syslog("CommonObject::add_contact ".$this->error,LOG_ERR);
			return -2;
		}

		$id_type_contact=0;
		if (is_numeric($type_contact))
		{
			$id_type_contact=$type_contact;
		}
		else
		{
			// On recherche id type_contact
			$sql = "SELECT tc.rowid";
			$sql.= " FROM ".MAIN_DB_PREFIX."c_type_contact as tc";
			$sql.= " WHERE element='".$this->element."'";
			$sql.= " AND source='".$source."'";
			$sql.= " AND code='".$type_contact."' AND active=1";
			$resql=$this->db->query($sql);
			if ($resql)
			{
				$obj = $this->db->fetch_object($resql);
				$id_type_contact=$obj->rowid;
			}
		}

        $datecreate = time();

        // Insertion dans la base
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."element_contact";
        $sql.= " (element_id, fk_socpeople, datecreate, statut, fk_c_type_contact) ";
        $sql.= " VALUES (".$this->id.", ".$fk_socpeople." , " ;
		$sql.= $this->db->idate($datecreate);
		$sql.= ", 4, '". $id_type_contact . "' ";
        $sql.= ")";
		dolibarr_syslog("CommonObject::add_contact sql=".$sql);

		$resql=$this->db->query($sql);
		if ($resql)
        {
            return 1;
        }
        else
        {
            $this->error=$this->db->error()." - $sql";
			dolibarr_syslog($this->error,LOG_ERR);
            return -1;
        }
	}

    /**
	 *      \brief      Mise a jour du statut d'un contact
     *      \param      rowid               La reference du lien contact-entit�
     * 		\param		statut	            Le nouveau statut
     *      \param      type_contact_id     Description du type de contact
     *      \return     int                 <0 si erreur, =0 si ok
     */
	function update_contact($rowid, $statut, $type_contact_id)
	{
        // Insertion dans la base
        $sql = "UPDATE ".MAIN_DB_PREFIX."element_contact set";
        $sql.= " statut = $statut,";
        $sql.= " fk_c_type_contact = '".$type_contact_id ."'";
        $sql.= " where rowid = ".$rowid;
        // Retour
        if (  $this->db->query($sql) )
        {
            return 0;
        }
        else
        {
            dolibarr_print_error($this->db);
            return -1;
        }
	 }

	/**
     *    \brief      Supprime une ligne de contact
     *    \param      rowid			La reference du contact
     *    \return     statur        >0 si ok, <0 si ko
     */
    function delete_contact($rowid)
    {

        $sql = "DELETE FROM ".MAIN_DB_PREFIX."element_contact";
        $sql.= " WHERE rowid =".$rowid;
        if ($this->db->query($sql))
        {
            return 1;
        }
        else
        {
            return -1;
        }
    }

    /**
     *    \brief      R�cup�re les lignes de contact de l'objet
     *    \param      statut        Statut des lignes detail � r�cup�rer
     *    \param      source        Source du contact external (llx_socpeople) ou internal (llx_user)
     *    \return     array         Tableau des rowid des contacts
     */
    function liste_contact($statut=-1,$source='external')
    {
        global $langs;

        $tab=array();

        $sql = "SELECT ec.rowid, ec.statut, ec.fk_socpeople as id,";
        if ($source == 'internal') $sql.=" '-1' as socid,";
        if ($source == 'external') $sql.=" t.fk_soc as socid,";
        if ($source == 'internal') $sql.=" t.name as nom,";
        if ($source == 'external') $sql.=" t.name as nom,";
        $sql.= "tc.source, tc.element, tc.code, tc.libelle";
        $sql.= " FROM ".MAIN_DB_PREFIX."element_contact ec,";
        if ($source == 'internal') $sql.=" ".MAIN_DB_PREFIX."user t,";
        if ($source == 'external') $sql.=" ".MAIN_DB_PREFIX."socpeople t,";
        $sql.= " ".MAIN_DB_PREFIX."c_type_contact tc";
        $sql.= " WHERE element_id =".$this->id;
        $sql.= " AND ec.fk_c_type_contact=tc.rowid";
        $sql.= " AND tc.element='".$this->element."'";
        if ($source == 'internal') $sql.= " AND tc.source = 'internal'";
        if ($source == 'external') $sql.= " AND tc.source = 'external'";
        $sql.= " AND tc.active=1";
        if ($source == 'internal') $sql.= " AND ec.fk_socpeople = t.rowid";
        if ($source == 'external') $sql.= " AND ec.fk_socpeople = t.idp";
        if ($statut >= 0) $sql.= " AND statut = '$statut'";
        $sql.=" ORDER BY t.name ASC";

        $resql=$this->db->query($sql);
        if ($resql)
        {
            $num=$this->db->num_rows($resql);
            $i=0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);

                $transkey="TypeContact_".$obj->element."_".$obj->source."_".$obj->code;
                $libelle_type=($langs->trans($transkey)!=$transkey ? $langs->trans($transkey) : $obj->libelle);
                $tab[$i]=array('source'=>$obj->source,'socid'=>$obj->socid,'id'=>$obj->id,'nom'=>$obj->nom,
                               'rowid'=>$obj->rowid,'code'=>$obj->code,'libelle'=>$libelle_type,'status'=>$obj->statut);
                $i++;
            }
            return $tab;
        }
        else
        {
            $this->error=$this->db->error();
            dolibarr_print_error($this->db);
            return -1;
        }
    }

	 /**
     *    \brief      Le d�tail d'un contact
     *    \param      rowid      L'identifiant du contact
     *    \return     object     L'objet construit par DoliDb.fetch_object
     */
 	function detail_contact($rowid)
    {
        $sql = "SELECT ec.datecreate, ec.statut, ec.fk_socpeople, ec.fk_c_type_contact,";
        $sql.= " tc.code, tc.libelle, s.fk_soc";
        $sql.= " FROM ".MAIN_DB_PREFIX."element_contact as ec, ".MAIN_DB_PREFIX."c_type_contact as tc, ";
        $sql.= " ".MAIN_DB_PREFIX."socpeople as s";
        $sql.= " WHERE ec.rowid =".$rowid;
        $sql.= " AND ec.fk_socpeople=s.idp";
        $sql.= " AND ec.fk_c_type_contact=tc.rowid";
        $sql.= " AND tc.element = '".$this->element."'";

        $resql=$this->db->query($sql);
        if ($resql)
        {
            $obj = $this->db->fetch_object($resql);
            return $obj;
        }
        else
        {
            $this->error=$this->db->error();
            dolibarr_print_error($this->db);
            return null;
        }
    }

    /**
     *      \brief      La liste des valeurs possibles de type de contacts
     *      \param      source      internal ou externam
     *      \return     array       La liste des natures
     */
 	function liste_type_contact($source)
    {
        global $langs;

  		$tab = array();

        $sql = "SELECT distinct tc.rowid, tc.code, tc.libelle";
        $sql.= " FROM ".MAIN_DB_PREFIX."c_type_contact as tc";
        $sql.= " WHERE element='".$this->element."'";
        $sql.= " AND source='".$source."'";
        $sql.= " ORDER by tc.code";

        $resql=$this->db->query($sql);
        if ($resql)
        {
            $num=$this->db->num_rows($resql);
            $i=0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($resql);

                $transkey="TypeContact_".$this->element."_".$source."_".$obj->code;
                $libelle_type=($langs->trans($transkey)!=$transkey ? $langs->trans($transkey) : $obj->libelle);
                $tab[$obj->rowid]=$libelle_type;
                $i++;
            }
            return $tab;
        }
        else
        {
            $this->error=$this->db->error();
//            dolibarr_print_error($this->db);
            return null;
        }
    }

    /**
     *      \brief      Retourne id des contacts d'une source et d'un type donn�
     *                  Exemple: contact client de facturation ('external', 'BILLING')
     *                  Exemple: contact client de livraison ('external', 'SHIPPING')
     *                  Exemple: contact interne suivi paiement ('internal', 'SALESREPFOLL')
     *      \return     array       Liste des id contacts
     */
    function getIdContact($source,$code)
    {
        $result=array();
        $i=0;

        $sql = "SELECT ec.fk_socpeople";
        $sql.= " FROM ".MAIN_DB_PREFIX."element_contact as ec, ".MAIN_DB_PREFIX."c_type_contact as tc";
        $sql.= " WHERE ec.element_id = ".$this->id;
        $sql.= " AND ec.fk_c_type_contact=tc.rowid";
        $sql.= " AND tc.element = '".$this->element."'";
        $sql.= " AND tc.source = '".$source."'";
        $sql.= " AND tc.code = '".$code."'";

        $resql=$this->db->query($sql);
        if ($resql)
        {
            while ($obj = $this->db->fetch_object($resql))
            {
                $result[$i]=$obj->fk_socpeople;
                $i++;
            }
        }
        else
        {
            $this->error=$this->db->error();
            return null;
        }

        return $result;
    }

    /** 	
    *		\brief      Charge le contact d'id $id dans this->contact
    *		\param      contactid          Id du contact
    */
    function fetch_contact($contactid) 	
    { 	
       $contact = new Contact($this->db); 	
       $contact->fetch($contactid); 	
       $this->contact = $contact; 	
     }

    /**
     *    	\brief      Charge le tiers d'id $this->socid dans this->client
     */
    function fetch_client()
    {
        $client = new Societe($this->db);
        $client->fetch($this->socid);
        $this->client = $client;
    }

    /**
    *		\brief      Charge le projet d'id $this->projet_id dans this->projet
    */
    function fetch_projet()
    {
        $projet = new Project($this->db);
        $projet->fetch($this->projet_id);
        $this->projet = $projet;
    }

	/** 	
    *		\brief      Charge le user d'id userid dans this->user
    *		\param      userid 		Id du contact
    */
    function fetch_user($userid) 	
    {	
       $user = new User($this->db, $userid); 	
       $user->fetch();
       $this->user = $user; 	
    }

}

?>
