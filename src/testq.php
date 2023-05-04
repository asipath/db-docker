<?php

//coment
class SCF
{
    public static function init_vars()
    {

        $init = [
            "amp_page" => SYSTEM::request("amp"),
            "module" => SYSTEM::request_get("module", "home"),
            "user_id" => isset($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : 0,
        ];

        $GLOBALS += $init;
    }

    public static function get_settings()
    {

        global $dbi;

        $settings = [];

        $query = sprintf("SELECT * FROM %s", DB_TBL_SETTING);
        $results = $dbi->query_to_multi_array($query);

        foreach ($results as $result) {
            $settings[$result["key"]] = $result["value"];
        }

        return $settings;
    }

    public static function update_setting($setting, $value)
    {

        global $dbi;

        $data = ["key" => $setting, "value" => $value];

        $dbi->insert_update(DB_TBL_SETTING, $data, $data);
    }

    public static function get_proxy_list()
    {

        global $dbi;

        $query = sprintf("SELECT ip, port, username, password FROM %s", DB_TBL_PROXY);
        return $dbi->query_to_multi_array($query);
    }

    public static function get_random_proxy()
    {

        global $proxy_list;

        return $proxy_list[array_rand($proxy_list)];
    }

    public static function get_template_path($section = "", $template_module_name = "")
    {

        static $accepted_sections;

        if (empty($accepted_sections)) {
            $accepted_sections = array_fill_keys(["template", "common", "module"], 1);
        }

        if (!isset($accepted_sections[$section])) {
            $section = "template";
        }

        if ("template" == $section) {
            if (!$template_module_name) {
                $template_module_name = $GLOBALS["user_template"];
            }
            $path = "main" . DIRECTORY_SEPARATOR . $template_module_name . DIRECTORY_SEPARATOR;
        } elseif ("module" == $section) {
            $path = $section . DIRECTORY_SEPARATOR . $template_module_name . DIRECTORY_SEPARATOR;
        } else {
            $path = $section . DIRECTORY_SEPARATOR;
        }

        return TEMPLATE_PATH . $path;
    }

    public static function get_asset_path($section = "", $template_module_name = "")
    {

        $path = self::get_asset_url($section, $template_module_name);
        return str_replace(ASSETS_URL . "/", ASSETS_PATH, $path) . DIRECTORY_SEPARATOR;
    }

    public static function get_asset_url($section = "", $template_module_name = "")
    {

        static $accepted_sections;

        if (empty($accepted_sections)) {
            $accepted_sections = array_fill_keys(["template", "common", "module"], 1);
        }

        if (!isset($accepted_sections[$section])) {
            $section = "template";
        }

        if ("template" == $section) {
            if (!$template_module_name) {
                $template_module_name = $GLOBALS["user_template"];
            }
            $path = $section . '/' . $template_module_name;
        } elseif ("module" == $section) {
            $path = $section . '/' . $template_module_name;
        } else {
            $path = $section;
        }

        return ASSETS_URL . '/' . $path;
    }

    public static function get_fail_safe_template_path($filename, $template_name, $fail_over_template = "default")
    {

        $path = self::get_template_path("template", $template_name);

        return file_exists($path . $filename) ? $path . $filename : self::get_template_path("template", $fail_over_template) . $filename;
    }

    public static function switch_to_template($template)
    {

        global $user_template, $current_template_path, $current_template_assets_url;

        $user_template = $template;
        $current_template_path = SCF::get_template_path();
        $current_template_assets_url = SCF::get_asset_url();

        Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "switch to template::" . $template, ["messages", "switch to template", $template]);
    }

    public static function switch_to_default_template()
    {

        self::switch_to_template("default");
    }

    /**
     * Get a Mail Object
     *
     * @return PHPMailer
     */
    public static function get_mailer()
    {

        $mailer = new PHPMailer(false);
        $mailer->isSMTP();
        $mailer->SMTPAuth = true;
        $mailer->Timeout = 30;
        //$mailer->SMTPSecure = false;
        //$mailer->SMTPAutoTLS = false;
        $mailer->SMTPSecure = "tls";
        $mailer->Host = MAIL_HOST;
        $mailer->Port = MAIL_PORT;
        $mailer->Username = MAIL_USERNAME;
        $mailer->Password = MAIL_PASSWORD;
        $mailer->setFrom(MAIL_FROM_ADDRESS, "SocialCatfish");

        return $mailer;
    }

    public static function get_mail_template($template, $data, $strip_tags = true)
    {

        $behavior_data = $data;
        $behavior_data["title"] = !empty($behavior_data["title"]) ? $behavior_data["title"] : ucwords(str_replace(["-", "_"], " ", $template));
        Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "{$template}::" . http_build_query($behavior_data, '', ', '), ["messages", "setting up email template to send", $template]);

        $template_file = EMAIL_TEMPLATES_PATH . $template . ".html";
        $main_email_template = EMAIL_TEMPLATES_PATH . "template-standard.html";

        $data["home_link"] = BASE_URL;
        $data["logo"] = BASE_URL . TEMPLATE_DIR . "/images/email-logo.png";

        $email_title = !empty($data["title"]) ? $data["title"] : ucwords(str_replace(["-", "_"], " ", $template));

        $content = "";
        if (file_exists($template_file)) {
            $content = file_get_contents($template_file);
            foreach ($data as $key => $value) {
                $value = $strip_tags ? nl2br(strip_tags($value)) : nl2br($value);
                $content = str_replace("{{$key}}", $value, $content);
                unset($data[$key]);
            }
        }

        $data["main_content"] = $content;
        $content = file_get_contents($main_email_template);
        $data["title"] = $email_title;


        if (substr($template, 0, 18) === "override_template_") {
            $content = $data["main_content"];
        } else {
            foreach ($data as $key => $value) {
                if ("main_content" != $key) {
                    $value = nl2br(strip_tags($value));
                }

                $content = preg_replace("/\{" . preg_quote($key, "/") . "\}/", addcslashes($value, '\\$'), $content);
            }

            $content = preg_replace("/\{" . preg_quote($key, "/") . "\}/", addcslashes($value, '\\$'), $content);
        }


        return [
            "text" => preg_replace("/\<[br \/]+\>/i", "\r\n", strip_tags($content, "br")),
            "html" => $content,
        ];
    }

    public static function get_latest_blog_posts($posts = 7)
    {

        global $dbi;

        $sql = sprintf("SELECT p.post_title, p.post_date, u.display_name, p.post_name, p.post_content, a.guid FROM %s p INNER JOIN %s u ON p.post_author = u.ID INNER JOIN %s m ON p.ID = m.post_id INNER JOIN %s a ON m.meta_value = a.ID WHERE p.post_type = 'post' AND p.post_status = 'publish' AND m.meta_key = '_thumbnail_id' ORDER BY p.post_date DESC LIMIT 0, %d", DB_TBL_WP_POSTS, DB_TBL_WP_USERS, DB_TBL_WP_POSTMETA, DB_TBL_WP_POSTS, $posts);
        $results = $dbi->query_to_multi_array($sql);

        foreach ($results as $index => $post) {
            $post["post_content"] = explode("<!--more-->", $post["post_content"]);
            $post["post_content"] = preg_replace("/\[.*?\]/", "", strip_tags(array_shift($post["post_content"])));
            if (strlen($post["post_content"]) > 150) {
                $post["post_content"] = substr($post["post_content"], 0, 150) . " ...";
            }
            $results[$index]["post_content"] = $post["post_content"];
            $results[$index]["comment_count"] = 0;
        }
        return $results;
    }

    public static function get_all_blog_posts()
    {

        global $dbi;

        $sql = sprintf("SELECT post_title, post_name FROM %s WHERE ( post_type = 'page' OR post_type = 'post' ) AND post_status = 'publish'", DB_TBL_WP_POSTS);
        $data = $dbi->query_to_multi_array($sql);

        return $data;
    }

    public static function get_blog_categories()
    {

        global $dbi;

        $sql = sprintf("SELECT wt.* FROM %s p
        INNER JOIN %s r ON r.object_id=p.ID
        INNER JOIN %s t ON t.term_taxonomy_id = r.term_taxonomy_id
        INNER JOIN %s wt on wt.term_id = t.term_id
        WHERE   t.taxonomy='category' GROUP BY wt.slug", DB_TBL_WP_POSTS, DB_TBL_WP_TERM_RELATIONSHIPS, DB_TBL_WP_TERM_TAXONOMY, DB_TBL_WP_TERMS);
        $data = $dbi->query_to_multi_array($sql);

        return $data;
    }

    public static function get_blog_posts_by_view_count($start, $limit)
    {

        global $dbi;

        $sql = sprintf("SELECT p.*, t.guid FROM (SELECT p.ID, p.post_title, p.post_date, p.post_name, p.post_content, CAST(m.meta_value AS UNSIGNED) AS views FROM %s p INNER JOIN %s m ON p.ID = m.post_id WHERE p.post_status = 'publish' AND (p.post_type = 'post' OR p.post_type = 'page') AND m.meta_key = 'wpb_post_views_count') p INNER JOIN %s t ON p.ID = t.post_parent AND t.post_type = 'attachment' WHERE SUBSTR( t.guid, -3 ) = 'png' OR SUBSTR( t.guid, -3 ) = 'jpg'  OR SUBSTR( t.guid, -3 ) = 'jpeg' GROUP BY p.ID ORDER BY p.views DESC LIMIT $start, $limit", DB_TBL_WP_POSTS, DB_TBL_WP_POSTMETA, DB_TBL_WP_POSTS);
        $results = $dbi->query_to_multi_array($sql);
        foreach ($results as $index => $post) {
            $post["post_content"] = explode("<!--more-->", $post["post_content"]);
            $post["post_content"] = preg_replace("/\[.*?\]/", "", strip_tags(array_shift($post["post_content"])));
            if (strlen($post["post_content"]) > 150) {
                $post["post_content"] = substr($post["post_content"], 0, 150) . " ...";
            }
            $results[$index]["post_content"] = $post["post_content"];
            $results[$index]["comment_count"] = 0;
        }

        return $results;
    }

    public static function get_blog_posts_by_category($category)
    {

        global $dbi;

        $sql = sprintf("SELECT p.post_title, p.post_date, u.display_name, p.post_name, p.post_content, a.guid FROM %s p LEFT JOIN %s rel ON rel.object_id = p.ID LEFT JOIN %s tax ON tax.term_taxonomy_id = rel.term_taxonomy_id LEFT JOIN %s t ON t.term_id = tax.term_id LEFT JOIN %s u ON p.post_author = u.ID INNER JOIN %s m ON p.ID = m.post_id INNER JOIN %s a ON m.meta_value = a.ID WHERE t.name LIKE 'Important' AND p.post_type = 'post' AND p.post_status = 'publish' AND m.meta_key = '_thumbnail_id'", DB_TBL_WP_POSTS, DB_TBL_WP_TERM_RELATIONSHIPS, DB_TBL_WP_TERM_TAXONOMY, DB_TBL_WP_TERMS, DB_TBL_WP_USERS, DB_TBL_WP_POSTMETA, DB_TBL_WP_POSTS);
        // $sql = sprintf( "SELECT p.post_title, t.term_id, t.name FROM %s p LEFT JOIN %s rel ON rel.object_id = p.ID LEFT JOIN %s tax ON tax.term_taxonomy_id = rel.term_taxonomy_id LEFT JOIN %s t ON t.term_id = tax.term_id WHERE t.name LIKE '%s'",DB_TBL_WP_POSTS, DB_TBL_WP_TERM_RELATIONSHIPS,DB_TBL_WP_TERM_TAXONOMY,DB_TBL_WP_TERMS,$category );
        // $sql = sprintf( "SELECT p.ID, t.term_id, t.name FROM %s p LEFT JOIN %s rel ON rel.object_id = p.ID LEFT JOIN %s tax ON tax.term_taxonomy_id = rel.term_taxonomy_id LEFT JOIN %s t ON t.term_id = tax.term_id WHERE t.name LIKE '%s'",DB_TBL_WP_POSTS, WP_TERM_RELATIONSHIPS,WP_TERM_TAXONOMY,WP_TERMS,$category );

        $results = $dbi->query_to_multi_array($sql);
        foreach ($results as $index => $post) {
            $post["post_content"] = explode("<!--more-->", $post["post_content"]);
            $post["post_content"] = preg_replace("/\[.*?\]/", "", strip_tags(array_shift($post["post_content"])));
            if (strlen($post["post_content"]) > 150) {
                $post["post_content"] = substr($post["post_content"], 0, 150) . " ...";
            }
            $results[$index]["post_content"] = $post["post_content"];
            $results[$index]["comment_count"] = 0;
        }

        return $results;
    }

    public static function log_action($action_id, $user_id = null, $action_data = [])
    {

        global $dbi, $log_action_ids;
        $action = !empty($log_action_ids[$action_id]) ? $log_action_ids[$action_id] : $action_id;
        Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "User:{$user_id}::" . http_build_query($action_data, '', ', '), ["messages", "log action", $action]);

        $action_data = serialize($action_data);

        $dbi->insert(
            DB_TBL_LOG,
            [
                "user_id" => $user_id,
                "log_action_id" => $action_id,
                "action_data" => $action_data,
                "time" => date("Y-m-d H:i:s"),
            ]
        );
    }

    public static function get_logged_action($action_id, $user_id, $limit = 10)
    {

        global $dbi;

        $sql = sprintf("SELECT * FROM %s WHERE user_id = %d AND log_action_id = %d ORDER BY time DESC LIMIT 0, %d", DB_TBL_LOG, $user_id, $action_id, $limit);
        $results = $dbi->query_to_multi_array($sql);

        foreach ($results as $index => $result) {
            $results[$index]["action_data"] = unserialize($result["action_data"]);
        }

        return $results;
    }

    public static function get_logged_user_cancellation_dates($user_id)
    {

        $plan_data = self::get_logged_action(LOG_ACTION_USER_SUBSCRIPTION_CANCELLED, $user_id, 100);
        $return = [];

        foreach ($plan_data as $data) {
            $return[$data["action_data"]["plan_id"]] = $data["time"];
        }

        return $return;
    }

    public static function get_city_list_for_state($state)
    {

        global $dbi;

        $sql = sprintf("SELECT a.city FROM %s a WHERE a.state= '%s'", DB_TBL_API_CITY_LIST, $dbi->escape($state));
        $results = $dbi->query_to_multi_array($sql);

        return array_map(function ($value) {
            return $value["city"];
        }, $results);
    }

    public static function validate_google_recaptcha()
    {

        global $client, $post_data, $testcase;

        if ($testcase) {
            return true;
        }
        $captcha_data = [
            "secret" => RECAPTCHA_SECRET_KEY,
            "response" => $post_data["g-recaptcha-response"],
            "remoteip" => $_SERVER["REMOTE_ADDR"],
        ];
        $stream = $client->request("https://www.google.com/recaptcha/api/siteverify", vbrowser::VBROWSER_METHOD_POST, $captcha_data);
        $response = json_decode($stream["response"], true);

        if (!$response["success"]) {
            Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "Recapcha Failed", ["errors", "recapcha failed", ""]);
        }


        return ($response["success"]);
    }

    public static function get_url_type($link)
    {

        if (preg_match("/^#?[0-9]+@(.*?)\$/", $link, $url_type)) {
            $url_type = $url_type[1];
            if ("linkedin" == $url_type) {
                $url_type = "linked-in";
            }
        } else {
            $types = [
                "google-plus" => "/plus\.google|googleusercontent\./i",
                "facebook" => "/facebook\.|fbcdn\./i",
                "youtube" => "/youtube\./i",
                "pinterest" => "/pinterest\.|pinimg\./i",
                "linked-in" => "/linkedin\.|licdn\./i",
                "twitter" => "/twitter\.|twimg\./i",
                "instagram" => "/instagram\./i",
                "myspace" => "/myspace\.|myspacecdn\./i",
                "gravatar" => "/gravatar\./i",
                "vk" => "/vk\.com|userapi\.com/i",
            ];

            $url_type = "";
            foreach ($types as $type => $pattern) {
                if (preg_match($pattern, $link)) {
                    $url_type = $type;
                    break;
                }
            }
        }

        return $url_type;
    }

    public static function get_image_url_source($link)
    {

        $find_replace = [
            "/^.*?graph.facebook.com\/([0-9]+)\/.*/im" => "https://www.facebook.com/\\1",
        ];

        foreach ($find_replace as $pattern => $replacement) {
            $new_url = preg_replace($pattern, $replacement, $link);
            if ($link != $new_url) {
                break;
            }
        }

        return ($link != $new_url) ? $new_url : "";
    }

    public static function imgcdn_url($url)
    {

        if (preg_match("/(?:devtemp\.)?socialcatfish\.com|berify\.com/", $url)) {
            return $url;
        }
        $url = str_split(str_replace("/", "-IDS-", base64_encode($url)), 50);
        return IMG_CDN_URL . implode("/", $url);
    }

    public static function print_imgcdn_url($url)
    {

        echo self::imgcdn_url($url);
    }

    public static function user_uploads_url($uploaded_url)
    {

        $url = explode("uploads" . DIRECTORY_SEPARATOR, $uploaded_url);
        $filename = end($url);

        return (file_exists(UPLOADS_PATH . $filename)) ? substr(BASE_URL, 0, -1) . str_replace("\\", "/", UPLOADS_URL . "/{$filename}") : USER_IMG_UPLOAD_URL . $filename;
    }

    public static function print_user_uploads_url($url)
    {

        echo self::user_uploads_url($url);
    }

    public static function log_request_execution_time($execution_start_time)
    {

        $error_message = error_get_last();

        if ($error_message && ($error_message["type"] == E_ERROR || $error_message["type"] == E_PARSE)) {
            $request = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "";
            $referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "";
            error_log("\nMessage: {$error_message["message"]}\nFile: {$error_message["file"]}\nLine: {$error_message["line"]}\nRequest: {$request}\nReferer: {$referer}", 0);
        }

        $GLOBALS["behavior_page_execution_time"] = $duration = round(microtime(true) - $execution_start_time, 2);

        $fp = fopen(ABS_PATH . "../request_execution_time.txt", "a");
        fputcsv($fp, [
            $_SERVER["REMOTE_ADDR"],
            (!empty($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : ""),
            date("Y-m-d H:i:s"),
            "{$_SERVER["REQUEST_SCHEME"]}://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}",
            $duration,
        ], "\t");
        fclose($fp);

        Behavior::log_action("execution_time");

        Behavior::send_data([], true);

        // AB Tracking
        if (!empty($GLOBALS["ab_conversion_tracking_stack"])) {
            $dbi = $GLOBALS["dbi"];
            $fp = tmpfile();
            $fp_meta = stream_get_meta_data($fp);

            foreach ($GLOBALS["ab_conversion_tracking_stack"] as $event) {
                $row_data = [
                    "date" => date("Y-m-d H:i:s"),
                    "user_id" => $GLOBALS["user_id"],
                    "experiment_key" => $event["experiment_key"],
                    "variation_key" => $event["experiment_key"],
                    "category_key" => $event["category_key"],
                    "session_id" => $event["user_id"],
                    "utm_source" => $_SESSION["advertisement_data"]["source"] ?? "",
                    "utm_medium" => $_SESSION["advertisement_data"]["medium"] ?? "",
                    "utm_referer" => $_SESSION["advertisement_data"]["referer"] ?? "",
                    "utm_campaign" => $_SESSION["advertisement_data"]["campaign"] ?? "",
                ];

                fputcsv($fp, $row_data);
            }

            $field_names = array_keys($row_data);

            $dbi->load_data(DB_TBL_AB_CONVERSION_TRACKING, $fp_meta["uri"], $field_names);
        }
    }

    public static function get_ip_info($ip)
    {

        global $dbi;

        $ip_lng = ip2long($ip);

        $sql = sprintf("SELECT * FROM %s WHERE %d BETWEEN ip_from AND ip_to;", DB_TBL_IP_INFO, $ip_lng);
        $result = $dbi->query_to_array($sql);

        return $result;
    }

    public static function blog_get_optimized_image($image, $high_res = false)
    {

        if (strpos($image, "devstaging.socialcatfish.com") !== false) {
            return $image;
        }

        $optimized_image = preg_replace("/(\.[^\.]+)\$/im", ($high_res ? "-web-op-2x.jpg" : "-web-op.jpg"), $image);
        $uri = parse_url($optimized_image);

        $check_url = BLOG_PATH . "wp-content/" . preg_replace("/^.*?\/(uploads\/.*?)(?: |,|\$).*\$/im", "\\1", $optimized_image);

        if (strpos($image, "new.socialcatfish.com") !== false) {
            return $image;
        }
        $img_url = str_replace(["socialcatfish.com/blog/wp-content", "socialcatfish.com/scamfish/wp-content"], ["scamfishcdn.socialcatfish.com", "scamfishcdn.socialcatfish.com"], (file_exists($check_url) ? $optimized_image : $image));
        $img_url = str_replace("/uploads", "", $img_url);
        //$img_url = str_replace( ["scamfishcdn.socialcatfish.com/uploads", "spcdnblog.socialcatfish.com/uploads", "scamfishcdn.socialcatfish.com/themes", "spcdnblog.socialcatfish.com/themes"], [ "socialcatfish.com/scamfish/wp-content/uploads","socialcatfish.com/scamfish/wp-content/uploads", "socialcatfish.com/scamfish/wp-content/themes", "socialcatfish.com/scamfish/wp-content/themes" ], $img_url ); //Disabled CDN

        return str_replace("devtemp.", "", $img_url);
    }

    public static function google_auth_sign_request($data, $secret_key)
    {

        return strtr(base64_encode(hash_hmac("sha1", $data, base64_decode(strtr($secret_key, "-_", "+/")), true)), "+/", "-_");
    }

    public static function google_auth_sign_url($url, $secret_key)
    {

        $uri = parse_url($url);
        parse_str($uri["query"], $params);
        $encoded_params = [];
        foreach ($params as $key => $value) {
            $encoded_params[] = $key . "=" . urlencode($value);
        }
        $sign_request = $uri["path"] . "?" . implode("&", $encoded_params);
        $signature = self::google_auth_sign_request($sign_request, $secret_key);
        return "{$uri["scheme"]}://{$uri["host"]}{$sign_request}&signature={$signature}";
    }

    public static function gdpr_block()
    {

        $gpdr_blocked_countries = array_fill_keys(['BE', 'BG', 'CZ', 'DK', 'DE', 'EE', 'IE', 'EL', 'ES', 'FR', 'HR', 'IT', 'CY', 'LV', 'LT', 'LU', 'HU', 'MT', 'NL', 'AT', 'PL', 'PT', 'RO', 'SI', 'SK', 'FI', 'SE'], 1);

        if (empty($_SESSION["ip_country"])) {
            // Mod of xml-sitemaps.com
            if ($_SERVER["REMOTE_ADDR"] == "85.92.66.148") {
                $ip_info = ["country_code" => "US"];
            } else {
                $ip_info = self::get_ip_info($_SERVER["REMOTE_ADDR"]);
            }

            $_SESSION["ip_country"] = $ip_info ? $ip_info["country_code"] : "";
        }

        if (!isset($_SESSION["gdpr-false"])) {
            return (isset($gpdr_blocked_countries[$_SESSION["ip_country"]]));
        } else {
            return false;
        }
    }

    public static function gdpr_log_ip($ip)
    {

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            global $dbi;
            $dbi->insert(
                DB_TBL_GDPR_LOG,
                [
                    "ip_address" => $ip
                ]
            );
        } else {
            return;
        }
    }

    public static function maintenance_mode_headers()
    {

        header("{$_SERVER["SERVER_PROTOCOL"]} 503 Service Unavailable");
        header('Status: 503 Service Unavailable');
        header('Retry-After: 300');
    }

    public static function js_controller($controller, $return = false)
    {

        if ($return) {
            return sprintf(' data-%s="%s"', JS_MAIN_CONTROLLER, $controller);
        } else {
            printf(' data-%s="%s"', JS_MAIN_CONTROLLER, $controller);
        }
    }

    public static function js_element_var($controller, $return = false)
    {

        if ($return) {
            return sprintf(' data-%s="%s"', JS_ELEMENT_VAR, $controller);
        } else {
            printf(' data-%s="%s"', JS_ELEMENT_VAR, $controller);
        }
    }

    public static function get_favicons_by_site_url($url)
    {

        $user_social_icon = "";
        $domain = !empty($url) ? parse_url($url, PHP_URL_HOST) : "";

        $host_parts = explode('.', $domain);
        $host_parts = array_reverse($host_parts);

        $usa_top_500 = [
            "google.com", "youtube.com", "amazon.com", "facebook.com", "yahoo.com", "zoom.us", "reddit.com", "wikipedia.org", "myshopify.com", "ebay.com", "office.com", "instructure.com", "netflix.com", "cnn.com", "bing.com", "live.com", "microsoft.com", "nytimes.com", "twitch.tv", "apple.com", "microsoftonline.com", "instagram.com", "espn.com", "zillow.com", "chaturbate.com", "chase.com", "dropbox.com", "etsy.com", "linkedin.com", "adobe.com", "walmart.com", "foxnews.com", "salesforce.com", "okta.com", "twitter.com", "force.com", "quizlet.com", "craigslist.org", "livejasmin.com", "amazonaws.com", "aliexpress.com", "wellsfargo.com", "tmall.com", "indeed.com", "hulu.com", "breitbart.com", "imdb.com", "bestbuy.com", "stackoverflow.com", "washingtonpost.com", "homedepot.com", "msn.com", "spotify.com", "pornhub.com", "target.com", "qq.com", "heavy.com", "paypal.com", "github.com", "alibaba.com", "ca.gov", "ups.com", "sohu.com", "usps.com", "fivethirtyeight.com", "wordpress.com", "patch.com", "fandom.com", "bbc.com", "duckduckgo.com", "theguardian.com", "xfinity.com", "bongacams.com", "chegg.com", "weather.com", "soundcloud.com", "taobao.com", "canva.com", "baidu.com", "realtor.com", "ballotpedia.org", "intuit.com", "cnbc.com", "nih.gov", "cnet.com", "capitalone.com", "fidelity.com", "vimeo.com", "pinterest.com", "medium.com", "jd.com", "360.cn", "businessinsider.com", "xvideos.com", "stackexchange.com", "wayfair.com", "tiktok.com", "schoology.com", "airbnb.com", "att.com", "verizon.com", "bankofamerica.com", "fedex.com", "tumblr.com", "slack.com", "healthline.com", "yelp.com", "adp.com", "discord.com", "costco.com", "mheducation.com", "fiverr.com", "padlet.com", "roblox.com", "slickdeals.net", "usatoday.com", "forbes.com", "citi.com", "westernjournal.com", "wsj.com", "squarespace.com", "wix.com", "blackboard.com", "aol.com", "grammarly.com", "nbcnews.com", "npr.org", "godaddy.com", "lowes.com", "nypost.com", "glassdoor.com", "sina.com.cn", "tradingview.com", "webex.com", "go.com", "thegatewaypundit.com", "redd.it", "office365.com", "politico.com", "thesaurus.com", "box.com", "ameritrade.com", "dailymail.co.uk", "udemy.com", "nfl.com", "investopedia.com", "zendesk.com", "cbssports.com", "irs.gov", "disneyplus.com", "khanacademy.org", "weibo.com", "wikihow.com", "amazon.co.uk", "ny.gov", "booking.com", "myworkday.com", "xhamster.com", "cbsnews.com", "zoho.com", "tripadvisor.com", "redfin.com", "discover.com", "americanexpress.com", "bitexworld.com", "shutterstock.com", "bbc.co.uk", "pearson.com", "macys.com", "doordash.com", "newegg.com", "eatthis.com", "marketwatch.com", "cengage.com", "surveymonkey.com", "imgur.com", "trello.com", "weebly.com", "ikea.com", "worldometers.info", "constantcontact.com", "t-mobile.com", "hbomax.com", "pandora.com", "archive.org", "bloomberg.com", "taboola.com", "kohls.com", "goodreads.com", "schwab.com", "hootsuite.com", "collegeboard.org", "theepochtimes.com", "270towin.com", "gamepedia.com", "ecollege.com", "webmd.com", "qualtrics.com", "docusign.net", "getadblock.com", "realclearpolitics.com", "huffpost.com", "cdc.gov", "xnxx.com", "crunchyroll.com", "gap.com", "usbank.com", "nike.com", "yahoo.co.jp", "coursehero.com", "desmos.com", "w3schools.com", "patreon.com", "amazon.co.jp", "bhphotovideo.com", "dailycaller.com", "usnews.com", "onlyfans.com", "eventbrite.com", "thehill.com", "wetransfer.com", "creditonebank.com", "dell.com", "onlinesbi.com", "ed.gov", "thepiratebay.org", "csdn.net", "op.gg", "apnews.com", "chess.com", "trulia.com", "myworkdayjobs.com", "reuters.com", "cvs.com", "lotterypost.com", "spanishdict.com", "google.com.tw", "spectrum.net", "alipay.com", "syf.com", "quizizz.com", "drudgereport.com", "google.com.sg", "google.com.hk", "speedtest.net", "expedia.com", "mozilla.org", "cargurus.com", "outbrain.com", "kahoot.it", "brainly.com", "cloudfront.net", "taleo.net", "overstock.com", "accuweather.com", "loom.com", "samsclub.com", "linktr.ee", "audible.com", "t.co", "gotomeeting.com", "usaa.com", "teacherspayteachers.com", "mailchimp.com", "merriam-webster.com", "giphy.com", "investing.com", "adoptapet.com", "seesaw.me", "wikimedia.org", "state.gov", "whatsapp.com", "buzzfeed.com", "hp.com", "nbcsports.com", "joinhoney.com", "autotrader.com", "harvard.edu", "coursera.org", "unsplash.com", "pnc.com", "walgreens.com", "genius.com", "cbs.com", "people.com", "gotowebinar.com", "seekingalpha.com", "cars.com", "thedailybeast.com", "allrecipes.com", "commonapp.org", "symbolab.com", "discordapp.com", "tdameritrade.com", "wowhead.com", "ign.com", "tianya.cn", "robinhood.com", "nordstrom.com", "clever.com", "vanguard.com", "msnbc.com", "samsung.com", "mit.edu", "weather.gov", "steamcommunity.com", "mathxl.com", "inquisitr.com", "grubhub.com", "houzz.com", "shopify.com", "noaa.gov", "bedbathandbeyond.com", "feedly.com", "atlassian.net", "groupon.com", "fantasypros.com", "xinhuanet.com", "rottentomatoes.com", "skype.com", "researchgate.net", "churchofjesuschrist.org", "wunderground.com", "list-manage.com", "nextdoor.com", "uscis.gov", "chron.com", "creditkarma.com", "ksl.com", "adblockplus.org", "studentaid.gov", "wa.gov", "quora.com", "att.net", "ancestry.com", "staples.com", "onelogin.com", "biblegateway.com", "apartments.com", "dictionary.com", "britannica.com", "steampowered.com", "mlb.com", "bleacherreport.com", "norton.com", "kickstarter.com", "oracle.com", "instacart.com", "thestartmagazine.com", "aa.com", "rakuten.com", "evernote.com", "ziprecruiter.com", "southwest.com", "smartsheet.com", "dailymotion.com", "marriott.com", "webassign.net", "pcmag.com", "mypearson.com", "gamespot.com", "delta.com", "deviantart.com", "narvar.com", "theverge.com", "bestlifeonline.com", "arcgis.com", "mayoclinic.org", "spankbang.com", "state.tx.us", "latimes.com", "shein.com", "cuny.edu", "docusign.com", "fool.com", "verizonwireless.com", "icims.com", "messenger.com", "geeksforgeeks.org", "vitalsource.com", "clickfunnels.com", "zerohedge.com", "timeanddate.com", "offerup.com", "kaiserpermanente.org", "classlink.com", "united.com", "ultipro.com", "vistaprint.com", "sfgate.com", "mydailymagazine.com", "hubspot.com", "squareup.com", "xbox.com", "yourdailysportfix.com", "tmz.com", "aofex.com", "vrbo.com", "wish.com", "theatlantic.com", "chewy.com", "brobible.com", "kbb.com", "gamestop.com", "texas.gov", "independent.co.uk", "poshmark.com", "istockphoto.com", "ultimate-guitar.com", "kayak.com", "cbslocal.com", "parler.com", "247sports.com", "bbcollab.com", "teachable.com", "freepik.com", "4chan.org", "vox.com", "secnews.gr", "study.com", "newsweek.com", "nflbite.com", "ask.com", "urbandictionary.com", "ck12.org", "ap.org", "shutterfly.com", "sciencedirect.com", "pbs.org", "zappos.com", "redbubble.com", "draftkings.com", "mxc.com", "amazon.ca", "storage.googleapis.com", "suntrust.com", "behance.net", "techradar.com", "pinimg.com", "xianjichina.com", "telegram.org", "denverpost.com", "medicalnewstoday.com", "wiley.com", "swagbucks.com", "history.com", "nvidia.com", "time.com", "ssa.gov", "cornell.edu", "slate.com", "tesla.com", "diply.com", "stockx.com", "ibanking-services.com", "bankrate.com", "purdue.edu", "secureinternetbank.com", "dominos.com", "hotels.com", "asana.com", "foodnetwork.com", "retailmenot.com", "sephora.com", "ea.com", "etrade.com", "whitepages.com", "slader.com", "wixsite.com", "tdbank.com", "wegotthiscovered.com", "duolingo.com", "service-now.com", "crackstreams.com", "match.com", "auth0.com", "chicagotribune.com", "epicgames.com", "playstation.com", "citationmachine.net", "aweber.com", "td.com", "youporn.com", "manyvids.com", "vk.com", "bbb.org", "upwork.com", "kitco.com", "infusionsoft.com", "slideshare.net", "foxbusiness.com", "wordreference.com", "sparknotes.com", "citibankonline.com", "cj.com", "nyc.gov", "zazzle.com", "stanford.edu", "indiatimes.com", "experian.com"
        ];

        $popular_domains = [
            "amazon", "android", "behance", "bing", "box", "buffer", "creativemarket", "delicious", "deviantart", "dribbble", "dropbox",
            "envato", "etsy", "facebook", "flickr", "foursquare", "hi5", "howcast", "html-5", "instagram", "kickstarter", "linkedin", "medium",
            "myspace", "path", "paypal", "periscope", "pinterest", "plaxo", "plus.google", "quora", "reddit", "scribd", "shutterstock", "skype",
            "snapchat", "soundcloud", "spotify", "stumbleupon", "trello", "tumblr", "twitter", "vimeo", "vine", "whatsapp", "wikipedia", "wordpress", "yelp",
            "youtube"
        ];

        if (preg_match('(' . implode('|', $popular_domains) . ')', $domain, $matches) && !empty($matches[0]) && file_exists(ASSETS_PATH . "/common/images/social-icons-popular/{$matches[0]}.png")) {
            $user_social_icon = $GLOBALS["common_assets_url"] . "/images/social-icons-popular/{$matches[0]}.png";
        } elseif (preg_match('(' . implode('|', $usa_top_500) . ')', $domain, $matches) && !empty($matches[0]) && file_exists(ASSETS_PATH . "/common/images/social-icons-top500/{$host_parts[1]}.png")) {
            $user_social_icon = $GLOBALS["common_assets_url"] . "/images/social-icons-top500/{$host_parts[1]}.png";
        } else {
            $user_social_icon = "https://www.google.com/s2/favicons?domain=" . $host_parts[1] . "." . $host_parts[0];
        }

        return $user_social_icon;
    }

    public static function array_flatten(&$array, $prefix = "")
    {

        $return = [];

        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $return = $return + self::array_flatten($value, $prefix . (($prefix) ? "/" : "") . $key);
                } else {
                    $return[$prefix . ($prefix ? "/" : "") . $key] = $value;
                }
            }
        }

        return $return;
    }

    public static function xml_to_array(&$xml)
    {

        $array = [];

        foreach ((array)$xml as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $array[$key] = self::xml_to_array($value);
            } else {
                $array[$key] = $value;
            }
        }

        return empty($array) ? "" : $array;
    }

    public static function tz_convert($time, $format = "Y-m-d H:i:s", $from_timezone = "UTC", $to_timezone = "America/Los_Angeles")
    {

        $format = is_null($format) ? "Y-m-d H:i:s" : $format;

        try {
            if (is_int($time)) {
                $time = date("Y-m-d H:i:s", $time);
            }

            $time = new DateTime($time, new DateTimeZone($from_timezone));
            $time->setTimezone(new DateTimeZone($to_timezone));
            return $time->format($format);
        } catch (Exception $e) {
            return "";
        }
    }

    public static function get_ip_api_usage($ip_address)
    {

        global $dbi;

        $sql = sprintf("SELECT COUNT(*) `count` FROM `%s` a WHERE a.date >= DATE_SUB(NOW(), INTERVAL 1 DAY) AND a.user_id IS NULL AND a.ip_address = '%s'", DB_TBL_API_CALLS, $dbi->escape($ip_address));
        $results = $dbi->query_to_array($sql);

        return $results["count"];
    }

    public static function language_get($lang_code)
    {
        $language_list = [
            "Afar" => "aa",
            "Abkhazian" => "ab",
            "Avestan" => "ae",
            "Afrikaans" => "af",
            "Akan" => "ak",
            "Amharic" => "am",
            "Aragonese" => "an",
            "Arabic" => "ar",
            "Assamese" => "as",
            "Avaric" => "av",
            "Aymara" => "ay",
            "Azerbaijani" => "az",
            "Bashkir" => "ba",
            "Belarusian" => "be",
            "Bulgarian" => "bg",
            "Bihari languages" => "bh",
            "Bislama" => "bi",
            "Bambara" => "bm",
            "Bengali" => "bn",
            "Tibetan" => "bo",
            "Breton" => "br",
            "Bosnian" => "bs",
            "Catalan; Valencian" => "ca",
            "Chechen" => "ce",
            "Chamorro" => "ch",
            "Corsican" => "co",
            "Cree" => "cr",
            "Czech" => "cs",
            "Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic" => "cu",
            "Chuvash" => "cv",
            "Welsh" => "cy",
            "Danish" => "da",
            "German" => "de",
            "Divehi; Dhivehi; Maldivian" => "dv",
            "Dzongkha" => "dz",
            "Ewe" => "ee",
            "Greek, Modern (1453-)" => "el",
            "English" => "en",
            "Esperanto" => "eo",
            "Spanish; Castilian" => "es",
            "Estonian" => "et",
            "Basque" => "eu",
            "Persian" => "fa",
            "Fulah" => "ff",
            "Finnish" => "fi",
            "Fijian" => "fj",
            "Faroese" => "fo",
            "French" => "fr",
            "Western Frisian" => "fy",
            "Irish" => "ga",
            "Gaelic; Scottish Gaelic" => "gd",
            "Galician" => "gl",
            "Guarani" => "gn",
            "Gujarati" => "gu",
            "Manx" => "gv",
            "Hausa" => "ha",
            "Hebrew" => "he",
            "Hindi" => "hi",
            "Hiri Motu" => "ho",
            "Croatian" => "hr",
            "Haitian; Haitian Creole" => "ht",
            "Hungarian" => "hu",
            "Armenian" => "hy",
            "Herero" => "hz",
            "Interlingua (International Auxiliary Language Association)" => "ia",
            "Indonesian" => "id",
            "Interlingue; Occidental" => "ie",
            "Igbo" => "ig",
            "Sichuan Yi; Nuosu" => "ii",
            "Inupiaq" => "ik",
            "Ido" => "io",
            "Icelandic" => "is",
            "Italian" => "it",
            "Inuktitut" => "iu",
            "Japanese" => "ja",
            "Javanese" => "jv",
            "Georgian" => "ka",
            "Kongo" => "kg",
            "Kikuyu; Gikuyu" => "ki",
            "Kuanyama; Kwanyama" => "kj",
            "Kazakh" => "kk",
            "Kalaallisut; Greenlandic" => "kl",
            "Central Khmer" => "km",
            "Kannada" => "kn",
            "Korean" => "ko",
            "Kanuri" => "kr",
            "Kashmiri" => "ks",
            "Kurdish" => "ku",
            "Komi" => "kv",
            "Cornish" => "kw",
            "Kirghiz; Kyrgyz" => "ky",
            "Latin" => "la",
            "Luxembourgish; Letzeburgesch" => "lb",
            "Ganda" => "lg",
            "Limburgan; Limburger; Limburgish" => "li",
            "Lingala" => "ln",
            "Lao" => "lo",
            "Lithuanian" => "lt",
            "Luba-Katanga" => "lu",
            "Latvian" => "lv",
            "Malagasy" => "mg",
            "Marshallese" => "mh",
            "Maori" => "mi",
            "Macedonian" => "mk",
            "Malayalam" => "ml",
            "Mongolian" => "mn",
            "Marathi" => "mr",
            "Malay" => "ms",
            "Maltese" => "mt",
            "Burmese" => "my",
            "Nauru" => "na",
            "Bokmål, Norwegian; Norwegian Bokmål" => "nb",
            "Ndebele, North; North Ndebele" => "nd",
            "Nepali" => "ne",
            "Ndonga" => "ng",
            "Dutch; Flemish" => "nl",
            "Norwegian Nynorsk; Nynorsk, Norwegian" => "nn",
            "Norwegian" => "no",
            "Ndebele, South; South Ndebele" => "nr",
            "Navajo; Navaho" => "nv",
            "Chichewa; Chewa; Nyanja" => "ny",
            "Occitan (post 1500)" => "oc",
            "Ojibwa" => "oj",
            "Oromo" => "om",
            "Oriya" => "or",
            "Ossetian; Ossetic" => "os",
            "Panjabi; Punjabi" => "pa",
            "Pali" => "pi",
            "Polish" => "pl",
            "Pushto; Pashto" => "ps",
            "Portuguese" => "pt",
            "Quechua" => "qu",
            "Romansh" => "rm",
            "Rundi" => "rn",
            "Romanian; Moldavian; Moldovan" => "ro",
            "Russian" => "ru",
            "Kinyarwanda" => "rw",
            "Sanskrit" => "sa",
            "Sardinian" => "sc",
            "Sindhi" => "sd",
            "Northern Sami" => "se",
            "Sango" => "sg",
            "Sinhala; Sinhalese" => "si",
            "Slovak" => "sk",
            "Slovenian" => "sl",
            "Samoan" => "sm",
            "Shona" => "sn",
            "Somali" => "so",
            "Albanian" => "sq",
            "Serbian" => "sr",
            "Swati" => "ss",
            "Sotho, Southern" => "st",
            "Sundanese" => "su",
            "Swedish" => "sv",
            "Swahili" => "sw",
            "Tamil" => "ta",
            "Telugu" => "te",
            "Tajik" => "tg",
            "Thai" => "th",
            "Tigrinya" => "ti",
            "Turkmen" => "tk",
            "Tagalog" => "tl",
            "Tswana" => "tn",
            "Tonga (Tonga Islands)" => "to",
            "Turkish" => "tr",
            "Tsonga" => "ts",
            "Tatar" => "tt",
            "Twi" => "tw",
            "Tahitian" => "ty",
            "Uighur; Uyghur" => "ug",
            "Ukrainian" => "uk",
            "Urdu" => "ur",
            "Uzbek" => "uz",
            "Venda" => "ve",
            "Vietnamese" => "vi",
            "Volapük" => "vo",
            "Walloon" => "wa",
            "Wolof" => "wo",
            "Xhosa" => "xh",
            "Yiddish" => "yi",
            "Yoruba" => "yo",
            "Zhuang; Chuang" => "za",
            "Chinese" => "zh",
            "Zulu" => "zu"
        ];
        return array_search($lang_code, $language_list);
    }

    function convert_to_hours_mins($time, $format = '%02d:%02d')
    {
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }

    public static function get_array_string_combinations($array, $value = [])
    {

        $index = count($value);
        $array_keys = array_keys($array);
        $last_key = $array_keys[count($array_keys) - 1];

        if (isset($array_keys[$index]) && is_array($array[$array_keys[$index]])) {
            $combination = [];

            foreach ($array[$array_keys[$index]] as $_index => $_value) {
                $new_array = $value;
                $new_array[$array_keys[$index]] = $_value;

                if ($last_key == $array_keys[$index]) {
                    $combination[] = $new_array;
                } else {
                    $combination = array_merge($combination, self::get_array_string_combinations($array, $new_array));
                }
            }

            return $combination;
        }
    }

    public static function add_influencers($data)
    {

        global $dbi;
        $dbi->insert(DB_TBL_DB_INFLUENCERS, $data);
    }

    public static function get_influencers($offset, $limit, $sort = "", $search_name = "")
    {

        global $dbi;

        $sql1 = "";
        if ($sort == "a") {
            $sql1 = "ORDER BY NAME ";
        }
        if ($sort == "d") {
            $sql1 = "ORDER BY NAME DESC ";
        }

        $sql2 = "";
        if ($search_name != "") {
            $sql2 = "WHERE name LIKE '%$search_name%'";
        }

        $sql = sprintf("SELECT * FROM `%s` %s %s LIMIT %d, %d", DB_TBL_DB_INFLUENCERS, $sql2, $sql1, $offset, $limit);
        $result = $dbi->query_to_multi_array($sql);
        return $result;
    }

    public static function count_influencers($search_name = "")
    {

        global $dbi;

        $sql2 = "";
        if ($search_name != "") {
            $sql2 = "WHERE name LIKE '%$search_name%'";
        }

        $sql = sprintf("SELECT COUNT('id') as `count` FROM `%s` %s", DB_TBL_DB_INFLUENCERS, $sql2);
        $result = $dbi->query_to_array($sql);
        return $result["count"];
    }

    public static function delete_influencer($id)
    {

        global $dbi;

        $sql = sprintf("DELETE FROM %s WHERE `id` = %d;", DB_TBL_DB_INFLUENCERS, $id);

        return $dbi->query($sql);
    }

    public static function get_domain_name($domain)
    {

        $link_parse = parse_url($domain);
        $host = str_replace("www.", "", $link_parse["host"]);
        $parse_name = strtolower(substr($host, 0, strpos($host, ".")));
        return $parse_name;
    }

    /**
     * Extract domain portion from an email address
     *
     * @param string $email
     * @return string
     */
    public static function extract_domain_from_email($email)
    {

        return preg_replace("/^.*?@(.*)\$/im", "\\1", strtolower($email));
    }

    /**
     * Identify disposable email domains
     *
     * @param string $email
     * @return bool
     */
    public static function is_disposable_email($email)
    {

        // Domain list retrieved from https://gist.github.com/michenriksen/8710649/
        $domain_list = "0815.ru|0wnd.net|0wnd.org|10minutemail.co.za|10minutemail.com|123-m.com|1fsdfdsfsdf.tk|1pad.de|20minutemail.com|21cn.com|2fdgdfgdfgdf.tk|2prong.com|30minutemail.com|33mail.com|3trtretgfrfe.tk|4gfdsgfdgfd.tk|4warding.com|5ghgfhfghfgh.tk|6hjgjhgkilkj.tk|6paq.com|7tags.com|9ox.net|a-bc.net|agedmail.com|ama-trade.de|amilegit.com|amiri.net|amiriindustries.com|anonmails.de|anonymbox.com|antichef.com|antichef.net|antireg.ru|antispam.de|antispammail.de|armyspy.com|artman-conception.com|azmeil.tk|baxomale.ht.cx|beefmilk.com|bigstring.com|binkmail.com|bio-muesli.net|bobmail.info|bodhi.lawlita.com|bofthew.com|bootybay.de|boun.cr|bouncr.com|breakthru.com|brefmail.com|bsnow.net|bspamfree.org|bugmenot.com|bund.us|burstmail.info|buymoreplays.com|byom.de|c2.hu|card.zp.ua|casualdx.com|cek.pm|centermail.com|centermail.net|chammy.info|childsavetrust.org|chogmail.com|choicemail1.com|clixser.com|cmail.net|cmail.org|coldemail.info|cool.fr.nf|courriel.fr.nf|courrieltemporaire.com|crapmail.org|cust.in|cuvox.de|d3p.dk|dacoolest.com|dandikmail.com|dayrep.com|dcemail.com|deadaddress.com|deadspam.com|delikkt.de|despam.it|despammed.com|devnullmail.com|dfgh.net|digitalsanctuary.com|dingbone.com|disposableaddress.com|disposableemailaddresses.com|disposableinbox.com|dispose.it|dispostable.com|dodgeit.com|dodgit.com|donemail.ru|dontreg.com|dontsendmespam.de|drdrb.net|dump-email.info|dumpandjunk.com|dumpyemail.com|e-mail.com|e-mail.org|e4ward.com|easytrashmail.com|einmalmail.de|einrot.com|eintagsmail.de|emailgo.de|emailias.com|emaillime.com|emailsensei.com|emailtemporanea.com|emailtemporanea.net|emailtemporar.ro|emailtemporario.com.br|emailthe.net|emailtmp.com|emailwarden.com|emailx.at.hm|emailxfer.com|emeil.in|emeil.ir|emz.net|ero-tube.org|evopo.com|explodemail.com|express.net.ua|eyepaste.com|fakeinbox.com|fakeinformation.com|fansworldwide.de|fantasymail.de|fightallspam.com|filzmail.com|fivemail.de|fleckens.hu|frapmail.com|friendlymail.co.uk|fuckingduh.com|fudgerub.com|fyii.de|garliclife.com|gehensiemirnichtaufdensack.de|get2mail.fr|getairmail.com|getmails.eu|getonemail.com|giantmail.de|girlsundertheinfluence.com|gishpuppy.com|gmial.com|goemailgo.com|gotmail.net|gotmail.org|gotti.otherinbox.com|great-host.in|greensloth.com|grr.la|gsrv.co.uk|guerillamail.biz|guerillamail.com|guerrillamail.biz|guerrillamail.com|guerrillamail.de|guerrillamail.info|guerrillamail.net|guerrillamail.org|guerrillamailblock.com|gustr.com|harakirimail.com|hat-geld.de|hatespam.org|herp.in|hidemail.de|hidzz.com|hmamail.com|hopemail.biz|ieh-mail.de|ikbenspamvrij.nl|imails.info|inbax.tk|inbox.si|inboxalias.com|inboxclean.com|inboxclean.org|infocom.zp.ua|instant-mail.de|ip6.li|irish2me.com|iwi.net|jetable.com|jetable.fr.nf|jetable.net|jetable.org|jnxjn.com|jourrapide.com|jsrsolutions.com|kasmail.com|kaspop.com|killmail.com|killmail.net|klassmaster.com|klzlk.com|koszmail.pl|kurzepost.de|lawlita.com|letthemeatspam.com|lhsdv.com|lifebyfood.com|link2mail.net|litedrop.com|lol.ovpn.to|lolfreak.net|lookugly.com|lortemail.dk|lr78.com|lroid.com|lukop.dk|m21.cc|mail-filter.com|mail-temporaire.fr|mail.by|mail.mezimages.net|mail.zp.ua|mail1a.de|mail21.cc|mail2rss.org|mail333.com|mailbidon.com|mailbiz.biz|mailblocks.com|mailbucket.org|mailcat.biz|mailcatch.com|mailde.de|mailde.info|maildrop.cc|maileimer.de|mailexpire.com|mailfa.tk|mailforspam.com|mailfreeonline.com|mailguard.me|mailin8r.com|mailinater.com|mailinator.com|mailinator.net|mailinator.org|mailinator2.com|mailincubator.com|mailismagic.com|mailme.lv|mailme24.com|mailmetrash.com|mailmoat.com|mailms.com|mailnesia.com|mailnull.com|mailorg.org|mailpick.biz|mailrock.biz|mailscrap.com|mailshell.com|mailsiphon.com|mailtemp.info|mailtome.de|mailtothis.com|mailtrash.net|mailtv.net|mailtv.tv|mailzilla.com|makemetheking.com|manybrain.com|mbx.cc|mega.zik.dj|meinspamschutz.de|meltmail.com|messagebeamer.de|mezimages.net|ministry-of-silly-walks.de|mintemail.com|misterpinball.de|moncourrier.fr.nf|monemail.fr.nf|monmail.fr.nf|monumentmail.com|mt2009.com|mt2014.com|mycard.net.ua|mycleaninbox.net|mymail-in.net|mypacks.net|mypartyclip.de|myphantomemail.com|mysamp.de|mytempemail.com|mytempmail.com|mytrashmail.com|nabuma.com|neomailbox.com|nepwk.com|nervmich.net|nervtmich.net|netmails.com|netmails.net|neverbox.com|nice-4u.com|nincsmail.hu|nnh.com|no-spam.ws|noblepioneer.com|nomail.pw|nomail.xl.cx|nomail2me.com|nomorespamemails.com|nospam.ze.tc|nospam4.us|nospamfor.us|nospammail.net|notmailinator.com|nowhere.org|nowmymail.com|nurfuerspam.de|nus.edu.sg|objectmail.com|obobbo.com|odnorazovoe.ru|oneoffemail.com|onewaymail.com|onlatedotcom.info|online.ms|opayq.com|ordinaryamerican.net|otherinbox.com|ovpn.to|owlpic.com|pancakemail.com|pcusers.otherinbox.com|pjjkp.com|plexolan.de|poczta.onet.pl|politikerclub.de|poofy.org|pookmail.com|privacy.net|privatdemail.net|proxymail.eu|prtnx.com|putthisinyourspamdatabase.com|putthisinyourspamdatabase.com|qq.com|quickinbox.com|rcpt.at|reallymymail.com|realtyalerts.ca|recode.me|recursor.net|reliable-mail.com|rhyta.com|rmqkr.net|royal.net|rtrtr.com|s0ny.net|safe-mail.net|safersignup.de|safetymail.info|safetypost.de|saynotospams.com|schafmail.de|schrott-email.de|secretemail.de|secure-mail.biz|senseless-entertainment.com|services391.com|sharklasers.com|shieldemail.com|shiftmail.com|shitmail.me|shitware.nl|shmeriously.com|shortmail.net|sibmail.com|sinnlos-mail.de|slapsfromlastnight.com|slaskpost.se|smashmail.de|smellfear.com|snakemail.com|sneakemail.com|sneakmail.de|snkmail.com|sofimail.com|solvemail.info|sogetthis.com|soodonims.com|spam4.me|spamail.de|spamarrest.com|spambob.net|spambog.ru|spambox.us|spamcannon.com|spamcannon.net|spamcon.org|spamcorptastic.com|spamcowboy.com|spamcowboy.net|spamcowboy.org|spamday.com|spamex.com|spamfree.eu|spamfree24.com|spamfree24.de|spamfree24.org|spamgoes.in|spamgourmet.com|spamgourmet.net|spamgourmet.org|spamherelots.com|spamherelots.com|spamhereplease.com|spamhereplease.com|spamhole.com|spamify.com|spaml.de|spammotel.com|spamobox.com|spamslicer.com|spamspot.com|spamthis.co.uk|spamtroll.net|speed.1s.fr|spoofmail.de|stuffmail.de|super-auswahl.de|supergreatmail.com|supermailer.jp|superrito.com|superstachel.de|suremail.info|talkinator.com|teewars.org|teleworm.com|teleworm.us|temp-mail.org|temp-mail.ru|tempe-mail.com|tempemail.co.za|tempemail.com|tempemail.net|tempemail.net|tempinbox.co.uk|tempinbox.com|tempmail.eu|tempmaildemo.com|tempmailer.com|tempmailer.de|tempomail.fr|temporaryemail.net|temporaryforwarding.com|temporaryinbox.com|temporarymailaddress.com|tempthe.net|thankyou2010.com|thc.st|thelimestones.com|thisisnotmyrealemail.com|thismail.net|throwawayemailaddress.com|tilien.com|tittbit.in|tizi.com|tmailinator.com|toomail.biz|topranklist.de|tradermail.info|trash-mail.at|trash-mail.com|trash-mail.de|trash2009.com|trashdevil.com|trashemail.de|trashmail.at|trashmail.com|trashmail.de|trashmail.me|trashmail.net|trashmail.org|trashymail.com|trialmail.de|trillianpro.com|twinmail.de|tyldd.com|uggsrock.com|umail.net|uroid.com|us.af|venompen.com|veryrealemail.com|viditag.com|viralplays.com|vpn.st|vsimcard.com|vubby.com|wasteland.rfc822.org|webemail.me|weg-werf-email.de|wegwerf-emails.de|wegwerfadresse.de|wegwerfemail.com|wegwerfemail.de|wegwerfmail.de|wegwerfmail.info|wegwerfmail.net|wegwerfmail.org|wh4f.org|whyspam.me|willhackforfood.biz|willselfdestruct.com|winemaven.info|wronghead.com|www.e4ward.com|www.mailinator.com|wwwnew.eu|x.ip6.li|xagloo.com|xemaps.com|xents.com|xmaily.com|xoxy.net|yep.it|yogamaven.com|yopmail.com|yopmail.fr|yopmail.net|yourdomain.com|yuurok.com|z1p.biz|za.com|zehnminuten.de|zehnminutenmail.de|zippymail.info|zoemail.net|zomg.info";

        $domain = self::extract_domain_from_email($email);

        return preg_match("/\\b" . preg_quote($domain, "/") . "\\b/i", $domain_list);
    }

    /**
     * Is email from a common service provider?
     *
     * @param string $email
     * @return string
     */
    public static function is_common_email_domain($email)
    {

        // Domain list retrieved from https://gist.github.com/mavieth/418b0ba7b3525517dd85b31ee881b2ec
        $domain_list = "aol.com|att.net|comcast.net|facebook.com|gmail.com|gmx.com|googlemail.com|google.com|hotmail.com|hotmail.co.uk|mac.com|me.com|mail.com|msn.com|live.com|sbcglobal.net|verizon.net|yahoo.com|yahoo.co.uk|email.com|fastmail.fm|games.com|gmx.net|hush.com|hushmail.com|icloud.com|iname.com|inbox.com|lavabit.com|love.com|outlook.com|pobox.com|protonmail.ch|protonmail.com|tutanota.de|tutanota.com|tutamail.com|tuta.io|keemail.me|rocketmail.com|safe-mail.net|wow.com|ygm.com|ymail.com|zoho.com|yandex.com|bellsouth.net|charter.net|cox.net|earthlink.net|juno.com|btinternet.com|virginmedia.com|blueyonder.co.uk|freeserve.co.uk|live.co.uk|ntlworld.com|o2.co.uk|orange.net|sky.com|talktalk.co.uk|tiscali.co.uk|virgin.net|wanadoo.co.uk|bt.com|sina.com|sina.cn|qq.com|naver.com|hanmail.net|daum.net|nate.com|yahoo.co.jp|yahoo.co.kr|yahoo.co.id|yahoo.co.in|yahoo.com.sg|yahoo.com.ph|163.com|yeah.net|126.com|21cn.com|aliyun.com|foxmail.com|hotmail.fr|live.fr|laposte.net|yahoo.fr|wanadoo.fr|orange.fr|gmx.fr|sfr.fr|neuf.fr|free.fr|gmx.de|hotmail.de|live.de|online.de|t-online.de|web.de|yahoo.de|libero.it|virgilio.it|hotmail.it|aol.it|tiscali.it|alice.it|live.it|yahoo.it|email.it|tin.it|poste.it|teletu.it|mail.ru|rambler.ru|yandex.ru|ya.ru|list.ru|hotmail.be|live.be|skynet.be|voo.be|tvcablenet.be|telenet.be|hotmail.com.ar|live.com.ar|yahoo.com.ar|fibertel.com.ar|speedy.com.ar|arnet.com.ar|yahoo.com.mx|live.com.mx|hotmail.es|hotmail.com.mx|prodigy.net.mx|yahoo.ca|hotmail.ca|bell.net|shaw.ca|sympatico.ca|rogers.com|yahoo.com.br|hotmail.com.br|outlook.com.br|uol.com.br|bol.com.br|terra.com.br|ig.com.br|itelefonica.com.br|r7.com|zipmail.com.br|globo.com|globomail.com|oi.com.br";

        // Whitelist Socialcatfish.com domain
        $domain_list .= "|socialcatfish.com";

        $domain = self::extract_domain_from_email($email);

        return preg_match("/\\b" . preg_quote($domain, "/") . "\\b/i", $domain_list);
    }

    public static function address_format($address)
    {

        $a = explode(',', ucwords($address));
        $b = strtoupper(end($a));
        array_pop($a);
        array_push($a, $b);
        return implode(',', $a);
    }
}


$execution_start_time = microtime(true);
register_shutdown_function(["SCF", "log_request_execution_time"], $execution_start_time);
