<x-ui.page title="Publish Queue" description="Multi-platform distribution — API connectors in Phase 4.">
    <x-slot name="actions">
        <x-ui.button :href="route('distribution.publish.create')" variant="primary" size="sm">Queue Publish</x-ui.button>
    </x-slot>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-2 font-medium">Media</th>
                <th class="py-2 font-medium">Channel</th>
                <th class="py-2 font-medium">Status</th>
                <th class="py-2 font-medium">Scheduled</th>
                <th class="py-2 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jobs as $job)
                <tr>
                    <td class="py-3">{{ $job->mediaAsset->title }}</td>
                    <td class="py-3"><x-ui.badge variant="primary">{{ $job->distributionChannel->name }}</x-ui.badge></td>
                    <td class="py-3"><x-ui.badge variant="{{ $job->status === 'published' ? 'success' : ($job->status === 'failed' ? 'danger' : 'warning') }}">{{ $job->status }}</x-ui.badge></td>
                    <td class="py-3 text-sm">{{ $job->scheduled_at?->format('M j, Y H:i') ?? 'Immediate' }}</td>
                    <td class="py-2 flex items-center gap-2">
                        @if($job->status === 'processing' || $job->status === 'published' || $job->status === 'failed')
                            <x-ui.button :href="route('distribution.publish.monitor', $job)" variant="link" size="sm" class="!text-indigo-600">Monitor</x-ui.button>
                        @endif
                        <form method="POST" action="{{ route('distribution.publish.destroy', $job) }}" class="inline">@csrf @method('DELETE')
                            <x-ui.button type="submit" variant="link" size="sm" class="!text-red-600" data-confirm="Remove from queue?">Remove</x-ui.button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-ui.empty-state title="Queue is empty"><x-slot name="action"><x-ui.button :href="route('distribution.publish.create')" variant="primary" size="sm">Queue publish</x-ui.button></x-slot></x-ui.empty-state></td></tr>
            @endforelse
        </tbody>
        <x-slot name="footer">{{ $jobs->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
