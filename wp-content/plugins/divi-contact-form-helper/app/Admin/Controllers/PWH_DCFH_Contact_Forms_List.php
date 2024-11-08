<?php

namespace PWH_DCFH\App\Admin\Controllers;

use WP_List_Table;

if (!class_exists('PWH_DCFH_Contact_Forms_List')) {
    class PWH_DCFH_Contact_Forms_List extends WP_List_Table
    {

        public $_page;

        private $_post_type;

        /**
         * Initial Class Constructor
         *
         * @param array $args
         */
        public function __construct($args = [])
        {
            parent::__construct([
                'singular' => __('Contact Form', pwh_dcfh_hc()::TEXT_DOMAIN),
                'plural' => __('Contact Forms', pwh_dcfh_hc()::TEXT_DOMAIN),
                'ajax' => false
            ]);
            $this->_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : null; // phpcs:ignore
            $this->_post_type = pwh_dcfh_hc()::POST_TYPE;
        }

        /**
         * Prepare Table Items
         */
        public function prepare_items()
        {
            ## Columns
            $this->_column_headers = $this->get_column_info();
            ## Process bulk action
            $this->process_bulk_action();
            ## Get Record
            $record = $this->get_record();
            $data = isset($record['items']) ? $record['items'] : [];
            usort($data, [&$this, 'sort_data']);
            $this->items = $data;
        }

        /**
         * Get Records From Database
         *
         * @return array
         */
        public function get_record()
        {
            $record = [];
            $forms = self::get_contact_forms();
            if (!empty($forms)) {
                $total_items = 0;
                foreach ($forms as $form_id => $form) {
                    $total_items = $total_items + 1;
                    $record['items'][] = [
                        'ID' => $form_id,
                        'title' => $form['title'],
                        'entries' => $form['entries'],
                        'views' => $form['views'],
                        'unique_views' => $form['unique_views'],
                        'conversion' => $form['conversion'],
                        'unique_conversion' => $form['unique_conversion'],
                        'last_entry_date' => $form['last_entry_date'],
                        'last_post_id' => $form['last_post_id'],
                        'page_id' => $form['page_id'],
                    ];
                }
                $record['total_items'] = $total_items;
            }

            return $record;
        }

        /**
         * @return array[]
         */
        public function get_sortable_columns()
        {
            return [
                "title" => ["title", true],
                "entries" => ["entries", true],
                "views" => ["views", true],
                "unique_views" => ["unique_views", true],
                "conversion" => ["conversion", true],
                "unique_conversion" => ["unique_conversion", true],
                "last_entry_date" => ["last_entry_date", true],
            ];
        }

        /** Get Columns
         *
         * @return array
         */
        public function get_columns()
        {
            return [
                'cb' => '<input type="checkbox" />',
                'title' => __('ID/Title', pwh_dcfh_hc()::TEXT_DOMAIN),
                'entries' => __('Entries', pwh_dcfh_hc()::TEXT_DOMAIN),
                'views' => __('Views', pwh_dcfh_hc()::TEXT_DOMAIN),
                'unique_views' => __('Unique Views', pwh_dcfh_hc()::TEXT_DOMAIN),
                'conversion' => __('Conversion', pwh_dcfh_hc()::TEXT_DOMAIN),
                'unique_conversion' => __('Unique Conversion', pwh_dcfh_hc()::TEXT_DOMAIN),
                'last_entry_date' => __('Last Entry', pwh_dcfh_hc()::TEXT_DOMAIN),
                'actions_html' => __('Actions', pwh_dcfh_hc()::TEXT_DOMAIN),
            ];
        }

        /**
         * Set Default Columns
         *
         * @param array|object $item
         * @param string $column_name
         *
         * @return mixed|string
         */
        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'title':
                case 'entries':
                case 'views':
                case 'unique_views':
                case 'last_entry_date':
                    return $item[$column_name];
                case 'conversion':
                case 'unique_conversion':
                    return sprintf('%s%%', $item[$column_name]);
                case 'actions_html':
                    return $this->action_links($item);
                default:
                    return null;
            }
        }

        /**
         * Checkbox Column
         *
         * @param $item
         *
         * @return string
         */
        public function column_cb($item)
        {
            return sprintf('<input type="checkbox" name="bulk-action[]" value="%s">', $item['ID']);
        }

        /**
         * No Item Found
         *
         * @return void
         */
        public function no_items()
        {
            esc_attr_e('Oops! No Record Found In Database.', pwh_dcfh_hc()::TEXT_DOMAIN);
        }

        /**
         * Register Bulk Actions
         *
         * @return array
         */
        public function get_bulk_actions()
        {
            return [
                'bulk-delete-entries' => __('Delete Entries', pwh_dcfh_hc()::TEXT_DOMAIN),
                'bulk-reset-views' => __('Reset Views', pwh_dcfh_hc()::TEXT_DOMAIN),
                'bulk-delete-options' => __('Delele Options', pwh_dcfh_hc()::TEXT_DOMAIN)
            ];
        }

        /**
         * Process Bulk Actions
         *
         * @return void
         */
        public function process_bulk_action()
        {
            if (isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_GET['_wpnonce']);
                if (!wp_verify_nonce($_wpnonce, 'bulk-action-'.$this->_page)) {
                    wp_die('Security checks failed. Please try again later.');
                }
                $action = $this->current_action();
                if (!$action) {
                    return;
                }
                $bulk_actions = isset($_GET['bulk-action']) ? array_map('sanitize_text_field', $_GET['bulk-action']) : [];
                if (!empty($bulk_actions)) {
                    // Delete Entries
                    if ('bulk-delete-entries' === $action) {
                        global $wpdb;
                        $rows = [];
                        foreach ($bulk_actions as $bulk_action) {
                            $rows[$bulk_action] = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_value LIKE %s ORDER BY meta_id DESC", $bulk_action)); //db call ok; no-cache ok
                        }
                        if (!empty($rows)) {
                            foreach ($rows as $row) {
                                foreach ($row as $_post) {
                                    wp_delete_post($_post->post_id, true);
                                }
                            }
                        }
                    }
                    // Reset Views
                    if ('bulk-reset-views' === $action) {
                        foreach ($bulk_actions as $bulk_action) {
                            pwh_dcfh_db_handler()::reset_contact_form_views($bulk_action);
                        }
                    }
                    // Delete Options
                    if ('bulk-delete-options' === $action) {
                        foreach ($bulk_actions as $bulk_action) {
                            pwh_dcfh_db_handler()::delete_contact_form_options($bulk_action);
                        }
                    }
                }
            }
        }

        /**
         * Get Contact Form Data
         *
         * @return false|mixed
         */
        private static function get_contact_forms()
        {
            $key = 'pwh_dcfh_form_'.md5(__FUNCTION__);
            $contact_forms = wp_cache_get($key);
            if (false === $contact_forms) {
                global $wpdb;
                // $results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key LIKE %s", pwh_dcfh_hc()::CF_FORM_ID_META_KEY)); //db call ok; no-cache ok
                $results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT option_name,option_value FROM $wpdb->options WHERE option_name LIKE %s", pwh_dcfh_hc()::CF_TITLE_OPTION_NAME."%")); //db call ok; no-cache ok
                if (!empty($results)) {
                    foreach ($results as $result) {
                        $contact_form_id = str_replace(pwh_dcfh_hc()::CF_TITLE_OPTION_NAME, '', $result->option_name);
                        $contact_form_title = $result->option_value;
                        $contact_form_conversion = 0;
                        $contact_form_unique_conversion = 0;
                        $contact_form_views = pwh_dcfh_db_handler()::get_contact_form_views($contact_form_id);
                        $contact_form_unique_views = pwh_dcfh_db_handler()::get_contact_form_unique_views($contact_form_id);
                        $contact_form_entries = pwh_dcfh_db_handler()::get_contact_form_total_entries($contact_form_id);
                        $last_post_id = pwh_dcfh_db_handler()::get_contact_form_last_post_id($contact_form_id);
                        $contact_form_last_entry_date = pwh_dcfh_helpers()::date_time(get_post_field('post_date', $last_post_id));
                        $page_id = pwh_dcfh_post_meta_handler()::get_page_id_meta_value($last_post_id);
                        if ($contact_form_entries && !empty($contact_form_views)) {
                            $contact_form_conversion = round(($contact_form_entries / $contact_form_views) * 100, 2);
                        }
                        if ($contact_form_entries && !empty($contact_form_unique_views)) {
                            $contact_form_unique_conversion = round(($contact_form_entries / $contact_form_unique_views) * 100, 2);
                        }
                        $contact_forms[$contact_form_id]['ID'] = $contact_form_id;
                        $contact_forms[$contact_form_id]['title'] = $contact_form_title;
                        $contact_forms[$contact_form_id]['views'] = $contact_form_views;
                        $contact_forms[$contact_form_id]['unique_views'] = $contact_form_unique_views;
                        $contact_forms[$contact_form_id]['entries'] = $contact_form_entries;
                        $contact_forms[$contact_form_id]['conversion'] = $contact_form_conversion;
                        $contact_forms[$contact_form_id]['unique_conversion'] = $contact_form_unique_conversion;
                        $contact_forms[$contact_form_id]['last_entry_date'] = $contact_form_last_entry_date;
                        $contact_forms[$contact_form_id]['last_post_id'] = $last_post_id;
                        $contact_forms[$contact_form_id]['page_id'] = $page_id;
                    }
                    wp_cache_set($key, $contact_forms, 'pwh_dcfh_cached_queries', 2 * MINUTE_IN_SECONDS);
                }
            }

            return $contact_forms;
        }

        /**
         * Table Action Links
         *
         * @param $item
         *
         * @return string
         */
        private function action_links($item)
        {
            $contact_form_id = $item['ID'];
            $last_post_id = $item['last_post_id'];
            $page_id = $item['page_id'];
            $view_entries = add_query_arg(['post_type' => $this->_post_type, 'contact_form_id' => $contact_form_id], admin_url('edit.php'));
            $export_csv = add_query_arg(['post_type' => $this->_post_type, 'page' => 'export_as_csv', 'contact_form_id' => $contact_form_id], admin_url('edit.php'));
            $view_form = get_the_permalink($page_id);
            $output = '<div class="action-links">';
            $output .= sprintf(' <a href="%s">'.__('View Entries', pwh_dcfh_hc()::TEXT_DOMAIN).'</a>', esc_url($view_entries));
            $output .= sprintf('<a href="%s">'.__('Export CSV', pwh_dcfh_hc()::TEXT_DOMAIN).'</a>', esc_url($export_csv));
            $output .= sprintf('<a href="%s">'.__('View Form', pwh_dcfh_hc()::TEXT_DOMAIN).'</a>', esc_url($view_form));
            $output .= '</div>';

            return $output;
        }

        /**
         * Sort Table Data
         *
         * @param $a
         * @param $b
         *
         * @return float|int
         */
        private function sort_data($a, $b)
        {
            // Set defaults
            $orderby = 'title';
            $order = 'asc';
            // phpcs:disable
            if (isset($_GET['orderby']) && !empty($_GET['orderby'])) {
                $orderby = sanitize_text_field($_GET['orderby']);
            }
            if (isset($_GET['order']) && !empty($_GET['order'])) {
                $order = sanitize_text_field($_GET['order']);
            }
            // phpcs:enable
            $result = strcmp($a[$orderby], $b[$orderby]);
            if ($order === 'asc') {
                return $result;
            }

            return -$result;
        }
    }
}