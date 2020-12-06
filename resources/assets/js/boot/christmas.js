window.addEventListener('DOMContentLoaded', function () {
    var isChristmas = document.body.classList.contains('egg-christmas');
    if (!isChristmas) return;

    var header = $('.header-image');
    var logo = header.find('.logo-image');
    var src = logo.attr('src');
    logo.attr('src', src.replace(/twhl-logo-64\.png/ig, 'twhl-logo-xmas1.png'));

    var snowCornerL = src.replace(/twhl-logo-64\.png/ig, 'snow-corner1.png');
    var snowCornerR = src.replace(/twhl-logo-64\.png/ig, 'snow-corner2.png');

    // It's snowing!

    var snowContainer = $('<div></div>').addClass('snowfield');
    snowContainer.append($('<img />').attr('src', snowCornerL).addClass('snow-corner-left'));
    snowContainer.append($('<img />').attr('src', snowCornerR).addClass('snow-corner-right'));

    var flakes = [];
    var containerWidth = snowContainer.width();
    var containerHeight = snowContainer.height();

    function repositionSnowflake(flake, initial) {
        if (initial) flake.top = Math.random() * 100;
        else flake.top = 0;
        flake.left = Math.random() * 100;
        flake.drift = Math.random() * 2;
        flake.vspeed = 0.25 + Math.random() * 0.25;
        flake.hspeed = Math.random();
        flake.direction = Math.random() < 0.5 ? -1 : 1;
        flake.element.css({
            opacity: Math.random(),
            transform: 'scale(' + (Math.random() * 0.6 + 0.2) + ')'
        });
    }

    var animating = false;
    setInterval(function () {
        containerWidth = snowContainer.width();
        containerHeight = snowContainer.height();
        var wasAnimating = animating;
        animating = containerWidth > 0 && containerHeight > 0;
        if (!wasAnimating && animating) window.requestAnimationFrame(animateSnowflakes);
    }, 2000);

    var last = 0;
    function animateSnowflakes(timestamp) {
        var elapsed = (timestamp - last) / 1000;
        last = timestamp;

        for (var i = 0; i < flakes.length; i++) {
            var flake = flakes[i];

            flake.top += flake.vspeed * elapsed * 60;
            if (flake.top > 100) {
                // If the tab is in the background or hasn't got animation frames for a while,
                // all the snowflakes will get reset to the top of the container, and it looks
                // bad. So retain the top value so flakes respawn in a nice random position.
                var tt = flake.top % 100;
                repositionSnowflake(flake);
                flake.top = tt;
            } else {
                var distance = flake.hspeed * elapsed;
                flake.left += distance * flake.direction;
                flake.drift -= distance;
                if (flake.drift < 0) {
                    flake.drift = Math.random() * 2;
                    flake.direction = Math.random() < 0.5 ? -1 : 1;
                    flake.hspeed = Math.random();
                }
            }

            flake.element.css({
                top: flake.top + '%',
                left: flake.left + '%'
            });
        }
        if (animating) window.requestAnimationFrame(animateSnowflakes);
    }

    // 50-100 snowflakes
    var numFlakes = Math.floor(Math.random() * 50 + 50);

    for (var i = 0; i < numFlakes; i++) {
        var flake = {
            element: $('<div></div>').addClass('snowflake'),
            top: 0,
            left: 0,
            drift: 0,
            hspeed: 0,
            vspeed: 0,
            direction: 0
        };
        flakes.push(flake);
        snowContainer.append(flake.element);
        repositionSnowflake(flake, true);
    }
    header.append(snowContainer);

    animating = true;
    window.requestAnimationFrame(animateSnowflakes);
});