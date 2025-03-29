@title('Vault item screenshots: '.$item->name)
@extends('app')

@section('content')
    <h1>Vault item screenshots: {{ $item->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
        <li><a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a></li>
        <li class="active">Manage Item Screenshots</li>
    </ol>

    <div class="alert alert-info">
        <h4>The first screenshot is the primary screenshot</h4>
        <p>The primary screenshot is used as the display image for the vault item.</p>
    </div>

    <div class="screenshots-dnd-explanation">
        Drag and drop to re-order the screenshots
        <span class="fa fa-arrow-down"></span>
    </div>

    <ul class="list-unstyled screenshot-list container">
        <li>
            <img src="{{ asset('images/loading.gif') }}" alt="Loading" /> Please wait...
        </li>
    </ul>
    <h3>Upload Screenshots <small>Maximum size: 2mb, maximum width/height: 3000px</small></h3>
    <form id="screenshot-upload" action="{{ act('vault', 'create-screenshot') }}" class="dropzone" enctype="multipart/form-data">
        <input type='hidden' name='_token' value='{{ csrf_token() }}'/>
        <input type="hidden" name="id" value="{{ $item->id }}"/>
        <div class="fallback">
            <input name="file" type="file" />
            <button class="btn btn-secondary" type="submit">Upload</button>
        </div>
    </form>
@endsection

@section('scripts')
    <script type="text/template" id="vault-screenshot-template">
        <li class="row mb-3" data-id="{id}">
            <div class="col-3">
                <img src="{{ asset('uploads/vault/{image_thumb}') }}" alt="Screenshot">
            </div>
            <div class="col-9">
                <div class="drag-handle">
                    <span class="fa fa-bars"></span>
                </div>
                <p>
                    <a href="{{ asset('uploads/vault/{image_full}') }}" target="_blank">See full image</a>
                </p>
                <button type="button" class="delete-button btn btn-danger btn-xs"><span class="fa fa-remove"></span> Delete</button>
            </div>
        </li>
    </script>
    <script type="text/javascript">

        Dropzone.options.screenshotUpload = {
            filesizeBase: 1024,
            maxFilesize: 2,
            init: function() {
                this.on('success', update_screenshot_list);
                this.on('error', function(file, message) {
                    if (typeof message == 'object' && message.file) {
                        $(file.previewElement).find('[data-dz-errormessage]').text(message.file);
                    }
                })
            }
        };

        var screenshot_template = $('#vault-screenshot-template').html();

        function save_screenshot_order() {
            $('.screenshot-list').addClass('loading');
            var ids = $('.screenshot-list li').map(function() { return $(this).data('id'); }).toArray();
            $.post('{{ url("vault/save-screenshot-order/{$item->id}") }}', { ids: ids, _token: '{{ csrf_token() }}' }, function(data) {
                $('.screenshot-list').removeClass('loading');
            });
        }

        function update_screenshot_list() {
            $('.screenshot-list').addClass('loading');
            $.get('{{ url("api/vault-screenshots") }}', { count: 100, item_id: "{{ $item->id }}" }, function(data) {
                var list = $('.screenshot-list').empty();
                if (list.data('ui-sortable')) list.sortable('destroy');
                for (var i = 0; i < data.length; i++) {
                    list.append(template(screenshot_template, data[i]));
                }
                list.sortable({
                    handle: '.drag-handle',
                    update: function () {
                        save_screenshot_order();
                    }
                });
                list.removeClass('loading');
            });
        }

        $(document).on('click', '.delete-button', function() {
            $('.screenshot-list').addClass('loading');
            var id = $(this).closest('li').data('id');
            $.post('{{ url("vault/delete-screenshot") }}', { id: id, _token: '{{ csrf_token() }}' }, update_screenshot_list);
        });

        update_screenshot_list();
    </script>
@endsection