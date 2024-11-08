<?php

namespace PWH_DCFH\App\Admin\CPT;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Contact_Form_Entries')) {
    class PWH_DCFH_Contact_Form_Entries
    {

        private $_post_type;

        private $_post_type_singular;

        private $_post_type_plural;

        private $_pagenow;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            global $pagenow;
            $this->_pagenow = $pagenow;
            $this->_post_type = pwh_dcfh_hc()::POST_TYPE;
            $this->_post_type_singular = _x('Entry', 'post type singular name', pwh_dcfh_hc()::TEXT_DOMAIN);
            $this->_post_type_plural = _x('Entries', 'post type plural name', pwh_dcfh_hc()::TEXT_DOMAIN);
            add_action('init', [$this, 'register_post_type']);
            // Columns
            add_filter("manage_{$this->_post_type}_posts_columns", [$this, 'maybe_column_header']);
            add_action("manage_{$this->_post_type}_posts_custom_column", [$this, 'maybe_column_content'], 10, 2);
            // Change Post Status Label
            add_filter("views_edit-$this->_post_type", [$this, 'maybe_change_status_label'], 10, 1);
            // Update Status When View
            add_action('load-post.php', [$this, 'maybe_update_entry_read_by']);
            add_filter('wp_insert_post_data', [$this, 'maybe_reset_post_modified_date'], 99, 2);
            // Register Bulk Action
            add_filter("bulk_actions-edit-$this->_post_type", [$this, 'maybe_bulk_action']);
            add_action("handle_bulk_actions-edit-$this->_post_type", [$this, 'maybe_handle_bulk_actions'], 10, 3);
            // Post Row Actions
            add_filter('post_row_actions', [$this, 'maybe_post_row_actions'], 10, 2);
            add_action('admin_init', [$this, 'maybe_handle_single_action']);
            // Action Messages
            add_action('admin_notices', [$this, 'maybe_custom_bulk_action_message']);
            add_filter('bulk_post_updated_messages', [$this, 'maybe_bulk_update_message'], 10, 2);
            // Page Title
            add_filter('admin_title', [$this, 'maye_change_admin_title'], 10, 2);
            add_action('admin_head', [$this, 'maye_change_post_title']);
            // Admin Modifications Hooks
            add_action('admin_init', [$this, 'maybe_deregister_script']);
            // Pending Label
            add_action('admin_menu', [$this, 'maybe_menu_hooks']);
            // Register Sortable & Filter
            add_filter("manage_edit-{$this->_post_type}_sortable_columns", [$this, 'maybe_sortable_columns']);
            add_filter('parse_query', [$this, 'maybe_parse_query']);
            add_action('restrict_manage_posts', [$this, 'maybe_manage_posts'], 10, 2);
            // Extra Tab Nav
            add_action('manage_posts_extra_tablenav', [$this, 'maybe_extra_table_nav'], 20, 1);
            // Remove Screen Options
            add_filter('screen_options_show_screen', [$this, 'maybe_remove_screen_options']);
            // On Delete
            add_action('before_delete_post', [$this, 'maybe_before_delete']);
        }

        /**
         * Register Custom Post Type
         */
        public function register_post_type()
        {
            $post_arguments = [
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => false,
                'capability_type' => 'post',
                'capabilities' => ['create_posts' => 'do_not_allow'],
                'map_meta_cap' => true,
                'has_archive' => false,
                'hierarchical' => false,
                'can_export' => true,
                'show_in_rest' => false,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => true,
                'menu_position' => 20,
                'menu_icon' => 'dashicons-email',
                'supports' => ['author'],
                'labels' => [
                    'name' => $this->_post_type_plural,
                    'singular_name' => $this->_post_type_singular,
                    'edit_item' => __('Entry Details', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'all_items' => sprintf('%s', $this->_post_type_plural),
                    'items_list' => sprintf(__('%s list', pwh_dcfh_hc()::TEXT_DOMAIN), $this->_post_type_plural),
                    'not_found' => sprintf(__('No %s found.', pwh_dcfh_hc()::TEXT_DOMAIN), $this->_post_type_plural),
                    'not_found_in_trash' => sprintf(__('No %s found in Trash.', pwh_dcfh_hc()::TEXT_DOMAIN), $this->_post_type_plural),
                    'search_items' => sprintf(__('Search %s', pwh_dcfh_hc()::TEXT_DOMAIN), $this->_post_type_plural),
                    'view_item' => sprintf(__('View %s', pwh_dcfh_hc()::TEXT_DOMAIN), $this->_post_type_singular),
                    'view_items' => sprintf(__('View %s', pwh_dcfh_hc()::TEXT_DOMAIN), $this->_post_type_plural),
                    'menu_name' => apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'cf_menu_name', __('Contact Form', pwh_dcfh_hc()::TEXT_DOMAIN)),
                ]
            ];
            register_post_type($this->_post_type, $post_arguments);
        }

        /**
         * Register Custom Post Type Header Columns
         *
         * @param $columns
         *
         * @return array
         */
        public function maybe_column_header($columns)
        {
            unset($columns['date'], $columns['title'], $columns['author']);
            $columns['pwh_dcfh_entry_id'] = __('Entry No.', pwh_dcfh_hc()::TEXT_DOMAIN);
            $columns['pwh_dcfh_user_email'] = __('Email', pwh_dcfh_hc()::TEXT_DOMAIN);
            $columns['pwh_dcfh_entry_date'] = __('Date', pwh_dcfh_hc()::TEXT_DOMAIN);
            $columns['pwh_dcfh_entry_read'] = __('Read', pwh_dcfh_hc()::TEXT_DOMAIN);
            $columns['pwh_dcfh_contact_form_id'] = __('Contact Form', pwh_dcfh_hc()::TEXT_DOMAIN);
            $n_columns = [];
            $before = 'pwh_dcfh_entry_date';
            foreach ($columns as $key => $value) {
                if ($key == $before) {
                    $n_columns['pwh_dcfh_entry_read'] = '';
                    $n_columns['pwh_dcfh_contact_form_id'] = '';
                }
                $n_columns[$key] = $value;
            }
            $n_columns['pwh_dcfh_entry_ip'] = __('IP', pwh_dcfh_hc()::TEXT_DOMAIN);

            return $n_columns;
        }

        /**
         * Register Custom Post Type Header Columns Content
         *
         * @param $column_name
         * @param $post_id
         */
        public function maybe_column_content($column_name, $post_id)
        {
            $contact_email = pwh_dcfh_post_meta_handler()::get_contact_email_meta_value($post_id);
            $post_date = get_post_field('post_date', $post_id);
            $post_author = get_post_field('post_author', $post_id);
            if (!empty($contact_email)) {
                $contact_email = make_clickable($contact_email);
            } elseif ($post_author > 0) {
                $user_data = get_user_by('ID', $post_author);
                if (isset($user_data->data->user_email)) {
                    $contact_email = make_clickable($user_data->data->user_email);
                }
            } else {
                $contact_email = __('No Email', pwh_dcfh_hc()::TEXT_DOMAIN);
            }
            $contact_form_id = pwh_dcfh_post_meta_handler()::get_contact_form_id_meta_value($post_id);
            $ip_address = pwh_dcfh_post_meta_handler()::get_ip_address_meta_value($post_id);
            $ip_address = !empty($ip_address) ? $ip_address : '-';
            switch ($column_name) {
                case 'pwh_dcfh_entry_id':
                    echo sprintf(__('<a href="%s">Entry# %d</a>', pwh_dcfh_hc()::TEXT_DOMAIN), esc_url(get_edit_post_link($post_id)), $post_id); // phpcs:ignore
                    break;
                case 'pwh_dcfh_user_email':
                    echo $contact_email; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    break;
                case 'pwh_dcfh_contact_form_id':
                    echo esc_html(pwh_dcfh_db_handler()::get_contact_form_title($contact_form_id));
                    break;
                case 'pwh_dcfh_entry_date':
                    echo esc_html(pwh_dcfh_helpers()::date_time($post_date));
                    break;
                case 'pwh_dcfh_entry_read':
                    echo self::is_read($post_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    break;
                case 'pwh_dcfh_entry_ip':
                    echo esc_html($ip_address);
                    break;
                default:
                    break;
            }
        }

        /**
         * Change Custom Post Type Status Text
         *
         * @param $views
         *
         * @return mixed
         */
        public function maybe_change_status_label($views)
        {
            if (isset($views['mine'])) {
                unset($views['mine']);
            }
            if (isset($views['private'])) {
                $views['private'] = str_replace('Private', __('Read', pwh_dcfh_hc()::TEXT_DOMAIN), $views['private']);
            }
            if (isset($views['draft'])) {
                $views['draft'] = str_replace(['Draft', 'Drafts'], __('Unread', pwh_dcfh_hc()::TEXT_DOMAIN), $views['draft']);
            }

            return $views;
        }

        /**
         * Update Custom Post Type Read By When Post View
         */
        public function maybe_update_entry_read_by()
        {
            global $current_screen;
            if (is_admin() && $this->_post_type === $current_screen->post_type) {
                $post_id = isset($_GET['post']) ? (int)sanitize_text_field($_GET['post']) : null; // phpcs:ignore WordPress.Security.NonceVerification
                if (!is_null($post_id) && get_post_status($post_id) === 'draft') {
                    $post = get_post($post_id);
                    if ('0000-00-00 00:00:00' === $post->post_modified) {
                        wp_update_post([
                            'ID' => $post_id,
                            'post_status' => 'private',
                            'post_modified' => current_time('mysql'),
                            'post_modified_gmt' => current_time('mysql', 1),
                            'meta_input' => [pwh_dcfh_hc()::CF_READ_BY_META_KEY => get_current_user_id()],
                        ]);
                    }
                }
            }
        }

        /**
         * Reset Custom Post Type Modified Date
         *
         * @param $data
         * @param $postarr
         *
         * @return mixed
         */
        public function maybe_reset_post_modified_date($data, $postarr)
        {
            $post_id = isset($postarr['ID']) ? absint($postarr['ID']) : 0;
            if ($post_id > 0 && $this->_post_type === get_post_type($post_id)) {
                $post = get_post($post_id);
                $data['post_date'] = $post->post_date;
                $data['post_date_gmt'] = $post->post_date;
                if ('0000-00-00 00:00:00' !== $post->post_modified) {
                    $data['post_modified'] = $post->post_modified;
                    $data['post_modified_gmt'] = $post->post_modified_gmt;
                }
            }

            return $data;
        }

        /**
         * Register Custom Post Typee Bulk Actions
         *
         * @param $bulk_actions
         *
         * @return mixed
         */
        public function maybe_bulk_action($bulk_actions)
        {
            if (isset($_GET['post_status']) && 'trash' === sanitize_text_field($_GET['post_status'])) {  // phpcs:ignore WordPress.Security.NonceVerification
                return $bulk_actions;
            }
            unset($bulk_actions['edit']);
            $bulk_actions['mark_read_all'] = __('Mark as Read', pwh_dcfh_hc()::TEXT_DOMAIN);
            $bulk_actions['delete_permanently'] = __('Delete Permanently', pwh_dcfh_hc()::TEXT_DOMAIN);

            return $bulk_actions;
        }

        /**
         * Register Custom Post Type Bulk Actions Process
         *
         * @param $redirect_url
         * @param $doaction
         * @param $post_ids
         *
         * @return false|string
         */
        public function maybe_handle_bulk_actions($redirect_url, $doaction, $post_ids)
        {
            $redirect = remove_query_arg(['mark_read_all', 'delete_permanently'], $redirect_url);
            if ('mark_read_all' === $doaction) {
                foreach ($post_ids as $post_id) {
                    if (get_post_status($post_id) === 'draft') {
                        wp_update_post([
                            'ID' => $post_id,
                            'post_status' => 'private',
                            'post_modified' => current_time('mysql'),
                            'post_modified_gmt' => current_time('mysql', 1),
                        ]);
                    }
                }
                $redirect = add_query_arg('mark_read_all', count($post_ids), $redirect);
            }
            if ('delete_permanently' === $doaction) {
                foreach ($post_ids as $post_id) {
                    wp_delete_post($post_id, true);
                }
                $redirect = add_query_arg('delete_permanently', count($post_ids), $redirect);
            }

            return $redirect;
        }

        /**
         * Register Custom Post Type Single Action Process
         */
        public function maybe_handle_single_action()
        {
            if (isset($_GET['_wpnonce']) && isset($_GET['delete_entry'])) {
                if (current_user_can('delete_posts')) {
                    if (wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'delete_entry')) {
                        $post_id = sanitize_text_field($_GET['delete_entry']);
                        $post_obj = get_post($post_id);
                        if (is_object($post_obj) || !is_wp_error($post_obj)) {
                            wp_delete_post($post_id, true);
                            wp_safe_redirect(admin_url('edit.php?post_type='.$this->_post_type));
                            exit;
                        }
                    }
                }
            }
        }

        /**
         * Register Custom Post Type Bulk Action Messages
         */
        public function maybe_custom_bulk_action_message()
        {
            $singular = strtolower($this->_post_type_singular);
            $plural = strtolower($this->_post_type_plural);
            // phpcs:disable
            if (isset($_GET['mark_read_all'])) {
                $action = sanitize_text_field($_GET['mark_read_all']);
                $message = sprintf(_n("%s $singular marked as read.", "%s $plural marked as read", $action), number_format_i18n($action));
                echo "<div class='updated'><p>".esc_html($message)."</p></div>";
            }
            if (isset($_GET['delete_permanently'])) {
                $action = sanitize_text_field($_GET['delete_permanently']);
                $message = sprintf(_n("%s $singular permanently deleted.", "%s $plural permanently deleted.", $action), number_format_i18n($action));
                echo "<div class='updated'><p>".esc_html($message)."</p></div>";
            }
            // phpcs:enable
        }

        /**
         * Resgiter Custom Post Type Bulk Action Update Messages
         *
         * @param $bulk_messages
         * @param $bulk_counts
         *
         * @return mixed
         */
        public function maybe_bulk_update_message($bulk_messages, $bulk_counts)
        {
            $singular = strtolower($this->_post_type_singular);
            $plural = strtolower($this->_post_type_plural);
            $bulk_messages[$this->_post_type] = [
                'updated' => _n("%s $singular updated.", "%s $plural updated.", $bulk_counts["updated"]),
                'locked' => _n("%s $singular not updated, somebody is editing it.", "%s $plural not updated, somebody is editing them.", $bulk_counts["locked"]),
                'deleted' => _n("%s $singular permanently deleted.", "%s $plural permanently deleted.", $bulk_counts["deleted"]),
                'trashed' => _n("%s $singular moved to the Trash.", "%s $plural moved to the Trash.", $bulk_counts["trashed"]),
                'untrashed' => _n("%s $singular restored from the Trash.", "%s $plural restored from the Trash.", $bulk_counts["untrashed"]),
            ];

            return $bulk_messages;
        }

        /**
         * Register Custom Post Type Row Action
         *
         * @param $actions
         * @param $post
         *
         * @return mixed
         */
        public function maybe_post_row_actions($actions, $post)
        {
            if (isset($_GET['post_status']) && 'trash' === sanitize_text_field($_GET['post_status'])) {   // phpcs:ignore WordPress.Security.NonceVerification
                return $actions;
            }
            if ($this->_post_type === get_post_type()) {
                unset($actions['inline hide-if-no-js']);
                if (isset($actions['edit'])) {
                    $actions['edit'] = str_replace('Edit', esc_html__('View', pwh_dcfh_hc()::TEXT_DOMAIN), $actions['edit']);
                }
                $url = esc_url(wp_nonce_url(add_query_arg('delete_entry', $post->ID), 'delete_entry'));
                $actions['delete-entry'] = sprintf('<a href="%1$s">%2$s</a>', $url, esc_html(__('Delete', pwh_dcfh_hc()::TEXT_DOMAIN)));
            }

            return $actions;
        }

        /**
         * Register Admin Title
         *
         * @return array|mixed|string|string[]|void
         */
        public function maye_change_admin_title($admin_title, $title)
        {
            global $post, $action, $current_screen;
            if (isset($current_screen->post_type) && $current_screen->post_type == pwh_dcfh_hc()::POST_TYPE && $action == 'edit') {
                $admin_title = self::clean_title($post->post_title); // phpcs:ignore
            }

            return $admin_title;
        }

        /**
         * Register Custom Post Type Admin Hooks
         */
        public function maye_change_post_title()
        {
            global $post, $title, $action, $current_screen;
            if (isset($current_screen->post_type) && $current_screen->post_type === $this->_post_type && $action == 'edit') {
                $title = self::clean_title($post->post_title); // phpcs:ignore
            }
        }

        /**
         * Deregister Custom Post Type Scripts Dragdrop
         */
        public function maybe_deregister_script()
        {
            if ('post.php' === $this->_pagenow && (isset($_GET['post']) && $this->_post_type === get_post_type(sanitize_text_field($_GET['post'])))) {  // phpcs:ignore WordPress.Security.NonceVerification
                remove_meta_box('submitdiv', $this->_post_type, 'side');
                wp_deregister_script('postbox');
                wp_dequeue_script('autosave');
            }
        }

        /**
         * Register Custom Post Type Menu New Entry Count
         */
        public function maybe_menu_hooks()
        {
            global $menu;
            $drafts = wp_count_posts(pwh_dcfh_hc()::POST_TYPE)->draft;
            if ($drafts > 0) {
                $menu[21][0] = $menu[21][0].' <span class="awaiting-mod">'.$drafts.'</span>';  // phpcs:ignore
            }
        }

        /**
         * Making Custom Post Type Columns Sortable
         *
         * @param $columns
         *
         * @return mixed
         */
        public function maybe_sortable_columns($columns)
        {
            $columns['pwh_dcfh_entry_id'] = 'pwh_dcfh_entry_id';
            $columns['pwh_dcfh_entry_date'] = 'pwh_dcfh_entry_date';
            $columns['pwh_dcfh_contact_form_id'] = 'pwh_dcfh_contact_form_id';
            $columns['pwh_dcfh_user_email'] = 'pwh_dcfh_user_email';

            return $columns;
        }

        /**
         * Filter Data From Custom Post Type Columns
         *
         * @param $query
         *
         * @return mixed
         */
        public function maybe_parse_query($query)
        {
            $q_post_type = isset($query->query['post_type']) ? $query->query['post_type'] : '';
            if (is_admin() && $this->_post_type === $q_post_type) {
                $contact_form_id = isset($_GET['contact_form_id']) ? sanitize_text_field($_GET['contact_form_id']) : null;  // phpcs:ignore WordPress.Security.NonceVerification
                if (!empty($contact_form_id)) {
                    $query->set('meta_key', pwh_dcfh_hc()::CF_FORM_ID_META_KEY);
                    $query->set('meta_value', $contact_form_id);
                    $query->set('meta_type', 'meta_value');
                }
                if ('pwh_dcfh_contact_form_id' === $query->get('orderby')) {
                    $query->set('orderby', 'meta_value');
                    $query->set('meta_key', pwh_dcfh_hc()::CF_FORM_ID_META_KEY);
                    $query->set('meta_type', 'meta_value');
                }
                if ('pwh_dcfh_user_email' === $query->get('orderby')) {
                    $query->set('orderby', 'meta_value');
                    $query->set('meta_key', pwh_dcfh_hc()::CF_CONTACT_EMAIL_META_KEY);
                }
            }

            return $query;
        }

        /**
         * Register Custom Post Type Filters
         *
         * @param $post_type
         * @param $which
         */
        public function maybe_manage_posts($post_type, $which)
        {
            if (pwh_dcfh_hc()::POST_TYPE === $post_type) {
                global $wpdb;
                $contact_forms = pwh_dcfh_db_handler()::get_contact_forms();
                $selected_contact_form_id = isset($_GET['contact_form_id']) ? sanitize_text_field($_GET['contact_form_id']) : null; // phpcs:ignore WordPress.Security.NonceVerification
                echo "<select name='contact_form_id' id='contact_form_id' class='postform'>";
                echo "<option value=''>".esc_html__('Contact Forms', pwh_dcfh_hc()::TEXT_DOMAIN)."</option>";
                if (!empty($contact_forms)) {
                    foreach ($contact_forms as $contact_form) {
                        $contact_form_id = $contact_form['ID'];
                        $contact_form_title = $contact_form['title'];
                        echo '<option value="'.esc_attr($contact_form_id).'" '.selected($selected_contact_form_id, esc_html($contact_form_id), false).'>'.esc_html($contact_form_title).'</option>';
                    }
                }
                echo "</select>";
            }
        }

        /**
         * Register Export CSV Button On Custom Post Type
         *
         * @param $which
         *
         * @return mixed|string
         */
        public function maybe_extra_table_nav($which)
        {
            global $current_screen;
            if ('top' === $which) {
                if (is_admin() && $this->_post_type === $current_screen->post_type) {
                    $wp_count_posts = wp_count_posts($this->_post_type);
                    $total_posts = intval($wp_count_posts->draft + $wp_count_posts->private);
                    if (0 === $total_posts) {
                        return $which;
                    }
                    $contact_form_id = isset($_GET['contact_form_id']) ? sanitize_text_field($_GET['contact_form_id']) : ''; // phpcs:ignore WordPress.Security.NonceVerification
                    $query_args = ['post_type' => $this->_post_type, 'page' => 'export_as_csv'];
                    if (!empty($contact_form_id)) {
                        $query_args['contact_form_id'] = $contact_form_id;
                    }
                    $csv_export_url = add_query_arg($query_args, admin_url('edit.php'));
                    printf(sprintf('<a href="%1$s" class="button button-primary export-csv-btn">%2$s</a>', esc_url($csv_export_url), esc_html__('Export CSV', pwh_dcfh_hc()::TEXT_DOMAIN)));
                }
            }

            return $which;
        }

        /**
         * Get Custom Post Type Read Meta Key
         *
         * @param $post_id
         *
         * @return string
         */
        private static function is_read($post_id)
        {
            $post_modified = get_post_field('post_modified', $post_id);
            $markup = "";
            if ($post_modified === '0000-00-00 00:00:00') {
                $markup .= "<span class='dashicons dashicons-email'></span>";
            } else {
                $post_read_by = pwh_dcfh_helpers()::get_author_name(get_post_meta($post_id, pwh_dcfh_hc()::CF_READ_BY_META_KEY, true));
                $post_modified = pwh_dcfh_helpers()::date_time($post_modified);
                $markup .= sprintf('<div><span>%1$s</span><span>%2$s</span></div>', esc_html__("Read By: $post_read_by", pwh_dcfh_hc()::TEXT_DOMAIN), $post_modified);
            }

            return force_balance_tags($markup); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        /**
         * Remove Screen Option
         *
         * @return bool
         */
        public function maybe_remove_screen_options()
        {
            if ('post.php' === $this->_pagenow && isset($_GET['post']) && $this->_post_type === get_post_type(sanitize_text_field($_GET['post']))) {  // phpcs:ignore WordPress.Security.NonceVerification
                return false;
            }

            return true;
        }

        /**
         * Delete records when post delete
         *
         * @param $post_id
         *
         * @return mixed
         */
        public function maybe_before_delete($post_id)
        {
            if (pwh_dcfh_hc()::POST_TYPE !== get_post_type($post_id)) {
                return $post_id;
            }
            $post_excerpt = get_post_field('post_excerpt', $post_id);
            if (is_serialized($post_excerpt)) {
                $form_entries = pwh_dcfh_helpers()::maybe_unserialize($post_excerpt);
                if (!empty($form_entries) && is_array($form_entries)) {
                    // Delete Attachments
                    $contact_form_id = pwh_dcfh_post_meta_handler()::get_contact_form_id_meta_value($post_id);
                    $tmp_upload_dir = pwh_dcfh_helpers()::get_temp_upload_dir();
                    foreach ($form_entries as $form_entry) {
                        if ('file' === $form_entry['type']) {
                            $files = explode(',', $form_entry['value']);
                            $subdir = isset($form_entry['subdir']) ? esc_html($form_entry['subdir']) : '';
                            if (!empty($files) && is_array($files) && !empty($subdir)) {
                                // Delete From Original Directory
                                foreach ($files as $file) {
                                    $upload_dir = pwh_dcfh_helpers()::get_form_upload_dir($contact_form_id, $subdir, $file);
                                    if (is_file($upload_dir) && file_exists($upload_dir)) {
                                        wp_delete_file($upload_dir);
                                    }
                                }
                                // Delete From Temp Directory
                                foreach ($files as $file) {
                                    $upload_dir = path_join($tmp_upload_dir, $file);
                                    if (is_file($upload_dir) && file_exists($upload_dir)) {
                                        wp_delete_file($upload_dir);
                                    }
                                }
                            }
                        }
                    }
                    // Delete Sent Email Logs
                    pwh_dcfh_logger()->delete_logs('sent_email_log', $post_id);
                }
            }

            return $post_id;
        }

        /**
         * Clean Post Title
         *
         * @param $title
         *
         * @return string
         */
        private static function clean_title($title)
        {
            $title = str_replace(['-'], '# ', $title);

            return sprintf(__("%s Details", pwh_dcfh_hc()::TEXT_DOMAIN), ucfirst($title));
        }

    }
}