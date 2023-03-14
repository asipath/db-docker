<?php

    use Algolia\AlgoliaAPI;

    // Default Timezone
    date_default_timezone_set("UTC");

    include_once("config.php");
    include_once("constants.php");


    // Auto Loader
    // Todo: Should be moved to the loader.php file
    spl_autoload_register(function ($class) {
        $locations = [
            TEMPLATE_CLASSES_PATH . str_replace([ "\\Template\\", "Template\\" ], "", $class) . '.php',
            CLASSES_PATH . $class . '.php',
            CLASSES_PATH . strtolower($class) . '.php',

        ];

        foreach ($locations as $filename) {
            $filename = str_replace("\\", "/", $filename);
            if (file_exists($filename)) {
                include_once($filename);
                return true;
            }
        }
    });

    // Start Session
    session_save_path("0;777;" . ABS_PATH . "xuser_sess");
    ini_set("session.gc_maxlifetime", 3600 * 8);
    ini_set("session.cookie_path", "/");

    if (! DEBUG) {
        ini_set("session.cookie_secure", 1);
        ini_set("session.cookie_httponly", 1);
    }
    session_name(SESSION_NAME);

    // Custom Handler
    $session_handler = new SCFSessionHandler();
    session_set_save_handler($session_handler, true);
    session_start();

    $fxbenchmark = new FXBenchmark();
    $fxbenchmark->add_marker("Session Started");
    $visitor_key = User::set_visitor_key();

    PageCache::serveStaticPage();

    // CJ Affiliate
    $cookie_name = "cje";
    $domain = ".socialcatfish.com"; // use your domain, for instance ".example.com"
    $_GET_lower = array_change_key_case($_GET, CASE_LOWER);
    $cjevent = $_GET_lower["cjevent"];
    if ($cjevent) {
        setcookie($cookie_name, $cjevent, time() + (86400 * 395), "/", $domain, true, false);
    }

    // For Debugging: Remove ASAP
    //if ( isset( $_GET["qweuseridqwe"] ) ) $_SESSION["user"]["id"] = $_GET["qweuseridqwe"];

    // Connect to the Database
    $dbi = new DBI();
    $dbi->on_connection_failure = function () {
        global $module;

        $module = "maintenance";
    };
    $dbi->connect(SCF_DB_HOST, SCF_DB_USER, SCF_DB_PASSWORD, SCF_DB_NAME);
    $dbi->log_query_execution_time = false;
    $dbi->auto_retries_on_connection_lost = 5;
    $dbi->connection_lost_retry_interval = 1000000;
    $fxbenchmark->add_marker("Connected to Database");

    // Initialize AB Testing Class
    $abtester = new FXABTest\FXABTest(ABTESTING_API_END_POINT);

    // Init Vars
    SCF::init_vars();

    // GPDR MOD to block all EU traffic
    if (SCF::gdpr_block()) {
        $module = "gdpr";
    }
    $fxbenchmark->add_marker("GDPR Check Completed");

    // Command Line Modules
    if (isset($argv[1]) && "cmd_module" == $argv[1] && ! empty($argv[2])) {
        define("CMD_MODULE", true);
        $module = file_exists(MODULES_PATH . $argv[2] . ".php") ? $argv[2] : die();
    }

    if ($module == "areacode" && ! Areacode::previously_visited_areacode_page_found()) {
        $module = "404";
    }

    // Check for page cache
    PageCache::getCache();

    include_once("loader.php");
    $fxbenchmark->add_marker("loader.php Completed");

    // Minify JS/CSS
    $use_minified_assets = defined("MINIFIED_JS") && MINIFIED_JS;

    // DataSource Logging
    $datasource_request_stack = [];
    $datasource_cache_id = null;
    $datasource_cache_person_id = null;

    // Timezone
    $timezone = new DateTimeZone("America/Los_Angeles");
    $current_time_ca = new DateTime("now", $timezone);

    // VBrowser Class
    $client = new vbrowser();

    $hasher = new Phash();

    // Defaults
    $download = SYSTEM::request("download");
    $refund = SYSTEM::request("refund");
    $plan_id = SYSTEM::request("plan_id");
    $action = SYSTEM::request("action");
    $type = SYSTEM::request("type");
    $archive = SYSTEM::request("archive");
    $cmd = SYSTEM::request("cmd");
    $id = SYSTEM::request("id");
    $img_id = SYSTEM::request("img_id");
    $areacode_prefix = SYSTEM::request("prefix");
    $areacode_subprefix = SYSTEM::request("subprefix");
    $redirect = SYSTEM::request("redirect");
    $page = SYSTEM::request("page", 1);
    $matches_only = SYSTEM::request("matches", false);
    $section = SYSTEM::request("section", "");
    $user_query = SYSTEM::request("user_query", "");
    $checkout_step = SYSTEM::request("step");
    $token = SYSTEM::request("token");
    $payment_session = SYSTEM::request("sess");
    $category = SYSTEM::request("category");
    $search_keyword = SYSTEM::request("search");
    $promo = SYSTEM::request("promo");
    $nmi_token = SYSTEM::request("token-id");
    $plan_1_99 = SYSTEM::request("plan_1_99");
    $campaign = SYSTEM::request("campaign");
    $from_ca_nv = SYSTEM::request("from");
    $captions = SYSTEM::request("captions");
    $mobile_app = ! empty($_SESSION["mobile_app_user"]) ? 1 : SYSTEM::request("is_mobile_app");
    $os_type = SYSTEM::request("os_type");
    $singular = SYSTEM::request("search");

    $is_mobile_app_email = SYSTEM::request("email");
    $is_mobile_app_session = SYSTEM::request("_s");
    $is_mobile_app_plan_id = SYSTEM::request("key");

    $validated_mobile_app_user = false;
    if ($mobile_app) {
        //validate session  and key with sent email
        //$is_mobile_app_key
        //$is_mobile_app_session
        //$is_mobile_app_email

        $validated_mobile_app_user = true;
        if (empty($_SESSION["mobile_app_user"]) && ! empty($is_mobile_app_email) && $validated_mobile_app_user) {
            $user_data = User::get_by_email($is_mobile_app_email);
            if (! empty($user_data) && ! empty($user_data["id"])) {
                $user_id = $user_data["id"];
                $_SESSION["mobile_app_user"] = $user_data["id"];
                $_SESSION["mobile_app_user_data"] = $user_data;
                $user_data["walkthrough"] = 0;
            }
        } elseif (! empty($_SESSION["mobile_app_user"]) && ! empty($_SESSION["mobile_app_user_data"])) {
            $user_id = $_SESSION["mobile_app_user"];
            $user_data = $_SESSION["mobile_app_user_data"];
        }
    }


    if ($mobile_app || ! empty($_SESSION["is_mobile_app"])) {
        $_SESSION["is_mobile_app"] = true;
        $exclude_header_footer_content = true;
    }

    $amp_force_recreate_styles = false;
    $amp_error = false;

    $remove_head = false;

    // Disabled for security reasons
    //$testcase = "qweasdzxc" == SYSTEM::request( "testcase" ) ? true : false;

    // Identify AJAX Requests
    $ajax_request = SYSTEM::is_ajax_request();

    $fetch_url = SYSTEM::request_post("fetch_url");

    $accepted_post_fields = [
        "fullname", "firstname", "lastname", "email", "email_confirm", "password", "password_confirm", "card_name", "card_number", "card_cvv", "card_expiry_month", "card_expiry_year",
        "billing_firstname", "billing_lastname", "billing_address1", "billing_address2", "billing_country", "billing_city", "billing_state", "billing_postal_code", "billing_phone",
        "first_name", "last_name", "display_name", "address_1", "address_2", "city", "state", "postal_code", "country", "mobile_number", "sc_billing_phone", "phone_number", "website", "about",
        "new_email", "new_email_confirm", "profile_email", "profile_email_confirm", "current_password", "new_password_1", "new_password_2", "update", "pending", "user", "key", "g-recaptcha-response", "name", "phone",
        "subject", "issue", "message", "tos_agree", "person_name", "person_email", "person_phone", "person_social_links", "person_occupation", "person_education", "person_address", "person_money", "person_suspicion",
        "person_met", "find", "method", "query", "source", "campaign", "from_date", "to_date", "stripeToken", "url", "status", "report_agreement", "query_data", "close_image_warning", "coupon", "last_id", "investigator_user_id",
        "images", "info_needed", "value", "notes", "reason","investigator_notes", "special_notes", "file", "age", "have_met", "user_names", "how_long_communicating", "form_of_communication", "still_contact", "find_identity",
        "gp_token", "user_level", "membership_plan", "coupon_code", "coupon_type", "fixed_amount", "percentage", "opt_check", "payment_id", "signup_purpose", "from", "to" , 'payment_card', "amount", "hear_about_us", "hear_about_us_data", "bt_token",
        "cancel_purpose", "other_purpose", "plan_id", "user_manager", "ctoken", "ctoken_card_cvv", "user_notes", "emails", "phones", "ap_token", "addon_id", "pg_type", "datauser" ,"unclamed_search_from", "unsubscribe_option", "show_phone_number", "card_token", "location", "ccpa_emails", "ccpa_phones", "ccpa_state", "ccpa_url", "ccpa_middle_name", "ccpa_age",
        "how_money_sent", "video_chatted", "n_image_notes", "n_indentity_notes", "n_monetary_notes", "image_links", "phash" ,"ccpa_description" ,"ccpa_file" ,"user_id" ,"rating" ,"feedback" ,"count" ,"device_type" ,"limit" ,"description","username", "tags","profile_links"
    ];

    $post_data = [];
    foreach ($accepted_post_fields as $field) {
        $post_data[ $field ] = SYSTEM::request_post($field);
    }

    $search_params = [
        "username" => SYSTEM::request_post("username"),
        "email" => SYSTEM::request_post("email"),
        "phone" => SYSTEM::request_post("phone"),
        "full_name" => SYSTEM::request_post("full_name"),
        "first_name" => SYSTEM::request_post("first_name"),
        "last_name" => SYSTEM::request_post("last_name"),
        "middle_name" => SYSTEM::request_post("middle_name"),
        "age" => SYSTEM::request_post("age"),
        "city" => SYSTEM::request_post("city"),
        "state" => SYSTEM::request_post("state"),
        "country" => SYSTEM::request_post("country"),
        "images" => SYSTEM::get_array_key_value($_FILES, "image", []),
        "image_urls" => SYSTEM::request_post("image_url", []),
        "type" => SYSTEM::request_post("search_type", null),
        "person_id" => SYSTEM::request("person_id"),
        "query" => SYSTEM::request_post("query"),
        "url" => SYSTEM::request_post("url"),
        "sid" => SYSTEM::request("sid"),
        "view" => SYSTEM::request("view"),
        "pdata" => SYSTEM::request("pdata"),
    ];
    $search_params["tpd_request"] = ($post_data["gp_token"] && $token) ? ! empty($_SESSION["guest_progress"][ $token ]["tpd_request"]) : (in_array(substr($search_params["person_id"], 0, 1), $report_page_id_flag));

    /**************** ***************/
    //Mobile APP
    if (! empty($_SESSION["thankyou_page_data"]) && ! empty($_SESSION["mobile_app_user"])) {
        $user_id = $_SESSION["mobile_app_user"];
        $mobile_app = true;
        $exclude_header_footer_content = true;
    }
    /**************** ***************/

    if ($user_id && ! $user_data = User::get_by_id($user_id)) {
        unset($_SESSION["user"]["id"]);
        $user_id = 0;
    }
    if (! empty($user_data["account_disabled"])) {
        unset($_SESSION["user"]["id"]);
        $user_id = 0;
        SYSTEM::redirect(BASE_URL);
    }


    /**************** ***************/
    //Mobile APP
    if (! empty($_SESSION["thankyou_page_data"]) && ! empty($_SESSION["mobile_app_user"])) {
        $user_data["walkthrough"] = 0;
    }
    /**************** ***************/

    if (! $user_id) {
        $singular_param = "?search=new";
    } else {
        $singular_param = "";
    }

    // User Template
    $user_template = "2020";

    // Only 2020 template modules
    $modules_2020 = [ "dr-phil", "page-loading" ];
    $redesign_landing_modules = [ "home", "email", "phone", "username", "ras_landing", "image", "visitor_key", "image-guest", "search", "guest-progress", "register" ];
    if ((isset($_SESSION["force_template_2020"]) && in_array($module, $redesign_landing_modules)) || $user_id || in_array($module, $modules_2020) || ! empty($_GET["force_2020"])) {
        $user_template = "2020";
    }

    //Optout CCPA force redesign
    if ($module == "optout") {
        SCF::switch_to_template("2020");
    }

    if ($module == "faq") {
        SCF::switch_to_template("2020");
    }
    if ($module == "about-us") {
        SCF::switch_to_template("2020");
    }
    if ($module == "contact-us") {
        SCF::switch_to_template("2020");
    }

    // Check whether user switched to classic view
    if (isset($user_data) && (($user_data["classic_template_forced"] && ("dashboard" == $module || ("image" == $module && ! $search_params["sid"]))))) {
        $user_template = "default";
    }

    // Landing Ads
    if (! empty($_GET["ga_redesign"])) {
        $user_template = "2020";
        $_SESSION["force_template_2020"] = true;
    }

    if (! empty($amp_page)) {
        $user_template = "default";
    }

    /* AB Testing code to split between */
    if (!isset($_SESSION["ab_baselines_split"])) {
        $_SESSION["ab_baselines_split"] = $abtester->get_experiment("ab_baselines_split", session_id(), SYSTEM::bot_detected() ? "standard" : "");
    }
    if (!isset($_SESSION["ab_split_2_50"])) {
        $_SESSION["ab_split_2_50"] = $abtester->get_experiment("ab_split_2_50", session_id(), SYSTEM::bot_detected() ? "standard" : "");
    }



    $behavior_tracking_activated =  true;
    $behavior_do_not_track = (! empty($user_data["user_level"]) && 255 == $user_data["user_level"]) ? false : false;
    $behavior_tracking_on_server_logging = false;
    $behavior_url_custom_type = "";
    $behavior_current_url = 0;
    $behavior_track_visibility = [ [ "popup view",".scf-popup" ] ];
    $behavior_ignore_modules = [ "heatmap", "sitemap", "thumbnail", "recaptcha" ];
    $behavior_visitor_key = Behavior::set_visitor_key();
    $behavior_actions = [
        "a" =>  "page_requested",
        "b" =>  "page_served",
        "c" =>  "page_loaded",
        "d" =>  "element_clicked",
        "e" =>  "module_started",
        "f" =>  "template_started",
        "g" =>  "system",
        "h" =>  "module_requested",
        "i" =>  "input_typed",
        "j" =>  "execution_time"

    ];
    $behavior_assest_url = SCF::get_asset_url("module", "behavior");
    $behavior_api_url = "https://behavior.drivenio.com/";//https://194.163.128.23/ // http://scfanalytics.com/
    $behavior_api_site_key = "f4ba79e163910b2be8f7a18fd8923e91";
    $behavior_assest_timestamp = TIMESTAMP_FOR_JS_CSS;

    $behavior_heatmap = (! empty($user_data["user_level"]) && 255 == $user_data["user_level"] && "true" == SYSTEM::request("behavior_trk_heatmap", "false")) ? true : false;
    if ($behavior_heatmap) {
        $behavior_tracking_activated = false;
        $behavior_template = SYSTEM::request("template");
        $behavior_loggedin = SYSTEM::request("loggedin");
        SCF::switch_to_template($behavior_template);

        if ($behavior_loggedin == "false") {
            $user_data["user_level"] = 0;
            $user_id = 0;
        }

        //print_r($heatmap_clicks);die;

        //$heatmap_viewport = strtok( SYSTEM::request("viewport"), "-" );

        //$iframed = trim( SYSTEM::request("iframed") );
        //if( ! empty( $iframed ) || $heatmap_viewport == "1200" ){

        //} else {
        //    $module = "heatmap";
        //}
        ///
    }

    //$is_behavior_heatmap_preview_activated = Behavior::is_behavior_heatmap_preview_activated();



    $current_template_assets_url = SCF::get_asset_url();
    $default_template_assets_url = SCF::get_asset_url("template", "default");

    $current_template_path = SCF::get_template_path();
    $common_template_path = SCF::get_template_path("template", "common");
    $default_template_path = SCF::get_template_path("template", "default");
    $amp_template_path = SCF::get_template_path("template", "amp");

    $common_assets_url = SCF::get_asset_url("common");

    if ("true" == $plan_1_99) {
        $_SESSION["plan_1_99"] = true;
    }

    // Input Data
    $input_post = new ObjectProxy($_POST);
    $input_get = new ObjectProxy($_GET);
    $input_request = new ObjectProxy($_REQUEST);
    $input_files = new ObjectProxy($_FILES);

    // List of Countries
    $list_of_countries = $list_of_states = [];
    $_temp = file(ABS_PATH . "countries.txt");
    foreach ($_temp as $temp) {
        $temp = explode("\t", trim($temp));
        if (count($temp) == 2) {
            $list_of_countries[ $temp[0] ] = $temp[1];
        }
    }
    unset($_temp, $temp);

    // List of States
    $_temp = file(ABS_PATH . "states.txt");
    foreach ($_temp as $temp) {
        $temp = explode("\t", trim($temp));
        if (count($temp) == 2) {
            $list_of_states[ $temp[0] ] = $temp[1];
        }
    }
    unset($_temp, $temp);
    //$list_of_states_for_mobile_app = array_merge($list_of_states, ["" => "Other"]);
    $list_of_states_for_mobile_app = $list_of_states;

    // Age Range
    $list_of_ages = [];
    for ($i = 18; $i <= 99; $i++) {
        $list_of_ages[ $i ] = $i;
    }

    $range_of_ages = ["" => "Search All", "18 - 25" => "18 - 25", "26 - 35" => "26 - 35", "36 - 45" => "36 - 45", "46 +" => "46 +"];

    $current_search_type = SEARCH_TYPE_NAME;
    $exclude_header_footer = false;
    $exclude_header_footer_content = ! empty($exclude_header_footer_content) ? $exclude_header_footer_content : false;
    $exclude_header_content = false;
    $exclude_main_wrapper = false;
    $exclude_hire_us_button = true;
    $no_template = false;
    $sub_page = "";
    $ajax_status = [ "status" => false ];
    $why_cancel_model = true;
    $awareness_page = false;
    $ris_landing_page_new = false;
    $ris_ads = false;
    $ris_ads_blue = false;
    $ca_page = false;
    $ca_privacy = false;
    $pre_page_loading = false;
    $ads_ris_campaign = false;
    $idi_show_criminal = true;
    if (! $user_id) {
        $no_result_funnel = true;
    } else {
        $no_result_funnel = false;
    }
    $hide_ris_latest_notification = false;
    $blog_page = false;

    $header_version = "compact";
    $include_map_scripts = false;
    $include_braintree_scripts = false;
    $header_heading = $search_type_page_titles_descriptions[ SEARCH_TYPE_NAME ]["title"];
    $header_text = $search_type_page_titles_descriptions[ SEARCH_TYPE_NAME ]["description"];

    $error_messages = $success_messages = $announcement_messages = $warning_messages = [];

    // Advertisement Mod
    $http_referer = ! empty($_SERVER['HTTP_REFERER']) ? ($_SERVER['HTTP_HOST'] != parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : "") : "";

    parse_str(preg_replace("/=utm/", "utm", $_SERVER["QUERY_STRING"]), $advertisement_query);
    $advertisement_data = [
        "medium" => ! empty($advertisement_query["utm_medium"]) ? $advertisement_query["utm_medium"] : "",
        "source" => ! empty($advertisement_query["utm_source"]) ? $advertisement_query["utm_source"] : "",
        "campaign" => ! empty($advertisement_query["utm_campaign"]) ? $advertisement_query["utm_campaign"] : "",
            "referer" => $http_referer,
    ];

    if ($advertisement_data["medium"] && $advertisement_data["source"]) {
        $_SESSION["hide_none_ad_memberships"] = true;
        $_SESSION["advertisement_data"] = $advertisement_data;

    //SYSTEM::redirect( ! empty( $_SERVER["REDIRECT_URL"] ) ? $_SERVER["REDIRECT_URL"] : str_replace( $_SERVER["QUERY_STRING"], "", $_SERVER["REQUEST_URI"] ) );
    } else {
        if (empty($_SESSION["advertisement_data"]["referer"]) && ! empty($http_referer)) {
            if (empty($_SESSION["advertisement_data"])) {
                $_SESSION["advertisement_data"] = [];
            }
            $_SESSION["advertisement_data"]["referer"] = $http_referer;
        }
    }

    $current_page_url = RELATIVE_URL . "{$module}.html";

    $page_title = "Reverse Lookup to Search and Verify Identities - Social Catfish";
    $page_description = "Find anyone online using socialcatfish.com image, phone, email, name and username searches. We help you search for people and verify online connections.";
    $no_index = $no_index_do_follow = false;

    // Load Settings
    $settings = SCF::get_settings();
    $fxbenchmark->add_marker("Settings Loaded");

    // Load Payment Gateways
    if (! DEBUG) {

        // Live
        $payflowpro = new PayflowPro(PAYFLOW_PARTNER, PAYFLOW_VENDOR, PAYFLOW_USERNAME, PAYFLOW_PASSWORD, PayflowPro::PAYFLOWPRO_ENVIRONMENT_LIVE);
        $payflow_express_checkout = new PayflowExpressCheckout(PAYFLOW_PARTNER, PAYFLOW_VENDOR, PAYFLOW_USERNAME, PAYFLOW_PASSWORD, PayflowPro::PAYFLOWPRO_ENVIRONMENT_LIVE);
        $paypal_express_checkout = new PaypalExpressCheckout(PAYPAL_API_USERNAME, PAYPAL_API_PASSWORD, PAYPAL_API_SIGNATURE, PaypalExpressCheckout::PAYPAY_MERCHANT_ENVIRONMENT_LIVE);
        Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        $stripe_publishable_key = STRIPE_PUBLISHABLE_KEY;
        $braintree = new BraintreePayments("production", BRAINTREE_LIVE_MERCHANT_ID, BRAINTREE_LIVE_PUBLIC_KEY, BRAINTREE_LIVE_PRIVATE_KEY);

        $amazonpay_config = array(
            'merchant_id'   =>	AMAZONPAY_LIVE_MERCHANT_ID,
            'access_key'    =>	AMAZONPAY_LIVE_ACCESS_KEY,
            'secret_key'    =>	AMAZONPAY_LIVE_SECRET_KEY,
            'client_id'     =>	AMAZONPAY_LIVE_CLIENT_ID,
            'region'        =>	'us',
            'currency_code' =>	'USD',
            'sandbox'       =>	false
        );
        $amazonpay_widget = 'https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js';
        $amazonpay = new AmazonPay\AmazonPayGateway();

        $nmi = new NmiPayments();
    } else {

        // Sandbox
        $payflowpro = new PayflowPro(PAYFLOW_PARTNER, PAYFLOW_VENDOR, PAYFLOW_USERNAME, PAYFLOW_PASSWORD, PayflowPro::PAYFLOWPRO_ENVIRONMENT_SANDBOX);
        $payflow_express_checkout = new PayflowExpressCheckout(PAYFLOW_PARTNER, PAYFLOW_VENDOR, PAYFLOW_USERNAME, PAYFLOW_PASSWORD, PayflowPro::PAYFLOWPRO_ENVIRONMENT_SANDBOX);
        $paypal_express_checkout = new PaypalExpressCheckout(PAYPAL_API_USERNAME, PAYPAL_API_PASSWORD, PAYPAL_API_SIGNATURE, PaypalExpressCheckout::PAYPAY_MERCHANT_ENVIRONMENT_SANDBOX);
        Stripe\Stripe::setApiKey(STRIPE_TEST_SECRET_KEY);
        $stripe_publishable_key = STRIPE_TEST_PUBLISHABLE_KEY;
        $braintree = new BraintreePayments("sandbox", BRAINTREE_TEST_MERCHANT_ID, BRAINTREE_TEST_PUBLIC_KEY, BRAINTREE_TEST_PRIVATE_KEY);

        $amazonpay_config = array(
            'merchant_id'   =>	AMAZONPAY_TEST_MERCHANT_ID,
            'access_key'    =>	AMAZONPAY_TEST_ACCESS_KEY,
            'secret_key'    =>	AMAZONPAY_TEST_SECRET_KEY,
            'client_id'     =>	AMAZONPAY_TEST_CLIENT_ID,
            'region'        =>	'us',
            'currency_code' =>	'USD',
            'sandbox'       =>	true
        );
        $amazonpay_widget = 'https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js';
        $amazonpay = new AmazonPay\AmazonPayGateway();

        $nmi = new NmiPayments();
    }
    $stripe = new StripeWrapper();
    $fxbenchmark->add_marker("Payment Gateways Loaded");

    // Get Proxy List
    $proxy_list = SCF::get_proxy_list();
    $fxbenchmark->add_marker("Proxies Loaded");

    // Extract Session
    empty($_SESSION["extract"]) || extract($_SESSION["extract"]);
    unset($_SESSION["extract"]);

    //if( ! empty( $error_messages ) )
    //    if( is_array( $error_messages ) )  Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "error_messages::" . implode(", " , $error_messages), ["errors", "general"] );
    //    else Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "error_message::{$error_messages}", ["errors", "general"] );

    //if( ! empty( $success_messages ) )
    //    if( is_array( $success_messages ) )  Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "success_messages::" . implode(", " , $success_messages ), ["messages", "general", "success"] );
    //    else Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "success_message::{$success_messages}", ["messages", "general", "success"] );

    // Google Tag Manager
    $gtm_events["user_data"] = [
        "user_id" => $user_id ?: "",
        "email" => $user_id ? sha1($user_data["email"]) : "",
        "new_user" => ! empty($new_user),
    ];

    // Check User Session
    if ($user_id) {

        // Show ReCaptcha for every 5 searches after the user has done 250 searches
        if (! empty($user_data["search_counter"]) && $user_data["search_counter"] > 250 && ($user_data["search_counter"] % 5 == 0) && ! DEBUG) {
            $social_search_show_recaptcha = true;
        }
        if ($_SESSION["criminal_search_count"]<1) {
            $_SESSION["criminal_search_count"] = 0;
        }
        if ($_SESSION["criminal_search_count"] % 5 == 0 &&  $_SESSION["criminal_search_count"] != 0 && ! DEBUG) {
            $criminal_search_show_recaptcha = true;
        }
        //Check for unsuccessfull transaction users and show popup to redo the payment { Issue CSI-373 03-may-2018 }
        $affected_csi_373_has_image_tokens = false;
        $affected_csi_373_has_regular_tokens = false;
        $affected_issue_csi_373 = false;
        if (in_array($user_data[ 'created' ], [ '2018-05-02', '2018-05-03' ])) {
            $affected_plans = Membership::get_user_plans($user_id, true);

            if (! empty($affected_plans)) {
                foreach ($affected_plans as $k => $v) {
                    if ($v[ 'subscription' ] && $v[ 'payment_gateway' ] == 'stripe' && empty($v[ 'subscription_profile' ])) {
                        $affected_issue_csi_373 = true;
                        if (! empty($v["has_image_tokens"])) {
                            $affected_csi_373_has_image_tokens = true;
                        }
                        if (! empty($v["has_regular_tokens"])) {
                            $affected_csi_373_has_regular_tokens = true;
                        }
                    }
                }
            }
        }
        //END { Issue CSI-373 03-may-2018 }

        $user_tokens = USER::tokens($user_id);
        User::set_user_token_vars();
        User::set_user_available_token_vars();

        //****Re active token after cancel subscription */
        if (! $user_has_search_tokens || ! $user_has_image_tokens || ! $user_has_criminal_tokens) {
            $tmp_user_tokens = User::get_token_after_cancel_subscription($user_id);
            foreach ($user_tokens as $key => $arr_value) {
                if ($arr_value["available"] <= 0) {
                    $user_tokens[$key]["available"] = $tmp_user_tokens[$key]["available"];
                    $user_tokens[$key]["total"] = $tmp_user_tokens[$key]["total"];
                }
            }
        }
        User::set_user_token_vars();
        User::set_user_available_token_vars();

        $behavior_message = [];
        if (! empty($user_has_search_tokens)) {
            $behavior_message[] = "regular";
        }
        if (! empty($user_has_image_tokens)) {
            $behavior_message[] = "image";
        }
        if (! empty($user_has_criminal_tokens)) {
            $behavior_message[] = "criminal";
        }
        if (! empty($user_has_address_search_tokens)) {
            $behavior_message[] = "address";
        }
        if (! $ajax_request && ! in_array($module, [ "ajax", "404" ])) {
            if (! empty($behavior_message)) {
                Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "User has " . implode(" ,", $behavior_message) . " tokens", ["messages", "tokens", "available"]);
            } else {
                Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "User has zero tokens", ["errors", "user has zero tokens page loads", ""]);
            }
        }

        if (! $user_has_search_tokens || ! $user_has_image_tokens || ! $user_has_criminal_tokens) { // Make sure Unlimited Plans can search until the end of the paid period.
            if ($token_data = USER::cancelled_unlimited_plans_within_paid_days($user_id, $user_tokens)) {
                $user_tokens = $token_data;
                User::set_user_token_vars();
                User::set_user_available_token_vars();

                $behavior_message = [];
                if (! empty($user_has_search_tokens)) {
                    $behavior_message[] = "regular";
                }
                if (! empty($user_has_image_tokens)) {
                    $behavior_message[] = "image";
                }
                if (! empty($user_has_criminal_tokens)) {
                    $behavior_message[] = "criminal";
                }
                if (! empty($user_has_address_search_tokens)) {
                    $behavior_message[] = "address";
                }
                if (! $ajax_request && ! in_array($module, [ "ajax", "404" ])) {
                    if (! empty($behavior_message)) {
                        Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "User within paid days and has " . implode(" ,", $behavior_message) . " tokens", ["messages", "tokens", "available"]);
                    }
                }
            }
        }

        $infinity_tokens = [];
        foreach ($search_types as $search_type_id => $search_type) {
            $infinity_tokens[ $search_type_id ] = ($user_tokens[ $search_type ]["total"] > 10000);
        }

        $late_payment_plans = (255 == $user_data["user_level"]) ? [ "general_search" => [], "image_search" => [] ] : Membership::late_payment_active_plans($user_id);

        $get_captcha = Search::get_captcha_count($user_id);
        $image_verification_required = ($get_captcha >= 10);
    } else {
        $user_has_search_tokens = $user_has_image_tokens = false;
    }
    $fxbenchmark->add_marker("User Check Completed");

    $user_has_combine_tokens = false;
    $current_memberships = [];
    $current_memberships_categories = [];
    $active_plans = [];
    $active_subscription_plans = [];
    $dr_phil_free_plan_active = false;
    $dr_phil_upgraded_plan_active = false;
    $dr_phil_promo_end_date = "2021-09-03";
    $active_plans_non_trial = [];
    $active_subscription_uncanceled = [];


    //Show cross sell popups based on search tokens
    if ($user_id && $user_data["user_level"] != 255) {
        $name_cross_sell_popup = 0;
        $username_cross_sell_popup = 0;
        $email_cross_sell_popup = 0;
        $phone_cross_sell_popup = 0;
        $address_cross_sell_popup = 0;
        $criminal_cross_sell_popup = 0;
        $image_cross_sell_popup = 0;

        if ($user_data["user_level"] != 255) {
            if ($user_tokens[ "name" ]["available"] <= 0) {
                $name_cross_sell_popup = 1;
            }
            if ($user_tokens[ "username"]["available"] <= 0) {
                $username_cross_sell_popup = 1;
            }
            if ($user_tokens[ "email"]["available"] <= 0) {
                $email_cross_sell_popup = 1;
            }
            if ($user_tokens[ "phone"]["available"] <= 0) {
                $phone_cross_sell_popup = 1;
            }
            if ($user_tokens[ "address"]["available"] <= 0) {
                $address_cross_sell_popup = 1;
            }
            if ($user_tokens[ "criminal"]["available"] <= 0) {
                $criminal_cross_sell_popup = 1;
            }
            if ($user_tokens[ "image"]["available"] <= 0) {
                $image_cross_sell_popup = 1;
            }
        }
    }


    if ($user_id) {
        $all_combine_plans = array( PLAN_UNLIMITED_5496, PLAN_UNLIMITED_MONTHLY_1, PLAN_UNLIMITED_MONTHLY_3, PLAN_UNLIMITED_MONTHLY_6, PLAN_DR_PHIL_SPECIAL_OFFER );
        if ($get_user_plans = Membership::get_user_plans($user_id, true)) {
            foreach ($get_user_plans as $row) {
                switch ($row["start_recurring_period"]) {

                    case "days": time() > strtotime($row["date"] . " +{$row["start_recurring_frequency"]} days") ? $active_plans_non_trial[] = $row : ""; break;
                    case "mont": time() > strtotime($row["date"] . " +{$row["start_recurring_frequency"]} months") ? $active_plans_non_trial[] = $row : ""; break;
                    case "year": time() > strtotime($row["date"] . " +{$row["start_recurring_frequency"]} years") ? $active_plans_non_trial[] = $row : ""; break;

                }

                if ($row["recurring_amount"] > 0) {
                    $active_subscription_plans[] = $row;
                }
                if ($row["recurring_amount"] > 0 && $row["active"] == 1) {
                    $active_subscription_uncanceled[] = $row;
                }




                if ($row["membership_id"] != IN_DEPTH_SEARCH_MEMBERSHIP_ID_NEW) {
                    $active_plans[] = $row["id"];
                }
                if ($row["membership_id"] != IN_DEPTH_SEARCH_MEMBERSHIP_ID_NEW
                && $row["membership_id"] != IN_DEPTH_24_HOUR_SERVICE
                && $row["membership_id"] != PLAN_PREMIUM_DATA
                && $row["membership_id"] != PLAN_PREMIUM_DATA_MONTHLY
                && $row["membership_id"] != PLAN_UNLIMITED_5496) {
                    $current_memberships[] = $row["id"];
                }
                if (in_array($row['membership_id'], $all_combine_plans)) {
                    $user_has_combine_tokens = true;
                }
                if (!in_array($row["membership_category"], $current_memberships_categories)) {
                    $current_memberships_categories[] = $row["membership_category"];
                }
            }
        }

        if ($behavior_tracking_activated && ! $behavior_do_not_track) {
            $behavior_tracking_membership_categories = [];
            if (! empty($current_memberships_categories)) {
                foreach ($current_memberships_categories as $cur_mk => $cur_mv) {
                    if ($cur_mv == MEMBERSHIP_CATEGORY_REGULAR) {
                        $behavior_tracking_membership_categories[] = "regular";
                    }
                    if ($cur_mv == MEMBERSHIP_CATEGORY_IMAGE) {
                        $behavior_tracking_membership_categories[] = "image";
                    }
                    if ($cur_mv == MEMBERSHIP_CATEGORY_COMBINED) {
                        $behavior_tracking_membership_categories[] = "combined";
                    }
                    if ($cur_mv == MEMBERSHIP_CATEGORY_HIRE_US) {
                        $behavior_tracking_membership_categories[] = "hire_us";
                    }
                    if ($cur_mv == MEMBERSHIP_CATEGORY_ADDON) {
                        $behavior_tracking_membership_categories[] = "addon";
                    }
                    if ($cur_mv == MEMBERSHIP_CATEGORY_RAS) {
                        $behavior_tracking_membership_categories[] = "ras";
                    }
                    if ($cur_mv == MEMBERSHIP_CATEGORY_CRIMINAL_RECORDS) {
                        $behavior_tracking_membership_categories[] = "criminal";
                    }
                }
            }
            sort($behavior_tracking_membership_categories);
            $behavior_tracking_membership_categories = implode(",", $behavior_tracking_membership_categories);

            $behavior_tracking_user_ltv = User::get_user_ltv();
        }

        //Check all user plans including free plans
        if ($get_user_plans = Membership::get_user_plans($user_id)) {
            foreach ($get_user_plans as $row) {
                if ($row["membership_id"] == PLAN_DR_PHIL_TRIAL_PLAN) {
                    $dr_phil_free_plan_active = true;
                }
                if ($row["membership_id"] == PLAN_DR_PHIL_SPECIAL_OFFER) {
                    $dr_phil_upgraded_plan_active = true;
                }
            }
        }

        $dr_phil_free_plan_expired = false;
        if ($dr_phil_upgraded_plan_active) {
            $dr_phil_free_plan_active = false;
        }
        if ($current_time_ca->format('Y-m-d') > $dr_phil_promo_end_date) {
            if ($dr_phil_free_plan_active) {
                $dr_phil_free_plan_expired = true;
            }
            $dr_phil_free_plan_active = false;
        }
        if ($dr_phil_free_plan_active && ! $user_has_address_search_tokens_available && ! $user_has_search_tokens_available && ! $user_has_image_tokens_available && ! $user_has_criminal_tokens_available) {
            if (! Membership::check_email_for_sendy_list(SENDY_LIST_DR_PHIL_SHOW_ZERO_TOKENS, $user_data["email"])) {
                $sendy = SYSTEM::loadsendy();
                $sendy->setListId(SENDY_LIST_DR_PHIL_SHOW_ZERO_TOKENS);
                $sendy->subscribe(array(
                    'email' => $user_data["email"],
                    "name" => (! empty($user_data["first_name"]) ? $user_data["first_name"] . " " : "") . (! empty($user_data["last_name"]) ? $user_data["last_name"] : "")
                ));
            }
        }
    }

    if ($user_id && ! empty($user_data["recaptcha_disabled"])) {
        $image_verification_required = $social_search_show_recaptcha = $criminal_search_show_recaptcha = false;
    }

    // Include the following Popups
    $include_popup = [ "searching_popup2", "select_state", "select_city", "select_age", ($user_id ? "address_search_progress" : "ras_proccess"), "refine_address_search","autocomplete_suggestion","no_address_suggestions" ];
    if ($user_id && (! empty($image_verification_required) || ! empty($social_search_show_recaptcha || ! empty($criminal_search_show_recaptcha)))) {
        $include_popup[] = "secure_recaptcha";
    }

    $include_popup[] = "blog_sign_up_exit";


   if ($module == "image" && empty($_SESSION["ris_tips_popup_showed"]) && empty($search_params["sid"])) {
       if (!empty($user_id)) {
           if (!User::get_meta($user_id, 'disable_ris_tips_popup')) {
               $include_popup[] =  "ris_upload";
               $_SESSION["ris_tips_popup_showed"] = true;
           };
       }
       //else{
        //    $include_popup[] =  "ris_upload";
        //    $_SESSION["ris_tips_popup_showed"] = true;
        //}
   }

    /** Redirect Privacy lock */
    if (empty($user_id) && "dashboard" == $module && "privacy_lock" == $section && empty($_SESSION["redirect_privacy_lock"])) {
        $_SESSION["redirect_privacy_lock"] = true;
    }


    // Unclaimed Funds Enabled
    $unclaimed_funds_enabled = false;
    var_dump($get_user_plans);exit;
    foreach ($get_user_plans as $plan) {
        if (strtotime($plan["date"]) <= 1604102399) {
            if (in_array($plan["membership_category"], [ MEMBERSHIP_CATEGORY_REGULAR, MEMBERSHIP_CATEGORY_IMAGE, MEMBERSHIP_CATEGORY_COMBINED ])) {
                $unclaimed_funds_enabled = true;
                break;
            }
        } elseif (MEMBERSHIP_CATEGORY_REGULAR == $plan["membership_category"]) {
            $unclaimed_funds_enabled = true;
            break;
        }
    }

    // Credit Card Expiry
    $start_year = date("Y");
    $end_year = $start_year + 10;
    $cc_expiry_years = [ "" => "Year" ];
    for ($i = $start_year; $i <= $end_year; $i++) {
        $cc_expiry_years[ $i ] = $i;
    }
    $cc_expiry_months = [ "" => "Month" ];
    for ($i = 1; $i <= 12; $i++) {
        $cc_expiry_months[ (PAYMENT_GATEWAY_DEFAULT == "nmi" ? sprintf("%02d", $i) : $i) ] = sprintf("%02d", $i);
    }
    unset($i, $start_year, $end_year);

    $directory_section_list = "123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $directory_list_name = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    // For single results, we need the counts for each section found.
    $single_results_field_list_for_counts = [
        "names" => "Names",
        "emails" => "Emails",
        "locations" => "Addresses",
        "phones" => "Phones",
        "images" => "Images",
        "usernames" => "Usernames",
        "relationships" => "Relationships",
        "urls" => "Social Profiles",
        "bankruptcy" => "Bankruptcies",
        "judgment" => "Judgments",
        "lien" => "Liens",
        "professional" => "Professional Licenses",
        "criminal" => "Possible Criminal",
    ];

    $social_profile_link_types = array_fill_keys([ "google-plus", "facebook", "youtube", "pinterest", "linked-in", "twitter", "instagram", "myspace", "vk" ], 1);
    $fxbenchmark->add_marker("Initiated Default Variables");

    /** TODO: Set paused account here */
    $paused_account_regular = Membership::check_if_paused_plan_activated();
    $paused_account_ris = Membership::check_if_paused_plan_activated("ris");
    $active_memberships = User::get_active_plan_ids($user_id, true);

    $both_ris_and_regular_plans_available = ! empty($active_memberships[ PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL ]) && ! empty($active_memberships[ PLAN_UNLIMITED_IMAGE_5_DAY_TRIAL ]) ? true : false;

    $landing_pages = [ "home", "email", "phone", "username", "ras_landing", "image" ];
    $ris_active_plans_only = empty($active_memberships[ PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL ]) && ! empty($active_memberships[ PLAN_UNLIMITED_IMAGE_5_DAY_TRIAL ]) && ! in_array($module, $landing_pages) ? true : false;

    $swtched_account_regular = ! empty($active_memberships[ PLAN_UNLIMITED_GENERAL_SWITCHED ]) ? true : false;
    $swtched_account_ris = ! empty($active_memberships[ PLAN_UNLIMITED_IMAGE_SWITCHED ]) ? true : false;
    $early_warn = user::get_restrictions_agreement($user_id);
    $key = AlgoliaAPI::getPublicKey();

    /** Best People User  */
    $bsp_user = empty($_SESSION["bsp_user"]) ? false : $_SESSION["bsp_user"];


    ////////////////////////////////////////////////////////////
    //$active_plans_non_trial = [];
    $privacy_lock_activated = true;
    $privacy_lock_activated = ! empty($user_data["privacy_lock"]) ? true : $privacy_lock_activated;
    //$privacy_lock_user_action_taken = ! empty( $user_data["privacy_lock"] ) ? true : false;
    $privacy_lock_user_action_taken = $user_data["privacy_lock"];
    $privacy_lock_for_old_user = $user_data["privacy_lock"] === null ? true : false;
    if ($privacy_lock_activated) {
        $privacy_lock_emails = ! empty($user_data["privacy_lock_emails"]) ? @unserialize($user_data["privacy_lock_emails"]) : [ 1 => [ $user_data["email"], $user_data["privacy_lock"] ] ];
        $privacy_lock_emails_currently_active = 0;
        foreach ($privacy_lock_emails as $ple_k => $ple_v) {
            if (! empty($ple_v[1]) && $ple_v[1] == 2) {
                $privacy_lock_emails_currently_active++;
            }
        }
        if ("dashboard" == $module) {
            $privacy_lock_history = PWNED::get_user_pawned_data($user_id);

            //print_r($privacy_lock_history);die;
        }

        if (! empty($_SESSION["privacy_lock_first_visit"])) {
            $privacy_lock_first_visit = true;
        } else {
            $privacy_lock_first_visit = false;
        }
    }

    $ris_latest_notification = [];
    if ($user_id && "search_history" != $section && ! $search_params["sid"]) {
        $ris_latest_notification = Search::ris_panding_notification($user_id, 1);
    }


    #######Overide User Tokens by special request = CSI-5626
    $special_user_token_override = [
        "dtoddboatwright777@gmail.com" => [
            "expire_on" =>  "2023-07-29",
            "username"  =>  100000,
            "email"  =>  100000,
            "name"  =>  100000,
            "phone"  =>  100000,
            "image" =>  100000
        ],
        "5225ford@gmail.com" => [
            "expire_on" =>  "2023-07-29",
            "username"  =>  100000,
            "email"  =>  100000,
            "name"  =>  100000,
            "phone"  =>  100000,
            "image" =>  100000
        ],
        "chris.septon@gmail.com" => [
            "expire_on" =>  "2023-07-29",
            "username"  =>  100000,
            "email"  =>  100000,
            "name"  =>  100000,
            "phone"  =>  100000,
            "image" =>  100000
        ],
        "pinked0110@gmail.com" => [
            "expire_on" =>  "2023-07-29",
            "username"  =>  100000,
            "email"  =>  100000,
            "name"  =>  100000,
            "phone"  =>  100000,
            "image" =>  100000
        ],
        "ruwanpereratest7@appearen.com" => [
            "expire_on" =>  "2022-09-25",
            "username"  =>  550,
            "email"  =>  550,
            "name"  =>  550,
            "phone"  =>  550,
            "image" =>  550,
            "address"   =>  550,
            "criminal"  =>  550
        ],
    ];


    $user_info_overide = [];
    $special_tokens = user::is_special_user_token_override();
    if ($user_data["user_level"] == 255 && $section == "users" && ! empty(SYSTEM::get_request_value("edit"))) {
        $searched_user_data = user::get_user_data_by_id(SYSTEM::get_request_value("edit"));
        if (! empty($searched_user_data["email"])) {
            $special_tokens = user::is_special_user_token_override($searched_user_data["email"]);
        }
    }
    if (! empty($special_tokens)) {
        foreach ($special_tokens as $special_tokensK => $special_tokensV) {
            $user_tokens[$special_tokensK] = ["available" => $special_tokensV, "total" => $special_tokensV ];
            ${$special_tokensK . "_cross_sell_popup"} = 0;
            $user_info_overide["tokens_" . $special_tokensK] = $special_tokensV;
        }
    }
    #######End Overide User Tokens by special request = CSI-5626

    if ($user_id) {
        $get_nps_feedback = CustomerFeedback::get_nps_rating($user_id);
        $last_feedback_score = $get_nps_feedback["score"];
    }

    Behavior::log_action("page_requested");
  //  if ( SCF::gdpr_block() ) $exclude_header_footer = true;
  $_SESSION["ipq_pwned_data_enrich"]='both';
  //none,both,ipq,pwned
  if (!empty($_GET["ipq_pwned_data"])) {
      $_SESSION["ipq_pwned_data_enrich"]=$_GET["ipq_pwned_data"];
  }
