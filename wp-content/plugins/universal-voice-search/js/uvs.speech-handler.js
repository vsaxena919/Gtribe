// *****************************************************************************************************
// *******              speak2web UNIVERSAL VOICE SEARCH                                     ***********
// *******               Get your subscription at                                            ***********
// *******                    https://speak2web.com/plugin#plans                             ***********
// *******               Need support? https://speak2web.com/support                         ***********
// *******               Licensed GPLv2+                                                     ***********
//******************************************************************************************************

// Cross browser 'trim()' funtion support
if (typeof String.prototype.trim !== 'function') { String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, ''); } }

var respTimeOut = false;
var errcnt    = 0;
var myStatus  = {};
var myContext = {};
var voice     = undefined;
var win1      = -1;
var win2      = -1;
var isTalking = false;
var isLoaded  = false;
var lang      = uvsSelectedLang;

let uvsHostName = typeof(uvsCurrentHostName) != 'undefined' ? uvsCurrentHostName : null;

var uvsPreservedText = null, uvsPreservedCallback = null, uvsVoicesPopulated = false;

var uvsDummySpeakButton = document.createElement('button');
uvsDummySpeakButton.setAttribute('type', 'button');

/**
 * Function to get availale list of voices in the browser
 */
function uvsPopulateVoiceList() {
    var speechSynthesis = window.speechSynthesis;

    if (typeof speechSynthesis === 'undefined') { return; }

    voices = speechSynthesis.getVoices();

    // Local function to get specific voice from available voices in browser
    var uvsGetVoice = function(uvsVoices, uvsVoiceName = null, uvsObjProp = 'name'){
        var uvsFoundedVoice = null;
        try {
            for (uvsI = 0; uvsI < uvsVoices.length; uvsI++) {
                if (uvsVoices[uvsI][uvsObjProp].indexOf(uvsVoiceName) != -1) {
                    uvsFoundedVoice = uvsVoices[uvsI];
                    break;
                }
            }
        } catch (err) { 
            uvsFoundedVoice = null; 
        }

        return uvsFoundedVoice;
    };

    //$$$$$$ AS OF NOW (2019 FIRST QUARTER) FIREFOX ON LINUX ONLY SUPPORT ROBOTIC MALE VOICE 
    //$$$$$$ THEREFORE THERE WILL ALWAYS BE A MALE VOICE ON DESKTOP LINUX AND ANDROID DEVICES
    switch (uvsTypeOfSelectedLanguage.toLowerCase()) {
        case 'german':
            // Windows OS
            voice = uvsGetVoice(voices, 'Hedda');

            //Chrome Browser
            if (typeof (voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'Google Deutsch'); }

            // Everything else
            if (typeof (voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'German'); }
            if (typeof (voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'german'); }
            if (typeof (voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'de-DE', 'lang'); }
            if (typeof (voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'de-DE', 'languageCode'); }
            if (typeof (voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'de-DE'); }

            break;

        case 'british english':
            // On Windows not Available
            // Browsers on Mac and iOS
            if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'Daniel'); } // en-GB
            // Linux Chrome
            if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'Google UK English Male'); }
            // Linux Firefox
            if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'english_rp'); } // en-GB
            //Android chrome
            if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'English United Kingdom'); } // en-GB
            if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'en_GB', 'lang'); }
            //Android Firefox
            if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'English (United Kingdom)'); } // en-GB
            
            break;
        
        default:
            // Do nothing for now
    }

    if (typeof(voice) == 'undefined' || voice === null) {
        // Browsers on Windows OS 
        voice = uvsGetVoice(voices, 'David');


        // Browsers on Mac and iOS
        if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'Alex')}; // en-US
        if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'Fred')}; // en-US
        if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'Daniel')}; // en-GB

        // Firefox browser on Linux
        if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'english-us')}; // en-US
        if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'english_rp')}; // en-GB
        
        // Chrome Browser on any platform
        if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'Google UK English Male')};
    }

    if (!(typeof(voice) != 'undefined' && voice !== null)) {
        voice = uvsGetVoice(voices, 'en-US', 'lang');
        
        if (typeof(voice) == 'undefined' || voice === null) { voice = uvsGetVoice(voices, 'en_US', 'lang')}; // en_US (For android)
    }
}

uvsPopulateVoiceList();

// Handle asynch behavior of Chrome browser to populate voice
if (typeof speechSynthesis !== 'undefined' && speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = function() {
        uvsVoicesPopulated = true;
        uvsPopulateVoiceList();

        if (typeof(uvsPreservedText) != 'undefined' && uvsPreservedText !== null) {
            tts(uvsPreservedText, uvsPreservedCallback);
        } 

        uvsPreservedText = null;
        uvsPreservedCallback = null;
    }
}

function stt(blob, errorRecovery, cb) {
    if (errorRecovery == false) {
        let i = Math.floor(Math.random() * 10); 
        let resp = uvsAlternativeResponse['randomLib'];
        
        if (respTimeOut == false) {
            tts(resp[i], function () { });
            respTimeOut = true;
            
            setTimeout(function () {
                respTimeOut = false;
            }, 6000);
        }
    }

    var wsURI           = uvsWebSocketUrl.url + uvsWebSocketUrl.tokenQs + uvsToken + uvsWebSocketUrl.otherQs;
    var websocket       = new WebSocket(wsURI);
    websocket.onopen    = function (evt) { onOpen(evt) };
    websocket.onclose   = function (evt) { onClose(evt) };
    websocket.onmessage = function (evt) { onMessage(evt) };
    websocket.onerror   = function (evt) { onError(evt) };

    function onOpen(evt) {
        // Log service call count
        uvsLogServiceCall();

        let message = {
            'action': 'start',
            'content-type': 'audio/wav',
            'interim_results': false,
            'max_alternatives': 3,
            'smart_formatting': true,            
        };

        websocket.send(JSON.stringify(message));
        websocket.send(blob);
        websocket.send(JSON.stringify({ 'action': 'stop' }));
    }

    function onMessage(evt) {
        let res = JSON.parse(evt.data);

        if (res.results != undefined) {
            let msg = "";

            // we have a message coming back :-)
            var foundFinal = false;

            for (var k in res.results) {
                if (res.results[k].final == true) {
                    msg = msg + res.results[k].alternatives[0].transcript;
                    foundFinal = true;
                }
            }

            errcnt = 0;

            if (foundFinal == true || res.results.length == 0) {
                if (typeof(cb) === 'function') { cb(msg) };
                websocket.close();
            }
        }
    }

    function onError(evt) {
        errcnt++;
        websocket.close();

        if (!(typeof(uvsXApiKey) != 'undefined' && uvsXApiKey !== null)) { return; }

        if (errcnt < 2) {
            //$$$$$$$$$$$$$$$ FETCH NEW TOKEN MIGHT HAVE EXPIRED $$$$$$$$$$$$$$$$$$
            let xhttp = new XMLHttpRequest();
            
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    let res = JSON.parse(this.responseText);
                    uvsToken   = res.token;

                    // Preserve token locally
                    uvsPreserveToken(uvsToken);

                    stt(blob,true,cb);
                }
            };

            try {
                xhttp.open("GET", uvsTokenApiUrl, true);
                xhttp.setRequestHeader("Content-type", "application/json");
                xhttp.setRequestHeader("x-api-key", uvsXApiKey);
                xhttp.send();
            } catch(err) {
                console.log('We had an error while availing expired token agina. Error:' + err.message);
            }
        }
    }

    function onClose(evt) { /* do nothing for now*/ }
}

function tts (text, callback) {
    let u = new SpeechSynthesisUtterance();
    let speechSynthesis = window.speechSynthesis;
    
    try {
        var l = document.getElementById("l1");

        if (l) { l.textContent = text; }
    } catch (err) { 
        // Do nothing
    }

    u.text = text;
    u.lang = lang;

    if (voice != 'undefined' && voice != null) { u.voice = voice; }

    if (uvsVoicesPopulated === false && voice === null) {
        uvsPreservedText = text;
        uvsPreservedCallback = callback;
        return;
    } else {
        uvsVoicesPopulated = false;
    }

    u.onend = function () {
        isTalking = false;
        
        if (callback) { callback(); }
    };

    u.onerror = function (e) {
        // In chrome 71 and above If page is reloaded or user have not yet 
        // interacted with page then 'not-allowed' constraint imposed.
        if (typeof(e.type) != 'undefined' && typeof(e.error) != 'undefined' 
            && e.type == 'error' && e.error == 'not-allowed') {
            isTalking = false;
            console.log('speechSynthesis not available');
            return;
        }

        if (callback) {
            //alert("Unable to speak!");
            callback(e);
        }
    };

    // Starting in Chrome 71, the speech synthesis API now requires some kind of user activation on the page 
    // before it’ll work. This brings it in line with other autoplay policies. 
    // If you try to use it before the user has interacted with the page, it will fire an error.
    try {
        isTalking = true;
        uvsDummySpeakButton.onclick = function() { speechSynthesis.speak(u) };
        uvsDummySpeakButton.click();
    } catch (err) {
        console.log('uvs: speechSynthesis not available. Error:' + err.message);
    }
}

function tts_stop() {
    speechSynthesis.cancel();
}

/**
 * Function to locally preserve token
 * 
 * @param uvsToken - string : Token
 */
function uvsPreserveToken(uvsThisToken = null) {
    try {
        if (uvsThisToken === null) return;

        var currentUtcTimestamp = Math.round(new Date().getTime()/1000);
        uvsThisToken = uvsThisToken + '_uvs_ts_' + currentUtcTimestamp;

        window.localStorage.setItem('uvsToken', uvsThisToken);
    } catch (err) {
        console.log('uvs: Not able to preserve token. Error:' + err.message);
    }
}

/**
 * Function to retrieve a token from local storage
 *
 * @returns preservedToken - string: A token if preserved for more than 6 hours otherwise null.
 */
function uvsGetLocallyPreservedToken() {
    var preservedToken = null;

    try {
        let localToken = window.localStorage.getItem('uvsToken');

        if (!(localToken !== null  && localToken.length > 0)) { return preservedToken; }

        let tokenData = localToken.split('_uvs_ts_');

        if (!(typeof(tokenData) != 'undefined' && tokenData !== null)) { return preservedToken; }

        if (tokenData.length < 2) { return preservedToken; }

        let tokenPreservanceTimestamp = tokenData[1];
        tokenPreservanceTimestamp     = Number(tokenPreservanceTimestamp);
        let currentUtcTimestamp       = Math.round(new Date().getTime()/1000);

        if (isNaN(tokenPreservanceTimestamp)) return preservedToken;

        // Add 6 hours to preservance time
        tokenPreservanceTimestamp = tokenPreservanceTimestamp + (6 * 3600);

        // Check if token has been preserved for more than 6 hours. If not then retrieve locally preserved token
        if (!(currentUtcTimestamp >= tokenPreservanceTimestamp)) {
            preservedToken = tokenData[0];
        } else {
            window.localStorage.removeItem('uvsToken');
        }
    } catch (err) {
        preservedToken = null;
    }

    return preservedToken;
}

/**
 * Function to log STT service call
 *
 * @param {uvsUpdateLastValue - int/Number} : 0 to not to update last value or 1 to update last value
 */
function uvsLogServiceCall(uvsUpdateLastValue = 0) {
    try {
        let uvsXhr = new XMLHttpRequest();

        uvsXhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let res = JSON.parse(this.responseText);

                // Update localized variables of service log 
                uvsServiceLogs.updatedAt    = res.updatedAt || uvsServiceLogs.updatedAt;
                uvsServiceLogs.currentValue = res.currentValue || uvsServiceLogs.currentValue;
                uvsServiceLogs.lastValue    = res.lastValue || uvsServiceLogs.lastValue;
            }
        };

        uvsXhr.open("POST", uvsAjaxObj.ajax_url , true); 
        uvsXhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        uvsXhr.send("action=uvs_log_service_call&_ajax_nonce=" + uvsAjaxObj.nonce + "&updateLastValue=" + uvsUpdateLastValue);
    } catch (err) {
        // Do nothing for now
    }
}

/**
 * Function to get current host/domain full URL
 *
 */
function uvsGetCurrentHostURL() {
    var currentHostUrl = null;
    try {
        if (!(typeof(window.location) != 'undefined' 
            && typeof(window.location.hostname) != 'undefined' 
            && typeof(window.location.protocol) != 'undefined')) {
            return uvsGetHostName();
        }

        var thisProtocol = window.location.protocol;
        var thisHostname = window.location.hostname;

        currentHostUrl = thisProtocol + '//' + thisHostname;
    } catch (err) {
        currentHostUrl = uvsGetHostName();
        console.log('Something went wrong while discovering current domain.');
    }

    return currentHostUrl;
}

/**
 * Function to get current host name from backend.
 */
function uvsGetHostName() {
    return uvsHostName;
}

