<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class VideoModel extends Model
{
    use SoftDeletes;

    protected $table = 'video';
    protected $fillable = ['title', 'filename', 'status'];

    public function subtractCapacity($uid, $filesize, $form_id){

        $userModel = config('admin.database.users_model');

        try{

            DB::beginTransaction();

            (new $userModel())->where("id", $uid)->decrement("capacity_left", $filesize);

            DB::commit();

        }catch (\Exception $e){

            DB::rollBack();

            self::find($form_id)->update([
                "status" => config('transcode')['status']['upload_error']
            ]);

            throw new \Exception("扣除空间失败".$e->getMessage(), 500);
        }
    }

    public static function preview($filename){
        return
<<<EOF
    <video src="{$filename}" controls="controls" style="width: 720px; height: 576px;"></video>
    <br/>
    <a href="{$filename}" target="_blank">下载</a>
EOF;

    }
}
