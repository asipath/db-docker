<?php

/**
 * IDI API Class
 * Author : Social Catfish, LLC
 * Author Email : welcome@socialcatfish.com
 * Author URL : https://socialcatfish.com
 * Version : 1.0
 * Released Date : 04/04/2022
 */

 namespace DataSource;

class IDI extends \DataSource\Base
{
    public const SDK_VERSION = 1.0;
    public const AUTH_VALIDITY = 1680;
    public const API_ENDPOINT_AUTH = "https://login-api.idicore.com/apiclient";
    public const API_ENDPOINT_AUTH_TEST = "https://idiapitunnel.socialcatfish.com/";
    public const API_ENDPOINT_SEARCH = "https://api.idicore.com/search/";
    public const API_ENDPOINT_SEARCH_TEST = "https://idiapitunnel.socialcatfish.com/";

    private $client;
    private $clientID;
    private $secretKey;
    private $authToken;
    private $authURL;
    private $searchURL;
    private $lastAuthTime;
    private $authCacheFile;

    public $configDPPA = "none";
    public $configGLBA = "otheruse";
    public $configFields = ["name", "dob", "address", "phone", "relationship", "email"];
    public $configFieldsPremium = ["bankruptcy", "property", "professional", "aircraft", "criminal", "lien", "judgment", "isDead"];
    public $useAPI = true;
    public $useTeaserAPI = false;

    /**
     * IDI Class Constructor
     *
     * @param string $clientID
     * @param string $secretKey
     * @param bool $sandboxEnv
     */
    public function __construct($clientID, $secretKey, $sandboxEnv = true)
    {

        $this->clientID = $clientID;
        $this->secretKey = $secretKey;

        $this->client = new \vbrowser();
        $this->client->timeout = 60;

        $this->client->add_custom_headers([
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "User-Agent" => "SCF IDI Client v" . self::SDK_VERSION,
        ]);

        $this->authURL = $sandboxEnv ? self::API_ENDPOINT_AUTH_TEST : self::API_ENDPOINT_AUTH;
        $this->searchURL = $sandboxEnv ? self::API_ENDPOINT_SEARCH_TEST : self::API_ENDPOINT_SEARCH;

        $this->dataStructure = [
        ];

        $this->authCacheFile = CACHE_PATH . "idi_auth";
    }

    /**
     * Generate Authorization Token
     *
     * @return bool
     */
    private function generateAuthToken()
    {

        if (file_exists($this->authCacheFile)) {
            $data = json_decode(file_get_contents($this->authCacheFile), true);
            if (time() < ($data["time"] + self::AUTH_VALIDITY)) {
                $this->authToken = $data["token"];
                $this->lastAuthTime = $data["time"];

                return true;
            } else {
                @unlink($this->authCacheFile);
            }
        }

        $params = [
            "dppa" => $this->configDPPA,
            "glba" => $this->configGLBA,
        ];

        $this->client->next_request_only_headers = ["Authorization" => "Basic " . base64_encode($this->clientID . ":" . $this->secretKey)];

        $stream = $this->client->request($this->authURL, \vbrowser::VBROWSER_METHOD_POST, json_encode($params));
        $this->authToken = ($stream["status_code"] == 200) ? $stream["response"] : "";
        $this->lastAuthTime = time();

        if ($this->authToken) {
            file_put_contents($this->authCacheFile, json_encode(["token" => $this->authToken, "time" => $this->lastAuthTime]));
        }

        return !empty($this->authToken);
    }

    /**
     * Send Search API Request
     *
     * @param array $searchParameters
     * @param bool $returnResultsOnly
     * @return mixed
     */
    public function runSearch($searchParameters, $returnResultsOnly = true, $premiumSearch = false)
    {

        if (!empty($searchParameters["age"])) {
            if (is_numeric($searchParameters["age"])) {
                $searchParameters["ageMin"] = $searchParameters["age"];
            } elseif (preg_match("/^([0-9]+) ?\- ?([0-9]+)/", $searchParameters["age"], $ageRange)) {
                $searchParameters["ageMin"] = $ageRange[1];
                $searchParameters["ageMax"] = $ageRange[2];
            }

            unset($searchParameters["age"]);
        }

        $paramStack = [];
        $searchParameters = array_filter(array_intersect_key($searchParameters, array_fill_keys(["email", "phonenumber", "firstName", "lastName", "middleName", "state", "city", "ageMin", "ageMax", "searchType", "dob", "pidlist", "referenceId"], "")));
        $searchParameters = array_merge($searchParameters, ["fields" => ($premiumSearch ? $this->configFieldsPremium : $this->configFields)]);

        if (!empty($searchParameters["searchType"])) {
            if ($searchParameters["searchType"] == "CriminalSearch") {
                $searchParameters["fields"] = ["criminal"];
            }
        }

        if ($this->useAPI) {
            if (!$this->useTeaserAPI) {
                $searchParameters["searchType"] = "APISearch";
            } else {
                unset($searchParameters["fields"]);
            }

            $nickNameSearch = (!$this->useTeaserAPI);
            $usePIDSearch = false;

            for ($retry = 1; $retry <= 2; $retry++) {
                $currentTime = time();
                $authStatus = $this->generateAuthToken();

                if ($authStatus) {
                    $this->client->next_request_only_headers = [
                        "Authorization" => $this->authToken,
                    ];

                    if ($nickNameSearch) {
                        $searchParameters["nicknamesearch"] = true;
                    } else {
                        unset($searchParameters["nicknamesearch"]);
                    }

                    if (!$usePIDSearch) {
                        $paramStack[] = $searchParameters;
                    } else {
                        $searchParameters = array_shift($paramStack);
                        $pidList = $this->getPIDListForSearch($searchParameters);
                        $searchParametersForPIDSearch = [
                        "pidlist" => array_slice($pidList, 0, 100),
                        "fields" => $searchParameters["fields"],
                        "searchType" => $searchParameters["searchType"],
                        ];
                    }

                    $stream = $this->client->request($this->searchURL . ($this->useTeaserAPI ? "TeaserIndicators" : ""), \vbrowser::VBROWSER_METHOD_POST, json_encode($usePIDSearch ? $searchParametersForPIDSearch : $searchParameters));
                    $response = json_decode($stream["response"], true);

                    $flag = [];
                    if ($usePIDSearch) {
                        $flag[] = "P";
                    }
                    if ($nickNameSearch) {
                        $flag[] = "N";
                    }
                    if (!empty($searchParameters["city"])) {
                        $flag[] = "C";
                    }
                    if (!empty($searchParameters["state"])) {
                        $flag[] = "S";
                    }
                    if (!empty($searchParameters["searchType"]) && $searchParameters["searchType"] == "CriminalSearch") {
                        $flag[] = "CR";
                    }
                    if (!empty($searchParameters["referenceId"]) && $searchParameters["referenceId"] == "teaser/nonbillable") {
                        $flag[] = "NB";
                    }

                    \Search::add_to_data_source_stack(SEARCH_ENGINE_IDI, implode("", $flag), count($response["result"]));

                    switch ($stream["status_code"]) {
                        case 200:
                            if (empty($response["result"])) {
                                if (!empty($searchParameters["city"])) {
                                    $searchParameters["city"] = "";
                                    $retry--;
                                    break;
                                } elseif (!empty($searchParameters["state"])) {
                                    $searchParameters["state"] = "";
                                    $retry--;
                                    break;
                                }
                            }

                            return $returnResultsOnly ? $this->parseResults($response) : $response;

                        case 400:
                            $errorCode = $response["error"]["code"] ?? "";

                            if ("TooManyMatches" == $errorCode) {
                                if ($nickNameSearch) {
                                    $nickNameSearch = false;
                                    break;
                                } else {
                                    $usePIDSearch = true;
                                    $retry--;
                                    break;
                                }
                            } else {
                                break 2;
                            }

                        case 401:
                            @unlink($this->authCacheFile);
                            $this->authToken = "";
                            $this->lastAuthTime = null;
                            break;
                    }
                }
            }
        } else {
            for ($try = 1; $try <= 5; $try++) {
                $response = $this->localSearch($searchParameters);

                $flag = [];
                if (!empty($searchParameters["city"])) {
                    $flag[] = "C";
                }
                if (!empty($searchParameters["state"])) {
                    $flag[] = "S";
                }
                if (!empty($searchParameters["ageMin"]) || !empty($searchParameters["ageMax"])) {
                    $flag[] = "A";
                }

                \Search::add_to_data_source_stack(SEARCH_ENGINE_IDI_LOCAL, implode("", $flag), count($response["result"]));

                if (empty($response["result"])) {
                    if (isset($searchParameters["ageMin"]) || isset($searchParameters["ageMax"])) {
                        unset($searchParameters["ageMin"], $searchParameters["ageMax"]);
                    } elseif (isset($searchParameters["city"])) {
                        unset($searchParameters["city"]);
                    } elseif (isset($searchParameters["state"])) {
                        unset($searchParameters["state"]);
                    } else {
                        break;
                    }
                } else {
                    $response = $this->parseLocalResults($response);
                    return $returnResultsOnly ? $this->parseResults($response) : $response;
                }
            }
        }

        return false;
    }

    public function parseLocalResults($response)
    {

        $reports = [];
        foreach ($response["result"] as $index => $report) {
            $reports[] = [
                "name" => array_map(function ($data) {

                    $data = explode("|", $data);
                    return [
                        "pidlist" => [],
                        "data" => ucwords(strtolower(implode(" ", $data))),
                        "first" => ucwords(strtolower(array_shift($data))),
                        "last" => ucwords(strtolower(array_shift($data))),
                    ];
                }, explode("\x01", $report["names"])),
                "dob" => array_map(function ($data) {

                    return [
                        "age" => $data,
                    ];
                }, explode("\x01", $report["ages"])),
                "address" => array_map(function ($data) {

                    $data = explode("|", $data);
                    return [
                        "data" => ucwords(strtolower($data[0])) . ", " . $data[1],
                        "city" => ucwords(strtolower(array_shift($data))),
                        "state" => array_shift($data),
                    ];
                }, explode("\x01", $report["locations"])),
                "phone" => empty($report["phone"]) ? [] : [["number" => strtolower($report["phone"])]],
                "relationship" => array_map(function ($data) {

                    $data = explode("|", $data);
                    return [
                        "name" => [
                            "pidlist" => [],
                            "data" => ucwords(strtolower(implode(" ", $data))),
                            "first" => ucwords(strtolower(array_shift($data))),
                            "last" => ucwords(strtolower(array_shift($data))),
                        ],
                    ];
                }, explode("\x01", $report["relationships"])),
                "email" => empty($report["email"]) ? [] : [["data" => strtolower($report["email"])]],
                "pid" => $report["pid"],
            ];
        }

        return ["result" => $reports];
    }

    /**
     * Parse IDI API Response and return results
     *
     * @param array $response
     * @return array
     */
    public function parseResults($response)
    {

        // Todo: Come up with a logic to identify the gender
        // Todo 10 -o Savindra -c Testing: Check whether the Job data are getting populated Properly
        // Todo 10 -o Savindra -c Testing: Add more information about the Jobs

        $results = [];
        $record_date_struct = [
            "name" => "names",
            "phone" => "phones",
            "address" => "locations",
            "email" => "emails",
            "relationship" => "relationships",
            "employment" => "jobs",
            "criminal" => "criminal",
            "property" => "property",
            "professional" => "professional",
            "bankruptcy" => "bankruptcy",
            "ip" => "ip",
            "judgment" => "judgment",
            "lien" => "lien"
        ];

        if (!empty($response["result"])) {
            foreach ($response["result"] as $result) {
                $record_dates = [];
                foreach ($result as $key => &$dataPoint) {
                    if (is_array($dataPoint)) {
                        if (isset($record_date_struct[$key])) {
                            foreach ($dataPoint as $dataPointKey => $dataPointValue) {
                                $firstSeen = ("relationship" == $key) ? ($dataPointValue["name"]["meta"]["firstSeen"] ?? "") : ($dataPointValue["meta"]["firstSeen"] ?? "");
                                $lastSeen = ("relationship" == $key) ? ($dataPointValue["name"]["meta"]["lastSeen"] ?? "") : ($dataPointValue["meta"]["lastSeen"] ?? "");

                                $record_dates[$record_date_struct[$key]][$dataPointKey] = [
                                    "first_seen" => preg_replace("/^([0-9]{4})([0-9]{2})([0-9]{2})\$/m", "\\1-\\2-\\3", $firstSeen),
                                    "last_seen" => preg_replace("/^([0-9]{4})([0-9]{2})([0-9]{2})\$/m", "\\1-\\2-\\3", $lastSeen),
                                    "category" => $dataPointValue["meta"]["category"] ?? "",
                                    "type" => $dataPointValue["type"] ?? "",
                                    "subtype" => $dataPointValue["subType"] ?? "",
                                    "age" => $dataPointValue["age"] ?? "",
                                ];

                                if (isset($dataPointValue["dob"]["age"])) {
                                    $record_dates[$record_date_struct[$key]][$dataPointKey]['age'] = $dataPointValue["dob"]["age"];
                                }
                            }
                        }
                    }
                }
                unset($dataPoint);

                $names = $result["name"];
                $name = array_shift($names);
                array_shift($record_dates["names"]);

                $relations = array_column($result["relationship"], "name");
                foreach ($relations as &$relation_data) {
                    $relation_data = "{$relation_data["first"]} {$relation_data["last"]}";
                }
                unset($relation_data);

                $age = array_column($result["dob"], "age");

                $result["address"] = array_map(function ($data) {

                    if (!isset($data["data"])) {
                        $data["data"] = implode(", ", array_filter([$data["city"], $data["state"]]));
                    }

                    return $data;
                }, $result["address"]);

                $person = [
                    "isDead" => $result["isDead"],
                    "search_pointer" => "idi_{$result["pid"]}",
                    "name" => $name["data"],
                    "age" => array_shift($age),
                    "gender" => "",
                    "names" => array_column($names, "data"),
                    "social" => [],
                    "locations" => array_column($result["address"], "data"),
                    "phones" => array_column($result["phone"], "number"),
                    "images" => [],
                    "emails" => array_map("strtolower", array_column($result["email"], "data")),
                    "usernames" => [],
                    "jobs" => array_column($result["employment"], "employerName"),
                    "jobs_in_detail" => [],
                    "language" => [],
                    "education" => [],
                    "education_in_detail" => [],
                    "urls" => [],
                    "url_previews" => [],
                    "relationships" => $relations,
                    "record_dates" => $record_dates,
                    "criminal" => $result["criminal"],
                    "property" => $result["property"],
                    "bankruptcy" => $result["bankruptcy"],
                    "professional" => $result["professional"],
                    "ip" => $result["ip"],
                    "lien" => $result["lien"],
                    "judgment" => $result["judgment"]
                ];

                $person = $this->array_map_recursive([$this, "caseCorrection"], $person);

                // Case Correction for Address
                $person["locations"] = array_map(function ($data) {

                    return preg_replace_callback("/(, [a-z]{2})( [0-9]+)/i", function ($state) {

                        return strtoupper($state[1]) . $state[2];
                    }, $data);
                }, $person["locations"]);

                if (!$this->useAPI || ($this->useAPI && $this->useTeaserAPI)) {
                    $person["teaser_request"] = true;
                    $person["teaser_api_request"] = ($this->useAPI && $this->useTeaserAPI);
                }

                // Premium Data
                $premiumData = [];
                $premiumDataKeys = [
                    // All these will be added automatically from search module
                    //"hasEmail" => "email",
                    //"hasAddress" => "locations",
                    //"hasRelatives" => "relationships",
                    //"hasMobilePhone" => "phones",
                    //"hasPhone" => "phones",
                    "hasProfessionalLicense" => "professional",
                    "hasJudgment" => "judgment",
                    "hasLien" => "lien",
                    "hasBankruptcy" => "bankruptcy",
                    "isCriminal" => "criminal",
                ];

                foreach ($premiumDataKeys as $premiumKey => $mappedKey) {
                    if (!empty($result[$premiumKey])) {
                        $premiumData[$mappedKey] = true;
                    }
                }

                $person["premium_data"] = $premiumData;

                $results[] = $person;
            }
        }
        return ["meta" => [], "data" => $results];
    }

    /**
     * Sort Data by Rank
     *
     * @param array $data
     * @return array
     */
    private function sortDataPoint($data)
    {

        usort($data, function ($element_1, $element_2) {

            $field = isset($element_1["meta"]["rank"]) ? "rank" : "count";

            return ($element_1["meta"][$field] < $element_2["meta"][$field]) ? 1 : (($element_1["meta"][$field] == $element_2["meta"][$field]) ? 0 : -1);
        });

        return $data;
    }

    public function mapParams($params)
    {

        if (!empty($params["full_name"])) {
            $name_parts = explode(" ", trim($params["full_name"]));

            $params["first_name"] = array_shift($name_parts);
            $params["last_name"] = array_pop($name_parts);

            if (count($name_parts)) {
                $params["middle_name"] = array_shift($name_parts);
            }
        }

        $return = [
            "age" => $params["age"] ?? "",
            "email" => $params["email"] ?? "",
            "phonenumber" => $params["phone"] ?? "",
            "firstName" => $params["first_name"] ?? "",
            "middleName" => $params["middle_name"] ?? "",
            "lastName" => $params["last_name"] ?? "",
            "city" => $params["city"] ?? "",
            "state" => $params["state"] ?? "",
            "country" => $params["country"] ?? "",
            "searchType" => $params["search_type"] ?? "",
            "dob" => $params["dob"] ?? "",
            "pidlist" => $params["pidlist"] ?? "",
        ];

        if (!empty($params["use_ccpa_key"])) {
            $return["referenceId"] = "opt-out";
        }
        if (!empty($params["reference"])) {
            $return["referenceId"] = $params["reference"];
        }
        return $return;
    }

    private function getDBI()
    {

        if (DEBUG) {
            $dbi = new \DBI();
            $dbi->connect(IDI_TEASER_DB_HOST, IDI_TEASER_DB_USER, IDI_TEASER_DB_PASSWORD, IDI_TEASER_DB_NAME, false . false);
        } else {
            $dbi = $GLOBALS["dbi"];
        }

        return $dbi;
    }

    private function getPIDListForSearch($params)
    {

        $dbi = $this->getDBI();

        extract($params);

        $filters = [];
        if (!empty($state)) {
            $filters[] = sprintf("state = '%s'", $dbi->escape($state));
        }
        if (!empty($city)) {
            $filters[] = sprintf("city = '%s'", $dbi->escape($city));
        }
        $filters = (!empty($filters) ? " AND " : "") . implode(" AND ", $filters);

        $sql = sprintf("SELECT pid FROM `%s` WHERE fn = '%s' AND ln = '%s' %s GROUP BY pid;", DB_TBL_IDI_TEASER_DATA, $dbi->escape($firstName), $dbi->escape($lastName), $filters);
        $results = $dbi->query_to_multi_array($sql);

        return array_column($results, "pid");
    }

    private function localSearch($searchParams, $limit = 100)
    {

        $dbi = $this->getDBI();

        if (empty($searchParams["firstName"]) || empty($searchParams["lastName"])) {
            return [];
        }

        $filters = [];

        if ($searchParams["firstName"]) {
            $filters[] = sprintf("i.fn = '%s'", $dbi->escape($searchParams["firstName"]));
        }
        if ($searchParams["lastName"]) {
            $filters[] = sprintf("i.ln = '%s'", $dbi->escape($searchParams["lastName"]));
        }
        if ($searchParams["state"]) {
            $filters[] = sprintf("i.state = '%s'", $dbi->escape($searchParams["state"]));
        }
        if ($searchParams["city"]) {
            $filters[] = sprintf("i.city = '%s'", $dbi->escape($searchParams["city"]));
        }
        if ($searchParams["ageMin"]) {
            $filters[] = sprintf("i.age >= %d", $searchParams["ageMin"]);
        }
        if ($searchParams["ageMax"]) {
            $filters[] = sprintf("i.age <= %d", $searchParams["ageMax"]);
        }

        $sql = sprintf("SELECT f.*, ep.phone, ep.email FROM (SELECT p.pid, GROUP_CONCAT(DISTINCT i.age SEPARATOR 0x01) ages, GROUP_CONCAT(DISTINCT CONCAT_WS('|', i.fn, i.ln) SEPARATOR 0x01) names, GROUP_CONCAT(DISTINCT CONCAT_WS('|', i.city, i.state) SEPARATOR 0x01) locations, GROUP_CONCAT(DISTINCT CONCAT_WS('|', i.rel_1_fn, i.rel_1_ln), 0x01, CONCAT_WS('|', i.rel_2_fn, i.rel_2_ln), 0x01, CONCAT_WS('|', i.rel_3_fn, i.rel_3_ln), 0x01, CONCAT_WS('|', i.rel_4_fn, i.rel_4_ln), 0x01, CONCAT_WS('|', i.rel_5_fn, i.rel_5_ln)) relationships FROM (SELECT i.pid FROM `%s` i WHERE %s GROUP BY i.pid LIMIT %d) p INNER JOIN `%s` i ON p.pid = i.pid GROUP BY p.pid) f LEFT OUTER JOIN `%s` ep ON f.pid = ep.pid GROUP BY f.pid", DB_TBL_IDI_TEASER_DATA, implode(" AND ", $filters), $limit, DB_TBL_IDI_TEASER_DATA, DB_TBL_IDI_TEASER_DATA_EMAIL_PHONE_NUMBER);
        $results = $dbi->query_to_multi_array($sql);

        return ["result" => $results];
    }
}
