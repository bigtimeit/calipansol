<?php

namespace PWH_DCFH\App\Helpers;

use Exception;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Email_Helper')) {
    class PWH_DCFH_Email_Helper
    {

        private $opening_tag = '%%';

        private $closing_tag = '%%';

        private $email_to;

        private $email_from;

        private $email_from_name;

        private $email_subject;

        private $email_body = [];

        private $email_template;

        private $email_headers = [];

        private $email_attachments = [];

        /**
         * Class Construnctor
         *
         * @param $email_to
         */
        public function __construct($email_to)
        {
            $this->email_to = $email_to;
            $this->set_email_from();
            $this->set_email_from_name();
        }

        /**
         * Send Email
         *
         * @return bool
         * @throws Exception
         */
        public function send()
        {
            $this->validate();
            if (wp_mail($this->get_email_to(), $this->get_email_subject(), $this->get_email_body(), $this->get_email_headers(), $this->get_email_attachments())) {
                return true;
            }

            return false;
        }

        /**
         * Delete Attachments
         */
        public function delete_attachment()
        {
            $attachments = $this->get_email_attachments();
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    wp_delete_file($attachment);
                }
            }
        }

        /**
         * WP MAIL Filters
         */
        public function set_wp_mail_filters()
        {
            add_filter('wp_mail_content_type', function () {
                return 'text/html';
            });
            add_filter('wp_mail_from', function ($email) {
                return $this->get_email_from();
            }, 10, 1);
            add_filter('wp_mail_from_name', function ($name) {
                return $this->get_email_from_name();
            }, 10, 1);
        }

        /**
         * Retrieves The Email To
         * @return mixed
         */
        public function get_email_to()
        {
            return $this->email_to;
        }

        /**
         * Set The Email To
         *
         * @param $email_to
         */
        public function set_email_to($email_to)
        {
            $this->email_to = $email_to;
        }

        /**
         * Retrieves The Email From
         * @return mixed
         */
        public function get_email_from()
        {
            return $this->email_from;
        }

        /**
         * Set The Email From
         *
         * @param null $email_from
         *
         * @return false|mixed|void|null
         */
        public function set_email_from($email_from = null)
        {
            $this->email_from = $email_from;
            if (empty($email_from)) {
                $this->email_from = get_option('admin_email');
            }

            return $this->email_from;
        }

        /**
         * Retrieves The Email From Name
         * @return mixed
         */
        public function get_email_from_name()
        {
            return $this->email_from_name;
        }

        /**
         * Set The Email From Name
         *
         * @param null $email_from_name
         *
         * @return string|null
         */
        public function set_email_from_name($email_from_name = null)
        {
            if (empty($email_from_name)) {
                $email_from_name = htmlspecialchars_decode(get_bloginfo('name'));
            }
            $this->email_from_name = $email_from_name;

            return $this->email_from_name;
        }

        /**
         * Retrieves The Email Subject
         *
         * @return mixed
         */
        public function get_email_subject()
        {
            return $this->email_subject;
        }

        /**
         * Set The Email Subject
         *
         * @param $email_subject
         */
        public function set_email_subject($email_subject)
        {
            $this->email_subject = $email_subject;
        }

        /**
         * Retrieves The Email Body
         *
         * @return array
         */
        public function get_email_body()
        {
            return $this->email_body;
        }

        /**
         * Set The Email Body
         *
         * @param array $email_body_arr
         *
         * @return array|string|string[]
         */
        public function set_email_body(array $email_body_arr)
        {
            $email_body = [];
            foreach ($email_body_arr as $key => $value) {
                if (isset($value) && $value != null) {
                    $email_body[$this->opening_tag.$key.$this->closing_tag] = $value;
                }
            }
            $this->email_body = str_replace(array_keys($email_body), array_values($email_body), $this->get_email_template());

            return $this->email_body;
        }

        /**
         * Retrieves The Heaaders
         *
         * @return array
         */
        public function get_email_headers()
        {
            return $this->email_headers;
        }

        /**
         * Set The Headers
         *
         * @param array $email_headers
         */
        public function set_email_headers(array $email_headers = [])
        {
            $this->email_headers = $email_headers;
        }

        /**
         * Retrieves The Attachments
         *
         * @return array
         */
        public function get_email_attachments()
        {
            return $this->email_attachments;
        }

        /**
         * Set The Attachments
         *
         * @param array $email_attachments
         */
        public function set_email_attachments(array $email_attachments = [])
        {
            $this->email_attachments = $email_attachments;
        }

        /**
         * Retrieves The Email TPL
         *
         * @return mixed
         */
        public function get_email_template()
        {
            return $this->email_template;
        }

        /**
         * Set The Email TPL
         *
         * @param $email_template
         *
         * @return string
         */
        public function set_email_template($email_template)
        {
            if (is_file($email_template)) {
                try {
                    global $wp_filesystem;
                    if ($wp_filesystem->exists($email_template)) {
                        $this->email_template = $wp_filesystem->get_contents($email_template);
                    } else {
                        throw new Exception(esc_html('ERROR: Invalid Email Template Filepath.'));
                    }
                } catch (Exception $e) {
                    //error_log($e->getMessage().' | FILE: '.$e->getFile().' | LINE: '.$e->getLine());
                }
            } else {
                try {
                    if (is_string($email_template)) {
                        $this->email_template = $email_template;
                    } else {
                        throw new Exception(esc_html("ERROR: Invalid Email Template. $email_template must be a String."));
                    }
                } catch (Exception $e) {
                    //error_log($e->getMessage().' | FILE: '.$e->getFile().' | LINE: '.$e->getLine());
                }
            }

            return $this->email_template;
        }

        /**
         * Valide Requirements
         *
         * @throws Exception
         */
        private function validate()
        {
            if (empty($this->get_email_template())) {
                throw new Exception(esc_html('ERROR: Email Template Required.'));
            }
            if (empty($this->get_email_to())) {
                throw new Exception(esc_html('ERROR: Email To Required.'));
            }
            if (empty($this->get_email_subject())) {
                throw new Exception(esc_html('ERROR: Email Subject Required.'));
            }
            if (empty($this->get_email_body())) {
                throw new Exception(esc_html('ERROR: Email Body Required.'));
            }
        }

    }
}