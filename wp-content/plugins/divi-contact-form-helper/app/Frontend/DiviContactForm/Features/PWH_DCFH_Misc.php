<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use DOMDocument;
use DOMException;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Misc')) {
    class PWH_DCFH_Misc
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
         * Render Referer URL Input
         *
         * @throws DOMException
         */
        public function output_referer_url()
        {
            $referer_url = isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field($_SERVER['HTTP_REFERER']) : '';
            $dom = new DOMDocument('1.0', 'UTF-8');
            if (function_exists('mb_convert_encoding')) {
                $dom->loadHTML(mb_convert_encoding($this->output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $dom->encoding = 'utf-8';
            } else {
                $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'."\n".$this->output, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            }
            $form = $dom->getElementsByTagName('form');
            if (isset($form->length) && 0 !== $form->length) {
                $form_item = $form->item(0);
                $input = $dom->createElement('input');
                $input->setAttribute('type', 'hidden');
                $input->setAttribute('name', 'et_pb_contact_field_referer_url');
                $input->setAttribute('value', $referer_url);
                $form_item->appendChild($input);
                $this->output = $dom->saveHTML();
            }

            return $this->output;
        }

    }
}