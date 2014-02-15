<?php

class ApiController extends BaseController {

    const MP_LINK_SELECTOR = "div.text > .row_item:nth-child(1) > .row_value > a";
    const MP_POSTCODE_URL = "http://findyourmp.parliament.uk/postcodes/";
    const MP_DATA_SELECTOR = ".member-details > p";
    const MP_PHOTO_SELECTOR = ".member-photo > img";
    const MP_EMAILS_SELECTOR = "a[href^=\"mailto:\"]";
    const MP_CONTACT_HEADER_SELECTOR = "h3[id^=\"ctl00_ctl00_FormContent_SiteSpecificPlaceholder_PageContent_ctlContactDetails_rptPhysicalAddresses_\"]";
    const MP_CONTACT_DETAILS = "#member-addresses > p";
    const CACHE_LENGTH = 43200; //1 month
    private $useCallback = true;

    public function __construct() {
        $controller = $this;
        $this->beforeFilter(function ($route, $request) use ($controller) {
            if (!Input::has("callback")) {
                $this->useCallback = false;
            }
            if (Session::token() != Input::get('_token')) {
                return $controller->getError("To prevent abuse, access to this API is limited to Reinst8 projects.", 403);
            }
        });
    }

    public function getToken() {
        return Response::json(Session::token());
    }

    public function getMpEmail($postcode) {
        $finalData = [];

        $postcode = strtoupper(trim(str_replace(" ", "", $postcode)));
        if (!$this->isValidPostcode($postcode)) {
            return $this->getError("Invalid postcode.");
        }
        $finalData['postcode'] = $postcode;

        try {
            if (!Cache::has("lookup-$postcode")) {
                $response = Requests::get(self::MP_POSTCODE_URL . $postcode)->body;
                Cache::put("lookup-$postcode", $response, self::CACHE_LENGTH);
                $finalData['pc-lookup-cached'] = false;
            } else {
                $response = Cache::get("lookup-$postcode");
                $finalData['pc-lookup-cached'] = true;
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->getError("A request-based error occurred.", 500);
        }

        $query = QueryPath::withHTML($response, null, array());
        $mpUrl = $query->find(self::MP_LINK_SELECTOR)->eq(0);
        $mpName = $mpUrl->text();
        $mpUrl = $mpUrl->attr("href");

        if ($mpUrl == null) {
            Log::error("mpUrl was null! Contents: " . $response);
            return $this->getError("A null querypath-based error occurred.", 500);
        }
        $finalData['mpUrl'] = $mpUrl;
        $finalData['mpName'] = $mpName;

        try {

            if (!Cache::has("mp-$mpUrl")) {
                $response = Requests::get($mpUrl)->body;
                Cache::put("mp-$mpUrl", $response, self::CACHE_LENGTH);
                $finalData['mp-lookup-cached'] = false;

            } else {
                $response = Cache::get("mp-$mpUrl");
                $finalData['mp-lookup-cached'] = true;
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->getError("A request-based error occurred.", 500);
        }

        $query = QueryPath::withHTML($response, null, array());
        $mpData = $query->find(self::MP_DATA_SELECTOR);
        $finalData['constituency'] = $mpData->eq(0)->text();
        $finalData['party'] = $mpData->eq(1)->text();
        $finalData['addressAs'] = $mpData->eq(2)->text();
        $mpPhoto = $query->find(self::MP_PHOTO_SELECTOR)->eq(0);

        $finalData['photoUrl'] = $this->getPhotoUrl($mpPhoto->attr("src"));

        $mpEmails = $query->find(self::MP_EMAILS_SELECTOR);
        $mpContactHeaders = $query->find(self::MP_CONTACT_HEADER_SELECTOR);


        $mpContactDetails = $query->find(self::MP_CONTACT_DETAILS);
        $i = 0;
        foreach ($mpContactDetails as $detailBlock) {
            $tel = $this->getTelephone($detailBlock->innerHTML());
            $address = $this->getAddress($detailBlock->innerHTML());
            if ($tel) {
                if ($mpContactHeaders->eq($i) != null) {
                    $finalData['contact'][$mpContactHeaders->eq($i)->text()]['telephone'] = $tel;
                } else {
                    $finalData['contact'][]['telephone'] = $tel;
                }
            }

            if ($address) {
                if ($mpContactHeaders->eq($i) != null) {
                    $finalData['contact'][$mpContactHeaders->eq($i)->text()]['address'] = $address;
                } else {
                    $finalData['contact'][]['address'] = $address;
                }
            }

            $email = $this->getEmail($detailBlock->find(self::MP_EMAILS_SELECTOR)->eq(0)->attr("href"));
            if ($email) {
                if ($mpContactHeaders->eq($i) != null) {
                    $finalData['contact'][$mpContactHeaders->eq($i)->text()]['email'] = $email;
                } else {
                    $finalData['contact'][]['email'] = $email;
                }

            }

            $i++;
        }


        return $this->getResponse($finalData);

    }


    private function getPhotoUrl($url) {
        $url = str_replace("assets3.parliament.uk/ext/mnis-bio-person/", "", $url);
        $url = substr($url, 0, -4);
        return $url;
    }

    private function getEmail($href) {
        $email = str_replace("mailto:", "", $href);
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );
        return ($validator->fails()) ? false : $email;
    }

    private function isValidPostcode($postcode) {
        //Thanks http://stackoverflow.com/a/8908522
        $expression = '/^(((([A-PR-UWYZ][0-9][0-9A-HJKS-UW]?)|([A-PR-UWYZ][A-HK-Y][0-9][0-9ABEHMNPRV-Y]?))\s{0,2}[0-9]([ABD-HJLNP-UW-Z]{2}))|(GIR\s{0,2}0AA))$/i';
        return preg_match($expression, $postcode);
    }

    private function getError($detail, $code = 422, $callback = true) {
        if ($callback && $this->useCallback) {
            return Response::json(["error" => $detail])->setStatusCode($code)->setCallback(Input::get('callback'));
        } else {
            return Response::json(["error" => $detail])->setStatusCode($code);
        }
    }

    private function getResponse($data) {
        return Response::json($data)->setStatusCode(200)->setCallback(Input::get('callback'));
    }

    private function getTelephone($innerHTML) {
        $list = explode("<br/>", html_entity_decode($innerHTML));
        foreach ($list as $item) {
            $item = trim($item);
            if (str_contains($item, "Tel")) {
                return preg_replace("/[^0-9]/", "", $item);
            }
        }
        return false;
    }

    private function getAddress($innerHTML) {
        $list = explode("<br/>", html_entity_decode($innerHTML));
        foreach ($list as $item) {
            $item = trim($item);
            if (str_contains($item, ",")) {
                return $item;
            }
        }
        return false;
    }
}
