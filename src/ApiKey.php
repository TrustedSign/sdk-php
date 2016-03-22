<?php
namespace TrustedSignSDK;

/**
 * An object representing your REST API key
 *
 * @package TrustedSignSDK
 */
class ApiKey
{
    /**
     * Hash algorithm
     * @var string
     */
    const HASH_ALGO = 'md5';

    /**
     * API key
     * @var string
     */
    protected $key;

    /**
     * API secret
     * @var string
     */
    protected $secret;

    /**
     * Constructor
     *
     * @param string $key    API key
     * @param string $secret API secret
     */
    public function __construct($key, $secret)
    {
        $this->key = (string) $key;
        $this->secret = (string) $secret;
    }

    /**
     * Gets the API key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * set the Authorization header
     *
     * @param Http\HeaderBag $header
     */
    public function setAuthorizationHeader(
        &$header
    ) {
        $nonce = md5(microtime() . mt_rand());
        $timestamp = time();

        $hashData = array(
            $this->secret,
            $nonce,
            $timestamp
        );

        $signature = hash(self::HASH_ALGO, implode("", $hashData));
        $header->set("App-Key", $this->key);
        $header->set("Nonce", $nonce);
        $header->set("Timestamp", $timestamp);
        $header->set("Signature", $signature);
    }
}
