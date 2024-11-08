<?php

namespace PWH_DCFH\App\Base;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Admin_Enqueue')) {
    class PWH_DCFH_Admin_Enqueue
    {

        private static $localizations = [];

        private static $allowed_pages;

        private $min = '.min';

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
            $admin_strings = (new PWH_DCFH_Strings())->instance()->strings('admin_strings');
            // Localizations
            self::$localizations = [
                'ajaxURL' => esc_js(admin_url('admin-ajax.php')),
                'ajaxNonce' => wp_create_nonce('admin-ajax-nonce'),
                'pluginURL' => esc_js(pwh_dcfh_helpers()::plugin_url()),
                'blogURL' => get_bloginfo('url'),
                'i18n' => $admin_strings,
            ];
            // Allowed Pages
            self::$allowed_pages = [
                'toplevel_page_'.pwh_dcfh_helpers()::get_divi_setting_slug(),
                pwh_dcfh_hc()::POST_TYPE.'_page_create_post',
                pwh_dcfh_hc()::POST_TYPE.'_page_create_email_template',
                pwh_dcfh_hc()::POST_TYPE.'_page_email_templates_list',
                pwh_dcfh_hc()::POST_TYPE.'_page_send_email',
                pwh_dcfh_hc()::POST_TYPE.'_page_export_as_csv',
                'pwh_dcfh_page_contact_forms',
                'divi_page_et_theme_builder',
                'post-new.php',
                'post.php',
                'edit.php',
                'index.php',
            ];
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts'], 10, 1);
            add_action('admin_print_styles', [$this, 'remove_menu_items']);
        }

        /**
         * Enqueue Scripts
         *
         * @param $hook
         *
         * @return void|null
         */
        public function enqueue_scripts($hook)
        {
            if (!in_array($hook, self::$allowed_pages)) {
                return null;
            }
            /*---------------- Admin App ----------------*/
            wp_enqueue_style(pwh_dcfh_helpers()::handle('app'), pwh_dcfh_helpers()::plugin_url("assets/admin/css/app$this->min.css"), [], pwh_dcfh_helpers()::plugin()->Version);
            wp_enqueue_script(pwh_dcfh_helpers()::handle('app'), pwh_dcfh_helpers()::plugin_url("assets/admin/js/app$this->min.js"), ['jquery', 'wp-i18n'], pwh_dcfh_helpers()::plugin()->Version, true);
            wp_localize_script(pwh_dcfh_helpers()::handle('app'), 'pwhDCFHAdmin', apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'admin_localizations', self::$localizations));
            if (!did_action('wp_enqueue_media')) {
                wp_enqueue_media();
            }
            /*---------------- Jquery UI  ----------------*/
            if (!did_action('wp-jquery-ui-dialog')) {
                wp_enqueue_script('jquery-ui-dialog');
                wp_enqueue_style('wp-jquery-ui-dialog');
                wp_enqueue_script('jquery-ui-autocomplete', '', ['jquery-ui-widget', 'jquery-ui-position'], '1.8.6');
            }
        }

        /**
         * Register Inline Scripts
         */
        public function remove_menu_items()
        {
            echo PHP_EOL;
            echo "<style>";
            echo "#menu-posts-pwh_dcfh ul.wp-submenu li:nth-child(3),#menu-posts-pwh_dcfh ul.wp-submenu li:nth-child(4),#menu-posts-pwh_dcfh ul.wp-submenu li:nth-child(6){display:none}";
            echo "</style>";
            echo PHP_EOL;
        }

    }
}