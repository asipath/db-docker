<?php
function remove_duplicate_address($array)
    {
    $full_address_string = '';
    foreach ($array as $val) {
        $address_array = explode(',', $val);

        if (count($address_array) <= 2) {
            //$half_address[]=$address_array[0];
            if (isset($address_array[1])) {
                $half_address[trim($address_array[1])] = $val;
                }
            } else {
            $full_address[]      = $val;
            $full_address_string .= $val . '|';
            }
        }

    foreach ($half_address as $k => $v) {
        if (strpos($full_address_string, $k) !== false) {
            unset($half_address[$k]);
            }

        }
    return array_merge($full_address, $half_address);
    }

if (!empty($action) && $action <= 8) {
    $exclude_header_footer_content = true;
    $include_popup[]               = "age_location_filter";
    }