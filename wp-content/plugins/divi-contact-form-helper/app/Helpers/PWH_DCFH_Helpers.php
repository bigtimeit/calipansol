<?php

namespace PWH_DCFH\App\Helpers;

use WP_User;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!function_exists('get_plugin_data')) {
    require_once(ABSPATH.'wp-admin/includes/plugin.php');
}
if (!class_exists('PWH_DCFH_Helpers')) {
    class PWH_DCFH_Helpers
    {

        private static $_instance;

        public static $errors = [];

        public static $messages = [];

        /**
         * Class Construnctor
         */
        private function __construct()
        {
        }

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Helpers
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Get Plugin DATA
         *
         * @return object|null
         */
        public static function plugin()
        {
            $plugin_info = get_plugin_data(pwh_dcfh_hc()::FILE, false, false);

            return (object)$plugin_info;
        }

        /**
         * Get Plugin DIR
         *
         * @param string $path
         *
         * @return string
         */
        public static function plugin_dir($path = '')
        {
            return plugin_dir_path(pwh_dcfh_hc()::FILE).$path;
        }

        /**
         * Get Plugin URL
         *
         * @param string $path
         *
         * @return string
         */
        public static function plugin_url($path = '')
        {
            return plugins_url($path, pwh_dcfh_hc()::FILE);
        }

        /**
         * Get Plugin Handle
         *
         * @param string $handle
         *
         * @return string
         */
        public static function handle($handle = '')
        {
            return strtolower(preg_replace("/[\s_]/", '-', self::plugin()->Name).'-'.$handle);
        }

        /**
         * Get Setting
         *
         * @param $key
         *
         * @return bool
         */
        public static function is_option_enabled($key)
        {
            $options = get_option('et_divi');

            return isset($options[$key]) && 'on' === $options[$key];
        }

        /**
         * Get Setting
         *
         * @param $key
         * @param string $default
         *
         * @return bool
         */
        public static function get_option($key, $default = '')
        {
            $options = get_option('et_divi');

            return isset($options[$key]) ? $options[$key] : $default;
        }

        /**
         * Check Divi 4.13.1
         *
         * @return bool
         */
        public static function is_divi_413_1_or_above()
        {
            $version = !is_child_theme() ? wp_get_theme()->get('Version') : wp_get_theme()->parent()->get('Version');
            if (version_compare($version, '4.13.1', '>=')) {
                return true;
            }

            return false;
        }

        /**
         * Get Divi Setting Page Slug
         *
         * @return string
         */
        public static function get_divi_setting_slug()
        {
            $slug = 'et_divi_options';
            if (is_plugin_active('divi-ghoster/divi-ghoster.php')) {
                $options = get_option('agsdg_settings');
                $slug = 'et_'.$options['theme_slug'].'_options';
            }

            return $slug;
        }

        /**
         * Get The Author Name
         *
         * @param null $user_id
         *
         * @return string
         */
        public static function get_author_name($user_id = null)
        {
            $user_info = $user_id ? new WP_User($user_id) : wp_get_current_user();
            if ($user_info->first_name) {
                if ($user_info->last_name) {
                    return $user_info->first_name.' '.$user_info->last_name;
                }

                return $user_info->first_name;
            }

            return $user_info->display_name;
        }

        /**
         * Get Author Name
         *
         * @param $post_id
         *
         * @return string
         */
        public static function get_submitter_name($post_id)
        {
            $post_author = __('Visitor', pwh_dcfh_hc()::TEXT_DOMAIN);
            if ($post_id > 0) {
                $user_data = get_user_by('ID', $post_id);
            } else {
                $user_data = pwh_dcfh_post_meta_handler()::get_contact_email_meta_value($post_id);
            }
            if (isset($user_data->ID)) {
                $post_author = pwh_dcfh_helpers()::get_author_name($user_data->ID);
            }

            return $post_author;
        }

        /**
         * Get WP Pages List
         *
         * @return array
         */
        public static function get_pages()
        {
            $pages_list = wp_list_pluck(get_posts(['post_type' => 'page', 'numberposts' => -1]), 'post_title', 'ID');
            $pages = array_replace([__('Please Select A Page', pwh_dcfh_hc()::TEXT_DOMAIN)], $pages_list);
            if (!empty($pages)) {
                return $pages;
            }

            return [];
        }

        /**
         * WP Post Types
         *
         * @return string[]
         */
        public static function get_post_types()
        {
            $posts_types = get_post_types(['public' => true]);
            unset($posts_types['attachment']);

            return $posts_types;
        }

        /**
         * Get Datetime
         *
         * @param $date
         *
         * @return string
         */
        public static function date_time($date)
        {
            $date_strttime = strtotime($date);
            $date_format = apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'admin_dateformat', 'Y/m/d');
            $date = date_i18n('Y/m/d', $date_strttime);
            $time_format = apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'admin_timeformat', 'g:i a');
            $time = date_i18n($time_format, $date_strttime);

            return sprintf(__('%1$s at %2$s', pwh_dcfh_hc()::TEXT_DOMAIN), $date, $time);
        }

        /**
         * Add Message To Array
         *
         * @param $message
         * @param false $is_error
         */
        public static function add_message($message, $is_error = false)
        {
            if ($is_error) {
                self::$errors[] = $message;
            } else {
                self::$messages[] = $message;
            }
        }

        /**
         * Add Error Message To Array
         *
         * @param $message
         */
        public static function add_error_message($message)
        {
            self::add_message($message, true);
        }

        /**
         * Display Messages
         *
         * @param false $errors
         * @param false $messages
         */
        public static function display_message($errors = false, $messages = false)
        {
            // phpcs:disable
            if (!$errors) {
                $errors = self::$errors;
            }
            if (!$messages) {
                $messages = self::$messages;
            }
            if (!empty($errors)) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <?php
                    if (count($errors) > 1) { ?>
                        <ul style="margin: 0.5em 0 0; padding: 2px;">
                            <li><?php echo implode('</li><li>', $errors); ?></li>
                        </ul>
                        <?php
                    } else { ?>
                        <p><?php echo $errors[0]; ?></p>
                        <?php
                    } ?>
                </div>
                <?php
            } elseif (!empty($messages)) {
                ?>
                <div id="message" class="notice notice-success is-dismissible">
                    <?php
                    if (count($messages) > 1) { ?>
                        <ul>
                            <li><?php echo implode('</li><li>', $messages); ?></li>
                        </ul>
                        <?php
                    } else { ?>
                        <p><?php echo $messages[0]; ?></p>
                        <?php
                    } ?>
                </div>
                <?php
                // phpcs:enable
            }
        }

        /**
         * Check is Debug Mode On
         *
         * @return bool
         */
        public static function is_debug_mode()
        {
            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                return true;
            }

            return false;
        }

        /**
         * Print Data
         *
         * @param string $data
         * @param bool $console
         * @param false $exit
         */
        public static function dd($data = '', $console = true, $exit = true)
        {
            // phpcs:disable
            if ($console) {
                echo '<script type="text/javascript" id="pwh-dcfh-print-r">';
                echo 'console.log('.wp_json_encode($data).')';
                echo '</script>';
            } else {
                echo "<pre>";
                print_r($data);
                echo "</pre>";
                echo $exit ? exit : null;
            }
            // phpcs:enable
        }

        /**
         * Log Data
         *
         * @param $data
         * @param bool $db
         * @param false $delete
         * @param string $filename
         */
        public static function log($data, $delete = false, $db = false, $filename = '')
        {
            // phpcs:disable
            if (false === $db) {
                if (empty($filename)) {
                    $filename = debug_backtrace()[1]['function'];
                }
                $filename = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$filename.'.log';
                if (et_()->WPFS()->exists($filename) && $delete) {
                    wp_delete_file($filename);
                }
                ini_set('error_log', $filename);
                if (is_array($data) || is_object($data)) {
                    error_log(print_r($data, true));
                } else {
                    error_log($data);
                }
            }
            if ($db) {
                $options_name = 'pwh_dcfh_debug_log';
                if (!$delete) {
                    $option = get_option($options_name);
                    if (empty($option)) {
                        $option = [];
                    }
                }
                $option[time()] = $data;
                update_option($options_name, $option, 'no');
            }
            // phpcs:enable
        }

        /**
         * Get Current Page Name
         *
         * @return string|null
         */
        public static function current_page()
        {
            return isset($_GET['page']) ? sanitize_text_field($_GET['page']) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No need to use nonce.
        }

        /**
         * Clean String
         *
         * @param $string
         * @param string $replace
         *
         * @return string
         */
        public static function clean_string($string, $replace = ' ')
        {
            $string = preg_replace('/[\-_]/', $replace, $string);
            // Replacing multiple spaces with a single space
            $string = preg_replace('!\s+!', ' ', $string);
            // Removes special chars
            //$string = preg_replace('/[^A-Za-z0-9_]/', ' ', $string);
            $string = preg_replace('/\W/', ' ', $string);
            // Replacing multiple spaces with a single space
            $string = preg_replace('!\s+!', ' ', $string);

            return ucwords($string);
        }

        /**
         * Underscore String
         *
         * @param $string
         *
         * @return string
         */
        public static function str_to_key($string)
        {
            // Replacing multiple spaces with a single space
            $string = preg_replace('!\s+!', ' ', $string);
            // Removes special chars
            $string = preg_replace('/\W/', ' ', $string);
            // Replacing multiple spaces with a single space
            $string = preg_replace('!\s+!', ' ', $string);
            // Replaces all spaces with undderscrore.
            $string = str_replace(' ', '_', trim($string));

            return strtolower($string);
        }

        /**
         * Create DIR
         *
         * @param $folder
         * @param bool $in_base
         *
         * @return bool
         */
        public static function create_dir($folder, $in_base = true)
        {

            $wp_upload_dir = wp_upload_dir();
            $path = path_join($wp_upload_dir['path'], $folder);
            $base_dir = path_join($wp_upload_dir['basedir'], $folder);
            $make_dir = $base_dir;
            if (!$in_base) {
                $make_dir = $path;
            }
            if (!is_dir($make_dir)) {
                wp_mkdir_p($make_dir);
                $index_file = path_join($make_dir, 'index.php');
                if (!et_()->WPFS()->exists($index_file)) {
                    et_()->WPFS()->put_contents($index_file, "<?php // Silence is golden.");
                }
                $htaccess_file = path_join($make_dir, '.htaccess');
                if (!et_()->WPFS()->exists($htaccess_file)) {
                    et_()->WPFS()->put_contents($htaccess_file, "## Prevent Directory Indexes\n\nOptions -Indexes");
                }

                return true;
            }

            return false;
        }

        /**
         * Get Year Month Dir
         *
         * @return array
         */
        public static function get_subdir()
        {
            $time = current_time('mysql');

            return [
                substr($time, 0, 4),
                substr($time, 5, 2),
            ];
        }

        /**
         * Upload DIR
         *
         * @param $folder
         * @param null $base
         *
         * @return array|mixed
         */
        public static function get_wp_upload_dir($folder, $base = null)
        {
            $wp_upload_dir = wp_upload_dir();
            $directory = [
                'basedir' => path_join($wp_upload_dir['basedir'], $folder),
                'baseurl' => path_join($wp_upload_dir['baseurl'], $folder),
            ];
            if (isset($directory[$base])) {
                return $directory[$base];
            }

            return $directory;
        }

        /**
         * Move Files To Directory
         *
         * @param $contact_form_id
         * @param $filename
         * @param $is_increment
         *
         * @return array|false
         */
        public static function upload_files_to_dir($contact_form_id, $filename, $is_increment = false)
        {
            list($y, $m) = self::get_subdir();
            $upload_folder = path_join(pwh_dcfh_hc()::UPLOAD_DIR, "forms/$contact_form_id/$y/$m/");
            $upload_basedir = self::get_wp_upload_dir($upload_folder, 'basedir');
            $upload_baseurl = self::get_wp_upload_dir($upload_folder, 'baseurl');
            if (!is_dir($upload_basedir)) {
                if (!wp_mkdir_p($upload_basedir)) {
                    return false;
                }
                $index_file = path_join($upload_basedir, 'index.php');
                if (!et_()->WPFS()->exists($index_file)) {
                    et_()->WPFS()->put_contents($index_file, "<?php // Silence is golden.");
                }
                $htaccess_file = path_join($upload_basedir, '.htaccess');
                if (!et_()->WPFS()->exists($htaccess_file)) {
                    et_()->WPFS()->put_contents($htaccess_file, "## Prevent Directory Indexes\n\nOptions -Indexes");
                }
            }
            $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
            $file_extension = '.'.$file_extension;
            $filename = sanitize_file_name(wp_basename($filename, $file_extension));
            $counter = 1;
            $uploaded_path = path_join($upload_basedir, $filename.$file_extension);
            while ($is_increment && file_exists($uploaded_path)) {
                $uploaded_path = $upload_basedir.$filename."$counter".$file_extension;
                $counter++;
            }
            $uploaded_path = trim($uploaded_path, '.');
            $uploaded_url = str_replace($upload_basedir, $upload_baseurl, $uploaded_path);

            return ['basedir' => $uploaded_path, 'baseurl' => $uploaded_url];
        }

        /**
         * Attachments To Media
         *
         * @param $file
         *
         * @return array|string[]
         */
        public static function upload_files_to_media_library($file)
        {
            require_once(ABSPATH.'wp-admin/includes/media.php');
            require_once(ABSPATH.'wp-admin/includes/file.php');
            require_once(ABSPATH.'wp-admin/includes/image.php');
            $attachment_data = [
                'attachment_id' => '',
                'attachment_url' => '',
            ];
            if (wp_http_validate_url($file) && !empty($file)) {
                $download_url = download_url($file);
                if (!is_wp_error($download_url)) {
                    $filename = wp_basename($file);
                    $wp_filetype = wp_check_filetype($filename);
                    $upload_file['tmp_name'] = $download_url;
                    $upload_file['name'] = $filename;
                    $post_data = [
                        'post_mime_type' => $wp_filetype['type'],
                        'post_parent' => 0,
                        'post_author' => get_current_user_id(),
                        'post_title' => $filename,
                        'post_name' => $filename,
                        'post_status' => 'inherit',
                        'comment_status' => 'closed',
                        'ping_status' => 'closed'
                    ];
                    $attachment_id = media_handle_sideload($upload_file, 0, __('Divi Contact Form Attachment', pwh_dcfh_hc()::TEXT_DOMAIN), $post_data);
                    if (!is_wp_error($attachment_id) && $attachment_id > 0) {
                        update_post_meta($attachment_id, '_wp_attachment_image_alt', $filename);
                        $attachment_data = [
                            'attachment_id' => $attachment_id,
                            'attachment_url' => wp_get_attachment_url($attachment_id),
                        ];
                    }
                }
            }

            return $attachment_data;
        }

        /**
         * Get Temp Upload Dir
         *
         * @return array|mixed
         */
        public static function get_temp_upload_dir()
        {
            return self::get_wp_upload_dir(path_join(pwh_dcfh_hc()::UPLOAD_DIR, pwh_dcfh_hc()::TEMP_UPLOAD_DIR), 'basedir');
        }

        /**
         * Get Temp Upload URL
         *
         * @return array|mixed
         */
        public static function get_temp_upload_url()
        {
            return self::get_wp_upload_dir(path_join(pwh_dcfh_hc()::UPLOAD_DIR, pwh_dcfh_hc()::TEMP_UPLOAD_DIR), 'baseurl');
        }

        /**
         * Get Upload Dir
         *
         * @param $contact_form_id
         * @param $subdir
         * @param $file
         *
         * @return string
         */
        public static function get_form_upload_dir($contact_form_id, $subdir, $file)
        {

            $upload_folder = path_join(pwh_dcfh_hc()::UPLOAD_DIR, "forms/$contact_form_id/$subdir");
            $upload_dir = self::get_wp_upload_dir($upload_folder, 'basedir');

            return path_join($upload_dir, $file);
        }

        /**
         * Get Upload URL
         *
         * @param $contact_form_id
         * @param $subdir
         * @param $file
         *
         * @return string
         */
        public static function get_form_upload_url($contact_form_id, $subdir, $file)
        {
            $upload_folder = path_join(pwh_dcfh_hc()::UPLOAD_DIR, "forms/$contact_form_id/$subdir");
            $upload_dir = self::get_wp_upload_dir($upload_folder, 'baseurl');

            return path_join($upload_dir, $file);
        }

        /**
         * Encrypt/Decrypt
         *
         * @param string $string
         * @param string $encrypt_decrypt
         *
         * @return false|string|null
         */
        public static function encrypt_decrypt($string = '', $encrypt_decrypt = 'e')
        {
            $output = null;
            $secret_key = 'wbtZKk}rohV^Uw7V?+pgtNG++R2@hT3La.A)u*8+MK]-l?pM&,lfs{79SvXu/';
            $secret_iv = 'w}W<<gj~+$S.TzRZ,=n*P@B{Ma{MnR(0baJ<zU|V7wCvl)&gC@4%+pth_-=|jRJo';
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            if ($encrypt_decrypt == 'e') {
                $output = base64_encode(openssl_encrypt($string, "AES-256-CBC", $key, 0, $iv)); // phpcs:ignore
            } elseif ($encrypt_decrypt == 'd') {
                $output = openssl_decrypt(base64_decode($string), "AES-256-CBC", $key, 0, $iv); // phpcs:ignore
            }

            return $output;
        }

        /**
         * Check Contains
         *
         * @param $haystack
         * @param $needles
         *
         * @return bool
         */
        public static function contains($haystack, $needles)
        {
            foreach ((array)$needles as $needle) {
                if ($needle !== '' && strpos($haystack, $needle) !== false) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Check Is BOT ?
         *
         * @return false|int
         */
        public static function is_bot()
        {
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
            if (!empty($user_agent)) {
                return preg_match('/abacho|accona|AddThis|AdsBot|ahoy|AhrefsBot|AISearchBot|alexa|altavista|anthill|appie|applebot|arale|araneo|AraybOt|ariadne|arks|aspseek|ATN_Worldwide|Atomz|baiduspider|baidu|bbot|bingbot|bing|Bjaaland|BlackWidow|BotLink|bot|boxseabot|bspider|calif|CCBot|ChinaClaw|christcrawler|CMC\/0\.01|combine|confuzzledbot|contaxe|CoolBot|cosmos|crawler|crawlpaper|crawl|curl|cusco|cyberspyder|cydralspider|dataprovider|digger|DIIbot|DotBot|downloadexpress|DragonBot|DuckDuckBot|dwcp|EasouSpider|ebiness|ecollector|elfinbot|esculapio|ESI|esther|eStyle|Ezooms|facebookexternalhit|facebook|facebot|fastcrawler|FatBot|FDSE|FELIX IDE|fetch|fido|find|Firefly|fouineur|Freecrawl|froogle|gammaSpider|gazz|gcreep|geona|Getterrobo-Plus|get|girafabot|golem|googlebot|-google|grabber|GrabNet|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|HTTrack|ia_archiver|iajabot|IDBot|Informant|InfoSeek|InfoSpiders|INGRID\/0\.1|inktomi|inspectorwww|Internet Cruiser Robot|irobot|Iron33|JBot|jcrawler|Jeeves|jobo|KDD-Explorer|KIT-Fireball|ko_yappo_robot|label-grabber|larbin|legs|libwww-perl|linkedin|Linkidator|linkwalker|Lockon|logo_gif_crawler|Lycos|m2e|majesticsEO|marvin|mattie|mediafox|mediapartners|MerzScope|MindCrawler|MJ12bot|mod_pagespeed|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|NationalDirectory|naverbot|NEC-MeshExplorer|NetcraftSurveyAgent|NetScoop|NetSeer|newscan-online|nil|none|Nutch|ObjectsSearch|Occam|openstat.ru\/Bot|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pingdom|pinterest|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|rambler|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Scrubby|Search-AU|searchprocess|search|SemrushBot|Senrigan|seznambot|Shagseeker|sharp-info-agent|sift|SimBot|Site Valet|SiteSucker|skymob|SLCrawler\/2\.0|slurp|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|spider|suke|tach_bw|TechBOT|TechnoratiSnoop|templeton|teoma|titin|topiclink|twitterbot|twitter|UdmSearch|Ukonline|UnwindFetchor|URL_Spider_SQL|urlck|urlresolver|Valkyrie libwww-perl|verticrawl|Victoria|void-bot|Voyager|VWbot_K|wapspider|WebBandit\/1\.0|webcatcher|WebCopier|WebFindBot|WebLeacher|WebMechanic|WebMoose|webquest|webreaper|webspider|webs|WebWalker|WebZip|wget|whowhere|winona|wlm|WOLP|woriobot|WWWC|XGET|xing|yahoo|YandexBot|YandexMobileBot|yandex|yeti|Zeus/i', $user_agent);
            }

            return false;
        }

        /**
         * Get IP Address
         *
         * @return string
         */
        public static function get_ip_address()
        {
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
            } else {
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip_address = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
                } else {
                    if (isset($_SERVER['HTTP_X_FORWARDED'])) {
                        $ip_address = sanitize_text_field($_SERVER['HTTP_X_FORWARDED']);
                    } else {
                        if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                            $ip_address = sanitize_text_field($_SERVER['HTTP_FORWARDED_FOR']);
                        } else {
                            if (isset($_SERVER['HTTP_FORWARDED'])) {
                                $ip_address = sanitize_text_field($_SERVER['HTTP_FORWARDED']);
                            } else {
                                if (isset($_SERVER['REMOTE_ADDR'])) {
                                    $ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR']);
                                } else {
                                    $ip_address = __('IP not found', pwh_dcfh_hc()::TEXT_DOMAIN);
                                }
                            }
                        }
                    }
                }
            }

            return $ip_address;
        }

        /**
         * Get User Agent
         *
         * @return array|null
         */
        public static function get_user_agent($agent = '')
        {
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : null;
            if (empty($user_agent)) {
                return null;
            }
            $browser = __('Browser not found', pwh_dcfh_hc()::TEXT_DOMAIN);
            $platform = __('Platforn not found', pwh_dcfh_hc()::TEXT_DOMAIN);
            $user_browser = "";
            if (preg_match('/linux/i', $user_agent)) {
                $platform = esc_html('linux');
            } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
                $platform = esc_html('mac');
            } elseif (preg_match('/windows|win32/i', $user_agent)) {
                $platform = esc_html('windows');
            }
            if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
                $browser = esc_html('Internet Explorer');
                $user_browser = esc_html('MSIE');
            } elseif (preg_match('/Firefox/i', $user_agent)) {
                $browser = esc_html('Mozilla Firefox');
                $user_browser = esc_html('Firefox');
            } elseif (preg_match('/Chrome/i', $user_agent)) {
                $browser = esc_html('Google Chrome');
                $user_browser = esc_html('Chrome');
            } elseif (preg_match('/Safari/i', $user_agent)) {
                $browser = esc_html('Apple Safari');
                $user_browser = esc_html('Safari');
            } elseif (preg_match('/Opera/i', $user_agent)) {
                $browser = esc_html('Opera');
                $user_browser = esc_html('Opera');
            } elseif (preg_match('/Netscape/i', $user_agent)) {
                $browser = esc_html('Netscape');
                $user_browser = esc_html('Netscape');
            }
            $known = ['Version', $user_browser, 'other'];
            $pattern = '#(?<browser>'.join('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
            if (preg_match_all($pattern, $user_agent, $matches)) {
                $i = count($matches['browser']);
                if ($i != 1) {
                    if (strripos($user_agent, 'Version') < strripos($user_agent, $user_browser)) {
                        $version = $matches['version'][0];
                    } else {
                        $version = $matches['version'][1];
                    }
                } else {
                    $version = $matches['version'][0];
                }
                if ($version == null || $version == '') {
                    $version = '';
                }
                $data = [
                    'agent' => $user_agent,
                    'browser' => $browser,
                    'version' => $version,
                    'platform' => $platform,
                ];
                if (!empty($agent) && isset($data[$agent])) {
                    return $data[$agent];
                }

                return $data;
            }

            return null;
        }

        /**
         * Get Key From String
         *
         * @param $key
         * @param $message
         *
         * @return mixed|null
         */
        public static function get_key_from_string($key, $message)
        {
            if (self::contains($message, [$key]) !== false) {
                preg_match_all('/'.$key.'([A-Za-z_][\w_]*)/', $message, $matches);
                if (isset($matches[0])) {
                    return $matches[0];
                }
            }

            return null;
        }

        /**
         * Images Mime Types
         *
         * @return string[]
         */
        public static function get_image_mimes()
        {
            $mimes = wp_get_mime_types();
            $image_mimes = [];
            foreach ($mimes as $mime) {
                if (strpos($mime, 'image/') !== false) {
                    $image_mimes[] = $mime;
                }
            }

            return $image_mimes;
        }

        /**
         * Get WordPress Default Cron Schedules
         *
         * @return array
         */
        public static function get_get_schedules()
        {
            $labels = [
                'hourly' => __('Every Hour', pwh_dcfh_hc()::TEXT_DOMAIN),
                'twicedaily' => __('Every 12 Hours', pwh_dcfh_hc()::TEXT_DOMAIN),
                'daily' => __('Every Day', pwh_dcfh_hc()::TEXT_DOMAIN),
                'weekly' => __('Every Week', pwh_dcfh_hc()::TEXT_DOMAIN),
                'monthly' => __('Every Month', pwh_dcfh_hc()::TEXT_DOMAIN),
            ];
            $wp_schedules = wp_get_schedules();
            // Sort Schedules With Intervals DESC
            foreach ($wp_schedules as $k_intervals => $v_intervals) {
                $sort_schedules[$k_intervals] = strtotime($v_intervals['interval']);
            }
            array_multisort($sort_schedules, SORT_DESC, $wp_schedules);
            // Get Required Data Fron Schedules
            foreach ($wp_schedules as $k => $v) {
                if ('pwh_dcfh_q4h' != $k) {
                    $schedules[$k] = isset($labels[$k]) ? $labels[$k] : $v['display'];
                }
            }

            return $schedules;
        }

        /**
         * Get WordPress Locale
         *
         * @return string
         */
        public static function get_wp_locale()
        {
            $locale_arr = explode('_', get_locale());

            return isset($locale_arr[0]) ? $locale_arr[0] : 'en';
        }

        /**
         * Get Number From String
         *
         * @param $string
         *
         * @return int|mixed
         */
        public static function get_number_from_string($string)
        {
            preg_match('/et_pb_contact_field_(\d{1,12})/', $string, $matches); // Extract Field ID
            $number = 0;
            if (isset($matches[1])) {
                $number = $matches[1];
            }

            return $number;
        }

        /**
         * Get Field Index
         *
         * @param $string
         *
         * @return mixed|string
         */
        public static function get_field_index($string)
        {

            preg_match("|\d+|", $string, $match);

            return isset($match[0]) ? $match[0] : '';
        }

        /**
         * Get Allowed Mimes Types
         *
         * @return array
         */
        public static function get_wp_allowed_mime_types()
        {
            $allowed_mime_type = [];
            foreach (get_allowed_mime_types() as $key => $value) {
                if ('css' === $key) {
                    $allowed_mime_type[$key] = $value;
                    $allowed_mime_type['htm|html'] = 'text/html';
                } elseif ('rtf' === $key) {
                    $allowed_mime_type[$key] = $value;
                    $allowed_mime_type['js'] = 'application/javascript';
                } else {
                    $allowed_mime_type[$key] = $value;
                }
            }

            return $allowed_mime_type;
        }

        /**
         * Fix Email HTML Body
         *
         * @param $content
         * @param bool $is_richtext
         *
         * @return string
         */
        public static function clean_html_email_message($content, $is_richtext = false)
        {
            if ($is_richtext) {
                $content = preg_replace('/<p[^>]*?>/', '', $content);
                $content = str_replace('</p>', '<br />', $content);
                $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
            } else {
                $content = str_replace("</p>", "\n", $content);
                $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
                $content = wp_strip_all_tags($content);
                $content = trim(stripslashes($content));
            }

            return $content;
        }

        /**
         * Unserialize String And Fixed String If Error In String
         *
         * @param $string
         *
         * @return mixed|string
         */
        public static function maybe_unserialize($string)
        {
            $string = wp_specialchars_decode($string);
            $unserialize_data = maybe_unserialize($string);
            if (false === $unserialize_data) {
                $string = preg_replace_callback('/s:(\d+):\"(.*?)\";/', function ($matches) {
                    if (isset($matches[2])) {
                        return "s:".strlen($matches[2]).':"'.$matches[2].'";';
                    }

                    return $string;
                }, $string);
                $unserialize_data = maybe_unserialize($string);
            }

            return $unserialize_data;
        }

    }
}