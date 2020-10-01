<?php

namespace App\Logger;

use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class ConsoleLoggerFactory
{
    public static function create(): LoggerInterface
    {
        $logger = new Logger('server');
        $logHandler = new StreamHandler(new ResourceOutputStream(STDOUT));
        $logHandler->setFormatter(new ConsoleFormatter());

        return $logger->pushHandler($logHandler);
    }
}
