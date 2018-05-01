<?php

namespace Reliv\WhiteRat;

/**
 * Indicates a whitelist did not pass validation.
 *
 * The path to the key that failed validation should be indicated, and the
 * reason for validation failure should be explained.
 *
 * @package Reliv\WhiteRat
 */
class WhitelistValidationException extends \InvalidArgumentException
{

}