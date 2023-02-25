

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
if (!defined("FROM_INDEX")) {
    die();
}
$token            = $_POST['token'];
$recaptcha_result = search::get_recpatcha_response($token);

$baseline_track["save"] = false;
// AB testing Start
if (!$user_id) {

        // CSI-700 Image Search Progress Web Worker
    if (isset($_SESSION["ab_search_progress_WW_RIS"]) && isset($_SESSION["step_zero_WW_RIS"]) && !isset($_SESSION["step_one_WW_RIS"])) {
        $_SESSION["ab_search_progress_WW_RIS"]->track_event("1_searched_WW_RIS", SYSTEM::get_device_type());
        $_SESSION["step_one_WW_RIS"] = true;
    }
    // CSI-5067 Image ads
    if (isset($_SESSION["ab_baselines_old_img"]) && isset($_SESSION["step_zero_ris"]) && !isset($_SESSION["step_one_ris"])) {
        $_SESSION["ab_baselines_old_img"]->track_event("1_made_search", SYSTEM::get_device_type());
        $_SESSION["step_one_ris"] = true;
    }
    // CSI-5068 Image ads
    if (isset($_SESSION["ab_baselines_basic_img"]) && isset($_SESSION["step_zero_ris_basic"]) && !isset($_SESSION["step_one_ris_basic"])) {
        $_SESSION["ab_baselines_basic_img"]->track_event("1_made_search_basic", SYSTEM::get_device_type());
        $_SESSION["step_one_ris_basic"] = true;
    }
    // CSI-5066 home name
    if (isset($_SESSION["ab_baselines_home_name"]) && !isset($_SESSION["home_step_one_image"])) {
        $_SESSION["ab_baselines_home_name"]->track_event("1_2_name_image_other", SYSTEM::get_device_type());
        $_SESSION["home_step_one_image"] = true;
    }
    // CSI-5120 home
    if (isset($_SESSION["ab_baselines_image"]) && isset($_SESSION["step_zero_ris"]) && !isset($_SESSION["step_one_ris"])) {
        $_SESSION["ab_baselines_image"]->track_event("1_image_search", SYSTEM::get_device_type());
        $_SESSION["step_one_ris"] = true;
    }
    // CSI-5069 Image main
    if (isset($_SESSION["ab_baselines_image_main"]) && isset($_SESSION["step_zero_ris_main"]) && !isset($_SESSION["step_one_ris_main"])) {
        $_SESSION["ab_baselines_image_main"]->track_event("1_image_search_main", SYSTEM::get_device_type());
        $_SESSION["step_one_ris_main"] = true;
    }
    // CSI-5083
    if (isset($_SESSION["ab_baselines_image_ad"]) && isset($_SESSION["step_zero_ris_ad"]) && !isset($_SESSION["step_one_ris_ad"])) {
        $_SESSION["ab_baselines_image_ad"]->track_event("1_image_search_ad", SYSTEM::get_device_type());
        $_SESSION["step_one_ris_ad"] = true;
    }
    // CSI-5129
    if (isset($_SESSION["ab_baselines_image_ad_copy"]) && isset($_SESSION["step_zero_ris_ad_copy"]) && !isset($_SESSION["step_one_ris_ad_copy"])) {
        $_SESSION["ab_baselines_image_ad_copy"]->track_event("1_image_search_ad_copy", SYSTEM::get_device_type());
        $_SESSION["step_one_ris_ad_copy"] = true;
    }
}
