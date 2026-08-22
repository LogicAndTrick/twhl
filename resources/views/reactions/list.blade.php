@php
    $reactions = ($item->reactions ?? collect());

    // compress reaction data to reduce size of html
    $foundUsers = [];
    $compressedUsers = [];
    $compressedReacts = [];

    // string replacements for avatar urls since they are repeated extremely often
    // avatars always look something like `12345_67890.png` so this is not a problem to do
    $repl = [];
    $repl[asset('uploads/avatars/inline/')] = '!U!';
    $repl[asset('images/avatars/inline/')] = '!I!';

    // use single-char keys instead of full key names, and
    // apply string replacements to the avatar_inline paths
    foreach ($reactions as $x) {
        $user = $x['user'];
        if (!isset($foundUsers[$user['id']])) {
            $foundUsers[$user['id']] = true;
            $compressedUsers[] = [
                'i' => $user['id'],
                'n' => $user['name'],
                'a' => strtr($user['avatar_inline'], $repl)
            ];
        }
        $compressedReacts[] = implode('|', [
            $x['user_id'],
            $x['reaction_type_id'],
        ]);
    }
    $reactionGroups = $reactions->groupBy('reaction_type_id');
    $all_reaction_types = collect(all_reaction_types())->keyBy('id');
    $user_id = Auth::id();
    $data_id = Illuminate\Support\Str::uuid();
    // using json_encode with these flags to make sure XSS isn't possible here
@endphp
<script type="text/json" id="reactions_data_{{$data_id}}">{!!
    json_encode([
        'repl' => $repl,
        'us' => $compressedUsers,
        'rs' => $compressedReacts
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
!!}</script>
<div class="reactions-list {{ $reactions->isEmpty() ? 'empty' : '' }}"
    data-user-id="{{ $user_id }}"
    data-reaction-entity-id="{{ $item->id }}"
    data-reaction-type="{{ $type }}"
    data-reactions-data-id="reactions_data_{{$data_id}}"
>
    @foreach ($all_reaction_types as $reaction_type)
        @php
            $reacts = $reactionGroups->get($reaction_type['id']);
        @endphp
        @if ($reacts)
            <div class="reaction {{ $reacts->contains('user_id', $user_id) ? 'active' : '' }}">
                <span class="img">
                    @if ($reaction_type['is_image'])
                        <img src="{{ $reaction_type['value'] }}" />
                    @else
                        {{ $reaction_type['value'] }}
                    @endif
                </span>
                <span class="num">{{ $reacts->count() }}</span>
            </div>
        @endif
    @endforeach
</div>