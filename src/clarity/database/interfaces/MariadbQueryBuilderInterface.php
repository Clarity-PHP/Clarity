<?php

declare(strict_types=1);

namespace framework\clarity\database\interfaces;

use framework\clarity\database\sql\StatementParameters;

interface MariadbQueryBuilderInterface extends QueryBuilderInterface
{
    function getStatement(): StatementParameters;
}
