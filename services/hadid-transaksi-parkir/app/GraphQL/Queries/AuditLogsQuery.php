<?php

namespace App\GraphQL\Queries;

use App\Models\AuditLog;
use Illuminate\Support\Collection;

final class AuditLogsQuery
{
    /**
     * @param  array<string,mixed>  $args
     * @return Collection<int,AuditLog>
     */
    public function __invoke($root, array $args): Collection
    {
        return AuditLog::query()
            ->when(! empty($args['transaction_id']), fn ($q) => $q->where('transaction_id', $args['transaction_id']))
            ->latest()
            ->get();
    }
}
