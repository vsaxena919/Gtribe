// *****************************************************************************************************
// *******              speak2web UNIVERSAL VOICE SEARCH                                     ***********
// *******               Get your subscription at                                            ***********
// *******                    https://speak2web.com/plugin#plans                             ***********
// *******               Need support? https://speak2web.com/support                         ***********
// *******               Licensed GPLv2+                                                     ***********
//******************************************************************************************************

window.AudioContext = window.AudioContext || window.webkitAudioContext;

var uvsAudioContext = null;
var uvsAudioInput   = null,
uvsRealAudioInput   = null,
uvsInputPoint       = null,
uvsAudioRecorder    = null;
var uvsRecIndex = 0;
var uvsToken    = "";
var initCB      = null;
let uvsStream   = null;

function uvsGetToken() {
    if (!(typeof(uvsXApiKey) != 'undefined' && uvsXApiKey !== null)) return;

    // Check when last service log was updated
    try {
        let uvsLastUpdatedAtTimestamp = uvsServiceLogs.updatedAt || null;

        if (uvsLastUpdatedAtTimestamp !== null) {
            uvsLastUpdatedAtTimestamp = Number(uvsLastUpdatedAtTimestamp);
            let currentUtcTimestamp = Math.round(new Date().getTime()/1000);

            // Add 24 hours to last updated timestamp
            uvsLastUpdatedAtTimestamp = uvsLastUpdatedAtTimestamp + (24 * 3600);

            // Check if last service call log update was older than 24 hours
            if (currentUtcTimestamp >= uvsLastUpdatedAtTimestamp) {
                // Log service call count
                uvsLogServiceCall(1);
            }
        }
    } catch (err) {
        // do nothing
    }

    // Check if token locally preserved. If yes then get it locally.
    var locallyPreservedToken = uvsGetLocallyPreservedToken();

    if (typeof(locallyPreservedToken) != 'undefined' 
        && locallyPreservedToken !== null 
        && locallyPreservedToken.length != 0) {
        uvsToken = locallyPreservedToken;
        return;
    }

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            //alert(this.responseText);
            var res = JSON.parse(this.responseText);
            uvsToken = res.token;
            // Preserve token locally
            uvsPreserveToken(uvsToken);
        }
    };

    try {
        xhttp.open("GET", uvsTokenApiUrl, true);
        xhttp.setRequestHeader("Content-type", "application/json");
        xhttp.setRequestHeader("x-api-key", uvsXApiKey);
        xhttp.send();
    } catch(err) {
        console.log('We had an error while availing token. Error:' + err.message);
    }
}

function uvsGotStream(stream) {
    uvsInputPoint = uvsAudioContext.createGain();
    uvsStream = stream;

    // Create an AudioNode from the stream.
    uvsRealAudioInput = uvsAudioContext.createMediaStreamSource(stream);
    uvsAudioInput     = uvsRealAudioInput;
    uvsAudioInput.connect(uvsInputPoint);
    
    uvsAudioRecorder = new Recorder(uvsInputPoint);
    initCB(uvsAudioRecorder);
}

function uvsInitAudio(cb) {
    uvsGetToken();
    initCB = cb;
    uvsAudioContext = new AudioContext();

    navigator.mediaDevices.getUserMedia({ "audio": !0 })
        .then(uvsGotStream)
        .catch(function (e) {
            console.log("We caught en error while gaining access to audio input", e.message);
        }
    );
}

/**
 * Function to stop accessing audio resource
 *
 */
function uvsStopAudio() {
    try {
        uvsStream.getTracks().forEach(function (track) {
            track.stop();
        });

        uvsAudioContext.close();    
        uvsAudioContext = null;
    } catch(err) {
        console.log('UVS Exception: Unable to release audio resource due to: ' + err.message);
    }
}
