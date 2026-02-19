<?php

namespace BDPay\LaravelBDPay\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use BDPay\LaravelBDPay\Exceptions\BDPayException;
use BDPay\LaravelBDPay\Exceptions\InvalidConfigurationException;

class BDPayClient
{
    protected Client $httpClient;
    protected array $config;
    protected string $environment;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->environment = $config['environment'];
        
        $this->validateConfiguration();
        $this->initializeHttpClient();
    }

    /**
     * Validate the BDPay configuration.
     */
    protected function validateConfiguration(): void
    {
        $apiConfig = $this->config['api'][$this->environment];
        
        if (empty($apiConfig['merchant_code']) || 
            empty($apiConfig['public_key']) || 
            empty($apiConfig['secret_key'])) {
            throw new InvalidConfigurationException(
                "BDPay configuration is incomplete for {$this->environment} environment. " .
                "Please check your .env file for BDPAY_{$this->environment}_MERCHANT_CODE, " .
                "BDPAY_{$this->environment}_PUBLIC_KEY, and BDPAY_{$this->environment}_SECRET_KEY."
            );
        }
    }

    /**
     * Initialize the HTTP client.
     */
    protected function initializeHttpClient(): void
    {
        $apiConfig = $this->config['api'][$this->environment];
        
        $this->httpClient = new Client([
            'base_uri' => $apiConfig['base_url'],
            'timeout' => $this->config['defaults']['timeout'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Merchant-Code' => $apiConfig['merchant_code'],
                'X-Public-Key' => $apiConfig['public_key'],
            ],
        ]);
    }

    /**
     * Make a request to BDPay API.
     */
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->getFullUrl($endpoint);
        
        try {
            // Add signature to data for POST requests
            if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH']) && !empty($data)) {
                $data['sign'] = $this->generateSignature($data);
            }
            
            $options = $this->prepareRequestOptions($method, $data);
            
            if ($this->config['logging']['enabled']) {
                $this->logRequest($method, $url, $data);
            }
            
            $response = $this->httpClient->request($method, $endpoint, $options);
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            if ($this->config['logging']['enabled']) {
                $this->logResponse($response->getStatusCode(), $responseData);
            }
            
            return $responseData;
            
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * Get the full URL for the endpoint.
     */
    protected function getFullUrl(string $endpoint): string
    {
        $apiConfig = $this->config['api'][$this->environment];
        return rtrim($apiConfig['base_url'], '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Prepare request options based on method and data.
     */
    protected function prepareRequestOptions(string $method, array $data): array
    {
        $options = [];
        
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $options['json'] = $data;
        } elseif (!empty($data)) {
            $options['query'] = $data;
        }
        
        return $options;
    }

    /**
     * Log the request details.
     */
    protected function logRequest(string $method, string $url, array $data): void
    {
        if ($this->config['logging']['enabled'] ?? false) {
            try {
                Log::channel($this->config['logging']['channel'])
                    ->{$this->config['logging']['level']}('BDPay API Request', [
                        'method' => $method,
                        'url' => $url,
                        'data' => $data,
                        'environment' => $this->environment,
                    ]);
            } catch (\Exception $e) {
                // Fallback to simple file logging if Laravel Log is not available
                $logFile = sys_get_temp_dir() . '/bdpay-api.log';
                $logData = [
                    'method' => $method,
                    'url' => $url,
                    'data' => $data,
                    'environment' => $this->environment,
                    'timestamp' => date('Y-m-d H:i:s'),
                ];
                file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
            }
        }
    }

    /**
     * Log the response details.
     */
    protected function logResponse(int $statusCode, array $responseData): void
    {
        if ($this->config['logging']['enabled'] ?? false) {
            try {
                Log::channel($this->config['logging']['channel'])
                    ->{$this->config['logging']['level']}('BDPay API Response', [
                        'status_code' => $statusCode,
                        'response' => $responseData,
                        'environment' => $this->environment,
                    ]);
            } catch (\Exception $e) {
                // Fallback to simple file logging if Laravel Log is not available
                $logFile = sys_get_temp_dir() . '/bdpay-api.log';
                $logData = [
                    'status_code' => $statusCode,
                    'response' => $responseData,
                    'environment' => $this->environment,
                    'timestamp' => date('Y-m-d H:i:s'),
                ];
                file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
            }
        }
    }

    /**
     * Handle request exceptions.
     */
    protected function handleRequestException(RequestException $e): void
    {
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 0;
        $responseBody = $response ? $response->getBody()->getContents() : '';
        
        $errorData = json_decode($responseBody, true);
        
        throw new BDPayException(
            $errorData['message'] ?? $e->getMessage(),
            $errorData['error_code'] ?? null,
            $errorData,
            $statusCode,
            $e
        );
    }

    /**
     * Generate signature for BDPay API requests.
     */
    public function generateSignature(array $data): string
    {
        $apiConfig = $this->config['api'][$this->environment];
        
        // Remove sign parameter if exists
        unset($data['sign']);

        // Filter out null values
        $data = array_filter($data, fn ($val) => !is_null($val));

        // Sort parameters by key
        ksort($data);

        // Create parameter string by concatenating values (not key=value format)
        $params_str = '';
        foreach ($data as $key => $val) {
            $params_str = $params_str . $val;
        }
        
        // Generate signature using RSA private key encryption
        return $this->privateKeyEncrypt($params_str, $apiConfig['secret_key']);
    }

    /**
     * Verify webhook signature using RSA public key decryption.
     */
    public function verifySignature(string $signature, array $data): bool
    {
        try {
            $apiConfig = $this->config['api'][$this->environment];

            unset($data['sign'], $data['platSign']);
            ksort($data);

            $params_str = '';
            foreach ($data as $key => $val) {
                $params_str = $params_str . $val;
            }

            $key = $apiConfig['platform_public_key'] ?? $apiConfig['public_key'];
            $decryptedSignature = $this->publicKeyDecrypt($signature, $key);

            return hash_equals($params_str, $decryptedSignature);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the current environment.
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get the merchant code for current environment.
     */
    public function getMerchantCode(): string
    {
        return $this->config['api'][$this->environment]['merchant_code'];
    }

    /**
     * Get the configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Encrypt data using RSA private key.
     */
    protected function privateKeyEncrypt(string $data, string $privateKey): string
    {
        $privateKey = '-----BEGIN PRIVATE KEY-----' . "\n" . $privateKey . "\n" . '-----END PRIVATE KEY-----';
        $pi_key = openssl_pkey_get_private($privateKey);
        
        if (!$pi_key) {
            throw new BDPayException('Invalid private key format');
        }
        
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $pi_key);
            $crypto .= $encryptData;
        }
        
        return base64_encode($crypto);
    }

    /**
     * Decrypt data using RSA public key.
     */
    protected function publicKeyDecrypt(string $data, string $publicKey): string
    {
        $publicKey = '-----BEGIN PUBLIC KEY-----' . "\n" . $publicKey . "\n" . '-----END PUBLIC KEY-----';
        $data = base64_decode($data);
        $pu_key = openssl_pkey_get_public($publicKey);
        
        if (!$pu_key) {
            throw new BDPayException('Invalid public key format');
        }
        
        $crypto = '';
        foreach (str_split($data, 128) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $pu_key);
            $crypto .= $decryptData;
        }
        
        return $crypto;
    }
}
