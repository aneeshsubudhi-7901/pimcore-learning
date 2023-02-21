<?php

namespace App\Service;

use DateTimeZone;
use Monolog\Logger;
use Pimcore\Log\ApplicationLogger;
use Pimcore\Log\Handler\ApplicationLoggerDb;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class ApplicationLoggerTZ extends ApplicationLogger
{
    public $timezone;
    public function __construct(string $timezone)
    {
        $this->timezone = new DateTimeZone($timezone);
    }

    public function addWriter($writer)
    {
        // $this->timezone = new DateTimeZone(date_default_timezone_get());
        if ($writer instanceof \Monolog\Handler\HandlerInterface) {
            if (!isset($this->loggers["default-monolog"])) {
                $this->loggers["default-monolog"] = new \Monolog\Logger(
                    "app",
                    [],
                    [],
                    $this->timezone
                );
            }
            $this->loggers["default-monolog"]->pushHandler($writer);
        } elseif ($writer instanceof \Psr\Log\LoggerInterface) {
            $this->loggers[] = $writer;
        }
    }
    public function setTimezone(string $tzone)
    {
        $tz = new \DateTimeZone($tzone);
        $this->timezone = $tz;
        if (isset($this->loggers["default-monolog"])) {
            $this->loggers["default-monolog"]->setTimezone($tz);
        }
    }
}
