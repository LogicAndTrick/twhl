@title('Upload new wiki file')
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>Upload new file</h1>

    <ol class="breadcrumb">
        <li><a href="{{ url('/wiki') }}">Wiki</a></li>
        <li class="active">Create File</li>
    </ol>

    <div class="alert alert-success">
        <h4>Please obey the rules when uploading files</h4>
        <ul>
            <li>Only files with these extensions can be uploaded: <strong>.jpg, .png, .gif, .mp3, .mp4</strong></li>
            <li>The size limit is <strong>4mb</strong></li>
            @if (permission('Admin'))
                <li>
                    Because you're an admin, you have a bit more freedom:
                    <ul>
                        <li>Extra file extensions: <strong>.zip, .rar, .exe, .msi</strong></li>
                        <li>Increased size limit: <strong>64mb</strong></li>
                    </ul>
                </li>
            @else
                <li>To upload archive files or items larger than 4mb, please contact an admin to do it for you.</li>
            @endif
            <li>Do not upload any copyrighted or inappropriate content</li>
        </ul>
    </div>
    @form(wiki/create-upload upload=true)
        @text(title $slug_title) = File Name
        @file(file) = Choose File
        <div class="wikicode-input">
            @textarea(content_text) = File Details
        </div>
        @submit = Upload File
    @endform
@endsection

