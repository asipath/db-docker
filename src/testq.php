<?php

$query_text = !empty($result["name"]) ? $result["name"] : (!empty($search_data["cached_data"]["query"]) ? $search_data["cached_data"]["query"] : (!empty($search_data["ras_records"]) ? $search_data["ras_records"]["property_info"]["address"] : "User Uploaded Image"));

// Remove Affiliate Links
foreach ($result["urls"] as $_index => $_link) {
    $uri = parse_url($_link);
    if (empty($uri["host"]) || preg_match("/(?:spokeo|archives|yellowpages|whitepage|whitepages|peoplefinders|10digits|beenverified|instantcheckmate)\.(?:com|us|org|net|plus)/i", $uri["host"])) {
        unset($result["urls"][$_index]);
    }
}
unset($_index, $_link);
unset($_SESSION["ris_intermediate"]);

$phone_number_search = ($search_data["type"] == SEARCH_TYPE_PHONE);
$email_number_search = ($search_data["type"] == SEARCH_TYPE_EMAIL);
//IPQ email search for report enrichment
$report_id = (!empty($result["id"])) ? $result["id"] : $search["cache_id"];
$email = ($email_number_search && !empty($search_data["cached_data"]['query']) ? $search_data["cached_data"]['query'] : $result["emails"][0]);
if (!empty($email) && $pwnd_and_IPQ_only) {
    //if( ! empty($email) && (($report_id>0)|| $pwnd_and_IPQ_only) ) {

    $get_ipq_email = $report_id ? IPQ::get_cached_data($report_id, SEARCH_TYPE_EMAIL) : null;

    if (empty($get_ipq_email)) {
        //$get_ipq_email = IPQ::search_email($email); //$query_text, Move to module
        //IPQ::save_cached_data( $get_ipq_email, $report_id, SEARCH_TYPE_EMAIL );
    }
    $pwned_data = PWNED::check_PWNED($email);
}

//IPQ phone search for report enrichment
$phone = ($phone_number_search ? $query_text : (!empty($search_data["cached_data"]['query']) ? $search_data["cached_data"]['query'] : $result["phones"][0]));
//if( ! empty( $phone) && (($report_id>0)|| $pwnd_and_IPQ_only)) {
if (!empty($phone) && $pwnd_and_IPQ_only) {
    $phone_number_filter = preg_replace("/[^0-9]/", '', $phone);
    $get_ipq_phone = $report_id ? IPQ::get_cached_data($report_id, SEARCH_TYPE_PHONE) : null;
    if (empty($get_ipq_phone)) {
        //$get_ipq_phone = IPQ::search_phone( $phone_number_filter ); //Move to moduleSS
        //IPQ::save_cached_data( $get_ipq_phone, $report_id, SEARCH_TYPE_PHONE );
    }
    $pwned_data = PWNED::check_PWNED($phone_number_filter);
}

// Pwned data variable from module
//$pwned_data=null;
//$get_ipq_phone=null;
//$get_ipq_email=null;

?>
<div class="scf-report" <?php SCF::js_controller("search.report_scroll") ?>>
    <div class="container">
        <?php if (!empty($privacy_lock_first_visit)) { ?>
            <div class="scf-pl-activated">
                <div class="row">
                    <div class="col-md-9">
                        <span class="si-secured-fill"></span>
                        <h4>PRIVACY LOCK ACTIVATED</h4>
                        <p>As part of your subscription, the email associated with your account is now enrolled in
                            Privacy Lock. <span>FREE of charge</span>. We will continue to monitor data breaches found
                            in the Dark Web and will notify you if your email account is ever involved. </p>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo PAGE_URL_DASHBOARD; ?>?section=privacy_lock" class="btn btn-bordered-gray">Learn
                            More</a>
                    </div>
                </div>
            </div>
        <?php }
        if ($search_data["type"] != null && ($search_data["type"] == SEARCH_TYPE_USERNAME || $search_data["type"] == SEARCH_TYPE_EMAIL || $search_data["type"] == SEARCH_TYPE_NAME || $search_data["type"] == SEARCH_TYPE_PHONE)) { ?>
            <div class="report-head">
                <?php
                if ($search_data["type"] == 3) {
                    if (!empty($search_data["actual_record_id"])) {
                        $cache_id = $search_data["actual_record_id"];
                    } else {
                        $cache_id = $search_data["cached_data"]["results"][0]["id"];
                    }
                } else {
                    $cache_id = $search_data["cached_data"]["cache_id"];
                }
                $pl_data = User::get_privacy_lock_tracking_report($user_id, $cache_id);
                $privacy_lock = false;
                $privacy_lock_class = (count($pl_data) > 0) ? 'show-success' : 'show-add-to';
                ?>
                <div class="row add-to-privacy <?php echo $privacy_lock_class; ?>">
                    <div class="col-sm-8">
                        <span class="si-secured"></span>
                        <p><span>Is This Your Personal Information?</span></p>
                        <p>Monitor changes to your public data using Privacy Lock.</p>
                    </div>
                    <div class="col-sm-4">
                        <button type="button" class="btn btn-light-blue"
                                data-val="<?php echo $cache_id ?>" <?php SCF::js_controller("privacy_lock.add") ?>><span
                                    class="si-plus"></span>Add to Privacy Lock
                        </button>
                    </div>
                </div>

                <div class="row added-to-privacy <?php echo $privacy_lock_class; ?>">
                    <div class="col-sm-8">
                        <span class="si-secured-fill"></span>
                        <p><span>Successfully Added to Privacy Lock</span></p>
                        <p>Monitoring the report in Privacy Lock since <?php
                        if ($pl_data["first_tracking_date"] == "") {
                            $pl_data["first_tracking_date"] = date(DATE_FORMAT);
                        }
                            echo date(DATE_FORMAT, strtotime($pl_data["first_tracking_date"])); ?></p>
                    </div>
                    <div class="col-sm-4">
                        <a href="<?php echo BASE_URL . "dashboard.html?section=privacy_lock" ?>">
                            <button type="button" class="btn btn-light-green"><span class="si-fullscreen"></span>View in
                                Privacy Lock
                            </button>
                        </a>
                    </div>
                </div>
                <span class="si-close-circle close-btn" <?php SCF::js_controller("privacy_lock.close") ?>></span>
            </div>
        <?php } ?>
        <?php
        if (!empty($search_data) && isset($search_data["type"]) && $search_data["type"] <> SEARCH_TYPE_IMAGE) {
            $profile_image = SCF::imgcdn_url(!empty($result["images"][0]) ? $result["images"][0] : $current_template_assets_url . "/images/no-image.jpg");

            if (!empty($result["name"])) {
                $first_name = explode(" ", $result["name"])[0];
                $name_origin = BehindTheNameAPI\BehindTheNameAPI::fetch_data($first_name);
            }

            $date_range = [];
            array_walk_recursive($result["record_dates"], function ($date) use (&$date_range) {

                $timestamp = strtotime($date);
                if (!empty($date) && $timestamp) {
                    if (!is_null($date_range["earliest"]) || !is_null($timestamp)) {
                        if (empty($date_range["earliest"]) || $date_range["earliest"] > $timestamp) {
                            $date_range["earliest"] = $timestamp;
                        }
                    }

                    if (!is_null($date_range["latest"]) || !is_null($timestamp)) {
                        if (empty($date_range["latest"]) || $date_range["latest"] < $timestamp) {
                            $date_range["latest"] = $timestamp;
                        }
                    }
                }
            });
            $meta_data =& $result["record_dates"];
            // Section: Profile Summary
            ?>
            <form method="post"
                  action="<?php echo RELATIVE_URL . "search.html" ?>" <?php SCF::js_controller("search.click_form") ?>
                  target="_blank">
                <input type="hidden" name="search_type" value=""/>
                <input type="hidden" name="full_name" value=""/>
                <input type="hidden" name="phone" value=""/>
                <input type="hidden" name="email" value=""/>
                <input type="hidden" name="username" value=""/>
            </form>
            <form method="post"
                  action="<?php echo RELATIVE_URL . "dashboard.html" ?>" <?php SCF::js_controller("search.click_form_ras") ?>
                  target="_blank">
                <input type="hidden" name="search_type" value=""/>
                <input type="hidden" name="address" value=""/>
            </form>
            <div class="report-box report-main ss-report-main">
                <div class="row">
                    <!--
            <div class="col-sm-8 col-md-8 report-img">
                <div class="row">
                    <div class="col-md-12">
                        <i class="si-done-circle"></i> Records found for <h4><?php echo $query_text; ?> <?php echo (!empty($result["age"])) ? "<span>Age " . $result["age"] . "</span>" : ""; ?></h4>
                    </div>
                </div>
            <?php
            if ($phone_number_search) {
                ?>
                <div class="row phone_number_search">
                    <div class="col-xs-12 col-md-12">
                    <strong>Carrier: </strong><span> <?php echo($search["meta"]["carrier"] ?: "N/A"); ?></span><br>
                    </div>
                </div>
                <div class="row phone_number_search">
                    <div class=" col-xs-12 col-md-9">
                        <div class="row">
                            <div class="col-xs-6 col-md-6 col-lg-3 col-xl-3">
                                <strong>Active: <span class="active_box">YES</span></strong>
                            </div>
                            <div class="col-xs-6 col-md-6 col-lg-3  col-xl-3">
                                <strong>Pre-paid:<span class="inactive_box"><?php echo(!empty($search["meta"]["is_prepaid"]) ? $search["meta"]["is_prepaid"] : "N/A"); ?></span></strong></div>
                            <div class="col-xs-12 col-md-12 col-lg-6  col-xl-6 location">
                                <strong>Line Type:</strong><span> <?php echo($search["meta"]["line_type"] ?: "N/A"); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row phone_number_search">
                    <div class="col-xs-12 col-md-12">
                        <strong>Location : </strong><span> <?php $phone_owner = array_shift($search["meta"]["names"]);
                        echo($phone_owner); ?></span><br>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <?php if (!empty($result["gender"])) { ?>
                                <strong>Gender :</strong><p><?php echo ucfirst(strtolower($result["gender"])); ?></p><br>
                <?php } ?>
                        <?php if (!empty($result["locations"][0])) { ?>
                            <strong>Likely Current Address :</strong><p><?php echo ucfirst(strtolower($result["locations"][0])); ?></p><br>
                        <?php } ?>
                        <?php if (!empty($result["phones"][0])) { ?>
                            <strong>Likely Current Phone :</strong><p><?php echo ucfirst(strtolower($result["phones"][0])); ?></p><br>
                        <?php } ?>
                        <?php if (!empty($result["emails"][0])) { ?>
                            <strong>Last Seen Email :</strong><p><?php echo ucfirst(strtolower($result["emails"][0])); ?></p><br>
                        <?php } ?>
            <?php } ?>
   
                <?php
                if (empty($premium_content_user) && !empty($result['premium_data'])) {
                    ?>
                            <br><div class="btn btn-dark-green mobile-premium-btn" data-target="premium_data_found" <?php SCF::js_controller("modal.onclick_show"); ?>>UNLOCK PREMIUM</div>
                    <?php
                }
                ?>
        
        </div> -->
                    <!-- start -->
                    <div class="col-sm-8 report-img">
                        <div>
                            <div>
                                <img src="<?php echo $profile_image; ?>" alt="User"
                                     style="background-image: url('<?php echo $profile_image; ?>');" decoding="async"
                                     loading="lazy"/>
                            </div>
                            <?php
                            if (!empty($search_data["cached_data"]["results"][0]["isDead"])) {
                                echo "<br><br><div><p class='btn btn-bordered-red' style='color: red;'>DECEASED</p></div>";
                            }
                            ?>

                        </div>
                        <div class="report-content">
                            <span><i class="si-done-circle"></i> Matches found for</span>
                            <h4><?php echo $query_text; ?><?php echo (!empty($result["age"])) ? "<span>Age " . $result["age"] . "</span>" : ""; ?></h4>
                            <?php
                            if ($phone_number_search) {
                                ?>
                                <strong>Possible Owner:</strong>
                                <p><?php $phone_owner = (array_shift($search["meta"]["names"]) ?: (array_shift(explode(',', $get_ipq_phone["name"]))));
                                    echo('<a href="' . BASE_URL . '">' . $phone_owner . '</a>'); ?></p><br>
                                <strong>Associated Phone(s):</strong><p><?php echo(1 ? "N/A" : "N/A"); ?></p><br>
                                <strong>Associated Emails(s):</strong>
                                <p><?php echo(!empty($get_ipq_phone["associated_email_addresses"]["emails"]) ? (implode(', ', $get_ipq_phone["associated_email_addresses"]["emails"])) : "N/A"); ?></p>
                                <br>
                                <strong>Location:</strong>
                                <p><?php echo(isset($result["locations"][0]) ? ucfirst(strtolower($result["locations"][0])) : $get_ipq_phone["city"] . "/" . $get_ipq_phone["country"] . "/" . ($get_ipq_phone["zip_code"] != "N/A" ? $get_ipq_phone["zip_code"] . "/" : "") . $get_ipq_phone["region"]); ?></p>
                                <?php
                            } else {
                                ?>
                                <?php if (!empty($result["gender"])) { ?>
                                    <strong>Gender :</strong>
                                    <p><?php echo ucfirst(strtolower($result["gender"])); ?></p><br>
                                <?php } ?>
                                <?php if (!empty($result["locations"][0])) { ?>
                                    <div class="result-tbl-fix"><strong>Likely Current Address :</strong>
                                        <p><?php echo SCF::address_format($result["locations"][0]); ?></p></div>
                                <?php } ?>
                                <?php if (!empty($result["phones"][0])) { ?>
                                    <strong>Likely Current Phone :</strong>
                                    <p><?php echo SYSTEM::phone_number_format($result["phones"][0]); ?></p><br>
                                <?php } ?>
                                <?php if (!empty($result["emails"][0])) { ?>
                                    <strong>Last Seen Email :</strong>
                                    <p><?php echo ucfirst(strtolower($result["emails"][0])); ?></p><br>
                                <?php } ?>
                            <?php } ?>

                            <?php
                            if (empty($premium_content_user) && !empty($result['premium_data'])) {
                                ?>
                                <br>
                                <div class="btn btn-dark-green mobile-premium-btn"
                                     data-target="premium_data_found" <?php SCF::js_controller("modal.onclick_show"); ?>>
                                    UNLOCK PREMIUM
                                </div>
                                <?php
                            }
                            ?>

                        </div>
                    </div>
                    <!-- end -->
                    <div class="col-sm-4 text-right actions">
                        <?php
                        if (empty($tracking_off)) {
                            ?>

                            <div class="divToggle">
                                <ol class="switches">
                                    <li>
                                        <input type="checkbox" name="allusers"
                                               id="toggle" <?php echo !empty($tracking_status) ? "checked" : "" ?>         <?php SCF::js_controller("tracking.toggle") ?>
                                               data-type="<?php echo !empty($tpd_report) ? $tpd_report["prefix"] : "" ?>"
                                               data-id="<?php echo !empty($result["id"]) ? $result["id"] : $search["cache_id"]; ?>">
                                        <label for="toggle">
                                            Tracking
                                            <span></span>
                                            <span <?php SCF::js_controller("tracking.toggle.text") ?>><?php echo !empty($tracking_status) ? "ON" : "OFF" ?> </span>
                                        </label>
                                    </li>
                                </ol>
                            </div>
                            <?php
                        }
                        if (empty($premium_content_user) && !empty($result['premium_data'])) {
                            ?>
                            <br>
                            <div class="btn btn-dark-green premium-btn"
                                 data-target="premium_data_found" <?php SCF::js_controller("modal.onclick_show"); ?>>
                                UNLOCK PREMIUM
                            </div>
                            <?php
                        }

                        if (empty($disable_feedback) && !CustomerFeedback::check_feedback(!empty($tpd_report) ? $tpd_report["id"] : $search_data["cached_data"]["cache_id"], $user_id, !empty($tpd_report) ? $tpd_report : false)) {
                            ?>
                            <br>
                            <div class="btn btn-gray rate_btn"
                                 data-target="rating_report" <?php SCF::js_controller("modal.onclick_show"); ?>><span
                                        class="si-star"></span> Rate This Report
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <?php
                //Removed temporary
                $phone_owner = array_shift($search["meta"]["names"]);
                if (0 && $phone_number_search && $phone_owner) {
                    ?>              <br/>
                    <div class="row phone_number_search_posibe_owner">
                        <div class="col-xs-12 col-md-8">
                            <strong>Possible Owner:</strong><span><?php echo($phone_owner); ?></span>
                        </div>

                        <div class="col-xs-12 col-md-4 text-right actions">
                            <a href="<?php echo BASE_URL ?>" class="btn btn-dark-green"><i class="si-user"></i>Run Name
                                Search</a>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <?php

                if ($phone_number_search) {
                    ?>              <br/>
                    <div class="row phone_number_search_posibe_owner">
                        <div class="col-xs-6 col-md-6 col-lg-3 col-xl-12">
                            <strong>Carrier: <p
                                        style="font-weight: normal;"><?php echo($search["meta"]["carrier"] ?: ($get_ipq_phone["carrier"] ?: "N/A")); ?></p>
                            </strong>
                        </div>
                        <div class="col-xs-6 col-md-6 col-lg-6 col-xl-6">
                            <strong>Active: <span
                                        class="active_box"><?php echo($get_ipq_phone["active"] ? "YES" : "NO") ?></span></strong>
                            <strong>Pre-paid:<span
                                        class="inactive_box"><?php echo(!empty($search["meta"]["is_prepaid"]) ? $search["meta"]["is_prepaid"] : ($get_ipq_phone["prepaid"] ? "YES" : "NO")); ?></span></strong>
                            <strong>Line
                                Type:<span> <?php echo($search["meta"]["line_type"] ?: ($get_ipq_phone["line_type"] ?: "N/A")); ?></span></strong>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <?php if (!empty($result["bankruptcy"]) || !empty($result["judgment"]) || !empty($result["lien"]) || !empty($result["professional"]) || !empty($result["criminal"])) { ?>
                    <div class="idi_records_summery">
                        <div class="row">
                            <div class="col-md-6">
                                <p data-target="<?php echo (empty($premium_content_user) && !empty($result["bankruptcy"])) ? 'premium_data_found' : 'bankruptcy'; ?>" <?php echo (empty($premium_content_user) && !empty($result["bankruptcy"])) ? SCF::js_controller("modal.onclick_show") : SCF::js_controller("results.summary_link"); ?>>
                                    <span class="si-cooperation"></span> <strong>Bankruptcies
                                        :</strong> <?php echo (!empty($result["bankruptcy"])) ? "<label>Found</label>" : "Searched. None Found"; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p data-target="<?php echo (empty($premium_content_user) && !empty($result["judgment"])) ? 'premium_data_found' : 'judgment'; ?>" <?php echo (empty($premium_content_user) && !empty($result["judgment"])) ? SCF::js_controller("modal.onclick_show") : SCF::js_controller("results.summary_link"); ?>>
                                    <span class="si-criminal-rec"></span> <strong>Judgments
                                        :</strong> <?php echo (!empty($result["judgment"])) ? "<label>Found</label>" : "Searched. None Found"; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p data-target="<?php echo (empty($premium_content_user) && !empty($result["lien"])) ? 'premium_data_found' : 'lien'; ?>" <?php echo (empty($premium_content_user) && !empty($result["lien"])) ? SCF::js_controller("modal.onclick_show") : SCF::js_controller("results.summary_link"); ?>>
                                    <span class="si-property"></span> <strong>Liens
                                        :</strong> <?php echo (!empty($result["lien"])) ? "<label>Found</label>" : "Searched. None Found"; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p data-target="<?php echo (empty($premium_content_user) && !empty($result["professional"])) ? 'premium_data_found' : 'professional'; ?>" <?php echo (empty($premium_content_user) && !empty($result["professional"])) ? SCF::js_controller("modal.onclick_show") : SCF::js_controller("results.summary_link"); ?>>
                                    <span class="si-account"></span> <strong>Professional Licenses
                                        :</strong> <?php echo (!empty($result["professional"])) ? "<label>Found</label>" : "Searched. None Found"; ?>
                                </p>
                            </div>
                            <?php if ($idi_show_criminal) { ?>
                                <div class="col-md-6">
                                    <p data-target="<?php echo (empty($premium_content_user) && !empty($result["criminal"])) ? 'premium_data_found' : 'criminal'; ?>"<?php echo (empty($premium_content_user) && !empty($result["criminal"])) ? SCF::js_controller("modal.onclick_show") : SCF::js_controller("results.summary_link"); ?>>
                                        <span class="si-criminal-rec"></span> <strong>Possible Criminal
                                            :</strong> <?php echo (!empty($result["criminal"])) ? "<label>Found</label>" : "Searched. None Found"; ?>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php

            // Section: Known Aliases
            $section_heading = $phone_number_search ? "People Associated with {$query_text}" : "Known Aliases";
            $data_ref = $phone_number_search ? $search["meta"]["names"] : $result["names"];

            $data_ref = !empty($data_ref) ? $data_ref : explode(',', $get_ipq_phone["name"]);
            if ($data_ref[0] != 'N/A' && !empty($data_ref[0])) {
                ?>
                <div class="report-box known-aliases">
                    <h3><?php echo $section_heading; ?> <span class="aliases-count">(<?php echo count($data_ref); ?>
                            )</span></h3>

                    <?php
                    if (!empty($data_ref)) {
                        ?>
                        <ul>
                            <?php
                            foreach ($data_ref as $_known_name) {
                                ?>
                                <li class="al"><?php echo $_known_name; ?></li>
                                <?php
                            }

                            if (count($data_ref) > 3) {
                                ?>
                                <li class="load-more-aliases more-aliases-mobile" <?php SCF::js_controller("accordion.show_more") ?>>
                                    +<?php echo(count($data_ref) - 3); ?> More <i class="si-down"></i></li>
                                <?php
                            }

                            if (count($data_ref) > 5) {
                                ?>
                                <li class="load-more-aliases" <?php SCF::js_controller("accordion.show_more") ?>>
                                    +<?php echo(count($data_ref) - 5); ?> More <i class="si-down"></i></li>
                                <?php
                            }

                            unset($_known_name);

                            ?>
                        </ul>
                        <?php
                    } else {
                        ?>
                        <p>Data from multiple sources shows that there are no publicly known aliases that have been
                            published online.</p>
                        <?php
                    }
                    unset($section_heading, $data_ref);
                    ?>
                </div>
                <?php
            }
            // Section: VOIP Alert
            if ((!empty($search["meta"]) && stripos($search["meta"]["line_type"], "VOIP") !== false) || (!empty($get_ipq_phone["VOIP"]) && $get_ipq_phone["VOIP"] !== false)) {
                ?>

                <div class="report-box voip-number-alert">


                    <h3 class=" no-velocity"><span class="si-warning-fill"></span> VOIP Number Found ! </h3>

                    <div class="accordion-btn" <?php SCF::js_controller("accordion.item") ?> ><span class="expand">What is this</span><span
                                class="collapse">Collapse This</span> <i class="si-down"></i></div>

                    <div class="accordion-content">
                        <p>A "VOIP" number is a <label>Virtual Phone Number</label> used by someone to make calls to
                            using a free service such as Skype or Google Voice numbers. Anyone can sign up for one
                            choosing any area code they want. These numbers are free and can be used by anyone (good or
                            bad). Virtual phone numbers are often used in <label>Money Scam or Romance Scams</label>.
                            They can also be used to enable long-distance service without incurring long-distance
                            charges and robocalling. But like we said before, VOIP numbers are usually used in scams.
                        </p>
                    </div>
                </div>
                <?php
            }
            // temp removing CTA due to change to IDI
            // enabled the section
            // CSI-7453 - Remove In-Report Criminal Records CTA
            if (SEARCH_TYPE_NAME == $search_data["type"]) {
                if ($_SESSION["cr_id"] != 0) {
                    $details = CriminalRecords::get_user_search_details($user_id, $_SESSION["cr_id"]);
                    ?>
                    <!--div class="report-box criminal-records-found">
                <h3><i class="si-warning"></i> <span class="name"><?php //echo $details["records_count"] ?></span> Possible Criminal Records found for <span class="name"><?php //echo ucwords($details["full_name"]); ?></span> in <span class="name"><?php //echo $details["state"] ?></h3>
                <button type="button" class="btn btn-bordered" data-url="<?php //echo RELATIVE_URL . "criminal_report/{$details["full_name"]}-{$_SESSION["cr_id"]}" ?>" <?php SCF::js_controller("modal.criminal_records_popup.go");
                    $_SESSION["cr_search"] = $query_text; ?>>View Report</button>
            </div-->
                    <?php
                } else {
                    ?>
                    <!--div class="report-box criminal-records">
                <h3><i class="si-criminal-rec"></i> Alert! <span class="name"> <?php //echo $query_text; ?></span>  has possible criminal records.</h3>
                <button type="button" class="btn btn-bordered" data-url="<?php //echo $search_type_links[SEARCH_TYPE_CR] ?>" <?php SCF::js_controller("modal.criminal_records_popup.go");
                    $_SESSION["cr_search"] = $query_text; ?>>Open Report</button>
            </div-->
                    <?php
                }
            } elseif (SEARCH_TYPE_PHONE == $search_data["type"]) { ?>
                <!--div class="report-box criminal-records">
            <h3><i class="si-criminal-rec"></i> Alert! <span class="name"> <?php //echo ($phone_owner); ; ?></span>  has possible criminal records.</h3>
            <button type="button" class="btn btn-bordered" data-url="<?php //echo $search_type_links[SEARCH_TYPE_CR] ?>" <?php SCF::js_controller("modal.criminal_records_popup.go");
                $_SESSION["cr_search"] = $phone_owner; ?>>Open Report</button>
        </div-->


            <?php }
            // Section: Search Velocity
            if (!$pwnd_and_IPQ_only) {
                ?>
                <div class="report-box search-velocity ss-velocity">

                    <?php
                    $class = '';
                    $velocitybtn = '';
                    $velocityClass = '';

                    if (!empty($velocity) && array_sum($velocity)) {
                        if ($velocity["total"] >= 3) {
                            $class = 'high-velocity';
                            $velocitybtn = '<div class="high-velocity-btn velocity-btn">High Velocity <i class="si-trending"></i></div>';
                        } elseif ($velocity["total"] == 2) {
                            $class = 'medium-velocity';
                            $velocitybtn = '<div class="medium-velocity-btn velocity-btn">Medium Velocity <i class="si-trending"></i></div>';
                        } else {
                            $class = 'low-velocity';
                            $velocitybtn = '<div class="low-velocity-btn velocity-btn">Low Velocity <i class="si-trending"></i></div>';
                        }

                        $velocityClass = 'has-velocity';
                    } else {
                        $velocityClass = 'no-velocity';
                    }
                    ?>

                    <?php
                    if (SEARCH_TYPE_NAME == $search_data["type"]) {
                        $searchtype = 'name';
                    } elseif (SEARCH_TYPE_PHONE == $search_data["type"]) {
                        $searchtype = 'number';
                    } elseif (SEARCH_TYPE_EMAIL == $search_data["type"]) {
                        $searchtype = 'email';
                    } elseif (SEARCH_TYPE_USERNAME == $search_data["type"]) {
                        $searchtype = 'username';
                    } elseif (SEARCH_TYPE_RAS == $search_data["type"]) {
                        $searchtype = 'address';
                    }
                    ?>

                    <h3 class="<?php echo $class; ?> <?php echo $velocityClass; ?>"><span
                                class="total-times"><?php if (!empty($velocity) && array_sum($velocity)) {
                                    ?><?php echo $velocity["total"]; ?><?php
                                                    } else {
                                                        echo "0";
                                                    } ?></span>times this <?php echo $searchtype; ?> has been
                        searched <?php echo $velocitybtn; ?></h3>

                    <div class="accordion-btn" <?php SCF::js_controller("accordion.item") ?>><span class="expand">What is this</span><span
                                class="collapse">Collapse This</span> <i class="si-down"></i></div>

                    <div class="accordion-content">
                        <p>This is the number of times this <?php echo $searchtype; ?> has been searched. The higher the
                            numbers, the greater the number of <?php echo $searchtype; ?> looking for information on
                            this person. If not a celebrity, a high Search Velocity count could indicate a fake persona
                            used by a scammer in multiple scams.</p>
                        <?php
                        if (!empty($velocity) && array_sum($velocity)) {
                            ?>
                            <ul>
                                <li>Last 24 Hours <span><?php echo $velocity["day"]; ?></span></li>
                                <li>This Month <span><?php echo $velocity["month"]; ?></span></li>
                                <li>This Year <span class="this-year"><?php echo $velocity["year"]; ?></span></li>
                            </ul>
                            <?php
                        } else {
                            ?>
                            <p>There is no record of a search for <span><?php echo $query_text ?></span> as of
                                <span><?php echo date_create("now", timezone_open("America/Los_Angeles"))->format("jS M Y g:ia"); ?></span>.<br/>This
                                is updated each time you access this Report. Come back often to get the latest Search
                                count.</p>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }

            if (!empty($pwned_data)) {
                ?>
                <div <?php SCF::js_controller("results.section.phonebreach") ?> class="report-box-panel red">
                    <div class="panel-heading"><strong>Reported in <span><?php echo count($pwned_data->sites) ?></span>
                            Data Breach Incidents</strong>
                        <div class="right-align">First : <label><?php echo $pwned_data->firstDate ?></label>&nbsp;&nbsp;&nbsp;&nbsp;Last
                            : <label><?php echo $pwned_data->lastDate ?></label></div>
                    </div>
                    <div class="panel-body">
                        <?php echo $phone ? $phone : $email; ?> was involved in
                        <b><?php echo count($pwned_data->sites) ?> data breach incidents,</b> the earliest of which was
                        <b><?php echo $pwned_data->firstDate ?></b> and the latest of which was
                        <b><?php echo $pwned_data->lastDate ?>.</b>
                        <div class="view-btn right-align">View List <i class="si-down"></i></div>

                        <div class="data-breach-box" style="display: none">
                            <div class="data-report">
                                <table class="scf-table" style="display: table;">
                                    <thead>
                                    <tr>
                                        <th>Website</th>
                                        <th>Data Breach Info</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($pwned_data->sites as $key => $pwned_phone_site) {
                                        ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo $pwned_phone_site->LogoPath ?>"
                                                     alt="<?php echo $pwned_phone_site->Title ?>"
                                                     style="background-image: url(&quot;<?php echo $pwned_phone_site->LogoPath ?>&quot;);"
                                                     decoding="async" loading="lazy">
                                                <a><?php echo $pwned_phone_site->Title ?></a>
                                            </td>
                                            <td>
                                                <p><?php echo $pwned_phone_site->Description ?></p>
                                                <p><strong>Compromised data:</strong>
                                                    <span><?php echo implode(", ", $pwned_phone_site->Compromised_Data) ?></span>
                                                </p>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
            <?php
            // Section: Name Origin and Popularity
            if (!empty($name_origin)) {
                ?>
                <div class="report-box orgin-pop">
                    <h3>Name Origin and Popularity</h3>
                    <div class="row">
                        <div class="col-md-3 tab-div-left">
                            <span class="name-op"><?php echo $name_origin["name"] ?></span>
                        </div>
                        <div class="col-md-9 tab-div-right">
                            <div class="op-list"><span>Gender</span>
                                <p><?php echo ("m" == $name_origin["gender"]) ? "Male" : "Female"; ?></p></div>
                            <?php
                            if (!empty($name_origin["origins"])) {
                                ?>
                                <div class="op-list"><span>Usage</span>
                                    <p><?php echo implode(", ", array_column(unserialize($name_origin["origins"]), "usage_full")); ?></p>
                                </div>
                                <?php
                            }

                            if (!empty($name_origin["aliases"])) {
                                ?>
                                <div class="op-list"><span>Possible Aliases</span>
                                    <p><?php echo implode(", ", unserialize($name_origin["aliases"])); ?></p></div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            if (!empty($phone_recording_data)) {
                ?>

                <div class="scf-robocall">
                    <h4>Robocall Warning</h4>
                    <p><?= $query_text; ?> is a Robocall. Do not answer, you might be receiving a scam call.</p>
                    <audio controls controlsList="nodownload">
                        <source src="<?= $phone_recording_data["recording_src"] ?>" type="audio/mp3">
                        Your browser does not support the audio element.
                    </audio>
                    <h5>Transcript :</h5>
                    <p><?= $phone_recording_data["transcript"] ?: "<span>Not available </span>" ?></p>
                    <h5>Call Activity : <span><?= $phone_recording_data["activity"] ?></span></h5>
                    <p>Last detected <?= $phone_recording_data["last_detected"] ?></p>
                </div>
            <?php } ?>

            <?php

            if ($get_ipq_email) {
                ?>
                <div class="phone-fraud-score ipq-email" <?php SCF::js_controller("results.section.sort_ipq") ?>>

                    <div class="row ">
                        <div class="col-md-8 col-xs-12"><h4><i class="si-email"></i>Email Address Analysis: </h4></div>
                        <div class="col-md-4 col-xs-12"><label class="title-box">Email : <span
                                        style="color: #0F63EC;"><?php echo $email; ?></span></label></div>
                    </div>
                    <div class="item primary">
                        <h5><span class="si-tip"></span> Fraud Score: <span
                                    class="lable_right"><?php echo $get_ipq_email["fraud_score"]; ?></span></h5>
                        <p>Ranging from 0 to 100, the higher the Fraud Score, the higher the instances the phone number
                            has been flagged for suspicious or fraudulent activity. </p>
                    </div>
                    <div class="item primary">
                        <h5><span class="si-done-circle"></span> Possible First Name of Owner: <span
                                    class="item-value"><?php echo $get_ipq_email["first_name"]; ?></span></h5>
                    </div>

                    <div class="item <?php echo ($get_ipq_email["valid"]) ? '' : 'error'; ?>">
                        <div class="row ">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_email["valid"]) ? 'si-done-circle' : 'si-warning-fill'; ?>"></span>
                                    Valid :</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_email["valid"]) ? "yes" : "no"; ?>"><?php echo ($get_ipq_email["valid"]) ? "Yes" : "No"; ?></span>
                            </div>
                        </div>
                        <p>Does this email address appear valid?</p>
                    </div>
                    <div class="item <?php echo ($get_ipq_email["disposable"]) ? 'error' : ''; ?>">
                        <div class="row ">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_email["disposable"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    Disposable :</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_email["disposable"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_email["disposable"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p>Is this email suspected of belonging to a temporary or disposable mail service? Usually
                            associated with fraudsters and scammers.</p>
                    </div>


                    <div class="item hidden <?php echo ($get_ipq_email["suspect"]) ? 'error' : ''; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_email["suspect"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    Suspect :</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_email["suspect"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_email["suspect"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p>This value indicates if the mail server is currently replying with a temporary error and
                            unable to verify the email address.</p>
                    </div>
                    <div class="item hidden <?php echo ($get_ipq_email["leaked"]) ? 'error' : ''; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_email["leaked"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    Leaked :</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_email["leaked"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_email["leaked"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p>Was this email address associated with a recent database leak from a third party? Leaked
                            accounts pose a risk as they may have become compromised during a database breach.</p>
                    </div>


                    <div class="item hidden primary">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_email["user_activity"] == 'low') ? '' : ''; ?>"></span>
                                    User Activity :</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn high"><?php echo $get_ipq_email["user_activity"]; ?></span></div>
                        </div>
                        <p>Frequency at which this email address makes legitimate purchases, account registrations, and
                            engages in legitimate user behavior online.</p>
                    </div>
                    <div class="view_more" <?php SCF::js_controller("results.section.view_ipq") ?>>
                        <h6 class="hdown">View More Data <label>+3</label> <span class="si-down"></span></h6>
                        <h6 class="hup" style="display: none;">View Less Data <span class="si-up"></span></h6>
                    </div>
                </div>
                <?php
            }

            ?>
            <?php
            if ($get_ipq_phone) {
                ?>
                <div class="phone-fraud-score ipq-phone" <?php SCF::js_controller("results.section.sort_ipq") ?>>
                    <div class="row ">
                        <div class="col-md-8 col-xs-12"><h4><i class="si-phone"></i>Phone Number Analysis: </h4></div>
                        <div class="col-md-4 col-xs-12"><label class="title-box">Phone : <span
                                        style="color: #0F63EC;"><?php echo $phone; ?></span></label></div>
                    </div>

                    <div class="item primary ipq_order">
                        <h5><span class="si-tip"></span> Fraud Score: <span
                                    class="lable_right"><?php echo $get_ipq_phone["fraud_score"]; ?></span></h5>
                        <p>Ranging from 0 to 100, the higher the Fraud Score, the higher the instances the phone number
                            has been flagged for suspicious or fraudulent activity.</p>
                    </div>
                    <div class="item <?php echo ($get_ipq_phone["valid"]) ? '' : 'error'; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_phone["valid"]) ? 'si-done-circle' : 'si-warning-fill'; ?>"></span>
                                    Valid:</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_phone["valid"]) ? "yes" : "no"; ?>"><?php echo ($get_ipq_phone["valid"]) ? "Yes" : "No"; ?></span>
                            </div>
                        </div>
                        <p> Is the phone number properly formatted and considered valid based on assigned phone numbers
                            available to carriers in that country?</p>
                    </div>
                    <div class="item <?php echo ($get_ipq_phone["active"]) ? '' : 'error'; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_phone["active"]) ? 'si-done-circle' : 'si-warning-fill'; ?>"></span>
                                    Active:</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_phone["active"]) ? "yes" : "no"; ?>"><?php echo ($get_ipq_phone["active"]) ? "Yes" : "No"; ?></span>
                            </div>
                        </div>
                        <p> Is this phone number a live usable phone number that is currently active?</p>
                    </div>
                    <div class="item hidden <?php echo ($get_ipq_phone["VOIP"]) ? 'error' : ''; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_phone["VOIP"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    VOIP:</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_phone["VOIP"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_phone["VOIP"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p>Is this phone number a Voice Over Internet Protocol (VOIP) or digital phone number?</p>
                    </div>
                    <div class="item hidden <?php echo ($get_ipq_phone["prepaid"]) ? 'error' : ''; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_phone["prepaid"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    Prepaid:</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_phone["prepaid"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_phone["prepaid"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p> Is this phone number associated with a prepaid service plan?</p>
                    </div>
                    <div class="item hidden <?php echo ($get_ipq_phone["risky"]) ? 'error' : ''; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_phone["risky"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    Risky:</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_phone["risky"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_phone["risky"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p>Is this phone number associated with fraudulent activity, scams, robo calls, fake accounts,
                            or other unfriendly behavior?</p>
                    </div>
                    <div class="item hidden <?php echo ($get_ipq_phone["leaked"]) ? 'error' : ''; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_phone["leaked"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    Leaked:</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_phone["leaked"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_phone["leaked"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p> Has this phone number recently been exposed in an online database breach or act of
                            compromise.</p>
                    </div>
                    <div class="item hidden <?php echo ($get_ipq_phone["spammer"]) ? 'error' : ''; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_phone["spammer"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    Spammer:</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_phone["spammer"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_phone["spammer"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p> Indicates if the phone number has recently been reported for spam or harassing
                            calls/texts.</p>
                    </div>
                    <div class="item hidden <?php echo ($get_ipq_phone["do_not_call"]) ? 'error' : ''; ?>">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span
                                            class="<?php echo ($get_ipq_phone["do_not_call"]) ? 'si-warning-fill' : 'si-done-circle'; ?>"></span>
                                    Do Not Call:</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn <?php echo ($get_ipq_phone["do_not_call"]) ? "no" : "false"; ?>"><?php echo ($get_ipq_phone["do_not_call"]) ? "True" : "False"; ?></span>
                            </div>
                        </div>
                        <p> Indicates if the phone number is listed on any Do Not Call (DNC) lists. Only supported in US
                            and CA. This data may not be 100% up to date with the latest DNC blacklists.</p>
                    </div>

                    <div class="item hidden primary">
                        <div class="row">
                            <div class="col-md-9 col-xs-8"><h5><span class=""></span> User Activity :</h5></div>
                            <div class="col-md-3 col-xs-4"><span
                                        class="btn high"><?php echo $get_ipq_phone["user_activity"]; ?></span></div>
                        </div>
                        <p>Frequency at which this phone number makes legitimate purchases, account registrations, and
                            engages in legitimate user behavior online.</p>
                    </div>

                    <div class="item hidden primary primary-text">
                        <h5>Carrier : <span class="item-value"><?php echo $get_ipq_phone["carrier"]; ?></span></h5>
                        <p> The carrier (service provider) this phone number has been assigned to or "N/A" if
                            unknown.</p>
                    </div>
                    <div class="item hidden primary primary-text">
                        <h5>Line Type : <span class="item-value"><?php echo $get_ipq_phone["line_type"]; ?></span></h5>
                        <p>The type of line this phone number is associated with (Toll Free, Mobile, Landline,
                            Satellite, VOIP, Premium Rate, Pager, etc...) or "N/A" if unknown.</p>
                    </div>
                    <div class="item hidden primary primary-text">
                        <h5>Country : <span class="item-value"><?php echo $get_ipq_phone["country"]; ?></span></h5>
                        <p>The two character country code for this phone number.</p>
                    </div>
                    <div class="item hidden primary primary-text">
                        <h5>City : <span class="item-value"><?php echo $get_ipq_phone["city"]; ?></span></h5>
                        <p>City of the phone number if available or "N/A" if unknown.</p>
                    </div>
                    <div class="item hidden primary primary-text">
                        <h5>Zip Code : <span class="item-value"><?php echo $get_ipq_phone["zip_code"]; ?></span></h5>
                        <p> Zip or Postal code of the phone number if available or "N/A" if unknown.</p>
                    </div>
                    <div class="item hidden primary primary-text">
                        <h5>Region : <span class="item-value"><?php echo $get_ipq_phone["region"]; ?></span></h5>
                        <p> Region (state) of the phone number if available or "N/A" if unknown.</p>
                    </div>
                    <div class="item hidden primary primary-text">
                        <h5>Timezone: <span class="item-value"><?php echo $get_ipq_phone["timezone"]; ?></span></h5>
                        <p>Timezone of the phone number if available or "N/A" if unknown.</p>
                    </div>
                    <div class="item hidden primary primary-text">
                        <h5>SMS Email: <span class="item-value"><?php echo $get_ipq_phone["sms_email"]; ?></span></h5>
                        <p>Additional details.</p>
                    </div>
                    <div class="view_more" <?php SCF::js_controller("results.section.view_ipq") ?>>
                        <h6 class="hdown">View More Data <label>+14</label> <span class="si-down"></span></h6>
                        <h6 class="hup" style="display: none;">View Less Data <span class="si-up"></span></h6>
                    </div>
                </div>
                <?php
            }

            ?>

            <?php
            if (!$pwnd_and_IPQ_only) {
                // Section: Summary
                ?>
                <div class="report-box rb-summary<?php echo !empty($premium_only_data) ? " premium_data_included" : ""; ?>">
                    <h3><span class="si-tip"></span> Summary</h3>
                    <div class="row summary-lists">
                        <?php

                        // Section: Summary
                        $section_summary = [
                            ["icon" => "image", "key" => "images", "caption" => "Photos"],
                            ["icon" => "user", "key" => "relationships", "caption" => "Relationships"],
                            ["icon" => "phone", "key" => "phones", "caption" => "Phone No"],
                            ["icon" => "username", "key" => "usernames", "caption" => "Usernames"],
                            ["icon" => "location", "key" => "locations", "caption" => "Addresses"],
                            ["icon" => "email", "key" => "emails", "caption" => "Emails"],
                            ["icon" => "website", "key" => "urls", "caption" => "Websites"],
                            ["icon" => "jobs", "key" => "jobs_in_detail", "caption" => "Jobs"],
                            ["icon" => "education", "key" => "education_in_detail", "caption" => "Education"]
                        ];

                        $idi_sections = ["bankruptcy", "lien", "judgment", "professional", "criminal"];

                        if (!empty($premium_content_user)) {
                            if (!empty($result["bankruptcy"])) {
                                array_push($section_summary, ["icon" => "cooperation", "key" => "bankruptcy", "caption" => "Bankruptcies"]);
                            }
                            if (!empty($result["lien"])) {
                                array_push($section_summary, ["icon" => "property", "key" => "lien", "caption" => "Liens"]);
                            }
                            if (!empty($result["judgment"])) {
                                array_push($section_summary, ["icon" => "criminal-rec", "key" => "judgment", "caption" => "Judgments"]);
                            }
                            if (!empty($result["professional"])) {
                                array_push($section_summary, ["icon" => "account", "key" => "professional", "caption" => "Professional"]);
                            }
                            if (!empty($result["criminal"])) {
                                array_push($section_summary, ["icon" => "criminal-rec", "key" => "criminal", "caption" => "Criminal"]);
                            }
                        }


                        foreach ($section_summary as $index => $_summary) {
                            $summary_count = ("relationships" == $_summary["key"] ? count($result["associated_people"]) : 0) + (!empty($result[$_summary["key"]]) ? count($result[$_summary["key"]]) : 0);
                            $section_summary[$index]["count"] = $summary_count;
                        }

                        array_multisort(array_column($section_summary, 'count'), SORT_DESC, $section_summary);

                        foreach ($section_summary as $_summary) {
                            ?>
                            <div class="col-md-4">
                                <div class="jump-link <?php echo (in_array($_summary['key'], $idi_sections)) ? 'idi_summary' : ''; ?>"
                                     data-target="<?php if ($_summary["key"] == 'jobs_in_detail') {
                                            echo 'jobs';
                                                  } elseif ($_summary["key"] == 'education_in_detail') {
                                                      echo 'educations';
                                                  } else {
                                                      echo $_summary["key"];
                                                  } ?>" <?php echo SCF::js_controller("results.summary_link"); ?>><span
                                            class="si-<?php echo $_summary["icon"] ?>"></span><?php echo $_summary["caption"] ?>
                                    <label class='<?php echo ($_summary["count"] == 0) ? "btn_zero" : ""; ?>'><?php echo $_summary["count"]; ?></label>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="sm-date">
                        <?php
                        if (!is_null($date_range["earliest"]) || !is_null($timestamp)) { ?>
                            <p><span class="si-calendar"></span> Earliest Validated
                                <span><?php echo date("Y", $date_range["earliest"]) ?><span></p>
                        <?php } ?>
                        <?php if (!is_null($date_range["latest"]) || !is_null($timestamp)) { ?>
                            <p><span class="si-calendar"></span> Last Confirmed
                                <span><?php echo date("Y", $date_range["latest"]) ?><span></p>
                        <?php } ?>
                    </div>
                </div>

                <!-- Oline accounts section -->
                <div class="report-box online-accounts">
                    <h2><span class="si-image"></span>Online Accounts<label>18</label></h2>
                    <p class="box-title">Based on data from third parties, it's highly likely this email has the
                        following accounts. We're just not able to provide exact profile link. The account link (URL)
                        might have changed. Go directly to the following sites to search more.</p>
                    <p class="box-title searched-data"><span class="email-label">Email Address :</span>
                        david.smith@gmail.com</p>
                    <div class="row">
                        <div class="category">
                            <div class="cat-tag">Social Media</div>
                            <div class="items-wrapper">
                                <div class="item"
                                     data-target="holehe-popup" <?php SCF::js_controller("holehe.onclick_show"); ?>>
                                    <div class="item-inner"><span class="icon-image"><img
                                                    src="<?php echo $current_template_assets_url; ?>/images/social-icons-cercle/facebook.svg"
                                                    alt="facebook" decoding="async" loading="lazy"/></span> facebook.com
                                    </div>
                                    <span class="si-share"></span></div>
                                <div class="item"
                                     data-target="holehe-popup" <?php SCF::js_controller("holehe.onclick_show"); ?>>
                                    <div class="item-inner"><span class="icon-image"><img
                                                    src="<?php echo $current_template_assets_url; ?>/images/social-icons-cercle/instagram.svg"
                                                    alt="instagram" decoding="async" loading="lazy"/></span>
                                        instagram.com
                                    </div>
                                    <span class="si-share"></span></div>
                                <div class="item"
                                     data-target="holehe-popup" <?php SCF::js_controller("holehe.onclick_show"); ?>>
                                    <div class="item-inner"><span class="icon-image"><img
                                                    src="<?php echo $current_template_assets_url; ?>/images/social-icons-cercle/twitter.svg"
                                                    alt="twitter" decoding="async" loading="lazy"/></span> twitter.com
                                    </div>
                                    <span class="si-share"></span></div>
                                <div class="item"
                                     data-target="holehe-popup" <?php SCF::js_controller("holehe.onclick_show"); ?>>
                                    <div class="item-inner"><span class="icon-image"><img
                                                    src="<?php echo $current_template_assets_url; ?>/images/social-icons-cercle/linkedin.svg"
                                                    alt="linkedin" decoding="async" loading="lazy"/></span> linkedin.com
                                    </div>
                                    <span class="si-share"></span></div>
                                <div class="item"
                                     data-target="holehe-popup" <?php SCF::js_controller("holehe.onclick_show"); ?>>
                                    <div class="item-inner"><span class="icon-image"><img
                                                    src="<?php echo $current_template_assets_url; ?>/images/social-icons-cercle/tinder.svg"
                                                    alt="tinder" decoding="async" loading="lazy"/></span> tinder.com
                                    </div>
                                    <span class="si-share"></span></div>
                                <div class="item"
                                     data-target="holehe-popup" <?php SCF::js_controller("holehe.onclick_show"); ?>>
                                    <div class="item-inner"><span class="icon-image"><img
                                                    src="<?php echo $current_template_assets_url; ?>/images/social-icons-cercle/pinterest.svg"
                                                    alt="pinterest" decoding="async" loading="lazy"/></span>
                                        pinterest.com
                                    </div>
                                    <span class="si-share"></span></div>
                                <div class="item"
                                     data-target="holehe-popup" <?php SCF::js_controller("holehe.onclick_show"); ?>>
                                    <div class="item-inner"><span class="icon-image"><img
                                                    src="<?php echo $current_template_assets_url; ?>/images/social-icons-cercle/onlyfans.svg"
                                                    alt="onlyfans" decoding="async" loading="lazy"/></span> onlyfans.com
                                    </div>
                                    <span class="si-share"></span></div>
                                <div class="item"
                                     data-target="holehe-popup" <?php SCF::js_controller("holehe.onclick_show"); ?>>
                                    <div class="item-inner"><span class="icon-image"><img
                                                    src="<?php echo $current_template_assets_url; ?>/images/social-icons-cercle/pornhub.svg"
                                                    alt="pornhub" decoding="async" loading="lazy"/></span> pornhub.com
                                    </div>
                                    <span class="si-share"></span></div>
                            </div>
                        </div>
                        <div class="view-more">View More Associate Websites <label>+10</label> <span
                                    class="si-down"></span></div>
                    </div>
                </div>

                <?php
                // Section: Photos
                if (!empty($result["images"])) {
                    $count = count($result["images"]);
                    ?>
                    <div <?php SCF::js_controller("results.section.images") ?>
                            class="report-box img<?php echo(!empty($premium_only_data["images"]) && count($premium_only_data["images"]) ? " premium_data_included" : ""); ?>">
                        <h2><span class="si-image"></span>Photos<label><?php echo $count ?></label></h2>
                        <p class="box-title">These photos may belong to the person you're researching.</p>
                        <div class="row">
                            <?php
                            $duplicates = [];
                            foreach ($result["images"] as $index => $data) {
                                $url_type = SCF::get_url_type($data);
                                $web_link = !empty($link_types[$url_type]) ? $link_types[$url_type][0] : $data;
                                $uri = parse_url($web_link);

                                if (in_array($web_link, $duplicates)) {
                                    continue;
                                } else {
                                    $duplicates[] = $web_link;
                                }

                                if ("facebook" == $url_type) {
                                    $data = IMG_CDN_URL . "no-image.png";
                                }

                                ?>
                                <div class="col-sm-6 col-md-3 data-point">
                                    <div class="box-col<?php echo isset($premium_only_data["images"][$index]) ? " premium_data_activated" : ""; ?>">
                                        <img data-target="<?php echo $data; ?>" <?php SCF::js_controller("lnt") ?>
                                             src="<?php echo SCF::imgcdn_url($data); ?>" alt="User Image"
                                             style="background-image: url('<?php echo SCF::imgcdn_url($data); ?>');"
                                             decoding="async" loading="lazy"/>
                                        <p>First
                                            Validated: <?php echo !empty($meta_data["thumbs"][$index]["first_seen"]) ? $meta_data["thumbs"][$index]["first_seen"] : "-"; ?></p>
                                        <p>Last
                                            Confirmed: <?php echo !empty($meta_data["thumbs"][$index]["last_seen"]) ? $meta_data["thumbs"][$index]["last_seen"] : "-"; ?></p>
                                        <a class="box-link" href="<?php echo $web_link; ?>" target="_blank">Found
                                            in <?php echo array_slice(explode(".", $uri["host"]), -2)[0]; ?></a>
                                    </div>
                                </div>
                                <?php
                            }
                            unset($index, $data, $uri);
                            ?>
                        </div>
                        <?php
                        if ($count > 4) {
                            ?>
                            <div class="text-right">
                                <div class="view_all_report btn btn-gray" <?php SCF::js_controller("results.view_all") ?>>
                                    <i>Show Less</i> <span class="si-up"></span></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div <?php SCF::js_controller("results.section.images") ?> class="report-box no-results">
                        <h2><span class="si-image"></span>Photos</h2>
                        <div class="scf_empty_report">
                            <img src="<?php echo $current_template_assets_url; ?>/images/no_results_head.svg"
                                 alt="No Data" decoding="async" loading="lazy"/>
                            <p>We searched popular websites and found no publicly viewable photos that are directly
                                associated with the person of interest.</p>
                        </div>
                    </div>
                    <?php
                }

                ?>

                <?php
                //New relationships breackdown stuctre

                if (!empty($result["relationships"]) || !empty($result["associated_people"])) {
                    $count = count($result["relationships"]) + count($result["associated_people"]);

                    /* $relationship_types = [
                    "/family|friend|work|^\$/im" => array( "Family / Friends", "#E94A74" ),
                    "/other/i" => array( "Followers", "#4F8BEB" ),
                    "/associates/i" => array( "Associated People", "#470FAA" )
                    ]; */
                    $relationship_types = [
                        "/p/i" => array("Parents", "#2AC984"),
                        "/m/i" => array("Spouse", "#470FAA"),
                        "/s/i" => array("Siblings", "#E52727"),
                        "/c/i" => array("Children", "#E94A74"),
                        "/i/i" => array("In-Laws", "#0F63EC"),
                        "/r/i" => array("Other Relatives", "#26273C"),
                        "/f/i" => array("Friends", "#767676"),
                        "/n|o/i" => array("Neighbors", "#E94A74"),
                        "/a/i" => array("Associates", "#4F5065"),
                        "/w/i" => array("Co-workers", "#F8933C"),
                        "/l/i" => array("Landlords", "#00B191"),
                        "/t/i" => array("Tenants", "#4F8BEB")
                    ];
                    $relationship_types_other = [
                        "/family|friend|work|^\$/im" => array("Family / Friends", "#E94A74"),
                        "/spouse/i" => array("Spouse", "#E94A74"),
                        "/siblings/i" => array("Siblings", "#00B191"),
                        "/law/i" => array("In-Law", "#4F8BEB"),
                        "/other/i" => array("Other Relatives", "#F8933C"),
                        "/neighbours/i" => array("Neighbors", "#D98A78"),
                        "/associates/i" => array("Associates", "#470FAA")
                    ];

                    $relationship_list = array_merge(is_array($result["relationships"]) ? $result["relationships"] : [], is_array($result["associated_people"]) ? $result["associated_people"] : []);
                    ?>
                    <div <?php SCF::js_controller("results.section.relationships") ?>
                            class="report-relationships report-box-group<?php echo(!empty($premium_only_data["relationships"]) && count($premium_only_data["relationships"]) ? " premium_data_included" : ""); ?>">
                        <div class="report-box" data-type="<?php echo SEARCH_TYPE_NAME; ?>">
                            <h2><span class="si-user"></span>Relationships <label><?php echo $count ?></label></h2>
                            <p class="box-title">Based on available relationship data from various websites, there is a
                                strong possibility that the people listed here are either relatives or friends.</p>

                            <?php foreach ($relationship_types as $_pattern => $_title) { ?>
                                <div class="relationships-box type-family" <?php SCF::js_controller("results.empty_relationship"); ?>>
                                    <p class="relationship_label"><?php echo $_title[0]; ?></p>
                                    <div class="row relationships_row">
                                        <?php
                                        foreach ($relationship_list as $rel_index => $rel_data) {
                                            if (!preg_match($_pattern, $meta_data["relationships"][$rel_index]["subtype"])) {
                                                continue;
                                            }
                                            ?>
                                            <div class="col-sm-6 col-md-3 data-point">
                                                <div class="box-col<?php echo isset($premium_only_data["relationships"][$rel_index]) ? " premium_data_activated" : ""; ?>"
                                                     style="border-left: 4px solid <?php echo $_title[1]; ?>;"
                                                     data-query="<?php echo $rel_data["name"] ?? $rel_data ?>" <?php SCF::js_controller("search.form_run_search") ?>>
                                                    <!--<div class="box-col<?php echo isset($premium_only_data["relationships"][$rel_index]) ? " premium_data_activated" : ""; ?>" style="border-left: 4px solid <?php echo $_title[1]; ?>;" <?php echo SCF::js_controller("search.run_search_specialist"); ?> data-query="<?php echo 'relations'; ?>" data-value="<?php echo $rel_data["name"] ?? $rel_data; ?>" data-type="name">-->
                                                    <a class="box-name"
                                                       data-id="<?php echo $meta_data["relationships"][$rel_index]["category"]; ?>"
                                                       title="<?php echo $rel_data["name"] ?? $rel_data; ?>"><?php echo $rel_data["name"] ?? $rel_data; ?></a>
                                                    <p>
                                                        Age: <?php echo !empty($meta_data["relationships"][$rel_index]["age"]) ? $meta_data["relationships"][$rel_index]["age"] : (!empty($rel_data["age"]) ? $rel_data["age"] : "-"); ?></p>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php
                            foreach ($relationship_types_other as $_pattern => $_title) { ?>
                                <div class="relationships-box type-family" <?php SCF::js_controller("results.empty_relationship"); ?>>
                                    <p class="relationship_label"><?php echo $_title[0]; ?></p>
                                    <div class="row relationships_row">
                                        <?php
                                        foreach ($relationship_list as $rel_index => $rel_data) {
                                            if (!preg_match($_pattern, $meta_data["relationships"][$rel_index]["category"]) || !empty($meta_data["relationships"][$rel_index]["subtype"])) {
                                                continue;
                                            }
                                            ?>
                                            <div class="col-sm-6 col-md-3 data-point">
                                                <div class="box-col<?php echo isset($premium_only_data["relationships"][$rel_index]) ? " premium_data_activated" : ""; ?>"
                                                     style="border-left: 4px solid <?php echo $_title[1]; ?>;"
                                                     data-query="<?php echo $rel_data["name"] ?? $rel_data; ?>" <?php SCF::js_controller("search.form_run_search") ?>>
                                                    <!--<div class="box-col<?php echo isset($premium_only_data["relationships"][$rel_index]) ? " premium_data_activated" : ""; ?>" style="border-left: 4px solid <?php echo $_title[1]; ?>;"  <?php echo SCF::js_controller("search.run_search_specialist"); ?> data-query="<?php echo 'relations'; ?>" data-value="<?php echo $rel_data["name"] ?? $rel_data; ?>" data-type="name">-->
                                                    <a class="box-name"
                                                       data-id="<?php echo $meta_data["relationships"][$rel_index]["category"]; ?>"
                                                       title="<?php echo $rel_data["name"] ?? $rel_data; ?>"><?php echo $rel_data["name"] ?? $rel_data; ?></a>
                                                    <p>
                                                        Age: <?php echo !empty($data_ref[$index]["age"]) ? $data_ref[$index]["age"] : (!empty($rel_data["age"]) ? $rel_data["age"] : "-"); ?></p>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>
                    </div>
                    <?php
                    unset($_pattern, $_title);
                } else {
                    ?>
                    <div <?php SCF::js_controller("results.section.relationships") ?> class="report-box no-results">
                        <h2><span class="si-user"></span>Relationships</h2>
                        <div class="scf_empty_report">
                            <img src="<?php echo $current_template_assets_url; ?>/images/no_results_head.svg"
                                 alt="No Data"/>
                            <p>We searched current and archived data sources but didn't find find relatives, friends, or
                                social media followers that can be clearly linked to the person of interest.</p>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <?php

                // Section: Addresses
                if (!empty($result["locations"])) {
                    $count = count($result["locations"]);
                    ?>
                    <div <?php SCF::js_controller("results.section.locations") ?>
                            class="report-box-location report-box<?php echo(!empty($premium_only_data["locations"]) && count($premium_only_data["locations"]) ? " premium_data_included" : ""); ?>"
                            data-type="<?php echo SEARCH_TYPE_RAS; ?>">
                        <h2><span class="si-location"></span>Map and Locations for <?php echo $query_text; ?>
                            <label><?php echo $count ?></label></h2>
                        <p class="box-title">These addresses may belong to the person you're searching for.</p>
                        <div class="row">
                            <?php
                            foreach ($result["locations"] as $index => $data) {
                                ?>
                                <div class="col-sm-6 col-md-3 data-point">
                                    <div class="box-col<?php echo isset($premium_only_data["locations"][$index]) ? " premium_data_activated" : ""; ?>">
                                        <iframe src="https://maps.google.com/maps?q=<?php echo rawurlencode($data) ?>&t=&z=13&ie=UTF8&iwloc=&output=embed"
                                                width="100%" height="100" allowfullscreen="" aria-hidden="false"
                                                tabindex="0"></iframe>
                                        <?php
                                        $split_location = explode(",", $data);
                                        $split_city = $split_location[0] . ",";
                                        unset($split_location[0]);
                                        $split_state = implode(",", $split_location);
                                        ?>
                                        <h5 title="<?php echo $data ?>"><?php echo "<span>" . $split_city . "</span>" . $split_state; ?></h5>
                                        <p>First
                                            Validated: <?php echo !empty($meta_data["locations"][$index]["first_seen"]) ? $meta_data["locations"][$index]["first_seen"] : "-"; ?></p>
                                        <p>Last
                                            Confirmed: <?php echo !empty($meta_data["locations"][$index]["last_seen"]) ? $meta_data["locations"][$index]["last_seen"] : "-"; ?></p>
                                        <a class="run-search btn"
                                           data-query="<?php echo $data ?>" <?php SCF::js_controller("search.form_run_search") ?>>Run
                                            Search</a>
                                    </div>
                                </div>
                                <?php
                            }
                            unset($index, $data);
                            ?>
                        </div>
                        <?php
                        if ($count > 4) {
                            ?>
                            <div class="text-right">
                                <div class="view_all_report btn btn-gray" <?php SCF::js_controller("results.view_all") ?>>
                                    <i>Show Less</i> <span class="si-up"></span></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div <?php SCF::js_controller("results.section.locations") ?> class="report-box no-results">
                        <h2><span class="si-location"></span>Addresses</h2>
                        <div class="scf_empty_report">
                            <img src="<?php echo $current_template_assets_url; ?>/images/no_results_head.svg"
                                 alt="No Data" decoding="async" loading="lazy"/>
                            <p>Multiple sources verify that there appears to be no address that can be definitively
                                associated with the person you're looking up.</p>
                        </div>
                    </div>
                    <?php
                }

                // Section: Phone Numbers
                if (!empty($result["phones"])) {
                    $count = count($result["phones"]);
                    ?>
                    <div <?php SCF::js_controller("results.section.phones") ?>
                            class="report-box<?php echo(!empty($premium_only_data["phones"]) && count($premium_only_data["phones"]) ? " premium_data_included" : ""); ?>"
                            data-type="<?php echo SEARCH_TYPE_PHONE; ?>">
                        <h2><span class="si-phone"></span>Phone Numbers<label><?php echo $count ?></label></h2>
                        <p class="box-title">Data from various sources, including popular directories, indicate that the
                            following phone numbers may have belonged or were in some way associated with the person
                            you're researching. </p>
                        <div class="row">
                            <?php
                            foreach ($result["phones"] as $index => $data) {
                                $phone_number_data = AreaCode::get_info_for_phone_numbers($result["phones"]);
                                $carrier = !empty($phone_number_data[$data]["carrier"]) ? $phone_number_data[$data]["carrier"] : "";
                                ?>
                                <div class="col-sm-6 col-md-3 data-point">
                                    <div class="box-col<?php echo isset($premium_only_data["phones"][$index]) ? " premium_data_activated" : ""; ?>">
                                        <a class="box-name" title="<?php echo $data ?>"><?php echo $data ?></a>
                                        <p title="<?php echo $carrier; ?>">Carrier: <?php echo $carrier ?: "-" ?></p>
                                        <p>
                                            Location: <?php echo !empty($phone_number_data[$data]["city"]) ? "{$phone_number_data[$data]["city"]}, {$phone_number_data[$data]["state"]}" : "-" ?></p>
                                        <p>Line
                                            Type: <?php echo !empty($phone_number_data[$data]["line_type"]) ? $phone_number_data[$data]["line_type"] : "-" ?></p>
                                        <p>First
                                            Validated: <?php echo !empty($meta_data["phones"][$index]["first_seen"]) ? $meta_data["phones"][$index]["first_seen"] : "-"; ?></p>
                                        <p>Last
                                            Confirmed: <?php echo !empty($meta_data["phones"][$index]["last_seen"]) ? $meta_data["phones"][$index]["last_seen"] : "-"; ?></p>
                                        <a class="run-search btn"
                                           data-query="<?php echo $data ?>" <?php SCF::js_controller("search.form_run_search") ?>>Run
                                            Search</a>
                                    </div>
                                </div>
                                <?php
                            }
                            unset($index, $data, $phone_number_data, $carrier);
                            ?>
                        </div>
                        <?php
                        if ($count > 4) {
                            ?>
                            <div class="text-right">
                                <div class="view_all_report btn btn-gray" <?php SCF::js_controller("results.view_all") ?>>
                                    <i>Show Less</i> <span class="si-up"></span></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div <?php SCF::js_controller("results.section.phones") ?> class="report-box no-results">
                        <h2><span class="si-phone"></span>Phone Numbers</h2>
                        <div class="scf_empty_report">
                            <img src="<?php echo $current_template_assets_url; ?>/images/no_results_head.svg"
                                 alt="No Data" decoding="async" loading="lazy"/>
                            <p>Multiple sources report no phone records are directly tied to your search. Note:
                                Sometimes phone numbers are registered under different names or households and don't
                                link the person who is using the phone.</p>
                        </div>
                    </div>
                    <?php
                }

                // Section: Emails
                if (!empty($result["emails"])) {
                    $count = count($result["emails"]);
                    ?>
                    <div <?php SCF::js_controller("results.section.emails") ?>
                            class="report-box data-breach-box<?php echo(!empty($premium_only_data["emails"]) && count($premium_only_data["emails"]) ? " premium_data_included" : ""); ?>"
                            data-type="<?php echo SEARCH_TYPE_EMAIL; ?>">
                        <h2><span class="si-email"></span>Emails<label><?php echo $count ?></label></h2>
                        <p class="box-title">We searched publicly available data online and found the following email
                            addresses are strongly linked to the person you're researching.</p>
                        <div class="uploading-data"><img
                                    src="<?php echo $current_template_assets_url ?>/images/loader-green.svg"
                                    alt="Loading..." decoding="async" loading="lazy"/>
                            <span>Updating Data Breach Report</span></div>
                        <table class="scf-table">
                            <thead>
                            <tr>
                                <th>Website</th>
                                <th>Data Breach Info</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr <?php SCF::js_element_var("row") ?>>
                                <td>
                                    <img <?php SCF::js_element_var("img") ?>
                                            src="<?php echo $current_template_assets_url ?>/images/loader-green.svg"
                                            alt="Adobe"
                                            style="background-image: url(<?php echo $current_template_assets_url ?>/images/loader-green.svg);"
                                            decoding="async" loading="lazy"/>
                                    <a <?php SCF::js_element_var("a") ?>></a>
                                </td>
                                <td>
                                    <p <?php SCF::js_element_var("desc") ?>></p>
                                    <p><strong>Compromised data:</strong>
                                        <span <?php SCF::js_element_var("comp") ?>></span></p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <?php
                        foreach ($result["emails"] as $index => $data) {
                            ?>
                            <div class="data-report data-point">
                                <div class="data-head">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h4 title="<?php echo $data ?>"><?php echo $data ?></h4>
                                            <span>First Validated: <?php echo !empty($meta_data["emails"][$index]["first_seen"]) ? $meta_data["emails"][$index]["first_seen"] : "-"; ?></span>
                                            <span>Last Confirmed: <?php echo !empty($meta_data["emails"][$index]["last_seen"]) ? $meta_data["emails"][$index]["last_seen"] : "-"; ?></span>
                                            <!--<a class="run-search" data-query="<?php echo $data ?>" <?php SCF::js_controller("search.form_run_search") ?>>Run Search</a>-->
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <h5 <?php SCF::js_element_var("not_found") ?>><span
                                                        class="si-done-circle"></span> Not found in any Reported Data
                                                Breach</h5>
                                            <h5 <?php SCF::js_element_var("reported") ?>>Reported in
                                                <span <?php SCF::js_element_var("counter") ?>>0</span> Incidents</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="data-section" <?php SCF::js_element_var("data") ?>>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <p>This email was involved in <span <?php SCF::js_element_var("count") ?>>0 data breach incidents</span>,
                                                the earliest of which was
                                                <span <?php SCF::js_element_var("date_first") ?>></span> and the latest
                                                of which was <span <?php SCF::js_element_var("date_last") ?>></span>.
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <div class="view_all_report btn btn-dark-green expand" <?php SCF::js_controller("results.section.emails.view") ?>>
                                                View List
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        unset($index, $data);
                        ?>
                        <?php
                        if ($count > 4) {
                            ?>
                            <div class="text-right">
                                <div class="view_all_report btn btn-gray" <?php SCF::js_controller("results.view_all") ?>>
                                    <i>Show Less</i> <span class="si-up"></span></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div <?php SCF::js_controller("results.section.emails") ?> class="report-box no-results">
                        <h2><span class="si-email"></span>Emails</h2>
                        <div class="scf_empty_report">
                            <img src="<?php echo $current_template_assets_url; ?>/images/no_results_head.svg"
                                 alt="No Data" decoding="async" loading="lazy"/>
                            <p>Several data providers covering the majority of the Internet confirms that there appears
                                to be no publicly-viewable email that is associated with the person of interest.</p>
                        </div>
                    </div>
                    <?php
                }

                // Section: Usernames
                if (!empty($result["usernames"])) {
                    $count = count($result["usernames"]);
                    ?>
                    <div <?php SCF::js_controller("results.section.usernames") ?>
                            class="report-box<?php echo(!empty($premium_only_data["usernames"]) && count($premium_only_data["usernames"]) ? " premium_data_included" : ""); ?>"
                            data-type="<?php echo SEARCH_TYPE_USERNAME; ?>">
                        <h2><span class="si-username"></span>Usernames<label><?php echo $count ?></label></h2>
                        <p class="box-title">Data gathered from multiple sources indicate that the following username(s)
                            are connected to the person you're looking up.</p>
                        <div class="row">
                            <?php
                            foreach ($result["usernames"] as $index => $data) {
                                ?>

                                <div class="col-sm-6 col-md-3 data-point">
                                    <div class="box-col<?php echo isset($premium_only_data["usernames"][$index]) ? " premium_data_activated" : ""; ?>">
                                        <a class="box-name" title="<?php echo $data ?>"><?php echo $data ?></a>
                                        <p>First
                                            Validated: <?php echo !empty($meta_data["usernames"][$index]["first_seen"]) ? $meta_data["usernames"][$index]["first_seen"] : "-"; ?></p>
                                        <p>Last
                                            Confirmed: <?php echo !empty($meta_data["usernames"][$index]["last_seen"]) ? $meta_data["usernames"][$index]["last_seen"] : "-"; ?></p>
                                        <a class="run-search btn"
                                           data-query="<?php echo $data ?>" <?php SCF::js_controller("search.form_run_search") ?>>Run
                                            Search</a>
                                    </div>
                                </div>
                                <?php
                            }
                            unset($index, $data);
                            ?>
                        </div>
                        <?php
                        if ($count > 4) {
                            ?>

                            <div class="text-right">
                                <div class="view_all_report btn btn-gray" <?php SCF::js_controller("results.view_all") ?>>
                                    <i>Show Less</i> <span class="si-up"></span></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div <?php SCF::js_controller("results.section.usernames") ?> class="report-box no-results">
                        <h2><span class="si-username"></span>Usernames</h2>
                        <div class="scf_empty_report">
                            <img src="<?php echo $current_template_assets_url; ?>/images/no_results_head.svg"
                                 alt="No Data" decoding="async" loading="lazy"/>
                            <p>We searched current and past Online information sources and didn't find usernames that
                                can be conclusively associated with the search subject.</p>
                        </div>
                    </div>
                    <?php
                }

                // Section: Websites
                if (!empty($result["urls"])) {
                    $domain_list = [];
                    $direct_url = [];
                    foreach ($result["urls"] as $index => $data) {
                        $uri = parse_url($data);
                        if (!empty($uri["host"])) {
                            $tmp_domain = preg_replace("/^.*?([^\.]+\.[^\.]+)\$/m", "\\1", $uri["host"]);
                            $domain_list[] = $tmp_domain;
                            if (empty($direct_url[$tmp_domain])) {
                                $direct_url[$tmp_domain] = $data;
                            }
                        }
                    }
                    $domain_list = array_unique($domain_list);
                    ?>
                    <div <?php SCF::js_controller("results.section.urls") ?>
                            class="report-box social-reports<?php echo(!empty($premium_only_data["urls"]) && count($premium_only_data["urls"]) ? " premium_data_included" : ""); ?>">
                        <h2><span class="si-website"></span>Websites<label><?php echo count($result["urls"]) ?></label>
                        </h2>
                        <p class="box-title">Information from multiple data sources point to the following website(s)
                            being closely linked to the person you're looking up.</p>
                        <ul>
                            <?php
                            foreach ($domain_list as $domain) {
                                ?>
                                <li><a target="_blank" href="<?php echo $direct_url[$domain]; ?>"> <span class="favicon"
                                                                                                         style="background-image: url('https://www.google.com/s2/favicons?domain=<?php echo $domain ?>')"></span> <?php echo $domain ?>
                                    </a></li>
                                <?php
                            }
                            ?>
                        </ul>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $url_types = [
                                    "/personal_profiles/i" => "Personal",
                                    "/professional_and_business/i" => "Business",
                                    "/background_reports|contact_details|email_address|media|public_records|publications|school_and_classmates|web_pages/i" => "Additional",
                                ];

                                foreach ($url_types as $_pattern => $_title) {
                                    $data_set = [];
                                    foreach ($meta_data["urls"] as $_index => $_data) {
                                        if (!empty($result["urls"][$_index]) && preg_match($_pattern, $_data["category"])) {
                                            $data_set[$_index] =& $meta_data["urls"][$_index];
                                        }
                                    };

                                    if (empty($data_set)) {
                                        continue;
                                    }

                                    $slider_key = "results.slider." . strtolower($_title);
                                    $count = count($data_set);
                                    ?>
                                    <div class="box-col">
                                        <div class="row">
                                            <div class="col-xs-7">
                                                <h4><?php echo $_title ?><span>(<?php echo $count; ?>)</span></h4>
                                            </div>
                                            <?php
                                            if ($count > 2) {
                                                ?>
                                                <div class="col-xs-5 box-slide <?php echo ($count < 4) ? " mobile-only" : "" ?>">
                                                    <span class="si-left-circle" data-direction="-"
                                                          data-target="<?php echo $slider_key ?>" <?php SCF::js_controller("slider.slide") ?>></span>
                                                    <span class="si-right-circle" data-direction="+"
                                                          data-target="<?php echo $slider_key ?>" <?php SCF::js_controller("slider.slide") ?>></span>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="row url-set" <?php SCF::js_controller($slider_key) ?>>
                                            <?php
                                            foreach ($data_set as $index => $data) {
                                                if (empty($result["urls"][$index])) {
                                                    continue;
                                                }

                                                $uri = parse_url($result["urls"][$index]);
                                                $domain = preg_replace("/^.*?([^\.]+\.[^\.]+)\$/m", "\\1", $uri["host"]);
                                                ?>
                                                <div class="col-xs-6 col-md-3 data-point">
                                                    <a href="<?php echo $result["urls"][$index]; ?>" target="_blank"
                                                       data-target="<?php echo $result["urls"][$index]; ?>" <?php SCF::js_controller("lnt") ?>>
                                                        <div class="box-col2">
                                                            <div class="link-title"><span class="favicon"
                                                                                          style="background-image: url('https://www.google.com/s2/favicons?domain=<?php echo $domain ?>')"></span> <?php echo $domain; ?>
                                                            </div>
                                                            <p><?php echo !empty($result["url_previews"][$index]["content"]) ? $result["url_previews"][$index]["content"] : (!empty($result["url_previews"][$index]["url_title"]) ? $result["url_previews"][$index]["url_title"] : "URL preview not available"); ?></p>
                                                        </div>
                                                    </a>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                unset($_data, $data_set, $_pattern, $_title, $url_types);
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div <?php SCF::js_controller("results.section.urls") ?> class="report-box no-results">
                        <h2><span class="si-website"></span>Websites</h2>
                        <div class="scf_empty_report">
                            <img src="<?php echo $current_template_assets_url; ?>/images/no_results_head.svg"
                                 alt="No Data" decoding="async" loading="lazy"/>
                            <p>The person you're looking up is not clearly linked with any publicly accessible website,
                                according to various data sources.</p>
                        </div>
                    </div>
                    <?php
                }
                //end of if(!$pwnd_and_IPQ_only){
            } else {
                ?>
                <div class="report-box no-results" style="background-color:#f8f8f8;border:0">
                    <div class="scf_empty_report">
                        <img src="<?php echo $current_template_assets_url; ?>/images/report-empty.svg" width="56"
                             alt="No Data" decoding="async" loading="lazy"/>
                        <p>We did a thorough search of databases but, unfortunately, didn't find information in the
                            following categories. Don't give up! Keep Searching with our other seaching tools.</p>
                    </div>


                </div>
                <p>&nbsp;</p>
                <div class="report-box no-results regapi">
                    <button type="button" class="btn btn-bordered-gray"><i class="si-image"></i> Photos</button>
                    <button type="button" class="btn btn-bordered-gray"><i class="si-user"></i> Relationships</button>
                    <button type="button" class="btn btn-bordered-gray"><i class="si-location"></i> Associated Locations
                    </button>
                    <button type="button" class="btn btn-bordered-gray"><i class="si-website"></i> Websites</button>
                    <button type="button" class="btn btn-bordered-gray"><i class="si-phone"></i> Phone Numbers</button>
                    <button type="button" class="btn btn-bordered-gray"><i class="si-email"></i> Emails</button>
                    <button type="button" class="btn btn-bordered-gray"><i class="si-username"></i> Usernames</button>
                </div>
                <?php
            }

            // Section: Jobs
            if (!empty($result["jobs_in_detail"])) {
                $count = count($result["jobs_in_detail"]);
                ?>
                <div <?php SCF::js_controller("results.section.jobs") ?>
                        class="report-box<?php echo(!empty($premium_only_data["jobs_in_detail"]) && count($premium_only_data["jobs_in_detail"]) ? " premium_data_included" : ""); ?>">
                    <h2><span class="si-username"></span>Jobs<label><?php echo $count ?></label></h2>
                    <p class="box-title">We found employment related information from additional premium data sources
                        which possibly shows the job history of the subject of your search.</p>
                    <div class="row">
                        <?php
                        foreach ($result["jobs_in_detail"] as $index => $data) {
                            ?>

                            <div class="col-sm-6 col-md-3 data-point">
                                <div class="box-col<?php echo isset($premium_only_data["jobs_in_detail"][$index]) ? " premium_data_activated" : ""; ?>">
                                    <a class="box-name"
                                       title="<?php echo !empty($data["title"]) ? $data["title"] : "N/A"; ?>"><?php echo !empty($data["title"]) ? $data["title"] : "N/A"; ?></a>
                                    <p>Industry: <?php echo !empty($data["industry"]) ? $data["industry"] : "-" ?></p>
                                    <p>
                                        Organization: <?php echo !empty($data["organization"]) ? $data["organization"] : "-" ?></p>
                                    <p>Start
                                        Date: <?php echo !empty($data["date_range"]["start"]) ? $data["date_range"]["start"] : "-" ?></p>
                                    <p>End
                                        Date: <?php echo !empty($data["date_range"]["end"]) ? $data["date_range"]["end"] : "-" ?></p>
                                    <p>First
                                        Validated: <?php echo !empty($meta_data["jobs"][$index]["first_seen"]) ? $meta_data["jobs"][$index]["first_seen"] : "-"; ?></p>
                                    <p>Last
                                        Confirmed: <?php echo !empty($meta_data["jobs"][$index]["last_seen"]) ? $meta_data["jobs"][$index]["last_seen"] : "-"; ?></p>
                                </div>
                            </div>
                            <?php
                        }
                        unset($index, $data);
                        ?>
                    </div>
                    <?php
                    if ($count > 4) {
                        ?>

                        <div class="text-right">
                            <div class="view_all_report btn btn-gray" <?php SCF::js_controller("results.view_all") ?>>
                                <i>Show Less</i> <span class="si-up"></span></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }

            // Section: Education
            if (!empty($result["education_in_detail"])) {
                $count = count($result["education_in_detail"]);
                ?>
                <div <?php SCF::js_controller("results.section.educations") ?>
                        class="report-box<?php echo(!empty($premium_only_data["education_in_detail"]) && count($premium_only_data["jobs_in_detail"]) ? " premium_data_included" : ""); ?>">
                    <h2><span class="si-username"></span>Education<label><?php echo $count ?></label></h2>
                    <p class="box-title">We gathered education related information from one of our premier data
                        providers that may show where your search subject might have been enrolled.</p>
                    <div class="row">
                        <?php
                        foreach ($result["education_in_detail"] as $index => $data) {
                            ?>

                            <div class="col-sm-6 col-md-3 data-point">
                                <div class="box-col<?php echo isset($premium_only_data["jobs_in_detail"][$index]) ? " premium_data_activated" : ""; ?>">
                                    <a class="box-name"
                                       title="<?php echo $data["degree"] ?>"><?php echo(!empty($data["degree"]) ? $data["degree"] : "N/A"); ?></a>
                                    <p>School: <?php echo !empty($data["school"]) ? $data["school"] : "-" ?></p>
                                    <p>Start
                                        Date: <?php echo !empty($data["date_range"]["start"]) ? $data["date_range"]["start"] : "-" ?></p>
                                    <p>End
                                        Date: <?php echo !empty($data["date_range"]["end"]) ? $data["date_range"]["end"] : "-" ?></p>
                                    <p>First
                                        Validated: <?php echo !empty($meta_data["education"][$index]["first_seen"]) ? $meta_data["education"][$index]["first_seen"] : "-"; ?></p>
                                    <p>Last
                                        Confirmed: <?php echo !empty($meta_data["education"][$index]["last_seen"]) ? $meta_data["education"][$index]["last_seen"] : "-"; ?></p>
                                </div>
                            </div>
                            <?php
                        }
                        unset($index, $data);
                        ?>
                    </div>
                    <?php
                    if ($count > 4) {
                        ?>

                        <div class="text-right">
                            <div class="view_all_report btn btn-gray" <?php SCF::js_controller("results.view_all") ?>>
                                <i>Show Less</i> <span class="si-up"></span></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }

            ?>
            <?php if (!empty($premium_content_user)) { ?>
                <?php if (!empty($result["bankruptcy"]) || !empty($result["judgment"]) || !empty($result["lien"]) || !empty($result["professional"])) { ?>
                    <div class="disclaimer-note">
                        <p><span class="si-info"></span> Disclaimer: We searched publicly available data online and
                            found the following information are strongly linked to the person you're researching.</p>
                    </div>
                <?php } ?>
                <?php if (!empty($result["bankruptcy"])) { ?>
                    <div <?php SCF::js_controller("results.section.bankruptcy") ?> class="report-box">
                        <h2><span class="si-cooperation"></span>Bankruptcies
                            <label><?php echo count($result["bankruptcy"]); ?></label> <span class="premium_label">Premium Data</span>
                        </h2>
                        <?php foreach ($result["bankruptcy"] as $index => $data) { ?>
                            <div class="report-list-sub">
                                <div class="list-head">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="list-title">Case Number:
                                                <span><?php echo $data["fullCaseNumber"]; ?></span></div>
                                            <p><?php echo $data["court"]["name"]; ?></p>
                                            <p><?php echo $data["caseStatus"]; ?></p>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <a class="btn btn-dark-green show-btn xxx" <?php SCF::js_controller("results.idi_view_more") ?>><span>View Details</span>
                                                <i class="si-down-circle"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-content">
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Full Case Number</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["fullCaseNumber"]; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Chapter</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["chapter"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Case Status</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["caseStatus"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Case Status Date</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>
                                                : <?php echo $data["caseStatusDate"]["data"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Filing Date</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>
                                                : <?php echo $data["filingDate"]["data"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Last Date To File Poc</strong></p>
                                        </div>
                                        <div class="col-md-3 col-xs-6"><p>
                                                : <?php echo $data["lastDateToFilePoc"]["data"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Judge Initials</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["judgeInitials"]; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Judge Name</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["judgeName"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Meeting Address</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>
                                                : <?php echo $data["meeting"]["address"]["complete"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Meeting Date</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>
                                                : <?php echo $data["meeting"]["date"]["data"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Asset Indicator</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["assetIndicator"]; ?></p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>screen</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["screen"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Converted</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["converted"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Converted Date</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>
                                                : <?php echo $data["convertedDate"]["data"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Date Collected</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>
                                                : <?php echo $data["dateCollected"]["data"]; ?></p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Transaction Id</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["transactionId"]; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Voluntary Flag</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["voluntaryFlag"]; ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-xs-6"><p><strong>Pro Se Indicator</strong></p></div>
                                        <div class="col-md-3 col-xs-6"><p>: <?php echo $data["proSeIndicator"]; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-content">
                                    <h4>Attorney Information:</h4>
                                    <p><?php echo $data["attorney"]["lawFirm"] . "<br />" . $data["attorney"]["address"]["complete"] . "<br />" . $data["attorney"]["address"]["city"] . " " . $data["attorney"]["address"]["state"] . " " . $data["attorney"]["address"]["zip"] . " " . $data["attorney"]["address"]["zip4"] . "<br />" . $data["attorney"]["phone"]; ?></p>
                                </div>
                                <div class="list-content">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4>Debtor:</h4>
                                            <p><?php echo $data["debtor1"]["name"][0]["data"] . "<br />" . $data["debtor1"]["address"]["complete"] . "<br />" . $data["debtor1"]["address"]["city"] . " " . $data["debtor1"]["address"]["state"] . " " . $data["debtor1"]["address"]["zip"] . " " . $data["debtor1"]["address"]["zip4"]; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4>Codebtor:</h4>
                                            <p><?php echo $data["debtor2"]["name"][0]["data"] . "<br />" . $data["debtor2"]["address"]["complete"] . "<br />" . $data["debtor2"]["address"]["city"] . " " . $data["debtor2"]["address"]["state"] . " " . $data["debtor2"]["address"]["zip"] . " " . $data["debtor2"]["address"]["zip4"]; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-content">
                                    <h4>Trustee:</h4>
                                    <p><?php echo $data["trustee"]["name"]["data"] . "<br />" . $data["trustee"]["address"]["complete"] . "<br />" . $data["trustee"]["address"]["city"] . " " . $data["trustee"]["address"]["state"] . " " . $data["trustee"]["address"]["zip"] . " " . $data["trustee"]["address"]["zip4"] . "<br />" . $data["trustee"]["phone"]; ?></p>
                                </div>
                            </div>
                        <?php }
                        unset($index, $data); ?>
                    </div>
                <?php } ?>
                <?php if (!empty($result["lien"])) { ?>
                    <div <?php SCF::js_controller("results.section.lien") ?> class="report-box">
                        <h2><span class="si-property"></span>Liens
                            <label><?php echo count($result["lien"]); ?></label><span
                                    class="premium_label">Premium Data</span></h2>
                        <?php foreach ($result["lien"] as $index => $data) { ?>
                            <div class="report-list-sub">
                                <div class="list-head">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="list-title">Debtor:
                                                <span><?php echo $data["debtor"][0]["name"][0]["first"] . " " . $data["debtor"][0]["name"][0]["middle"] . " " . $data["debtor"][0]["name"][0]["last"]; ?></span>
                                            </div>
                                            <p><?php echo $data["debtor"][0]["address"][0]["complete"] . ", " . $data["debtor"][0]["address"][0]["city"] . " " . $data["debtor"][0]["address"][0]["state"] . " " . $data["debtor"][0]["address"][0]["zip"] . ", " . $data["debtor"][0]["address"][0]["zip4"]; ?></p>
                                            <p><strong>Lien Amount:
                                                    <span><?php echo round($data["info"][0]["amount"], 2); ?></span></strong>
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <a class="btn btn-dark-green show-btn" <?php SCF::js_controller("results.idi_view_more") ?>><span>View Details</span>
                                                <i class="si-down-circle"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-content">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-xs-6 col-md-3"><p><strong>Case Description</strong></p>
                                                </div>
                                                <div class="col-xs-6 col-md-9"><p>
                                                        : <?php echo $data["record"][0]["caseDescription"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Fips Code</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"][0]["fipsCode"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Case County</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"][0]["caseCounty"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Fcase State</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"][0]["caseState"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Amount</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["info"][0]["amount"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Deed Category</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["deedCategory"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Document Number</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["documentLocation"]["docNumber"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Original Document Number</strong></p>
                                                </div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["originalDocumentLocation"]["docNumber"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Recording Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["recordingDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["date"]["data"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Damar Type</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["damarType"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Original Recording Date</strong></p>
                                                </div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["origRecordingDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Tax Period Max</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["taxPeriodMax"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Tax Period Min</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["taxPeriodMin"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Refile Extend Last Date</strong></p>
                                                </div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["refileExtendLastDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Abstract Issue Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["abstractIssueDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Stay Ordered Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["stayOrderedDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Document Filing Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["documentFilingDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Original Document Date</strong></p>
                                                </div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["origDocumentDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Court Case Number</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["courtCaseNumber"][0]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Tax Certification Number</strong></p>
                                                </div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["taxCertificationNumber"][0]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Lien Type</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["lienType"][0]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Creditor</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["creditor"][0]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Issuing Agency</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["issuingAgency"][0]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Property</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["property"][0]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Business</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["business"][0]; ?></p></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        unset($index, $data); ?>
                    </div>
                <?php } ?>
                <?php if (!empty($result["judgment"])) { ?>
                    <div <?php SCF::js_controller("results.section.judgment") ?> class="report-box">
                        <h2><span class="si-criminal-rec"></span>Judgments
                            <label><?php echo count($result["judgment"]); ?></label><span class="premium_label">Premium Data</span>
                        </h2>
                        <?php foreach ($result["judgment"] as $index => $data) { ?>
                            <div class="report-list-sub">
                                <div class="list-head">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="list-title">Defendant:
                                                <span><?php echo $data["defendant"][0]["name"][0]["first"] . " " . $data["defendant"][0]["name"][0]["middle"] . " " . $data["defendant"][0]["name"][0]["last"]; ?></span>
                                            </div>
                                            <p><?php echo $data["defendant"][0]["address"][0]["complete"] . ", " . $data["defendant"][0]["address"][0]["city"] . " " . $data["defendant"][0]["address"][0]["state"] . " " . $data["defendant"][0]["address"][0]["zip"] . ", " . $data["defendant"][0]["address"][0]["zip4"]; ?></p>
                                            <p><strong>Lien Amount:
                                                    <span><?php echo round($data["info"][0]["amount"], 2); ?></span></strong>
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <a class="btn btn-dark-green show-btn" <?php SCF::js_controller("results.idi_view_more") ?>><span>View Details</span>
                                                <i class="si-down-circle"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-content">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-xs-6 col-md-3"><p><strong>Case Description</strong></p>
                                                </div>
                                                <div class="col-xs-6 col-md-9"><p>
                                                        : <?php echo $data["record"][0]["caseDescription"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Fips Code</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"][0]["fipsCode"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Case State</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"][0]["caseState"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Amount</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["info"][0]["amount"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>InterestRate</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"][0]["interestRate"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Creditor</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"][0]["creditor"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Deed Category</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["deedCategory"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Book Number</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["documentLocation"]["bookNumber"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Page Number</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["documentLocation"]["pageNumber"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Document Number</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["documentLocation"]["docNumber"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Recording Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["recordingDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["date"]["data"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Damar Type</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["damarType"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Original Recording Date</strong></p>
                                                </div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["origRecordingDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Tax Period Max</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["taxPeriodMax"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Tax Period Min</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["taxPeriodMin"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Refile Extend Last Date</strong></p>
                                                </div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["refileExtendLastDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Abstract Issue Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["abstractIssueDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Stay Ordered Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["stayOrderedDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Document Filing Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["documentFilingDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>original Document Date</strong></p>
                                                </div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["record"][0]["origDocumentDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Court Case Number</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["courtCaseNumber"][0]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Attorney</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["attorney"][0]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Creditor</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["creditor"][0]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Business</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["business"][0]; ?></p></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        unset($index, $data); ?>
                    </div>
                <?php } ?>
                <?php if ($idi_show_criminal && !empty($result["criminal"])) { ?>
                    <div <?php SCF::js_controller("results.section.criminal") ?> class="report-box">
                        <h2><span class="si-criminal-rec"></span>Possible Criminal / Infractions
                            <label><?php echo count($result["criminal"]); ?></label><span class="premium_label">Premium Data</span>
                        </h2>
                        <?php foreach ($result["criminal"] as $index => $data) { ?>
                            <div class="report-list-sub">
                                <div class="list-head">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="list-title">Name:
                                                <span><?php echo $data["name"][0]["data"]; ?></span> &nbsp;&nbsp;&nbsp;
                                                Case Number:
                                                <span><?php echo $data["offense"][0]["caseNumber"]; ?></span></div>
                                            <p>Source: <?php echo $data["offense"][0]["sourceName"]; ?></p>
                                            <p>Charges
                                                Filed: <?php echo $data["offense"][0]["chargesFiledDate"]["data"]; ?></p>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <a class="btn btn-dark-green show-btn" <?php SCF::js_controller("results.idi_view_more") ?>><span>View Details</span>
                                                <i class="si-down-circle"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-content">
                                    <h4>Personal Information:</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Full Name</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["name"][0]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Aka Flag</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["name"][0]["akaFlag"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>DOB</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["dob"][0]["date"]["data"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Age</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["age"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Hair Color</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["hairColor"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Eye Color</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["eyeColor"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Height</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["height"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Weight</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["weight"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Race</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["race"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Sex</strong></p></div>
                                                <div class="col-xs-6"><p>: <?php echo $data["sex"]; ?></p></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php foreach ($data["offense"] as $criminal_offense_index => $criminal_offense) { ?>
                                    <div class="list-content">
                                        <h4>Offense: <?php echo $criminal_offense_index + 1; ?></h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-xs-6 col-md-3"><p><strong>Description</strong></p>
                                                    </div>
                                                    <div class="col-xs-6 col-md-9"><p>
                                                            : <?php echo $criminal_offense["description"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Case Number</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["caseNumber"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Category</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["category"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Source State</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["sourceState"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Source Name</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["sourceName"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Charges Filed Date</strong></p>
                                                    </div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["chargesFiledDate"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Conviction</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["conviction"]["date"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Warrant</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["warrant"]["date"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Supervision</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["supervision"]["date"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Commitment</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["commitment"]["date"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Disposition</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["disposition"]["date"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Arrest</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["arrest"]["date"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Court</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["court"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>County Or Jurisdiction</strong></p>
                                                    </div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["countyOrJurisdiction"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Release Date</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_offense["releaseDate"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php foreach ($data["crime"] as $criminal_crime_index => $criminal_crime) { ?>
                                    <div class="list-content">
                                        <h4>Crime: <?php echo $criminal_crime_index + 1; ?></h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-xs-6 col-md-3"><p><strong>Offense
                                                                description</strong></p></div>
                                                    <div class="col-xs-6 col-md-9"><p>
                                                            : <?php echo $criminal_crime["offense"]["description"][0]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-xs-6 col-md-3"><p><strong>Comments</strong></p>
                                                    </div>
                                                    <div class="col-xs-6 col-md-9"><p>
                                                            : <?php echo $criminal_crime["comments"][0]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Case Number</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["caseNumber"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Source Name</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["sourceName"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Source State</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["sourceState"]; ?></p></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Offense category</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["offense"]["category"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Offense Date</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["offense"]["date"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Arresting Agency</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["arrest"]["arrestingAgency"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Arrest warrant</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["arrest"]["warrant"]["date"]["data"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Case Type</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["courtCase"]["caseType"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>Court</strong></p></div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["courtCase"]["court"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-xs-6"><p><strong>County Or Jurisdiction</strong></p>
                                                    </div>
                                                    <div class="col-xs-6"><p>
                                                            : <?php echo $criminal_crime["courtCase"]["countyOrJurisdiction"]; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <!-- <div class="list-content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6"><p><strong>Comments</strong></p></div>
                                <div class="col-md-6"><p>: Citation Number 200728</p></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6"><p><strong>Status</strong></p></div>
                                <div class="col-md-6"><p>: Disposed</p></div>
                            </div>
                        </div>
                    </div>
                </div> -->
                            </div>
                        <?php }
                        unset($index, $data); ?>
                    </div>
                <?php } ?>
                <?php
                if (!empty($result["professional"])) { ?>
                    <div <?php SCF::js_controller("results.section.professional") ?> class="report-box">
                        <h2><span class="si-account"></span>Professional Licenses
                            <label><?php echo count($result["professional"]); ?></label><span class="premium_label">Premium Data</span>
                        </h2>
                        <?php foreach ($result["professional"] as $index => $data) { ?>
                            <div class="report-list-sub">
                                <div class="list-head">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="list-title">License Description:
                                                <span><?php echo $data["info"]["license"]["desc"]; ?></span></div>
                                            <p>License Number: <?php echo $data["info"]["license"]["number"]; ?></p>
                                            <p>License Status: <?php echo $data["info"]["status"]; ?></p>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <a class="btn btn-dark-green show-btn" <?php SCF::js_controller("results.idi_view_more") ?>><span>View Details</span>
                                                <i class="si-down-circle"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-content">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>License state</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"]["license"]["state"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>License Board</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"]["license"]["board"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Record Type</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"]["recordType"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Record Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"]["recordDate"]["data"]; ?></p></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Original Issue Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"]["originalIssueDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Registered Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"]["registeredDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Expiration Date</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["info"]["expirationDate"]["data"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-xs-6"><p><strong>Person Name</strong></p></div>
                                                <div class="col-xs-6"><p>
                                                        : <?php echo $data["person"][0]["name"][0]["first"] . " " . $data["person"][0]["name"][0]["middle"] . " " . $data["person"][0]["name"][0]["last"]; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-xs-3"><p><strong>Address</strong></p></div>
                                                <div class="col-xs-9"><p>
                                                        : <?php echo $data["address"][0]["data"]; ?></p></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        unset($index, $data); ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <?php
            if (empty($premium_content_user) && !empty($result['premium_data'])) {
                if (!isset($_SESSION["step_zero_premium_scroll"]) && !isset($_SESSION["ab_premium_scroll"])) {
                    $_SESSION["ab_premium_scroll"] = $abtester->get_experiment("ab_premium_scroll", session_id(), SYSTEM::bot_detected() ? "standard" : "");
                    $_SESSION["step_zero_premium_scroll"] = true;

                    if (SYSTEM::get_device_type() == "mobile") {
                        $_SESSION["ab_premium_scroll"]->track_event("0_landing_premium_mobile", SYSTEM::get_device_type());
                    } elseif (SYSTEM::get_device_type() == "tablet") {
                        $_SESSION["ab_premium_scroll"]->track_event("0_landing_premium_tablet", SYSTEM::get_device_type());
                    } else {
                        $_SESSION["ab_premium_scroll"]->track_event("0_landing_premium_desktop", SYSTEM::get_device_type());
                    }
                }

                $section_list = [
                    "images" => "Photos",
                    "relationships" => "Relationships",
                    "locations" => "Addresses",
                    "phones" => "Phone Numbers",
                    "emails" => "Emails",
                    "usernames" => "Usernames",
                    "jobs" => "Jobs",
                    "jobs_in_detail" => "Jobs",
                    "education" => "Education",
                    "education_in_detail" => "Education",
                    "bankruptcy" => "Bankruptcy",
                    "lien" => "Liens",
                    "judgment" => "Judgments",
                    "criminal" => "Possible Criminal / Infractions",
                    "professional" => "Professional Licenses",
                    "property" => "Propety Owner",
                ];

                $total_counts = 0;
                foreach ($result['premium_data'] as $key => $count) {
                    $total_counts += $count;
                }
                ?>
                <div class="report-box rp-access-premium idi_premium_found">
                    <img src="<?php echo $current_template_assets_url; ?>/images/premium_found.svg"
                         alt="Premium Found"/>
                    <h4>Found More Data on <a><?php echo $query_text; ?></a>.
                        <span><?php echo $total_counts . " +"; ?></span></h4>
                    <p>We just checked premium sources and found the following additional information.</p>
                    <ul>
                        <?php
                        foreach ($result['premium_data'] as $key => $count) {
                            ?>
                            <li><span class="si-lock-fill"></span> <?php echo "{$section_list[$key]}" ?></li>
                            <?php
                        }
                        ?>
                    </ul>
                    <div class="btn btn-dark-green"
                         data-target="premium_data_found" <?php SCF::js_controller("modal.onclick_show"); ?>>UNLOCK
                        PREMIUM
                    </div>
                </div>
                <?php
            }

            // *** Image Search ***
        } elseif (!empty($image)) {
            include("ris_report.php");
        } elseif (!empty($search_data["ras_records"])) {

            /** Reverse Address Search **/

            $property_info = new ObjectProxy($search_data["ras_records"]["property_info"]);
            $radius_info = new ObjectProxy($search_data["ras_records"]["radius_info"]);
            $deed_info = &$search_data["ras_records"]["deed_info"];

            $tax_info_data = unserialize($property_info['tax_other_info']) ?: [];
            $tax_info = new ObjectProxy($tax_info_data);

            $legal_info_data = unserialize($property_info["legal"]) ?: [];
            $legal_info = new ObjectProxy($legal_info_data);

            $int_room_info_data = unserialize($property_info["int_room_info"]) ?: [];
            $int_room_info = new ObjectProxy($int_room_info_data);

            $ext_building_info_data = unserialize($property_info["ext_building_info"]) ?: [];
            $ext_building_info = new ObjectProxy($ext_building_info_data);

            $property_use_info_data = unserialize($property_info["property_use_info"]) ?: [];
            $property_use_info = new ObjectProxy($property_use_info_data);

            $parking_info_data = unserialize($property_info["parking"]) ?: [];
            $parking_info = new ObjectProxy($parking_info_data);

            $pool_info_data = unserialize($property_info["pool"]) ?: [];
            $pool_info = new ObjectProxy($pool_info_data);

            $true_values = ["yes", "true"];
            $possible_owners = (($deed_info[0]["primary_grantee_1"] ? 1 : 0) + ($deed_info[0]["primary_grantee_2"] ? 1 : 0) + ($deed_info[0]["secondary_grantee_1"] ? 1 : 0) + ($deed_info[0]["secondary_grantee_2"] ? 1 : 0));
            $assessor_records = count(array_filter($search_data["ras_records"]["property_info"], function ($data) {

                return !empty($data) && ($data != 0);
            }));

            $loan_count = count($deed_info);
            $loans = [];
            array_walk($deed_info, function ($data) use (&$loans) {

                $mortgage_data = unserialize($data["mortgage_1"]);
                if (!empty($mortgage_data["Amount"])) {
                    $loans[] = $mortgage_data["Amount"];
                }
            });
            list($loan_min, $loan_max) = [min($loans), max($loans)];
            $loan_range = empty($loans) ? "" : ($loan_min == $loan_max ? "\${$loan_min}" : "\${$loan_min} - \${$loan_max}");

            ?>
            <form method="post"
                  action="<?php echo RELATIVE_URL . "search.html" ?>" <?php SCF::js_controller("search.click_form") ?>
                  target="_blank">
                <input type="hidden" name="search_type" value=""/>
                <input type="hidden" name="full_name" value=""/>
                <input type="hidden" name="phone" value=""/>
                <input type="hidden" name="email" value=""/>
                <input type="hidden" name="username" value=""/>
            </form>
            <form method="post"
                  action="<?php echo RELATIVE_URL . "dashboard.html" ?>" <?php SCF::js_controller("search.click_form_ras") ?>
                  target="_blank">
                <input type="hidden" name="search_type" value=""/>
                <input type="hidden" name="address" value=""/>
            </form>
            <div class="report-box report-main">
                <div class="row">
                    <div class="col-sm-8 report-img">
                        <iframe class="property-map" frameborder="0"
                                src="https://maps.google.com/maps?q=<?php echo rawurlencode($property_info["address"]) ?>&t=&z=13&ie=UTF8&iwloc=&output=embed"
                                width="156" height="156" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
                        <div class="report-content">
                            <span>Records found for </span>
                            <h4><?php echo $property_info["address"]; ?></h4>
                            <strong class="ras-state">State</strong>
                            <p><?php echo $property_info["state"] ?></p>
                        </div>
                    </div>
                    <?php
                    if (false) {
                        ?>
                        <div class="col-sm-4 text-right actions">
                            <div class="btn btn-bordered-gray">Share Now <span class="si-share"></span></div>
                            <br>
                            <div class="btn btn-darkgray">Tracking Off <span class="si-notification"></span></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="report-box rb-property-summary">
                <h3>Property Summary</h3>
                <div class="row summary-lists">
                    <div class="col-md-4"><span class="si-construct"></span>Constructed in
                        <label><?php echo $property_info["year_built"] ?: "-"; ?></label></div>
                    <div class="col-md-4"><span
                                class="si-ruler"></span><?php echo $property_info["area_building"] ? "{$property_info["area_building"]} SF Building" : ""; ?>
                        <label><?php echo $property_info["area_lot_sf"] ? "{$property_info["area_lot_sf"]} SF Lot" : ""; ?></label>
                    </div>
                    <div class="col-md-4"><span
                                class="si-home-plan"></span><?php echo $property_info["bedroom_count"]; ?> Bedrooms
                        <label><?php echo $property_info["bath_count"]; ?> Baths</label></div>
                    <div class="col-md-4"><span class="si-sale"></span>Sale Price
                        <label><?php echo ($property_info["deed_last_sale_price"] && "0.00" != $property_info["deed_last_sale_price"]) ? "\${$property_info["deed_last_sale_price"]}" : "-"; ?></label>
                    </div>
                    <div class="col-md-4"><span
                                class="si-tracked-data"></span><?php echo "{$loan_count} Loans" . ($loan_min != $loan_max ? " found in Range of" : ""); ?>
                        <label><?php echo $loan_range ?></label></div>
                    <div class="col-md-4"><span class="si-money"></span>Market Value
                        <label><?php echo ($property_info["market_value"] && "0.00" != $property_info["market_value"]) ? "\${$property_info["market_value"]}" : "-"; ?></label>
                    </div>
                    <div class="col-md-4"><span class="si-cooperation"></span>Property Tax
                        <label><?php echo ($property_info["tax_billed_amt"] && "0.00" != $property_info["tax_billed_amt"]) ? "\${$property_info["tax_billed_amt"]}" : "-"; ?></label>
                    </div>
                    <div class="col-md-4"><span class="si-location"></span>Located in
                        <label><?php echo $property_info["county"]; ?> County</label></div>
                </div>
            </div>
            <div class="report-box rb-summary">
                <h3>Search Summary</h3>
                <div class="row summary-lists ras">
                    <div class="col-md-6"><span class="si-user"></span>Possible Owners
                        <label>(<?php echo $possible_owners ?: "Not Found" ?>)</label></div>
                    <div class="col-md-6"><span class="si-phone"></span>County Assessor Records
                        <label>(<?php echo $assessor_records; ?>)</label></div>
                    <div class="col-md-6"><span class="si-location"></span>Deeds
                        <label>(<?php echo count($deed_info) ?: "-"; ?>)</label></div>
                </div>
            </div>
            <div class="report-box rb-summary rb-worth-noting">
                <h2><span class="si-tip"></span>Worth Noting</h2>
                <div class="row summary-lists ras">
                    <div class="col-md-3"><span
                                class="si-<?php echo in_array($tax_info["TaxExemptionWidow"], $true_values) ? "done" : "close" ?>-circle"></span>Widow:
                        <label><?php echo in_array($tax_info["TaxExemptionWidow"], $true_values) ? "Yes" : "No" ?></label>
                    </div>
                    <div class="col-md-3"><span
                                class="si-<?php echo in_array($tax_info["TaxExemptionVeteran"], $true_values) ? "done" : "close" ?>-circle"></span>Veteran:
                        <label><?php echo in_array($tax_info["TaxExemptionVeteran"], $true_values) ? "Yes" : "No" ?></label>
                    </div>
                    <div class="col-md-3"><span
                                class="si-<?php echo in_array($tax_info["TaxExemptionDisabled"], $true_values) ? "done" : "close" ?>-circle"></span>Handicap:
                        <label><?php echo in_array($tax_info["TaxExemptionDisabled"], $true_values) ? "Yes" : "No" ?></label>
                    </div>
                    <div class="col-md-3"><span
                                class="si-<?php echo in_array($tax_info["TaxExemptionSenior"], $true_values) ? "done" : "close" ?>-circle"></span>Senior:
                        <label><?php echo in_array($tax_info["TaxExemptionSenior"], $true_values) ? "Yes" : "No" ?></label>
                    </div>
                </div>
            </div>
            <?php
            if ($search_data["ras_records"]["property_info"]["opt_out"] != 1 && $possible_owners > 0) { ?>
                <div class="report-box" data-type="<?php echo SEARCH_TYPE_NAME; ?>">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2><span class="si-user"></span>Possible
                                Owners<label><?php echo $possible_owners; ?></label></h2>
                        </div>
                    </div>
                    <?php
                    $owner_fields = ["primary_grantee_1", "primary_grantee_2", "secondary_grantee_1", "secondary_grantee_2"];

                    foreach ($owner_fields as $field) {
                        if (empty($deed_info[0][$field])) {
                            continue;
                        }
                        $show_text = false;
                        if (!preg_match(RAS_OWNERS, $deed_info[0][$field]) == 1) {
                            $show_text = true;
                        }
                    } ?>
                    <p class="box-title">According to the most updated deed, these are the possible owners of this
                        property. <?php if ($show_text) {
                            ?>Click on 'Run Search' to find out more information about any possible owner.<?php
                                  } ?></p>
                    <div class="row img-box-row">
                        <?php
                        $owner_fields = ["primary_grantee_1", "primary_grantee_2", "secondary_grantee_1", "secondary_grantee_2"];
                        //opted out owners
                        $owner = array("Tamara G Page");
                        foreach ($owner_fields as $field) {
                            if (empty($deed_info[0][$field])) {
                                continue;
                            }
                            if (in_array($deed_info[0][$field], $owner)) {
                                continue;
                            }

                            $image = rand(1, 108);
                            ?>


                            <div class="col-xs-6 col-md-3 img-box-list">
                                <div class="box-col">
                                    <div class="img-thumbnail">
                                        <img src="<?php echo $common_assets_url . "/images/owners/owner_{$image}.jpg" ?>"
                                             alt="User"
                                             style="background-image: url(<?php echo $common_assets_url . "/images/owners/owner_{$image}.jpg" ?>);"
                                             decoding="async" loading="lazy"/>
                                    </div>
                                    <a class="box-name ras_owner"><?php echo $deed_info[0][$field]; ?></a>
                                    <?php
                                    if (!preg_match(RAS_OWNERS, $deed_info[0][$field]) == 1) { ?>
                                        <a data-query="<?php echo $deed_info[0][$field]; ?>" <?php SCF::js_controller("search.form_run_search") ?>
                                           class="run-search btn">Run Search</a>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
            <div class="report-box country_assessor">
                <div class="row">
                    <div class="col-xs-12">
                        <h2><span class="si-money"></span>County Assessor
                            Records<label><?php echo $assessor_records; ?></label></h2>
                    </div>
                </div>
                <p class="box-title">The sections below contain information from publicly available records maintained
                    by the County Assessor (Recorder or Clerk) on the property and property's owner.</p>
                <div class="box-col">
                    <h4>Property Owner Details</h4>
                    <p>Electronically accessible information on this property's owner is detailed below.</p>
                    <h5><?php echo $property_info["address"]; ?></h5>
                    <div class="country_as_list">
                        <div class="row">
                            <div class="col-md-4"><strong>Owner Occupied</strong></div>
                            <div class="col-md-8">: <?php echo($property_info["owner_address"] ? "No" : "Yes"); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><strong>Ownership Vesting Type</strong></div>
                            <div class="col-md-8">: <?php echo($property_info["vesting_type"] ?: "-"); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><strong>Mailing Address for Taxes</strong></div>
                            <div class="col-md-8">
                                : <?php echo($property_info["owner_address"] ?: $property_info["address"]); ?></div>
                        </div>
                    </div>
                </div>
                <div class="box-col">
                    <h4>Property Value & Taxes</h4>
                    <p>Valuation and tax details that have been made electronically accessible are detailed below.</p>
                    <div class="row">
                        <div class="col-md-4">
                            <h5>Market Value</h5>
                            <div class="country_as_list">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Total Value</strong></div>
                                    <div class="col-xs-6">
                                        : <?php echo ($property_info["market_value"] && "0.00" != $property_info["market_value"]) ? "\${$property_info["market_value"]}" : "N/A"; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5>Assessed Value As of <?php echo $property_info["tax_year"]; ?></h5>
                            <div class="country_as_list">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Total Value</strong></div>
                                    <div class="col-xs-6">
                                        : <?php echo ($property_info["assessed_value"] && "0.00" != $property_info["assessed_value"]) ? "\${$property_info["assessed_value"]}" : "N/A"; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5>Property Taxes for <?php echo $property_info["tax_year"]; ?></h5>
                            <div class="country_as_list">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Tax Amount</strong></div>
                                    <div class="col-xs-6">
                                        : <?php echo ($property_info["tax_billed_amt"] && "0.00" != $property_info["tax_billed_amt"]) ? "\${$property_info["tax_billed_amt"]}" : "N/A"; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-col">
                    <h4>Location of Property</h4>
                    <p>Electronically accessible location identifiers for this property are listed below.</p>
                    <h5><?php $property_info["address"]; ?></h5>
                    <div class="country_as_list">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>State</strong></div>
                                    <div class="col-xs-6">: <?php echo $property_info["state"] ?: "-"; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>County</strong></div>
                                    <div class="col-xs-6">: <?php echo $property_info["county"] ?: "-"; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Municipality</strong></div>
                                    <div class="col-xs-6">
                                        : <?php echo $property_info["PropertyUseMuni"] ?: "-"; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>City</strong></div>
                                    <div class="col-xs-6">: <?php echo $property_info["city"] ?: "-"; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>FIPS County Code</strong></div>
                                    <div class="col-xs-6">: <?php echo $property_info["fips_code"] ?: "-"; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>APN</strong></div>
                                    <div class="col-xs-6">: <?php echo $property_info["apn"] ?: "-"; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Zip</strong></div>
                                    <div class="col-xs-6">: <?php echo $property_info["zip"] ?: "-"; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Subdivision</strong></div>
                                    <div class="col-xs-6">: <?php echo $legal_info["Subdivision"] ?: "N/A" ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Tract Number</strong></div>
                                    <div class="col-xs-6">: <?php echo $legal_info["TractNumber"] ?: "N/A" ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Block</strong></div>
                                    <div class="col-xs-6">: <?php echo $legal_info["Block"] ?: "N/A" ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Section</strong></div>
                                    <div class="col-xs-6">: <?php echo $legal_info["Section"] ?: "N/A" ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Unit</strong></div>
                                    <div class="col-xs-6">: <?php echo $legal_info["Unit"] ?: "N/A" ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Lot</strong></div>
                                    <div class="col-xs-6">: <?php echo $legal_info["Lot"] ?: "N/A" ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6"><strong>Map</strong></div>
                                    <div class="col-xs-6"><span class="clcik_here"
                                                                data-target="https://www.google.com/maps/@<?php echo $property_info["lat"]; ?>,<?php echo $property_info["lng"]; ?>,15z" <?php SCF::js_controller("lnt") ?>>:  Click here</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-col-join">
                    <div class="box-col box-col-lbuilding">
                        <h4>Lot & Building Details</h4>
                        <p>Electronically accessible details on the lot and building(s) that comprise this property are
                            shown below.</p>
                        <h5>Lot Details</h5>
                        <div class="country_as_list">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-xs-6">Standardized Land Use Code</div>
                                        <div class="col-xs-6">
                                            : <?php echo $property_use_info["PropertyUseStandardized"] ?: "N/A"; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">County Land Use Code</div>
                                        <div class="col-xs-6">
                                            : <?php echo $property_use_info["PropertyUseMuni"] ?: "N/A"; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">Zoning</div>
                                        <div class="col-xs-6">
                                            : <?php echo $property_use_info["ZonedCodeLocal"] ?: "N/A"; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">Buildings</div>
                                        <div class="col-xs-6">
                                            : <?php echo $ext_building_info["BuildingsCount"] ?: "N/A"; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">Lot Size</div>
                                        <div class="col-xs-6">
                                            : <?php echo ($property_info["area_lot_sf"] && "0.00" != $property_info["area_lot_sf"]) ? "{$property_info["area_lot_sf"]} Sq Ft" : "N/A"; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-col">
                        <h5>Building Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Overall Attributes</h6>
                                <div class="country_as_list">
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Total Square Footage</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo ($property_info["area_building"] && "0.00" != $property_info["area_building"]) ? "{$property_info["area_building"]} Sq Ft" : "N/A"; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Number of Stories</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo $int_room_info["StoriesCount"] ?: "1"; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Number of Units</strong></div>
                                        <div class="col-xs-6">: <?php echo $int_room_info["UnitsCount"] ?: "1"; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Year Built</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo $property_info["year_built"] ?: "N/A"; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Exterior Dimensions</h6>
                                <div class="country_as_list">
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Total Finished Area</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo ($property_info["area_building"] && "0.00" != $property_info["area_building"]) ? "{$property_info["area_building"]} Sq Ft" : "N/A"; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Garage</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo !empty($parking_info_data) ? "Yes" : "N/A" ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Pool</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo !empty($pool_info_data) ? "Yes" : "N/A" ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Interior Dimensions</h6>
                                <div class="country_as_list">
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Total Room Count</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo $property_info["room_count"] ?: "N/A" ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Bedrooms</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo $property_info["bedroom_count"] ?: "N/A" ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Bathrooms</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo $property_info["bath_count"] ?: "N/A" ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6"><strong>Year Built</strong></div>
                                        <div class="col-xs-6">
                                            : <?php echo $property_info["year_built"] ?: "N/A"; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if (count($deed_info)) {
                if ($search_data["ras_records"]["property_info"]["opt_out"] != 1) { ?>
                    <div class="report-box deeds_box">
                        <div class="row">
                            <div class="col-xs-12">
                                <h2>
                                    <span class="si-property"></span>Deeds<label><?php echo count($deed_info); ?></label>
                                </h2>
                            </div>
                        </div>
                        <p class="box-title">Any transaction such as ownership changes and property loans-that have been
                            made digitally accessible by the county this property is located in are detailed below.</p>
                        <h3><span><?php echo count($deed_info); ?> deeds</span> were found for this property.</h3>
                        <?php
                        foreach ($deed_info as $deed) {
                            $mortage = unserialize($deed["mortgage_1"]);
                            $mortage_seconday = unserialize($deed["mortgage_2"]);
                            $doc_info = unserialize($deed["doc_info"]);
                            $tax_info = unserialize($deed["tax_info"]);

                            $date = (!empty($deed["mortgage_date"]) && $deed["mortgage_date"] != "0000-00-00") ? $deed["mortgage_date"] : $deed["recording_date"];
                            $timestamp = strtotime($date);
                            ?>
                            <div class="deed_section">
                                <div class="deed_list">
                                    <div class="deed_date"><?php echo date("M", $timestamp) . "<br />" . date("Y", $timestamp) ?></div>
                                    <div class="deed_body">
                                        <h4><?php echo ($deed["primary_grantor_1"] <> "") ? "Ownership Change" : "New Loan Recorded"; ?></h4>
                                        <p>
                                            From: <?php echo $deed["primary_grantor_1"] ?: ($mortage["LenderFullName"] ?: "N/A"); ?></p>
                                        <p>To: <?php echo $deed["primary_grantee_1"]; ?></p>
                                    </div>
                                    <div class="deed_action si-plus" <?php SCF::js_controller("results.ras.deeds") ?>></div>
                                </div>
                                <div class="box-col-join">
                                    <div class="box-col primary_lender">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5>Primary Lender Details</h5>
                                                <div class="country_as_list">
                                                    <div class="row">
                                                        <div class="col-xs-6">Lender</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo $mortage["LenderFullName"] ?: "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Loan Amount</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo $mortage["Amount"] ? "\${$mortage["Amount"]}" : "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Loan Type</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo $mortage["Type"] ?: "N/A"; ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Secondary Lender Details</h5>
                                                <div class="country_as_list">
                                                    <div class="row">
                                                        <div class="col-xs-6">Lender</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo $mortage_seconday["LenderFullName"] ?: "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Loan Amount</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo $mortage_seconday["Amount"] ? "\${$mortage_seconday["Amount"]}" : "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Loan Type</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo $mortage_seconday["Type"] ?: "N/A"; ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-col">
                                        <h5>County Records</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="country_as_list">
                                                    <div class="row">
                                                        <div class="col-xs-6">State</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo $property_info["state"]; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">County</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo $property_info["county"]; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Transfer Date</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo date(DATE_FORMAT, $timestamp); ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Transfer Amount</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo ($deed["transfer_amount"] && "0.00" != $deed["transfer_amount"]) ? "\${$deed["transfer_amount"]}" : "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Transfer Tax</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo ($tax_info["TransferTaxTotal"] && "0.00" != $tax_info["TransferTaxTotal"]) ? "\${$deed["TransferTaxTotal"]}" : "N/A"; ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="country_as_list">
                                                    <div class="row">
                                                        <div class="col-xs-6">Document Number</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo !empty($doc_info["NumberFormatted"]) ? $doc_info["NumberFormatted"] : "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Document Type</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo !empty($doc_info["TypeCode"]) ? $doc_info["TypeCode"] : "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Book Number</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo !empty($doc_info["Book"]) ? $doc_info["Book"] : "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Page Number</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo !empty($doc_info["Page"]) ? $doc_info["Page"] : "N/A"; ?></div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xs-6">Recording Date</div>
                                                        <div class="col-xs-6">
                                                            : <?php echo date(DATE_FORMAT, $timestamp); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
            }
            ?>
            <div class="report-box scf-neighborhood">
                <div class="row">
                    <div class="col-xs-12">
                        <h2><span class="si-warning"></span> Neighborhood Safety Report
                            <label><?php echo count($radius_info); ?></label></h2>
                    </div>
                </div>
                <p id="latitude" style="display:none"><?php echo $property_info["lat"]; ?></p>
                <p id="longitude" style="display:none"><?php echo $property_info["lng"]; ?></p>
                <p id="zip" style="display:none"><?php echo $property_info["zip"]; ?></p>
                <p class="box-title">There are <?php echo count($radius_info); ?> Sex Offenders who live near this
                    address.</p>

                <div id="radius-map"<?php SCF::js_controller("results.ras.map") ?>></div>

                <?php
                $record_id = array();
                foreach ($radius_info as $data) {
                    if (in_array($data["record_id"], $record_id)) {
                        continue;
                    }
                    $record_id[] = $data["record_id"];

                    $types = ["SEX" => "Sex Offenders Registry", "DOC" => "Department of Corrections", "Arrest-Log" => "Arrest Logs"];
                    $source = "Court Records";
                    foreach ($types as $type => $caption) {
                        if (strpos($data["record_id"], $type) !== false) {
                            $source = $caption;
                            break;
                        }
                    }
                    ?>
                    <div class="box-col neighborhood-list">
                        <div class="row">
                            <div class="col-md-1 col-xs-3">
                                <span class="si-user"></span>
                            </div>
                            <div class="col-md-3 col-xs-9">
                                <h3><?php echo $data["full_name"]; ?></h3>
                                <span class="title_lbl"><?php echo round(melissadata\MelissaDataRAS::twopoints_on_earth($property_info["lat"], $property_info["lng"], $data["latitude"], $data["longitude"]), 2); ?>
                                    miles away</span>
                            </div>
                            <div class="col-md-4 col-xs-12">
                                <h5>Charges Filed</h5>
                                <span class="hd_text"><?php echo $data["record_id"] ?: "Undisclosed to Public"; ?></span>
                            </div>
                            <div class="col-md-4">
                                <h5>Source</h5>
                                <span class="hd_text"><?php echo $data["record_id"] ? "{$source}, {$data["state_name"]}" : "Undisclosed to Public"; ?></span>
                            </div>
                        </div>
                        <div class="box-col neighborhood-list-inner">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="charge">Case ID</div>
                                    <div class="charge_val"><?php echo $data["record_id"] ?: "Undisclosed to Public"; ?></div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span class="btn btn-dark-green no-bckgrd-col" <?php SCF::js_controller("results.ras.view") ?>><span>View Details</span><i
                                                class="si-down-circle"></i></span>
                                </div>
                            </div>
                            <div class="neighborhood_content">
                                <table class="scf-table">
                                    <tr>
                                        <td>Source</td>
                                        <td><?php echo $data["record_id"] ? $source : "Undisclosed to Public"; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Source State</td>
                                        <td><?php echo $data["source"]; ?></td>
                                    </tr>
                                </table>
                                <h4 class="nbr_tbl_title">Personal Details</h4>
                                <table class="scf-table">
                                    <tr>
                                        <td>Full Name</td>
                                        <td><?php echo $data["full_name"]; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Age</td>
                                        <td><?php echo ($data["dob"] && $data["dob"] != "1970-01-01") ? date_diff(date_create($data["dob"]), date_create(date()))->format("%Y") : "--"; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Date of Birth</td>
                                        <td><?php echo ($data["dob"] && $data["dob"] != "1970-01-01") ? $data["dob"] : "Undisclosed to Public"; ?></td>
                                    </tr>
                                    <?php
                                    $field_list = ["height" => "Height", "weight" => "Weight", "hair" => "Hair Color", "eyes" => "Eye Color", "race" => "Race", "address" => "Address", "latitude" => "Location", "personal" => "Identifying Marks", "vehicle" => "Vehicle"];
                                    foreach ($field_list as $_key => $_caption) {
                                        if (empty($data[$_key])) {
                                            continue;
                                        }

                                        switch ($_key) {
                                            case "height":
                                                $value = "{$data[$_key][0]}' {$data[$_key][1]}{$data[$_key][2]}\"";
                                                break;

                                            case "weight":
                                                $value = $data[$_key] . (strpos($data[$_key], 'lbs') === false ? " lbs" : "");
                                                break;

                                            case "latitude":
                                                $value = sprintf('<a target="_blank" href="http://www.google.com/maps/place/%s,%s">%s, %s</a>', $data["latitude"], $data["longitude"], $data["latitude"], $data["longitude"]);
                                                break;

                                            default:
                                                $value = $data[$_key];
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $_caption ?></td>
                                            <td><?php echo $value; ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                                <h4 class="nbr_tbl_title">Charges filed</h4>
                                <table class="scf-table">
                                    <tr>
                                        <td>Offence information</td>
                                        <td><?php echo $data["crime"] ?: "Undisclosed to Public"; ?></td>
                                    </tr>
                                    <?php
                                    $field_list = ["type" => "Type", "sentence" => "Sentence", "last_update" => "Last Update"];
                                    foreach ($field_list as $_key => $_caption) {
                                        if (empty($data[$_key])) {
                                            continue;
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $_caption ?></td>
                                            <td><?php echo $data[$_key]; ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>