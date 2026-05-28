@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ __($pageTitle) }}   <span class="font-bold"></span></h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">@lang('app.menu.stickyNotes')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/icheck/skins/all.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/multiselect/css/multi-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/icheck/skins/all.css') }}">
<style>
    .panel-darkblue, .panel-purple {
        border-color: #ab8ce4 !important;
    }
    .panel-purple .panel-heading {
        border-color: #ab8ce4  !important;
        color: #fff !important;
        background-color: #ab8ce4  !important;
    }
    .panel-purple .panel-heading a, .panel-purple .panel-heading a {
        color: #fff !important;
    }
</style>
@endpush

@section('content')

    <div class="flex flex-col">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <a href="javascript:;" onclick="showCreateNoteModal()"  class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">@lang("modules.sticky.addNote")</a>
            </div>
        </div>

        <div class="w-full">
            <div class="flex flex-wrap gap-4" id="noteBox">
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
<script src="{{ asset('js/cbpFWTabs.js') }}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/icheck/icheck.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/icheck/icheck.init.js') }}"></script>


<script src="{{ asset('assets/plugins/jQueryUI/jquery-ui.min.js') }}"></script>
<script>

    getNoteData();

    //getting all chat data according to user
    function getNoteData(){

        var url = "{{ route('admin.sticky-note.index') }}";

        $.easyAjax({
            type: 'GET',
            url: url,
            messagePosition: '',
            data:  {},
            container: ".noteBox",
            error: function (response) {

                //set notes in box
                $('#noteBox').html(response.responseText);
            }
        });
    }
</script>



@endpush