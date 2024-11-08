<?php

namespace PWH_DCFH\App\Admin\Controllers;
if (!class_exists('PWH_DCFH_Email_Template_Handler')) {
    class PWH_DCFH_Email_Template_Handler
    {

        private static $_instance;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Email_Template_Handler
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Get Email Template Name
         *
         * @param $string
         *
         * @return string
         */
        public static function set_template_id($string)
        {
            return pwh_dcfh_hc()::CF_EMAIL_TPL_PREFIX.sanitize_key($string);
        }

        /**
         * Check Option Exist In Database
         *
         * @param $option_name
         *
         * @return bool
         */
        public static function is_template_exist($option_name)
        {
            global $wpdb;
            $result = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM $wpdb->options WHERE option_name LIKE %s ", $option_name)); // db call ok; no-cache ok
            if ($result > 0 && !is_wp_error($result)) {
                return true;
            }

            return false;
        }

        /**
         * Get Email Templates
         *
         * @return array|false|mixed
         */
        public static function get_templates()
        {
            $key = 'pwh_dcfh_templates_'.md5(wp_basename(__FILE__));
            $cached_data = wp_cache_get($key);
            if (!$cached_data) {
                global $wpdb;
                $templates = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s ", '%'.pwh_dcfh_hc()::CF_EMAIL_TPL_PREFIX.'%'));//db call ok no-cache ok
                if (!empty($templates)) {
                    foreach ($templates as $template) {
                        $options = get_option($template);
                        if (false !== $options) {
                            $cached_data[$template] = $options;
                        }
                    }
                    // Sort Date Wise
                    array_multisort(array_map('strtotime', array_column($cached_data, 'created_at')), SORT_DESC, $cached_data);
                    wp_cache_set($key, $cached_data, 'pwh_dcfh_cached_queries', 2 * MINUTE_IN_SECONDS);
                }
            }

            return $cached_data;
        }

        /**
         * Get Email Templates Type
         *
         * @return array
         */
        public static function get_templates_types()
        {
            $templates = [
                'general' => __('General', pwh_dcfh_hc()::TEXT_DOMAIN),
                'admin' => __('Admin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'confirmation' => __('Confirmation', pwh_dcfh_hc()::TEXT_DOMAIN),
                'reply_send' => __('Reply/Send Email', pwh_dcfh_hc()::TEXT_DOMAIN),
                'post_clone' => __('Post Clone', pwh_dcfh_hc()::TEXT_DOMAIN),
            ];

            return $templates;
        }

        /**
         * Get Email Template Type Label
         *
         * @return string
         */
        public static function get_template_type_label($label)
        {
            $templates = self::get_templates_types();
            if ('' !== $label && isset($templates[$label])) {
                return $templates[$label];
            }

            return $label;
        }
    }
}