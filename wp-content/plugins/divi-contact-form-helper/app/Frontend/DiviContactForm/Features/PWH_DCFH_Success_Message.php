<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use ET_Builder_Element;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Success_Message')) {
    class PWH_DCFH_Success_Message
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
         * Render Css
         *
         * @return void
         */
        public function css()
        {
            $success_message_class = '%%order_class%% .et-pb-contact-message.et_pb_success_message';
            $desktop_font = isset($this->props['success_message_font']) ? $this->props['success_message_font'] : null;
            $tablet_font = isset($this->props['success_message_font_tablet']) ? $this->props['success_message_font_tablet'] : null;
            $phone_font = isset($this->props['success_message_font_phone']) ? $this->props['success_message_font_phone'] : null;
            // Text Font
            if (!empty($desktop_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $success_message_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($desktop_font)),
                ]);
            }
            if (!empty($tablet_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $success_message_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($tablet_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if (!empty($phone_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $success_message_class,
                    'declaration' => sprintf('%s;', et_builder_set_element_font($phone_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
            }
            // Text Color
            $d_text_color = et_pb_responsive_options()->get_property_values($this->props, 'success_message_font_color');
            et_pb_responsive_options()->generate_responsive_css($d_text_color, $success_message_class, 'color', $this->render_slug, '', 'color');
            // Background Color
            $d_box_background = et_pb_responsive_options()->get_property_values($this->props, 'success_message_background');
            et_pb_responsive_options()->generate_responsive_css($d_box_background, $success_message_class, 'background', $this->render_slug, '', 'color');
            // Text Size
            $d_text_size = et_pb_responsive_options()->get_property_values($this->props, 'success_message_font_size');
            et_pb_responsive_options()->generate_responsive_css($d_text_size, $success_message_class, 'font-size', $this->render_slug);
            // Text Letter Spacing
            $d_text_letter = et_pb_responsive_options()->get_property_values($this->props, 'success_message_letterspace');
            et_pb_responsive_options()->generate_responsive_css($d_text_letter, $success_message_class, 'letter-spacing', $this->render_slug);
            // Text Line Height
            $d_text_line = et_pb_responsive_options()->get_property_values($this->props, 'success_message_lineheight');
            et_pb_responsive_options()->generate_responsive_css($d_text_line, $success_message_class, 'line-height', $this->render_slug);
            // Text Alignment
            $d_text_alignment = et_pb_responsive_options()->get_property_values($this->props, 'success_message_alignment');
            et_pb_responsive_options()->generate_responsive_css($d_text_alignment, $success_message_class, 'text-align', $this->render_slug, '', 'text_alignment');
            // Margin
            $d_margin = et_pb_responsive_options()->get_property_values($this->props, 'success_message_margin');
            $d_margin = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_margin);
            et_pb_responsive_options()->generate_responsive_css($d_margin, $success_message_class, 'margin', $this->render_slug, '', 'custom_padding');
            // Padding
            $d_padding = et_pb_responsive_options()->get_property_values($this->props, 'success_message_padding');
            $d_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_padding);
            et_pb_responsive_options()->generate_responsive_css($d_padding, $success_message_class, 'padding', $this->render_slug, '', 'custom_padding');
            // Border
            $d_border = et_pb_responsive_options()->get_property_values($this->props, 'success_message_border');
            $d_border = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_border);
            et_pb_responsive_options()->generate_responsive_css($d_border, $success_message_class, 'border-radius', $this->render_slug, '', 'border-radius');
            // Border Color
            $d_border_color = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'success_message_border_color');
            et_pb_responsive_options()->generate_responsive_css($d_border_color, $success_message_class, 'border-color', $this->render_slug, '', 'color');
            // Border Width
            $d_border_width = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'success_message_border_width');
            et_pb_responsive_options()->generate_responsive_css($d_border_width, $success_message_class, 'border-width', $this->render_slug);
            // Border Style
            $d_border_style = pwh_dcfh_module_helpers()::get_responsive_props($this->props, 'success_message_border_style');
            et_pb_responsive_options()->generate_responsive_css($d_border_style, $success_message_class, 'border-style', $this->render_slug, '', 'select');
            // Shadow
            $box_shadow_preset = isset($this->props['success_message_shadow']) ? $this->props['success_message_shadow'] : null;
            if (!empty($box_shadow_preset) && 'none' !== $box_shadow_preset) {
                $shadow = pwh_dcfh_module_helpers()::get_shadow_presets_values($box_shadow_preset);
                $horizontal = $shadow['horizontal'];
                $vertical = $shadow['vertical'];
                $blur = $shadow['blur'];
                $spread = $shadow['spread'];
                $position = $shadow['position'];
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => $success_message_class,
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