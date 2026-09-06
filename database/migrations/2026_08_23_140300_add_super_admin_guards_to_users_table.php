<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Defense-in-depth: MySQL triggers that block any DELETE or dangerous UPDATE
     * on the hardcoded Super Admin row, even from raw SQL outside the app layer.
     *
     * The email is the canonical identity of the account. Password changes are
     * still permitted (email/role/is_active are untouched in that case).
     */
    public function up(): void
    {
        $email = config('auth.super_admin.email', 'raihan.shifat01@gmail.com');

        DB::statement(<<<SQL
CREATE TRIGGER prevent_super_admin_delete
BEFORE DELETE ON users
FOR EACH ROW
BEGIN
    IF OLD.email = '{$email}' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Super admin account cannot be deleted';
    END IF;
END
SQL);

        DB::statement(<<<SQL
CREATE TRIGGER prevent_super_admin_update
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.email = '{$email}' THEN
        IF NEW.email <> OLD.email OR NEW.role <> OLD.role OR NEW.is_active <> OLD.is_active THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Super admin account cannot be modified';
        END IF;
    END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS prevent_super_admin_delete');
        DB::statement('DROP TRIGGER IF EXISTS prevent_super_admin_update');
    }
};
