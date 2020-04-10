// *****************************************************************************************************
// *******              speak2web UNIVERSAL VOICE SEARCH                                     ***********
// *******               Get your subscription at                                            ***********
// *******                    https://speak2web.com/plugin#plans                             ***********
// *******               Need support? https://speak2web.com/support                         ***********
// *******               Licensed GPLv2+                                                     ***********
//******************************************************************************************************

(function () {
    'use strict';
    
    let uvsTimerID = 0;
    
    // Auto timeout duration to stop listening Mic
    var uvsMicListenAutoTimeoutDuration = null;

    if (typeof(uvsMicListenTimeoutDuration) != 'undefined' && uvsMicListenTimeoutDuration !== null) {
        uvsMicListenAutoTimeoutDuration = parseInt(uvsMicListenTimeoutDuration);
        uvsMicListenAutoTimeoutDuration = isNaN(uvsMicListenAutoTimeoutDuration) ? 8 : uvsMicListenAutoTimeoutDuration;
    } else {
        uvsMicListenAutoTimeoutDuration = 8;
    }

    uvsMicListenAutoTimeoutDuration = (uvsMicListenAutoTimeoutDuration < 8) ? 8 : uvsMicListenAutoTimeoutDuration;
    uvsMicListenAutoTimeoutDuration = (uvsMicListenAutoTimeoutDuration > 20) ? 20 : uvsMicListenAutoTimeoutDuration;
    uvsMicListenAutoTimeoutDuration = uvsMicListenAutoTimeoutDuration * 1000;

    // Local function to clear interval
    function uvsClearTimer(timerInstance) {
        try {
            if (typeof (timerInstance) != 'undefined' && timerInstance !== null && timerInstance != false) {
                clearInterval(timerInstance);
            }
        } catch (err) {/* do nothing for now*/ }
    }

    // sanitize alpha-numeric css values to get numeric value
    function getNumber(number) {
        number = parseInt(number, 10);
        return isNaN(number) || number === null || typeof (number) === 'undefined' ? 0 : number;
    }

    // Function to check if any mic already listening
    function uvsAnyOtherMicListening(uvsExceptionBtnId = null) {
        var uvsOneOfMicListening = false;
        try {
            var uvsAllMicButtons = document.querySelectorAll('button.universal-voice-search-button');

            if (typeof(uvsAllMicButtons) == 'undefined' 
                || uvsAllMicButtons === null 
                || uvsExceptionBtnId == null) { return uvsOneOfMicListening; }

            for (var uvsI = 0; uvsI < uvsAllMicButtons.length; uvsI++) {
                var uvsClassNames = uvsAllMicButtons[uvsI].className;
                var uvsBtnId = uvsAllMicButtons[uvsI].getAttribute('id');

                if (!(typeof(uvsClassNames) != 'undefined' && uvsClassNames.trim() != '')) continue;

                if (uvsClassNames.indexOf('listening') != -1 && uvsExceptionBtnId != uvsBtnId) {
                    uvsOneOfMicListening = true;
                    break;
                }
            }
        } catch (err) {
            uvsOneOfMicListening = false;
        }

        return uvsOneOfMicListening;
    }

    var inputWrapperSelectorsToSeek = [
        'form[role=search]',
        'form[class=searchform]',
        'form[class=search_form]',
        'form[class=search-form]',
        'form[class=searchForm]'
    ];
    var searchInputSelectorsToSeek = [
        'input[name=s]',
        'input[name=search]',
        'input[name=find]',
        'input[type=search]',
        'input[class=search-field]',
        'input[class=search_field]',
        'input[class=searchfield]',
        'input[class=searchField]',
        'input[id=search]',
        'input[id=search-field]',
        'input[id=search_field]',
        'input[id=searchfield]',
        'input[id=searchField]'
    ];

    // Detect and decide event handler for browsers over iOS
    var uvsIOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
    var uvsMicEventToListen = (uvsIOS === true) ? 'touchend' : 'click';

    // Detect Android OS
    var ua = navigator.userAgent.toLowerCase();
    var isAndroid = ua.indexOf("android") > -1;

    var recordTimer = null;

    var uvsAllFormsOnPage = document.querySelectorAll('form');// Get all forms on a page
    var speechInputWrappers = [];

    // Seek and get forms with search ability
    try {
        for (var vdnI = 0; vdnI < inputWrapperSelectorsToSeek.length; vdnI++) {
            speechInputWrappers = document.querySelectorAll(inputWrapperSelectorsToSeek[vdnI]);

            if (speechInputWrappers.length > 0) { break; }
        }
    } catch (err) { speechInputWrappers = []; }

    // Override 'speechInputWrappers' to cover missing forms from page
    if (speechInputWrappers.length < uvsAllFormsOnPage.length) {
        speechInputWrappers = null;
        speechInputWrappers = uvsAllFormsOnPage;
    }

    let formElementForWidget = null;

    [].forEach.call(speechInputWrappers, function (speechInputWrapper, index) {
        var inputEl = null;
        var recognizing = false;

        try {
            // Preserve first form on page and it's input element for widget
            if (index == 0) {
                formElementForWidget = speechInputWrapper;
            }

            // Get input field intented for search feature on page
            for (var uvsI = 0; uvsI < searchInputSelectorsToSeek.length; uvsI++) {
                inputEl = speechInputWrapper.querySelector(searchInputSelectorsToSeek[uvsI]);

                if (inputEl !== null) { break; }
            }

            // Get submit button
            let formSubmitBtnEl = speechInputWrapper.querySelector("input[type=submit]");

            if (formSubmitBtnEl === null) { 
                formSubmitBtnEl = speechInputWrapper.querySelector("button[type=submit]");
            }

            if (formSubmitBtnEl !== null) {
                speechInputWrapper.addEventListener('submit', function(submitEvent) {
                    // If mic is listening then abort form submition
                    if (recognizing == true) {
                        submitEvent.preventDefault();
                    }
                }, false);

                 // Remove any overlapping icon from submit button of search form                   
                try {
                    let submitButtonChildNodes = formSubmitBtnEl.querySelectorAll('img, svg');
                    
                    for (let j = 0; j < submitButtonChildNodes.length; j++) {
                        let submitBtnChildNode = submitButtonChildNodes[j];
                        submitBtnChildNode.classList.add('uvs-hide-element');
                    }
                } catch(err) {
                    // do nothing for now
                }
            }
        } catch (err) { inputEl = null; }

        // If search input field not found then continue
        if (null === inputEl) { return; }
        
        try {
            // Try to show the form temporarily so we can calculate the sizes
            var speechInputWrapperStyle = speechInputWrapper.getAttribute('style');
            var havingInlineStyle = (typeof(speechInputWrapperStyle) != 'undefined' 
                && speechInputWrapperStyle !== null && speechInputWrapperStyle.trim() != '') ? true : false;
            speechInputWrapperStyle = (havingInlineStyle) ? speechInputWrapperStyle + ';' : '';
            speechInputWrapper.setAttribute('style', speechInputWrapperStyle + 'display: block !important');
            speechInputWrapper.classList.add('universal-voice-search-wrapper');
            speechInputWrapper.classList.add('uvs-sanitize-form-wrapper');

            // Add some markup as a button to the search form
            var micBtn = document.createElement('button');
            micBtn.setAttribute('type', 'button');
            micBtn.setAttribute('class', 'universal-voice-search-button');
            micBtn.setAttribute('id', 'universal-voice-search-button' + index);
            micBtn.appendChild(document.createTextNode(universal_voice_search.button_message));

             // Add mic image icon
            var uvsMicIcon = document.createElement('img');
            uvsMicIcon.setAttribute('src', uvsImagesPath + 'uvs_mic.svg');
            uvsMicIcon.setAttribute('class', 'uvs-mic-image');

            micBtn.appendChild(uvsMicIcon);

            var inputHeight = getNumber(inputEl.offsetHeight);// Get search input height
            var buttonSize = getNumber(0.8 * inputHeight);

            // Set default mic button size to 35px when button size calculated to 0 or unknown
            if (getNumber(buttonSize) == 0) { inputHeight = buttonSize = 35; }

            var micbtnPositionTop = getNumber(0.1 * inputHeight);

            // Size and position of complete mic button
            var inlineStyle = 'top: ' + micbtnPositionTop + 'px; ';
            inlineStyle += 'height: ' + buttonSize + 'px !important; ';
            inlineStyle += 'width: ' + buttonSize + 'px !important; ';
            inlineStyle += 'z-index: 999 !important; margin-left: 3px !important; border-radius: 50% !important;  border: 2px solid #ffff !important;';
            micBtn.setAttribute('style', inlineStyle);

            // Create Wrapper to wrap around input search field like a elastic band
            var wrapper = document.createElement('div');
            let inputCurrentStyle = window.getComputedStyle(inputEl);
            wrapper.setAttribute('class', 'uvs-mic-band');
            wrapper.setAttribute('onclick', 'return false');
            wrapper.style.width = inputCurrentStyle.width;
            inputEl.insertAdjacentElement('beforebegin', wrapper);// Place wrapper before input search field

            // Set parent element's (parent of inputEl) display stack order higher 
            // To handle overlapped submit button on mic icon
            var parentEl = inputEl.parentNode.nodeName;

            if (typeof(parentEl) != 'undefined' && parentEl !== null && parentEl.length != 0) {
                parentEl = parentEl.toLowerCase();

                if (parentEl != 'form') {
                    inputEl.parentNode.style.zIndex = 1;
                }
            }

            // Append search input field element inside a wrapper band
            wrapper.appendChild(inputEl);

            // Place mic button/icon exact before search input field element
            inputEl.insertAdjacentElement('beforebegin', micBtn);
            inputEl.classList.add('uvs-mic-band');

            // Reset form style again
            speechInputWrapper.setAttribute('style', speechInputWrapperStyle);
            
            // Setup recognition
            var finalTranscript = '';
            var final_transcript = "";
            var ignore_onend;

            if ('webkitSpeechRecognition' in window) {
                var recognition = new webkitSpeechRecognition();
                recognition.continuous = true;
                recognition.interimResults = true;

                recognition.onstart = function () {
                    recognizing = true;
                };

                recognition.onerror = function (event) {
                    uvsClearTimer(recordTimer);
                    micBtn.classList.remove('listening');
                    recognizing = false;

                    if (event.error == 'no-speech') {
                        inputEl.placeholder = uvsAlternativeResponse['notAudible'];
                        tts(uvsAlternativeResponse['notAudible'], function(){});
                        ignore_onend = true;
                    }
                    if (event.error == 'audio-capture') {
                        inputEl.placeholder =  uvsMessages['micNotAccessible'];
                        ignore_onend = true;
                    }
                    if (event.error == 'not-allowed') {
                        inputEl.placeholder = uvsMessages['browserDenyMicAccess'];
                        micBtn.style.setProperty("color", "white");
                        ignore_onend = true;
                    }
                };

                function processEnd() {
                    recognizing = false;

                    if (ignore_onend) { return; }
                    
                    finalTranscript = final_transcript;   
                    micBtn.classList.remove('listening');
                    micBtn.style.setProperty("color", "white");               
                    
                    if (typeof(finalTranscript) != 'undefined' && finalTranscript.length != 0) {
                        inputEl.value = final_transcript;

                        tts(uvsAlternativeResponse['basic'], function () {
                            speechInputWrapper.submit();
                        });
                    } else {
                        // tts(uvsAlternativeResponse['notAudible'], function(){});
                        inputEl.placeholder = uvsMessages['ask'];
                    }
                };

                recognition.onend = function () {
                    if (isAndroid) {
                        processEnd();
                    }
                };

                recognition.onresult = function (event) {
                    let interim_transcript = '';
                    
                    if (typeof (event.results) == 'undefined') {
                        recognition.onend = null;
                        recognition.stop();
                        inputEl.placeholder = uvsMessages['unableToHear'];
                        tts(uvsAlternativeResponse['micConnect'], function () { });
                        return;
                    }

                    for (var i = event.resultIndex; i < event.results.length; ++i) {
                        if (event.results[i].isFinal) {
                            final_transcript = event.results[i][0].transcript;
                            
                            if (isAndroid == false) {
                                processEnd();
                                recognition.stop();
                            }                        
                        } else {
                            interim_transcript += event.results[i][0].transcript;
                            inputEl.value = interim_transcript;
                        }
                    }
                };

                micBtn.addEventListener(uvsMicEventToListen, function (event) {
                    // micBtn.onclick = function (event) {
                    if (uvsAnyOtherMicListening(micBtn.getAttribute('id')) === true) return;

                    // Deny recording if microphone is not accessible
                    var micButtonInteracted = document.getElementById("universal-voice-search-button" + index);
                    var classNamesInContext = micButtonInteracted.className;

                    // Set interval for 20 seconds when mic is in reset/fresh state ie: when user clicked mic for recording to start
                    if (!(typeof (classNamesInContext) != 'undefined' && classNamesInContext !== null
                        && classNamesInContext.indexOf('listening') != -1)) {
                        recordTimer = setInterval(function () {
                            // Get updated list of class attached with interacted mic. As interval having its own running context
                            var updatedClassNames = micButtonInteracted.className;

                            // Trigger a click to reset and end recording. As user did not end manually.
                            if (typeof (updatedClassNames) != 'undefined' && updatedClassNames !== null
                                && updatedClassNames.indexOf('listening') != -1) {
                                micButtonInteracted.click();
                            }

                            // clear the record timer interval
                            uvsClearTimer(recordTimer);
                            recordTimer = false;
                        }, uvsMicListenAutoTimeoutDuration);
                    }

                    if (recognizing) {
                        if (isAndroid == false) {
                            processEnd();
                            recognition.stop();
                        }
                    } else {
                        micBtn.classList.add('listening');
                        event.preventDefault();
                        tts_stop();
                        inputEl.value = finalTranscript = '';
                        recognizing = true;
                        recognition.lang = uvsSelectedLang;
                        recognition.start();
                        ignore_onend = false;
                    }
                });
            } else {
                //CODE FOR BROWSERS THAT DO NOT SUPPORT STT NATIVLY
                // MUST USE THE BUILT IN MICROPHONE
                micBtn.addEventListener(uvsMicEventToListen, function (event) {
                    if (uvsAnyOtherMicListening(micBtn.getAttribute('id')) === true) return;

                    // Deny recording if microphone is not accessible
                    if (!uvsAudioRecorder || !uvsAudioContext) {
                        uvsInitAudio(function (a) {
                            if (!uvsAudioRecorder || !uvsAudioContext) {
                                alert(uvsMessages['cantAccessMicrophone']);
                                return false;
                            } else {
                                listenEvent();
                            }
                        });
                    } else {
                        listenEvent();
                    }

                    function listenEvent() {
                        // If API system key is unavailable then acknowledge service unavailability and stop voice navigation.
                        if (!(typeof (uvsXApiKey) != 'undefined' && uvsXApiKey !== null)) {
                            tts(uvsAlternativeResponse['unavailable'], function () { });
                            return false;
                        }

                        // Get current list of class attached with interacted mic button
                        var micButtonInteracted = document.getElementById("universal-voice-search-button" + index);
                        var classNamesInContext = micButtonInteracted.className;

                        // Set interval for 20 seconds when mic is in reset/fresh state ie: when user clicked mic for recording to start
                        if (!(typeof (classNamesInContext) != 'undefined' && classNamesInContext !== null
                            && classNamesInContext.indexOf('listening') != -1)) {
                            recordTimer = setInterval(function () {
                                // Get updated list of class attached with interacted mic. As interval having its own running context
                                var updatedClassNames = micButtonInteracted.className;

                                // Trigger a click to reset and end recording. As user did not end manually.
                                if (typeof (updatedClassNames) != 'undefined' && updatedClassNames !== null
                                    && updatedClassNames.indexOf('listening') != -1) {
                                    micButtonInteracted.click();
                                }

                                // clear the record timer interval
                                uvsClearTimer(recordTimer);
                                recordTimer = false;
                            }, uvsMicListenAutoTimeoutDuration);
                        }

                        // User ending recording by clicking back mic
                        if (recognizing) {
                            // clear the record timer interval when user ending recording
                            uvsClearTimer(recordTimer);

                            // Stop recorder
                            uvsAudioRecorder.stop();

                            // Stop access to audio resource
                            uvsStopAudio();

                            //replace recording with mic icon
                            micBtn.classList.remove('listening');

                            micBtn.style.setProperty("color", "white");
                            inputEl.placeholder = uvsMessages['transcribeText'];

                            tts("", function () {
                                uvsAudioRecorder.getBuffers(function (buffers) {
                                    uvsAudioRecorder.exportWAV(function (blob) {
                                        uvsRecIndex++;

                                        stt(blob, false, function (request) {
                                            if (typeof(request) != 'undefined' && request.length != 0) {
                                                inputEl.value = request;

                                                tts(uvsAlternativeResponse['basic'], function(){
                                                    speechInputWrapper.submit();
                                                });
                                            } else {
                                                tts(uvsAlternativeResponse['notAudible'], function(){});
                                                inputEl.placeholder = uvsMessages['ask'];
                                            }
                                        });
                                    });
                                });
                            });

                            recognizing = false;
                            return;
                        } else {// User started recording by clicking mic
                            micBtn.classList.add('listening');
                            event.preventDefault();

                            tts_stop();
                            inputEl.value = finalTranscript = '';

                            recognizing = true;
                            uvsAudioRecorder.clear();
                            uvsAudioRecorder.record(micButtonInteracted);
                        }
                    }
                }, false); 
            }
        } catch (err) {  /* do nothing */ }
    });

    // Load dloating mic with search bar
    if (uvsFloatingMic && uvsFloatingMic === 'yes') {
        //############################# Floating mic - Widget ###################################
        let uvsDocFragment = document.createDocumentFragment();
        // Create root/widget wrapper
        let uvsWidgetWrapper = document.createElement('div');

        let uvsWrapperMicPositionClass = 'uvs-widget-wrapper-middle-right';
        let uvsChatWrapperMicPositionClass = 'uvs-widget-chat-wrapper-middle-right';
        let uvsMicPosition =  uvsSelectedMicPosition ? uvsSelectedMicPosition.toLowerCase() : 'middle right';
    
        switch (uvsMicPosition) {
            case 'middle left':
                uvsWrapperMicPositionClass = 'uvs-widget-wrapper-middle-left';
                uvsChatWrapperMicPositionClass = 'uvs-widget-chat-wrapper-middle-left';
                break;
            case 'top right':
                uvsWrapperMicPositionClass = 'uvs-widget-wrapper-top-right';
                uvsChatWrapperMicPositionClass = 'uvs-widget-chat-wrapper-top-right';
                break;
            case 'top left':
                uvsWrapperMicPositionClass = 'uvs-widget-wrapper-top-left';
                uvsChatWrapperMicPositionClass = 'uvs-widget-chat-wrapper-top-left';
                break; 
            case 'bottom right':
                uvsWrapperMicPositionClass = 'uvs-widget-wrapper-bottom-right';
                uvsChatWrapperMicPositionClass = 'uvs-widget-chat-wrapper-bottom-right';
                break;
            case 'bottom left':
                uvsWrapperMicPositionClass = 'uvs-widget-wrapper-bottom-left';
                uvsChatWrapperMicPositionClass = 'uvs-widget-chat-wrapper-bottom-left';
                break;
            default:
                uvsWrapperMicPositionClass = 'uvs-widget-wrapper-middle-right';
                uvsChatWrapperMicPositionClass = 'uvs-widget-chat-wrapper-middle-right';
        }

        uvsWidgetWrapper.setAttribute('class', 'uvs-widget-wrapper '+ uvsWrapperMicPositionClass +'');

        // Create chat wrapper
        let uvsWidgetChatWrapper = document.createElement('div');
        uvsWidgetChatWrapper.setAttribute('class', 'uvs-widget-chat-wrapper '+ uvsChatWrapperMicPositionClass + '');

        // ############################ Widget Fields (Input section) ############################
        // Create widget text field and mic (Input Section)
        let uvsWidgetField = document.createElement('div');
        uvsWidgetField.setAttribute('class', 'uvs-widget-field');

        // Create mic icon wrapper
        let uvsWidgetMic = document.createElement('a');
        uvsWidgetMic.setAttribute('id', 'uvsWidgetMic');
        uvsWidgetMic.setAttribute('class', 'uvs-widget-button');

        // Create and append mic icon/image to mic wrapper
        let uvsWidgetMicImg = document.createElement('img');
        uvsWidgetMicImg.setAttribute('src', uvsImagesPath + 'uvs-widget-mic-black.svg');
        uvsWidgetMic.appendChild(uvsWidgetMicImg);

        // Create button wrapper next to input text field
        let uvsWidgetSearchBtn = document.createElement('a');
        uvsWidgetSearchBtn.setAttribute('id', 'uvsWidgetSearchBtn');

        // Create and append search button to button wrapper
        let uvsWidgetSearchBtnEl = document.createElement('button');
        uvsWidgetSearchBtnEl.setAttribute('class', 'uvs-widget-form-submit-btn');
        uvsWidgetSearchBtnEl.setAttribute('type', 'submit');
        uvsWidgetSearchBtnEl.setAttribute('alt', 'Go');
        uvsWidgetSearchBtnEl.setAttribute('title', 'Search');
        uvsWidgetSearchBtn.appendChild(uvsWidgetSearchBtnEl);

        // Create form for widget
        let uvsWidgetForm = document.createElement('form');
        uvsWidgetForm.setAttribute("class", "uvs-widget-form");

        if (formElementForWidget !== null) {
            uvsWidgetForm.action = formElementForWidget.action;
            uvsWidgetForm.method = formElementForWidget.method;
        } else {
            uvsWidgetForm.action = uvsGetCurrentHostURL() + '/';
            uvsWidgetForm.method = "get";
        }

        // Create input text field 
        let uvsWidgetSearch = document.createElement('input');
        uvsWidgetSearch.setAttribute('id', 'uvsWidgetSearch');
        uvsWidgetSearch.setAttribute('class', 'uvs-widget-search uvs-widget-search-text');
        uvsWidgetSearch.setAttribute('name', 'uvs-widget-search');
        uvsWidgetSearch.setAttribute('placeholder', uvsWidgetMessages['placeholder']);
        uvsWidgetSearch.setAttribute('name', 's');

        uvsWidgetForm.appendChild(uvsWidgetSearch);
        uvsWidgetForm.appendChild(uvsWidgetSearchBtn);

        // Append mic and form to widget field section (input section)
        uvsWidgetField.appendChild(uvsWidgetMic);
        uvsWidgetField.appendChild(uvsWidgetForm);

        // Append chat header, chat conversation and input fields to widget chat wrapper
        uvsWidgetChatWrapper.appendChild(uvsWidgetField);

        // ################################ Widget Toggle button #########################
        // Create a widget toggle button wrapper
        let uvsWidgetToggleButton = document.createElement('a');
        uvsWidgetToggleButton.setAttribute('id', 'uvsWidgetToggleButton');
        uvsWidgetToggleButton.setAttribute('class', 'uvs-widget-button');

        // Create toggle button icon element
        let uvsWidgetIcon = document.createElement('div');
        uvsWidgetIcon.setAttribute('class', 'uvs-widget-icon uvs-widget-toggle-button uvs-toggle-btn-mic');

        // Append toggle button icon to toggle button wrapper
        uvsWidgetToggleButton.appendChild(uvsWidgetIcon);

        // Append chat wrapper and toggle button to widget wrapper
        uvsWidgetWrapper.appendChild(uvsWidgetChatWrapper);
        uvsWidgetWrapper.appendChild(uvsWidgetToggleButton);

        // Append widget to body
        uvsDocFragment.appendChild(uvsWidgetWrapper);
        document.body.appendChild(uvsDocFragment);

        // Listen event to show/hide widget
        uvsWidgetToggleButton.addEventListener('click', function(event){
            uvsToggleWidgetElements();
        });

        /*############################# Widget mic handling ################################*/
        // Setup recognition
        let widgetFinalTranscript = '';
        let widgetRecognizing     = false;
        let widget_final_transcript = "";
        let widget_ignore_onend;

        if ('webkitSpeechRecognition' in window) {
            let widgetRecognition = new webkitSpeechRecognition();
            widgetRecognition.continuous = true;
            widgetRecognition.interimResults = true;

            widgetRecognition.onstart = function () { 
                widgetRecognizing = true; 
            };

            widgetRecognition.onerror = function (event) {
                uvsWidgetMic.classList.remove('listening');
                widgetRecognizing = false;

                if (event.error == 'no-speech') {
                    tts(uvsAlternativeResponse['notAudible'], function(){});
                    widget_ignore_onend = true;
                    uvsWidgetSearch.placeholder = uvsAlternativeResponse['notAudible'];
                }

                if (event.error == 'audio-capture') {
                    widget_ignore_onend = true;
                    uvsWidgetSearch.placeholder = uvsMessages['micNotAccessible'];
                }

                if (event.error == 'not-allowed') {
                    widget_ignore_onend = true;
                    uvsWidgetSearch.placeholder = uvsMessages['browserDenyMicAccess'];
                }
            };

            function widgetProcessEnd() {
                widgetRecognizing = false;

                if (widget_ignore_onend) { return; }

                widgetFinalTranscript = widget_final_transcript;   
                uvsWidgetMic.classList.remove('listening');
                
                if (typeof(widgetFinalTranscript) != 'undefined' && widgetFinalTranscript.length != 0) {
                    uvsWidgetSearch.value = widgetFinalTranscript;
                    
                    tts(uvsAlternativeResponse['basic'], function () {
                        uvsWidgetForm.submit();
                    });
                } else {
                    tts(uvsAlternativeResponse['notAudible'], function(){});
                    uvsWidgetSearch.placeholder = uvsMessages['ask'];
                }

            }

            widgetRecognition.onend = function () { 
                if (isAndroid) { widgetProcessEnd(); }
            };

            widgetRecognition.onresult = function (event) {
                let interim_transcript = '';

                if (typeof (event.results) == 'undefined') {
                    widgetRecognition.onend = null;
                    widgetRecognition.stop();
                    uvsWidgetSearch.placeholder = uvsMessages['unableToHear'];
                    tts(uvsAlternativeResponse['micConnect'], function () { });
                    return;
                }

                let eventResultsLength = event.results.length;

                for (let i = event.resultIndex; i < eventResultsLength; ++i) {
                    if (event.results[i].isFinal) {
                        widget_final_transcript = event.results[i][0].transcript;

                        if (isAndroid == false) {
                            widgetProcessEnd();
                            widgetRecognition.stop();
                        }                        
                    } else {
                        interim_transcript += event.results[i][0].transcript;
                    }
                }
            };

            uvsWidgetMic.addEventListener(uvsMicEventToListen, function (event) {
                if (uvsAnyOtherMicListening(uvsWidgetMic.getAttribute('id'), uvsWidgetMic) === true) return;

                // Deny recording if microphone is not accessible
                let classNamesInContext = uvsWidgetMic.className;

                //We need to make sure that we keep track which timer is firing!
                //To keep do so we start the timer in this function and we keep an ID in local storage to ensure we know if we are still active
                function uvsSetTimeOut(uvsMyTimerid, uvsMicBtn, uvsTimeOutTime) {
                    return new Promise(function (resolve) {
                        setTimeout(function () {
                            var updatedClassNames = uvsMicBtn.className;

                            // Trigger a click to reset and end recording. As user did not end manually.
                            if (typeof (updatedClassNames) != 'undefined' && updatedClassNames !== null
                                && updatedClassNames.indexOf('listening') != -1 && uvsMyTimerid == uvsTimerID) {
                                uvsMicBtn.click();
                            }
                            resolve('resolved');
                        }, uvsTimeOutTime);
                    });
                }

                // Set interval for 20 seconds when mic is in reset/fresh state ie: when user clicked mic for recording to start
                if (!(typeof (classNamesInContext) != 'undefined' && classNamesInContext !== null
                    && classNamesInContext.indexOf('listening') != -1)) {
                    uvsTimerID = uvsTimerID + 1;
                    uvsSetTimeOut(uvsTimerID, uvsWidgetMic, uvsMicListenAutoTimeoutDuration);
                }

                if (widgetRecognizing) {
                    if (isAndroid == false) {
                        widgetProcessEnd();
                        widgetRecognition.stop();
                    }
                } else {
                    uvsWidgetMic.classList.add('listening');
                    event.preventDefault();
                    tts_stop();
                    widgetFinalTranscript = '';
                    widgetRecognizing = true;
                    widgetRecognition.lang = uvsSelectedLang;
                    widgetRecognition.start();
                    widget_ignore_onend = false;
                }
            });
        } else {
            //CODE FOR BROWSERS THAT DO NOT SUPPORT STT NATIVLY
            // MUST USE THE BUILT IN MICROPHONE
            uvsWidgetMic.addEventListener(uvsMicEventToListen, function (event) {
                if (uvsAnyOtherMicListening(uvsWidgetMic.getAttribute('id'), uvsWidgetMic) === true) return;

                // Deny recording if microphone is not accessible
                if (!uvsAudioRecorder || !uvsAudioContext) {
                    uvsInitAudio(function (a) {
                        if (!uvsAudioRecorder || !uvsAudioContext) {                        
                            uvsWidgetSearch.placeholder = uvsMessages['micNotAccessible'];
                            return false;
                        } else {
                            widgetListenEvent();
                        }
                    });
                } else {
                    widgetListenEvent();
                }

                function widgetListenEvent() {
                    // If API system key is unavailable then acknowledge service unavailability and stop voice navigation.
                    if (!(typeof (uvsXApiKey) != 'undefined' && uvsXApiKey !== null)) {
                        tts(uvsAlternativeResponse['unavailable'], function () { });
                        return false;
                    }

                    // Get current list of class attached with interacted mic button
                    let classNamesInContext = uvsWidgetMic.className;

                    //We need to make sure that we keep track which timer is firing!
                    //To keep do so we start the timer in this function and we keep an ID in local storage to ensure we know if we are still active
                    function uvsSetTimeOut(uvsMyTimerid, uvsMicBtn, uvsTimeOutTime) {
                        return new Promise(function (resolve) {
                            setTimeout(function () {
                                var updatedClassNames = uvsMicBtn.className;

                                // Trigger a click to reset and end recording. As user did not end manually.
                                if (typeof (updatedClassNames) != 'undefined' && updatedClassNames !== null
                                    && updatedClassNames.indexOf('listening') != -1 && uvsMyTimerid == uvsTimerID) {
                                    uvsWidgetMic.click();
                                }
                                resolve('resolved');
                            }, uvsTimeOutTime);
                        });
                    }

                    // Set interval for 20 seconds when mic is in reset/fresh state ie: when user clicked mic for recording to start
                    if (!(typeof (classNamesInContext) != 'undefined' && classNamesInContext !== null 
                        && classNamesInContext.indexOf('listening') != -1)) {
                        uvsTimerID = uvsTimerID + 1;
                        uvsSetTimeOut(uvsTimerID, uvsWidgetMic, uvsMicListenAutoTimeoutDuration);
                    }

                    // User ending recording by clicking back mic
                    if (widgetRecognizing) {
                        // Stop recorder
                        uvsAudioRecorder.stop();

                        // Stop access to audio resource
                        uvsStopAudio();

                        //replace recording with mic icon
                        uvsWidgetMic.classList.remove('listening');

                        uvsWidgetSearch.placeholder = uvsMessages['transcribeText'];

                        tts("", function () {
                            uvsAudioRecorder.getBuffers(function (buffers) {
                                uvsAudioRecorder.exportWAV(function (blob) {
                                    uvsRecIndex++;
                                
                                    stt(blob, false, function (request) {
                                        if (typeof(request) != 'undefined' && request.length != 0) {
                                            uvsWidgetSearch.value = request;

                                            tts(uvsAlternativeResponse['basic'], function(){
                                                uvsWidgetForm.submit();
                                            });
                                        } else {
                                            tts(uvsAlternativeResponse['notAudible'], function(){});
                                            uvsWidgetSearch.placeholder = uvsMessages['ask'];
                                        }
                                    });
                                });
                            });
                        });

                        widgetRecognizing = false;
                        return;
                    } else {// User started recording by clicking mic
                        uvsWidgetMic.classList.add('listening');
                        event.preventDefault();

                        tts_stop();
                        widgetFinalTranscript = '';

                        widgetRecognizing = true;
                        uvsAudioRecorder.clear();
                        uvsAudioRecorder.record(uvsWidgetMic);
                    }
                }
            }, false); 
        }

        /*###############################################################################*/

        /**
         * Function to toggle class of the HTML element
         *
         * @param {elmSelector - String} : CSS Selector
         * @param {nameOfClass - String} : Class name to add/remove
         */
        function uvsToggleClass(elmSelector, nameOfClass) {
            if (!(typeof(elmSelector) != 'undefined' && elmSelector != null && elmSelector.length != 0)) return false;

            let element = document.querySelector(elmSelector);

            if (element.classList) { 
                element.classList.toggle(nameOfClass);
            } else {
                // For IE9

                let classes = element.className.split(" ");
                let i = classes.indexOf(nameOfClass);

                if (i >= 0) {
                    classes.splice(i, 1);
                } else {
                    classes.push(nameOfClass);
                    element.className = classes.join(" "); 
                }
            }
        }

        /**
         * Function to toggle chat and links
         */
        function uvsToggleWidgetElements() {
            uvsToggleClass('.uvs-widget-toggle-button', 'uvs-toggle-btn-mic');
            uvsToggleClass('.uvs-widget-toggle-button', 'uvs-toggle-btn-close');
            uvsToggleClass('.uvs-widget-toggle-button', 'uvs-widget-active');
            uvsToggleClass('.uvs-widget-toggle-button', 'uvs-widget-visible');
            uvsToggleClass('#uvsWidgetToggleButton', 'uvs-widget-float');
            uvsToggleClass('.uvs-widget-chat-wrapper', 'uvs-widget-visible');
            uvsToggleClass('.uvs-widget-button', 'uvs-widget-visible');
        }
    }
})();