///////////////////////////////////////////////////
// Basic AJAX functionality for FGMS Status Page //
// 2012 - Rob Dosogne                            //
// admin [ a t ] truthsolo [ d o t ] net         //
///////////////////////////////////////////////////

function getXMLHttp() {
  var xmlHttp
  try {
    // Firefox, Opera 8.0+, Safari
    xmlHttp = new XMLHttpRequest();
  } catch(e) {
    // Internet Explorer
    try {
      xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch(e) {
      try {
        xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch(e) {
        alert("Your browser does not support AJAX!")
        return false;
      }
    }
  }
  return xmlHttp;
}

function check_mpserver(mpserver) {
  var xmlHttp = getXMLHttp();
  document.getElementById("" + mpserver + "_status").innerHTML ="CHK";
  document.getElementById("" + mpserver + "_chk").style.backgroundColor = "pink";
  xmlHttp.onreadystatechange = function() {
    if(xmlHttp.readyState == 4) {
      //HandleResponse(xmlHttp.responseText);
      document.getElementById("" + mpserver + "").innerHTML = xmlHttp.responseText;
    }
  }
  xmlHttp.open("GET", "_fgms_checker.php?s=" + mpserver + "&lv=1", true); 
  xmlHttp.send(null);
}

function HandleResponse(response) {
  document.getElementById('ResponseDiv').innerHTML = response;
}