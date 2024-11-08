<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use DOMDocument;
use DOMException;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Field_Textarea_Validation')) {
    class PWH_DCFH_Field_Textarea_Validation
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
            $required_mark = isset($this->props['required_mark']) ? $this->props['required_mark'] : 'on';
            $dom = new DOMDocument('1.0', 'UTF-8');
            if (function_exists('mb_convert_encoding')) {
                $dom->loadHTML(mb_convert_encoding($this->output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $dom->encoding = 'utf-8';
            } else {
                $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$this->output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            }
            $input = $dom->getElementsByTagName('textarea');
            $p_tag = $dom->getElementsByTagName('p');
            if ((isset($input->length) && 0 !== $input->length) && (isset($p_tag->length) && 0 !== $p_tag->length)) {
                $input_item = $input->item(0);
                $p_item = $p_tag->item(0);
                $input_name = $input_item->getAttribute('name');
                $field_title = isset($this->props['field_title']) ? $this->props['field_title'] : 'Message';
                $min_length = isset($this->props['min_length']) ? intval($this->props['min_length']) : 0;
                $max_length = isset($this->props['max_length']) ? intval($this->props['max_length']) : 0;
                $allowed_symbols = isset($this->props['allowed_symbols']) ? $this->props['allowed_symbols'] : 'all';
                $pattern = '';
                $title = '';
                $max_length_attr = '';
                $symbols_pattern = '.';
                $length_pattern = '*';
                if (in_array($allowed_symbols, ['letters', 'numbers', 'alphanumeric'])) {
                    switch ($allowed_symbols) {
                        case 'letters':
                            $symbols_pattern = '[A-Z|a-z|\s-]';
                            $title = sprintf(__('%s: Only letters allowed.', 'et_builder'), $field_title);
                            break;
                        case 'numbers':
                            $symbols_pattern = '[0-9\s-]';
                            $title = sprintf(__('%s: Only numbers allowed.', 'et_builder'), $field_title);
                            break;
                        case 'alphanumeric':
                            $symbols_pattern = '[\w\s-]';
                            $title = sprintf(__('%s: Only letters and numbers allowed.', 'et_builder'), $field_title);
                            break;
                    }
                }
                if (0 !== $min_length || 0 !== $max_length) {
                    $length_pattern = '{';
                    if (0 !== $min_length) {
                        $length_pattern .= $min_length;
                        $title .= sprintf(__('%1$s: Minimum length: %2$d characters. ', 'et_builder'), $field_title, $min_length);
                    }
                    if (0 === $max_length) {
                        $length_pattern .= ',';
                    }
                    if (0 === $min_length) {
                        $length_pattern .= '0';
                    }
                    if (0 !== $max_length) {
                        $length_pattern .= ",$max_length";
                        $title .= sprintf(__('%1$s: Maximum length: %2$d characters.', 'et_builder'), $field_title, $max_length);
                    }
                    $length_pattern .= '}';
                }
                if ('.' !== $symbols_pattern || '*' !== $length_pattern) {
                    $pattern = sprintf('%1$s%2$s', $symbols_pattern, $length_pattern);
                }
                if ('' !== $title) {
                    $title = sprintf('%1$s', $title);
                }
                if (!empty($pattern) && !empty($title)) {
                    $input_item->setAttribute('data-pattern', $pattern);
                    $input_item->setAttribute('data-message', $title);
                    $hidden_input = $dom->createElement('input');
                    $hidden_input->setAttribute('type', 'text');
                    $hidden_input->setAttribute('class', 'pwh-dcfh-textarea-validation et_pb_d_none');
                    $hidden_input->setAttribute('data-required_mark', 'required');
                    $hidden_input->setAttribute('data-title', $field_title);
                    $hidden_input->setAttribute('data-textarea', $input_name);
                    $hidden_input->setAttribute('value', '');
                    $p_item->appendChild($hidden_input);
                    $this->output = $dom->saveHTML();
                }
            }

            return $this->output;
        }

    }
}