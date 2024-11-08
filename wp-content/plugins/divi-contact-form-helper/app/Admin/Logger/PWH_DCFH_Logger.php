<?php

namespace PWH_DCFH\App\Admin\Logger;

use WP_Error;
use WP_Post;
use WP_Query;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Logger')) {
    class PWH_DCFH_Logger
    {

        private static $_instance;

        private $_post_type = 'pwh_dcfh_log';

        private $_taxonomy_type = 'pwh_dcfh_log_taxonomies';

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Logger
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            // Create the log post type
            add_action('init', [$this, 'register_post_type'], 1);
            // Create types taxonomy and default types
            add_action('init', [$this, 'register_taxonomy'], 1);
        }

        /**
         * Registers the pwh_dcfh_log Post Type
         *
         * @return void
         */
        public function register_post_type()
        {
            /* Logs post type */
            $log_args = [
                'labels' => 'Logs',
                'public' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_ui' => false,
                'query_var' => false,
                'rewrite' => false,
                'capability_type' => 'post',
                'supports' => ['title', 'editor'],
                'can_export' => false,
            ];
            register_post_type($this->_post_type, $log_args);
        }

        /**
         * Registers the Type Taxonomy
         *
         * The "Type" taxonomy is used to determine the type of log entry
         *
         * @return void
         */
        public function register_taxonomy()
        {
            register_taxonomy($this->_taxonomy_type, $this->_post_type, ['public' => false]);
        }

        /**
         * Add Log
         *
         * @param $title
         * @param $message
         * @param $log_term
         * @param int $parent
         *
         * @return int|WP_Error
         */
        public function add_log($title, $message, $log_term, $parent = 0)
        {
            $log_data = [
                'post_title' => $title,
                'post_content' => $message,
                'post_parent' => $parent,
                'log_type' => $log_term,
            ];

            return $this->save_log($log_data);
        }

        /**
         * Save Log
         *
         * @param array $log_data
         * @param array $log_meta
         *
         * @return int|WP_Error
         */
        public function save_log($log_data = [], $log_meta = [])
        {
            $defaults = [
                'post_type' => $this->_post_type,
                'post_status' => 'private',
                'post_parent' => 0,
                'post_content' => '',
                'post_author' => get_current_user_id(),
                'log_type' => false,
            ];
            $args = wp_parse_args($log_data, $defaults);
            $log_id = wp_insert_post($args);
            if ($log_data['log_type']) {
                wp_set_object_terms($log_id, $log_data['log_type'], $this->_taxonomy_type, false);
            }
            if ($log_id && !empty($log_meta)) {
                foreach ((array)$log_meta as $key => $meta) {
                    update_post_meta($log_id, '_pwh_dcfh_log_'.sanitize_key($key), $meta);
                }
            }

            return $log_id;
        }

        /**
         * Update Log
         *
         * @param array $log_data
         * @param array $log_meta
         */
        public function update_log($log_data = [], $log_meta = [])
        {
            $defaults = [
                'post_type' => $this->_post_type,
                'post_status' => 'private',
                'post_parent' => 0,
                'post_author' => get_current_user_id(),
            ];
            $args = wp_parse_args($log_data, $defaults);
            $log_id = wp_update_post($args);
            if ($log_id && !empty($log_meta)) {
                foreach ((array)$log_meta as $key => $meta) {
                    if (!empty($meta)) {
                        update_post_meta($log_id, '_pwh_dcfh_log_'.sanitize_key($key), $meta);
                    }
                }
            }
        }

        /**
         * Get Logs
         *
         * @param $log_term
         * @param int $parent
         * @param null $paged
         *
         * @return false|int[]|WP_Post[]
         */
        public function get_logs($log_term, $parent = 0, $paged = null)
        {
            return $this->get_connected_logs(['post_parent' => $parent, 'paged' => $paged, 'log_type' => $log_term]);
        }

        /**
         * Get Connected Logs
         *
         * @param array $args
         *
         * @return false|int[]|WP_Post[]
         */
        public function get_connected_logs($args = [])
        {
            $defaults = [
                'post_type' => $this->_post_type,
                'posts_per_page' => 20,
                'post_status' => 'private',
                'paged' => get_query_var('paged'),
                'log_type' => false,
            ];
            $query_args = wp_parse_args($args, $defaults);
            if ($query_args['log_type']) {
                // phpcs:disable
                $query_args['tax_query'] = [
                    [
                        'taxonomy' => $this->_taxonomy_type,
                        'field' => 'slug',
                        'terms' => $query_args['log_type'],
                    ]
                ];
                // phpcs:enable
            }
            $logs = get_posts($query_args);
            if ($logs) {
                return $logs;
            }

            return false;
        }

        /**
         * Get Log Counts
         *
         * @param $log_term
         * @param int $parent
         * @param null $meta_query
         * @param null $date_query
         *
         * @return int
         */
        public function get_log_count($log_term, $parent = 0, $meta_query = null, $date_query = null)
        {
            $query_args = [
                'post_parent' => $parent,
                'post_type' => $this->_post_type,
                'posts_per_page' => -1,
                'post_status' => 'private',
                'fields' => 'ids',
            ];
            if (!empty($log_term)) {
                // phpcs:disable
                $query_args['tax_query'] = [
                    [
                        'taxonomy' => $this->_taxonomy_type,
                        'field' => 'slug',
                        'terms' => $log_term,
                    ]
                ];
            }
            if (!empty($meta_query)) {
                $query_args['meta_query'] = $meta_query;
            }
            if (!empty($date_query)) {
                $query_args['date_query'] = $date_query;
            }
            // phpcs:enable
            $logs = new WP_Query($query_args);

            return $logs->post_count;
        }

        /**
         * Delete Log
         *
         * @param $log_term
         * @param int $parent
         * @param null $meta_query
         */
        public function delete_logs($log_term, $parent = 0, $meta_query = null)
        {
            $query_args = [
                'post_parent' => $parent,
                'post_type' => $this->_post_type,
                'posts_per_page' => -1,
                'post_status' => 'private',
                'fields' => 'ids',
            ];
            if (!empty($log_term)) {
                // phpcs:disable
                $query_args['tax_query'] = [
                    [
                        'taxonomy' => $this->_taxonomy_type,
                        'field' => 'slug',
                        'terms' => $log_term,
                    ]
                ];
            }
            if (!empty($meta_query)) {
                $query_args['meta_query'] = $meta_query;
            }
            // phpcs:enable
            $logs = get_posts($query_args);
            if ($logs) {
                foreach ($logs as $log) {
                    wp_delete_post($log, true);
                }
            }
        }

    }
}