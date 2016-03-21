<?php
namespace TrustedSignSDK\Http;

/**
 * Class RequestBodyJSON
 *
 * @package TrustedSignSDK
 */
class RequestBodyJSON implements RequestBodyInterface
{
    /**
     * @var array The parameters to send with this request.
     */
    protected $params = [];

    /**
     * Creates a new EncodedBody entity.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return json_encode($this->params);
    }
}
