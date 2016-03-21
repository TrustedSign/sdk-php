<?php
namespace TrustedSignSDK\Exceptions;

/**
 * An exception thrown when attempting to do an operation that is not allowed,
 * e.g. saving a read-only object
 *
 * @package TrustedSignSDK\Exceptions
 */
class MethodNotAllowedException extends SDKException
{
}
