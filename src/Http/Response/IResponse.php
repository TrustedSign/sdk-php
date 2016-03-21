<?php
namespace TrustedSignSDK\Http\Response;

use TrustedSignSDK\Http\HeaderBag;

/**
 * An interface for HTTP responses using different HTTP libraries
 *
 * @package TrustedSignSDK
 */
interface IResponse
{
    /**
     * Get the body content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get the headers from the response
     *
     * @return HeaderBag
     */
    public function getHeaders();

    /**
     * Get the status code
     *
     * @return int
     */
    public function getStatusCode();
}
