<?php

if (! defined("FROM_INDEX")) {
    die();
}

set_time_limit(0);
ini_set('memory_limit', '1024M');

if ("name_update" == $section) {
    $results = Search::get_results_for_name_update();

    foreach ($results as $result) {
        $data = unserialize($result["data"]);
        if (! empty($data["name"])) {
            $dbi->update(DB_TBL_SEARCH_PERSON_CACHE, [ "name" => $data["name"] ], "id = {$result["id"]}");
        }
    }
} elseif ("ip_log_rotate" == $section) {
    // This can be executed daily, Depending on how long we should keep track of an ip

    $temp_file = PATH_IP_LOG_SEARCH . "_";
    $full_log_file = PATH_IP_LOG_SEARCH_FULL_LOG;

    if (rename(PATH_IP_LOG_SEARCH, $temp_file)) {
        if (`cat {$temp_file} >> {$full_log_file}`) {
            @unlink($temp_file);
        }
    }
} elseif ("ip_blacklist_update" == $section) {
    // This should be executed every 10 minutes

    $cmd = "cat " . PATH_IP_LOG_SEARCH . " | grep -Eo \"[0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+\" | sort | uniq -c | awk '\$1>100 {print \$2}' >> " . PATH_IP_LOG_BLACKLISTED;
    `{$cmd}`;

    $cmd = "cat " . PATH_IP_LOG_BLACKLISTED . " | sort | uniq > " . PATH_IP_LOG_BLACKLISTED . "_";
    `{$cmd}`;

    $cmd = "cat " . PATH_IP_LOG_BLACKLISTED . "_ | grep -v -F -f " . PATH_IP_LOG_WHITELISTED_CRAWLER . " | grep -v -F -f " . PATH_IP_LOG_WHITELISTED . " > " . PATH_IP_LOG_BLACKLISTED;
    `{$cmd}`;

    @unlink(PATH_IP_LOG_BLACKLISTED . "_");
} elseif ("ip_whitelist_crawler" == $section) {
    // This should be executed daily

    $client = new \vbrowser();

    $endpoint_list = [
        "google" => "https://developers.google.com/static/search/apis/ipranges/googlebot.json",
        "bing" => "https://www.bing.com/toolbox/bingbot.json",
    ];

    foreach ($endpoint_list as $service => $url) {
        $stream = $client->request($url);
        $ip_data = json_decode($stream["response"], true);

        $ip_list[ $service ] = [];
        if (! empty($ip_data["prefixes"])) {
            foreach ($ip_data["prefixes"] as $data) {
                foreach ($data as $prefix => $cidr) {
                    if ($prefix == "ipv4Prefix") {
                        $ip_list[ $service ] = array_merge($ip_list[ $service ], SYSTEM::generate_ips_from_cidr($cidr));
                    }
                }
            }
        }
    }

    if (count($ip_list["google"]) > 100 && count($ip_list["bing"]) > 100) {
        $ip_list = array_merge($ip_list["google"], $ip_list["bing"]);
        file_put_contents(PATH_IP_LOG_WHITELISTED_CRAWLER, implode("\n", $ip_list));
    }

    die();
} elseif ("most_searched_names" == $section) {
    Search::update_most_searched_names();
} elseif ("smoke_test_ris" == $section) {
    $count = reports::smoke_test_ris_pending_count();

    if ($count['pending_count'] > 500) {
        reports::update_smoke_test_ris_pending_count("ris", $count['pending_count']);

        $email_template_data = SCF::get_mail_template("RIS-Pending-Searches-Alert", ["count" => $count['pending_count']]);
        $mailer = SCF::get_mailer();
        $mailer->Subject = "RIS Pending Searches Alert";
        $mailer->addAddress("franz@socialcatfish.com", "Franz Cruz");
        $mailer->addAddress("savindram@socialcatfish.com", "Savindra Marasinghe");
        $mailer->addAddress("romesh@socialcatfish.com", "Romesh");
        $mailer->addAddress("vimanse@socialcatfish.com", "Vimanse");
        $mailer->addAddress("supun@socialcatfish.com", "Supun");

        $mailer->msgHTML($email_template_data["html"]);
        $mailer->AltBody = $email_template_data["text"];
        $mailer->send();
    }
} elseif ("remove_active_users_from_abandoned_cart_lists_sendy" == $section) {
    $sendy = SYSTEM::loadsendy();

    $data = User::get_all_active_users();
    if (! empty($data)) {
        foreach ($data as $k => $v) {
            $sendy->setListId(SENDY_LIST_ABANDONEDCART_IMAGE_SEARCH_REMOVED_ACTIVE_USERS);
            $sendy->unsubscribe($v["email"]);

            $sendy->setListId(SENDY_LIST_ABANDONEDCART_STANDARD_SEARCH_REMOVED_ACTIVE_USERS);
            $sendy->unsubscribe($v["email"]);

            $sendy->setListId(SENDY_LIST_ABANDONEDCART_SEARCH_SPECIALIST_REMOVED_ACTIVE_USERS);
            $sendy->unsubscribe($v["email"]);
        }
    }

    die("done");
} elseif ("download_latest_externel_files" == $section) {
} elseif ("run_privacy_lock_pwned_api" == $section) {
    PWNED::update_user_pawned_data_bulk();
} elseif ("action_log_hourly_average" == $section) {
    $search_types = [
        "login:1" => "Login",
        "search:" . SEARCH_TYPE_USERNAME => "Search:Username",
        "search:" . SEARCH_TYPE_EMAIL => "Search:Email",
        "search:" . SEARCH_TYPE_NAME => "Search:Name",
        "search:" . SEARCH_TYPE_PHONE => "Search:Phone",
        "search:" . SEARCH_TYPE_IMAGE => "Search:Image",
        "search:" . SEARCH_TYPE_RAS => "Search:RAS"
    ];
    $to_date = date("Y-m-d H:i:s");
    foreach ($search_types as $key => $value) {
        $hourly_avg_lastweek = Action_log::hourly_avg_lastweek($key, $to_date);
        $hourly_avg_everyday_lastweek = Action_log::hourly_avg_everyday_lastweek($key, $to_date);
        $hourly_avg_thisday_lastfewweek = Action_log::hourly_avg_thisday_lastfewweek($key, $to_date);
        $hourly_avg_thisday_lastyear = Action_log::hourly_avg_thisday_lastyear($key, $to_date);
        if ($hourly_avg_lastweek <= 20 && $hourly_avg_everyday_lastweek <= 20 && $hourly_avg_thisday_lastfewweek <= 20 && $hourly_avg_thisday_lastyear <= 20) {
            if (defined("CMD_MODULE")) {
                \Slack::sendAlert("ACTION LOG ALERT!!", "The number of [$value] seems very low comparing to the averages ref [#$value]", SLACK_ACTION_LOG_ALERT_WEBHOOK_URL);
            }
            echo "<label style='color: red;'><b>The number of searches seems very low comparing to the averages ref [#$key]($value) </b></label><br>";
        } else {
            echo "<label style='color: green;'>Searches working fine!! [#$key]($value)</label><br>";
        }
    }
    $hourly_avg_lastweek = Action_log::payment_hourly_avg_lastweek($to_date);
    $hourly_avg_everyday_lastweek = Action_log::payment_hourly_avg_everyday_lastweek($to_date);
    $hourly_avg_thisday_lastfewweek = Action_log::payment_hourly_avg_thisday_lastfewweek($to_date);
    $hourly_avg_thisday_lastyear = Action_log::payment_hourly_avg_thisday_lastyear($to_date);
    if ($hourly_avg_lastweek <= 20 && $hourly_avg_everyday_lastweek <= 20 && $hourly_avg_thisday_lastfewweek <= 20 && $hourly_avg_thisday_lastyear <= 20) {
        if (defined("CMD_MODULE")) {
            \Slack::sendAlert("ACTION LOG ALERT!!", "The number of payments seems very low comparing to the averages ref #$value", SLACK_ACTION_LOG_ALERT_WEBHOOK_URL);
        }
        echo "<label style='color: red;'><b>The number of Payments seems very low comparing to the averages ref [#Payments:1](Payments)</b></label><br>";
    } else {
        echo "<label style='color: green;'>Searches working fine!![#Payments:1](Payments)</label><br>";
    }
} elseif ("run_privacy_lock_checker" == $section) {
    $cron_qa_email = System::request_get("cron_qa_email", "");


    $users = User::get_all_active_users_privacy_lock_acivated();
    foreach ($users as $k => $v) {
        if (! empty($v["email"])) {
            if (! empty($cron_qa_email)) {
                if ($v["email"] != $cron_qa_email) {
                    continue;
                }
            }


            if (date('j') == 1 || ! empty($cron_qa_email)) {
                if (strtotime($v["created"]) < strtotime('-20 days')) {
                    $pwned_lastmonth = PWNED::is_pwned_within_last_month($v["id"]);
                    if (! empty($pwned_lastmonth)) {
                        $email_template_data = SCF::get_mail_template(
                            "override_template_privacy_lock_issue_found_monthly",
                            [
                            "User" => "{$v["first_name"]} {$v["last_name"]}",
                            ]
                        );

                        $mailer = SCF::get_mailer();
                        $mailer->addAddress($v["email"], "{$v["first_name"]} {$v["last_name"]}");
                        $mailer->Subject = "Changes Detected";
                        $mailer->msgHTML($email_template_data["html"]);
                        $mailer->AltBody = $email_template_data["text"];
                        $mailer->send();
                    } else {
                        $email_template_data = SCF::get_mail_template(
                            "override_template_privacy_lock_no_issue_found_monthly",
                            [
                            "User" => "{$v["first_name"]} {$v["last_name"]}",
                            ]
                        );

                        $mailer = SCF::get_mailer();
                        $mailer->addAddress($v["email"], "{$v["first_name"]} {$v["last_name"]}");
                        $mailer->Subject = "We have some good news for you!";
                        $mailer->msgHTML($email_template_data["html"]);
                        $mailer->AltBody = $email_template_data["text"];
                        $mailer->send();
                    }
                }
            }


            if ($v["created"] == date('Y-m-d', strtotime('-2 days'))) {
                $email_template_data = SCF::get_mail_template(
                    "override_template_privacy_lock_2days_after_signup",
                    [
                    "User" => "{$v["first_name"]} {$v["last_name"]}",
                    ]
                );

                $mailer = SCF::get_mailer();
                $mailer->addAddress($v["email"], "{$v["first_name"]} {$v["last_name"]}");
                $mailer->Subject = "Thank you for using Privacy Lock!";
                $mailer->msgHTML($email_template_data["html"]);
                $mailer->AltBody = $email_template_data["text"];
                $mailer->send();
            }
        }


        $pl = @unserialize($v["privacy_lock_emails"]);


        if (is_array($pl) && ! empty($pl)) {
            foreach ($pl as $pl_k => $pl_v) {
                if (count($pl_v) != 3 || $pl_v[1] != 2 || empty($pl_v[0])) {
                    continue;
                }
                $pwned = PWNED::check_PWNED($pl_v[0]);
                $sitescount = ! empty($pwned->sites) ? count($pwned->sites) : 0;
                $status = PWNED::check_user_pawned_data($v["id"], $pl_v[0], $sitescount);
                $data = [
                    "user_id"   =>  $v["id"],
                    "email"     =>  $pl_v[0],
                    "status"    =>  $sitescount,
                    "data"      =>  serialize($pwned),
                    "date"      =>  date("Y-m-d H:i:s"),
                    "change_detected"   => ($status ? 1 : 0)
                ];

                $dbi->insert(DB_TBL_PRIVACYLOCK_DATA, $data);

                if (! empty($status)) {
                    $email_template_data = SCF::get_mail_template(
                        "override_template_privacy_lock_change_detected",
                        [
                        "User" => "{$v["first_name"]} {$v["last_name"]}",
                        "email" => $pl_v[0],
                        ]
                    );

                    $mailer = SCF::get_mailer();
                    $mailer->addAddress($v["email"], "{$v["first_name"]} {$v["last_name"]}");
                    $mailer->Subject = "Your Email May Have Been Compromised.";
                    $mailer->msgHTML($email_template_data["html"]);
                    $mailer->AltBody = $email_template_data["text"];
                    $mailer->send();
                }
            }
        } else {
            if (! empty($v["email"])) {
                $pwned = PWNED::check_PWNED($v["email"]);
                $sitescount = ! empty($pwned->sites) ? count($pwned->sites) : 0;
                $status = PWNED::check_user_pawned_data($v["id"], $v["email"], $sitescount);
                $data = [
                    "user_id"   =>  $v["id"],
                    "email"     =>  $v["email"],
                    "status"    =>  $sitescount,
                    "data"      =>  serialize($pwned),
                    "date"      =>  date("Y-m-d H:i:s"),
                    "change_detected"   => ($status ? 1 : 0)
                ];

                $dbi->insert(DB_TBL_PRIVACYLOCK_DATA, $data);

                if (! empty($status)) {
                    $email_template_data = SCF::get_mail_template(
                        "override_template_privacy_lock_change_detected",
                        [
                        "User" => "{$v["first_name"]} {$v["last_name"]}",
                        ]
                    );

                    $mailer = SCF::get_mailer();
                    $mailer->addAddress($v["email"], "{$v["first_name"]} {$v["last_name"]}");
                    $mailer->Subject = "Your Email May Have Been Compromised.";
                    $mailer->msgHTML($email_template_data["html"]);
                    $mailer->AltBody = $email_template_data["text"];
                    $mailer->send();
                }
            }
        }
    }



    die("done");
    /*
    if( ! empty( $users ) ){

    foreach( $users as $k => $user ){

    $data = [];
    $privacy_lock_emails = ! empty( $user["privacy_lock_emails"] ) ? explode(",", $user["privacy_lock_emails"] ) : [ $user["email"] ];
    foreach( $privacy_lock_emails as $protected_email ){
    // Check if pwned before
    $status = PWNED::check_user_pawned_data( $user["id"], $protected_email  );
    if(1 === $status) $data[] = PWNED::save_user_pawned_data( $protected_email );
    elseif(2 === $status) $data[] = PWNED::update_user_pawned_data( $user["id"], $protected_email );
    }

    $data = [
    "data"     =>  $data
    ];

    $email_template_data = SCF::get_mail_template("privacy_lock_email_first_reminder", $data );
    $mailer = SCF::get_mailer();
    $mailer->addAddress( $user['email'], "SocialCatfish" );
    $mailer->Subject = "Privacy Lock Reminder";
    $mailer->msgHTML( $email_template_data["html"] );
    $mailer->AltBody = $email_template_data["text"];
    $mailer->send();
    }

    }
    */


    die("f");
} elseif ("check_sendy_new_version_update" == $section) {
    $html = file_get_contents('https://socialcatfishmx.com/scf_tools/check_the_version.php');

    if (! empty($html)) {
        $versions = explode(":", $html);
        if (! empty($versions[0]) && ! empty($versions[1])) {
            $email_template_data = SCF::get_mail_template(
                "sendy-new-version-available",
                [
                "current" => $versions[0],
                "new" => $versions[1]
                ]
            );

            $mailer = SCF::get_mailer();
            $mailer->addAddress("david@socialcatfish.com", "David McClellan");
            $mailer->addBCC("franz@socialcatfish.com", "Franz Cruz");
            $mailer->addBCC("ruwan@socialcatfish.com", "Ruwan Perera");
            $mailer->addBCC("savindram@socialcatfish.com", "Savindra Marasinghe");
            $mailer->Subject = "Sendy new version is available.";
            $mailer->msgHTML($email_template_data["html"]);
            $mailer->AltBody = $email_template_data["text"];
            $mailer->send();
        }
    }
} elseif ("ris_email_notification" == $section) {
    $get_panding_list = Search::get_ris_notification();

    foreach ($get_panding_list as $row) {
        $email_template_data = SCF::get_mail_template("ris-email-notification", [ "title" =>  "Your image search report is ready" ]);
        $mailer = SCF::get_mailer();
        $mailer->addAddress($row["email"], "SocialCatfish");
        $mailer->Subject = "Your image search report is ready";
        $mailer->msgHTML($email_template_data["html"]);
        $mailer->AltBody = $email_template_data["text"];
        $mailer->send();

        Search::update_ris_notification($row["id"], 2);
    }
} elseif ("send_mobile_app_push_notification" == $section) {
    mobileapp::send_mobile_notifications();
} elseif ("recurring_mobile_app_push_notification" == $section) {
    mobileapp::recurring_push_notification();
} elseif ("ris_baseline_alerts" == $section) {
    $dbi = new DBI();
    $dbi->connect(API_DB_HOST, API_DB_USER, API_DB_PASSWORD, API_DB_NAME, 1, 0);
    $sql_avg_by_day = sprintf("SELECT AVG( c.yandex_results ) yandex_results_avg, AVG( c.googleLens_results ) googleLens_results_avg, AVG( c.bingapi_results ) bingapi_results_avg, AVG( c.tineye_results ) tineye_results_avg, AVG( c.bing_results ) bing_results_avg, AVG( c.google_results ) google_results_avg FROM %s c WHERE c.date >= DATE_SUB(NOW(), INTERVAL 3 DAY)", DB_TBL_CACHE);

    $sql_avg_by_hour = sprintf("SELECT AVG( c.yandex_results ) yandex_results_avg, AVG( c.googleLens_results ) googleLens_results_avg, AVG( c.bingapi_results ) bingapi_results_avg,AVG( c.tineye_results ) tineye_results_avg, AVG( c.bing_results ) bing_results_avg, AVG( c.google_results ) google_results_avg FROM %s c WHERE c.date >= DATE_SUB(NOW(), INTERVAL 3 HOUR)", DB_TBL_CACHE);

    $avg_by_day = $dbi->query_to_array($sql_avg_by_day);
    $avg_by_hour = $dbi->query_to_array($sql_avg_by_hour);

    $avg_diff = abs($avg_by_day["yandex_results_avg"] - $avg_by_hour["yandex_results_avg"]);
    $avg_d_h = (($avg_by_day["yandex_results_avg"] + $avg_by_hour["yandex_results_avg"]) / 2) * 0.5;
    if ($avg_diff > $avg_d_h ||  $avg_by_day["yandex_results_avg"] <= 0 || $avg_by_hour["yandex_results_avg"] <= 0) {
        \Slack::sendAlert("RIS Baseline - There are an abnormal count in Yandex", API_THRESHOLD_SLACK_ALERT_WEBHOOK);
    }

    $avg_diff = abs($avg_by_day["googleLens_results_avg"] - $avg_by_hour["googleLens_results_avg"]);
    $avg_d_h = (($avg_by_day["googleLens_results_avg"] + $avg_by_hour["googleLens_results_avg"]) / 2) * 0.5;
    if ($avg_diff > $avg_d_h ||  $avg_by_day["googleLens_results_avg"] <= 0 || $avg_by_hour["googleLens_results_avg"] <= 0) {
        \Slack::sendAlert("RIS Baseline - There are an abnormal count in Google Lens", API_THRESHOLD_SLACK_ALERT_WEBHOOK);
    }

    $avg_diff = abs($avg_by_day["bingapi_results_avg"] - $avg_by_hour["bingapi_results_avg"]);
    $avg_d_h = (($avg_by_day["bingapi_results_avg"] + $avg_by_hour["bingapi_results_avg"]) / 2) * 0.5;
    if ($avg_diff > $avg_d_h ||  $avg_by_day["bingapi_results_avg"] <= 0 || $avg_by_hour["bingapi_results_avg"] <= 0) {
        \Slack::sendAlert("RIS Baseline - There are an abnormal count in Bing API", API_THRESHOLD_SLACK_ALERT_WEBHOOK);
    }

    $avg_diff = abs($avg_by_day["tineye_results_avg"] - $avg_by_hour["tineye_results_avg"]);
    $avg_d_h = (($avg_by_day["tineye_results_avg"] + $avg_by_hour["tineye_results_avg"]) / 2) * 0.5;
    if ($avg_diff > $avg_d_h || $avg_by_day["tineye_results_avg"] <= 0 || $avg_by_hour["tineye_results_avg"] <= 0) {
        \Slack::sendAlert("RIS Baseline - There are an abnormal count in Tineye", API_THRESHOLD_SLACK_ALERT_WEBHOOK);
    }

    $avg_diff = abs($avg_by_day["bing_results_avg"] - $avg_by_hour["bing_results_avg"]);
    $avg_d_h = (($avg_by_day["bing_results_avg"] + $avg_by_hour["bing_results_avg"]) / 2) * 0.5;
    if ($avg_diff > $avg_d_h ||  $avg_by_day["bing_results_avg"] <= 0 || $avg_by_hour["bing_results_avg"] <= 0) {
        \Slack::sendAlert("RIS Baseline - There are an abnormal count in Bing", API_THRESHOLD_SLACK_ALERT_WEBHOOK);
    }

    $avg_diff = abs($avg_by_day["google_results_avg"] - $avg_by_hour["google_results_avg"]);
    $avg_d_h = (($avg_by_day["google_results_avg"] + $avg_by_hour["google_results_avg"]) / 2) * 0.5;
    if (($avg_diff > $avg_d_h) || $avg_by_day["google_results_avg"] <= 0 || $avg_by_hour["google_results_avg"] <= 0) {
        \Slack::sendAlert("RIS Baseline - There are an abnormal count in Google", API_THRESHOLD_SLACK_ALERT_WEBHOOK);
    }
} elseif ("ris_api_status" == $section) {
    $dbi_cache = new DBI();
    $dbi_cache->connect(API_DB_HOST, API_DB_USER, API_DB_PASSWORD, API_DB_NAME, 1, 0);
    $sql = sprintf(
        "select count(*) as total,
			IF(((count(case yandex_results when 0 then 1 else null end)/count(*))*100) >50 , false,true) yandex,
			IF(((count(case googleLens_results when 0 then 1 else null end)/count(*))*100) >99 , false,true) googleLens,
			IF(((count(case bingapi_results when 0 then 1 else null end)/count(*))*100) >90 , false,true) bingapi,
			IF(((count(case tineye_results when 0 then 1 else null end)/count(*))*100) >99 , false,true) tineye,
			IF(((count(case bing_results when 0 then 1 else null end)/count(*))*100) >70 , false,true) bing,
			IF(((count(case google_results when 0 then 1 else null end)/count(*))*100) >50 , false,true) google
			from %s  WHERE date >= DATE_SUB(NOW(), INTERVAL 5 HOUR);",
        DB_TBL_CACHE
    );

    $result = $dbi_cache->query_to_array($sql);

    global $dbi;

    if ($result["yandex"] == 0) {
        $sql = sprintf("update %s set status = 0 , last_checked = now() , last_downtime = IF(last_downtime is null ,now(),last_downtime) where id = 1", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    } else {
        $sql = sprintf("update %s set status = 1 , last_checked = now() , last_downtime = null where id = 1", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    }

    if ($result["googleLens"] == 0) {
        $sql = sprintf("update %s set status = 0 , last_checked = now() , last_downtime = IF(last_downtime is null ,now(),last_downtime) where id = 2", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    } else {
        $sql = sprintf("update %s set status = 1 , last_checked = now() , last_downtime = null where id = 2", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    }

    if ($result["bingapi"] == 0) {
        $sql = sprintf("update %s set status = 0 , last_checked = now() , last_downtime = IF(last_downtime is null ,now(),last_downtime) where id = 3", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    } else {
        $sql = sprintf("update %s set status = 1 , last_checked = now() , last_downtime = null where id = 3", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    }

    if ($result["tineye"] == 0) {
        $sql = sprintf("update %s set status = 0 , last_checked = now() , last_downtime = IF(last_downtime is null ,now(),last_downtime) where id = 4", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    } else {
        $sql = sprintf("update %s set status = 1 , last_checked = now() , last_downtime = null where id = 4", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    }

    if ($result["bing"] == 0) {
        $sql = sprintf("update %s set status = 0 , last_checked = now() , last_downtime = IF(last_downtime is null ,now(),last_downtime) where id = 5", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    } else {
        $sql = sprintf("update %s set status = 1 , last_checked = now() , last_downtime = null where id = 5", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    }

    if ($result["google"] == 0) {
        $sql = sprintf("update %s set status = 0 , last_checked = now() , last_downtime = IF(last_downtime is null ,now(),last_downtime) where id = 6", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    } else {
        $sql = sprintf("update %s set status = 1 , last_checked = now() , last_downtime = null where id = 6", DB_TBL_RIS_API_STATUS);
        $dbi->query($sql);
    }

    print_r($result);
} elseif ("unviewed_ris_report_alert_sendy" == $section) {
    $firstmail_reports = [];
    $secondmail_reports = [];
    $firstmail_users = [];
    $secondmail_users = [];

    $firstmail_report_link_list = [];
    $secondmail_report_link_list = [];

    $firstmail_reports_links = [];
    $secondmail_reports_links = [];

    $data = Search::sendy_unviewed_ris_reports();
    if (! empty($data)) {
        foreach ($data as $k => $v) {
            $reports = Search::sendy_unviewed_ris_reports($v["user_id"]);

            foreach ($reports as $report) {
                if ($report["has_sent_first_mail"]) {
                    array_push($secondmail_reports, $report);
                    array_push($secondmail_users, ["email" => $report["email"] , "first_name" => $report["first_name"] , "last_name" => $report["last_name"]]);
                } else {
                    array_push($firstmail_reports, $report);
                    array_push($firstmail_users, ["email" => $report["email"] , "first_name" => $report["first_name"] , "last_name" => $report["last_name"]]);
                }
            }
        }

        foreach ($firstmail_reports as $firstmail_report) {
            $firstmail_reports_links[$firstmail_report["email"]] .= '<p><a href="' . BASE_URL . "reverse-image-search?" . $firstmail_report["id"] . '">' . BASE_URL . "reverse-image-search?" . $firstmail_report["id"] . '</a></p>';
        }

        foreach ($secondmail_reports as $secondmail_report) {
            $secondmail_reports_links[$secondmail_report["email"]] .= '<p><a href="' . BASE_URL . "reverse-image-search?" . $secondmail_report["id"] . '">' . BASE_URL . "reverse-image-search?" . $secondmail_report["id"] . '</a></p>';
        }

        foreach (array_intersect_key($firstmail_users, array_unique(array_column($firstmail_users, 'email'))) as $user) {
            $email_template_data = SCF::get_mail_template(
                "override_template_ris_unviewed_report_first_mail",
                [
                "User" => "{$user["first_name"]} {$user["last_name"]}",
                "Reports" => $firstmail_reports_links[$user["email"]]
                ],
                false
            );

            $mailer = SCF::get_mailer();
            $mailer->addAddress($user["email"], "{$user["first_name"]} {$user["last_name"]}");
            $mailer->Subject = "Unviewed Reverse Image Search Report";
            $mailer->msgHTML($email_template_data["html"]);
            $mailer->AltBody = $email_template_data["text"];
            $mailer->send();
        }

        foreach (array_intersect_key($secondmail_users, array_unique(array_column($firstmail_users, 'email'))) as $user) {
            $email_template_data = SCF::get_mail_template(
                "override_template_ris_unviewed_report_second_mail",
                [
                "User" => "{$user["first_name"]} {$user["last_name"]}",
                "Reports" => $secondmail_reports_links[$user["email"]]
                ],
                false
            );

            $mailer = SCF::get_mailer();
            $mailer->addAddress($user["email"], "{$user["first_name"]} {$user["last_name"]}");
            $mailer->Subject = "Unviewed Reverse Image Search Report";
            $mailer->msgHTML($email_template_data["html"]);
            $mailer->AltBody = $email_template_data["text"];
            $mailer->send();
        }
    }

    die("done");
} elseif ("ris_re_run_downtime_reports" == $section) {
    global $dbi;

    $sql = sprintf("select * from %s where status = 0", DB_TBL_RIS_API_STATUS);
    $results = $dbi->query_to_array($sql);
    if (!$results) {
        $sql = sprintf("SELECT *  FROM %s i WHERE re_ran_report = 0 limit 5", DB_TBL_IMAGE_SEARCH_DOWN_TIMELOG);
        $results = $dbi->query_to_multi_array($sql);

        foreach ($results as $result) {
            $sql = sprintf("UPDATE %s SET re_ran_report = 1, old_result_exact_count = %s WHERE image_search_id = %s", DB_TBL_IMAGE_SEARCH_DOWN_TIMELOG, Search::get_image_search_status($result["image_search_id"])["matches_exact"], $result["image_search_id"]);
            $dbi->query($sql);

            $sql = sprintf("UPDATE %s SET pending = 1 , batch = '' ,completed = 0 WHERE id = %s and completed = 1;", DB_TBL_IMAGE_SEARCH, $result["image_search_id"]);
            $dbi->query($sql);
        }

        $sql = sprintf("SELECT *  FROM %s i WHERE re_ran_report = 1 AND proceeded = 0", DB_TBL_IMAGE_SEARCH_DOWN_TIMELOG);
        $results = $dbi->query_to_multi_array($sql);
        foreach ($results as $result) {
            $image_search_result = Search::get_image_search_status($result["image_search_id"]);

            if ($image_search_result["completed"] && !$image_search_result["pending"]) {
                if ($image_search_result["matches_exact"] > $result["old_result_exact_count"]) {
                    $sql = sprintf("SELECT i.user_id, u.email FROM %s i inner join %s u on i.user_id = u.id WHERE i.id = %s", DB_TBL_IMAGE_SEARCH, DB_TBL_USER, $result["image_search_id"]);
                    $data = $dbi->query_to_array($sql);

                    echo $data["email"] . "\n";

                    $sendy = SYSTEM::loadsendy();
                    $sendy->setListId(SENDY_LIST_RIS_DOWN_TIME_REPORTS);

                    $sendy->delete($data["email"]);
                    $sendy->subscribe(
                        array(
                        'email' => $data["email"],
                        )
                    );
                }

                $sql = sprintf("UPDATE %s SET proceeded = 1 WHERE image_search_id = %s", DB_TBL_IMAGE_SEARCH_DOWN_TIMELOG, $result["image_search_id"]);
                $dbi->query($sql);
            }
        }
    }
}
die();
