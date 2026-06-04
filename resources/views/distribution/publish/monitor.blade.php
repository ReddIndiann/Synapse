<x-ui.page title="Publish Job Monitor" description="Real-time media distribution tracker.">
    <x-slot name="actions">
        <x-ui.button :href="route('distribution.publish.index')" variant="secondary" size="sm">Back to Queue</x-ui.button>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-ui.card>
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-5">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Publishing Asset</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Asset: <span class="font-medium text-slate-700">{{ $job->mediaAsset->title }}</span></p>
                </div>
                <div class="flex items-center gap-3">
                    <x-ui.badge variant="primary" class="!px-3 !py-1 text-sm font-semibold">{{ $job->distributionChannel->name }}</x-ui.badge>
                    <span id="status-badge" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset transition-all
                        @if($job->status === 'published') bg-emerald-50 text-emerald-700 ring-emerald-600/20
                        @elseif($job->status === 'failed') bg-rose-50 text-rose-700 ring-rose-600/20
                        @elseif($job->status === 'processing') bg-amber-50 text-amber-700 ring-amber-600/20
                        @else bg-slate-50 text-slate-700 ring-slate-600/20
                        @endif">
                        {{ ucfirst($job->status) }}
                    </span>
                </div>
            </div>

            <!-- Progress Tracker -->
            <div class="space-y-2 mb-6">
                <div class="flex justify-between text-sm font-semibold text-slate-700">
                    <span>Publishing Progress</span>
                    <span id="progress-text">0%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3.5 overflow-hidden border border-slate-200">
                    <div id="progress-bar" class="bg-indigo-600 h-full rounded-full transition-all duration-500 w-[0%]"></div>
                </div>
            </div>

            <!-- Live URL link (hidden initially unless published) -->
            <div id="live-url-container" class="mb-6 p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 flex items-center justify-between {{ $job->status === 'published' ? '' : 'hidden' }}">
                <div class="text-sm text-emerald-800">
                    <p class="font-semibold">Your media is live!</p>
                    <p class="text-xs text-emerald-600 mt-0.5">Published via Synapse distribution automation.</p>
                </div>
                <x-ui.button id="live-url-btn" :href="$job->published_url ?? '#'" target="_blank" variant="primary" size="sm" class="!bg-emerald-600 hover:!bg-emerald-700">
                    View Published Media
                </x-ui.button>
            </div>

            <!-- Terminal Console Logs -->
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700 flex justify-between">
                    <span>Connection Console Logs</span>
                    <span class="text-xs font-mono text-slate-400">polling active</span>
                </label>
                <div id="console-logs" class="bg-slate-950 text-slate-300 font-mono text-xs p-4 rounded-xl h-60 overflow-y-auto space-y-2 border border-slate-900 shadow-inner">
                    <!-- Logs dynamically appended -->
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- Polling JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const jobId = "{{ $job->id }}";
            const pollUrl = "{{ route('distribution.publish.status', $job->id) }}";
            const logsContainer = document.getElementById("console-logs");
            const progressBar = document.getElementById("progress-bar");
            const progressText = document.getElementById("progress-text");
            const statusBadge = document.getElementById("status-badge");
            const liveUrlContainer = document.getElementById("live-url-container");
            const liveUrlBtn = document.getElementById("live-url-btn");

            let lastLogCount = 0;
            let pollInterval = null;

            function updateUI(data) {
                // Update progress
                progressBar.style.width = data.progress + "%";
                progressText.innerText = data.progress + "%";

                // Update badge styling
                statusBadge.innerText = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                
                // Clear colors & set
                statusBadge.className = "inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset transition-all";
                if (data.status === 'published') {
                    statusBadge.classList.add("bg-emerald-50", "text-emerald-700", "ring-emerald-600/20");
                } else if (data.status === 'failed') {
                    statusBadge.classList.add("bg-rose-50", "text-rose-700", "ring-rose-600/20");
                } else if (data.status === 'processing') {
                    statusBadge.classList.add("bg-amber-50", "text-amber-700", "ring-amber-600/20");
                } else {
                    statusBadge.classList.add("bg-slate-50", "text-slate-700", "ring-slate-600/20");
                }

                // Update logs
                if (data.logs && data.logs.length > lastLogCount) {
                    for (let i = lastLogCount; i < data.logs.length; i++) {
                        const entry = data.logs[i];
                        const logLine = document.createElement("div");
                        logLine.className = "flex gap-2.5 py-0.5 border-b border-slate-900/40 last:border-0 hover:bg-slate-900/30 rounded px-1 transition-colors";
                        logLine.innerHTML = `
                            <span class="text-indigo-400 shrink-0">[${entry.formatted_time}]</span>
                            <span class="text-slate-200">${entry.message}</span>
                        `;
                        logsContainer.appendChild(logLine);
                    }
                    lastLogCount = data.logs.length;
                    logsContainer.scrollTop = logsContainer.scrollHeight;
                }

                // Update live url
                if (data.status === 'published' && data.published_url) {
                    liveUrlContainer.classList.remove("hidden");
                    liveUrlBtn.setAttribute("href", data.published_url);
                }

                // Stop polling if complete
                if (data.status === 'published' || data.status === 'failed') {
                    clearInterval(pollInterval);
                    const indicator = document.querySelector(".text-slate-400");
                    if (indicator) indicator.innerText = "simulation complete";
                }
            }

            // Start polling
            function startPolling() {
                // Initial poll immediately
                fetch(pollUrl)
                    .then(res => res.json())
                    .then(data => {
                        updateUI(data);
                        if (data.status !== 'published' && data.status !== 'failed') {
                            pollInterval = setInterval(() => {
                                fetch(pollUrl)
                                    .then(res => res.json())
                                    .then(data => updateUI(data))
                                    .catch(err => console.error("Polling error:", err));
                            }, 1200);
                        }
                    })
                    .catch(err => console.error("Initial load error:", err));
            }

            startPolling();
        });
    </script>
</x-ui.page>
