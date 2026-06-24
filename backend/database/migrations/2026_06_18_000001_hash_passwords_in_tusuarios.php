<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('TUsuarios')->where('estadoA', true)->get();

        foreach ($users as $user) {
            if (!Hash::needsRehash($user->password_hash) || Hash::isHashed($user->password_hash)) {
                continue;
            }

            DB::table('TUsuarios')
                ->where('id_usuario', $user->id_usuario)
                ->update(['password_hash' => Hash::make($user->password_hash)]);
        }
    }

    public function down(): void
    {
        // No podemos revertir el hash
    }
};
