<?php
require_once('./inc/connstring.php');
require_once('./inc/database.php');
require_once('./inc/functions.php');
require_once('./inc/ldap.php');

$MYSQL = new Database();
$MYSQL->MyConnect($MYserverName, $MYconnectionInfo);

require_once('header.php');
$DigLookup = " data-show=\"true\"";
$location = "Click to Set";
$locationID = 0;
if(isset($_REQUEST['location']))
	{
        $lookup=htmlspecialchars($_REQUEST['location']);
        $items = array("s","$lookup");
        $result = $MYSQL->Query("SELECT locationID,LocationTitle FROM smsp_location where active = 1 and locationID = ?",$items);
    
        if ($result->num_rows>0)
        {
            while ($myrow = $result->fetch_assoc())
            {
    
                $locationID=$myrow['locationID'];
                $location=$myrow['LocationTitle'];
                $DigLookup = "";
            }
    
            $result->close();
        }
    }

?>
    <!-- Modals -->

    <!-- Submit form -->
    <div class="modal fade" id="AddAttendee" tabindex="-1" role="dialog" aria-labelledby="AddAttendeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="AddAttendeeLabel">Add new attendee</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="NewAttendeeForm" name="NewAttendeeForm" class="needs-validation" novalidate>
                <div class="form-group row">
                    <label for="AddAttendeeName" class="col-sm-3 col-form-label">Patients Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="AddAttendeeName" placeholder="" required>
                        <div class="invalid-feedback">
                        Please add Patient name
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-3">
                    <label class=" col-form-label">Date of Appointment</label>
                    </div>
                    <label for="AddAttendeeDate" class="col-sm-1 col-form-label">Date</label>
                    <div class="col-sm-3">
                        <input type="text" id="AddAttendeeDate" name="AddAttendeeDate" class="form-control" pattern="(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}" placeholder="DD/MM/YYYY" required>    
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-3">
                    <label class=" col-form-label">Time of Appointment</label>
                    </div>
                    <label for="AddAttendeeHour" class="col-sm-1 col-form-label">Hour</label>
                    <div class="col-sm-2"> 
                        <select id="AddAttendeeHour" name="AddAttendeeHour" class="custom-select" required>
                        <option selected></option>
                        <?php optionNo(0,23); ?>
                        </select>
                    </div>
                    <label for="AddAttendeeMin" class="col-sm-1 col-form-label">Min</label>
                    <div class="col-sm-2">
                        <select type="text" id="AddAttendeeMin" name="AddAttendeeMin" class="custom-select" required> 
                        <option selected></option>
                        <?php optionNo(0,59); ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="AddAttendeeMobileNumber" class="col-sm-3 col-form-label">Mobile Number</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="AddAttendeeMobileNumber" placeholder="" minlength="11" maxlength="12" required>
                        <div class="invalid-feedback">
                        Please add mobile number
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="AddAttendeeLocation" class="col-sm-3 col-form-label">Location</label>
                    <div class="col-sm-9">
                        <?php 
                            autoselect($MYSQL,"SELECT locationID,LocationTitle FROM smsportal.smsp_location where active = '1'","AddAttendeeLocation","locationID","LocationTitle","Choose...",$locationID," required"); 
                        ?>
                        <div class="invalid-feedback">
                        Please select a Location
                        </div>
                    </div>
                </div>
                <input type="hidden" id="AddAttendeeID" placeholder="">
                <input type="hidden" id="appointmentID" placeholder="">
            </form>

            <div id="appointmentnosave" name="appointmentnosave" class="alert alert-danger d-none" role="alert">
                <h4 class="alert-heading">Could not save New Attendee</h4>
                <p></p>
            </div>
        </div>
        <div class="modal-footer">  
        <button type="button" id="AddAttendeeSave" name="AddAttendeeSave" class="btn btn-primary">Save</button>    
        </div>
        </div>
    </div>
    </div>

    <!-- Lookup Modal -->
    <div class="modal fade" id="Locationmod" name="Locationmod" tabindex="-1" aria-labelledby="Location" aria-hidden="true" data-keyboard="false" <?php echo $DigLookup;?>>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title h4" id="Locationtitle">Select a Location</h5>
                </div>
                    <div class="modal-body" id="Locationtxt">
                    
                        <div class="form-group">
                            <label for="Location">Location</label>
                            <form action="" method="GET">
                            <?php 
                                    autoselect($MYSQL,"SELECT locationID,LocationTitle FROM smsportal.smsp_location where active = '1'","location","locationID","LocationTitle","Choose...",$locationID,"onchange='this.form.submit()'"); 
                            ?>
                            </form>
                        </div>
                        
                    </div>
                </div>                   
            </div>
        </div>
    </div>

    <!-- notifictaion Modal -->

    <div id="notenmod" name="notenmod" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Note Modal" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h5" ></h5>
                </div>
                    <div class="modal-body">
                    </div>
            </div>
        </div>
    </div>

    
    <!-- Send SMS Modal -->
        <div class="modal fade" id="sendSMSmodal" tabindex="-1" role="dialog" aria-labelledby="sendmodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="sesendmodalLabel"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="sendSMSmodalfrm" name="sendSMSmodalfrm">
            <div class="form-group">
                <label for="recipient-number" class="col-form-label">Number:</label>
                <input type="hidden" id="message-appointmentID" placeholder="">
                <input type="text" class="form-control" id="recipient-number" required>
            </div>
            <div class="form-group">
                <label for="message-text" class="col-form-label">Message:</label>
                <input type="hidden" id="message-template" value="Please immediately join your partner for their appointment. Thank you for your patience.">
                <textarea class="form-control" id="message-text" maxlength="159" rows="4" required></textarea>
            </div>
            </form>
            <div id="smsnosent" name="smsnosent" class="alert alert-danger d-none" role="alert">
                <h4 class="alert-heading">Could not send message</h4>
                <p></p>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="BTNsendSMS" name="BTNsendSMS" class="btn btn-primary">Send message</button>
        </div>
        </div>
    </div>
    </div>

    <!-- Confirmation Modal -->

    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Do you want to remove Appointment:</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="deletemodaltxt" name="deletemodaltxt" class="capitalize" >Name, Date</p>
      </div>
      <div class="modal-footer">
        <input type="hidden" id="ConfirmID" name="ConfirmID" value ="">
        <button type="button" id="AttendeeRemove" name="AttendeeRemove" class="btn bg-danger">Yes</button>
        <button type="button" class="btn bg-success" data-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>

    <!-- Start of page -->

    <div class="container">
        <div class="bg-info">
        <button type="button" id="newAttendee" Name="newAttendee" class="btn btn-secondary float-right bg-primary" data-toggle="modal" data-target="#AddAttendee"><i class="fas fa-plus"></i> Add new attendee</button>
        </div>
    
    <h3 class="bd-title">Location, <span data-toggle="tooltip" data-placement="right" title="Click here to change location"><a href="#Locationmod"  data-toggle="modal" data-target="#Locationmod"><?php echo $location;?></a></span></h3>	
    <input type="hidden" id="LocationID" name="LocationID" value="<?php echo $locationID;?>">
    <hr>
        <div class="table-responsive-sm">
            <table class="table table-striped">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">Patient</th>
                        <th scope="col">Appointment Time</th>
                        <th scope="col" class="colSentAt">Sent</th>
                        <th scope="col" class="colStatus">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                    </thead>
                    <tbody id="tblAttendees" name="tblAttendees">
                    </tbody>
            </table>
        </div>  
    </div>
</table>

    <?php
require_once('footer.php');
?>