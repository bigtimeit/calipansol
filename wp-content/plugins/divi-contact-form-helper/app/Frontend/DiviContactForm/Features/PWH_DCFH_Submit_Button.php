<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use DOMDocument;
use DOMException;
use ET_Builder_Element;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Submit_Button')) {
    class PWH_DCFH_Submit_Button
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
        public function rebuild_submit_button_output()
        {
            $dom = new DOMDocument('1.0', 'UTF-8');
            if (function_exists('mb_convert_encoding')) {
                $dom->loadHTML(mb_convert_encoding($this->output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $dom->encoding = 'utf-8';
            } else {
                $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$this->output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            }
            $form = $dom->getElementsByTagName('form')->item(0);
            foreach ($dom->getElementsByTagName('button') as $button) {
                if ('submit' === $button->getAttribute('type')) {
                    $delete_button = $button;
                    $div_wrapper = $dom->createElement('div');
                    $div_wrapper->setAttribute('class', 'et_pb_submit_btn_container');
                    $div_wrapper->appendChild($delete_button);
                    $form->appendChild($div_wrapper);
                    $this->output = $dom->saveHTML();
                    break;
                }
            }

            return $this->output;
        }

        /**
         * Add Wait Text While File Upload
         *
         * @return false|string
         */
        public function add_wait_text_attribute()
        {
            $dom = new DOMDocument('1.0', 'UTF-8');
            if (function_exists('mb_convert_encoding')) {
                $dom->loadHTML(mb_convert_encoding($this->output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $dom->encoding = 'utf-8';
            } else {
                $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$this->output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            }
            $button = $dom->getElementsByTagName('button');
            if ((isset($button->length) && 0 !== $button->length)) {
                $button_wait_text = $this->props['submit_button_wait_text'];
                $button_item = $button->item(0);
                $button_item->setAttribute('data-wait-text', $button_wait_text);
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
            // Alignment
            $d_alignment = et_pb_responsive_options()->get_property_values($this->props, 'submit_button_alignment');
            if ('off' !== $d_alignment['desktop']) {
                // Set Desktop Button Right Margin
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_submit',
                    'declaration' => 'margin: 0 0 0 32px;',
                    'media_query' => ET_Builder_Element::get_media_query('min_width_981'),
                ]);
                $d_alignment = pwh_dcfh_module_helpers()::get_flex_alignment($d_alignment);
                $is_used_captcha = isset($this->props['captcha']) ? $this->props['captcha'] : 'off';
                // If captcha enabled
                if ('on' === $is_used_captcha) {
                    et_pb_responsive_options()->generate_responsive_css($d_alignment, '%%order_class%% .et_pb_submit_btn_container', 'justify-content', $this->render_slug, '', 'align');
                } else {
                    ET_Builder_Element::set_style($this->render_slug, [
                        'selector' => '%%order_class%% .et_contact_bottom_container',
                        'declaration' => 'float:none;',
                    ]);
                    et_pb_responsive_options()->generate_responsive_css($d_alignment, '%%order_class%% .et_contact_bottom_container', 'justify-content', $this->render_slug, '', 'align');
                }
            }
            // Width
            $button_fullwidth = isset($this->props['submit_button_full_width']) ? $this->props['submit_button_full_width'] : 'off';
            $is_fullwidth_responsive = et_pb_responsive_options()->is_responsive_enabled($this->props, 'submit_button_full_width');
            $button_fullwidth_tablet = isset($this->props['submit_button_full_width_tablet']) ? $this->props['submit_button_full_width_tablet'] : $button_fullwidth;
            $button_fullwidth_phone = isset($this->props['submit_button_full_width_phone']) ? $this->props['submit_button_full_width_phone'] : $button_fullwidth;
            if ('on' === $button_fullwidth) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_submit',
                    'declaration' => 'margin: 0 0 0 0;',
                    'media_query' => ET_Builder_Element::get_media_query('min_width_981'),
                ]);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_contact_bottom_container',
                    'declaration' => 'width: 100%;flex-direction: column; gap: 1em;padding-left: 3%;',
                    'media_query' => ET_Builder_Element::get_media_query('min_width_981'),
                ]);
            }
            if ($is_fullwidth_responsive && 'on' === $button_fullwidth_tablet) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_submit',
                    'declaration' => 'margin: 0 0 0 0;',
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_contact_bottom_container',
                    'declaration' => 'width: 100%;flex-direction: column; gap: 1em;padding-left: 3%;',
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if ($is_fullwidth_responsive && 'on' === $button_fullwidth_phone) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_pb_contact_submit',
                    'declaration' => 'margin: 0 0 0 0;',
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_contact_bottom_container',
                    'declaration' => 'width: 100%;flex-direction: column; gap: 1em;padding-left: 3%;',
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
            }
        }

        /**
         * Render Css
         *
         * @return void
         */
        public function css_old()
        {
            $is_used_captcha = isset($this->props['captcha']) ? $this->props['captcha'] : 'off';
            // Alignment
            $button_alignment = isset($this->props['submit_button_alignment']) ? $this->props['submit_button_alignment'] : 'off';
            $is_aligment_responsive = et_pb_responsive_options()->is_responsive_enabled($this->props, 'submit_button_alignment');
            $d_alignment = et_pb_responsive_options()->get_property_values($this->props, 'submit_button_alignment', 'off', true);
            $button_alignment_tablet = isset($this->props['submit_button_alignment_tablet']) ? $this->props['submit_button_alignment_tablet'] : $button_alignment;
            $button_alignment_phone = isset($this->props['submit_button_alignment_phone']) ? $this->props['submit_button_alignment_phone'] : $button_alignment;
            // Full Width
            $button_fullwidth = isset($this->props['submit_button_full_width']) ? $this->props['submit_button_full_width'] : 'off';
            $is_fullwidth_responsive = et_pb_responsive_options()->is_responsive_enabled($this->props, 'submit_button_full_width');
            $d_width = et_pb_responsive_options()->get_property_values($this->props, 'submit_button_full_width', 'off', true);
            $button_fullwidth_tablet = isset($this->props['submit_button_full_width_tablet']) ? $this->props['submit_button_full_width_tablet'] : $button_fullwidth;
            $button_fullwidth_phone = isset($this->props['submit_button_full_width_phone']) ? $this->props['submit_button_full_width_phone'] : $button_fullwidth;
            if ('on' === $button_fullwidth) {
                $d_width['desktop'] = '100%';
            }
            if ('on' === $button_fullwidth_tablet) {
                $d_width['tablet'] = '100%';
            }
            if ('on' === $button_fullwidth_phone) {
                $d_width['phone'] = '100%';
            }
            ET_Builder_Element::set_style($this->render_slug, [
                'selector' => '%%order_class%% .et_contact_bottom_container .et_pb_contact_submit,%%order_class%% .et_pb_submit_btn_container button',
                'declaration' => 'margin: 0 0 0 3%;',
            ]);
            if ('on' === $is_used_captcha) {
                if ('off' === $button_fullwidth) {
                    et_pb_responsive_options()->generate_responsive_css($d_alignment, '.et_pb_submit_btn_container', 'justify-content', $this->render_slug, '', 'align');
                }
                if ('on' === $button_fullwidth || $is_fullwidth_responsive) {

                    et_pb_responsive_options()->generate_responsive_css($d_width, '.et_pb_submit_btn_container .et_pb_button', 'width', $this->render_slug, '', 'select');
                }
            } else {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .et_contact_bottom_container',
                    'declaration' => 'float:none;',
                ]);
                if ('off' === $button_fullwidth) {
                    et_pb_responsive_options()->generate_responsive_css($d_alignment, '.et_contact_bottom_container', 'justify-content', $this->render_slug, '', 'align');
                }
                if ('on' === $button_fullwidth || $is_fullwidth_responsive) {
                    et_pb_responsive_options()->generate_responsive_css($d_width, '.et_contact_bottom_container button', 'width', $this->render_slug, ' !important', 'yes_no_button');
                }
            }
        }

    }
}