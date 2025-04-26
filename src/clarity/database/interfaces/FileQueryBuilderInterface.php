<?php

declare(strict_types=1);

namespace framework\clarity\database\interfaces;

use framework\clarity\database\file\StatementParameters;

interface FileQueryBuilderInterface extends QueryBuilderInterface
{
    function getStatement(): StatementParameters;
}
