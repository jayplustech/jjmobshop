<?php

class PawaPayHandler {
    private $token;
    private $baseUrl = "https://api.sandbox.pawapay.io/v2";

    public function __construct($config) {
        $this->token = $config['token'] ?? '';
    }

    public function deposit($data) {
        $endpoint = $this->baseUrl . "/deposits";
        
        $payload = [
            "depositId" => $data['depositId'],
            "amount" => (string)$data['amount'],
            "currency" => "TZS",
            "payer" => [
                "type" => "MMO",
                "accountDetails" => [
                    "phoneNumber" => $data['phoneNumber'],
                    "provider" => $data['provider']
                ]
            ]
        ];

        return $this->makeRequest('POST', $endpoint, $payload);
    }

    public function checkStatus($depositId) {
        $endpoint = $this->baseUrl . "/deposits/" . $depositId;
        return $this->makeRequest('GET', $endpoint);
    }

    private function makeRequest($method, $url, $data = null) {
        $ch = curl_init($url);
        
        $headers = [
            "Authorization: Bearer " . $this->token,
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Log Request
        $this->log("REQUEST [$method] $url: " . json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Log Response
        $this->log("RESPONSE [$httpCode]: " . $response);
        if ($error) {
            $this->log("CURL ERROR: " . $error);
        }

        return [
            'status' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }

    private function log($message) {
        $logFile = __DIR__ . '/../logs/pawapay.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}
