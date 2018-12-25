<?php

namespace App\Models;

use DB;

Class UserModel
{
    public function getUserInfo($uid){

        $user = DB::table('admin_users')->where('id', $uid)->first([
            'id',
            'username',
            'capacity',
            'capacity_left'
        ]);

        $role = DB::table('admin_role_users')->where('user_id', $uid)->first(['role_id']);
        $template = DB::table('admin_roles')->where('id', $role->role_id)->first(['template_group']);

        $data = [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'username' => $user->username,
                'capacity' => $user->capacity,
                'capacity_left' => $user->capacity_left,
                'template_group' => TranscodeModel::getTemplateGroupInfo($template->template_group),
                'input' => config('input_path'),
                'output' => config('output_path'),
            ],
        ];

        return $data;
    }
}