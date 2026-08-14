type Snowflake = {
    element: HTMLElement;
    top: number;
    left: number;
    drift: number;
    hspeed: number;
    vspeed: number;
    direction: number;
};

window.addEventListener('DOMContentLoaded', function () {
    const isChristmas = document.body.classList.contains('egg-christmas');
    if (!isChristmas) return;

    const header = document.querySelector('.header-image')! as HTMLElement;
    const logo = header.querySelector('.logo-image')! as HTMLImageElement;
    const src = logo.src;
    logo.src = src.replace(/twhl-logo-64\.png/gi, 'twhl-logo-xmas1.png');

    const snowCornerL = src.replace(/twhl-logo-64\.png/gi, 'snow-corner1.png');
    const snowCornerR = src.replace(/twhl-logo-64\.png/gi, 'snow-corner2.png');

    // It's snowing!

    const snowContainer = document.createElement('div');
    snowContainer.classList.add('snowfield');

    const cLeft = document.createElement('img');
    cLeft.src = snowCornerL;
    cLeft.classList.add('snow-corner-left');

    const cRight = document.createElement('img');
    cRight.src = snowCornerR;
    cRight.classList.add('snow-corner-right');

    snowContainer.append(cLeft, cRight);

    const flakes: Snowflake[] = [];
    let containerWidth = snowContainer.clientWidth;
    let containerHeight = snowContainer.clientHeight;

    function repositionSnowflake(flake: Snowflake, initial: boolean) {
        if (initial) flake.top = Math.random() * 100;
        else flake.top = 0;
        flake.left = Math.random() * 100;
        flake.drift = Math.random() * 2;
        flake.vspeed = 0.25 + Math.random() * 0.25;
        flake.hspeed = Math.random();
        flake.direction = Math.random() < 0.5 ? -1 : 1;
        flake.element.style.opacity = Math.random().toString();
        flake.element.style.transform = 'scale(' + (Math.random() * 0.6 + 0.2) + ')';
    }

    let animating = false;
    setInterval(function () {
        containerWidth = snowContainer.clientWidth;
        containerHeight = snowContainer.clientHeight;
        const wasAnimating = animating;
        animating = containerWidth > 0 && containerHeight > 0;
        if (!wasAnimating && animating) window.requestAnimationFrame(animateSnowflakes);
    }, 2000);

    let last = 0;

    function animateSnowflakes(timestamp: number) {
        const elapsed = (timestamp - last) / 1000;
        last = timestamp;

        for (let i = 0; i < flakes.length; i++) {
            const flake = flakes[i];

            flake.top += flake.vspeed * elapsed * 60;
            if (flake.top > 100) {
                // If the tab is in the background or hasn't got animation frames for a while,
                // all the snowflakes will get reset to the top of the container, and it looks
                // bad. So retain the top value so flakes respawn in a nice random position.
                const tt = flake.top % 100;
                repositionSnowflake(flake, false);
                flake.top = tt;
            } else {
                const distance = flake.hspeed * elapsed;
                flake.left += distance * flake.direction;
                flake.drift -= distance;
                if (flake.drift < 0) {
                    flake.drift = Math.random() * 2;
                    flake.direction = Math.random() < 0.5 ? -1 : 1;
                    flake.hspeed = Math.random();
                }
            }

            flake.element.style.top = flake.top + '%';
            flake.element.style.left = flake.left + '%';
        }
        if (animating) window.requestAnimationFrame(animateSnowflakes);
    }

    // 50-100 snowflakes
    const numFlakes = Math.floor(Math.random() * 50 + 50);

    for (let i = 0; i < numFlakes; i++) {
        const el = document.createElement('div');
        el.classList.add('snowflake');
        const flake: Snowflake = {
            element: el,
            top: 0,
            left: 0,
            drift: 0,
            hspeed: 0,
            vspeed: 0,
            direction: 0,
        };
        flakes.push(flake);
        snowContainer.append(flake.element);
        repositionSnowflake(flake, true);
    }
    header.append(snowContainer);

    animating = true;
    window.requestAnimationFrame(animateSnowflakes);
});

window.addEventListener('DOMContentLoaded', () => {
    const isPride = document.body.classList.contains('egg-pride');
    if (!isPride) return;

    const header = document.querySelector('.header-image');
    const logo = header?.querySelector('.logo-image') as HTMLImageElement;
    if (!logo) return;

    const src = logo.src;
    logo.src = src.replace(/twhl-logo-64\.png/gi, 'twhl-logo-pride.png');
});
