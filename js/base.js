/*
SMS portal Javascript and Bootstrap code
Christopher Rouse Jan 2020
*/

/**
 * @function pad
 * Pad a decimal number
 * @param  int number to be padded
 * @param  int width of padding 
 * @param  string padding string (optional)
 * @return string of padded daye
 */
function pad(n, width, z) {
  z = z || '0';
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

/**
 * @function parseDate
 * Convert dd/mm/yy hh:mm int javascript date object
 * @param  str date string to be converted
 * @return Date object
 */
function parseDate(str) {
            var m = str.match(/(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](\d{4})[\s](0[0-9]|[1][0-9]|[2][0-4])[:]([0-5][0-9])/);

            return (m) ? new Date(m[3], m[2]-1, m[1],m[4],m[5]) : null;
        }
/**
 * @function updateSMS
 * Check status of send SMS message and update table.
 * @param  int EsendexID - Esendex msg ID to check
 * @param  int appointmentID - appointmentID used to update table
 * @return void no return
 */
function updateSMS(EsendexID,appointmentID)
{
    

    $.ajax(
                    'ajax/sms.php?func=checkSMSstate',
                    {
                        type: "POST",
                        data: {

                                "EsendexID" : EsendexID,                               
                              }

                    }
                ).done(function (statusret) {
                    $('#busy').addClass("d-none");
                    
                    if(statusret.status==true)
                    {        
                       // $('#StatusCell'+appointmentID).html(statusret.Esendexstate).fadeIn();
                        
                        $('#StatusCell'+appointmentID).fadeOut(function() {
                            $(this).html(statusret.Esendexstate).fadeIn();
                        });

                        var msgat = "";
                        if (statusret.Esendexstate == "Delivered")
                        {
                            msgat =  statusret.DeliverdDate;
                        }
                        else
                        {
                            msgat = statusret.SentDate;
                        }
                        
                       // $('#SentATCell'+appointmentID).html(msgat).fadeIn();
                        $('#SentATCell'+appointmentID).fadeOut(function() {
                            $(this).html(msgat).fadeIn();
                        });
                        
                        if(statusret.Esendexstate=="Sent")
                        {
                            setTimeout(function(){ updateSMS(EsendexID,appointmentID) }, 30000);
                        }
                        
                    }
                })
                .fail(function(jqXHR, textStatus,errorThrown) {
                    var notenmodal =  $('#notenmod');
                    $('#busy').addClass("d-none");
                    $('#sendSMSmodal').modal('hide')
                    notenmodal.find('.modal-title').text("Something Has Gone Wrong");
                    notenmodal.find('.modal-body').html('Please reload the page </hr><p class="mb-0">Error Code: '+ jqXHR.status +'</p>');
                    notenmodal.modal('show');
                  });

    
}


/**
 * @function refreshdata
 * Draw the apointmnet table
 * @return void no return
 */
function refreshdata()
{
    var LocationID = $('#LocationID').val();
   // var AddAttendeeApptTime = null;
    $.ajax(
        'ajax/database.php?func=attendees',
        {
            type: "POST",
            data: {
                   // "ApptTime" : AddAttendeeApptTime,
                    "LocationID" : LocationID 
                  }

        }
    ).done(function (JsonList) {
        
        
        if(JsonList.status==true)
        {
            
        var table = $('<tbody>');
	    table.attr("id","tblAttendees");// create a new table 
        localStorage.clear();
        localStorage.setItem("tblAttendees",JSON.stringify(JsonList.attendees));
        $.each( JsonList.attendees, function( i, attendee ) {

        var tr = $("<tr>");
        
        var rowname = $("<th>"); // Name
        rowname.attr({"id":attendee.appointmentID ,
		"AddAttendeeID":attendee.appointmentID,"class":"capitalize"});
        rowname.html('<p>' + attendee.Name+'</p>').appendTo(tr);


        $("<td>").html('<p>' + attendee.AppointmentTime + '</p>').appendTo(tr); // Time
        
        var SentStatus = "";
        var msgat = "";
        if (attendee.Status !== null)
        {
            SentStatus = attendee.Status;
            
            if (SentStatus == "Delivered")
            {
                msgat =  attendee.DeliverdDate;
            }
            else
            {
                msgat = attendee.SentDate;
            }

        }

        var SentATCell = $("<td>");
        var SentATP = $("<p>");
        SentATP.html(msgat);
        SentATP.attr({"id":"SentATCell" + attendee.appointmentID});
        SentATP.appendTo(SentATCell);
        SentATCell.appendTo(tr);


        var stateCell = $("<td>");
        var stateP = $("<p>");
        stateP.html(SentStatus);
        stateP.attr({"id":"StatusCell" + attendee.appointmentID});
        stateP.appendTo(stateCell);
        stateCell.appendTo(tr);




        var actions = $("<td>");
        
        if (attendee.appointmentID !== null)
        {
            var buttonEdit = $("<button>");
            buttonEdit.attr({
                            "id":"btnEDIT" + attendee.appointmentID,
                            "type":"button",
                            "class":"btn btn-sm bg-primary btn-outline-light ml-1",
                            "data-toggle":"modal",
                            "data-target":"#AddAttendee",
                            "title":"Edit",
                            "data-AddAttendeeID":i});

            buttonEdit.html('<i class="fas fa-pencil-alt"></i>');

            var buttonSMS = $("<button>");
            buttonSMS.attr({
                            "id":"btnSMS" + attendee.appointmentID,
                            "type":"button",
                            "class":"btn btn-sm bg-success btn-outline-light ml-1",
                            "data-toggle":"modal",
                            "data-target":"#sendSMSmodal",
                            "title":"Send Text",
                            "data-AddAttendeeID":i});

            buttonSMS.html("Send");
            
            var buttonDelete = $("<button>");
            buttonDelete.attr({
                                "id":"btnDelete" + attendee.appointmentID,
                                "type":"button",
                                "class":"btn btn-sm bg-danger btn-outline-light ml-1",
                                "data-toggle":"modal",
                                "data-target":"#deletemodal",
                                "title":"Remove Attendance",
                                "data-AddAttendeeID":i});
            buttonDelete.html('<i class="far fa-trash-alt"></i>');
            

            buttonEdit.appendTo(actions);
            buttonSMS.appendTo(actions);
            buttonDelete.appendTo(actions);

            
           
            actions.appendTo(tr);
        }
        else
        {
             actions.html("").appendTo(tr);
        }
        
        tr.appendTo(table);
         
        })
        
        $("#tblAttendees").replaceWith(function() {
        return $(table).hide().fadeIn();
        });


        }
        else
        {
            var notenmodal =  $('#notenmod');
            notenmodal.find('.modal-title').text("Somthing Has Gone Wrong");
            notenmodal.find('.modal-body').html('Please reload the page </hr><p class="mb-0">Error: '+ JsonList.mgs +'</p>');
            notenmodal.modal('show');
        }
    })
    .fail(function(jqXHR, textStatus,errorThrown) {
        var notenmodal =  $('#notenmod');
        notenmodal.find('.modal-title').text("Somthing Has Gone Wrong");
        notenmodal.find('.modal-body').html('Please reload the page </hr><p class="mb-0">Error Code: '+ jqXHR.status +'</p>');
        notenmodal.modal('show');
      });

}

// Ui Elements

$(document).ready(function(){
    // Modal Dialogues

    // new attendee modal
    var AddAttendeemodal = new bootstrap.Modal(document.getElementById('AddAttendee'), {
        keyboard: false
    });
    
    $('#AddAttendee').on('hide.bs.modal', function (e) {
        
       
            var d = new Date(); // for now
            var inthrs = pad(d.getHours(),2);
            var intmins = pad(d.getMinutes(),2);
            var dateStr = pad(d.getDate(),2)+"/"+pad(d.getMonth()+1,2)+"/"+pad(d.getFullYear(),2)
            $('#AddAttendeeHour').val(inthrs);
            $('#AddAttendeeMin').val(intmins);
         
            $("#AddAttendeeMobileNumber").val("");
            $("#AddAttendeeID").val("");
            $('#AddAttendeeName').val("");
            $('#appointmentID').val("");
            $('#appointmentnosave').addClass("d-none");
       
        
    })


    $('#AddAttendee').on('show.bs.modal', function (e) {
        
        var buttonClicked = $(e.relatedTarget) // Button that triggered the modal
        var recipient = buttonClicked.data('addattendeeid') // Extract info from data-* attributes
        
        if (typeof recipient === "undefined") {
            var d = new Date(); // for now
            var inthrs = pad(d.getHours(),2);
            var intmins = pad(d.getMinutes(),2);
            var dateStr = pad(d.getDate(),2)+"/"+pad(d.getMonth()+1,2)+"/"+pad(d.getFullYear(),2)
            $('#AddAttendeeHour').val(inthrs);
            $('#AddAttendeeMin').val(intmins);
            $('#AddAttendeeDate').val(dateStr);
            $("#AddAttendeeMobileNumber").val("");
            $("#AddAttendeeID").val("");
            $('#AddAttendeeName').val("");
            $('#appointmentID').val("");
            $('#appointmentnosave').addClass("d-none");
        }
        else
        {
            tblAttendees = JSON.parse(localStorage.getItem("tblAttendees"));
            var tblrow = tblAttendees[recipient];
            console.log(tblrow.AppointmentTime);
            var d = new parseDate(tblrow.AppointmentTime);
            console.log(d);
            var inthrs = pad(d.getHours(),2);
            var intmins = pad(d.getMinutes(),2);
            var dateStr = pad(d.getDate(),2)+"/"+pad(d.getMonth()+1,2)+"/"+pad(d.getFullYear(),2)
            $('#AddAttendeeHour').val(inthrs);
            $('#AddAttendeeMin').val(intmins);
            $('#AddAttendeeDate').val(dateStr);
            $("#AddAttendeeMobileNumber").val(tblrow.MobileNumber);
            $("#AddAttendeeID").val(tblrow.PaientID);
            $('#AddAttendeeName').val(tblrow.Name);
            $('#appointmentID').val(tblrow.appointmentID);
        }
        
    })

    // Location modal
    var Locationmodal = new bootstrap.Modal(document.getElementById('Locationmod'), {
        keyboard: false
    });

    // Notifictaion modal

    var notenmod = new bootstrap.Modal(document.getElementById('notenmod'), {
        keyboard: false
    });

    // Send SMS modal

    var sendSMSmodal = new bootstrap.Modal(document.getElementById('sendSMSmodal'), {
        keyboard: false
    });

    $('#sendSMSmodal').on('show.bs.modal', function (e) {

        var buttonClicked = $(e.relatedTarget) // Button that triggered the modal
        var recipient = buttonClicked.data('addattendeeid') // Extract info from data-* attributes

       
        tblAttendees = JSON.parse(localStorage.getItem("tblAttendees"));
        var tblrow = tblAttendees[recipient];

         $('#BTNsendSMS').removeAttr('disabled');
        var SentAtTXT = $('#SentATCell' + tblrow.appointmentID).text();
        
        if ( SentAtTXT != "")
        {
        
        var SentAt = new parseDate(SentAtTXT);
        var endTime = new Date();
        
        var difference = endTime.getTime() - SentAt.getTime(); // This will give difference in milliseconds
        var SentAtMinutes = Math.round(difference / 60000);
    

         var StatusCelltxt = $('#StatusCell' + tblrow.appointmentID).text();

        if (SentAtMinutes <=1 || StatusCelltxt == "Sending")
        {
            $('#BTNsendSMS').attr('disabled','disabled');
        } 

        

        }

        var modal = $(this)
        modal.find('.modal-title').text('New message to ' + tblrow.Name)
        $('#recipient-number').val(tblrow.MobileNumber);
        $('#message-appointmentID').val(tblrow.appointmentID);
        $('#message-text').val($('#message-template').val());
        $('#smsnosent').addClass("d-none");
    });

    // Delete Modal
    var deletemodal = new bootstrap.Modal(document.getElementById('deletemodal'), {
        keyboard: false
    });

    $('#deletemodal').on('show.bs.modal', function (e) {

        var buttonClicked = $(e.relatedTarget) // Button that triggered the modal
        var recipient = buttonClicked.data('addattendeeid') // Extract info from data-* attributes

       
        tblAttendees = JSON.parse(localStorage.getItem("tblAttendees"));
        var tblrow = tblAttendees[recipient];
        $('#deletemodaltxt').html(tblrow.Name + " at " + tblrow.AppointmentTime);
        $('#ConfirmID').val(tblrow.appointmentID);
    });
   

    //lookups

    // Contact lookup
    $('#AddAttendeeName').autoComplete({
        resolver: 'custom',
        //preventEnter:false,

        formatResult: function (item) {
            return {
                value: item.patientID,
                text: item.name,
                html: [ 
                        item.name + " - " + item.MobileNumber
                    ] 
            };
        },
        events: {
            search: function (qry, callback) {
                // let's do a custom ajax call
                $.ajax(
                    'ajax/lookups.php',
                    {
                        data: { 'name': qry}
                    }
                ).done(function (res) {
                
                    callback(res.contacts)
                });
            }

        }
    });
  
    $('#AddAttendeeName').on('autocomplete.select', function (evt, value) {
       
       if (value.patientID !== null)
       {
            $("#AddAttendeeMobileNumber").val(value.MobileNumber);
            $("#AddAttendeeID").val(value.patientID);
       }
       else
       {
           
           // $('#AddAttendeeName').val("");
            $("#AddAttendeeMobileNumber").val("");
            $("#AddAttendeeID").val("");
       }
    });

	$('#AddAttendeeName').on('autocomplete.freevalue', function (evt, value) {
	    console.log("There");
        $("#AddAttendeeMobileNumber").val("");
        $("#AddAttendeeID").val("");
	});    

    // Enable tool tips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

   // Hide all html enable dialogues 
   $('*[data-show]').modal('show');

   // Delete Button

    $("#AttendeeRemove").click(function (){
        var delvar = $('#ConfirmID').val();
        $('#deletemodal').modal('hide');
        $('#busy').removeClass("d-none");
        $.ajax(
            'ajax/database.php?func=delete',
            {
            type: "POST",    
            data: {
                    "appointmentID" : delvar
                }

            }
            ).done(function (statusret) {
                $('#busy').addClass("d-none");

                if(statusret.status==true)
                {
                    refreshdata();
                    var notenmodal =  $('#notenmod');
                    notenmodal.find('.modal-title').text("Information");
                    notenmodal.find('.modal-body').text("Deleted");
                    notenmodal.modal('show');
                    setTimeout(function(){
                        $('#notenmod').modal('hide')
                    }, 1000);
                }
                else
                {
                    var notenmodal =  $('#notenmod');
                    $('#busy').addClass("d-none");
                    notenmodal.find('.modal-title').text("Something Has Gone Wrong");
                    notenmodal.find('.modal-body').html('Please reload the page </hr><p class="mb-0">Error Code: '+ statusret.mgs +'</p>');
                    notenmodal.modal('show');
                }
            })
            .fail(function(jqXHR, textStatus,errorThrown) {
                var notenmodal =  $('#notenmod');
                $('#busy').addClass("d-none");
                $('#AddAttendee').modal('hide');
                notenmodal.find('.modal-title').text("Something Has Gone Wrong");
                notenmodal.find('.modal-body').html('Please reload the page </hr><p class="mb-0">Error Code: '+ jqXHR.status +'</p>');
                notenmodal.modal('show');
        });


    });
   // Save Button
    $("#AddAttendeeSave").click(function (){
        var Addform = $("#NewAttendeeForm");
        var AddAttendeeMobileNumber = $("#AddAttendeeMobileNumber").val();
        var AddAttendeeID = $("#AddAttendeeID").val();
        var AddAttendeeName = $('#AddAttendeeName').val();
        var AddAttendeeMin = $('#AddAttendeeMin').val();
        var AddAttendeeHour = $('#AddAttendeeHour').val();
        var AddAttendeeDate = $('#AddAttendeeDate').val();
        var AddAttendeeApptTime = AddAttendeeDate + " "+ AddAttendeeHour + ":" + AddAttendeeMin;
        var AddAttendeeLocation = $('#AddAttendeeLocation').val();
        var appointmentID =  $('#appointmentID').val();
        $('#appointmentnosave').addClass("d-none");
        if ($("#NewAttendeeForm")[0].checkValidity()===false)
        {
      
            if(!Addform.hasClass('was-validated'))
            {
                Addform.addClass('was-validated');
            }

        }
        else
        {
           
            $('#busy').removeClass("d-none");
            $.ajax(
                    'ajax/database.php?func=save',
                    {
                        type: "POST",
                        data: {
                                "AttendeeID" : AddAttendeeID,
                                "AttendeeName" : AddAttendeeName,
                                "MobileNumber" : AddAttendeeMobileNumber,
                                "ApptTime" : AddAttendeeApptTime,
                                "LocationID" : AddAttendeeLocation,
                                "appointmentID" : appointmentID
                              }

                    }
                ).done(function (statusret) {
                    $('#busy').addClass("d-none");
                    
                    if(statusret.status==true)
                    {
                        refreshdata();
                        $('#AddAttendee').modal('hide');
                        var notenmodal =  $('#notenmod');
                        notenmodal.find('.modal-title').text("Information")
                        notenmodal.find('.modal-body').text("Attendee Added")
                        notenmodal.modal('show');
                        setTimeout(function(){
                            $('#notenmod').modal('hide')
                          }, 1000);

                    }
                    else
                    {
                        var alertxtx = $('#appointmentnosave');
                        alertxtx.removeClass("d-none");
                        alertxtx.find('p').text(statusret.mgs);
                    }
                })
                .fail(function(jqXHR, textStatus,errorThrown) {
                    var notenmodal =  $('#notenmod');
                    $('#busy').addClass("d-none");
                    $('#AddAttendee').modal('hide');
                    notenmodal.find('.modal-title').text("Something Has Gone Wrong");
                    notenmodal.find('.modal-body').html('Please reload the page </hr><p class="mb-0">Error Code: '+ jqXHR.status +'</p>');
                    notenmodal.modal('show');
                  });
        }
    });


    // Send button
    $("#BTNsendSMS").click(function (){
        var SMSform = $("#sendSMSmodalfrm");
        var AddAttendeeMobileNumber = $("#recipient-number").val();
        var appointmentID =  $('#message-appointmentID').val();
        var MobileMsg =  $('#message-text').val(); 
        
        if ($("#sendSMSmodalfrm")[0].checkValidity()===false)
        {
            if(!SMSform.hasClass('was-validated'))
            {
                SMSform.addClass('was-validated');
            }
        }
        else
        {
           
            $.ajax(
                    'ajax/sms.php?func=send',
                    {
                        type: "POST",
                        data: {

                                "MobileNumber" : AddAttendeeMobileNumber,
								"MobileMsg" : MobileMsg,                                
                                "appointmentID" : appointmentID
                              }

                    }
                ).done(function (statusret) {
                    $('#busy').addClass("d-none");
                    
                    if(statusret.status==true)
                    {        
                        $('#sendSMSmodal').modal('hide');
                        $('#StatusCell'+appointmentID).html(statusret.Esendexstate);
                         $('#StatusCell'+appointmentID).html();
                        setTimeout(function(){ updateSMS(statusret.EsendexID,appointmentID) }, 5000);
                    }
                    else
                    {
                        var alertxtx = $('#smsnosent');
                        alertxtx.removeClass("d-none");
                        alertxtx.find('p').text(statusret.mgs);
                    }
                })
                .fail(function(jqXHR, textStatus,errorThrown) {
                    console.log("Hmm");
                    var notenmodal =  $('#notenmod');
                    $('#busy').addClass("d-none");
                    $('#sendSMSmodal').modal('hide')
                    notenmodal.find('.modal-title').text("Something Has Gone Wrong");
                    notenmodal.find('.modal-body').html('Please reload the page </hr><p class="mb-0">Error Code: '+ jqXHR.status +'</p>');
                    notenmodal.modal('show');
                  });

        }
    });
    refreshdata();

    setInterval(function(){ refreshdata(); }, 300000);

    
});