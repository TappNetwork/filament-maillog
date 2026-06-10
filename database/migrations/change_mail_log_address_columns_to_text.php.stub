<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_logs', function (Blueprint $table): void {
            $table->text('from')->nullable()->change();
            $table->text('to')->nullable()->change();
            $table->text('cc')->nullable()->change();
            $table->text('bcc')->nullable()->change();
            $table->text('subject')->change();
        });
    }
};
