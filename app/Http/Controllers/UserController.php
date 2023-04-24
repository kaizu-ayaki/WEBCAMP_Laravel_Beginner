<?php

declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterPost;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller{
     /**
     * トップページ を表示する
     *
     * @return \Illuminate\View\View
     */
    public function index(){
        return view('user.register');
    }
    public function register(UserRegisterPost $request){
        $datum = $request->validated();
        //
        $datum['password'] = Hash::make($datum['password']);
        //var_dump($datum); exit;

        try{
            $r=UserModel::create($datum);
        //var_dump($r);exit;
        }catch(\Throwable $e){
            echo $e->getMessage();
            exit;
        }

        $request->session()->flash('user_regist', true);

        return redirect('/');
    }
}