<?php

declare(strict_types=1);

namespace App\Web\Analytics\PackageDownloadsPerDay;

use Override;
use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class CreatePackageDownloadsPerDayTable implements MigratesUp, MigratesDown
{
    public string $name = '2024-12-14_01_create_package_downloads_per_day_table';

    #[Override]
    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(PackageDownloadsPerDay::class)
            ->primary()
            ->datetime('date')
            ->varchar('package')
            ->integer('count')
            ->unique('date', 'package');
    }

    #[Override]
    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(PackageDownloadsPerDay::class);
    }
}
