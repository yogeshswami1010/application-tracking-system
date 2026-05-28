@extends('layouts.app')
@push('head-script')
<style>
    [type="checkbox"]:not(:checked), [type="checkbox"]:checked {
    position: absolute;
    left: auto !important;
}
.ml{
    margin-left: 503px;
}
</style>
@endpush

@section('content')

    <div class="jc-table-card ra-dt-wrap overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-4 pt-4 pb-2">
            <div id="search-container" class="flex items-center flex-1 max-w-md">
                <input id="skill" class="form-control flex-1" type="text" name="skill" placeholder="@lang('modules.applicationArchive.enterSkill')">
                <a href="javascript:;" class="hidden ml-2 text-gray-500 hover:text-gray-700">
                    <i class="fa fa-times-circle-o"></i>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-sm btn-danger deleteButton hidden" id="deleteAllSelectedRecords">delete</button>
                <a onclick="exportJobApplication()">
                    <button class="btn btn-sm btn-primary" type="button">
                        <i class="fa fa-upload"></i>  @lang('menu.export')
                    </button>
                </a>
            </div>
        </div>
        <table id="myTable" class="jc-cat-table display w-full" style="width:100%">
            <thead>
            <tr>
                <th style="width:48px;">
                    <input type="checkbox" id="chkCheckAll" class="rounded border-gray-300 text-primary focus:ring-primary">
                </th>
                <th style="width:72px;">#</th>
                <th>@lang('modules.jobApplication.applicantName')</th>
                <th>@lang('menu.jobs')</th>
                <th>@lang('menu.locations')</th>
            </tr>
            </thead>
        </table>
    </div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script>
        var table;
        loadTable();

        function redrawTable() {
            table.draw(false);
        }

        function loadTable() {
            table = $('#myTable').DataTable({
                responsive: false,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{!! route('admin.applications-archive.data') !!}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            skill: $('#skill').val()
                        } );
                    }
                },
                language: languageOptions(),
                stripeClasses: [],
                dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
                drawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                columns: [
                    {data: 'select_orders', name: 'select_orders', orderable: false, searchable: false},
                    { data: 'DT_Row_Index', orderable: false, searchable: false},
                    { data: 'full_name', name: 'full_name' },
                    { data: 'title', name: 'job_id', width: '17%'},
                    { data: 'location', name: 'location_id'},
                ]
            });
        }
        $(function(e){
    $("#deleteAllSelectedRecords").hide();
            
        $('#chkCheckAll').click(function () {
        var checkboxes =  $('input[name="check[]"]').length;
            $('.checkBoxClass').prop('checked',$(this).prop('checked'));
            if ( checkboxes > 0 && this.checked) {
                $("#deleteAllSelectedRecords" ).show();
            }else{
                $("#deleteAllSelectedRecords").hide();
            }
     });
    $('#deleteAllSelectedRecords').click(function(e){
        e.preventDefault();
       
        var rowdIds = $("#myTable input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();
            swal({
                title: "@lang('errors.areYouSure')",
                text: "@lang('errors.deleteWarning')",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "@lang('app.delete')",
                cancelButtonText: "@lang('app.cancel')",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {  
                    var url = "{{ route('admin.applications-archive.deleteRecords',':rowdIds') }}";
                    url = url.replace(':rowdIds', rowdIds);

                    var token = "{{ csrf_token() }}"; 
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token },
                        success: function (response) {
                            if (response.status == "success" ) {
                                $.unblockUI();
                                table.draw(false);
                            }
                        }
                    });
                }
            });
    });
});    
        
        $(function(e){
         var counterChecked = 0;
    $('body').on('change', 'input[name="check[]"]', function() {
    this.checked ? counterChecked++ : counterChecked--;
    counterChecked > 0 ? $('#deleteAllSelectedRecords').show(): $('#deleteAllSelectedRecords').hide();

    });
 });
        function exportJobApplication(){
            var skillVal = $('#skill').val();

            if (skillVal == '') {
                skillVal = undefined
            }

            var url = '{{ route('admin.applications-archive.export', ':skill') }}';
            url = url.replace(':skill', skillVal);

            window.location.href = url;
        }

        search($('#skill'), 500, 'table');

        $('#myTable').on('click', '.show-detail', function () {
            var $sidebar = $("#right-sidebar");
            $sidebar.removeClass('translate-x-full').addClass("shw-rside");

            var id = $(this).data('row-id');
            var url = "{{ route('admin.applications-archive.show',':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'GET',
                url: url,
                success: function (response) {
                    if (response.status == "success") {
                        $('#right-sidebar-content').html(response.view);
                    }
                }
            });
        });
    </script>
@endpush