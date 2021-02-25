<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

require_once('../inc/connstring.php');
require_once('../inc/database.php');
require_once('../inc/functions.php');
require_once('../inc/ldap.php');
require_once('../inc/autoload.php');
require_once('../inc/esendexsfg.php');


//error_reporting(E_ALL);
//ini_set('display_errors', '1');
$MYSQL = new Database();
$MYSQL->MyConnect($MYserverName, $MYconnectionInfo);
$ladp = new Ldap($ldapserver,$ldaplogin,$baseDSN); 
$InGroup = false; 
$user = get_user();
$JSONarray=array();

if (isAuthorised($ladp,$GroupsLdap,$user) === true)
{
    $func="";
    if(isset($_REQUEST['func']))
	{
		$func=htmlspecialchars($_REQUEST['func']);
	}
    switch ($func) {

        case "send": // start sendSMS
        
          
            if(isset($_REQUEST['MobileNumber']))
            {
                if (strlen($_REQUEST['MobileNumber'])>0)
                {
                    $MobileNumber=htmlspecialchars($_REQUEST['MobileNumber']);
                }
                else
                {
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "VAL";
                    $JSONarray['mgs']="No Phone Number";	
                }
            }
            else
            {
                $JSONarray['status'] = false;
                $JSONarray['etype'] = "VAL";
                $JSONarray['mgs']="No Phone Number";		
            }    
          
            if(isset($_REQUEST['MobileMsg']))
            {
                if (strlen($_REQUEST['MobileMsg'])>0)
                {
                    $MobileMsg=htmlspecialchars($_REQUEST['MobileMsg']);
                }
                else
                {
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "VAL";
                    $JSONarray['mgs']="No Message";	
                }
            }
            else
            {
                $JSONarray['status'] = false;
                $JSONarray['etype'] = "VAL";
                $JSONarray['mgs']="No Message";			
            }  

            if(isset($_REQUEST['appointmentID']))
            {
                if (strlen($_REQUEST['appointmentID'])>0)
                {
                    $appointmentID=htmlspecialchars($_REQUEST['appointmentID']);
                }
                else
                {
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "VAL";
                    $JSONarray['mgs']="No appointmentID";	
                }
            }
            else
            {
                $JSONarray['status'] = false;
                $JSONarray['etype'] = "VAL";
                $JSONarray['mgs']="No appointmentID";			
            }
            if (!isset($JSONarray['status']))
            {
                

                $service = new \Esendex\DispatchService($EsendexAuth);
                $message = new \Esendex\Model\DispatchMessage(
                    $EsendexFrom, // Send from
                    $MobileNumber, // Send to any valid number
                    $MobileMsg,
                    \Esendex\Model\Message::SmsType
                );
                try {
                    $result = $service->send($message);
                } catch (Exception $errcode) {
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "API";
                    if ($errcode->getCode()=="402")
                    {
                        $JSONarray['mgs'] = "No credit";
                    }
                    else
                    {
                        $JSONarray['mgs']=$errcode->getMessage();
                    }
                }
                if (!isset($errcode))
                {
                    $EsendexID =  $result->id();
                    $sql = "INSERT INTO smsp_state (messageID, `messagee`, `Status`, SenderSam,SentDate) VALUES (?, ?, ?, ?,now())";
                    $state = "Sending";
                    $items = array("ssss","$EsendexID","$MobileMsg","$state","$user");
                    $result = $MYSQL->Query($sql,$items);
                    $stateID = $MYSQL->insert_id;
                    if ($MYSQL->sqlerr<>0)
                    {
                    $JSONarray['etype']="sql";
                    $JSONarray['status'] = false;
                    }
                    else
                    {
                        $sql = "UPDATE smsp_appointment SET stateID = ? WHERE appointmentID = ?;";
                        $items = array("ss","$stateID","$appointmentID");
                        $result = $MYSQL->Query($sql,$items);
                        $JSONarray['status'] = true;
                        $JSONarray['EsendexID'] = $EsendexID;
                        $JSONarray['Esendexstate'] = $state; 
                    }
                }

            }
         
            
            
           
        
        break;  // end sendSMS
        
        case "checkSMSstate": // start checkSMSstate
        
            if(isset($_REQUEST['EsendexID']))
            {
                if (strlen($_REQUEST['EsendexID'])>0)
                {
                    $EsendexID=htmlspecialchars($_REQUEST['EsendexID']);
                }
                else
                {
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "VAL";
                    $JSONarray['mgs']="No EsendexID";	
                }
            }
            else
            {
                $JSONarray['status'] = false;
                $JSONarray['etype'] = "VAL";
                $JSONarray['mgs']="No EsendexID";			
            }
            if (!isset($JSONarray['status']))
            {
                $headerService = new \Esendex\MessageHeaderService($EsendexAuth);
                try {
                $message = $headerService->message($EsendexID);
                } catch (Exception $errcode) {
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "API";
                    {
                        $JSONarray['mgs']=$errcode->getMessage();
                    }
                }
                if (!isset($errcode))
                {
                    $SentAt = null;
                    $deliveredAt = null;
                    $Esendexstatus = $message->status();
                    if($Esendexstatus == "Sent")
                    {
                        $SentAt = $message->sentAt()->format('Y-m-d H:i:s');
                        $SentAtUi = $message->sentAt()->format('d/m/Y H:i:s');
                        $items = array("sss","$Esendexstatus","$SentAt","$EsendexID");
                        $sql = "UPDATE smsp_state SET `Status` = ?, SentDate = ? WHERE messageID = ?;";
                    }
                    elseif($Esendexstatus == "Delivered")
                    {
                        $SentAt = $message->sentAt()->format('Y-m-d H:i:s');
                        $deliveredAt = $message->deliveredAt()->format('Y-m-d H:i:s');
                        $SentAtUi = $message->sentAt()->format('Y-m-d H:i:s');
                        $deliveredAtUi = $message->deliveredAt()->format('d/m/Y H:i:s');
                        $items = array("ssss","$Esendexstatus","$SentAt","$deliveredAt","$EsendexID");
                      
                        $sql = "UPDATE smsp_state SET `Status` = ?, SentDate = ?, DeliverdDate= ? WHERE messageID = ?";
                    }
                    else
                    {
                        $items = array("ss","$Esendexstatus","$EsendexID");
                        $sql = "UPDATE smsp_state SET `Status` = ? WHERE messageID = ?;";
                    }
                    $result = $MYSQL->Query($sql,$items);
                    if ($MYSQL->sqlerr<>0)
                    {
                    
                    $JSONarray['etype']="sql";
                    $JSONarray['status'] = false;
                    }
                    else
                    {
                        $JSONarray['SentDate'] = $SentAtUi;
                        $JSONarray['DeliverdDate'] = $deliveredAtUi;
                        $JSONarray['Esendexstate'] = $Esendexstatus;
                        $JSONarray['status'] = true;
                    }
                }

            }
        break; // end checkSMSstate

        default: // catch all
            
        $JSONarray['etype']="Other";
        $JSONarray['status'] = false;

		break;
        
       

    }
    $json_concat = json_encode($JSONarray);
    echo $json_concat;      
}
else
{
    header("HTTP/1.1 403 Forbidden");
    echo "Access Denied\n";
    echo "Your user account doesn’t have access to this content\n"; 
}
?>