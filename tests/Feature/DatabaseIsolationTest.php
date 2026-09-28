<?php

use Illuminate\Support\Facades\DB;

test('la suite usa una base de datos SQLite en memoria separada de la de desarrollo', function () {
    expect(DB::connection()->getDriverName())->toBe('sqlite');
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');
});
