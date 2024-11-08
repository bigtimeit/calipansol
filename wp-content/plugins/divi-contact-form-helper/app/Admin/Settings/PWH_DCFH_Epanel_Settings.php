<?php

namespace PWH_DCFH\App\Admin\Settings;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Epanel_Settings')) {
    class PWH_DCFH_Epanel_Settings
    {

        private $wp_mail_failed;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            add_filter('et_epanel_tab_names', [$this, 'register_tab']);
            add_filter('et_epanel_layout_data', [$this, 'register_content']);
            add_action('admin_init', [$this, 'admin_hooks']);
            add_action('admin_menu', [$this, 'add_setting_links']);
        }

        public function add_setting_links()
        {
            global $submenu;
            $menu_page = pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG;
            // phpcs:disable
            $submenu[$menu_page][] = [
                __('Backups', pwh_dcfh_hc()::TEXT_DOMAIN),
                'manage_options',
                esc_url(admin_url('admin.php?page=et_divi_options#wrap-pwh-dcfh-epanel'))
            ];
            $submenu[$menu_page][] = [
                __('SMTP', pwh_dcfh_hc()::TEXT_DOMAIN),
                'manage_options',
                esc_url(admin_url('admin.php?page=et_divi_options#wrap-pwh-dcfh-epanel'))
            ];
            // phpcs:enable
        }

        /**
         * Register Admin Hooks
         */
        public function admin_hooks()
        {
            add_action('wp_mail_failed', [$this, 'get_wp_mail_failed_error']);
            add_action('wp_ajax_process_smtp_test', [$this, 'process_smtp_test']);
        }

        /**
         * Add Custom Tab To Panel
         *
         * @param $tabs
         *
         * @return array
         */
        public function register_tab($tabs)
        {
            return array_merge($tabs, ['pwh-dcfh-epanel' => __(pwh_dcfh_helpers()::plugin()->Name, pwh_dcfh_hc()::TEXT_DOMAIN)]);
        }

        /**
         * Add Options To Custom Tab
         *
         * @param $options
         *
         * @return mixed
         */
        public function register_content($options)
        {

            $options[] = [
                'name' => 'wrap-pwh-dcfh-epanel',
                'type' => 'contenttab-wrapstart',
            ];
            // Sub Navs
            $options[] = [
                'type' => 'subnavtab-start',
            ];
            $options[] = [
                'name' => 'pwh-dcfh-tab-general',
                'type' => 'subnav-tab',
                'desc' => __('General Settings', pwh_dcfh_hc()::TEXT_DOMAIN)
            ];
            $options[] = [
                'name' => 'pwh-dcfh-tab-smtp',
                'type' => 'subnav-tab',
                'desc' => __('SMTP Settings', pwh_dcfh_hc()::TEXT_DOMAIN)
            ];
            $options[] = [
                'name' => 'pwh-dcfh-tab-auto-backup',
                'type' => 'subnav-tab',
                'desc' => __('Auto Backup', pwh_dcfh_hc()::TEXT_DOMAIN)
            ];
            $options[] = [
                'name' => 'pwh-dcfh-tab-labels',
                'type' => 'subnav-tab',
                'desc' => __('Custom Label Text', pwh_dcfh_hc()::TEXT_DOMAIN)
            ];
            $options[] = [
                'type' => 'subnavtab-end',
            ];
            // General
            $options = $this->general_fields($options);
            // SMTP
            $options = $this->smpt_fields($options);
            // Auto Backup Fields
            $options = $this->auto_backup_fields($options);
            // Custom Label Text
            $options = $this->custom_label_text_fields($options);
            // End
            $options[] = [
                'name' => 'wrap-pwh-dcfh-epanel',
                'type' => 'contenttab-wrapend',
            ];

            return $options;
        }

        private function general_fields($options)
        {
            $options[] = [
                'name' => 'pwh-dcfh-tab-general',
                'type' => 'subcontent-start',
            ];
            $options[] = [
                'name' => __('Enable Contact Forms Stats', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_stats_enabled',
                'type' => 'checkbox',
                'desc' => __('Choose to show contact form data stats widget on admin dashboard.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'off',
            ];
            $options[] = [
                'name' => __('Enable Sent Email Logs', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_enable_sent_email_log',
                'type' => 'checkbox',
                'desc' => __('Choose to log email sent history to submitter from send email page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'off',
            ];
            $options[] = [
                'name' => __('Enable Clone Logs', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_enable_clone_log',
                'type' => 'checkbox',
                'desc' => __('Choose to log clone history of contact form data to other post types.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'off',
            ];
            $options[] = [
                'name' => __('Remove Plugin Data When Uninstalled', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_delete_plugin_data',
                'type' => 'checkbox',
                'desc' => __('This setting will remove all plugin data and saved settings when you uninstall the plugin. Please use this setting carefully with the understanding that if you enable the setting you will lose all saved settings if you uninstall the plugin.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'off',
            ];
            $options[] = [
                'name' => 'pwh-dcfh-tab-general',
                'type' => 'subcontent-end',
            ];

            return $options;
        }

        private function smpt_fields($options)
        {
            $options[] = [
                'name' => 'pwh-dcfh-tab-smtp',
                'type' => 'subcontent-start',
            ];
            $options[] = [
                'name' => __('Enable SMTP Settings', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_enabled',
                'type' => 'checkbox2',
                'desc' => __('Choose to use SMTP configuration when sending emails WordPress must utilise a legitimate SMTP provider.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'off',
            ];
            $options[] = [
                'name' => __('SMTP Host', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_host',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('This is the SMTP host address provided by your hosting company.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => '',
            ];
            $options[] = [
                'name' => __('SMTP From Email', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_from_email',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('This is the email address which will be used to send all WordPress emails.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => get_option('admin_email'),
            ];
            $options[] = [
                'name' => __('SMTP From Name', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_from_name',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('This name will be used to send emails. We recommend using your website’s title as from name.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => get_option('blogname'),
            ];
            $options[] = [
                'name' => __('Encryption', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_encryption',
                'type' => 'select',
                'options' => [
                    'none' => __('None', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'ssl' => __('SSL', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'tls' => __('TLS', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'et_save_values' => true,
                'desc' => __('The encryption method used by your mail server to send emails. Usually it is TLS.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'none',
            ];
            $options[] = [
                'name' => __('SMTP Port', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_port',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('This is the port used by outgoing mail server.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => '25',
            ];
            $options[] = [
                'name' => __('Auto TLS', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_autotls',
                'type' => 'checkbox2',
                'desc' => __('By default, TLS encryption is automatically used if the server supports it (recommended). In some cases, due to server misconfigurations, this can cause issues and may need to be disabled.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'off',
            ];
            $options[] = [
                'name' => __('Authentication', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_authentication',
                'type' => 'checkbox2',
                'desc' => __('Email senders (clients) must have permission to use the email server in order to use SMTP authentication. Outgoing messages can only be sent by authorised users.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'off',
            ];
            $options[] = [
                'name' => __('SMTP Username', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_username',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('This is usually the email address you are using to send emails.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => '',
            ];
            $options[] = [
                'name' => __('SMTP Password', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_smtp_password',
                'type' => 'password',
                'validation_type' => 'nohtml',
                'desc' => __('This is the password for the email account you are using to send emails.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => '',
            ];
            $options[] = [
                'name' => '',
                'id' => 'pwh_dcfh_smtp_test',
                'type' => 'callback_function',
                'function_name' => [$this, 'render_smtp_button'],
                'desc' => '',
            ];
            $options[] = [
                'name' => 'pwh-dcfh-tab-smtp',
                'type' => 'subcontent-end',
            ];

            return $options;
        }

        private function auto_backup_fields($options)
        {
            $options[] = [
                'name' => 'pwh-dcfh-tab-auto-backup',
                'type' => 'subcontent-start',
            ];
            $options[] = [
                'name' => __('Enable Auto Backup', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_backup_enabled',
                'type' => 'checkbox2',
                'desc' => __('Choose to create an automatic backup of your contact form data.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'off',
            ];
            $options[] = [
                'name' => __('Auto Backup Email Address', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_backup_email',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter the email address where you want to receive the auto backup file. The default is the site administrator email address.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => get_option('admin_email'),
            ];
            $options[] = [
                'name' => __('Auto Backup Schedule', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_backup_schedule',
                'type' => 'select',
                'options' => pwh_dcfh_helpers()::get_get_schedules(),
                'et_save_values' => true,
                'desc' => __('Select the desired frequency schedule for the auto backup.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'weekly',
            ];
            $options[] = [
                'name' => 'pwh-dcfh-tab-auto-backup',
                'type' => 'subcontent-end',
            ];

            return $options;
        }

        private function custom_label_text_fields($options)
        {
            $options[] = [
                'name' => 'pwh-dcfh-tab-labels',
                'type' => 'subcontent-start',
            ];
            $options[] = [
                'name' => '',
                'id' => 'pwh_dcfh_divider_1',
                'type' => 'callback_function',
                'function_name' => function () {
                    echo sprintf('<b>%s</b>', __('File Upload Custom Labels', pwh_dcfh_hc()::TEXT_DOMAIN)); // phpcs:ignore
                },
                'desc' => '',
            ];
            $options[] = [
                'name' => __('Upload File Button Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_file_upload_btn_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for upload file button.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'Choose Files',
            ];
            $options[] = [
                'name' => __('Accepted File Types Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_accepted_file_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for accepted file types.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'Accepted file types:',
            ];
            $options[] = [
                'name' => __('Max File Size Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_max_filesize_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for max file size.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'Max. file size:',
            ];
            $options[] = [
                'name' => __('No Files Chosen Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_chosen_file_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for no file chosen.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'No Files Chosen',
            ];
            $options[] = [
                'name' => __('Number Of Chosen Files Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_selected_files_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for selected files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => '{chosen_files_count} file{s} selected',
            ];
            $options[] = [
                'name' => __('Uploading Files Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_uploading_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text while uploading files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => '{percentage} uploaded your files, please wait for the system to continue.',
            ];
            $options[] = [
                'name' => __('File Already Attached Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_already_attached_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for files already attached.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'File {filename} has already attached. Please choose another file.',
            ];
            $options[] = [
                'name' => __('File Size Warning Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_allow_filesize_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for maximum allow file size.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'File {filename} not uploaded. Maximum file size {allowed_filesize}.',
            ];
            $options[] = [
                'name' => __('Number Of Allowed Files Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_x_files_allow_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for allow to upload only number of files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'Only {allowed_files} files are allowed to upload.',
            ];
            $options[] = [
                'name' => __('Remove File Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_remove_file_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for remove file.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'Are you sure you want to remove {filename}?',
            ];
            $options[] = [
                'name' => __('Not Alllowed To Upload', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_security_reason_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for file type not allowed to upload.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'File {filename} has failed to upload. Sorry, this file type is not permitted for security reasons.',
            ];
            $options[] = [
                'name' => __('Filesize To Upload', pwh_dcfh_hc()::TEXT_DOMAIN),
                'id' => 'pwh_dcfh_allowedfilesize_text',
                'type' => 'text',
                'validation_type' => 'nohtml',
                'desc' => __('Enter custom text for max file size allowed to upload.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'std' => 'File {filename} not uploaded. Maximum file size {filesize}',
            ];
            $options[] = [
                'name' => 'pwh-dcfh-tab-labels',
                'type' => 'subcontent-end',
            ];

            return $options;
        }

        /**
         * SMTP Test Button HTML
         */
        public function render_smtp_button()
        {
            ?>
            <button type="button" id="pwh-dcfh-smtp-button" class="et-button pwh-dcfh-smtp-button">
                <?php esc_html_e('Check SMTP Integration Working', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
            </button><p id="pwh-dcfh-smtp-response" class="pwh-dcfh-smtp-response"></p>
            <?php
        }

        /**
         * Get Wp Mail Errors
         *
         * @param $wp_error
         */
        public function get_wp_mail_failed_error($wp_error)
        {
            if (isset($wp_error->errors)) {
                $wp_mail_failed = isset($wp_error->errors['wp_mail_failed']) ? $wp_error->errors['wp_mail_failed'] : null;
                if (!empty($wp_mail_failed)) {
                    $this->wp_mail_failed = implode(', ', $wp_mail_failed);
                }
            }
        }

        /**
         * Process SMPTP Intgeration Test
         */
        public function process_smtp_test()
        {
            if (isset($_REQUEST['_wpnonce']) && !empty($_REQUEST['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_REQUEST['_wpnonce']);
                if (wp_verify_nonce($_wpnonce, 'admin-ajax-nonce')) {
                    $smtp_settings = get_option('et_divi');
                    if (isset($smtp_settings['pwh_dcfh_smtp_enabled'])) {
                        $errors = [];
                        if ('on' !== trim($smtp_settings['pwh_dcfh_smtp_enabled'])) {
                            $errors[] = __('Please save changes.', pwh_dcfh_hc()::TEXT_DOMAIN);
                        }
                        if ('' === trim($smtp_settings['pwh_dcfh_smtp_host'])) {
                            $errors[] = __('SMTP Host required.', pwh_dcfh_hc()::TEXT_DOMAIN);
                        }
                        if ('on' === trim($smtp_settings['pwh_dcfh_smtp_authentication'])) {
                            if ('' === trim($smtp_settings['pwh_dcfh_smtp_username']) && '' === trim($smtp_settings['pwh_dcfh_smtp_password'])) {
                                $errors[] = __('SMTP SMTP Username & Password required.', pwh_dcfh_hc()::TEXT_DOMAIN);
                            }
                        }
                        if (!empty($errors)) {
                            $smtp_settings['pwh_dcfh_smtp_enabled'] = false;
                            update_option('et_divi', $smtp_settings);
                            wp_send_json_error(implode('<br>', $errors));
                        }
                        if (empty($errors)) {
                            $admin_email = get_option('admin_email');
                            $blog_name = get_option('blogname');
                            if (wp_mail($admin_email, __('SMTP Integrated', pwh_dcfh_hc()::TEXT_DOMAIN), sprintf(__('Congrats, SMTP has been integrated on your site %s successfully!', pwh_dcfh_hc()::TEXT_DOMAIN), $blog_name))) {
                                wp_send_json_success(sprintf(__('Test email is sent at %s successfully! Please check your inbox to make sure it was delivered.', pwh_dcfh_hc()::TEXT_DOMAIN), $admin_email));
                            } else {
                                $failed_reason = $this->wp_mail_failed;
                                if (!empty($failed_reason)) {
                                    wp_send_json_error(make_clickable($failed_reason));
                                }
                            }
                        }
                    }
                }
            }
        }

    }
}
/* $options[] = [
                'name' => 'pwh-dcfh-tab-permission',
                'type' => 'subnav-tab',
                'desc' => __('Permission', pwh_dcfh_hc()::TEXT_DOMAIN)
            ];*/
/* $options[] = [
               'name' => 'pwh-dcfh-tab-permission',
               'type' => 'subcontent-start',
           ];
           $options[] = [
               'name' => __('Editor Permissions', pwh_dcfh_hc()::TEXT_DOMAIN),
               'type' => 'checkbox_list',
               'usefor' => 'custom',
               'id' => 'pwh_dcfh_editor_permissions',
               'index' => -1,
               'desc' => __('By default, the Divi Builder is only accessible on standard post types. This option lets you enable the builder on any custom post type currently registered on your website, however the builder may not be compatible with all custom post types.', 'et_builder'),
               'options' => [
                   'pwh_dcfh_view_entries' => 'View Entries',
                   'pwh_dcfh_send_email' => 'Reply/Send Email',
                   'pwh_dcfh_create_email_template' => 'Create Email Template',
                   'pwh_dcfh_create_posts' => 'Create Posts',
                   'pwh_dcfh_delete_entries' => 'Delete Entries',
                   'pwh_dcfh_export_csv ' => 'Export CSV ',
               ],
               'default' => [],
               'validation_type' => 'on_off_array',
               'et_save_values' => true,
           ];
           $options[] = [
               'name' => __('Contributor Permissions', pwh_dcfh_hc()::TEXT_DOMAIN),
               'type' => 'checkbox_list',
               'usefor' => 'custom',
               'id' => 'pwh_dcfh_contributor_permissions',
               'index' => -1,
               'desc' => __('By default, the Divi Builder is only accessible on standard post types. This option lets you enable the builder on any custom post type currently registered on your website, however the builder may not be compatible with all custom post types.', 'et_builder'),
               'options' => [
                   'pwh_dcfh_view_entries' => 'View Entries',
                   'pwh_dcfh_send_email' => 'Reply/Send Email',
                   'pwh_dcfh_create_email_template' => 'Create Email Template',
                   'pwh_dcfh_create_posts' => 'Create Posts',
                   'pwh_dcfh_delete_entries' => 'Delete Entries',
                   'pwh_dcfh_export_csv ' => 'Export CSV ',
               ],
               'default' => [],
               'validation_type' => 'on_off_array',
               'et_save_values' => true,
           ];
           $options[] = [
               'name' => __('Author Permissions', pwh_dcfh_hc()::TEXT_DOMAIN),
               'type' => 'checkbox_list',
               'usefor' => 'custom',
               'id' => 'pwh_dcfh_author_permissions',
               'index' => -1,
               'desc' => __('By default, the Divi Builder is only accessible on standard post types. This option lets you enable the builder on any custom post type currently registered on your website, however the builder may not be compatible with all custom post types.', 'et_builder'),
               'options' => [
                   'pwh_dcfh_view_entries' => 'View Entries',
                   'pwh_dcfh_send_email' => 'Reply/Send Email',
                   'pwh_dcfh_create_email_template' => 'Create Email Template',
                   'pwh_dcfh_create_posts' => 'Create Posts',
                   'pwh_dcfh_delete_entries' => 'Delete Entries',
                   'pwh_dcfh_export_csv ' => 'Export CSV ',
               ],
               'default' => [],
               'validation_type' => 'on_off_array',
               'et_save_values' => true,
           ];
           $options[] = [
               'name' => 'pwh-dcfh-tab-permission',
               'type' => 'subcontent-end',
           ];*/