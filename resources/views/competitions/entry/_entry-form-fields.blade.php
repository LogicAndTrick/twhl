@hidden(id $comp)
@text(title $entry) = Entry Name

{? $method = Request::old('__upload_method') ? Request::old('__upload_method') : ($entry && $entry->is_hosted_externally ? 'link' : 'file'); ?}
<div class="card mb-3 option-panel">
    <div class="card-header">
        <span>File upload method:</span>
        <label class="form-check-label ms-2">
            <input class="form-check-input" type="radio" name="__upload_method" value="file" {{ $method != 'link' ? 'checked' : '' }} /> Upload file
        </label>
        <label class="form-check-label ms-1">
            <input class="form-check-input" type="radio" name="__upload_method" value="link" {{ $method == 'link' ? 'checked' : '' }} /> Link to file
        </label>
    </div>
    <div class="card-body">
        @file(file) = File Upload (.zip, .rar, .7z, maximum size: 16mb)
        {? $location = $entry && $entry->is_hosted_externally ? $entry->file_location : ''; ?}
        @text(link $location) = Link to File (Dropbox, Steam Workshop, etc.)
    </div>
</div>

@if (!$entry)
    @file(screenshot) = Screenshot (Required)
    <div class="alert alert-info"><span class="fa fa-info-circle"></span> You can add more screenshots to your entry later.</div>
@endif

<div class="wikicode-input">
    @textarea(content_text class=small $entry) = Entry Description
</div>
@submit = Submit Entry

<script type="text/javascript" defer>
    const optionPanels = document.querySelectorAll('.option-panel .card-body > div');
    document.body.filteredEventListener('change', '[name=__upload_method]', () => {
        const checked = document.querySelector('[name=__upload_method]:checked');
        if (!checked) return;
        optionPanels.forEach(x => x.classList.add('d-none'));
        document.querySelector(`[name="${checked.value}"]`).parentElement.classList.remove('d-none');
    });
</script>