import { router } from '@inertiajs/vue3';

/** Adds search to legacy native selects while leaving Vue's model bindings in control. */
export function enableSearchableSelects(): void {
    const initialized = new WeakSet<HTMLSelectElement>();
    const failed = new WeakSet<HTMLSelectElement>();
    const optionSignatures = new WeakMap<HTMLSelectElement, string>();
    let scheduled = false;

    const scan = () => {
        scheduled = false;
        const jquery = (window as any).jQuery ?? (window as any).$;
        if (!jquery?.fn?.select2) return;

        document.querySelectorAll<HTMLSelectElement>('#app select').forEach((select) => {
            if (failed.has(select)) return;
            // Select2Input and Select2MultiInput initialize themselves.
            if (select.classList.contains('select2-hidden-accessible') && !initialized.has(select)) return;
            // The bundled theme calls .trim() on the raw class attribute.
            select.classList.add('app-searchable-select');

            const signature = Array.from(select.options, (option) => `${option.value}:${option.text}:${option.disabled}`).join('|');
            if (initialized.has(select)) {
                if (optionSignatures.get(select) !== signature) {
                    optionSignatures.set(select, signature);
                    jquery(select).trigger('change.select2');
                }
                return;
            }

            const dialog = select.closest('.modal') as HTMLElement | null;
            const hasEmptyOption = Array.from(select.options).some((option) => option.value === '');
            try {
                jquery(select).select2({
                    theme: 'bootstrap5',
                    width: '100%',
                    minimumResultsForSearch: 0,
                    placeholder: select.multiple ? ' ' : hasEmptyOption ? select.options[0]?.text : undefined,
                    allowClear: select.multiple || hasEmptyOption,
                    dropdownParent: dialog ? jquery(dialog) : jquery(document.body),
                    dir: select.closest('[dir]')?.getAttribute('dir') ?? document.documentElement.dir ?? 'ltr',
                });
            } catch (error) {
                // Leave this control usable as a native select, and continue
                // enhancing the rest of the page.
                failed.add(select);
                console.error('Could not enable search for a dropdown:', error);
                return;
            }
            initialized.add(select);
            optionSignatures.set(select, signature);

            // Select2 fires a jQuery event; Vue's v-model listens for the native event.
            jquery(select).on('select2:select.appSearch select2:unselect.appSearch select2:clear.appSearch', () => {
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    };

    const schedule = () => {
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(scan);
    };

    const observer = new MutationObserver(schedule);
    observer.observe(document.body, { childList: true, subtree: true });
    router.on('navigate', schedule);
    schedule();
}
