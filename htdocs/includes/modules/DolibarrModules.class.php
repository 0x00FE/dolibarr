<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004 Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2004 Eric Seigne          <eric.seigne@ryxeo.com>
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
 *
 * $Id$
 * $Source$
 *
 */

/**     \file       htdocs/includes/modules/DolibarrModules.class.php
        \brief      Fichier de description et activation des modules Dolibarr
*/


/**     \class DolibarrModules
        \brief      Classe m�re des classes de description et activation des modules Dolibarr
*/
class DolibarrModules
{
    var $db;         // Handler d'acc�s aux base
    var $boxes;      // Tableau des boites
    var $const;      // Tableau des constantes


    /**  \brief      Constructeur
     *   \param      DB      handler d'acc�s base
     */
    function DolibarrModules($DB)
    {
        $this->db = $DB ;
    }


    /**  \brief      Fonction d'activation. Ins�re en base les constantes et boites du module
     *   \param      array_sql       tableau de requete sql a ex�cuter � l'activation
     */
    function _init($array_sql)
    {
        // Ins�re les constantes
        $err = 0;
        $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."const WHERE name = '".$this->const_name."';";
        $this->db->query($sql_del);

        $sql ="INSERT INTO ".MAIN_DB_PREFIX."const (name,value,visible) VALUES
        ('".$this->const_name."','1',0);";
        if (!$this->db->query($sql))
        {
            $err++;
        }

        // Ins�re les boxes dans llx_boxes_def
        foreach ($this->boxes as $key => $value)
        {
            $titre = $this->boxes[$key][0];
            $file  = $this->boxes[$key][1];

            $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."boxes_def WHERE name ='".$titre."'";

            if ( $this->db->query($sql) )
            {
                $row = $this->db->fetch_row($sql);
                if ($row[0] == 0)
                {
                    $sql = "INSERT INTO ".MAIN_DB_PREFIX."boxes_def (name, file) VALUES ('".$titre."','".$file."')";
                    if (! $this->db->query($sql) )
                    {
                        $err++;
                    }
                }
            }
            else
            {
                $err++;
            }
        }

        // D�finit les constantes associ�es
        foreach ($this->const as $key => $value)
        {
            $name   = $this->const[$key][0];
            $type   = $this->const[$key][1];
            $val    = $this->const[$key][2];
            $note   = $this->const[$key][3];
            $visible= $this->const[$key][4]||'0';

            $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."const WHERE name ='".$name."'";

            if ( $this->db->query($sql) )
            {
                $row = $this->db->fetch_row($sql);

                if ($row[0] == 0)
                {
                    // Si non trouve // '$visible'
                    if (strlen($note)){
                        $sql = "INSERT INTO ".MAIN_DB_PREFIX."const (name,type,value,note,visible) VALUES ('$name','$type','$val','$note',0);";
                    }elseif (strlen($val))
                    {
                        $sql = "INSERT INTO ".MAIN_DB_PREFIX."const (name,type,value,visible) VALUES ('$name','$type','$val',0);";
                    }
                    else
                    {
                        $sql = "INSERT INTO ".MAIN_DB_PREFIX."const (name,type,visible) VALUES ('$name','$type',0);";
                    }

                    if (! $this->db->query($sql) )
                    {
                        $err++;
                    }
                }
            }
            else
            {
                $err++;
            }
        }

        // D�finit les permissions associ�es au module actif
        if (is_array($this->rights))
        {
            foreach ($this->rights as $key => $value)
            {
                $r_id       = $this->rights[$key][0];
                $r_desc     = $this->rights[$key][1];
                $r_type     = $this->rights[$key][2];
                $r_def      = $this->rights[$key][3];
                $r_perms    = $this->rights[$key][4];
                $r_subperms = $this->rights[$key][5];
                $r_modul    = $this->rights_class;

                if (strlen($r_perms) )
                {
                    if (strlen($r_subperms) )
                    {
                        $sql = "INSERT INTO ".MAIN_DB_PREFIX."rights_def ";
                        $sql .= " (id, libelle, module, type, bydefault, perms, subperms)";
                        $sql .= " VALUES ";
                        $sql .= "(".$r_id.",'".$r_desc."','".$r_modul."','".$r_type."',".$r_def.",'".$r_perms."','".$r_subperms."');";
                    }
                    else
                    {
                        $sql = "INSERT INTO ".MAIN_DB_PREFIX."rights_def ";
                        $sql .= " (id, libelle, module, type, bydefault, perms)";
                        $sql .= " VALUES ";
                        $sql .= "(".$r_id.",'".$r_desc."','".$r_modul."','".$r_type."',".$r_def.",'".$r_perms."');";
                    }
                }
                else
                {
                    $sql = "INSERT INTO ".MAIN_DB_PREFIX."rights_def ";
                    $sql .= " (id, libelle, module, type, bydefault)";
                    $sql .= " VALUES ";
                    $sql .= "(".$r_id.",'".$r_desc."','".$r_modul."','".$r_type."',".$r_def.");";
                }

                if ( $this->db->query($sql) )
                {
                }

            }
        }

        // Cr�� les r�pertoires
        if (is_array($this->dirs))
        {
            foreach ($this->dirs as $key => $value)
            {
                $dir = $value;
                if ($dir && ! file_exists($dir))
                {
                    umask(0);
                    if (! @mkdir($dir, 0755))
                    {
                        $this->error = "Erreur: Le r�pertoire '$dir' n'existe pas et Dolibarr n'a pu le cr�er.";
                        dolibarr_syslog("Erreur: impossible de cr�er $dir");

                    }
                }
            }
        }

        // Ex�cute les requetes sql compl�mentaires
        for ($i = 0 ; $i < sizeof($array_sql) ; $i++)
        {
            if (! $this->db->query($array_sql[$i]))
            {
                $err++;
            }
        }

        if ($err > 0)
        {
            return 0;
        }
        else
        {
            return 1;
        }
    }


    /**     \brief      Fonction de d�sactivation. Supprime de la base les constantes et boites du module
     *      \param      array_sql       tableau de requete sql a ex�cuter � la d�sactivation
     *      \reutnr     int             0 si erreur, 1 si ok
     */
    function _remove($array_sql)
    {
        $err = 0;

        // Supprime la constante d'activation du module
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."const WHERE name = '".$this->const_name."'";
        if (!$this->db->query($sql))
        {
            $err++;
        }

        // Supprime les droits de la liste des droits disponibles
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."rights_def WHERE module = '".$this->rights_class."';";
        if (!$this->db->query($sql))
        {
            $err++;
        }

        // Supprime les boites de la liste des boites disponibles
        foreach ($this->boxes as $key => $value)
        {
            $titre = $this->boxes[$key][0];
            $file  = $this->boxes[$key][1];

            $sql = "DELETE FROM ".MAIN_DB_PREFIX."boxes_def WHERE file = '".$file."'";
            if (! $this->db->query($sql) )
            {
                $err++;
            }
        }

        // Ex�cute les requets sql compl�mentaires
        for ($i = 0 ; $i < sizeof($array_sql) ; $i++)
        {
            if (!$this->db->query($array_sql[$i]))
            {
                $err++;
            }
        }

        if ($err > 0)
        {
            return 0;
        }
        else
        {
            return 1;
        }
    }


    /**  \brief     Retourne le nom traduit du module si la traduction existe dans admin.lang,
     *              sinon le nom d�fini par d�faut dans le module.
     *   \return    string      Nom du module traduit
     */
    function getName()
    {
        global $langs;
        $langs->load("admin");

        if ($langs->trans("Module".$this->numero."Name") != ("Module".$this->numero."Name"))
        {
            // Si traduction du nom du module existe
            return $langs->trans("Module".$this->numero."Name");
        }
        else
        {
            // Si traduction du nom du module n'existe pas, on prend d�finition en dur dans module
            return $this->name;
        }
    }


    /**  \brief     Retourne la description traduite du module si la traduction existe dans admin.lang,
     *              sinon la description d�finie par d�faut dans le module.
     *   \return    string      Nom du module traduit
     */
    function getDesc()
    {
        global $langs;
        $langs->load("admin");

        if ($langs->trans("Module".$this->numero."Desc") != ("Module".$this->numero."Desc"))
        {
            // Si traduction de la description du module existe
            return $langs->trans("Module".$this->numero."Desc");
        }
        else
        {
            // Si traduction de la description du module n'existe pas, on prend d�finition en dur dans module
            return $this->description;
        }
    }

}
?>
