<?php

namespace PWH_DCFH\App\Admin\CPT;

use DateTime;
use WP_Query;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Contact_Form_Metaboxes')) {
    class PWH_DCFH_Contact_Form_Metaboxes
    {

        private $_post_type;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            $post = isset($_GET['post']) ? sanitize_text_field($_GET['post']) : null;  // phpcs:ignore WordPress.Security.NonceVerification
            $this->_post_type = pwh_dcfh_hc()::POST_TYPE;
            add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
            if (get_post_type($post) === $this->_post_type) {
                add_action('edit_form_top', [$this, 'register_quick_action_buttons']);
                add_action('admin_notices', [$this, 'register_admin_notice']);
            }
        }

        /**
         * Quick Actions
         *
         * @param $post
         */
        public function register_quick_action_buttons($post)
        {
            if (pwh_dcfh_hc()::POST_TYPE === $post->post_type) {
                $post_id = $post->ID;
                echo '<div class="quick-actions">';
                printf(sprintf('<a href="%1$s" class="button button-primary trash">%2$s</a>', esc_url(get_delete_post_link($post_id)), esc_html__('Trash', pwh_dcfh_hc()::TEXT_DOMAIN)));
                printf(sprintf('<a href="%1$s" class="button button-primary delete" onclick="return confirm( \'%2$s\' );">%3$s</a>', esc_url(get_delete_post_link($post_id, '', true)),
                    esc_html__('Are you sure want to delete this entry permanently?', pwh_dcfh_hc()::TEXT_DOMAIN), esc_html__('Delete', pwh_dcfh_hc()::TEXT_DOMAIN)));
                echo '</div>';
            }
        }

        /**
         * Register Custom Post Type Meta Boxes
         */
        public function register_meta_boxes()
        {
            $mb_titles = [
                'mb_entry_detail' => __('Entry Details', pwh_dcfh_hc()::TEXT_DOMAIN),
                'mb_actions' => __('Actions', pwh_dcfh_hc()::TEXT_DOMAIN),
                'mb_user_entries' => __('Same User Entries', pwh_dcfh_hc()::TEXT_DOMAIN),
                'mb_email_logs' => __('Sent Email Logs', pwh_dcfh_hc()::TEXT_DOMAIN),
                'mb_clone_logs' => __('Entry Clone Logs', pwh_dcfh_hc()::TEXT_DOMAIN),
                'mb_meta_details' => __('Meta Details', pwh_dcfh_hc()::TEXT_DOMAIN),
                'mb_user_agent' => __('User-Agent Details', pwh_dcfh_hc()::TEXT_DOMAIN),
            ];
            $mb_titles = apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'metabox_titles', $mb_titles);
            add_meta_box('pwh-dcfh-contact-form-entry-metabox', $mb_titles['mb_entry_detail'], [$this, 'maybe_contact_form_entry'], $this->_post_type, 'normal', 'core');
            add_meta_box('pwh-dcfh-action-buttons-metabox', $mb_titles['mb_actions'], [$this, 'maybe_action_buttons'], $this->_post_type, 'side', 'core');
            add_meta_box('pwh-dcfh-user-other-entry-metabox', $mb_titles['mb_user_entries'], [$this, 'maybe_user_entries'], $this->_post_type, 'normal', 'core');
            if (pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_enable_sent_email_log')) {
                add_meta_box('pwh-dcfh-email-logs-metabox', $mb_titles['mb_email_logs'], [$this, 'maybe_email_logs'], $this->_post_type, 'normal', 'core');
            }
            if (pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_enable_clone_log')) {
                add_meta_box('pwh-dcfh-clone-logs-metabox', $mb_titles['mb_clone_logs'], [$this, 'maybe_clone_logs'], $this->_post_type, 'normal', 'core');
            }
            add_meta_box('pwh-dcfh-meta-details-metabox', $mb_titles['mb_meta_details'], [$this, 'maybe_form_meta'], $this->_post_type, 'side', 'core');
            add_meta_box('pwh-dcfh-user-agent-metabox', $mb_titles['mb_user_agent'], [$this, 'maybe_user_agent'], $this->_post_type, 'side', 'core');
        }

        /**
         * Register Display Contact Form Entry Meta Box
         *
         * @param $post
         */
        public function maybe_contact_form_entry($post)
        {
            $post_id = $post->ID;
            $post_excerpt = get_post_field('post_excerpt', $post_id);
            $markup = "";
            if (is_serialized($post_excerpt)) {
                $form_entries = pwh_dcfh_helpers()::maybe_unserialize($post_excerpt);
                if (!empty($form_entries) && is_array($form_entries)) {
                    $markup .= "<table>";
                    $markup .= "<tbody>";
                    $contact_form_id = pwh_dcfh_post_meta_handler()::get_contact_form_id_meta_value($post_id);
                    foreach ($form_entries as $form_entry) {
                        $images_list = '';
                        $files_list = '';
                        $field_type = $form_entry['type'];
                        $field_label = $form_entry['label'];
                        $field_value = '' !== $form_entry['value'] ? $form_entry['value'] : '-';
                        $markup .= "<tr>";
                        $markup .= sprintf('<th>%s:</th>', $field_label);
                        if ('text' === $field_type) {
                            $output = wpautop($field_value);
                        } elseif ('email' === $field_type || is_email($field_value)) {
                            $output = make_clickable($field_value);
                        } elseif ('file' === $field_type) {
                            $files = explode(',', $field_value);
                            if (!empty($files) && is_array($files)) {
                                foreach ($files as $file) {
                                    $subdir = isset($form_entry['subdir']) ? esc_html($form_entry['subdir']) : '';
                                    if (!empty($file) && !empty($subdir)) {
                                        $upload_dir = pwh_dcfh_helpers()::get_form_upload_dir($contact_form_id, $subdir, $file);
                                        $upload_url = pwh_dcfh_helpers()::get_form_upload_url($contact_form_id, $subdir, $file);
                                        $wp_check_filetype = wp_check_filetype($upload_dir);
                                        if (is_file($upload_dir) && file_exists($upload_dir)) {
                                            $file_mimetype = $wp_check_filetype['type'];
                                            $file_size = size_format(filesize($upload_dir));
                                            if (in_array($file_mimetype, pwh_dcfh_helpers()::get_image_mimes())) {
                                                $images_list .= '<li>';
                                                $images_list .= '<a href="'.esc_url($upload_url).'" target="_blank"><img src="'.esc_url($upload_url).'" alt="'.esc_attr($file).'"/></a>';
                                                $images_list .= sprintf('<span>%1$s: %2$s</span>', __('Size', pwh_dcfh_hc()::TEXT_DOMAIN), esc_html($file_size));
                                                $images_list .= '</li>';
                                            } else {
                                                $files_list .= '<li>';
                                                $files_list .= '<a href="'.esc_url($upload_url).'" target="_blank">'.esc_attr($file).'</a>';
                                                $files_list .= sprintf('<span>%1$s: %2$s</span>', __('Size', pwh_dcfh_hc()::TEXT_DOMAIN), esc_html($file_size));
                                                $files_list .= '</li>';
                                            }
                                        }
                                    }
                                }
                                $output = '';
                                if (!empty($images_list)) {
                                    $output .= '<ul class="images-list">'.$images_list.'</ul>';
                                }
                                if (!empty($files_list)) {
                                    $output .= '<ul class="files-list">'.$files_list.'</ul>';
                                }
                            }
                        } else {
                            $output = $field_value;
                        }
                        $markup .= sprintf('<td>%s</td>', $output);
                        $markup .= "</tr>";
                    }
                    $markup .= "</tbody>";
                    $markup .= "</table>";
                }
                echo force_balance_tags($markup);  // phpcs:ignore WordPress.Security.EscapeOutput
            }
        }

        /**
         * Register Display Entry Actions Meta Box
         *
         * @param $post
         */
        public function maybe_action_buttons($post)
        {
            $post_id = $post->ID;
            $markup = "";
            $menu_page = admin_url(pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG);
            $post_url = esc_url(add_query_arg(['post_id' => $post_id, 'page' => 'create_post'], $menu_page));
            $template_url = esc_url(add_query_arg(['post_id' => $post_id, 'page' => 'create_email_template'], $menu_page));
            $send_email_url = esc_url(add_query_arg(['post_id' => $post_id, 'page' => 'send_email'], $menu_page));
            $link_disabled = '';
            $link_target = '_blank';
            if (!pwh_dcfh_user_handler()::is_user_email_exist($post_id)) {
                $link_disabled = 'disabled';
                $link_target = '';
                $template_url = 'javascript:void(0)';
                $send_email_url = 'javascript:void(0)';
            }
            $markup .= sprintf('<a href="%1$s" class="button button-large" '.esc_attr($link_disabled).'>%2$s</a>', $send_email_url, __('Reply/Send Email', pwh_dcfh_hc()::TEXT_DOMAIN));
            $markup .= sprintf('<a href="%1$s" class="button button-large" target="'.esc_attr($link_target).'" '.esc_attr($link_disabled).'>%2$s</a>', $template_url,
                __('Create Email Template', pwh_dcfh_hc()::TEXT_DOMAIN));
            $markup .= sprintf('<a href="%1$s" class="button button-large">%2$s</a>', $post_url, __('Create Post', pwh_dcfh_hc()::TEXT_DOMAIN));
            echo force_balance_tags($markup);  // phpcs:ignore WordPress.Security.EscapeOutput
        }

        /**
         * Register Display Cloned Meta Box
         *
         * @param $post
         */
        public function maybe_clone_logs($post)
        {
            $post_id = $post->ID;
            $clones = pwh_dcfh_post_meta_handler()::get_clones_meta_value($post_id);
            $markup = "";
            if (!empty($clones)) {
                $markup .= "<table>";
                $markup .= "<thead>";
                $markup .= "<tr>";
                $markup .= "<th class='text-center'>#</th>";
                $markup .= "<th>".__('Title', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Created by', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Created at', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Type', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Staus', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "</tr>";
                $markup .= "</thead>";
                $markup .= "<tbody>";
                usort($clones, function ($a, $b) {
                    $date_a = DateTime::createFromFormat('d/m/Y H:i:s', $a['date']);
                    $date_b = DateTime::createFromFormat('d/m/Y H:i:s', $b['date']);

                    return $date_a >= $date_b;
                });
                $serial_number = 0;
                foreach ($clones as $clone) {
                    $serial_number++;
                    $created_by = pwh_dcfh_helpers()::get_author_name($clone['author']);
                    $post_title = sprintf('<a href="%1$s">%2$s</a>', get_edit_post_link($clone['post_id']), get_the_title($clone['post_id']));
                    if (empty(get_the_title($clone['post_id']))) {
                        $post_title = __('May be deleted.', pwh_dcfh_hc()::TEXT_DOMAIN);
                    }
                    $post_date = pwh_dcfh_helpers()::date_time($clone['date']);
                    $post_type = ucfirst(get_post_type($clone['post_id']));
                    $post_status = ucfirst(get_post_status($clone['post_id']));
                    $markup .= "<tr>";
                    $markup .= "<td class='text-center'>".$serial_number."</td>";
                    $markup .= "<td>".$post_title."</td>";
                    $markup .= "<td>".$created_by."</td>";
                    $markup .= "<td>".$post_date."</td>";
                    $markup .= "<td>".$post_type."</td>";
                    $markup .= "<td>".$post_status."</td>";
                    $markup .= "</tr>";
                }
                $markup .= "</tbody>";
                $markup .= "</table>";
            }
            if (empty($clones)) {
                $markup .= "<div class='text-center'>".__('No record found.', pwh_dcfh_hc()::TEXT_DOMAIN)."</div>";
            }
            echo force_balance_tags($markup);  // phpcs:ignore WordPress.Security.EscapeOutput
        }

        /**
         * Register Display Email Replies Meta Box
         *
         * @param $post
         */
        public function maybe_email_logs($post)
        {
            $post_id = $post->ID;
            $logs = pwh_dcfh_logger()->get_logs('sent_email_log', $post_id);
            $markup = "";
            if (!empty($logs)) {
                $markup .= "<table>";
                $markup .= "<thead>";
                $markup .= "<tr>";
                $markup .= "<th class='text-center'>#</th>";
                $markup .= "<th>".__('Reply by', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Reply at', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Reply from', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Reply to', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "</tr>";
                $markup .= "</thead>";
                $markup .= "<tbody>";
                $serial_number = 0;
                foreach ($logs as $log) {
                    if (is_serialized($log->post_content)) {
                        $post_content = maybe_unserialize($log->post_content);
                        if (!empty($post_content) && is_array($post_content)) {
                            $serial_number++;
                            $email_by = pwh_dcfh_helpers()::get_author_name($log->post_author);
                            $email_at = pwh_dcfh_helpers()::date_time($log->post_date);
                            $email_from = $post_content['email_from'];
                            $email_to = $post_content['email_to'];
                            $email_body = $post_content['email_body'];
                            $markup .= "<tr>";
                            $markup .= "<td class='text-center'>".$serial_number."</td>";
                            $markup .= "<td>".$email_by."</td>";
                            $markup .= "<td>".$email_at."</td>";
                            $markup .= "<td>".$email_from."</td>";
                            $markup .= "<td>".$email_to."</td>";
                            $markup .= "</tr>";
                        }
                    }
                }
                $markup .= "</tbody>";
                $markup .= "</table>";
            }
            if (empty($logs)) {
                $markup .= "<div class='text-center'>".__('No record found.', pwh_dcfh_hc()::TEXT_DOMAIN)."</div>";
            }
            echo force_balance_tags($markup);  // phpcs:ignore WordPress.Security.EscapeOutput
        }

        /**
         * Register Display User Meta Box
         *
         * @param $post
         *
         * @return void|null
         */
        public function maybe_user_entries($post)
        {
            $post_id = $post->ID;
            $user_email = pwh_dcfh_post_meta_handler()::get_contact_email_meta_value($post_id);
            $ip_address = pwh_dcfh_post_meta_handler()::get_ip_address_meta_value($post_id);
            if (!pwh_dcfh_user_handler()::is_user_other_entry_exist($post_id, $user_email, $ip_address)) {
                echo "<div class='text-center'>".esc_html__('No record found.', pwh_dcfh_hc()::TEXT_DOMAIN)."</div>";

                return null;
            }
            $paged = isset($_GET['paged']) ? sanitize_key($_GET['paged']) : 1; // phpcs:ignore WordPress.Security.NonceVerification
            $per_page = intval(apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'user_entries_per_page', 10));
            $args = [
                'post_type' => $this->_post_type,
                'post__not_in' => [$post_id],
                'posts_per_page' => $per_page,
                'paged' => $paged,
                'post_status' => ['draft', 'private'],
                'orderby' => 'date',
                'order' => 'DESC',
                'no_found_rows' => false,
                'cache_results' => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                // phpcs:ignore
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => pwh_dcfh_hc()::CF_CONTACT_EMAIL_META_KEY,
                        'compare' => 'LIKE',
                        'value' => $user_email
                    ],
                    [
                        'key' => pwh_dcfh_hc()::CF_IP_ADDRESS_META_KEY,
                        'compare' => 'LIKE',
                        'value' => $ip_address
                    ]
                ],
            ];
            if ($post->post_author > 0) {
                $args['author__in'] = $post->post_author;
            }
            $user_query = new WP_Query($args);
            $markup = "";
            if (isset($user_query->posts) && !empty($user_query->posts)) {
                if (1 !== $paged) {
                    $serial_number = ($per_page * ($paged - 1));
                } else {
                    $serial_number = 0;
                }
                $markup .= "<table>";
                $markup .= "<tbody>";
                $markup .= "<thead>";
                $markup .= "<tr>";
                $markup .= "<th class='text-center'>".__('Sr#', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Entry', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Read', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Read By', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Read at', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Contact Form', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<th>".__('Date', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "</tr>";
                $markup .= "</thead>";
                foreach ($user_query->posts as $user_post) {
                    $serial_number++;
                    $contact_form = pwh_dcfh_db_handler()::get_contact_form_title(pwh_dcfh_post_meta_handler()::get_contact_form_id_meta_value($user_post->ID));
                    $post_title = sprintf(__('<a href="%s">Entry# %d</a>', pwh_dcfh_hc()::TEXT_DOMAIN), esc_url(get_edit_post_link($user_post->ID)), $user_post->ID);
                    $post_date = pwh_dcfh_helpers()::date_time($user_post->post_date);
                    $post_read = __('No', pwh_dcfh_hc()::TEXT_DOMAIN);
                    $post_read_at = $user_post->post_modified;
                    $post_read_by = '-';
                    if ($post_read_at !== '0000-00-00 00:00:00') {
                        $post_read_at = pwh_dcfh_helpers()::date_time($post_read_at);
                        $post_read_by = pwh_dcfh_helpers()::get_author_name(get_post_meta($user_post->ID, pwh_dcfh_hc()::CF_READ_BY_META_KEY, true));
                        $post_read = __('Yes', pwh_dcfh_hc()::TEXT_DOMAIN);
                    }
                    if ($post_read_at === '0000-00-00 00:00:00') {
                        $post_read_at = '-';
                    }
                    $markup .= "<tr>";
                    $markup .= "<td class='text-center'>".esc_html($serial_number)."</td>";
                    $markup .= "<td>".$post_title."</td>";
                    $markup .= "<td>".esc_html($post_read)."</td>";
                    $markup .= "<td>".esc_html($post_read_by)."</td>";
                    $markup .= "<td>".esc_html($post_read_at)."</td>";
                    $markup .= "<td>".esc_html($contact_form)."</td>";
                    $markup .= "<td>".esc_html($post_date)."</td>";
                    $markup .= "</tr>";
                }
                $markup .= "</tbody>";
                $markup .= "</table>";
                $big = PHP_INT_MAX;
                $markup .= '<div class="pagination">';
                $markup .= paginate_links([
                    'base' => str_replace([$big, '#038;'], ['%#%', ''], esc_url(get_pagenum_link($big)).'#pwh-dcfh-user-other-entry-metabox'),
                    'total' => $user_query->max_num_pages,
                    'current' => max(1, $paged),
                    'format' => '?paged=%#%',
                    'show_all' => false,
                    'type' => 'plain',
                    'end_size' => 2,
                    'mid_size' => 1,
                    'prev_next' => true,
                    'screen_reader_text' => '&nbsp;',
                    'prev_text' => __('Previous', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'next_text' => __('Next', pwh_dcfh_hc()::TEXT_DOMAIN),
                    'add_args' => false,
                    'add_fragment' => '',
                ]);
                $markup .= '</div>';
            }
            if (empty($user_query->posts)) {
                $markup .= "<div class='text-center'>".__('No record found.', pwh_dcfh_hc()::TEXT_DOMAIN)."</div>";
            }
            echo force_balance_tags($markup);  // phpcs:ignore WordPress.Security.EscapeOutput
        }

        /**
         * Register Display Form Meta Detail Meta Box
         *
         * @param $post
         */
        public function maybe_form_meta($post)
        {
            $post_id = $post->ID;
            $submitter_name = apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'submitter_label', __('Visitor', pwh_dcfh_hc()::TEXT_DOMAIN));
            $submitter_id = '';
            if ($post->post_author > 0) {
                $user_data = get_user_by('ID', $post->post_author);
            } else {
                $user_data = get_user_by('email', pwh_dcfh_post_meta_handler()::get_contact_email_meta_value($post_id));
            }
            if (isset($user_data->ID)) {
                $submitter_id = $user_data->ID;
                $submitter_name = sprintf('<a target="_blank" href="%1$s">%2$s</a>', get_edit_user_link($user_data->ID), pwh_dcfh_helpers()::get_author_name($user_data->ID));
            }
            $contact_form_id = pwh_dcfh_post_meta_handler()::get_contact_form_id_meta_value($post_id);
            $contact_form_title = pwh_dcfh_db_handler()::get_contact_form_title($contact_form_id);
            $page_id = pwh_dcfh_post_meta_handler()::get_page_id_meta_value($post_id);
            $referer_url = pwh_dcfh_post_meta_handler()::get_referer_url_meta_value($post_id);
            $post_date = pwh_dcfh_helpers()::date_time($post->post_date);
            $markup = "<table>";
            $markup .= "<tbody>";
            if (!empty($submitter_id)) {
                $markup .= "<tr>";
                $markup .= "<th>".__('Submitter ID:', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<td>".$submitter_id."</td>";
                $markup .= "</tr>";
            }
            $markup .= "<tr>";
            $markup .= "<th>".__('Submitter:', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
            $markup .= "<td>".$submitter_name."</td>";
            $markup .= "</tr>";
            $markup .= "<tr>";
            $markup .= "<th>".__('Contact Form:', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
            $markup .= "<td>".$contact_form_title."</td>";
            $markup .= "</tr>";
            $markup .= "<tr>";
            $markup .= "<th>".__('Page:', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
            $markup .= "<td>".sprintf('<a href="%1$s" target="_blank">%2$s</a>', get_the_permalink($page_id), get_the_title($page_id))."</td>";
            $markup .= "</tr>";
            $markup .= "<tr>";
            $markup .= "<th>".__('Referer URL:', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
            $markup .= "<td>".make_clickable($referer_url)."</td>";
            $markup .= "</tr>";
            $markup .= "<tr>";
            $markup .= "<th>".__('Date:', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
            $markup .= "<td>".$post_date."</td>";
            $markup .= "</tr>";
            $markup .= "</tbody>";
            $markup .= "</table>";
            echo force_balance_tags($markup);  // phpcs:ignore WordPress.Security.EscapeOutput
        }

        /**
         * Register Display User Agent Meta Box
         *
         * @param $post
         */
        public function maybe_user_agent($post)
        {
            $post_id = $post->ID;
            // IP Address
            $ip_address = pwh_dcfh_post_meta_handler()::get_ip_address_meta_value($post_id);
            // User Browser
            $user_browser = '';
            $user_agent = pwh_dcfh_post_meta_handler()::get_user_agent_meta_value($post_id);
            if (isset($user_agent['browser']) && !empty($user_agent['browser'])) {
                $user_browser = $user_agent['browser'].'-'.$user_agent['version'];
            }
            // User OS
            $user_os = '';
            if (isset($user_agent['platform']) && !empty($user_agent['platform'])) {
                $user_os = ucfirst($user_agent['platform']);
            }
            if (!empty($ip_address) || !empty($user_browser) || !empty($user_os)) {
                $markup = "<table>";
                $markup .= "<tbody>";
                $markup .= "<th>".__('IP Address:', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<td>".$ip_address."</td>";
                $markup .= "</tr>";
                $markup .= "<tr>";
                $markup .= "<th>".__('Browser', pwh_dcfh_hc()::TEXT_DOMAIN)."</th>";
                $markup .= "<td>".$user_browser."</td>";
                $markup .= "</tr>";
                $markup .= "<tr>";
                $markup .= "<th>".esc_html('OS')."</th>";
                $markup .= "<td>".$user_os."</td>";
                $markup .= "</tr>";
                $markup .= "</tbody>";
                $markup .= "</table>";
            } else {
                $markup = "<div class='text-center'>".__('No record found.', pwh_dcfh_hc()::TEXT_DOMAIN)."</div>";
            }
            echo force_balance_tags($markup);  // phpcs:ignore WordPress.Security.EscapeOutput
        }

        /**
         * Register Display Admin Notice
         *
         * @return void|null
         */
        public function register_admin_notice()
        {
            if (isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_GET['_wpnonce']);
                if (wp_verify_nonce($_wpnonce, 'entry_action')) {
                    $success = isset($_GET['success']) ? sanitize_text_field($_GET['success']) : false;
                    $cloned_post_id = isset($_GET['cloned_post_id']) ? sanitize_text_field($_GET['cloned_post_id']) : null;
                    $email_to = isset($_GET['email_sent_to']) ? sanitize_text_field($_GET['email_sent_to']) : null;
                    $class = $success ? 'notice notice-success is-dismissible' : 'notice notice-error is-dismissible';
                    $message = '';
                    if ($success && !empty($cloned_post_id)) {
                        $message .= __('The post is created successfully.', pwh_dcfh_hc()::TEXT_DOMAIN);
                        $message .= sprintf('<a href="%1$s" target="_blank">%2$s</a>', esc_url(get_edit_post_link($cloned_post_id)), __('Click here to edit', pwh_dcfh_hc()::TEXT_DOMAIN));
                        if ('publish' === get_post_status($cloned_post_id)) {
                            $message .= sprintf(' &#124; <a href="%1$s" target="_blank">%2$s</a>', esc_url(get_the_permalink($cloned_post_id)), __('Click here to view', pwh_dcfh_hc()::TEXT_DOMAIN));
                        }
                    }
                    if (is_email($email_to)) {
                        if ($success) {
                            $message .= __('The email sent successfully to ', pwh_dcfh_hc()::TEXT_DOMAIN);
                            $message .= make_clickable($email_to);
                        } else {
                            $reason = get_transient('pwh_dcfh_email_failed');
                            $message .= __('The email does not sent at '.make_clickable($email_to), pwh_dcfh_hc()::TEXT_DOMAIN);  // phpcs:ignore WordPress.Security.EscapeOutput
                            if (!empty($reason)) {
                                $message .= sprintf('<b>Reason:%s</b>', esc_html($reason));
                                delete_transient('pwh_dcfh_email_failed');
                            }
                        }
                    }
                    pwh_dcfh_helpers()::add_message($message);
                    pwh_dcfh_helpers()::display_message();
                }
            }
        }

    }
}