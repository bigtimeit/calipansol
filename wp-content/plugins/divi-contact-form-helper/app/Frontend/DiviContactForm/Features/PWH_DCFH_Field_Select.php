<?php

namespace PWH_DCFH\App\Frontend\DiviContactForm\Features;

use ET_Builder_Element;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Field_Select')) {
    class PWH_DCFH_Field_Select
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
         */
        public function output()
        {
        }

        public function css()
        {
            // BG Color
            $d_field_background_color = et_pb_responsive_options()->get_property_values($this->props, 'form_field_background_color');
            et_pb_responsive_options()->generate_responsive_css($d_field_background_color, '%%order_class%% .select2-container--default .select2-selection--single .select2-selection__rendered', 'background', $this->render_slug, "!important;", 'color');
            // Text Color
            $d_field_text_color = et_pb_responsive_options()->get_property_values($this->props, 'form_field_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_field_text_color, '%%order_class%% .select2-container--default .select2-selection--single .select2-selection__rendered', 'color', $this->render_slug, "!important;", 'color');
            et_pb_responsive_options()->generate_responsive_css($d_field_text_color, '%%order_class%% .et_pb_contact_field[data-type=select]:after', 'border-top-color', $this->render_slug, "!important;", 'color');
            // Focus BG Color
            $d_field_focus_background_color = et_pb_responsive_options()->get_property_values($this->props, 'form_field_focus_background_color');
            et_pb_responsive_options()->generate_responsive_css($d_field_focus_background_color, '%%order_class%% .select2-container .select2-selection--single:focus', 'background', $this->render_slug, "!important;", 'color');
            // Focus Text Color
            $d_field_focus_text_color = et_pb_responsive_options()->get_property_values($this->props, 'form_field_focus_text_color');
            et_pb_responsive_options()->generate_responsive_css($d_field_focus_text_color, '%%order_class%% .select2-container--default .select2-selection--single .select2-selection__rendered:focus', 'color', $this->render_slug, "!important;", 'color');
            // Padding
            $d_field_custom_padding = et_pb_responsive_options()->get_property_values($this->props, 'form_field_custom_padding');
            $d_field_custom_padding = pwh_dcfh_module_helpers()::get_responsive_margin_padding_border_props($d_field_custom_padding);
            et_pb_responsive_options()->generate_responsive_css($d_field_custom_padding, '%%order_class%% .select2-container .select2-selection--single', 'padding', $this->render_slug, '', 'custom_padding');
            // Text Font
            $form_field_font_last_edited = isset($this->props['form_field_font_last_edited']) ? $this->props['form_field_font_last_edited'] : null;
            $is_form_field_font_responsive = et_pb_get_responsive_status($form_field_font_last_edited);
            $desktop_font = isset($this->props['form_field_font']) ? $this->props['form_field_font'] : null;
            $tablet_font = isset($this->props['form_field_font_tablet']) ? $this->props['form_field_font_tablet'] : null;
            $phone_font = isset($this->props['form_field_font_phone']) ? $this->props['form_field_font_phone'] : null;
            if (!empty($desktop_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .select2-container .select2-selection--single',
                    'declaration' => sprintf('%s', et_builder_set_element_font($desktop_font)),
                ]);
            }
            if (!empty($tablet_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .select2-container .select2-selection--single',
                    'declaration' => sprintf('%s', et_builder_set_element_font($tablet_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_980'),
                ]);
            }
            if (!empty($phone_font)) {
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .select2-container .select2-selection--single',
                    'declaration' => sprintf('%s', et_builder_set_element_font($phone_font)),
                    'media_query' => ET_Builder_Element::get_media_query('max_width_767'),
                ]);
            }
            // Size
            $d_field_font_size = et_pb_responsive_options()->get_property_values($this->props, 'form_field_font_size');
            et_pb_responsive_options()->generate_responsive_css($d_field_font_size, '%%order_class%% .select2-container .select2-selection--single', 'font-size', $this->render_slug);
            // Align
            $d_field_text_align = et_pb_responsive_options()->get_property_values($this->props, 'form_field_text_align');
            et_pb_responsive_options()->generate_responsive_css($d_field_text_align, '%%order_class%% .select2-container--default .select2-selection--single .select2-selection__rendered', 'text-align', $this->render_slug, '', 'text_alignment');
            // Letter Spacing
            $d_field_letter_spacing = et_pb_responsive_options()->get_property_values($this->props, 'form_field_letter_spacing');
            et_pb_responsive_options()->generate_responsive_css($d_field_letter_spacing, '%%order_class%% .select2-container .select2-selection--single', 'letter-spacing', $this->render_slug);
            // Line Height
            $line_height_important = et_builder_has_limitation('force_use_global_important') ? ' !important' : '';
            $d_field_line_height = et_pb_responsive_options()->get_property_values($this->props, 'form_field_line_height');
            //et_pb_responsive_options()->generate_responsive_css($d_field_line_height, '%%order_class%% .select2-container--default .select2-selection--single .select2-selection__rendered', 'line-height', $this->render_slug, $line_height_important);
            // Shadow
            $field_text_shadow_style = isset($this->props['form_field_text_shadow_style']) ? $this->props['form_field_text_shadow_style'] : null;
            if (!empty($field_text_shadow_style) && 'none' !== $field_text_shadow_style) {
                $form_field_text_shadow_horizontal_length = isset($this->props['form_field_text_shadow_horizontal_length']) ? $this->props['form_field_text_shadow_horizontal_length'] : null;
                $form_field_text_shadow_vertical_length = isset($this->props['form_field_text_shadow_vertical_length']) ? $this->props['form_field_text_shadow_vertical_length'] : null;
                $form_field_text_shadow_blur_strength = isset($this->props['form_field_text_shadow_blur_strength']) ? $this->props['form_field_text_shadow_blur_strength'] : null;
                $form_field_text_shadow_color = isset($this->props['form_field_text_shadow_color']) ? $this->props['form_field_text_shadow_color'] : null;
                ET_Builder_Element::set_style($this->render_slug, [
                    'selector' => '%%order_class%% .select2-container--default .select2-selection--single .select2-selection__rendered',
                    'declaration' => "text-shadow:$form_field_text_shadow_horizontal_length $form_field_text_shadow_vertical_length $form_field_text_shadow_blur_strength $form_field_text_shadow_color;",
                ]);
            }
            // Global
            $declaration = 'line-height: 52px;padding-left: 16px;-webkit-appearance: none;background-color: #eee;width: 100%;border-width: 0;border-radius: 0;';
            if (empty($desktop_font)) {
                $declaration .= 'font-weight: normal;';
            }
            if (isset($d_field_text_color['desktop']) && empty($d_field_text_color['desktop'])) {
                $declaration .= 'color: #999;';
            }
            ET_Builder_Element::set_style($this->render_slug, [
                'selector' => '%%order_class%% .select2-container--default .select2-selection--single .select2-selection__rendered',
                'declaration' => $declaration,
            ]);
        }

    }
}