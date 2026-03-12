<?php

namespace Utopia\DI;

use RuntimeException;
use Psr\Container\ContainerExceptionInterface;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
