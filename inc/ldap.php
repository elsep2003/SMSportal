<?php
/**
* AD connection class
* This class is used to connect to Active Directory LDAP.
*
* @author Christopher Rouse
* @date Created April 2016 
* @license http://www.php.net/license/3_01.txt PHP License 3.01

Notes:

*/



class Ldap
{
    /**
    * Public variables
    *
    * None
    *
    *Private variables
    * @var $ldapconn - the connection handle used to initiate ldap searches.
    * @var $ldapbind - A variable that hold the connection bindings.
    * @var $baseDSN - a base DN used for general Searches.
    * @var $ADFieldsFull - A array with all fields that will be returned from a LDAP query this covers a wide range of fields and is used when speed doesn't matter (users).
    * @var $ADFieldsQuick - A array with all fields that will be returned from a LDAP query this covers a limited range fields and is used when speed is important (users).
    * @var $ADGroupFields - A array with all fields that will be returned from a LDAP query this covers a limited range fields and is used when speed is important or no details required (groups).
    */
    private $ldapconn;
    private $ldapbind;
    private $baseDSN;
    private $ADFieldsFull = array("objectsid","department","displayName","distinguishedName","givenName","sn","mail","physicalDeliveryOfficeName","title","sAMAccountName");
    private $ADFieldsQuick = array("objectsid","displayName","title","sAMAccountName");
    private $ADphoto = array("objectsid","sAMAccountName","displayName","thumbnailPhoto");
	private $ADGroupFields = array("member");
    
    
    /**
    * This constructor creates a connection to the LDAP Server and binds the connection to the $ldapconn handle.
    *
    * @param $server - a String that holds hostname or IP address of the LDAP Server to be connected to.
    * @param $login - An Array that hold the connection details for the LDAP server. 
    *        The array looks like $login = array("UID" =>"uname", "PWD" =>"pwd");
    * @param $baseDSN - This is the base DN used to use as a search DN
    *        The base DN could look like "DC=northumbria-healthcare,DC=nhs,DC=uk"
    *
    * @return - This has no returns
    */
    
    
    public function __construct($server,$login,$baseDSN,$pwdcheck=false)
    {
        $this->ldapconn = ldap_connect($server) or die("Could not connect to LDAP server.");
        if ($this->ldapconn) 
        {
            // binding to ldap server
            ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);
            $this->ldapbind = ldap_bind($this->ldapconn, $login['UID'], $login['PWD']);
        }
        if (!$this->ldapbind) 
        {
        ldap_get_option($this->ldapconn, 0x0032, $extended_error);
			if ($pwdcheck==false)
			{
			die("Error Binding to LDAP: $extended_error");
			}
			else
			{
				$this->null;
			}
        }
        $this->baseDSN = $baseDSN;
    }
    /**
    * This public function gets the $baseDSN for general quires.
    *
    * @param - none
    *
    * @return - This has no returns the base dn
    */
    public function getBaseLdap()
    {
        return $this->$baseDSN;
    }
    
     /**
    * This public function sets the $baseDSN for general quires.
    *
    * @param $baseDSN - a String that will be used for the source DN.
    *
    * @return - This has no returns
    */
    
    
    public function setBaseLdap($baseDSN)
    {
        $this->$baseDSN = $baseDSN;
    }
    
    /**
    * This public function returns and array of users based on a sAMAccountName name.
    *
    * @param $Username - a String that will be used querying LDAP base on sAMAccountName, * will cards can be used.
    * @param $mode - Optional - int this can be used to pick a full set of user fields or limited.  The default is 1 which is a limited selection.
    *
    * @return - A nested array with user details, or an array with a 'count' of 0 if no results are found.
    */
    
    public function getUserSam($Username,$mode = 1)
    {
        $filter = "(&(objectCategory=person)(objectClass=user)(sAMAccountName=".$Username."))";
        if($mode == 1)
        {
            
            $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsQuick);
        }
        else
        {
            $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsFull);
        }
        $info = ldap_get_entries($this->ldapconn, $sr);
        if  ($info['count']>=1)
        {
            
            for ($tu=0;$tu<$info['count'];$tu++)
            {                
                $info[$tu]['objectsid'][0] = $this->fromLdap($info[$tu]['objectsid'][0]);
            }
                
        }
        return $info;
    }
    
	
	/**
    * This public function returns and array of users based on a sAMAccountName name.
    *
    * @param $Username - a String that will be used querying LDAP base on sAMAccountName, * will cards can be used.
    * @param $mode - Optional - int this can be used to pick a full set of user fields or limited.  The default is 1 which is a limited selection.
    *
    * @return - A nested array with user details, or an array with a 'count' of 0 if no results are found.
    */
    
    public function getUserSamlimited($Username,$limit=5)
    {
        $filter = "(&(objectCategory=person)(objectClass=user)(sAMAccountName=".$Username."))";

            $sr=@ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsQuick,null,$limit,0,0);
        
        $info = ldap_get_entries($this->ldapconn, $sr);
        if  ($info['count']>=1)
        {
            
            for ($tu=0;$tu<$info['count'];$tu++)
            {                
                $info[$tu]['objectsid'][0] = $this->fromLdap($info[$tu]['objectsid'][0]);
            }
                
        }
        return $info;
    }
	
	
	/**
    * This public function returns and array of users based on a Email name.
    *
    * @param $Email - a String that will be used querying LDAP base on sAMAccountName, * will cards can be used.
    * @param $mode - Optional - int this can be used to pick a full set of user fields or limited.  The default is 1 which is a limited selection.
    *
    * @return - A nested array with user details, or an array with a 'count' of 0 if no results are found.
    */
	 public function getUserEmail($Email,$mode = 1)
    {
        $filter = "(&(objectCategory=person)(objectClass=user)(mail=".$Email."))";
        if($mode == 1)
        {
            
            $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsQuick);
        }
        else
        {
            $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsFull);
        }
        $info = ldap_get_entries($this->ldapconn, $sr);
        if  ($info['count']>=1)
        {
            
            for ($tu=0;$tu<$info['count'];$tu++)
            {                
                $info[$tu]['objectsid'][0] = $this->fromLdap($info[$tu]['objectsid'][0]);
            }
                
        }
        return $info;
    }
	
    /**
    * This public function returns and array of users based on an objects DN.
    *
    * @param $LdapDN - a String that will be used querying LDAP base on full DN, the class baseDSN is ignored in this function.
    * @param $mode - Optional - int this can be used to pick a full set of user fields or limited.  The default is 1 which is a limited selection.
    *
    * @return - A nested array with user details, or an array with a 'count' of 0 if no results are found.
    */
    public function getUserDN($LdapDN,$mode = 1)
    {
        $filter = "(&(objectCategory=person)(objectClass=user))";
       
        if($mode == 1)
        {
            
            $sr=ldap_search($this->ldapconn,  $LdapDN, $filter, $this->ADFieldsQuick);
        }
        else
        {
            $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsFull);
        }
        $info = ldap_get_entries($this->ldapconn, $sr);
        
        if  ($info['count']>=1)
        {
            
            for ($tu=0;$tu<$info['count'];$tu++)
            {                
                $info[$tu]['objectsid'][0] = $this->fromLdap($info[$tu]['objectsid'][0]);
            }
                
        }

        return $info;
    }
    
    /**
    * This public function returns an array with user details for a specifies SID.
    *
    * @param $SID - a String that will be used querying LDAP base on a user SID. The SID must be in S-1-5-21-1004336348-1177238915-682003330-512 notation. 
    * @param $mode - Optional - int this can be used to pick a full set of user fields or limited.  The default is 1 which is a limited selection.
    *
    * @return - A nested array with user details, or an array with a 'count' of 0 if no results are found.
    */
       public function getUserSID($SID,$mode = 1)
    {
        
        $filter = "(&(objectCategory=person)(objectClass=user)(objectsid=".$this->toLdap($SID)."))";
        
        if($mode == 1)
        {
            
            $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsQuick);
        }
        else
        {
            $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsFull);
        }
        $info = ldap_get_entries($this->ldapconn, $sr);
        if  ($info['count']>=1)
        {
            
            for ($tu=0;$tu<$info['count'];$tu++)
            {                
                $info[$tu]['objectsid'][0] = $this->fromLdap($info[$tu]['objectsid'][0]);
            }
                
        }
        return $info;
    }
    
	
	/**
    * This public function returns an array with the user SID,Samacount and a thumbnail image for a specifies SID.
    *
    * @param $SID - a String that will be used querying LDAP base on a user SID. The SID must be in S-1-5-21-1004336348-1177238915-682003330-512 notation. 
   
    *
    * @return - A nested array with user sid,samacount and a thumbnail image, or an array with a 'count' of 0 if no results are found.
    */
    public function getUserPhotoSID($SID)
    {
       
        $filter = "(&(objectCategory=person)(objectClass=user)(objectsid=".$this->toLdap($SID)."))";
        
        $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADphoto);
        
        $info = ldap_get_entries($this->ldapconn, $sr);
		
		if  ($info['count']>=1)
        {
           
            for ($tu=0;$tu<$info['count'];$tu++)
            {                
                $info[$tu]['objectsid'][0] = $this->fromLdap($info[$tu]['objectsid'][0]);
            }
                
        }
        
		return $info;
    }
    
		/**
    * This public function removes the image of a user based on its SID.
    *
    * @param $SID - a String that will be used querying LDAP base on a user SID. The SID must be in S-1-5-21-1004336348-1177238915-682003330-512 notation.
    *
    * @return - True = success, False = Failure
	*/
	
	public function removeUserPhotoSID($SID)
    {
		
		$file['thumbnailphoto'] = array();
		$filter = "(&(objectCategory=person)(objectClass=user)(objectsid=".$this->toLdap($SID)."))";
            
        $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsFull);
        
        $info = ldap_get_entries($this->ldapconn, $sr);
        if  ($info['count']>=1)
        {
				 
		 $dn =  $info[0]['dn'];               
             
			$sr=ldap_read($this->ldapconn, $dn, $filter, array("thumbnailphoto"));
			$entry = ldap_get_entries($this->ldapconn, $sr);
		
			if (isset($entry[0]['thumbnailphoto']))
			{
			
				if (ldap_mod_del($this->ldapconn, $dn, $file)==true)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return true;
			}
        }
		else
		{
			return false;
		}
    }
	
	/**
    * This public function sets the image of a user based on its SID.
    *
    * @param $SID - a String that will be used querying LDAP base on a user SID. The SID must be in S-1-5-21-1004336348-1177238915-682003330-512 notation.
    * @param $imagepath - The image to upload, this must be a JPG of no less than 100 X 100 Pixels
    *
    * @return - True = success, False = Failure
	*/
	
	public function setUserPhotoSID($SID,$imagepath)
    {
        
		
		$file['thumbnailphoto'] = array();
		file_get_contents($imagepath);
		$filter = "(&(objectCategory=person)(objectClass=user)(objectsid=".$this->toLdap($SID)."))";
            
        $sr=ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsFull);
        
        $info = ldap_get_entries($this->ldapconn, $sr);
		 
		if  ($info['count']>=1)
        {
				
		$dn =  $info[0]['dn'];               
      
		
		   
			$this->removeUserPhotoSID($SID);
			
				$file['thumbnailphoto'] = file_get_contents($imagepath);
				
				if (ldap_mod_add($this->ldapconn, $dn, $file)==true)
				{
					return true;
				}
				else
				{
					return false;
				}
			
			
			
			 
        }
        else
		{
			return false;
		}
    }
	
    /**
    * This public function returns and array of users based on an displayName.
    *
    * @param $name - a String that will be used querying LDAP base on Display name. * will cards can be used.
    * @param $limit - Optional - int this can be used to limit the number of results.  The default value is 5 records.
    * @param $mode - Optional - int this can be used to pick a full set of user fields or limited.  The default is 1 which is a limited selection.
    *
    * @return - A nested array with user details, or an array with a 'count' of 0 if no results are found.
    */
    
    public function getUserSearchDisplayName($name,$limit=5,$mode = 1)
    {
         $filter = "(&(objectCategory=person)(objectClass=user)(displayName=".$name."))";
        if($mode == 1)
        {
            
            $sr=@ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsQuick,null,$limit,0,0);
        }
        else
        {
            $sr=@ldap_search($this->ldapconn,  $this->baseDSN, $filter, $this->ADFieldsFull,null,$limit,0,0);
        }
        $info = ldap_get_entries($this->ldapconn, $sr);
         
        if  ($info['count']>=1)
        {
            
            for ($tu=0;$tu<$info['count'];$tu++)
            {                
                $info[$tu]['objectsid'][0] = $this->fromLdap($info[$tu]['objectsid'][0]);
            }
                
        }
        
        return $info;
    }
    
    
    /**
    * This public function returns and array of users based on there group membership.
    *
    * @param $GroupLdap - a String that will be used querying a group base on full DN, the class baseDSN is ignored in this function.
    *
    * @return - A nested array with user details, or an array with a 'count' of 0 if no results are found.
    */
    
    public function getUsersinGroup($GroupLdap)
    {

        $filter = "(objectClass=group)";
        

        
        $sr=ldap_search($this->ldapconn,  $GroupLdap, $filter, $this->ADGroupFields);
       
        $info = ldap_get_entries($this->ldapconn, $sr);
        
       
        if  ($info[0]['count']>=1)
        {
            $members = $info[0]['member'];
        }
        else
        {
            $members['count']=0;
        }
        return $members;

    }
    
    /**
    * This public function tests to see if a user in in a group.
    *
    * @param $GroupLdap - a String that will be used querying a group base on full DN, the class baseDSN is ignored in this function.
    * @param $userID - This is a string representing the user in one of there ways, DN, sAMAccountName or the SID.  The Type must be set in IDmode.
    * @param $IDmode - Optional ish - The Default option is search with 0 - DN.  IDmode should be set based on $userID content 0 = DN, 1 = sAMAccountName and 2 = SID.  
    * @return - Bool - if the user is found then 1 is retuned 0 is returned for any other item.
    */
    
    public function getUserinGroup($GroupLdap,$userID,$IDmode=0)
    {

        $filter = "(objectClass=group)";
        switch($IDmode)
        {
        case 0: // DN
        $user = $userID;
        break;
        
        case 1: // SAM 
        $ADreturn = $this->getUserSam($userID,1);
        $user = $ADreturn[0]['dn'];
        break;
        
        case 2: // SID
        $ADreturn  = $this->getUserSID($userID,1);
        $user = $ADreturn[0]['dn'];
        break;
        }

        
        $sr=ldap_search($this->ldapconn,  $GroupLdap, $filter, $this->ADGroupFields);
       
        $info = ldap_get_entries($this->ldapconn, $sr);
       
        if  ($info[0]['count']>=1)
        {
            $members = $info[0]['member'];
            foreach($members as $dn)
            {
                if($dn==$user)
                {
                return 1;
                }
            }
            
        }
        return 0;

    }

    /**
    * This private function is used to convert user readable SID and converts it to the Octal used by Active directory.
    *
    * @param  $sid - a String that holding a SID in S-1-5-21-1004336348-1177238915-682003330-512 notation.
    *
    * @return - String - This returns an octal version of the SID that can be used by AD. 
    */
    
     private function toLdap($sid)
    {
        $sid = ltrim($sid, 'S-');
        $sid = explode('-', $sid);
 
        $revLevel = array_shift($sid);
        $authIdent = array_shift($sid);
        $id = array_shift($sid);
 
        $sidHex = str_pad(dechex($revLevel), 2, '0', STR_PAD_LEFT);
        $sidHex .= str_pad(dechex($authIdent), 2, '0', STR_PAD_LEFT);
        $sidHex .= str_pad(dechex($authIdent), 12, '0', STR_PAD_LEFT);
        $sidHex .= str_pad(dechex($id), 8, '0', STR_PAD_RIGHT);
 
        foreach ($sid as $subAuth) {
            // little endian, so reverse the hex order.
            $sidHex .= implode('', array_reverse(
                // After going from dec to hex, pad it and split it into hex chunks so it can be reversed.
                str_split(str_pad(dechex($subAuth), 8, '0', STR_PAD_LEFT), 2))
            );
        }
        // All hex parts must have a leading backslash for the search.
        $sidHex = str_split($sidHex, '2');
 
        return '\\'.implode('\\', $sidHex);
    }
    
    /**
    * This private function is used to converts it to the Octal SID used by Active directory into a user readable SID.
    *
    * @param  $sid - String - An octal version of the SID that can be used by AD.
    *
    * @return - String - a String that holding a SID in S-1-5-21-1004336348-1177238915-682003330-512 notation.
    */
    
      private function fromLdap($sid)
    {
        // How to unpack all of this in one statement to avoid resorting to hexdec? Is it even possible?
        $sidHex = unpack('H*hex', $sid)['hex'];
        $subAuths = unpack('H2/H2/n/N/V*', $sid);
 
        $revLevel = hexdec(substr($sidHex, 0, 2));
        $authIdent = hexdec(substr($sidHex, 4, 12));
 
        return 'S-'.$revLevel.'-'.$authIdent.'-'.implode('-', $subAuths);
    }
    
    /**
    * This public function closes the LDAP connection
    *
    * @param  - none
    *
    * @return - none
    */
    
     public function close()
    {
       ldap_close($this->ldapconn);  
    }
 
}
?>