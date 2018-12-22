<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Video', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('视频标题');
            $table->integer('taskid')->comment('taskid');
            $table->string('filename')->comment('文件名');
            $table->tinyInteger('status')->comment('转码状态');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Video');
    }
}
