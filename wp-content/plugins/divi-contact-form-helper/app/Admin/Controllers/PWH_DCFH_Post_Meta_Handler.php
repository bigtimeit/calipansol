<?php

namespace PWH_DCFH\App\Admin\Controllers;
if (!class_exists('PWH_DCFH_Post_Meta_Handler')) {
    class PWH_DCFH_Post_Meta_Handler
    {

        private static $_instance;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Post_Meta_Handler
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Get Post Meta Value
         *
         * @param $post_id
         * @param $meta_key
         * @param bool $single
         *
         * @return mixed
         */
        public static function get_post_meta($post_id, $meta_key, $single = true)
        {
            return get_post_meta($post_id, $meta_key, $single);
        }

        /**
         * Get Page ID From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_page_id_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::CF_PAGE_ID_META_KEY);
        }

        /**
         * Get Form Name From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_contact_form_id_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::CF_FORM_ID_META_KEY);
        }

        /**
         * Get Form Fields From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_contact_form_fields_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::CF_FIELDS_META_KEY);
        }

        /**
         * Get User Email From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_contact_email_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::CF_CONTACT_EMAIL_META_KEY);
        }

        /**
         * Get IP Address From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_ip_address_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::CF_IP_ADDRESS_META_KEY);
        }

        /**
         * Get User Agent From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_user_agent_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::CF_USER_AGEN_META_KEY);
        }

        /**
         * Get Referer URL From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_referer_url_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::CF_REFERER_URL_META_KEY);
        }

        /**
         * Get Replies History From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_replies_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::EMAIL_SENT_LOGS_META_KEY);
        }

        /**
         * Get Clones History From Meta
         *
         * @param $post_id
         *
         * @return mixed
         */
        public static function get_clones_meta_value($post_id)
        {
            return self::get_post_meta($post_id, pwh_dcfh_hc()::ENTRY_CLONE_LOG_META_KEY);
        }

        /**
         * Update Replies History
         *
         * @param $post_id
         * @param $email_from
         * @param $email_to
         */
        public static function update_email_replies_history($post_id, $email_from, $email_to)
        {
            if (!pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_enable_sent_email_log')) {
                return;
            }
            $old_meta = self::get_replies_meta_value($post_id);
            if (empty($old_meta)) {
                $old_meta = [];
            }
            $new_meta = [
                'author' => get_current_user_id(),
                'date' => current_time('mysql'),
                'email_from' => $email_from,
                'email_to' => $email_to,
            ];
            array_push($old_meta, $new_meta);
            update_post_meta($post_id, pwh_dcfh_hc()::EMAIL_SENT_LOGS_META_KEY, $old_meta);
        }

        /**
         * Upldate Cloned History
         *
         * @param $post_id
         * @param $cloned_post_id
         */
        public static function update_cloned_history($post_id, $cloned_post_id)
        {
            if (!pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_enable_clone_log')) {
                return;
            }
            $old_meta = self::get_clones_meta_value($post_id);
            if (empty($old_meta)) {
                $old_meta = [];
            }
            $new_meta = [
                'post_id' => $cloned_post_id,
                'author' => get_current_user_id(),
                'date' => current_time('mysql'),
            ];
            array_push($old_meta, $new_meta);
            update_post_meta($post_id, pwh_dcfh_hc()::ENTRY_CLONE_LOG_META_KEY, $old_meta);
        }

        /**
         * Get Post Type Terms
         */
        public function get_post_type_terms()
        {
            if (isset($_REQUEST['_wpnonce']) && !empty($_REQUEST['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_REQUEST['_wpnonce']);
                if (wp_verify_nonce($_wpnonce, 'admin-ajax-nonce')) {
                    $post_type = isset($_REQUEST['post_type']) ? sanitize_text_field($_REQUEST['post_type']) : null;
                    if ('' !== $post_type) {
                        if ($post_type !== 'page') {
                            $taxonomies = get_object_taxonomies($post_type);
                            $taxonomies_new = [];
                            foreach ($taxonomies as $taxonomy) {
                                if (!strpos($taxonomy, 'tag')) {
                                    $taxonomies_new[] = $taxonomy;
                                }
                            }
                            $terms = get_terms([
                                'taxonomy' => $taxonomies_new,
                                'hide_empty' => false,
                            ]);
                            if (!is_wp_error($terms)) {
                                $markup = "";
                                foreach ($terms as $term) {
                                    $markup .= "<option value=\"$term->term_id\">".$term->name."</option>";
                                }
                                wp_send_json_success($markup);
                                wp_die();
                            }
                        } else {
                            wp_send_json_error('No data');
                            wp_die();
                        }
                    }
                } else {
                    wp_send_json_error('Nope! Security check failed!', '400');
                    wp_die();
                }
            }
        }

        /**
         * Get Post Meta Keys
         */
        public function get_post_meta_keys()
        {
            if (isset($_REQUEST['_wpnonce']) && !empty($_REQUEST['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_REQUEST['_wpnonce']);
                if (wp_verify_nonce($_wpnonce, 'admin-ajax-nonce')) {
                    $search_meta = isset($_REQUEST['search_meta']) ? sanitize_text_field($_REQUEST['search_meta']) : null;
                    if ('' !== $search_meta) {
                        $list = [];
                        global $wpdb;
                        // phpcs:ignore
                        $meta_keys = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT meta_key FROM $wpdb->postmeta WHERE meta_key LIKE %s AND meta_key NOT LIKE '%_pwh_dcfh_%' AND meta_key NOT LIKE '%_edit%' AND meta_key NOT LIKE '%_menu%' AND meta_key NOT LIKE '%_et_%' AND meta_key NOT LIKE '%_thumbnail%'",
                            '%'.$wpdb->esc_like($search_meta).'%'));
                        if (!empty($meta_keys)) {
                            foreach ($meta_keys as $meta_key) {
                                $list[] = $meta_key->meta_key;
                            }
                        }
                        array_filter($list, 'strlen');
                        wp_send_json_success($list);
                    }
                }
            }
        }

    }
}