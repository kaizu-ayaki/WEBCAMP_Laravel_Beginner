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

    public function detail($task_id)
    {
        //
        return $this->singleTaskRender($task_id, 'task.detail');
    }

    /**
     * タスクの編集画面表示
     */
    public function edit($task_id)
    {
        // task_idのレコードを取得する(引数で取得)
        // テンプレートに「取得したレコード」の情報を渡す
        return $this->singleTaskRender($task_id, 'task.edit');
    }

     /**
     * 「単一のタスク」Modelの取得
     */
    protected function getTaskModel($task_id)
    {
        // task_idのレコードを取得する
        $task = TaskModel::find($task_id);
        if ($task === null) {
            return null;
        }
        // 本人以外のタスクならNGとする
        if ($task->user_id !== Auth::id()) {
            return null;
        }
        return $task;
    }

    /**
     * 「単一のタスク」の表示
     */
    protected function singleTaskRender($task_id, $template_name)
    {
        // task_idのレコードを取得する
        $task = $this->getTaskModel($task_id);
        if ($task === null) {
            return redirect('/task/list');
        }

        // テンプレートに「取得したレコード」の情報を渡す
        return view($template_name, ['task' => $task]);
    }

    public function editSave(TaskRegisterPostRequest $request,$task_id){
        // formからの情報を取得する
        $datum=$request->validated();

        // task_idのレコードを取得する
        $task=$this->getTaskModel($task_id);

        if($task===null){
            return redirect('/task/list');
        }

        // レコードの内容をUPDATEする
        $task->name = $datum['name'];
        $task->period = $datum['period'];
        $task->detail = $datum['detail'];
        $task->priority = $datum['priority'];

        // レコードを更新
        $task->save();

        // タスク編集成功
        $request->session()->flash('front.task_edit_success',true);

        // 詳細閲覧画面にリダイレクトする
        return redirect(route('detail',['task_id'=>$task->id]));
    }
}