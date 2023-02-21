<?php

    // - If the user is logged in, check whether the user has enough tokens to do the search, if not show the "You ran out of credit" page.
    // - If the user is not logged in, show the search results page, but if clicked on an individual result, show the memberships page.
    //   If there is only 1 result, then show the membership page as well.
    // - Do the search, Check whether there are any results, if not show the "No results" page.
    // - If there are results, and the user click on any of the results, tokens should be deducted from the account.
    // - If the user is logged in save the search in history

    // ** Image Search
    // Todo: People can do the reverse search using 3 different methods
    // 1) Upload multiple images and get the results
    // 2) Do a search using URLs
    // 3) XML Sitemap of images to select required images.

    // Ajax call to get the image URLs from a XML Sitemap
    // Todo: Save uploaded images to the "uploads" folder under current users folder.
    // Todo: All Remote URLs should be downloaded to the server and saved inside "uploads" folder under current users folder.
    // Todo: The list of files saved in the current users "uploads" folder should be sent to the search functions.
    if ( ! defined( "FROM_INDEX" ) ) die();
    $token = $_POST['token'];
    $recaptcha_result = search::get_recpatcha_response($token);
    
    $baseline_track["save"] = false;
	// AB testing Start
	if (!$user_id) {
		if ($search_params["type"] == SEARCH_TYPE_EMAIL) {
			// CSI-5066 home name
			if (isset($_SESSION["ab_baselines_home_name"]) && !isset($_SESSION["home_step_one_email"]) ) {
				$_SESSION["ab_baselines_home_name"]->track_event("1_2_name_email_other", SYSTEM::get_device_type());
				$_SESSION["home_step_one_email"] = true;
			}
			// CSI-5120 home
			if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_zero_email"]) && !isset($_SESSION["step_one_email"]) ) {
				$_SESSION["ab_baselines_email"]->track_event("1_email_search", SYSTEM::get_device_type());
				$_SESSION["step_one_email"] = true;
			}
			// CSI-5130 
			if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_zero_email_main"]) && !isset($_SESSION["step_one_email_main"]) ) {
				$_SESSION["ab_baselines_email_main"]->track_event("1_email_search_main", SYSTEM::get_device_type());
				$_SESSION["step_one_email_main"] = true;
			}

			// CSI-5708
			if (isset($_SESSION["step_zero_sp_mobile"]) && isset($_SESSION["ab_sp_single_mobile"]) && !isset($_SESSION["step_one_sp_mobile"]) && SYSTEM::get_device_type() == "mobile") {
				$_SESSION["ab_sp_single_mobile"]->track_event("1_ab_sp_mobile_name", SYSTEM::get_device_type());
				$_SESSION["step_one_sp_mobile"] = true;
				$_SESSION["step_search_sp_mobile"] = true;
			}

		}

		if ($search_params["type"] == SEARCH_TYPE_USERNAME) {
			// CSI-5066 home name
			if (isset($_SESSION["ab_baselines_home_name"]) && !isset($_SESSION["home_step_one_username"]) ) {
				$_SESSION["ab_baselines_home_name"]->track_event("1_2_name_username_other", SYSTEM::get_device_type());
				$_SESSION["home_step_one_username"] = true;
			}
			// CSI-5120 home
			if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_zero_username"]) && !isset($_SESSION["step_one_username"]) ) {
				$_SESSION["ab_baselines_username"]->track_event("1_username_search", SYSTEM::get_device_type());
				$_SESSION["step_one_username"] = true;
			}
            // CSI-5085 
            if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_zero_username_main"]) && !isset($_SESSION["step_one_username_main"]) ) {
                $_SESSION["ab_baselines_username_main"]->track_event("1_username_search_main", SYSTEM::get_device_type());
                $_SESSION["step_one_username_main"] = true;
            }
			if (isset($_SESSION["ab_username_copy"]) && isset($_SESSION["step_zero_username_copy"]) && !isset($_SESSION["step_one_username_copy"]) ) {
				$_SESSION["ab_username_copy"]->track_event("1_username_search_copy", SYSTEM::get_device_type());
				$_SESSION["step_one_username_copy"] = true;
			}
			// CSI-5708
			if (isset($_SESSION["step_zero_sp_mobile"]) && isset($_SESSION["ab_sp_single_mobile"]) && !isset($_SESSION["step_one_sp_mobile"]) && SYSTEM::get_device_type() == "mobile") {
				$_SESSION["ab_sp_single_mobile"]->track_event("1_ab_sp_mobile_name", SYSTEM::get_device_type());
				$_SESSION["step_one_sp_mobile"] = true;
				$_SESSION["step_search_sp_mobile"] = true;
			}

		}

		if ($search_params["type"] == SEARCH_TYPE_PHONE) {
			if (isset($_SESSION["ab_baselines_home_name"]) && !isset($_SESSION["home_step_one_phone"]) ) {
				$_SESSION["ab_baselines_home_name"]->track_event("1_2_name_phone_other", SYSTEM::get_device_type());
				$_SESSION["home_step_one_phone"] = true;
			}
			// CSI-5120 home
			if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_zero_phone"]) && !isset($_SESSION["step_one_phone"]) ) {
				$_SESSION["ab_baselines_phone"]->track_event("1_phone_search", SYSTEM::get_device_type());
				$_SESSION["step_one_phone"] = true;
			}
			// CSI-5070 Phone Main
			if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_zero_phone_main"]) && !isset($_SESSION["step_one_phone_main"]) ) {
				$_SESSION["ab_baselines_phone_main"]->track_event("1_phone_main_search", SYSTEM::get_device_type());
				$_SESSION["step_one_phone_main"] = true;
			}
            // CSI-5708
            if (isset($_SESSION["step_zero_sp_mobile"]) && isset($_SESSION["ab_sp_single_mobile"]) && !isset($_SESSION["step_one_sp_mobile"]) && SYSTEM::get_device_type() == "mobile") {
                $_SESSION["ab_sp_single_mobile"]->track_event("1_ab_sp_mobile_name", SYSTEM::get_device_type());
                $_SESSION["step_one_sp_mobile"] = true;
                $_SESSION["step_search_sp_mobile"] = true;
            }
			// CSI-5708
			if (isset($_SESSION["step_zero_sp_mobile"]) && isset($_SESSION["ab_sp_single_mobile"]) && !isset($_SESSION["step_one_sp_mobile"]) && SYSTEM::get_device_type() == "mobile") {
				$_SESSION["ab_sp_single_mobile"]->track_event("1_ab_sp_mobile_name", SYSTEM::get_device_type());
				$_SESSION["step_one_sp_mobile"] = true;
				$_SESSION["step_search_sp_mobile"] = true;
			}
		}

		if ($search_params["type"] == SEARCH_TYPE_IMAGE) {
            // CSI-700 Image Search Progress Web Worker
			if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_zero_WW_RIS"]) && !isset($_SESSION["step_one_WW_RIS"]) ) {
				$_SESSION["ab_search_progress_WW_RIS"]->track_event("1_searched_WW_RIS", SYSTEM::get_device_type());
				$_SESSION["step_one_WW_RIS"] = true;
			}
			// CSI-5067 Image ads
			if (isset($_SESSION["ab_baselines_old_img"]) && isset($_SESSION["step_zero_ris"]) && !isset($_SESSION["step_one_ris"]) ) {
				$_SESSION["ab_baselines_old_img"]->track_event("1_made_search", SYSTEM::get_device_type());
				$_SESSION["step_one_ris"] = true;
			}
			// CSI-5068 Image ads
			if (isset($_SESSION["ab_baselines_basic_img"]) && isset($_SESSION["step_zero_ris_basic"]) && !isset($_SESSION["step_one_ris_basic"]) ) {
				$_SESSION["ab_baselines_basic_img"]->track_event("1_made_search_basic", SYSTEM::get_device_type());
				$_SESSION["step_one_ris_basic"] = true;
			}
			// CSI-5066 home name
			if (isset($_SESSION["ab_baselines_home_name"]) && !isset($_SESSION["home_step_one_image"]) ) {
				$_SESSION["ab_baselines_home_name"]->track_event("1_2_name_image_other", SYSTEM::get_device_type());
				$_SESSION["home_step_one_image"] = true;
			}
			// CSI-5120 home
			if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_zero_ris"]) && !isset($_SESSION["step_one_ris"]) ) {
				$_SESSION["ab_baselines_image"]->track_event("1_image_search", SYSTEM::get_device_type());
				$_SESSION["step_one_ris"] = true;
			}
			// CSI-5069 Image main
			if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_zero_ris_main"]) && !isset($_SESSION["step_one_ris_main"]) ) {
				$_SESSION["ab_baselines_image_main"]->track_event("1_image_search_main", SYSTEM::get_device_type());
				$_SESSION["step_one_ris_main"] = true;
			}
			// CSI-5083 
			if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_zero_ris_ad"]) && !isset($_SESSION["step_one_ris_ad"]) ) {
				$_SESSION["ab_baselines_image_ad"]->track_event("1_image_search_ad", SYSTEM::get_device_type());
				$_SESSION["step_one_ris_ad"] = true;
			}
			// CSI-5129
			if (isset($_SESSION["ab_baselines_image_ad_copy"]) && isset($_SESSION["step_zero_ris_ad_copy"]) && !isset($_SESSION["step_one_ris_ad_copy"]) ) {
				$_SESSION["ab_baselines_image_ad_copy"]->track_event("1_image_search_ad_copy", SYSTEM::get_device_type());
				$_SESSION["step_one_ris_ad_copy"] = true;
			}
		}

		if ($search_params["type"] ==  SEARCH_TYPE_NAME) {
            // CSI-700 Web Worker Search Progress
			if (isset($_SESSION["step_zero_WW_SS"]) && isset($_SESSION["ab_search_progress_WW_SS"]) && !isset($_SESSION["step_one_WW_SS"])) {
				$_SESSION["ab_search_progress_WW_SS"]->track_event("1_searched_WW_SS", SYSTEM::get_device_type());
				$_SESSION["step_one_WW_SS"] = true;
			}
			// CSI-5066 home name
			if (isset($_SESSION["ab_baselines_home_name"]) && !isset($_SESSION["step_one_name"]) ) {
				$_SESSION["ab_baselines_home_name"]->track_event("1_1_name_search", SYSTEM::get_device_type());
				$_SESSION["step_one_name"] = true;
			}
            // CSI-6420
            if (isset($_SESSION["ab_cr_signedout_2022"]) && !isset($_SESSION["step_one_cr"]) ) {
                $_SESSION["ab_cr_signedout_2022"]->track_event("1_1_name_search_cr", SYSTEM::get_device_type());
                $_SESSION["step_one_cr"] = true;
            }
			// CSI-5708
			if (isset($_SESSION["step_zero_sp_mobile"]) && isset($_SESSION["ab_sp_single_mobile"]) && !isset($_SESSION["step_one_sp_mobile"]) && SYSTEM::get_device_type() == "mobile") {
				$_SESSION["ab_sp_single_mobile"]->track_event("1_ab_sp_mobile_name", SYSTEM::get_device_type());
				$_SESSION["step_one_sp_mobile"] = true;
				$_SESSION["step_search_sp_mobile"] = true;
			}

		}
        
	} 

    // AB Testing End

    if ($recaptcha_result["success"] == true && $recaptcha_result["score"] < 0.5 && $token != null && !$user_id) {
        $id = search::get_random_result();
        $token = null;
        if (OptOut::is_search_pointer_optedout($id)) {

            SYSTEM::redirect($_SERVER["REQUEST_URI"]);
        }
        $redirect_page = BASE_URL . "search/" . SYSTEM::sanitize($search_data["query"]) . "-{$id}/";
        System::redirect($redirect_page);
    }

    if($input_get["recaptcha"]==1){
        $search_params = $_SESSION["search_param_copy"];
        unset($_SESSION["search_param_copy"]);
        $social_search_show_recaptcha = false;
    }

    // ReCaptcha Implementation for Guest Users
    $guest_captcha_test = $input_get->search_token && isset( $_SESSION["search_token"][ $input_get->search_token ] ) && $input_get["g-recaptcha-response"];
    if ( $guest_captcha_test ) {

        $search_params = $_SESSION["search_token"][ $input_get->search_token ];

    }

    $results = "search/results-b.php";
    $results_user = "search/results.php";

    if (isset($_SESSION["removed-head-foot"]) && !$user_id) {
        $remove_head_foot = true;
    }

    // Page Data
    $page_title .= " - Search";
    $redirect_page = false;
    $create_link_if_results_morethan = 3;
    //$no_index = true;

    // Flagged Users
    if ( ! empty( $user_data["flagged_account"] ) ) {

        if ( SYSTEM::is_ajax_request() ) {

            $ajax_status["status"] = true;
            $ajax_status["url"] = PAGE_URL_DASHBOARD;
            SYSTEM::flush_ajax_response();

        } else {

            SYSTEM::redirect( PAGE_URL_DASHBOARD );

        }

    }

    if ( $post_data["gp_token"] && ! empty( $token ) && ! empty( $_SESSION["guest_progress"][ $token ] ) ) $search_params = $_SESSION["guest_progress"][ $token ];

    if ( $user_id && ( ! empty( $late_payment_plans["image_search"] ) || ! empty( $late_payment_plans["general_search"] ) ) ) {
        $flg_late_payment_plans = false;
        if( ! empty( $late_payment_plans["general_search"] ) && ( $search_params["type"] == SEARCH_TYPE_EMAIL || $search_params["type"] == SEARCH_TYPE_USERNAME || $search_params["type"] == SEARCH_TYPE_PHONE || $search_params["type"] ==  SEARCH_TYPE_NAME ) ) $flg_late_payment_plans = true;
        if( ! empty( $late_payment_plans["image_search"] ) && $search_params["type"] == SEARCH_TYPE_IMAGE ) $flg_late_payment_plans = true;

        if( $flg_late_payment_plans ) {
            if ( SYSTEM::is_ajax_request() ) {

                $ajax_status["status"] = true;
                $ajax_status["url"] = PAGE_URL_DASHBOARD;
                SYSTEM::flush_ajax_response();

            } else System::redirect( PAGE_URL_DASHBOARD );
        }
    }

    /* No results */
    $user_plans = user::get_active_plan_ids( $user_id );
    if( array_key_exists( PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL, $user_plans ) || array_key_exists( PLAN_UNLIMITED_3_DAY_SS, $user_plans ) ) $social_search_plan = true;
    if( array_key_exists( PLAN_UNLIMITED_IMAGE_5_DAY_TRIAL, $user_plans ) || array_key_exists( PLAN_UNLIMITED_3_DAY_RIS, $user_plans ) ) $image_search_plan = true;

    if(empty($user_id)) $no_res_categorykey = "guest";
    else if(!empty($social_search_plan) && !empty($image_search_plan))  $no_res_categorykey = "members_ss_ris";
        else $no_res_categorykey = "members_ss";

    /* End of No results */

    // Mod for CSI-6432 (Mark URLs as 404)
    $custom_urls_to_be_marked_as_404 = [
        "/person/rhonda-vaupel-9213609/",    
        "/person/david-mccelland-134/",    	
    ];
    
    if ( ! empty( $_SERVER["REQUEST_URI"] ) && in_array( strtolower( $_SERVER["REQUEST_URI"] ), $custom_urls_to_be_marked_as_404 ) ) {
    
        $module = "404";
        include_once( MODULES_PATH . $module . ".php" );
		return;
			        
	}
    
    $fxbenchmark->add_marker( "Search Module: Start Search" );
    if ( SEARCH_TYPE_IMAGE == $search_params["type"] ) {

        $search_data = [
            "engine" => "ris",
            "type" => SEARCH_TYPE_IMAGE,
        ];

        if ( $user_id && User::has_tokens_for_search() ) {

            // $$affected_issue_csi_373 = Check for unsuccessfull transaction users and show popup to redo the payment { Issue CSI-373 03-may-2018 }
            if( $affected_csi_373_has_image_tokens ){

                if ( SYSTEM::is_ajax_request() ) {

                    $ajax_status["status"] = true;
                    $ajax_status["url"] = PAGE_URL_DASHBOARD;
                    SYSTEM::flush_ajax_response();

                } else System::redirect( PAGE_URL_DASHBOARD );

            }

            if ( ! empty( $search_params["url"] ) ) {

                if ( ! Search::validate_image_url( $search_params["url"] ) ) {

                    $return_urls = true;
                    $ajax_status["images"] = Search::get_image_links_from_url( $search_params["url"] );
                    $ajax_status["image_search"] = true;

                } else {

                    $search_params["image_urls"] = [ $search_params["url"] ];
                    $image_verification_required = false;

                }

            }

            if ( ! empty( $search_params["images"]["tmp_name"][0] ) && ( ! Search::validate_image_file( $search_params["images"]["tmp_name"][0] ) ) ) {

                @unlink( $search_params["images"]["tmp_name"][0] );
                $return_urls = true;
                $ajax_status["image_error"] = true;

            }

            if ( empty( $return_urls ) ) {

                if ( $image_verification_required ) {

                    if ( ! SCF::validate_google_recaptcha() ) {

                        $_SESSION["extract"]["error_messages"][] = "Verification failed. Please try again.";

                    } else Search::queue_image_search( $search_params );

                } else Search::queue_image_search( $search_params );

                $redirect_page = PAGE_URL_DASHBOARD . "?section=ris_intermediate";
                //$redirect_page = RELATIVE_URL . "reverse-image-search-intermediate/";

            }

        } elseif ( ! $user_id ) {

            $image_token = md5( time() . "_" . rand( 1000000, 9999999 ) );
            if ( ! empty( $search_params["images"]["tmp_name"][0] ) && ( Search::validate_image_file( $search_params["images"]["tmp_name"][0] ) ) ) {

                $image_search_id = Search::queue_image_search( $search_params );
                $redirect_page = RELATIVE_URL . "image-upload/?token={$image_token}";

            } else $redirect_page = RELATIVE_URL . "ris-membership-levels/";

            $_SESSION["image_token"][ $image_token ] = [
                "pending_image_id" => ! empty( $image_search_id ) ? $image_search_id : null,
            ];

        } else $redirect_page = RELATIVE_URL . "ris-membership-levels/";

    } elseif ( ! $user_id && ! empty( $search_params["person_id"] ) ) {
       
        if ( ( ! defined( "DEV_SKIP_PROGRESS_PAGE" ) || empty( DEV_SKIP_PROGRESS_PAGE ) ) && isset( $_SERVER["HTTP_REFERER"] ) && preg_match( "/socialcatfish.com\/search\//i", $_SERVER["HTTP_REFERER"] ) ) {

            $token = md5( rand( 10000, 99999 ) . time() );
            $_SESSION["guest_progress"][ $token ] = $search_params;
            if( $singular == "new" )  SYSTEM::redirect( PAGE_URL_GUEST_PROGRESS . "?token={$token}" );

        }
        
        if ( $data = $search_params["tpd_request"] ? ( ( $data = Search::parse_search_parameters( $search_params ) ) ? $data["cached_data"] : [] ) : Search::get_cached_person( $search_params["person_id"] ) ) {

            $_SESSION["search_params"] = $search_params;
            $search = [ "engine" => "pipl" ];
            $data = Search::process_fields( $data );
            $query_data = $data["results"][0];

            if ( empty( $post_data["gp_token"] ) ) {

                if( $singular != "new" ) $module = "singular";
                else $module = "register";
                include_once( MODULES_PATH . $module . ".php" );

            } else {

                $search["results"] = $data["results"];
                $redirect_page = BASE_URL . "person/" . SYSTEM::sanitize( $data["results"][0]["name"] ?: $search_params["query"] ) . "-{$search_params["person_id"]}/" . $singular_param;

            }

        } else {

            $module = "404";
            include_once( MODULES_PATH . $module . ".php" );

        }

    } elseif ( $search_data = Search::parse_search_parameters( $search_params ) ) {
       


        $fxbenchmark->add_marker( "Search Module: Search Parameters Parsed" );

        if ( empty( $search_params["sid"] ) && empty( $user_submitted_search ) ) {

            $_SESSION["extract"]["user_submitted_search"] = true;

            // Show ReCaptcha for every 5 searches after the user has done 250 searches
            //*********** Error Line Here */
            if ( empty( $search_params["person_id"] ) && ! empty( $social_search_show_recaptcha ) && ! SCF::validate_google_recaptcha() ) {

                if ( SYSTEM::is_ajax_request() ) {

                    $ajax_status["status"] = true;
                    $ajax_status["url"] = PAGE_URL_DASHBOARD;

                    SYSTEM::flush_ajax_response();

                } else {


                    $_SESSION["recaptcha_link"]= BASE_URL."search.html?recaptcha=1" ;
                    $_SESSION["search_param_copy"]= $search_params;
                    SYSTEM::redirect( RELATIVE_URL."recaptcha" );
                    die();

                }

            }

        }

        $get_search_params  = Search::get_cache_search_params( $search_params['sid'] );

        $popular_search = Search::popular_search();
        // $$affected_issue_csi_373 = Check for unsuccessfull transaction users and show popup to redo the payment { Issue CSI-373 03-may-2018 }
        if( ! empty( $affected_csi_373_has_regular_tokens ) ){

            if ( SYSTEM::is_ajax_request() ) {

                $ajax_status["status"] = true;
                $ajax_status["url"] = PAGE_URL_DASHBOARD;
                SYSTEM::flush_ajax_response();

            } else System::redirect( PAGE_URL_DASHBOARD );

        }

        // ABTest: Business Premium API Plan
        //$business_premium_plan_user = ! empty( $user_data["active_plans"] ) && is_array( $user_data["active_plans"] ) && in_array( PLAN_UNLIMITED_SOCIAL_BUSINESS_API, $user_data["active_plans"] );
        //if ( $business_premium_plan_user ) $search_data["params"]["premium_request"] = true;

        if( ! empty( $paused_account_regular ) && in_array( $search_data["type"], [ SEARCH_TYPE_PHONE, SEARCH_TYPE_NAME, SEARCH_TYPE_EMAIL, SEARCH_TYPE_USERNAME ] ) && ! SYSTEM::is_ajax_request() && $user_id ){
            System::redirect( PAGE_URL_DASHBOARD );
        }

        if( ! empty( $paused_account_ris ) && $search_data["type"] == SEARCH_TYPE_IMAGE && ! SYSTEM::is_ajax_request() && $user_id ){
            System::redirect( PAGE_URL_DASHBOARD );
        }

        // ReCaptcha for Guest Users
        // Disabled temporarily
        /*
        if ( ! $user_id ) {

            if ( $guest_captcha_test ) {

                unset( $_SESSION["search_token"][ $input_get->search_token ] );
                if ( ! SCF::validate_google_recaptcha() ) {

                    $ajax_status["status"] = true;
                    $ajax_status["url"] = BASE_URL . "search/" . SYSTEM::sanitize( $search_data["query"] ) . "-0/";

                    if ( SYSTEM::is_ajax_request() ) {

                        SYSTEM::flush_ajax_response();

                    } else {

                        SYSTEM::redirect( $ajax_status["url"] );

                    }

                }

            } else {

                if ( empty( $search_data["cached_data"] ) ) {

                    $ip_usage = SCF::get_ip_api_usage( $_SERVER["REMOTE_ADDR"] );
                    if ( $ip_usage > 0 && $ip_usage % 5 == 0 ) {

                        $search_token = md5( microtime( true ) );
                        $_SESSION["search_token"][ $search_token ] = $search_params;
                        $redirect_url = PAGE_URL_CAPTCHA_PAGE . "?search_token={$search_token}";

                        if ( SYSTEM::is_ajax_request() ) {

                            $ajax_status["status"] = true;
                            $ajax_status["url"] = $redirect_url;

                            SYSTEM::flush_ajax_response();

                        } else {

                            SYSTEM::redirect( $redirect_url );

                        }

                    }

                }

            }

        }
        */
        
        $search = Search::do_search( $search_data );

        $fxbenchmark->add_marker( "Search Module: Executed Do Search" );
          
		if ( empty( $search["optout"] ) ) {

	        $full_name_arr =  explode( " ",  $search_data["query"] );
	        $first_name = $full_name_arr[0];
	        $behindTheName = new BehindTheNameAPI\BehindTheNameAPI( BEHIND_THE_NAME_API_KEY );
	        $behindTheName->search($first_name);
	        if(SEARCH_TYPE_NAME == $search_data["type"]){
	            $_SESSION["cr_search"] = $search_data["query"];
	        }
	        $fxbenchmark->add_marker( "Search Module: Executed BTN API" );

		}
        
        ## Add phone number data to visited areacode pages database table
        if( SEARCH_TYPE_PHONE == $search_data["type"] && ! SYSTEM::is_ajax_request() && $user_id ){

            $searched_query_for_visited_pages = preg_replace("/[^0-9]/", '', $search_data["query"]);
            if( AreaCode::is_valid_prefix_for_areacode( substr(  $searched_query_for_visited_pages, 3, 3), substr(  $searched_query_for_visited_pages, 0, 3) ) ){

                $areacode_levels_by_phone = [ "level2"  => substr(  $searched_query_for_visited_pages, 0, 3), "level3"  => substr(  $searched_query_for_visited_pages, 3, 3), "level4"  => substr(  $searched_query_for_visited_pages, 6, 2)];
                if( ! Areacode::previously_visited_areacode_page_found( $areacode_levels_by_phone ) ){

                    Areacode::add_new_previously_visited_areacode_pages( $areacode_levels_by_phone );
                    PageCache::deleteAreacodeCache( $areacode_levels_by_phone );

                }

            }

        }
        
        if ( $search["result_count"] ) {
           
            if ( $search["result_count"] == 1 && empty( $search_params["person_id"] )) {

                if ( empty( $search["results"][0]["id"] ) ) {

                    $cached_search = Search::cache_search( $search["cache_id"] );
                    $person_id = $cached_search["results"][0]["id"];

                } else $person_id = ( ( isset( $search_data["tpd_id"] ) && ! empty( $search_data["tpd_id"] ) ) ? $report_page_id_flag[ $search_data["tpd_id"] ] : "" ) . $search["results"][0]["id"];

                $search_params = [
                    "person_id" => $person_id,
                    "query" => $search_data["query"],
                    "type" => $search_data["type"],
                    "tpd_request" => $search_params["tpd_request"],
                ];

                $search_data = Search::parse_search_parameters( $search_params );

                // Identify Search Pointer Requests which has only 1 result
                $search_data["single_result_search_pointer_request"] = 1;

                $search = Search::do_search( $search_data );

                if( $singular != "new" ) $url_param = "?search=new";
                else $url_param = "";
                $redirect_page = BASE_URL . "person/" . SYSTEM::sanitize( $search["results"][0]["name"] ?: $search_params["query"] ) . "-{$person_id}/" . $url_param;

                if ( ! $user_id ) {

                    $token = md5( rand( 10000, 99999 ) . time() );
                    $_SESSION["guest_progress"][ $token ] = $search_params;
                    if( $singular == "new" ) $redirect_page = PAGE_URL_GUEST_PROGRESS . "?token={$token}";

                }

            } elseif ( $search["result_count"] == 1 && SEARCH_TYPE_PHONE == $search_data["type"] && empty( $search["results"][0]["id"] ) ) {

                $search_data = Search::parse_search_parameters( $search_params );
                $search = Search::do_search( $search_data );

            }
            $search = Search::process_fields( $search );
            if(!empty( $search_params["sid"] )){

                if($_SESSION["last_search_params"]["email"]){
                    if(!empty( $_SESSION["last_search_email"])) unset($_SESSION["last_search_email"]);
                    $last_search_email = $_SESSION["last_search_params"]["email"];
                    foreach($search["results"] as $key => $search_result){
                        if ( empty( $search_result["name"] ) ) continue;
                        $_SESSION["last_search_email"][$last_search_email][] = $search_result["id"];
                    }
                }

                else {

                    if(!empty( $_SESSION["last_search_email"])) unset($_SESSION["last_search_email"]);
                }

            }
            else {
                
                if($search_params["type"] == SEARCH_TYPE_EMAIL ){
                    if(!empty( $_SESSION["last_search_email"])) unset($_SESSION["last_search_email"]);
                    $last_search_email = $search_params["query"];
                    $_SESSION["last_search_email"][$last_search_email][] = $search_params["person_id"];
                    $_SESSION["last_search_params"]["email"] = $last_search_email;

                }

            }

        }

        if( !empty( $search_data["params"] ) ) $_SESSION["last_search_params"] =  $search_data["params"];
        if( !empty( $search_data["type"] ) ) $_SESSION["last_search_type"] =  $search_data["type"];
        if( !empty( $search_data["cached_data"]["cache_id"] ) ) $_SESSION["last_search_id"] =  $search_data["cached_data"]["cache_id"];

        $_SESSION["extract"]["search_query"] = $search_data["query"];
        $current_search_type = $search_data["type"];

        if ( $search["optout"] ) {

            $search["results"] = [];
            $search["result_count"] = 0;

        }

        // 404 Selected IDs
        $blocked_404_results = [ 3543043, 3717469, 3363789, 3462707, 3869318, 3869269, 3366263, 2046402, 3540659 ];
        if ( in_array( $search["cache_id"], $blocked_404_results ) ) {

            header( "{$_SERVER["SERVER_PROTOCOL"]} 404 Not Found" );

        }

        $search_engine_id = $search_engine_type[ $search_data["engine"] ] ?? null;

        if ( $user_id ) {

              if( SYSTEM::checkNullEmpty( $search_data["params"]["state"])){
                $plan_id = Membership::get_user_plan_id( $user_id, PLAN_CRIMINAL_RECORDS_ADDON)["id"];
                if($plan_id == 0){
                $last_name = $search_data["params"]["last_name"];
                $first_name = $search_data["params"]["first_name"];
                $state = $search_data["params"]["state"];
                $params =  [
                    "first_name" => $first_name,
                    "last_name" => $last_name,
                    "state" => $state,               
                    "search_type" => "CriminalSearch",
                    "fields" => '["criminal"]',
                    "reference" => 'teaser/nonbillable',
                    "dob" => $dob,];
               
                // Fetch Records from the API            
                $ds_idi = new \DataSource\IDI( IDI_CLIENT_ID, IDI_SECRET_KEY, DEBUG );
                $records = $ds_idi->runSearch( $ds_idi->mapParams( $params ) );

                CriminalRecords::save($records["data"]);
                $filters = [
                    "full_name" => $first_name." ".$last_name ,
                    "state" => $state,
                    "dob" => $dob,
                ];
                $records = CriminalRecords::search($filters);
                if(!$records){

                }else{
                $field_counts["criminal_records"] = count( $records );
                $filters["count"] =count( $records );
                $token = md5( uniqid() );
                $_SESSION["cr_count"] =  count( $records );
                $_SESSION["cr_tokens"][ $token ] = [
                    "query" => $full_name,
                    "filters" => $filters,

                ];
                $plan_id = Membership::get_user_plan_id( $user_id, PLAN_CRIMINAL_RECORDS_ADDON)["id"];
                if($plan_id == 0){
                    $plan_id = 1;
                }
                if($user_has_criminal_tokens_available){$purchase_status=1;$subscibed = true; }else{$purchase_status=0;}

                $existing_id = CriminalRecords::get_id_if_report_exists($user_id, $_SESSION["cr_tokens"][ $token ]["filters"],$user_data["user_level"]);

                if($existing_id == 0){
                        $id = CriminalRecords::add_user_search( $user_id, $plan_id, $_SESSION["cr_tokens"][ $token ]["filters"],$purchase_status,$user_data["user_level"]  );
                }else{
                    $id = $existing_id;
                }
                    $_SESSION["cr_id"] =  $id;
                    $_SESSION["update_cr"] = true;
            }
            }
            }

            if(isset($_POST["search_type"]) && $baseline_track["save"] == false && $_POST["search_type"] >= 0 && array_key_exists($_POST["search_type"], $tracking_search_types)) {
                SS_baseline::save_baseline_tracking($_POST["search_type"], date("Y-m-d H:i:s"), $search["result_count"], (time() - $baseline_track["start_time"]), 0);
                $baseline_track["save"] = true;
            }
            if( isset($_POST["search_type"]) && $_POST["search_type"] >= 0  ) Action_log::save_log( "search:".$_POST["search_type"], $baseline_track["start_time"], print_r($search_params, true) );
            if ( 1 == $search["result_count"] ) {
                
                $pre_page_loading = true;

                // AB testing for premium data upsell
                $_SESSION["ab_premium_upsell"] = $abtester->get_experiment( "premium_modal", session_id(), SYSTEM::bot_detected() ? "standard" : "standard" );

                $include_popup[] = "tracking_on";
                $include_popup[] = "tracking_off";
                $include_popup[] = "rating-report";
                $include_popup[] = "rating-success";
            
                // Dr.Phil Upsell Popup
                if ( User::current_user_has_purchased_plan( PLAN_DR_PHIL_TRIAL_PLAN ) ) {

                    if ( ! User::current_user_has_purchased_any_of_the_plans( [ PLAN_DR_PHIL_SPECIAL_OFFER, PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL, PLAN_UNLIMITED_IMAGE_5_DAY_TRIAL ] ) ) {

                        $_SESSION["include_dr_phil_popup"] = true;

                    }

                }

                $tpd_report = Search::parse_tpd_id( $search_params["person_id"], false );

                $save_cache_key = $tpd_report ? $tpd_report["id"] : ( ( SEARCH_TYPE_PHONE != $search_data["type"] ) ? $search["cache_id"] : ( ! empty( $search["results"][0]["id"] ) ? $search["results"][0]["id"] : null ) );

                $charge_token = Search::save_results_for_user( $user_id, $save_cache_key, $tpd_report, $search["results"][0]["name"] );

				if ( ! empty( $_SESSION["privacy_lock_first_visit"] ) ) unset( $_SESSION["privacy_lock_first_visit"] );

				$include_map_scripts = true;

				if ( ! empty( $_SESSION["user"]["pre_redesign_user"] ) ) {

					if ( empty( $_SESSION["user"]["newlook_popup"] ) ) $include_popup[] = "new_look";
					$include_popup[] = "switch_classic";

				}

                if(SEARCH_TYPE_PHONE == $search_data["type"] ){
                    if(!empty($search_data["query"])){
                        $phone_number_raw = preg_replace('/[^0-9]/', '', $search_data["query"]);
                        $pwned_data = PWNED::check_PWNED( $phone_number_raw );
                    }                    
                }elseif(SEARCH_TYPE_EMAIL == $search_data["type"] ){ 
                       $pwned_data = PWNED::check_PWNED($search_data["query"],'email' );
                }
               
				$sub_page = "search/single.php";                
				$premium_content_user = false;

                if($_SESSION["cr_count"]>0 && ! empty( $_SESSION["cr_tokens"])  && $_SESSION["update_cr"] != true){
                    CriminalRecords::update_user_search( $user_id, null, $_SESSION["cr_id"]);
                    $_SESSION["cr_link"]= RELATIVE_URL . "criminal_report/{$_SESSION["cr_tokens"][ array_keys($_SESSION["cr_tokens"])[0]]["query"]}-{$_SESSION['cr_id']}";
                    unset( $_SESSION["cr_tokens"]);
                    unset( $_SESSION["cr_count"]);
                }

                if ( $search_data["type"] == SEARCH_TYPE_NAME && $charge_token ) {

                    $search_data["type"] = Search::get_sid_type_for_user( $user_id, $save_cache_key );
                }

                if ( ! $charge_token || User::has_tokens_for_search() ) {

                    if ( $charge_token ) User::add_remove_tokens( $user_id, [ $search_types[ $search_data["type"] ] => 1 ], "remove" );

                    if ( $tpd_report ) {

                        $premium_content_user = false;
                        $result = array_shift( $search["results"] );

                    } else {

                        if ( $section == 'premium_data' || $section ==  'premium_data_monthly' ) {

                            $premium_monthly  = ( $section ==  'premium_data_monthly' )? "1" : "0";
                            $premium_requested = Search::premium_data_request_log( $user_id , $save_cache_key, $premium_monthly );

                            if ( $premium_requested ) {

                                $search_redirect_uri = SYSTEM::sanitize( $search["results"][0]["name"] ) . "-" . ( $search_data['actual_record_id'] ?? $search['cache_id'] );

                                // Tokenized premium data
                                if ( ! $premium_monthly && ! Search::is_premium_user_for_content( $user_id, $save_cache_key ) && $user_tokens["premium"]["available"] ) {

                                    User::add_remove_tokens( $user_id, [ "premium" => 1 ], "remove" );
                                    Search::update_premium_data_requests( $premium_requested , 2 );                                    
                                    SYSTEM::redirect( "/person/" . $search_redirect_uri . "/" );

                                }

                                $query_data = [
                                    "type_id" => $search_data["type"],
                                    'premium_request_id' => $premium_requested,
                                    'premium_monthly' => $premium_monthly,
                                    'redirect_uri' => $search_redirect_uri,
                                    "query" => $search_data["query"],
                                    "name" => $search["results"][0]["name"],
                                    "age" => $search["results"][0]["age"],
                                    "image" => ( ! empty( $search["results"][0]["images"][0] ) ? SCF::get_image_url_source( $search["results"][0]["images"][0] ) : "" ),
                                    "location" => ( ! empty( $search["results"][0]["partial_locations"][0] ) ? $search["results"][0]["partial_locations"][0] : "" ),
                                ];     

                                $module = "register";
                                include_once( MODULES_PATH . $module . ".php" );
                            }
                        }

                        $search = Search::check_and_update_phone_results( $search, $user_id, $save_cache_key );

                        // IDI/PDL Add Premium Content Counts and Enrich Data
                        if ( in_array( $search_engine_id, [ SEARCH_ENGINE_IDI, SEARCH_ENGINE_PDL ] ) ) {

                        	$full_data_provider = true;
                        	
                        	if ( ! empty( $search["results"][0]["teaser_request"] ) ) {
                        	
                        		$search = Search::update_teaser_data( $search );	
                        		
							}
							
                        	if ( $search["partial_data"] ) {

                        		$search["results"][0] = Search::enrich_data_for_result( $search_engine_id, ( empty( $search_data["actual_record_id"] ) ? $search["cache_id"] : $search_data["actual_record_id"] ), $search["results"][0] );
                                $search["partial_data"] = 0;

							}

                        	$premium_data_counts = Search::get_premium_data_counts( Search::get_result_data_point_count( $search["results"][0] ) );
                        	$search["results"][0]["premium_data"] = ! empty( $search["results"][0]["premium_data"] ) ? array_merge( $search["results"][0]["premium_data"], $premium_data_counts ) : $premium_data_counts;

						}

                        $premium_content_user = ( ! empty( $search["results"][0]["premium_data"] ) ) ? Search::is_premium_user_for_content( $user_id, $save_cache_key ) : false;

                        // AB Test 5% premium START CSI-6419
                        $_SESSSION["user_gets_premium_ab"] = false;

                        // if (!is_null(User::get_custom_data_user())) {
                        //     $custom_data_id = User::get_custom_data_user();
                            
                        //     $_SESSSION["user_gets_premium_ab"] = ABtest::custom_data_return($custom_data_id);
                        //     $premium_content_user = $_SESSSION["user_gets_premium_ab"];

                        // } else {

                        //     if (rand(1,100) > 95) {
                            
                        //         User::set_custom_data_user("ab_premium_5"); 
                                
                        //     } else {
                                
                        //         User::set_custom_data_user("ab_premium_95");
                                
                        //     }

                        // }
                        // AB Test END CSI-6419

                        
                        if ( $premium_content_user ) {
                            if ( ! empty( $full_data_provider ) ) {

                                if ( $search_data["engine"] == "idi" ) {
                                    
                                    if ( Search::check_idi_premium_data( $search_data["cached_data"]["cache_id"] ) ) {
                                    	
				                        $params = [ "pidlist" => [ str_ireplace( "idi_", "", $search["results"][0]["search_pointer"] ) ] ];
				                        $ds_idi = new \DataSource\IDI( IDI_CLIENT_ID, IDI_SECRET_KEY, DEBUG );
				                        $records = $ds_idi->runSearch( $ds_idi->mapParams( $params ), true, true ); 
	                                    search::insert_idi_premium_data( $search["cache_id"], $records["data"][0] );
	                                    
									}
                                    
					                $result_idi = Search::get_idi_premium( $search_data["cached_data"]["cache_id"] );
					                if ( $result_idi ) {
					               
					               		$search["results"][0] = array_merge( $search["results"][0], $result_idi ); 	
					                	
									}
                                    
								}
                                                            	
                            	$result = array_shift( $search["results"] );

							} else {

	                            $result = Search::get_updated_premium_data( $save_cache_key , $search );
	                            $premium_only_data = Search::get_premiumonly_dataset( $save_cache_key );

	                            if ( empty( $premium_only_data ) ) {

	                                $result = Search::get_updated_premium_data_monthly( $save_cache_key , $search );
	                                $premium_only_data = Search::get_premiumonly_dataset( $save_cache_key );

	                            }

							}

                        } elseif ( ! empty( $search["partial_data"] ) ) {

                            $fxbenchmark->add_marker( "Search Module: Before Updating Partial Search" );
                            $result = Search::update_partial_result( $search );
                            $fxbenchmark->add_marker( "Search Module: Partial Search Updated" );
                            OptOut::source_id_auto_optout_all();

                        } else {

                        	$result = array_shift( $search["results"] );

                        	// if ( $full_data_provider ) {

                        	// 	$result = Search::remove_premium_data_points( $result, DATA_POINT_VALUE_LIMIT );

							// }

						}

                        if ( 255 == $user_data["user_level"] && ! $premium_content_user ) {
                           
                            $premium_only_data = Search::get_premiumonly_dataset( $save_cache_key );

                            if( ! empty( $premium_only_data ) ){

                                $result = Search::get_premium_standard_data_for_superadmin( $save_cache_key );
                                $premium_content_user = true;
                            }
                            
                            
                        }
                        unset( $result["premium_data"]["emails"] );
                        if ( ! $premium_content_user && ! empty( $result['premium_data'] ) ) {

                            $include_popup[] = "premium_data_found"; 

                        }  

                        // AB TEST CSI-6419
                        if ( $_SESSSION["user_gets_premium_ab"] ) {

                            $include_popup[] = "free_premium_data";
                            $include_popup[] = "free_premium_data_two";

                        }
                        // AB TEST CSI-6419

                        $link_types = [];
                        foreach ( $result["images"] as $image_url ) {

                            if ( $url = SCF::get_image_url_source( $image_url ) ) {

                                $result["urls"][] = $url;

                            }

                        }

                        $filtered_urls = [];
                        foreach ( $result["urls"] as $_index => $_link ) {
                            $check_url = preg_replace( "/https?\:\/\/(?:www.)(.*)/i", "\\1", str_replace( "/people/_/", "/", $_link ) );
                            if ( isset( $filtered_urls[ $check_url ] ) ) unset( $result["urls"][ $_index ] );
                            $filtered_urls[ $check_url ] = 1;
                        }

                        unset( $filtered_urls, $_index, $_link );

                        foreach ( $result["urls"] as $link ) {

                            if ( $url_type = SCF::get_url_type( $link ) ) {

                                $link_types[ $url_type ][] = $link;

                            }

                        }

                        if ( SEARCH_TYPE_PHONE == $search_data["type"] ) {

                            $phone_recording_data = Scams::get_number_recording( $search_data["query"] );

                        }

                        // Todo 10 -c Bug: Emails from last search is getting added to the report !!!
                        /* Single Results Page */
                        /*
                        if( empty( $premium_only_data ) && $_SESSION["last_search_email"] ) {

                            if( $_SESSION["last_search_params"]["email"] && in_array( $search_params["person_id"], $_SESSION["last_search_email"][$_SESSION["last_search_params"]["email"]] ) ) {

                                $result["emails"][] = key( $_SESSION["last_search_email"] );

                            }

                        }
                        */

                        // AB Test: No Premium Popup : Start

                        $_SESSION["no_premium"] = $abtester->get_experiment( "no_premium", session_id(), SYSTEM::bot_detected() ? "standard" : $_SESSION["landing_page_AB"] ? "standard" : "standard" );

                        // AB Test: No Premium popup : End

                        /** End of Pwned Data */
                        if( ! empty( $_SESSION["privacy_lock_first_visit"] ) ) unset($_SESSION["privacy_lock_first_visit"]);
                        $sub_page = "search/single.php";

                    }

                    $tracking_status = Search::get_tracking( $user_id, ! empty( $result["id"] ) ? $result["id"] : $search['cache_id'], ! empty( $tpd_report ) ? $tpd_report["type"] : "" );
                    $include_map_scripts = true;
                    $sub_page = "search/single.php";

                    OptOut::data_id_check_blocked_ids_for_report( $save_cache_key );
                    if ( OptOut::is_report_optedout( $save_cache_key ) ) {

                        SYSTEM::redirect( $_SERVER["REQUEST_URI"] );

                    }

                    if( SEARCH_TYPE_NAME == $search_data["type"] ){

                        $search_text = ! empty ( $search_data["query"] )? $search_data["query"] : $search_data["cached_data"]["query"];
                        $search_query_text = ucwords( strtolower( $search_text ) );
                        $slected_city = ! empty( $_SESSION["last_search_params"]['city']) ? ucwords( $_SESSION["last_search_params"]['city'] ) . ", " : "";
                        $slected_state = ! empty( $_SESSION["last_search_params"]['state'] )? $_SESSION["last_search_params"]['state'] : "";
                        $title_location = ( empty ( $slected_city ) && empty( $slected_state ) )? "" : " - " . $slected_city . $slected_state;

                        $page_title = $search_query_text . $title_location . " - SocialCatfish.com";
                        $page_description = $search_query_text . "'s Public Records. Uncover social media accounts, images, age, phone numbers, emails, criminal records and more on SocialCatfish.com.";
                    }

                } else {

                    Search::delete_results_for_user( $user_id, $save_cache_key );
                    $redirect_page = RELATIVE_URL . "membership-upgrade/";
                    $_SESSION["extract"]["search_query"] = $search_data["query"];
                    $_SESSION["extract"]["out_of_credit"] = true;

                }

                //**** Unclaimed Money search */
                $unclaimed_money_result = []; $i=0;
                if($search_data["type"] == SEARCH_TYPE_NAME ) {
                    $arr_name = explode(" ", $result["name"]);
                    $name_last = array_pop($arr_name);
                    $first_name = implode (" ", $arr_name);                    
                    foreach($result["states"] as $state_key => $nn){
                        
                        $tmp_unclaimed_result = UnclaimedFunds::get_unclaimed_data( $first_name, $name_last, $state_key );
                        if( ! empty($tmp_unclaimed_result["data"]) ) {
                            $unclaimed_money_result[$i] = $tmp_unclaimed_result;
                            $i++;
                        }
                    }
                }
                if( ! empty( $unclaimed_money_result ) ) $include_popup[] = "unclaimed_money_popup";
                if ( empty( $redirect_page ) && empty( $search_params["person_id"] ) && SEARCH_TYPE_PHONE != $search_data["type"] ) System::redirect( BASE_URL . "person/" . SYSTEM::sanitize( $result["name"] ) . "-{$result["id"]}/" );

                /**  get the latest location */
                if(!empty($result["record_dates"]["locations"])){
                    foreach($result["record_dates"]["locations"] as $locationID => $location){
                        $location_names = explode(",", $result["locations"][$locationID]);
                        for ($i=count($location_names)+1; $i > -1; --$i) {
                            if(!empty($location_names[$i])) $locationStat = LOCATIONS::getStatByStateName(trim($location_names[$i]));

                            if (!empty($locationStat)) {
                                $covidstat = $locationStat;
                                $covid_data = true;
                                break 2;
                            }
                        }
                    }
                }

                if (!empty($covid_data)) {

                    if (!empty($covidstat["usa"]["lastUpdated"])) {
                        $utc_date = DateTime::createFromFormat(
                            'Y-m-d H:i:s',
                            $covidstat["usa"]["lastUpdated"],
                            new DateTimeZone('UTC')
                        );

                        $EST_date = $utc_date;
                        $EST_date->setTimeZone(new DateTimeZone('EST'));
                        $usa_time = $EST_date->format('Y-m-d g:i A');
                    }

                }

                $user_covid_feedback_status = LOCATIONS::user_feedback_status();

            } elseif ( empty( $search["result_count"] ) ) {

                $search_query_from_url = Search::get_search_query_from_url();
                
            //CSI-5307 
           // Search::no_results_IPQ_Pwned_check();
            // CSI-5307 end.
                if ( isset( $remove_head_foot ) ) $remove_head_foot = false;
                Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "No Results::" . $search_types[( empty( $search_data["type"] ) ? 0 : $search_data["type"] )] . " :: ". ( ! empty( $search_data["query"] ) ? $search_data["query"] : "" ), ["errors", "no results " . $search_types[( empty( $search_data["type"] ) ? 0 : $search_data["type"] )] , ( ! empty( $search_data["query"] ) ? $search_data["query"] : "" ) ] );

                //$sub_page = "search/single.php";
                $sub_page = "search/no_results.php";
                $behavior_url_custom_type = "no_results";

            }

            else {

                $sub_page = ( SEARCH_TYPE_USERNAME == $search_data["type"] ) ? $results_user : $results;
                //$sub_page = "search/results.php";

                if ( empty( $search_params["sid"] ) ) {
                    //baseline_track

                    Search::save_search_results_for_user( $user_id, $search["cache_id"] );
                    $redirect_page = BASE_URL . "search/" . SYSTEM::sanitize( $search_data["query"] ) . "-{$search["cache_id"]}/";
                    if ( ! SYSTEM::is_ajax_request() ) System::redirect( $redirect_page );

                }

            }

        } else {

            if ( 1 == $search["result_count"] ) {

                $query_data = [
                    "type_id" => $search_data["type"],
                    "query" => $search_data["query"],
                    "name" => $search["results"][0]["name"],
                    "age" => $search["results"][0]["age"],
                    "image" => ( ! empty( $search["results"][0]["images"][0] ) ? $search["results"][0]["images"][0] : "" ),
                    "location" => ( ! empty( $search["results"][0]["partial_locations"][0] ) ? $search["results"][0]["partial_locations"][0] : "" ),
                ];

                //$_SESSION["search_params"] = $search_params;
                //$redirect_page = RELATIVE_URL . "membership-levels/";

                if ( ! SYSTEM::is_ajax_request() ) {

                    $module = "register";
                    include_once( MODULES_PATH . $module . ".php" );

                }

            } elseif ( empty( $search["result_count"] ) ) {

                $search_query_from_url = Search::get_search_query_from_url();
              
            //CSI-5307 
            ////Search::no_results_IPQ_Pwned_check();
            // CSI-5307 end.
                if ( isset( $remove_head_foot ) ) $remove_head_foot = false;

                    $sub_page = "search/no_results.php";
                    $behavior_url_custom_type = "no_results";
    
                    if ( ! SYSTEM::is_ajax_request() ) {
    
                        header( "{$_SERVER["SERVER_PROTOCOL"]} 404 Not Found" );
                    }
                

               
            }

            else {

                $sub_page = ( SEARCH_TYPE_USERNAME == $search_data["type"] ) ? $results_user : $results;

            }

        }

        // Remove associated people for some names
        Search::remove_associate_people( $search, $result );
       
        // IDI and Flat file Providers - Limit data on Search Pointers
        if ( $search["result_count"] > 1 && SEARCH_ENGINE_IDI == $search_engine_id ) {

        	// foreach ( $search["results"] as &$_result ) {

        	// 	$_result = Search::remove_premium_data_points( $_result, DATA_POINT_VALUE_LIMIT );

			// }
			// unset( $_result );

		}

        if ( 1 < $search["result_count"] || ( $search["result_count"] && SEARCH_TYPE_PHONE == $search_data["type"] ) ) {

            $search_query_text = ucwords( strtolower( $search_data["query"] ) );
            $search_city_text = ( ! empty( $search_data["city"] ) ? " in {$search_data["city"]}" : "" );
            $search_phone = ( ! empty( $search_data['cached_data']['results'][0]['phones'][0] ) ? "{$search_data['cached_data']['results'][0]['phones'][0]}" : "" );

            if ( preg_match( "/([0-9]{3})([0-9]{3})([0-9]{4})\$/m", preg_replace( "/[^0-9]/", "", $search_query_text ), $formatted_phone_number ) ) $formatted_phone_number = implode( "-", array_slice( $formatted_phone_number, 1 ) );
            else $formatted_phone_number = $search_query_text;
            $phone_number_location = AreaCode::phone_number_info( $formatted_phone_number );

            $search_city_set = [];
            foreach ( $search["cities"] as $city => $count ) {

                $search_city_set[] = $city;
                if ( count( $search_city_set ) >= 2 ) break;

            }
            $search_city_set = ! empty( $search_city_set ) ? ( implode( ", ", $search_city_set ) ) : "";

            $slected_city = ! empty( $_SESSION["last_search_params"]['city']) ? ucwords( $_SESSION["last_search_params"]['city'] ) . ", " : "";
            $slected_state = ! empty( $_SESSION["last_search_params"]['state'] )? $_SESSION["last_search_params"]['state'] : "";
            switch ( $search_data["type"] ) {

                case SEARCH_TYPE_NAME:

                    $squery_state = SYSTEM::request( "sstate", "" );
                    $squery_city = SYSTEM::request( "scity", "" );
                    $squery_for_title = ! empty( $squery_state ) ? ucwords( $squery_state ) : "";
                    $squery_for_title = ! empty( $squery_city ) ? ucwords( $squery_city ) . " in " . $squery_for_title : $squery_for_title;

                    //$page_title = sprintf( "%s - %s %s %s phone, email, social & more Socialcatfish.com", $search_query_text, $squery_for_title, $slected_city , $slected_state );
                    //$page_description = sprintf( "%d results - We found %s in 28 states including %s. See %s's contact info, dating, social and other websites - Click now to see results - Socialcatfish.com", $search["result_count"], $search_query_text, $search_city_set, $search_query_text );
                    //$page_description = sprintf( "We found %d results for %s in 28 states. Check contact info, addresses, social profiles and more! Click here to see results on Social Catfish.", $search["result_count"], $search_query_text, $search_city_set, $search_query_text );
                    $page_title = sprintf( "%s (%d) %s %s %s phone, social media, email - Socialcatfish.com", $search_query_text, $search["result_count"], $squery_for_title, $slected_city , $slected_state );
                    $page_description = sprintf( "We found %d results for %s in 28 states. Check contact info, addresses, social profiles and more! Click to uncover results on Social Catfish.", $search["result_count"], $search_query_text, $search_city_set, $search_query_text );
                    $name_search_results = true;
                    break;

                case SEARCH_TYPE_USERNAME:

                    $page_title = sprintf( "%s online profiles and pictures - Socialcatfish.com", $search_query_text, $search_city_text );
                    $page_description = sprintf( "Find %s's online social networks, pictures, job information, phone numbers and even possibly dating websites for %s", $search_query_text, $search_query_text );
                    break;

                case SEARCH_TYPE_PHONE:
                    $amp_page = false;

                    $search_state_text = ! empty( $search["states"] ) ? key( $search["states"] ) : "";
                    $search_state_text = $search_state_text ? ( " in " . ( ! empty( $list_of_states[ $search_state_text ] ) ? $list_of_states[ $search_state_text ] : $search_state_text ) ) : "";
                    $search_phone_type = ! empty( $search["meta"]["line_type"] ) ? $search["meta"]["line_type"] : "cell phone";

                    $page_title = sprintf( "Owner found for %s%s - Socialcatfish.com", $search_query_text, $search_city_text );
                    $page_description = sprintf( "%s Get caller name, carrier and location for %s number %s%s.", $search_query_text, $search_phone_type, $search_query_text, $search_state_text );
                    break;

            }

        }
      
    } elseif( SYSTEM::request_get("emailquery") ) {
      
    	$email_query = urldecode( base64_decode( SYSTEM::request_get("emailquery") ) );
    	$blocked_email = OptOut::is_email_data_id_blocked( $email_query );

        if ($blocked_email ) {
            $module = "404";
            include_once( MODULES_PATH . $module . ".php" );

        }else{
            Search::no_results_IPQ_Pwned_check(null,$email_query);
           
            if (empty($user_id)  && ($_SESSION['IPQresult']||$_SESSION['Pwnedresult'])) {
                $sub_page = "search/no_results_signedout.php";      
            } elseif ($user_id && ($_SESSION['IPQresult']||$_SESSION['Pwnedresult']) ) {
         
            //$unlimited_ss_plan_info = Membership::get( PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL);              
            //$pwned_count = PWNED::check_PWNED_status( $email_query);

            //if(!empty( $pwned_count)){
            //    $pwned_no_result = $pwned_count;
           // }

          
	        /** Show pwned data for email no results CSI-5204  */
	        $result["name"] = $email_query;
	        $result["emails"][] = $email_query;
	        $premium_only_data["emails"][] =  $email_query;
	        $search_data["type"] = SEARCH_TYPE_EMAIL;
	        $pre_page_loading = true;
	        $pwned_data = $_SESSION['Pwnedresult']?PWNED::check_PWNED( $email_query):null;
	        $result["record_dates"][] = $pwned_data->firstDate;
	        $result["record_dates"][] = $pwned_data->lastDate;
	        $disable_feedback = true;
	        $disable_criminal_records_popup = true;
	        $tracking_off = true;
	        $sub_page = "search/single.php";
            $pwnd_and_IPQ_only = true;

		}else{
            $sub_page = "search/no_results.php";  
        }
    }
    } elseif(SYSTEM::request_get("phonequery")) {
            $phone_query = urldecode( base64_decode( SYSTEM::request_get("phonequery") ) );
            Search::no_results_IPQ_Pwned_check($phone_query);
              if (empty( $user_id) && ($_SESSION['IPQresult']||$_SESSION['Pwnedresult']) ) {
                $sub_page = "search/no_results_signedout.php";      
            } elseif ($user_id  && ($_SESSION['IPQresult']||$_SESSION['Pwnedresult']) ) { 

        	        /** Show pwned data for email no results CSI-5204  */
	        $result["name"] = $phone_query;
	        $result["phones"][] = $phone_query;
	        //$premium_only_data["emails"][] =  $email_query;
	        $search_data["type"] = SEARCH_TYPE_PHONE;
	        $pre_page_loading = true;
	        $pwned_data = $_SESSION['Pwnedresult']?PWNED::check_PWNED($phone_query):null;
	        $result["record_dates"][] = $pwned_data->firstDate;
	        $result["record_dates"][] = $pwned_data->lastDate;
	        $disable_feedback = true;
	        $disable_criminal_records_popup = true;
	        $tracking_off = true;
	        $sub_page = "search/single.php";
            $pwnd_and_IPQ_only = true;
            }else{
                $sub_page = "search/no_results.php";
            }
        
    }else {

        $search = [ "engine" => "pipl", "query" => ( ! empty( $search_query ) ? $search_query : "" ) ];
        $search_query_from_url = Search::get_search_query_from_url();
        
        $last_search_param = false;
        $last_search_type = false;
        $search_types_fliped = array_flip( $search_types );
        foreach ($_SESSION["last_search_params"] as $last_search_key => $last_search_param) {
            $last_search_param = $last_search_param;
            $last_search_type = array_search($last_search_key,$search_types);
        }        

        if(empty($search_query == $last_search_param )){
            $alert_error = true;
        }
        

        if ( isset( $remove_head_foot ) ) $remove_head_foot = false;
          
            //CSI-5307 
            
 
        $sub_page = "search/no_results.php";
        header( "{$_SERVER["SERVER_PROTOCOL"]} 404 Not Found" );
        $behavior_url_custom_type = "no_results";
    
        


       

    }

    if( isset($_POST["search_type"]) && $baseline_track["save"] == false && $_POST["search_type"] >= 0  ) Action_log::save_log( "search:".$_POST["search_type"], $baseline_track["start_time"], print_r($search_params, true) );

    if(isset($_POST["search_type"]) && $baseline_track["save"] == false && $_POST["search_type"] >= 0 && array_key_exists($_POST["search_type"], $tracking_search_types)) {
        SS_baseline::save_baseline_tracking($_POST["search_type"], date("Y-m-d H:i:s"), $search["result_count"], (time() - $baseline_track["start_time"]), 0);
        $baseline_track["save"] = true;
    }
    // Result Counts for the Progress Page
    $field_counts = ["names"    =>  0 ];
    $results_images = [];

    if ( ! empty( $search["results"] ) ) {

        $_squery_state = SYSTEM::request( "sstate", "" );
        if( ! empty( $_squery_state ) )
            $squery_state = array_search(strtolower(str_replace("-", " ", $_squery_state ) ), array_map('strtolower', $list_of_states ) );
        if( empty( $squery_state ) ) $squery_state = array_search(strtolower( $_squery_state ), array_map('strtolower', $list_of_states ) );
        $squery_city = SYSTEM::request( "scity", "" );
        if( ! empty( $squery_city ) ){

            if( empty( Search::check_if_city_exists_in_state( $squery_state, $squery_city ) ) ) $squery_city = "";

        }

        foreach ( $search["results"] as $_result_k => $_result ) {

            if( ! empty( $squery_state ) && empty( $squery_city ) ){
                if( ! array_key_exists( $squery_state, $_result["states"] ) ) unset( $search["results"][$_result_k] );
            }

            if( ! empty( $squery_city ) ){
                $remove = true;
                foreach($_result["cities"] as $sckey => $scvalue){
                    if (strpos( str_replace("-", " ", strtolower( $sckey ) ), str_replace("-", " ", strtolower( $squery_city ) ) ) !== false) {
                        $remove = false;
                    }
                }

                if( $remove )  unset( $search["results"][$_result_k] );

            }

            foreach ( $single_results_field_list_for_counts as $field_name => $field_caption ) {

                if ( ! isset( $field_counts[ $field_name ] ) ) $field_counts[ $field_name ] = 0;
                $field_counts[ $field_name ] += ( isset( $_result[ $field_name ] ) && is_array( $_result[ $field_name ] ) ) ? count( $_result[ $field_name ] ) : 0;

            }

            // Only count the name if its not a phone number.
            if ( ! empty( $_result["name"] ) && ! preg_match( "/^[0-9\(\)\- ]+\$/", $_result["name"] ) ) $field_counts["names"]++;

            if ( ! empty( $_result["images"] ) ) {

                foreach ( $_result["images"] as $_image ) {

                    if ( ! empty( $_image ) ) $results_images[] = $_image;

                }

            }

        }

        if( ! empty( $squery_state ) ){

            $search = Search::process_fields( $search );

        }

        $search_other_cities = [];
        $search_filtered_cities = [];
        $create_city_links = [];
        if( ! empty( $search["cities"] ) ){
            foreach( $search["cities"] as $_search_filtered_city => $_search_filtered_count ){

                $_city_state = explode( ",", $_search_filtered_city );
                $city_state = ! empty( $_city_state[1] ) ? trim( $_city_state[1] ) : "";
                $city_city = ! empty( $_city_state[0] ) ? trim( $_city_state[0] ) : "";

                if( ! empty( $squery_state ) ){
                    if( $squery_state == $city_state ) $search_filtered_cities[ $_search_filtered_city ] = $_search_filtered_count;
                    else $search_other_cities[ $_search_filtered_city ] = $_search_filtered_count;
                }

                if( $_search_filtered_count > $create_link_if_results_morethan ) {

                    if( ! empty( Search::check_if_city_exists_in_state( $city_state, $city_city ) ) ) $create_city_links[] = $city_city;

                }

            }

            if( ! empty( $squery_state ) ){
                ksort( $search_filtered_cities );
                ksort( $search_other_cities );

                $search["cities"] = array_merge( $search_filtered_cities, $search_other_cities );
            }

        }

    }
    if ( ! empty( $search["meta"]["names"] ) ) $field_counts["names"] += count( $search["meta"]["names"] );

    // Limit to 3 images
    $results_images = array_slice( $results_images, 0, 3 );
    foreach ( $results_images as $_index => $img ) $results_images[ $_index ] = SCF::imgcdn_url( $img );

    if(!empty($_SESSION["unclaimed-fund-landing"]["found"]) && !empty($search_params["person_id"]) && !empty($user_id)){
        $unclaimed_fund_found =  $_SESSION["unclaimed-fund-landing"]["data"];
        unset($_SESSION["unclaimed-fund-landing"]);
    }

    if ( SYSTEM::is_ajax_request() ) {

        if ( "ris" == $search_data["engine"] ) {

            $ajax_status["status"] = true;
            $ajax_status["url"] = $redirect_page ?: ( PAGE_URL_REVERSE_IMAGE_SEARCH );

        } else {

            $ajax_status["status"] = true;

            if($search_data["type"] == SEARCH_TYPE_PHONE){

                $get_optout_numbers_data = OptOut::get_optout_number_status($search_data["phone"]);
               
                if($get_optout_numbers_data){
                   
                    $ajax_status["url"] = $redirect_page ?: ( BASE_URL . "search/" . SYSTEM::sanitize( $search_data["query"] ) . "-0/" );
                }else{
                    Search::no_results_IPQ_Pwned_check();
                    
                    if($_SESSION['IPQresult']||$_SESSION['Pwnedresult']){
                        $ajax_status["url"] = BASE_URL . "person/" . urlencode(base64_encode(SYSTEM::sanitize($search_data["query"])) ) . "-p/";

                    }else{
                        $ajax_status["url"] = $redirect_page ?: ( BASE_URL . "search/" . SYSTEM::sanitize( $search_data["query"] ) . "-{$search["cache_id"]}/" . $singular_param );
                    }
                     }
            } else {

                $ajax_status["url"] = $redirect_page ?: ( BASE_URL . "search/" . SYSTEM::sanitize( $search_data["query"] ) . "-{$search["cache_id"]}/" . $singular_param );

                if ( $search_data["type"] == SEARCH_TYPE_EMAIL && empty( $search["cache_id"] ) ) {

					$blocked_email = OptOut::is_email_data_id_blocked( $search_data["query"] );

					if ( ! $blocked_email ) {
                        //CSI-6924
	                    //$pwned_data = PWNED::check_PWNED( $search_data["query"] );
                        Search::no_results_IPQ_Pwned_check();
	                    //if ( ! empty( $pwned_data ) ) {
                        if($_SESSION['IPQresult']||$_SESSION['Pwnedresult']){
	                        $ajax_status["url"] = BASE_URL . "person/" . urlencode(base64_encode($search_data["query"]) ) . "-e/";

	                    }

					}

                }
            }

            if(SYSTEM::checkNullEmpty($filters)){
                $field_counts["criminal"] = $filters["count"];
                $_SESSION["cr_count"] =  $filters["count"];
            }
            $ajax_status["counts"] = isset( $field_counts ) ? $field_counts : [];
            $ajax_status["images"] = isset( $results_images ) ? $results_images : [];

        }

        SYSTEM::flush_ajax_response();

    } elseif ( ! empty( $redirect_page ) ) System::redirect( $redirect_page );

    if ( ! empty( $search_data["cached_data"]["query"] ) ) {$velocity = Search::get_search_velocity( $search_data["cached_data"]["query"] );}
    else if ( ! empty($result["name"] ) ) $velocity = Search::get_search_velocity( $result["name"] );
  
    // ReCaptcha Implementation for Guest Users
    if ( $guest_captcha_test ) {

        $redirect_page = BASE_URL . "search/" . SYSTEM::sanitize( $search_data["query"] ) . "-" . ( ! empty( $search["cache_id"] ) ? $search["cache_id"] : "0" ) . "/";
        SYSTEM::redirect( $redirect_page );

    }

