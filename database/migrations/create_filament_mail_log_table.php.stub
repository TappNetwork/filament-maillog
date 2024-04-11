<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->text('headers')->nullable();
            $table->longText('attachments')->nullable();
            $table->uuid('message_id')->nullable();
            $table->string('status')->nullable();
            $table->longText('data')->nullable();
            $table->timestamp('opened')->nullable();
            $table->timestamp('delivered')->nullable();
            $table->timestamp('complaint')->nullable();
            $table->timestamp('bounced')->nullable();

            $table->timestamps();

            $table->index('message_id');
            $table->index('status');
        });
    }
};
