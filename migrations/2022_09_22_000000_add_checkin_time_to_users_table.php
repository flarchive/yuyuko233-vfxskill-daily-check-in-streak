<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->table('users', function (Blueprint $table) use ($schema) {
            if (! $schema->hasColumn('users', 'last_checkin_time')) {
                $table->dateTime('last_checkin_time')->nullable();
            }
        });
    },
    'down' => function (Builder $schema) {
        $schema->table('users', function (Blueprint $table) use ($schema) {
            if ($schema->hasColumn('users', 'last_checkin_time')) {
                $table->dropColumn('last_checkin_time');
            }
        });
    },
];
