@title('Special Pages')
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>
        <span class="fa fa-star"></span>
        Special pages
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li class="active">Special Pages</li>
    </ol>

    <div class="row">
        <div class="col-4">
            <h4>Maintenance</h4>
            <ul>
                <li><a href="{{ url('wiki-special/maintenance-categories') }}">Pages with no categories</a></li>
                <li><a href="{{ url('wiki-special/maintenance-links') }}">Links to uncreated pages</a></li>
            </ul>
        </div>
        <div class="col-4">
            <h4>Reports</h4>
            <ul>
                <li><a href="{{ url('wiki-special/report-changes') }}">Recent changes</a></li>
                <li><a href="{{ url('wiki-special/report-links') }}">Link statistics</a></li>
                <li><a href="{{ url('wiki-special/report-pages') }}">Page statistics</a></li>
                <li><a href="{{ url('wiki-special/report-users') }}">User statistics</a></li>
            </ul>
        </div>
        <div class="col-4">
            <h4>Queries</h4>
            <ul>
               <li><a href="{{ url('wiki-special/query-links') }}">Links to and from pages</a></li>
            </ul>
        </div>
    </div>

@endsection