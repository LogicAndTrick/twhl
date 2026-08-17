type CountdownOptions = {
    until: Date;
    description?: string;
};

function pad(value: number) {
    return value.toString().padStart(2, '0');
}

function createCountdown(element: Element, options: CountdownOptions) {
    const until = options.until.getTime();

    // assemble dom
    element.classList.add('countdown-container');

    const descr = document.createElement('div');
    descr.classList.add('countdown-descr');
    descr.textContent = options.description ?? '';

    const timer = document.createElement('div');
    timer.classList.add('countdown-timer');

    const units: HTMLSpanElement[] = [];
    const unitContainers = ['Days', 'Hours', 'Minutes', 'Seconds'].map((unitName) => {
        const container = document.createElement('div');
        container.classList.add('countdown-unit-container');

        const unit = document.createElement('div');
        unit.classList.add('countdown-unit');
        unit.textContent = pad(0);
        units.push(unit);

        const name = document.createElement('div');
        name.classList.add('countdown-unit-name');
        name.textContent = unitName;

        container.replaceChildren(unit, name);
        return container;
    });

    timer.replaceChildren(...unitContainers);
    element.replaceChildren(descr, timer);

    // set up the update every second
    let interval: number | undefined = undefined;

    function update() {
        const remaining = Math.max(0, until - Date.now());
        const totalSeconds = Math.floor(remaining / 1000);

        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        const vals = [days, hours, minutes, seconds];
        for (let i = 0; i < 4; i++) {
            units[i].textContent = pad(vals[i]);
        }
        if (remaining <= 0) clearInterval(interval);
    }

    update();
    interval = window.setInterval(update, 1000);
}

Object.assign(window, {
    createCountdown,
});
