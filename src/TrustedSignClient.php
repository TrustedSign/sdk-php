<?php
namespace TrustedSignSDK;

use TrustedSignSDK\Http\Request;
use TrustedSignSDK\Http\Response\IResponse;

/**
 *
 * Class TrustedSignClient
 *
 * @package TrustedSignSDK
 */
class TrustedSignClient
{

    /**
     * @const string The name of the environment variable that contains the app key.
     */
    const APP_KEY_ENV_NAME = 'TRUSTEDSIGN_APP_KEY';

    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    const APP_SECRET_ENV_NAME = 'TRUSTEDSIGN_APP_KEY';

    /**
     * The default host for the API
     */
    const DEFAULT_HOST = 'api.trustedsign.com/app';

    /**
     * Whether to use SSL for API calls
     * @var bool
     */
    protected $useSsl = true;

    /**
     * API key details
     * @var ApiKey
     */
    protected $apiKey;

    /**
     * Class name of request adapter to use
     * @var string
     */
    protected $requestAdapter;


    /**
     * Constructor
     *
     * @param array $config onfiguration of client
     * @throws Exceptions\SDKException
     */
    public function __construct($config)
    {
        $config = array_merge([
            'app_key' => getenv(static::APP_KEY_ENV_NAME),
            'app_secret' => getenv(static::APP_SECRET_ENV_NAME),
            'adapter' => 'curl',
            'ssl' => true,
            'host' => static::DEFAULT_HOST
        ], $config);

        if (!$config['app_key']) {
            throw new Exceptions\SDKException('Required "app_key" key not supplied in config and could not find fallback environment variable "' . static::APP_KEY_ENV_NAME . '"');
        }
        if (!$config['app_secret']) {
            throw new Exceptions\SDKException('Required "app_secret" key not supplied in config and could not find fallback environment variable "' . static::APP_SECRET_ENV_NAME . '"');
        }

        $this->apiKey = new ApiKey($config['app_key'], $config['app_secret']);
        $this->requestAdapter = $config['adapter'];
        $this->useSsl = (bool)$config['ssl'];
        $this->host = (string)$config['host'];
    }


    /**
     * Makes a request to the API and returns the decoded response body ready
     * for use.
     *
     * Can also return the response object itself if $returnResponse is true
     *
     * @param string $method HTTP method
     * @param string $endpoint URI to request (without hostname)
     * @param null|array $params Optional body content of the request,
     * not encoded
     * @param bool $returnResponse Whether to return the response object
     * @return mixed
     */
    protected function sendRequest(
        $method = 'GET',
        $endpoint,
        $params = null,
        $returnResponse = false
    )
    {
        $fullUri = sprintf(
            'http%s://%s%s',
            $this->useSsl ? 's' : '',
            $this->host,
            $endpoint
        );
        $request = new Request($fullUri);
        $this->apiKey->setAuthorizationHeader($request->headers);

        $response = $request->setMethod($method)
            ->send($params);
        $this->handleStatusCode($response);

        if ($returnResponse) {
            return $response;
        }

        $content = json_decode($response->getContent());

        return $content;
    }

    /**
     * Sends a GET request to Server and returns the result.
     *
     * @param string $endpoint
     * @param bool $returnResponse
     *
     * @return IResponse
     *
     * @throws Exceptions\SDKException
     */
    public function get($endpoint, $returnResponse = false)
    {
        return $this->sendRequest(
            'GET',
            $endpoint,
            $params = [],
            $returnResponse
        );
    }

    /**
     * Sends a POST request to Server and returns the result.
     *
     * @param string $endpoint
     * @param array $params
     * @param bool $returnResponse
     *
     * @return IResponse
     *
     * @throws Exceptions\SDKException
     */
    public function post($endpoint, array $params = [], $returnResponse = false)
    {
        return $this->sendRequest(
            'POST',
            $endpoint,
            $params,
            $returnResponse
        );
    }

    /**
     * Sends a Put request to Server and returns the result.
     *
     * @param string $endpoint
     * @param array $params
     * @param bool $returnResponse
     *
     * @return IResponse
     *
     * @throws Exceptions\SDKException
     */
    public function put($endpoint, array $params = [], $returnResponse = false)
    {
        return $this->sendRequest(
            'POST',
            $endpoint,
            $params,
            $returnResponse
        );
    }

    /**
     * Sends a DELETE request to Graph and returns the result.
     *
     * @param string $endpoint
     * @param array $params
     * @param bool $returnResponse
     *
     * @return IResponse
     *
     * @throws Exceptions\SDKException
     */
    public function delete($endpoint, array $params = [], $returnResponse = false)
    {
        return $this->sendRequest(
            'DELETE',
            $endpoint,
            $params,
            $returnResponse
        );
    }

    /**
     * Checks the status code of a response and throws an exception if required
     *
     * @param IResponse $response Response instance
     *
     * @throws Exceptions\AuthorizationException
     * @throws Exceptions\InvalidDataException
     * @throws Exceptions\MethodNotAllowedException
     * @throws Exceptions\ResourceNotFoundException
     * @throws Exceptions\ServiceException
     * @throws \Exception
     */
    protected function handleStatusCode(IResponse $response)
    {
        $statusCode = $response->getStatusCode();

        switch ($statusCode) {
            // These codes are fine
            case 200:
                // OK
            case 201:
                // Created
            case 202:
                // Accepted
            case 204:
                // No Content
            case 410:
                // Gone
                break;

            // These codes are bad
            case 400:
                // Bad Request
                throw new Exceptions\InvalidDataException(
                    (array)json_decode($response->getContent())->errors,
                    $statusCode
                );
            case 401:
                // Unauthorized
                $message = json_decode($response->getContent())->error;
                throw new Exceptions\AuthorizationException($message, $statusCode);
            case 404:
                // Not Found
                throw new Exceptions\ResourceNotFoundException('', $statusCode);
            case 405:
                // Method Not Allowed
                throw new Exceptions\MethodNotAllowedException('', $statusCode);
            case 415:
                // Unsupported Media Type
                throw new Exceptions\UnsupportedMediaTypeException('', $statusCode);

            // And these are also bad
            case 500:
                // Internal Server Error
            case 502:
                // Bad Gateway
            case 503:
                // Service Unavailable
            case 504:
                // Gateway Timeout
                throw new Exceptions\ServiceException('', $statusCode);
            default:
                throw new Exceptions\SDKException('Received unexpected response', $statusCode);
        }
    }
}
