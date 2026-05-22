<?php

declare(strict_types=1);

namespace App\Web\Analytics\VisitsPerDay;

use Override;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;

final class AlterVisitsPerDayTable implements MigratesUp
{
    public string $name = '2024-12-13_01_alter_visits_table';

    #[Override]
    public function up(): QueryStatement
    {
        return AlterTableStatement::forModel(VisitsPerDay::class)->unique('date');
    }
}
