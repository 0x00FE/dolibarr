<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 *
 *
 * L'utilisation d'adresses de courriers �lectroniques dans les op�rations
 * de prospection commerciale est subordonn�e au recueil du consentement 
 * pr�alable des personnes concern�es.
 *
 * Le dispositif juridique applicable a �t� introduit par l'article 22 de 
 * la loi du 21 juin 2004  pour la confiance dans l'�conomie num�rique.
 *
 * Les dispositions applicables sont d�finies par les articles L. 34-5 du 
 * code des postes et des t�l�communications et L. 121-20-5 du code de la 
 * consommation. L'application du principe du consentement pr�alable en 
 * droit fran�ais r�sulte de la transposition de l'article 13 de la Directive 
 * europ�enne du 12 juillet 2002 � Vie priv�e et communications �lectroniques �.
 */

/**
       	\file       htdocs/includes/modules/mailings/poire.modules.php
		\ingroup    mailing
		\brief      Fichier de la classe permettant de g�n�rer la liste de destinataires Poire
		\version    $Revision$
*/

include_once DOL_DOCUMENT_ROOT.'/includes/modules/mailings/modules_mailings.php';


/**
	    \class      mailing_poire
		\brief      Classe permettant de g�n�rer la liste des destinataires Poire
*/

class mailing_poire extends MailingTargets
{
    var $name='ContactCompanies';                       // Identifiant du module mailing
    var $desc='Contacts des soci�t�s';      			// Libell� utilis� si aucune traduction pour MailingModuleDescXXX ou XXX=name trouv�e
    var $require_module=array("commercial");            // Module mailing actif si modules require_module actifs
    var $require_admin=0;                               // Module mailing actif pour user admin ou non
    var $picto='contact';
    
    var $db;
    

    function mailing_poire($DB)
    {
        $this->db=$DB;
    }


	function getSqlArrayForStats()
	{
        global $langs;
        $langs->load("commercial");

	    $statssql=array();
        $statssql[0]="SELECT '".$langs->trans("NbOfCompaniesContacts")."' as label, count(distinct(c.email)) as nb FROM ".MAIN_DB_PREFIX."socpeople as c, ".MAIN_DB_PREFIX."societe as s WHERE s.rowid = c.fk_soc AND s.client = 1 AND c.email != ''";

		return $statssql;
	}

    
    /*
     *		\brief		Return here number of distinct emails returned by your selector.
     *					For example if this selector is used to extract 500 different
     *					emails from a text file, this function must return 500.
     *		\return		int
     */
    function getNbOfRecipients()
    {
        $sql  = "SELECT count(distinct(c.email)) as nb";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c";
        $sql .= ", ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE s.rowid = c.fk_soc";
        $sql .= " AND c.email != ''";

        // La requete doit retourner un champ "nb" pour etre comprise
        // par parent::getNbOfRecipients
        return parent::getNbOfRecipients($sql); 
    }
    
    
    /**
     *      \brief      Affiche formulaire de filtre qui apparait dans page de selection
     *                  des destinataires de mailings
     *      \return     string      Retourne zone select
     */
    function formFilter()
    {
        global $langs;
        $langs->load("companies");
        $langs->load("commercial");
        $langs->load("suppliers");
        
        $s='';
        $s.='<select name="filter" class="flat">';
        $s.='<option value="all">'.$langs->trans("ContactsAllShort").'</option>';
        $s.='<option value="prospects">'.$langs->trans("ThirdPartyProspects").'</option>';
        $s.='<option value="customers">'.$langs->trans("ThirdPartyCustomers").'</option>';
        //$s.='<option value="customersidprof">'.$langs->trans("ThirdPartyCustomersWithIdProf12",$langs->trans("ProfId1"),$langs->trans("ProfId2")).'</option>';
        $s.='<option value="suppliers">'.$langs->trans("ThirdPartySuppliers").'</option>';
        $s.='</select>';
        return $s;
    }
    
    
    /**
     *      \brief      Renvoie url lien vers fiche de la source du destinataire du mailing
     *      \return     string      Url lien
     */
    function url($id)
    {
        return '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$id.'">'.img_object('',"contact").'</a>';
    }
    
    
    /**
     *    \brief      Ajoute destinataires dans table des cibles
     *    \param      mailing_id    Id du mailing concern�
     *    \param      filterarray   Requete sql de selection des destinataires
     *    \return     int           <0 si erreur, nb ajout si ok
     */
    function add_to_target($mailing_id,$filtersarray=array())
    {
        $cibles = array();

        // La requete doit retourner: id, email, fk_contact, name, firstname
        $sql = "SELECT c.rowid as id, c.email as email, c.rowid as fk_contact, c.name as name, c.firstname as firstname";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c";
        $sql .= ", ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE s.rowid = c.fk_soc";
        $sql .= " AND c.email != ''";
        foreach($filtersarray as $key)
        {
            if ($key == 'prospects') $sql.= " AND s.client=2";
            if ($key == 'customers') $sql.= " AND s.client=1";
            if ($key == 'suppliers') $sql.= " AND s.fournisseur=1";
        }
        $sql .= " ORDER BY c.email";


        // Stocke destinataires dans cibles
        $result=$this->db->query($sql);
        if ($result)
        {
            $num = $this->db->num_rows($result);
            $i = 0;
            $j = 0;

            dolibarr_syslog("poire.modules.php: mailing $num cibles trouv�es");

            $old = '';
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($result);
                if ($old <> $obj->email)
                {
                    $cibles[$j] = array(
                    		'email' => $obj->email,
                    		'fk_contact' => $obj->fk_contact,
                    		'name' => $obj->name,
                    		'firstname' => $obj->firstname,
                    		'url' => $this->url($obj->id)
                    		);
                    $old = $obj->email;
                    $j++;
                }

                $i++;
            }
        }
        else
        {
            dolibarr_syslog($this->db->error());
            $this->error=$this->db->error();
            return -1;
        }

        return parent::add_to_target($mailing_id, $cibles);
    }

}

?>
