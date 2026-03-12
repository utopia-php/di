<?php

namespace Utopia\DI;

use RuntimeException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
