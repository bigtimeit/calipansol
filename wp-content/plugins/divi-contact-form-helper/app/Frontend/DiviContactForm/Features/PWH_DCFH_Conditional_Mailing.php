<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use PWH_DCFH\App\Frontend\Request\PWH_DCFH_Post_Request;
use PWH_DCFH\App\Frontend\Request\PWH_DCFH_Post_Repository;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Conditional_Mailing')) {
    class PWH_DCFH_Conditional_Mailing
    {
        private static $_instance;

        public $email_pattern;

        public $admin_emails;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Conditional_Mailing
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            add_filter('et_contact_page_email_to', [$this, 'maybe_conditional_emails']);
        }

        /**
         * Send Conditional Emails
         *
         * @param $et_email_to
         *
         * @return mixed
         */
        public function maybe_conditional_emails($et_email_to)
        {
            $admin_emails = $this->get_admin_emails();
            if (!empty($admin_emails)) {
                $post_request = new PWH_DCFH_Post_Request();
                $data = $post_request->get_contact_form_processed_fields();
                $et_emails_to = [];
                foreach ($data as $form_data) {
                    if (isset($form_data['id']) && isset($form_data['value']) && !empty($form_data['value'])) {
                        $field_id = pwh_dcfh_helpers()::str_to_key($form_data['id']);
                        $field_data = explode(',', $form_data['value']);
                        if (is_array($field_data)) {
                            foreach ($field_data as $field_value) {
                                $field_value = pwh_dcfh_helpers()::str_to_key($field_value);
                                if (isset($admin_emails[$field_id][$field_value])) {
                                    $et_emails_to[] = $admin_emails[$field_id][$field_value];
                                }
                            }
                        }
                        /*if (isset($admin_emails[$field_id][$field_value])) {
                            $et_email_to = sanitize_email($admin_emails[$field_id][$field_value]);
                        }*/
                    }
                }
                if (!empty($et_emails_to)) {
                    $et_email_to = array_unique($et_emails_to);
                }
            }

            return $et_email_to;
        }

        /**
         * @return mixed
         */
        private function get_admin_emails()
        {
            return $this->admin_emails;
        }

        /**
         * Update Conditional Email Data
         *
         * @return void
         */
        public function set_admin_emails()
        {
            $admin_emails = [];
            $pattern = wp_strip_all_tags(htmlspecialchars_decode($this->email_pattern));
            $main_arr = explode(',', $pattern);
            if (is_array($main_arr)) {
                foreach ($main_arr as $main_val) {
                    $outer_arr = explode('::', $main_val);
                    if (isset($outer_arr[0]) && isset($outer_arr[1])) {
                        $field_id = pwh_dcfh_helpers()::str_to_key($outer_arr[0]);
                        $inner_arr = explode('|', $outer_arr[1]);
                        foreach ($inner_arr as $inner_val) {
                            $data_arr = explode(':', $inner_val);
                            if (isset($data_arr[0]) && isset($data_arr[1])) {
                                $index = pwh_dcfh_helpers()::str_to_key($data_arr[0]);
                                $email = sanitize_email($data_arr[1]);
                                $admin_emails[$field_id][$index] = $email;
                            }
                        }
                    }
                }
            }
            $this->admin_emails = $admin_emails;
        }

    }
}