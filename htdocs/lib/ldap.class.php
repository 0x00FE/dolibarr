<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2005-2006 Regis Houssin        <regis.houssin@cap-networks.com>
 * Copyright (C) 2006      Laurent Destailleur  <eldy@users.sourceforge.net>
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
 */


/**
		\file 		htdocs/lib/ldap.class.php
		\brief 		Classe de gestion d'annuaire LDAP
		\author 	Rodolphe Quiedeville
		\author		Benoit Mortier
		\author		Regis Houssin
		\author		Laurent Destailleur
		\version 	$Revision$
*/
class Ldap
{

    /**
     * Tableau des serveurs (IP addresses ou nom d'h�tes)
     */
    var $server;
    /**
     * Base DN (e.g. "dc=foo,dc=com")
     */
    var $dn;
    /**
     * type de serveur, actuellement OpenLdap et Active Directory
     */
    var $serverType;
    /**
     * Version du protocole ldap
     */
    var $domain;
    /**
     * Administrateur Ldap
     * Active Directory ne supporte pas les connexions anonymes
     */
    var $searchUser;
    /**
     * Mot de passe de l'administrateur
     * Active Directory ne supporte pas les connexions anonymes
     */
    var $searchPassword;
    /**
     *  DN des utilisateurs
     */
    var $people;
    /**
     * DN des groupes
     */
    var $groups;
    /**
     * Code erreur retourn� par le serveur Ldap
     */
    var $ldapErrorCode;
    /**
     * Message texte de l'erreur
     */
    var $ldapErrorText;


    //Fetch user
    var $name;
    var $firstname;
    var $login;
    var $phone;
    var $fax;
    var $mail;
    var $mobile;

    var $uacf;
    var $pwdlastset;


    // 1.2 Private properties ----------------------------------------------------
    /**
     * The internal LDAP connection handle
     */
    var $connection;
    /**
     * Result of any connections etc.
     */
    var $result;

    /**
     * Constructor- creates a new instance of the authentication class
     *
     */
    function Ldap ()
    {
    	global $conf;

        //Server
        $this->server = array($conf->global->LDAP_SERVER_HOST, $conf->global->LDAP_SERVER_HOST_SLAVE);
        $this->serverPort          = $conf->global->LDAP_SERVER_PORT;
        $this->ldapProtocolVersion = $conf->global->LDAP_SERVER_PROTOCOLVERSION;
        $this->dn                  = $conf->global->LDAP_SERVER_DN;
        $this->serverType          = $conf->global->LDAP_SERVER_TYPE;
        $this->domain              = $conf->global->LDAP_SERVER_DN;
        $this->searchUser          = $conf->global->LDAP_ADMIN_DN;
        $this->searchPassword      = $conf->global->LDAP_ADMIN_PASS;
        $this->people              = $conf->global->LDAP_USER_DN;
        $this->groups              = $conf->global->LDAP_GROUP_DN;
        $this->filter              = $conf->global->LDAP_FILTER_CONNECTION;

        //Users
        $this->attr_login      = $conf->global->LDAP_FIELD_LOGIN; //unix
        $this->attr_sambalogin = $conf->global->LDAP_FIELD_LOGIN_SAMBA; //samba, activedirectory
        $this->attr_name       = $conf->global->LDAP_FIELD_NAME;
        $this->attr_firstname  = $conf->global->LDAP_FIELD_FIRSTNAME;
        $this->attr_mail       = $conf->global->LDAP_FIELD_MAIL;
        $this->attr_phone      = $conf->global->LDAP_FIELD_PHONE;
        $this->attr_fax        = $conf->global->LDAP_FIELD_FAX;
        $this->attr_mobile     = $conf->global->LDAP_FIELD_MOBILE;
    }



    // 2.1 Connection handling methods -------------------------------------------

    /**
     * 2.1.1 : Connects to the server. Just creates a connection which is used
     * in all later access to the LDAP server. If it can't connect and bind
     * anonymously, it creates an error code of -1. Returns true if connected,
     * false if failed. Takes an array of possible servers - if one doesn't work,
     * it tries the next and so on.
     *		\deprecated		Utiliser connect_bind a la place
     */
	function connect()
	{
		foreach ($this->server as $key => $host)
		{
			if (ereg('^ldap',$host))
			{
				$this->connection = ldap_connect($host);
			}
			else
			{
				$this->connection = ldap_connect($host,$this->serverPort);
			}
			if ($this->connection)
			{
				$this->setVersion();
				if ($this->serverType == "activedirectory")
				{
					$this->setReferrals();
					return true;
				}
				else
				{
					// Connected, now try binding anonymously
					$this->result=@ldap_bind( $this->connection);
				}
				return true;
			}
		}

		$this->ldapErrorCode = -1;
		$this->ldapErrorText = "Unable to connect to any server";
		return false;
	}


    /**
     *		\brief		Connect and bind
     *		\return		<0 si KO, 1 si bind anonymous, 2 si bind auth
     *		\remarks	this->connection and $this->bind are defined
     */
	function connect_bind()
	{
		global $conf,$langs;

		$connected=0;
		$this->bind=0;

		foreach ($this->server as $key => $host)
		{
			if ($connected) break;

			if (ereg('^ldap',$host))
			{
				$this->connection = ldap_connect($host);
			}
			else
			{
				$this->connection = ldap_connect($host,$this->serverPort);
			}

			if ($this->connection)
			{
				$this->setVersion();

				if ($this->serverType == "activedirectory")
				{
					$this->setReferrals();
					$connected=2;
				}
				else
				{
					// Try in auth mode
					if ($conf->global->LDAP_ADMIN_DN && $conf->global->LDAP_ADMIN_PASS)
					{
						dolibarr_syslog("Ldap.class::connect_bind try bindauth on ".$host." user=".$conf->global->LDAP_ADMIN_DN,LOG_DEBUG);
						$result=$this->bindauth($conf->global->LDAP_ADMIN_DN,$conf->global->LDAP_ADMIN_PASS);
						if ($result)
						{
							$this->bind=$this->result;
							$connected=2;
							break;
						}
						else
						{
							$this->error=ldap_errno($this->connection).' '.ldap_error($this->connection);
						}
					}
					// Try in anonymous
					if (! $this->bind)
					{
						dolibarr_syslog("Ldap.class::connect_bind try bind on ".$host,LOG_DEBUG);
						$result=$this->bind();
						if ($result)
						{
							$this->bind=$this->result;
							$connected=1;
							break;
						}
						else
						{
							$this->error=ldap_errno($this->connection).' '.ldap_error($this->connection);
						}
					}
				}
			}

			if (! $connected) $this->close();
		}

		$return=($connected ? $connected : -1);
		dolibarr_syslog("Ldap.class::connect_bind return=".$return,LOG_DEBUG);
		return $return;
	}



    /**
     * 2.1.2 : Simply closes the connection set up earlier.
     * Returns true if OK, false if there was an error.
     */
    function close()
    {
        if ($this->connection && ! @ldap_close($this->connection))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 2.1.3 : Anonymously binds to the connection. After this is done,
     * queries and searches can be done - but read-only.
     */
    function bind()
    {
        if (! $this->result=@ldap_bind($this->connection))
        {
            $this->ldapErrorCode = ldap_errno($this->connection);
            $this->ldapErrorText = ldap_error($this->connection);
            $this->error=$this->ldapErrorCode." ".$this->ldapErrorText;
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 2.1.4 : Binds as an authenticated user, which usually allows for write
     * access. The FULL dn must be passed. For a directory manager, this is
     * "cn=Directory Manager" under iPlanet. For a user, it will be something
     * like "uid=jbloggs,ou=People,dc=foo,dc=com".
     */
    function bindauth($bindDn,$pass)
    {
        if (! $this->result = @ldap_bind( $this->connection,$bindDn,$pass))
        {
            $this->ldapErrorCode = ldap_errno($this->connection);
            $this->ldapErrorText = ldap_error($this->connection);
            $this->error=$this->ldapErrorCode." ".$this->ldapErrorText;
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
	* 	\brief 		Unbind du serveur ldap.
	* 	\param		ds
	* 	\return		bool
	*/
	function unbind()
	{
		if (!$this->result=@ldap_unbind($this->connection))
		{
			return false;
		} else {
			return true;
		}
	}


    /**
		 * \brief verification de la version du serveur ldap.
		 * \param	ds
		 * \return	version
     */
     function getVersion()
     {
     	$version = 0;
     	$version = @ldap_get_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $version);
     	return $version;
    }

    /**
		 * \brief changement de la version du serveur ldap.
		 * \return	version
     */
     function setVersion() {
     	global $conf;
		// LDAP_OPT_PROTOCOL_VERSION est une constante qui vaut 17
     	$ldapsetversion = ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $this->ldapProtocolVersion);
     	return $ldapsetversion;
    }

    /**
		 * \brief changement du referrals.
		 * \return	referrals
     */
     function setReferrals() {
     	global $conf;
		// LDAP_OPT_REFERRALS est une constante qui vaut ?
     	$ldapreferrals = ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
     	return $ldapreferrals;
    }


	/**
	*   \brief      Mise � jour dans l'arbre LDAP
	*   \param      dn			DN
	*   \param      info		Tableau info
	*   \param    	user		Objet user qui fait l'op�ration
	*	\return		int			<0 si ko, >0 si ok
	*	\remarks	Ldap object connect and bind must have been done
	*/
	function update($dn,$info,$user,$olddn='')
	{
		global $conf, $langs;

		if (! $this->connection)
		{
			$this->error=$langs->trans("NotConnected");
			return -2;
		}
		if (! $this->bind)
		{
			$this->error=$langs->trans("NotConnected");
			return -3;
		}

		if (! $olddn) $olddn=$dn;

		dolibarr_syslog("Ldap.class::update dn=".$dn." olddn=".$olddn);

		// On supprime et on ins�re
		$result = $this->delete($olddn);
		$result = $this->add($dn, $info, $user);
		if ($result <= 0)
		{
			$this->error = ldap_errno($this->connection)." ".ldap_error($this->connection)." ".$this->error;
			dolibarr_syslog("Ldap.class::update ".$this->error,LOG_ERROR);
			//print_r($info);
			return -1;
		}
		else
		{
			dolibarr_syslog("Ldap.class::update done successfully");
			return 1;
		}
	}


    // 2.2 Password methods ------------------------------------------------------

    /**
     * 2.2.1 : Checks a username and password - does this by logging on to the
     * server as a user - specified in the DN. There are several reasons why
     * this login could fail - these are listed below.
     */
    function checkPass($uname,$pass)
    {
        /* Construct the full DN, eg:-
        ** "uid=username, ou=People, dc=orgname,dc=com"
        */
        if ($this->serverType == "activedirectory") {
            $checkDn = "$uname@$this->domain";
        } else {
            $checkDn = $this->getUserIdentifier() . "=$uname, " . $this->setDn(true);
        }
        // Try and connect...
        $this->result = @ldap_bind( $this->connection,$checkDn,$pass);
        if ( $this->result) {
            // Connected OK - login credentials are fine!
            return true;
        } else {
            /* Login failed. Return false, together with the error code and text from
            ** the LDAP server. The common error codes and reasons are listed below :
            ** (for iPlanet, other servers may differ)
            ** 19 - Account locked out (too many invalid login attempts)
            ** 32 - User does not exist
            ** 49 - Wrong password
            ** 53 - Account inactive (manually locked out by administrator)
            */
            $this->ldapErrorCode = ldap_errno( $this->connection);
            $this->ldapErrorText = ldap_error( $this->connection);
            return false;
        }
    }


    /**
     * 2.2.2 : Allows a password to be changed. Note that on most LDAP servers,
     * a new ACL must be defined giving users the ability to modify their
     * password attribute (userPassword). Otherwise this will fail.
     */
    function changePass($uname,$oldPass,$newPass)
    {
        // builds the appropriate dn, based on whether $this->people and/or $this->group is set
        if ($this->serverType == "activedirectory") {
            $checkDn = "$uname@$this->domain";
        } else {
            $checkDn = $this->getUserIdentifier() . "=$uname, " . $this->setDn(true);
        }
        $this->result = @ldap_bind( $this->connection,$checkDn,$oldPass);

        if ( $this->result) {
            // Connected OK - Now modify the password...
            $info["userPassword"] = $newPass;
            $this->result = @ldap_modify( $this->connection, $checkDn, $info);
            if ( $this->result) {
                // Change went OK
                return true;
            } else {
                // Couldn't change password...
                $this->ldapErrorCode = ldap_errno( $this->connection);
                $this->ldapErrorText = ldap_error( $this->connection);
                return false;
            }
        } else {
            // Login failed - see checkPass method for common error codes
            $this->ldapErrorCode = ldap_errno( $this->connection);
            $this->ldapErrorText = ldap_error( $this->connection);
            return false;
        }
    }


    /**
     * 2.2.3 : Returns days until the password will expire.
     * We have to explicitly state this is what we want returned from the
     * LDAP server - by default, it will only send back the "basic"
     * attributes.
     */
    function checkPassAge ( $uname)
    {
        $results[0] = "passwordexpirationtime";
        // builds the appropriate dn, based on whether $this->people and/or $this->group is set
        $checkDn = $this->setDn(true);
        $this->result = @ldap_search( $this->connection,$checkDn,$this->getUserIdentifier()."=$uname",$results);

        if ( !$info=@ldap_get_entries( $this->connection, $this->result)) {
            $this->ldapErrorCode = ldap_errno( $this->connection);
            $this->ldapErrorText = ldap_error( $this->connection);
            return false;
        } else {
            /* Now work out how many days remaining....
            ** Yes, it's very verbose code but I left it like this so it can easily
            ** be modified for your needs.
            */
            $date  = $info[0]["passwordexpirationtime"][0];
            $year  = substr( $date,0,4);
            $month = substr( $date,4,2);
            $day   = substr( $date,6,2);
            $hour  = substr( $date,8,2);
            $min   = substr( $date,10,2);
            $sec   = substr( $date,12,2);

            $timestamp = mktime( $hour,$min,$sec,$month,$day,$year);
            $today  = mktime();
            $diff   = $timestamp-$today;
            return round( ( ( ( $diff/60)/60)/24));
        }
    }

    // 2.3 Group methods ---------------------------------------------------------

    /**
     * 2.3.1 : Checks to see if a user is in a given group. If so, it returns
     * true, and returns false if the user isn't in the group, or any other
     * error occurs (eg:- no such user, no group by that name etc.)
     */
    function checkGroup ( $uname,$group)
    {
        // builds the appropriate dn, based on whether $this->people and/or $this->group is set
        $checkDn = $this->setDn(false);

        // We need to search for the group in order to get it's entry.
        $this->result = @ldap_search( $this->connection, $checkDn, "cn=" .$group);
        $info = @ldap_get_entries( $this->connection, $this->result);

        // Only one entry should be returned(no groups will have the same name)
        $entry = ldap_first_entry( $this->connection,$this->result);

        if ( !$entry) {
            $this->ldapErrorCode = ldap_errno( $this->connection);
            $this->ldapErrorText = ldap_error( $this->connection);
            return false;  // Couldn't find the group...
        }
        // Get all the member DNs
        if ( !$values = @ldap_get_values( $this->connection, $entry, "uniqueMember")) {
            $this->ldapErrorCode = ldap_errno( $this->connection);
            $this->ldapErrorText = ldap_error( $this->connection);
            return false; // No users in the group
        }

        foreach ( $values as $key => $value) {
            /* Loop through all members - see if the uname is there...
            ** Also check for sub-groups - this allows us to define a group as
            ** having membership of another group.
            ** FIXME:- This is pretty ugly code and unoptimised. It takes ages
            ** to search if you have sub-groups.
            */
            list( $cn,$ou) = explode( ",",$value);
            list( $ou_l,$ou_r) = explode( "=",$ou);

            if ( $this->groups==$ou_r) {
                list( $cn_l,$cn_r) = explode( "=",$cn);
                // OK, So we now check the sub-group...
                if ( $this->checkGroup ( $uname,$cn_r)) {
                    return true;
                }
            }

            if ( preg_match( "/$uname/i",$value)) {
                return true;
            }
        }
    }


	/*
	* 	\brief		Add a LDAP entry
	*	\param		dn			DN entry key
	*	\param		info		Attributes array
	*	\param		user		Objet utilisateru qui cr�e
	*	\return		int			<0 si KO, >0 si OK
	*/
	function add($dn, $info, $user)
	{
		global $conf;

		dolibarr_syslog("Ldap.class::add dn=".$dn." info=".join(',',$info));

		// Encode en UTF8
		$dn=$this->ldap_utf8_encode($dn);
		foreach($info as $key => $val)
		{
			if (! is_array($val)) $info[$key]=$this->ldap_utf8_encode($val);
		}

		$this->dump($dn,$info);
		
		//print_r($info);
		$result=@ldap_add($this->connection, $dn, $info);

		if ($result)
		{
			dolibarr_syslog("Ldap.class::add successfull");
			return 1;
		}
		else
		{
			dolibarr_syslog("Ldap.class::add failed");
			return -1;
		}
	}

	/*
	* 	\brief		Delete a LDAP entry
	*	\param		dn			DN entry key
	*	\return		int			<0 si KO, >0 si OK
	*/
	function delete($dn)
	{
		global $conf;

		dolibarr_syslog("Ldap.class::delete Delete LDAP entry dn=".$dn);

		// Encode en UTF8
		$dn=$this->ldap_utf8_encode($dn);

		$result=@ldap_delete($this->connection, $dn);

		if ($result) return 1;
		return -1;
	}


	/*
	* 	\brief		Dump a LDAP message to ldapinput.in file
	*	\param		dn			DN entry key
	*	\param		info		Attributes array
	*	\return		int			<0 si KO, >0 si OK
	*/
	function dump($dn, $info)
	{
		global $conf;
		create_exdir($conf->ldap->dir_temp);
		
		$file=$conf->ldap->dir_temp.'/ldapinput.in';
		$fp=fopen($file,"w");
		if ($fp)
		{
			fputs($fp, "dn: ".$dn."\n");	
			foreach($info as $key => $value)
			{
				if (! is_array($value))
				{
					fputs($fp, "$key: $value\n");
				}
				else
				{
					foreach($value as $valuekey => $valuevalue)
					{
						fputs($fp, "$key: $valuevalue\n");
					}
				}
			}
			fclose($fp);
		}
	}


    // 2.4 Attribute methods -----------------------------------------------------
    /**
     * 2.4.1 : Returns an array containing values for an attribute and for first record matching filterrecord
     */
    function getAttribute($filterrecord,$attribute)
    {
        $attributes[0] = $attribute;

        // We need to search for this user in order to get their entry.
        $this->result = @ldap_search($this->connection,$this->people,$filterrecord,$attributes);

		// Pourquoi cette ligne ?
        //$info = ldap_get_entries($this->connection, $this->result);

        // Only one entry should ever be returned (no user will have the same uid)
        $entry = ldap_first_entry($this->connection, $this->result);

        if (!$entry)
		{
            $this->ldapErrorCode = -1;
            $this->ldapErrorText = "Couldn't find user";
            return false;  // Couldn't find the user...
        }

        // Get values
        if (! $values = @ldap_get_values( $this->connection, $entry, $attribute))
		{
            $this->ldapErrorCode = ldap_errno( $this->connection);
            $this->ldapErrorText = ldap_error( $this->connection);
            return false; // No matching attributes
        }

        // Return an array containing the attributes.
        return $values;
    }


    // 2.5 User methods ----------------------------------------------------------
    /**
     * 2.5.1 : Returns an array containing a details of users, sorted by
     * username. The search criteria is a standard LDAP query - * returns all
     * users.  The $attributeArray variable contains the required user detail field names
     */
    function getUsers($search, $userDn, $useridentifier, $attributeArray, $activefilter=0)
    {
		$userslist=array();
		
		dolibarr_syslog("Ldap.class::getUsers search=".$search." userDn=".$userDn." useridentifier=".$useridentifier." attributeArray=".$attributeArray);

	    // if the directory is AD, then bind first with the search user first
        if ($this->serverType == "activedirectory") {
            $this->bindauth($this->searchUser, $this->searchPassword);
        }

        //permet de choisir le filtre adequat
        if ($activefilter == 1)
        {
        	$filter = $this->filter;
        }
        else
        {
        	$filter = '('.$useridentifier.'='.$search.')';
        }

        $this->result = @ldap_search($this->connection, $userDn, $filter);

        if (!$this->result)
        {
        	$this->error = ldap_errno($this->connection)." ".ldap_error($this->connection);
        	return -1;
        }

        $info = @ldap_get_entries($this->connection, $this->result);
		//print_r($info);
        for ($i = 0; $i < $info["count"]; $i++)
        {
            $recordid=$this->ldap_utf8_decode($info[$i][$useridentifier][0]);
			if ($recordid)
			{
				//print "Found record with key $useridentifier=".$recordid."<br>\n";
	            $userslist[$recordid][$useridentifier]=$recordid;
	
	            // Add to the array for each attribute in my list
	            for ($j = 0; $j < count($attributeArray); $j++)
	            {
	            	//print " Param ".$attributeArray[$j]."=".$info[$i][$attributeArray[$j]][0]."<br>\n";
	                $userslist[$recordid][$attributeArray[$j]] = $this->ldap_utf8_decode($info[$i][$attributeArray[$j]][0]);
	            }
			}
        }

        asort($userslist);
        return $userslist;
    }

    /**
     *  Converts a little-endian hex-number to one, that 'hexdec' can convert
     *	Indispensable pour Active Directory
     */
    function littleEndian($hex) {
    	for ($x=strlen($hex)-2; $x >= 0; $x=$x-2) {
    		$result .= substr($hex,$x,2);
    	}
    	return $result;
    }
    
    
    /**
      * R�cup�re le SID de l'utilisateur 	 
      * ldapuser. le login de l'utilisateur 	 
      * Indispensable pour Active Directory
      */ 	 
     function getObjectSid($ldapUser) 	 
     { 	 
         $criteria =  $this->getUserIdentifier()."=$ldapUser"; 	 
         $justthese = array("objectsid"); 	 
  	 
         $ldapSearchResult = ldap_search($this->connection, $this->people, $criteria, $justthese); 	 
  	 
         $entry = ldap_first_entry($this->connection, $ldapSearchResult); 	 
         $ldapBinary = ldap_get_values_len ($this->connection, $entry, "objectsid"); 	 
         $SIDText = $this->binSIDtoText($ldapBinary[0]); 	 
         return $SIDText; 	 
         return $ldapBinary; 	 
     }
     
     /**
      * Returns the textual SID 	 
      * Indispensable pour Active Directory
      */ 	 
      function binSIDtoText($binsid) { 	 
         $hex_sid=bin2hex($binsid); 	 
         $rev = hexdec(substr($hex_sid,0,2));          // Get revision-part of SID 	 
         $subcount = hexdec(substr($hex_sid,2,2));    // Get count of sub-auth entries 	 
         $auth = hexdec(substr($hex_sid,4,12));      // SECURITY_NT_AUTHORITY 	 
         $result = "$rev-$auth"; 	 
         for ($x=0;$x < $subcount; $x++) { 	 
                 $subauth[$x] = hexdec($this->littleEndian(substr($hex_sid,16+($x*8),8)));  // get all SECURITY_NT_AUTHORITY 	 
                 $result .= "-".$subauth[$x]; 	 
         } 	 
         return $result; 	 
     }
     

	/**
	* 	\brief 		Fonction de recherche avec filtre
	*	\remarks	this->connection doit etre d�fini donc la methode bind ou bindauth doit avoir deja �t� appel�e
	* 	\param 		checkDn			DN de recherche (Ex: ou=users,cn=my-domain,cn=com)
	* 	\param 		filter			Filtre de recherche (ex: (sn=nom_personne) )
	*	\return		array			Tableau des reponses
	*/
	function search($checkDn, $filter)
	{
		dolibarr_syslog("Ldap.class::search checkDn=".$checkDn." filter=".$filter);

		$checkDn=$this->ldap_utf8_encode($checkDn);
		$filter=$this->ldap_utf8_encode($filter);

		// if the directory is AD, then bind first with the search user first
		if ($this->serverType == "activedirectory") {
			$this->bindauth($this->searchUser, $this->searchPassword);
		}

		$this->result = @ldap_search($this->connection, $checkDn, $filter);

		$result = @ldap_get_entries($this->connection, $this->result);
		if (! $result)
		{
			$this->error = ldap_errno($this->connection)." ".ldap_error($this->connection);
			return -1;
		}
		else
		{
			ldap_free_result($this->result);
			return $result;
		}
	}


   /**
     * \brief r�cup�re les attributs de l'utilisateur
     * \param $user : utilisateur ldap
     */
    function fetch($user)
    {
        // Perform the search and get the entry handles

        // if the directory is AD, then bind first with the search user first
        if ($this->serverType == "activedirectory") {
            $this->bindauth($this->searchUser, $this->searchPassword);
        }
        $userIdentifier = $this->getUserIdentifier();

        $filter = '('.$this->filter.'('.$userIdentifier.'='.$user.'))';

        $this->result = @ldap_search($this->connection, $this->people, $filter);

        $result = @ldap_get_entries( $this->connection, $this->result);

        if (! $result)
        {
        	$this->error = ldap_errno($this->connection)." ".ldap_error($this->connection);
        }
        else
        {
        	$this->name       = $this->ldap_utf8_decode($result[0][$this->attr_name][0]);
        	$this->firstname  = $this->ldap_utf8_decode($result[0][$this->attr_firstname][0]);
        	$this->login      = $this->ldap_utf8_decode($result[0][$userIdentifier][0]);
        	$this->phone      = $this->ldap_utf8_decode($result[0][$this->attr_phone][0]);
        	$this->fax        = $this->ldap_utf8_decode($result[0][$this->attr_fax][0]);
        	$this->mail       = $this->ldap_utf8_decode($result[0][$this->attr_mail][0]);
        	$this->mobile     = $this->ldap_utf8_decode($result[0][$this->attr_mobile][0]);

        	$this->uacf       = $this->parseUACF($this->ldap_utf8_decode($result[0]["useraccountcontrol"][0]));
        	$this->pwdlastset = $this->ldap_utf8_decode($result[0]["pwdlastset"][0]);

        	ldap_free_result($this->result);
        }
      }


    // 2.6 helper methods

    /**
     * Sets and returns the appropriate dn, based on whether there
     * are values in $this->people and $this->groups.
     *
     * @param boolean specifies whether to build a groups dn or a people dn
     * @return string if true ou=$this->people,$this->dn, else ou=$this->groups,$this->dn
     */
    function setDn($peopleOrGroups) {

        if ($peopleOrGroups) {
            if ( isset($this->people) && (strlen($this->people) > 0) ) {
                $checkDn = "ou=" .$this->people. ", " .$this->dn;
            }
        } else {
            if ( isset($this->groups) && (strlen($this->groups) > 0) ) {
                $checkDn = "ou=" .$this->groups. ", " .$this->dn;
            }
        }

        if ( !isset($checkDn) ) {
            $checkDn = $this->dn;
        }
        return $checkDn;
    }

    /**
     * Returns the correct user identifier to use, based on the ldap server type
     */
    function getUserIdentifier() {
        if ($this->serverType == "activedirectory") {
            return $this->attr_sambalogin;
        } else {
            return $this->attr_login;
        }
    }

   /**
		* \brief UserAccountControl Flgs to more human understandable form...
		*
		*/
    function parseUACF($uacf) {
    //All flags array
    $flags = array( "TRUSTED_TO_AUTH_FOR_DELEGATION"  =>    16777216,
                    "PASSWORD_EXPIRED"                =>    8388608,
                    "DONT_REQ_PREAUTH"                =>    4194304,
                    "USE_DES_KEY_ONLY"                =>    2097152,
                    "NOT_DELEGATED"                   =>    1048576,
                    "TRUSTED_FOR_DELEGATION"          =>    524288,
                    "SMARTCARD_REQUIRED"              =>    262144,
                    "MNS_LOGON_ACCOUNT"               =>    131072,
                    "DONT_EXPIRE_PASSWORD"            =>    65536,
                    "SERVER_TRUST_ACCOUNT"            =>    8192,
                    "WORKSTATION_TRUST_ACCOUNT"       =>    4096,
                    "INTERDOMAIN_TRUST_ACCOUNT"       =>    2048,
                    "NORMAL_ACCOUNT"                  =>    512,
                    "TEMP_DUPLICATE_ACCOUNT"          =>    256,
                    "ENCRYPTED_TEXT_PWD_ALLOWED"      =>    128,
                    "PASSWD_CANT_CHANGE"              =>    64,
                    "PASSWD_NOTREQD"                  =>    32,
                    "LOCKOUT"                         =>    16,
                    "HOMEDIR_REQUIRED"                =>    8,
                    "ACCOUNTDISABLE"                  =>    2,
                    "SCRIPT"                          =>    1);

    //Parse flags to text
    $retval = array();
    while (list($flag, $val) = each($flags)) {
        if ($uacf >= $val) {
            $uacf -= $val;
            $retval[$val] = $flag;
        }
    }

    //Return human friendly flags
    return($retval);
  }

   /**
		* \brief SamAccountType value to text
		*
		*/
		function parseSAT($samtype) {
    $stypes = array(    805306368    =>    "NORMAL_ACCOUNT",
                        805306369    =>    "WORKSTATION_TRUST",
                        805306370    =>    "INTERDOMAIN_TRUST",
                        268435456    =>    "SECURITY_GLOBAL_GROUP",
                        268435457    =>    "DISTRIBUTION_GROUP",
                        536870912    =>    "SECURITY_LOCAL_GROUP",
                        536870913    =>    "DISTRIBUTION_LOCAL_GROUP");

    $retval = "";
    while (list($sat, $val) = each($stypes)) {
        if ($samtype == $sat) {
            $retval = $val;
            break;
        }
    }
    if (empty($retval)) $retval = "UNKNOWN_TYPE_" . $samtype;

    return($retval);
  }

  /**
		* \Parse GroupType value to text
		*
		*/
		function parseGT($grouptype) {
    $gtypes = array(    -2147483643    =>    "SECURITY_BUILTIN_LOCAL_GROUP",
                        -2147483644    =>    "SECURITY_DOMAIN_LOCAL_GROUP",
                        -2147483646    =>    "SECURITY_GLOBAL_GROUP",
                        2              =>    "DISTRIBUTION_GLOBAL_GROUP",
                        4              =>    "DISTRIBUTION_DOMAIN_LOCAL_GROUP",
                        8              =>    "DISTRIBUTION_UNIVERSAL_GROUP");

    $retval = "";
    while (list($gt, $val) = each($gtypes)) {
        if ($grouptype == $gt) {
            $retval = $val;
            break;
        }
    }
    if (empty($retval)) $retval = "UNKNOWN_TYPE_" . $grouptype;

    return($retval);
  }


	/*
	*	\brief		Encode in UTF8 or not
	*	\param		string		String to decode
	*	\return		string		String decoded
	*/
	function ldap_utf8_encode($string)
	{
		if ($this->serverType != "activedirectory")	return utf8_encode($string);
		else return($string);
	}
	

	/*
	*	\brief		Decode in UTF8 or not
	*	\param		string		String to decode
	*	\return		string		String decoded
	*/
	function ldap_utf8_decode($string)
	{
		if ($this->serverType != "activedirectory")	return utf8_decode($string);
		else return($string);
	}	

}


?>