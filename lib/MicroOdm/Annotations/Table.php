<?php
declare(strict_types=1);

namespace MicroOdm\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Table
 * @Annotation
 * @Target({"CLASS"})
 */
class Table extends Annotation
{
    public string $name;
}