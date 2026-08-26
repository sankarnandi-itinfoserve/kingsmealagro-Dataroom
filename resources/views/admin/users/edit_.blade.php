@extends('admin.layouts.app')
@section('title', 'Edit User')
@section('page_title',  'Edit User')

@section('content')

<style>
.form-wrapper {
    max-width: 700px;
    margin: auto;
}

.card-box {
    background: #fff;
    border-radius: 14px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.form-label {
    font-weight: 500;
}

.avatar-preview {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: 10px;
}

.btn {
    border-radius: 8px;
}
</style>

<div class="form-wrapper create-user edit-user">

    <div class="card-box">

        <h4 class="mb-4">Edit User</h4>

        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" value="{{ old('fname', $user->fname) }}"
                        class="form-control @error('fname') is-invalid @enderror" required>
                    @error('fname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" value="{{ old('lname', $user->lname) }}"
                        class="form-control @error('lname') is-invalid @enderror" required>
                    @error('lname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="form-control @error('email') is-invalid @enderror" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Avatar --}}
            <div class="mb-3 ">
                <label class="form-label d-block">Profile Image</label>

                <input type="file" name="avatar" class="form-control" onchange="previewAvatar(event)">

                <img id="avatarPreview"
                     src="{{ $user->avatar ? asset('storage/'.$user->avatar) : 'https://i.pravatar.cc/150' }}"
                     class="avatar-preview">

                <small class="text-muted">Optional (JPG, PNG)</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror">
                <small class="text-muted">Leave blank to keep old password</small>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
                <button class="btn btn-primary">Update User</button>
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
        document.getElementById('avatarPreview').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endpush