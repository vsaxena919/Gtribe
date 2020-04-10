<?php
defined( 'WPINC' ) or die;

class Universal_Voice_Search_Plugin extends WP_Stack_Plugin2 {

    /**
     * @var self
     */
    public static $plugin_directory_path = null;
    public static $uvs_ios = false;
    public static $uvs_url = "";
    public static $is_chrome = false;
    public static $uvs_license_key = "";
    public static $uvs_api_access_key = null;
    public static $uvs_admin_notice_logo = "";
    public static $uvs_selected_language = "English";
    public static $uvs_floating_mic_position = "Middle Right";
    public static $uvs_file_type  = '.min';
    //public static $uvs_file_type = ''; // For debugging

    /*
     * Note: This map of language name as value (Eg: English) maps to value being saved to DB for plugin language option on settings page
     *
     * The keys of map (eg: en_US) are taken into account as of Wordpress version 5.3.2
     */
    public static $uvs_auto_detect_lang_map = array(
        'en_US' => 'English',
        'en_GB' => 'British English',
        'de_DE' => 'German',
        'pt_PT' => 'Portuguese',
        'zh_CN' => 'Chinese',
        'zh_TW' => 'Chinese',
        'fr_FR' => 'French',
        'ja'    => 'Japanese',
        'ja_JP' => 'Japanese',
        'ko_KR' => 'Korean',
        'es_ES' => 'Spanish'
    );

    /**
     * Plugin version.
     */
    const VERSION = '1.1.4';
    
    /**
     * Constructs the object, hooks in to `plugins_loaded`.
     */
    protected function __construct()
    {
        // Get database values
        self::$uvs_license_key = get_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['license_key'], null);
        self::$uvs_license_key = self::uvs_sanitize_variable_for_local_script(self::$uvs_license_key);

        // Get API access key.
        self::$uvs_api_access_key = get_option('uvs_api_system_key', null);
        self::$uvs_api_access_key = self::uvs_sanitize_variable_for_local_script(self::$uvs_api_access_key);

        self::$uvs_selected_language = get_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['selected_language'], null);
        self::$uvs_selected_language = self::uvs_sanitize_variable_for_local_script(self::$uvs_selected_language);

        // Detect OS by user agent
        $iPod   = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
        $iPad   = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
        $chrome_browser = stripos($_SERVER['HTTP_USER_AGENT'],"Chrome");

        if (!($iPod == false && $iPhone == false && $iPad == false)) { /*self::$uvs_ios = true;*/ }

        if ($chrome_browser != false) { self::$is_chrome = true; }

        $this->hook( 'plugins_loaded', 'add_hooks' );
    }

    /**
     * Adds hooks.
     */
    public function add_hooks()
    {
        $this->hook( 'init' );
        $this->hook( 'admin_enqueue_scripts', 'enqueue_admin_scripts' );

        if ( self::$is_chrome == true 
            || (self::$is_chrome == false && !empty(self::$uvs_license_key) && !empty(self::$uvs_api_access_key)) ) {
            $this->hook( 'wp_enqueue_scripts', 'enqueue_frontend_scripts' );
        }

        // Register action to hook into admin_notices to display dashboard notice for non-HTTPS site
        if (is_ssl() == false) {
            add_action( 'admin_notices', function(){
    ?>          <div class="notice notice-error is-dismissible">
                    <p> <?php echo self::$uvs_admin_notice_logo; ?>
                        <br/> <?php echo UVS_LANGUAGE_LIBRARY['other']['nonHttpsNotice']; ?>
                    </p>        
                </div>
    <?php
            });
        }

        // Register the STT service call action
        add_action ( 'wp_ajax_' . 'uvs_log_service_call', array($this, 'uvs_log_service_call'));
        add_action ( 'wp_ajax_nopriv_' . 'uvs_log_service_call', array($this, 'uvs_log_service_call'));

        // Register action to hook into admin_notices to display dahsboard notices when license key is missing or invalid
        if (empty(self::$uvs_license_key) || empty(self::$uvs_api_access_key)) {
            add_action( 'admin_notices', array($this, 'go_pro_notice'));          
        } 
    }

    /**
     * Method as action to invoke when license key is missing
     */
    public function go_pro_notice() {
    ?>
        <div class="notice notice-warning is-dismissible">
            <p> <?php echo self::$uvs_admin_notice_logo; ?>
                <br/>
                <a target="blank" href="https://speak2web.com/plugin/#plan"><?php echo UVS_LANGUAGE_LIBRARY['other']['goProNotice']['goPro']; ?></a><?php echo UVS_LANGUAGE_LIBRARY['other']['goProNotice']['supportMoreBrowsers']; ?>
            </p>
        </div>
    <?php
    }   

    /**
     * Initializes the plugin, registers textdomain, etc.
     * Most of WP is loaded at this stage, and the user is authenticated
     */
    public function init()
    {
        self::$uvs_url = $this->get_url();
        self::$uvs_admin_notice_logo = "<img style='margin-left: -7px;vertical-align:middle;width:110px; height: 36px;' src='".self::$uvs_url."images/speak2web_logo.png'/>|<b> Universal Voice Search</b>";

        // Get plugin directory path and add trailing slash if needed (For browser compatibility)
        self::$plugin_directory_path = plugin_dir_path(__DIR__);
        $trailing_slash = substr(self::$plugin_directory_path, -1);

        if ($trailing_slash != '/') { self::$plugin_directory_path .= '/'; }

        if ( isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'plugins.php' ) {
            add_filter( 'plugin_row_meta', array(&$this, 'custom_plugin_row_meta'), 10, 2);
        }

        $this->load_textdomain( 'universal-voice-search', '/languages' );

        // To enable floating mic by default (only when 'uvs_floating_mic' option is missing from DB)
        $is_uvs_floating_mic_exist = get_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['floating_mic']);

        if ($is_uvs_floating_mic_exist === false) {
            update_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['floating_mic'], 'yes');
        }

        // Get floating mic position from DB
        self::$uvs_floating_mic_position = get_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['floating_mic_position']);

        if (self::$uvs_floating_mic_position === false) {
            update_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['floating_mic_position'], 'Middle Right');
            self::$uvs_floating_mic_position = 'Middle Right';
        }
    }

    /**
     * Class method to broadcast 'settings' page data in 'uvs.speech-handler.js'
     */
    public static function broadcast_api_meta_data()
    {
        $stt_model = '&model=en-US_BroadbandModel';

        switch (strtolower(self::$uvs_selected_language)) {
            case 'german':
                $stt_model = '&model=de-DE_BroadbandModel';
                break;
            case 'british english':
                $stt_model = '&model=en-GB_BroadbandModel';
                break;
            case 'portuguese':
                $stt_model = '&model=pt-BR_BroadbandModel';
                break;
            case 'chinese':
                $stt_model = '&model=zh-CN_BroadbandModel';
                break;
            case 'french':
                $stt_model = '&model=fr-FR_BroadbandModel';
                break;
            case 'japanese':
                $stt_model = '&model=ja-JP_BroadbandModel';
                break;
            case 'korean':
                $stt_model = '&model=ko-KR_BroadbandModel';
                break;
            case 'spanish':
                $stt_model = '&model=es-ES_BroadbandModel';
                break;
            default:
               $stt_model = '&model=en-US_BroadbandModel';
                break;
        }

        $web_socket_url = array(
            'url'     => 'wss://stream.watsonplatform.net/speech-to-text/api/v1/recognize',
            'tokenQs' => '?access_token=',
            'otherQs' => $stt_model
        );
        $token_api_url = 'https://yjonpgjqs9.execute-api.us-east-1.amazonaws.com/V2';

        // Make web socket url and token url available to 'uvs.speech-handler.js'
        wp_localize_script( 'uvs.speech-handler', 'uvsWebSocketUrl', $web_socket_url);
        wp_localize_script( 'uvs.speech-handler', 'uvsTokenApiUrl', $token_api_url);

        // Make token url available to 'uvs.audio-input-handler.js'
        wp_localize_script( 'uvs.audio-input-handler', 'uvsTokenApiUrl', $token_api_url);
    }

    /**
     * Method to enqueue JS scripts and CSS of Admin for loading 
     */
    public function enqueue_admin_scripts()
    {
        // Enqueue JS: uvs-settings.js
        wp_enqueue_script(
            'uvs-settings',
            $this->get_url() . 'js/settings/uvs-settings'.self::$uvs_file_type.'.js',
            array(),
            filemtime(self::$plugin_directory_path.'js/settings/uvs-settings'.self::$uvs_file_type.'.js'),
            true
        );

        // Enqueue CSS: uvs-settings.css
        wp_enqueue_style(
            'uvs_settings_css',
            $this->get_url() . '/css/settings/uvs-settings'.self::$uvs_file_type.'.css',
            array(),
            filemtime(self::$plugin_directory_path.'css/settings/uvs-settings'.self::$uvs_file_type.'.css'),
            'screen'
        );
    }

    /**
     * Method to enqueue JS scripts and CSS for loading at Front end
     */
    public function enqueue_frontend_scripts()
    {
        // Enqueue JS: uvs.text-library.js
        wp_enqueue_script(
            'uvs.text-library',
            $this->get_url() . 'js/uvs.text-library'.self::$uvs_file_type.'.js',
            array(),
            filemtime(self::$plugin_directory_path.'js/uvs.text-library'.self::$uvs_file_type.'.js'),
            true
        );

        // Make selected language available to 'uvs.text-library.js'
        wp_localize_script( 'uvs.text-library', 'uvsSelectedLanguage', self::$uvs_selected_language);

        // Enqueue JS: uvs.speech-handler.js
        wp_enqueue_script(
            'uvs.speech-handler',
            $this->get_url() . 'js/uvs.speech-handler'.self::$uvs_file_type.'.js',
            array(),
            filemtime(self::$plugin_directory_path.'js/uvs.speech-handler'.self::$uvs_file_type.'.js'),
            true
        );

        // Make images path available to 'uvs.speech-handler.js'
        wp_localize_script( 'uvs.speech-handler', 'uvsImagesPath', self::$uvs_url . 'images/');

        // Make ajax obj available to 'uvs.speech-handler.js'
        $count_nonce = wp_create_nonce( 'service_call_count' );

        wp_localize_script( 'uvs.speech-handler', 'uvsAjaxObj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $count_nonce,
        ));

        // Make host name available to 'uvs.speech-handler.js'
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['SERVER_NAME'];
        wp_localize_script( 'uvs.speech-handler', 'uvsCurrentHostName', $protocol.$domainName);

        // Enqueue JS: uvs.audio-input-handler.js
        wp_enqueue_script(
            'uvs.audio-input-handler',
            $this->get_url() . 'js/uvs.audio-input-handler'.self::$uvs_file_type.'.js',
            array(),
            filemtime(self::$plugin_directory_path.'js/uvs.audio-input-handler'.self::$uvs_file_type.'.js'),
            true
        );

        // Making API meta data broadcasted in JS file(s)
        self::broadcast_api_meta_data();

        // Enqueue JS: uvs.audio-recorder.js
        wp_enqueue_script(
            'uvs.audio-recorder',
            $this->get_url() . 'js/recorderjs/uvs.audio-recorder'.self::$uvs_file_type.'.js',
            array(),
            filemtime(self::$plugin_directory_path.'js/recorderjs/uvs.audio-recorder'.self::$uvs_file_type.'.js'),
            true
        );

        // Make worker path available to 'uvs.audio-recorder.js'
        wp_localize_script( 'uvs.audio-recorder', 'uvsWorkerPath', $this->get_url(). 'js/recorderjs/uvs.audio-recorder-worker'.self::$uvs_file_type.'.js');

        // Enqueue JS: universal-voice-search.js
        wp_enqueue_script(
            'universal-voice-search',
            $this->get_url() . 'js/universal-voice-search'.self::$uvs_file_type.'.js',
            array(),
            filemtime(self::$plugin_directory_path.'js/universal-voice-search'.self::$uvs_file_type.'.js'),
            true
        );

        // Make floating mic status available to 'universal-voice-search.js'
        $uvs_floating_mic = get_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['floating_mic'], null);
        $uvs_floating_mic = self::uvs_sanitize_variable_for_local_script($uvs_floating_mic);
        wp_localize_script( 'universal-voice-search', 'uvsFloatingMic', $uvs_floating_mic);

        // Make selected Mic Position available to 'voice-forms.js'
        wp_localize_script( 'universal-voice-search', 'uvsSelectedMicPosition', self::$uvs_floating_mic_position);

        // Make button message and talk message available to 'universal-voice-search.js'
        wp_localize_script( 'universal-voice-search', 'universal_voice_search', array(
            'button_message' => __( 'Speech Input', 'universal-voice-search' ),
            'talk_message'   => __( 'Start Talking…', 'universal-voice-search' ),
        ));

        // Enqueue CSS: universal-voice-search.css
        wp_enqueue_style(
            'universal-voice-search',
            $this->get_url() . 'css/universal-voice-search'.self::$uvs_file_type.'.css',
            array(),
            filemtime(self::$plugin_directory_path.'css/universal-voice-search'.self::$uvs_file_type.'.css'),
            'screen'
        );

        // Get auto timeout value from Database
        $uvs_mic_listening_timeout = get_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['mic_listening_timeout'], null);
        $uvs_mic_listening_timeout = self::uvs_sanitize_variable_for_local_script($uvs_mic_listening_timeout);

        if (is_null($uvs_mic_listening_timeout)) {
            update_option(Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['mic_listening_timeout'], '8');
            $uvs_mic_listening_timeout = '8';
        }

        // Make mic auto timeout value and images path available to 'universal-voice-search.js'
        wp_localize_script( 'universal-voice-search', 'uvsMicListenTimeoutDuration', $uvs_mic_listening_timeout);
        wp_localize_script( 'universal-voice-search', 'uvsImagesPath', self::$uvs_url . 'images/');

        // Make 'X API Key' available to 'uvs.speech-handler.js', 'uvs.audio-input-handler.js' and 'universal-voice-search.js'
        wp_localize_script( 'uvs.speech-handler', 'uvsXApiKey', self::$uvs_api_access_key);
        wp_localize_script( 'uvs.audio-input-handler', 'uvsXApiKey', self::$uvs_api_access_key);
        wp_localize_script( 'universal-voice-search', 'uvsXApiKey', self::$uvs_api_access_key);

        $uvs_current_value = get_option('uvs_current_value', "0");
        $uvs_last_value = get_option('uvs_last_value', "0");
        $uvs_last_value_updated_at = get_option('uvs_last_value_updated_at', null);
        $uvs_last_value_updated_at = self::uvs_sanitize_variable_for_local_script($uvs_last_value_updated_at);

        // Make 'Service logs' available to 'uvs.speech-handler.js'
        $uvs_service_logs = array(
            'updatedAt' => $uvs_last_value_updated_at,
            'currentValue' => $uvs_current_value,
            'lastValue' => $uvs_last_value,
        );

        wp_localize_script( 'uvs.speech-handler', 'uvsServiceLogs', $uvs_service_logs);
    }

    /**
     * Method to add additional link to settings page below plugin on the plugins page.
     */
    function custom_plugin_row_meta( $links, $file )
    {
        if ( strpos( $file, 'universal-voice-search.php' ) !== false ) {
            $new_links = array('settings' => '<a href="' . site_url() . '/wp-admin/admin.php?page=universal-voice-search-settings" title="Universal Voice Search">Settings</a>');
            $links = array_merge( $links, $new_links );
        }

        return $links;
    }

    /**
     * Class method to get REST API access key ('x-api-key') against license key instate to avail plugin (Universal Voice Search) service
     *
     * @param $convertable_license_key - String : License key customer posses
     */
    public static function uvs_get_api_key_from_license_key($convertable_license_key = null, $license_key_field_changed = false)
    {
        $result = array();

        try {
            // Throw exception when license key is blank or unavailable
            if (!(isset($convertable_license_key) && is_null($convertable_license_key) == false 
                && trim($convertable_license_key) != '')) {
                update_option( 'uvs_api_system_key', '');
                throw new Exception("Error: License key is unavailable or invalid.");
            }

            $uvs_api_system_key = get_option('uvs_api_system_key', null);
            $uvs_api_system_key = isset($uvs_api_system_key) ? trim($uvs_api_system_key) : null;

            if (!empty($uvs_api_system_key) && $license_key_field_changed === false) {
                self::$uvs_api_access_key = $uvs_api_system_key;
            } else {
                $body = array( 'license' => trim($convertable_license_key) );
                $args = array(
                    'body'        => json_encode($body),
                    'timeout'     => '60',
                    'headers'     => array(
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                        'x-api-key'    => 'jEODHPKy2z7GEIuerFBWk7a0LqVRJ7ER3aDExmbK'
                    )
                );

                $response = wp_remote_post( 'https://1kosjp937k.execute-api.us-east-1.amazonaws.com/V2', $args );

                // Check the response code
                $response_code = wp_remote_retrieve_response_code( $response );

                if ((int)$response_code == 200) {
                    $response_body = wp_remote_retrieve_body($response);
                    $result = @json_decode($response_body, true);

                    if (!empty($result) && is_array($result)) {
                        if (array_key_exists('errorMessage', $result)) {
                            update_option( 'uvs_api_system_key', '');
                        } else {
                            $conversion_status_code = !empty($result['statusCode']) ? trim($result['statusCode']) : null;;
                            $conversion_status      = !empty($result['status']) ? trim($result['status']) : null;

                            if (!is_null($conversion_status_code) && !is_null($conversion_status) 
                                && (int)$conversion_status_code == 200 && strtolower(trim($conversion_status)) == 'success') {
                                self::$uvs_api_access_key = !empty($result['key']) ? trim($result['key']) : null;
                                
                                if (self::$uvs_api_access_key !== null) {
                                    update_option( 'uvs_api_system_key', self::$uvs_api_access_key);
                                } else {
                                    update_option( 'uvs_api_system_key', '');
                                }
                            } else {
                                update_option( 'uvs_api_system_key', '');
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            self::$uvs_api_access_key = null;
        }

        self::$uvs_api_access_key = self::uvs_sanitize_variable_for_local_script(self::$uvs_api_access_key);
    }

    /**
     * Class method to sanitize empty variables
     *
     * @param $uvs_var - String : Variable to sanitize
     * @return 
     */
    public static function uvs_sanitize_variable_for_local_script($uvs_var = null)
    {
        if (empty($uvs_var)) {
            return null;
        } else {
            return $uvs_var;
        }
    }

    /**
     * Method to log STT service call count to local DB and Cloud
     *
     * @return JSON response obj
     */
    public function uvs_log_service_call()
    {
        check_ajax_referer('service_call_count');

        // Get values from database, HTTP request
        $uvs_do_update_last_value = isset($_REQUEST['updateLastValue']) ? (int) $_REQUEST['updateLastValue'] : 0;
        $uvs_current_value        = (int) get_option('uvs_current_value', 0);
        $uvs_last_value           = (int) get_option('uvs_last_value', 0);
        $uvs_last_value_updated_at= get_option('uvs_last_value_updated_at', null);
        $uvs_current_value_to_log = ($uvs_do_update_last_value == 1) ? $uvs_current_value : $uvs_current_value + 1;
        $uvs_temp_last_value      = get_option('uvs_last_value', null); // To check if we are making initial service log call
        $uvs_log_result = array(
            'uvsSttAccess'  => 'allowed',
            'updatedAt'    => $uvs_last_value_updated_at,
            'currentValue' => $uvs_current_value,
            'lastValue'    => $uvs_last_value
        );

        try {
            // We need to reset current value count to 0 if current count log exceeds 25000
            if ($uvs_current_value_to_log > 25000) {
                update_option('uvs_current_value', 0);
            }

            // Log service count by calling cloud API if last update was before 24 hours or current count is +50 of last count
            if (is_null($uvs_temp_last_value) || $uvs_do_update_last_value === 1 || ($uvs_current_value_to_log > ($uvs_last_value + 50))) {
                $uvs_body = array(
                    'license'      => trim(self::$uvs_license_key),
                    'action'       => "logCalls",
                    'currentValue' => $uvs_current_value_to_log,
                    'lastValue'    => $uvs_last_value,
                );
                
                $uvs_args = array(
                    'body'         => json_encode($uvs_body),
                    'timeout'      => '60',
                    'headers'      => array(
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                        'x-api-key'    => 'jEODHPKy2z7GEIuerFBWk7a0LqVRJ7ER3aDExmbK'
                    )
                );

                $uvs_response = wp_remote_post( 'https://1kosjp937k.execute-api.us-east-1.amazonaws.com/V2', $uvs_args );

                // Check the response code
                $uvs_response_code = wp_remote_retrieve_response_code($uvs_response);


                if ($uvs_response_code == 200) {
                    $uvs_response_body = wp_remote_retrieve_body($uvs_response);
                    $uvs_result = @json_decode($uvs_response_body, true);

                    if (!empty($uvs_result) && is_array($uvs_result)) {
                        $log_status = array_key_exists("status",$uvs_result) ? strtolower($uvs_result['status']) : 'failed';
                        $actual_current_value = array_key_exists("current Value",$uvs_result) ? strtolower($uvs_result['current Value']) : null;
                        $uvs_error = array_key_exists("errorMessage",$uvs_result) ? true : false;

                        if ($log_status == 'success' && is_null($actual_current_value) === false && $uvs_error === false) {
                            // Store updated values to database
                            $uvs_current_timestamp = time(); // epoc 
                            update_option('uvs_current_value', $actual_current_value);
                            update_option('uvs_last_value', $actual_current_value);
                            update_option('uvs_last_value_updated_at', $uvs_current_timestamp);
                            
                            // Prepare response 
                            $uvs_log_result['updatedAt']    = $uvs_current_timestamp;
                            $uvs_log_result['currentValue'] = $actual_current_value;
                            $uvs_log_result['lastValue']    = $actual_current_value;
                            $uvs_log_result['cloud']    = true;
                        }
                    }
                } 
            } else {
                // Increase current count
                update_option('uvs_current_value', $uvs_current_value_to_log);

                // Prepare response
                $uvs_log_result['currentValue'] = $uvs_current_value_to_log;
                $uvs_log_result['local']    = true;
            }
        } catch (\Exception $ex) {
            // Prepare response 
            $uvs_log_result['uvsSttAccess']  = 'restricted';
        }

        wp_send_json($uvs_log_result);
    }

    /**
     * Method to register plugin for the first time
     *
     */
    public static function uvs_register_plugin()
    {
        try {           
            // Get plugin first activation status and license key from DB 
            $uvs_license_key      = get_option('uvs_license_key', null);
            $uvs_first_activation = get_option('uvs_first_activation', null);   
            $uvs_site_name        = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
            
            if (empty($uvs_first_activation) && empty(trim($uvs_license_key))) {
                // Mark first activation activity flag in local DB 
                update_option('uvs_first_activation', true);// Store first activation flag in DB

                // Detect site language and set the plugin language
                $uvs_site_language_code = get_locale();

                if (!empty($uvs_site_language_code) && array_key_exists($uvs_site_language_code, self::$uvs_auto_detect_lang_map)) {
                    update_option(
                        Universal_Voice_Search_Settings_Page::BASIC_CONFIG_OPTION_NAMES['selected_language'],
                        self::$uvs_auto_detect_lang_map[$uvs_site_language_code]
                    );
                }

                // Generate UUID and store in DB
                $uvs_new_uuid = wp_generate_uuid4();
                update_option('uvs_uuid', $uvs_new_uuid);

                $uvs_body = array(
                    'action' => 'regUVS',                  
                    'url'    => $uvs_site_name.'_'.$uvs_new_uuid,
                );

                $uvs_args = array(
                    'body'        => json_encode($uvs_body),
                    'timeout'     => '60',
                    'headers'     => array(
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                        'x-api-key'    => 'jEODHPKy2z7GEIuerFBWk7a0LqVRJ7ER3aDExmbK'
                    )
                );

                $uvs_response = wp_remote_post( 'https://1kosjp937k.execute-api.us-east-1.amazonaws.com/V2', $uvs_args );

                // Check the response body
                $uvs_response_body = wp_remote_retrieve_body($uvs_response);
                $uvs_result = @json_decode($uvs_response_body, true);             

                if (!empty($uvs_result) && is_array($uvs_result)) {
                    $log_status = array_key_exists('status', $uvs_result) ? strtolower(trim($uvs_result['status'])) : null;

                    if ($log_status == '200 success') {
                        // Do nothing for now                       
                    }  
                }             
            }
        } catch(\Exception $ex) {
            // Do nothing for now               
        }
    }
}


