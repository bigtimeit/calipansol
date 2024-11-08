<?php

namespace PWH_DCFH\App\Base;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Public_Enqueue')) {
    class PWH_DCFH_Public_Enqueue
    {

        private $min = '.min';

        private static $localizations = [];

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            if (defined('PWH_DCFH_PLUGIN_DEV_MOD') && PWH_DCFH_PLUGIN_DEV_MOD) {
                $this->min = '';
            }
            // Translations Strings
            $frontend_strings = (new PWH_DCFH_Strings())->instance()->strings('frontend_strings');
            // Localizations
            self::$localizations = [
                'ajaxURL' => esc_js(admin_url('admin-ajax.php')),
                'ajaxNonce' => wp_create_nonce(pwh_dcfh_hc()::AJAX_NONCE),
                'pluginURL' => esc_js(pwh_dcfh_helpers()::plugin_url()),
                'blogURL' => get_bloginfo('url'),
                'wpLocale' => pwh_dcfh_helpers()::get_wp_locale(),
                'datepickerClass' => pwh_dcfh_hc()::DATEPICKER_CLASS,
                'wpMaxUploadSize' => wp_max_upload_size(),
                'wpMaxUploadSizeFormatted' => size_format(wp_max_upload_size()),
                'imageMimeTypes' => pwh_dcfh_helpers()::get_image_mimes(),
                'uploadFileClass' => pwh_dcfh_hc()::UPLOAD_FILE_CLASS,
                'i18n' => $frontend_strings,
                'isSuperAdmin' => is_super_admin(),
                'userIpAddress' => md5(pwh_dcfh_helpers()::get_ip_address()),
            ];
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 10, 1);
        }

        /**
         * Enqueue Scripts
         */
        public function enqueue_scripts()
        {
            /*---------------- Datetime Picker ----------------*/
            wp_enqueue_style(pwh_dcfh_helpers()::handle('datetimepicker'), pwh_dcfh_helpers()::plugin_url("assets/public/vendor/clean-datetimepicker/jquery.datetimepicker.min.css"), [], '1.3.4');
            wp_enqueue_script(pwh_dcfh_helpers()::handle('datetimepicker'), pwh_dcfh_helpers()::plugin_url("assets/public/vendor/clean-datetimepicker/jquery.datetimepicker.full.min.js"), ['jquery'], '1.3.4', true);
            /*---------------- Select2 ----------------*/
            wp_enqueue_style(pwh_dcfh_helpers()::handle('select2'), pwh_dcfh_helpers()::plugin_url("assets/public/vendor/select2/select2.min.css"), [], '4.1.0');
            wp_enqueue_script(pwh_dcfh_helpers()::handle('select2'), pwh_dcfh_helpers()::plugin_url("assets/public/vendor/select2/select2.min.js"), ['jquery'], '4.1.0', true);
            /*---------------- App ----------------*/
            wp_enqueue_style(pwh_dcfh_helpers()::handle('app'), pwh_dcfh_helpers()::plugin_url("assets/public/css/app$this->min.css"), [], pwh_dcfh_helpers()::plugin()->Version);
            wp_enqueue_script(pwh_dcfh_helpers()::handle('app'), pwh_dcfh_helpers()::plugin_url("assets/public/js/app$this->min.js"), ['jquery', 'wp-i18n'], pwh_dcfh_helpers()::plugin()->Version, true);
            wp_localize_script(pwh_dcfh_helpers()::handle('app'), 'pwhDCFHPublic', apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'public_localizations', self::$localizations));
            wp_set_script_translations(pwh_dcfh_helpers()::handle('app'), pwh_dcfh_hc()::TEXT_DOMAIN);
        }

    }
}