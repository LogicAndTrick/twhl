<?php
    $d = [];
    $colours = ["#C858CB","#68B04D","#D65234","#5C9BAC","#BC9336","#CE4F80","#706FC8","#4C6939","#825E86","#984E3D"];
    $i = 0;
    $total_votes = 0;
    foreach ($poll->items->sortByDesc('stat_votes') as $item) {
        $d[] = [
            'value' => $item->stat_votes,
            'label' => $item->text,
            'color' => $colours[$i++%count($colours)]
        ];
        $total_votes += $item->stat_votes;
    }
    $i = 0;
    $front_page = isset($front) && !!$front;
?>
<div class="poll-results row">
    <div class="{{ $front_page ? 'col-xs-12' : 'col-md-6' }}">
        <canvas id="poll-chart-{{ $poll->id }}" width="220" height="220"></canvas>
    </div>
    <div class="{{ $front_page ? 'col-xs-12' : 'col-md-6' }}">
        <ul>
            @foreach ($poll->items->sortByDesc('stat_votes') as $item)
                <li style="border-color: {{ $colours[$i++%count($colours)] }}" class="{{ array_search($item->id, $user_votes) !== false ? 'chosen' : '' }}">
                    {{ $item->text }}: {{ round(($item->stat_votes / $total_votes) * 100) }}% ({{ $item->stat_votes . ' vote' . ($item->stat_votes == 1 ? '' : 's') }})
                </li>
            @endforeach
        </ul>
    </div>
</div>
<script type="text/javascript">
    $(function() {
        var data = {!! json_encode($d) !!};
        var ctx = document.getElementById("poll-chart-{{ $poll->id }}").getContext("2d");
        var chart = new Chart(ctx).Pie(data, {animationSteps:50, animationEasing: "easeOutQuart"});
    });
</script>