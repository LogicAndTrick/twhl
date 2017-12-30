@hidden(id $comp)
@text(title $entry) = Entry Name

@if (!$comp->isVoted())
    {? $method = Request::old('__upload_method') ? Request::old('__upload_method') : ($entry && $entry->is_hosted_externally ? 'link' : 'file'); ?}
    <div class="card mb-3 option-panel">
        <div class="card-header">
            <span>File upload method:</span>
            <span class="form-check form-check-inline d-inline-block mb-0">
                <label class="form-check-label ml-2">
                    <input class="form-check-input" type="radio" name="__upload_method" value="file" {{ $method != 'link' ? 'checked' : '' }} /> Upload file
                </label>
                <label class="form-check-label ml-1">
                    <input class="form-check-input" type="radio" name="__upload_method" value="link" {{ $method == 'link' ? 'checked' : '' }} /> Link to file
                </label>
            </span>
        </div>
        <div class="card-block">
            @file(file) = File Upload (.zip, .rar, .7z, maximum size: 16mb)
            {? $location = $entry && $entry->is_hosted_externally ? $entry->file_location : ''; ?}
            @text(link $location) = Link to File (Dropbox, Steam Workshop, etc.)
        </div>
    </div>
@endif

@if (!$entry)
    @file(screenshot) = Screenshot (Required)
    <div class="alert alert-info"><span class="fa fa-info-circle"></span> You can add more screenshots to your entry later.</div>
@endif

<div class="wikicode-input">
    @textarea(content_text class=small $entry) = Entry Description
</div>
@submit = Submit Entry

<script type="text/javascript">
    $(function() {
        $('[name=__upload_method]').on('change', function() {
            var sel = $('[name=__upload_method]:checked').attr('value');
            $('.option-panel .card-block > div').addClass('d-none');
            $('[name="' + sel + '"]').parent().removeClass('d-none');
        }).change();
    });
</script>