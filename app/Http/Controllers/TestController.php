<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TestPostRequest;

class TestController extends Controller
{
    /**
     * トップページ を表示する
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('test.index');
    }

    public function input(TestPostRequest $request){

        //validate済
        $validateData=$request->validated();

        //var_dump($validateData); exit;

        return view('test.input',['datum'=>$validateData]);
    }
}