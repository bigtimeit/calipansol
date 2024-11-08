<?php

namespace PWH_DCFH\App\Admin\Controllers;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_SMTP')) {
    class PWH_DCFH_SMTP
    {

        private $smtp_host;

        private $smtp_encryption;

        private $smtp_port;

        private $smtp_autotls;

        private $smtp_auth = false;

        private $smtp_username;

        private $smtp_password;

        private $smtp_from_email;

        private $smtp_from_name;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            $this->smtp_host = pwh_dcfh_helpers()::get_option('pwh_dcfh_smtp_host');
            $this->smtp_encryption = pwh_dcfh_helpers()::get_option('pwh_dcfh_smtp_encryption');
            $this->smtp_port = pwh_dcfh_helpers()::get_option('pwh_dcfh_smtp_port');
            $this->smtp_autotls = pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_smtp_autotls');
            $this->smtp_auth = pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_smtp_authentication');
            $this->smtp_username = pwh_dcfh_helpers()::get_option('pwh_dcfh_smtp_username');
            $this->smtp_password = pwh_dcfh_helpers()::get_option('pwh_dcfh_smtp_password');
            $this->smtp_from_email = pwh_dcfh_helpers()::get_option('pwh_dcfh_smtp_from_email');
            $this->smtp_from_name = pwh_dcfh_helpers()::get_option('pwh_dcfh_smtp_from_name');
            if (pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_smtp_enabled')) {
                add_action('wp_mail_from', [$this, 'maybe_filter_mail_from_email']);
                add_action('wp_mail_from_name', [$this, 'maybe_filter_mail_from_name']);
                add_action('phpmailer_init', [$this, 'maybe_override_phpmailer']);
            }
        }

        /**
         * Filter PHPMAILER
         *
         * @param $php_mailer
         */
        public function maybe_override_phpmailer($php_mailer)
        {
            $php_mailer->isSMTP();
            $php_mailer->Host = $this->smtp_host;
            $php_mailer->Port = $this->smtp_port;
            if ($this->smtp_auth) {
                $php_mailer->SMTPAuth = true;
                $php_mailer->Username = $this->smtp_username;
                $php_mailer->Password = $this->smtp_password;
            }
            if ('none' !== $this->smtp_encryption) {
                $php_mailer->SMTPSecure = $this->smtp_encryption;
            }
            if ($this->smtp_autotls && 'tls' !== $this->smtp_encryption) {
                $php_mailer->SMTPAutoTLS = true;
            }
        }

        /**
         * Filter WP Mail From
         *
         * @param $from_email
         *
         * @return false|mixed|void
         */
        public function maybe_filter_mail_from_email($from_email)
        {
            if (!empty($this->smtp_from_email)) {
                $from_email = $this->smtp_from_email;
            } else {
                $from_email = get_option('admin_email');
            }

            return $from_email;
        }

        /**
         * Filter WP Mail From Name
         *
         * @param $from_name
         *
         * @return false|mixed|void
         */
        public function maybe_filter_mail_from_name($from_name)
        {
            if (!empty($this->smtp_from_name)) {
                $from_name = $this->smtp_from_name;
            } else {
                $from_name = get_option('blogname');
            }

            return $from_name;
        }

    }
}