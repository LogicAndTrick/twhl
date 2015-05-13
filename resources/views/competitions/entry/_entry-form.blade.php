<h3>{{ $entry ? 'Update' : 'Submit' }} Entry</h3>
@form(competition-entry/submit upload=true)
    @hidden(id $comp)
    @text(title $entry) = Entry Name

    @if (!$comp->isVoted())
        {? $method = Request::old('__upload_method') ? Request::old('__upload_method') : ($entry && $entry->is_hosted_externally ? 'link' : 'file'); ?}
        <div class="panel panel-default option-panel">
            <div class="panel-heading">
                <span>File upload method:</span>
                <label class="radio-inline">
                    <input type="radio" name="__upload_method" value="file" {{ $method != 'link' ? 'checked' : '' }} /> Upload file
                </label>
                <label class="radio-inline">
                    <input type="radio" name="__upload_method" value="link" {{ $method == 'link' ? 'checked' : '' }} /> Link to file
                </label>
            </div>
            <div class="panel-body">
                @file(file) = File Upload (.zip, .rar, .7z, maximum size: 16mb)
                {? $location = $entry && $entry->is_hosted_externally ? $entry->file_location : ''; ?}
                @text(link $location) = Link to File (Dropbox, Steam Workshop, etc.)
            </div>
        </div>
    @endif

    @if (!$entry)
        @file(screenshot) = Screenshot (Required)
        <div class="alert alert-info"><span class="glyphicon glyphicon-info-sign"></span> You can add more screenshots to your entry later.</div>
    @endif

    @textarea(content_text class=small $entry) = Entry Description

    @submit = Submit Entry
@endform

<script type="text/javascript">
    $(function() {
        $('[name=__upload_method]').on('change', function() {
            var sel = $('[name=__upload_method]:checked').attr('value');
            $('.option-panel .panel-body > div').addClass('hide');
            $('[name="' + sel + '"]').parent().removeClass('hide');
        }).change();
    });
</script>