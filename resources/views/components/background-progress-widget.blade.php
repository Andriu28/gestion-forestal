{{-- Widget de progreso en background (reutilizable en cualquier vista) --}}
<div id="bg-progress-widget"
     class="fixed bottom-4 right-4 z-50 hidden w-80 rounded-xl shadow-2xl
            bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
            overflow-hidden transition-all duration-300">
    <div class="flex items-center justify-between px-4 py-3
                bg-gradient-to-r from-emerald-600 to-emerald-500
                dark:from-emerald-700 dark:to-emerald-600">
        <div class="flex items-center gap-2">
            <svg id="bg-progress-spinner" class="w-5 h-5 text-white animate-spin"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span id="bg-progress-title" class="text-sm font-semibold text-white">Procesando</span>
        </div>
        <button type="button" id="bg-progress-minimize"
                class="text-white/80 hover:text-white transition-colors p-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
        </button>
    </div>

    <div class="px-4 pt-3">
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
            <div id="bg-progress-bar"
                 class="bg-emerald-500 h-2 rounded-full transition-all duration-500 ease-out"
                 style="width: 0%;"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mt-1.5">
            <span id="bg-progress-text">Iniciando…</span>
            <span id="bg-progress-pct">0%</span>
        </div>
    </div>

    <div id="bg-progress-list"
         class="max-h-52 overflow-y-auto px-4 py-3 space-y-1.5 text-xs"></div>

    <div id="bg-progress-footer"
         class="hidden px-4 py-3 border-t border-gray-200 dark:border-gray-700
                bg-gray-50 dark:bg-gray-900/40">
        <div class="text-xs text-gray-700 dark:text-gray-300" id="bg-progress-summary"></div>
        <button type="button" id="bg-progress-close"
                class="mt-2 w-full py-1.5 text-xs font-medium rounded-md
                       bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600
                       text-gray-700 dark:text-gray-200 transition-colors">
            Cerrar
        </button>
    </div>
</div>

<script>
if (!window.BackgroundProgress) {
    window.BackgroundProgress = (() => {
        const STORAGE_KEY = 'bg_active_task';
        const MAX_404_GRACE = 8; // ~6.4s de gracia (800ms × 8)
        let pollInterval = null;
        let currentTaskId = null;
        let progressUrlTemplate = null;
        let consecutive404 = 0;

        const el = {
            widget:  () => document.getElementById('bg-progress-widget'),
            bar:     () => document.getElementById('bg-progress-bar'),
            text:    () => document.getElementById('bg-progress-text'),
            pct:     () => document.getElementById('bg-progress-pct'),
            list:    () => document.getElementById('bg-progress-list'),
            spinner: () => document.getElementById('bg-progress-spinner'),
            footer:  () => document.getElementById('bg-progress-footer'),
            summary: () => document.getElementById('bg-progress-summary'),
            title:   () => document.getElementById('bg-progress-title'),
        };

        function show()   { el.widget()?.classList.remove('hidden'); }
        function hide()   { el.widget()?.classList.add('hidden'); }
        function setTitle(t) { const x = el.title(); if (x) x.textContent = t; }

        function resetUI() {
            el.bar().style.width = '0%';
            el.text().textContent = 'Iniciando…';
            el.pct().textContent = '0%';
            el.list().innerHTML = '';
            el.footer().classList.add('hidden');
            el.spinner()?.classList.remove('hidden');
        }

        function escapeHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function statusIcon(status) {
            return ({ analyzed:'🔍', success:'✅', skipped:'⏭️', duplicated:'⚠️', error:'❌' }[status]) || '⏳';
        }

        function renderList(features) {
            const c = el.list();
            if (!c) return;
            c.innerHTML = '';
            Object.entries(features).forEach(([idx, f]) => {
                const row = document.createElement('div');
                row.className = 'flex items-center gap-2 py-0.5';
                const ha = (f.deforested_ha !== null && f.deforested_ha !== undefined)
                    ? `<span class="text-xs font-medium">${Number(f.deforested_ha).toFixed(2)} ha</span>` : '';
                row.innerHTML = `
                    <span class="flex-shrink-0">${statusIcon(f.status)}</span>
                    <span class="flex-1 truncate text-gray-700 dark:text-gray-300">${escapeHtml(f.name || `Feature #${idx}`)}</span>
                    ${ha}
                `;
                c.appendChild(row);
            });
            c.scrollTop = c.scrollHeight;
        }

        function update(state) {
            const total = state.total || 0;
            const current = state.current || 0;
            const pct = total > 0 ? Math.round((current / total) * 100) : 0;
            el.bar().style.width = `${pct}%`;
            el.pct().textContent = `${pct}%`;
            el.text().textContent = `${current} de ${total}`;
            renderList(state.feature_statuses || {});
            if (state.status === 'done') finish(state);
        }

        function finish(state) {
            stop();
            localStorage.removeItem(STORAGE_KEY);
            el.spinner()?.classList.add('hidden');
            el.bar().style.width = '100%';
            el.pct().textContent = '100%';
            el.text().textContent = 'Completado';

            const s = state.summary || {};
            const parts = [];
            if (s.imported)           parts.push(`✅ ${s.imported} importado(s)`);
            if (s.analyzed)           parts.push(`🔍 ${s.analyzed} analizado(s)`);
            if (s.skipped)            parts.push(`⏭️ ${s.skipped} omitido(s)`);
            if (s.duplicated?.length) parts.push(`⚠️ ${s.duplicated.length} duplicado(s)`);
            if (s.errors?.length)     parts.push(`❌ ${s.errors.length} error(es)`);

            el.summary().innerHTML = parts.length ? parts.join(' · ') : 'Sin cambios.';
            el.footer().classList.remove('hidden');
            if (!s.errors?.length) setTimeout(hide, 8000);
        }

        async function poll() {
            if (!currentTaskId || !progressUrlTemplate) return;
            const url = progressUrlTemplate.replace('__ID__', currentTaskId);
            try {
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (res.status === 404) {
                    // El servidor aún no ha creado el cache entry (timing normal
                    // al inicio de la importación). Damos un pequeño periodo de
                    // gracia antes de rendirnos.
                    consecutive404++;
                    if (consecutive404 >= MAX_404_GRACE) {
                        stop();
                        localStorage.removeItem(STORAGE_KEY);
                    }
                    return;
                }

                if (!res.ok) return;

                // Respuesta válida → reset del contador de gracia
                consecutive404 = 0;

                const data = await res.json();
                if (data.success) update(data.state);
            } catch (e) {
                console.warn('Polling error:', e);
            }
        }

        function start(taskId, options = {}) {
            currentTaskId = taskId;
            progressUrlTemplate = options.progressUrl || '/polygons/import/progress/__ID__';
            const noPolling = options.noPolling === true;
            consecutive404 = 0;

            setTitle(options.title || 'Procesando');
            show();
            resetUI();

            // Si no hay polling real, no guardamos en localStorage ni
            // arrancamos el setInterval. El caller actualiza manualmente.
            if (noPolling) return;

            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                taskId,
                progressUrl: progressUrlTemplate,
            }));

            if (pollInterval) clearInterval(pollInterval);
            poll();
            pollInterval = setInterval(poll, 800);
        }

        function stop() {
            if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
        }

        function attachIfActive() {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (!stored) return;
            try {
                const { taskId, progressUrl } = JSON.parse(stored);
                if (taskId) start(taskId, { progressUrl, title: 'Importando polígonos' });
            } catch (e) { localStorage.removeItem(STORAGE_KEY); }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('bg-progress-close')?.addEventListener('click', hide);
            document.getElementById('bg-progress-minimize')?.addEventListener('click', hide);
            attachIfActive();
        });

        return { start, stop, hide, show, update, setTitle };
    })();
}
</script>