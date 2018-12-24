<?php

namespace App\Admin\Controllers;

use App\Models\VideoModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Admin;
use App\Models\TranscodeModel;
use Illuminate\Support\MessageBag;

class VideoController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('视频管理')
            ->description('我的视频')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('视频详情')
            ->description('我的视频')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('编辑信息')
            ->description('我的视频')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建视频')
            ->description('我的视频')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VideoModel);

        $grid->model()->where("uid", \Admin::user()->id)->orderBy("id", "desc");

        $grid->id('ID');
        $grid->title('标题');

        $grid->template_id('转码模板')->display(function ($template_id){
            $roles = json_decode(\Admin::user()->roles, true)[0];
            $template_group = TranscodeModel::getTemplateGroupInfo($roles["template_group"]);
            return @$template_group[$template_id]? : "";
        });

        $grid->status('转码状态')->display(function ($status){
            return config('transcode')['code'][$status];
        });

        $grid->taskid('taskid');

        $grid->created_at('创建时间');

        $grid->disableExport();

        $grid->tools(function ($tools){
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        $grid->actions(function ($actions){
            $actions->disableDelete();
            $actions->disableEdit();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $video = VideoModel::findOrFail($id);

        $show = new Show($video);

        $show->id('ID');
        $show->title('标题');

        $show->template_id('转码模板')->as(function ($template_id){
            $roles = json_decode(\Admin::user()->roles, true)[0];
            $template_group = TranscodeModel::getTemplateGroupInfo($roles["template_group"]);
            return @$template_group[$template_id]? : "";
        });

        $show->status('转码状态')->as(function ($status){
            return config('transcode')['code'][$status];
        });

        $show->taskid('taskid');

        if(intval($video->status) === config('transcode')["status"]["upload"]){
            $show->filename('预览')->unescape()->as(function ($filename){
                return VideoModel::preview($filename);
            });
        }

        $show->created_at('创建时间');


        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableList();
            $tools->disableDelete();
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $roles = json_decode(\Admin::user()->roles, true)[0];

        $form = new Form(new VideoModel);

        $form->display('id', 'ID');
        $form->text('title', '标题')->rules('required');
        $form->largefile('filename', '视频')->rules('required');

        $form->select('template_id', '转码模板')->options(TranscodeModel::getTemplateGroupInfo($roles['template_group']))->rules('required');

        $form->hidden('uid')->value(\Admin::user()->id);
        $form->hidden('status')->value(0);
        $form->hidden('taskid')->value(0);

        // 保存前检查空间
        $form->saving(function (Form $form){

            try{

                $file = config('input_path'). DIRECTORY_SEPARATOR.$form->filename;

                $filesize = filesize($file);

                if($filesize > \Admin::user()->capacity_left){
                    throw new \Exception("存储空间到达上限: ".$file."|".$filesize, 403);
                }

            }catch (\Exception $e){

                unlink($file);

                $error = new MessageBag([
                    'title'   => '保存失败',
                    'message' => '['.$e->getCode().']'.$e->getMessage(),
                ]);

                return back()->with(compact('error'));
            }

        });

        //保存后回掉
        $form->saved(function (Form $form){
            try{

                $file = config('input_path'). DIRECTORY_SEPARATOR.$form->model()->filename;
                $filesize = filesize($file);

                (new VideoModel())->subtractCapacity(\Admin::user()->id, $filesize, $form->model()->id);//扣除剩余空间
                (new TranscodeModel())->createTask($form->model()->id); // 通知转码

            }catch(\Exception $e){

                unlink($file);

                $error = new MessageBag([
                    'title'   => '保存失败',
                    'message' => '['.$e->getCode().']'.$e->getMessage(),
                ]);

                return back()->with(compact('error'));
            }

        });
        
        return $form;
    }
}
