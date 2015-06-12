<div class="poll-form">
    @form(poll/vote)
        @hidden(id $poll)
        @if ($errors->has('item_id'))
            <div class="has-error"><p class="help-block">Pick something to vote for!</p></div>
        @endif
        @foreach ($poll->items as $item)
            <div class="radio">
                <label for="item_{{ $item->id }}">
                    <input type="radio" id="item_{{ $item->id }}" name="item_id" value="{{ $item->id }}" {{ array_search($item->id, $user_votes) !== false ? 'checked' : '' }}>
                    {{ $item->text }}
                </label>
            </div>
        @endforeach
        <p class="text-center">
            @if (Auth::user())
                <button class="btn btn-info" type="submit">Vote now!</button>
            @else
                <button class="btn btn-info" type="button" disabled>Login to vote</button>
            @endif
        </p>
    @endform
</div>