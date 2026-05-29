<link rel="stylesheet" href="{{ asset('assets/node_modules_files/select2/dist/css/select2.css') }}">
<style>
    #ja-filter-bar,
    #ja-table-filter-bar {
        /* max-height: 0; */
        padding-top: 0;
        padding-bottom: 0;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0.22, 1, 0.36, 1), padding-top 0.3s, padding-bottom 0.3s;
    }
    #ja-filter-bar.ja-filter-open,
    #ja-table-filter-bar.ja-filter-open {
        max-height: min(1200px, 88vh);
        padding-top: 14px;
        padding-bottom: 14px;
    }
    #ja-filter-bar .select2-container,
    #ja-table-filter-bar .select2-container {
        width: 100% !important;
    }
    #ja-filter-bar .select2-container--default .select2-selection--single,
    #ja-table-filter-bar .select2-container--default .select2-selection--single {
        min-height: 38px;
        border: 1.5px solid #E2DED8;
        border-radius: 10px;
        background-color: #F8F7F4;
        padding: 4px 10px;
    }
    #ja-filter-bar .select2-container--default .select2-selection--single .select2-selection__rendered,
    #ja-table-filter-bar .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
        padding-left: 2px;
        color: #1A1E2E;
        font-size: 13px;
    }
    #ja-filter-bar .select2-container--default .select2-selection--single .select2-selection__arrow,
    #ja-table-filter-bar .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 6px;
    }
    #ja-filter-bar .select2-container--default.select2-container--focus .select2-selection--single,
    #ja-filter-bar .select2-container--default.select2-container--open .select2-selection--single,
    #ja-table-filter-bar .select2-container--default.select2-container--focus .select2-selection--single,
    #ja-table-filter-bar .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #2563eb;
    }
    #ja-filter-bar .select2-container--default .select2-selection--multiple,
    #ja-table-filter-bar .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1.5px solid #E2DED8;
        border-radius: 10px;
        background-color: #F8F7F4;
        padding: 4px 8px;
    }
    #ja-filter-bar .select2-container--default .select2-selection--multiple .select2-selection__rendered,
    #ja-table-filter-bar .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 0;
    }
    #ja-filter-bar .select2-container--default .select2-selection--multiple .select2-selection__choice,
    #ja-table-filter-bar .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #e8eefc;
        border: 1px solid #c7d7f7;
        border-radius: 6px;
        color: #1e3a5f;
        font-size: 12px;
        margin-top: 4px;
    }
    #ja-filter-bar .select2-container--default.select2-container--focus .select2-selection--multiple,
    #ja-table-filter-bar .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #2563eb;
    }
    #ja-filter-bar .select2-dropdown,
    #ja-table-filter-bar .select2-dropdown {
        border: 1.5px solid #E2DED8;
        border-radius: 10px;
        overflow: hidden;
    }
</style>
