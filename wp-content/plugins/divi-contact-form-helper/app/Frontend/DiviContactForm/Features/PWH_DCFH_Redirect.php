<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use DOMDocument;
use DOMException;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Redirect')) {
    class PWH_DCFH_Redirect
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
            $form = $dom->getElementsByTagName('form');
            if (isset($form->length) && 0 !== $form->length) {
                $form_item = $form->item(0);
                $redirect_url = '';
                $custom_redirect_to = isset($this->props['custom_redirect_to']) ? $this->props['custom_redirect_to'] : 'url';
                $custom_redirect_url = isset($this->props['custom_redirect_url']) ? $this->props['custom_redirect_url'] : null;
                $custom_redirect_page = isset($this->props['custom_redirect_page']) ? $this->props['custom_redirect_page'] : null;
                $custom_redirect_delay = isset($this->props['custom_redirect_delay']) ? $this->props['custom_redirect_delay'] : '3000';
                if ('url' === $custom_redirect_to && !empty($custom_redirect_url)) {
                    $redirect_url = $custom_redirect_url;
                }
                if ('page' === $custom_redirect_to && !empty($custom_redirect_page)) {
                    $redirect_url = get_the_permalink(intval($custom_redirect_page));
                }
                if (!empty($redirect_url)) {
                    $form_item->setAttribute('data-redirect-url', $redirect_url);
                    $form_item->setAttribute('data-redirect-delay', $custom_redirect_delay);
                    $this->output = $dom->saveHTML();
                }
            }

            return $this->output;
        }

    }
}