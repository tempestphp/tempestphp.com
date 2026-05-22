<?php

declare(strict_types=1);

namespace App\Web\Analytics\PackageDownloadsPerHour;

use Override;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\IntegerStatement;

final class UpdatePackageDownloadsPerHourTable implements MigratesUp
{
    public string $name = '2024-12-14_02_update_package_downloads_per_hour_table';

    #[Override]
    public function up(): QueryStatement
    {
        return AlterTableStatement::forModel(PackageDownloadsPerHour::class)
            ->add(new IntegerStatement('total', default: 0));
    }
}
