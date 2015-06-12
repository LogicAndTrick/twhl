{?
    // Valid classes: full, small, inline
    $class = isset($class) ? $class : 'full';
    $border = isset($border) && $border;
    $name = !isset($name) || $name;
    $title = $class == 'full' && $user->title_custom && (!isset($title) || $title);
?}
<span class="avatar {{ $class }} {{ $border ? 'border' : '' }}">
    <a href="{{ act('user', 'view', $user->id) }}">
    <img src="{{ $user->getAvatarUrl($class) }}" alt="{{ $user->name}}"/>
    @if ($name)
        <span class="name">{{ $user->name }}</span>
    @endif
    </a>
    @if ($title)
        <span class="title">{{ $user->title_text }}</span>
    @endif
</span>