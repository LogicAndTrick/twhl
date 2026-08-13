
<div id="gallery-modal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">View Entry Screenshots</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="loading-el text-center p-4">
            <img src="{{ asset("images/loading.gif") }}" alt="Loading..." /> Loading...
        </div>
        <div class="gallery-el d-none">
            ...
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-inverse" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    const gallery_url_template = "{{ url('competition/entry-screenshot-gallery/-id-') }}";
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('gallery-modal'),
              titleEl = modalEl.querySelector('.modal-title'),
              loadingEl = modalEl.querySelector('.loading-el'),
              galleryEl = modalEl.querySelector('.gallery-el'),
              modal = new bootstrapModal(modalEl);
        
        let abort = undefined;
        let currentSwiper = undefined;
        modalEl.addEventListener('hidden.bs.modal', () => {
            abort?.abort();
            currentSwiper?.destroy();
            galleryEl.replaceChildren();
        });

        document.querySelectorAll('.gallery-button').forEach(btn => {
            btn.addEventListener('click', event => {
                event.preventDefault();
                const t = event.target,
                      par = t.closest('[data-id]'),
                      id = par.dataset.id,
                      title = par.dataset.title;

                titleEl.textContent = title;
                loadingEl.classList.toggle('d-none', false);
                galleryEl.classList.toggle('d-none', true);

                abort = new AbortController();
                fetch(gallery_url_template.replace('-id-', id), { signal: abort.signal })
                    .then(x => x.text())
                    .then(x => {
                        const el = document.createElement('div');
                        el.innerHTML = x;
                        galleryEl.replaceChildren(el);
                        const swiper = el.querySelector('#competition-slider');
                        const thumbs = el.querySelector('#competition-thumbnails');
                        if (swiper) {
                            window.initialiseSwiper({
                                element: swiper,
                                thumbnailsElement: thumbs
                            });
                        }
                        loadingEl.classList.toggle('d-none', true);
                        galleryEl.classList.toggle('d-none', false);
                    });

                modal.show();
            });
        });
    });
</script>