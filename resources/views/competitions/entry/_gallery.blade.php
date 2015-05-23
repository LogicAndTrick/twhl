
<div class="slider">
    <div class="loading" data-u="loading">
        Loading...
    </div>
    <div class="slides" data-u="slides">
        @if (count($entry->screenshots) > 0)
            @foreach($entry->screenshots as $sshot)
            <div>
                <img data-u="image" data-src2="{{ asset('uploads/competition/'.$sshot->image_full) }}" alt="Screenshot" />
                <img data-u="thumb" data-src2="{{ asset('uploads/competition/'.$sshot->image_thumb) }}" alt="Thumbnail" />
            </div>
            @endforeach
        @else
            <div>
                <img data-u="image" data-src2="{{ asset('images/no-screenshot-320.png') }}" alt="Screenshot" />
                <img data-u="thumb" data-src2="{{ asset('images/no-screenshot-160.png') }}" alt="Thumbnail" />
            </div>
        @endif
    </div>
    <div data-u="thumbnavigator" class="thumbs">
        <div data-u="slides">
            <div data-u="prototype" class="p">
                <div data-u="thumbnailtemplate" class="i"></div>
            </div>
        </div>
    </div>
    <span data-u="arrowleft" class="arrow left" style="top: 123px; left: 8px;"></span>
    <span data-u="arrowright" class="arrow right" style="top: 123px; right: 8px;"></span>
</div>