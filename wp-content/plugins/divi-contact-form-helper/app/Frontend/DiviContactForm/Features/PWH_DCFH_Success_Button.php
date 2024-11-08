<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use DOMDocument;
use DOMException;
use ET_Builder_Element;
use DOMXPath;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Success_Button')) {
    class PWH_DCFH_Success_Button
    {

        public $render_slug;

        public $output;

        public $props = [];

        public $contact_form_order_class;

        /**
         * Class Construnctor
         */
        public function __construct($render_slug, $output, $props, $contact_form_order_class)
        {
            $this->render_slug = $render_slug;
            $this->output = $output;
            $this->props = $props;
            $this->contact_form_order_class = $contact_form_order_class;
        }

        /**
         * Render Ouput
         *
         * @throws DOMException
         */
        public function output()
        {
            $success_button_text = $this->props['success_button_text'];
            $success_button_type = isset($this->props['success_button_type']) ? $this->props['success_button_type'] : 'text';
            $success_button_url = isset($this->props['success_button_url']) ? $this->props['success_button_url'] : null;
            $success_button_page = isset($this->props['success_button_page']) ? $this->props['success_button_page'] : null;
            $success_button_target = isset($this->props['success_button_target']) ? $this->props['success_button_target'] : '_self';
            $success_button_href = '';
            if ('url' === $success_button_type && !empty($success_button_url)) {
                $success_button_href = $success_button_url;
            } elseif ('page' === $success_button_type && !empty($success_button_page)) {
                $success_button_href = get_the_permalink($success_button_page);
            }
            $dom = new DOMDocument('1.0', 'UTF-8');
            if (function_exists('mb_convert_encoding')) {
                $dom->loadHTML(mb_convert_encoding($this->output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $dom->encoding = 'utf-8';
            } else {
                $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$this->output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            }
            $dom_xpath = new DOMXPath($dom);
            $form = $dom_xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), '$this->contact_form_order_class')]");
            if ((isset($form->length) && 0 !== $form->length)) {
                $form_item = $form->item(0);
                // $form = $dom->getElementById($this->contact_form_order_class);
                $div_wrapper = $dom->createElement('div');
                $div_wrapper->setAttribute('class', 'et_pb_success_btn_container');
                $anchor_tag = $dom->createElement('a', $success_button_text);
                $anchor_tag->setAttribute('class', 'et_pb_success_button');
                if ('text' !== $success_button_type && '' !== $success_button_href) {
                    $anchor_tag->setAttribute('href', $success_button_href);
                    $anchor_tag->setAttribute('target', $success_button_target);
                }
                $div_wrapper->appendChild($anchor_tag);
                $form_item->appendChild($div_wrapper);
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
            $button_parent_class = '%%order_class%% .et_pb_success_btn_container';
            $button_class = '%%order_class%% .et_pb_success_button';
            $success_button_font_last_edited = isset($this->props['success_button_font_last_edited']) ? $this->props['success_button_font_last_edited'] : null;
            $is_success_button_font_responsive = et_pb_get_responsive_status($success_button_font_last_edited);
            $desktop_font = isset($this->props['success_button_font']) ? $this->props['success_button_font'] : null;
            $tablet_font = isset($this->props['success_button_font_tablet']) ? $this->props['success_button_font_tablet'] : null;
            $phone_font = isset($this->props['success_button_font_phone']) ? $this->props['success_button_font_phone'] : null;
            // Text Font
            if (!empty($desktop_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $button_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($desktop_font)),
                ]);
            }
            if (!empty($tablet_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $button_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($tablet_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if (!empty($phone_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $button_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($phone_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
            }
            // Color
            $d_color = et_pb_responsive_options()->get_property_values($this->props, 'success_button_color');
            et_pb_responsive_options()->generate_responsive_css($d_color, $button_class, 'color', $this->render_slug, '', 'color');
            // Background Color
            $d_background = et_pb_responsive_options()->get_property_values($this->props, 'success_button_background');
            et_pb_responsive_options()->generate_responsive_css($d_background, $button_class, 'background', $this->render_slug, '', 'color');
            // Size
            $d_size = et_pb_responsive_options()->get_property_values($this->props, 'success_button_size');
            et_pb_responsive_options()->generate_responsive_css($d_size, $button_class, 'font-size', $this->render_slug);
            // Letter Spacing
            $d_letter = et_pb_responsive_options()->get_property_values($this->props, 'success_button_letter_space');
            et_pb_responsive_options()->generate_responsive_css($d_letter, $button_class, 'letter-spacing', $this->render_slug);
            // Line Height
            $line_height_important = et_builder_has_limitation('force_use_global_important') ? ' !important' : '';
            $d_line_height = et_pb_responsive_options()->get_property_values($this->props, 'success_button_line_height');
            et_pb_responsive_options()->generate_responsive_css($d_line_height, $button_class, 'line-height', $this->render_slug, $line_height_important);
            // Alignment
            $d_alignment = et_pb_responsive_options()->get_property_values($this->props, 'success_button_alignment');
            if (!empty($d_text_alignment)) {
                $d_text_alignment = pwh_dcfh_module_helpers()::get_flex_alignment($d_text_alignment);
            }
            et_pb_responsive_options()->generate_responsive_css($d_alignment, $button_parent_class, 'justify-content', $this->render_slug, '', 'text_alignment');
            // Margin
            $d_margin = et_pb_responsive_options()->get_property_values($this->props, 'success_button_margin');
            $d_margin = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_margin);
            et_pb_responsive_options()->generate_responsive_css($d_margin, $button_class, 'margin', $this->render_slug, '', 'custom_padding');
            // Padding
            $d_padding = et_pb_responsive_options()->get_property_values($this->props, 'success_button_padding');
            $d_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_padding);
            et_pb_responsive_options()->generate_responsive_css($d_padding, $button_class, 'padding', $this->render_slug, '', 'custom_padding');
            //  Border
            $d_border = et_pb_responsive_options()->get_property_values($this->props, 'success_button_border');
            $d_border = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_border);
            et_pb_responsive_options()->generate_responsive_css($d_border, $button_class, 'border-radius', $this->render_slug, '', 'border-radius');
            // Border Color
            $d_border_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'success_button_border_color');
            et_pb_responsive_options()->generate_responsive_css($d_border_color, $button_class, 'border-color', $this->render_slug, '', 'color');
            // Border Width
            $d_border_width = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'success_button_border_width');
            et_pb_responsive_options()->generate_responsive_css($d_border_width, $button_class, 'border-width', $this->render_slug);
            // Border Style
            $d_border_style = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'success_button_border_style');
            et_pb_responsive_options()->generate_responsive_css($d_border_style, $button_class, 'border-style', $this->render_slug, '', 'select');
            // Shadow
            $shadow_preset = isset($this->props['success_button_shadow']) ? $this->props['success_button_shadow'] : null;
            if (!empty($shadow_preset) && 'none' !== $shadow_preset) {
                $shadow = pwh_dcfh_module_helpers()::get_shadow_presets_values($shadow_preset);
                $horizontal = $shadow['horizontal'];
                $vertical = $shadow['vertical'];
                $blur = $shadow['blur'];
                $spread = $shadow['spread'];
                $position = $shadow['position'];
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $button_class,
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