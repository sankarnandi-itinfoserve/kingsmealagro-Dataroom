@extends('admin.layouts.app')

@section('title', 'Create User')
@section('page_title', 'Create User')

@section('content')



<div class="form-wrapper create-user">

    <div class="card-box">

        <h4 class="mb-4">Create New User</h4>

        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" value="{{ old('fname') }}"
                        class="form-control @error('fname') is-invalid @enderror" required>
                    @error('fname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" value="{{ old('lname') }}"
                        class="form-control @error('lname') is-invalid @enderror" required>
                    @error('lname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" value="{{ old('username') }}"
                    class="form-control @error('username') is-invalid @enderror" required>
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Avatar --}}
            <div class="mb-3">
                <label class="form-label d-block">Profile Image</label>

                <input type="file" name="avatar" class="form-control" onchange="previewAvatar(event)">

                <img id="avatarPreview" class="avatar-preview">

                <small class="text-muted">Optional (JPG, PNG, max 2MB)</small>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-success">Save User</button>
            </div>

        </form>

    </div>

</div>

@endsection

@push('script')
<script>
function previewAvatar(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const img = document.getElementById('avatarPreview');
        img.src = reader.result;
        img.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endpush