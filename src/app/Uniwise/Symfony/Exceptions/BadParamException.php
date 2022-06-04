<?php


namespace Uniwise\Symfony\Exceptions;


class BadParamException extends \Exception
{
    protected $message = 'The given Parameters are not correct';
}
