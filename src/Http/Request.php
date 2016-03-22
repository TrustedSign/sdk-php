<?php
namespace TrustedSignSDK\Http;

use TrustedSignSDK\Exceptions\SDKException;
use TrustedSignSDK\FileUpload\UploadFile;
use TrustedSignSDK\Http\Response\IResponse;

/**
 * A class for dealing with HTTP requests and responses
 *
 * @package TrustedSignSDK\Http
 */
class Request
{
    const DEFAULT_ADAPTER = 'curl';

    const DEFAULT_REQUEST_TIMEOUT = 10;

    const DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT = 120;

    /**
     * Instance of request
     * @var Request\IRequest
     */
    protected $request;

    /**
     * URL to request
     * @var string
     */
    protected $url;

    /**
     * Request method
     * @var string
     */
    protected $method = 'GET';

    /**
     * Request headers
     * @var HeaderBag
     */
    public $headers;

    /**
     * The parameters to send with this request
     * @var array
     */
    protected $params = [];

    /**
     * The files to send with this request
     * @var array
     */
    protected $files = [];

    /**
     * Whether to use JSON format when post/put/patch
     * @var array
     */
    protected $useJSON = true;

    /**
     * Constructor
     *
     * @param null|string $url URL to request
     * @param string $handler Request handler type to use
     * @throws SDKException
     */
    public function __construct($url = null, $handler = self::DEFAULT_ADAPTER)
    {
        $this->setUrl($url);

        if ('stream' === $handler) {
            $this->request = new Request\Stream();
        } else if ('curl' === $handler) {
            if (!extension_loaded('curl')) {
                throw new SDKException('The cURL extension must be loaded in order to use the "curl" handler.');
            }

            $this->request = new Request\Curl();
        } else {
            throw new SDKException('The http client handler must be set to "curl" or "stream". Guzzle will be supported soon.');
        }

        $this->headers = new HeaderBag();
    }

    /**
     * Sets the URL to request
     *
     * @param string $url URL to request
     * @return $this Provides a fluent interface
     */
    public function setUrl($url)
    {
        $this->url = (string)$url;

        return $this;
    }

    /**
     * Gets the URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the HTTP request method
     *
     * @param string $method Request method
     * @return Request Provides a fluent interface
     */
    public function setMethod($method)
    {
        $this->method = (string)strtoupper($method);

        return $this;
    }

    /**
     * Gets the HTTP request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Iterate over the params and pull out the file uploads.
     *
     * @param array $params
     *
     * @return array
     */
    public function sanitizeFileParams(array $params)
    {
        foreach ($params as $key => $value) {
            if ($value instanceof UploadFile) {
                $this->addFile($key, $value);
                unset($params[$key]);
            }
        }

        return $params;
    }

    /**
     * Add a file to be uploaded.
     *
     * @param string $key
     * @param UploadFile $file
     */
    public function addFile($key, UploadFile $file)
    {
        $this->files[$key] = $file;
    }

    /**
     * Removes all the files from the upload queue.
     */
    public function resetFiles()
    {
        $this->files = [];
    }

    /**
     * Get the list of files to be uploaded.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Let's us know if there is a file upload with this request.
     *
     * @return boolean
     */
    public function containsFileUploads()
    {
        return !empty($this->files);
    }


    /**
     * Returns the body of the request as multipart/form-data.
     *
     * @return RequestBodyMultipart
     */
    public function getMultipartBody()
    {
        return new RequestBodyMultipart($this->params, $this->files);
    }

    /**
     * Returns the body of the request as URL-encoded.
     *
     * @return RequestBodyUrlEncoded
     */
    public function getUrlEncodedBody()
    {
        return new RequestBodyUrlEncoded($this->params);
    }

    /**
     * Returns the body of the request as JSON.
     *
     * @return RequestBodyUrlEncoded
     */
    public function getJSONEncodedBody()
    {
        return new RequestBodyJSON($this->params);
    }

    /**
     * Prepares the request for sending to the client handler.
     *
     *
     * @return array
     */
    public function prepareRequestBody()
    {
        // If we're sending files they should be sent as multipart/form-data
        if ($this->containsFileUploads()) {
            $requestBody = $this->getMultipartBody();
            $this->headers->set('Content-Type', 'multipart/form-data; boundary=' . $requestBody->getBoundary());
        } else if ($this->useJSON) {
            $requestBody = $this->getJSONEncodedBody();
            $this->headers->set('Content-Type', 'application/json');
        } else {
            $requestBody = $this->getUrlEncodedBody();
            $this->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        }

        return $requestBody->getBody();
    }


    /**
     * Makes the request and returns a response adapter object
     *
     * @param null|array $params Data to send (for POST/PATCH/PUT requests)
     * @return IResponse
     * @throws \RuntimeException
     */
    public function send($params = null)
    {
        $this->params = $params;
        $body = $this->prepareRequestBody();
        $headers = array();

        foreach ($this->headers->all() as $name => $values) {
            $name = implode('-', array_map('ucfirst', explode('-', $name)));
            $headers[$name] = $values[0];
        }
        $timeout = static::DEFAULT_REQUEST_TIMEOUT;
        if ($this->containsFileUploads()) {
            $timeout = static::DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT;
        }

        $response = $this->request->request(
            $this->url,
            $this->method,
            $headers,
            $body,
            $timeout
        );

        return $response;
    }

    /**
     * Performs a GET request
     *
     * @return IResponse
     */
    public function get()
    {
        return $this->setMethod(__FUNCTION__)
            ->send();
    }

    /**
     * Performs a POST request
     *
     * @param null|string $data Data to send
     * @return IResponse
     */
    public function post($data = null)
    {
        return $this->setMethod(__FUNCTION__)
            ->send($data);
    }

    /**
     * Performs a DELETE request
     *
     * @return IResponse
     */
    public function delete()
    {
        return $this->setMethod(__FUNCTION__)
            ->send();
    }

    /**
     * Performs a PATCH request
     *
     * @param null|string $data Data to send
     * @return IResponse
     */
    public function patch($data = null)
    {
        return $this->setMethod(__FUNCTION__)
            ->send($data);
    }

    /**
     * Performs a PUT request
     *
     * @param null|string $data Data to send
     * @return IResponse
     */
    public function put($data = null)
    {
        return $this->setMethod(__FUNCTION__)
            ->send($data);
    }

    /**
     * Performs an OPTIONS request
     *
     * @return IResponse
     */
    public function options()
    {
        return $this->setMethod(__FUNCTION__)
            ->send();
    }

    /**
     * Performs a HEAD request
     *
     * @return IResponse
     */
    public function head()
    {
        return $this->setMethod(__FUNCTION__)
            ->send();
    }
}
