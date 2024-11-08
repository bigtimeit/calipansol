<?php

namespace PWH_DCFH\App\Frontend\Controllers;
if (!class_exists('PWH_DCFH_Email_Merge_Data_Tags')) {
    class PWH_DCFH_Email_Merge_Data_Tags
    {

        private static $_instance;

        /**
         * Keywords Array
         *
         * @var string[]
         */
        public static $merg_data_tags = [
            '%%dcfh_site_name%%' => '',
            '%%dcfh_site_tagline%%' => '',
            '%%dcfh_site_url%%' => '',
            '%%dcfh_site_login_url%%' => '',
            '%%dcfh_post_id%%' => '',
            '%%dcfh_post_title%%' => '',
            '%%dcfh_post_url%%' => '',
            '%%dcfh_date%%' => '',
            '%%dcfh_time%%' => '',
            '%%dcfh_user_ip%%' => '',
            '%%dcfh_browser%%' => '',
            '%%dcfh_platform%%' => '',
        ];

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Email_Merge_Data_Tags
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Set Custom Tags
         */
        public static function set_static_tags_values()
        {

            self::$merg_data_tags['%%dcfh_site_name%%'] = get_option('blogname');
            self::$merg_data_tags['%%dcfh_site_tagline%%'] = get_option('blogdescription');
            self::$merg_data_tags['%%dcfh_site_url%%'] = home_url();
            self::$merg_data_tags['%%dcfh_site_login_url%%'] = wp_login_url();
            self::$merg_data_tags['%%dcfh_post_id%%'] = get_the_ID();
            self::$merg_data_tags['%%dcfh_post_title%%'] = get_the_title();
            self::$merg_data_tags['%%dcfh_post_url%%'] = get_the_permalink();
            self::$merg_data_tags['%%dcfh_date%%'] = apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'tag_date_format', date_i18n('Y-m-d'));
            self::$merg_data_tags['%%dcfh_time%%'] = apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'tag_time_format', date_i18n('h:i:sa'));
            self::$merg_data_tags['%%dcfh_user_ip%%'] = pwh_dcfh_helpers()::get_ip_address();
            self::$merg_data_tags['%%dcfh_browser%%'] = ucfirst((string)pwh_dcfh_helpers()::get_user_agent('browser'));
            self::$merg_data_tags['%%dcfh_platform%%'] = ucfirst((string)pwh_dcfh_helpers()::get_user_agent('platform'));
        }

        /**
         * Set Contact Form ID Tag Value
         *
         * @param $contact_form_id
         *
         * @return mixed
         */
        public static function set_contact_form_id_tag_value($contact_form_id)
        {
            return self::$merg_data_tags['%%dcfh_contact_form_id%%'] = $contact_form_id;
        }

        /**
         * Set Contact Entry Number Tag Value
         *
         * @param $entry_number
         *
         * @return mixed
         */
        public static function set_contact_entry_number_tag_value($entry_number)
        {
            return self::$merg_data_tags['%%dcfh_entry_number%%'] = $entry_number;
        }

        /**
         * Set Contact Form Title Tag Value
         *
         * @param $contact_form_title
         *
         * @return mixed
         */
        public static function set_contact_form_title_tag_value($contact_form_title)
        {
            return self::$merg_data_tags['%%dcfh_contact_form_title%%'] = $contact_form_title;
        }

        /**
         * Set Referer URL Tah
         *
         * @param $referer_url
         *
         * @return mixed
         */
        public static function set_referer_url_tag_value($referer_url)
        {
            return self::$merg_data_tags['%%dcfh_referer_url%%'] = $referer_url;
        }

        /**
         * Set Admin Tag Values
         *
         * @param $message
         *
         * @return void
         */
        public static function set_site_admin_tag_values($message)
        {
            $keys = pwh_dcfh_helpers()::get_key_from_string('dcfh_site_admininfo', $message);
            if (!empty($keys)) {
                $value = '';
                foreach ($keys as $key) {
                    $tag = str_replace('dcfh_site_admininfo_', '', $key);
                    if (!empty($tag)) {
                        $user_data = get_user_by('login', $tag);
                        if (false !== $user_data) {
                            $value = $user_data->first_name.' '.$user_data->last_name;
                        }
                        self::$merg_data_tags['%%'.$key.'%%'] = $value;
                    }
                }
            }
        }

        /**
         * Get Submitter Info Tags Data
         *
         * @param $message
         *
         * @return void
         */
        public static function set_submitter_tags_values($message)
        {
            $keys = pwh_dcfh_helpers()::get_key_from_string('dcfh_site_userinfo', $message);
            if (!empty($keys)) {
                if (is_user_logged_in()) {
                    $user_data = wp_get_current_user();
                    $value = '';
                    foreach ($keys as $key) {
                        $tag = strtolower(str_replace('dcfh_site_userinfo_', '', $key));
                        if ('id' === $tag) {
                            $value = $user_data->data->ID;
                        }
                        if ('name' === $tag) {
                            $value = $user_data->first_name.' '.$user_data->last_name;
                        }
                        if ('email' === $tag) {
                            $value = $user_data->data->user_email;
                        }
                        if ('registered' === $tag) {
                            $value = $user_data->data->user_registered;
                        }
                        if ('username' === $tag) {
                            $value = $user_data->data->user_login;
                        }
                        self::$merg_data_tags['%%'.$key.'%%'] = $value;
                    }
                } else {
                    foreach ($keys as $key) {
                        $tag = strtolower(str_replace('dcfh_site_userinfo_', '', $key));
                        if (in_array($tag, ['id', 'name', 'email', 'registered', 'username'])) {
                            self::$merg_data_tags['%%'.$key.'%%'] = __('Visitor', pwh_dcfh_hc()::TEXT_DOMAIN);
                        }
                    }
                }
            }
        }

        /**
         * Replaced Tags Values
         *
         * @param $string
         * @param $post_request
         *
         * @return string
         */
        public static function set_form_entry_tags_values($string, $post_request)
        {
            self::set_site_admin_tag_values($string);
            self::set_submitter_tags_values($string);
            self::set_referer_url_tag_value($post_request->get_referer_url());
            $string = trim(stripslashes($string));
            $replace_pairs = self::$merg_data_tags;
            foreach ($post_request->get_contact_form_processed_fields() as $value) {
                $replace_pairs['%%'.$value['id'].'%%'] = $value['value'];
            }

            return str_ireplace(array_keys($replace_pairs), array_values($replace_pairs), $string);
        }

    }
}