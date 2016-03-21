<?php
namespace TrustedSignSDK\Http;

/**
 * Interface
 *
 * @package Facebook
 */
interface RequestBodyInterface
{
    /**
     * Get the body of the request to send to Graph.
     *
     * @return string
     */
    public function getBody();
}
