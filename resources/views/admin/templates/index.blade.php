@extends('admin.layouts.app')

@section('title', 'Templates')
@section('page_title', 'Templates')

@section('content')

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">Templates</h4>
            <p class="text-muted small mb-0">
                Reusable M&amp;A folder structures. Create a template from an existing project to speed up new deal setup.
            </p>
        </div>
        <button type="button"
                class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                id="toggleCreateForm">
            <i class="fa-solid fa-plus"></i>
            New Template
        </button>
    </div>

    {{-- Standard M&A template names (informational) --}}
    <div class="alert alert-info border-0 small d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="fa-solid fa-circle-info mt-1 flex-shrink-0"></i>
        <div>
            <strong>Standard M&amp;A folder structure</strong> — pre-loaded category names:
            <span class="fw-medium">Process Documents</span>,
            <span class="fw-medium">Financial Information</span>,
            <span class="fw-medium">Employee Information</span>,
            <span class="fw-medium">Operations</span>,
            <span class="fw-medium">Organizational &amp; Corporate Information</span>.
        </div>
    </div>

    {{-- Create template form (hidden by default) --}}
    <div class="card border-0 shadow-sm mb-4 d-none" id="createTemplateForm">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-layer-group text-primary"></i>
            <span class="fw-semibold text-dark">Create New Template</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('templates.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="tpl_name" class="form-label fw-medium small">
                            Template Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="tpl_name"
                               name="name"
                               class="form-control form-control-sm @error('name') is-invalid @enderror"
                               placeholder="e.g. Standard M&A Due Diligence"
                               value="{{ old('name') }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tpl_source" class="form-label fw-medium small">Source Project</label>
                        <select id="tpl_source"
                                name="source_folder_id"
                                class="form-select form-select-sm @error('source_folder_id') is-invalid @enderror">
                            <option value="">— No source project —</option>
                            @if (!empty($folders))
                                @foreach ($folders as $folder)
                                    <option value="{{ $folder->id }}"
                                            {{ old('source_folder_id') == $folder->id ? 'selected' : '' }}>
                                        {{ $folder->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('source_folder_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="tpl_description" class="form-label fw-medium small">Description</label>
                        <textarea id="tpl_description"
                                  name="description"
                                  class="form-control form-control-sm @error('description') is-invalid @enderror"
                                  rows="2"
                                  placeholder="Brief description of this template's purpose">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                id="cancelCreateForm">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save Template
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Templates table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if (!empty($templates) && $templates->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-muted">Name</th>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-muted">Description</th>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-muted">Created By</th>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-muted">Created At</th>
                                <th class="px-4 py-3 text-uppercase small fw-semibold text-muted text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($templates as $template)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="fw-medium text-dark">{{ $template->name }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-muted small">
                                        {{ $template->description ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-muted small">
                                        {{ $template->creator->name ?? ($template->created_by ?? '—') }}
                                    </td>
                                    <td class="px-4 py-3 text-muted small" style="font-variant-numeric: tabular-nums; white-space: nowrap;">
                                        {{ isset($template->created_at) ? \Carbon\Carbon::parse($template->created_at)->format('d M Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <form method="POST"
                                              action="{{ route('templates.destroy', $template->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete template \'{{ addslashes($template->name) }}\'? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1">
                                                <i class="fa-solid fa-trash-can"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (method_exists($templates, 'links'))
                    <div class="px-4 py-3 border-top">
                        {{ $templates->links() }}
                    </div>
                @endif

            @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                    <i class="fa-regular fa-copy fa-3x mb-3 opacity-40"></i>
                    <p class="fw-medium mb-0">No templates yet.</p>
                    <p class="small mt-1">Create a template to quickly replicate folder structures for new projects.</p>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('script')
<script>
(function () {
    var toggleBtn   = document.getElementById('toggleCreateForm');
    var cancelBtn   = document.getElementById('cancelCreateForm');
    var formCard    = document.getElementById('createTemplateForm');

    if (!toggleBtn || !formCard) return;

    toggleBtn.addEventListener('click', function () {
        formCard.classList.toggle('d-none');
        var isVisible = !formCard.classList.contains('d-none');
        toggleBtn.innerHTML = isVisible
            ? '<i class="fa-solid fa-xmark"></i> Cancel'
            : '<i class="fa-solid fa-plus"></i> New Template';
        if (isVisible) {
            formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            formCard.classList.add('d-none');
            toggleBtn.innerHTML = '<i class="fa-solid fa-plus"></i> New Template';
        });
    }

    {{-- Auto-open form if there was a validation error --}}
    @if ($errors->any())
    formCard.classList.remove('d-none');
    toggleBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> Cancel';
    @endif
})();
</script>
@endpush
