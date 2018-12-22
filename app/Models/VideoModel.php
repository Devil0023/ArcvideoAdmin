<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoModel extends Model
{
    use SoftDeletes;

    protected $table = 'video';


    public static function preview($filename){
        return
<<<EOF
    <video src="{$filename}" controls="controls" style="width: 720px; height: 576px;"></video>
    <br/>
    <a href="{$filename}" target="_blank">下载</a>
EOF;

    }
}
