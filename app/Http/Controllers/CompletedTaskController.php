<?php

declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CompletedTask as CompletedTaskModel;
use Illuminate\Support\Facades\Auth;

class CompletedTaskController extends Controller{
     /**
     * トップページ を表示する
     *
     * @return \Illuminate\View\View
     */
    public function list(){
        $per_page=10;
        $list=CompletedTaskModel::where('user_id', Auth::id())->paginate($per_page);
        //echo "<pre>\n";var_dump($list);exit;
        return view('task.completed_list',['list'=>$list]);
    }

}