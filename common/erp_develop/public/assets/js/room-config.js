var checkedPax;
var selectedRoomPax;
var roomType;
var paxValue;
var age_cat = [];
var val     = [];
var phpArrObj = [];
var ageCat  = [];
var ageInfo  = [];
var paxInfo = [];
var myObj    =  {};
//var roomCapArr = <?php echo json_encode($all_rooms) ?>;
//var roomArr[info] = {id: id, type: type, name: name, age: age}
var $a      = 1;
var totalSelectedPax = 0;
var maxPax;
var maxExtra;
var extraPax;
var total_child_with_bed = 0;
var warning = $(".message");

jQuery(document).ready(function($) {
	  var $isOptTipComp = $('#is_opt_tip_comp').val();
    if($isOptTipComp) { 
      $( "#dialog-confirm" ).dialog({
        resizable: false,
        height:170,
        width: 400,
        modal: true,
        buttons: {
          "YES": function() {
            updatePrepaidStatus(true);
            $( this ).dialog( "close" );
          },
          "NO": function() {
            // Set is_tip and is_comp to false
            updatePrepaidStatus(false);
            $( this ).dialog( "close" );
          }
        }
      });
    }
	 /**** Set the div height of content same as booking summary ****/
	 /*$this = $("#sidebar");
	  pos = $this.offset({top: 165, left: 1020});
	  $this.animate({
		  left: "-" + pos.left + "px",
        top:  "-" + pos.top  + "px"
		
	  });*/
	  /**** Set the div height of content same as booking summary ****/
	 divSidebarH = $('#sidebar').height();
     divContentH = $('#content').height();
     if(divSidebarH > divContentH) {    
        divContentH = divSidebarH + 150;   
        $('#content').height(divContentH)
        .css({});
      }
     
});
	 

/**
 *  This function is using for select room type 
 *  It assigns the roomType and maxPax value
 *  value will be assigned when people select the room type box
 **/
$("select.room_type_form_select").change(function() {
    // Get value from room type
    var roomType = $(".room_type_form_select").val();
    $.ajax({
        type : 'post',
        async:false,
        dataType : 'json', // http://en.wikipedia.org/wiki/JSON
        url : 'get_room_info',
        data : { room_type: roomType},
        success: function(response) {
            if( response ) {
                maxPax = response[0].capacity;
                maxExtra = response[0].max_capacity;
                console.log(maxPax);
            } else {
                console.log('Error');
            };
        }
    });

    //console.log(roomType);
    // assign name to roomType and give it capacity
    /*switch(roomType) 
    {

      case 'Single':
        maxPax      =  1;
        extraPax    =  0;
        break;
      case 'Double':
        maxPax      =  2;
        extraPax    =  1;
        break;
      case 'Twin':
        maxPax      =  2;
        extraPax    =  1;
        break;
      case 'Triple':
        maxPax      =  3;
        extraPax    =  1;
        break;     
      case 'Quad':
        maxPax      =  4;
        extraPax    =  1;
        break;  
    }*/
    
});


 /**
 *  This is function when people click confirm button
 **/
 $("span.room-btn").click(function() {

  $('div#errorMessage').html("");
  $('div#errorMessage').css( "display", "none" );

  var i = 0;
  var tourPax = '';
  var is_status = ''; // for using child with bed or single suppliment
  var divLength = $('div#roomDiv').length;
  $a = divLength + 1;
  var roomType = $("select.room_type_form_select").val();
  var adult = 0;
  var child = 0;
  var is_nbed = 1;
  var roomPax = 0;
  var child_nbed = 0;
   // assign name to roomType and give it capacity
     //Remove By Wen
//     $.ajax({
//        type : 'post',
//        dataType : 'json', // http://en.wikipedia.org/wiki/JSON
//        url : 'get_room_info',
//        data : { room_type: roomType},
//        success: function(response) {
//            if( response ) {
//
//                maxExtra = response[0].max_capacity;
//                maxPax = response[0].capacity;
//                console.log(maxPax);
//            } else {
//                console.log('Error');
//            };
//        }
//    });

  // Room validation
  if(!roomType) 
  {
     warning.addClass("error").removeClass("success").html("Please select a room type !");
     return false;
  } 
  else 
  {
    warning.addClass("success").removeClass("error").html("");
    //$('select.room_type_form_select', this).remove();

    // Create myObj as new Object when room is selected
    console.log("room type :" + roomType);
    console.log("max pax :" + maxPax);
    //alert(roomType);
    //alert(maxPax);
    myObj['roomType']   = roomType;
    myObj['maxPax']     = maxPax;
  }

  // Get value from room type
  paxValue = $(".room_pax_form_select").val();
  
  // Check if paxValue is null
  if(!paxValue) {
    // show error message remove success message
    warning.addClass("error").removeClass("success").html("Please select a passenger !");
     return false;
  }
  else
  {
    // show success message remove error message
    warning.addClass("success").removeClass("error").html("");   
  }

    // Loop though select passenger
    // Find checked box
    $(':checkbox:checked').each(function(i){
          
          // Get value from checked field
          nameStr         = $(this).val();
          span            = $(this).closest("span").text();
          //alert(span);
          // Split value from checked field into an array 
          age_cat          = nameStr.split('|');
          // Get age cat from splited array
          ageInfo[i]       = age_cat[1];
          // Get pax name from splited array
          paxInfo[i]       = age_cat[0];
          // Get is pax allow no bed - 1: adult or child is not allow no bed 2: child allow no bed
          is_nbed       = age_cat[2];
          // Pax temp id
          temp_id       = age_cat[3];
          // add checked pax into string
          // as html format for display
          tourPax += "<li id="+i+">";
          tourPax += paxInfo[i] + "|" + ageInfo[i];
          tourPax += "</li>";
          if(ageInfo[i] == 'Adult')
          {
            adult++;
          }
          // add child into string
          // for using check if child has bed
          
          if(ageInfo[i] == 'Child' && is_nbed == 2)
          {
          	child++;
          	child_nbed++;
          	maxPax = maxExtra;
          } 
          if(ageInfo[i] == 'Child') {
          	child++;
          }

          roomPax++;
    });

    myObj['paxName'] = paxInfo;
    myObj['ageCat'] = ageInfo;
    
    // check is over room max capacity
    // Compare maxPax and selected Pax
    /*if(adult < 1)
     {
        // show error message remove success message
        warning.addClass("error").removeClass("success").html("Please select an adult !");
        return false;
    } 
    else
    {
      // show success message remove error message
      warning.addClass("success").removeClass("error").html("");
    }*/
    //alert(paxValue.length);
    //alert(maxPax);
    console.log("pax length : " + paxValue.length);
    console.log("max Pax : " + maxPax);
    if(child > 0)
    {
       //alert(totalSelectedPax);
      if(paxValue.length < maxPax-1)
      {     
            // show error message remove success message
            warning.addClass("error").removeClass("success").html("Please select more pax in the room");
            return false;
      } 
      else
      {
        // show success message remove error message
        warning.addClass("success").removeClass("error").html("");
      }
    } else {
       //alert(totalSelectedPax);
      if(paxValue.length < maxPax)
      {
            // show error message remove success message
            warning.addClass("error").removeClass("success").html("Please select a minimum of "+maxPax+" passenger/s for this room");
            return false;
      } 
      else
      {
        // show success message remove error message
        warning.addClass("success").removeClass("error").html("");
      }
    }
   

    // Child is not allowed into signle room
    if(age_cat[1] != "Adult" && roomType == "Single")
    {
          // show error message remove success message
          warning.addClass("error").removeClass("success").html("Sorry ! Child is not allowed into a single room !");
          return false;
    } 
    else
    {
      // show success message remove error message
      warning.addClass("success").removeClass("error").html("");
    }

     // Pass maxExtra value to maxPax
     // If pax is a child or teen
     /*if(age_cat[1] != "Adult" && roomType != "Single")
     {
        maxPax = maxExtra;

     }*/
    //alert(totalSelectedPax);
    if(paxValue.length > maxPax)
    {
      console.log(maxPax);
          // show error message remove success message
          warning.addClass("error").removeClass("success").html("Maximum room capacity is "+maxPax+"");
          return false;
    } 
    else
    {
      /***************** CHECK PASSENGER AND ROOM STATUS *****************/
      // check if child has bed
      //console.log(child);
  
      if(child > 0)
      {
      	if(child_nbed > 0) {
      		maxPax = maxPax - 1;
      	}
          
          child_with_bed = maxPax - adult;
          if(adult > 0 && adult != maxPax) {
            is_status = '<span style="color:#005702;" class="child_with_bed'+$a+'">'+child_with_bed+'</span><span style="color:#005702;"> x child/s need to pay bed charge</span>';
          } else if(adult == maxPax) {
            is_status = '<span style="color:#005702;" class="child_with_bed'+$a+'">'+child_with_bed+'</span><span style="color:#005702;"> x child/s need to pay bed charge</span>';
          } else {
            is_status = '<span style="color:#005702;" class="child_with_bed'+$a+'">'+child_with_bed+'</span><span style="color:#005702;"> x child/s need to pay bed charge</span>';
          }
          total_child_with_bed = total_child_with_bed + child_with_bed;
      } 

      // check is single suppliment
      if(roomType == "Single")
      {
        is_status = '<span style="color:#005702;">This person needs to pay a Single Suppliment fee</span>';
      }
      /***************** END CHECK PASSENGER AND ROOM STATUS *****************/

      // show success message remove error message
      warning.addClass("success").removeClass("error").html("");
      
      // Disable selected passenger when item added to room_pax
      var el = $("select.room_pax_form_select").multiselect(),
            disabled = $('#disabled'),
            selected = $('#selected');

      if(':checkbox:checked'){
        $("select.room_pax_form_select option:checked").attr('disabled','disabled');
        $("select.room_pax_form_select option:checked").removeAttr('selected');
      }

      if(selected.is(':checked')){
        opt.attr('selected','selected');
      }
      
      $("select.room_pax_form_select").appendTo( el );
      
      el.multiselect('refresh');

      //console.log(phpArrObj);

      var roomInfo = "<div id='roomDiv'><hr /><p id='selectedRoom'><span id='maxPax"+$a+"' style='display:none;'>"+maxPax+"</span><span style='color:#3376bb; font-size:20px;'> "+$a+". </span><span id='room"+$a+"' style='color:#3376bb; font-size:20px;'>"+roomType +"</span><ol id='pax"+$a+"' style='margin-top:-25px; margin-left: 500px; color:#3376bb; font-size:16px; height:auto;'>"+ tourPax +"</ol></p>"+is_status+"</div>";

      $("#room_pax").append(roomInfo);  
      //$(".top_text").append(is_status);

      // Assign a json object to a variable
      var roomObj = JSON.stringify(myObj);

      $a++;

    } // End Add
});

$('input[name="submit"]').click(function() {
    var array = [];
    // Total pax in the list
    var totalPax = $('select.room_pax_form_select').find('option').length;
    // Get and add total selected pax
    var totalSelectedPax = $('div#roomDiv').find('li').length;

    // Loop how many roomDiv is generated
    $("div#roomDiv").each(function(i){
      
        //myObj['roomType']   = roomType;
        //myObj['maxPax']     = maxPax;
        ageInfo  = [];
        paxInfo = [];
        $a = i + 1;
        // find text inside span
        myObj['roomType'] = $('div#roomDiv').find('span#room'+$a+'').text();
        myObj['maxPax']   = $('div#roomDiv').find('span#maxPax'+$a+'').text();
        myObj['child_with_bed']   = $('div#roomDiv').find('span.child_with_bed'+$a+'').text();

        // Loop how many pax insdie the ol
        $('ol#pax'+$a+'').find('li').each(function(index){
            // Get value from checked field
            val = $(this).text();
            // Split value from checked field into an array
            age_cat = val.split('|');
            // Get age cat from splited array
            ageInfo[index]       = age_cat[1];
            // Get pax name from splited array
            paxInfo[index]       = age_cat[0];
            
        });
        
        myObj['paxName'] = paxInfo;
        myObj['ageCat'] = ageInfo;
        array[i] = JSON.stringify(myObj);

    });
    //console.log(array[0]);
    // give error message if they are not selected all pax from list
    if(totalSelectedPax != totalPax)
    {
      // Error
       //warning.addClass("error").removeClass("success").html("Please assign passengers to a room !");
       jQuery("div#errorMessage").css({ // css is only set for div #add_note tour itinerary
            "position":"absolute", 
            "top": "125" + "px",
            "left": "350" + "px",
            "width": "300" + "px",
            "padding": "10" + "px",
            "background": "red",
            "color": "#fff",
            "font-size": "16" + "px",
            "font-weight": "bold",
        });
        $('div#errorMessage').hide().html("Please hit the add button below to process").fadeIn();
        $('div#errorMessage')
          .css({
            borderTopLeftRadius: 10, 
            borderTopRightRadius: 10, 
            borderBottomLeftRadius: 10, 
            borderBottomRightRadius: 10,
            WebkitBorderTopLeftRadius: 10, 
            WebkitBorderTopRightRadius: 10, 
            WebkitBorderBottomLeftRadius: 10, 
            WebkitBorderBottomRightRadius: 10, 
            MozBorderRadius: 10 
          });
       return false;  
    } else {
        //warning.addClass("success").removeClass("error").html("success");
        
        /****** Redirect to the page and allow time for process *************/
        if(totalPax != 0) {
          var delay = 1000; //Your delay in milliseconds
          setTimeout(function(){ window.location = "service_requests"; }, delay);
          console.log(array);

          // Prcoess form data from php
          $.ajax({
            type: "POST",
            dataType: "json",
            data: {get_param: array},
            beforeSend: function(x) {
              if(x && x.overrideMimeType) {
                x.overrideMimeType("application/json;charet=UTF-8");
              }
            },
            url: 'room_config_check',
            success: function(data) {
              //window.location.href = "service_requests";
              if(data.msg == "success") {
              } else {
                //alert('nope mate');
              }            // 'data' is a JSON object which we can access directly.
             
            }
          });  
        } else {
           warning.addClass("error").removeClass("success").html("Please select pax in the room");
           return false;
        } 
    }
     
});
 

/**
 *  This is for style and function the passenger multiselect box
 **/
$(function(){
  $("select.room_pax_form_select").multiselect({ 
   //selectedList: 0 // 0-based index   

     selectedText: function(numChecked, numTotal, checkedItems){
      return numChecked + ' of ' + numTotal + ' checked';
    }
  });
});

/**
 *  This is for reset function
 **/ 
$(".reset").click(function() {
  ConfirmDialog('Click yes to clear room selection ?');
});

function ConfirmDialog(message){
    $('<div></div>').appendTo('body')
      .html('<div><h6>'+message+'?</h6></div>')
      .dialog({
          modal: true, title: 'Reset', zIndex: 1000, autoOpen: true,
          width: '300px', resizable: false,
          buttons: {
              Yes: function () {
                  $("#room_pax").empty();
                  $(this).dialog("close");
                    $.get('kill_roomconfig_session');

                    /****** Redirect to the page and allow time for process *************/
                    var delay = 1000; //Your delay in milliseconds
                    setTimeout(function(){ window.location = "room_config"; }, delay);

                  // Problem when use google chorm
                  //location.reload(true);
              },
              No: function () {
                  $(this).dialog("close");
              }
          },
          close: function (event, ui) {
              $(this).remove();
          }
      });
};

function updatePrepaidStatus($status) { 
  $.ajax({
      type: "POST",
      dataType: "json",
      data: {isPrePaid: $status},
      beforeSend: function(x) {
        if(x && x.overrideMimeType) {
          x.overrideMimeType("application/json;charet=UTF-8");
        }
      },
      url: 'tip_comp_status_change',
      success: function(data) {
        //alert(data);
        //window.location.href = "service_requests";
      },
      error: function() { 
        //alert("error");
      }
  });  
}


