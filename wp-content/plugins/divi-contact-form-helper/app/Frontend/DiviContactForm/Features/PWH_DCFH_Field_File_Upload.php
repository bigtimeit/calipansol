<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use DOMDocument;
use DOMException;
use ET_Builder_Element;
use PWH_DCFH\App\Base\PWH_DCFH_Strings;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Field_File_Upload')) {
    class PWH_DCFH_Field_File_Upload
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
            $p_tag = $dom->getElementsByTagName('p');
            if ((isset($input->length) && 0 !== $input->length) && (isset($p_tag->length) && 0 !== $p_tag->length)) {
                $file_size = isset($this->props['file_size']) ? $this->props['file_size'] : '1024';
                $file_size = preg_replace('/\D/', '', $file_size);
                if (is_numeric($file_size)) {
                    $file_size = $file_size * KB_IN_BYTES;      // Convet User Size To Bytes
                    $wp_max_upload_size = wp_max_upload_size(); // WordPress Max File Size In Bytes
                    // Overide User File Size With WP File Size If Exceeds
                    if ($file_size > $wp_max_upload_size) {
                        $file_size = $wp_max_upload_size;
                    }
                    $input_item = $input->item(0);
                    $p_item = $p_tag->item(0);
                    $p_class = $p_item->getAttribute('class');
                    $field_order_class = ET_Builder_Element::get_module_order_class($this->render_slug);
                    $field_id = pwh_dcfh_helpers()::get_field_index($field_order_class);
                    // Add Custom Class To P Tag
                    $p_item->setAttribute('class', "$p_class et_pb_files_container");
                    $file_mimes = isset($this->props['file_mime']) ? $this->props['file_mime'] : '';
                    if (!empty($file_mimes)) {
                        $processed_mimes = pwh_dcfh_module_helpers()::process_multiple_mimes_checkboxes_value($file_mimes);
                        $files_extentions = $processed_mimes['values'];
                        $files_mimes = $processed_mimes['keys'];
                        $file_size_formatted = size_format($file_size);
                        $files_limit = isset($this->props['files_limit']) ? $this->props['files_limit'] : 2;
                        $frontend_strings = (new PWH_DCFH_Strings())->instance()->strings('frontend_strings');
                        $file_desc = sprintf('%1$s %2$s. %3$s %4$s', $frontend_strings['accepted_file_text'], $files_extentions, $frontend_strings['max_filesize_text'], $file_size_formatted);
                        // Adding Input File
                        $input_name = $input_item->getAttribute('name');
                        $input_class = $input_item->getAttribute('class').' '.pwh_dcfh_hc()::UPLOAD_FILE_CLASS;
                        $input_item->setAttribute('class', 'et_pb_contact_hidden_files');
                        $input_item->setAttribute('data-field-id', $field_id);
                        $file_input = $dom->createElement('input');
                        $file_input->setAttribute('type', 'file');
                        $file_input->setAttribute('class', $input_class);
                        $file_input->setAttribute('name', $input_name);
                        if ($files_limit > 1) {
                            $file_input->setAttribute('data-limit', $files_limit);
                            $file_input->setAttribute('multiple', 'multiple');
                        }
                        $file_input->setAttribute('data-field-id', $field_id);
                        $file_input->setAttribute('data-size', $file_size);
                        $file_input->setAttribute('data-size-formatted', $file_size_formatted);
                        $file_upload_button = $dom->createElement('span', $frontend_strings['file_upload_btn_text']);
                        $file_upload_button->setAttribute('class', 'et_pb_contact_submit et_pb_button et_pb_file_upload_button');
                        $file_chosen_span = $dom->createElement('span', $frontend_strings['chosen_file_text']);
                        $file_chosen_span->setAttribute('class', 'et_pb_file_chosen_desc');
                        $p_item->appendChild($file_input);
                        $p_item->appendChild($file_upload_button);
                        $p_item->appendChild($file_chosen_span);
                        // Hidden Field
                        $file_hidden_input = $dom->createElement('input');
                        $file_hidden_input->setAttribute('type', 'hidden');
                        $file_hidden_input->setAttribute('name', $input_name.'_is_file');
                        $file_hidden_input->setAttribute('value', 'yes');
                        $p_item->appendChild($file_hidden_input);
                        // File Token
                        $file_token_input = $dom->createElement('input');
                        $file_token_input->setAttribute('type', 'hidden');
                        $file_token_input->setAttribute('name', $input_name.'_file_token');
                        $file_token_input->setAttribute('value', pwh_dcfh_helpers()::encrypt_decrypt(wp_json_encode([
                            'size' => $file_size,
                            'extentions' => $files_extentions,
                            'mimetypes' => $files_mimes,
                            'limit' => $files_limit,
                        ])));
                        $p_item->appendChild($file_token_input);
                        // Spinner
                        $waiting_spinner = $dom->createElement('span');
                        $waiting_spinner->setAttribute('id', "et_pb_wait_spinner_$field_id");
                        $waiting_spinner->setAttribute('class', 'et_pb_wait_spinner');
                        $p_item->appendChild($waiting_spinner);
                        // File Description
                        $file_description = $dom->createElement('span');
                        $file_description->setAttribute('id', "et_pb_accepted_files_desc_$field_id");
                        $file_description->setAttribute('class', 'et_pb_accepted_files_desc');
                        $file_description->setAttribute('data-description', $file_desc);
                        $p_item->appendChild($file_description);
                        $description = $dom->createTextNode($file_desc);
                        $file_description->appendChild($description);
                        // Files List
                        $files_list = $dom->createElement('span');
                        $files_list->setAttribute('id', "et_pb_files_list_$field_id");
                        $files_list->setAttribute('class', 'et_pb_files_list');
                        $p_item->appendChild($files_list);
                        $this->output = $dom->saveHTML();
                    }
                }
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
            // Drop Area
            $files_container_class = '%%order_class%% .et_pb_files_container';
            // Background
            $d_files_container_background = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'files_container_background');
            et_pb_responsive_options()->generate_responsive_css($d_files_container_background, $files_container_class, 'background', $this->render_slug, "!important;", 'color');
            // Margin
            $d_files_container_margin = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props(et_pb_responsive_options()->get_property_values($this->props, 'files_container_margin'));
            et_pb_responsive_options()->generate_responsive_css($d_files_container_margin, $files_container_class, 'margin', $this->render_slug, '', 'custom_padding');
            // Padding
            $d_files_container_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props(et_pb_responsive_options()->get_property_values($this->props, 'files_container_padding'));
            et_pb_responsive_options()->generate_responsive_css($d_files_container_padding, $files_container_class, 'padding', $this->render_slug, '!important;', 'custom_padding');
            // Border
            $d_files_container_border = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props(et_pb_responsive_options()->get_property_values($this->props, 'files_container_border'));
            et_pb_responsive_options()->generate_responsive_css($d_files_container_border, $files_container_class, 'border-radius', $this->render_slug, '', 'border-radius');
            // Border Color
            $d_files_container_border_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'files_container_border_color');
            et_pb_responsive_options()->generate_responsive_css($d_files_container_border_color, $files_container_class, 'border-color', $this->render_slug, '', 'color');
            // Border Width
            $d_files_container_border_width = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'files_container_border_width');
            et_pb_responsive_options()->generate_responsive_css($d_files_container_border_width, $files_container_class, 'border-width', $this->render_slug);
            // Border Style
            $d_files_container_border_style = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'files_container_border_style');
            et_pb_responsive_options()->generate_responsive_css($d_files_container_border_style, $files_container_class, 'border-style', $this->render_slug, '', 'select');
            // Shadow
            $files_container_shadow = isset($this->props['files_container_shadow']) ? $this->props['files_container_shadow'] : null;
            if (!empty($files_container_shadow) && 'none' !== $files_container_shadow) {
                $shadow = pwh_dcfh_module_helpers()::get_shadow_presets_values($files_container_shadow);
                $horizontal = $shadow['horizontal'];
                $vertical = $shadow['vertical'];
                $blur = $shadow['blur'];
                $spread = $shadow['spread'];
                $position = $shadow['position'];
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $files_container_class,
                    'declaration' => "
                    -webkit-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    -moz-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                     box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    ",
                ]);
            }
            // List Color
            $d_files_container_list_color = et_pb_responsive_options()->get_property_values($this->props, 'files_container_list_color');

            et_pb_responsive_options()->generate_responsive_css($d_files_container_list_color, "$files_container_class .et_pb_files_list .et_pb_file_name", 'color', $this->render_slug, '!important', 'color');
            et_pb_responsive_options()->generate_responsive_css($d_files_container_list_color, "$files_container_class .et_pb_files_list .et_pb_file_size", 'color', $this->render_slug, '!important', 'color');
            // Accepted File Text
            $accepted_files_class = '%%order_class%% .et_pb_files_container .et_pb_accepted_files_desc';
            // Color
            $d_accepted_file_color = et_pb_responsive_options()->get_property_values($this->props, 'accepted_file_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_accepted_file_color, "$accepted_files_class", 'color', $this->render_slug, '', 'color');
            // Size
            $d_accepted_file_size = et_pb_responsive_options()->get_property_values($this->props, 'accepted_file_text_size');
            et_pb_responsive_options()->generate_responsive_css($d_accepted_file_size, "$accepted_files_class", 'font-size', $this->render_slug);
            // Font
            $accepted_file_text_font = isset($this->props['accepted_file_text_font']) ? $this->props['accepted_file_text_font'] : null;
            $is_accepted_file_font_responsive = et_pb_responsive_options()->is_responsive_enabled($this->props, 'accepted_file_text_font');
            if (!empty($accepted_file_text_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$accepted_files_class",
                    'declaration' => sprintf('%s;', et_builder_set_element_font($accepted_file_text_font)),
                ]);
            }
            if ($is_accepted_file_font_responsive) {
                $accepted_file_text_font_tablet = isset($this->props['accepted_file_text_font_tablet']) ? $this->props['accepted_file_text_font_tablet'] : null;
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$accepted_files_class",
                    'declaration' => sprintf('%s;', et_builder_set_element_font($accepted_file_text_font_tablet)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if ($is_accepted_file_font_responsive) {
                $accepted_file_text_font_phone = isset($this->props['accepted_file_text_font_phone']) ? $this->props['accepted_file_text_font_phone'] : null;
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$accepted_files_class",
                    'declaration' => sprintf('%s;', et_builder_set_element_font($accepted_file_text_font_phone)),
                    'media_query' => ET_Builder_Element::get_media_query('768_980'),
                ]);
            }
            // Letter Spacing
            $d_accepted_file_letter = et_pb_responsive_options()->get_property_values($this->props, 'accepted_file_text_letterspace');
            et_pb_responsive_options()->generate_responsive_css($d_accepted_file_letter, "$accepted_files_class", 'letter-spacing', $this->render_slug);
            // Line Height
            $d_accepted_file_line_height = et_pb_responsive_options()->get_property_values($this->props, 'accepted_file_text_lineheight');
            et_pb_responsive_options()->generate_responsive_css($d_accepted_file_line_height, "$accepted_files_class", 'line-height', $this->render_slug);
            // Alignment
            $d_accepted_file_alignment = et_pb_responsive_options()->get_property_values($this->props, 'accepted_file_text_alignment');
            et_pb_responsive_options()->generate_responsive_css($d_accepted_file_alignment, "$accepted_files_class", 'text-align', $this->render_slug, '', 'text_alignment');
            // Margin
            $d_accepted_file_margin = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props(et_pb_responsive_options()->get_property_values($this->props, 'accepted_file_text_margin'));
            et_pb_responsive_options()->generate_responsive_css($d_accepted_file_margin, "$accepted_files_class", 'margin', $this->render_slug, '', 'custom_padding');
            // Padding
            $d_accepted_file_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props(et_pb_responsive_options()->get_property_values($this->props, 'accepted_file_text_padding'));
            et_pb_responsive_options()->generate_responsive_css($d_accepted_file_padding, "$accepted_files_class", 'padding', $this->render_slug, '', 'custom_padding');
            // Chosen File
            $file_chosen_class = '%%order_class%% .et_pb_files_container .et_pb_file_chosen_desc';
            // Color
            $d_chosen_file_color = et_pb_responsive_options()->get_property_values($this->props, 'chosen_file_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_chosen_file_color, $file_chosen_class, 'color', $this->render_slug, '!important;', 'color');
            // Size
            $d_chosen_file_size = et_pb_responsive_options()->get_property_values($this->props, 'chosen_file_text_size');
            et_pb_responsive_options()->generate_responsive_css($d_chosen_file_size, $file_chosen_class, 'font-size', $this->render_slug);
            // Font
            $chosen_file_text_font = isset($this->props['chosen_file_text_font']) ? $this->props['chosen_file_text_font'] : null;
            $is_chosen_file_font_responsive = et_pb_responsive_options()->is_responsive_enabled($this->props, 'chosen_file_text_font');
            if (!empty($chosen_file_text_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $file_chosen_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($chosen_file_text_font)),
                ]);
            }
            if ($is_chosen_file_font_responsive) {
                $chosen_file_text_font_tablet = isset($this->props['chosen_file_text_font_tablet']) ? $this->props['chosen_file_text_font_tablet'] : null;
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $file_chosen_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($chosen_file_text_font_tablet)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if ($is_chosen_file_font_responsive) {
                $chosen_file_text_font_phone = isset($this->props['chosen_file_text_font_phone']) ? $this->props['chosen_file_text_font_phone'] : null;
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $file_chosen_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($chosen_file_text_font_phone)),
                    'media_query' => ET_Builder_Element::get_media_query('768_980'),
                ]);
            }
            // Letter Spacing
            $d_chosen_file_letter = et_pb_responsive_options()->get_property_values($this->props, 'chosen_file_text_letterspace');
            et_pb_responsive_options()->generate_responsive_css($d_chosen_file_letter, $file_chosen_class, 'letter-spacing', $this->render_slug);
            // File Button
            $file_button_class = '%%order_class%% .et_pb_contact_submit.et_pb_button.et_pb_file_upload_button';
            $file_button_font = isset($this->props['file_button_font']) ? $this->props['file_button_font'] : null;
            $is_file_button_font_responsive = et_pb_responsive_options()->is_responsive_enabled($this->props, 'file_button_font');
            // Text Font
            if (!empty($file_button_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $file_button_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($file_button_font)),
                ]);
            }
            if ($is_file_button_font_responsive) {
                $file_button_font_tablet = isset($this->props['file_button_font_tablet']) ? $this->props['file_button_font_tablet'] : null;
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $file_button_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($file_button_font_tablet)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if ($is_file_button_font_responsive) {
                $file_button_font_phone = isset($this->props['file_button_font_phone']) ? $this->props['file_button_font_phone'] : null;
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $file_button_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($file_button_font_phone)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
            }
            // Color
            $d_file_button_color = et_pb_responsive_options()->get_property_values($this->props, 'file_button_color');
            et_pb_responsive_options()->generate_responsive_css($d_file_button_color, $file_button_class, 'color', $this->render_slug, '', 'color');
            // Background Color
            $d_file_button_background = et_pb_responsive_options()->get_property_values($this->props, 'file_button_background');
            et_pb_responsive_options()->generate_responsive_css($d_file_button_background, $file_button_class, 'background', $this->render_slug, '', 'color');
            // Size
            $d_file_button_size = et_pb_responsive_options()->get_property_values($this->props, 'file_button_size');
            et_pb_responsive_options()->generate_responsive_css($d_file_button_size, $file_button_class, 'font-size', $this->render_slug);
            // Letter Spacing
            $d_file_button_letter = et_pb_responsive_options()->get_property_values($this->props, 'file_button_letter_space');
            et_pb_responsive_options()->generate_responsive_css($d_file_button_letter, $file_button_class, 'letter-spacing', $this->render_slug);
            // Line Height
            $d_file_button_line_height = et_pb_responsive_options()->get_property_values($this->props, 'file_button_line_height');
            et_pb_responsive_options()->generate_responsive_css($d_file_button_line_height, $file_button_class, 'line-height', $this->render_slug);
            // Margin
            $d_file_button_margin = et_pb_responsive_options()->get_property_values($this->props, 'file_button_margin');
            $d_file_button_margin = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_file_button_margin);
            et_pb_responsive_options()->generate_responsive_css($d_file_button_margin, $file_button_class, 'margin', $this->render_slug, '', 'custom_padding');
            // Padding
            $d_file_button_padding = et_pb_responsive_options()->get_property_values($this->props, 'file_button_padding');
            $d_file_button_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_file_button_padding);
            et_pb_responsive_options()->generate_responsive_css($d_file_button_padding, $file_button_class, 'padding', $this->render_slug, '', 'custom_padding');
            //  Border
            $d_file_button_border = et_pb_responsive_options()->get_property_values($this->props, 'file_button_border');
            $d_file_button_border = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_file_button_border);
            et_pb_responsive_options()->generate_responsive_css($d_file_button_border, $file_button_class, 'border-radius', $this->render_slug, '', 'border-radius');
            // Border Color
            $d_file_button_border_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'file_button_border_color');
            et_pb_responsive_options()->generate_responsive_css($d_file_button_border_color, $file_button_class, 'border-color', $this->render_slug, '', 'color');
            // Border Width
            $d_file_button_border_width = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'file_button_border_width');
            et_pb_responsive_options()->generate_responsive_css($d_file_button_border_width, $file_button_class, 'border-width', $this->render_slug);
            // Border Style
            $d_file_button_border_style = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'file_button_border_style');
            et_pb_responsive_options()->generate_responsive_css($d_file_button_border_style, $file_button_class, 'border-style', $this->render_slug, '', 'select');
            // Icon
            $file_button_icon = isset($this->props['file_button_icon']) ? $this->props['file_button_icon'] : null;
            et_pb_responsive_options()->generate_responsive_css($d_file_button_color, "%order_class%% .et_pb_contact_submit.et_pb_button.et_pb_file_upload_button:after, .et_pb_contact_submit.et_pb_button.et_pb_file_upload_button:before", 'color', $this->render_slug, '', 'color');
            if (!empty($file_button_icon)) {
                $file_button_icon_processed = html_entity_decode(esc_attr(et_pb_process_font_icon($file_button_icon)));
                if ((function_exists('et_pb_get_icon_font_family') && function_exists('et_pb_get_icon_font_weight'))) {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => "$file_button_class:after, $file_button_class:before",
                        'declaration' => sprintf("font-family:%s !important;font-weight:%s !important;", et_pb_get_icon_font_family($file_button_icon), et_pb_get_icon_font_weight($file_button_icon)),
                    ]);
                }
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$file_button_class:after, $file_button_class:before",
                    'declaration' => "content:'$file_button_icon_processed' !important;",
                ]);
            }
            // Show Icon
            $file_button_icon_on_hover = isset($this->props['file_button_icon_on_hover']) ? $this->props['file_button_icon_on_hover'] : 'on';
            if ('off' === $file_button_icon_on_hover) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$file_button_class:after, $file_button_class:before",
                    'declaration' => "opacity: 1; margin-left: 0.2em; left: auto;",
                ]);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => "$file_button_class, $file_button_class",
                    'declaration' => "padding-right: 2em;padding-left: 0.7em;",
                ]);
            }
            // Shadow
            $file_button_shadow = isset($this->props['file_button_shadow']) ? $this->props['file_button_shadow'] : null;
            if (!empty($file_button_shadow) && 'none' !== $file_button_shadow) {
                $shadow = pwh_dcfh_module_helpers()::get_shadow_presets_values($file_button_shadow);
                $horizontal = $shadow['horizontal'];
                $vertical = $shadow['vertical'];
                $blur = $shadow['blur'];
                $spread = $shadow['spread'];
                $position = $shadow['position'];
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $file_button_class,
                    'declaration' => "
                    -webkit-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    -moz-box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    box-shadow: $position $horizontal $vertical $blur $spread rgb(0 0 0 / 30%);
                    ",
                ]);
            }
        }

    }
}