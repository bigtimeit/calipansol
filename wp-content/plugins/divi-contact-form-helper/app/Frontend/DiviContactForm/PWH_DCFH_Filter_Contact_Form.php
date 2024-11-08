<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm;

use DOMXPath;
use DOMDocument;
use ET_Builder_Element;
use PWH_DCFH\App\Frontend\Request\PWH_DCFH_Confirmation_Email_Request;
use PWH_DCFH\App\Frontend\Request\PWH_DCFH_Post_Repository;
use PWH_DCFH\App\Frontend\Request\PWH_DCFH_Post_Request;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Submit_Button;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Success_Button;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Success_Message;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Redirect;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Field_Layout;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Misc;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Field_Date_Time;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Conditional_Mailing;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Field_Select;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Field_File_Upload;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Filter_Contact_Form')) {
    class PWH_DCFH_Filter_Contact_Form
    {

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            add_filter('et_builder_get_parent_modules', [$this, 'maybe_add_toggles'], 10, 2);
            add_filter('et_pb_all_fields_unprocessed_'.pwh_dcfh_hc()::DIVI_CONTACT_FORM_SLUG, [$this, 'get_fields']);
            add_filter('et_pb_module_shortcode_attributes', [$this, 'maybe_filter_shortcode_attributes'], 10, 5);
            add_filter('et_module_shortcode_output', [$this, 'maybe_filter_shortcode_output'], 10, 3);
            add_filter('et_builder_custom_dynamic_content_fields', [$this, 'maybe_custom_dynamic_content_fields'], 10, 3);
            add_shortcode('pwh_dcfh_email_template', [$this, 'maybe_add_email_template_shortcode']);
        }

        /**
         * Filter et_builder_get_parent_modules
         *
         * @param $modules
         * @param $post_type
         *
         * @return mixed
         */
        public function maybe_add_toggles($modules, $post_type)
        {
            static $is_applied = false;
            if ($is_applied) {
                return $modules;
            }
            if (empty($modules)) {
                return $modules;
            }
            foreach ($modules as $module_slug => $module) {
                if (pwh_dcfh_hc()::DIVI_CONTACT_FORM_SLUG === $module_slug) {
                    if (!isset($module->settings_modal_toggles) || !isset($module->advanced_fields) || !isset($module->fields_unprocessed)) {
                        continue;
                    }
                    $settings_modal_toggles = $module->settings_modal_toggles;
                    $advanced_fields_toggles = $module->advanced_fields;
                    // Change Priority main_content toggle
                    if (isset($settings_modal_toggles['general']['toggles']['main_content'])) {
                        $settings_modal_toggles['general']['toggles']['main_content'] = [
                            'title' => et_builder_i18n('Title'),
                            'priority' => 5,
                        ];
                    }
                    // Rename Email Toggle To Admin Email Toggle
                    if (isset($settings_modal_toggles['general']['toggles']['email'])) {
                        $settings_modal_toggles['general']['toggles']['email'] = [
                            'title' => et_builder_i18n('Admin Email'),
                            'priority' => 5,
                        ];
                    }
                    // Rename Toggle Button To Submit Button Toggle
                    if (isset($settings_modal_toggles['advanced']['toggles']['button'])) {
                        $settings_modal_toggles['advanced']['toggles']['button'] = [
                            'title' => et_builder_i18n('Submit Button'),
                            'priority' => 70,
                        ];
                    }
                    // Rename Redirect Toggle To After Submission Behavior Toggle
                    if (isset($settings_modal_toggles['general']['toggles']['redirect'])) {
                        $settings_modal_toggles['general']['toggles']['redirect'] = [
                            'title' => __('After Submission Behavior', pwh_dcfh_hc()::TEXT_DOMAIN),
                        ];
                    }
                    // General Toggles
                    if (isset($settings_modal_toggles['general']) && !empty($settings_modal_toggles['general']['toggles'])) {
                        $settings_modal_toggles['general']['toggles']['confirmation_settings_toggle'] = [
                            'title' => __('Confirmation Email', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 5,
                        ];
                        $settings_modal_toggles['general']['toggles']['submission_toggle'] = [
                            'title' => __('Submission Entries', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 5,
                        ];
                        $settings_modal_toggles['general']['toggles']['submit_button_toggle'] = [
                            'title' => __('Submit Button', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 5,
                        ];
                        $settings_modal_toggles['general']['toggles']['integrations_toggle'] = [
                            'title' => __('Integrations', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 5,
                        ];
                        $modules[$module_slug]->settings_modal_toggles = $settings_modal_toggles;
                    }
                    // Advanced Toggles
                    if (isset($settings_modal_toggles['advanced']) && !empty($settings_modal_toggles['advanced']['toggles'])) {

                        $settings_modal_toggles['advanced']['toggles']['field_label_toggle'] = [
                            'title' => __('Field Label Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 30,
                        ];
                        $settings_modal_toggles['advanced']['toggles']['field_desc_toggle'] = [
                            'title' => __('Field Description Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 31,
                        ];
                        $settings_modal_toggles['advanced']['toggles']['success_button_toggle'] = [
                            'title' => __('Success Button', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 70,
                        ];
                        $settings_modal_toggles['advanced']['toggles']['success_message_design_toggle'] = [
                            'title' => __('Success Message Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 70,
                        ];
                        $settings_modal_toggles['advanced']['toggles']['file_upload_design_toggle'] = [
                            'title' => __('File Upload', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'tabbed_subtoggles' => true,
                            'sub_toggles' => [
                                'container_toggle' => [
                                    'name' => __('Container', pwh_dcfh_hc()::TEXT_DOMAIN)
                                ],
                                'description_toggle' => [
                                    'name' => __('Descriptions', pwh_dcfh_hc()::TEXT_DOMAIN)
                                ],
                                'button_toggle' => [
                                    'name' => __('Upload Button', pwh_dcfh_hc()::TEXT_DOMAIN)
                                ],
                            ],
                            'priority' => 70,
                        ];
                        $settings_modal_toggles['advanced']['toggles']['datetimepicker_design_toggle'] = [
                            'title' => __('Date/Time Picker', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'tabbed_subtoggles' => true,
                            'sub_toggles' => [
                                'default_toggle' => [
                                    'name' => __('General', pwh_dcfh_hc()::TEXT_DOMAIN)
                                ],
                                'date_toggle' => [
                                    'name' => __('Date', pwh_dcfh_hc()::TEXT_DOMAIN)
                                ],
                                'time_toggle' => [
                                    'name' => __('Time', pwh_dcfh_hc()::TEXT_DOMAIN)
                                ],
                            ],
                            'priority' => 70,
                        ];
                        $modules[$module_slug]->settings_modal_toggles = $settings_modal_toggles;
                    }
                }
            }
            $is_applied = true;

            return $modules;
        }

        /**
         * Filter Form Fields
         *
         * @param $fields_unprocessed
         *
         * @return array
         */
        public function get_fields($fields_unprocessed)
        {
            //==================================================
            // Contact Form Unique ID
            //==================================================
            $custom_fields['_unique_id'] = [
                'label' => __('Contact Form Unique ID', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => sprintf(__('This field can be used to give the contact form a unique identification number which will be shown in the database and other admin areas. You can generate a new ID <a href="%1$s" target="_blank">%2$s</a>', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'https://www.guidgenerator.com/online-guid-generator.aspx', __('here', pwh_dcfh_hc()::TEXT_DOMAIN)),
                'toggle_slug' => 'admin_label',
            ];
            //==================================================
            // Override Default Fields
            //==================================================
            $fields_unprocessed['submit_button_text']['toggle_slug'] = 'submit_button_toggle';
            $fields_unprocessed['success_message']['toggle_slug'] = 'redirect';
            //==================================================
            // Admin Email
            //==================================================
            $fields_unprocessed['email']['dynamic_content'] = 'text';
            $fields_unprocessed['email']['show_if'] = ['use_conditional_emails' => 'off'];
            $fields_unprocessed['email']['label'] = __('Admin Email Address', pwh_dcfh_hc()::TEXT_DOMAIN);
            $fields_unprocessed['custom_message']['label'] = __('Admin Email Message', pwh_dcfh_hc()::TEXT_DOMAIN);
            $fields_unprocessed['custom_message']['show_if'] = ['use_custom_message_richtext' => 'off'];
            $custom_fields['use_custom_message_richtext'] = [
                'label' => __('Use Rich Text Formatting For Admin Email Message', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to enable a rich text editor for customizing the admin email message with HTML.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'option_category' => 'configuration',
                'toggle_slug' => 'email',
            ];
            $custom_fields['custom_message_richtext'] = [
                'label' => __('Admin Email Message', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'tiny_mce',
                'description' => et_get_safe_localization(__('Here you can define the custom pattern for the email Message. Fields should be included in following format - <strong>%%field_id%%</strong>. For example if you want to include the field with id = <strong>phone</strong> and field with id = <strong>message</strong>, then you can use the following pattern: <strong>My message is %%message%% and phone number is %%phone%%</strong>. Leave blank for default.', 'et_builder')),
                'show_if' => ['use_custom_message_richtext' => 'on'],
                'dynamic_content' => 'text',
                'option_category' => 'configuration',
                'toggle_slug' => 'email',
            ];
            $custom_fields['use_conditional_emails'] = [
                'label' => __('Use Conditional Email Address Routing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to rout the form to specific admin email addresses based on conditional logic.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'option_category' => 'configuration',
                'toggle_slug' => 'email',
            ];
            $custom_fields['conditional_email_pattern'] = [
                'label' => __('Conditional Email Address Routing Logic', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'textarea',
                'description' => sprintf(__('Set up the logic of the conditional email address routing using the field IDs and field options. Please refer to our <a href="%s" target="_blank">documentation</a> for instructions about how to set this up.', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'https://www.peeayecreative.com/docs/divi-contact-form-helper/admin-email-settings/'),
                'show_if' => ['use_conditional_emails' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'email',
            ];
            $custom_fields['admin_email_subject'] = [
                'label' => __('Admin Email Subject', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => et_get_safe_localization(__('Enter a custom subject line for the admin email.', pwh_dcfh_hc()::TEXT_DOMAIN)),
                'option_category' => 'configuration',
                'toggle_slug' => 'email',
            ];
            $custom_fields['admin_email_cc'] = [
                'label' => __('Admin Email Address CC', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => et_get_safe_localization(__('Input a carbon copy email address for the admin message. Multiple email addresses can be added separated by a comma.', pwh_dcfh_hc()::TEXT_DOMAIN)),
                'option_category' => 'configuration',
                'toggle_slug' => 'email',
            ];
            $custom_fields['admin_email_bcc'] = [
                'label' => __('Admin Email Address BCC', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => et_get_safe_localization(__('Input a blind carbon copy email address for the admin message. Multiple email addresses can be added separated by a comma.', pwh_dcfh_hc()::TEXT_DOMAIN)),
                'option_category' => 'configuration',
                'toggle_slug' => 'email',
            ];
            //==================================================
            // Database Settings
            //==================================================
            $custom_fields['save_entries_to_db'] = [
                'label' => __('Save Entries To Database', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to save all form entries to the website database. This is helpful to retain a copy in case there are delivery problems. Entries can be viewed by going to your WordPress Dashboard to Contact Form > Entries.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'on',
                'option_category' => 'configuration',
                'toggle_slug' => 'submission_toggle',
            ];
            $custom_fields['save_files_to_db'] = [
                'label' => __('Save File Uploads To Database', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to save all file uploads with the form entry in the website database.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'on',
                'show_if' => ['save_entries_to_db' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'submission_toggle',
            ];
            $custom_fields['send_files_as_attachment'] = [
                'label' => __('Send File Uploads As Attachments With Emails', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to attach all file uploads to the admin email.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'on',
                'option_category' => 'configuration',
                'toggle_slug' => 'submission_toggle',
            ];
            $custom_fields['collect_ip_useragent_details'] = [
                'label' => __('Collect User Agent Details', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to collect information such as IP Address, Browser, and OS.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['save_entries_to_db' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'submission_toggle',
            ];
            $custom_fields['attached_files_message'] = [
                'label' => __('Change Attachments Custom Message', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Choose to enter custom text for the message that automatically displays in the footer of any email with files added to prompt the recipient to view the attachments.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'show_if' => ['send_files_as_attachment' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'submission_toggle',
            ];
            //==================================================
            // Email Copy To Sender
            //==================================================
            $custom_fields['use_confirmation_email'] = [
                'label' => __('Send A Confirmation Email To Sender', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to send a confirmation email to the form submitter.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'option_category' => 'configuration',
                'toggle_slug' => 'confirmation_settings_toggle',
            ];
            $custom_fields['confirmation_email_subject'] = [
                'label' => __('Confirmation Email Subject', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => et_get_safe_localization(__('Enter a custom subject line for the confirmation email.', pwh_dcfh_hc()::TEXT_DOMAIN)),
                'show_if' => ['use_confirmation_email' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'confirmation_settings_toggle'
            ];
            $custom_fields['confirmation_email_message'] = [
                'label' => __('Confirmation Email Message', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'textarea',
                'description' => et_get_safe_localization(__('Here you can define the custom pattern for the email Message. Fields should be included in following format - <strong>%%field_id%%</strong>. For example if you want to include the field with id = <strong>phone</strong> and field with id = <strong>message</strong>, then you can use the following pattern: <strong>My message is %%message%% and phone number is %%phone%%</strong>',
                    pwh_dcfh_hc()::TEXT_DOMAIN)),
                'show_if' => ['use_confirmation_email' => 'on', 'use_confirmation_message_richtext' => 'off'],
                'option_category' => 'configuration',
                'toggle_slug' => 'confirmation_settings_toggle'
            ];
            $custom_fields['use_confirmation_message_richtext'] = [
                'label' => __('Use Rich Text Formatting For Confirmation Email Message', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to enable a rich text editor for customizing the confirmation email message with HTML.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['use_confirmation_email' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'confirmation_settings_toggle'
            ];
            $custom_fields['confirmation_message_richtext'] = [
                'label' => __('Confirmation Email Message', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'tiny_mce',
                'description' => et_get_safe_localization(__('Here you can define the custom pattern for the email Message. Fields should be included in following format - <strong>%%field_id%%</strong>. For example if you want to include the field with id = <strong>phone</strong> and field with id = <strong>message</strong>, then you can use the following pattern: <strong>My message is %%message%% and phone number is %%phone%%</strong>. Leave blank for default.', 'et_builder')),
                'show_if' => ['use_confirmation_email' => 'on', 'use_confirmation_message_richtext' => 'on'],
                'dynamic_content' => 'text',
                'option_category' => 'configuration',
                'toggle_slug' => 'confirmation_settings_toggle'
            ];
            //==================================================
            // Zapier
            //==================================================
            $custom_fields['use_zapier'] = [
                'label' => __('Use Zapier Email Parser', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Enable the Zapier integration settings.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'option_category' => 'configuration',
                'toggle_slug' => 'integrations_toggle',
            ];
            $custom_fields['zapier_mailbox_address'] = [
                'label' => __('Zapier Email Parser Mailbox Address', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Enter the Email Parser by Zapier mailbox email address to send emails to Zapier to be integrated with hundreds of other tools and services.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'show_if' => ['use_zapier' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'integrations_toggle'
            ];
            //==================================================
            // Pabbly
            //==================================================
            $custom_fields['use_pabbly'] = [
                'label' => __('Use Pabbly Email Parser', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Enable the Pabbly integration settings.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'option_category' => 'configuration',
                'toggle_slug' => 'integrations_toggle',
            ];
            $custom_fields['pabbly_mailbox_address'] = [
                'label' => __('Pabbly Email Parser Mailbox Address', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Enter the Email Parser by Pabbly mailbox email address to send emails to Pabbly to be integrated with hundreds of other tools and services.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'show_if' => ['use_pabbly' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'integrations_toggle'
            ];
            //==================================================
            // Submit Button Wait Text
            //==================================================
            $custom_fields['submit_button_wait_text'] = [
                'label' => __('Submit Button Wait Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => __('Please wait...', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter custom text for the submit button which will show while files are being uploaded.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'option_category' => 'configuration',
                'toggle_slug' => 'submit_button_toggle'
            ];
            //==================================================
            // Submit Button Alignment And Width
            //==================================================
            $custom_fields['submit_button_alignment'] = [
                'label' => __('Button Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Choose to align the submit button to the left, center, or right.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'default' => 'off',
                'show_if' => ['custom_button' => 'on'],
                'mobile_options' => true,
                'toggle_slug' => 'button',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['submit_button_full_width'] = [
                'label' => __('Button Full Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('If enabled, the field will take 100% of the width of the content area.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['custom_button' => 'on'],
                'mobile_options' => true,
                'toggle_slug' => 'button',
                'tab_slug' => 'advanced',
            ];
            //==================================================
            // Hide Form Title
            //==================================================
            $custom_fields['hide_form_title'] = [
                'label' => __('Hide Form Title', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to hide the contact form title after submitting form.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'option_category' => 'configuration',
                'toggle_slug' => 'redirect',
            ];
            //==================================================
            // Success Button
            //==================================================
            $custom_fields['use_success_button'] = [
                'label' => __('Use Success Button', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'description' => __('Choose to add a button to the success message after a successful form submission.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'option_category' => 'configuration',
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['success_button_text'] = [
                'label' => __('Success Button Custom Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Enter custom text for the success button.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => __('Go Back To Home', pwh_dcfh_hc()::TEXT_DOMAIN),
                'show_if' => ['use_success_button' => 'on'],
                'dynamic_content' => 'text',
                'option_category' => 'configuration',
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['success_button_type'] = [
                'label' => __('Success Button Link Type', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose the type of link to use for the success button.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'url' => __('Custom URL', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'page' => __('Page', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => 'url',
                'show_if' => ['use_success_button' => 'on'],
                'affects' => ['success_button_url', 'success_button_page'],
                'option_category' => 'configuration',
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['success_button_url'] = [
                'label' => __('Success Button Custom URL', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Enter a custom URL to use for the success button link.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'depends_show_if' => 'url',
                'default' => home_url(),
                'option_category' => 'configuration',
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['success_button_page'] = [
                'label' => __('Success Button Page Link', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Select a page for the success button link.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => pwh_dcfh_helpers()::get_pages(),
                'depends_show_if' => 'page',
                'option_category' => 'configuration',
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['success_button_target'] = [
                'label' => __('Success Button Link Target', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether the button link opens in the same window or new tab.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    '_self' => __('In The Same Tab', pwh_dcfh_hc()::TEXT_DOMAIN),
                    '_blank' => __('In A New Tab', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => '_self',
                'show_if' => ['use_success_button' => 'on'],
                'option_category' => 'configuration',
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['success_button_font'] = [
                'label' => __('Button Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the Title. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_color'] = [
                'label' => __('Button Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_size'] = [
                'label' => __('Button Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_letter_space'] = [
                'label' => __('Button Text Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the Title.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_line_height'] = [
                'label' => __('Button Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the Title text. This becomes noticeable if the Title is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_alignment'] = [
                'label' => __('Button Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Align the Title to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_margin'] = [
                'label' => __('Button Margin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_margin',
                'description' => __('Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'responsive' => true,
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_padding'] = [
                'label' => __('Button Padding', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_padding',
                'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_background'] = [
                'label' => __('Button Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the background style of this element by customizing the background color, gradient, image and video.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_border'] = [
                'label' => __('Button Border Radius', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'border-radius',
                'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'option_category' => 'border',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_border_color'] = [
                'label' => __('Button Border Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('Pick a color to be used for the border.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_border_width'] = [
                'label' => __('Button Border Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increasing the width of the border will increase its size/thickness.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '50',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 50,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_border_style'] = [
                'label' => __('Button Border Style', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Borders support various different styles, each of which will change the shape of the border element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_border_styles(),
                'default' => 'solid',
                'mobile_options' => true,
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_shadow'] = [
                'label' => __('Button Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_box_shadow',
                'description' => __('Pick a box shadow style to enable box shadow for this element. Once enabled, you will be able to customize your box shadow style further. To disable custom box shadow style, choose the None option.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'presets' => pwh_dcfh_module_helpers()::get_shadow_presets('success_button_toggle'),
                'default' => 'none',
                'className' => 'et_pb_success_button',
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_shadow_horizontal'] = [
                'type' => 'range',
                'label' => __('Shadow Horizontal Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s horizontal distance from the element. A negative value places the shadow to the left of the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'success_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '6px',
                        'preset3' => '0px',
                        'preset4' => '10px',
                        'preset5' => '0px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => [
                    'success_button_shadow' => 'none',
                ],
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_shadow_vertical'] = [
                'type' => 'range',
                'label' => __('Shadow Vertical Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s vertical distance from the element. A negative value places the shadow above the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'success_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '2px',
                        'preset2' => '6px',
                        'preset3' => '12px',
                        'preset4' => '10px',
                        'preset5' => '6px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => [
                    'success_button_shadow' => 'none',
                ],
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_shadow_blur'] = [
                'type' => 'range',
                'label' => __('Shadow Blur Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The higher the value, the bigger the blur, the shadow becomes wider and lighter.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => 0,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'success_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '18px',
                        'preset2' => '18px',
                        'preset3' => '18px',
                        'preset4' => '0px',
                        'preset5' => '0px',
                        'preset6' => '18px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => [
                    'success_button_shadow' => 'none',
                ],
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_shadow_spread'] = [
                'type' => 'range',
                'label' => __('Shadow Spread Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Increasing the spread strength will increase the density of the box shadow. Higher density results in a more intense shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'success_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '0px',
                        'preset3' => '6px',
                        'preset4' => '0px',
                        'preset5' => '10px',
                        'preset6' => '0px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['success_button_shadow' => 'none'],
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_shadow_color'] = [
                'type' => 'color-alpha',
                'label' => __('Shadow Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The color of the shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => 'rgba(0,0,0,0.3)',
                'field_template' => 'color',
                'show_if_not' => [
                    'success_button_shadow' => 'none',
                ],
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_button_shadow_position'] = [
                'type' => 'select',
                'label' => __('Shadow Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether you would like the shadow to appear outside your module, lifting the module up from the page, or inside the module, setting the module downwards within the page.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    '' => __('Outer Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'inset' => __('Inner Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => [
                    'success_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '',
                        'preset2' => '',
                        'preset3' => '',
                        'preset4' => '',
                        'preset5' => '',
                        'preset6' => 'inset',
                        'preset7' => 'inset',
                    ]
                ],
                'show_if_not' => [
                    'success_button_shadow' => 'none',
                ],
                'toggle_slug' => 'success_button_toggle',
                'tab_slug' => 'advanced',
            ];
            //==================================================
            // Success Message Text
            //==================================================
            $custom_fields['success_message_background'] = [
                'label' => __('Success Message Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the background style by customizing the background color.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_font'] = [
                'label' => __('Font', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the Title. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_font_color'] = [
                'label' => __('Font Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_font_size'] = [
                'label' => __('Font Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_letterspace'] = [
                'label' => __('Font Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the Title.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_lineheight'] = [
                'label' => __('Font Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the Title text. This becomes noticeable if the Title is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_alignment'] = [
                'label' => __('Font Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text_align',
                'description' => __('Align the Title to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_margin'] = [
                'label' => __('Success Message Margin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_margin',
                'description' => __('Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'responsive' => true,
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_padding'] = [
                'label' => __('Success Message Padding', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_padding',
                'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_border'] = [
                'label' => __('Success Message Border Radius', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'border-radius',
                'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'option_category' => 'border',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_border_color'] = [
                'label' => __('Success Message Border Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('Pick a color to be used for the border.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_border_width'] = [
                'label' => __('Success Message Border Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increasing the width of the border will increase its size/thickness.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '50',
                    'step' => '1',
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_border_style'] = [
                'label' => __('Success Message Border Style', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Borders support various different styles, each of which will change the shape of the border element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_border_styles(),
                'default' => 'solid',
                'mobile_options' => true,
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_shadow'] = [
                'label' => __('Success Message Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_box_shadow',
                'description' => __('Pick a box shadow style to enable box shadow for this element. Once enabled, you will be able to customize your box shadow style further. To disable custom box shadow style, choose the None option.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'presets' => pwh_dcfh_module_helpers()::get_shadow_presets('success_message_design_toggle'),
                'default' => 'none',
                'className' => 'et_pb_success_message',
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_shadow_horizontal'] = [
                'type' => 'range',
                'label' => __('Shadow Horizontal Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s horizontal distance from the element. A negative value places the shadow to the left of the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'success_message_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '6px',
                        'preset3' => '0px',
                        'preset4' => '10px',
                        'preset5' => '0px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => [
                    'success_message_shadow' => 'none',
                ],
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_shadow_vertical'] = [
                'type' => 'range',
                'label' => __('Shadow Vertical Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s vertical distance from the element. A negative value places the shadow above the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'success_message_shadow',
                    [
                        'none' => '',
                        'preset1' => '2px',
                        'preset2' => '6px',
                        'preset3' => '12px',
                        'preset4' => '10px',
                        'preset5' => '6px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => ['success_message_shadow' => 'none'],
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_shadow_blur'] = [
                'type' => 'range',
                'label' => __('Shadow Blur Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The higher the value, the bigger the blur, the shadow becomes wider and lighter.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => 0,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => 0,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'success_message_shadow',
                    [
                        'none' => '',
                        'preset1' => '18px',
                        'preset2' => '18px',
                        'preset3' => '18px',
                        'preset4' => '0px',
                        'preset5' => '0px',
                        'preset6' => '18px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['success_message_shadow' => 'none'],
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_shadow_spread'] = [
                'type' => 'range',
                'label' => __('Shadow Spread Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Increasing the spread strength will increase the density of the box shadow. Higher density results in a more intense shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'success_message_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '0px',
                        'preset3' => '6px',
                        'preset4' => '0px',
                        'preset5' => '10px',
                        'preset6' => '0px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['success_message_shadow' => 'none'],
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_shadow_color'] = [
                'type' => 'color-alpha',
                'label' => __('Shadow Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The color of the shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => 'rgba(0,0,0,0.3)',
                'field_template' => 'color',
                'show_if_not' => ['success_message_shadow' => 'none'],
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['success_message_shadow_position'] = [
                'type' => 'select',
                'label' => __('Shadow Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether you would like the shadow to appear outside your module, lifting the module up from the page, or inside the module, setting the module downwards within the page.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    '' => __('Outer Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'inset' => __('Inner Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => [
                    'success_message_shadow',
                    [
                        'none' => '',
                        'preset1' => '',
                        'preset2' => '',
                        'preset3' => '',
                        'preset4' => '',
                        'preset5' => '',
                        'preset6' => 'inset',
                        'preset7' => 'inset',
                    ]
                ],
                'show_if_not' => [
                    'success_message_shadow' => 'none',
                ],
                'toggle_slug' => 'success_message_design_toggle',
                'tab_slug' => 'advanced',
            ];
            //==================================================
            // Redirect
            //==================================================
            if (isset($fields_unprocessed['use_redirect'])) {
                $fields_unprocessed['use_redirect'] = [];
            }
            if (isset($fields_unprocessed['redirect_url'])) {
                $fields_unprocessed['redirect_url'] = [];
            }
            $custom_fields['custom_use_redirect'] = [
                'label' => __('Enable Redirect URL', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'option_category' => 'configuration',
                'options' => [
                    'off' => et_builder_i18n('No'),
                    'on' => et_builder_i18n('Yes'),
                ],
                'affects' => [
                    'redirect_url',
                ],
                'toggle_slug' => 'redirect',
                'description' => __('Redirect users after successful form submission.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default_on_front' => 'off',
            ];
            $custom_fields['custom_redirect_to'] = [
                'label' => __('Redirect To', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose the type of redirect link.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'url' => __('Custom URL', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'page' => __('Page', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => 'url',
                'show_if' => [
                    'custom_use_redirect' => 'on',
                ],
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['custom_redirect_url'] = [
                'label' => __('Custom URL', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Enter a custom URL to redirect to after a successful form submission.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => home_url(),
                'show_if' => [
                    'custom_use_redirect' => 'on',
                    'custom_redirect_to' => 'url',
                ],
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['custom_redirect_page'] = [
                'label' => __('Select Page', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Select a page for the redirect link.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => pwh_dcfh_helpers()::get_pages(),
                'show_if' => [
                    'custom_use_redirect' => 'on',
                    'custom_redirect_to' => 'page',
                ],
                'toggle_slug' => 'redirect',
            ];
            $custom_fields['custom_redirect_delay'] = [
                'label' => __('Redirect Delay', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Set the amount of time in seconds for the redirect to wait after a successful form submission.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'unitless' => true,
                'range_settings' => [
                    'min' => '100',
                    'max' => '100000',
                    'step' => '100',
                    'min_limit' => 100,
                    'max_limit' => 100000,
                ],
                'default' => '3000',
                'show_if' => ['custom_use_redirect' => 'on'],
                'toggle_slug' => 'redirect',
            ];
            //==================================================
            // Field Input Focus Border
            //==================================================
            $custom_fields['input_focus_border'] = [
                'label' => __('Input Focus Border Radius', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'border-radius',
                'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'option_category' => 'border',
                'mobile_options' => true,
                'toggle_slug' => 'border',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['input_focus_border_color'] = [
                'label' => __('Input Focus Border Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('Pick a color to be used for the border.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'border',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['input_focus_border_width'] = [
                'label' => __('Input Focus Border Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increasing the width of the border will increase its size/thickness.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '50',
                    'step' => '1',
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'border',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['input_focus_border_style'] = [
                'label' => __('Input Focus Border Style', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Borders support various different styles, each of which will change the shape of the border element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_border_styles(),
                'default' => 'solid',
                'mobile_options' => true,
                'toggle_slug' => 'border',
                'tab_slug' => 'advanced',
            ];
            //==================================================
            // Field Label Text
            //==================================================
            $custom_fields['field_label_font'] = [
                'label' => __('Label Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the Title. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_color'] = [
                'label' => __('Label Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_size'] = [
                'label' => __('Label Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_letter_space'] = [
                'label' => __('Label Text Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the Title.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_line_height'] = [
                'label' => __('Label Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the Title text. This becomes noticeable if the Title is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_alignment'] = [
                'label' => __('Label Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Align the Title to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_margin'] = [
                'label' => __('Label Margin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_margin',
                'description' => __('Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'responsive' => true,
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_padding'] = [
                'label' => __('Label Padding', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_padding',
                'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_background'] = [
                'label' => __('Label Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the background style of this element by customizing the background color, gradient, image and video.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_border'] = [
                'label' => __('Label Border Radius', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'border-radius',
                'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'option_category' => 'border',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_border_color'] = [
                'label' => __('Label Border Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('Pick a color to be used for the border.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_border_width'] = [
                'label' => __('Label Border Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increasing the width of the border will increase its size/thickness.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '50',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 50,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_border_style'] = [
                'label' => __('Label Border Style', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Borders support various different styles, each of which will change the shape of the border element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_border_styles(),
                'default' => 'solid',
                'mobile_options' => true,
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_shadow'] = [
                'label' => __('Label Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_box_shadow',
                'description' => __('Pick a box shadow style to enable box shadow for this element. Once enabled, you will be able to customize your box shadow style further. To disable custom box shadow style, choose the None option.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'presets' => pwh_dcfh_module_helpers()::get_shadow_presets('field_label_toggle'),
                'default' => 'none',
                'className' => 'et_pb_success_button',
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_shadow_horizontal'] = [
                'type' => 'range',
                'label' => __('Shadow Horizontal Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s horizontal distance from the element. A negative value places the shadow to the left of the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'field_label_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '6px',
                        'preset3' => '0px',
                        'preset4' => '10px',
                        'preset5' => '0px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => ['field_label_shadow' => 'none'],
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_shadow_vertical'] = [
                'type' => 'range',
                'label' => __('Shadow Vertical Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s vertical distance from the element. A negative value places the shadow above the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'field_label_shadow',
                    [
                        'none' => '',
                        'preset1' => '2px',
                        'preset2' => '6px',
                        'preset3' => '12px',
                        'preset4' => '10px',
                        'preset5' => '6px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => [
                    'field_label_shadow' => 'none',
                ],
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_shadow_blur'] = [
                'type' => 'range',
                'label' => __('Shadow Blur Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The higher the value, the bigger the blur, the shadow becomes wider and lighter.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => 0,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'field_label_shadow',
                    [
                        'none' => '',
                        'preset1' => '18px',
                        'preset2' => '18px',
                        'preset3' => '18px',
                        'preset4' => '0px',
                        'preset5' => '0px',
                        'preset6' => '18px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['field_label_shadow' => 'none'],
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_shadow_spread'] = [
                'type' => 'range',
                'label' => __('Shadow Spread Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Increasing the spread strength will increase the density of the box shadow. Higher density results in a more intense shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'field_label_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '0px',
                        'preset3' => '6px',
                        'preset4' => '0px',
                        'preset5' => '10px',
                        'preset6' => '0px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['field_label_shadow' => 'none'],
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_shadow_color'] = [
                'type' => 'color-alpha',
                'label' => __('Shadow Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The color of the shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => 'rgba(0,0,0,0.3)',
                'field_template' => 'color',
                'show_if_not' => [
                    'field_label_shadow' => 'none',
                ],
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_label_shadow_position'] = [
                'type' => 'select',
                'label' => __('Shadow Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether you would like the shadow to appear outside your module, lifting the module up from the page, or inside the module, setting the module downwards within the page.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    '' => __('Outer Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'inset' => __('Inner Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => [
                    'field_label_shadow',
                    [
                        'none' => '',
                        'preset1' => '',
                        'preset2' => '',
                        'preset3' => '',
                        'preset4' => '',
                        'preset5' => '',
                        'preset6' => 'inset',
                        'preset7' => 'inset',
                    ]
                ],
                'show_if_not' => ['field_label_shadow' => 'none'],
                'toggle_slug' => 'field_label_toggle',
                'tab_slug' => 'advanced',
            ];
            //==================================================
            // Desc Label Text
            //==================================================
            $custom_fields['field_desc_font'] = [
                'label' => __('Description Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the Title. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_color'] = [
                'label' => __('Description Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_size'] = [
                'label' => __('Description Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_letter_space'] = [
                'label' => __('Description Text Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the Title.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_line_height'] = [
                'label' => __('Description Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the Title text. This becomes noticeable if the Title is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_alignment'] = [
                'label' => __('Description Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Align the Title to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_margin'] = [
                'label' => __('Description Margin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_margin',
                'description' => __('Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'responsive' => true,
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_padding'] = [
                'label' => __('Description Padding', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_padding',
                'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_background'] = [
                'label' => __('Description Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the background style of this element by customizing the background color, gradient, image and video.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_border'] = [
                'label' => __('Description Border Radius', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'border-radius',
                'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'option_category' => 'border',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_border_color'] = [
                'label' => __('Description Border Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('Pick a color to be used for the border.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_border_width'] = [
                'label' => __('Description Border Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increasing the width of the border will increase its size/thickness.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '50',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 50,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_border_style'] = [
                'label' => __('Description Border Style', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Borders support various different styles, each of which will change the shape of the border element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_border_styles(),
                'default' => 'solid',
                'mobile_options' => true,
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_shadow'] = [
                'label' => __('Description Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_box_shadow',
                'description' => __('Pick a box shadow style to enable box shadow for this element. Once enabled, you will be able to customize your box shadow style further. To disable custom box shadow style, choose the None option.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'presets' => pwh_dcfh_module_helpers()::get_shadow_presets('field_desc_toggle'),
                'default' => 'none',
                'className' => 'et_pb_success_button',
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_shadow_horizontal'] = [
                'type' => 'range',
                'label' => __('Shadow Horizontal Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s horizontal distance from the element. A negative value places the shadow to the left of the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'field_desc_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '6px',
                        'preset3' => '0px',
                        'preset4' => '10px',
                        'preset5' => '0px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => ['field_desc_shadow' => 'none'],
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_shadow_vertical'] = [
                'type' => 'range',
                'label' => __('Shadow Vertical Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s vertical distance from the element. A negative value places the shadow above the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'field_desc_shadow',
                    [
                        'none' => '',
                        'preset1' => '2px',
                        'preset2' => '6px',
                        'preset3' => '12px',
                        'preset4' => '10px',
                        'preset5' => '6px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => [
                    'field_desc_shadow' => 'none',
                ],
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_shadow_blur'] = [
                'type' => 'range',
                'label' => __('Shadow Blur Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The higher the value, the bigger the blur, the shadow becomes wider and lighter.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => 0,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'field_desc_shadow',
                    [
                        'none' => '',
                        'preset1' => '18px',
                        'preset2' => '18px',
                        'preset3' => '18px',
                        'preset4' => '0px',
                        'preset5' => '0px',
                        'preset6' => '18px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['field_desc_shadow' => 'none'],
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_shadow_spread'] = [
                'type' => 'range',
                'label' => __('Shadow Spread Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Increasing the spread strength will increase the density of the box shadow. Higher density results in a more intense shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'field_desc_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '0px',
                        'preset3' => '6px',
                        'preset4' => '0px',
                        'preset5' => '10px',
                        'preset6' => '0px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['field_desc_shadow' => 'none'],
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_shadow_color'] = [
                'type' => 'color-alpha',
                'label' => __('Shadow Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The color of the shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => 'rgba(0,0,0,0.3)',
                'field_template' => 'color',
                'show_if_not' => [
                    'field_desc_shadow' => 'none',
                ],
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['field_desc_shadow_position'] = [
                'type' => 'select',
                'label' => __('Shadow Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether you would like the shadow to appear outside your module, lifting the module up from the page, or inside the module, setting the module downwards within the page.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    '' => __('Outer Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'inset' => __('Inner Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => [
                    'field_desc_shadow',
                    [
                        'none' => '',
                        'preset1' => '',
                        'preset2' => '',
                        'preset3' => '',
                        'preset4' => '',
                        'preset5' => '',
                        'preset6' => 'inset',
                        'preset7' => 'inset',
                    ]
                ],
                'show_if_not' => [
                    'field_desc_shadow' => 'none',
                ],
                'toggle_slug' => 'field_desc_toggle',
                'tab_slug' => 'advanced',
            ];
            //==================================================
            // File Upload Design
            //==================================================
            $custom_fields['files_container_background'] = [
                'label' => __('Container Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the background style by customizing the background color.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '#eee',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_margin'] = [
                'label' => __('Container Margin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_margin',
                'description' => __('Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'responsive' => true,
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_padding'] = [
                'label' => __('Container Padding', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_padding',
                'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '20px|20px|0px|20px',
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_border'] = [
                'label' => __('Container Border Radius', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'border-radius',
                'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'option_category' => 'border',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_border_color'] = [
                'label' => __('Container Border Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('Pick a color to be used for the border.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_border_width'] = [
                'label' => __('Container Border Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increasing the width of the border will increase its size/thickness.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '50',
                    'step' => '1',
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_border_style'] = [
                'label' => __('Container Border Style', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Borders support various different styles, each of which will change the shape of the border element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_border_styles(),
                'default' => 'solid',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_shadow'] = [
                'label' => __('Container Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_box_shadow',
                'description' => __('Pick a box shadow style to enable box shadow for this element. Once enabled, you will be able to customize your box shadow style further. To disable custom box shadow style, choose the None option.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'presets' => pwh_dcfh_module_helpers()::get_shadow_presets('files_container_design_toggle'),
                'default' => 'none',
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_shadow_horizontal'] = [
                'type' => 'range',
                'label' => __('Shadow Horizontal Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s horizontal distance from the element. A negative value places the shadow to the left of the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'files_container_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '6px',
                        'preset3' => '0px',
                        'preset4' => '10px',
                        'preset5' => '0px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => ['files_container_shadow' => 'none'],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_shadow_vertical'] = [
                'type' => 'range',
                'label' => __('Shadow Vertical Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s vertical distance from the element. A negative value places the shadow above the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'files_container_shadow',
                    [
                        'none' => '',
                        'preset1' => '2px',
                        'preset2' => '6px',
                        'preset3' => '12px',
                        'preset4' => '10px',
                        'preset5' => '6px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => ['files_container_shadow' => 'none'],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_shadow_blur'] = [
                'type' => 'range',
                'label' => __('Shadow Blur Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The higher the value, the bigger the blur, the shadow becomes wider and lighter.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => 0,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => 0,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'files_container_shadow',
                    [
                        'none' => '',
                        'preset1' => '18px',
                        'preset2' => '18px',
                        'preset3' => '18px',
                        'preset4' => '0px',
                        'preset5' => '0px',
                        'preset6' => '18px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['files_container_shadow' => 'none'],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_shadow_spread'] = [
                'type' => 'range',
                'label' => __('Shadow Spread Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Increasing the spread strength will increase the density of the box shadow. Higher density results in a more intense shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'files_container_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '0px',
                        'preset3' => '6px',
                        'preset4' => '0px',
                        'preset5' => '10px',
                        'preset6' => '0px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['files_container_shadow' => 'none'],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_shadow_color'] = [
                'type' => 'color-alpha',
                'label' => __('Shadow Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The color of the shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => 'rgba(0,0,0,0.3)',
                'field_template' => 'color',
                'show_if_not' => ['files_container_shadow' => 'none'],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_shadow_position'] = [
                'type' => 'select',
                'label' => __('Shadow Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether you would like the shadow to appear outside your module, lifting the module up from the page, or inside the module, setting the module downwards within the page.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    '' => __('Outer Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'inset' => __('Inner Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => [
                    'files_container_shadow',
                    [
                        'none' => '',
                        'preset1' => '',
                        'preset2' => '',
                        'preset3' => '',
                        'preset4' => '',
                        'preset5' => '',
                        'preset6' => 'inset',
                        'preset7' => 'inset',
                    ]
                ],
                'show_if_not' => ['files_container_shadow' => 'none'],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['files_container_list_color'] = [
                'label' => __('Attached List Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the color style by customizing the color color.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '#808080',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'container_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['accepted_file_text_color'] = [
                'label' => __('Accepted File Types Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the color style by customizing the color color.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '#999',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['accepted_file_text_size'] = [
                'label' => __('Accepted File Types Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the accepted file text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['accepted_file_text_font'] = [
                'label' => __('Accepted File Types Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the Title. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['accepted_file_text_letterspace'] = [
                'label' => __('Accepted File Types Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the Title.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['accepted_file_text_lineheight'] = [
                'label' => __('Accepted File Types Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the Title text. This becomes noticeable if the Title is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['accepted_file_text_alignment'] = [
                'label' => __('Accepted File Types Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Align the Title to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['accepted_file_text_margin'] = [
                'label' => __('Accepted File Types Margin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_margin',
                'description' => __('Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'responsive' => true,
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['accepted_file_text_padding'] = [
                'label' => __('Accepted File Types Padding', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_padding',
                'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'default' => '0|0|10px|0',
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['chosen_file_text_color'] = [
                'label' => __('File Chosen Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the color style by customizing the color color.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '#999',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['chosen_file_text_size'] = [
                'label' => __('File Chosen Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the accepted file text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['chosen_file_text_font'] = [
                'label' => __('File Chosen Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the Title. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['chosen_file_text_letterspace'] = [
                'label' => __('File Chosen Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the Title.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['chosen_file_text_lineheight'] = [
                'label' => __('File Chosen Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the Title text. This becomes noticeable if the Title is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'description_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_font'] = [
                'label' => __('Button Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the Title. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_color'] = [
                'label' => __('Button Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_size'] = [
                'label' => __('Button Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the Title text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_letter_space'] = [
                'label' => __('Button Text Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the Title.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_line_height'] = [
                'label' => __('Button Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the Title text. This becomes noticeable if the Title is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_margin'] = [
                'label' => __('Button Margin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_margin',
                'description' => __('Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'responsive' => true,
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_padding'] = [
                'label' => __('Button Padding', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_padding',
                'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified'], ['justify' => 'Justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_background'] = [
                'label' => __('Button Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the background style of this element by customizing the background color, gradient, image and video.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_border'] = [
                'label' => __('Button Border Radius', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'border-radius',
                'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'option_category' => 'border',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_border_color'] = [
                'label' => __('Button Border Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('Pick a color to be used for the border.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_border_width'] = [
                'label' => __('Button Border Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increasing the width of the border will increase its size/thickness.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '50',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 50,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_border_style'] = [
                'label' => __('Button Border Style', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Borders support various different styles, each of which will change the shape of the border element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_border_styles(),
                'default' => 'solid',
                'mobile_options' => true,
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_icon'] = [
                'label' => __('Button Icon', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_icon',
                'description' => __('Choose an icon for the button.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_icon_on_hover'] = [
                'label' => esc_html__('Only Show Icon On Hover for Button', 'et_builder'),
                'description' => esc_html__('By default, button icons are displayed on hover. If you would like button icons to always be displayed, then you can enable this option.', 'et_builder'),
                'type' => 'yes_no_button',
                'default' => 'on',
                'options' => [
                    'off' => et_builder_i18n('No'),
                    'on' => et_builder_i18n('Yes'),
                ],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_shadow'] = [
                'label' => __('Button Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_box_shadow',
                'description' => __('Pick a box shadow style to enable box shadow for this element. Once enabled, you will be able to customize your box shadow style further. To disable custom box shadow style, choose the None option.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'presets' => pwh_dcfh_module_helpers()::get_shadow_presets('button_toggle'),
                'default' => 'none',
                'className' => 'et_pb_file_upload_button',
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_shadow_horizontal'] = [
                'type' => 'range',
                'label' => __('Shadow Horizontal Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s horizontal distance from the element. A negative value places the shadow to the left of the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'file_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '6px',
                        'preset3' => '0px',
                        'preset4' => '10px',
                        'preset5' => '0px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => [
                    'file_button_shadow' => 'none',
                ],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_shadow_vertical'] = [
                'type' => 'range',
                'label' => __('Shadow Vertical Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s vertical distance from the element. A negative value places the shadow above the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'file_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '2px',
                        'preset2' => '6px',
                        'preset3' => '12px',
                        'preset4' => '10px',
                        'preset5' => '6px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => [
                    'file_button_shadow' => 'none',
                ],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_shadow_blur'] = [
                'type' => 'range',
                'label' => __('Shadow Blur Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The higher the value, the bigger the blur, the shadow becomes wider and lighter.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => 0,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'file_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '18px',
                        'preset2' => '18px',
                        'preset3' => '18px',
                        'preset4' => '0px',
                        'preset5' => '0px',
                        'preset6' => '18px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => [
                    'file_button_shadow' => 'none',
                ],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_shadow_spread'] = [
                'type' => 'range',
                'label' => __('Shadow Spread Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Increasing the spread strength will increase the density of the box shadow. Higher density results in a more intense shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'file_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '0px',
                        'preset3' => '6px',
                        'preset4' => '0px',
                        'preset5' => '10px',
                        'preset6' => '0px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['file_button_shadow' => 'none'],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_shadow_color'] = [
                'type' => 'color-alpha',
                'label' => __('Shadow Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The color of the shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => 'rgba(0,0,0,0.3)',
                'field_template' => 'color',
                'show_if_not' => [
                    'file_button_shadow' => 'none',
                ],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['file_button_shadow_position'] = [
                'type' => 'select',
                'label' => __('Shadow Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether you would like the shadow to appear outside your module, lifting the module up from the page, or inside the module, setting the module downwards within the page.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    '' => __('Outer Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'inset' => __('Inner Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => [
                    'file_button_shadow',
                    [
                        'none' => '',
                        'preset1' => '',
                        'preset2' => '',
                        'preset3' => '',
                        'preset4' => '',
                        'preset5' => '',
                        'preset6' => 'inset',
                        'preset7' => 'inset',
                    ]
                ],
                'show_if_not' => [
                    'file_button_shadow' => 'none',
                ],
                'toggle_slug' => 'file_upload_design_toggle',
                'sub_toggle' => 'button_toggle',
                'tab_slug' => 'advanced',
            ];
            //==================================================
            // Datetime Picker Design
            //==================================================
            $custom_fields['datetimepicker_background'] = [
                'label' => __('Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Adjust the background style by customizing the background color.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_margin'] = [
                'label' => __('Margin', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_margin',
                'description' => __('Margin adds extra space to the outside of the element, increasing the distance between the element and other items on the page.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'responsive' => true,
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_padding'] = [
                'label' => __('Padding', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'custom_padding',
                'description' => __('Padding adds extra space to the inside of the element, increasing the distance between the edge of the element and its inner contents.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_border'] = [
                'label' => __('Border Radius', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'border-radius',
                'description' => __('Here you can control the corner radius of this element. Enable the link icon to control all four corners at once, or disable to define custom values for each.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'option_category' => 'border',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_border_color'] = [
                'label' => __('Border Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('Pick a color to be used for the border.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_border_width'] = [
                'label' => __('Border Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increasing the width of the border will increase its size/thickness.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '50',
                    'step' => '1',
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_border_style'] = [
                'label' => __('Border Style', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Borders support various different styles, each of which will change the shape of the border element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_border_styles(),
                'default' => 'solid',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_shadow'] = [
                'label' => __('Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_box_shadow',
                'description' => __('Pick a box shadow style to enable box shadow for this element. Once enabled, you will be able to customize your box shadow style further. To disable custom box shadow style, choose the None option.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'presets' => pwh_dcfh_module_helpers()::get_shadow_presets('datetimepicker_design_toggle'),
                'default' => 'none',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_shadow_horizontal'] = [
                'type' => 'range',
                'label' => __('Shadow Horizontal Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s horizontal distance from the element. A negative value places the shadow to the left of the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'datetimepicker_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '6px',
                        'preset3' => '0px',
                        'preset4' => '10px',
                        'preset5' => '0px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => ['datetimepicker_shadow' => 'none'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_shadow_vertical'] = [
                'type' => 'range',
                'label' => __('Shadow Vertical Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Shadow\'s vertical distance from the element. A negative value places the shadow above the element.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'datetimepicker_shadow',
                    [
                        'none' => '',
                        'preset1' => '2px',
                        'preset2' => '6px',
                        'preset3' => '12px',
                        'preset4' => '10px',
                        'preset5' => '6px',
                        'preset6' => '0px',
                        'preset7' => '10px',
                    ]
                ],
                'show_if_not' => ['datetimepicker_shadow' => 'none'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_shadow_blur'] = [
                'type' => 'range',
                'label' => __('Shadow Blur Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The higher the value, the bigger the blur, the shadow becomes wider and lighter.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => 0,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => 0,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'datetimepicker_shadow',
                    [
                        'none' => '',
                        'preset1' => '18px',
                        'preset2' => '18px',
                        'preset3' => '18px',
                        'preset4' => '0px',
                        'preset5' => '0px',
                        'preset6' => '18px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['datetimepicker_shadow' => 'none'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_shadow_spread'] = [
                'type' => 'range',
                'label' => __('Shadow Spread Strength', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Increasing the spread strength will increase the density of the box shadow. Higher density results in a more intense shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'range_settings' => [
                    'min' => -80,
                    'max' => 80,
                    'step' => 1,
                    'min_limit' => -80,
                    'max_limit' => 80,
                ],
                'allowed_units' => et_builder_get_acceptable_css_string_values('font-size'),
                'default_unit' => 'px',
                'fixed_range' => true,
                'default' => [
                    'datetimepicker_shadow',
                    [
                        'none' => '',
                        'preset1' => '0px',
                        'preset2' => '0px',
                        'preset3' => '6px',
                        'preset4' => '0px',
                        'preset5' => '10px',
                        'preset6' => '0px',
                        'preset7' => '0px',
                    ]
                ],
                'show_if_not' => ['datetimepicker_shadow' => 'none'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_shadow_color'] = [
                'type' => 'color-alpha',
                'label' => __('Shadow Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('The color of the shadow.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => 'rgba(0,0,0,0.3)',
                'field_template' => 'color',
                'show_if_not' => ['datetimepicker_shadow' => 'none'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datetimepicker_shadow_position'] = [
                'type' => 'select',
                'label' => __('Shadow Position', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether you would like the shadow to appear outside your module, lifting the module up from the page, or inside the module, setting the module downwards within the page.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => [
                    '' => __('Outer Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'inset' => __('Inner Shadow', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => [
                    'datetimepicker_shadow',
                    [
                        'none' => '',
                        'preset1' => '',
                        'preset2' => '',
                        'preset3' => '',
                        'preset4' => '',
                        'preset5' => '',
                        'preset6' => 'inset',
                        'preset7' => 'inset',
                    ]
                ],
                'show_if_not' => ['datetimepicker_shadow' => 'none'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'default_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_width'] = [
                'label' => __('Datepicker Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Here you can choose datepicker width.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'range_settings' => [
                    'min' => '1',
                    'max' => '1000',
                    'step' => '1',],
                'default' => '224px',
                'mobile_options' => true,
                'validate_unit' => true,
                'allowed_units' => ['%', 'em', 'rem', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'vh', 'vw'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_height'] = [
                'label' => __('Datepicker Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Here you can choose datepicker height.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'range_settings' => [
                    'min' => '1',
                    'max' => '1000',
                    'step' => '1',],
                'default' => '',
                'mobile_options' => true,
                'validate_unit' => true,
                'allowed_units' => ['%', 'em', 'rem', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'vh', 'vw'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_yearmonth_alignment'] = [
                'label' => __('Month/Year Text Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Align the text to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_yearmonth_text_color'] = [
                'label' => __('Month/Year Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_yearmonth_background'] = [
                'label' => __('Month/Year Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the background.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'hover' => 'tabs',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_yearmonth_size'] = [
                'label' => __('Month/Year Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_yearmonth_letter_space'] = [
                'label' => __('Month/Year Text Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_yearmonth_line_height'] = [
                'label' => __('Month/Year Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the text. This becomes noticeable if the text is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_weekdays_font'] = [
                'label' => __('Days Of Week Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the text. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_weekdays_alignment'] = [
                'label' => __('Days Of Week Text Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Align the text to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_weekdays_text_color'] = [
                'label' => __('Days Of Week Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_weekdays_size'] = [
                'label' => __('Days Of Week Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_weekdays_letter_space'] = [
                'label' => __('Days Of Week Text Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_weekdays_line_height'] = [
                'label' => __('Days Of Week Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the text. This becomes noticeable if the text is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_dates_font'] = [
                'label' => __('Calendar Dates Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the text. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_dates_alignment'] = [
                'label' => __('Calendar Dates Text Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Align the text to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_dates_text_color'] = [
                'label' => __('Calendar Dates Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_dates_size'] = [
                'label' => __('Calendar Dates Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_dates_letter_space'] = [
                'label' => __('Calendar Dates Text Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_dates_line_height'] = [
                'label' => __('Calendar Dates Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the text. This becomes noticeable if the text is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_current_date_background'] = [
                'label' => __('Current Date Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a background color to be used for the current date.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'hover' => 'tabs',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_current_date_color'] = [
                'label' => __('Current Date Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a text color to be used for the current date.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_selected_date_background'] = [
                'label' => __('Selected Date Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a background color to be used for the selected date.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'hover' => 'tabs',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_selected_date_color'] = [
                'label' => __('Selected Date Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a text color to be used for the selected date.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_available_dates_background'] = [
                'label' => __('Available Dates Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a background color to be used for the available dates.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_available_dates_text_color'] = [
                'label' => __('Available Date Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a text color to be used for the available date.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_unavailable_dates_background'] = [
                'label' => __('Unavailable Dates Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a background color to be used for the unavailable dates.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['datepicker_unavailable_dates_text_color'] = [
                'label' => __('Unavailable Dates Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a text color to be used for the unavailable dates.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'date_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timepicker_width'] = [
                'label' => __('Timepicker Width', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Here you can choose timepicker width.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'range_settings' => [
                    'min' => '1',
                    'max' => '1000',
                    'step' => '1',],
                'default' => '58px',
                'mobile_options' => true,
                'validate_unit' => true,
                'allowed_units' => ['%', 'em', 'rem', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'vh', 'vw'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timepicker_height'] = [
                'label' => __('Timepicker Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Here you can choose timepicker height.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'range_settings' => [
                    'min' => '1',
                    'max' => '1000',
                    'step' => '1',],
                'default' => '151px',
                'mobile_options' => true,
                'validate_unit' => true,
                'allowed_units' => ['%', 'em', 'rem', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'vh', 'vw'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timepicker_time_font'] = [
                'label' => __('Time Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'font',
                'description' => __('Choose a custom font to use for the text. All Google web fonts are available, or you can upload your own custom font files.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'default' => '',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timepicker_time_alignment'] = [
                'label' => __('Time Text Alignment', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'align',
                'description' => __('Align the text to the left, right, center or justify.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => et_builder_get_text_orientation_options(['justified']),
                'advanced_fields' => true,
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timepicker_time_text_color'] = [
                'label' => __('Time Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a color to be used for the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timepicker_time_size'] = [
                'label' => __('Time Text Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Increase or decrease the size of the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timepicker_time_letter_space'] = [
                'label' => __('Time Text Letter Spacing', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Letter spacing adjusts the distance between each letter in the text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => 100,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timepicker_time_line_height'] = [
                'label' => __('Time Text Line Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'description' => __('Line height adjusts the distance between each line of the text. This becomes noticeable if the text is long and wraps onto multiple lines.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'fixed_unit' => 'em',
                'validate_unit' => true,
                'fixed_range' => true,
                'range_settings' => [
                    'min' => '1',
                    'max' => '3',
                    'step' => '0.1',
                    'min_limit' => 1,
                    'max_limit' => 3,
                ],
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timpicker_selected_time_background'] = [
                'label' => __('Selected Time Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a background color to be used for the current time.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'hover' => 'tabs',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timpicker_selected_time_color'] = [
                'label' => __('Selected Time Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a text color to be used for the current time.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timpicker_available_times_background'] = [
                'label' => __('Available Times Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a background color to be used for the available times.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timpicker_available_times_text_color'] = [
                'label' => __('Available Times Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a text color to be used for the available times.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timpicker_unavailable_times_background'] = [
                'label' => __('Unavailable Times Background Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a background color to be used for the unavailable times.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                //'affects' => ['timpicker_unavailable_times_text_color'],
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];
            $custom_fields['timpicker_unavailable_times_text_color'] = [
                'label' => __('Unavailable Times Text Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color-alpha',
                'description' => __('Pick a text color to be used for the unavailable times.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'mobile_options' => true,
                //'depends_show_if_not' => '',
                'toggle_slug' => 'datetimepicker_design_toggle',
                'sub_toggle' => 'time_toggle',
                'tab_slug' => 'advanced',
            ];

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Filter Form Props
         *
         * @param $props
         * @param $attrs
         * @param $render_slug
         * @param $_address
         * @param $content
         *
         * @return mixed
         */
        public function maybe_filter_shortcode_attributes($props, $attrs, $render_slug, $_address, $content)
        {

            if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
                return $props;
            }
            if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
                return $props;
            }
            if (is_admin() || wp_doing_ajax()) {
                return $props;
            }
            if (pwh_dcfh_hc()::DIVI_CONTACT_FORM_SLUG != $render_slug) {
                return $props;
            }
            $post_repository = PWH_DCFH_Post_Repository::instance();
            $_unique_id = isset($props['_unique_id']) ? $props['_unique_id'] : '';
            $admin_label = isset($props['admin_label']) ? $props['admin_label'] : '';
            $title = isset($props['title']) ? $props['title'] : '';
            // Set Contact Form ID
            $post_repository->contact_form_id = $_unique_id;
            // Set Contact Form Title
            $contact_form_title = !empty($admin_label) ? $admin_label : $title;
            $post_repository->contact_form_title = $contact_form_title;
            // Update Contact Form Title, PageID & Views
            if (!empty($_unique_id)) {
                pwh_dcfh_db_handler()::update_contact_form_title($_unique_id, $contact_form_title);
                pwh_dcfh_db_handler()::update_contact_form_page_id($_unique_id);
                pwh_dcfh_db_handler()::update_contact_form_views($_unique_id);
            }
            // Save Submission
            $save_entries_to_db = isset($props['save_entries_to_db']) ? $props['save_entries_to_db'] : 'off';
            if ('on' === $save_entries_to_db && !empty($_unique_id)) {
                $post_repository->save_entry_to_db = true;
            }
            // Save Files To Directory
            $save_files_to_db = isset($props['save_files_to_db']) ? $props['save_files_to_db'] : 'off';
            if ('on' === $save_entries_to_db && 'on' === $save_files_to_db && !empty($_unique_id)) {
                $post_repository->save_files_to_db = true;
            }
            // Send Files To Email
            $send_files_as_attachment = isset($props['send_files_as_attachment']) ? $props['send_files_as_attachment'] : 'off';
            if ('on' === $send_files_as_attachment) {
                $post_repository->send_files_as_attachment = true;
                $post_repository->attached_files_message = (isset($props['attached_files_message']) && !empty($props['attached_files_message'])) ? $props['attached_files_message'] : '';
            }
            // Collect User Agent Details
            $collect_ip_useragent_details = isset($props['collect_ip_useragent_details']) ? $props['collect_ip_useragent_details'] : 'off';
            if ('on' === $collect_ip_useragent_details) {
                $post_repository->collect_ip_useragent_details = true;
            }
            // Admin Email
            $post_repository->module_admin_email = (isset($props['email']) && !empty($props['email'])) ? sanitize_email($props['email']) : null;
            $admin_email_subject = isset($props['admin_email_subject']) ? $props['admin_email_subject'] : null;
            if (!empty($admin_email_subject)) {
                $post_repository->admin_email_subject = $admin_email_subject;
            }
            // Admin Conditional Emails
            $use_conditional_emails = isset($props['use_conditional_emails']) ? $props['use_conditional_emails'] : 'off';
            $conditional_email_pattern = isset($props['conditional_email_pattern']) ? $props['conditional_email_pattern'] : '';
            if ('on' === $use_conditional_emails && !empty($conditional_email_pattern)) {
                $conditional_mailing = PWH_DCFH_Conditional_Mailing::instance();
                $conditional_mailing->email_pattern = $conditional_email_pattern;
                $conditional_mailing->set_admin_emails();
                $conditional_mailing->init();
            }
            // Admin Formatted Message
            $use_custom_message_richtext = isset($props['use_custom_message_richtext']) ? $props['use_custom_message_richtext'] : 'off';
            $custom_message_richtext = isset($props['custom_message_richtext']) ? $props['custom_message_richtext'] : '';
            if ('on' === $use_custom_message_richtext && !empty($custom_message_richtext)) {
                $post_repository->is_custom_message_richtext = true;
                $custom_message_richtext = pwh_dcfh_helpers()::clean_html_email_message($custom_message_richtext, true);
                $props['custom_message'] = do_shortcode($custom_message_richtext);
            }
            // Admin Email CC
            $admin_email_cc = isset($props['admin_email_cc']) ? $props['admin_email_cc'] : null;
            if (!empty($admin_email_cc)) {
                $post_repository->admin_email_cc = $admin_email_cc;
            }
            // Admin Email Bcc
            $admin_email_bcc = isset($props['admin_email_bcc']) ? $props['admin_email_bcc'] : null;
            if (!empty($admin_email_bcc)) {
                $post_repository->admin_email_bcc = $admin_email_bcc;
            }
            // Customer Subject && Message
            $use_confirmation_email = isset($props['use_confirmation_email']) ? $props['use_confirmation_email'] : 'off';
            $confirmation_email_subject = isset($props['confirmation_email_subject']) ? $props['confirmation_email_subject'] : null;
            $confirmation_email_message = isset($props['confirmation_email_message']) ? $props['confirmation_email_message'] : null;
            $confirmation_message_richtext = isset($props['confirmation_message_richtext']) ? $props['confirmation_message_richtext'] : null;
            if ('on' === $use_confirmation_email && !empty($confirmation_email_subject) && (!empty($confirmation_email_message) || !empty($confirmation_message_richtext))) {
                $use_confirmation_message_richtext = isset($props['use_confirmation_message_richtext']) ? $props['use_confirmation_message_richtext'] : 'off';
                if ('on' === $use_confirmation_message_richtext && !empty($confirmation_message_richtext)) {
                    $post_repository->is_confirmation_message_richtext = true;
                    $confirmation_email_message = do_shortcode($confirmation_message_richtext);
                }
                $post_repository->is_confirmation_email_enabled = true;
                $confirmation_request = PWH_DCFH_Confirmation_Email_Request::instance();
                $confirmation_request->get_post_request()->set_confirmation_email_subject($confirmation_email_subject);
                $confirmation_request->get_post_request()->set_confirmation_email_message($confirmation_email_message);
            }
            // Zapier Mailbox
            $use_zapier = isset($props['use_zapier']) ? $props['use_zapier'] : 'off';
            $zapier_mailbox_address = isset($props['zapier_mailbox_address']) ? $props['zapier_mailbox_address'] : null;
            if ('on' === $use_zapier && !empty($zapier_mailbox_address)) {
                $post_repository->is_zapier_enabled = true;
                $post_repository->zapier_mailbox_address = $zapier_mailbox_address;
            }
            // Pabbly Mailbox
            $use_pabbly = isset($props['use_pabbly']) ? $props['use_pabbly'] : 'off';
            $pabbly_mailbox_address = isset($props['pabbly_mailbox_address']) ? $props['pabbly_mailbox_address'] : null;
            if ('on' === $use_pabbly && !empty($pabbly_mailbox_address)) {
                $post_repository->is_pabbly_enabled = true;
                $post_repository->pabbly_mailbox_address = $pabbly_mailbox_address;
            }
            // Add Redirect Settings To New Settings
            if (isset($props['use_redirect']) && 'on' === $props['use_redirect'] && !empty($props['redirect_url'])) {
                $props['custom_use_redirect'] = 'on';
            }
            if (isset($props['redirect_url']) && !empty($props['redirect_url'])) {
                $props['custom_redirect_url'] = $props['redirect_url'];
                $props['redirect_url'] = '';
            }

            return $props;
        }

        /**
         * Filter Form HTML Output
         *
         * @param $output
         * @param $render_slug
         * @param $module
         *
         * @return array|false|mixed|string|string[]|null
         * @throws @import DOMException
         */
        public function maybe_filter_shortcode_output($output, $render_slug, $module)
        {
            if (function_exists('et_fb_is_enabled') && et_fb_is_enabled()) {
                return $output;
            }
            if (function_exists('et_builder_bfb_enabled') && et_builder_bfb_enabled()) {
                return $output;
            }
            if (is_admin() || wp_doing_ajax() || is_array($output)) {
                return $output;
            }
            if (pwh_dcfh_hc()::DIVI_CONTACT_FORM_SLUG !== $render_slug) {
                return $output;
            }
            if (empty($output)) {
                return $output;
            }
            // Post Request
            $post_request = new PWH_DCFH_Post_Request(false);
            // Module Order Class
            $contact_form_order_class = ET_Builder_Element::get_module_order_class($render_slug);
            /*
             * Add form_unique_id attribute to contact form div
             *
             * If divi is older than 4.13.1
             *
             * @since 1.0.0
             * */
            if (!pwh_dcfh_helpers()::is_divi_413_1_or_above()) {
                $dom = new DOMDocument('1.0', 'UTF-8');
                if (function_exists('mb_convert_encoding')) {
                    $dom->loadHTML(mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                    $dom->encoding = 'utf-8';
                } else {
                    $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                }
                $dom_xpath = new DOMXPath($dom);
                $form = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), '$contact_form_order_class')]");
                if ((isset($form->length) && 0 !== $form->length)) {
                    // $form = $dom->getElementById($contact_form_order_class);
                    $form_item = $form->item(0);
                    $form_item->setAttribute('data-form_unique_id', $module->props['_unique_id']);
                    $output = $dom->saveHTML();
                }
            }
            // Field Focus Design Css
            $field_layout = new PWH_DCFH_Field_Layout($render_slug, $output, $module->props);
            $field_layout->render_field_focus_css();
            // Label Design Css
            $field_layout->render_global_label_css();
            // Desc Design Css
            $field_layout->render_global_desc_css();
            // Use Redirect
            $custom_use_redirect = isset($module->props['custom_use_redirect']) ? $module->props['custom_use_redirect'] : 'off';
            if ('on' === $custom_use_redirect) {
                $redirect_obj = new PWH_DCFH_Redirect($render_slug, $output, $module->props);
                $output = $redirect_obj->output();
            }
            // Submit Button
            $use_custom_button = isset($module->props['custom_button']) ? $module->props['custom_button'] : 'off';
            if ('on' === $use_custom_button) {
                $is_used_captcha = isset($module->props['captcha']) ? $module->props['captcha'] : 'off';
                $button_alignment = isset($module->props['submit_button_alignment']) ? $module->props['submit_button_alignment'] : 'off';
                $submit_btn_obj = new PWH_DCFH_Submit_Button($render_slug, $output, $module->props);
                // If captcha ON Then Rebuild Submit Button
                if ('on' === $is_used_captcha && !in_array($button_alignment, ['off', 'right'], true)) {
                    $output = $submit_btn_obj->rebuild_submit_button_output();
                }
                $submit_btn_obj->css();
            }
            // Submit Wait Text
            $button_wait_text = isset($module->props['submit_button_wait_text']) ? $module->props['submit_button_wait_text'] : '';
            if (!empty($button_wait_text)) {
                $submit_btn_obj = new PWH_DCFH_Submit_Button($render_slug, $output, $module->props);
                $output = $submit_btn_obj->add_wait_text_attribute();
            }
            // Success Button
            $use_success_button = isset($module->props['use_success_button']) ? $module->props['use_success_button'] : 'off';
            if ('on' === $use_success_button) {
                $success_btn_obj = new PWH_DCFH_Success_Button($render_slug, $output, $module->props, $contact_form_order_class);
                if ($post_request->is_contact_form_processed()) {
                    $output = $success_btn_obj->output();
                }
                $success_btn_obj->css();
            }
            // Success Message CSS
            $success_message_obj = new PWH_DCFH_Success_Message($render_slug, $output, $module->props);
            $success_message_obj->css();
            // Add Custom Class To Success Message After Form Submit
            if ($post_request->is_contact_form_processed()) {
                $output = preg_replace('/\bet-pb-contact-message\b/', 'et-pb-contact-message et_pb_success_message', $output);
            }
            // Hide Form Title
            $hide_form_title = isset($module->props['hide_form_title']) ? $module->props['hide_form_title'] : 'off';
            if ('on' === $hide_form_title && $post_request->is_contact_form_processed()) {
                $output = preg_replace('/\bet_pb_contact_main_title\b/', 'et_pb_d_none', $output);
            }
            // Referer URL
            $misc_obj = new PWH_DCFH_Misc($render_slug, $output, $module->props);
            $output = $misc_obj->output_referer_url();
            // File Upload Design Settings
            $field_upload_obj = new PWH_DCFH_Field_File_Upload($render_slug, $output, $module->props);
            $field_upload_obj->css();
            // Datetime Picker Design Settings
            $field_datetime_obj = new PWH_DCFH_Field_Date_Time($render_slug, $output, $module->props);
            $field_datetime_obj->css();
            // Select2
            $field_select_obj = new PWH_DCFH_Field_Select($render_slug, $output, $module->props);
            $field_select_obj->css();

            return $output;
        }

        /**
         * Filter Custom Dynamic Fields
         *
         * @param $custom_fields
         * @param $post_id
         * @param $raw_custom_fields
         *
         * @return mixed
         */
        public function maybe_custom_dynamic_content_fields($custom_fields, $post_id, $raw_custom_fields)
        {
            global $wpdb;
            $templates = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s ", '%'.pwh_dcfh_hc()::CF_EMAIL_TPL_PREFIX.'%'));//db call ok no-cache ok
            if (!empty($templates)) {
                foreach ($templates as $template) {
                    $option = get_option($template);
                    if (!empty($option) && (isset($option['tpl_type']) && in_array($option['tpl_type'], ['general', 'admin', 'confirmation']))) {
                        $tpl_id = esc_html($template);
                        $tpl_name = esc_html($option['tpl_name']);
                        $custom_fields["pwh_dcfh_email_template$tpl_id"] = [
                            'label' => $tpl_name,
                            'type' => 'any',
                            'fields' => [
                                'before' => [
                                    'label' => $tpl_name,
                                    'type' => 'text',
                                    'default' => "[pwh_dcfh_email_template tpl_id='$tpl_id']",
                                    'value' => "[pwh_dcfh_email_template tpl_id='$tpl_id']",
                                    'show_on' => 'text',
                                ],
                            ],
                            'meta_key' => "pwh_dcfh_email_template_meta_key$tpl_id", // phpcs:ignore
                            'group' => __('Email Templates - Divi Contact Form Helper', pwh_dcfh_hc()::TEXT_DOMAIN),
                        ];
                    }
                }
            }

            return $custom_fields;
        }

        /**
         * Add Email Template Shortcode
         *
         * @param $atts
         *
         * @return mixed|string
         */
        public function maybe_add_email_template_shortcode($atts)
        {
            $atts = shortcode_atts(['tpl_id' => ''], $atts, 'pwh_dcfh_email_template');
            $tpl_id = $atts['tpl_id'];
            $option = get_option($tpl_id);
            if (isset($option['email_body']) && !empty($option['email_body'])) {
                return $option['email_body'];
            }

            return '';
        }

    }
}