<?php
/**
* Miscellaneous functions
* Functions that can be used in projects
*
* @author Christopher Rouse
* @date Created April 2016 
* @license http://www.php.net/license/3_01.txt PHP License 3.01

Notes:


 /**
    * This public function will query the $_SERVER constant in PHP to get the user.
    *
    * Integrated Windows authentication enabled should be enabled in IIS to use this.
    * LoadModule authnz_sspi_module modules/mod_authnz_sspi.so is required for apache.
    *
    * @return - This returns the username from the client, or -1 if the username is not found.
    */ 
function get_user()
{
    if (array_key_exists("LOGON_USER",$_SERVER) && $_SERVER['LOGON_USER'] != ""){return substr(strrchr($_SERVER['LOGON_USER'], '\\'),1);}
    elseif (array_key_exists("AUTH_USER",$_SERVER) && $_SERVER['AUTH_USER'] != ""){return substr(strrchr($_SERVER['AUTH_USER'], '\\'),1);}
    elseif (array_key_exists("REMOTE_USER",$_SERVER) && $_SERVER['REMOTE_USER'] != ""){return substr(strrchr($_SERVER['REMOTE_USER'], '\\'),1);}
    elseif (array_key_exists("PHP_AUTH_USER",$_SERVER) && $_SERVER['PHP_AUTH_USER'] != ""){return substr(strrchr($_SERVER['PHP_AUTH_USER'], '\\'),1);}
    return false;
} 

function autoselect($MYSQL,$sql,$htmlid,$idcol,$capcol,$default,$selected,$addattrib="")
{
	echo "<select name=\"$htmlid\" id=\"$htmlid\" class=\"form-control\" ".$addattrib.">";
	echo "<option value=\"\">$default</option>";
	$items=array();
	$result = $MYSQL->Query($sql,$items);
	if ($result)
			{
				
				if ($result->num_rows>0)
				{
					while ($myrow = $result->fetch_assoc())
					{
						$id=$myrow[$idcol];
						$optioncaption=$myrow[$capcol];
							
						if (strlen($selected)<=1 && $id==$selected && $selected<>0)
						{
							
							echo "<option value=\"$id\" selected>$optioncaption</option>";
						}
						elseif(strlen($selected)>=2 && $id==$selected)
						{
							echo "<option value=\"$id\" selected>$optioncaption</option>";
						}
						else
						{
							echo "<option value=\"$id\">$optioncaption</option>";
						}
					
					}
				}
				/*else
				{
				echo "<option value=\"0\">No options</option>";
				}*/
			}	
	echo "</select>";
	 $result->close();
       
} 

/**
    * This public function will check if a user is in a AD group
    *
    * Integrated Windows authentication enabled should be enabled in IIS to use this.
    * LoadModule authnz_sspi_module modules/mod_authnz_sspi.so is required for apache.
	* @var $ladp  - LDAP Class
	* @var $GroupsLdap - Array of Groups DN's
	* @var $user - String of with name as SamAccountName
	* @return - This returns the True if in group and false if not.
    */ 

function isAuthorised($ladp,$GroupsLdap,$user)
{
	$InGroup = false;
	foreach ($GroupsLdap as $group)
	{
		if ($ladp->getUserinGroup($group,$user,1)=="1")
		{
			$InGroup = true;
		}
	} 

	return $InGroup;
}

function optionNo ($from,$to)
{
	for ($iNum=$from;$iNum<=$to;$iNum++ )
	{
		$prenum = sprintf('%02d', $iNum);
		echo "<option value=\"$prenum\">$prenum</option>";
	}
}
?>