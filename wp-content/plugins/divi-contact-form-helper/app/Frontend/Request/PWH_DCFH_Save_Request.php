<?php

namespace PWH_DCFH\App\Frontend\Request;

use PWH_DCFH\App\Base\PWH_DCFH_Strings;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Save_Request')) {
    class PWH_DCFH_Save_Request
    {
        private static $is_save_entry = false;

        private $post_request;

        private $post_repository;

        private $temp_upload_dir;

        private $temp_upload_url;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            add_filter('et_contact_page_headers', [$this, 'maybe_filter_contact_page_headers'], 10, 3);
            add_action('wp_ajax_upload_file', [$this, 'maybe_upload_file']);
            add_action('wp_ajax_nopriv_upload_file', [$this, 'maybe_upload_file']);
            add_action('wp_ajax_remove_file', [$this, 'maybe_remove_file']);
            add_action('wp_ajax_nopriv_remove_file', [$this, 'maybe_remove_file']);
            $this->temp_upload_dir = pwh_dcfh_helpers()::get_temp_upload_dir();
            $this->temp_upload_url = pwh_dcfh_helpers()::get_temp_upload_url();
        }

        /**
         * Filter et_contact_page_headers
         *
         * @param $headers
         * @param $contact_name
         * @param $contact_email
         *
         * @return mixed
         */
        public function maybe_filter_contact_page_headers($headers, $contact_name, $contact_email)
        {
            if (self::$is_save_entry) {
                return $headers;
            }
            $this->post_request = new PWH_DCFH_Post_Request();
            $this->post_repository = PWH_DCFH_Post_Repository::instance();
            /**
             * Set Contact Email From Contact Form Headers if exist
             * @since 1.0.0
             */
            $this->post_request->set_contact_email($contact_email);
            /**
             * If save entry enabled save entries to db
             * @since 1.0.0
             */
            if ($this->post_repository->save_entry_to_db && !empty($this->post_repository->contact_form_id)) {
                $this->save_entry();
            }
            /**
             * Copy Files from temp to final directory if save files true
             * @since 1.0.0
             */
            if ($this->post_repository->save_files_to_db) {
                $this->post_request->move_attachments_dir();
            }
            /**
             * Set Custom Data Tags Values Before Email Send
             * @since 1.1
             */
            pwh_dcfh_email_tags_handler()::set_contact_entry_number_tag_value($this->post_repository->entry_number);
            pwh_dcfh_email_tags_handler()::set_contact_form_id_tag_value($this->post_repository->contact_form_id);
            pwh_dcfh_email_tags_handler()::set_contact_form_title_tag_value($this->post_repository->contact_form_title);
            /**
             * Filter WP Mail Before Send
             * @since 1.0.0
             */
            $this->maybe_filter_wp_mail();
            /**
             * If Zapier settings enabled send email BCC to zapier
             * @since 1.0.0
             */
            if ($this->post_repository->is_zapier_enabled) {
                $headers[] = sprintf('Bcc: %s', sanitize_email($this->post_repository->zapier_mailbox_address));
            }
            /**
             * If Pabbly settings enabled send email BCC to zapier
             * @since 1.0.3
             */
            if ($this->post_repository->is_pabbly_enabled) {
                $headers[] = sprintf('Bcc: %s', sanitize_email($this->post_repository->pabbly_mailbox_address));
            }
            /**
             * Admin Email Cc
             * @since 1.1
             */
            $headers = $this->post_repository->get_admin_cc_headers($headers);
            $headers = $this->post_repository->get_admin_bcc_headers($headers);
            /**
             * Admin Formatted Message
             * @since 1.3
             */
            if ($this->post_repository->is_custom_message_richtext) {
                $headers = ['Content-Type: text/html; charset=UTF-8'];
            }
            // Save Entries True Now
            self::$is_save_entry = true;

            return $headers;
        }

        /**
         * Process Save Entries
         *
         *
         * @since 1.0.0
         */
        private function save_entry()
        {
            add_filter('wp_insert_post_data', [$this, 'maybe_filter_post_insert_data'], PHP_INT_MAX, 2);
            global $current_user;
            $user_id = isset($current_user->ID) ? $current_user->ID : 0;
            $post_id = wp_insert_post([
                'post_type' => pwh_dcfh_hc()::POST_TYPE,
                'post_name' => uniqid('entry-'),
                'post_content' => maybe_serialize($this->post_request->get_contact_form_raw_fields()),
                'post_excerpt' => maybe_serialize($this->post_request->get_contact_form_processed_fields()),
                'post_status' => 'draft',
                'post_author' => $user_id,
                'post_modified' => '0000-00-00 00:00:00',
                'post_modified_gmt' => '0000-00-00 00:00:00',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ], true);
            if (!is_wp_error($post_id)) {
                // Page ID
                update_post_meta($post_id, pwh_dcfh_hc()::CF_PAGE_ID_META_KEY, get_the_ID());
                // Contact Form ID
                update_post_meta($post_id, pwh_dcfh_hc()::CF_FORM_ID_META_KEY, $this->post_repository->get_contact_form_id());
                // Contact Form Fields
                update_post_meta($post_id, pwh_dcfh_hc()::CF_FIELDS_META_KEY, $this->post_request->get_contact_form_fields());
                // Contact Form Email
                update_post_meta($post_id, pwh_dcfh_hc()::CF_CONTACT_EMAIL_META_KEY, $this->post_request->get_contact_email());
                // Referer URL
                update_post_meta($post_id, pwh_dcfh_hc()::CF_REFERER_URL_META_KEY, $this->post_request->get_referer_url());
                if ($this->post_repository->collect_ip_useragent_details) {
                    // IP Address
                    update_post_meta($post_id, pwh_dcfh_hc()::CF_IP_ADDRESS_META_KEY, pwh_dcfh_helpers()::get_ip_address());
                    // User Agent
                    update_post_meta($post_id, pwh_dcfh_hc()::CF_USER_AGEN_META_KEY, pwh_dcfh_helpers()::get_user_agent());
                }
                // Update Post Title
                $this->post_repository->entry_number = $post_id;
                $post_title = 'entry-'.$post_id;
                wp_update_post(['ID' => $post_id, 'post_title' => $post_title]);
            }
            remove_filter('wp_insert_post_data', [$this, 'maybe_filter_post_insert_data'], PHP_INT_MAX);
        }

        /**
         * Filter WP Mail
         *
         * @return void
         */
        private function maybe_filter_wp_mail()
        {
            /**
             * Filter WP Mail Attributes
             * @since 1.0.0
             */
            add_filter('wp_mail', function ($attr) {
                $_wpnonce = isset($_POST['_wpnonce-et-pb-contact-form-submitted-'.$this->post_request->contact_form_num]) && wp_verify_nonce(sanitize_text_field($_POST['_wpnonce-et-pb-contact-form-submitted-'.$this->post_request->contact_form_num]),
                        'et-pb-contact-form-submit');
                if ($_wpnonce) {
                    // Prevents Infinite Loop
                    if (array_key_exists('et_pb_contactform_submit_'.$this->post_request->contact_form_num, wp_unslash($_POST))) {
                        if (!isset($_POST['pwh_dcfh_et_pb_contactform_submit_'.$this->post_request->contact_form_num])) {
                            $_POST['pwh_dcfh_et_pb_contactform_submit_'.$this->post_request->contact_form_num] = 'et_contact_proccess';
                        }
                        unset($_POST['et_pb_contactform_submit_'.$this->post_request->contact_form_num]);
                        $message = $attr['message'];
                        /**
                         * Send Attachments in email
                         * @since 1.0.0
                         */
                        $attachments = $this->post_request->get_attachments_from_tmp_dir();
                        $attached_file_message = null;
                        if (!empty($attachments) && $this->post_repository->send_files_as_attachment) {
                            $attr['attachments'] = $attachments;
                            /*
                             * Attachment Custom Message
                             * @since  1.0.1
                             */
                            $attached_file_message = $this->post_repository->attached_files_message;
                        }
                        // Email Subject Merge Tags
                        $subject = $this->post_repository->get_admin_email_subject();
                        if (!empty($subject)) {
                            $subject = pwh_dcfh_email_tags_handler()::set_form_entry_tags_values($subject, $this->post_request);
                            $attr['subject'] = $subject;
                        }
                        // Message Merge Tags
                        $message = pwh_dcfh_email_tags_handler()::set_form_entry_tags_values($message, $this->post_request);
                        /*
                        * Message All Fiels Tag Merge
                        * @since  1.3
                        */
                        if (preg_match('/%%dcfh_all_fields%%/i', $message)) {
                            $is_richtext = $this->post_repository->is_custom_message_richtext;
                            $message = str_replace("%%dcfh_all_fields%%", $this->post_request->get_all_fields_data($is_richtext), $message);
                        }
                        // Replaced Final Message
                        $attr['message'] = $message.PHP_EOL.$attached_file_message;
                    }
                }

                return $attr;
            });
        }

        /**
         * Process File Upload
         * @since 1.0.0
         */
        public function maybe_upload_file()
        {
            if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_POST['_wpnonce']);
                if (wp_verify_nonce($_wpnonce, pwh_dcfh_hc()::AJAX_NONCE) && (isset($_FILES) && !empty($_FILES))) {
                    $json_response = [
                        'success' => [],
                        'errors' => [],
                    ];
                    $errors = [];
                    $upload_temp_dir = $this->temp_upload_dir;
                    $upload_temp_url = $this->temp_upload_url;
                    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : null;
                    if (empty($token)) {
                        wp_send_json_error(esc_html__('Unable to upload this file, please refresh and try again.', pwh_dcfh_hc()::TEXT_DOMAIN));
                        wp_die();
                    }
                    $wp_allowed_mime_types = pwh_dcfh_helpers()::get_wp_allowed_mime_types();
                    $token = json_decode(pwh_dcfh_helpers()::encrypt_decrypt($token, 'd'), ARRAY_A);
                    $allowd_filesize = isset($token['size']) ? $token['size'] : '';
                    $allowd_mimes = isset($token['mimetypes']) ? explode(',', $token['mimetypes']) : [];
                    $allowd_extentions = isset($token['extentions']) ? explode(',', $token['extentions']) : [];
                    // $this->maybe_wp_check_filetype_and_ext($allowd_extentions);
                    // Before Upload
                    $frontend_strings = (new PWH_DCFH_Strings())->instance()->strings('frontend_strings');
                    foreach ($_FILES as $file) {
                        if (isset($file['error']) && UPLOAD_ERR_OK === $file['error']) {
                            $filename = sanitize_file_name($file['name']);
                            $file_tmp_name = $file['tmp_name'];
                            $file_type = $file['type'];
                            $file_size = $file['size'];
                            $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
                            $file_real_mime = '';
                            if (extension_loaded('fileinfo')) {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $file_real_mime = finfo_file($finfo, $file_tmp_name);
                                finfo_close($finfo);
                            }
                            $wp_filetype = wp_check_filetype_and_ext($file_tmp_name, $filename, $wp_allowed_mime_types);
                            // Check Type & Ext
                            if (empty($wp_filetype['type']) || empty($wp_filetype['ext'])) {
                                $error_msg = str_replace('{filename}', $filename, $frontend_strings['pwh_dcfh_security_reason_text']);
                                $errors[$filename] = $error_msg;
                                continue;
                            }
                            //  Mime
                            if (!in_array($file_real_mime, $allowd_mimes) || !in_array($file_real_mime, $wp_allowed_mime_types)) {
                                $error_msg = str_replace('{filename}', $filename, $frontend_strings['pwh_dcfh_security_reason_text']);
                                $errors[$filename] = $error_msg;
                                continue;
                            }
                            // Check File Size
                            if (($file_size > wp_max_upload_size()) || ($file_size > $allowd_filesize)) {
                                $error_msg = str_replace('{filename}', $filename, $frontend_strings['pwh_dcfh_allow_filesize_text']);
                                $error_msg = str_replace('{filesize}', $filename, $frontend_strings['pwh_dcfh_allow_filesize_text']);
                                $errors[$filename] = $error_msg;;
                            }
                        }
                    }
                    // Upload File
                    foreach ($_FILES as $file) {
                        if (isset($file['error']) && UPLOAD_ERR_OK === $file['error']) {
                            $filename = sanitize_file_name($file['name']);
                            $file_tmp_name = $file['tmp_name'];
                            $wp_filetype = wp_check_filetype_and_ext($file_tmp_name, $filename, $wp_allowed_mime_types);
                            if (isset($errors[$filename])) {
                                $json_response['errors'][] = [
                                    'name' => $filename,
                                    'message' => $errors[$filename],
                                ];
                            } else {
                                $filename_renamed = strtolower(pathinfo($filename, PATHINFO_FILENAME));
                                $filename_renamed = preg_replace('/[^A-Za-z\d\-]/', ' ', $filename_renamed);
                                $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
                                $filename_unique = wp_unique_filename($upload_temp_dir,
                                    sprintf('%1$s-%2$s-%3$s.%4$s', mb_substr($filename_renamed, 0, 30, 'UTF-8'), str_pad(wp_rand(999, time()), 5, 0, STR_PAD_BOTH), time(), $file_extension));
                                $file_dir = path_join($upload_temp_dir, $filename_unique);
                                if (copy($file_tmp_name, $file_dir)) {
                                    $file_url = path_join($upload_temp_url, $filename_unique);
                                    $json_response['success'][] = [
                                        'tmp_name' => $filename,
                                        'name' => $filename_unique,
                                        'size' => size_format(filesize($file_dir)),
                                        'mime' => $wp_filetype['type'],
                                        'url' => $file_url,
                                    ];
                                }
                            }
                        }
                    }
                    array_filter($json_response);
                    if (!empty($json_response)) {
                        wp_send_json_success($json_response);
                        wp_die();
                    }
                } else {
                    wp_send_json_error(__('Unable to upload this file, please refresh and try again.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    wp_die();
                }
            }
        }

        /**
         * When you upload a file, WordPress does some security checks on the file in the wp_check_filetype_and_ext
         * function in wp-include/functions.php:2503. Part of these checks is to validate the given mimetype of the
         * file with the mimetype that PHP detects, using the PHP function finfo_file().
         *
         * @param $allowd_extentions
         *
         * @return void
         */
        public function maybe_wp_check_filetype_and_ext($allowd_extentions)
        {
            add_filter('wp_check_filetype_and_ext', function ($types, $file, $filename, $mimes) use ($allowd_extentions) {
                $wp_filetype = wp_check_filetype($filename, $mimes);
                $ext = $wp_filetype['ext'];
                $type = $wp_filetype['type'];
                if (in_array($ext, $allowd_extentions)) {
                    $types['ext'] = $ext;
                    $types['type'] = $type;
                }

                return $types;
            }, 12, 4);
        }

        /**
         * Process File Remove
         * @since 1.0.0
         */
        public function maybe_remove_file()
        {
            if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_POST['_wpnonce']);
                if (wp_verify_nonce($_wpnonce, pwh_dcfh_hc()::AJAX_NONCE)) {
                    $filename = isset($_POST['file_name']) ? sanitize_text_field($_POST['file_name']) : null;
                    if (!empty($filename)) {
                        $tmp_path = path_join($this->temp_upload_dir, $filename);
                        if (et_()->WPFS()->is_file($tmp_path) && et_()->WPFS()->exists($tmp_path)) {
                            wp_delete_file($tmp_path);
                            wp_send_json_success(esc_html__('File Deleted Successfully!', pwh_dcfh_hc()::TEXT_DOMAIN));
                        } else {
                            wp_send_json_error(esc_html__('Something went wrong. Please upload file again.', pwh_dcfh_hc()::TEXT_DOMAIN));
                        }
                        wp_die();
                    }
                }
            }
        }

        /**
         * Set Post Modified Date Nulled When Entry Save
         *
         * @param $data
         * @param $post_arr
         *
         * @return mixed
         */
        public function maybe_filter_post_insert_data($data, $post_arr)
        {
            if (!empty($post_arr['post_modified']) && !empty($post_arr['post_modified_gmt'])) {
                $data['post_modified'] = '0000-00-00 00:00:00';
                $data['post_modified_gmt'] = '0000-00-00 00:00:00';
            }

            return $data;
        }

    }
}