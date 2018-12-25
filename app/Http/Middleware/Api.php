<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use App\Models\ToolsModel;

class Api
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $appid = $request->appid;
        $sign = $request->sign;
        $uid = intval($request->uid)? : 0;

        if(!$appid || !$sign || !$uid){
            exit(json_encode([
                'code' => -1,
                'msg' => 'invalid parameter'
            ]));
        }

        $user = DB::table('admin_users')->where('id', $uid)->first();

        if(!$user){
            exit(json_encode([
                'code' => -1,
                'msg' => 'invalid parameter'
            ]));
        }

        $role = DB::table('admin_role_users')->where('user_id', $uid)->first(['role_id']);
        $appinfo = DB::table('admin_roles')->where('id', $role->role_id)->first(['appsecret', 'appid']);

        if($appid !== $appinfo->appid){
            exit(json_encode([
                'code' => -1,
                'msg' => 'invalid parameter'
            ]));
        }

        $data = [
            "appid" => $appid,
            "uid" => $uid,
        ];

        if(!ToolsModel::checkSign($appinfo->appsecret, $data, $sign)){
            exit(json_encode([
                'code' => -1,
                'msg' => 'invalid parameter'
            ]));
        }

        return $next($request);
    }
}
