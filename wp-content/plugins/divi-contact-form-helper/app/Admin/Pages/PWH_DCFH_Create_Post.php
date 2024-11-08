<?php

namespace PWH_DCFH\App\Admin\Pages;

use Exception;
use PWH_DCFH\App\Admin\Controllers\PWH_DCFH_Post_Meta_Handler;
use PWH_DCFH\App\Helpers\PWH_DCFH_Email_Helper;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Create_Post')) {
    class PWH_DCFH_Create_Post
    {

        private $_post_type;

        private $_post_id;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            $this->_post_type = pwh_dcfh_hc()::POST_TYPE;
            $_page = pwh_dcfh_helpers()::current_page();
            $this->_post_id = isset($_GET['post_id']) ? sanitize_key($_GET['post_id']) : null; // phpcs:ignore
            add_action('admin_menu', [$this, 'menu']);
            if (!empty($this->_post_id) && $_page == 'create_post') {
                if (empty(get_post($this->_post_id))) {
                    wp_safe_redirect(add_query_arg(['post_type' => $this->_post_type], admin_url('edit.php')));
                    exit();
                }
                add_action('admin_init', function () {
                    ob_start();
                });
                add_action('view_create_post', [$this, 'display_form'], 10);
            }
            add_action('wp_ajax_terms_list', [new PWH_DCFH_Post_Meta_Handler(), 'get_post_type_terms']);
            add_action('wp_ajax_meta_keys', [new PWH_DCFH_Post_Meta_Handler(), 'get_post_meta_keys']);
        }

        /**
         * Register Wp Admin Menu
         */
        public function menu()
        {
            add_submenu_page(pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG, __('Create Post', pwh_dcfh_hc()::TEXT_DOMAIN), null, 'manage_options', 'create_post', [$this, 'process_form']);
        }

        /**
         * Process Form Actions
         */
        public function process_form()
        {
            $post_request = [
                'to_post_type' => '',
                'post_status' => '',
                'post_term' => '',
                'copy_as' => '',
                'custom_field' => '',
                'custom_featured_img' => '',
                'custom_featured_img_url' => '',
                'send_email_to_submitter' => '',
            ];
            if (isset($_POST['btn_create_post']) && isset($_POST['_wpnonce']) && isset($_POST['to_post_type'])) {
                $post_request = $_POST; // phpcs:ignore
                $post_request['custom_featured_img_url'] = null;
                $validate_request = true;
                $to_post_type = sanitize_text_field($_POST['to_post_type']);
                $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'pending';
                $post_term = isset($_POST['post_term']) ? sanitize_text_field($_POST['post_term']) : null;
                $copy_as = isset($_POST['copy_as']) ? $_POST['copy_as'] : null; // phpcs:ignore
                $custom_featured_img = isset($_POST['custom_featured_img']) ? sanitize_text_field($_POST['custom_featured_img']) : null;
                $send_email_to_submitter = isset($_POST['send_email_to_submitter']) && sanitize_text_field($_POST['send_email_to_submitter']);
                $email_to = '';
                if (!empty($custom_featured_img)) {
                    $post_request['custom_featured_img_url'] = wp_get_attachment_url($custom_featured_img);
                }
                if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'create_post')) {
                    wp_die(esc_html__('Unable to submit this form, please refresh and try again.', pwh_dcfh_hc()::TEXT_DOMAIN));
                }
                if (empty($to_post_type)) {
                    $validate_request = false;
                    pwh_dcfh_helpers()::add_error_message(__('Please select post type.', pwh_dcfh_hc()::TEXT_DOMAIN));
                }
                $mapped_data = [];
                foreach ($copy_as as $copy_key => $copy_value) {
                    // Mapping Title and Content
                    if (isset($_POST['post_data'][$copy_key]) && !empty($_POST['post_data'][$copy_key])) {
                        $post_data = $_POST['post_data'][$copy_key]; // phpcs:ignore
                        if ('title' === $copy_value || 'content' === $copy_value) {
                            $mapped_data[$copy_value] = $post_data;
                        }
                    }
                    // Mapping Field As Meta
                    if ((isset($_POST['custom_field'][$copy_key]) && !empty($_POST['custom_field'][$copy_key])) && !is_array($copy_value)) {
                        $meta_key = pwh_dcfh_helpers()::str_to_key(sanitize_text_field($_POST['custom_field'][$copy_key]));
                        if ($copy_value == 'custom_field') {
                            $mapped_data['meta_input'][$meta_key] = $post_data;
                        }
                    }
                    // Mapping File As Meta
                    if (is_array($copy_value)) {
                        foreach ($copy_value as $k => $v) {
                            $attachment = isset($_POST['post_data'][$copy_key.'_id'][$k]) ? sanitize_text_field($_POST['post_data'][$copy_key.'_id'][$k]) : null;
                            if ('custom_field' === $v && isset($_POST['custom_field'][$copy_key][$k])) {
                                $media_response = pwh_dcfh_helpers()::upload_files_to_media_library($attachment);
                                $meta_key = pwh_dcfh_helpers()::str_to_key(sanitize_text_field($_POST['custom_field'][$copy_key][$k]));
                                $mapped_data['meta_input'][$meta_key] = $media_response['attachment_url'];
                                $mapped_data['attachment_ids'][] = $media_response['attachment_id'];
                            } elseif ('featured_image' === $v) {
                                $media_response = pwh_dcfh_helpers()::upload_files_to_media_library($attachment);
                                $mapped_data['featured_image'] = $media_response['attachment_id'];
                            }
                        }
                    }
                }
                if (empty($mapped_data)) {
                    $validate_request = false;
                    pwh_dcfh_helpers()::add_error_message(__('Data is not mapped. Please contact '.make_clickable(pwh_dcfh_helpers()::plugin()->AuthorURI), pwh_dcfh_hc()::TEXT_DOMAIN));
                }
                if (!isset($mapped_data['title']) || !isset($mapped_data['content'])) {
                    $validate_request = false;
                    pwh_dcfh_helpers()::add_error_message(__('Please map title and content of post.', pwh_dcfh_hc()::TEXT_DOMAIN));
                }
                if ($validate_request) {
                    $post_title = $mapped_data['title'];
                    $post_content = $mapped_data['content'];
                    $post_meta = isset($mapped_data['meta_input']) ? $mapped_data['meta_input'] : null;
                    $post_attachment_ids = isset($mapped_data['attachment_ids']) ? $mapped_data['attachment_ids'] : null;
                    $cloned_post_id = wp_insert_post([
                        'post_author' => get_current_user_id(),
                        'post_title' => $post_title,
                        'post_name' => $post_title,
                        'post_content' => $post_content,
                        'post_excerpt' => wp_trim_words(wp_trim_excerpt($post_content), 25),
                        'post_status' => $post_status,
                        'post_type' => $to_post_type,
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'meta_input' => $post_meta,
                    ], true);
                    if (!is_wp_error($cloned_post_id)) {
                        // Term
                        if (!empty($post_term)) {
                            $term_id = intval($post_term);
                            $term = get_term($term_id);
                            wp_set_post_terms($cloned_post_id, $term_id, $term->taxonomy);
                        }
                        // Clone History
                        pwh_dcfh_post_meta_handler()::update_cloned_history($this->_post_id, $cloned_post_id);
                        if ($send_email_to_submitter && 'publish' === $post_status) {
                            $email_to = pwh_dcfh_user_handler()::get_post_user_email($this->_post_id);
                            $this->send_email($email_to, $cloned_post_id);
                        }
                        // Featured Image
                        if ($custom_featured_img > 0) {
                            set_post_thumbnail($cloned_post_id, $custom_featured_img);
                        }
                        if (empty($custom_featured_img) && (isset($mapped_data['featured_image']) && !empty($mapped_data['featured_image']))) {
                            $featured_img_id = $mapped_data['featured_image'];
                            set_post_thumbnail($cloned_post_id, $featured_img_id);
                            wp_update_post([
                                'ID' => $featured_img_id,
                                'post_parent' => $cloned_post_id
                            ]);
                        }
                        // Update Attachment ID To Post
                        if (!empty($post_attachment_ids)) {
                            foreach ($post_attachment_ids as $post_attachment_id) {
                                wp_update_post([
                                    'ID' => $post_attachment_id,
                                    'post_parent' => $cloned_post_id
                                ]);
                            }
                        }
                        // Redirect
                        wp_safe_redirect(add_query_arg([
                            'post_type' => $this->_post_type,
                            'post' => $this->_post_id,
                            'success' => true,
                            'action' => 'edit',
                            '_wpnonce' => wp_create_nonce('entry_action'),
                            'cloned_post_id' => $cloned_post_id,
                            'email_sent_to' => $email_to,
                        ], admin_url('post.php')));
                        exit();
                    } else {
                        pwh_dcfh_helpers()::add_error_message(__('Something went wrong. Please contact '.make_clickable(pwh_dcfh_helpers()::plugin()->AuthorURI), pwh_dcfh_hc()::TEXT_DOMAIN));
                    }
                }
            }
            do_action('view_create_post', $post_request);
        }

        /**
         * Display Form HTML Content
         *
         * @param $post_request
         */
        public function display_form($post_request)
        {
            $show_media = ' style="display: none"';
            if (!empty($post_request['custom_featured_img_url'])) {
                $show_media = ' style="display: block"';
            }
            $post_obj = get_post($this->_post_id);
            if (is_serialized($post_obj->post_excerpt)) {
                $form_entries = maybe_unserialize($post_obj->post_excerpt);
                if (!empty($form_entries) && is_array($form_entries)) { ?>
                    <div class="wrap pwh-dcfh-page" id="page-create-post">
                        <h1 class="wp-heading-inline"><?php esc_html_e('Create Post', pwh_dcfh_hc()::TEXT_DOMAIN); ?></h1>
                        <a href="<?php echo esc_url(add_query_arg(['post_type' => $this->_post_type], admin_url('edit.php'))); ?>" class="page-title-action"><?php esc_html_e('Entries',
                                pwh_dcfh_hc()::TEXT_DOMAIN); ?></a>
                        <hr class="wp-header-end">
                        <?php pwh_dcfh_helpers()::display_message(); ?>
                        <span class="spinner"></span>
                        <div class="js-response"></div>
                        <div id="poststuff">
                            <div id="post-body" class="metabox-holder columns-2">
                                <div class="postbox-container">
                                    <div class="postbox">
                                        <div class="inside">
                                            <form method="POST" autocomplete="off" enctype="application/x-www-form-urlencoded">
                                                <table class="form-table post-selection">
                                                    <tbody>
                                                    <tr>
                                                        <th><?php esc_html_e('Type:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                            <span class="required">*</span></th>
                                                        <td>
                                                            <select title="" class="form-control" name="to_post_type" id="to_post_type" required>;
                                                                <option value=""><?php esc_html_e('Please Select', pwh_dcfh_hc()::TEXT_DOMAIN); ?></option>
                                                                <?php foreach (pwh_dcfh_helpers()::get_post_types() as $post_key => $posts_type) {
                                                                    $posts_type = ucwords($posts_type); ?>
                                                                    <option value="<?php echo esc_html($post_key); ?>" <?php selected($post_key,
                                                                        esc_html($post_request['to_post_type'])); ?>><?php echo esc_html($posts_type); ?></option>";
                                                                <?php } ?>
                                                            </select>
                                                            <p class="helper"><?php esc_html_e('Choose post type from list.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                        </td>
                                                        <th><?php esc_html_e('Status:', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                        <td>
                                                            <select title="" class="form-control" name="post_status" id="post_status">;
                                                                <?php foreach (get_post_statuses() as $status_key => $status) {
                                                                    $status = ucwords($status); ?>
                                                                    <option value="<?php echo esc_html($status_key); ?>" <?php selected($status_key,
                                                                        esc_html($post_request['post_status'])); ?>><?php echo esc_html($status); ?></option>";
                                                                <?php } ?>
                                                            </select>
                                                            <p class="helper"><?php esc_html_e('Choose post status from list.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                        </td>
                                                        <th><?php esc_html_e('Term:', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                        <td>
                                                            <select title="" class="form-control" name="post_term" id="post_term" disabled>;
                                                                <option value=""><?php esc_html_e('Please Select', pwh_dcfh_hc()::TEXT_DOMAIN); ?></option>
                                                            </select>
                                                            <p class="helper"><?php esc_html_e('Choose post category from list.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <table class="form-table form-data">
                                                    <thead class="d-none">
                                                    <tr>
                                                        <th><?php esc_html_e('Field', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                        <th><?php esc_html_e('Value', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                        <th><?php esc_html_e('Type', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                        <th><?php esc_html_e('Custom Field', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    foreach ($form_entries as $form_key => $form_entry) {
                                                        $field_label = esc_html($form_entry['label']);
                                                        $field_type = esc_html($form_entry['type']);
                                                        $post_data = isset($post_request['post_data'][$form_key]) ? $post_request['post_data'][$form_key] : esc_html($form_entry['value']);
                                                        $copy_as = isset($post_request['copy_as'][$form_key]) ? $post_request['copy_as'][$form_key] : null;
                                                        $custom_field = isset($post_request['custom_field'][$form_key]) ? $post_request['custom_field'][$form_key] : null;
                                                        $readonly = empty($custom_field) ? 'readonly' : null;
                                                        if ('text' !== $field_type && 'file' !== $field_type) {
                                                            ?>
                                                            <tr>
                                                                <th><?php echo esc_html($field_label.':'); ?></th>
                                                                <td>
                                                                    <input title="" type="text" name="post_data[<?php echo esc_attr($form_key); ?>]" id="<?php echo esc_attr('post_data_'.$form_key); ?>" class="regular-text" value="<?php esc_attr_e($post_data); ?>">
                                                                    <p class="helper"><?php esc_html_e('Form entry.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                                </td>
                                                                <td>
                                                                    <select title="" class="form-control copy-as" name="copy_as[<?php echo esc_attr($form_key); ?>]" id="<?php echo esc_attr('copy_as_'.$form_key); ?>" data-row="<?php echo esc_attr($form_key); ?>">
                                                                        <?php foreach (self::get_clone_types() as $key => $type) { ?>
                                                                            <option value="<?php echo esc_html($key); ?>" <?php selected($key, $copy_as); ?>><?php echo esc_html($type); ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                    <p class="helper"><?php esc_html_e('Choose option from list.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                                </td>
                                                                <td>
                                                                    <input title="" type="text" class="form-control" name="custom_field[<?php echo esc_attr($form_key); ?>]" id="<?php echo esc_attr('custom_field_'.$form_key); ?>" placeholder="<?php esc_html_e('Choose Custom Field',
                                                                        pwh_dcfh_hc()::TEXT_DOMAIN); ?>" value="<?php echo esc_html($custom_field); ?>" <?php echo esc_attr($readonly); ?>>
                                                                    <p class="helper"><?php esc_html_e('Enter meta key name.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                                </td>
                                                            </tr>
                                                        <?php } elseif ('file' === $field_type) {
                                                            $subdir = isset($form_entry['subdir']) ? esc_html($form_entry['subdir']) : '';
                                                            $files = is_array($post_data) ? $post_data : explode(',', $post_data);
                                                            if (!empty($files) && is_array($files) && !empty($subdir)) {
                                                                $file_counter = 0;
                                                                $contact_form_id = pwh_dcfh_post_meta_handler()::get_contact_form_id_meta_value($this->_post_id);
                                                                foreach ($files as $file) {
                                                                    $file_counter++;
                                                                    $row_id = $file_counter + $form_key;
                                                                    $upload_dir = pwh_dcfh_helpers()::get_form_upload_dir($contact_form_id, $subdir, $file);
                                                                    $upload_url = pwh_dcfh_helpers()::get_form_upload_url($contact_form_id, $subdir, $file);
                                                                    $wp_check_filetype = wp_check_filetype($upload_dir);
                                                                    if (is_file($upload_dir) && file_exists($upload_dir)) {
                                                                        ?>
                                                                        <tr>
                                                                            <th><?php echo esc_html($field_label.' '.$file_counter.':'); ?></th>
                                                                            <td>
                                                                                <?php echo sprintf('<a href="%1$s" target="_blank">%2$s</a>', esc_url($upload_url), esc_html($file)); ?>
                                                                                <p class="helper"><?php esc_html_e('Contact form attachment.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                                                <input type="hidden" name="post_data[<?php echo esc_attr($form_key); ?>_id][<?php echo esc_attr($file_counter); ?>]" value="<?php echo esc_attr($upload_url); ?>">
                                                                            </td>
                                                                            <td>
                                                                                <select title="" class="form-control copy-as" name="copy_as[<?php echo esc_attr($form_key); ?>][<?php echo esc_attr($file_counter); ?>]" data-row="<?php echo esc_attr($row_id); ?>">
                                                                                    <?php foreach (self::get_clone_types() as $key => $type) { ?>
                                                                                        <option value="<?php echo esc_html($key); ?>" <?php selected($key,
                                                                                            isset($copy_as[$file_counter]) ? $copy_as[$file_counter] : ''); ?>><?php echo esc_html($type); ?></option>
                                                                                    <?php } ?>
                                                                                </select>
                                                                                <p class="helper"><?php esc_html_e('Choose option from list.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                                            </td>
                                                                            <td>
                                                                                <input title="" type="text" class="form-control" name="custom_field[<?php echo esc_attr($form_key); ?>][<?php echo esc_attr($file_counter); ?>]" id="<?php echo esc_attr('custom_field_'.$row_id); ?>" placeholder="<?php esc_html_e('Choose Custom Field',
                                                                                    pwh_dcfh_hc()::TEXT_DOMAIN); ?>" value="<?php echo esc_html(isset($custom_field[$file_counter]) ? $custom_field[$file_counter] : ''); ?>" <?php echo esc_attr($readonly); ?>>
                                                                                <p class="helper"><?php esc_html_e('Enter meta key name.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                                            </td>
                                                                        </tr>
                                                                        <?php
                                                                    }
                                                                }
                                                            }
                                                        } elseif ('text' === $field_type) { ?>
                                                            <tr>
                                                                <th><?php echo esc_html($field_label.':'); ?></th>
                                                                <td colspan="2">
                                                                    <select title="" class="form-control" name="copy_as[<?php echo esc_attr($form_key); ?>]" id="<?php echo esc_attr('copy_as_'.$form_key); ?>" data-row="<?php echo esc_attr($form_key); ?>">
                                                                        <?php foreach (self::get_clone_types() as $key => $type) {
                                                                            $selected = isset($post_request['copy_as'][$form_key]) ? $post_request['copy_as'][$form_key] : 'content'; ?>
                                                                            <option value="<?php echo esc_html($key); ?>" <?php selected($key, $selected); ?>><?php echo esc_html($type); ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input title="" type="text" class="form-control" name="custom_field[<?php echo esc_attr($form_key); ?>]" id="<?php echo esc_attr('custom_field_'.$form_key); ?>" placeholder="<?php esc_html_e('Choose Custom Field',
                                                                        pwh_dcfh_hc()::TEXT_DOMAIN); ?>" readonly>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="4">
                                                                    <?php wp_editor($post_data, 'post_data', [
                                                                        'wpautop' => true,
                                                                        'textarea_name' => "post_data[$form_key]",
                                                                        'textarea_rows' => 10
                                                                    ]);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                        <?php }
                                                    } ?>
                                                    </tbody>
                                                </table>
                                                <div class="media">
                                                    <div class="featured-img" <?php echo $show_media; // phpcs:ignore ?>>
                                                        <img src="<?php echo esc_html($post_request['custom_featured_img_url']); ?>" id="uploaded-featured-image-url" alt="">
                                                        <a href="javascript:void(0)" id="js-remove-featured-image"><?php esc_html_e('Remove', pwh_dcfh_hc()::TEXT_DOMAIN); ?></a>
                                                    </div>
                                                    <a href="javascript:void(0)" class="button-secondary" id="upload-featured-image"><?php esc_html_e('Featured Image',
                                                            pwh_dcfh_hc()::TEXT_DOMAIN); ?></a>
                                                    <input type="hidden" name="custom_featured_img" id="uploaded-featured-image-id" value="<?php echo esc_html($post_request['custom_featured_img']); ?>"/>
                                                </div>
                                                <?php if (!empty(pwh_dcfh_user_handler()::get_post_user_email($this->_post_id))) { ?>
                                                    <div class="send-email-to-submitter">
                                                        <label class="bulk-select-button" for="send_email_to_submitter">
                                                            <input type="checkbox" id="send_email_to_submitter" name="send_email_to_submitter" class="bulk-select-switcher" value="1">
                                                            <span class="bulk-select-button-label"><?php esc_html_e('Send Email To Submitter'); ?></span>
                                                        </label>
                                                        <p class="helper"><?php esc_html_e('Email send only when status is published.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                    </div>
                                                <?php }
                                                wp_nonce_field('create_post', '_wpnonce');
                                                submit_button(esc_html__('Create Post', pwh_dcfh_hc()::TEXT_DOMAIN), 'primary large', 'btn_create_post');
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div id="postbox-container-1" class="postbox-container">
                                    <?php
                                    require_once dirname(__DIR__)."/template/documentation-widget.php";
                                    require_once dirname(__DIR__)."/template/support-widget.php";
                                    ?>
                                </div>
                            </div>
                            <br class="clear">
                        </div>
                    </div>
                    <?php
                }
            }
        }

        /**
         * Send Email To Submitter
         *
         * @param $email_to
         * @param $created_post_id
         *
         * @return bool
         */
        private function send_email($email_to, $created_post_id)
        {
            $email_helper = new PWH_DCFH_Email_Helper($email_to);
            $email_subject = wp_strip_all_tags(apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'create_post_email_subject', __('Entry Cloned', pwh_dcfh_hc()::TEXT_DOMAIN)));
            $heading = wp_strip_all_tags(apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'create_post_email_heading', __('Entry Cloned', pwh_dcfh_hc()::TEXT_DOMAIN)));
            $email_helper->set_email_subject($email_subject);
            $email_helper->set_email_template(pwh_dcfh_helpers()::plugin_dir('html-templates/tpl-entry-clone.html'));
            $email_helper->set_email_body([
                'heading' => $heading,
                'site_title' => htmlspecialchars_decode(get_bloginfo('name')),
                'site_url' => get_option('siteurl'),
                'post_type' => get_post_type($created_post_id),
                'post_title' => get_the_title($created_post_id),
                'post_url' => get_the_permalink($created_post_id),
                'post_date' => pwh_dcfh_helpers()::date_time(date_i18n('Y-m-d h:is')),
            ]);
            $email_helper->set_wp_mail_filters();
            try {
                if ($email_helper->send()) {
                    return true;
                }
            } catch (Exception $e) {
            }

            return false;
        }

        /**
         * To Copy As
         *
         * @return array
         */
        private static function get_clone_types()
        {
            return [
                '' => __('Please Select', pwh_dcfh_hc()::TEXT_DOMAIN),
                'title' => __('Title', pwh_dcfh_hc()::TEXT_DOMAIN),
                'content' => __('Content', pwh_dcfh_hc()::TEXT_DOMAIN),
                'featured_image' => __('Featured Image', pwh_dcfh_hc()::TEXT_DOMAIN),
                'custom_field' => __('Custom Field', pwh_dcfh_hc()::TEXT_DOMAIN),
            ];
        }
    }
}