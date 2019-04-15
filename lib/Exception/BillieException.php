<?php

namespace Billie\Exception;

/**
 * Class BillieException
 *
 * @package Billie\Exception
 * @author Marcel Barten <github@m-barten.de>
 */
abstract class BillieException extends \Exception
{
    public function __construct()
    {
        parent::__construct();
    }
}