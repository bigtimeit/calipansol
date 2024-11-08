<?php

namespace PWH_DCFH\App\Frontend\Request;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Post_Repository')) {
    class PWH_DCFH_Post_Repository
    {

        private static $instance = null;

        public $contact_form_id;

        public $contact_form_title;

        public $entry_number;

        public $save_entry_to_db = false;

        public $save_files_to_db = false;

        public $send_files_as_attachment = false;

        public $collect_ip_useragent_details = false;

        public $attached_files_message;

        public $is_confirmation_email_enabled = false;

        public $is_zapier_enabled = false;

        public $zapier_mailbox_address;

        public $is_pabbly_enabled = false;

        public $pabbly_mailbox_address;

        public $is_custom_message_richtext = false;

        public $is_confirmation_message_richtext = false;

        public $module_admin_email;

        public $admin_email_subject;

        public $admin_email_cc;

        public $admin_email_bcc;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Post_Repository|null
         */
        public static function instance()
        {
            if (self::$instance == null) {
                self::$instance = new PWH_DCFH_Post_Repository();
            }

            return self::$instance;
        }

        /**
         * Retrieves The Form Name
         *
         * @return string
         */
        public function get_contact_form_id()
        {
            return sanitize_title($this->contact_form_id);
        }

        /**
         * Retrieves The Admin Email Subject
         *
         * @return string
         * @since 1.0.0
         */
        public function get_admin_email_subject()
        {
            return $this->admin_email_subject;
        }

        /**
         * Retrieves The Admin Email Cc
         *
         * @return string
         * @since 1.1
         */
        public function get_admin_email_cc()
        {
            return $this->admin_email_cc;
        }

        /**
         * Retrieves The Admin Email Cc Headers
         *
         * @param $headers
         *
         * @return mixed
         */
        public function get_admin_cc_headers($headers)
        {
            $emails = $this->get_admin_email_cc();
            if (!empty($emails)) {
                $emails = array_filter(explode(',', $emails));
                foreach ((array)$emails as $email) {
                    $headers[] = sprintf('Cc: %s', sanitize_email($email));
                }
            }

            return $headers;
        }

        /**
         * Retrieves The Admin Email Bcc
         *
         * @return string
         * @since 1.1
         */
        public function get_admin_email_bcc()
        {
            return $this->admin_email_bcc;
        }

        /**
         * Retrieves The Admin Email Bcc Headers
         *
         * @param $headers
         *
         * @return mixed
         */
        public function get_admin_bcc_headers($headers)
        {
            $emails = $this->get_admin_email_bcc();
            if (!empty($emails)) {
                $emails = array_filter(explode(',', $emails));
                foreach ((array)$emails as $email) {
                    $headers[] = sprintf('Bcc: %s', sanitize_email($email));
                }
            }

            return $headers;
        }

    }
}