<?php

namespace app\agent\model;

use think\Db;
use think\Model;


class SearchModel extends  Model
{

    function Search($table , $keyword)
    {
        $data = self::where('name','like' , "%$keyword%")->name("$table")->select();
        return $data;
    }

}