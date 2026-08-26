<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')
    
        <div class="row g-3">
    
            <!-- First Name -->
            <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text"
                       name="fname"
                       class="form-control"
                       value="{{ old('fname', $user->fname) }}"
                       required>
                @error('fname')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Last Name -->
            <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text"
                       name="lname"
                       class="form-control"
                       value="{{ old('lname', $user->lname) }}"
                       required>
                @error('lname')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Email — never editable here, regardless of account type -->
            <div class="col-md-12">
                <label class="form-label">Email</label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ $user->email }}"
                       disabled>
            </div>
    
        </div>
    
        <div class="mt-4">
            <button class="btn btn-primary">
                <i class="fa-solid fa-save me-1"></i> Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success ms-2">Saved successfully</span>
            @endif
        </div>
    </form>
</section>
