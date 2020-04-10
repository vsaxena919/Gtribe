<?php
if ( !defined('ABSPATH') ) exit;

class Universal_Voice_Search_Settings_Page 
{
    // Database field name map
    const BASIC_CONFIG_OPTION_NAMES = array(
        'license_key'           => 'uvs_license_key',
        'mic_listening_timeout' => 'uvs_mic_listening_timeout',
        'selected_language'     => 'uvs_selected_language',
        'floating_mic'          => 'uvs_floating_mic',
        'floating_mic_position' => 'uvs_floating_mic_position'
    );

    private $uvs_license_key           = '';
    private $uvs_mic_listening_timeout = null;
    private $uvs_selected_language     = 'English';
    private $uvs_floating_mic          = null;
    private $uvs_floating_mic_position = 'Middle Right';

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'uvs_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'uvs_page_init' ) );

        // Register callback to hook into post create and update (License key) option action
        add_action( 'add_option_'.self::BASIC_CONFIG_OPTION_NAMES['license_key'], array( $this, 'uvs_post_adding_license_key'), 10, 2 );
        add_action( 'update_option_'.self::BASIC_CONFIG_OPTION_NAMES['license_key'], array( $this, 'uvs_post_update_license_key'), 10, 2 );
    }

    /**
     * Method as callback post to license key option creation in DB
     *
     * @param $option_name - string : Option name
     * @param $option_value - string : Option value
     */
    public function uvs_post_adding_license_key( $option_name, $option_value)
    {
        try {
            Universal_Voice_Search_Plugin::uvs_get_api_key_from_license_key(trim($option_value), true);
        } catch (\Exception $ex) {
            // Do nothing for now
        }
    }

    /**
     * Method as callback post to license key option update in DB
     *
     * @param $old_value - string : Option value before update
     * @param $new_value - string : Updated Option value
     */
    public function uvs_post_update_license_key( $old_value, $new_value)
    {
        try {
            $option_value = strip_tags(stripslashes($new_value));
            
            if ($old_value != trim($option_value)) {
                Universal_Voice_Search_Plugin::uvs_get_api_key_from_license_key(trim($option_value), true);
            }
        } catch (\Exception $ex) {
            // Do nothing for now
        }
    }

    /**
     * Add options page
     */
    public function uvs_add_plugin_page()
    {
        // This page will be under "Settings"
        add_submenu_page(
            'options-general.php',// Parent menu as 'settings'
            'Universal Voice Search',
            'Universal Voice Search',
            'manage_options',
            'universal-voice-search-settings',// Slug for page
            array( $this, 'uvs_settings_create_page')// View 
        );
    }

    /**
     * Options/Settings page callback to create view/html of settings page
     */
    public function uvs_settings_create_page()
    {
        // For license key
        $this->uvs_license_key = strip_tags(stripslashes(get_option(self::BASIC_CONFIG_OPTION_NAMES['license_key'], '')));
        $this->uvs_license_key = !empty($this->uvs_license_key) ? $this->uvs_license_key : '';

        if (empty($this->uvs_license_key)) { update_option('uvs_api_system_key', ''); }

        // For Mic listening auto timeout
        $this->uvs_mic_listening_timeout = strip_tags(stripslashes(get_option(self::BASIC_CONFIG_OPTION_NAMES['mic_listening_timeout'], null)));

        // if voice type is blank then always store voice type as male
        if (empty($this->uvs_mic_listening_timeout) || $this->uvs_mic_listening_timeout < 8) {
            update_option(self::BASIC_CONFIG_OPTION_NAMES['mic_listening_timeout'], 8);
            $this->uvs_mic_listening_timeout = 8;
        } elseif ($this->uvs_mic_listening_timeout > 20) {
            update_option(self::BASIC_CONFIG_OPTION_NAMES['mic_listening_timeout'], 20);
            $this->uvs_mic_listening_timeout = 20;
        }

        // For language
        $this->uvs_selected_language = strip_tags(stripslashes(get_option( 
            self::BASIC_CONFIG_OPTION_NAMES['selected_language'], 'English')));

        // For floating mic
        $this->uvs_floating_mic = strip_tags(stripslashes(get_option(self::BASIC_CONFIG_OPTION_NAMES['floating_mic'], null)));

        // For Mic Position
        $this->uvs_floating_mic_position = strip_tags(stripslashes(get_option( 
            self::BASIC_CONFIG_OPTION_NAMES['floating_mic_position'], 'Middle Right')));
?>
        <div class="wrap">
            <div id="uvsavigationSettingsWrapper">
                <div id="uvsavigationSettingsHeader" class="uvs-row">
                    <div class="uvs-setting-header-column-1"><br>
                        <span id="uvsavigationSettingsPageHeading">Universal Voice Search Setup</span>
                    </div>
                    <div class="uvs-setting-header-column-2">
                        <a title="Wordpress Plugin - speak2web" target="blank" href="https://speak2web.com/plugin/">
                            <img id="uvsavigationSettingsPageHeaderLogo" 
                            src="<?php echo dirname(plugin_dir_url(__FILE__)).'/images/speak2web_logo.png'?>">
                        </a>
                    </div>
                </div>

                <form id="uvsavigationBasicConfigForm" method="post" action="options.php">
                    <?php
                        // This prints out all hidden setting fields
                        settings_fields( 'uvs-basic-config-settings-group' );
                        do_settings_sections( 'uvs-settings' );

                        // To display errors
                        settings_errors('uvs-settings', true, true);
                    ?>
                    <div id="uvsavigationBasicConfigSection" class='uvs-row uvs-card'>
                        <div id="uvsBasicConfHeaderSection" class="uvs-setting-basic-config-column-1 uvs-basic-config-section-title">
                            <table id="uvsavigationBasicConfHeaderTable">
                                <tr>
                                    <th><h4><u><?php echo UVS_LANGUAGE_LIBRARY['basicConfig']['basicConfiguration']; ?></u></h4></th>
                                </tr>
                            </table>
                        </div>
                        <div class="uvs-setting-basic-config-column-2">
                            <div class="uvs-basic-config-sub-row">
                                <div><?php echo UVS_LANGUAGE_LIBRARY['basicConfig']['selectLanguage']; ?>
                                    <select  id="uvsLanguage" class="uvs-language" name="<?php echo self:: BASIC_CONFIG_OPTION_NAMES['selected_language']; ?>">
                                        <option value="English" <?php selected('English', $this->uvs_selected_language);?>>English(US)</option>
                                        <option value="British English" <?php selected('British English', $this->uvs_selected_language);?> >British English</option>
                                        <option value="German"  <?php selected('German', $this->uvs_selected_language);?> >German</option>
                                        <option value="Portuguese"  <?php selected('Portuguese', $this->uvs_selected_language);?> >Portuguese</option>
                                        <option value="Chinese"  <?php selected('Chinese', $this->uvs_selected_language);?> >Chinese</option>
                                        <option value="French"  <?php selected('French', $this->uvs_selected_language);?> >French</option>
                                        <option value="Japanese"  <?php selected('Japanese', $this->uvs_selected_language);?> >Japanese</option>
                                        <option value="Korean"  <?php selected('Korean', $this->uvs_selected_language);?> >Korean</option>
                                        <option value="Spanish"  <?php selected('Spanish', $this->uvs_selected_language);?> >Spanish</option>
                                    </select>
                                </div>                            
                            </div>

                            <div class="uvs-basic-config-sub-row">
                                <div class="uvs-basic-config-attached-label-column">License Key</div>
                                <div class="uvs-basic-config-attached-input-column">
                                    <input 
                                    type="text" 
                                    name="<?php echo self::BASIC_CONFIG_OPTION_NAMES['license_key']; ?>" 
                                    id="uvsavigationLicenseKey" 
                                    placeholder="<?php echo UVS_LANGUAGE_LIBRARY['basicConfig']['copyYourLicenseKey']; ?>" 
                                    value="<?php echo $this->uvs_license_key; ?>"/>
                                </div>
                            </div>
                            <div class="uvs-basic-config-sub-row">
                                <span class="uvs-autotimeout-label">
                                    <input 
                                        class="uvs-autotimeout-mic"
                                        type='number' 
                                        name="<?php echo self::BASIC_CONFIG_OPTION_NAMES['mic_listening_timeout']; ?>" 
                                        min="8"
                                        max="20"
                                        step="1"
                                        onKeyup="uvsResetTimeoutDefaultValue(this, event)"
                                        onKeydown="uvsValidateTimeoutValue(this, event)"
                                        value="<?php echo $this->uvs_mic_listening_timeout; ?>"
                                    /> <?php echo UVS_LANGUAGE_LIBRARY['basicConfig']['autoTimeoutDuration']; ?></span>
                            </div>
                            <div class="uvs-basic-config-sub-row">
                                <label for="uvsFloatingMic">
                                    <input 
                                    id="uvsFloatingMic"
                                    type='checkbox' 
                                    name="<?php echo self::BASIC_CONFIG_OPTION_NAMES['floating_mic']; ?>" 
                                    value="yes" <?php checked('yes', $this->uvs_floating_mic);?> 
                                    > <?php echo UVS_LANGUAGE_LIBRARY['basicConfig']['floatingMic']; ?> 
                                </label>
                            </div>
                            <!-- Floating Mic Position -->
                                <div class="uvs-basic-config-sub-row">
                                    <label for="<?php echo self::BASIC_CONFIG_OPTION_NAMES['floating_mic_position']; ?>">
                                        <b><?php echo UVS_LANGUAGE_LIBRARY['basicConfig']['floatingMicOptions']; ?></b>
                                    </label><br>
                                    <div><?php echo UVS_LANGUAGE_LIBRARY['basicConfig']['selectFloatingMicPosition']; ?> <select id="uvsFloatingMicPosition" name="<?php echo self:: BASIC_CONFIG_OPTION_NAMES['floating_mic_position']; ?>">
                                            <option value="Middle Right" <?php selected('Middle Right', $this->uvs_floating_mic_position);?>>Middle Right</option>
                                            <option value="Middle Left" <?php selected('Middle Left', $this->uvs_floating_mic_position);?>>Middle Left</option>
                                            <option value="Top Right" <?php selected('Top Right', $this->uvs_floating_mic_position);?>>Top Right</option>
                                            <option value="Top Left" <?php selected('Top Left', $this->uvs_floating_mic_position);?>>Top Left</option>
                                            <option value="Bottom Right" <?php selected('Bottom Right', $this->uvs_floating_mic_position);?>>Bottom Right</option>
                                            <option value="Bottom Left" <?php selected('Bottom Left', $this->uvs_floating_mic_position);?>>Bottom Left</option>                                                                                 
                                        </select>
                                    </div>
                                </div>
                            <!-- END Floating Mic Position --> 
                        </div>

                        <div class="uvs-setting-basic-config-column-3 uvs-basic-config-sub-row">
                            <?php 
                            $other_attributes = array( 'id' => 'uvsavigationBasicConfigSettingsSave' );
                            submit_button( 
                                UVS_LANGUAGE_LIBRARY['basicConfig']['saveSettings'], 
                                'primary', 
                                'uvs-basic-config-settings-save', 
                                false, 
                                $other_attributes
                            );
                            ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
<?php
    }

    /**
     * Register and add settings
     */
    public function uvs_page_init()
    {
        // Register settings for feilds of 'Basic Configuration' section
        register_setting('uvs-basic-config-settings-group', self::BASIC_CONFIG_OPTION_NAMES['license_key']);
        register_setting('uvs-basic-config-settings-group', self::BASIC_CONFIG_OPTION_NAMES['mic_listening_timeout']);
        register_setting('uvs-basic-config-settings-group', self::BASIC_CONFIG_OPTION_NAMES['selected_language']);
        register_setting('uvs-basic-config-settings-group', self::BASIC_CONFIG_OPTION_NAMES['floating_mic']);
        register_setting('uvs-basic-config-settings-group', self::BASIC_CONFIG_OPTION_NAMES['floating_mic_position']);
    }
}

// check user capabilities and hook into 'init' to initialize 'Universal Voice Search' settings object
add_action('init', 'initialize_uvs_settings_object');

/**
 * Initialize 'Universal Voice Search' settings object when 'pluggable' files are loaded from '/wp-includes/pluggable'
 * Which contains 'current_user_can' function.
 */
function initialize_uvs_settings_object(){
    if ( !current_user_can( 'manage_options' ) ) return;  

    $universal_voice_search_settings_page = new Universal_Voice_Search_Settings_Page();
}