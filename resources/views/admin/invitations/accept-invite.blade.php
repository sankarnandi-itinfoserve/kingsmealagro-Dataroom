@extends('layouts.guest')

@section('content')

<div class="card">
    <div class="card-body">

        
        <div class="text-center mb-4">
            <img src="{{ asset('frontend/images/logo.png') }}"
                 onerror="this.src='{{ asset('images/default-logo.png') }}'"
                 alt="Logo"
                 class="login-logo mb-2">
                 <h4>Complete Registration</h4>
          
        </div>

        <form method="POST" action="{{ route('invite.complete', $invitation->token) }}">
            @csrf
            <div class="row mb-3">
                <div class="col-lg-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" value="{{ old('fname') }}"
                        class="form-control @error('fname') is-invalid @enderror"  autofocus>
        
                    @error('fname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" value="{{ old('lname') }}"
                        class="form-control @error('lname') is-invalid @enderror"  autofocus>
        
                    @error('lname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" >
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control @error('password') is-invalid @enderror" >
            </div>
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div class="mb-3">
            <input type="checkbox" name="nda" value="1" id="ndaCheck" 
                class="@error('nda') is-invalid @enderror" required>
        
            <label for="ndaCheck">
                I accept 
                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ndaModal">
                    NDA Terms
                </a>
            </label>
        
            @error('nda')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

            <button class="btn btn-success">Complete Registration</button>

        </form>
        <!-- NDA Modal -->
<div class="modal fade" id="ndaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Non-Disclosure Agreement (NDA)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="font-size:14px; line-height:1.6;">

                <p><strong>1. Confidential Information</strong></p>
                <p>
                    You agree not to disclose any confidential information related to the platform, users, or projects.
                </p>

                <p><strong>2. Usage Restrictions</strong></p>
                <p>
                    Information provided must only be used for authorized purposes.
                </p>

                <p><strong>3. Data Protection</strong></p>
                <p>
                    You must ensure proper handling and protection of sensitive data.
                </p>

                <p><strong>4. Legal Compliance</strong></p>
                <p>
                    Any violation may result in termination and legal action.
                </p>

                <hr>

                <p class="text-muted">
                    By accepting, you agree to comply with all NDA terms.
                </p>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>

                <button type="button" class="btn btn-success" id="acceptNDA">
                    Accept NDA
                </button>
            </div>

        </div>
    </div>
</div>

    </div>
</div>

@endsection
@push('script')
<script>
document.getElementById('acceptNDA').addEventListener('click', function () {
    document.getElementById('ndaCheck').checked = true;

    let modal = bootstrap.Modal.getInstance(document.getElementById('ndaModal'));
    modal.hide();
});
</script>
@endpush