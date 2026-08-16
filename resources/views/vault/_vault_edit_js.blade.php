<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        const gameId = document.querySelector('[name="game_id"]');
        const engineId = document.querySelector('[name="engine_id"]');
        const categoryId = document.querySelector('[name="category_id"]');
        const licenseId = document.querySelector('[name="license_id"]');
        const typeId = document.querySelector('[name="type_id"]');

        const uploadMethod = document.querySelector('[name="__upload_method"]');
        const categoryHelp = document.getElementById('category-help');
        const licenseHelp = document.getElementById('license-help');

        gameId.addEventListener('change', event => {
            const d = window.getAutocompleteData(gameId);
            if (d) window.setAutocompleteValue(engineId, d.engine_id);
        });

        engineId.addEventListener('change', event => {
            const d = window.getAutocompleteData(engineId);
            const g = window.getAutocompleteData(gameId);
            if (d && g && d.id != g.engine_id) window.setAutocompleteValue(gameId, []);
        });

        categoryId.addEventListener('change', event => {
            const d = window.getAutocompleteData(categoryId);
            categoryHelp.textContent = d?.description ?? '';
        });

        licenseId.addEventListener('change', event => {
            const d = window.getAutocompleteData(licenseId);
            licenseHelp.textContent = d?.description ?? '';
        });

        typeId.addEventListener('change', event => {
            const d = window.getAutocompleteData(typeId);
            const id = d?.id?.toString();
            document.querySelectorAll('[name="__includes[]"]').forEach(x => {
                const dt = x.dataset.type?.toString();
                if (dt != id) {
                    x.checked = false;
                    x.disabled = true;
                } else {
                    x.disabled = false;
                }
            });
        });

        document.querySelectorAll('[name="__upload_method"]').forEach(x => {
            x.addEventListener('change', event => {
                const val = document.querySelector('[name="__upload_method"]:checked')?.value;
                document.querySelectorAll('.option-panel .card-body > fieldset').forEach(fs => {
                    fs.disabled = true;
                    fs.hidden = true;
                });
                document.querySelectorAll('[name="' + val + '-fields"]').forEach(fs => {
                    fs.disabled = false;
                    fs.hidden = false;
                });
            });
        });
        document.querySelector('[name="__upload_method"]').dispatchEvent(new Event('change'));
    });
</script>
