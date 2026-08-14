@if (count($entry->screenshots) > 1)
    <div id="competition-slider" class="swiper">
        <div class="swiper-wrapper">
            @foreach($entry->screenshots->sortBy('order_index') as $sshot)
                <div class="swiper-slide">
                    <img src="{{ asset('uploads/competition/'.$sshot->image_full) }}" alt="Screenshot" />
                </div>
            @endforeach
        </div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
    <div id="competition-thumbnails" class="swiper swiper-thumbs">
        <div class="swiper-wrapper">
            @foreach($entry->screenshots->sortBy('order_index') as $sshot)
                <div class="swiper-slide">
                    <img src="{{ asset('uploads/competition/'.$sshot->image_thumb) }}" alt="Thumbnail" />
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="empty-swiper">
        @if (count($entry->screenshots) > 0)
            <img src="{{ asset('uploads/competition/'.$entry->screenshots->first()->image_full) }}" alt="Screenshot" />
        @else
            <img src="{{ asset('images/no-screenshot-640.png') }}" alt="Screenshot" />
        @endif
    </div>
@endif