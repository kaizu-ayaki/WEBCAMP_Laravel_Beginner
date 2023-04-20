<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRegisterPostRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Task as TaskModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CompletedTask as CompletedTaskModel;

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

    public function delete($task_id,Request $request){
        // task_idのレコードを取得する
        $task=$this->getTaskModel($task_id);

        // タスクを削除する
        if($task !== null){
            $task->delete();
            $request->session()->flash('front.task_delete_success',true);
        }

        // 一覧に遷移する
        return redirect('/task/list');
    }

    public function complete(Request $request,$task_id)
    {
        /* タスクを完了テーブルに移動させる */
        try {
            // トランザクション開始
            DB::beginTransaction();

            // task_idのレコードを取得する
            $task = $this->getTaskModel($task_id);
            if ($task === null) {
                // task_idが不正なのでトランザクション終了
                throw new \Exception('');
            }

            // tasks側を削除する
            $task->delete();
//var_dump($task->toArray()); exit;

            // completed_tasks側にinsertする
            $dask_datum = $task->toArray();
            unset($dask_datum['created_at']);
            unset($dask_datum['updated_at']);
            $r = CompletedTaskModel::create($dask_datum);
            if ($r === null) {
                // insertで失敗したのでトランザクション終了
                throw new \Exception('');
            }
//echo '処理成功'; exit;

            // トランザクション終了
            DB::commit();
             // 完了メッセージ出力
            $request->session()->flash('front.task_completed_success', true);
        } catch(\Throwable $e) {
//var_dump($e->getMessage()); exit;
            // トランザクション異常終了
            DB::rollBack();
            // 完了失敗メッセージ出力
            $request->session()->flash('front.task_completed_failure', true);
        }

        // 一覧に遷移する
        return redirect('/task/list');
    }
}