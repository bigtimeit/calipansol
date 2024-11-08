<?php

namespace PWH_DCFH\App\Base;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Plugin_Meta_Links')) {
    class PWH_DCFH_Plugin_Meta_Links
    {

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            add_filter('extra_plugin_headers', [$this, 'plugin_extra_headers']);
            add_filter('plugin_action_links', [$this, 'plugin_action_links'], 10, 2);
            add_filter('plugin_row_meta', [$this, 'plugin_meta_links'], 10, 2);
        }

        /**
         * Add Plugin Extra Headers
         *
         * @param $headers
         *
         * @return mixed
         */
        public function plugin_extra_headers($headers)
        {
            if (!in_array('DocumentationURI', $headers)) {
                $headers[] = 'DocumentationURI';
            }

            return $headers;
        }

        /**
         * Add Plugin Links
         *
         * @param $links
         * @param $file
         *
         * @return mixed
         */
        public function plugin_action_links($links, $file)
        {
            if (pwh_dcfh_hc()::BASENAME !== $file) {
                return $links;
            }
            $url = "admin.php?page=".pwh_dcfh_helpers()::get_divi_setting_slug()."#wrap-pwh-dcfh-epanel";
            $settings_link = sprintf('<a href="%s">'.__('Settings', pwh_dcfh_hc()::TEXT_DOMAIN).'</a>.', esc_url($url));
            array_unshift($links, $settings_link);

            return $links;
        }

        /**
         * Add Plugin Meta
         *
         * @param $links
         * @param $file
         *
         * @return mixed
         */
        public function plugin_meta_links($links, $file)
        {
            if (pwh_dcfh_hc()::BASENAME !== $file) {
                return $links;
            }
            $links[] = sprintf('<a href="%s"  target="_blank">'.esc_html__('Documentation & Support', pwh_dcfh_hc()::TEXT_DOMAIN).'</a>', esc_url(pwh_dcfh_helpers()::plugin()->DocumentationURI));

            return $links;
        }

    }
}