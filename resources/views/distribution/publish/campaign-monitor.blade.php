<x-ui.page title="Campaign Monitor" description="Track multi-platform publish progress.">
    <x-slot name="actions">
        <x-ui.button :href="route('distribution.publish.index')" variant="secondary" size="sm">Back to Queue</x-ui.button>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-ui.card>
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-border pb-4 mb-5">
                <div>
                    <h3 class="text-lg font-semibold">{{ $campaign->mediaAsset->title }}</h3>
                    <p class="text-sm text-muted mt-0.5" id="progress-summary">Loading progress...</p>
                </div>
                <x-ui.badge id="campaign-status-badge" variant="warning">{{ $campaign->status }}</x-ui.badge>
            </div>

            @if ($marketingBudget)
                <div class="mb-5 p-4 rounded-xl border border-border bg-surface">
                    <p class="text-sm font-semibold">Marketing Budget</p>
                    <p class="text-xs text-muted mt-1">
                        Spent {{ number_format($marketingBudget['spent'], 2) }} / {{ number_format($marketingBudget['limit'], 2) }} GHS
                        — {{ number_format($marketingBudget['remaining'], 2) }} remaining
                    </p>
                </div>
            @endif

            <div id="jobs-container" class="space-y-4"></div>
        </x-ui.card>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pollUrl = @json(route('distribution.publish.campaign.status', $campaign));
            const container = document.getElementById('jobs-container');
            const summary = document.getElementById('progress-summary');
            const badge = document.getElementById('campaign-status-badge');
            let pollInterval;

            function renderJobs(data) {
                summary.textContent = data.progress_summary + ' channels published';
                badge.textContent = data.campaign_status;
                container.innerHTML = '';

                data.jobs.forEach(job => {
                    const card = document.createElement('div');
                    card.className = 'p-4 rounded-xl border border-border bg-surface';
                    card.innerHTML = `
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <span class="font-semibold text-sm">${job.channel}</span>
                            <span class="text-xs font-semibold uppercase">${job.status}</span>
                        </div>
                        <div class="w-full bg-[var(--bg2)] rounded-full h-2 mb-2">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width:${job.progress}%"></div>
                        </div>
                        ${job.published_url ? `<a href="${job.published_url}" target="_blank" class="text-xs text-indigo-600 underline">View published</a>` : ''}
                        <div class="mt-2 flex gap-2">
                            <a href="/distribution/publish/${job.id}/monitor" class="text-xs text-muted underline">Job logs</a>
                        </div>
                    `;
                    container.appendChild(card);
                });

                const allDone = data.jobs.every(j => j.status === 'published' || j.status === 'failed');
                if (allDone && pollInterval) clearInterval(pollInterval);
            }

            function poll() {
                fetch(pollUrl).then(r => r.json()).then(renderJobs).catch(console.error);
            }

            poll();
            pollInterval = setInterval(poll, 1500);
        });
    </script>
</x-ui.page>
