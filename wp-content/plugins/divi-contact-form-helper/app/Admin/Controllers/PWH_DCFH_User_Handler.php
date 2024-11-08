<?php

namespace PWH_DCFH\App\Admin\Controllers;
if (!class_exists('PWH_DCFH_User_Handler')) {
    class PWH_DCFH_User_Handler
    {

        private static $_instance;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_User_Handler
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Get Submitter Emails From Database To Send Email Reply
         *
         * @param $post_id
         *
         * @return array
         */
        public static function get_user_emails($post_id)
        {
            $post_excerpt = get_post_field('post_excerpt', $post_id);
            $post_author = get_post_field('post_author', $post_id);
            $emails = [];

            if (is_serialized($post_excerpt)) {
                $pattern = '/[a-z0-9_\-+.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
                preg_match_all($pattern, $post_excerpt, $matches);
                if (isset($matches[0])) {
                    foreach ($matches[0] as $match) {
                        $emails[$match] = __('Entry Email ', pwh_dcfh_hc()::TEXT_DOMAIN).'<'.$match.'>';
                    }
                }
            }
            if ($post_author > 0) {
                $user_data = get_user_by('ID', $post_author);
                if (isset($user_data->data->user_email)) {
                    $emails[$user_data->data->user_email] = __('Profile Email ', pwh_dcfh_hc()::TEXT_DOMAIN).'<'.$user_data->data->user_email.'>';
                }
            }

            return $emails;
        }

        /**
         * Get Submitter Email To Send Email When Entry Cloned
         *
         * @return false|mixed|null
         */
        public static function get_post_user_email($post_id)
        {
            $post_excerpt = get_post_field('post_excerpt', $post_id);
            $post_author = get_post_field('post_author', $post_id);
            if ($post_author > 0) {
                $user_data = get_user_by('ID', $post_author);
                if (isset($user_data->data->user_email)) {
                    return $user_data->data->user_email;
                }
            }
            if (is_serialized($post_excerpt)) {
                // $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
                $pattern = '/[a-z0-9_\-+.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
                preg_match_all($pattern, $post_excerpt, $matches);
                if (isset($matches[0])) {
                    $email = reset($matches[0]);
                    if (is_email(esc_html($email))) {
                        return $email;
                    }
                }
            }

            return '';
        }

        /**
         * Check is user email exits in database
         *
         * @param $post_id
         *
         * @return bool
         */
        public static function is_user_email_exist($post_id)
        {
            $post_meta = pwh_dcfh_post_meta_handler()::get_contact_email_meta_value($post_id);
            $post_author = get_post_field('post_author', $post_id);
            if (!empty($post_meta)) {
                return true;
            } elseif ($post_author > 0) {
                $user_data = get_user_by('ID', $post_author);
                if (isset($user_data->data->user_email)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Check User Other Entries Exist
         *
         * @param $post_id
         * @param $user_email
         * @param $ip_address
         *
         * @return bool
         */
        public static function is_user_other_entry_exist($post_id, $user_email, $ip_address)
        {
            global $wpdb;
            $result = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->postmeta WHERE (meta_key = %s AND meta_value LIKE %s AND post_id != %d) OR (meta_key = %s AND meta_value LIKE %s AND post_id != %d)",
                pwh_dcfh_hc()::CF_CONTACT_EMAIL_META_KEY, $user_email, $post_id, pwh_dcfh_hc()::CF_IP_ADDRESS_META_KEY, $ip_address, $post_id)); //db call ok; no-cache ok
            if ($result > 0 && !is_wp_error($result)) {
                return true;
            }

            return false;
        }

    }
}