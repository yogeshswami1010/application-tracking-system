@extends('layouts.app')

@section('content')
    <script>window.jaStandaloneProfile = true;</script>
    <div id="standalone-applicant-profile">
        {!! $profileHtml !!}
    </div>
@endsection
