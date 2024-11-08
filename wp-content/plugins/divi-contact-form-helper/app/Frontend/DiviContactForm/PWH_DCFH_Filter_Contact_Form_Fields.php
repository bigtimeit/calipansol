<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm;

use DOMException;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Field_Layout;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Field_File_Upload;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Field_Date_Time;
use PWH_DCFH\App\Frontend\DiviContactForm\Features\PWH_DCFH_Field_Textarea_Validation;
use ET_Builder_Element;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Filter_Contact_Form_Fields')) {
    class PWH_DCFH_Filter_Contact_Form_Fields
    {

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            add_filter('et_builder_get_child_modules', [$this, 'maybe_add_toggles'], 10, 2);
            add_filter('et_pb_all_fields_unprocessed_'.pwh_dcfh_hc()::DIVI_CONTACT_FORM_FIELDS_SLUG, [$this, 'get_fields']);
            add_filter('et_module_shortcode_output', [$this, 'maybe_filter_shortcode_output'], 10, 3);
        }

        /**
         * Add Custom Toggles
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
                if (pwh_dcfh_hc()::DIVI_CONTACT_FORM_FIELDS_SLUG === $module_slug) {
                    if (!isset($module->settings_modal_toggles) || !isset($module->advanced_fields) || !isset($module->fields_unprocessed)) {
                        continue;
                    }
                    $settings_modal_toggles = $module->settings_modal_toggles;
                    $advanced_fields_toggles = $module->advanced_fields;
                    // General Toggles
                    if (isset($settings_modal_toggles['general']) && !empty($settings_modal_toggles['general']['toggles'])) {
                        $settings_modal_toggles['general']['toggles']['fileupload_settings'] = [
                            'title' => __('File Upload Settings', pwh_dcfh_hc()::TEXT_DOMAIN),
                            'priority' => 15,
                        ];
                        $settings_modal_toggles['general']['toggles']['datetime_settings'] = [
                            'title' => __('Date Time Settings', pwh_dcfh_hc()::TEXT_DOMAIN),
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
                            'priority' => 15,
                        ];
                        $modules[$module_slug]->settings_modal_toggles = $settings_modal_toggles;
                    }
                }
            }
            $is_applied = true;

            return $modules;
        }

        /**
         * Filter Form Fields Fields
         *
         * @param $fields_unprocessed
         *
         * @return array
         */
        public function get_fields($fields_unprocessed)
        {
            $custom_fields = [];
            //==================================================
            // Field
            //==================================================
            $fields_unprocessed['field_title']['label'] = __('Label', pwh_dcfh_hc()::TEXT_DOMAIN);
            $custom_fields['use_field_label'] = [
                'label' => __('Show Label On Frontend', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to show or hide the field label on the frontend.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'show_if_not' => ['field_type' => ['checkbox', 'radio']],
                'default' => 'off',
                'toggle_slug' => 'main_content',
                'option_category' => 'basic_option',
            ];
            $custom_fields['use_field_label_cr'] = [
                'label' => __('Show Label On Frontend', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to show or hide the field label on the frontend.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'show_if_not' => ['field_type' => ['input', 'email', 'text', 'select']],
                'default' => 'on',
                'toggle_slug' => 'main_content',
                'option_category' => 'basic_option',
            ];
            $custom_fields['use_field_placeholder'] = [
                'label' => __('Hide Placeholder', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to show or hide the field placeholder text.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => ['input', 'email', 'text']],
                'toggle_slug' => 'main_content',
                'option_category' => 'basic_option',
            ];
            $custom_fields['field_placeholder'] = [
                'label' => __('Placeholder', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Enter custom placeholder text for the field.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'show_if' => ['field_type' => ['input', 'email', 'text'], 'use_field_placeholder' => 'off'],
                'toggle_slug' => 'main_content',
                'option_category' => 'basic_option',
            ];
            $custom_fields['field_description'] = [
                'label' => __('Description', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Enter custom description text to help explain or describe the field.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'toggle_slug' => 'main_content',
                'option_category' => 'basic_option',
            ];
            $custom_fields['field_description_location'] = [
                'label' => __('Description Location', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'above' => __('Above Field', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'below' => __('Below Field', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => 'below',
                'description' => __('Choose field description location.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'toggle_slug' => 'main_content',
                'option_category' => 'basic_option',
            ];
            $custom_fields['use_field_icon'] = [
                'label' => __('Use Icon', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enable this option to add an icon to the form field.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if_not' => ['use_file_upload' => 'on', 'use_field_label_cr' => 'off'],
                'option_category' => 'field',
                'toggle_slug' => 'field_options',
            ];
            $custom_fields['use_dropdown_search'] = [
                'label' => __('Use Dropdown Search', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to add a search bar to the dropdown items.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'select'],
                'option_category' => 'field',
                'toggle_slug' => 'field_options',
            ];
            $custom_fields['field_icon'] = [
                'label' => __('Icon', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose an icon to display inside the form field.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select_icon',
                'default' => 'off',
                'show_if' => ['use_field_icon' => 'on'],
                'option_category' => 'field',
                'toggle_slug' => 'field_options',
            ];
            $custom_fields['field_icon_color'] = [
                'label' => __('Icon Color', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'color',
                'description' => __('By default, all icon colors in Divi will appear as white or dark gray. If you would like to change the color of your icon, choose your desired color from the color picker using this option.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_color' => true,
                'default' => '',
                'show_if' => ['use_field_icon' => 'on'],
                'option_category' => 'field',
                'toggle_slug' => 'field_options',
            ];
            //==================================================
            // File Upload
            //==================================================
            $custom_fields['use_file_upload'] = [
                'label' => __('Use As File Upload Field', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enable this option to change this input field into a file upload field type for adding attachments to the form.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'off'],
                'option_category' => 'configuration',
                'toggle_slug' => 'fileupload_settings',
            ];
            $custom_fields['file_size'] = [
                'label' => __('Maximum Upload File Size', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'number',
                'description' => __('Set an allowed maximum file size limit for the attachments uploaded to the form. The value is set in kb (kilobytes) and can be calculated easily online from megabits if needed.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'value_type' => 'integer',
                'default' => '1024',
                'default_on_front' => '1024',
                'show_if' => ['use_file_upload' => 'on', 'field_type' => 'input'],
                'option_category' => 'configuration',
                'toggle_slug' => 'fileupload_settings',
            ];
            $custom_fields['warning_file_size'] = [
                'type' => 'warning',
                'value' => true,
                'display_if' => true,
                'message' => sprintf(__('Default WordPress Max Upload Fize Size %1$s KB i.e. %2$s Convert your MB to KB (in binary) using <a href="%3$s">%3$s</a>', pwh_dcfh_hc()::TEXT_DOMAIN),
                    round(wp_max_upload_size() / 1024), size_format(wp_max_upload_size()), 'https://www.gbmb.org/mb-to-kb'),
                'show_if' => ['use_file_upload' => 'on', 'field_type' => 'input'],
                'option_category' => 'configuration',
                'toggle_slug' => 'fileupload_settings',
            ];
            $custom_fields['files_limit'] = [
                'label' => __('Maximum Number Of Files', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Set an allowed maximum number of files which can be attached to the contact form.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'range_settings' => [
                    'min' => '1',
                    'max' => pwh_dcfh_module_helpers()::max_number_files_allowed(),
                    'step' => '1',
                    'min_limit' => 1,
                    'max_limit' => pwh_dcfh_module_helpers()::max_number_files_allowed(),
                ],
                'unitless' => true,
                'show_if' => ['use_file_upload' => 'on', 'field_type' => 'input'],
                'default' => '2',
                'option_category' => 'basic_option',
                'toggle_slug' => 'fileupload_settings',
            ];
            $custom_fields['file_mime'] = [
                'label' => __('Allowed File Types', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'multiple_checkboxes',
                'description' => __('Select the types of files users are allowed to upload to the form.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'options' => pwh_dcfh_module_helpers()::get_mimes_with_ext(),
                'default' => 'on|on',
                'show_if' => ['use_file_upload' => 'on', 'field_type' => 'input'],
                'option_category' => 'configuration',
                'toggle_slug' => 'fileupload_settings',
            ];
            /* $custom_fields['file_type_accepted_text'] = [
                 'label' => __('Accepted File Types Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'type' => 'text',
                 'default' => __('Accepted file types:', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'description' => __('Enter custom text for accepted file type.', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'show_if' => ['use_file_upload' => 'on'],
                 'option_category' => 'configuration',
                 'toggle_slug' => 'fileupload_settings',
             ];
             $custom_fields['file_max_size_text'] = [
                 'label' => __('Max File Size Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'type' => 'text',
                 'default' => __('Max. file size:', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'description' => __('Enter custom text for max file size.', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'show_if' => ['use_file_upload' => 'on'],
                 'option_category' => 'configuration',
                 'toggle_slug' => 'fileupload_settings',
             ];
             $custom_fields['file_no_file_chosen_text'] = [
                 'label' => __('No Files Chosen Text', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'type' => 'text',
                 'default' => __('No Files Chosen', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'description' => __('Enter custom text for no file selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                 'show_if' => ['use_file_upload' => 'on'],
                 'option_category' => 'configuration',
                 'toggle_slug' => 'fileupload_settings',
             ];*/
            //==================================================
            // Date/Time Picker
            //==================================================
            $custom_fields['use_datetime'] = [
                'label' => __('Use As Date/Time Picker', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enable this option to change this input field into a date and time picker field type.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'input', 'use_file_upload' => 'off'],
                'option_category' => 'configuration',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'default_toggle',
            ];
            $custom_fields['warning_toggle_date'] = [
                'label' => '',
                'type' => 'warning',
                'value' => true,
                'display_if' => true,
                'message' => __('Enable Date/Time Picker', pwh_dcfh_hc()::TEXT_DOMAIN),
                'show_if' => [
                    'use_datetime' => 'off',
                ],
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['warning_toggle_time'] = [
                'label' => '',
                'type' => 'warning',
                'value' => true,
                'display_if' => true,
                'message' => __('Enable Date/Time Picker', pwh_dcfh_hc()::TEXT_DOMAIN),
                'show_if' => [
                    'use_datetime' => 'off',
                ],
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'time_toggle',
            ];
            $custom_fields['datetime_type'] = [
                'label' => __('Date/Time Picker Type', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose the type of date and/or time picker field to use.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'both' => __('Date & Time Picker', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'date' => __('Date Picker', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'time' => __('Time Picker', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => 'both',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'affects' => ['datetime_format', 'date_format', 'time_format', 'datetime_week_start'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'default_toggle',
            ];
            $custom_fields['datetime_format'] = [
                'label' => __('Date/Time Picker Format', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Set the date and/or time format to use.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'Y/m/d_dt_h:i' => __('Y/m/d h:i (12-Hours Format)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'Y/m/d_dt_h:ia' => __('Y/m/d h:ia (12-Hours Format am or pm)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'Y-m-d_dt_h:i' => __('Y-m-d h:i (12-Hours Format)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'Y-m-d_dt_h:ia' => __('Y-m-d h:ia (12-Hours Format am or pm)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'Y/m/d_dt_H:i' => __('Y/m/d H:i (24-Hours Format)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'Y/m/d_dt_H:ia' => __('Y/m/d H:ia (24-Hours Format am or pm)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'Y-m-d_dt_H:i' => __('Y-m-d H:i (24-Hours Format)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'Y-m-d_dt_H:ia' => __('Y-m-d H:ia (24-Hours Format am or pm)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'F j, Y_dt_h:i' => __('F j, Y h:i (12-Hours Format)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'F j, Y_dt_h:ia' => __('F j, Y h:ia (12-Hours Format am or pm)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'F j, Y_dt_H:i' => __('F j, Y H:i (24-Hours Format)', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'F j, Y_dt_H:ia' => __('F j, Y H:ia (24-Hours Format am or pm)', pwh_dcfh_hc()::TEXT_DOMAIN),
                ],
                'default' => 'Y/m/d_dt_h:ia',
                'depends_show_if' => 'both',
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'default_toggle',
            ];
            $custom_fields['date_format'] = [
                'label' => __('Date Picker Format', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Set the date format to use.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'Y-m-d' => esc_html('Y-m-d'),
                    'Y/m/d' => esc_html('Y/m/d'),
                    'F j, Y' => esc_html('F j, Y'),
                ],
                'default' => 'Y-m-d',
                'depends_show_if' => 'date',
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['custom_date_format'] = [
                'label' => __('Custom Date Format', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter a custom time format using your preferred PHP parameters. This will override and take priority over any other time format you have selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                ],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['time_format'] = [
                'label' => __('Time Picker Format', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Set the time format to use.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'h:i' => __('h:i (12-Hours Format)'),
                    'h:ia' => __('h:ia (12-Hours Format am or pm)'),
                    'H:i' => __('H:i (24-Hours Format)'),
                    'H:ia' => __('H:ia (24-Hours Format am or pm)'),
                ],
                'default' => 'h:ia',
                'depends_show_if' => 'time',
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'time_toggle',
            ];
            $custom_fields['custom_time_format'] = [
                'label' => __('Custom Time Format', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter a custom time format using your preferred PHP parameters. This will override and take priority over any other time format you have selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                ],
                'show_if_not' => ['datetime_type' => 'date'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'time_toggle',
            ];
            $custom_fields['datetime_week_start'] = [
                'label' => __('Week Start Day', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose the first day of the week in the calendar.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => pwh_dcfh_module_helpers()::get_week_day_names(),
                'default_on_front' => 'sunday',
                'default' => 'sunday',
                'depends_show_if_not' => 'time',
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['datetime_disabled_week_days'] = [
                'label' => esc_html__('Disable Week Days', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => esc_html__('Select days of the week to disable.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'multiple_checkboxes',
                'options' => pwh_dcfh_module_helpers()::get_week_day_names(),
                'default' => 'on|off|off|off|off|off|on',
                'show_if' => ['use_datetime' => 'on'],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'configuration',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['datetime_disabled_weeks'] = [
                'label' => __('Disable Weeks', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enable this option to disabled week days.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'on',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['datetime_disabled_past_days'] = [
                'label' => __('Disable Past Dates', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to prevent past dates from being selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['datetime_disabled_current_date'] = [
                'label' => __('Disable Current Date', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to prevent the current date from being selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['min_date_type'] = [
                'label' => __('Minimum Date Type', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether to use a fixed or relative minimum date.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'off' => et_builder_i18n('None'),
                    'fixed' => et_builder_i18n('Fixed'),
                    'relative' => et_builder_i18n('Relative'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['fixed_min_date'] = [
                'label' => __('Fixed Minimum Date', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter a fixed date to set a limit for the minimum date that can be selected. Set 0 for today.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                    'min_date_type' => 'fixed'
                ],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['relative_min_date'] = [
                'label' => __('Relative Minimum Date', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter a negative or positive number relevant to the current date to set a limit for the minimum date that can be selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                    'min_date_type' => 'relative'
                ],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['max_date_type'] = [
                'label' => __('Maximum Date Type', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose whether to use a fixed or relative maximum date.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'off' => et_builder_i18n('None'),
                    'fixed' => et_builder_i18n('Fixed'),
                    'relative' => et_builder_i18n('Relative'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['fixed_max_date'] = [
                'label' => __('Fixed Maximum Date', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter a fixed date to set a limit for the maximum date that can be selected. Set 0 for today.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                    'max_date_type' => 'fixed'
                ],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['relative_max_date'] = [
                'label' => __('Relative Maximum Date', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter a negative or positive number relevant to the current date to set a limit for the maximum date that can be selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                    'max_date_type' => 'relative'
                ],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['available_dates'] = [
                'label' => __('Available Dates', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter specific dates that are available to be selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                ],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['unavailable_dates'] = [
                'label' => __('Unavailable Dates', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter specific dates that are not available to be selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                ],
                'show_if_not' => ['datetime_type' => 'time'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'date_toggle',
            ];
            $custom_fields['min_time'] = [
                'label' => __('Minimum Time', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter the minimum time limit that can be selected. Set 0 to disabled past time.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                ],
                'show_if_not' => ['datetime_type' => 'date'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'time_toggle',
            ];
            $custom_fields['max_time'] = [
                'label' => __('Maximuum Time', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter the maximuum time limit that can be selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                ],
                'show_if_not' => ['datetime_type' => 'date'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'time_toggle',
            ];
            $custom_fields['available_times'] = [
                'label' => __('Available Times', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter specific times that are available to be selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                ],
                'show_if_not' => ['datetime_type' => 'date'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'time_toggle',
            ];
            $custom_fields['unavailable_times'] = [
                'label' => __('Unavailable Times', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter specific times that are not available to be selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '',
                'show_if' => [
                    'field_type' => 'input',
                    'use_datetime' => 'on',
                ],
                'show_if_not' => ['datetime_type' => 'date'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'time_toggle',
            ];
            $custom_fields['time_steps'] = [
                'label' => __('Time Steps', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Enter the number of minutes to set the interval between the available times that can be selected.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'text',
                'default' => '30',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'time_toggle',
            ];
            $custom_fields['datetime_inline'] = [
                'label' => __('Show Date/Time Picker Inline', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to show the date and time picker all the time by default without first clicking into the field to open it.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'default_toggle',
            ];
            $custom_fields['datetime_setcurrent_datetime'] = [
                'label' => __('Set Input With Current Date/Time', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to set textbox with current date/time onload.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => 'off',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on', 'datetime_inline' => 'off', 'datetime_disabled_current_date' => 'off'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'default_toggle',
            ];
            $custom_fields['datetime_locale'] = [
                'label' => __('Date/Time Picker Language', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose which language to use for the date and time picker text. NOTE: This automatically uses the Site Language set in WordPress General Settings, so there is usually no need to adjust this, but it can be overridden here if needed.',
                    pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'en' => esc_html('English'),
                    'en-GB' => esc_html('English (British)'),
                    'ar' => esc_html('Arabic'),
                    'az' => esc_html('Azerbaijanian (Azeri)'),
                    'bg' => esc_html('Bulgarian'),
                    'bs' => esc_html('Bosanski'),
                    'ca' => esc_html('Català'),
                    'ch' => esc_html('Simplified Chinese'),
                    'cs' => esc_html('Čeština'),
                    'da' => esc_html('Dansk'),
                    'de' => esc_html('German'),
                    'el' => esc_html('Ελληνικά'),
                    'es' => esc_html('Spanish'),
                    'et' => esc_html('"Eesti"'),
                    'eu' => esc_html('Euskara'),
                    'fa' => esc_html('Persian'),
                    'fi' => esc_html('Finnish (Suomi)'),
                    'fr' => esc_html('French'),
                    'gl' => esc_html('Galego'),
                    'he' => esc_html('Hebrew (עברית)'),
                    'hr' => esc_html('Hrvatski'),
                    'hu' => esc_html('Hungarian'),
                    'id' => esc_html('Indonesian'),
                    'it' => esc_html('Italian'),
                    'ja' => esc_html('Japanese'),
                    'ko' => esc_html('Korean (한국어)'),
                    'kr' => esc_html('Korean'),
                    'lt' => esc_html('Lithuanian (lietuvių)'),
                    'lv' => esc_html('Latvian (Latviešu)'),
                    'mk' => esc_html('Macedonian (Македонски)'),
                    'mn' => esc_html('Mongolian (Монгол)'),
                    'nl' => esc_html('Dutch'),
                    'no' => esc_html('Norwegian'),
                    'pl' => esc_html('Polish'),
                    'pt' => esc_html('Portuguese'),
                    'pt-BR' => esc_html('Português(Brasil)'),
                    'ro' => esc_html('Romanian'),
                    'ru' => esc_html('Russian'),
                    'se' => esc_html('Swedish'),
                    'sk' => esc_html('Slovenčina'),
                    'sl' => esc_html('Slovenščina'),
                    'sq' => esc_html('Albanian (Shqip)'),
                    'sr' => esc_html('Serbian Cyrillic (Српски)'),
                    'sr-YU' => esc_html('Serbian (Srpski)'),
                    'sv' => esc_html('Svenska'),
                    'th' => esc_html('Thai'),
                    'tr' => esc_html('Turkish'),
                    'uk' => esc_html('Ukrainian'),
                    'vi' => esc_html('Vietnamese'),
                    'zh' => esc_html('Simplified Chinese (简体中文)'),
                    'zh-TW' => esc_html('Traditional Chinese (繁體中文)'),
                ],
                'default' => pwh_dcfh_helpers()::get_wp_locale(),
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'default_toggle',
            ];
            $custom_fields['datetime_locale_direction'] = [
                'label' => __('Locale Direction RTL', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Set current locale right-to-left (RTL).', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'yes_no_button',
                'options' => [
                    'on' => et_builder_i18n('Yes'),
                    'off' => et_builder_i18n('No'),
                ],
                'default' => is_rtl() ? 'on' : 'off',
                'show_if' => ['field_type' => 'input', 'use_datetime' => 'on'],
                'option_category' => 'basic_option',
                'toggle_slug' => 'datetime_settings',
                'sub_toggle' => 'default_toggle',
            ];
            //==================================================
            // Checkbox Layout
            //==================================================
            $custom_fields['checkbox_layout'] = [
                'label' => __('Checkbox Layout', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose how you want the checkboxes to display.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'column' => et_builder_i18n('Columns'),
                    'inline' => et_builder_i18n('Inline'),
                ],
                'default' => 'column',
                'show_if' => ['field_type' => 'checkbox'],
                'option_category' => 'field',
                'toggle_slug' => 'field_options',
            ];
            $custom_fields['checkbox_columns'] = [
                'label' => __('Number Of Checkbox Columns', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose the number of columns for the checkboxes.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    '1' => et_builder_i18n('1 Column'),
                    '2' => et_builder_i18n('2 Column'),
                    '3' => et_builder_i18n('3 Column'),
                    '4' => et_builder_i18n('4 Column'),
                ],
                'default' => '1',
                'show_if' => ['field_type' => 'checkbox', 'checkbox_layout' => 'column'],
                'mobile_options' => true,
                'option_category' => 'field',
                'toggle_slug' => 'field_options',
            ];
            //==================================================
            // Radio Layout
            //==================================================
            $custom_fields['radio_layout'] = [
                'label' => __('Radio Button Layout', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose how you want the radio buttons to display.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    'column' => et_builder_i18n('Columns'),
                    'inline' => et_builder_i18n('Inline'),
                ],
                'default' => 'column',
                'show_if' => ['field_type' => 'radio'],
                'option_category' => 'field',
                'toggle_slug' => 'field_options',
            ];
            $custom_fields['radio_columns'] = [
                'label' => __('Number Of Radio Button Columns', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose the number of columns for the radio buttons.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'select',
                'options' => [
                    '1' => et_builder_i18n('1 Column'),
                    '2' => et_builder_i18n('2 Column'),
                    '3' => et_builder_i18n('3 Column'),
                    '4' => et_builder_i18n('4 Column'),
                ],
                'default' => '1',
                'show_if' => ['field_type' => 'radio', 'radio_layout' => 'column'],
                'mobile_options' => true,
                'option_category' => 'field',
                'toggle_slug' => 'field_options',
            ];
            //==================================================
            // Message Box Height
            //==================================================
            $custom_fields['textarea_min_height'] = [
                'label' => __('Message Textarea Minimum Height', pwh_dcfh_hc()::TEXT_DOMAIN),
                'description' => __('Choose to adjust the default height of the message box.', pwh_dcfh_hc()::TEXT_DOMAIN),
                'type' => 'range',
                'range_settings' => [
                    'min' => '0',
                    'max' => '800',
                    'step' => '10',
                    'min_limit' => 0,
                    'max_limit' => 800,
                ],
                'fixed_unit' => 'px',
                'validate_unit' => true,
                'fixed_range' => true,
                'default' => '150px',
                'mobile_options' => true,
                'option_category' => 'basic_option',
                'show_if' => ['field_type' => 'text'],
                'toggle_slug' => 'field_options',
            ];
            //==================================================
            // Override Default Based On Custom Fields
            //==================================================
            $fields_unprocessed['allowed_symbols']['show_if'] = [
                'use_file_upload' => 'off',
                'use_datetime' => 'off',
                'field_type' => ['input', 'email', 'text']
            ];
            $fields_unprocessed['min_length']['show_if'] = [
                'use_file_upload' => 'off',
                'use_datetime' => 'off',
                'field_type' => ['input', 'email', 'text']
            ];
            $fields_unprocessed['max_length']['show_if'] = [
                'use_file_upload' => 'off',
                'use_datetime' => 'off',
                'field_type' => ['input', 'email', 'text']
            ];

            return wp_parse_args($custom_fields, $fields_unprocessed);
        }

        /**
         * Filter Form Fields HTML Output
         *
         * @param $output
         * @param $render_slug
         * @param $module
         *
         * @return array|false|mixed|string
         *
         * @throws DOMException
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
            if (pwh_dcfh_hc()::DIVI_CONTACT_FORM_FIELDS_SLUG != $render_slug) {
                return $output;
            }
            if (empty($output)) {
                return $output;
            }
            // Module Order Class
            $module_order_class = ET_Builder_Element::get_module_order_class($render_slug);
            // Field
            $field_type = $module->props['field_type'];
            $field_layout_obj = new PWH_DCFH_Field_Layout($render_slug, $output, $module->props);
            $output = $field_layout_obj->render_label_placeholder_desc_html();
            $field_layout_obj->show_hide_label_placeholder();
            $use_field_icon = isset($module->props['use_field_icon']) ? $module->props['use_field_icon'] : 'off';
            $field_icon = isset($module->props['field_icon']) ? $module->props['field_icon'] : 'off';
            if ('off' !== $use_field_icon && 'off' !== $field_icon) {
                $output = $field_layout_obj->render_field_icon();
            }
            // TextArea Height CSS & Validation
            if ('text' === $field_type) {
                $field_textarea_obj = new PWH_DCFH_Field_Textarea_Validation($render_slug, $output, $module->props);
                $field_layout_obj->render_textarea_min_height_css();
                $output = $field_textarea_obj->output();
            }
            if ('checkbox' === $field_type) {
                $field_layout_obj->render_checkboxes_layout_css();
            }
            if ('radio' === $field_type) {
                $field_layout_obj->render_radio_layout_css();
            }
            // File Upload Settings
            $use_file_upload = isset($module->props['use_file_upload']) ? $module->props['use_file_upload'] : 'off';
            if ('input' === $field_type && 'on' === $use_file_upload) {
                $field_upload_obj = new PWH_DCFH_Field_File_Upload($render_slug, $output, $module->props);
                $output = $field_upload_obj->output();
            }
            // Datetime Settings
            $use_datetime = isset($module->props['use_datetime']) ? $module->props['use_datetime'] : 'off';
            if ('input' === $field_type && 'on' === $use_datetime) {
                $field_datetime_obj = new PWH_DCFH_Field_Date_Time($render_slug, $output, $module->props);
                $output = $field_datetime_obj->output();
            }
            // Select2
            $use_dropdown_search = isset($module->props['use_dropdown_search']) ? $module->props['use_dropdown_search'] : 'off';
            if ('on' === $use_dropdown_search) {
                $output = preg_replace('/\bet_pb_contact_select input\b/', 'et_pb_contact_select input et_pb_contact_select2', $output);
            }
            /* EXTRA */

            /*if ('Heading' === $module->props['field_id']) {

                $dom = new DOMDocument('1.0', 'UTF-8');
                if (function_exists('mb_convert_encoding')) {
                    $dom->loadHTML(mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                    $dom->encoding = 'utf-8';
                } else {
                    $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                }
                $input = $dom->getElementsByTagName('input');
                $p_tag = $dom->getElementsByTagName('p');
                if ((isset($input->length) && 0 !== $input->length) && (isset($p_tag->length) && 0 !== $p_tag->length)) {
                    $input_item = $input->item(0);
                    $p_item = $p_tag->item(0);
                    $p_item->setAttribute('data-conditional-relation', '');
                    $p_item->setAttribute('data-conditional-logic', '');
                    $output = $dom->saveHTML();
                }
            }*/

            return $output;
        }
    }
}