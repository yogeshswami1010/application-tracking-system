<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\Question\StoreRequest;
use App\Http\Requests\Question\UpdateRequest;
use App\JobOfferQuestion;
use App\Support\RaDataTableHtml;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Yajra\DataTables\Facades\DataTables;

class AdminJobOfferQuestionController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.question');
        $this->pageIcon = 'icon-grid';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        return view('admin.job-offer-question.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

        return view('admin.job-offer-question.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(StoreRequest $request)
    {

        $question = new JobOfferQuestion;
        $question->question = $request->question;
        $question->required = $request->required;
        $question->type = $request->type;

        $question->save();

        return Reply::redirect(route('admin.job-onboard-questions.index'), __('menu.question').' '.__('messages.createdSuccessfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        abort_if(! $this->user->cans('edit_question'), 403);

        $this->question = JobOfferQuestion::find($id);

        return view('admin.job-offer-question.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(UpdateRequest $request, $id)
    {
        abort_if(! $this->user->cans('edit_question'), 403);
        $question = JobOfferQuestion::find($id);
        $question->question = $request->question;
        $question->required = $request->required;
        $question->type = $request->type;
        $question->save();

        return Reply::redirect(route('admin.job-onboard-questions.index'), __('menu.question').' '.__('messages.updatedSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        abort_if(! $this->user->cans('delete_question'), 403);

        JobOfferQuestion::destroy($id);

        return Reply::success(__('messages.questionDeleted'));
    }

    public function data()
    {

        $questions = JobOfferQuestion::all();

        return DataTables::of($questions)
            ->addColumn('action', function ($row) {
                $parts = [
                    RaDataTableHtml::edit(route('admin.job-onboard-questions.edit', [$row->id])),
                    RaDataTableHtml::deleteSaParams($row->id),
                ];

                return RaDataTableHtml::wrap(implode('', $parts));
            })
            ->editColumn('required', function ($row) {
                return ucfirst($row->required);
            })
            ->editColumn('requ', function ($row) {
                return ucfirst($row->question);
            })
            ->addIndexColumn()
            ->make(true);
    }
}
