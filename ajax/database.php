<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

require_once('../inc/connstring.php');
require_once('../inc/database.php');
require_once('../inc/functions.php');
require_once('../inc/ldap.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');
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
        case "save": // start save update
        
            

            // AttendeeID 
            //AttendeeName
            //MobileNumber
           // ApptTime
           // LocationID
           //appointmentID
            $AttendeeID = null;
            $AttendeeName = null;
            $MobileNumber = null;
            $ApptTime = null;
            $LocationID = null;
            $appointmentID = null;
         
            if(isset($_REQUEST['AttendeeID']))
            {
                if (strlen($_REQUEST['AttendeeID'])>0)
                {
                 $AttendeeID=htmlspecialchars($_REQUEST['AttendeeID']);
                }
            }
            
            if(isset($_REQUEST['AttendeeName']))
            {
                if (strlen($_REQUEST['AttendeeName'])>0)
                {
                    $AttendeeName=htmlspecialchars($_REQUEST['AttendeeName']);
                }
                else
                {
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "VAL";
                    $JSONarray['mgs']="No Attendee Name";	
                }
            }
            else
            {
                $JSONarray['status'] = false;
                $JSONarray['etype'] = "VAL";
                $JSONarray['mgs']="No Attendee Name";		
            }    
            
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
                    $JSONarray['mgs']="No Mobile Number";	
                }
            }
            else
            {
                $JSONarray['status'] = false;
                $JSONarray['etype'] = "VAL";
                $JSONarray['mgs']="No Mobile number";		
            }    
            
            if(isset($_REQUEST['ApptTime']))
            {
                if (strlen($_REQUEST['ApptTime'])>0)
                {
                    $ApptTime=htmlspecialchars($_REQUEST['ApptTime']);
                }
                else
                {
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "VAL";
                    $JSONarray['mgs']="No Appointmnet Time";	
                }
            }
            else
            {
                $JSONarray['status'] = false;
                $JSONarray['etype'] = "VAL";
                $JSONarray['mgs']="No Appointmnet Time";		
            }
            
            if(isset($_REQUEST['LocationID']))
            {
                if (strlen($_REQUEST['LocationID'])>0)
                {
                    $LocationID=htmlspecialchars($_REQUEST['LocationID']);
                }
            }
            if(isset($_REQUEST['appointmentID']))
            {
                if (strlen($_REQUEST['appointmentID'])>0)
                {
                    $appointmentID=htmlspecialchars($_REQUEST['appointmentID']);
                }
            }

           
            
            if(isset($AttendeeID))
            {
                $items = array("sss","$AttendeeName","$MobileNumber","$AttendeeID");
                $sql = "UPDATE smsp_patient SET Name = ?,MobileNumber = ? WHERE patientID = ?;";
                $result = $MYSQL->Query($sql,$items);
                if ($MYSQL->sqlerr<>0)
                {
                $JSONarray['etype']="sql";
                $JSONarray['status'] = false;
                switch($MYSQL->sqlerr)
                    {
                    case 1062:
                            $JSONarray['mgs']="$MobileNumber is already in use.";
                    break;
                    default:
                        
                        $JSONarray['mgs']= "An error has occurred in the database. error number: ".$MYSQL->sqlerr;
                        
                    }
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "SQL";					
                }
            
            }
            else
            {
                $items = array("ss","$AttendeeName","$MobileNumber");
                $sql = "INSERT INTO smsp_patient (Name,MobileNumber) VALUES (?,?)";
                $result = $MYSQL->Query($sql,$items);
                $AttendeeID = $MYSQL->insert_id;
                //1062
                if ($MYSQL->sqlerr<>0)
                {
                $JSONarray['etype']="sql";
                $JSONarray['status'] = false;
                switch($MYSQL->sqlerr)
                    {
                    case 1062:
                            $JSONarray['mgs']="$MobileNumber is already in use.";
                    break;
                    default:
                        
                        $JSONarray['mgs']= "An error has occurred in the database. error number: ".$MYSQL->sqlerr;
                        
                    }
                    $JSONarray['status'] = false;
                    $JSONarray['etype'] = "SQL";
                    					
                }

            }

            if (isset($JSONarray['status']) && $AttendeeID !== null)
            {
                $JSONarray['status'] = false;
                $JSONarray['etype'] = "SQL";
            }
            else
            {
                $Time = DateTime::createFromFormat('d/m/Y H:i',$ApptTime)->format('Y-m-d H:i');	
                $items = array("isii","$appointmentID","$Time","$LocationID","$AttendeeID");
                $sql = "REPLACE INTO smsp_appointment (appointmentID,`time`, locationID, PaientID) VALUES (?,?,?,?);";
                $result = $MYSQL->Query($sql,$items);
                if ($MYSQL->sqlerr<>0)
                {
                    $JSONarray['etype']="SQL";
                    $JSONarray['mgs']= "Could not create record";
                    $JSONarray['status'] = false;
                }
                else
                {
                    $JSONarray['mgs']= "Record Saved";
                    $JSONarray['status'] = true;
                }
            }

        break; // end save update
        
        case "delete": // Start delete

            if(isset($_REQUEST['appointmentID']))
           {
               if (strlen($_REQUEST['appointmentID'])>0)
               {
                   $appointmentID=htmlspecialchars($_REQUEST['appointmentID']);
               }
               else
               {
                   $JSONarray['etype']="VAL";
                   $JSONarray['mgs']= "No appointmentID";
                   $JSONarray['status'] = false;
               }
            }
            else
            {
                $JSONarray['etype']="VAL";
                $JSONarray['mgs']= "No appointmentID";
                $JSONarray['status'] = false;
            }

            if (!isset($JSONarray['status']))
            {
                //DELETE FROM smsp_appointment WHERE (appointmentID = ?);

                $items = array("i","$appointmentID");
                $sql = "DELETE FROM smsp_appointment WHERE (appointmentID = ?);";
                $result = $MYSQL->Query($sql,$items);
                if ($MYSQL->sqlerr<>0)
                {
                    $JSONarray['etype']="SQL";
                    $JSONarray['mgs']= "Record deleted record";
                    $JSONarray['status'] = false;
                }
                else
                {
                    $JSONarray['mgs']= "Record Deleted";
                    $JSONarray['status'] = true;
                }

            }


        break;   // end delete

        case "attendees": // Start attendees list 
            
            $DateTime = new DateTime();
            $DateTime->modify('-2 hours');
            $ApptFrom =  $DateTime->format("Y-m-d H:i:s");
            
            $DateTime = new DateTime();
            $DateTime->modify('+2 hours');
            $ApptTo =  $DateTime->format("Y-m-d H:i:s");
            


            $locationID = null;

            if(isset($_REQUEST['ApptTime']))
            {
                if (strlen($_REQUEST['ApptTime'])>0)
                {
                    $ApptDate=htmlspecialchars($_REQUEST['ApptTime']);
                    $DateTime = DateTime::createFromFormat('Y-m-d H:i',$ApptDate)->modify('-2 hours');
                    $ApptFrom =  $DateTime->format("Y-m-d H:i:s");

                    $DateTime = DateTime::createFromFormat('Y-m-d H:i',$ApptDate)->modify('+2 hours');
                    $ApptTo =  $DateTime->format("Y-m-d H:i:s");

                }
            }
           
          

           if(isset($_REQUEST['LocationID']))
           {
               if (strlen($_REQUEST['LocationID'])>0)
               {
                   $LocationID=htmlspecialchars($_REQUEST['LocationID']);
               }
               else
               {
                   $JSONarray['etype']="VAL";
                   $JSONarray['mgs']= "No Location ID";
                   $JSONarray['status'] = false;
               }
            }
            else
            {
                $JSONarray['etype']="VAL";
                $JSONarray['mgs']= "No Location ID";
                $JSONarray['status'] = false;
            }

            if (!isset($JSONarray['status']))
            {
                $emptyrow = array("appointmentID"=>null,"AppointmentTime"=>"","locationID"=>null,"PaientID"=>null,"MobileNumber"=>null,"stateID"=>null,"Name"=>"No Attendees","Status"=>null,"SentDate"=>null);


                $items = array("sss","$LocationID","$ApptFrom","$ApptTo");
                    $result = $MYSQL->Query("SELECT appointmentID,DATE_FORMAT(AppointmentTime, \"%d/%m/%Y %H:%i\") as AppointmentTime ,locationID,PaientID,MobileNumber,stateID,Name,Status,DATE_FORMAT(SentDate, \"%d/%m/%Y %H:%i\") as SentDate,DATE_FORMAT(DeliverdDate, \"%d/%m/%Y %H:%i\") as DeliverdDate FROM view_appointment where locationID = ? and (AppointmentTime BETWEEN ? AND ?) order by AppointmentTime;",$items);
                
                    if ($result->num_rows>0)
                    {
                        while ($myrow = $result->fetch_assoc())
                        {
                
                            //appointmentID 					
                            //AppointmentTime
                            //locationID
                            //PaientID
                            //stateID
                            //Name
                            //Status
                            //SentDate
                            //MobileNumber
                                                
                            $appointmentID=$myrow['appointmentID'];
                            $AppointmentTime = $myrow['AppointmentTime'];
                            $locationID = $myrow['locationID'];
                            $PaientID = $myrow['PaientID'];
                            $MobileNumber = $myrow['MobileNumber'];
                            $stateID = $myrow['stateID'];
                            $Name = $myrow['Name'];
                            $Status = $myrow['Status'];
                            $SentDate = $myrow['SentDate'];
                            $DeliverdDate = $myrow['DeliverdDate'];

                                
                            $attendeerow= array("appointmentID"=>$appointmentID,"AppointmentTime"=>$AppointmentTime,"locationID"=>$locationID,"PaientID"=>$PaientID,"MobileNumber"=>$MobileNumber,"stateID"=>$stateID,"Name"=>$Name,"Status"=>$Status,"SentDate"=>$SentDate,"DeliverdDate"=>$DeliverdDate);
                            $attendeesarray[] = $attendeerow;
                        }
                        $result->close();
                    }
                    else
                    {
                        
                        $attendeesarray[] = $emptyrow;
                    }
                    $JSONarray['attendees'] =  $attendeesarray;
                    $JSONarray['status'] = true;
            
        }

        break; // end attendees list   
        
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