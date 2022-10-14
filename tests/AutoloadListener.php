<?php

namespace tests;

class AutoloadListener
{
    public static function autoload()
    {
        require dirname(__DIR__)
            . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'autoload.php';
    }
}
