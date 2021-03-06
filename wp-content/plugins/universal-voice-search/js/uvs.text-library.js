// *****************************************************************************************************
// *******              speak2web UNIVERSAL VOICE SEARCH                                    ***********
// *******               AI Service requires subcriptions                                    ***********
// *******               Get your subscription at                                            ***********
// *******                    https://speak2web.com/plugin#plans                             ***********
// *******               Need support? https://speak2web.com/support                         ***********
// *******               Licensed GPLv2+                                                     ***********
//******************************************************************************************************


// Get selected language for plugin from plugin's settings/configurations
var uvsTypeOfSelectedLanguage = (typeof(uvsSelectedLanguage) != 'undefined' && uvsSelectedLanguage !== null) ? uvsSelectedLanguage.trim() : 'English';

// English: Language param for speech/speaking
var uvsSelectedLang = 'en-US';

var uvsAlternativeResponse = '';

var uvsMessages = '';
var uvsWidgetMessages = { 'placeholder': '' };

switch (uvsTypeOfSelectedLanguage.toLowerCase()) {
    case 'german':
        uvsSelectedLang = 'de-DE';

        uvsAlternativeResponse = {
            'basic'  : 'Lass mich danach suchen.', 
            'randomLib' : [
                "Eine Sekunde bitte.", 
                "Ich bin dabei.", 
                "Kein Problem.", 
                "Einen Moment, ich brauche eine kurze Pause.", 
                "Sie scheinen zu hart zu arbeiten. Holen Sie sich einen Kaffee, und ich werde es für Sie nachschlagen.", 
                "Ich komme gleich.", 
                "Ich werde mein Bestes geben", 
                "Alles für dich. Ich werde gleich loslegen.", 
                "Daran zu arbeiten. Einen Moment bitte.", 
                "Beep - Beep - Beep, nur ein Scherz. Einen Moment bitte."
            ],
            'micConnect' : 'Es tut mir leid, aber ich kann nicht auf Ihr Mikrofon zugreifen. Bitte schließen Sie ein Mikrofon an oder geben Sie bei Bedarf Ihre Frage ein.',
            'unavailable' : 'Die Sprachnavigation ist derzeit nicht verfügbar. Bitte versuchen Sie es nach einiger Zeit erneut.',
            'notAudible': 'Ich kann dich nicht hören',
            'simonShortIntro': "Hallo, ich heiße Simon. Ich bin Ihr virtueller Webassistent."
        };

        // German: Common messages/text 
        uvsMessages = {
            'micNotAccessible': 'Ich kann nicht auf das Mikrofon zugreifen.',
            'browserDenyMicAccess': "Ihre Browsersicherheit erlaubt mir nicht, auf das Mikrofon zuzugreifen.",
            'transcribeText': ' Transkribieren ....',
            'unableToHear': 'Ich kann dich nicht hören.',
            'ask': ' Sage es noch einmal ....',
            'cantAccessMicrophone' : 'kann nicht auf das Mikrofon zugreifen',
        }

        uvsWidgetMessages.placeholder = 'Geben Sie eine Abfrage ein';
        break;

    case 'portuguese':
        uvsSelectedLang = 'pt-BR';

        uvsAlternativeResponse = {
            'basic'  : '',
            'randomLib' : ["","","", "","", "", "", "","",""],
            'micConnect' : 'I am sorry but I am unable to access your microphone. Please connect a microphone or you can also type your question if needed.',
            'unavailable' : 'Voice navigation is currently unavailable. Please try again after some time.',
            'notAudible': 'I am unable to hear you',
            'simonShortIntro': ""
        };

        uvsMessages = {    
            'micNotAccessible': 'Não consigo acessar o microfone.',
            'browserDenyMicAccess': "A segurança do seu navegador não me permite acessar o microfone.",
            'transcribeText': ' Transcrição ....',
            'unableToHear': 'Eu sou incapaz de ouvir você.',
            'ask': ' Diga isso de novo ....',
            'cantAccessMicrophone': 'não consigo acessar o microfone'
        };

        uvsWidgetMessages.placeholder = 'Digite uma consulta';
        break;

    case 'chinese':
        uvsSelectedLang = 'zh-CN';

        uvsAlternativeResponse = {
            'basic'  : '',
            'randomLib' : ["","","", "","", "", "", "","",""],
            'micConnect' : 'I am sorry but I am unable to access your microphone. Please connect a microphone or you can also type your question if needed.',
            'unavailable' : 'Voice navigation is currently unavailable. Please try again after some time.',
            'notAudible': 'I am unable to hear you',
            'simonShortIntro': ""
        };

        uvsMessages = {    
            'micNotAccessible': '我无法使用麦克风。',
            'browserDenyMicAccess': "您的浏览器安全性不允许我访问麦克风。",
            'transcribeText': ' 抄写....',
            'unableToHear': '我听不到您的声音。',
            'ask': ' 再说一遍 ....',
            'cantAccessMicrophone': '无法访问麦克风'
        };

        uvsWidgetMessages.placeholder = '输入查询';     
        break;

    case 'french':
        uvsSelectedLang = 'fr-FR';

        uvsAlternativeResponse = {
            'basic'  : '',
            'randomLib' : ["","","", "","", "", "", "","",""],
            'micConnect' : 'I am sorry but I am unable to access your microphone. Please connect a microphone or you can also type your question if needed.',
            'unavailable' : 'Voice navigation is currently unavailable. Please try again after some time.',
            'notAudible': 'I am unable to hear you',
            'simonShortIntro': ""
        };

        uvsMessages = {    
            'micNotAccessible': 'Je ne parviens pas à accéder au microphone.',
            'browserDenyMicAccess': "La sécurité de votre navigateur ne me permet pas d'accéder au micro.",
            'transcribeText': ' Transcription ....',
            'unableToHear': 'Je suis incapable de vous entendre.',
            'ask': ' Dis le encore ....',
            'cantAccessMicrophone': 'ne peut pas accéder au microphone'
        };

        uvsWidgetMessages.placeholder = 'Tapez une requête';
        break;

    case 'japanese':
        uvsSelectedLang = 'ja-JP';

        uvsAlternativeResponse = {
            'basic'  : '',
            'randomLib' : ["","","", "","", "", "", "","",""],
            'micConnect' : 'I am sorry but I am unable to access your microphone. Please connect a microphone or you can also type your question if needed.',
            'unavailable' : 'Voice navigation is currently unavailable. Please try again after some time.',
            'notAudible': 'I am unable to hear you',
            'simonShortIntro': ""
        };

        uvsMessages = {    
            'micNotAccessible': 'マイクにアクセスできません。',
            'browserDenyMicAccess': "ブラウザのセキュリティにより、マイクにアクセスできません。",
            'transcribeText': '転写....',
            'unableToHear': 'あなたの声が聞こえません。',
            'ask': ' もう一度言ってください ....',
            'cantAccessMicrophone': 'マイクにアクセスできません'
        };

        uvsWidgetMessages.placeholder = 'クエリを入力します';
        break;

    case 'korean':
        uvsSelectedLang = 'ko-KR';

        uvsAlternativeResponse = {
            'basic'  : '',
            'randomLib' : ["","","", "","", "", "", "","",""],
            'micConnect' : 'I am sorry but I am unable to access your microphone. Please connect a microphone or you can also type your question if needed.',
            'unavailable' : 'Voice navigation is currently unavailable. Please try again after some time.',
            'notAudible': 'I am unable to hear you',
            'simonShortIntro': ""
        };

        uvsMessages = {    
            'micNotAccessible': '마이크에 액세스 할 수 없습니다.',
            'browserDenyMicAccess': "브라우저 보안으로 마이크에 액세스 할 수 없습니다.",
            'transcribeText': ' 전사 ....',
            'unableToHear': '나는 당신을들을 수 없습니다.',
            'ask': ' 다시 말해봐 ....',
            'cantAccessMicrophone': '마이크에 액세스 할 수 없습니다'
        };

        uvsWidgetMessages.placeholder = '검색어를 입력하십시오';
        break;

    case 'spanish':
        uvsSelectedLang = 'es-ES';

        uvsAlternativeResponse = {
            'basic'  : '',
            'randomLib' : ["","","", "","", "", "", "","",""],
            'micConnect' : 'I am sorry but I am unable to access your microphone. Please connect a microphone or you can also type your question if needed.',
            'unavailable' : 'Voice navigation is currently unavailable. Please try again after some time.',
            'notAudible': 'I am unable to hear you',
            'simonShortIntro': ""
        };

        uvsMessages = {    
            'micNotAccessible': 'No puedo acceder al micrófono.',
            'browserDenyMicAccess': "La seguridad de su navegador no me permite acceder al micrófono.",
            'transcribeText': ' Transcribiendo ...',
            'unableToHear': 'No puedo escucharte.',
            'ask': ' Dilo otra vez ....',
            'cantAccessMicrophone': 'no puedo acceder al micrófono'
        };

        uvsWidgetMessages.placeholder = 'Escribe una consulta';
        break;   

    default:
        uvsSelectedLang = (uvsTypeOfSelectedLanguage.toLowerCase() === 'british english') ? 'en-GB' : 'en-US';

        uvsAlternativeResponse = {
            'basic'  : 'Let me search for that.', 
            'randomLib' : [
                "Just a second please.",
                "I am on it.", 
                "No problem.",
                "Just a moment, I need a brief rest.",
                "You seem to work too hard. Get your self a coffee, and I will look it up for you.",
                "Coming right up.",
                "I will do my best","Anything for you. I will get right on it.",
                "Working on it. One moment please.", 
                "Beep - Beep - Beep, just kidding. One moment please."
            ],
            'micConnect' : 'I am sorry but I am unable to acces your microphone. Please connect a microphone or you can also type your question if needed.',
            'unavailable' : 'Voice navigation is currently unavailable. Please try again after some time.',
            'notAudible': 'I am unable to hear you',
            'simonShortIntro': "Hello, my name is Simon. I am your web virtual assistant."
        };

        uvsMessages = {     
            'micNotAccessible': 'I am unable to access the microphone.',
            'browserDenyMicAccess': "Your browser security doesn't allow me to access the mic.",
            'transcribeText': ' Transcribing ....',
            'unableToHear': 'I am unable to hear you.',
            'ask': ' Say it again ....',
            'cantAccessMicrophone' : 'can"t access the microphone'
        };

        uvsWidgetMessages.placeholder = 'Type a query';
}
