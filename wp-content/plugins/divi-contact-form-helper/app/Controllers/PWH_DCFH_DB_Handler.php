<?php

namespace PWH_DCFH\App\Controllers;
if (!class_exists('PWH_DCFH_DB_Handler')) {
    class PWH_DCFH_DB_Handler
    {

        private static $_instance;

        private static $contact_form_title_option;

        private static $contact_form_pageid_option;

        private static $contact_form_views_option;

        private static $contact_form_unique_views_option;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_DB_Handler
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }
            self::$contact_form_title_option = pwh_dcfh_hc()::CF_TITLE_OPTION_NAME;
            self::$contact_form_pageid_option = pwh_dcfh_hc()::CF_PAGEID_OPTION_NAME;
            self::$contact_form_views_option = pwh_dcfh_hc()::CF_VIEWS_OPTION_NAME;
            self::$contact_form_unique_views_option = pwh_dcfh_hc()::CF_UNIQUE_VIEWS_OPTION_NAME;

            return self::$_instance;
        }

        /**
         * Update Contact Form Title
         *
         * @param $unique_id
         *
         * @param $title
         *
         * @return void
         */
        public static function update_contact_form_title($unique_id, $title)
        {
            $title_option = self::$contact_form_title_option.$unique_id;
            if (!empty($title)) {
                update_option($title_option, wp_strip_all_tags($title), 'no');
            } else {
                update_option($title_option, $unique_id, 'no');
            }
        }

        /**
         * Update Contact Form Page ID
         *
         * @param $unique_id
         *
         * @return void
         */
        public static function update_contact_form_page_id($unique_id)
        {
            $page_id = get_the_ID();
            $pageid_option = self::$contact_form_pageid_option.$unique_id;
            update_option($pageid_option, $page_id, 'no');
        }

        /**
         * Update Contact Form Views
         *
         * @param $unique_id
         *
         * @return void
         */
        public static function update_contact_form_views($unique_id)
        {
            // Return If Logged In
            if (is_user_logged_in()) {
                return;
            }
            // Return If Form Submit
            if (!empty($_POST)) { // phpcs:ignore WordPress.Security.NonceVerification
                return;
            }
            // Return If BOT
            if (pwh_dcfh_helpers()::is_bot()) {
                return;
            }
            // Update Views
            $views_option = self::$contact_form_views_option.$unique_id;
            $views = get_option($views_option);
            $views = false === $views ? 1 : $views + 1;
            update_option($views_option, $views, 'no');
            // Return If IP Exist
            $ip_address = pwh_dcfh_helpers()::get_ip_address();
            if (self::is_user_ip_exists($unique_id, $ip_address)) {
                return;
            }
            // Return If Cookie Exist
            $cookie_name = 'pwh_dcfh_uniqueviews_'.md5($ip_address).'_'.$unique_id;
            if (isset($_COOKIE[$cookie_name])) {
                return;
            }
            // Update Unique Views
            $unique_views_option = self::$contact_form_unique_views_option.$unique_id;
            $unique_views = get_option($unique_views_option);
            $unique_views = false === $unique_views ? 1 : $unique_views + 1;
            update_option($unique_views_option, $unique_views, 'no');
        }

        /**
         * Reset Form Views
         *
         * @param $unique_id
         *
         * @return void
         */
        public static function reset_contact_form_views($unique_id)
        {
            update_option(self::$contact_form_views_option.$unique_id, 0, 'no');
            update_option(self::$contact_form_unique_views_option.$unique_id, 0, 'no');
        }

        /**
         * Delete Form Options
         *
         * @param $unique_id
         *
         * @return void
         */
        public static function delete_contact_form_options($unique_id)
        {
            delete_option(self::$contact_form_title_option.$unique_id);
            delete_option(self::$contact_form_pageid_option.$unique_id);
            delete_option(self::$contact_form_views_option.$unique_id);
            delete_option(self::$contact_form_unique_views_option.$unique_id);
        }

        /**
         * Get Form Title
         *
         * @param $unique_id
         *
         * @return mixed|void
         */
        public static function get_contact_form_title($unique_id)
        {
            return get_option(self::$contact_form_title_option.$unique_id);
        }

        /**
         * Get Contact Form Page ID
         *
         * @param $unique_id
         *
         * @return false|mixed|void
         */
        public static function get_contact_form_page_id($unique_id)
        {
            return get_option(self::$contact_form_pageid_option.$unique_id);
        }

        /**
         * Get Form Views
         *
         * @param $unique_id
         *
         * @return mixed|void
         */
        public static function get_contact_form_views($unique_id)
        {
            $views = get_option(self::$contact_form_views_option.$unique_id);

            return false === $views ? 0 : $views;
        }

        /**
         * Get Form Unique Views
         *
         * @param $unique_id
         *
         * @return mixed|void
         */
        public static function get_contact_form_unique_views($unique_id)
        {
            $unique_views = get_option(self::$contact_form_unique_views_option.$unique_id);

            return false === $unique_views ? 0 : $unique_views;
        }

        /**
         * Get Forms  Database
         *
         * @return false|mixed
         */
        public static function get_contact_forms()
        {
            $key = 'pwh_dcfh_contact_forms_'.md5(__FUNCTION__);
            $contact_forms = wp_cache_get($key);
            if (false === $contact_forms) {
                global $wpdb;
                $results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key LIKE %s", pwh_dcfh_hc()::CF_FORM_ID_META_KEY)); //db call ok; no-cache ok
                // $results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT option_name,option_value FROM $wpdb->options WHERE option_name LIKE %s", '_pwh_dcfh_contact_form_title_%')); //db call ok; no-cache ok
                if (!empty($results)) {
                    foreach ($results as $result) {
                        $contact_form_id = $result->meta_value;
                        $contact_form_title = pwh_dcfh_db_handler()::get_contact_form_title($contact_form_id);
                        $contact_forms[$contact_form_id]['ID'] = $contact_form_id;
                        $contact_forms[$contact_form_id]['title'] = $contact_form_title;
                    }
                    wp_cache_set($key, $contact_forms, 'pwh_dcfh_cached_queries', 2 * MINUTE_IN_SECONDS);
                }
            }

            return $contact_forms;
        }

        /**
         * Get Contact Form Total Entries
         *
         * @param $contact_form_id
         *
         * @return int
         */
        public static function get_contact_form_total_entries($contact_form_id)
        {
            global $wpdb;
            $count = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) AS total_entries FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value LIKE %s", pwh_dcfh_hc()::CF_FORM_ID_META_KEY,
                $wpdb->esc_like($contact_form_id))); //db call ok; no-cache ok

            return (isset($count->total_entries) && !empty($count->total_entries)) ? $count->total_entries : 0;
        }

        /**
         * Get Contact Form Last Post ID
         *
         * @param $contact_form_id
         *
         * @return string|null
         */
        public static function get_contact_form_last_post_id($contact_form_id)
        {
            global $wpdb;
            $post_id = $wpdb->get_var($wpdb->prepare("SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_value LIKE %s ORDER BY meta_id DESC", $contact_form_id)); //db call ok; no-cache ok

            return $post_id;
        }

        /**
         * Get Form Fields and Total Entries
         *
         * @param $contact_form_id
         *
         * @return array
         */
        public static function get_contact_form_meta($contact_form_id)
        {
            global $wpdb;
            // Total Rows
            $total_rows = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) AS total_rows FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value LIKE %s", pwh_dcfh_hc()::CF_FORM_ID_META_KEY,
                $wpdb->esc_like($contact_form_id))); //db call ok; no-cache ok
            // Distict Post ID
            $post_id = $wpdb->get_row($wpdb->prepare("SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_value LIKE %s AND meta_key LIKE %s ORDER BY post_id DESC LIMIT 1", $wpdb->esc_like($contact_form_id),
                pwh_dcfh_hc()::CF_FORM_ID_META_KEY)); //db call ok; no-cache ok
            // Fields
            $fields = $wpdb->get_var($wpdb->prepare("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key LIKE %s AND post_id = %d ORDER BY post_id DESC", pwh_dcfh_hc()::CF_FIELDS_META_KEY,
                $post_id->post_id)); //db call ok; no-cache ok

            return [
                'fields' => $fields,
                'total_entries' => $total_rows->total_rows,
            ];
        }

        /**
         * Get Form Field Labels
         *
         * @param $contact_form_id
         *
         * @return array|mixed|string
         */
        public static function get_contact_form_fields_label($contact_form_id)
        {
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare("SELECT wp.post_excerpt FROM $wpdb->posts wp,$wpdb->postmeta wpm WHERE wp.ID = wpm.post_id AND wpm.meta_key = %s AND wpm.meta_value = %s AND wp.post_type = %s ORDER BY wp.post_date DESC LIMIT 1",
                pwh_dcfh_hc()::CF_FORM_ID_META_KEY, $contact_form_id, pwh_dcfh_hc()::POST_TYPE)); //db call ok; no-cache ok
            $labels = [];
            if ($row) {
                if (is_serialized($row->post_excerpt)) {
                    $labels = maybe_unserialize($row->post_excerpt);
                    if (!empty($labels)) {
                        $labels = wp_list_pluck($labels, 'label', 'id');
                    }
                }
            }

            return $labels;
        }

        /**
         * Get Form CSV Tags
         *
         * @return false|mixed
         */
        public static function get_contact_form_csv_data()
        {
            $key = 'pwh_dcfh_contact_form_csv_'.md5(__FUNCTION__);
            $cached_data = wp_cache_get($key);
            if (!$cached_data) {
                $contact_forms = self::get_contact_forms();
                if (!empty($contact_forms)) {
                    foreach ($contact_forms as $contact_form) {
                        $contact_form_id = $contact_form['ID'];
                        $contact_form_title = $contact_form['title'];
                        $form_meta = self::get_contact_form_meta($contact_form_id);
                        if (!empty($form_meta['fields'])) {
                            $fields = explode(',', $form_meta['fields']);
                            $fields_label = pwh_dcfh_db_handler()::get_contact_form_fields_label($contact_form_id);
                            $markup = "<ul>";
                            foreach ($fields as $field) {
                                $lablel = isset($fields_label[$field]) ? $fields_label[$field] : pwh_dcfh_helpers()::clean_string($field);
                                $markup .= "<li><input type='checkbox' id='$field' name='contact_form_fields[]' value='$field' checked>";
                                $markup .= "<label for='$field'>".$lablel."</label></li>";
                            }
                            $markup .= "<li><input type='checkbox' id='entry_number' name='contact_form_fields[]' value='entry_number' checked><label for='entry_number'>".__('Entry Number', pwh_dcfh_hc()::TEXT_DOMAIN)."</label></li>";
                            $markup .= "<li><input type='checkbox' id='read_by' name='contact_form_fields[]' value='read_by' checked><label for='read_by'>".__('Read By', pwh_dcfh_hc()::TEXT_DOMAIN)."</label></li>";
                            $markup .= "<li><input type='checkbox' id='submitter' name='contact_form_fields[]' value='submitter' checked><label for='submitter'>".__('Submitter', pwh_dcfh_hc()::TEXT_DOMAIN)."</label></li>";
                            $markup .= "<li><input type='checkbox' id='post_title' name='contact_form_fields[]' value='page_title' checked><label for='page_title'>".__('Page Title', pwh_dcfh_hc()::TEXT_DOMAIN)."</label>";
                            $markup .= "<li><input type='checkbox' id='page_url' name='contact_form_fields[]' value='page_url' checked><label for='page_url'>".__('Page URL', pwh_dcfh_hc()::TEXT_DOMAIN)."</label>";
                            $markup .= "<li><input type='checkbox' id='date' name='contact_form_fields[]' value='date' checked><label for='date'>".__('Date', pwh_dcfh_hc()::TEXT_DOMAIN)."</label></li>";
                            $markup .= "<li><input type='checkbox' id='ip_address' name='contact_form_fields[]' value='ip_address' checked><label for='ip_address'>".__('IP Address', pwh_dcfh_hc()::TEXT_DOMAIN)."</label>";
                            $markup .= "<li><input type='checkbox' id='browser' name='contact_form_fields[]' value='browser' checked><label for='browser'>".__('Browser', pwh_dcfh_hc()::TEXT_DOMAIN)."</label></li>";
                            $markup .= "<li><input type='checkbox' id='platform' name='contact_form_fields[]' value='platform' checked><label for='platform'>".__('Platform', pwh_dcfh_hc()::TEXT_DOMAIN)."</label></li>";
                            $markup .= "</ul>";
                            $cached_data[$contact_form_id] = [
                                'contact_form_id' => $contact_form_id,
                                'contact_form_title' => $contact_form_title,
                                'contact_form_fields' => $fields,
                                'contact_form_fields_html' => $markup,
                                'contact_form_total_entries' => number_format_i18n($form_meta['total_entries'])
                            ];
                            wp_cache_set($key, $cached_data, 'pwh_dcfh_cached_queries', 2 * MINUTE_IN_SECONDS);
                        }
                    }
                }
            }

            return $cached_data;
        }

        /**
         * Get Form Fields
         *
         * @return false|mixed
         */
        public static function get_contact_form_template_data()
        {
            $key = 'pwh_dcfh_contact_form_template_'.md5(__FUNCTION__);
            $cached_data = wp_cache_get($key);
            if (!$cached_data) {
                $contact_forms = self::get_contact_forms();
                if (!empty($contact_forms)) {
                    foreach ($contact_forms as $contact_form) {
                        $contact_form_id = $contact_form['ID'];
                        $contact_form_title = $contact_form['title'];
                        $contact_form_meta = self::get_contact_form_meta($contact_form_id);
                        if (!empty($contact_form_meta['fields'])) {
                            $fields = explode(',', $contact_form_meta['fields']);
                            $markup = "<ul>";
                            foreach ($fields as $field) {
                                $markup .= "<li class='keyword'>%%".$field."%%</li>";
                            }
                            $markup .= "<li class='keyword'>%%dcfh_post_title%%</li>";
                            $markup .= "<li class='keyword'>%%dcfh_post_url%%</li>";
                            $markup .= "<li class='keyword'>%%dcfh_site_name%%</li>";
                            $markup .= "<li class='keyword'>%%dcfh_site_url%%</li>";
                            $markup .= "<li class='keyword'>%%dcfh_admin_email%%</li>";
                            $markup .= '</ul>';
                            $cached_data[$contact_form_id] = [
                                'contact_form_id' => $contact_form_id,
                                'contact_form_title' => $contact_form_title,
                                'contact_form_fields' => $fields,
                                'contact_form_fields_html' => $markup,
                                'contact_form_total_entries' => number_format_i18n($contact_form_meta['total_entries'])
                            ];
                            wp_cache_set($key, $cached_data, 'pwh_dcfh_cached_queries', 2 * MINUTE_IN_SECONDS);
                        }
                    }
                }
            }

            return $cached_data;
        }

        /**
         * Get Send Email Keywords
         *
         * @param $post_id
         *
         * @return void
         */
        public static function get_contact_form_send_email_data($post_id)
        {
            $fields_string = pwh_dcfh_post_meta_handler()::get_contact_form_fields_meta_value($post_id);
            $markup = "<ul>";
            if (!empty($fields_string)) {
                $fields = explode(',', $fields_string);
                foreach ($fields as $field) {
                    $markup .= "<li class='keyword'>%%".$field."%%</li>";
                }
            }
            $markup .= "<li class='keyword'>%%dcfh_post_title%%</li>";
            $markup .= "<li class='keyword'>%%dcfh_post_url%%</li>";
            $markup .= "<li class='keyword'>%%dcfh_site_name%%</li>";
            $markup .= "<li class='keyword'>%%dcfh_site_url%%</li>";
            $markup .= "<li class='keyword'>%%dcfh_admin_email%%</li>";
            $markup .= '</ul>';
            echo force_balance_tags($markup);  // phpcs:ignore
        }

        /**
         * Global Email Keywords
         *
         * @param $post
         *
         * @return array
         */
        public static function get_send_email_static_keywords_values($post)
        {
            $post_id = $post->ID;
            $contact_form_id = pwh_dcfh_post_meta_handler()::get_contact_form_id_meta_value($post_id);
            $page_id = pwh_dcfh_post_meta_handler()::get_page_id_meta_value($post_id);

            return [
                '%%dcfh_site_name%%' => get_bloginfo('name'),
                '%%dcfh_site_url%%' => get_bloginfo('url'),
                '%%dcfh_admin_email%%' => get_bloginfo('admin_email'),
                '%%dcfh_post_title%%' => get_the_title($page_id),
                '%%dcfh_post_url%%' => get_the_permalink($page_id),
                '%%dcfh_date%%' => $post->post_date,
            ];
        }

        /**
         * Get Entry Stats From Database
         *
         * @return false|mixed
         */
        public static function get_contact_forms_dashboard_stats()
        {
            $dashboard_stats = get_transient('pwh_dcfh_dashboard_stats');
            // phpcs:disable
            if (false === $dashboard_stats) {
                global $wpdb;
                $results = $wpdb->get_results("
           SELECT CASE
           WHEN DATE(wpp.post_date) = CURDATE() THEN 'today'
           WHEN DATE(wpp.post_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 'yesterday'
           WHEN DATE(wpp.post_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE() THEN 'last_week'
           WHEN DATE(wpp.post_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE() THEN 'last_month'
           ELSE 'other' END title,
           COUNT(wpp.id) AS total_posts,
           wppm.meta_value AS contact_form_id
           FROM ".$wpdb->posts." AS wpp
           LEFT JOIN ".$wpdb->postmeta." wppm ON (wppm.post_id = wpp.id)
           WHERE Date(wpp.post_date) BETWEEN Date_sub(CURDATE(), INTERVAL 1 MONTH) 
           AND CURDATE()
           AND post_type = '".pwh_dcfh_hc()::POST_TYPE."'
           AND wppm.meta_key = '".pwh_dcfh_hc()::CF_FORM_ID_META_KEY."'
           GROUP BY CASE
                        WHEN DATE(wpp.post_date) = CURDATE() THEN 0
                        WHEN DATE(wpp.post_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1
                        WHEN DATE(wpp.post_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE() THEN 7
                        WHEN DATE(wpp.post_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE() THEN 30
                        ELSE -1
                        END,
                    wppm.meta_value
           ORDER BY wppm.meta_value");
                if (!empty($results)) {
                    foreach ($results as $result) {
                        $dashboard_stats[pwh_dcfh_db_handler()::get_contact_form_title($result->contact_form_id)][$result->title] = $result->total_posts;
                    }
                    set_transient('pwh_dcfh_dashboard_stats', $dashboard_stats, 2 * MINUTE_IN_SECONDS);
                }
            }

            // phpcs:enable
            return $dashboard_stats;
        }

        /**
         * @param $contact_form_id
         * @param $ip_address
         *
         * @return bool
         */
        private static function is_user_ip_exists($contact_form_id, $ip_address)
        {
            global $wpdb;
            // phpcs:disable
            $row = $wpdb->get_row("
                                            SELECT 
                                           (SELECT COUNT(*) FROM $wpdb->postmeta  WHERE meta_key = '".pwh_dcfh_hc()::CF_FORM_ID_META_KEY."' AND meta_value = '".$contact_form_id."') contact_form_id ,
                                           (SELECT COUNT(*) FROM $wpdb->postmeta  WHERE meta_key = '".pwh_dcfh_hc()::CF_IP_ADDRESS_META_KEY."' AND meta_value = '".$ip_address."') ip_address 
                                           FROM $wpdb->postmeta LIMIT 1
                                           ");
            // phpcs:enable
            if ($row->contact_form_id > 0 && $row->ip_address > 0) {
                return true;
            }

            return false;
        }

        /**
         * Get Form Names From Database
         *
         * @return array|false|mixed
         */
        public static function get_contact_forms_old()
        {

            $key = 'pwh_dcfh_form_'.md5(__FUNCTION__);
            $contact_forms = wp_cache_get($key);
            if (false === $contact_forms) {
                global $wpdb;
                $_ids = [];
                // Get Forms
                $contact_forms = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key LIKE %s", pwh_dcfh_hc()::CF_FORM_ID_META_KEY)); //db call ok; no-cache ok
                if (!empty($contact_forms)) {
                    foreach ($contact_forms as $contact_form) {
                        $title_option_name = $option_name = self::$contact_form_title_option.$contact_form->meta_value;
                        $_ids[$title_option_name] = $contact_form->meta_value;
                        $contact_form->meta_title = $contact_form->meta_value;
                    }
                    // Get Form Titles
                    $impload_ids = "'".implode("','", array_keys($_ids))."'";
                    $_titles = $wpdb->get_results("SELECT option_name,option_value FROM $wpdb->options WHERE option_name IN ($impload_ids)");// phpcs:ignore
                    foreach ($_titles as $_title) {
                        if (isset($_ids[$_title->option_name])) {
                            $_ids[$_title->option_name] = $_title->option_value;
                        }
                    }
                    // Rebuild Forms
                    foreach ($contact_forms as $contact_form) {
                        $title_option_name = $option_name = self::$contact_form_title_option.$contact_form->meta_value;
                        if (isset($_ids[$title_option_name])) {
                            $contact_form->meta_title = $_ids[$title_option_name];
                        }
                    }
                    wp_cache_set($key, $contact_forms, 'pwh_dcfh_cached_queries', 2 * MINUTE_IN_SECONDS);
                }
            }

            return $contact_forms;
        }
    }
}