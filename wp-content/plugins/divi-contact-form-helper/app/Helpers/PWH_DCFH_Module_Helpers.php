<?php

namespace PWH_DCFH\App\Helpers;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Module_Helpers')) {
    class PWH_DCFH_Module_Helpers
    {

        private static $_instance;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Module_Helpers
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Get Alignment
         *
         * @param $props
         * @param string $device
         *
         * @return string
         */
        public static function get_alignment($props, $device = 'desktop')
        {
            $suffix = 'desktop' !== $device ? "_$device" : '';
            $text_orientation = isset($props["button_alignment$suffix"]) ? $props["button_alignment$suffix"] : '';

            return et_pb_get_alignment($text_orientation);
        }

        /**
         * Get Shadow Values
         *
         * @param $preset
         *
         * @return string[]|string[][]
         */
        public static function get_shadow_presets_values($preset = null)
        {
            $presets = [
                'none' => [
                    'horizontal' => '',
                    'vertical' => '',
                    'blur' => '',
                    'spread' => '',
                    'position' => '',
                ],
                'preset1' => [
                    'horizontal' => '0px',
                    'vertical' => '2px',
                    'blur' => '18px',
                    'spread' => '0px',
                    'position' => '',
                ],
                'preset2' => [
                    'horizontal' => '6px',
                    'vertical' => '6px',
                    'blur' => '18px',
                    'spread' => '0px',
                    'position' => '',
                ],
                'preset3' => [
                    'horizontal' => '0px',
                    'vertical' => '12px',
                    'blur' => '18px',
                    'spread' => '-6px',
                    'position' => '',
                ],
                'preset4' => [
                    'horizontal' => '10px',
                    'vertical' => '10px',
                    'blur' => '0px',
                    'spread' => '0px',
                    'position' => '',
                ],
                'preset5' => [
                    'horizontal' => '0px',
                    'vertical' => '6px',
                    'blur' => '0px',
                    'spread' => '10px',
                    'position' => '',
                ],
                'preset6' => [
                    'horizontal' => '0px',
                    'vertical' => '0px',
                    'blur' => '18px',
                    'spread' => '0px',
                    'position' => 'inset',
                ],
                'preset7' => [
                    'horizontal' => '10px',
                    'vertical' => '10px',
                    'blur' => '0px',
                    'spread' => '0px',
                    'position' => 'inset',
                ],
            ];
            if (!empty($preset) && isset($presets[$preset])) {
                return $presets[$preset];
            }

            return $presets;
        }

        /**
         * Get Shadow Options
         *
         * @param $prefix
         *
         * @return array
         */
        public static function get_shadow_presets($prefix)
        {
            $options = [];
            foreach (self::get_shadow_presets_values() as $key => $value) {
                if ('none' === $key) {
                    $options[] = [
                        'value' => $key,
                        'icon' => $key,
                        'fields' => [
                            $prefix.'_shadow_horizontal' => '0',
                            $prefix.'_shadow_vertical' => '0',
                            $prefix.'_shadow_blur' => '0',
                            $prefix.'_shadow_spread' => '0',
                            $prefix.'_shadow_position' => '',
                        ]
                    ];
                } else {
                    $options[] = [
                        'value' => $key,
                        'content' => sprintf('<span class="preset %1$s"></span>', esc_attr($key)),
                        'fields' => [
                            $prefix.'_shadow_horizontal' => $value["horizontal"],
                            $prefix.'_shadow_vertical' => $value["vertical"],
                            $prefix.'_shadow_blur' => $value["blur"],
                            $prefix.'_shadow_spread' => $value["spread"],
                            $prefix.'_shadow_position' => $value["position"],
                        ]
                    ];
                }
            }

            return $options;
        }

        /**
         * @param $props
         * @param int|string $top
         * @param int|string $right
         * @param int|string $bottom
         * @param int|string $left
         *
         * @return string[]
         */
        public static function get_responsive_margin_padding_border_props($props, $top = '0', $right = '0', $bottom = '0', $left = '0')
        {
            $responsive_props = ['desktop' => '', 'tablet' => '', 'phone' => ''];
            $default_values = [0 => $top, 1 => $right, 2 => $bottom, 3 => $left];
            foreach ($props as $device => $prop) {
                if (!empty($prop)) {
                    $prop_array = explode('|', $prop);
                    foreach ($prop_array as $key => $value) {
                        if (!in_array($value, ['true', 'false', 'on', 'off'])) {
                            $prop_array[$key] = !empty($value) ? $value : (isset($default_values[$key]) ? $default_values[$key] : 0);
                        } else {
                            unset($prop_array[$key]);
                        }
                    }
                    $responsive_props[$device] = implode(' ', $prop_array);
                } else {
                    $responsive_props[$device] = '';
                }
            }

            return $responsive_props;
        }

        /**
         * Build Devicss CSS Props
         *
         * @param $props
         * @param $slug
         * @param string $default
         * @param false $copy
         *
         * @return array|string|string[]
         */
        public static function get_responsive_props($props, $slug, $default = '', $copy = false)
        {
            $responsive_prop = [];
            $responsive_enabled = isset($props["{$slug}_last_edited"]) && et_pb_get_responsive_status($props["{$slug}_last_edited"]);
            if (!isset($props[$slug]) || '' === $props[$slug]) {
                $responsive_prop['desktop'] = $default;
            } else {
                $responsive_prop['desktop'] = $props[$slug];
            }
            if (!$responsive_enabled || !isset($props["{$slug}_tablet"]) || '' === $props["{$slug}_tablet"]) {
                $responsive_prop['tablet'] = $copy ? $responsive_prop['desktop'] : $default;
            } else {
                $responsive_prop['tablet'] = $props["{$slug}_tablet"];
            }
            if (!$responsive_enabled || !isset($props["{$slug}_phone"]) || '' === $props["{$slug}_phone"]) {
                $responsive_prop['phone'] = $copy ? $responsive_prop['tablet'] : $default;
            } else {
                $responsive_prop['phone'] = $props["{$slug}_phone"];
            }

            return str_replace(['|', 'on|', 'off|'], '', $responsive_prop);
        }

        /**
         * Process Multiple Mimes Checkbox
         *
         * @param $data
         *
         * @return array
         */
        public static function process_multiple_mimes_checkboxes_value($data)
        {
            $allowed_mime_types = pwh_dcfh_helpers()::get_wp_allowed_mime_types();
            $mimes = array_keys($allowed_mime_types);
            $mime_values = [];
            $mime_keys = [];
            $data = explode('|', $data);
            foreach ($data as $key => $val) {
                if ('on' === strtolower($val) && isset($mimes[$key]) && isset($allowed_mime_types[$mimes[$key]])) {
                    array_push($mime_values, ' '.str_replace("|", ', ', $mimes[$key]));
                    array_push($mime_keys, $allowed_mime_types[$mimes[$key]]);
                }
            }

            return ['keys' => implode(',', $mime_keys), 'values' => implode(',', $mime_values)];
        }

        /**
         * Process Multiple Checkboxs
         *
         * @param $checkboxes
         * @param $array_to_match
         *
         * @return string
         */
        public static function process_multiple_checkboxes_value($checkboxes, $array_to_match)
        {
            $processed_data = [];
            $checkboxes = explode('|', $checkboxes);
            foreach ($checkboxes as $key => $val) {
                if (isset($array_to_match[$key]) && 'on' === strtolower($val)) {
                    array_push($processed_data, $array_to_match[$key]);
                }
            }

            return implode(',', $processed_data);
        }

        /**
         * Get WordPress Allowed Mimes Keys with .
         *
         * @return int[]|string[]
         */
        public static function get_mimes_with_ext()
        {
            $allowed_mime_types = pwh_dcfh_helpers()::get_wp_allowed_mime_types();
            $mimes = array_combine(
                array_map(function ($key) {
                    return '.'.$key;
                }, array_keys($allowed_mime_types)),
                $allowed_mime_types
            );

            return array_keys($mimes);
        }

        /**
         * Get Week Day Names
         *
         * @return array
         */
        public static function get_week_day_names($associative_array = true)
        {
            $week_day_names = [];
            $today = (86400 * (date_i18n("N")));
            setlocale(LC_TIME, pwh_dcfh_helpers()::get_wp_locale());
            for ($i = 0; $i < 7; $i++) {
                $name = strftime('%A', time() - $today + ($i * 86400));
                if ($associative_array) {
                    $index = strtolower($name);
                } else {
                    $index = $i;
                }
                $week_day_names[$index] = $name;
            }

            return $week_day_names;
        }

        /**
         * Get Flex Box Aligment
         *
         * @param array $alignment
         *
         * @return array|string[]
         */
        public static function get_flex_alignment(array $alignment)
        {
            $flex_alignment = array_map(
                function ($v) {
                    if ($v == 'center') {
                        $v = 'center';
                    } elseif ($v == 'right') {
                        $v = 'flex-end';
                    } else {
                        $v = 'flex-start';
                    }

                    return $v;
                },
                $alignment
            );

            return $flex_alignment;
        }

        /**
         * Get Flex Box Column
         *
         * @param array $column
         *
         * @return array|string[]
         */
        public static function get_flex_column(array $column)
        {
            $flex_column = array_map(
                function ($v) {
                    if ($v == '2') {
                        $v = '50%';
                    } elseif ($v == '3') {
                        $v = '33%';
                    } elseif ($v == '4') {
                        $v = '25%';
                    } else {
                        $v = '100%';
                    }

                    return $v;
                },
                $column
            );

            return $flex_column;
        }

        /**
         * Get Server Max Files Allowed
         *
         * @return false|string
         */
        public static function max_number_files_allowed()
        {
            return ini_get('max_file_uploads');
        }
    }
}