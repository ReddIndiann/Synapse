<x-ui.page title="Publish Queue" description="Multi-platform distribution campaigns grouped by media.">
    <x-slot name="actions">
        <x-ui.button :href="route('distribution.publish.create')" variant="primary" size="sm">Queue Publish</x-ui.button>
    </x-slot>

    <x-ui.table-shell>
        <thead>
            <tr>
                <th class="py-2 font-medium">Media</th>
                <th class="py-2 font-medium">Channels</th>
                <th class="py-2 font-medium">Progress</th>
                <th class="py-2 font-medium">Status</th>
                <th class="py-2 font-medium">Scheduled</th>
                <th class="py-2 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($campaigns as $campaign)
                @php
                    $jobs = $campaign->publishJobs;
                    $published = $jobs->where('status', 'published')->count();
                    $total = $jobs->count();
                @endphp
                <tr>
                    <td class="py-3 font-medium">{{ $campaign->mediaAsset->title }}</td>
                    <td class="py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach ($jobs as $job)
                                <x-ui.badge variant="{{ $job->status === 'published' ? 'success' : ($job->status === 'failed' ? 'danger' : 'primary') }}">
                                    {{ $job->distributionChannel->name }}
                                </x-ui.badge>
                            @endforeach
                        </div>
                    </td>
                    <td class="py-3 text-sm">{{ $published }}/{{ $total }} published</td>
                    <td class="py-3">
                        <x-ui.badge variant="{{ $campaign->status === 'completed' ? 'success' : ($campaign->status === 'failed' ? 'danger' : 'warning') }}">
                            {{ $campaign->status }}
                        </x-ui.badge>
                    </td>
                    <td class="py-3 text-sm">{{ $campaign->scheduled_at?->format('M j, Y H:i') ?? 'Immediate' }}</td>
                    <td class="py-2 flex items-center gap-2">
                        <x-ui.button :href="route('distribution.publish.campaign', $campaign)" variant="link" size="sm" class="!text-indigo-600">Monitor</x-ui.button>
                        <form method="POST" action="{{ route('distribution.publish.campaign.destroy', $campaign) }}" class="inline">@csrf @method('DELETE')
                            <x-ui.button type="submit" variant="link" size="sm" class="!text-red-600" data-confirm="Remove entire campaign?">Remove</x-ui.button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-ui.empty-state title="Queue is empty"><x-slot name="action"><x-ui.button :href="route('distribution.publish.create')" variant="primary" size="sm">Queue publish</x-ui.button></x-slot></x-ui.empty-state></td></tr>
            @endforelse
        </tbody>
        <x-slot name="footer">{{ $campaigns->links() }}</x-slot>
    </x-ui.table-shell>
</x-ui.page>
