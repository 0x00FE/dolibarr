<?php
/* Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/includes/triggers/interface_demo.class.php
        \ingroup    core
        \brief      Fichier de demo de personalisation des actions du workflow
        \remarks    Son propre fichier d'actions peut etre cr�� par recopie de celui-ci:
                    - Le nom du fichier doit etre interface_xxx.class.php
                    - Le fichier doit rester stock� dans includes/triggers
                    - Le nom de la classe doit etre InterfaceXxx
*/


/**
        \class      InterfaceDemo
        \brief      Classe des fonctions triggers des actions personalis�es du workflow
*/

class InterfaceDemo
{
    var $db;
    
    /**
     *   \brief      Constructeur.
     *   \param      DB      Handler d'acc�s base
     */
    function InterfaceDemo($DB)
    {
        $this->db = $DB ;
    
        $this->name = "Demo";
        $this->family = "demo";
        $this->description = "Les triggers de ce composant sont des fonctions vierges. Elles n'ont aucun effet. Ce composant est fourni � des fins de tutorial.";
        $this->version = 'dolibarr';                        // 'experimental' or 'dolibarr' or version
    }
    
    /**
     *   \brief      Renvoi nom du lot de triggers
     *   \return     string      Nom du lot de triggers
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     *   \brief      Renvoi descriptif du lot de triggers
     *   \return     string      Descriptif du lot de triggers
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   \brief      Renvoi version du lot de triggers
     *   \return     string      Version du lot de triggers
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }
    
    /**
     *      \brief      Fonction appel�e lors du d�clenchement d'un �v�nement Dolibarr.
     *                  D'autres fonctions run_trigger peuvent etre pr�sentes dans includes/triggers
     *      \param      action      Code de l'evenement
     *      \param      object      Objet concern�
     *      \param      user        Objet user
     *      \param      lang        Objet lang
     *      \param      conf        Objet conf
     *      \return     int         <0 si ko, 0 si aucune action faite, >0 si ok
     */
	function run_trigger($action,$object,$user,$langs,$conf)
    {
        // Mettre ici le code � ex�cuter en r�action de l'action
        // Les donn�es de l'action sont stock�es dans $object
    
        // Users
        if     ($action == 'USER_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_MODIFY')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_NEW_PASSWORD')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_DISABLE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_DELETE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Companies
        elseif     ($action == 'COMPANY_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'COMPANY_MODIFY')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'COMPANY_DELETE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Contacts
        elseif ($action == 'CONTACT_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CONTACT_MODIFY')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CONTACT_DELETE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Products
        elseif ($action == 'PRODUCT_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'PRODUCT_MODIFY')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'PRODUCT_DELETE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

		// Customer orders
        elseif ($action == 'ORDER_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'ORDER_VALIDATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'ORDER_DELETE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

		// Supplier orders
        elseif ($action == 'ORDER_SUPPLIER_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'ORDER_SUPPLIER_VALIDATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

        // Proposals
        elseif ($action == 'PROPAL_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'PROPAL_MODIFY')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'PROPAL_VALIDATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'PROPAL_CLOSE_SIGNED')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'PROPAL_CLOSE_REFUSED')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

        // Contracts
        elseif ($action == 'CONTRACT_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'CONTRACT_MODIFY')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'CONTRACT_ACTIVATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'CONTRACT_CANCEL')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'CONTRACT_CLOSE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'CONTRACT_DELETE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

        // Bills
        elseif ($action == 'BILL_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'BILL_MODIFY')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'BILL_VALIDATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'BILL_CANCEL')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'BILL_DELETE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

        // Payments
        elseif ($action == 'PAYMENT_CUSTOMER_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'PAYMENT_SUPPLIER_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

		// Interventions
	    elseif ($action == 'FICHEINTER_VALIDATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

        // Members
        elseif ($action == 'MEMBER_CREATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'MEMBER_VALIDATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'MEMBER_SUBSCRIPTION')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'MEMBER_MODIFY')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'MEMBER_RESILIATE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }
        elseif ($action == 'MEMBER_DELETE')
        {
            dolibarr_syslog("Trigger '".$this->name."' for action '$action' launched");
        }

		return 0;
    }

}
?>
