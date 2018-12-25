<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserModel;

class ApiController extends Controller
{
    public function userinfo(Request $request)
    {
        $uid = intval($request->uid)? : 0;
        return (new UserModel)->getUserInfo($uid);
    }
}
