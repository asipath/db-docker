<?php

use melissadata\MelissaDataRAS;
use ZeroBounceAPI\ZeroBounceAPI;

if (! defined("FROM_INDEX")) {
    die();
}

if (SYSTEM::is_ajax_request()) {
    switch ($cmd) {
        // AB Baselines

        case 'sendy_ss':
            $email = SYSTEM::get_request_value("email", "", "POST");

            if (!DEBUG && !empty($email)) {
                    $sendy = SYSTEM::loadsendy();

                if (!isset($_SESSION["capture-email"])) {
                    $_SESSION["capture-email"] = $email;

                    $email_validation = new ZeroBounceAPI(ZEROBOUNCE_API_KEY);
                    $status = $email_validation->send_request($email);
                    if ($status == "valid") {
                        // standard search
                        $sendy->setListId(SENDY_LIST_ABANDONEDCART_STANDARD_SEARCH);
                        $sendy->subscribe(array(
                            'email' => $email,
                            "name" => ""
                        ));

                        $ajax_status["status"] = $email;
                    } else {
                        $ajax_status["status"] = $email;
                    }
                } else {
                    $_SESSION["capture-email"] = $email;
                    $ajax_status["status"] = $email;
                }
            } else {
                $ajax_status["status"] = $email;
            }

            break;

        case 'sendy_search_specialist':
            $email = SYSTEM::get_request_value("email", "", "POST");

            if (! DEBUG && !empty($email)) {
                $sendy = SYSTEM::loadsendy();

                $sendy->setListId(SENDY_LIST_ABANDONEDCART_SEARCH_SPECIALIST);
                $sendy->subscribe(array(
                    'email' => $post_data["email"],
                    "name" => ""
                ));
            }
            break;

        case 'baselines_cancel_pause_1':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("4_cancel_pause_30", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;

        case 'baselines_cancel_pause_2':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("4_cancel_pause_60", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;

        case 'baselines_cancel_pause_3':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("4_cancel_pause_90", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }
            break;

        case 'baselines_cancel_no':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("1_cancel_no", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;

        case 'baselines_cancel_essentials':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("2_cancel_essentials", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;

        case 'baselines_cancel_switch':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("3_cancel_switch", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;

        case 'baselines_cancel_reason_1':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("5_cancel_full_1", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;
        case 'baselines_cancel_reason_2':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("5_cancel_full_2", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;
        case 'baselines_cancel_reason_3':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("5_cancel_full_3", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;
        case 'baselines_cancel_reason_4':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("5_cancel_full_4", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;
        case 'baselines_cancel_reason_5':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("5_cancel_full_5", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;
        case 'baselines_cancel_reason_6':
            if (isset($_SESSION["ab_baselines_cancel"]) && isset($_SESSION["step_zero_cancel"]) && !isset($_SESSION["step_one_cancel"])) {
                $_SESSION["ab_baselines_cancel"]->track_event("5_cancel_full_6", SYSTEM::get_device_type());
                $_SESSION["step_one_cancel"] = true;
            }

            break;

        case 'baselines_ris_0':
            if (isset($_SESSION["step_one_price_ris"]) && isset($_SESSION["ab_price_change_ris"]) && !isset($_SESSION["step_two_price_ris"]["0"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_0", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["0"] = true;
            }
            if (isset($_SESSION["ab_price_change_ris"]) && isset($_SESSION["step_one_price_ris"]) && !isset($_SESSION["step_two_price_ris"]["0"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_0", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["0"] = true;
            }
            if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_one_WW_RIS"]) && !isset($_SESSION["step_two_WW_RIS"])) {
                $_SESSION["ab_search_progress_WW_RIS"]->track_event("2_image_a_WW_0", SYSTEM::get_device_type());
                $_SESSION["step_two_WW_RIS"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_old_img"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_three"]["1"])) {
                $_SESSION["ab_baselines_old_img"]->track_event("ris_a_step1", SYSTEM::get_device_type());
                $_SESSION["step_three"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_basic_img"]) && isset($_SESSION["step_one_ris_basic"]) && !isset($_SESSION["step_three_basic"]["1"])) {
                $_SESSION["ab_baselines_basic_img"]->track_event("ris_basic_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_three_basic"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_two_ris"]["1"])) {
                $_SESSION["ab_baselines_image"]->track_event("2_image_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_two_ris"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_one_ris_main"]) && !isset($_SESSION["step_two_ris_main"]["1"])) {
                $_SESSION["ab_baselines_image_main"]->track_event("2_image_a_step_1_main", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_main"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_one_ris_ad"]) && !isset($_SESSION["step_two_ris_ad"]["1"])) {
                $_SESSION["ab_baselines_image_ad"]->track_event("2_image_a_step_1_ad", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_ad"]["1"] = true;
            }
            break;

        case 'baselines_ris_1':
            if (isset($_SESSION["step_one_price_ris"]) && isset($_SESSION["ab_price_change_ris"]) && !isset($_SESSION["step_two_price_ris"]["1"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_1", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["1"] = true;
            }
            if (isset($_SESSION["ab_price_change_ris"]) && isset($_SESSION["step_one_price_ris"]) && !isset($_SESSION["step_two_price_ris"]["1"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_1", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["1"] = true;
            }
            if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_one_WW_RIS"]) && !isset($_SESSION["step_two_WW_RIS"]["2"])) {
                $_SESSION["ab_search_progress_WW_RIS"]->track_event("2_image_a_WW_1", SYSTEM::get_device_type());
                $_SESSION["step_two_WW_RIS"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_old_img"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_three"]["2"])) {
                $_SESSION["ab_baselines_old_img"]->track_event("ris_a_step2", SYSTEM::get_device_type());
                $_SESSION["step_three"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_basic_img"]) && isset($_SESSION["step_one_ris_basic"]) && !isset($_SESSION["step_three_basic"]["2"])) {
                $_SESSION["ab_baselines_basic_img"]->track_event("ris_basic_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_three_basic"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_two_ris"]["2"])) {
                $_SESSION["ab_baselines_image"]->track_event("2_image_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_two_ris"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_one_ris_main"]) && !isset($_SESSION["step_two_ris_main"]["2"])) {
                $_SESSION["ab_baselines_image_main"]->track_event("2_image_a_step_2_main", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_main"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_one_ris_ad"]) && !isset($_SESSION["step_two_ris_ad"]["2"])) {
                $_SESSION["ab_baselines_image_ad"]->track_event("2_image_a_step_2_ad", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_ad"]["2"] = true;
            }
            break;

        case 'baselines_ris_2':
            if (isset($_SESSION["step_one_price_ris"]) && isset($_SESSION["ab_price_change_ris"]) && !isset($_SESSION["step_two_price_ris"]["2"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_2", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["2"] = true;
            }
            if (isset($_SESSION["ab_price_change_ris"]) && isset($_SESSION["step_one_price_ris"]) && !isset($_SESSION["step_two_price_ris"]["2"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_2", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["2"] = true;
            }
            if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_one_WW_RIS"]) && !isset($_SESSION["step_two_WW_RIS"]["3"])) {
                $_SESSION["ab_search_progress_WW_RIS"]->track_event("2_image_a_WW_2", SYSTEM::get_device_type());
                $_SESSION["step_two_WW_RIS"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_old_img"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_three"]["3"])) {
                $_SESSION["ab_baselines_old_img"]->track_event("ris_a_step3", SYSTEM::get_device_type());
                $_SESSION["step_three"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_basic_img"]) && isset($_SESSION["step_one_ris_basic"]) && !isset($_SESSION["step_three_basic"]["3"])) {
                $_SESSION["ab_baselines_basic_img"]->track_event("ris_basic_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_three_basic"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_two_ris"]["3"])) {
                $_SESSION["ab_baselines_image"]->track_event("2_image_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_two_ris"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_one_ris_main"]) && !isset($_SESSION["step_two_ris_main"]["3"])) {
                $_SESSION["ab_baselines_image_main"]->track_event("2_image_a_step_3_main", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_main"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_one_ris_ad"]) && !isset($_SESSION["step_two_ris_ad"]["3"])) {
                $_SESSION["ab_baselines_image_ad"]->track_event("2_image_a_step_3_ad", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_ad"]["3"] = true;
            }
            break;

        case 'baselines_ris_3':
            if (isset($_SESSION["step_one_price_ris"]) && isset($_SESSION["ab_price_change_ris"]) && !isset($_SESSION["step_two_price_ris"]["3"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_3", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["3"] = true;
            }
            if (isset($_SESSION["ab_price_change_ris"]) && isset($_SESSION["step_one_price_ris"]) && !isset($_SESSION["step_two_price_ris"]["3"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_3", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["3"] = true;
            }
            if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_one_WW_RIS"]) && !isset($_SESSION["step_two_WW_RIS"]["4"])) {
                $_SESSION["ab_search_progress_WW_RIS"]->track_event("2_image_a_WW_3", SYSTEM::get_device_type());
                $_SESSION["step_two_WW_RIS"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_old_img"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_three"]["4"])) {
                $_SESSION["ab_baselines_old_img"]->track_event("ris_a_step4", SYSTEM::get_device_type());
                $_SESSION["step_three"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_basic_img"]) && isset($_SESSION["step_one_ris_basic"]) && !isset($_SESSION["step_three_basic"]["4"])) {
                $_SESSION["ab_baselines_basic_img"]->track_event("ris_basic_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_three_basic"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_two_ris"]["4"])) {
                $_SESSION["ab_baselines_image"]->track_event("2_image_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_two_ris"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_one_ris_main"]) && !isset($_SESSION["step_two_ris_main"]["4"])) {
                $_SESSION["ab_baselines_image_main"]->track_event("2_image_a_step_4_main", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_main"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_one_ris_ad"]) && !isset($_SESSION["step_two_ris_ad"]["4"])) {
                $_SESSION["ab_baselines_image_ad"]->track_event("2_image_a_step_4_ad", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_ad"]["4"] = true;
            }
            break;

        case 'baselines_ris_4':
            if (isset($_SESSION["step_one_price_ris"]) && isset($_SESSION["ab_price_change_ris"]) && !isset($_SESSION["step_two_price_ris"]["4"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_4", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["4"] = true;
            }
            if (isset($_SESSION["ab_price_change_ris"]) && isset($_SESSION["step_one_price_ris"]) && !isset($_SESSION["step_two_price_ris"]["4"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_4", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["4"] = true;
            }
            if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_one_WW_RIS"]) && !isset($_SESSION["step_two_WW_RIS"]["5"])) {
                $_SESSION["ab_search_progress_WW_RIS"]->track_event("2_image_a_WW_4", SYSTEM::get_device_type());
                $_SESSION["step_two_WW_RIS"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_old_img"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_three"]["5"])) {
                $_SESSION["ab_baselines_old_img"]->track_event("ris_a_step5", SYSTEM::get_device_type());
                $_SESSION["step_three"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_basic_img"]) && isset($_SESSION["step_one_ris_basic"]) && !isset($_SESSION["step_three_basic"]["5"])) {
                $_SESSION["ab_baselines_basic_img"]->track_event("ris_basic_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_three_basic"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_two_ris"]["5"])) {
                $_SESSION["ab_baselines_image"]->track_event("2_image_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_two_ris"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_one_ris_main"]) && !isset($_SESSION["step_two_ris_main"]["5"])) {
                $_SESSION["ab_baselines_image_main"]->track_event("2_image_a_step_5_main", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_main"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_one_ris_ad"]) && !isset($_SESSION["step_two_ris_ad"]["5"])) {
                $_SESSION["ab_baselines_image_ad"]->track_event("2_image_a_step_5_ad", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_ad"]["5"] = true;
            }
            break;

        case 'baselines_ris_5':
            if (isset($_SESSION["step_one_price_ris"]) && isset($_SESSION["ab_price_change_ris"]) && !isset($_SESSION["step_two_price_ris"]["5"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_5", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["5"] = true;
            }
            if (isset($_SESSION["ab_price_change_ris"]) && isset($_SESSION["step_one_price_ris"]) && !isset($_SESSION["step_two_price_ris"]["5"])) {
                $_SESSION["ab_price_change_ris"]->track_event("2_a_price_ris_5", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ris"]["5"] = true;
            }
            if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_one_WW_RIS"]) && !isset($_SESSION["step_two_WW_RIS"]["6"])) {
                $_SESSION["ab_search_progress_WW_RIS"]->track_event("2_image_a_WW_5", SYSTEM::get_device_type());
                $_SESSION["step_two_WW_RIS"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_old_img"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_three"]["6"])) {
                $_SESSION["ab_baselines_old_img"]->track_event("ris_a_step6", SYSTEM::get_device_type());
                $_SESSION["step_three"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_basic_img"]) && isset($_SESSION["step_one_ris_basic"]) && !isset($_SESSION["step_three_basic"]["6"])) {
                $_SESSION["ab_baselines_basic_img"]->track_event("ris_basic_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_three_basic"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_one_ris"]) && !isset($_SESSION["step_two_ris"]["6"])) {
                $_SESSION["ab_baselines_image"]->track_event("2_image_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_two_ris"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_one_ris_main"]) && !isset($_SESSION["step_two_ris_main"]["6"])) {
                $_SESSION["ab_baselines_image_main"]->track_event("2_image_a_step_6_main", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_main"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_one_ris_ad"]) && !isset($_SESSION["step_two_ris_ad"]["6"])) {
                $_SESSION["ab_baselines_image_ad"]->track_event("2_image_a_step_6_ad", SYSTEM::get_device_type());
                $_SESSION["step_two_ris_ad"]["6"] = true;
            }
            break;

        case 'baselines_ss_0':
            if (isset($_SESSION["ab_email_capture"]) && isset($_SESSION["step_one_capture"]) && !isset($_SESSION["step_two_capture"]["0"])) {
                $_SESSION["ab_email_capture"]->track_event("2_search_capture_a_0", SYSTEM::get_device_type());
                $_SESSION["step_two_capture"]["0"] = true;
            }
            if (isset($_SESSION["step_one_price_ss"]) && isset($_SESSION["ab_price_change_ss"]) && !isset($_SESSION["step_two_price_ss"]["0"])) {
                $_SESSION["ab_price_change_ss"]->track_event("2_a_price_ss_0", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ss"]["0"] = true;
            }
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["0"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step0_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["0"] = true;
            }
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_two_cr"]) && !isset($_SESSION["step_three_cr"]["0"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("3_name_a_step0_cr", SYSTEM::get_device_type());
                $_SESSION["step_three_cr"]["0"] = true;
            }
            if (isset($_SESSION["ab_baselines_home_name"]) && isset($_SESSION["step_two_name"]) && !isset($_SESSION["step_three_ss"]["0"])) {
                $_SESSION["ab_baselines_home_name"]->track_event("3_name_a_step0", SYSTEM::get_device_type());
                $_SESSION["step_three_ss"]["0"] = true;
            }
            if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_two_username"]) && !isset($_SESSION["step_three_username"]["0"])) {
                $_SESSION["ab_baselines_username"]->track_event("3_username_a_step_0", SYSTEM::get_device_type());
                $_SESSION["step_three_username"]["0"] = true;
            }
            if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_two_username_main"]) && !isset($_SESSION["step_three_username_main"]["0"])) {
                $_SESSION["ab_baselines_username_main"]->track_event("3_username_main_a_step_0", SYSTEM::get_device_type());
                $_SESSION["step_three_username_main"]["0"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_one_phone"]) && !isset($_SESSION["step_two_phone"]["0"])) {
                $_SESSION["ab_baselines_phone"]->track_event("2_phone_a_step_0", SYSTEM::get_device_type());
                $_SESSION["step_two_phone"]["0"] = true;
            }
            if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_one_email"]) && !isset($_SESSION["step_two_email"]["0"])) {
                $_SESSION["ab_baselines_email"]->track_event("2_email_a_step_0", SYSTEM::get_device_type());
                $_SESSION["step_two_email"]["0"] = true;
            }
            if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_one_email_main"]) && !isset($_SESSION["step_two_email_main"]["0"])) {
                $_SESSION["ab_baselines_email_main"]->track_event("2_email_main_a_step_0", SYSTEM::get_device_type());
                $_SESSION["step_two_email_main"]["0"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_one_phone_main"]) && !isset($_SESSION["step_two_phone_main"]["0"])) {
                $_SESSION["ab_baselines_phone_main"]->track_event("2_phone_main_a_step_0", SYSTEM::get_device_type());
                $_SESSION["step_two_phone_main"]["0"] = true;
            }
            if (isset($_SESSION["step_two_WW_SS"]) && isset($_SESSION["ab_search_progress_WW_SS"]) && !isset($_SESSION["step_three_WW_SS"])) {
                $_SESSION["ab_search_progress_WW_SS"]->track_event("3_a_WW_RIS_0", SYSTEM::get_device_type());
                $_SESSION["step_three_WW_SS"]["0"] = true;
            }
            break;

        case 'baselines_ss_1':
            if (isset($_SESSION["ab_email_capture"]) && isset($_SESSION["step_one_capture"]) && !isset($_SESSION["step_two_capture"]["1"])) {
                $_SESSION["ab_email_capture"]->track_event("2_search_capture_a_1", SYSTEM::get_device_type());
                $_SESSION["step_two_capture"]["1"] = true;
            }
            if (isset($_SESSION["step_one_price_ss"]) && isset($_SESSION["ab_price_change_ss"]) && !isset($_SESSION["step_two_price_ss"]["1"])) {
                $_SESSION["ab_price_change_ss"]->track_event("2_a_price_ss_1", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ss"]["1"] = true;
            }
            if (isset($_SESSION["step_two_WW_SS"]) && isset($_SESSION["ab_search_progress_WW_SS"]) && !isset($_SESSION["step_three_WW_SS"]["1"])) {
                $_SESSION["ab_search_progress_WW_SS"]->track_event("3_a_WW_RIS_1", SYSTEM::get_device_type());
                $_SESSION["step_three_WW_SS"]["1"] = true;
            }
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["1"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step1_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["1"] = true;
            }
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_two_cr"]) && !isset($_SESSION["step_three_cr"]["1"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("3_name_a_step1_cr", SYSTEM::get_device_type());
                $_SESSION["step_three_cr"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_home_name"]) && isset($_SESSION["step_two_name"]) && !isset($_SESSION["step_three_ss"]["1"])) {
                $_SESSION["ab_baselines_home_name"]->track_event("3_name_a_step1", SYSTEM::get_device_type());
                $_SESSION["step_three_ss"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_two_username"]) && !isset($_SESSION["step_three_username"]["1"])) {
                $_SESSION["ab_baselines_username"]->track_event("3_username_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_three_username"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_two_username_main"]) && !isset($_SESSION["step_three_username_main"]["1"])) {
                $_SESSION["ab_baselines_username_main"]->track_event("3_username_main_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_three_username_main"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_one_phone"]) && !isset($_SESSION["step_two_phone"]["1"])) {
                $_SESSION["ab_baselines_phone"]->track_event("2_phone_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_two_phone"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_one_email"]) && !isset($_SESSION["step_two_email"]["1"])) {
                $_SESSION["ab_baselines_email"]->track_event("2_email_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_two_email"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_one_email_main"]) && !isset($_SESSION["step_two_email_main"]["1"])) {
                $_SESSION["ab_baselines_email_main"]->track_event("2_email_main_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_two_email_main"]["1"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_one_phone_main"]) && !isset($_SESSION["step_two_phone_main"]["1"])) {
                $_SESSION["ab_baselines_phone_main"]->track_event("2_phone_main_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_two_phone_main"]["1"] = true;
            }

            break;

        case 'baselines_ss_2':
            if (isset($_SESSION["ab_email_capture"]) && isset($_SESSION["step_one_capture"]) && !isset($_SESSION["step_two_capture"]["2"])) {
                $_SESSION["ab_email_capture"]->track_event("2_search_capture_a_2", SYSTEM::get_device_type());
                $_SESSION["step_two_capture"]["2"] = true;
            }
            if (isset($_SESSION["step_one_price_ss"]) && isset($_SESSION["ab_price_change_ss"]) && !isset($_SESSION["step_two_price_ss"]["2"])) {
                $_SESSION["ab_price_change_ss"]->track_event("2_a_price_ss_2", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ss"]["2"] = true;
            }
            if (isset($_SESSION["step_two_WW_SS"]) && isset($_SESSION["ab_search_progress_WW_SS"]) && !isset($_SESSION["step_three_WW_SS"]["2"])) {
                $_SESSION["ab_search_progress_WW_SS"]->track_event("3_a_WW_RIS_2", SYSTEM::get_device_type());
                $_SESSION["step_three_WW_SS"]["2"] = true;
            }
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["2"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step2_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["2"] = true;
            }
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_two_cr"]) && !isset($_SESSION["step_three_cr"]["2"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("3_name_a_step2_cr", SYSTEM::get_device_type());
                $_SESSION["step_three_cr"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_home_name"]) && isset($_SESSION["step_two_name"]) && !isset($_SESSION["step_three_ss"]["2"])) {
                $_SESSION["ab_baselines_home_name"]->track_event("3_name_a_step2", SYSTEM::get_device_type());
                $_SESSION["step_three_ss"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_two_username"]) && !isset($_SESSION["step_three_username"]["2"])) {
                $_SESSION["ab_baselines_username"]->track_event("3_username_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_three_username"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_two_username_main"]) && !isset($_SESSION["step_three_username_main"]["2"])) {
                $_SESSION["ab_baselines_username_main"]->track_event("3_username_main_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_three_username_main"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_one_phone"]) && !isset($_SESSION["step_two_phone"]["2"])) {
                $_SESSION["ab_baselines_phone"]->track_event("2_phone_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_two_phone"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_one_email"]) && !isset($_SESSION["step_two_email"]["2"])) {
                $_SESSION["ab_baselines_email"]->track_event("2_email_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_two_email"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_one_email_main"]) && !isset($_SESSION["step_two_email_main"]["2"])) {
                $_SESSION["ab_baselines_email_main"]->track_event("2_email_main_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_two_email_main"]["2"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_one_phone_main"]) && !isset($_SESSION["step_two_phone_main"]["2"])) {
                $_SESSION["ab_baselines_phone_main"]->track_event("2_phone_main_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_two_phone_main"]["2"] = true;
            }

            break;

        case 'baselines_ss_3':
            if (isset($_SESSION["ab_email_capture"]) && isset($_SESSION["step_one_capture"]) && !isset($_SESSION["step_two_capture"]["3"])) {
                $_SESSION["ab_email_capture"]->track_event("2_search_capture_a_3", SYSTEM::get_device_type());
                $_SESSION["step_two_capture"]["3"] = true;
            }
            if (isset($_SESSION["step_one_price_ss"]) && isset($_SESSION["ab_price_change_ss"]) && !isset($_SESSION["step_two_price_ss"]["3"])) {
                $_SESSION["ab_price_change_ss"]->track_event("2_a_price_ss_3", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ss"]["3"] = true;
            }
            if (isset($_SESSION["step_two_WW_SS"]) && isset($_SESSION["ab_search_progress_WW_SS"]) && !isset($_SESSION["step_three_WW_SS"]["3"])) {
                $_SESSION["ab_search_progress_WW_SS"]->track_event("3_a_WW_RIS_3", SYSTEM::get_device_type());
                $_SESSION["step_three_WW_SS"]["3"] = true;
            }
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["3"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step3_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["3"] = true;
            }
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_two_cr"]) && !isset($_SESSION["step_three_cr"]["3"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("3_name_a_step3_cr", SYSTEM::get_device_type());
                $_SESSION["step_three_cr"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_home_name"]) && isset($_SESSION["step_two_name"]) && !isset($_SESSION["step_three_ss"]["3"])) {
                $_SESSION["ab_baselines_home_name"]->track_event("3_name_a_step3", SYSTEM::get_device_type());
                $_SESSION["step_three_ss"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_two_username"]) && !isset($_SESSION["step_three_username"]["3"])) {
                $_SESSION["ab_baselines_username"]->track_event("3_username_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_three_username"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_two_username_main"]) && !isset($_SESSION["step_three_username_main"]["3"])) {
                $_SESSION["ab_baselines_username_main"]->track_event("3_username_main_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_three_username_main"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_one_phone"]) && !isset($_SESSION["step_two_phone"]["3"])) {
                $_SESSION["ab_baselines_phone"]->track_event("2_phone_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_two_phone"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_one_email"]) && !isset($_SESSION["step_two_email"]["3"])) {
                $_SESSION["ab_baselines_email"]->track_event("2_email_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_two_email"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_one_email_main"]) && !isset($_SESSION["step_two_email_main"]["3"])) {
                $_SESSION["ab_baselines_email_main"]->track_event("2_email_main_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_two_email_main"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_one_phone_main"]) && !isset($_SESSION["step_two_phone_main"]["3"])) {
                $_SESSION["ab_baselines_phone_main"]->track_event("2_phone_main_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_two_phone_main"]["3"] = true;
            }
            break;

        case 'baselines_ss_4':
            if (isset($_SESSION["ab_email_capture"]) && isset($_SESSION["step_one_capture"]) && !isset($_SESSION["step_two_capture"]["4"])) {
                $_SESSION["ab_email_capture"]->track_event("2_search_capture_a_4", SYSTEM::get_device_type());
                $_SESSION["step_two_capture"]["4"] = true;
            }
            if (isset($_SESSION["step_one_price_ss"]) && isset($_SESSION["ab_price_change_ss"]) && !isset($_SESSION["step_two_price_ss"]["4"])) {
                $_SESSION["ab_price_change_ss"]->track_event("2_a_price_ss_4", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ss"]["4"] = true;
            }
            if (isset($_SESSION["step_two_WW_SS"]) && isset($_SESSION["ab_search_progress_WW_SS"]) && !isset($_SESSION["step_three_WW_SS"]["4"])) {
                $_SESSION["ab_search_progress_WW_SS"]->track_event("3_a_WW_RIS_4", SYSTEM::get_device_type());
                $_SESSION["step_three_WW_SS"]["4"] = true;
            }
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["4"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step4_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["4"] = true;
            }
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_two_cr"]) && !isset($_SESSION["step_three_cr"]["4"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("3_name_a_step4_cr", SYSTEM::get_device_type());
                $_SESSION["step_three_cr"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_home_name"]) && isset($_SESSION["step_two_name"]) && !isset($_SESSION["step_three_ss"]["4"])) {
                $_SESSION["ab_baselines_home_name"]->track_event("3_name_a_step4", SYSTEM::get_device_type());
                $_SESSION["step_three_ss"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_two_username"]) && !isset($_SESSION["step_three_username"]["4"])) {
                $_SESSION["ab_baselines_username"]->track_event("3_username_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_three_username"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_two_username_main"]) && !isset($_SESSION["step_three_username_main"]["4"])) {
                $_SESSION["ab_baselines_username_main"]->track_event("3_username_main_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_three_username_main"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_one_phone"]) && !isset($_SESSION["step_two_phone"]["4"])) {
                $_SESSION["ab_baselines_phone"]->track_event("2_phone_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_two_phone"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_one_email"]) && !isset($_SESSION["step_two_email"]["4"])) {
                $_SESSION["ab_baselines_email"]->track_event("2_email_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_two_email"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_one_email_main"]) && !isset($_SESSION["step_two_email_main"]["4"])) {
                $_SESSION["ab_baselines_email_main"]->track_event("2_email_main_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_two_email_main"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_one_phone_main"]) && !isset($_SESSION["step_two_phone_main"]["4"])) {
                $_SESSION["ab_baselines_phone_main"]->track_event("2_phone_main_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_two_phone_main"]["4"] = true;
            }
            break;

        case 'baselines_ss_5':
            if (isset($_SESSION["ab_email_capture"]) && isset($_SESSION["step_one_capture"]) && !isset($_SESSION["step_two_capture"]["5"])) {
                $_SESSION["ab_email_capture"]->track_event("2_search_capture_a_5", SYSTEM::get_device_type());
                $_SESSION["step_two_capture"]["5"] = true;
            }
            if (isset($_SESSION["step_one_price_ss"]) && isset($_SESSION["ab_price_change_ss"]) && !isset($_SESSION["step_two_price_ss"]["5"])) {
                $_SESSION["ab_price_change_ss"]->track_event("2_a_price_ss_5", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ss"]["5"] = true;
            }
            if (isset($_SESSION["step_two_WW_SS"]) && isset($_SESSION["ab_search_progress_WW_SS"]) && !isset($_SESSION["step_three_WW_SS"]["5"])) {
                $_SESSION["ab_search_progress_WW_SS"]->track_event("3_a_WW_RIS_5", SYSTEM::get_device_type());
                $_SESSION["step_three_WW_SS"]["5"] = true;
            }
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["5"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step5_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["5"] = true;
            }
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_two_cr"]) && !isset($_SESSION["step_three_cr"]["5"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("3_name_a_step5_cr", SYSTEM::get_device_type());
                $_SESSION["step_three_cr"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_home_name"]) && isset($_SESSION["step_two_name"]) && !isset($_SESSION["step_three_ss"]["5"])) {
                $_SESSION["ab_baselines_home_name"]->track_event("3_name_a_step5", SYSTEM::get_device_type());
                $_SESSION["step_three_ss"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_two_username"]) && !isset($_SESSION["step_three_username"]["5"])) {
                $_SESSION["ab_baselines_username"]->track_event("3_username_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_three_username"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_two_username_main"]) && !isset($_SESSION["step_three_username_main"]["5"])) {
                $_SESSION["ab_baselines_username_main"]->track_event("3_username_main_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_three_username_main"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_one_phone"]) && !isset($_SESSION["step_two_phone"]["5"])) {
                $_SESSION["ab_baselines_phone"]->track_event("2_phone_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_two_phone"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_one_email"]) && !isset($_SESSION["step_two_email"]["5"])) {
                $_SESSION["ab_baselines_email"]->track_event("2_email_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_two_email"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_one_email_main"]) && !isset($_SESSION["step_two_email_main"]["5"])) {
                $_SESSION["ab_baselines_email_main"]->track_event("2_email_main_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_two_email_main"]["5"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_one_phone_main"]) && !isset($_SESSION["step_two_phone_main"]["5"])) {
                $_SESSION["ab_baselines_phone_main"]->track_event("2_phone_main_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_two_phone_main"]["5"] = true;
            }
            break;

        case 'progress_ss_idi':
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["6"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step6_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["6"] = true;
            }
            break;

        case 'baselines_ss_report':
            if (isset($_SESSION["ab_email_capture"]) && isset($_SESSION["step_one_capture"]) && !isset($_SESSION["step_two_capture"]["6"])) {
                $_SESSION["ab_email_capture"]->track_event("2_search_capture_a_6", SYSTEM::get_device_type());
                $_SESSION["step_two_capture"]["6"] = true;
            }
            if (isset($_SESSION["step_one_price_ss"]) && isset($_SESSION["ab_price_change_ss"]) && !isset($_SESSION["step_two_price_ss"]["6"])) {
                $_SESSION["ab_price_change_ss"]->track_event("2_a_price_ss_6", SYSTEM::get_device_type());
                $_SESSION["step_two_price_ss"]["6"] = true;
            }
            if (isset($_SESSION["step_two_WW_SS"]) && isset($_SESSION["ab_search_progress_WW_SS"]) && !isset($_SESSION["step_three_WW_SS"]["6"])) {
                $_SESSION["ab_search_progress_WW_SS"]->track_event("3_a_WW_RIS_6", SYSTEM::get_device_type());
                $_SESSION["step_three_WW_SS"]["6"] = true;
            }
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["8"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step7_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["8"] = true;
            }
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_two_cr"]) && !isset($_SESSION["step_three_cr"]["6"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("3_name_a_step6_cr", SYSTEM::get_device_type());
                $_SESSION["step_three_cr"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_home_name"]) && isset($_SESSION["step_two_name"]) && !isset($_SESSION["step_three_ss"]["6"])) {
                $_SESSION["ab_baselines_home_name"]->track_event("3_name_a_step6", SYSTEM::get_device_type());
                $_SESSION["step_three_ss"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_two_username"]) && !isset($_SESSION["step_three_username"]["6"])) {
                $_SESSION["ab_baselines_username"]->track_event("3_username_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_three_username"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_two_username_main"]) && !isset($_SESSION["step_three_username_main"]["6"])) {
                $_SESSION["ab_baselines_username_main"]->track_event("3_username_main_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_three_username_main"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_one_phone"]) && !isset($_SESSION["step_two_phone"]["6"])) {
                $_SESSION["ab_baselines_phone"]->track_event("2_phone_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_two_phone"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_one_email"]) && !isset($_SESSION["step_two_email"]["6"])) {
                $_SESSION["ab_baselines_email"]->track_event("2_email_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_two_email"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_one_email_main"]) && !isset($_SESSION["step_two_email_main"]["6"])) {
                $_SESSION["ab_baselines_email_main"]->track_event("2_email_main_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_two_email_main"]["6"] = true;
            }
            if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_one_phone_main"]) && !isset($_SESSION["step_two_phone_main"]["6"])) {
                $_SESSION["ab_baselines_phone_main"]->track_event("2_phone_main_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_two_phone_main"]["6"] = true;
            }

            break;

        case 'baselines_ras_1':
            if (isset($_SESSION["ab_baselines_ras"]) && isset($_SESSION["step_zero_ras"]) && !isset($_SESSION["step_one_ras"])) {
                $_SESSION["ab_baselines_ras"]->track_event("1_ras_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_one_ras"] = true;
            }
            if (isset($_SESSION["ab_baselines_ras_main"]) && isset($_SESSION["step_zero_ras_main"]) && !isset($_SESSION["step_one_ras_main"])) {
                $_SESSION["ab_baselines_ras_main"]->track_event("1_ras_main_a_step_1", SYSTEM::get_device_type());
                $_SESSION["step_one_ras_main"] = true;
            }
            break;

        case 'baselines_ras_2':
            if (isset($_SESSION["ab_baselines_ras"]) && isset($_SESSION["step_one_ras"]) && !isset($_SESSION["step_two_ras"])) {
                $_SESSION["ab_baselines_ras"]->track_event("1_ras_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_two_ras"] = true;
            }
            if (isset($_SESSION["ab_baselines_ras_main"]) && isset($_SESSION["step_one_ras_main"]) && !isset($_SESSION["step_two_ras_main"])) {
                $_SESSION["ab_baselines_ras_main"]->track_event("1_ras_main_a_step_2", SYSTEM::get_device_type());
                $_SESSION["step_two_ras_main"] = true;
            }
            break;
        case 'baselines_ras_3':
            if (isset($_SESSION["ab_baselines_ras"]) && isset($_SESSION["step_two_ras"]) && !isset($_SESSION["step_three_ras"])) {
                $_SESSION["ab_baselines_ras"]->track_event("1_ras_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_three_ras"]["3"] = true;
            }
            if (isset($_SESSION["ab_baselines_ras_main"]) && isset($_SESSION["step_two_ras_main"]) && !isset($_SESSION["step_three_ras_main"])) {
                $_SESSION["ab_baselines_ras_main"]->track_event("1_ras_main_a_step_3", SYSTEM::get_device_type());
                $_SESSION["step_three_ras_main"] = true;
            }
            break;

        case 'baselines_ras_4':
            if (isset($_SESSION["ab_baselines_ras"]) && isset($_SESSION["step_three_ras"]) && !isset($_SESSION["step_four_ras"])) {
                $_SESSION["ab_baselines_ras"]->track_event("1_ras_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_four_ras"]["4"] = true;
            }
            if (isset($_SESSION["ab_baselines_ras_main"]) && isset($_SESSION["step_three_ras_main"]) && !isset($_SESSION["step_four_ras_main"])) {
                $_SESSION["ab_baselines_ras_main"]->track_event("1_ras_main_a_step_4", SYSTEM::get_device_type());
                $_SESSION["step_four_ras_main"] = true;
            }
            break;

        case 'baselines_ras_5':
            if (isset($_SESSION["ab_baselines_ras"]) && isset($_SESSION["step_four_ras"]) && !isset($_SESSION["step_five_ras"])) {
                $_SESSION["ab_baselines_ras"]->track_event("1_ras_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_five_ras"] = true;
            }
            if (isset($_SESSION["ab_baselines_ras_main"]) && isset($_SESSION["step_four_ras_main"]) && !isset($_SESSION["step_five_ras_main"])) {
                $_SESSION["ab_baselines_ras_main"]->track_event("1_ras_main_a_step_5", SYSTEM::get_device_type());
                $_SESSION["step_five_ras_main"] = true;
            }
            break;

        case 'baselines_ras_6':
            if (isset($_SESSION["ab_baselines_ras"]) && isset($_SESSION["step_five_ras"]) && !isset($_SESSION["step_six_ras"])) {
                $_SESSION["ab_baselines_ras"]->track_event("1_ras_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_six_ras"] = true;
            }
            if (isset($_SESSION["ab_baselines_ras_main"]) && isset($_SESSION["step_five_ras_main"]) && !isset($_SESSION["step_six_ras_main"])) {
                $_SESSION["ab_baselines_ras_main"]->track_event("1_ras_main_a_step_6", SYSTEM::get_device_type());
                $_SESSION["step_six_ras_main"] = true;
            }
            break;

        case 'better_results_search':
            $token = md5(uniqid());
            $_SESSION["better_results_search"][$token] = $input_post;
            $ajax_status["status"] = true;
            $ajax_status["token"] = $token;
            break;

        case 'search_filtering':
            $token = md5(uniqid());
            $_SESSION["search_filtering"][$token] = $input_post;
            $ajax_status["status"] = true;
            $ajax_status["token"] = $token;
            break;

        case "close_phil_walkthrough":
            if ($user_id) {
                $ajax_status["status"] = true;
                USER::close_phil_walkthrough($user_id);
            } else {
                $ajax_status["status"] = false;
            }
            break;

        case "search_verification_required":
            $ajax_status = [
                "status" => ! empty($social_search_show_recaptcha),
                "content" => ! empty($social_search_show_recaptcha) ? file_get_contents($current_template_path . "content/recaptcha_modal.php") : "",
            ];
            break;

        case "ris_status":
            if (! empty($input_post["id_list"]) && is_array($input_post["id_list"])) {
                $ajax_status = [
                    "status" => true,
                    "results" => Search::ris_status($user_id, $input_post["id_list"]),
                ];
            } else {
                $ajax_status["results"] = [];
            }
            break;

        case "ris_report_ready":
            if (! empty($input_post["id"])) {
                $ajax_status = [
                    "status" => Search::ris_report_status($user_id, $input_post["id"]),
                ];
            } else {
                $ajax_status["status"] = false;
            }
            break;

        case "address_verification":
            $ajax_status = [
                "status" => true,
                "old_address" => "",
                "new_address" => "",
                "billing_address1" => "",
                "billing_city" => "",
                "billing_state" => "",
                "billing_postal_code" => "",
            ];

            $fields = [ "billing_address1", "billing_address2", "billing_city", "billing_state", "billing_postal_code", "billing_country" ];
            $address = SYSTEM::array_get_values_for_keys($_POST, $fields, "");

            if ("US" == $address["billing_country"]) {
                unset($address["billing_country"]);
                $stream = $client->request("https://maps.googleapis.com/maps/api/geocode/json?&address=" . implode(" ", $address) . "&key=AIzaSyBz8VgaMxVEgJiVtZvNxcI319THlvbizPg");
                $address_data = ( $stream["status"] ) ? @json_decode($stream["response"], true) : false;

                if (! empty($address_data["results"][0]["address_components"])) {
                    $compontents = [];
                    foreach ($address_data["results"][0]["address_components"] as $compontent) {
                        $key = array_shift($compontent["types"]);
                        $compontents[ $key ] = $compontent["short_name"];
                    }
                    $new_address = "{$compontents["street_number"]} {$compontents["route"]}, {$compontents["locality"]}, {$compontents["administrative_area_level_1"]} {$compontents["postal_code"]}" . ( ! empty($compontents["postal_code_suffix"]) ? "-{$compontents["postal_code_suffix"]}" : "" );
                    $old_address = preg_replace("/, ,/", ",", "{$address["billing_address1"]}, {$address["billing_address2"]}, {$address["billing_city"]}, {$address["billing_state"]} {$address["billing_postal_code"]}");

                    $check_address = strtolower(preg_replace("/[^a-z0-9 \-]/i", "", $new_address));
                    $old_check_address = strtolower(preg_replace("/[ ]+/", " ", preg_replace("/[^a-z0-9 \-]/i", " ", $old_address)));

                    if ($check_address != $old_check_address) {
                        $ajax_status = [
                            "status" => false,
                            "old_address" => $old_address,
                            "new_address" => $new_address,
                            "suggested" => [
                                "billing_address1" => "{$compontents["street_number"]} {$compontents["route"]}",
                                "billing_city" => $compontents["locality"],
                                "billing_state" => $compontents["administrative_area_level_1"],
                                "billing_postal_code" => $compontents["postal_code"] . ( ! empty($compontents["postal_code_suffix"]) ? "-{$compontents["postal_code_suffix"]}" : "" ),
                            ]
                        ];
                    }
                }
            }
            break;

        case 'apply_coupon':
            if ($coupon_data = $stripe->get_coupon_code($post_data["coupon"])) {
                $ajax_status["status"] = true;
                $_SESSION["coupon_data"] = $coupon_data;
            } else {
                $ajax_status["status"] = false;
            }

            break;

        case 'remove_coupon':
            unset($_SESSION["coupon_data"]);
            $ajax_status["status"] = true;

            break;

        case 'get_cities':
            $ajax_status["data"] = SCF::get_city_list_for_state($post_data["state"]);
            $ajax_status["status"] = true;
            break;

        case 'phone_check':
            $phone_number = preg_replace("/[^0-9]/", "", $post_data["phone_number"]);
            $ajax_status["status"] = WhitePages::phone_scrape_check($phone_number);
            break;

        case 'igp_status':
            $ajax_status["status"] = ! empty($_SESSION["image_token"][ $token ]);
            if ($ajax_status["status"]) {
                // Randomize image counts for guest users
                //$image_status = Search::get_igp_image_search_status( $_SESSION["image_token"][ $token ]["pending_image_id"] ); // Disabled this to randomize the numbers
                $image_status = [ "matches_exact" => mt_rand(10, 30), "matches_similar" => mt_rand(10, 30), "batch" => 1, "phash" => md5(time()) ];
                $_SESSION["image_token"][ $token ]["matches_exact"] = $ajax_status["exact_matches"] = $image_status["matches_exact"];
                $_SESSION["image_token"][ $token ]["matches_similar"] = $ajax_status["similar_matches"] = $image_status["matches_similar"];
                $ajax_status["batch"] = ! empty($image_status["batch"]);
                $ajax_status["hash"] = ! empty($image_status["phash"]);
            }
            break;

        case 'nmi_update':
            $error_fields = $nmi->validate_initial_form_data($post_data);
            if (! empty($error_messages)) {
                $ajax_status["status"] = false;
                $ajax_status["message"] = array_shift($error_messages);
                //if( ! empty( $GLOBALS["error_messages"] ) ) Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "NMI::" . array_shift( $error_messages ), "error" );


                unset($GLOBALS["error_messages"]);
            } else {
                $plan_id = SYSTEM::request("id", 0);
                $plan_data = Membership::get_user_plan($user_id, $plan_id);

                if (empty($plan_data)) {
                    $ajax_status["status"] = false;
                    $ajax_status["message"] = "An error Occured. Please contact site administrator";
                } else {
                    $data = [
                        "firstname" => ! empty($user_data["first_name"]) ? $user_data["first_name"] : $post_data["billing_firstname"],
                        "lastname" => ! empty($user_data["last_name"]) ? $user_data["last_name"] : $post_data["billing_lastname"],
                        "address1" => $post_data["billing_address1"],
                        "address2" => $post_data["billing_address2"],
                        "city" => $post_data["billing_city"],
                        "state" => $post_data["billing_state"],
                        "zip" => $post_data["billing_postal_code"],
                        "country" => $post_data["billing_country"],
                        "email" => ! empty($user_data["email"]) ? $user_data["email"] : $post_data["email"],
                        "plan" => $plan_data
                    ];

                    $nmi->set_billing($data);
                    $ajax_status = $nmi->update_customer_vault($data, BASE_URL . "dashboard.html?section=update&id={$plan_id}");
                    $ajax_status["status"] = true;
                }
            }
            break;

        case 'membership_signup':
            $datauser = ! empty($post_data["datauser"]) ? $post_data["datauser"] : 0;


            if ($post_data["pg_type"] != "nmi" || ( $post_data["pg_type"] == "nmi" && empty($datauser) )) {
                $form_validation = [
                    [
                        "name" => "billing_firstname",
                        "value" => $post_data["billing_firstname"],
                        "caption" => "First Name",
                        "validation" => "required|max_length[100]",
                    ],
                    [
                        "name" => "billing_lastname",
                        "value" => $post_data["billing_lastname"],
                        "caption" => "Last Name",
                        "validation" => "required|max_length[100]",
                    ],
                    [
                        "name" => "email",
                        "value" => $post_data["email"],
                        "caption" => "E-Mail",
                        "validation" => "required|email|max_length[100]",
                    ],
                    [
                        "name" => "password",
                        "value" => $post_data["password"],
                        "caption" => "Password",
                        "validation" => "required|max_length[100]",
                    ],
                ];


                if (! empty($post_data["billing_firstname"])) {
                    Behavior::add_user_data("firstname", $post_data["billing_firstname"]);
                }
                if (! empty($post_data["billing_lastname"])) {
                    Behavior::add_user_data("lastname", $post_data["billing_lastname"]);
                }
                if (! empty($post_data["email"])) {
                    Behavior::add_user_data("email", $post_data["email"]);
                }

                if (! empty($post_data["show_phone_number"]) && $post_data["show_phone_number"] == 1) {
                    $form_validation = array_merge($form_validation, [
                        [
                            "name" => "billing_phone",
                            "value" => preg_replace("/[^0-9]/", "", $post_data["billing_phone"]),
                            "caption" => "Phone Number",
                            "validation" => "required|phone|max_length[100]",
                        ],
                    ]);
                }

                $error_fields = SYSTEM::form_validate($form_validation);
                if (! $user_id && $user_details = User::get_by_email($post_data["email"])) {
                    /* AB Test: Existing User Login CSI-3859 : START*/


                    $error_messages[] =  "Login User";
                    $_SESSION["has_active_plans"] =  USER::get_active_plan_ids($user_details["id"]);
                    $ajax_status["active_plans"] = array_key_exists($_SESSION["membership_type"], $_SESSION["has_active_plans"]);
                    /* AB Test: Existing User Login CSI-3859 : START*/
                }



                if (! $user_id && User::is_email_blocked($post_data["email"])) {
                    $error_messages[] = "E-Mail Address blocked.";
                }

                if ($post_data["email"] != $post_data["email_confirm"]) {
                    $error_messages[] = "E-Mail Address does not match.";
                }

                if (! empty($post_data["show_phone_number"]) && $post_data["show_phone_number"] == 1 && in_array("billing_phone", $error_fields)) {
                    array_unshift($error_messages, "Phone Number required and must be atleast 10 digits.");
                }

                if ($input_post->email_validation != 1 && ! DEBUG) {
                    $email_validation = new ZeroBounceAPI(ZEROBOUNCE_API_KEY);
                    $status = $email_validation->send_request($post_data["email"]);
                    if ($status != "valid" && $status != "invalid") {
                        $_SESSION["email_verification_status"] = $status;
                    }
                    if ($status == "invalid") {
                        unset($_SESSION["email_verification_status"]);
                        $error_messages[] = "Valid Email Required. We will NEVER share or sell your email. Email is required to access your account, change your password and receive billing information.";
                    }
                }
            }

            if (! empty($error_messages)) {
                $error_message = array_shift($error_messages);
                Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, $error_message, ["errors", "membership signup failed", $error_message ]);

                $ajax_status["status"] = false;
                $ajax_status["message"] = $error_message;
                unset($GLOBALS["error_messages"]);
            } else {
                if ($post_data["pg_type"] == "nmi") {
                    if (! empty($_SESSION["tokens"][ $token ])) {
                        $membership = $_SESSION["tokens"][ $token ]["membership"];

                        //Assign combine monlthy membership plans
                        $monthly_membership_plans_combine = array( PLAN_UNLIMITED_MONTHLY_1, PLAN_UNLIMITED_MONTHLY_3, PLAN_UNLIMITED_MONTHLY_6 );
                        if (! empty($post_data["plan_id"]) && in_array($post_data["plan_id"], $monthly_membership_plans_combine)) {
                            $membership = $_SESSION["tokens"][$token]["membership"] = Membership::get($post_data["plan_id"]);
                            $_SESSION["tokens"][$token]["membership_id"] = $membership["id"];
                        }

                        $is_subscription = ($membership["recurring_amount"] > 0);

                        if ($is_subscription) {
                            switch ($membership["start_recurring_period"]) {
                                case "days":
                                        $start_subscription = strtotime("+{$membership["start_recurring_frequency"]} days");
                                    break;
                                case "mont":
                                        $start_subscription = strtotime("+{$membership["start_recurring_frequency"]} month");
                                    break;
                                case "year":
                                        $start_subscription = strtotime("+{$membership["start_recurring_frequency"]} year");
                                    break;
                            }

                            $start_subscription = date("Ymd", $start_subscription);
                        }

                        $data = [
                            "firstname" => ! empty($user_data["first_name"]) ? $user_data["first_name"] : $post_data["billing_firstname"],
                            "lastname" => ! empty($user_data["last_name"]) ? $user_data["last_name"] : $post_data["billing_lastname"],
                            "address1" => $post_data["billing_address1"],
                            "address2" => $post_data["billing_address2"],
                            "city" => $post_data["billing_city"],
                            "state" => $post_data["billing_state"],
                            "zip" => $post_data["billing_postal_code"],
                            "country" => $post_data["billing_country"],
                            "email" => ! empty($user_data["email"]) ? $user_data["email"] : $post_data["email"],
                            "is_subscription" => $is_subscription,
                            "start_subscription" => $start_subscription,
                            "membership" => $membership,
                        ];

                        $_SESSION["tokens"][$token]["nmi_data"] = [
                            "billing_firstname" => $post_data["billing_firstname"],
                            "billing_lastname" => $post_data["billing_lastname"],
                            "billing_address1" => $post_data["billing_address1"],
                            "billing_address2" => $post_data["billing_address2"],
                            "billing_city" => $post_data["billing_city"],
                            "billing_state" => $post_data["billing_state"],
                            "billing_postal_code" => $post_data["billing_postal_code"],
                            "billing_country" => $post_data["billing_country"],
                            "email" => $post_data["email"],
                            "password" => $post_data["password"],
                            "tos_agree" => $post_data["tos_agree"],
                            "card_name" => $post_data["card_name"],
                            "report_agreement" => $post_data["report_agreement"],
                        ];

                        $nmi->set_billing($data);
                        $ajax_status = $nmi->do_sale($data, BASE_URL . "membership-levels/?cmd=nmitr&token={$token}");

                        $_SESSION["tokens"][$token]["nmi_auth_response"] = $ajax_status["response"];
                        unset($ajax_status["response"]);
                    }
                }
                if (in_array("4", $post_data["signup_purpose"])) {
                    $post_data["signup_purpose"][0] = $input_post->signup_purpose_text;
                }
                $_SESSION["sign_up_purpose"] = $post_data["signup_purpose"];
                $ajax_status["status"] = true;
            }
            break;

        case "token_update":
            $_SESSION["tokens"][ $token ]["post_data"] = $_POST;
            $ajax_status["status"] = true;
            break;

        case 'my_stat':
            // User search tracking alert popup disable request.
            if (! empty($user_id) && ! empty($post_data['method'])) {
                user::set_meta($user_id, 'disable_alert_being_tracked', ( $post_data['method'] == 'true' ) ? 1 : 0);
            }

            break;

        case 'subscription_delete_alert':
            // whenever user or super admin trying to delete a unlimited subscription, they will get a popup alert asking to downgrade account

            $id = SYSTEM::get_request_value("id", 0);
            $user_info = SYSTEM::get_request_value("user", 0);
            $canceltype = SYSTEM::get_request_value("canceltype", "");

            $user_info = ( ! empty($user_info) && 255 == $user_data["user_level"] ) ? $user_info : $user_id;
            $cancel_plan = Membership::get_user_plan($user_info, $id);

            $switch_plan_allowed = false;
            $new_membership = [];
            $user_manager_id = SYSTEM::get_request_value("user_manager", 0);
            $show_wrapper = true;

            if (! empty($cancel_plan) && ! empty($cancel_plan["active"])) {
                $cancel_plan_membership = Membership::get($cancel_plan["membership_id"]);
                $_is_still_in_trial = Membership::is_plan_still_on_trial($cancel_plan, $cancel_plan_membership);
                $switchable_plans = Membership::get_switchable_data_for_plan($cancel_plan);
                if ($switch_plan_allowed = $switchable_plans["switchable"]) {
                    $new_membership = $switchable_plans["switchable_plan"];
                }
            }

            if ($user_data["classic_template_forced"] == 1) {
                include "{$default_template_path}content/cancel-subscription-modal.php";
            } else {
                include "{$current_template_path}content/modals/cancel-subscription-modal.php";
            }

            die;

            break;

        case 'paymentcard':
            global $stripe;

            $id = SYSTEM::get_request_value("id", "");
            $type = SYSTEM::get_request_value("type", "");

            $customer_id = User::get_customer_id_by_user_id($user_id);
            if (! empty($customer_id)) {
                $user_cards = User::get_user_payment_cards($user_id);
                if (! empty($user_cards['card_list'])) {
                    foreach ($user_cards['card_list'] as $card) {
                        if ($card->systemid == $id) {
                            $id = $card->id;
                            break;
                        }
                    }
                    $return['status'] = 'err';
                    if ('del' == $type  && count($user_cards['card_list']) > 1) {
                        $return = $stripe->delete_card($customer_id, $id);
                    } elseif ('edit' == $type && count($user_cards['card_list']) >= 1) {
                        $current_date = date("Y-m");
                        $expire_date = date("Y-m", strtotime("{$post_data["card_expiry_year"]}-{$post_data["card_expiry_month"]}"));
                        if (! empty($post_data["card_name"]) && $current_date <= $expire_date) {
                            $data = [
                                "card_name" =>  $post_data["card_name"],
                                "card_expiry_month" =>  $post_data["card_expiry_month"],
                                "card_expiry_year" =>  $post_data["card_expiry_year"]
                            ];

                            $return = $stripe->update_card($customer_id, $id, $data);
                        } else {
                            $return['status'] = 2;
                        }
                    }

                    if ($return['status'] == 1) {
                        $ajax_status["status"] = 1;
                    } elseif ($return['status'] == 'err') {
                        $ajax_status["status"] = 'err';
                    } else {
                        $ajax_status["status"] = 2;
                    }
                } else {
                    $ajax_status["status"] = 'err';
                }
            } else {
                $ajax_status["status"] = 'err';
            }

            break;

        case 'filter_city':
            //Area Code City Filter
            $country = $post_data['country'];
            $key =  $post_data['key'];
            $filter_cities = AreaCode::key_cities($key, $country);
            echo "<div class='results_cnt'>";
            foreach ($filter_cities as $row) {
                echo "<div class='filter_results'>";
                echo "<a href='" . RELATIVE_URL . "area-code-lookup/" . $row['areacode'] . "/" . $row['prefix'] . "'>";
                echo $row['city'];
                echo "<span> (" . $row['areacode'] . "/" . $row['prefix'] . ")</span>";
                echo "</a>";
                echo "</div>";
            }
            if (count($filter_cities) == 0) {
                echo "Search results not found";
            }
            echo "</div>";
            die;

            break;

        case "report_phone_number":
            $form_validation = [
                [
                    "name" => "phone-number",
                    "value" => ! empty($_POST["phone-number"]) ? $_POST["phone-number"] : "",
                    "caption" => "Phone number",
                    "validation" => "required|phone|min_length[12]",
                ],
                [
                    "name" => "email",
                    "value" => $post_data["email"],
                    "caption" => "Email address",
                    "validation" => "required|email|max_length[100]",
                ],
                [
                    "name" => "type",
                    "value" => ! empty($_POST["type"]) ? $_POST["type"] : "",
                    "caption" => "Phone number type",
                    "validation" => "required|max_length[100]",
                ],
                [
                    "name" => "details",
                    "value" => ! empty($_POST["details"]) ? $_POST["details"] : "",
                    "caption" => "Phone number details",
                    "validation" => "required|max_length[200]",
                ],
            ];

            $error_fields = SYSTEM::form_validate($form_validation);
            if (empty($error_messages)) {
                if ($verification_link = Scams::add_number($post_data["email"], $_POST["phone-number"], $_POST["type"], $_POST["details"])) {
                    $data = [
                        "verification_link" => $verification_link,
                        "email"    =>  $post_data["email"]
                    ];

                    $email_template_data = SCF::get_mail_template("report-a-phone-number-confirmation", $data);
                    $mailer = SCF::get_mailer();
                    $mailer->addAddress($post_data["email"]);
                    $mailer->Subject = "Report A Phone Number Confirmation";
                    $mailer->msgHTML($email_template_data["html"]);
                    $mailer->AltBody = $email_template_data["text"];
                    $mailer->send();
                }
                $ajax_status["status"] = true;
            } else {
                Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, implode(", ", $error_messages), ["errors", "report phone number failed", implode(", ", $error_messages) ]);
                $ajax_status["error"] = implode("<br />", $error_messages);
            }
            break;

        case 'reports':
            $type = SYSTEM::get_request_value("type", "");
            $date = SYSTEM::get_request_value("date", "");

            $date = DateTime::createFromFormat("Y-m-d", date('Y-m-d', (int)substr($date, 0, 10)));
            if ("abandoned_cart" == $type && $date !== false && ! array_sum($date->getLastErrors()) &&  255 == $user_data["user_level"]) {
                $data = [];
                $abandoned_cart = Reports::get_user_abandoned_cart_list($date->format('Y-m-d'));
                if (! empty($abandoned_cart)) {
                    foreach ($abandoned_cart as $k => $v) {
                        $key = empty($v['user_id']) ? $v['visitor_key'] : $v['user_id'];

                        $data[ $key ][] = $v;
                    }
                }

                ob_start();
                include "{$default_template_path}content/reports/abandoned_cart_data.php";
                $html = ob_get_contents();
                ob_end_clean();

                $ajax_status["status"] = true;
                $ajax_status["html"] = $html;
            } else {
                $ajax_status["status"] = false;
            }

            break;

        case 'hide_fb_group_popup':
            User::set_meta($user_id, 'disable_fb_group_popup', 1);
            $_SESSION["user"]["disable_fb_group_popup"] = 1;
            $ajax_status["status"] = true;
            break;

        case 'hide_verify_exit_page':
            User::set_meta($user_id, 'disable_verify_exit_page', 1);
            $_SESSION["user"]["disable_verify_exit_page"] = 1;
            $ajax_status["status"] = true;
            break;

        case 'new_look':
            User::set_meta($user_id, 'newlook_popup', 1);
            $_SESSION["user"]["newlook_popup"] = 1;
            $ajax_status["status"] = true;
            break;

        case "no_result_newsletter":
            $email = SYSTEM::get_request_value("email", "");
            if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $sendy = SYSTEM::loadsendy();
                $sendy->setListId(SENDY_LIST_NO_RESULTS_SEARCHES);
                $sendy->subscribe(array(
                    'email' => $email,
                ));

                $ajax_status["status"] = true;
            }

            break;
            /* return states - arosha CSI-658*/
        case 'get_states':
            $ajax_status["data"] = json_decode('[{"Name": "Alabama", "code": "AL"}, {"Name": "Alaska", "code": "AK"}, {"Name": "Arizona", "code": "AZ"}, {"Name": "Arkansas", "code": "AR"}, {"Name": "California", "code": "CA"}, {"Name": "Colorado", "code": "CO"}, {"Name": "Connecticut", "code": "CT"}, {"Name": "Delaware", "code": "DE"}, {"Name": "District Of Columbia", "code": "DC"}, {"Name": "Florida", "code": "FL"}, {"Name": "Georgia", "code": "GA"}, {"Name": "Hawaii", "code": "HI"}, {"Name": "Idaho", "code": "ID"}, {"Name": "Illinois", "code": "IL"}, {"Name": "Indiana", "code": "IN"}, {"Name": "Iowa", "code": "IA"}, {"Name": "Kansas", "code": "KS"}, {"Name": "Kentucky", "code": "KY"}, {"Name": "Louisiana", "code": "LA"}, {"Name": "Maine", "code": "ME"}, {"Name": "Maryland", "code": "MD"}, {"Name": "Massachusetts", "code": "MA"}, {"Name": "Michigan", "code": "MI"}, {"Name": "Minnesota", "code": "MN"}, {"Name": "Mississippi", "code": "MS"}, {"Name": "Missouri", "code": "MO"}, {"Name": "Montana", "code": "MT"}, {"Name": "Nebraska", "code": "NE"}, {"Name": "Nevada", "code": "NV"}, {"Name": "New Hampshire", "code": "NH"}, {"Name": "New Jersey", "code": "NJ"}, {"Name": "New Mexico", "code": "NM"}, {"Name": "New York", "code": "NY"}, {"Name": "North Carolina", "code": "NC"}, {"Name": "North Dakota", "code": "ND"}, {"Name": "Ohio", "code": "OH"}, {"Name": "Oklahoma", "code": "OK"}, {"Name": "Oregon", "code": "OR"}, {"Name": "Pennsylvania", "code": "PA"}, {"Name": "Rhode Island", "code": "RI"}, {"Name": "South Carolina", "code": "SC"}, {"Name": "South Dakota", "code": "SD"}, {"Name": "Tennessee", "code": "TN"}, {"Name": "Texas", "code": "TX"}, {"Name": "Utah", "code": "UT"}, {"Name": "Vermont", "code": "VT"}, {"Name": "Virginia", "code": "VA"}, {"Name": "Washington", "code": "WA"}, {"Name": "West Virginia", "code": "WV"}, {"Name": "Wisconsin", "code": "WI"}, {"Name": "Wyoming", "code": "WY"} ]');
            $ajax_status["status"] = true;
            break;

            /* return countries - arosha CSI-658*/
        case 'get_countries':
            $country_list = [];
            foreach ($GLOBALS["list_of_countries"] as $value => $caption) {
                $countrt_item =  new stdClass();
                $countrt_item->code = $value;
                $countrt_item->name = $caption;
                $country_list[] = $countrt_item;
            }
            $ajax_status["data"] = $country_list;
            $ajax_status["status"] = true;
            break;

        case 'exclude_from_directory':
            $id = SYSTEM::get_request_value("id", 0);
            $type = SYSTEM::get_request_value("type", 0);

            Search::search_name_exclude_n_include_from_search_cache($id, $type);

            if (! empty($type)) {
                $ajax_status["html"] = "Include To Directory";
                $ajax_status["removeclass"] = "exclude_from_directory";
                $ajax_status["addclass"] = "include_to_directory";
            } else {
                $ajax_status["html"] = "Exclude From Directory";
                $ajax_status["addclass"] = "exclude_from_directory";
                $ajax_status["removeclass"] = "include_to_directory";
            }

            $ajax_status["status"] = true;

            break;

        case 'delete_from_directory':
            $id = SYSTEM::get_request_value("id", 0);

            Search::search_name_delete_from_popular_names($id);
            $ajax_status["status"] = true;

            break;

        case 'blacklist_customers':
            $type = SYSTEM::get_request_value("type", "");
            $method = SYSTEM::get_request_value("method", "");

            $email = SYSTEM::get_request_value("email", "");
            $first_name = SYSTEM::get_request_value("first_name", "");
            $last_name = SYSTEM::get_request_value("last_name", "");
            $creditcard = SYSTEM::get_request_value("creditcard", "");
            $phone_number = SYSTEM::get_request_value("phone_number", "");
            $ip = SYSTEM::get_request_value("ip", "");
            $address_1 = SYSTEM::get_request_value("address_1", "");
            $address_2 = SYSTEM::get_request_value("address_2", "");

            if ("search" == $type) {
                $search_blacklist_customers = USER::search_blacklist_customers($email, $first_name, $last_name, $phone_number, $ip, $address_1, $address_2);
            } elseif ("filterslist" == $type) {
                $search_blacklist_customers = USER::blacklist_filters_list();
            } elseif ("update" == $type) {
                $ids = SYSTEM::get_request_value("ids", "");

                $update_method = $method == "enable" ? "activate" : "deactivate";
                User::update_activa_status($update_method, $ids);

                $ids = implode(",", $ids);
                $search_blacklist_customers = USER::search_blacklist_customers_by_ids($ids);
            } elseif ("deletefilter" == $type) {
                $id = SYSTEM::get_request_value("id", "");

                $filter_deleted = USER::delete_blacklist_filter_by_id($id);
                if ($filter_deleted) {
                    $ajax_status["success"] = true;
                }
            } elseif ("submitfilter" == $type) {
                if (empty($email) && empty($first_name) && empty($last_name) && empty($creditcard) && empty($phone_number) && empty($ip) && empty($address_1) && empty($address_2)) {
                    $ajax_status["mess"] = "<span class='btn_disabled'>Filter fields empty..</span>";
                    $ajax_status["status"] = true;
                } else {
                    $submitfilters_blacklist_customers = USER::submitfilters_blacklist_customers($email, $first_name, $last_name, $phone_number, $ip, $address_1, $address_2, $creditcard);

                    if ($submitfilters_blacklist_customers) {
                        if ("exists" === $submitfilters_blacklist_customers) {
                            $ajax_status["mess"] = "<span class='btn_disabled'>Filter already exist..</span>";
                            $ajax_status["status"] = true;
                        } else {
                            $ajax_status["mess"] = "<span class='btn_enabled'>Filter Successfully Added</span>";
                            $ajax_status["status"] = true;
                        }
                    } else {
                        $ajax_status["mess"] = "<span class='btn_disabled'>Filter Added Failed. Please try again</span>";
                        $ajax_status["status"] = false;
                    }
                }

                header("Content-Type: application/json");
                echo json_encode($ajax_status);
                die();
            }

            $ajax_status["r"] = $search_blacklist_customers;
            $ajax_status["status"] = true;

            break;

        case 'no_result_newsletter_revised':
            $email = SYSTEM::get_request_value("email", "", "POST");
            if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $sendy = SYSTEM::loadsendy();
                $sendy->setListId(SENDY_LIST_NO_RESULTS_SEARCHES_REVISED);
                $sendy->subscribe(array(
                    'email' => $email,
                ));

                if (! $user_id) {
                    foreach ($_SESSION["add_deep_search_email"] as $index => $row) {
                        $insert_data = array (
                            "email" => $email,
                            "query" => $row,
                            "params" => $_SESSION["last_search_params"],
                            "type" => $index,
                            "status" => 0
                        );
                        Search::add_no_results_search($insert_data);
                    }
                    $_SESSION['no_result_funnel_step'] = $_SESSION['no_result_funnel_step'] + 1;
                    $data["id"] = $_SESSION['no_result_funnel_id'];
                    $data["option"] = 1;
                    $data["email"] = $email;
                    Search::no_results_tracking($_SESSION['no_result_funnel_step'], $data);
                    unset($_SESSION["add_deep_search_email"]);
                }

                $ajax_status["status"] = true;
            }
            break;

        case 'unclaimed_check':
            header("Content-Type: application/json");

            $result = false;
            if (!empty($_SESSION["unclaimed-fund-landing"]["status"]) && $_SESSION["last_search_type"] == SEARCH_TYPE_NAME) {
                $last_search_params = $_SESSION["last_search_params"];
                /** Check if empty **/
                foreach ($last_search_params as $last_search_param) {
                    if (empty($last_search_param)) {
                        die();
                    }
                }
                $unclaimedFunds = UnclaimedFunds::get_unclaimed_exact_data(
                    $last_search_params["first_name"],
                    $last_search_params["last_name"],
                    $last_search_params["state"]
                );
                $unclaimedFunds = count($unclaimedFunds);
                if ($unclaimedFunds > 0) {
                    $_SESSION["unclaimed-fund-landing"]["found"] = true;
                    $_SESSION["unclaimed-fund-landing"]["data"] = $last_search_params;
                // $_SESSION["unclaimed-fund-landing"]["status"] = false;

                    $result["status"] = true;
                    $result["count"] =  $unclaimedFunds;
                    $result["name"] =  $last_search_params["first_name"] . " " . $last_search_params["last_name"];
                }
                $from_unclaimed_fund_landing = true;
            }
            echo json_encode($result);
            exit();

        case 'pwned_check':
            header("Content-Type: application/json");
            $result = new \stdClass();
            $result->status = false;

            $email = SYSTEM::get_request_value("email", "", "POST");

            if (empty($email)) {
                $email = $_SESSION["last_search_params"]["email"];
            }

            if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            }

            $result->email = $email;
            $pwned = PWNED::check_PWNED_status($email);
            if (!empty($pwned)) {
                $result->status = true;
                $result->count = $pwned;
            }

            echo json_encode($result);
            die();

        case 'pwned_email_list':
            header("Content-Type: application/json");
            $json_output = $input_post->json ? true : false;
            $result = new \stdClass();
            $result->status = false;
            $emails = SYSTEM::get_request_value("email_list", "", "POST");
            $records = SYSTEM::get_request_value("record_dates", "", "POST");

            /* Convert Email List to PWNED friendly array */
            $PWNED_Email_list = [];
            $result->pwned_emails = false;

            $result->html = "";
            foreach ($emails as $key => $res_email) {
                sleep(2);
                $pwned_obj = PWNED::check_PWNED($res_email);
                if ($pwned_obj) {
                    $result->pwned_emails = true;
                }
                $PWNED_Email_list[] = $pwned_obj ?: [ "email" => $res_email ];
            }

            if ($json_output) {
                $result->pwned = $PWNED_Email_list;
            } else {
                foreach ($PWNED_Email_list as $key => $pwned) {
                    $html = "";
                    if (is_object($pwned)) {
                            $sitehtml = "";
                        foreach ($pwned->sites as $pwned_sites) {
                            $sitehtml .= sprintf("<tr><td><strong>%s</strong><img src='%s' width='100%%' /></td><td><p>%s</p><p><strong>Compromised data:</strong> %s</p></td></tr>", $pwned_sites->Title, $pwned_sites->LogoPath, $pwned_sites->Description, implode(", ", $pwned_sites->Compromised_Data));
                        }

                            $sitecount = count($pwned->sites);
                        if ($sitecount == 1) {
                            $breach_summary_title = 'Reported in <span class="badge">1</span> incident';
                            $breach_summary_description = 'This email was involved in a data breach incident on ' . $pwned->firstDate . '.';
                        } elseif ($sitecount > 1) {
                            $breach_summary_title = 'Reported in <span class="badge">' . $sitecount . '</span> incidents';
                            $breach_summary_description = 'This email was involved in ' . $sitecount . ' data breach incidents, the earliest of which was ' . $pwned->firstDate . ' and the latest of which was ' . $pwned->lastDate . '.';
                        }

                            $html = sprintf('<div class="col-md-12 pwned selectable  limited-result" data-query="">
                                <div class="row pwned-titlebar">
                                    <div class="col-sm-6">
                                        %s
                                    </div>
                                    <div class="col-sm-6">
                                            <p class=""  data-toggle="collapse" data-target="%s" aria-expanded="false">
                                                Email Breach Summary:  <span class="break-mobile">%s</span>
                                            </p>
                                    </div>
                                </div>
                                <div class="row row-full">
                                    <div class="col-md-4 normal">
                                        <div class="period email">
                                            <ul>
                                                <li>%s %s</li>
                                            </ul>
                                        </div>
                                    </div>
                                     <div class="col-md-7">
                                            <div class="pwned-summary">%s</div>
                                    </div>
                                    <div class="col-md-1">
                                        <a class="pwned-email-list-btn collapsed"  data-toggle="collapse" data-target="%s" aria-expanded="false" href="#!">View</a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 collapse" id="%s">
                                        <table>
                                            <tr><th width="20%%" >Website</th><th  width="80%%">Data Breach</th></tr>
                                            %s
                                        </table>
                                    </div>

                                </div>
                                 </div>', $emails[$key], "#pwtable-" . $key, $breach_summary_title, empty($records[$key]["first_seen"]) ? '' : "First Seen: " . $records[$key]["first_seen"], empty($records[$key]["last_seen"]) ? '' : "| Last Seen: " . $records[$key]["last_seen"], $breach_summary_description, "#pwtable-" . $key, "pwtable-" . $key, $sitehtml);
                    } else {
                        $html = sprintf('<div class="col-md-12 pwned selectable  limited-result" data-query="">
                                    <div class="row pwned-titlebar">
                                        <div class="col-sm-6">
                                            %s
                                        </div>
                                        <div class="col-sm-6">
                                                <p>
                                                    <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span> Not Found in any Reported Data Breach
                                                </p>
                                        </div>
                                    </div>
                                    <div class="row row-full">
                                        <div class="col-md-12 normal">
                                            <div class="period email">
                                                <ul>
                                                    <li>%s %s</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                 </div>', $emails[$key], empty($records[$key]["first_seen"]) ? '' : "First Seen: " . $records[$key]["first_seen"], empty($records[$key]["last_seen"]) ? '' : "| Last Seen: " . $records[$key]["last_seen"]);
                    }

                        $result->html .= $html;
                }
            }

                $result->status = true;

                echo json_encode($result);
            die();

        case 'feedback_info':
            $period = SYSTEM::get_request_value("period", "", "POST");
            $data = CustomerFeedback::get_avg_feedback_2($period);
            $ajax_status["status"] = true;
            $ajax_status['cfb_info'] = $data;
            break;
        case 'feedback_info_by_type':
            $type = SYSTEM::get_request_value("report_type", "", "POST");
            $period = SYSTEM::get_request_value("period", "", "POST");
            $data = CustomerFeedback::get_avg_feedback_2($period, $type);
            $ajax_status["status"] = true;
            $ajax_status['cfb_info'] = $data;
            break;
        case 'feedback_info_filter':
            $from = SYSTEM::get_request_value("from", "", "POST");
            $to = SYSTEM::get_request_value("to", "", "POST");
            $type = SYSTEM::get_request_value("report_type", "", "POST");
            $data = CustomerFeedback::get_avg_feedback_filtered($from, $to, $type);
            $ajax_status["status"] = true;
            $ajax_status['cfb_info'] = $data;
            break;
        case 'feedback_covid_window':
            $feedback = SYSTEM::get_request_value("feedback", "", "POST");
            $status = LOCATIONS::update_user_feedback($feedback);
            $ajax_status["status"] =  $status;
            break;
        case 'agree_restrictions':
            User::set_restrictions_agreement($user_id);
            $early_warn = true;
            $ajax_status["status"] = true;
            break;
        case 'cancellation_info':
            $year = SYSTEM::get_request_value("year", "", "POST");
            $year_to = SYSTEM::get_request_value("year_to", "", "POST");
            $data = CustomerFeedback::get_cancellation_trend($year, $year_to);
            $ajax_status["status"] = true;
            $ajax_status['ct_info'] = $data;
            break;
        case 'unclaimed_popup':
            User::set_meta($user_id, 'unclaimed_popup', 1);
            $_SESSION["user"]["unclaimed_popup"] = 1;
            $ajax_status["status"] = true;
            break;
        case 'ras_popup':
            User::set_meta($user_id, 'ras_popup', 1);
            $_SESSION["user"]["ras_popup"] = 1;
            $ajax_status["status"] = true;
            break;
        case 'radius_info':
            $zip = SYSTEM::get_request_value("zip", "", "POST");
            $data = MelissaDataRAS::fetch_radus_data($zip);
            $ajax_status["status"] = true;
            $ajax_status["radius_info"] = $data;
            break;
        case 'ras_update':
            $address = SYSTEM::get_request_value("address", "", "POST");
            $msd = new melissadata\MelissaDataRAS(MELISSADATA_API_KEY);
            $msd->update_report($address);
            $ajax_status["status"] = true;

            break;
        case 'customer_feedback_landing':
            $header = "UserID,User Type,Design,How does it feel,How Much trust,Next Section,Where are the eyes drawn,Will get from using this site,Date\n";

            $design = str_replace('"', "'", SYSTEM::get_request_value("design", "", "POST"));
            $how_feel = str_replace('"', "'", SYSTEM::get_request_value("how_feel", "", "POST"));
            $trust = str_replace('"', "'", SYSTEM::get_request_value("trust", "", "POST"));
            $nextsection = str_replace('"', "'", SYSTEM::get_request_value("nextsection", "", "POST"));
            $eyes_drawn = str_replace('"', "'", SYSTEM::get_request_value("eyes_drawn", "", "POST"));
            $what_you_will_get = str_replace('"', "'", SYSTEM::get_request_value("what_you_will_get", "", "POST"));
            $usertype = str_replace('"', "'", SYSTEM::get_request_value("usertype", "", "POST"));
            $usertype = preg_replace('/[^A-Za-z0-9 \-_]+/', '', $usertype);

            $date = date('Y-m-d H:i:s');

            //$data = str_replace("\n", " ", "$user_id,$design,$how_feel,$trust,$nextsection,$eyes_drawn,$what_you_will_get,$date" ) . "\n";
            $data = "$user_id,\"$usertype\",\"$design\",\"$how_feel\",\"$trust\",\"$nextsection\",\"$eyes_drawn\",\"$what_you_will_get\",$date\n";

            $FileName =  ABS_PATH . "landing_page_feedback" . DIRECTORY_SEPARATOR .  "customer_feedback_landing_{$usertype}.csv";
            if (file_exists($FileName)) {
                file_put_contents($FileName, $data, FILE_APPEND);
            } else {
                file_put_contents($FileName, $header . $data);
            }

            $ajax_status["message"] = "Form Submitted Successfully. Thank you for your feedback.";

            break;

        case 'sv_feedback':
            $feedback = SYSTEM::get_request_value("feedback", "", "POST");
            $report_id = SYSTEM::get_request_value("report_id", "", "POST");
            $status = CustomerFeedback::add_sv_feedback($report_id, $feedback);
            $ajax_status["status"] =  $status;
            break;
        case 'no_feedback':
            $feedback = SYSTEM::get_request_value("feedback", "", "POST");
            $report_id = SYSTEM::get_request_value("report_id", "", "POST");
            $status = CustomerFeedback::add_name_origins_feedback($report_id, $feedback);
            $ajax_status["status"] =  $status;
            break;

        case 'js_error':
            //if ( strpos( $input_post->data, 'Script error' ) !== false ) break;
            $data = json_decode($input_post->data, true);

            if (! empty($data["error"])) {
                Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "JS error=" . $data["error"] . "error_file=" . $data["file"], ["errors", "JS error", "{$data["file"]}::{$data["error"]}" ]);
            }

            $fp = fopen(TEMP_PATH . "js_error_log.txt", "a");
            fputcsv($fp, array_merge([ date("Y-m-d H:i:s") ], $data));
            fclose($fp);

            $ajax_status["status"] =  true;
            break;
        case 'ras_save':
            $address = $input_post->address;
            $state = ucfirst($input_post->state);
            $city = $input_post->city;
            $zip = $input_post->zip;
            $lat = $input_post->lat;
            $lng = $input_post->lng;
            $_SESSION["ras_data"] = ["address" => $address,"state" => $state,"city" => $city,"zip" => $zip,"lat" => $lat,"lng" => $lng];
            // CSI-5066 home name baseline
            if (isset($_SESSION["ab_baselines_home_name"]) && !isset($_SESSION["step_zero_ras_home"])) {
                $_SESSION["ab_baselines_home_name"]->track_event("1_2_name_address_other", SYSTEM::get_device_type());
                $_SESSION["step_zero_ras_home"] = true;
            }
            // CSI-5066 home name baseline
            break;
        case 'recaptcha_link':
            $link = $input_post->link;
            $_SESSION["recaptcha_link"] = $link;
            break;
        case 'behavior':
            $section = SYSTEM::get_request_value("section");


            if ($section == "get_filters") {
                $data = Behavior::get_rebiuld_heatmap_filters($filter_type, $filter_level);
                $ajax_status["data"] = $data;
                $template = ! empty($_SESSION["heatmap_filters"][1]["data"]["template_version"]) ? $_SESSION["heatmap_filters"][1]["data"]["template_version"] : "";
                $ajax_status["url"] = ! empty($_SESSION["heatmap_filters"][1]["url"]) ? BASE_URL . $_SESSION["heatmap_filters"][1]["data"]["url"] . "?behavior_trk_heatmap=true&template={$template}&" . ( ! empty($_SESSION["heatmap_filters"][1]["data"]["query_string"]) ? $_SESSION["heatmap_filters"][1]["data"]["query_string"] : "" ) : "";
                $ajax_status["width"] =  ! empty($_SESSION["heatmap_filters"][2]["selected"]) ? $_SESSION["heatmap_filters"][2]["selected"] : "";
                //print_r($_SESSION["heatmap_filters"][1]);die;

                header("Content-Type: application/json");
                echo json_encode($ajax_status);
                die();
            } elseif ($section == "heatmap_run_filter") {
                $filter_type = $input_post->v;
                $filter_level = $input_post->l;

                $data = Behavior::run_heatmap_filters($filter_type, $filter_level);
                $ajax_status["data"] = $data;
                $template = ! empty($_SESSION["heatmap_filters"][1]["data"]["template_version"]) ? $_SESSION["heatmap_filters"][1]["data"]["template_version"] : "";
                $ajax_status["url"] = ! empty($_SESSION["heatmap_filters"][1]["url"]) ? BASE_URL . $_SESSION["heatmap_filters"][1]["data"]["url"] . "?behavior_trk_heatmap=true&template={$template}&" . ( ! empty($_SESSION["heatmap_filters"][1]["data"]["query_string"]) ? $_SESSION["heatmap_filters"][1]["data"]["query_string"] : "" ) : "";
                $ajax_status["width"] =  ! empty($_SESSION["heatmap_filters"][2]["selected"]) ? $_SESSION["heatmap_filters"][2]["selected"] : "";
                //print_r($_SESSION["heatmap_filters"][1]);die;
                header("Content-Type: application/json");
                echo json_encode($ajax_status);
                die();
            } elseif ($section == "heatmap_topbar_disable") {
                $_SESSION["heatmap_filters"] = [];

                header("Content-Type: application/json");
                echo json_encode($ajax_status);
                die();
            } elseif ($section == "heatmap_topbar") {
                $page = $input_post->page;

                $data = Behavior::get_heatmap_admin_topbar($page);
                $ajax_status["data"] = $data;
                header("Content-Type: application/json");
                echo json_encode($ajax_status);
                die();
            } elseif ($section == "heatmap") {
                $filter_type = $input_post->v;
                $filter_level = $input_post->l;
                $data = Behavior::get_heatmap_filters($filter_type, $filter_level);
                $ajax_status["data"] = $data;
                $ajax_status["url"] = ! empty($_SESSION["heatmap_filters"][1]["url"]) ? BASE_URL . $_SESSION["heatmap_filters"][1]["data"]["url"] . "?behavior_trk_heatmap=true&" . ( ! empty($_SESSION["heatmap_filters"][1]["data"]["query_string"]) ? $_SESSION["heatmap_filters"][1]["data"]["query_string"] : "" ) : "";
                $ajax_status["template"] = ! empty($_SESSION["heatmap_filters"][1]["data"]["template_version"]) ? $_SESSION["heatmap_filters"][1]["data"]["template_version"] : "";

                header("Content-Type: application/json");
                echo json_encode($ajax_status);
                die();
            } elseif (array_key_exists($input_post->type, $behavior_actions) && $input_post->_v == $visitor_key) {
                if ("g" == $input_post->type) {
                    $behavior_actions_data["action_time"] = date("Y-m-d H:i:s", ( $input_post->time / 1000 ));
                    Behavior::log_action($behavior_actions[$input_post->type], $input_post->_e, $behavior_actions_data);
                } else {
                    $behavior_actions_data = [];
                    $element_data = [];
                    $behavior_actions_data["action_time"] = date("Y-m-d H:i:s", ( $input_post->time / 1000 ));
                    $behavior_actions_data["url"] = $input_post->url;
                    $behavior_actions_data["url_type"] = $input_post->url_type;
                    $behavior_actions_data["url_template"] = $input_post->url_template;
                    $behavior_actions_data["cursor_x"] = $input_post->x;
                    $behavior_actions_data["cursor_y"] = $input_post->y;
                    $behavior_actions_data["element_left"] = $input_post->l;
                    $behavior_actions_data["element_top"] = $input_post->t;
                    $behavior_actions_data["viewport_width"] = $input_post->winw;
                    $behavior_actions_data["viewport_height"] = $input_post->winh;

                    $element_data["element"] = $input_post->tr_node;
                    $element_data["element_class"] = $input_post->tr_class;
                    $element_data["element_id"] = $input_post->tr_id;
                    $element_data["element_text"] = $input_post->tr_text;
                    $element_data["element_placeholder"] = $input_post->tr_placeholder;
                    $element_data["element_title"] = $input_post->tr_title;
                    $element_data["element_src"] = $input_post->tr_src;
                    $element_data["element_href"] = $input_post->tr_href;
                    $element_data["element_name"] = $input_post->tr_name;
                    $element_data["element_type"] = $input_post->tr_type;
                    $element_data["attributes"] = $input_post->attr;

                    if ($behavior_tracking_activated) {
                        $behaviordata = Behavior::log_action($behavior_actions[$input_post->type], "", $behavior_actions_data, $element_data);
                        $ajax_status["page"] = $behaviordata["page"];
                    }
                }
            }

            break;
        case 'reset_cr_search':
               unset($_SESSION["cr_search"]);
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_four_cr"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("5_dont_search_cr", SYSTEM::get_device_type());
                $_SESSION["cr_choice"] = "cancel";
            }
            break;
        case "criminal_rec_donot_show":
            $value = $_POST["value"];
            user::set_criminal_records_popup($value);
        case "cr_set":
            $ajax_status["data"] = $_SESSION["cr_search_filters"] ;
            $ajax_status["status"] = true;
            break;
        case "ccpa_request_update":
            CCPA_Requests::update_records($input_post);
            break;
        case "ccpa_request_status":
            $id = $input_post->id;
            $status = $input_post->status;
            $note = $input_post->note;
            CCPA_Requests::update_status($id, $status, $note);
            break;
        case "ccpa_duplicate":
            $id = $input_post->id;
             $id = trim($id, "duplicate_");
            CCPA_Requests::duplicate_request($id);
            break;
        case "set_link":
            $link = $_POST["link"];
            $_SESSION["redirect_link"] = $link;
            break;
        case "privacy_lock_what_is_this":
            User::set_meta($user_id, "privacy_lock_close_description", 1);
            break;
        case 'privacy_lock':
            $val = SYSTEM::get_request_value("v", "");
            $email_index = SYSTEM::get_request_value("val", "");

            if (empty($active_plans)) {
                $ajax_status["status"] = true;
                $ajax_status["failed"] = true;
            } else {
                $ajax_status["m"] = User::change_privacy_lock_status($val, $email_index);
                if ($val == 2) {
                    $privacy_lock_emails = ! empty($user_data["privacy_lock_emails"]) ? explode(",", $user_data["privacy_lock_emails"]) : [ $user_data["email"] ];
                    if ($privacy_lock_activated && ! empty($privacy_lock_emails[$email_index][0])) {
                        //foreach( $privacy_lock_emails as $pl_email ){
                        PWNED::save_user_pawned_data($privacy_lock_emails[$email_index][0]);
                        //}
                    }
                }
            }
            $ajax_status["status"] = true;
            break;
        case 'criminal_record_search':
            if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_four_cr"])) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("6_searched_cr", SYSTEM::get_device_type());
            }
            $full_name = $input_post->name;
            $full_name_arr = explode(" ", $full_name);
            $last_name = array_pop($full_name_arr);
            $first_name = reset($full_name_arr);
            $state = $input_post->state;
            $params =  [
            "first_name" => $first_name,
            "last_name" => $last_name,
            "state" => $state,
            "search_type" => "CriminalSearch",
            "fields" => '["criminal"]',
            "dob" => $dob,];

            // Fetch Records from the API
            $ds_idi = new \DataSource\IDI(IDI_CLIENT_ID, IDI_SECRET_KEY, DEBUG);
            $records = $ds_idi->runSearch($ds_idi->mapParams($params));
            CriminalRecords::save($records["data"]);
            $filters = [
            "full_name" => $first_name . " " . $last_name ,
            "state" => $state,
            "dob" => $dob,
            ];
            $records = CriminalRecords::search($filters);
            if ($records) {
                $filters["count"] = count($records);
                $token = md5(uniqid());
                $_SESSION["cr_tokens"][ $token ] = [
                "query" => $full_name,
                "filters" => $filters,

                ];
                $plan_id = 0;
                $purchase_status = 0;
                $user_id = 0;
                $existing_id = CriminalRecords::get_id_if_report_exists($user_id, $_SESSION["cr_tokens"][ $token ]["filters"], $user_data["user_level"]);
                if ($existing_id == 0) {
                    $id = CriminalRecords::add_user_search($user_id, $plan_id, $_SESSION["cr_tokens"][ $token ]["filters"], $purchase_status, $user_data["user_level"]);
                } else {
                    $id = $existing_id;
                }
                $_SESSION["cr_count"] =  count($records);
                $_SESSION["cr_id"] =  $id;
                $_SESSION["add_cr_to_new_user"] = true;
                $ajax_status["token"] = $token;
                $ajax_status["count"] =  $_SESSION["cr_count"];
                $ajax_status["status"] = true;


                if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_four_cr"])) {
                    $_SESSION["ab_cr_signedout_2022"]->track_event("8_results_cr", SYSTEM::get_device_type());
                    $_SESSION["step_results_cr"] = true;
                    $_SESSION["cr_choice"] = "results";
                }
            } else {
                if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["step_four_cr"])) {
                    $_SESSION["ab_cr_signedout_2022"]->track_event("7_no_results_cr", SYSTEM::get_device_type());
                    $_SESSION["cr_choice"] = "no_results";
                }
            }
            break;
        case 'privacy_lock_feedback':
            User::set_meta($user_id, "privacy_lock_feedback", $input_post->feedback);
            $ajax_status["status"] = true;
            break;
        case 'resend_link':
            User::send_verification_link($user_id, $user_data["email"], true);
            $ajax_status["status"] = true;
            break;
        case 'ccpa_refresh':
            CCPA_Requests::get_emails();
            $ajax_status["status"] = true;
            break;
        case "ccpa_extend":
            $id = $input_post->id;
             $id = trim($id, "extend_");
            CCPA_Requests::extend_request($id);
            break;

        case 'chat':
            if (trim($post_data["message"]) == "" && ( count($_FILES["file"]["tmp_name"]) == 0 )) {
                die();
            }

            if (count($_FILES["file"]["name"]) == "0") {
                $file_url = "";
            } else {
                $img_extension = array_pop(explode(".", $_FILES["file"]["name"]));
                if (preg_match("/jpe?g|png|gif|mp[3|4]|avi|docx?|txt|rtf|pdf/i", $img_extension)) {
                    $file_url = "chat/{$user_id}/{$id}/" . time() . "." . strtolower($img_extension);
                    $upload_path = UPLOADS_PATH . $file_url;
                    @mkdir(dirname($upload_path), 0755, true);
                    move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path);
                } else {
                    $file_url = "";
                }
            }

                    $saved_data['indepth_id'] = $id;
                    $saved_data['user_id'] = $user_id;
                    $saved_data['message'] = $post_data['message'];
                    $saved_data['file'] = $file_url;
                    $get_indepth_data = Membership::add_chat($saved_data);
                    $ajax_status["status"] = true;
                    $ajax_status["msg"] = "test";
            break;

        case 'chat_update':
            $ajax_status["status"] = false;

            if (isset($post_data["last_id"])) {
                if ($post_data["chat_data"] = Membership::get_chat($id, $post_data["last_id"])) {
                    $ajax_status["id"] = $id;
                    $ajax_status["msg"] = "chat_update_test";
                    $ajax_status["data"] = $post_data["chat_data"];
                    $ajax_status["status"] = true;
                }
            }

            break;
        case "agree_assoc":
            $id = $input_post->id;
            User::set_assoc_acc_cancel_($id);
            break;

        case "ris_crop_popup":
            $id = $input_post["id"];
            User::set_ris_crop_popup($user_id, $id);
            $ajax_status["status"] = true;
            break;

        case "run_image_url":
            if (255 == $user_data["user_level"] && Search::validate_image_url($input_post->image_url)) {
                /* $user_upload_path = UPLOADS_PATH . $user_id . DIRECTORY_SEPARATOR;
                if ( ! file_exists( $user_upload_path ) ) @mkdir( $user_upload_path );

                $basename = md5( time() ) . mt_rand( 100000, 999999 );
                $url = Search::file_get_contents_curl( $input_post->image_url );

                $filename = $user_upload_path . $basename . basename( $url );
                file_put_contents( $filename , $url ); */

                $search_params = array (
                "image_urls" => [ $input_post->image_url ],
                "type" => SEARCH_TYPE_IMAGE,
                "tpd_request" => 1
                );
                Search::queue_image_search($search_params);
                $ajax_status["url"] = PAGE_URL_REVERSE_IMAGE_SEARCH . "?search_history=1";
                $ajax_status["status"] = true;
            } else {
                $ajax_status["status"] = false;
            }

            break;

        case "optout_ris_status":
            $id = $input_post["id"];
            $status = $input_post["status"];
            $optout_date = date('Y-m-d');
            $optout_by = $user_data['email'];
            $phash = $input_post["phash"];

            Optout::update_ris_optout_status($status, $optout_date, $optout_by, $id);
            Optout::update_image_results_optout(( $status == 2 ? 0 : $status ), $phash);

            $ajax_status["status"] = true;
            break;

        case "update_ris_notification":
            $id = $input_post["id"];
            $status = $input_post["status"];
            Search::update_ris_notification($id, $status);
            $ajax_status["status"] = true;
            break;

        case "ris_notification_close":
            $id = $input_post["id"];
            Search::ris_notification_close($id, 1);
            $ajax_status["status"] = true;
            break;

        case "check_is_ris_pending":
            $id = $input_post["id"];
            $result = Search::check_is_ris_pending($id);
            $ajax_status["progress"] = Search::ris_get_progress($id);

            if ($input_post["type"] == "intermediate") {
                $last_search = User::search_history_ris_intermediate($user_id, 0, 1);
                $additional_info = explode("|", $last_search[0]["additional_info"]);

                $ajax_status["exact"] = $additional_info[1];
                $ajax_status["similar"] = $additional_info[2];
            }

            if ($result) {
                $ajax_status["status"] = true;
            } else {
                $ajax_status["status"] = false;
                Behavior::system_log_action(__FILE__, __LINE__, __METHOD__, "RIS search end", [ "behavior_reporting", "RIS_behavior", "sid: {$id}" ]);
            }
            break;

        case "get_ris_pending":
            $id = $input_post["id"];
            $result = Search::get_ris_pending($id);

            $ajax_status["opt_out"] = $result["opt_out"];
            $ajax_status["pending"] = $result["pending"];
            $ajax_status["status"] = true;
            break;

        case "block_domain":
            $id = $input_post["id"];
            $status = $input_post["status"];
            OptOut::update_domain_in_blocked_list($id, $status);
            $ajax_status["status"] = true;
            break;

        case "faq_cancel_subscription":
            $_SESSION["faq_cancel_subscription"] = true;
            $ajax_status["status"] = true;
            break;

        case "faq_cancel_subscription_unset":
            if (isset($_SESSION["faq_cancel_subscription"])) {
                unset($_SESSION["faq_cancel_subscription"]);
            }
            $ajax_status["status"] = true;
            break;

        case 'ris_tips_popup':
            $ajax_status["status"] = true;
            if (! empty($user_id) && ! empty(SYSTEM::request("setting"))) {
                $res = user::set_meta($user_id, 'disable_ris_tips_popup', ( SYSTEM::request("setting") == 'true' ) ? 1 : 0);
            }

        case "block_boot":
            OptOut::block_data_id_for_phone_or_email($input_post->value);
            break;


        case "get_ris_current_image_count":
            $id = $input_post["id"];
            $total_result_count = $input_post["total_result_count"];

            $result = Search::get_image_results($id);

            $facial_recognition = $duplicates = $possible_matches = [];
            foreach ($result as &$image) {
                if (empty($image["source"])) {
                    continue;
                }

                if (! empty($image["flags"]) && ( $image["flags"] & RESULT_FLAG_FACIAL_RECOGNITION )) {
                    $facial_recognition[] = $image;
                } else {
                    if ($image["score"] >= SCORE_EXACT_MATCH) {
                        $duplicates[] = $image;
                    } else {
                        $possible_matches[] = $image;
                    }
                }
            }

            $fg_facial_recognition = [];
            $fg_duplicates = [];
            $fg_possible_matches = [];
            $fi_facial_recognition = [];
            $fi_duplicates = [];
            $fi_possible_matches = [];

            foreach ($facial_recognition as $index => $row) {
                if ($row["face_matches"] == 1) {
                    $fg_facial_recognition[] = $facial_recognition[$index];
                } else {
                    $fi_facial_recognition[] =  $facial_recognition[$index];
                }
            }
            foreach ($duplicates as $index => $row) {
                if ($row["face_matches"] == 1) {
                    $fg_duplicates[] = $duplicates[$index];
                } else {
                    $fi_duplicates[] =  $duplicates[$index];
                }
            }
            foreach ($possible_matches as $index => $row) {
                if ($row["face_matches"] == 1) {
                    $fg_possible_matches[] = $possible_matches[$index];
                } else {
                    $fi_possible_matches[] =  $possible_matches[$index];
                }
            }
            $fg_facial_recognition = array_values($fg_facial_recognition);
            $fg_duplicates = array_values($fg_duplicates);
            $fg_possible_matches = array_values($fg_possible_matches);

            $facial_recognition = array_values($fi_facial_recognition);
            $duplicates = array_values($fi_duplicates);
            $possible_matches = array_values($fi_possible_matches);

            array_multisort(array_column($fg_facial_recognition, 'phash_face_distance'), SORT_ASC, $fg_facial_recognition);
            array_multisort(array_column($fg_duplicates, 'phash_face_distance'), SORT_ASC, $fg_duplicates);
            array_multisort(array_column($fg_possible_matches, 'phash_face_distance'), SORT_ASC, $fg_possible_matches);

            array_multisort(array_column($facial_recognition, 'phash_face_distance'), SORT_ASC, $facial_recognition);
            array_multisort(array_column($duplicates, 'phash_face_distance'), SORT_ASC, $duplicates);
            array_multisort(array_column($possible_matches, 'phash_face_distance'), SORT_ASC, $possible_matches);

            $total_fg_count = count($fg_facial_recognition) + count($fg_duplicates) + count($fg_possible_matches);
            $total_full_image_count = count($facial_recognition) + count($duplicates) + count($possible_matches);

            $ajax_status["total_result_count"] = count($result);
            $ajax_status["full_image_count"] = $total_full_image_count;
            $ajax_status["total_fg_count"] = $total_fg_count;

            $ajax_status["duplicates"] = count($duplicates);
            $ajax_status["possible_matches"] = count($possible_matches);

            $ajax_status["fg_duplicates"] = count($fg_duplicates);
            $ajax_status["fg_possible_matches"] = count($fg_possible_matches);

            $ajax_status["total_result_count"] = count($result);
            $ajax_status["diff"] = count($result) - $total_result_count;

            $ajax_status["status"] = true;
            $ajax_status["stop_flg"] = Search::get_stopflg_image_search($id);
            $ajax_status["progress"] = Search::ris_get_progress($id);
            break;

        case "get_ris_current_images":
            $id = $input_post["id"];
            $limit = $input_post["limit"];
            $sort_by_website = $input_post["sort_by_website"];
            $force_old_scoring = false;
            $result = Search::get_image_results($id, 0, $limit);

            $facial_recognition = $duplicates = $possible_matches = [];
            foreach ($result as &$image) {
                if (empty($image["source"])) {
                    continue;
                }

                if (! empty($image["flags"]) && ( $image["flags"] & RESULT_FLAG_FACIAL_RECOGNITION )) {
                    $facial_recognition[] = $image;
                } else {
                    if ($image["score"] >= SCORE_EXACT_MATCH) {
                        $duplicates[] = $image;
                    } else {
                        $possible_matches[] = $image;
                    }
                }
            }

            $fg_facial_recognition = [];
            $fg_duplicates = [];
            $fg_possible_matches = [];
            $fi_facial_recognition = [];
            $fi_duplicates = [];
            $fi_possible_matches = [];

            foreach ($facial_recognition as $index => $row) {
                if ($row["face_matches"] == 1) {
                    $fg_facial_recognition[] = $facial_recognition[$index];
                } else {
                    $fi_facial_recognition[] =  $facial_recognition[$index];
                }
            }
            foreach ($duplicates as $index => $row) {
                if ($row["face_matches"] == 1) {
                    $fg_duplicates[] = $duplicates[$index];
                } else {
                    $fi_duplicates[] =  $duplicates[$index];
                }
            }
            foreach ($possible_matches as $index => $row) {
                if ($row["face_matches"] == 1) {
                    $fg_possible_matches[] = $possible_matches[$index];
                } else {
                    $fi_possible_matches[] =  $possible_matches[$index];
                }
            }
            $fg_facial_recognition = array_values($fg_facial_recognition);
            $fg_duplicates = array_values($fg_duplicates);
            $fg_possible_matches = array_values($fg_possible_matches);

            $facial_recognition = array_values($fi_facial_recognition);
            $duplicates = array_values($fi_duplicates);
            $possible_matches = array_values($fi_possible_matches);

            if ($force_old_scoring) {
                array_multisort(array_column($fg_facial_recognition, 'phash_face_distance'), SORT_ASC, $fg_facial_recognition);
                array_multisort(array_column($fg_duplicates, 'phash_face_distance'), SORT_ASC, $fg_duplicates);
                array_multisort(array_column($fg_possible_matches, 'phash_face_distance'), SORT_ASC, $fg_possible_matches);

                array_multisort(array_column($facial_recognition, 'phash_face_distance'), SORT_ASC, $facial_recognition);
                array_multisort(array_column($duplicates, 'phash_face_distance'), SORT_ASC, $duplicates);
                array_multisort(array_column($possible_matches, 'phash_face_distance'), SORT_ASC, $possible_matches);
            }

            $data_sets = [
            "user" => [
                "caption" => "Boosted Image Results",
                "count" => count($facial_recognition),
                "data" => &$facial_recognition,
                "compare" => true,
                "srot_order" => 4,
                "description" => "These image results were found using our boosting service, which uses facial features to find more images online that may match the image you uploaded."
            ],
            "image-highsimilar" => [
                "caption" => "Exact Image Matches",
                "count" => count($duplicates),
                "data" => &$duplicates,
                "compare" => false,
                "srot_order" => 1,
                "description" => "These image results match the main points of comparison found in your original.  (In some cases, the degrees of similarity were high enough that the algorithm included them for your review.)",
            ],
            "image-similar" => [
                "caption" => "Similar Image Matches",
                "count" => count($possible_matches),
                "data" => &$possible_matches,
                "compare" => true,
                "srot_order" => 5,
                "description" => "These image results match the main points of comparison found in your original. While it's possible there may be an exact match, in some cases the degrees of similarity are high enough the algorithm includes them for your review.",
            ],
            ];

            $data_sets_fg = [
            "user" => [
                "caption" => "Boosted Face Matches",
                "count" => count($fg_facial_recognition),
                "data" => &$fg_facial_recognition,
                "compare" => true,
                "srot_order" => 3,
            ],
            "image-highsimilar" => [
                "caption" => "Exact Face Matches",
                "count" => count($fg_duplicates),
                "data" => &$fg_duplicates,
                "compare" => false,
                "srot_order" => 2,
            ],
            "image-similar" => [
                "caption" => "Similar Face Matches",
                "count" => count($fg_possible_matches),
                "data" => &$fg_possible_matches,
                "compare" => true,
                "srot_order" => 6,
            ],
            ];


            $images_data = [];
            $images_data_fg = [];

            foreach ($data_sets as $icon => $data_set) {
                if ($data_set["count"]) {
                    foreach ($data_set["data"] as $index => $data) {
                              $html_div = "";
                              $uri = parse_url($data["ref"]);
                              $domain = preg_replace("/^((?:[^\.]+\.){2}[^\.]+)\$/m", "\\1", $uri["host"]);

                              $html_div = '<div class="col-xs-6 col-md-3 ' . str_replace(" ", "_", $data_set["caption"]) . ' img-box-list" style="display:block"  data-domain="' . $domain . '" >
                                <div class="box-col">
                                <div class="ris-img-blur">
                                    <span class="si-eye-close"></span>
                                    <p>Preview Unavailable</p>
                                    <a data-url="' . $data["ref"] . '" onload="scf.results.image.external_func(this,scf);" class="btn">Visit Website</a>
                                </div>
                                    <div class="img-thumbnail" data-url="' .  $data["source"] . '"  onclick="scf.results.image.external_func(this,scf);"> 
                                    <img src="' . $current_template_assets_url . '/images/loader-white.svg" data-src="' . SCF::imgcdn_url($data["source"]) . '" class="lazy-loader scf_ris_loader" alt="User" decoding="async" loading="lazy"/>
                                </div> 
                                <a href="' . $data["ref"] . '" target="_blank" data-url="' . $data["ref"] . '" onclick="scf.results.image.external_func(this,scf);" class="box-name">
                                        <img src="' . $current_template_assets_url . '/images/loader-white.svg" class="lazy-loader favicon" data-src="https://www.google.com/s2/favicons?domain=' . $domain . '" decoding="async" loading="lazy" />
                                    ' . $domain . '
                                    </a>
                                    <a data-url="' . $data["ref"] . '" onclick="scf.results.image.external_func(this,scf);" class="btn">Open Website</a> ';

                        if ($data_set["compare"]) {
                            $html_div .= '<a class="run-search compare-action" onclick="scf.results.image.compare_func(this,scf);" data-size="' . round($data["size"] / 1024, 2) . '" data-url="' . SCF::imgcdn_url($data["source"]) . '" data-ref="' . $data["ref"] . '" data-source="' . $data["source"] . '" data-dimensions="' . "{$data["width"]}x{$data["height"]}" . '">Compare Original</a>';
                        }

                              $html_div .= '</div></div>';

                              array_push($images_data, ["id" => str_replace(" ", "_", $data_set["caption"]) , "data" => $html_div , "count" => $data_set["count"] ,"domain" => $domain ,"source" => $data["source"] ,"sort_order" => $data["srot_order"]]);
                    }
                }
            }


            foreach ($data_sets_fg as $icon => $data_set) {
                if ($data_set["count"]) {
                    foreach ($data_set["data"] as $index => $data) {
                        $html_div_fg = "";
                        $uri = parse_url($data["ref"]);
                        $domain = preg_replace("/^((?:[^\.]+\.){2}[^\.]+)\$/m", "\\1", $uri["host"]);


                        $html_div_fg = '<div class="col-xs-6 col-md-3 fg_' . str_replace(" ", "_", $data_set["caption"]) . ' img-box-list" style="display:block" data-domain="' . $domain . '">
                                <div class="box-col">
                                    <div class="ris-img-blur">
                                        <span class="si-eye-close"></span>
                                        <p>Preview Unavailable</p>
                                        <a data-url="' . $data["ref"] . '" onclick="scf.results.image.external_func(this,scf);" class="btn">Visit Website</a>
                                    </div>
                                    <div class="img-thumbnail" data-url="' .  $data["source"] . '"  onclick="scf.results.image.external_func(this,scf);"> 
                                       <img src="' . $current_template_assets_url . '/images/loader-white.svg" data-src="' . SCF::imgcdn_url($data["source"]) . '" class="lazy-loader scf_ris_loader" alt="User" decoding="async" loading="lazy"/>
                                  </div> 
                                  <a href="' . $data["ref"] . '" target="_blank" data-url="' . $data["ref"] . '" onclick="scf.results.image.external_func(this,scf);" class="box-name">
                                        <img src="' . $current_template_assets_url . '/images/loader-white.svg" class="lazy-loader favicon" data-src="https://www.google.com/s2/favicons?domain=' . $domain . '" decoding="async" loading="lazy"/>
                                       ' . $domain . '
                                    </a>
                                    <a data-url="' . $data["ref"] . '" onclick="scf.results.image.external_func(this,scf);" class="btn">Open Website</a> ';

                        if ($data_set["compare"]) {
                            $html_div_fg .= '<a class="run-search compare-action" onclick="scf.results.image.compare_func(this,scf);" data-size="' . round($data["size"] / 1024, 2) . '" data-url="' . SCF::imgcdn_url($data["source"]) . '" data-ref="' . $data["ref"] . '" data-source="' . $data["source"] . '" data-dimensions="' . "{$data["width"]}x{$data["height"]}" . '">Compare Original</a>';
                        }

                           $html_div_fg .= '</div>
                           </div>';

                        array_push($images_data_fg, ["id" => "fg_" . str_replace(" ", "_", $data_set["caption"]) , "data" => $html_div_fg, "count" => $data_set["count"],"domain" => $domain, "source" => $data["source"],"sort_order" => $data["srot_order"]]);
                    }
                }
            }

                    array_multisort(array_column($images_data, 'sort_order'), SORT_ASC, $images_data);
                    array_multisort(array_column($images_data_fg, 'sort_order'), SORT_ASC, $images_data_fg);

                    global $dbi;

            if ($sort_by_website == "true") {
                array_multisort(array_column($images_data, 'id'), SORT_ASC, $images_data, array_column($images_data, 'domain'), SORT_ASC, $images_data);

                $urls = [];
                $other_urls = [];
                $images_data_sorted = [];
                $urls_exceed_5 = [];
                $urls_not_exceed_5 = [];

                $sql = "CREATE TEMPORARY TABLE IF NOT EXISTS `temptable` (`id` int NOT NULL,`type` text NOT NULL,`data` text NOT NULL,`count` text NOT NULL,`domain` text NOT NULL)";
                $dbi->query($sql);


                foreach ($images_data as $data) {
                    $dbi->insert("temptable", [ "id" => $id,"type" => $data["id"], "data" => $data["data"], "count" => $data["count"] ,"domain" => $data["domain"]]);
                }


                $html_div = ' <div class="col-xs-12 col-md-12 category-title">
                                             <div class="cat-fb">
                                                 <p class="sm-type">
                                                 <img src="' . $current_template_assets_url . '/images/loader-white.svg" class="lazy-loader favicon" data-src="https://www.google.com/s2/favicons?domain=" /> Other URL</p>
                                                 <br/>
                                                 <br/>
                                             </div>
                                     </div>';
                array_push($other_urls, ["id" => $data["id"] , "data" => $html_div, "count" => $data["count"],"domain" => $data["domain"]]);



                $sql = sprintf("select * , count(domain) as domain_count from temptable where id = %s group by domain ,type", $id);
                $results = $dbi->query_to_multi_array($sql);

                foreach ($results as $result) {
                    if ($result["domain_count"] >= 5) {
                        array_push($urls_exceed_5, [ "id" => $result["type"], "data" => $result["data"], "count" => $result["count"] , "domain" => $result["domain"]]);
                    } else {
                        array_push($urls_not_exceed_5, [ "id" => $result["type"], "data" => $result["data"], "count" => $result["count"] , "domain" => $result["domain"]]);
                    }
                }


                foreach ($urls_exceed_5 as $url_exceed_5) {
                    $html_div = ' <div class="col-xs-12 col-md-12 category-title">
                        <div class="cat-fb">
                            <p class="sm-type">
                            <img src="' . $current_template_assets_url . '/images/loader-white.svg" class="lazy-loader favicon" data-src="https://www.google.com/s2/favicons?domain=' . $url_exceed_5["domain"] . '" /> ' . $url_exceed_5["domain"] . '</p>
                            <br/>
                            <br/>
                        </div>
                        </div>';

                    array_push($images_data_sorted, ["id" => $url_exceed_5["id"] , "data" => $html_div, "count" => $url_exceed_5["count"],"domain" => $url_exceed_5["domain"]]);


                    $sql = sprintf("select * from temptable where domain = '%s' and type = '%s' and id = %s", $url_exceed_5["domain"], $url_exceed_5["id"], $id);
                    $results = $dbi->query_to_multi_array($sql);

                    $count = 0;
                    foreach ($results as $data) {
                        if ($count < 3) {
                            array_push($images_data_sorted, [ "id" => $data["type"], "data" => $data["data"], "count" => $data["count"] ,"domain" => $data["domain"]]);
                        } else {
                            $html_div = str_replace("display:block", "display:none", $data["data"]);
                            array_push($images_data_sorted, [ "id" => $data["type"], "data" => $html_div, "count" => $data["count"] , "domain" => $data["domain"]]);
                        }
                        $count++;
                    }
                    $html_div = '<div class="col-xs-6 col-md-3 img-box-list folded-item">
                             <div class="box-col">
                                     <div class="img-thumbnail">
                                         <div class="ris-img-overlay">
                                             <p>+' . ($count - 3) . '</p>
                                         </div>
                                         <img src="' . $current_template_assets_url . '/images/report.png"  class="scf_ris_loader" alt="User" decoding="async" loading="lazy" style="background-image: url(' . SCF::imgcdn_url($url_exceed_5["source"]) . '" />
                                     </div>
                                     <p>These multiple results came from same website.</p>
                                     <a class="view-all-btn" data-domain="' . $url_exceed_5["domain"] . '" onclick="scf.results.image.view_all(this,scf);">View All</a>
                                 </div>
                             </div>';
                     array_push($images_data_sorted, ["id" => $url_exceed_5["id"] , "data" => $html_div, "count" => $url_exceed_5["count"],"domain" => $url_exceed_5["domain"]]);
                }

                foreach ($urls_not_exceed_5 as $url_not_exceed_5) {
                    $sql = sprintf("select * from temptable where domain = '%s' and type = '%s' and id = %s", $url_not_exceed_5["domain"], $url_not_exceed_5["id"], $id);
                    $results = $dbi->query_to_multi_array($sql);
                    foreach ($results as $data) {
                        array_push($other_urls, [ "id" => $data["type"], "data" => $data["data"], "count" => $data["count"] ,"domain" => $data["domain"]]);
                    }
                }



                $images_data_sorted = array_merge($images_data_sorted, $other_urls);

                /********************Facial Recog********************/
                $sql = sprintf("delete from temptable");
                $dbi->query($sql);
                array_multisort(array_column($images_data_fg, 'id'), SORT_ASC, $images_data_fg, array_column($images_data_fg, 'domain'), SORT_ASC, $images_data_fg);

                $other_urls_fg = [];
                $images_data_sorted_fg = [];
                $urls_exceed_5_fg = [];
                $urls_not_exceed_5_fg = [];

                foreach ($images_data_fg as $data) {
                    $dbi->insert("temptable", [ "id" => $id,"type" => $data["id"], "data" => $data["data"], "count" => $data["count"] , "domain" => $data["domain"]]);
                }


                $html_div = ' <div class="col-xs-12 col-md-12 category-title">
                                             <div class="cat-fb">
                                                 <p class="sm-type">
                                                 <img src="' . $current_template_assets_url . '/images/loader-white.svg" class="lazy-loader favicon" data-src="https://www.google.com/s2/favicons?domain=" /> Other URL</p>
                                                 <br/>
                                                 <br/>
                                             </div>
                                     </div>';
                array_push($other_urls_fg, ["id" => $data["id"] , "data" => $html_div, "count" => $data["count"],"domain" => $data["domain"]]);



                $sql = sprintf("select * , count(domain) as domain_count from temptable where id = %s group by domain ,type", $id);
                $results = $dbi->query_to_multi_array($sql);

                foreach ($results as $result) {
                    if ($result["domain_count"] >= 5) {
                        array_push($urls_exceed_5_fg, [ "id" => $result["type"], "data" => $result["data"], "count" => $result["count"] , "prv" => $result["prv"],"domain" => $result["domain"]]);
                    } else {
                        array_push($urls_not_exceed_5_fg, [ "id" => $result["type"], "data" => $result["data"], "count" => $result["count"] , "prv" => $result["prv"],"domain" => $result["domain"]]);
                    }
                }


                foreach ($urls_exceed_5_fg as $url_exceed_5) {
                    $html_div = ' <div class="col-xs-12 col-md-12 category-title">
                        <div class="cat-fb">
                            <p class="sm-type">
                            <img src="' . $current_template_assets_url . '/images/loader-white.svg" class="lazy-loader favicon" data-src="https://www.google.com/s2/favicons?domain=' . $url_exceed_5["domain"] . '" /> ' . $url_exceed_5["domain"] . '</p>
                            <br/>
                            <br/>
                        </div>
                        </div>';

                    array_push($images_data_sorted_fg, ["id" => $url_exceed_5["id"] , "data" => $html_div, "count" => $url_exceed_5["count"],"domain" => $url_exceed_5["domain"]]);


                    $sql = sprintf("select * from temptable where domain = '%s' and type = '%s' and id = %s", $url_exceed_5["domain"], $url_exceed_5["id"], $id);
                    $results = $dbi->query_to_multi_array($sql);

                    $count = 0;
                    foreach ($results as $data) {
                        if ($count < 3) {
                            array_push($images_data_sorted_fg, [ "id" => $data["type"], "data" => $data["data"], "count" => $data["count"], "domain" => $data["domain"]]);
                        } else {
                            $html_div = str_replace("display:block", "display:none", $data["data"]);
                            array_push($images_data_sorted_fg, [ "id" => $data["type"], "data" => $html_div, "count" => $data["count"] , "domain" => $data["domain"]]);
                        }
                        $count++;
                    }
                    $html_div = '<div class="col-xs-6 col-md-3 img-box-list folded-item">
                             <div class="box-col">
                                     <div class="img-thumbnail">
                                         <div class="ris-img-overlay">
                                             <p>+' . ($count - 3) . '</p>
                                         </div>
                                         <img src="' . $current_template_assets_url . '/images/report.png"  class="scf_ris_loader" alt="User" decoding="async" loading="lazy" style="background-image: url(' . SCF::imgcdn_url($url_exceed_5["source"]) . '" />
                                     </div>
                                     <p>These multiple results came from same website.</p>
                                     <a class="view-all-btn" data-domain="' . $url_exceed_5["domain"] . '" onclick="scf.results.image.view_all(this,scf);">View All</a>
                                 </div>
                             </div>';
                     array_push($images_data_sorted_fg, ["id" => $url_exceed_5["id"] , "data" => $html_div, "count" => $url_exceed_5["count"],"domain" => $url_exceed_5["domain"]]);
                }
                foreach ($urls_not_exceed_5_fg as $url_not_exceed_5) {
                    $sql = sprintf("select * from temptable where domain = '%s' and type = '%s' and id = %s", $url_not_exceed_5["domain"], $url_not_exceed_5["id"], $id);
                    $results = $dbi->query_to_multi_array($sql);
                    foreach ($results as $data) {
                        array_push($other_urls_fg, [ "id" => $data["type"], "data" => $data["data"], "count" => $data["count"] ,"domain" => $data["domain"]]);
                    }
                }


                        $images_data_sorted_fg = array_merge($images_data_sorted_fg, $other_urls_fg);

                        $sql = sprintf("DROP TABLE IF EXISTS temptable");
                        $dbi->query($sql);

                        $ajax_status["images_data"] = $images_data_sorted;
                        $ajax_status["images_data_fg"] = $images_data_sorted_fg;
            } else {
                    $ajax_status["images_data"] = $images_data;
                    $ajax_status["images_data_fg"] = $images_data_fg;
            }
            break;
        case "API_Tracking_Berify":
            $from_date = $input_post["from_date"];
            $to_date = $input_post["to_date"];
            $time_split = $input_post["time_split"];
            $engine = $input_post["engine"];
            // $test = $input_post["test"];

            try {
                $url = sprintf('https://api.berify.com/api/baseline-api/?token=tNm2mXbZa52y3Y&from_date=%s&to_date=%s&time_split=%s&engine=%s&test=0', $from_date, $to_date, $time_split, $engine);
                $client = new \vbrowser();
                $stream = $client->request($url);
                //$data = json_decode( $stream["response"], true );
                $ajax_status["data"] = $stream["response"];
            } catch (Exception $ex) {
                $ajax_status["data"] = $ex;
            }

            break;

        case "api_key_indicator_monthly":
            try {
                $type = $input_post["type"];

                $start_date_current = date("Y-m-d", strtotime("-1 months"));
                $start_date_current = date('Y-m-d', (strtotime('-1 day', strtotime($start_date_current)) ));
                $end_date_current =  date("Y-m-d");

                $start_date_previous = date("Y-m-d", (strtotime("-1 months", strtotime($start_date_current))));
                $end_date_previous =  date('Y-m-d', (strtotime('-1 months', strtotime($end_date_current)) ));

                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, "https://api.berify.com/api/baseline-api/?token=tNm2mXbZa52y3Y&from_date={$start_date_current}&to_date={$end_date_current}&time_split=d&engine=a&test=0");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $output_previous = curl_exec($curl);


                curl_setopt($curl, CURLOPT_URL, "https://api.berify.com/api/baseline-api/?token=tNm2mXbZa52y3Y&from_date={$start_date_previous}&to_date={$end_date_previous}&time_split=d&engine=a&test=0");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $output_current = curl_exec($curl);

                curl_close($curl);

                $output_previous = json_decode($output_previous, true);
                $output_current = json_decode($output_current, true);


                $return_output_previous = [];
                $return_output_current = [];
                $return_labels = [];

                $c = 0;
                foreach ($output_previous["response"] as $daten) {
                    if (!empty($daten)) {
                        array_push($return_output_previous, $daten[$type]);
                        array_push($return_labels, $c++);
                    }
                }

                foreach ($output_current["response"] as $daten) {
                    if (!empty($daten)) {
                        array_push($return_output_current, $daten[$type]);
                    }
                }


                $ajax_status["output_previous"] = $return_output_previous;
                $ajax_status["output_current"] = $return_output_current;
                $ajax_status["labels"] = array_reverse($return_labels);
            } catch (Exception $ex) {
                $ajax_status["data"] = $ex;
            }

            break;

        case "api_key_indicator_daily":
            try {
                $type = $input_post["type"];
                $day = $input_post["day"];

                $start_date_current =  date("Y-m-d", strtotime("-" . ($day + 1) . " day"));
                $end_date_current =  date("Y-m-d", strtotime("-" . $day . " day"));

                $start_date_previous = date("Y-m-d", (strtotime("-1 months", strtotime($start_date_current))));
                $end_date_previous =  date('Y-m-d', (strtotime('-1 months', strtotime($end_date_current)) ));

                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, "https://api.berify.com/api/baseline-api/?token=tNm2mXbZa52y3Y&from_date={$start_date_current}&to_date={$end_date_current}&time_split=h&engine=a&test=0");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $output_previous = curl_exec($curl);


                curl_setopt($curl, CURLOPT_URL, "https://api.berify.com/api/baseline-api/?token=tNm2mXbZa52y3Y&from_date={$start_date_previous}&to_date={$end_date_previous}&time_split=h&engine=a&test=0");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $output_current = curl_exec($curl);

                curl_close($curl);

                $output_previous = json_decode($output_previous, true);
                $output_current = json_decode($output_current, true);


                $return_output_previous = [];
                $return_output_current = [];
                $return_labels = [];

                $c = 0;
                foreach ($output_previous["response"] as $daten) {
                    if (!empty($daten)) {
                        array_push($return_output_previous, $daten[$type]);
                        array_push($return_labels, $c++);
                    }
                }

                foreach ($output_current["response"] as $daten) {
                    if (!empty($daten)) {
                        array_push($return_output_current, $daten[$type]);
                    }
                }


                $ajax_status["output_previous"] = $return_output_previous;
                $ajax_status["output_current"] = $return_output_current;
                $ajax_status["labels"] = array_reverse($return_labels);
            } catch (Exception $ex) {
                $ajax_status["data"] = $ex;
            }

            break;

        case "set_regular_premium_combine":
            $_SESSION["regular_premium_combine"] = 1;
            $_SESSION["active_boosted_ontime"] = 1;
            $ajax_status["status"] = true;

            // CSI-5822
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["7"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step6_yes_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["7"] = true;
            }
            break;

        case "unset_regular_premium_combine":
            if (isset($_SESSION["regular_premium_combine"])) {
                unset($_SESSION["regular_premium_combine"]);
            }
            $ajax_status["status"] = true;

            // CSI-5822
            if (isset($_SESSION["step_two_idi"]) && !isset($_SESSION["step_three_idi"]["7"]) && isset($_SESSION["ab_premium_idi_SO"])) {
                $_SESSION["ab_premium_idi_SO"]->track_event("3_a_step6_no_idi_SO", SYSTEM::get_device_type());
                $_SESSION["step_three_idi"]["7"] = true;
            }
            break;

        case "set_regular_premium_combine_plan":
            $_SESSION["active_boosted_ontime"] = 1;
            $ajax_status["status"] = true;
            break;

        case "unset_regular_premium_combine_plan":
            if (isset($_SESSION["active_boosted_ontime"])) {
                unset($_SESSION["active_boosted_ontime"]);
            }
            $ajax_status["status"] = true;
            break;

        case "update_gmaps_api_count":
            global $dbi;
            $sql = sprintf("INSERT INTO %s (`cache_id`,`cache_person_id`,`engine_id`,`results`,`flags`,`stage`) VALUES (NULL,NULL,%s,1,NULL,0)", DB_TBL_DATA_SOURCE_LOG, SEARCH_ENGINE_GOOGLE_PLACES_API);
            $dbi->query($sql);

            $ajax_status["is_done"] = true;
            break;
        case "search_specialist_phone":
            $_SESSION["specialist_number"] = $input_post["number"];
            $ajax_status["status"] = true;
            break;
        case "remove_pl_report":
            $id = $input_post["id"];
            $user_id = $input_post["user_id"];
            Search::update_privacy_lock_tracking($user_id, false, $id, date("Y-m-d"), "", false);
            break;
        case "monitor_pl_report":
            $id = $input_post["id"];
            $user_id = $input_post["user_id"];
            $val = $input_post["val"];
            if ($val == 1) {
                $tracking = true;
            } else {
                $tracking = false;
            }
                    Search::update_privacy_lock_tracking($user_id, $tracking, $id, date("Y-m-d"), "", true);
            break;
        case "delete_pl_email":
            $id = $input_post["id"];
            User::delete_privacy_lock_emails($id);
            break;
        case "add_report":
            $id = $input_post["id"];
            $date = date('Y-m-d');
            Search::update_privacy_lock_tracking($user_id, true, $id, $date, "", true);
            $ajax_status["status"] = true;
        case "privacy_lock_rating":
            $get_meta = user::get_meta($user_id, "privacy_lock_feedback");
            if (! empty($get_meta)) {
                $get_meta = unserialize($get_meta);

                $db_star = $get_meta["rating"];
                $db_q1 = $get_meta["q1"];
                $db_q2 = $get_meta["q2"];
                $db_q3 = $get_meta["q3"];
                $db_q4 = $get_meta["q4"];
                $db_q5 = $get_meta["q5"];
                $db_reports = $get_meta["reports"];
                $db_emails = $get_meta["emails"];
                $db_plans = $get_meta["plans"];
            }

            $star = ( ! empty($input_post->star) ) ? $input_post->star : ( ! empty($db_star) ? $db_star : "" );
            $q1 = ( ! empty($input_post->q1) ) ? $input_post->q1 : ( ! empty($db_q1) ? $db_q1 : "" );
            $q2 = ( ! empty($input_post->q2) ) ? $input_post->q2 : ( ! empty($db_q2) ? $db_q2 : "" );
            $q3 = ( ! empty($input_post->q3) ) ? $input_post->q3 : ( ! empty($db_q3) ? $db_q3 : "" );
            $q4 = ( ! empty($input_post->q4) ) ? $input_post->q4 : ( ! empty($db_q4) ? $db_q4 : "" );
            $q5 = ( ! empty($input_post->q5) ) ? $input_post->q5 : ( ! empty($db_q5) ? $db_q5 : "" );
            $reports = ( ! empty($input_post->reports) ) ? $input_post->reports : ( ! empty($db_reports) ? $db_reports : "0" );
            $emails = ( ! empty($input_post->emails) ) ? $input_post->emails : ( ! empty($db_emails) ? $db_emails : "0" );
            $plans = ( ! empty($input_post->plans) ) ? $input_post->plans : ( ! empty($db_plans) ? $db_plans : "" );

            /*$reports = ( ! empty ( $input_post["reports"] ) ? $input_post["reports"] : "0");
            $emails = ( ! empty ( $input_post["emails"] ) ? $input_post["emails"] : "0");
            $plans = ( ! empty ( $input_post["plans"] ) ? $input_post["plans"] : "");*/

            if (strtotime($user_data["created"]) < strtotime("2022-10-15")) {
                $new_user = 0;
            } else {
                $new_user = 1;
                $q4 = "";
                $q5 = "";
            }

            $value = [
            "new_user" => $new_user,
            "rating" => $star,
            "q1" => $q1,
            "q2" => $q2,
            "q3" => $q3,
            "q4" => $q4,
            "q5" => $q5,
            "emails" => $emails,
            "reports" => $reports,
            "plans" => $plans
            ];

            $serialize_value = serialize($value);
            User::set_meta($user_id, "privacy_lock_feedback", $serialize_value);
            $ajax_status["status"] = true;
            break;
        case "add_deep_search":
            $_SESSION["add_deep_search"][ $input_post["type"] ] = $input_post["id"];
            $_SESSION['no_result_funnel_step'] = $_SESSION['no_result_funnel_step'] + 1;
            $data["id"] = $_SESSION['no_result_funnel_id'];
            $data["option"] = 1;
            Search::no_results_tracking($_SESSION['no_result_funnel_step'], $data);
            $ajax_status["status"] = true;
            break;
        case "run_deep_search":
            if (isset($_SESSION["add_deep_search"])) {
                $deep_search_count = [];
                $dp_index = 0;
                unset($_SESSION["no_results_history"]);

                foreach ($_SESSION["add_deep_search"] as $index => $row) {
                    if ($index == SEARCH_TYPE_NAME) {
                        $params = [
                        "full_name" => $row, /* david smith */
                        "type" => SEARCH_TYPE_NAME,
                        ];
                    } elseif ($index == SEARCH_TYPE_PHONE) {
                        $params = [
                        "phone" => $row, /* 8044534130 */
                        "type" => SEARCH_TYPE_PHONE
                        ];
                    } elseif ($index == SEARCH_TYPE_USERNAME) {
                        $params = [
                        "username" => $row, /* dave.mcclellan.92 */
                        "type" => SEARCH_TYPE_USERNAME
                        ];
                    } elseif ($index == SEARCH_TYPE_EMAIL) {
                        $params = [
                        "email" => $row, /* david.mcclellan@langley.af.mil */
                        "type" => SEARCH_TYPE_EMAIL
                        ];
                    } else {
                        $params = [];
                    }

                    $deep_results = \WebAPI\Search::run($params);
                    $deep_search_count[$dp_index]["cache_id"] = $deep_results["cache_id"];
                    $deep_search_count[$dp_index]["count"] = $deep_results["result_count"];
                    $deep_search_count[$dp_index]["query"] = $row;
                    $_SESSION["no_results_history"][$dp_index] = $deep_results["cache_id"];
                    $dp_index++;
                }

                array_multisort(array_column($deep_search_count, 'count'), SORT_DESC, $deep_search_count);

                if (! empty($deep_search_count[0]["query"]) && ! empty($deep_search_count[0]["cache_id"])) {
                    $redirect_page = BASE_URL . "search/" . SYSTEM::sanitize($deep_search_count[0]["query"]) . "-{$deep_search_count[0]["cache_id"]}/?search=new";
                } else {
                    $redirect_page = "";
                    if ($user_id) {
                        foreach ($_SESSION["add_deep_search"] as $index => $row) {
                            $insert_data = array(
                            "email" => $user_data["email"],
                            "query" => $row,
                            "params" => $_SESSION["last_search_params"],
                            "type" => $index,
                            "status" => 0
                            );
                            Search::add_no_results_search($insert_data);
                        }
                    }
                }

                $_SESSION["add_deep_search_email"] = $_SESSION["add_deep_search"];
                unset($_SESSION["add_deep_search"]);
            } else {
                $redirect_page = "";
            }

            if (! empty($redirect_page)) {
                $data["id"] = $_SESSION['no_result_funnel_id'];
                $data["option"] = 1;
                Search::no_results_tracking(5, $data);
            }
            $ajax_status["url"] = $redirect_page;
            $ajax_status["status"] = true;
            break;

        case "ris_intermediate_update":
            $id = $input_post["id"];
            $reuslt = User::search_history_ris_id($id, $user_id, SEARCH_TYPE_IMAGE, 0, 5);
            $ajax_status["status"] = true;
            $ajax_status["report_id"] = $reuslt["id"];
            $ajax_status["similar"] = $reuslt["similar"];
            $ajax_status["exact"] = $reuslt["exact"];
            $ajax_status["loading"] = $reuslt["loading"];
            break;


        case "deactivate_my_account":
            ##Permanantly delete user account by user from mobile app
            if (! empty($mobile_app)) {
                User::update_activa_status("deactivate", $user_id);
                session_destroy();
                session_name(SESSION_NAME);
                session_start();
                $ajax_status["status"] = true;
            } else {
                $ajax_status["status"] = false;
            }
            ##Permanantly delete user account by user from mobile app
            break;

        case "snap_cache_check":
            $ajax_status["status"] = false;

            if ($input_post->url) {
                $client = new vbrowser();
                $client->headers_only(true);
                $stream = $client->request("https://webcache.googleusercontent.com/search?q=cache:" . urlencode($input_post->url));

                $ajax_status["status"] = ( $stream["status_code"] == 200 );
            }

            break;
        case "bing_api":
            $url = $input_post["url"];
            $result = BingAPI::search_file(UPLOADS_PATH . $url);
            $ajax_status["data"] = $result;
            $ajax_status["status"] = true;
            break;

        case "skip_deep_search":
            $_SESSION['no_result_funnel_step'] = $_SESSION['no_result_funnel_step'] + 1;
            $data["id"] = $_SESSION['no_result_funnel_id'];
            $data["option"] = 2;
            Search::no_results_tracking($_SESSION['no_result_funnel_step'], $data);
            $ajax_status["status"] = true;
            break;

        case "chatbot_submit":
            $_SESSION["chatbot_data"] = $_SESSION["chatbot_data"] ?? [];
            $chatbot_data =& $_SESSION["chatbot_data"];

            $chatbot = new \DataSource\ChatBot();
            if ("welcome" == $input_post->data) {
                $message = $chatbot->getMessage(empty($chatbot_data) ? \DataSource\ChatBot::DATA_SET_WELCOME : \DataSource\ChatBot::DATA_SET_WELCOME_BACK);
                $_SESSION["chatbot_data"] = [ "known_information" => [] ];
            } else {
                if (! empty($chatbot_data["abuse_system"])) {
                    break;
                }
                $chatbot_data["abuse_system"] = true;
                $message = $chatbot->parseMessage($input_post->data, $chatbot_data["known_information"]);

                if (empty($message["person_search"])) {
                    $message = $chatbot->getMessage(\DataSource\ChatBot::DATA_SET_PEOPLE_INFO_ONLY);
                } else {
                    $chatbot_data["known_information"] = array_merge($chatbot_data["known_information"] ?? [], array_filter($message));
                    $searchable = ( $message["not_known"] && ! empty($message["first_name"]) && ! empty($message["last_name"]) );
                    $do_search = false;

                    if ($searchable) {
                        if (! empty($message["reply"]) && empty($message["state"]) && empty($chatbot_data["asked_state"])) {
                            $chatbot_data["asked_state"] = true;
                            $searchable = false;
                        } else {
                            $do_search = true;
                        }
                    }

                    if ($do_search) {
                        $search_params["type"] = SEARCH_TYPE_NAME;
                        $search_params = array_merge($search_params, array_intersect_key($message, $search_params));
                        $search_data = Search::parse_search_parameters($search_params);
                        $search = Search::do_search($search_data);

                        if (count($search["results"])) {
                            $message = str_replace([ "{name}", "{count}" ], [ $search_data["query"], count($search["results"]) ], $chatbot->getMessage(\DataSource\ChatBot::DATA_SET_FOUND_RESULTS));
                            $redirect_page = BASE_URL . "search/" . SYSTEM::sanitize($search_data["query"]) . "-{$search["cache_id"]}/";
                            $message .= "<br /><br /><a href=\"{$redirect_page}\">View Results</a><br />";
                        } else {
                            $message = str_replace("{name}", $search_data["query"], $chatbot->getMessage(\DataSource\ChatBot::DATA_SET_NO_RESULTS));
                        }

                        $message .= "<br />" . $chatbot->getMessage(\DataSource\ChatBot::DATA_SET_THANK_YOU);
                        $chatbot_data["known_information"] = [];
                    } else {
                        $message = $message["reply"] ?? $chatbot->getMessage(\DataSource\ChatBot::DATA_SET_INSUFFICIENT_INFO);
                    }
                }
            }

            $chatbot_data["abuse_system"] = false;

            $ajax_status = [
            "status" => true,
            "return" => $message
            ];
            break;

        case "scam_chat_check":
            $chat = new DataSource\ChatBot();
            $romance_scam = $chat->scamMessageValidator($input_post->message);

            if (!empty($romance_scam)) {
                $ajax_status["status"] = true;
                $ajax_status["romance_scam"] = $romance_scam;
            }
            break;
        case "check_all_opt":
            foreach ($_SESSION[$input_post["class"]] as $k => $val) {
                $_SESSION[$input_post["class"]][$k] = $input_post["is_checked"];
            }
            $ajax_status["status"] = true;
            break;
        case "check_single_opt":
            $_SESSION[$input_post["class"]][$input_post["id"]] = $input_post["is_checked"];
            $ajax_status["status"] = true;
            break;
        default:
            $ajax_status["status"] = false;
    }

    header("Content-Type: application/json");
    echo json_encode($ajax_status);
    die();
}

    $module = "404";
    $page_title = "People Search - Socialcatfish.com";
    $page_description = "Find anyone online using socialcatifish.com image, phone, email, name and username searches. We help you search for people and verify online connections.";
    header("{$_SERVER["SERVER_PROTOCOL"]} 404 Not Found");
