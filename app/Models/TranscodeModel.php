<?php

namespace App\Models;

use DB;

Class TranscodeModel
{
    public function createTask($id){

    }

    public static function getTemplateGroupInfo($template_group){
        $info = [];

        foreach(config($template_group)? explode(PHP_EOL, config($template_group)): [] as $template){
            $temp = explode(":", $template);
            $info[$temp[0]] = $temp[1];
        }

        return $info;
    }
}