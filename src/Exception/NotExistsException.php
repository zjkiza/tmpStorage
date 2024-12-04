<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Exception;

use Zjk\TmpStorage\Contract\ExceptionInterface;

class NotExistsException extends \RuntimeException implements ExceptionInterface
{
}
