<?php

namespace PWH_DCFH\App\Base;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Strings')) {
    class PWH_DCFH_Strings
    {
        private static $_instance;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Strings
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public function strings($key)
        {

            $strings = [
                'admin_strings' => [
                    'pleaseWait' => __('Please wait...', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'rSMTPHost' => __('SMPT host required.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'rSMTPFromEmail' => __('SMTP from email required.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'rSMTPFromName' => __('SMTP from name required.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'rSMTPPort' => __('SMTP port required.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'rSMTPUsername' => __('SMPT username required.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'rSMTPPassword' => __('SMTP password required.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'saveSMTPSettings' => __('Have you saved your SMTP settings? Please save the settings before testing SMTP.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'selectTerm' => __('Please Select Term', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'notApplicable' => __('Not Applicable', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'customFieldName' => __('Enter custom field name.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'ChoosecustomFieldName' => __('Choose Custom Field', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'frontend_strings' => [
                    'duplicate_form_id_text' => __('Attention: multiple contact forms currently have the same unique ID. Please open the module settings and open the Admin Label toggle and change the Contact Form Unique ID.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'file_upload_btn_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_file_upload_btn_text', 'Choose Files'),
                    'accepted_file_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_accepted_file_text', 'Accepted file types:'),
                    'max_filesize_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_max_filesize_text', 'Max. file size:'),
                    'chosen_file_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_chosen_file_text', 'No file chosen'),
                    'selected_files_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_selected_files_text', '{chosen_files_count} file{s} selected'),
                    'uploading_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_uploading_text', '{percentage} uploaded your files, please wait for the system to continue.'),
                    'already_attached_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_already_attached_text', 'File {filename} has already attached. Please choose another file.'),
                    'allow_filesize_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_allow_filesize_text', 'File {filename} not uploaded. Maximum file size {allowed_filesize}.'),
                    'x_files_allow_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_x_files_allow_text', 'Only {allowed_files} files are allowed to upload.'),
                    'remove_file_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_remove_file_text', 'Are you sure you want to remove {filename}?'),
                    'pwh_dcfh_security_reason_text' => pwh_dcfh_helpers()::get_option('pwh_dcfh_security_reason_text', 'File {filename} has failed to upload. Sorry, this file type is not permitted for security reasons.'),
                ]
            ];

            return $strings[$key];
        }
    }
}