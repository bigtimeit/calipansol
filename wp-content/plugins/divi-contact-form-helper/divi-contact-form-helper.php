<?php
/*
  * Plugin Name: Divi Contact Form Helper
  * Description: Upgrade the Divi Contact Form with tons of new settings and premium features like file uploads, date & time picker, custom subject line, confirmation emails, Zapier integration, save submissions to database, SMTP, icons, new merge tags, dashboard stats, and so much more!.
  * Author: <a href="https://pakwebhouse.com/">Pak Web House</a> & <a href="https://www.peeayecreative.com/">Pee-Aye Creative</a>
  * Update URI: https://elegantthemes.com/
  * Text Domain: pwh-dcfh
  * Domain Path: /languages/
  * Version: 1.3
  * Requires at least: 5.x
  * Requires PHP: 7.1
  * DocumentationURI: https://www.peeayecreative.com/docs/divi-contact-form-helper/
  * License:  GPL2
  * License URI: https://www.gnu.org/licenses/gpl-2.0.html
  *
  * You should have received a copy of the GNU General Public License
  * along with Divi Contact Form Helper. If not, see <https://www.gnu.org/licenses/>.
  *
  * @since     1.0.0
  * @author    Pak Web House & Pee-Aye Creative
  * @license   GPL-2.0+
  * @copyright Copyright (c) 2022, Divi Contact Form Helper
*/

namespace PWH_DCFH;

use PWH_DCFH\App\Controllers\PWH_DCFH_Load;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
// Plugin File Name
if (!defined('PWH_DCFH_PLUGIN_FILE')):
    define('PWH_DCFH_PLUGIN_FILE', __FILE__);
endif;
// Plugin Basename
if (!defined('PWH_DCFH_PLUGIN_BASENAME')):
    define('PWH_DCFH_PLUGIN_BASENAME', plugin_basename(__FILE__));
endif;
// Plugin Dev Mod
if (!defined('PWH_DCFH_PLUGIN_DEV_MOD')):
    define('PWH_DCFH_PLUGIN_DEV_MOD', false);
endif;
// Plugin Required Bootstrap
require_once __DIR__.'/bootstrap.php';
// Plugin Activation
if (!function_exists('pwh_dcfh_plugin_activation')):
    function pwh_dcfh_plugin_activation($network_wide)
    {
        App\Base\PWH_DCFH_Activate::activate($network_wide);
    }

    register_activation_hook(__FILE__, __NAMESPACE__.'\pwh_dcfh_plugin_activation');
endif;
// Plugin Deactivation
if (!function_exists('pwh_dcfh_plugin_deactivation')):
    function pwh_dcfh_plugin_deactivation($network_wide)
    {
        App\Base\PWH_DCFH_Deactivate::deactivate($network_wide);
    }

    register_deactivation_hook(__FILE__, __NAMESPACE__.'\pwh_dcfh_plugin_deactivation');
endif;
// Plugin Translation Ready
if (!function_exists('pwh_dcfh_text_domain')):
    function pwh_dcfh_text_domain()
    {
        load_plugin_textdomain(pwh_dcfh_hc()::TEXT_DOMAIN, false, wp_basename(__DIR__).'/languages/');
    }

    add_action('init', __NAMESPACE__.'\pwh_dcfh_text_domain');
endif;
// Plugin Services
add_action('plugins_loaded', [new PWH_DCFH_Load(), 'register_services']);