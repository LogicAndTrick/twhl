
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.comment-meta-rating').forEach(self => {
        const sel = self.querySelector('select');
        const con = self.querySelector('.stars');

        if (!sel || !con) return;

        sel.style.display = 'none';

        const fs = con.dataset.fullStar;
        const es = con.dataset.emptyStar;

        for (let i = 1; i <= 5; i++) {
            const img = document.createElement('img');
            img.src = es;
            img.alt = 'star';
            img.dataset['score'] = i.toString();
            img.addEventListener('click', event => {
                sel.value = event.target.dataset.score;
                sel.dispatchEvent(new Event('change'));
            });
            con.appendChild(img);
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-inverse btn-sm';
        button.textContent = 'Clear rating';
        button.addEventListener('click', event => {
            sel.value = '0';
            sel.dispatchEvent(new Event('change'));
        });
        con.appendChild(button);

        sel.addEventListener('change', event => {
            const val = event.target.value;
            con.querySelectorAll('img').forEach(st => {
                const idx = st.dataset['score'];
                st.setAttribute('src', idx <= val ? fs : es);
            });
        });
    });
});