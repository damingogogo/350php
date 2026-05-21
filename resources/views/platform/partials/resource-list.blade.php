<div class="list">
    @forelse($items as $resource)
        <a class="list-row" href="{{ route('platform.resources.show', $resource) }}">
            <div>
                <strong>{{ $resource->title }}</strong>
                <p class="small muted">{{ $resource->course_name ?: optional($resource->category)->name }} · {{ optional($resource->user)->nickname }}</p>
            </div>
            <span class="badge">{{ $resource->file_type_label }}</span>
        </a>
    @empty
        <p class="muted">暂无数据</p>
    @endforelse
</div>
