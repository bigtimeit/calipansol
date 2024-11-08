<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use DOMDocument;
use ET_Builder_Element;
use DOMException;
use DOMXPath;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Output_Redirect')) {
    class PWH_DCFH_Field_Layout
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
         * Render Input Desc and Placeholder HTML
         *
         * @return false|string
         * @throws DOMException
         */
        public function render_label_placeholder_desc_html()
        {
            $field_description = isset($this->props['field_description']) ? $this->props['field_description'] : '';
            $field_description_location = isset($this->props['field_description_location']) ? $this->props['field_description_location'] : 'below';
            $field_placeholder = isset($this->props['field_placeholder']) ? $this->props['field_placeholder'] : '';
            $field_type = $this->props['field_type'];
            $field_order_class = ET_Builder_Element::get_module_order_class($this->render_slug);
            $field_index = pwh_dcfh_helpers()::get_field_index($field_order_class);
            $dom = new DOMDocument('1.0', 'UTF-8');
            if (function_exists('mb_convert_encoding')) {
                $dom->loadHTML(mb_convert_encoding($this->output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $dom->encoding = 'utf-8';
            } else {
                $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$this->output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            }
            $span_element = $dom->createElement('span', esc_attr($field_description));
            $span_element->setAttribute('id', 'et_pb_contact_name_desc_'.$field_index);
            $span_element->setAttribute('class', 'et_pb_contact_field_desc');
            // IF Input,Email Text & Select
            if (in_array($field_type, ['input', 'email', 'text', 'select'])) {
                if ('input' === $field_type || 'email' === $field_type) {
                    $qualified_name = 'input';
                } elseif ('text' === $field_type) {
                    $qualified_name = 'textarea';
                } else {
                    $qualified_name = 'select';
                }
                $p_tag = $dom->getElementsByTagName('p');
                $input_tag = $dom->getElementsByTagName($qualified_name);
                if ((isset($p_tag->length) && 0 !== $p_tag->length) && isset($input_tag->length) && 0 !== $input_tag->length) {
                    $input_item = $input_tag->item(0);
                    $p_item = $p_tag->item(0);
                    if (!empty($field_description)) {
                        'below' === $field_description_location ? $p_item->appendChild($span_element) : $p_item->insertBefore($span_element, $input_item);
                    }
                    if (in_array($field_type, ['input', 'email', 'text']) && !empty($field_placeholder)) {
                        $input_item->setAttribute('placeholder', esc_attr($field_placeholder));
                    }
                }
            }
            // IF Checkbox,Radio & Select
            if (in_array($field_type, ['checkbox', 'radio'])) {
                if (!empty($field_description)) {
                    $dom_xpath = new DOMXPath($dom);
                    if ('below' === $field_description_location) {
                        $field_options_wrapper = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'et_pb_contact_field_options_list')]");
                        $field_options_wrapper_item = $field_options_wrapper->item(0);
                        $field_options_wrapper_item->appendChild($span_element);
                    } else {
                        $field_options_wrapper = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'et_pb_contact_field_options_title')]");
                        $field_options_wrapper_item = $field_options_wrapper->item(0);
                        $field_options_wrapper_item->appendChild($span_element);
                    }
                }
            }
            $this->output = $dom->saveHTML();

            return $this->output;
        }

        /*
        * Render Input Css
        * */
        public function show_hide_label_placeholder()
        {
            // Show Label Text.Input,Email and Select
            $use_field_label = isset($this->props['use_field_label']) ? $this->props['use_field_label'] : 'off';
            $field_description = isset($this->props['field_description']) ? $this->props['field_description'] : '';
            $field_description_location = isset($this->props['field_description_location']) ? $this->props['field_description_location'] : 'below';
            if ('on' === $use_field_label) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_form_label',
                    'declaration' => 'display:flex;',
                ]);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_form_label',
                    'declaration' => 'font-weight: 600;margin-bottom: 0.5em;',
                ]);
            }
            if ('select' === $this->props['field_type']) {
                $select_declaration = '';
                if ('off' === $use_field_label && !empty($field_description)) {
                    $select_declaration = 'below' === $field_description_location ? 'top:36% !important' : 'top:65% !important';
                }
                if ('on' === $use_field_label && !empty($field_description)) {
                    $select_declaration = 'below' === $field_description_location ? 'top:55% !important' : 'top:75% !important';
                }
                if ('on' === $use_field_label && empty($field_description)) {
                    $select_declaration = 'top:75% !important';
                }
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%%.et_pb_contact_field[data-type=select]:after',
                    'declaration' => $select_declaration,
                ]);
            }
            // Hide Label Checkbox and Radio
            $use_field_label_cr = isset($this->props['use_field_label_cr']) ? $this->props['use_field_label_cr'] : 'off';
            if ('off' === $use_field_label_cr) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_field_options_title',
                    'declaration' => 'display:none;',
                ]);
            }
            // Hide Placeholder
            $use_field_placeholder = isset($this->props['use_field_placeholder']) ? $this->props['use_field_placeholder'] : 'off';
            if ('on' === $use_field_placeholder) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%%  ::-webkit-input-placeholder,%%order_class%%  ::-moz-placeholder,%%order_class%%  ::-ms-input-placeholder',
                    'declaration' => 'color:transparent !important;',
                ]);
            }
        }

        /**
         * Render Global Label Design Settings
         *
         * @return void
         */
        public function render_global_label_css()
        {
            $class = '%%order_class%% .et_pb_contact_form_label,%%order_class%% .et_pb_contact_field_options_title';
            $desktop_font = isset($this->props['field_label_font']) ? $this->props['field_label_font'] : null;
            $tablet_font = isset($this->props['field_label_font_tablet']) ? $this->props['field_label_font_tablet'] : null;
            $phone_font = isset($this->props['field_label_font_phone']) ? $this->props['field_label_font_phone'] : null;
            // Text Font
            if (!empty($desktop_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($desktop_font)),
                ]);
            }
            if (!empty($tablet_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($tablet_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if (!empty($phone_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($phone_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
            }
            // Text Color
            $d_text_color = et_pb_responsive_options()->get_property_values($this->props, 'field_label_color');
            et_pb_responsive_options()->generate_responsive_css($d_text_color, $class, 'color', $this->render_slug, '', 'color');
            // Background Color
            $d_box_background = et_pb_responsive_options()->get_property_values($this->props, 'field_label_background');
            et_pb_responsive_options()->generate_responsive_css($d_box_background, $class, 'background', $this->render_slug, '', 'color');
            // Text Size
            $d_text_size = et_pb_responsive_options()->get_property_values($this->props, 'field_label_size');
            et_pb_responsive_options()->generate_responsive_css($d_text_size, $class, 'font-size', $this->render_slug);
            // Text Letter Spacing
            $d_text_letter = et_pb_responsive_options()->get_property_values($this->props, 'field_label_letter_space');
            et_pb_responsive_options()->generate_responsive_css($d_text_letter, $class, 'letter-spacing', $this->render_slug);
            // Text Line Height
            $d_text_line = et_pb_responsive_options()->get_property_values($this->props, 'field_label_line_height');
            et_pb_responsive_options()->generate_responsive_css($d_text_line, $class, 'line-height', $this->render_slug);
            // Text Alignment
            $d_text_alignment = et_pb_responsive_options()->get_property_values($this->props, 'field_label_alignment');
            if (!empty($d_text_alignment)) {
                $d_text_alignment = pwh_dcfh_module_helpers()::get_flex_alignment($d_text_alignment);
            }
            et_pb_responsive_options()->generate_responsive_css($d_text_alignment, $class, 'justify-content', $this->render_slug, '', 'text_alignment');
            // Margin
            $d_margin = et_pb_responsive_options()->get_property_values($this->props, 'field_label_margin');
            $d_margin = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_margin);
            et_pb_responsive_options()->generate_responsive_css($d_margin, $class, 'margin', $this->render_slug, '', 'custom_padding');
            // Padding
            $d_padding = et_pb_responsive_options()->get_property_values($this->props, 'field_label_padding');
            $d_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_padding);
            et_pb_responsive_options()->generate_responsive_css($d_padding, $class, 'padding', $this->render_slug, '', 'custom_padding');
            // Border
            $d_border = et_pb_responsive_options()->get_property_values($this->props, 'field_label_border');
            $d_border = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_border);
            et_pb_responsive_options()->generate_responsive_css($d_border, $class, 'border-radius', $this->render_slug, '', 'border-radius');
            // Border Color
            $d_border_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'field_label_border_color');
            et_pb_responsive_options()->generate_responsive_css($d_border_color, $class, 'border-color', $this->render_slug, '', 'color');
            // Border Width
            $d_border_width = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'field_label_border_width');
            et_pb_responsive_options()->generate_responsive_css($d_border_width, $class, 'border-width', $this->render_slug);
            // Border Style
            $d_border_style = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'field_label_border_style');
            et_pb_responsive_options()->generate_responsive_css($d_border_style, $class, 'border-style', $this->render_slug, '', 'select');
            // Shadow
            $box_shadow_preset = isset($this->props['field_label_shadow']) ? $this->props['field_label_shadow'] : null;
            if (!empty($box_shadow_preset) && 'none' !== $box_shadow_preset) {
                $shadow = pwh_dcfh_module_helpers()::get_shadow_presets_values($box_shadow_preset);
                $horizontal = $shadow['horizontal'];
                $vertical = $shadow['vertical'];
                $blur = $shadow['blur'];
                $spread = $shadow['spread'];
                $position = $shadow['position'];
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $class,
                    'declaration' => "
                    -webkit-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    -moz-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    ",
                ]);
            }
        }

        /**
         * Render Global Label Design Settings
         *
         * @return void
         */
        public function render_global_desc_css()
        {
            $class = '%%order_class%% .et_pb_contact_field_desc';
            $desktop_font = isset($this->props['field_desc_font']) ? $this->props['field_desc_font'] : null;
            $tablet_font = isset($this->props['field_desc_font_tablet']) ? $this->props['field_desc_font_tablet'] : null;
            $phone_font = isset($this->props['field_desc_font_phone']) ? $this->props['field_desc_font_phone'] : null;
            // Text Font
            if (!empty($desktop_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($desktop_font)),
                ]);
            }
            if (!empty($tablet_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($tablet_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if (!empty($phone_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($phone_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
            }
            // Text Color
            $d_text_color = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_color');
            et_pb_responsive_options()->generate_responsive_css($d_text_color, $class, 'color', $this->render_slug, '', 'color');
            // Background Color
            $d_box_background = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_background');
            et_pb_responsive_options()->generate_responsive_css($d_box_background, $class, 'background', $this->render_slug, '', 'color');
            // Text Size
            $d_text_size = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_size');
            et_pb_responsive_options()->generate_responsive_css($d_text_size, $class, 'font-size', $this->render_slug);
            // Text Letter Spacing
            $d_text_letter = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_letter_space');
            et_pb_responsive_options()->generate_responsive_css($d_text_letter, $class, 'letter-spacing', $this->render_slug);
            // Text Line Height
            $d_text_line = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_line_height');
            et_pb_responsive_options()->generate_responsive_css($d_text_line, $class, 'line-height', $this->render_slug);
            // Text Alignment
            $d_text_alignment = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_alignment');
            if (!empty($d_text_alignment)) {
                $d_text_alignment = pwh_dcfh_module_helpers()::get_flex_alignment($d_text_alignment);
            }
            et_pb_responsive_options()->generate_responsive_css($d_text_alignment, $class, 'justify-content', $this->render_slug, '', 'text_alignment');
            // Margin
            $d_margin = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_margin');
            $d_margin = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_margin);
            et_pb_responsive_options()->generate_responsive_css($d_margin, $class, 'margin', $this->render_slug, '', 'custom_padding');
            // Padding
            $d_padding = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_padding');
            $d_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_padding);
            et_pb_responsive_options()->generate_responsive_css($d_padding, $class, 'padding', $this->render_slug, '', 'custom_padding');
            // Border
            $d_border = et_pb_responsive_options()->get_property_values($this->props, 'field_desc_border');
            $d_border = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_border);
            et_pb_responsive_options()->generate_responsive_css($d_border, $class, 'border-radius', $this->render_slug, '', 'border-radius');
            // Border Color
            $d_border_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'field_desc_border_color');
            et_pb_responsive_options()->generate_responsive_css($d_border_color, $class, 'border-color', $this->render_slug, '', 'color');
            // Border Width
            $d_border_width = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'field_desc_border_width');
            et_pb_responsive_options()->generate_responsive_css($d_border_width, $class, 'border-width', $this->render_slug);
            // Border Style
            $d_border_style = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'field_desc_border_style');
            et_pb_responsive_options()->generate_responsive_css($d_border_style, $class, 'border-style', $this->render_slug, '', 'select');
            // Shadow
            $box_shadow_preset = isset($this->props['field_desc_shadow']) ? $this->props['field_desc_shadow'] : null;
            if (!empty($box_shadow_preset) && 'none' !== $box_shadow_preset) {
                $shadow = pwh_dcfh_module_helpers()::get_shadow_presets_values($box_shadow_preset);
                $horizontal = $shadow['horizontal'];
                $vertical = $shadow['vertical'];
                $blur = $shadow['blur'];
                $spread = $shadow['spread'];
                $position = $shadow['position'];
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $class,
                    'declaration' => "
                    -webkit-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    -moz-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    ",
                ]);
            }
        }

        /**
         * Render Input Icon CSS
         *
         * @return false|string
         * @throws DOMException
         */
        public function render_field_icon()
        {
            $field_type = $this->props['field_type'];
            $field_icon = isset($this->props['field_icon']) ? $this->props['field_icon'] : '';
            $field_icon_color = !empty($this->props['field_icon_color']) ? $this->props['field_icon_color'] : '#999';
            if (!empty($field_icon)) {
                $dom = new DOMDocument('1.0', 'UTF-8');
                if (function_exists('mb_convert_encoding')) {
                    $dom->loadHTML(mb_convert_encoding($this->output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                    $dom->encoding = 'utf-8';
                } else {
                    $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$this->output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                }
                // IF Input,Email & Text
                if (in_array($field_type, ['input', 'email', 'text'])) {
                    $qualified_name = in_array($field_type, ['input', 'email']) ? 'input' : 'textarea';
                    $p_tag = $dom->getElementsByTagName('p');
                    $input_tag = $dom->getElementsByTagName($qualified_name);
                    if (isset($p_tag->length) && 0 !== $p_tag->length && isset($input_tag->length) && 0 !== $input_tag->length) {
                        $input_item = $input_tag->item(0);
                        $p_item = $p_tag->item(0);
                        $field_icon_span = $dom->createElement('span');
                        $field_order_class = ET_Builder_Element::get_module_order_class($this->render_slug);
                        $field_index = pwh_dcfh_helpers()::get_field_index($field_order_class);
                        $field_icon_span->setAttribute('id', "et_pb_contact_field_icon_$field_index");
                        $field_icon_span->setAttribute('class', 'et_pb_contact_field_icon');
                        $field_icon_span->setAttribute('data-icon', html_entity_decode(esc_attr(et_pb_process_font_icon($field_icon))));
                        $field_icon_span->setAttribute('data-field-type', $field_type);
                        $p_item->insertBefore($field_icon_span, $input_item);
                        $this->output = $dom->saveHTML();
                        ET_Builder_Element::set_style($this->render_slug, [
                            'selector' => 'p.et_pb_contact_field%%order_class%% input,p.et_pb_contact_field%%order_class%% textarea',
                            'declaration' => 'text-indent:1.3rem;',
                        ]);
                        ET_Builder_Element::set_style($this->render_slug, [
                            'selector' => "p.et_pb_contact_field%%order_class%% .et_pb_contact_field_icon:before",
                            'declaration' => sprintf('color:%s;', $field_icon_color),
                        ]);
                        if ((function_exists('et_pb_get_icon_font_family') && function_exists('et_pb_get_icon_font_weight'))) {
                            ET_Builder_Element::set_style($this->render_slug, [
                                'selector' => "p.et_pb_contact_field%%order_class%% .et_pb_contact_field_icon:before",
                                'declaration' => sprintf("font-family:%s;font-weight:%s;", et_pb_get_icon_font_family($field_icon), et_pb_get_icon_font_weight($field_icon)),
                            ]);
                        } else {
                            ET_Builder_Element::set_style($this->render_slug, [
                                'selector' => "p.et_pb_contact_field%%order_class%% .et_pb_contact_field_icon:before",
                                'declaration' => "font-family:ETModules;",
                            ]);
                        }
                    }
                }
                // IF Checkbox,Radio & Select
                if (in_array($field_type, ['checkbox', 'radio', 'select'])) {
                    $dom_xpath = new DOMXPath($dom);
                    $class = in_array($field_type, ['radio', 'checkbox']) ? 'et_pb_contact_field_options_wrapper' : 'et_pb_contact_form_label';
                    $field_options_wrapper = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), '$class')]");
                    if ((isset($field_options_wrapper->length) && 0 !== $field_options_wrapper->length)) {
                        $field_options_wrapper[0]->setAttribute('data-icon', html_entity_decode(esc_attr(et_pb_process_font_icon($field_icon))));
                        $this->output = $dom->saveHTML();
                        ET_Builder_Element::set_style($this->render_slug, [
                            'selector' => "p.et_pb_contact_field%%order_class%% .et_pb_contact_form_label",
                            'declaration' => 'padding-left:18px;',
                        ]);
                        ET_Builder_Element::set_style($this->render_slug, [
                            'selector' => "p.et_pb_contact_field%%order_class%% .et_pb_contact_field_options_title",
                            'declaration' => 'padding-left:24px;',
                        ]);
                        ET_Builder_Element::set_style($this->render_slug, [
                            'selector' => "p.et_pb_contact_field%%order_class%% .$class:before",
                            'declaration' => sprintf('content: attr(data-icon);color:%s;', $field_icon_color),
                        ]);
                        if ((function_exists('et_pb_get_icon_font_family') && function_exists('et_pb_get_icon_font_weight'))) {
                            ET_Builder_Element::set_style($this->render_slug, [
                                'selector' => "p.et_pb_contact_field%%order_class%% .$class:before",
                                'declaration' => sprintf("font-family:%s;font-weight:%s;", et_pb_get_icon_font_family($field_icon), et_pb_get_icon_font_weight($field_icon)),
                            ]);
                        } else {
                            ET_Builder_Element::set_style($this->render_slug, [
                                'selector' => "p.et_pb_contact_field%%order_class%% .$class:before",
                                'declaration' => "font-family:ETModules;",
                            ]);
                        }
                    }
                }
            }

            return $this->output;
        }

        /**
         * Render Input Focus
         *
         * @return void
         */
        public function render_field_focus_css()
        {
            // Border Radius
            $d_border = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props(et_pb_responsive_options()->get_property_values($this->props, 'input_focus_border'));
            // Border Color
            $d_border_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'input_focus_border_color');
            // Border Width
            $d_border_width = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'input_focus_border_width');
            // Border Style
            $d_border_style = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'input_focus_border_style');
            $input_focus_selector = '.input:focus,.input[type="text"]:active + label i,.input[type="checkbox"]:active + label i,.input[type="radio"]:active + label i';
            et_pb_responsive_options()->generate_responsive_css($d_border, $input_focus_selector, 'border-radius', $this->render_slug, '', 'border-radius');
            et_pb_responsive_options()->generate_responsive_css($d_border_color, $input_focus_selector, 'border-color', $this->render_slug, ' !important;', 'color');
            et_pb_responsive_options()->generate_responsive_css($d_border_width, $input_focus_selector, 'border-width', $this->render_slug);
            et_pb_responsive_options()->generate_responsive_css($d_border_style, $input_focus_selector, 'border-style', $this->render_slug, '', 'select');
        }

        /**
         * Render Textarea Min Height CSS
         *
         * @return void
         */
        public function render_textarea_min_height_css()
        {
            $devices_options = et_pb_responsive_options()->get_property_values($this->props, 'textarea_min_height');
            if ('150px' === $devices_options['desktop']) {
                $devices_options['desktop'] = '';
            }
            if ('150px' === $devices_options['tablet']) {
                $devices_options['tablet'] = '';
            }
            if ('150px' === $devices_options['phone']) {
                $devices_options['phone'] = '';
            }
            if (!empty(array_filter($devices_options))) {
                et_pb_responsive_options()->generate_responsive_css($devices_options, '%%order_class%% textarea.et_pb_contact_message', 'min-height', $this->render_slug);
            }
        }

        /**
         * Render Textarea Min Height CSS
         *
         * @return void
         */
        public function render_checkboxes_layout_css()
        {
            $checkbox_layout = isset($this->props['checkbox_layout']) ? $this->props['checkbox_layout'] : 'column';
            $devices_options = et_pb_responsive_options()->get_property_values($this->props, 'checkbox_columns');
            $devices_options['desktop'] = isset($devices_options['desktop']) ? $devices_options['desktop'] : '';
            $devices_options['tablet'] = (isset($devices_options['tablet']) && !empty($devices_options['tablet'])) ? $devices_options['tablet'] : $devices_options['desktop'];
            $devices_options['phone'] = (isset($devices_options['phone']) && !empty($devices_options['phone'])) ? $devices_options['phone'] : $devices_options['desktop'];
            // Columns Layout
            if ('column' === $checkbox_layout) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_field_options_list',
                    'declaration' => 'display: flex;flex-flow: row wrap;',
                ]);
                $devices_options = pwh_dcfh_module_helpers()::get_flex_column($devices_options);
                et_pb_responsive_options()->generate_responsive_css($devices_options, '%%order_class%% .et_pb_contact_field_checkbox', 'flex-basis', $this->render_slug, '', 'select');
            }
            // Inline Layout
            if ('inline' === $checkbox_layout) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_field_options_list span',
                    'declaration' => 'display: inline-block;margin-right:30px;',
                ]);
            }
        }

        /**
         * Render Textarea Min Height CSS
         *
         * @return void
         */
        public function render_radio_layout_css()
        {
            $radio_layout = isset($this->props['radio_layout']) ? $this->props['radio_layout'] : 'column';
            $devices_options = et_pb_responsive_options()->get_property_values($this->props, 'radio_columns');
            $devices_options['desktop'] = isset($devices_options['desktop']) ? $devices_options['desktop'] : '';
            $devices_options['tablet'] = (isset($devices_options['tablet']) && !empty($devices_options['tablet'])) ? $devices_options['tablet'] : $devices_options['desktop'];
            $devices_options['phone'] = (isset($devices_options['phone']) && !empty($devices_options['phone'])) ? $devices_options['phone'] : $devices_options['desktop'];
            // Columns Layout
            if ('column' === $radio_layout) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_field_options_list',
                    'declaration' => 'display: flex;flex-flow: row wrap;',
                ]);
                $devices_options = pwh_dcfh_module_helpers()::get_flex_column($devices_options);
                et_pb_responsive_options()->generate_responsive_css($devices_options, '%%order_class%% .et_pb_contact_field_radio', 'flex-basis', $this->render_slug, '', 'select');
            }
            // Inline Layout
            if ('inline' === $radio_layout) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_field_options_list span',
                    'declaration' => 'display:inline-block;margin-right:30px;',
                ]);
            }
        }

    }
}