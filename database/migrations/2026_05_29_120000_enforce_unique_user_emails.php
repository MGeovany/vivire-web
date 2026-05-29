<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $rows = DB::table('users')->orderBy('id')->get(['id', 'email']);

        $seen = [];

        foreach ($rows as $row) {
            $normalized = strtolower(trim($row->email));

            if ($normalized === '') {
                continue;
            }

            if (isset($seen[$normalized])) {
                DB::table('users')->where('id', $row->id)->delete();

                continue;
            }

            $seen[$normalized] = $row->id;

            if ($row->email !== $normalized) {
                DB::table('users')->where('id', $row->id)->update(['email' => $normalized]);
            }
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $indexes = collect(DB::select("PRAGMA index_list('users')"))
                ->pluck('name')
                ->filter(fn ($name) => str_contains($name, 'email'));

            if ($indexes->isEmpty()) {
                DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
            }
        }
    }

    public function down(): void
    {
        // Irreversible cleanup of duplicate accounts.
    }
};
