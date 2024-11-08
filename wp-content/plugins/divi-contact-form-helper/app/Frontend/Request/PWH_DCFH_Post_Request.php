<?php

namespace PWH_DCFH\App\Frontend\Request;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Post_Request')) {
    class PWH_DCFH_Post_Request
    {

        public $contact_form_num = 0;

        public $contact_form_raw_fields = [];

        public $contact_form_processed_fields = [];

        public $contact_form_fields;

        private $contact_email;

        private $confirmation_email_subject;

        private $confirmation_email_message;

        public $is_email_found = null;

        public $files;

        public $referer_url;

        /**
         * Class Construnctor
         *
         * @param bool $processed_contact_form
         *
         * @since 1.0.0
         */
        public function __construct($processed_contact_form = true)
        {
            global $et_pb_contact_form_num;
            $this->contact_form_num = $et_pb_contact_form_num;
            if ($processed_contact_form) {
                $this->processed_contact_form_entries();
            }
            pwh_dcfh_email_tags_handler()::set_static_tags_values();
        }

        /**
         * Retrieves The Form Endcoded Data
         *
         * @return array
         *
         * @since 1.0.0
         */
        public function get_contact_form_raw_fields()
        {
            return $this->contact_form_raw_fields;
        }

        /**
         * Set The Form Dedcoded Data
         *
         * @param array $contact_form_processed_fields
         *
         * @since 1.0.0
         */
        public function set_contact_form_processed_fields(array $contact_form_processed_fields)
        {
            $this->contact_form_processed_fields = $contact_form_processed_fields;
        }

        /**
         * Retrieves The Form Dedcoded Data
         *
         * @return array
         *
         * @since 1.0.0
         */
        public function get_contact_form_processed_fields()
        {
            return $this->contact_form_processed_fields;
        }

        /**
         * Retrieves The Form Fields
         *
         * @return mixed
         *
         * @since 1.0.0
         */
        public function get_contact_form_fields()
        {
            return $this->contact_form_fields;
        }

        /**
         * Check is Contact Form Processed
         *
         * @return bool
         *
         * @since 1.0.0
         */
        public function is_contact_form_processed()
        {
            $is_nonce_verified = isset($_POST['_wpnonce-et-pb-contact-form-submitted-'.$this->contact_form_num]) && wp_verify_nonce(sanitize_text_field($_POST['_wpnonce-et-pb-contact-form-submitted-'.$this->contact_form_num]),
                    'et-pb-contact-form-submit');
            if ($is_nonce_verified) {
                if (isset($_POST['et_pb_contactform_submit_'.$this->contact_form_num]) && 'et_contact_proccess' === sanitize_text_field($_POST['et_pb_contactform_submit_'.$this->contact_form_num])) {
                    return true;
                }
                if (isset($_POST['pwh_dcfh_et_pb_contactform_submit_'.$this->contact_form_num]) && 'et_contact_proccess' === sanitize_text_field($_POST['pwh_dcfh_et_pb_contactform_submit_'.$this->contact_form_num])) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Retrieves The Contact Email
         *
         * @return mixed
         *
         * @since 1.0.0
         */
        public function get_contact_email()
        {
            return $this->contact_email;
        }

        /**
         * Set Contact Email
         *
         * @param $email
         *
         * @since 1.0.0
         */
        public function set_contact_email($email)
        {
            if (!empty($email)) {
                $this->contact_email = $email;
            }
        }

        /**
         * Retrieves The Customer Email Subject
         *
         * @return mixed
         *
         * @since 1.0.0
         */
        public function get_confirmation_email_subject()
        {
            return $this->confirmation_email_subject;
        }

        /**
         * Set The Customer Email Subject
         *
         * @param $subject
         *
         * @since 1.0.0
         */
        public function set_confirmation_email_subject($subject)
        {
            if (!empty($subject)) {
                $this->confirmation_email_subject = $subject;
            }
        }

        /**
         * Retrieves The Customer Email Message
         *
         * @return mixed
         *
         * @since 1.0.0
         */
        public function get_confirmation_email_message()
        {
            return $this->confirmation_email_message;
        }

        /**
         * Set The Customer Email Message
         *
         * @param $message
         *
         * @since 1.0.0
         */
        public function set_confirmation_email_message($message)
        {
            if (!empty($message)) {
                $this->confirmation_email_message = $message;
            }
        }

        /**
         * Process Form Entries
         *
         * @since 1.0.0
         */
        protected function processed_contact_form_entries()
        {
            $is_nonce_verified = isset($_POST['_wpnonce-et-pb-contact-form-submitted-'.$this->contact_form_num]) && wp_verify_nonce(sanitize_text_field($_POST['_wpnonce-et-pb-contact-form-submitted-'.$this->contact_form_num]),
                    'et-pb-contact-form-submit');
            if ($is_nonce_verified) {
                $contact_form_num = $this->contact_form_num;
                $current_form_fields = isset($_POST["et_pb_contact_email_fields_$contact_form_num"]) ? sanitize_text_field($_POST["et_pb_contact_email_fields_$contact_form_num"]) : null;
                $hidden_form_fields = isset($_POST["et_pb_contact_email_hidden_fields_$contact_form_num"]) ? sanitize_text_field($_POST["et_pb_contact_email_hidden_fields_$contact_form_num"]) : null;
                if (!empty($current_form_fields)) {
                    $fields_data_array = json_decode(str_replace('\\', '', $current_form_fields), true);
                    $fields_data_array = null === $fields_data_array ? [] : $fields_data_array;
                    // Hidden Fields
                    if (!empty($fields_data_array) && is_array($fields_data_array)) {
                        if (!empty($hidden_form_fields)) {
                            $hidden_fields_data_array = json_decode(str_replace('\\', '', $hidden_form_fields), true);
                            $hidden_fields_data_array = null === $hidden_fields_data_array ? [] : $hidden_fields_data_array;
                            if (!empty($hidden_fields_data_array) && is_array($hidden_fields_data_array)) {
                                foreach ($hidden_fields_data_array as $hidden_field) {
                                    $fields_data_array[] = [
                                        'field_id' => 'et_pb_contact_'.$hidden_field.'_'.$contact_form_num,
                                        'original_id' => $hidden_field,
                                        'field_type' => 'input',
                                        'field_label' => $hidden_field
                                    ];
                                }
                            }
                        }
                        // All Data
                        $all_fields = '';
                        $confirmation_request = PWH_DCFH_Confirmation_Email_Request::instance();
                        $post_repository = PWH_DCFH_Post_Repository::instance();
                        list($y, $m) = pwh_dcfh_helpers()::get_subdir();
                        foreach ($fields_data_array as $index => $value) {
                            $field_id = $value['original_id'];
                            $field_type = $value['field_type'];
                            $field_label = pwh_dcfh_helpers()::clean_string($value['field_label']);
                            //$field_id = isset($value['original_id']) ? $value['original_id'] : preg_replace('/[^A-Za-z0-9]/', '_', strtolower($field_label));
                            $field_value = isset($_POST[$value['field_id']]) ? wp_unslash(sanitize_text_field($_POST[$value['field_id']])) : null;
                            if ('text' === $field_type) {
                                $field_value = sanitize_textarea_field($field_value);
                            } elseif ('email' === $field_type) {
                                $field_value = sanitize_email($field_value);
                            } elseif (isset($_POST[$value['field_id'].'_is_file'])) {
                                $field_type = 'file';
                                $this->files[$field_id] = $field_value;
                            } else {
                                $field_value = sanitize_text_field($field_value);
                            }
                            // If input type is file and save to database true save files
                            if ('file' === $field_type && $post_repository->save_files_to_db) {
                                $this->contact_form_processed_fields[] = [
                                    'id' => $field_id,
                                    'label' => $field_label,
                                    'value' => $field_value,
                                    'type' => $field_type,
                                    'subdir' => "$y/$m"];
                            } // If input type is not file and save to database false save
                            elseif ('file' !== $field_type) {
                                $this->contact_form_processed_fields[] = [
                                    'id' => $field_id,
                                    'label' => $field_label,
                                    'value' => $field_value,
                                    'type' => $field_type];
                            }
                            // If email not set from outside check submitter email from input field
                            if (empty($this->contact_email)) {
                                if ('email' === $field_type) {
                                    $this->contact_email = sanitize_email($field_value);
                                    $this->is_email_found = $this->contact_email;
                                } elseif (is_email($field_value)) {
                                    if (!empty($this->is_email_found)) {
                                        $this->contact_email = sanitize_email($this->is_email_found);
                                    } else {
                                        $this->contact_email = sanitize_email($field_value);
                                        $this->is_email_found = $this->contact_email;
                                    }
                                }
                            }
                            $all_fields .= $field_id.',';
                        }
                        // If submitter email not empty set email of submitter
                        if (!empty($this->contact_email)) {
                            $confirmation_request->get_post_request()->set_contact_email($this->contact_email);
                        }
                        $this->contact_form_raw_fields = $fields_data_array;
                        $this->contact_form_fields = substr($all_fields, 0, -1);
                    }
                    $confirmation_request->get_post_request()->set_contact_form_processed_fields($this->contact_form_processed_fields);
                }
            }
        }

        /**
         * Get Attachments From Temp Directory
         *
         * @return array
         *
         * @since 1.0.0
         */
        public function get_attachments_from_tmp_dir()
        {
            $attachments = [];
            $tmp_files = $this->files;
            if (!empty($tmp_files)) {
                foreach ($tmp_files as $tmp_file) {
                    $files = explode(',', $tmp_file);
                    if (!empty($files) && is_array($files)) {
                        $upload_temp_dir = pwh_dcfh_helpers()::get_temp_upload_dir();
                        foreach ($files as $file) {
                            $file_temp_dir = path_join($upload_temp_dir, $file);
                            if (et_()->WPFS()->is_file($file_temp_dir) && et_()->WPFS()->exists($file_temp_dir)) {
                                $attachments[] = $file_temp_dir;
                            }
                        }
                    }
                }
            }

            return $attachments;
        }

        /**
         * Move Attachments To Directory
         *
         * @return array
         *
         * @since 1.0.0
         */
        public function move_attachments_dir()
        {
            $attachments = [];
            $tmp_files = $this->files;
            if (!empty($tmp_files)) {
                $upload_temp_dir = pwh_dcfh_helpers()::get_temp_upload_dir();
                $post_repository = PWH_DCFH_Post_Repository::instance();
                foreach ($tmp_files as $tmp_file) {
                    $files = explode(',', $tmp_file);
                    if (!empty($files) && is_array($files)) {
                        foreach ($files as $file) {
                            $upload_dir = pwh_dcfh_helpers()::upload_files_to_dir($post_repository->contact_form_id, $file);
                            $file_temp_dir = path_join($upload_temp_dir, $file);
                            if (et_()->WPFS()->is_file($file_temp_dir) && et_()->WPFS()->exists($file_temp_dir)) {
                                if (copy($file_temp_dir, $upload_dir['basedir'])) {
                                    $attachments[] = $upload_dir['basedir'];
                                }
                            }
                        }
                    }
                }
            }

            return $attachments;
        }

        /**
         * Get Referer URL
         *
         * @return string
         *
         * @since 1.1
         */
        public function get_referer_url()
        {
            return isset($_POST['et_pb_contact_field_referer_url']) ? sanitize_text_field($_POST['et_pb_contact_field_referer_url']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
        }

        /**
         * Get All Fields Tag Form Data
         *
         * @param $is_richtext
         *
         * @return string
         *
         * @since 1.3
         */
        public function get_all_fields_data($is_richtext = false)
        {
            $processed_fields_values = $this->get_contact_form_processed_fields();
            $line_break = $is_richtext ? "<br>" : "\r\n";
            $all_fields_data = '';
            foreach ($processed_fields_values as $key => $value) {
                if ('file' !== $value['type']) {
                    $field_label = $value['label'];
                    $field_value = !empty($value['value']) ? $value['value'] : '-';
                    if (!$is_richtext) {
                        $all_fields_data .= sprintf('%1$s: %2$s', $field_label, $field_value);
                    } else {
                        $all_fields_data .= sprintf('<b>%1$s:</b> %2$s', $field_label, $field_value);
                    }
                    $all_fields_data .= str_repeat($line_break, 1);
                }
            }

            return $all_fields_data;
        }
    }
}