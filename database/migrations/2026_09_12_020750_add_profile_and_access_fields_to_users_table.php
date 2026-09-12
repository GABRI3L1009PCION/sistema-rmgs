<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active')->after('password');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->timestamp('invited_at')->nullable()->after('last_login_at');
            $table->foreignId('created_by')->nullable()->after('invited_at')->constrained('users')->nullOnDelete();
            $table->index(['role_id', 'status'], 'users_role_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_status_index');
            $table->dropForeign(['created_by']);
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'role_id', 'phone', 'avatar_path', 'status', 'last_login_at', 'invited_at', 'created_by',
            ]);
        });
    }
};
