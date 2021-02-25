<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

require_once('../inc/connstring.php');
require_once('../inc/database.php');
require_once('../inc/functions.php');
require_once('../inc/ldap.php');
$MYSQL = new Database();
$MYSQL->MyConnect($MYserverName, $MYconnectionInfo);
$ladp = new Ldap($ldapserver,$ldaplogin,$baseDSN); 
$InGroup = false; 
$user = get_user();


if (isAuthorised($ladp,$GroupsLdap,$user) === true)
{
    $emptyrow = array("patientID"=>null,"name"=>"Nothing found", "MobileNumber"=>"No Number");
    if(isset($_REQUEST['name']))
	{
        $name = htmlspecialchars($_REQUEST['name']);
        $items = array("s","$name%");
            $result = $MYSQL->Query("SELECT patientID,Name,MobileNumber FROM smsp_patient where name like ?;",$items);
        
            if ($result->num_rows>0)
            {
                while ($myrow = $result->fetch_assoc())
                {
        
                    $patientID=$myrow['patientID'];
                    $Name=$myrow['Name'];
                    $MobileNumber=$myrow['MobileNumber'];
                    $contactrow= array("patientID"=>$patientID,"name"=>$Name, "MobileNumber"=>$MobileNumber);
                    $contactsarray['contacts'][] = $contactrow;
                }
                $result->close();
            }
            else
            {
                
                $contactsarray['contacts'][] = $emptyrow;
            }
    }
    else
    {
        $contactsarray['contacts'][] = $emptyrow;
    }
    $json_contact = json_encode($contactsarray);
    echo $json_contact;    
}
else
{
    header("HTTP/1.1 403 Forbidden");
    echo "Access Denied\n";
    echo "Your user account doesn’t have access to this content\n"; 
   
}
?>