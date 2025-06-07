<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    // تغییر اول: حذف ستون 'job_title' از جدول 'users'
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('job_title');
    });

    // تغییر دوم: افزودن ستون 'role' به جدول 'company_user'
    Schema::table('company_user', function (Blueprint $table) {
        $table->string('role')->default('member')->after('employment_type');

        // $table->timestamps(); // ✅ این خط حذف یا کامنت شد
    });
}

public function down()
{
    // ...

    Schema::table('company_user', function (Blueprint $table) {
        $table->dropColumn('role');

        // $table->dropTimestamps(); // ✅ این خط حذف یا کامنت شد
    });
}
};
