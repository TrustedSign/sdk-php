<?php
namespace TrustedSignSDK\Http\Request;

use TrustedSignSDK\Http\Response\IResponse;

/**
 * An interface for making requests using different HTTP libraries
 *
 * @package TrustedSignSDK
 */
interface IRequest
{
    /**
     * Makes a HTTP request
     *
     * @param string $url URL to request
     * @param string $method HTTP method to use
     * @param array $headers Headers to include
     * @param null $body Body contents if applicable
     * @param int $timeout The timeout in seconds for the request.
     * @return IResponse
     */
    public function request(
        $url,
        $method = 'GET',
        array $headers = array(),
        $body = null,
        $timeout
    );
}
