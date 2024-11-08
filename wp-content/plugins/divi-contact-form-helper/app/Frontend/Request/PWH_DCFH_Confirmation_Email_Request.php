<?php

namespace PWH_DCFH\App\Frontend\Request;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Confirmation_Email_Request')) {
    class PWH_DCFH_Confirmation_Email_Request
    {

        private static $instance = null;

        protected $post_request;

        protected $post_repository;

        public $attachments = [];

        /**
         * Class Construnctor
         */
        private function __construct()
        {
            $this->post_request = new PWH_DCFH_Post_Request(false);
            $this->post_repository = PWH_DCFH_Post_Repository::instance();
            if (pwh_dcfh_helpers()::is_divi_413_1_or_above()) {
                add_action('et_pb_contact_form_submit', [$this, 'maybe_divi_confirmation_email'], 10, 3);
            } else {
                add_action('phpmailer_init', [$this, 'register_phpmailer_action']);
                add_action('pwh_dcfh_confirmation_email', [$this, 'maybe_phpmailer_confirmation_email'], 10, 8);
            }
        }

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Confirmation_Email_Request|null
         */
        public static function instance()
        {
            if (self::$instance == null) {
                self::$instance = new PWH_DCFH_Confirmation_Email_Request();
            }

            return self::$instance;
        }

        /**
         * Retrieves The Request
         *
         * @return PWH_DCFH_Post_Request
         */
        public function get_post_request()
        {
            return $this->post_request;
        }

        /**
         * Retrieves The Request
         *
         * @return PWH_DCFH_Post_Repository
         */
        public function get_post_repository()
        {
            return $this->post_repository;
        }

        /**
         * Send Email To Customer Divi After Form Submit Hook
         *
         * @param $processed_fields_values
         * @param $et_contact_error
         * @param $contact_form_info
         *
         * @return void
         */
        public function maybe_divi_confirmation_email($processed_fields_values, $et_contact_error, $contact_form_info)
        {
            if (!empty($et_contact_error)) {
                return;
            }
            $this->send_email();
        }

        /**
         * Register PHPMAILER Action
         *
         * @param $phpmailer
         */
        public function register_phpmailer_action($phpmailer)
        {
            $phpmailer->action_function = 'pwh_dcfh_confirmation_email';
        }

        /**
         * Send Email To Customer with custom phpmailer hook
         *
         * @param $is_email_sent
         * @param $to
         * @param $cc
         * @param $bcc
         * @param $subject
         * @param $body
         * @param $from
         * @param $extra
         *
         * @return void|null
         */
        public function maybe_phpmailer_confirmation_email($is_email_sent, $to, $cc, $bcc, $subject, $body, $from, $extra)
        {
            if (!$this->get_post_repository()->is_confirmation_email_enabled) {
                return null;
            }
            if ($is_email_sent && did_action('phpmailer_init') === 1) {
                $this->send_email();
            }
        }

        /**
         * Send Email
         *
         * @return void|null
         */
        private function send_email()
        {
            $post_request = $this->get_post_request();
            $to = $post_request->get_contact_email();
            $subject = $post_request->get_confirmation_email_subject();
            $message = $post_request->get_confirmation_email_message();
            if (empty($to) || empty($subject) || empty($message)) {
                return null;
            }
            // From Name
            $from_name = htmlspecialchars_decode(get_bloginfo('name'));
            // From Email
            $moduel_admin_email = $this->get_post_repository()->module_admin_email;
            $from_email = empty($moduel_admin_email) ? get_option('admin_email') : $moduel_admin_email;
            // Host Name
            $host = wp_parse_url(get_bloginfo('url'), 1);
            // Subject
            $subject = pwh_dcfh_email_tags_handler()::set_form_entry_tags_values($subject, $post_request);
            // Message
            $message = pwh_dcfh_email_tags_handler()::set_form_entry_tags_values($message, $post_request);
            $headers = [sprintf('From: %1$s <mail@%2$s>', $from_name, $host), sprintf('Reply-To: %1$s <%2$s>', $from_name, $from_email)];
            if ($this->post_repository->is_confirmation_message_richtext) {
                $message = pwh_dcfh_helpers()::clean_html_email_message($message, true);
                $headers = ['Content-Type: text/html; charset=UTF-8'];
            } else {
                $message = pwh_dcfh_helpers()::clean_html_email_message($message);
            }
            // Message All Fiels Tag Merge
            if (preg_match('/%%dcfh_all_fields%%/i', $message)) {
                $is_richtext = $this->post_repository->is_confirmation_message_richtext;
                $message = str_replace("%%dcfh_all_fields%%", $this->post_request->get_all_fields_data($is_richtext), $message);
            }
            // Fixed Attachments Tag
            $attachments = [];
            preg_match_all("~\[attachments[^][]*]\s*(.+?)\[/attachments]~", $message, $matches);
            if (isset($matches[1][0]) && !empty($matches[1][0])) {
                $attachments_arr = explode(',', $matches[1][0]);
                if (is_array($attachments_arr) && !empty($attachments_arr)) {
                    foreach ($attachments_arr as $attachment) {
                        $attachments[] = get_attached_file($attachment);
                    }
                }
                $attachments = array_filter($attachments);
                $message = preg_replace("/\[attachments.+attachments]/", '', $message);
            }
            wp_mail($to, $subject, $message, $headers, $attachments);
        }
    }
}