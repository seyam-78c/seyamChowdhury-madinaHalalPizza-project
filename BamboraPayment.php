<?php
class BamboraPayment {
    private $merchantId;
    private $apiKey;
    private $apiVersion = 'v1';
    private $baseUrl = 'https://api.na.bambora.com';

    public function __construct($merchantId, $apiKey) {
        $this->merchantId = $merchantId;
        $this->apiKey = $apiKey;
    }

    public function processPayment($paymentData) {
        $endpoint = "/payments";
        $url = $this->baseUrl . '/' . $this->apiVersion . $endpoint;

        $data = [
            'amount' => $paymentData['amount'],
            'payment_method' => 'card',
            'card' => [
                'name' => $paymentData['card_holder'],
                'number' => $paymentData['card_number'],
                'expiry_month' => $paymentData['expiry_month'],
                'expiry_year' => $paymentData['expiry_year'],
                'cvd' => $paymentData['cvv']
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Passcode ' . base64_encode($this->merchantId . ':' . $this->apiKey),
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }
}
?>