<?php


namespace src;


use Exception;
use Throwable;

/**
 * Class ParserException
 * @package src
 */
class ParserException extends Exception
{
    /**
     * @var string
     */
    private $parser = '';
    /**
     * ParserException constructor.
     *
     * @param string         $message
     * @param string         $parser
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", $parser = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setParser($parser);
    }



    /**
     * @return string
     */
    public function getParser():string
    {
        return $this->parser;
    }



    /**
     * @param string $parser
     */
    private function setParser(string $parser):void
    {
        $this->parser = $parser;
    }
}