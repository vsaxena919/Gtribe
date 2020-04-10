=== Universal Voice Search ===
Contributors:      mspanwa2   
Tags:              voice search, iOS voice search, Microsoft Edge voice search, multi platform voice search, voice, AI, speech, FireFox voice search, api,  
Requires at least: 2.6  
Tested up to:      5.3.2  
Requires PHP:      5.3
Stable tag:        1.1.4 
License:           GPLv2 or later  
License URI:       http://www.gnu.org/licenses/gpl-2.0.html  

Add an universal, multi lingual voice to web pages. This plugin will add voice search ability to all web pages. Simply click the microphone and dictate your search terms. 
The plugin will automatically detect end of speech and submit your search to the web page.

== Description ==

This plugin adds voice search to every web page. Once the plugin is installed a microphone symbol is added to every search bar allowing the user to dictate their search rather than typing it.
This is particularly helpful for users on mobile devices. Being able to dictate the search terms greatly improves the mobile experience.
The plugin is FREE to use on Chrome browsers. 
The plugin also supports all other modern platforms and browsers such as iOS, MacOS, Linux, Windows, Safari, FireFox, MS Edge etc. For browsers other than Chrome an external 
speech to text service is required. This service is a paid service and requires a valid license key to be entered.
Without a valid license key the functionality is only available on Chrome browsers. All other platforms will NOT display voice capabilities.

To obtain a valid license key to support all platform and browsers please visit our web page and select a plan that suits your needs.
Simply go to (https://speak2web.com/plugin) and select your plan. 


== Supported Languages ==
This plugin support multiple languges. 
PLEASE MAKE SURE TO SELECT the languge that matches your web page.
Languages:
*   English
*   German
*   Portuguese
*   Chinese
*   French
*   Japanese
*   Korean
*   Spanish


== Installation ==

= Manual Installation =

1. Navigate to your WordPress admin console.
2. Click on "Plugins/Add New" on the right-hand navigation bar
3. Either search for the "universal Voice search" plugin on the wordpress.org plugin store or upload the ZIP file obtained from https://speak2web.com
4. Activate the plugin
Optional install license key to support all browsers and platforms
1. Navigate to "Settings/Universal Voice Search" on right hand navigation bar
2. Copy the license key you obtained from https://speak2web.com into the "license key" field and press "Save Settings" button
6. Ensure the license key activated correctly

Detailed instructions on how to install and configure the plugin can be found here:
[Support Web Page:] (https://speak2web.com/support/) 

== Screenshots ==
1. Voice added to search field - Mic Symbol in the search field
2. Voice Search on Android
3. Voice Search on iOS
4. Config page  
5. Floating Mic
6. Language Selection


== Frequently Asked Questions ==

= How do I get a license key =

You can select a plan that fits your budget right here [Plans] (https://speak2web.com/plugin/#plan)


= Why do I need a license key? =

Many browsers and platforms do not support speech to text capabilities. In order to give the user a consistent voice experience across all platforms and browsers
an external, paid speech to text service needs to be engaged.
The plugin uses enterprise class AI technology for speech to text services and more to deliver a consistent experience across all platforms.
A license key to enable this functionality can be obtained at our web store.
You can see more details at the speak2web web page [Plugin Details] (https://speak2web.com/plugin/#plan)

= How does the plugin work =
The plugin adds a microphone symbol to every search bar on the web page. The plugin will engaged the microphone, transcribe the spoken words into the search bar and engage the
web page's search functionality. The plugin is able to detect the end of speech on all platforms allowing the user to simply click the microphone to start the recording and wait for the search 
to take place.

= Do I need a security certificate for my web page? =

It is highly recommended to have a certificate and use a https URL. Most web browsers do not allow microphone access unless the URL is secure.


== Example Usage ==
1. The microphone button added to the search form by the plugin.
2. You can see a couple of examples right here: [Videos] (https://speak2web.com/video/) 

=== CLOUD SERVICES USED / CLOUD APIs Called ===
This plugin accesses a number of cloud services to perform the voice dialog functionality. In general, the API's accessed are either speak2web cloud services hosted in AWS or IBM Watson Cloud Services. 
The detailed privacy implications can be found below.

== Cloud Calls Issued by the Plugin ==
- During Install / Setup -
The first cloud call will take place when the license key is being entered and activated. This call will invoke a speak2web cloud service to validate the license key and provision cloud resources for the AI 
to be used.

- On loading of the plugin on a page -
Every time the plugin is loaded onto a page, a call is issued to the a speak2web service to retrieve a valid token to access IBM cloud services

- when a voice request is being issued -
When the user clicks the microphone to issue a voice command, additional cloud calls are being placed to IBM Watson Cloud STT to transcribe the recorded audio
To process the request the plugin will call a speak2web cloud service to process the natural language request and prepare a response.


=== COMPLIANCE WITH LOCAL LAWS ===
THE USER OF THIS PLUGIN AND THE ASSOCIATED SERVICE IS RESPONSIBLE TO ENSURE COMPLIANCE WITH APPLICABLE LAWS INCLUDING PRIVACY LAWS.
speak2web is making an effort to ensure privacy of the users of this service. As such, this plugin and the associated service DO NOT correlate IP Addresses or other personal data like browser history etc. to 
the transcript of the voice interaction. The speak2web does NOT store voice recordings, but we do retain anonymous transcript of the dialog in logs for a period of time.
More detail about the service utilized and the privacy statements related to these services can be found below.


=== Terms of Use and usage of 3rd Party Services ===
This plugin invokes a number of cloud services to perform the speech to text function (STT), analyses natural language requests and perform a natural dialog.
The services are all provided through your speak2web subscription service. By using the speak2web voice dialog-navigation service you also agree to the terms of use and privacy terms of the 
following 3rd party services:

Amazon Web Services:
++++++++++++++++++++
speak2web is hosting its cloud services in AWS infrastructure. We are utilizing services such AWS Gateway API, AWS compute Services, AWS storage and AWS database services.
[AWS Services:] (https://aws.amazon.com)
[The AWS privacy terms can be reviewed here:] (https://aws.amazon.com/privacy/)  

IBM WATSON Cloud Services:
++++++++++++++++++++++++++
speak2web is utilizing the following IBM Cloud Services as part of this plugin:
[IBM STT:] (https://www.ibm.com/watson/services/speech-to-text/) 

[The Terms of IBM Cloud Services] (https://cloud.ibm.com/docs/overview/terms-of-use?topic=overview-terms#terms_details)  
[IBM Cloud Service Privacy Statement] (https://cloud.ibm.com/docs/overview/terms-of-use?topic=overview-terms#privacy_policy)  

speak2web Voice Search Service:
++++++++++++++++++++++++++++++++++++++++++
This plugin requires a subscription to the speak2web ["WP Voice Search Service"] (https://speak2web.com/plugin/#plan)
The subscription give access to the speak2web voice service which is utilizing the 3rd party services listed above.
By subscribing to this service, the user agrees to the privacy terms of speak2web and the 3rd party services listed above.

VOICE RECORDING --- CANNOT BE PERSONALLY IDENTFIED:
+++++++++++++++++++++++++++++++++++++++++++++++++++
The cloud service does stream audio data to the IBM Watson STT service while the recording is active, but we DO NOT keep a copy of the audio recording. 
The transcript of the spoken request is being kept in logs for a period of time but CANNOT BE RELATED to the user it came from. The service DOES NOT track IP addressed or other
personally, identifiable data. The transcript remains anonymous in the logs and CAN NOT be associated with the person it came from.

[speak2web terms of use] (https://speak2web.com/voice-dialog-service-terms/)
[speak2web privacy policy] (https://speak2web.com/privacy-policy/)


== Changelog ==

= 1.0.0 =
* Initial version

= 1.0.1 =
* Fix lincese activation issue


= 1.0.2 =
* Reduce warning message on WP dashboard

= 1.0.3 =
* Removed warning message WP plugin page

= 1.1.0 =
* Added Floating Microphone for dynamic search bar
* Added support for additional languages:
*   German
*   Portuguese
*   Chinese
*   French
*   Japanese
*   Korean
*   Spanish

= 1.1.1 =
* Added support for British English
* Added warning for missing SSL cert

= 1.1.2 =
* Fixed Formating Issue where Mic is not centered correctly

= 1.1.3 =
* Allow floating Microphone position to change - 6 possible positions to choose from
* Fixed formating issue of disappearing mic

= 1.1.4 =
* Auto detect language settings and default search languge to page settings
