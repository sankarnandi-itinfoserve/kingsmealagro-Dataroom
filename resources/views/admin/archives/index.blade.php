@extends('admin.layouts.app')

@section('title', 'Archives')
@section('page_title', 'Archives')

@section('content')

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">Archives</h4>
            <p class="text-muted small mb-0">Archived projects can be restored at any time.</p>
        </div>
        <a href="{{ route('projects.index') }}"
           class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Projects
        </a>
    </div>

    {{-- Archives table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if (!empty($archives) && $archives->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-muted">Name</th>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-muted">Archived Date</th>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-muted text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($archives as $project)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="fw-medium text-dark">{{ $project->name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-muted small" style="font-variant-numeric: tabular-nums; white-space: nowrap;">
                                        {{ isset($project->deleted_at)
                                            ? \Carbon\Carbon::parse($project->deleted_at)->format('d M Y')
                                            : (isset($project->updated_at)
                                                ? \Carbon\Carbon::parse($project->updated_at)->format('d M Y')
                                                : '—') }}
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <form method="POST"
                                              action="{{ route('projects.restore', $project->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Restore \'{{ addslashes($project->name) }}\' from archives?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1">
                                                <i class="fa-solid fa-rotate-left"></i>
                                                Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if (method_exists($archives, 'links'))
                    <div class="px-4 py-3 border-top">
                        {{ $archives->links() }}
                    </div>
                @endif

            @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                    <i class="fa-regular fa-folder-open fa-3x mb-3 opacity-40"></i>
                    <p class="fw-medium mb-0">No archived projects.</p>
                    <p class="small mt-1">Projects you archive will appear here.</p>
                </div>
            @endif
        </div>
    </div>

@endsection
