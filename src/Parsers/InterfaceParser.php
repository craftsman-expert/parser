<?php

namespace src\parsers;

/**
 * Class InterfaceParser
 * @package src\parsers
 */
interface InterfaceParser
{
    function loadProduct();
    function loadUrl();



    /**
     * @param callable $function
     *
     * @return mixed
     */
    public function onProgress(callable $function);



    /**
     * @param callable $function
     *
     * @return mixed
     */
    public function onStats(callable $function);



    /**
     * @param        $ip
     * @param        $port
     * @param string $login
     * @param        $password
     *
     * @return mixed
     */
    function setProxy($ip, $port, $login, $password);
}