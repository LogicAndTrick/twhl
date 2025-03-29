{? $shot = $entry->screenshots->first(); ?}
<div class="slot" data-id="{{ $entry->id }}" data-title="{{ ($entry->title ? $entry->title : 'Unnamed entry') . ' - ' . $entry->user->name }}">
    <div class="slot-heading">
        <div class="slot-avatar">
            @avatar($entry->user small show_name=false)
        </div>
        <div class="slot-title">
            @avatar($entry->user text)

            @if (!isset($deleting) || !$deleting)
                @if (permission('CompetitionAdmin') || ( permission('CompetitionEnter') && Auth::user()->id == $entry->user_id && $comp->canEnter() ))
                    <a href="{{ act('competition-entry', 'delete', $entry->id) }}" class="btn btn-danger btn-xs"><span class="fa fa-remove"></span> Delete Entry</a>
                    <a href="{{ act('competition-entry', 'manage', $entry->id) }}" class="btn btn-info btn-xs"><span class="fa fa-picture-o"></span> Edit Screenshots</a>
                @endif
            @endif
        </div>
        <div class="slot-subtitle">
            {{ $entry->title }}
        </div>
    </div>
    <div class="slot-main d-flex flex-column flex-md-row">

        <div class="text-center me-md-3">
            <a href="#" class="gallery-button img-thumbnail">
                <img class="main" src="{{asset( $shot ? 'uploads/competition/'.$shot->image_thumb : 'images/no-screenshot-320.png' ) }}" alt="Entry">
                @foreach($entry->screenshots->slice(1, 3) as $sh)
                    <span class="preview" style="background-image: url('{{asset( $shot ? 'uploads/competition/'.$sh->image_thumb : 'images/no-screenshot-320.png' ) }}');">
                    </span>
                @endforeach
                @if ($entry->screenshots->count() > 4)
                    <span class="more">+{{ $entry->screenshots->count() - 4 }}</span>
                @endif
            </a>
        </div>

        <div>
            <div class="bbcode">
                {!! $entry->content_html ? $entry->content_html : '<em>No Description</em>' !!}
            </div>

            @if ($entry->getLinkUrl())
            <p>
                <a href="{{ $entry->getLinkUrl() }}" class="btn btn-sm btn-success"><span class="fa fa-download"></span> Download</a>
            </p>
            @endif
        </div>
    </div>
</div>