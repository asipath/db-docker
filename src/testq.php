<?php

    if ( ! defined( "FROM_INDEX" ) ) die(); 

    if (isset($_SESSION["removed-head-foot"]) && !$user_id) {
        $remove_head_foot = true;
    }

    if ($user_id) {
        $get_plans_of_user = Membership::get_user_plans( $user_id, true );
        $_SESSION["price_changed"] = in_array($get_plans_of_user[0]['membership_id'], ['77','76'], true );
    }


    $no_index = true;
    $override_checkout = 0;
    $exclude_hire_us_button = true;
    $coupon_applied = ! empty( $_SESSION["coupon_data"] );
    $include_map_scripts = true;
    $user_credit_cards = [];
	$testcase_is_running = ! empty( $user_data["email"] ) && EMAIL_TEST_MAIL == $user_data["email"] ? true : false;
	$amazon_biling_agreement = ! empty( $post_data["ap_token"] ) ? $post_data["ap_token"] : "";
    $dashboard_membership_addon_request = SYSTEM::request( "dashboard_membership_addon_request" );
    $is_mobile_app = SYSTEM::request( "is_mobile_app" );
    $is_RIS_only = SYSTEM::request('ris_only');
    $is_mobile_app_email = SYSTEM::request( "email" );
    $is_mobile_app_session = SYSTEM::request( "_s" );
    $is_mobile_app_plan_id = SYSTEM::request( "key" );
    $is_mobile_app_key = SYSTEM::request( "_k" );
    $is_mobile_app_person_id = SYSTEM::request( "person" );
    $is_mobile_app_person_name = SYSTEM::request( "name" );
    $criminal_records_addon = SYSTEM::request("criminal_records_addon");
    $check_out = true;
    if( ! empty( $post_data["billing_phone"] ) ) $post_data["billing_phone"] = preg_replace("/[^0-9]/", "", $post_data["billing_phone"] );

    $token = ! empty( $direct_token ) ? $direct_token : $token;

    // Device Type
    $device = SYSTEM::get_device_type();

    $validated_mobile_app_user = false;
    if( $is_mobile_app ){
        //validate session  and key with sent email
        //$is_mobile_app_key
        //$is_mobile_app_session
        //$is_mobile_app_email

        $validated_mobile_app_user = true;
        if( ! empty( $is_mobile_app_email ) && $validated_mobile_app_user ) {
            $user_data = User::get_by_email( $is_mobile_app_email );
            if( ! empty( $user_data ) && ! empty( $user_data["id"] ) ){
                $user_id = $user_data["id"];
                $_SESSION["mobile_app_user"] = $user_data["id"];
                $_SESSION["mobile_app_user_data"] = $user_data;
                $user_data["walkthrough"] = 0;
            }
        }
    }

    if( ! empty( $_SESSION["mobile_app_user"] ) ) $user_id = $_SESSION["mobile_app_user"];
    if( ! empty( $_SESSION["mobile_app_user_data"] ) ) $user_data = $_SESSION["mobile_app_user_data"];


    $include_popup[] = "cvv_popup";
    $include_popup[] = "search_ready";
    if ( ! $user_id ) $include_popup[] = "signup_form";
    if ( $_SESSION["boosted_no_results"] ) $include_popup[] = "ris-no-boosted";

    /*
    $amazon_pay_ab_test = $abtester->get_experiment( "amazon_pay", session_id() );
    if ( $amazon_pay_ab_test->variation_key == "amazon_pay" ) {

        $amazon_pay_ab_test_active = true;
        $include_amazonpay_scripts = true;

    }
    */

/* AB Test: better_results : START*/

if (isset($_SESSION["br_complete"])) {
    unset($_SESSION["br_complete"]);
}

/* AB Test: better_results : END*/

    // Ajax
    if ( SYSTEM::is_ajax_request() ) {

        SYSTEM::flush_ajax_response();

    }

    // Get current users active plan list
    $active_memberships = User::get_active_plan_ids( $user_id, true );

    // PayFlow Transparent Redirect Response
    if ( "pftr" == $cmd && ! empty( $_SESSION["tokens"][ $token ] ) ) {

        $_SESSION["tokens"][ $token ]["payflow_data"] = $_GET;
        $action = "checkout";
        $post_data = array_merge( $post_data, $_SESSION["tokens"][ $token ]["post_data"] );
        $post_data["card_token"] = 1;
        unset( $_SESSION["tokens"][ $token ]["pf_token_data"], $_SESSION["tokens"][ $token ]["post_data"] );

    }

    // USAePay Tokenization
    if ( "usaepay_token" == $cmd && ! empty( $_SESSION["tokens"][ $token ] ) ) {

        $_SESSION["tokens"][ $token ]["usaepay_data"] = $_GET;
        $_SESSION["tokens"][ $token ]["post_data"]["card_token"] = $input_get["UMrefNum"];
        $redirect = RELATIVE_URL . "membership-levels/?token={$token}&cmd=usaepay_token_set";
        SYSTEM::redirect( $redirect );

    } elseif ( "usaepay_token_set" == $cmd ) {

        $_GET = array_merge( $_GET, $_SESSION["tokens"][ $token ]["usaepay_data"] );
        $post_data = array_merge( $post_data, $_SESSION["tokens"][ $token ]["post_data"] );
        $action = "checkout";
    }

    if ( "nmitr" == $cmd && ! empty( $_SESSION["tokens"][ $token ] ) ) {

        $nmi_payment = true;
        $action = "checkout";
        $post_data = $_SESSION["tokens"][ $token ]["nmi_data"];
        $_SESSION["tokens"][ $token ] = array_merge( $_SESSION["tokens"][ $token ], $post_data );

    }

    if ( ! empty( $payment_session ) ) {

        $payment_token = $token;
        $token = $payment_session;
        $override_checkout = $checkout_step ?: 0;
        if ( ! empty( $cmd ) ) {

            $post_data["method"] = $cmd;
            $post_data["tos_agree"] = 1;

        }

        if ( $_SESSION["tokens"][ $token ]["payment_token"] != $payment_token ) $token = "";
        if ( ! empty( $_SESSION["tokens"][ $token ]["upsell_membership_selected"] ) ) $post_data["plan_id"] = "upsell";

    } else $payment_token = "";

    $mailchimp_script = true;

    // Page Data
    if ( ! empty( $token ) && ! empty( $_SESSION["tokens"][ $token ] ) ) {

        $checkout_step = 1;
        $page_title = "Membership Checkout | People Search - SocialCatfish.com";
        $page_description = "Find or verify someone using just an image Find out information about someone with just their name Locate online social profiles (dating profiles, social profiles and work profiles) Get access to criminal records* Find out who lives at an address Verify a business Find out who owns an email Find out who owns a phone &hellip;";
        $token_session_data = &$_SESSION["tokens"][ $token ];        

		if( empty( $token_session_data["billing_lastname"] ) || empty( $token_session_data["billing_firstname"] ) || ( ! empty( $membership["show_phone_number"] ) && empty( $token_session_data["billing_phone"] ) ) ) $token_session_data = array_merge( $token_session_data, array_filter( $post_data ) );

        if ( ! isset( $token_session_data["sub_page"] ) ) {

            $checkout_page = true;
        }

        if ( ! empty( $token_session_data["template"] ) ) {

            SCF::switch_to_template( $token_session_data["template"] );

        }

        // $_SESSION['AB_skip_combo'] = $abtester->get_experiment( "skip_trial_combo", session_id(), SYSTEM::bot_detected() ? "standard" :  $_SESSION["landing_page_AB"] ? "standard" : "standard" );

        // if ($_SESSION['AB_skip_combo']->variation_key == "revised") {

        //     if (SYSTEM::request_post( "skip_trial_combo" )) {
        //         $plan_skip = SYSTEM::request_post( "skip_trial_combo" );
        //         $token_session_data["membership_id"] = $plan_skip;
        //         $token_session_data["membership"] = Membership::get( $plan_skip );
        //         $redirect = RELATIVE_URL . "membership-levels/?token={$token}";
        //         SYSTEM::redirect( $redirect );
        //     }

        // }

        // Assign combine: unlimite social search plan and one time boosted plan
        if( isset ( $_SESSION["regular_premium_combine"] ) ) {
            if( isset( $_SESSION["active_boosted_ontime"] ) ) {
                $membership = Membership::get( PLAN_UNLIMITED_GENERAL_BOOSTED_ONETIME );
                $token_session_data["membership_id"] = PLAN_UNLIMITED_GENERAL_BOOSTED_ONETIME;
                $token_session_data["membership"] = $membership;
            } else {
                $membership = Membership::get( PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL );
                $token_session_data["membership_id"] = PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL;
                $token_session_data["membership"] = $membership;
            }
            $person_id = $_SESSION["search_params"]["person_id"];
        }
        
        if ($_SESSION["price_changed"]) {

            if ($token_session_data["membership"]["id"] == "75") {
                
                $membership = Membership::get( PLAN_UNLIMITED_3_DAY_RIS_2897 );
                $token_session_data["membership_id"] = PLAN_UNLIMITED_3_DAY_RIS_2897;
                $token_session_data["membership"] = $membership;

            }

            if ($token_session_data["membership"]["id"] == "74") {
            
                $membership = Membership::get( PLAN_UNLIMITED_3_DAY_SS_2894 );
                $token_session_data["membership_id"] = PLAN_UNLIMITED_3_DAY_SS_2894;
                $token_session_data["membership"] = $membership;

            }
        }

        $payment_page_string = "";

        if ( ! empty( $user_id ) ) {

            $payment_page_string = "register/checkout-cross.php";

        } else {
                $payment_page_string = "register/checkout-b.php";

        }
        $sub_page = ( isset( $token_session_data["sub_page"] ) ) ? $token_session_data["sub_page"] : $payment_page_string;

        if($sub_page == "register/checkout-b.php" && empty( $user_id ) && $_SESSION["last_search_type"] == SEARCH_TYPE_EMAIL){
            $_SESSION["new_user_registered"] = false;
            $_SESSION["new_user_redirect_url"] = $token_session_data["query_data"]["name"];
        }

        $query_data = ! empty( $token_session_data["query_data"] ) ? $token_session_data["query_data"] : "";
        $image_search_data = ! empty( $token_session_data["image_search_data"] ) ? $token_session_data["image_search_data"] : "";

        if ( empty( $post_data["billing_country"] ) ) $post_data["billing_country"] = "US";

		if( ! empty( $dashboard_membership_addon_request ) ){

			$id = ! empty( $dashboard_membership_addon_request ) ? $dashboard_membership_addon_request : $id;

            $token_session_data["cancel_old_plan_if_combined"] = $_session["tokens"][ $token ]["cancel_old_plan_if_combined"] = $id;
			$_SESSION["dashboard_addon_request"] = true;

            if ( ! empty( $id ) && ( $membership = Membership::get( $id ) ) ) {
                $token_session_data["membership_id"] = $id;
                $token_session_data["membership"] = $membership;
                $redirect = RELATIVE_URL . "membership-levels/?token={$token}";

            } else $redirect = RELATIVE_URL;
            SYSTEM::redirect( $redirect );

        }
        if( ! empty( $criminal_records_addon ) ){

			$id = ! empty( $criminal_records_addon ) ? $criminal_records_addon : $id;

           	$_SESSION["criminal_records_addon_request"] = true;

            if ( ! empty( $id ) && ( $membership = Membership::get( $id ) ) ) {
                $token_session_data["membership_id"] = $id;
                $token_session_data["membership"] = $membership;
                $redirect = RELATIVE_URL . "membership-levels/?token={$token}";

            } else $redirect = RELATIVE_URL;
            SYSTEM::redirect( $redirect );

        }

        if ( empty( $token_session_data["membership_id"] ) ) {


            if ( ! empty( $id ) && ( $membership = Membership::get( $id ) ) ) {
                $token_session_data["membership_id"] = $id;
                $token_session_data["membership"] = $membership;
                $redirect = RELATIVE_URL . "membership-levels/?token={$token}";

            } else $redirect = RELATIVE_URL;
            SYSTEM::redirect( $redirect );

        } else {

            $id = ! empty( $post_data["addon_id"] ) ? $post_data["addon_id"] : $id;
            if ( ! empty( $post_data["addon_id"] ) && $membership = Membership::get( $post_data["addon_id"] ) ) {

                $token_session_data["membership_id"] = $id;
                $token_session_data["membership"] = $membership;
                $redirect = RELATIVE_URL . "membership-levels/?token={$token}";
                SYSTEM::redirect( $redirect );

            } elseif ( SYSTEM::request_post( "change_plan" ) && PLAN_UNLIMITED_SAVE_A_SALE == SYSTEM::request_post( "change_plan" ) && $membership = Membership::get( PLAN_UNLIMITED_SAVE_A_SALE ) ) {

                $token_session_data["membership_id"] = PLAN_UNLIMITED_SAVE_A_SALE;
                $token_session_data["membership"] = $membership;
                $redirect = RELATIVE_URL . "membership-levels/?token={$token}";
                SYSTEM::redirect( $redirect );

            }

            if ( PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL == $token_session_data["membership_id"] ) {

                $save_a_sale_enabled = ($device == "mobile") ? false : true;

            }

        }

        if( ! empty( $_SESSION["mobile_app_user"] ) ){

            $user_data = User::get_by_id( $_SESSION["mobile_app_user"] );
            if( ! empty( $user_data ) && ! empty( $user_data["id"] ) ){
                $user_id = $user_data["id"];
                $mobile_app = true;
            }

        }

        $membership = $token_session_data["membership"];
        $_SESSION["membership_type"] =  $membership["id"];
        $membership_info = Membership::parse_data( $membership, "per" );

        // Check whether this membership can be purchased only if another plan is already purchased.
        if ( ! empty( $membership["depends_on_membership"] ) ) {

            $membership["depends_on_membership"] = explode( ",", $membership["depends_on_membership"] );
            $all_user_plans = array_merge( $user_data["active_plans"], $user_data["deactived_plans"] );

            if ( ! array_intersect( $membership["depends_on_membership"], $all_user_plans ) ) {

                unset( $_SESSION["tokens"][ $token ] );
                SYSTEM::redirect( PAGE_URL_MEMBERSHIP_LEVELS );

            }

        }

        // If already signed in for the plan and if the plan is marked as "avoid_duplicate_signups", redirect to membership levels page
        if ( ! empty( $membership["avoid_duplicate_signups"] ) && $user_id && in_array( $membership["id"], $user_data["active_plans"] ) ) {

            unset( $_SESSION["tokens"][ $token ] );
            SYSTEM::redirect( PAGE_URL_MEMBERSHIP_LEVELS );

        }

        // Assign combine monlthy membership plans
        $monthly_membership_plans_combine = array( PLAN_UNLIMITED_MONTHLY_1, PLAN_UNLIMITED_MONTHLY_3, PLAN_UNLIMITED_MONTHLY_6 );
        if( isset( $post_data["plan_id"] ) && in_array( $post_data["plan_id"], $monthly_membership_plans_combine ) ){
            $membership = Membership::get( $post_data["plan_id"] );
            $token_session_data["membership_id"] = $post_data["plan_id"];
            $token_session_data["membership"] = $membership;
        }

        // Multi-membership Plan Selection
        $multiple_membership_options = ! empty( $token_session_data["membership_options"] ) && is_array( $token_session_data["membership_options"] );

        // Accepted Payment Gateways
        $pg_enabled = [ "paypal" => true, "payflowpro" => true, "braintree" => true, "amazon_pay" => true, "nmi" => true, "usaepay" => true ];
        $payment_gateway_default = PAYMENT_GATEWAY_DEFAULT;

        if ( ! empty( $membership["allowed_payment_gateways"] ) ) {

            $pg_enabled = array_fill_keys( array_keys( $pg_enabled ), false );
            foreach ( $membership["allowed_payment_gateways"] as $_pg ) $pg_enabled[$_pg]= true;
            unset( $_pg );

            if ( $pg_enabled["braintree"] && $payment_gateway_default != "braintree" ) {

                $payment_gateway_default = "braintree";

            } elseif ( $pg_enabled["payflowpro"] && $payment_gateway_default != "payflowpro" ) {

                $payment_gateway_default = "payflowpro";

            }

        }

        // Payment Gateway Params
        $payflow_transparent_redirect_mod = ( "payflowpro" == $payment_gateway_default && PAYFLOW_TRANSPARENT_REDIRECT );
        $braintree_payment = $include_braintree_scripts = ( "braintree" == $payment_gateway_default );
        $usaepay_payment = $include_usaepay_scripts = ( "usaepay" == $payment_gateway_default );
        $avoid_posting_cc_data = ( $payflow_transparent_redirect_mod || $braintree_payment || ! empty( $nmi_payment ) || $usaepay_payment );

		// Paypal Transparent Redirect Token
		if ( $payflow_transparent_redirect_mod ) {

            $pftr_amount = ( defined( "PAYFLOW_TRANSPARENT_REDIRECT_ZERO_VERIFICATION" ) && PAYFLOW_TRANSPARENT_REDIRECT_ZERO_VERIFICATION ) ? 0 : $membership["initial_amount"];
            $token_session_data["pf_token_data"] = $payflowpro->get_secure_token( [ "amount" => $pftr_amount, "error_url" => BASE_URL . "membership-levels/?token={$token}&cmd=pftr&utm_nooverride=1&error=1",  "cancel_url" => BASE_URL . "membership-levels/?token={$token}&cmd=pftr&utm_nooverride=1&cancel=1", "return_url" => BASE_URL . "membership-levels/?token={$token}&cmd=pftr&utm_nooverride=1&success=1" ] );

        }

        // USAePay
        if ( $usaepay_payment && $avoid_posting_cc_data ) {

            $usaepay_pg = \PaymentGateway\Common::getPaymentGatewayInstance( $payment_gateway_default );
            $usaepay_umhash = $usaepay_pg->generateUMhash( "cc:authonly", $membership["initial_amount"] );

        }

		// Braintree
		if ( $braintree_payment && empty( $token_session_data["bt_client_token"] ) ) $token_session_data["bt_client_token"] = $braintree->get_client_token();


        //CSI-6653 - special approval for a user to create black friday plan after offer ends
        //Must remove this if after the user completed his purchase
        if( ! empty( $user_data["email"] ) && ! in_array( $user_data["email"], ["jhaney@ualberta.ca", "asdasd@appearen.com", "asitha@socialcatfish.com"])){
            //Black Friday offer is over
            if( ! empty( $token_session_data["section"] ) && $token_session_data["section"] == "black-friday-2020" ) SYSTEM::redirect( PAGE_URL_DASHBOARD );
        }

        
        if ( $user_id ) {

            //CSI-6653 - special approval for a user to create black friday plan after offer ends
            //Must remove this if after the user completed his purchase
            if( ! empty( $user_data["email"] ) && ! in_array( $user_data["email"], ["jhaney@ualberta.ca", "asdasd@appearen.com", "asitha@socialcatfish.com"])){
                //Already registered users cannot purchase this offer
                if( ! empty( $token_session_data["section"] ) && $token_session_data["section"] == "black-friday-2020" ) SYSTEM::redirect( PAGE_URL_DASHBOARD );
            }

		    // Braintree
		    if ( $braintree_payment && ! isset( $token_session_data["bt_user_data"] ) ) {

                $bt_user_data = $braintree->find_customer( $user_data["email"] );
                $token_session_data["bt_user_data"] = [
                    "id" => $bt_user_data->id,
                    "cards" => array_map( function( $obj ) {

                        return json_decode( json_encode( $obj ), true );

                    }, is_array( $bt_user_data->creditCards ) ? $bt_user_data->creditCards : [] ),
                ];


            }
		    if ( ! empty( $token_session_data["bt_user_data"]["cards"] ) ) {

		    	$card_identifiers = [];

			    foreach ( $token_session_data["bt_user_data"]["cards"] as $bt_card ) {

				    if ( ! $bt_card["expired"] && empty( $card_identifiers[ $bt_card["uniqueNumberIdentifier"] ] ) ) {

					    $user_credit_cards[] = [
						    "caption" => "{$bt_card["maskedNumber"]} ({$bt_card["cardType"]})",
						    "token" => $bt_card["token"],
                            "cardtype" => $bt_card["cardType"]
					    ];

					    $card_identifiers[ $bt_card["uniqueNumberIdentifier"] ] = 1;

				    }

			    }
			    unset( $bt_card );

		    }

            $registered_user_allowed_memberships = [ 'ris' , 'upgrade' , 'premium_data', 'premium_data_monthly' , 'avoid-cancel-regular' , 'avoid-cancel-ris' , 'avoid-cancel-regular-ris' ];

            $append_data = [
                "email" => $user_data["email"],
                "billing_firstname" => $user_data["first_name"],
                "billing_lastname" => $user_data["last_name"],
                "billing_phone" => ! empty( $token_session_data["billing_phone"] ) ? $token_session_data["billing_phone"] : $user_data["phone_number"],
                "password" => "password",
            ];
            $post_data = array_merge( $post_data, $append_data );


        } else {
            if (!isset($_SESSION["regular_premium_combine"])) {
                if ( ! preg_match( "/^ris|initial|hidden|black-friday-2020|promo\$/im", $membership["type"] ) ) SYSTEM::redirect( RELATIVE_URL . "membership-levels/" );
            }

        }

        // Form Validation
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

        if ( ! empty( $token_session_data["confirm_password"] ) ) {

            $form_validation[] = [
                "name" => "confirm_password",
                "value" => $input_post->confirm_password,
                "caption" => "Confirm Password",
                "validation" => "max_length[100]|match[password]",
            ];

        }

        if ( $membership["initial_amount"] > 0 ) {

            $form_validation = array_merge( $form_validation, [
                [
                    "name" => "billing_address1",
                    "value" => $post_data["billing_address1"],
                    "caption" => "Billing Address",
                    "validation" => "required|max_length[100]",
                ],
                [
                    "name" => "billing_address2",
                    "value" => $post_data["billing_address2"],
                    "caption" => "Billing Address 2",
                    "validation" => "max_length[100]",
                ],
                [
                    "name" => "billing_city",
                    "value" => $post_data["billing_city"],
                    "caption" => "Billing City",
                    "validation" => "required|max_length[100]",
                ],
                [
                    "name" => "billing_state",
                    "value" => $post_data["billing_state"],
                    "caption" => "Billing State",
                    "validation" => "required|max_length[100]",
                ],
                [
                    "name" => "billing_country",
                    "value" => $post_data["billing_country"],
                    "caption" => "Billing Country",
                    "validation" => "required|max_length[100]",
                ],
                [
                    "name" => "billing_postal_code",
                    "value" => $post_data["billing_postal_code"],
                    "caption" => "Postal Code",
                ],
                [
                    "name" => "tos_agree",
                    "value" => $post_data["tos_agree"],
                ],
                [
                    "name" => "card_name",
                    "value" => $post_data["card_name"],
                    "caption" => "Card Holder Name",
                    "validation" => "required|max_length[100]",
                ],
            ] );

            if ( ! $avoid_posting_cc_data ) {

                $form_validation = array_merge( $form_validation, [
                    [
                        "name" => "card_number",
                        "value" => $post_data["card_number"],
                        "caption" => "Card Number",
                        "validation" => "required|max_length[16]",
                    ],
                    [
                        "name" => "card_cvv",
                        "value" => $post_data["card_cvv"],
                        "caption" => "Card CVV",
                        "validation" => "required|max_length[4]",
                    ],
                    [
                        "name" => "card_expiry_month",
                        "value" => $post_data["card_expiry_month"],
                        "caption" => "Expiry Month",
                        "validation" => "required|max_length[2]",
                    ],
                    [
                        "name" => "card_expiry_year",
                        "value" => $post_data["card_expiry_year"],
                        "caption" => "Expiry Year",
                        "validation" => "required|max_length[4]",
                    ],
                ] );

            } elseif ( $braintree_payment  ) {

                $form_validation[] = [
                        "name" => "bt_token",
                        "value" => $post_data["bt_token"],
                        "caption" => "Secure Token",
                        "validation" => "required",
                ];

            } else {

                $form_validation[] = [
                    "name" => "card_token",
                    "value" => $post_data["card_token"],
                    "caption" => "Payment Token",
                    "validation" => "required",
                ];

            }

            if ( ! empty( $post_data["ctoken"] ) ) {
                /*
                $form_validation = array_merge( $form_validation, [
                    [
                        "name" => "ctoken",
                        "value" => $post_data["ctoken"],
                        "caption" => "Payment Token",
                        "validation" => "required",
                    ],
                    [
                        "name" => "ctoken_card_cvv",
                        "value" => $post_data["ctoken_card_cvv"],
                        "caption" => "Card CVV",
                        "validation" => "required|max_length[4]",
                    ],
                ] );
                */
                $form_validation = [
                    [
                        "name" => "ctoken",
                        "value" => $post_data["ctoken"],
                        "caption" => "Payment Token",
                        "validation" => "required",
                    ],
                ];

            }

            if ( ! empty( $post_data["ap_token"] ) ) {

                $form_validation = array_merge( $form_validation, [
                    [
                        "name" => "ap_token",
                        "value" => $post_data["ap_token"],
                        "caption" => "Payment Token",
                        "validation" => "required",
                    ]
                ] );

            }

            $form_validation = array_merge( $form_validation, [
                [
                    "name" => "report_agreement",
                    "value" => $post_data["report_agreement"],
                    "caption" => "Report Agreement",
                    "validation" => "required",
                ],
                [
                    "name" => "signup_purpose",
                    "value" => ( ! empty( $post_data["signup_purpose"] ) )? implode( "|", $post_data["signup_purpose"] ) : "",
                ],
            ] );

            if ( ! empty( $post_data["payment_card"] ) && $post_data["payment_card"] != 'payment_add_new' ) {

                $form_validation = [
                    [
                        "name" => "tos_agree",
                        "value" => $post_data["tos_agree"],
                    ],
                    [
                        "name" => "report_agreement",
                        "value" => $post_data["report_agreement"],
                        "caption" => "Report Agreement",
                        "validation" => "required",
                    ],
                    [
                        "name" => "signup_purpose",
                        "value" => ( ! empty( $post_data["signup_purpose"] ) )? implode( "|", $post_data["signup_purpose"] ) : "",
                    ],
                ];

            }

            if( ! empty( $nmi_payment ) ){

                $form_validation = [
                    [
                        "name" => "gateway_token",
                        "value" => $nmi_token,
                    ]
                ];

            }

            if( $membership["show_phone_number"] == 1 ){

                $form_validation = array_merge( $form_validation, [
                    [
                        "name" => "billing_phone",
                        "value" => $post_data[ ( ! empty( $post_data["ctoken"] ) ? "sc_billing_phone" : "billing_phone" ) ],
                        "caption" => "Phone Number",
                        "validation" => "required|phone|max_length[100]",
                    ],
                ] );

            }

        }

        if ( ! DEBUG ) {

            // add all user emails to sendy list as abandoned cart users
            // However that email will move from abandoned cart list to customers list ONCE a successfull checkout has completed.
            if( ! empty( $post_data["email"] )  && ! $do_not_add_to_sendy_lists ){

                $sendy = SYSTEM::loadsendy();

                if( ! empty( $membership["tokens_email"] ) ){
                    // standard search
                    $sendy->setListId( SENDY_LIST_ABANDONEDCART_STANDARD_SEARCH );
                    $sendy->subscribe(array(
                        'email' => $post_data["email"],
                        "name" => ( ! empty( $post_data["billing_firstname"] ) ? $post_data["billing_firstname"] . " " : "" ) . ( ! empty( $post_data["billing_lasttname"] ) ? $post_data["billing_lasttname"] : "" )
                    ));
                }
                if( ! empty( $membership["tokens_image"] ) ){
                    // image search
                    $sendy->setListId( SENDY_LIST_ABANDONEDCART_IMAGE_SEARCH );
                    $sendy->subscribe(array(
                        'email' => $post_data["email"],
                        "name" => ( ! empty( $post_data["billing_firstname"] ) ? $post_data["billing_firstname"] . " " : "" ) . ( ! empty( $post_data["billing_lasttname"] ) ? $post_data["billing_lasttname"] : "" )
                    ));

                }
                if( empty( $membership["tokens_email"] ) && empty( $membership["tokens_image"] ) ){
                    // hire us search
                    $sendy->setListId( SENDY_LIST_ABANDONEDCART_SEARCH_SPECIALIST );
                    $sendy->subscribe(array(
                        'email' => $post_data["email"],
                        "name" => ( ! empty( $post_data["billing_firstname"] ) ? $post_data["billing_firstname"] . " " : "" ) . ( ! empty( $post_data["billing_lasttname"] ) ? $post_data["billing_lasttname"] : "" )
                    ));

                }
            }
            ############## END add emails to sendy list

        }

        // Check whether user has Premium Credits and the Plan is a Premium One Time Plan
        $one_time_premium_plans = ( $user_id && $user_data["premium_credit_balance"] ) ? Membership::get_one_time_premium_plans() : [];
        $minimum_required_amount = $multiple_membership_options ? Membership::get_minimum_required_points( $token_session_data["membership_options"], $one_time_premium_plans ) : $membership["initial_amount"];
        $show_premium_credit_option = ( $minimum_required_amount <= $user_data["premium_credit_balance"] ) && ( ( $multiple_membership_options && ! empty( array_intersect( array_keys( $token_session_data["membership_options"] ), $one_time_premium_plans ) ) ) || ( in_array( $membership["id"], $one_time_premium_plans ) ) );

        if ( "checkout" == $action ) {

            //$user_in_blocked_list = User::user_in_blocked_list( $post_data );
            //if( $user_in_blocked_list ) $error_messages[] = "Sorry.. You have been blocked by the system. Please contact us.";

            // Multi-membership Plan Selection
            if ( $multiple_membership_options ) {

                // Mod for PayPal Express Checkout
                if ( ! empty( $input_get->p_id ) && empty( $post_data["plan_id"] ) ) $post_data["plan_id"] = $input_get->p_id;

                if ( isset( $token_session_data["membership_options"][ $post_data["plan_id"] ] ) ) {

                    $membership = $token_session_data["membership_options"][ $post_data["plan_id"] ];
                    $token_session_data["membership_id"] = $post_data["plan_id"];
                    $token_session_data["membership"] = $membership;

                } else {

                    unset( $_SESSION["tokens"][ $token ] );
                    SYSTEM::redirect( PAGE_URL_MEMBERSHIP_LEVELS );

                }

            }

            $checkout_step = ( $override_checkout ) ? $override_checkout : 2;
            $payment_method = ( ! empty( $post_data["method"] ) ) ? $post_data["method"] : ( ! empty( $token_session_data["method"] ) ? $token_session_data["method"] :  "payflowpro" );
            $token_session_data["last_payment_method"] = $payment_method;

            switch ( $payment_method ) {

                case "paypal_express":
                    $payment_gateway = "paypal_express_checkout";
                    $avoid_validation = true;
                    break;

                case "amazonpay":
                    $payment_gateway = "amazonpay";
                    break;

                case "nmi":
                    $payment_gateway = "nmi";
                    break;

                case "pcb":
                    $payment_gateway = "pcb";
                    $avoid_validation = true;
                    break;

                default: $payment_gateway = $payment_gateway_default;

            }

            $is_subscription = ( $membership["recurring_amount"] > 0 );

            if ( empty( $avoid_validation ) && empty( $user_data["membership_plan"] ) ) $error_fields = SYSTEM::form_validate( $form_validation );
            if ( ! $user_id && ! empty( $post_data["email"] ) && User::get_by_email( $post_data["email"] ) ) $error_messages[] = "E-Mail Address already in use. Please login.";
            //if ( ! $user_id && ! empty( $post_data["email"] ) && User::is_email_blocked( $post_data["email"] ) ) $error_messages[] = "E-Mail Address blocked.";
            if ( empty( $post_data["tos_agree"] ) ) $error_messages[] = "Please accept the Terms of Service.";

            // Cross check email against mailing lists
            $do_not_add_to_sendy_lists = false;
            if ( ! empty( $membership["check_mailing_list"] ) && defined( "SENDY_LIST_{$membership["check_mailing_list"]}" ) ) {
                if( ! Membership::check_email_for_sendy_list( constant( "SENDY_LIST_{$membership["check_mailing_list"]}" ), $post_data["email"] ) ){
                    $error_messages[] = "The email you entered is not registered for this promo. Please try again.";
                } else $do_not_add_to_sendy_lists = true;
            }

            if( ! empty( $membership["check_mailing_list"] ) && $membership["check_mailing_list"] == "DR_PHIL_SHOW" && $current_time_ca->format('Y-m-d') > $dr_phil_promo_end_date ){
                $error_messages[] = "Sorry, This promo has ended.";
            }


            if ( "paypal_express_checkout" == $payment_gateway ) {

                require( "payment_gateways/{$payment_gateway}.php" );

            }

            if ( empty( $error_messages ) ) {

                if ( empty( $avoid_merge ) ) $token_session_data = array_merge( $token_session_data, SYSTEM::construct_custom_array( $form_validation, "name", "{value}" ) );
                extract( $token_session_data, EXTR_PREFIX_ALL, "cc" );
                $payment_error = $transaction_id = "";

                if ( $is_subscription ) {

                    switch ( $membership["start_recurring_period"] ) {

                        case "days":

                            if ( 5 == $membership["start_recurring_frequency"] && ( "payflowpro" == $payment_gateway || "paypal_express_checkout" == $payment_gateway ) ) $start_subscription = strtotime( "+6 days" );
                            else $start_subscription = strtotime( "+{$membership["start_recurring_frequency"]} days" );
                            break;

                        case "mont": $start_subscription = strtotime( "+{$membership["start_recurring_frequency"]} month" ); break;
                        case "year": $start_subscription = strtotime( "+{$membership["start_recurring_frequency"]} year" ); break;

                    }

                }

				if ( ! $testcase_is_running && $membership["initial_amount"] > 0 ) {

                    if ( $payment_gateway == "usaepay" ) require( "payment_gateways/process_signup.php" );
                    elseif ( $payment_gateway == "pcb" ) {

                        if ( ! in_array( $membership["id"], $one_time_premium_plans ) ) $payment_error = "Sorry! Subscriptions cannot be purchased using Premium Credit Balance.";
                        elseif ( $membership["initial_amount"] > $user_data["premium_credit_balance"] ) $payment_error = "Sorry! There's not enough credits to purchase this plan.";
                        else {

                            User::use_premium_credit_balance( $user_id, $membership["initial_amount"] );
                            $transaction_id = uniqid( "pcb" );

                        }

                    }
                    else require( "payment_gateways/{$payment_gateway}.php" );

                }

                // Payment Response Tracking
                $payment_tracking_data = [
                    "date" => date( "Y-m-d H:i:s"),
                    "pg" => $payment_gateway,
                    "first_name" => ! empty( $cc_billing_firstname ) ? $cc_billing_firstname : "",
                    "last_name" => $cc_billing_lastname,
                    "email" => $cc_email,
                    "subscription" => ( $is_subscription ? 1 : 0 ),
                    "membership_id" => $membership["id"],
                    "amount" => $membership["initial_amount"],
                    "auth_response" => ( ( ! empty( $response_verification ) ) ? serialize( $response_verification ) : "" ),
                    "sale_response" => ( ( ! empty( $response_sale ) ) ? serialize( $response_sale ) : "" ),
                    "subscription_response" => ( ( ! empty( $response_subscription ) ) ? serialize( $response_subscription ) : "" ),
                    "success" => $payment_error ? 0 : 1,
                ];

                $payment_tracking_data_id = Membership::add_pg_response( $payment_tracking_data );

                if ( ! empty( $payment_error ) ) {

                    $_SESSION["extract"]["error_messages"][] = $payment_error;
                    Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "Checkout Payment Errors::" . $payment_error, ["errors", "checkout payment errors {$payment_gateway}", $payment_error ] );

                } else {

                    if ( ! $testcase_is_running ) {

                        Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "plan conversion: ". $membership['id']. "",  ["initial amount: ".$membership["initial_amount"]] );




                        /** check for new user */
                        if(isset($_SESSION["new_user_registered"])){
                            $_SESSION["new_user_registered"] = true;
                        }

                        // Remove header and footer test
                        if (isset($_SESSION["removed-head-foot"])) {
                            if($_SESSION["removed-head-foot"]) $_SESSION["removed-head-foot"] = false;
                            unset($_SESSION["removed-head-foot"]);
                        }

                        //AB Testing Start

                        if (isset($_SESSION["ab_email_capture"]) && isset($_SESSION["step_three_capture"]) && !isset($_SESSION["step_four_capture"])) {
                            $_SESSION["ab_email_capture"]->track_event("4_search_capture_conversion", SYSTEM::get_device_type());
                            $_SESSION["step_four_capture"] = true;
                        }

                        if (!isset($_SESSION["step_four_price_ss"]) && isset($_SESSION["ab_price_change_ss"]) && isset($_SESSION["step_three_price_ss"])) {
                            $_SESSION["ab_price_change_ss"]->track_event("4_conversion_price_ss", SYSTEM::get_device_type());
                            $_SESSION["step_four_price_ss"] = true;
                        }

                        if (isset($_SESSION["ab_price_change_ris"]) && isset($_SESSION["step_three_price_ris"]) && !isset($_SESSION["step_four_price_ris"]) ) {
                            $_SESSION["ab_price_change_ris"]->track_event("4_conversion_price_ris", SYSTEM::get_device_type());
                            $_SESSION["step_four_price_ris"] = true;
                        }

                        if (isset($_SESSION["ab_premium_idi_SO"]) && isset($_SESSION["step_four_idi"]) && !isset($_SESSION["step_five_idi"]) ) {

                            if ( "revised" == $_SESSION["ab_premium_idi_SO"]->variation_key ) {
                                if (isset($_SESSION["active_boosted_ontime"])) {
                                    $_SESSION["ab_premium_idi_SO"]->track_event("5_premium_conversion_idi_SO", SYSTEM::get_device_type());
                                } else {
                                    $_SESSION["ab_premium_idi_SO"]->track_event("5_non_premium_conversion_idi_SO", SYSTEM::get_device_type());
                                }
                            } else {
                                $_SESSION["ab_premium_idi_SO"]->track_event("5_regular_conversion_idi_SO", SYSTEM::get_device_type());
                            }
                            $_SESSION["step_five_idi"] = true;
                            
                        }

                        if ( $membership["id"] == PLAN_PREMIUM_DATA_MONTHLY || $membership["id"] == PLAN_PREMIUM_DATA ) {

                            if(isset($_SESSION["no_premium"])) $_SESSION["no_premium"]->track_event("conversion", $device);

                        }
   
                        if(isset($_SESSION["rebuild_two"])) $_SESSION["rebuild_two"]->track_event("conversion", $device);

                        if(isset($_SESSION["ss_ultra_focus"])) $_SESSION["ss_ultra_focus"]->track_event("conversion", $device);

                        if (isset($_SESSION["ab_cr_signedout_2022"]) && isset($_SESSION["cr_choice"])) {
                            switch ($_SESSION["cr_choice"]) {
                                case 'results':
                                    $_SESSION["ab_cr_signedout_2022"]->track_event("9_2_results_conversion_cr", $device);
                                    break;

                                case 'no_results':
                                    $_SESSION["ab_cr_signedout_2022"]->track_event("9_1_no_results_conversion_cr", $device);
                                    break;

                                case 'cancel':
                                    $_SESSION["ab_cr_signedout_2022"]->track_event("9_dont_search_conversion_cr", $device);
                                    break;

                                case 'none':
                                    $_SESSION["ab_cr_signedout_2022"]->track_event("9_3_standard_conversion_cr", $device);
                                    break;

                                default:

                                    break;
                            }
                        }

                        if (isset($_SESSION["ab_premium_scroll"]) && isset($_SESSION["step_one_premium_scroll"]) && !isset($_SESSION["step_two_premium_scroll"])) {
                            $_SESSION["ab_premium_scroll"]->track_event("2_premium_conversion", SYSTEM::get_device_type());
                            $_SESSION["step_two_premium_scroll"] = true;
                        }

                        if(isset($_SESSION["better_results"])) $_SESSION["better_results"]->track_event("conversion", $device);
                        
                        if(isset($_SESSION["br_survey"])) $_SESSION["br_survey"]->track_event("conversion", $device);

                        if(isset($_SESSION["br_no_survey"])) $_SESSION["br_no_survey"]->track_event("conversion", $device);

                        if(isset($_SESSION["existing_user"])) $_SESSION["existing_user"]->track_event("conversion", $device);

                        if(isset($_SESSION["homepage_headline"])) $_SESSION["homepage_headline"]->track_event("conversion", $device);

                        if(isset($_SESSION["ab_blog_2020"])) $_SESSION["ab_blog_2020"]->track_event("conversion", $device);

                        if(isset($_SESSION["ab_ris_speed"])) $_SESSION["ab_ris_speed"]->track_event("conversion", $device);

                        if(isset($_SESSION["ab_us_only"])) $_SESSION["ab_us_only"]->track_event("conversion", $device);

                        if (isset($_SESSION["step_four"]) && isset($_SESSION["ab_baselines_old_img"])) $_SESSION["ab_baselines_old_img"]->track_event("ris_user_conversion", SYSTEM::get_device_type());

                        if (isset($_SESSION["step_four_basic"]) && isset($_SESSION["ab_baselines_basic_img"])) $_SESSION["ab_baselines_basic_img"]->track_event("ris_basic_user_conversion", SYSTEM::get_device_type());

                        if (isset($_SESSION["ab_baselines_home_name"]) && isset($_SESSION["step_four_name"]) ) {
                            $_SESSION["ab_baselines_home_name"]->track_event("5_conversion", SYSTEM::get_device_type());
                        }

                        if (isset($_SESSION["ab_baselines_phone_main"]) && isset($_SESSION["step_three_phone_main"]) ) {
                            $_SESSION["ab_baselines_phone_main"]->track_event("4_phone_main_conversion", SYSTEM::get_device_type());
                        }

                        if (isset($_SESSION["ab_search_progress_WW_SS"]) && isset($_SESSION["step_four_WW_SS"]) && !isset($_SESSION["step_five_WW_SS"])) {
                            $_SESSION["ab_search_progress_WW_SS"]->track_event("5_conversion_WW_RIS", SYSTEM::get_device_type());
                            $_SESSION["step_five_WW_SS"] = true;
                        }

                        if (isset($_SESSION["ab_baselines_username"]) && isset($_SESSION["step_four_username"]) ) {
                            $_SESSION["ab_baselines_username"]->track_event("5_username_conversion", SYSTEM::get_device_type());
                        }
                        
                        if (isset($_SESSION["ab_baselines_phone"]) && isset($_SESSION["step_three_phone"]) ) {
                            $_SESSION["ab_baselines_phone"]->track_event("4_phone_conversion", SYSTEM::get_device_type());
                        }
                        
                        if (isset($_SESSION["ab_baselines_email"]) && isset($_SESSION["step_three_email"]) ) {
                            $_SESSION["ab_baselines_email"]->track_event("4_email_conversion", SYSTEM::get_device_type());
                        }

                        if (isset($_SESSION["ab_baselines_ras"]) && isset($_SESSION["step_seven_ras"]) ) {
                            $_SESSION["ab_baselines_ras"]->track_event("3_ras_conversion", SYSTEM::get_device_type());
                        }

                        if (isset($_SESSION["ab_baselines_ras_main"]) && isset($_SESSION["step_seven_ras_main"]) ) {
                            $_SESSION["ab_baselines_ras_main"]->track_event("3_ras_main_conversion", SYSTEM::get_device_type());
                        }

                        if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_three_ris"]) ) {
                            $_SESSION["ab_baselines_image"]->track_event("4_image_conversion", SYSTEM::get_device_type());
                        }
                        if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_three_ris_main"]) ) {
                            $_SESSION["ab_baselines_image_main"]->track_event("4_image_conversion_main", SYSTEM::get_device_type());
                        }
               
                        if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_three_ris_ad"]) ) {
                            $_SESSION["ab_baselines_image_ad"]->track_event("4_image_conversion_ad", SYSTEM::get_device_type());
                        }

                        if (isset($_SESSION["ab_baselines_email_main"]) && isset($_SESSION["step_three_email_main"]) ) {
                            $_SESSION["ab_baselines_email_main"]->track_event("4_email_conversion_main", SYSTEM::get_device_type());
                        }
                        
                        if (isset($_SESSION["ab_baselines_username_main"]) && isset($_SESSION["step_four_username_main"]) ) {
                            $_SESSION["ab_baselines_username_main"]->track_event("5_username_conversion_main", SYSTEM::get_device_type());
                        }

                        if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_three_WW_RIS"]) && !isset($_SESSION["step_four_WW_RIS"]) ) {
                            $_SESSION["ab_search_progress_WW_RIS"]->track_event("4_image_conversion_WW", SYSTEM::get_device_type());
                            $_SESSION["step_four_WW_RIS"] = true;
                        }

                        //AB Testing End

                    }

                    if ( ! $user_id ) {

                        $user_data = [
                            "first_name" => $cc_billing_firstname,
                            "last_name" => $cc_billing_lastname,
                            "email" => $cc_email,
                            "password" => $cc_password,
                            "address_1" => ( ! empty( $cc_billing_address1 ) ? $cc_billing_address1 : "" ),
                            "address_2" => ( ! empty( $cc_billing_address2 ) ? $cc_billing_address2 : "" ),
                            "country" => ( ! empty( $cc_billing_country ) ? $cc_billing_country : "" ),
                            "city" => ( ! empty( $cc_billing_city ) ? $cc_billing_city : "" ),
                            "state" => ( ! empty( $cc_billing_state ) ? $cc_billing_state : "" ),
                            "postal_code" => $cc_billing_postal_code,
                            "phone_number" => ( ! empty( $cc_billing_phone ) ? $cc_billing_phone : "" ),
                            "flagged_email" => $_SESSION["email_verification_status"],
                        ];
                        unset($_SESSION["email_verification_status"]); 

                        if( $privacy_lock_activated ){
                            $user_data["privacy_lock"] = 2;
                            $user_data["privacy_lock_emails"] = serialize( [ $cc_email, 2, 0 ] );
                        }

                        if ( ! empty( $_SESSION["advertisement_data"]["source"] ) || ! empty( $_SESSION["advertisement_data"]["referer"] ) ) {

                            $user_data["ref_source"] = ! empty( $_SESSION["advertisement_data"]["source"] ) ? $_SESSION["advertisement_data"]["source"] : "";
                            $user_data["ref_referer"] = ! empty( $_SESSION["advertisement_data"]["referer"] ) ? $_SESSION["advertisement_data"]["referer"] : "";

                        }

                        if ( User::create( $user_data ) ) {
                            
                            $_SESSION["extract"]["new_user"] = true;
                            $_SESSION["extract"]["trigger_new_user"] = true;
                            $_SESSION["extract"]["success_messages"][] = "Thank you for your payment. Account created successfully.";                            
                            
                            $user = User::get_by_email( $cc_email );
                            if( ! empty( $_SESSION["no_results_history"] ) ) {
                                foreach( $_SESSION["no_results_history"] as $index => $row ) {
                                    if( ! empty( $row ) ) {
                                        Search::save_results_for_user( $user["id"], $row );
                                    }
                                }
                                unset( $_SESSION["no_results_history"] );
                            }

                            if(!empty($_SESSION['no_result_funnel_id'])){
                                $data["option"] = 1;
                                $data["user_id"] =$user["id"];
                                Search::no_results_tracking(6,$data);
                                unset($_SESSION['no_result_funnel_id']);
                                unset($_SESSION['no_result_funnel_step']);
                            };
                            if ( ! empty($_SESSION["sign_up_purpose"] ) ) {
                                $cc_signup_purpose = array_fill_keys( $_SESSION["sign_up_purpose"], 1 );
                                $lost_loved_one = isset( $cc_signup_purpose[ SIGN_UP_PURPOSE_LOST_LOVED_ONE ] );
                                $research_myself = isset( $cc_signup_purpose[ SIGN_UP_PURPOSE_RESEARCH ] );
                                $professional_use = isset( $cc_signup_purpose[ SIGN_UP_PURPOSE_PROFESSIONAL ] );
                                unset( $cc_signup_purpose[ SIGN_UP_PURPOSE_SOME_MET_ONLINE ], $cc_signup_purpose[ SIGN_UP_PURPOSE_LOST_LOVED_ONE ], $cc_signup_purpose[ SIGN_UP_PURPOSE_RESEARCH ], $cc_signup_purpose[ SIGN_UP_PURPOSE_PROFESSIONAL ], $cc_signup_purpose["other"] );
                                $other = ( ! empty( $cc_signup_purpose ) ) ? key( $cc_signup_purpose ) : "";

                                $signup_purpose_data = array(
                                    "user_id" => $user["id"],
                                    "verify_someone" => $verify_someone,
                                    "lost_loved_one" => $lost_loved_one,
                                    "research_myself" => $research_myself,
                                    "professional_use" => $professional_use,
                                    "other" => $other
                                );

                                Membership::add_signup_purpose( $signup_purpose_data );

                            }

                            if ( ! empty( $user ) ) {

                                $_SESSION["user"] = $user;
                                setcookie( SESSION_NAME, session_id(), time() + ( 86400 * 30 ), "/" );
                                $user_id = $user["id"];

                            }
                            
                            if ( ! DEBUG && ! $testcase_is_running ) {
                            	                            	
                                if ( ! empty( $membership["mailing_list"] ) && ! $do_not_add_to_sendy_lists ) {

                                    $sendy = SYSTEM::loadsendy();
                                    $sendy_list_id = explode( ",", strtoupper( str_replace( " ", "", $membership["mailing_list"] ) ) );

                                    if( ! empty( $sendy_list_id ) )
                                        foreach ( $sendy_list_id as $_sendy_list_id ){

                                            $sendy->setListId( constant( "SENDY_LIST_" . $_sendy_list_id ) );
                                            $sendy->subscribe(array(
                                                'email' => $cc_email,
                                                "name" => ( ! empty( $cc_billing_firstname ) ? $cc_billing_firstname . " " : "" ) . ( ! empty( $cc_billing_lastname ) ? $cc_billing_lastname : "" )
                                            ));

                                    }
                                }

                                // This is a user with successfull payment and this user's emails has been added to sendy list as abandoned cart user
                                // Here current user's email will move from abandoned cart list to customers list.
                                if( ! empty( $post_data["email"] )  && ! $do_not_add_to_sendy_lists ){

                                    $sendy = SYSTEM::loadsendy();

                                    if( ! empty( $membership["tokens_email"] ) ){
                                        // standard search
                                        $sendy->setListId( SENDY_LIST_ABANDONEDCART_STANDARD_SEARCH );
                                        $sendy_substatus = $sendy->substatus( $post_data["email"] );
                                        $sendy->unsubscribe( $post_data["email"] );

                                            $sendy->setListId( SENDY_LIST_SITE_SIGNUP );
                                            $sendy->subscribe(array(
                                                'email' => $post_data["email"],
                                                "name" => ( ! empty( $post_data["billing_firstname"] ) ? $post_data["billing_firstname"] . " " : "" ) . ( ! empty( $post_data["billing_lasttname"] ) ? $post_data["billing_lasttname"] : "" )
                    
                                            ));


                                    }
                                    if( ! empty( $membership["tokens_image"] ) ){
                                        // image search
                                        $sendy->setListId( SENDY_LIST_ABANDONEDCART_IMAGE_SEARCH );
                                        $sendy_substatus = $sendy->substatus( $post_data["email"] );
                                        $sendy->unsubscribe( $post_data["email"] );


                                            $sendy->setListId( SENDY_LIST_RIS );
                                            $sendy->subscribe(array(
                                                'email' => $post_data["email"],
                                                "name" => ( ! empty( $post_data["billing_firstname"] ) ? $post_data["billing_firstname"] . " " : "" ) . ( ! empty( $post_data["billing_lasttname"] ) ? $post_data["billing_lasttname"] : "" )
                    
                                            ));

                                    }
                                    if( empty( $membership["tokens_email"] ) && empty( $membership["tokens_image"] ) ){
                                        // hire us search
                                        $sendy->setListId( SENDY_LIST_ABANDONEDCART_SEARCH_SPECIALIST );
                                        $sendy_substatus = $sendy->substatus( $post_data["email"] );
                                        $sendy->unsubscribe( $post_data["email"] );

                                            $sendy->setListId( SENDY_LIST_INDEPTH );
                                            $sendy->subscribe(array(
                                                'email' => $post_data["email"],
                                                "name" => ( ! empty( $post_data["billing_firstname"] ) ? $post_data["billing_firstname"] . " " : "" ) . ( ! empty( $post_data["billing_lasttname"] ) ? $post_data["billing_lasttname"] : "" )
                    
                                            ));

                                    }
                                }
                                ############## END add emails to sendy list

                            }

                            if ( ! empty( $token_session_data["image_search_data"] ) ) {

                                $reduce_image_token = Search::assign_image_search_to_user( $token_session_data["image_search_data"]["pending_image_id"], $user_id );

                            }

                            //send verification email
                           user::send_verification_link($user_id, $post_data["email"]);

                        }

                    } else {

                        if ( ! DEBUG && ! $testcase_is_running ) {

                            if ( ! empty( $membership["mailing_list"] )  && ! $do_not_add_to_sendy_lists ) {

                                $sendy = SYSTEM::loadsendy();
                                $sendy_list_id = explode( ",", strtoupper( str_replace( " ", "", $membership["mailing_list"] ) ) );

                                $add_to_multiple_list = false;

                                if( ! empty( $sendy_list_id ) ) {

                                    foreach ( $sendy_list_id as $_sendy_list_id ){

                                        switch( $_sendy_list_id ){

                                            case "INDEPTH" :

                                                ## check to see if current user email in cludes in idfferent search type
                                                $sendy->setListId( SENDY_LIST_SITE_SIGNUP );
                                                $sendy_substatus = $sendy->substatus( $cc_email );
                                                if( ! empty( $sendy_substatus["message"] ) && 'Subscribed' == $sendy_substatus["message"] ){

                                                    $add_to_multiple_list = true;
                                                    $sendy->unsubscribe( $cc_email );

                                                }

                                                ## check to see if current user email in cludes in idfferent search type
                                                $sendy->setListId( SENDY_LIST_RIS );
                                                $sendy_substatus = $sendy->substatus( $cc_email );
                                                if( ! empty( $sendy_substatus["message"] ) && 'Subscribed' == $sendy_substatus["message"] ){

                                                    $add_to_multiple_list = true;
                                                    $sendy->unsubscribe( $cc_email );

                                                }
                                                break;

                                            case "SITE_SIGNUP" :

                                                ## check to see if current user email in cludes in idfferent search type
                                                $sendy->setListId( SENDY_LIST_INDEPTH );
                                                $sendy_substatus = $sendy->substatus( $cc_email );
                                                if( ! empty( $sendy_substatus["message"] ) && 'Subscribed' == $sendy_substatus["message"] ){

                                                    $add_to_multiple_list = true;
                                                    $sendy->unsubscribe( $cc_email );

                                                }

                                                ## check to see if current user email in cludes in idfferent search type
                                                $sendy->setListId( SENDY_LIST_RIS );
                                                $sendy_substatus = $sendy->substatus( $cc_email );
                                                if( ! empty( $sendy_substatus["message"] ) && 'Subscribed' == $sendy_substatus["message"] ){

                                                    $add_to_multiple_list = true;
                                                    $sendy->unsubscribe( $cc_email );

                                                }
                                                break;

                                            case "RIS" :

                                                ## check to see if current user email in cludes in idfferent search type
                                                $sendy->setListId( SENDY_LIST_INDEPTH );
                                                $sendy_substatus = $sendy->substatus( $cc_email );
                                                if( ! empty( $sendy_substatus["message"] ) && 'Subscribed' == $sendy_substatus["message"] ){

                                                    $add_to_multiple_list = true;
                                                    $sendy->unsubscribe( $cc_email );

                                                }

                                                ## check to see if current user email in cludes in idfferent search type
                                                $sendy->setListId( SENDY_LIST_SITE_SIGNUP );
                                                $sendy_substatus = $sendy->substatus( $cc_email );
                                                if( ! empty( $sendy_substatus["message"] ) && 'Subscribed' == $sendy_substatus["message"] ){

                                                    $add_to_multiple_list = true;
                                                    $sendy->unsubscribe( $cc_email );

                                                }
                                                break;

                                            default :
                                                break;

                                        }

                                    }

                                }

                                if ( $add_to_multiple_list ) {

                                    $sendy->setListId( SENDY_LIST_MULTIPLE_SEARCHES );
                                    $sendy->subscribe(array(
                                        'email' => $cc_email,
                                        "name" => ( ! empty( $cc_billing_firstname ) ? $cc_billing_firstname . " " : "" ) . ( ! empty( $cc_billing_lastname ) ? $cc_billing_lastname : "" )
                                            
                                    ));

                                }

                            }

                        }

                        if ( $user_data["membership_plan"] ) User::update( $user_id, [ "membership_plan" => null ] );
                        $_SESSION["extract"]["success_messages"][] = "Thank you for your payment. Account upgraded.";                        
                        //Todo: If there are any active same type subscriptions, cancel it.

                    }

                    $plan_data = [
                        "membership_id" => $membership["id"],
                        "search_type" => $cc_search_type,
                        "payment_gateway" => $payment_gateway,
                        "card_number" => ( ! empty( $cc_card_number ) ? str_repeat( "X", strlen( $cc_card_number ) - 4 ) . substr( $cc_card_number, -4 ) : "" ),
                        "card_type" => ( ! empty( $card_type ) ? $card_type : "" ),
                        "subscription" => ( $is_subscription ? 1 : 0 ),
                        "subscriber_email" => ( ! empty( $cc_subscriber_email ) ? $cc_subscriber_email : "" ),
                        "active" => ( $is_subscription ? 1 : 0 ),
                        "next_payment" => "",
                        "billing_firstname" => $cc_billing_firstname,
                        "billing_lastname" => $cc_billing_lastname,
                        "billing_address1" => ( ! empty( $cc_billing_address1 ) ? $cc_billing_address1 : "" ),
                        "billing_address2" => ( ! empty( $cc_billing_address2 ) ? $cc_billing_address2 : "" ),
                        "billing_country" => ( ! empty( $cc_billing_country ) ? $cc_billing_country : "" ),
                        "billing_city" => ( ! empty( $cc_billing_city ) ? $cc_billing_city : "" ),
                        "billing_state" => ( ! empty( $cc_billing_state ) ? $cc_billing_state : "" ),
                        "billing_postal_code" => $cc_billing_postal_code,
                        "billing_phone" => ( ! empty( $cc_billing_phone ) ? $cc_billing_phone : "" ),
                        "date" => date( "Y-m-d H:i:s"),
                        "from_plan" => ( ! empty( $token_session_data["delete_membership_id"] ) ? $token_session_data["delete_membership_id"] : NULL ),
                        "associated_image_search" => ( ! empty( $token_session_data["associated_image_search"] ) ? $token_session_data["associated_image_search"] : NULL ),
                        "associated_search_cache" => ( ! empty( $token_session_data["associated_search_cache"] ) ? $token_session_data["associated_search_cache"] : NULL ),
                        "associated_search_person_cache" => ( ! empty( $token_session_data["associated_search_person_cache"] ) ? $token_session_data["associated_search_person_cache"] : NULL ),
                    ];

                    if ( $is_subscription && ! empty( $response_subscription ) ) {

                        $plan_data = array_merge( $plan_data, [
                            "subscription_profile" => ! empty( $subscription_profile_id ) ? $subscription_profile_id : "",
                            "next_payment" => date( "Y-m-d", $start_subscription ),
                        ] );

                    }

                    $plan_data["hash"] = md5( json_encode( $plan_data ) );

                    // Ad Tracking
                    if ( ! empty( $_SESSION["advertisement_data"]["source"] ) || ! empty( $_SESSION["advertisement_data"]["campaign"] ) ) {

                        $plan_data["ref_source"] = $_SESSION["advertisement_data"]["source"];
                        $plan_data["ref_campaign"] = $_SESSION["advertisement_data"]["campaign"];

                    }

                    // IF membership is a one time premium data request for a single search query
                    if ( $membership["type"] == 'premium_data' || $membership["type"] == 'premium_data_monthly' ) {

                        $premium_request_id = isset( $token_session_data["query_data"]['premium_request_id'] ) ? $token_session_data["query_data"]['premium_request_id'] : '';
                        $plan_data["redirect_uri"] = isset( $token_session_data["query_data"]['redirect_uri'] ) ? $token_session_data["query_data"]['redirect_uri'] : '';
                        $plan_data["custom_membership_reference"] = $premium_request_id;

                        Search::update_premium_data_requests( $premium_request_id , 1 );

                    }

                    $plan_data["email"] = $cc_email;
                    $plan_id = Membership::add_plan_to_user( $user_id, $plan_data );
                    if( $plan_data["membership_id"] == PLAN_UNLIMITED_GENERAL_BOOSTED_ONETIME ) {
                        $premium_data_id = Search::premium_data_request_log( $user_id , $person_id, 0 );
                        Search::update_premium_data_requests( $premium_data_id , 1 );
                    }

                    //Check membership type to see if there are any pending subscription deletions
                    if ( ! empty( $token_session_data["delete_membership_id"] ) && ! empty( $user_id ) ) {

                        //Downgrade a user subscription by super admin
                        $new_membership_userid = ( ! empty( $token_session_data["new_membership_userid"] ) && 255 == $user_data["user_level"] ) ? $token_session_data["new_membership_userid"] : $user_id;
                        Membership::cancel_subscription( $new_membership_userid, $token_session_data["delete_membership_id"], "Downgraded or upgraded", $plan_id, $user_id );
                        sleep( 1 );

                    }

                    //Cancel if there is any related duwngrade plan
                    $auto_cancelled_downgraded_plan = false;              
                    $new_membership_details = Membership::get( $plan_data["membership_id"] );
                    if(! empty( $new_membership_details ) && ! empty( $new_membership_details["avoid_cancellation_alert"] ) && in_array( $new_membership_details["avoid_cancellation_alert"], ["avoid-cancel-regular", "avoid-cancel-ris"] ) ){

                        $get_all_active_plans = Membership::get_user_plans( $user_id, true );
                        if( ! empty( $get_all_active_plans ) )
                        foreach ( $get_all_active_plans as $active_plan ){
                            $old_membership_details = Membership::get( $active_plan['membership_id'] );
                            
                            if( ! empty( $old_membership_details ) && $old_membership_details['type'] == $new_membership_details["avoid_cancellation_alert"] ){
                                $auto_cancelled_downgraded_plan = true;
                                Membership::cancel_subscription( $user_id, $active_plan['id'], "Auto cancelled downgraded plan when upgrade", $plan_id, $user_id );
                                sleep( 1 );
                                
                            }
                        }

                    }
                    //die("ss");
                    $_SESSION["combine_dashboard_popup"] = true;
                    $combine_plan = false;

                    $get_all_active_plans = Membership::get_user_plans( $user_id, true );
                    foreach ( $get_all_active_plans as $active_plan ){
                        if( $active_plan['membership_id'] == PLAN_UNLIMITED_5496 ){
                            $combine_plan = true;
                            break;
                        }
                    }

                    // Cancel old plans if user registered for combine plan
                    if ( $combine_plan ){
                        $cancelled_by_id = 999999997;
                        foreach( $current_memberships as $row ){
                            $cancel_old_subscription = Membership::cancel_subscription( $user_id, $row, "Registered for combine plan", $plan_id, $cancelled_by_id );
                        }
                        unset( $token_session_data["cancel_old_plan_if_combined"] );
                        unset( $_SESSION["dashboard_addon_request"] );
                        unset( $_SESSION["combine_dashboard_popup"] );

                    }

                    if ( $payment_method == "amazonpay" ){

                        $amazon_pay_cron_data = [
                            "plan"	=>	$plan_id,
                            "last_payment_amount"	=>	$membership["initial_amount"],
                            "last_payment_date"	=>	date( "Y-m-d" ),
                            "last_payment_status"	=>	empty( $payment_error ) ? 1 : 0,
                            "next_retry_date"	=> date( "Y-m-d", $start_subscription ),
                            "next_charge_amount"	=> ! empty( $membership["recurring_amount"] ) ? $membership["recurring_amount"] : null,
                            "is_subscription"	=>	$is_subscription ? 1 : 0,
                            "last_payment_type"	=>	$is_subscription ? ( $membership["start_recurring_frequency"] > 0 ? "trial" : "initial"  ) : "initial",
                            "payment_tracking_data_id"	=>	! empty( $payment_tracking_data_id ) ? $payment_tracking_data_id : ""
                        ];

                        Membership::add_amazon_pay_cron_data( $amazon_pay_cron_data );

                    }

                    Membership::add_payment( $plan_id, [
                    	"email" => $cc_email,
                        "amount" => $membership["initial_amount"],
                        "txn_id" => ! empty( $transaction_id ) ? $transaction_id : "",
                        "date" => date("Y-m-d H:i:s"),
                    ] );

                    $membership_tokens = Membership::get_tokens( $membership );
                    if ( $membership["one_search_type"] ) {

                        $search_type = $search_types[ $cc_search_type ];
                        User::add_remove_tokens( $user_id, [
                            $search_type => $membership_tokens[ $search_type ],
                        ] );

                        if ( ! empty( $_SESSION["search_params"] ) ) {

                            $_SESSION["extract"]["search_params"] = $_SESSION["search_params"];
                            $membership["redirect_url"] = "search.html";

                        }

                    } else {

                        $method_of_add_remove_tokens = $auto_cancelled_downgraded_plan ? "reset" : "add";
                        User::add_remove_tokens( $user_id, $membership_tokens, $method_of_add_remove_tokens );
                        
                    }

                    if ( ! empty( $reduce_image_token ) ) {

                        User::add_remove_tokens( $user_id, [
                            "image" => 1
                            ], "remove" );

                    }

                    $cancelled_by_id = 999999996;
                    //cancel paused plans
                    $pause_regular30_is_active = ! empty( $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_30DAYS ] ) ? true : false;
                    $pause_regular60_is_active = ! empty( $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_60DAYS ] ) ? true : false;
                    $pause_regular90_is_active = ! empty( $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_90DAYS ] ) ? true : false;

                    if( $membership["id"] == PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL ){

                        if( $pause_regular30_is_active ) Membership::cancel_subscription( $user_id, $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_30DAYS ], "", null, $cancelled_by_id );
                        if( $pause_regular60_is_active ) Membership::cancel_subscription( $user_id, $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_60DAYS ], "", null, $cancelled_by_id );
                        if( $pause_regular90_is_active ) Membership::cancel_subscription( $user_id, $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_90DAYS ], "", null, $cancelled_by_id );

                    }

                    $pause_ris30_is_active = ! empty( $active_memberships[ PLAN_AVOID_PAUSE_RIS_30DAYS ] ) ? true : false;
                    $pause_ris60_is_active = ! empty( $active_memberships[ PLAN_AVOID_PAUSE_RIS_60DAYS ] ) ? true : false;
                    $pause_ris90_is_active = ! empty( $active_memberships[ PLAN_AVOID_PAUSE_RIS_90DAYS ] ) ? true : false;

                    if( $membership["id"] == PLAN_UNLIMITED_IMAGE_5_DAY_TRIAL ){

                        if( $pause_ris30_is_active ) Membership::cancel_subscription( $user_id, $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_30DAYS ], "", null, $cancelled_by_id );
                        if( $pause_ris60_is_active ) Membership::cancel_subscription( $user_id, $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_60DAYS ], "", null, $cancelled_by_id );
                        if( $pause_ris90_is_active ) Membership::cancel_subscription( $user_id, $active_memberships[ PLAN_AVOID_PAUSE_REGULAR_90DAYS ], "", null, $cancelled_by_id );

                    }
                    //PLAN_AVOID_PAUSE_REGULAR_30DAYS

                    if ( true) {

                        $related_memberships = "a different type of search that is not include in this plan";
                        if( ! empty( $membership["related_membership_ids"] ) ){

                            $_related_memberships = [];
                            $related_membership_ids = explode(",", $membership["related_membership_ids"]);
                            if( ! empty( $related_membership_ids ) ){

                                foreach( $related_membership_ids as $relm_k =>  $related_membership_id ){

                                    $related_membership = Membership::get( $related_membership_id );
                                    if( ! empty( $related_membership ) ){

                                        $_related_memberships[] = $related_membership["title"];

                                    }

                                }

                                $related_memberships = "'" . implode("' or '", $_related_memberships) . "'";

                            }

                        }

                        $welcome_confirmation_template = "welcome-confirmation";
                        if ( PLAN_PREMIUM_DATA == $membership["id"] ) $welcome_confirmation_template = "welcome-confirmation-premiumdata";
                        elseif( PLAN_PREMIUM_DATA_MONTHLY == $membership["id"] ) $welcome_confirmation_template = "welcome-confirmation-premiumdata-monthly";
                        elseif( PLAN_RIS_BOOSTED_UNLIMITED == $membership["id"] ) $welcome_confirmation_template = "welcome-boosted-unlimited";
                        elseif( PLAN_DR_PHIL_TRIAL_PLAN == $membership["id"] ) $welcome_confirmation_template = "welcome-confirmation-dr-phil-free";
                        elseif( PLAN_CRIMINAL_REPORT == $membership["id"] || PLAN_CRIMINAL_RECORDS_ADDON == $membership["id"] ) $welcome_confirmation_template = "welcome-confirmation-criminal-records"; 

                       if($membership["recurring_frequency"]  == 1){
                        // Send Welcome/Confirmation Email
                        $email_template_data = SCF::get_mail_template( $welcome_confirmation_template, [
                            "membership_title" => $membership["title"],
                            "transaction_id" => $transaction_id,
                            "date" => SCF::tz_convert( time(), "F d, Y" ),
                            "account" => $cc_email,
                            "initial_amount" => $membership["initial_amount"],
                            "recurring_amount" => $membership["recurring_amount"],
                            "start_recurring_frequency" => $membership["start_recurring_frequency"],
                            "start_recurring_date" => SCF::tz_convert( $start_subscription, "F d, Y" ),
                            "start_recurring_period" => $membership["start_recurring_period"],
                            "related_memberships_text" => $related_memberships,
                            "email" => $cc_email,
                            "title" =>  "SocialCatfish Membership Information"

                        ]);

                        $mailer = SCF::get_mailer();
                        $mailer->addAddress( $cc_email, "{$cc_billing_firstname} {$cc_billing_lastname}" );
                        $mailer->Subject = "SocialCatfish Membership Information";
                        $mailer->msgHTML( $email_template_data["html"] );
                        $mailer->AltBody = $email_template_data["text"];
                        $mailer->send();
                    }

                        if ( preg_match( "/^avoid-cancel|avoid_pause/i", $membership["type"] ) ) {

                            if ( preg_match( "/^avoid-cancel/i", $membership["type"] ) ) {

                                $email_title_cancel_alternative = "SocialCatfish Membership Downgraded";

                                $email_template_data = SCF::get_mail_template( "cancel-alternative-downgrade-to-essentials", [
                                    "subscription_amount" => ( preg_match( "/trial/i", $membership["type"] ) ? "9.99" : "9.99" ),
                                    "title" => $email_title_cancel_alternative,
                                    "email" =>  $cc_email
                                ] );

                            } else if( preg_match( "/^avoid_pause/i", $membership["type"] ) ){

                                $email_title_cancel_alternative = "SocialCatfish Membership Paused";
                                $email_template_data = SCF::get_mail_template( "cancel-alternative-paused", [
                                    "break_period" => $membership["start_recurring_frequency"],
                                    "title" => $email_title_cancel_alternative,
                                    "email" =>  $cc_email
                                ] );
                            }

                            $mailer = SCF::get_mailer();
                            $mailer->addAddress( $cc_email, "{$cc_billing_firstname} {$cc_billing_lastname}" );
                            $mailer->addBCC( "ruwan@socialcatfish.com", "SocialCatfish" );
                            $mailer->Subject = $email_title_cancel_alternative;
                            $mailer->msgHTML( $email_template_data["html"] );
                            $mailer->AltBody = $email_template_data["text"];
                            $mailer->send();

                        }

                        // Notification Email for investigator
                        if ( IN_DEPTH_SEARCH_MEMBERSHIP_ID_NEW == $membership["id"] ) {

                            if( empty( $cc_email ) ) $cc_email = ! empty( $post_data["email"] ) ? $post_data["email"] : "";
                            if( empty( $cc_billing_phone ) ) $cc_billing_phone = ! empty( $post_data["billing_phone"] ) ? $post_data["billing_phone"] :  $_SESSION["specialist_number"];
                            if( empty( $cc_billing_firstname ) ) $cc_billing_firstname = ! empty( $post_data["billing_firstname"] ) ? $post_data["billing_firstname"] : "";
                            if( empty( $cc_billing_lastname ) ) $cc_billing_lastname = ! empty( $post_data["billing_lastname"] ) ? $post_data["billing_lastname"] : "";

                            $email_template_data = SCF::get_mail_template( "new-indepth-notification", [
                                "membership_title" => $membership["title"],
                                "transaction_id" => $transaction_id,
                                "date" => SCF::tz_convert( time(), "F d, Y" ),
                                "account" => $cc_email,
                                "billing_phone" => ( ! empty ( $cc_billing_phone )? $cc_billing_phone :  $_SESSION["specialist_number"] ),
                                "billing_firstname" => $cc_billing_firstname,
                                "billing_lastname" => $cc_billing_lastname,
                            ]);
                            $mailer = SCF::get_mailer();

                            $mailer->addAddress( "investigator@socialcatfish.com", "Socialcatfish Investigator" );
                            $mailer->addAddress( "investigations@socialcatfish.com", "Breanne McClellan" );
                            $mailer->Subject = "New SocialCatfish In-Depth Search";
                            $mailer->msgHTML( $email_template_data["html"] );
                            $mailer->AltBody = $email_template_data["text"];
                            $mailer->send();

                            //Email notification to user
                            $email_template_data_indepth = SCF::get_mail_template( "indepth-register", [
                                "title" =>  "Welcome to Social Catfish's Search Specialist Service!",
                                "email" =>  $cc_email
                            ] );
                            $mailer_indepth = SCF::get_mailer();
                            $mailer_indepth->addAddress( $cc_email, "Socialcatfish User" );
                            $mailer_indepth->Subject = "Welcome to Social Catfish's Search Specialist Service!";
                            $mailer_indepth->msgHTML( $email_template_data_indepth["html"] );
                            $mailer_indepth->AltBody = $email_template_data_indepth["text"];
                            $mailer_indepth->send();

                        }

                    }

                    if ( ! $testcase_is_running ) {

                        // Set Google eCommerce Tracking
                        $_SESSION["extract"]["gtm_events"]["conversion"] = [
                            "transactionId" => $plan_id,
                            "transactionAffiliation" => 'SocialCarfish.com' . ( ! empty( $_SESSION["advertisement_data"] ) ? " ({$_SESSION["advertisement_data"]["source"]})" : "" ) . " - {$payment_gateway}",
                            "transactionTotal" => (float) $membership["initial_amount"],
                            "transactionProducts" => [
                                [
                                    "name" => $membership["title"],
                                    "sku" => $membership["id"],
                                    "price" => $membership["initial_amount"],
                                    "quantity" => 1,
                                ],
                            ],
                            "event" => "conversion",
                        ];

                        FacebookConversionsAPI::puchase_event($payment_tracking_data["amount"],$payment_tracking_data["email"],$plan_id);

                    }
                    // Assign search data to user
                    if ( ! empty( $token_session_data["add_search"] ) ) {

                        $search_id = Search::add_search_to_user( $user_id, $plan_id, $token_session_data["add_search"],$user_data["user_level"] );
                        //Run billable crim search IDI API call after user purchases report
                         $full_name_arr = explode( " ", $token_session_data["add_search"]["data"]["full_name"] );
                            $last_name = array_pop( $full_name_arr );
                            $first_name = reset($full_name_arr);
                
                            $params =  [
                                "first_name" => $first_name,
                                "last_name" => $last_name,
                                "state" => $token_session_data["add_search"]["data"]["state"],               
                                "search_type" => "CriminalSearch",
                                "fields" => '["criminal"]',
                                "dob" => $token_session_data["add_search"]["data"]["dob"],];
                           
                            // Fetch Records from the API            
                            $ds_idi = new \DataSource\IDI( IDI_CLIENT_ID, IDI_SECRET_KEY, DEBUG );
                            $records = $ds_idi->runSearch( $ds_idi->mapParams( $params ) );  
                    }
                    if ($_SESSION["add_cr_to_new_user"]) {
                        CriminalRecords::update_user_search( $user_id,$plan_id, $_SESSION["cr_id"]);
                        unset($_SESSION["add_cr_to_new_user"]);
                        
                    }

                    //if privacy lock activated
                    if( $privacy_lock_activated ) PWNED::save_user_pawned_data( $cc_email );

                    // Thank you page data
                    $_SESSION["thankyou_page_data"] = $plan_data;
                    $_SESSION["thankyou_page_data"]["txn_id"] = $transaction_id;
                    $_SESSION["thankyou_page_data"]["session"] = $_SESSION["tokens"][ $token ];
                    $_SESSION["thankyou_page_data"]["session"]["membership"] = $membership;
                    $_SESSION["thankyou_page_data"]["redirect_url"] = $_SESSION["redirect_link"];

                    unset( $_SESSION["tokens"][ $token ], $_SESSION["search_params"] );
                    unset( $_SESSION["regular_premium_combine"] );

                    if($membership["id"] == PLAN_CRIMINAL_RECORDS_ADDON){
                        $membership["redirect_url"] = "criminal_report/{hash}/";
                    }

                    //Running RAS for users who purchase sunbscription through RAS Landingg page
                    if(!empty($_SESSION["ras_data"]) ){
                        $user_data["tokens_address"] = 10000;
                        $msd= new melissadata\MelissaDataRAS(MELISSADATA_API_KEY);
                        $results = $msd->search_address($_SESSION["ras_data"]["address"],$_SESSION["ras_data"]["city"],$_SESSION["ras_data"]["state"],$_SESSION["ras_data"]["state"],$_SESSION["ras_data"]["lat"],$_SESSION["ras_data"]["lng"]);
                        $membership["redirect_url"] = "ras_report/?id=".$results;
                        unset($_SESSION["ras_data"]);

                    }

                    if ( PLAN_RIS_BOOSTED_ONETIME == $membership["id"] ) {

                        Search::ris_boost_search( $token_session_data["hash"] );

                    } elseif ( PLAN_RIS_BOOSTED_UNLIMITED == $membership["id"] ) {

                        Search::ris_rerun_search( $token_session_data["hash"] );

                    }

                    // Membership Redirection URL
                    if ( $membership["redirect_url"] ) {
                        if ( PLAN_CRIMINAL_REPORT == $membership["id"] || $membership["id"] == PLAN_CRIMINAL_RECORDS_ADDON) {
                            $hash_replacement = SYSTEM::sanitize( $query_data["name"] ) . "-{$search_id}";
                            $button_caption = "View Criminal Records Now";
                        } elseif ( "premium_data" == $membership["type"] || "premium_data_monthly" == $membership["type"] ) {

                            $pid = [ $_SESSION["premium_data"]["pid"] ];                                
                            $params =  [
                                "pidlist" => $pid];                               
                            // Fetch Records from the API            
                            $ds_idi = new \DataSource\IDI( IDI_CLIENT_ID, IDI_SECRET_KEY, DEBUG );
                            $records = $ds_idi->runSearch( $ds_idi->mapParams( $params ),true,true ); 
                            search::insert_idi_premium_data($_SESSION["premium_data"]["cache_id"],$records["data"][0]);
                            unset( $_SESSION["premium_data"]);
                            // AB tracking for premium upsell data
                            // if (isset($_SESSION["ab_premium_upsell"])) {
                            //     $_SESSION["ab_premium_upsell"]->track_event("conversion", $device);
                            // }

                            $hash_replacement = $plan_data["redirect_uri"];
                            $button_caption = "View Premium Data Now";

                        } elseif ( ! empty( $token_session_data["hash"] ) ) {

                            $hash_replacement = $token_session_data["hash"];

                        } else {

                            $hash_replacement = $plan_data["hash"];
                            $button_caption = "Start Running Searches Now";

                        }

                        $_SESSION["thankyou_page_data"]["redirect_url"] = RELATIVE_URL . str_replace( "{hash}", $hash_replacement, $membership["redirect_url"] );
                        $_SESSION["thankyou_page_data"]["button_caption"] = $button_caption;
                    }

                    // Disable the FB Group Popup for In-Depth Users
                    if ( MEMBERSHIP_CATEGORY_HIRE_US == $membership["category"] ) {

                        User::set_meta( $user_id, 'disable_fb_group_popup', 1 );
                        $_SESSION["user"]["disable_fb_group_popup"] = 1;

                    }

                    if( ! empty( $amazon_pay_ab_test ) ){

                        if( $payment_gateway == "amazonpay" ) $amazon_pay_ab_test->track_event("conversion_amazon_pay");
                        else $amazon_pay_ab_test->track_event("conversion");

                    }

                    SYSTEM::redirect( PAGE_URL_DASHBOARD );

                }

                if ( empty( $redirect ) ) $redirect = RELATIVE_URL . "membership-levels/?token={$token}";
                SYSTEM::redirect( $redirect );

            } else {
                $error_messages = array_slice( $error_messages, 0, 1 );
                Behavior::system_log_action( __FILE__, __LINE__, __METHOD__, "Checkout Error::{$error_messages}", ["errors", "checkout error", "{$error_messages}" ] );
            }

        } else {

            $fields = SYSTEM::array_get_value_from_multi_array_by_key( $form_validation, "name" );
            $post_data = array_merge( $post_data, SYSTEM::array_get_values_for_keys( $token_session_data, $fields ) );

            // Test Data
            if ( DEBUG && ( "new.socialcatfish.com" == $_SERVER["HTTP_HOST"] || 81 == $_SERVER["SERVER_PORT"] ) && empty( $post_data["card_name"] ) ) {

                $random_user = json_decode( file_get_contents( "https://randomuser.me/api/?nat=us" ), true )["results"][0];
                $post_data["card_name"] = ucwords( "{$random_user["name"]["first"]} {$random_user["name"]["last"]}" );
                $post_data["billing_firstname"] = ucwords( $random_user["name"]["first"] );
                $post_data["billing_lastname"] = ucwords( $random_user["name"]["last"] );
                $post_data["email"] = $random_user["email"];
                $post_data["email_confirm"] = $random_user["email"];
                $post_data["card_number"] = ( PAYMENT_GATEWAY_DEFAULT != "usaepay" ) ? "4111111111111111" : "4000100011112224";
                $post_data["card_cvv"] = "111";
                $post_data["card_expiry_month"] = sprintf( "%02f", rand( 1, 12 ) );
                $post_data["card_expiry_year"] = rand( date("Y") + 1, date("Y") + 5 );
                $post_data["billing_address1"] = "{$random_user["location"]["street"]["number"]} {$random_user["location"]["street"]["name"]}";
                $post_data["billing_address2"] = "";
                $post_data["billing_city"] = ucwords( $random_user["location"]["city"] );
                $post_data["billing_postal_code"] = ucwords( $random_user["location"]["postcode"] );
                $post_data["billing_state"] = ucwords( $random_user["location"]["state"] );

                if ( ! empty( $membership["show_phone_number"] ) ) {

                    $post_data["billing_phone"] = "2012001234";

                }

            }

        }

    } else {

        $page_title = "Membership Levels | People Search - SocialCatfish.com";
        $page_description = "Find or verify someone using just an image Find out information about someone with just their name Locate online social profiles (dating profiles, social profiles and work profiles) Get access to criminal records* Find out who lives at an address Verify a business Find out who owns an email Find out who owns a phone &hellip;";
        $sub_page = ( "custom" != $action ) ? ( $_SESSION["price_changed"] ? "register/info-ab.php" : "register/info.php" ) : "register/custom.php";
        $section = $section ?: "initial";

        // 99 cents promo plan
        $section = ( ! empty( $promo ) && "99cents" == $promo ) ? "promo" : $section;

        // Black Friday Promo plans cents promo plan
        $section = ( ! empty( $promo ) && ( "black-friday-2020-all-unlimited" == $promo || "black-friday-2020-all-350" == $promo ) ) ? "black-friday-2020" : $section;

        $_SERVER["HTTP_REFERER"] = ( ! empty( $_SERVER["HTTP_REFERER"] ) ) ? $_SERVER["HTTP_REFERER"] : "";
        if ( "initial" == $section && $user_has_search_tokens ) $section = "initial";
        elseif ( "ris" == $section && $user_has_image_tokens ) $section = "initial";
        elseif ( "upgrade" == $section && empty( $user_id ) ) SYSTEM::redirect( RELATIVE_URL );
        elseif ( "upgrade" == $section ) $section = "initial";

        //if ( 'upgrade' == $section ) $sub_page = "register/customize.php";

        if ( "phone_teaser" == $section ) {

            if ( $user_id ) SYSTEM::redirect( PAGE_URL_REVERSE_PHONE_SEARCH );
            if ( ! $phone_info = AreaCode::phone_number_info( $search_keyword ) ) {

                $module = "404";
                include_once( MODULES_PATH . "404.php" );

            } else {

                $query_data = [
                    "name" => $search_keyword,
                    "partial_locations" => [ "{$phone_info["city"]}, {$phone_info["state"]}" ],
                    "phone_provider" => "{$phone_info["carrier"]} ({$phone_info["line_type"]})",
                ];

            }

            $section = "initial";

        }

        if (strpos($section, 'reset_member_plan_') === 0) {

            $membership_levels = [ Membership::get( str_replace("reset_member_plan_", "", $section ) ) ];

        } else if( ! empty( $load_plan ) ){

            $membership_levels = [ Membership::get( $load_plan ) ];

        } else {

            $membership_levels = Membership::get_membership_levels( $section );

        }


        //Showing RIS membenrship level in standard membership level page by request
        if ( $section == "initial" && empty( $search_params["sid"] ) && empty( $search_params["person_id"] ) ) {

            $membership_levels_ris = Membership::get_membership_levels( 'ris' );
            // $membership_levels_ris[0]["default"] = 0;
            $membership_levels = array_merge( array_slice( $membership_levels, 0, 1 ) , $membership_levels_ris, array_slice( $membership_levels, 1 ) );

        }

        if( ! empty( $promo ) && ( "black-friday-2020-all-unlimited" == $promo || "black-friday-2020-all-350" == $promo ) ){
            if( "black-friday-2020-all-unlimited" == $promo ) $promo_id = PLAN_BLACK_2020_FRIDAY_ALL_UNLIMITED;
            if( "black-friday-2020-all-350" == $promo ) $promo_id = PLAN_BLACK_2020_FRIDAY_ALL_350;

            foreach( $membership_levels as $levels => $level ){

                if( $level["id"] <> $promo_id ) unset( $membership_levels[ $levels ] );

            }

            //CSI-6653 - special approval for a user to create black friday plan after offer ends
            //Must remove this if after the user completed his purchase
            if( ! empty( $user_data["email"] ) && ! in_array( $user_data["email"], ["jhaney@ualberta.ca", "asdasd@appearen.com", "asitha@socialcatfish.com"])){
                //Already registered users cannot purchase this offer
                if( $user_id ) SYSTEM::redirect( PAGE_URL_DASHBOARD );
            } else if( empty( $user_id ) ){
                SYSTEM::redirect( RELATIVE_URL );
            }
            

        }

        foreach( $membership_levels as $levels => $level ) {

            if ( $level["id"] == PLAN_UNLIMITED_MONTHLY_1 ) unset( $membership_levels[ $levels ] );

        }

        if( ! empty( $_SESSION["plan_1_99"] ) ){

            $membership_levels = [ Membership::get( PLAN_RIS_1_99 ) ];

        }

        $validated_mobile_app_user = false;
        if( $is_mobile_app || ! empty( $_SESSION["mobile_app_user"] ) ){
            //validate session  and key with sent email
            //$is_mobile_app_key
            //$is_mobile_app_session
            //$is_mobile_app_email

            $validated_mobile_app_user = true;
            if( ( ! empty( $is_mobile_app_email ) && $validated_mobile_app_user ) || ! empty( $_SESSION["mobile_app_user"] ) ) {

                if( ! empty( $_SESSION["mobile_app_user"] ) ) $user_data = User::get_by_id( $_SESSION["mobile_app_user"] );
                else $user_data = User::get_by_email( $is_mobile_app_email );
                if( ! empty( $user_data ) && ! empty( $user_data["id"] ) ){
                    $user_id = $user_data["id"];
                    $_SESSION["mobile_app_user"] = $user_data["id"];
                    $user_data["walkthrough"] = 0;
                    $membership_levels = [ Membership::get( $is_mobile_app_plan_id ) ];

                }
            }

            if( $is_mobile_app_plan_id == PLAN_PREMIUM_DATA ){
                $premium_requested = Search::premium_data_request_log( $user_id , $is_mobile_app_person_id, 0 );

                $query_data['premium_request_id'] = $is_mobile_app_person_id;
                $query_data = [
                    "type_id" => 2,
                    'premium_request_id' => $premium_requested,
                    'premium_monthly' => 0,
                    //'redirect_uri' => "",
                    //"query" => "david",
                    "name" => $is_mobile_app_person_name,
                   // "age" => "10",
                    //"image" => "",
                    //"location" => "loc",
                ];
            }

            $mobile_app = true;
            $exclude_header_footer_content = true;
        }


        // Gray out for logged in user membership plans
        if ( $user_id ) {

            $default_plan = false;
            foreach ( $membership_levels as &$_plan ) {

                if ( $_plan["purchased"] = ( ( ! empty( $active_memberships[ $_plan["id"] ] ) || ! empty( $active_memberships[ PLAN_UNLIMITED_3999 ] ) || ! empty( $active_memberships[ PLAN_UNLIMITED_3999_5_DAY_TRIAL ] ) ) && $_plan["id"] != IN_DEPTH_SEARCH_MEMBERSHIP_ID_NEW ) ? 1 : 0 ) $_plan["default"] = 0;
                elseif ( ! $default_plan ) {

                    $default_plan = true;
                    $_plan["default"] = 1;

                }

            }
            unset( $_plan );

        }


        $max_features_count = 0;
        foreach ( $membership_levels as $index => $membership_level ) {

            if( ! empty( $active_memberships[ PLAN_DR_PHIL_SPECIAL_OFFER ] ) && in_array( $membership_level["id"], [ PLAN_UNLIMITED_IMAGE_5_DAY_TRIAL, PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL ] ) ){
                unset( $membership_levels[ $index ] );
                continue;
            }


            if( ! empty( $active_memberships[ PLAN_UNLIMITED_IMAGE_SWITCHED ] ) && $membership_level["id"] == PLAN_UNLIMITED_IMAGE_5_DAY_TRIAL ){
                $membership_levels[ $index ]["purchased"] = 1;
            }

            if( ! empty( $active_memberships[ PLAN_UNLIMITED_GENERAL_SWITCHED ] ) && $membership_level["id"] == PLAN_UNLIMITED_GENERAL_5_DAY_TRIAL ){
                $membership_levels[ $index ]["purchased"] = 1;
            }

            //avoid duplicate signups for selected membership plans
            //if( $membership_level['avoid_duplicate_signups'] && in_array( $membership_level['id'], $active_memberships ) ) unset( $membership_levels[ $index ] );

            if ( ( "initial" == $section || "ris" == $section ) && ! empty( $_SESSION["hide_none_ad_memberships"] ) && $membership_level["hide_for_ads"] ) {

                unset( $membership_levels[ $index ] );
                continue;

            }
            if ( count( $membership_level["features"] ) > $max_features_count ) $max_features_count = count( $membership_level["features"] );

        }

        ## New membership plans not available for this user/member.
        if( empty( $membership_levels ) )
            if ( $user_id ) SYSTEM::redirect( PAGE_URL_DASHBOARD );
            else SYSTEM::redirect( RELATIVE_URL );

        $plan_count = count( $membership_levels );
        $plan_bootstrap_width = $plan_count ? intval( 12 / $plan_count ) : 12;

        $search_type_id = ! empty( $query_data["type_id"] ) ? $query_data["type_id"] : "";

        if ( ! empty( $_SESSION["image_token"][ $token ] ) ) {

            $image_search_data = $_SESSION["image_token"][ $token ];
            unset( $_SESSION["image_token"][ $token ] );

        } else {

            $token = md5( time() * rand( 100, 200 ) );
            $image_search_data = [];

        }

        $_SESSION["tokens"][ $token ] = [
            "section" => $section,
            "search_type" => $search_type_id,
            "current_step" => 1,
            "image_search_data" => $image_search_data,
            "query_data" => ( ! empty( $query_data ) ) ? $query_data : [],
            "delete_membership_id" => ( ! empty( $delete_membership_id ) ) ? $delete_membership_id : "",
            "new_membership_userid" => ( ! empty( $delete_membership_userid ) ) ? $delete_membership_userid : "",
        ];
        $token_session_data =& $_SESSION["tokens"][ $token ];

        $cart_progress_data = [];
        if( ! empty( $query_data['premium_request_id'] ) ){

            $cart_progress_data['search_type'] = "premiumdata";
            $cart_progress_data['search_reference']['person_id'] = $query_data['premium_request_id'];

        }

        User::log_user_cart_progress( $token, $cart_progress_data );

        if ( empty( $token_session_data["membership_id"] ) ) {


            if ( ! empty( $id ) && ( $membership = Membership::get( $id ) ) ) {
                $token_session_data["membership_id"] = $id;
                $token_session_data["membership"] = $membership;
                $redirect = RELATIVE_URL . "membership-levels/?token={$token}";
                SYSTEM::redirect( $redirect );
            }
            // } else $redirect = RELATIVE_URL;


        }

        if ( ! empty( $user_id ) ) {

            $token_session_data["billing_address1"] = $user_data["address_1"];
            $token_session_data["billing_address2"] = $user_data["address_2"];
            $token_session_data["billing_city"] = $user_data["city"];
            $token_session_data["billing_state"] = $user_data["state"];
            $token_session_data["billing_postal_code"] = $user_data["postal_code"];
            $token_session_data["billing_country"] = $user_data["country"];

        }

        if ( ( ! empty( $query_data ) || $image_search_data ) ) {

            if ( count( $membership_levels ) > 1 ) {

            	$membership_levels = [ ( "initial" == $section ) ? Membership::get( PLAN_UNLIMITED_3_DAY_SS ) : ( ( "ris" == $section ) ? Membership::get( PLAN_UNLIMITED_3_DAY_RIS ) : array_shift( $membership_levels ) ) ];

			}
            $token_session_data["current_step"] = 1;
            $query_data = $token_session_data["query_data"];

        }

        if ( count( $membership_levels ) == 1 ) {

            $membership = array_shift( $membership_levels );
            $token_session_data["membership_id"] = $membership["id"];
            $token_session_data["membership"] = $membership;

            if ( preg_match( "/^reset_member_plan_|avoid-cancel|black-friday|avoid_pause|premium_data|switched_but/i", $token_session_data["section"] ) ) {

                $redirect = RELATIVE_URL . "membership-levels/?token={$token}";
                SYSTEM::redirect( $redirect );

            }

            $direct_token = $token;
            include( MODULES_PATH . "register.php" );

        }

        if( ! empty( $dashboard_membership_addon_request ) ){
            $redirect = RELATIVE_URL . "membership-levels/?token={$token}&dashboard_membership_addon_request={$dashboard_membership_addon_request}";
            SYSTEM::redirect( $redirect );

        }

    }

    if( $settings["indepth_waiting_list"] == "1" && $membership["id"] == IN_DEPTH_SEARCH_MEMBERSHIP_ID_NEW ) SYSTEM::redirect( BASE_URL . "search-specialist-waitlist/" );

    if ($membership["id"] == IN_DEPTH_SEARCH_MEMBERSHIP_ID_NEW ) $include_popup[] = "phone_required";
