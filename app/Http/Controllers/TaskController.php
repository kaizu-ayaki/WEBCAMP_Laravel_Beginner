<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRegisterPostRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Task as TaskModel;

class TaskController extends Controller
{
    /**
     * トップページ を表示する
     *
     * @return \Illuminate\View\View
     */
    public function list()
    {
        $per_page=15;

        $list = TaskModel::where('user_id', Auth::id())
                            ->orderBy('priority', 'DESC')
                            ->orderBy('period')
                            ->orderBy('created_at')
                            ->paginate($per_page);
                            //->get();
        //$sql=TaskModel::where('user_id',Auth::id())->toSql();
        //echo "<pre>\n"; var_dump($sql,$list); exit;
        return view('task.list',['list'=>$list]);
    }

    public function register(TaskRegisterPostRequest $request){
        $datum=$request->validated();

        //$user = Auth::user();
        //$id = Auth::id();
        //var_dump($datum, $user, $id); exit;

        $datum['user_id']=Auth::id();

        try{
            $r=TaskModel::create($datum);
            //var_dump($r); exit;
        }catch(\Throwable $e){
            echo $e->getMessage();
            exit;
        }

        $request->session()->flash('front.task_register_success', true);

        return redirect('/task/list');

    }
}