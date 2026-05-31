<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->table('users', function (Blueprint $table) use ($schema) {
            if (! $schema->hasColumn('users', 'total_checkin_count')) {
                $table->integer('total_checkin_count')->default(0);
            }

            if (! $schema->hasColumn('users', 'total_continuous_checkin_count')) {
                $table->integer('total_continuous_checkin_count')->default(0);
            }
        });
    },
    'down' => function (Builder $schema) {
        $schema->table('users', function (Blueprint $table) use ($schema) {
            $columns = [];

            if ($schema->hasColumn('users', 'total_checkin_count')) {
                $columns[] = 'total_checkin_count';
            }

            if ($schema->hasColumn('users', 'total_continuous_checkin_count')) {
                $columns[] = 'total_continuous_checkin_count';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    },
];
