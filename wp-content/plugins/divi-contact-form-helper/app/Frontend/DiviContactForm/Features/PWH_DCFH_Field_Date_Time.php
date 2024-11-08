<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use DOMDocument;
use DOMException;
use ET_Builder_Element;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Field_Date_Time')) {
    class PWH_DCFH_Field_Date_Time
    {

        public $render_slug;

        public $output;

        public $props = [];

        /**
         * Class Construnctor
         */
        public function __construct($render_slug, $output, $props)
        {
            $this->render_slug = $render_slug;
            $this->output = $output;
            $this->props = $props;
        }

        /**
         * Render Output
         *
         * @throws DOMException
         */
        public function output()
        {
            $dom = new DOMDocument('1.0', 'UTF-8');
            if (function_exists('mb_convert_encoding')) {
                $dom->loadHTML(mb_convert_encoding($this->output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $dom->encoding = 'utf-8';
            } else {
                $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$this->output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            }
            $input = $dom->getElementsByTagName('input');
            if (isset($input->length) && 0 !== $input->length) {
                $date_format = '';
                $time_format = '';
                $input_item = $input->item(0);
                $datetime_type = isset($this->props['datetime_type']) ? $this->props['datetime_type'] : 'both';
                $custom_locale = isset($this->props['datetime_locale']) ? $this->props['datetime_locale'] : 'en';
                $datetime_week_start = isset($this->props['datetime_week_start']) ? date_i18n("w", strtotime($this->props['datetime_week_start'])) : 'sunday';
                $time_steps = isset($this->props['time_steps']) ? $this->props['time_steps'] : '30';
                $datetime_disabled_weeks = isset($this->props['datetime_disabled_weeks']) ? $this->props['datetime_disabled_weeks'] : 'on';
                // Locale
                $datetime_locale = pwh_dcfh_helpers()::get_wp_locale();
                if ($custom_locale !== $datetime_locale) {
                    $datetime_locale = $custom_locale;
                }
                // Date & Time Format
                if ('both' === $datetime_type) {
                    $datetime_format = isset($this->props['datetime_format']) ? $this->props['datetime_format'] : 'Y/m/d_dt_h:ia';
                    $datetime_format = explode('_dt_', $datetime_format);
                    $date_format = isset($datetime_format[0]) ? $datetime_format[0] : '';
                    $time_format = isset($datetime_format[1]) ? $datetime_format[1] : '';
                }
                if ('date' === $datetime_type) {
                    $date_format = isset($this->props['date_format']) ? $this->props['date_format'] : 'y/m/d';
                }
                if ('time' === $datetime_type) {
                    $time_format = isset($this->props['time_format']) ? $this->props['time_format'] : 'h:ia';
                }
                // Custom Date & Time Format
                $custom_date_format = isset($this->props['custom_date_format']) ? $this->props['custom_date_format'] : '';
                if ('' !== $custom_date_format) {
                    $date_format = $custom_date_format;
                }
                $custom_time_format = isset($this->props['custom_time_format']) ? $this->props['custom_time_format'] : '';
                if ('' !== $custom_time_format) {
                    $time_format = $custom_time_format;
                }
                // Set Data Attributes
                $input_item->setAttribute('class', $input_item->getAttribute('class').' '.pwh_dcfh_hc()::DATEPICKER_CLASS);
                $input_item->setAttribute('autocomplete', 'off');
                $input_item->setAttribute('data-locale', $datetime_locale);
                $input_item->setAttribute('data-type', $datetime_type);
                $input_item->setAttribute('data-date', $date_format);
                $input_item->setAttribute('data-time', $time_format);
                $input_item->setAttribute('data-week-start', $datetime_week_start);
                $input_item->setAttribute('data-weeks', $datetime_disabled_weeks);
                $input_item->setAttribute('data-time-steps', $time_steps);
                // Disabled Week Days
                $disabled_week_days = isset($this->props['datetime_disabled_week_days']) ? $this->props['datetime_disabled_week_days'] : '';
                if (!empty($disabled_week_days)) {
                    $keys_to_match = array_keys(pwh_dcfh_module_helpers()::get_week_day_names(false));
                    $disabled_week_days = pwh_dcfh_module_helpers()::process_multiple_checkboxes_value($disabled_week_days, $keys_to_match);
                    $input_item->setAttribute('data-disabled-week-days', $disabled_week_days);
                }
                // Disabled Past Days
                $disabled_past_days = isset($this->props['datetime_disabled_past_days']) ? $this->props['datetime_disabled_past_days'] : 'off';
                if ('on' === $disabled_past_days) {
                    $input_item->setAttribute('data-disabled-past-days', 'on');
                }
                // Disabled Past Days
                $disabled_current_date = isset($this->props['datetime_disabled_current_date']) ? $this->props['datetime_disabled_current_date'] : 'off';
                if ('on' === $disabled_current_date) {
                    $input_item->setAttribute('data-disabled-current-date', 'on');
                }
                // Inline
                $datetime_inline = isset($this->props['datetime_inline']) ? $this->props['datetime_inline'] : 'off';
                if ('on' === $datetime_inline) {
                    $input_item->setAttribute('data-inline', 'on');
                }
                // Set Current Datetime
                $setcurrent_datetime = isset($this->props['datetime_setcurrent_datetime']) ? $this->props['datetime_setcurrent_datetime'] : 'off';
                if ('on' === $setcurrent_datetime) {
                    $input_item->setAttribute('data-setcurrent-datetime', 'on');
                }
                // RTL
                $datetime_locale_direction = isset($this->props['datetime_locale_direction']) ? $this->props['datetime_locale_direction'] : 'off';
                if ('on' === $datetime_locale_direction) {
                    $input_item->setAttribute('data-rtl', 'on');
                }
                // Minimum Date
                $minimum_date_type = isset($this->props['min_date_type']) ? $this->props['min_date_type'] : 'off';
                if ('off' !== $minimum_date_type) {
                    $min_date = '';
                    $minimum_date_fixed = isset($this->props['fixed_min_date']) ? $this->props['fixed_min_date'] : '';
                    $minimum_date_relative = isset($this->props['relative_min_date']) ? $this->props['relative_min_date'] : '';
                    if ('fixed' === $minimum_date_type && '' !== $minimum_date_fixed) {
                        $min_date = $minimum_date_fixed;
                        if (0 !== $minimum_date_fixed) {
                            $min_date = date_i18n($date_format, strtotime($minimum_date_fixed));
                        }
                    }
                    if ('relative' === $minimum_date_type && '' !== $minimum_date_relative) {
                        $min_relative_date = absint($minimum_date_relative);
                        if (strpos($minimum_date_relative, '+') !== false) {
                            $min_date = date_i18n($date_format, strtotime(gmdate('Y').'-'.gmdate('m').'-'.$min_relative_date));
                        } elseif (strpos($minimum_date_relative, '-') !== false) {
                            $min_date = "-".date_i18n($date_format, strtotime('1970-01-'.$min_relative_date));
                        }
                    }
                    if ('' !== $min_date) {
                        $input_item->setAttribute('data-min-date-type', $minimum_date_type);
                        $input_item->setAttribute('data-min-date', $min_date);
                    }
                }
                // Maximum Date
                $maximum_date_type = isset($this->props['max_date_type']) ? $this->props['max_date_type'] : 'off';
                if ('off' !== $maximum_date_type) {
                    $max_date = '';
                    $maximum_date_fixed = isset($this->props['fixed_max_date']) ? $this->props['fixed_max_date'] : '';
                    $maximum_date_relative = isset($this->props['relative_max_date']) ? $this->props['relative_max_date'] : '';
                    if ('fixed' === $maximum_date_type && '' !== $maximum_date_fixed) {
                        $max_date = $maximum_date_fixed;
                        if (0 !== $maximum_date_fixed) {
                            $max_date = date_i18n($date_format, strtotime($maximum_date_fixed));
                        }
                    }
                    if ('relative' === $maximum_date_type && '' !== $maximum_date_relative) {
                        $max_relative_date = absint($maximum_date_relative);
                        $operator = '+';
                        if (strpos($maximum_date_relative, '-') !== false) {
                            $operator = '-';
                        }
                        $max_date = $operator.date_i18n($date_format, strtotime('1970-01-'.$max_relative_date));
                    }
                    if ('' !== $max_date) {
                        $input_item->setAttribute('data-max-date-type', $maximum_date_type);
                        $input_item->setAttribute('data-max-date', $max_date);
                    }
                }
                // Available Dates
                $available_dates = isset($this->props['available_dates']) ? $this->props['available_dates'] : '';
                if ('' !== $available_dates) {
                    $explode_available_dates = explode(',', $available_dates);
                    $imploade_available_dates = implode(',', array_map(function ($value) use ($date_format) {
                        return date_i18n($date_format, strtotime($value));
                    }, $explode_available_dates));
                    if (!empty($imploade_available_dates)) {
                        $input_item->setAttribute('data-available-dates', $imploade_available_dates);
                    }
                }
                // Unavailable Dates
                $unavailable_dates = isset($this->props['unavailable_dates']) ? $this->props['unavailable_dates'] : '';
                if ('' !== $unavailable_dates) {
                    $explode_unavailable_dates = explode(',', $unavailable_dates);
                    $imploade_unavailable_dates = implode(',', array_map(function ($value) use ($date_format) {
                        return date_i18n($date_format, strtotime($value));
                    }, $explode_unavailable_dates));
                    if (!empty($imploade_unavailable_dates)) {
                        $input_item->setAttribute('data-unavailable-dates', $imploade_unavailable_dates);
                    }
                }
                // Minimum Time
                $min_time = isset($this->props['min_time']) ? $this->props['min_time'] : '';
                if ('' !== $min_time) {
                    $input_item->setAttribute('data-min-time', $min_time);
                }
                // Maximum Time
                $max_time = isset($this->props['max_time']) ? $this->props['max_time'] : '';
                if ('' !== $max_time) {
                    $input_item->setAttribute('data-max-time', $max_time);
                }
                // Available Times
                $available_times = isset($this->props['available_times']) ? $this->props['available_times'] : '';
                if ('' !== $available_times) {
                    $input_item->setAttribute('data-available-times', $available_times);
                }
                // Unavailable Times
                $unavailable_times = isset($this->props['unavailable_times']) ? $this->props['unavailable_times'] : '';
                if ('' !== $unavailable_times) {
                    $explode_unavailable_times = explode(',', $unavailable_times);
                    $implode_unavailable_times = implode(',', array_map(function ($value) use ($time_format) {
                        return date_i18n($time_format, strtotime($value));
                    }, $explode_unavailable_times));
                    $input_item->setAttribute('data-unavailable-times', $implode_unavailable_times);
                }
                $this->output = $dom->saveHTML();
            }

            return $this->output;
        }

        /**
         * Render Css
         *
         * @return void
         */
        public function css()
        {
            $main_class = '%%order_class%%.xdsoft_datetimepicker';
            // Background
            $d_datetimepicker_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datetimepicker_background');
            et_pb_responsive_options()->generate_responsive_css($d_datetimepicker_background, "$main_class, $main_class .xdsoft_datepicker .xdsoft_month,$main_class .xdsoft_datepicker .xdsoft_year ", 'background', $this->render_slug, "!important;", 'color');
            // Padding
            $d_padding = et_pb_responsive_options()->get_property_values($this->props, 'datetimepicker_padding');
            $d_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_padding);
            et_pb_responsive_options()->generate_responsive_css($d_padding, $main_class, 'padding', $this->render_slug, '', 'custom_padding');
            // Margin
            $d_margin = et_pb_responsive_options()->get_property_values($this->props, 'datetimepicker_margin');
            $d_margin = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_margin);
            et_pb_responsive_options()->generate_responsive_css($d_margin, $main_class, 'margin', $this->render_slug, '', 'custom_padding');
            // Border
            $d_border = et_pb_responsive_options()->get_property_values($this->props, 'datetimepicker_border');
            $d_border = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_border);
            et_pb_responsive_options()->generate_responsive_css($d_border, $main_class, 'border-radius', $this->render_slug, '', 'border-radius');
            // Border Color
            $d_border_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datetimepicker_border_color');
            et_pb_responsive_options()->generate_responsive_css($d_border_color, $main_class, 'border-color', $this->render_slug, '', 'color');
            // Border Width
            $d_border_width = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datetimepicker_border_width');
            et_pb_responsive_options()->generate_responsive_css($d_border_width, $main_class, 'border-width', $this->render_slug);
            // Border Style
            $d_border_style = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datetimepicker_border_style');
            et_pb_responsive_options()->generate_responsive_css($d_border_style, $main_class, 'border-style', $this->render_slug, '', 'select');
            // Shadow
            $box_shadow_preset = isset($this->props['datetimepicker_shadow']) ? $this->props['datetimepicker_shadow'] : null;
            if (!empty($box_shadow_preset) && 'none' !== $box_shadow_preset) {
                $shadow = pwh_dcfh_module_helpers()::get_shadow_presets_values($box_shadow_preset);
                $horizontal = $shadow['horizontal'];
                $vertical = $shadow['vertical'];
                $blur = $shadow['blur'];
                $spread = $shadow['spread'];
                $position = $shadow['position'];
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $main_class,
                    'declaration' => "
                    -webkit-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    -moz-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                     box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    ",
                ]);
            }
            // Datepicker Width
            $d_width = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_width');
            $d_width['tablet'] = (isset($d_width['tablet']) && empty($d_width['tablet'])) ? '224px' : $d_width['tablet'];
            $d_width['phone'] = (isset($d_width['phone']) && empty($d_width['phone'])) ? '224px' : $d_width['phone'];
            et_pb_responsive_options()->generate_responsive_css($d_width, "$main_class .xdsoft_datepicker", 'width', $this->render_slug);
            // Height Timepicker
            $d_height = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_height');
            $d_height['tablet'] = (isset($d_height['tablet']) && empty($d_height['tablet'])) ? '' : $d_height['tablet'];
            $d_height['phone'] = (isset($d_height['phone']) && empty($d_height['phone'])) ? '' : $d_height['phone'];
            et_pb_responsive_options()->generate_responsive_css($d_height, "$main_class .xdsoft_calendar table", 'height', $this->render_slug);
            // Yearmonth Font
            $desktop_font = isset($this->props['datepicker_yearmonth_font']) ? $this->props['datepicker_yearmonth_font'] : null;
            $datepicker_yearmonth_font_last_edited = isset($this->props['datepicker_yearmonth_font_last_edited']) ? $this->props['datepicker_yearmonth_font_last_edited'] : null;
            ET_Builder_Element::set_style($this->render_slug, [
                'selector' => "$main_class .xdsoft_datepicker .xdsoft_month,$main_class .xdsoft_datepicker .xdsoft_year",
                'declaration' => sprintf('%s;', et_builder_set_element_font($desktop_font)),
            ]);
            if (et_pb_get_responsive_status($datepicker_yearmonth_font_last_edited)) {
                $tablet_font = isset($this->props['datepicker_yearmonth_font_tablet']) ? $this->props['datepicker_yearmonth_font_tablet'] : null;
                $phone_font = isset($this->props['datepicker_yearmonth_font_phone']) ? $this->props['datepicker_yearmonth_font_phone'] : null;
                if (!empty($tablet_font)) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$main_class .xdsoft_datepicker .xdsoft_month,$main_class .xdsoft_datepicker .xdsoft_year",
                        'declaration' => sprintf('%s;', et_builder_set_element_font($tablet_font)),
                        'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                    ]);
                }
                if (!empty($phone_font)) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$main_class .xdsoft_datepicker .xdsoft_month,$main_class .xdsoft_datepicker .xdsoft_year",
                        'declaration' => sprintf('%s;', et_builder_set_element_font($phone_font)),
                        'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                    ]);
                }
            }
            // Yearmonth Alignment
            $d_alignment = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_yearmonth_alignment');
            et_pb_responsive_options()->generate_responsive_css($d_alignment, "$main_class .xdsoft_monthselect,$main_class .xdsoft_month", 'text-align', $this->render_slug, '', 'text_alignment');
            // Yearmonth Color
            $d_color = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_yearmonth_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_color, "$main_class .xdsoft_datepicker .xdsoft_month,$main_class .xdsoft_datepicker .xdsoft_year", 'color', $this->render_slug, '', 'color');
            // Yearmonth Background
            $d_background = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_yearmonth_background');
            et_pb_responsive_options()->generate_responsive_css($d_background, "$main_class .xdsoft_monthselect .xdsoft_option.xdsoft_current,$main_class .xdsoft_yearselect .xdsoft_option.xdsoft_current", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_background, "$main_class .xdsoft_monthselect .xdsoft_option.xdsoft_current,$main_class .xdsoft_yearselect .xdsoft_option.xdsoft_current", 'box-shadow', $this->render_slug, " 1px 3px 0 inset !important;", 'color');
            if (et_pb_hover_options()->is_enabled('datepicker_yearmonth_background', $this->props)) {
                $d_datepicker_yearmonth_background = et_pb_hover_options()->get_value('datepicker_yearmonth_background', $this->props);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$main_class .xdsoft_monthselect .xdsoft_option:hover,$main_class .xdsoft_yearselect .xdsoft_option:hover",
                    'declaration' => "background:$d_datepicker_yearmonth_background !important;"
                ]);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$main_class .xdsoft_monthselect .xdsoft_option:hover,$main_class .xdsoft_yearselect .xdsoft_option:hover",
                    'declaration' => "box-shadow:$d_datepicker_yearmonth_background 1px 3px 0 inset !important;"
                ]);
            }
            // Yearmonth Size
            $d_size = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_yearmonth_size');
            et_pb_responsive_options()->generate_responsive_css($d_size, "$main_class .xdsoft_datepicker .xdsoft_month,$main_class .xdsoft_datepicker .xdsoft_year", 'font-size', $this->render_slug);
            // Yearmonth Letter Spacing
            $d_letter = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_yearmonth_letter_space');
            et_pb_responsive_options()->generate_responsive_css($d_letter, "$main_class .xdsoft_datepicker .xdsoft_month,$main_class .xdsoft_datepicker .xdsoft_year", 'letter-spacing', $this->render_slug);
            // Yearmonth Line Height
            $line_height_important = et_builder_has_limitation('force_use_global_important') ? ' !important;' : '';
            $d_line_height = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_yearmonth_line_height');
            et_pb_responsive_options()->generate_responsive_css($d_line_height, "$main_class .xdsoft_datepicker .xdsoft_month,$main_class .xdsoft_datepicker .xdsoft_year", 'line-height', $this->render_slug, $line_height_important);
            // Week Days Font
            $desktop_font = isset($this->props['datepicker_weekdays_font']) ? $this->props['datepicker_weekdays_font'] : null;
            $datepicker_weekdays_font_last_edited = isset($this->props['datepicker_weekdays_font_last_edited']) ? $this->props['datepicker_weekdays_font_last_edited'] : null;
            ET_Builder_Element::set_style($this->render_slug, [
                'selector' => "$main_class .xdsoft_calendar th",
                'declaration' => sprintf('%s;', et_builder_set_element_font($desktop_font)),
            ]);
            if (et_pb_get_responsive_status($datepicker_weekdays_font_last_edited)) {
                $tablet_font = isset($this->props['datepicker_weekdays_font_tablet']) ? $this->props['datepicker_weekdays_font_tablet'] : null;
                $phone_font = isset($this->props['datepicker_weekdays_font_phone']) ? $this->props['datepicker_weekdays_font_phone'] : null;
                if (!empty($tablet_font)) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$main_class .xdsoft_calendar th",
                        'declaration' => sprintf('%s;', et_builder_set_element_font($tablet_font)),
                        'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                    ]);
                }
                if (!empty($phone_font)) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$main_class .xdsoft_calendar th",
                        'declaration' => sprintf('%s;', et_builder_set_element_font($phone_font)),
                        'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                    ]);
                }
            }
            // Week Days Alignment
            $d_alignment = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_weekdays_alignment');
            et_pb_responsive_options()->generate_responsive_css($d_alignment, "$main_class .xdsoft_calendar th", 'text-align', $this->render_slug, '', 'text_alignment');
            // Week Days Color
            $d_color = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_weekdays_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_color, "$main_class .xdsoft_calendar th", 'color', $this->render_slug, '', 'color');
            // Week Days Size
            $d_size = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_weekdays_size');
            et_pb_responsive_options()->generate_responsive_css($d_size, "$main_class .xdsoft_calendar th", 'font-size', $this->render_slug);
            // Week Days Letter Spacing
            $d_letter = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_weekdays_letter_space');
            et_pb_responsive_options()->generate_responsive_css($d_letter, "$main_class .xdsoft_calendar th", 'letter-spacing', $this->render_slug);
            // Week Days Line Height
            $line_height_important = et_builder_has_limitation('force_use_global_important') ? ' !important;' : '';
            $d_line_height = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_weekdays_line_height');
            et_pb_responsive_options()->generate_responsive_css($d_line_height, "$main_class .xdsoft_calendar th", 'line-height', $this->render_slug, $line_height_important);
            // Calendar Days Font
            $desktop_font = isset($this->props['datepicker_dates_font']) ? $this->props['datepicker_dates_font'] : null;
            $datepicker_dates_font_last_edited = isset($this->props['datepicker_dates_font_last_edited']) ? $this->props['datepicker_dates_font_last_edited'] : null;
            ET_Builder_Element::set_style($this->render_slug, [
                'selector' => "$main_class .xdsoft_date",
                'declaration' => sprintf('%s;', et_builder_set_element_font($desktop_font)),
            ]);
            if (et_pb_get_responsive_status($datepicker_dates_font_last_edited)) {
                $tablet_font = isset($this->props['datepicker_dates_font_tablet']) ? $this->props['datepicker_dates_font_tablet'] : null;
                $phone_font = isset($this->props['datepicker_dates_font_phone']) ? $this->props['datepicker_dates_font_phone'] : null;
                if (!empty($tablet_font)) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$main_class .xdsoft_date",
                        'declaration' => sprintf('%s;', et_builder_set_element_font($tablet_font)),
                        'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                    ]);
                }
                if (!empty($phone_font)) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$main_class .xdsoft_date",
                        'declaration' => sprintf('%s;', et_builder_set_element_font($phone_font)),
                        'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                    ]);
                }
            }
            // Calendar Days Alignment
            $d_alignment = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_dates_alignment');
            et_pb_responsive_options()->generate_responsive_css($d_alignment, "$main_class .xdsoft_calendar td>div", 'text-align', $this->render_slug, '', 'text_alignment');
            // Calendar Days Color
            $d_color = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_dates_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_color, "$main_class .xdsoft_date", 'color', $this->render_slug, '', 'color');
            // Calendar Days Size
            $d_size = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_dates_size');
            et_pb_responsive_options()->generate_responsive_css($d_size, "$main_class .xdsoft_date", 'font-size', $this->render_slug);
            // Calendar Days Letter Spacing
            $d_letter = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_dates_letter_space');
            et_pb_responsive_options()->generate_responsive_css($d_letter, "$main_class .xdsoft_date", 'letter-spacing', $this->render_slug);
            // Calendar Days Line Height
            $line_height_important = et_builder_has_limitation('force_use_global_important') ? ' !important;' : '';
            $d_line_height = et_pb_responsive_options()->get_property_values($this->props, 'datepicker_dates_line_height');
            et_pb_responsive_options()->generate_responsive_css($d_line_height, "$main_class .xdsoft_date", 'line-height', $this->render_slug, $line_height_important);
            // Current Date Background and Text Color
            $d_datepicker_current_date_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datepicker_current_date_background');
            $d_datepicker_current_date_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datepicker_current_date_color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_current_date_background, "$main_class .xdsoft_date.xdsoft_today", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_current_date_background, "$main_class .xdsoft_date.xdsoft_today", 'box-shadow', $this->render_slug, " 1px 3px 0 inset !important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_current_date_color, "$main_class .xdsoft_date.xdsoft_today", 'color', $this->render_slug, "!important;", 'color');
            if (et_pb_hover_options()->is_enabled('datepicker_current_date_background', $this->props)) {
                $d_datepicker_date_hover_background = et_pb_hover_options()->get_value('datepicker_current_date_background', $this->props);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$main_class td.xdsoft_today > div:hover",
                    'declaration' => "background:$d_datepicker_date_hover_background !important;"
                ]);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$main_class  td.xdsoft_today > div:hover",
                    'declaration' => "box-shadow:$d_datepicker_date_hover_background 1px 3px 0 inset !important;"
                ]);
            }
            // Selected Date Background and Text Color
            $d_datepicker_selected_date_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datepicker_selected_date_background');
            $d_datepicker_selected_date_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datepicker_selected_date_color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_selected_date_background, "$main_class .xdsoft_date.xdsoft_current:not(.xdsoft_today)", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_selected_date_background, "$main_class.xdsoft_datetimepicker .xdsoft_calendar td:hover", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_selected_date_background, "$main_class .xdsoft_date.xdsoft_current:not(.xdsoft_today)", 'box-shadow', $this->render_slug, " 1px 3px 0 inset !important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_selected_date_color, "$main_class .xdsoft_date.xdsoft_current:not(.xdsoft_today)", 'color', $this->render_slug, "!important;", 'color');
            // Available Dates Background and Text Color
            $d_datepicker_available_dates_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datepicker_available_dates_background');
            $d_datepicker_available_dates_text_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datepicker_available_dates_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_available_dates_background, "$main_class .xdsoft_date:not(.xdsoft_disabled):not(.xdsoft_current)", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_available_dates_text_color, "$main_class .xdsoft_date:not(.xdsoft_disabled):not(.xdsoft_current)", 'color', $this->render_slug, "!important;", 'color');
            // Unvailable Dates Background and Text Color
            $d_datepicker_unavailable_dates_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datepicker_unavailable_dates_background');
            $d_datepicker_unavailable_dates_text_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'datepicker_unavailable_dates_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_unavailable_dates_background, "$main_class .xdsoft_date.xdsoft_disabled", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_datepicker_unavailable_dates_text_color, "$main_class .xdsoft_date.xdsoft_disabled", 'color', $this->render_slug, "!important;", 'color');
            // Width Timepicker
            $d_width = et_pb_responsive_options()->get_property_values($this->props, 'timepicker_width');
            $d_width['tablet'] = (isset($d_width['tablet']) && empty($d_width['tablet'])) ? '58px' : $d_width['tablet'];
            $d_width['phone'] = (isset($d_width['phone']) && empty($d_width['phone'])) ? '58px' : $d_width['phone'];
            et_pb_responsive_options()->generate_responsive_css($d_width, "$main_class .xdsoft_timepicker", 'width', $this->render_slug);
            // Height Timepicker
            $d_height = et_pb_responsive_options()->get_property_values($this->props, 'timepicker_height');
            $d_height['tablet'] = (isset($d_height['tablet']) && empty($d_height['tablet'])) ? '151px' : $d_height['tablet'];
            $d_height['phone'] = (isset($d_height['phone']) && empty($d_height['phone'])) ? '151px' : $d_height['phone'];
            et_pb_responsive_options()->generate_responsive_css($d_height, "$main_class .xdsoft_timepicker .xdsoft_time_box", 'height', $this->render_slug);
            // Timepicker Times Font
            $desktop_font = isset($this->props['timepicker_time_font']) ? $this->props['timepicker_time_font'] : null;
            $timepicker_time_font_last_edited = isset($this->props['timepicker_time_font_last_edited']) ? $this->props['timepicker_time_font_last_edited'] : null;
            ET_Builder_Element::set_style($this->render_slug, [
                'selector' => "$main_class .xdsoft_time",
                'declaration' => sprintf('%s;', et_builder_set_element_font($desktop_font)),
            ]);
            if (et_pb_get_responsive_status($timepicker_time_font_last_edited)) {
                $tablet_font = isset($this->props['timepicker_time_font_tablet']) ? $this->props['timepicker_time_font_tablet'] : null;
                $phone_font = isset($this->props['timepicker_time_font_phone']) ? $this->props['timepicker_time_font_phone'] : null;
                if (!empty($tablet_font)) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$main_class .xdsoft_time",
                        'declaration' => sprintf('%s;', et_builder_set_element_font($tablet_font)),
                        'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                    ]);
                }
                if (!empty($phone_font)) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$main_class .xdsoft_time",
                        'declaration' => sprintf('%s;', et_builder_set_element_font($phone_font)),
                        'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                    ]);
                }
            }
            // Timepicker Times Color
            $d_color = et_pb_responsive_options()->get_property_values($this->props, 'timepicker_time_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_color, "$main_class .xdsoft_timepicker .xdsoft_time_box>div>div", 'color', $this->render_slug, '', 'color');
            // Timepicker Times Alignment
            $d_alignment = et_pb_responsive_options()->get_property_values($this->props, 'timepicker_time_alignment');
            et_pb_responsive_options()->generate_responsive_css($d_alignment, "$main_class .xdsoft_timepicker .xdsoft_time_box>div>div", 'text-align', $this->render_slug, '', 'text_alignment');
            // Timepicker Times Size
            $d_size = et_pb_responsive_options()->get_property_values($this->props, 'timepicker_time_size');
            et_pb_responsive_options()->generate_responsive_css($d_size, "$main_class .xdsoft_time", 'font-size', $this->render_slug, '!important');
            // Timepicker Times Letter Spacing
            $d_letter = et_pb_responsive_options()->get_property_values($this->props, 'timepicker_time_letter_space');
            et_pb_responsive_options()->generate_responsive_css($d_letter, "$main_class .xdsoft_time", 'letter-spacing', $this->render_slug);
            // Timepicker Times Line Height
            $line_height_important = et_builder_has_limitation('force_use_global_important') ? ' !important' : '';
            $d_line_height = et_pb_responsive_options()->get_property_values($this->props, 'timepicker_time_line_height');
            et_pb_responsive_options()->generate_responsive_css($d_line_height, "$main_class .xdsoft_timepicker .xdsoft_time_box>div>div", 'line-height', $this->render_slug, $line_height_important);
            // Current Time Background and Text Color
            $d_timpicker_selected_time_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'timpicker_selected_time_background');
            $d_timpicker_selected_time_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'timpicker_selected_time_color');
            et_pb_responsive_options()->generate_responsive_css($d_timpicker_selected_time_background, "$main_class .xdsoft_time.xdsoft_current", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_timpicker_selected_time_background, "$main_class .xdsoft_time.xdsoft_current", 'box-shadow', $this->render_slug, " 1px 3px 0 inset !important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_timpicker_selected_time_color, "$main_class .xdsoft_time.xdsoft_current", 'color', $this->render_slug, "!important;", 'color');
            if (et_pb_hover_options()->is_enabled('timpicker_selected_time_background', $this->props)) {
                $d_timpicker_hover_background = et_pb_hover_options()->get_value('timpicker_selected_time_background', $this->props);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$main_class .xdsoft_timepicker .xdsoft_time_box > div > div:hover",
                    'declaration' => "background:$d_timpicker_hover_background !important;"
                ]);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$main_class .xdsoft_timepicker .xdsoft_time_box > div > div:hover",
                    'declaration' => "box-shadow:$d_timpicker_hover_background 1px 3px 0 inset !important;"
                ]);
            }
            // Available Times Background and Text Color
            $d_timpicker_available_dates_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'timpicker_available_times_background');
            $d_timpicker_available_dates_text_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'timpicker_available_times_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_timpicker_available_dates_background, "$main_class .xdsoft_time:not(.xdsoft_disabled):not(.xdsoft_current)", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_timpicker_available_dates_text_color, "$main_class .xdsoft_time:not(.xdsoft_disabled):not(.xdsoft_current)", 'color', $this->render_slug, "!important;", 'color');
            // Unvailable Times Background and Text Color
            $d_timpicker_unavailable_times_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'timpicker_unavailable_times_background');
            $d_timpicker_unavailable_times_text_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'timpicker_unavailable_times_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_timpicker_unavailable_times_background, "$main_class .xdsoft_time.xdsoft_disabled", 'background', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_timpicker_unavailable_times_text_color, "$main_class .xdsoft_time.xdsoft_disabled", 'color', $this->render_slug, "!important;", 'color');
        }

    }
}