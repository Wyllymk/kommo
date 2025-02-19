<?php

namespace WyllyMk\KommoCRM;

if (!defined('ABSPATH')) {
    exit;
}

class Kommo_API {
    private $api_key;
    private $account_domain;
    private $api_endpoint = 'https://{domain}.kommo.com/api/v4/';

    public function __construct() {
        $this->api_key = get_option('kommo_api_key');
        $this->account_domain = get_option('kommo_account_domain');
    }

    /**
     * Make API request to Kommo
     */
    public function make_request($endpoint, $method = 'GET', $data = null) {
        $url = str_replace('{domain}', $this->account_domain, $this->api_endpoint) . $endpoint;

        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'method' => $method,
        ];

        if ($data) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}