<div class="ue-wrap">

    <div class="ue-header">
        <div class="ue-avatar"><i class="fa-solid fa-user-plus"></i></div>
        <div class="flex-grow-1">
            <h5 class="ue-title">Create New User</h5>
            <p class="ue-sub">Add a new user account</p>
        </div>
        <button type="button" class="ue-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form id="userForm" action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="ue-body">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="ue-label">First Name</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-user ue-input-icon"></i>
                        <input type="text" name="fname" id="cuFname" value="{{ old('fname') }}"
                            class="ue-input @error('fname') ue-input-err @enderror" required autocomplete="off">
                    </div>
                    @error('fname') <span class="ue-err">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="ue-label">Last Name</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-user ue-input-icon"></i>
                        <input type="text" name="lname" id="cuLname" value="{{ old('lname') }}"
                            class="ue-input @error('lname') ue-input-err @enderror" required autocomplete="off">
                    </div>
                    @error('lname') <span class="ue-err">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="ue-label">Username</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-at ue-input-icon"></i>
                        <input type="text" name="username" id="cuUsername" value="{{ old('username') }}"
                            class="ue-input @error('username') ue-input-err @enderror" required autocomplete="off">
                    </div>
                    @error('username') <span class="ue-err">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="ue-label">Email</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-envelope ue-input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="ue-input @error('email') ue-input-err @enderror" required>
                    </div>
                    @error('email') <span class="ue-err">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                {{-- Role --}}
                <div class="col-md-6">
                    <label class="ue-label">Select Role</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-user-shield ue-input-icon"></i>
                        <select name="role" class="ue-input ue-select @error('role') ue-input-err @enderror" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}"
                                    {{ old('role') == $role->name ? 'selected' : '' }}
                                    {{ in_array($role->name, ['admin', 'super-admin']) ? 'disabled' : '' }}>
                                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('role') <span class="ue-err">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="row g-3 mb-1">
                <div class="col-md-6">
                    <label class="ue-label">Password</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-lock ue-input-icon"></i>
                        <input type="password" name="password" id="cuPassword"
                            class="ue-input ue-input-has-toggle @error('password') ue-input-err @enderror" required>
                        <button type="button" class="ue-toggle-pass" tabindex="-1"
                            onclick="toggleUePasswordField('cuPassword', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('password') <span class="ue-err">{{ $message }}</span> @enderror
                </div>

                {{-- Avatar --}}
                <div class="col-md-6">
                    <label class="ue-label">Profile Image</label>
                    <input type="file" name="avatar" class="ue-file" onchange="previewAvatar(event)">
                    <img id="avatarPreview" class="ue-avatar-preview">
                    <small class="ue-hint">Optional (JPG, PNG, max 2MB)</small>
                </div>
            </div>

        </div>

        <div class="ue-footer">
            <button type="button" class="ue-btn-cancel" onclick="closeModal()">Cancel</button>
            <button type="button" class="ue-btn-save" onclick="submitForm()">
                <i class="fa-solid fa-check me-1"></i> Save
            </button>
        </div>

    </form>

</div>

<script>
    (function() {
        var fname = document.getElementById('cuFname');
        var lname = document.getElementById('cuLname');
        var username = document.getElementById('cuUsername');
        if (!fname || !lname || !username) return;

        function slugify(value) {
            return value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        }

        var lastAuto = username.value;

        function updateUsername() {
            var generated = [slugify(fname.value), slugify(lname.value)].filter(Boolean).join('_');
            if (username.value === '' || username.value === lastAuto) {
                username.value = generated;
            }
            lastAuto = generated;
        }

        fname.addEventListener('input', updateUsername);
        lname.addEventListener('input', updateUsername);
    })();

    function toggleUePasswordField(inputId, btn) {
        var input = document.getElementById(inputId);
        if (!input) return;
        var icon = btn.querySelector('i');
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        icon.classList.toggle('fa-eye', showing);
        icon.classList.toggle('fa-eye-slash', !showing);
    }
</script>

<style>
    .ue-wrap {
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 3.5rem);
        overflow: hidden;
    }

    .ue-wrap > form {
        display: contents;
    }

    /* ── Header ── */
    .ue-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 24px;
        background: linear-gradient(135deg, #253447 0%, #1a2737 100%);
        border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;
        flex-shrink: 0;
    }

    .ue-avatar {
        width: 44px;
        height: 44px;
        border-radius: 11px;
        background: rgba(255, 255, 255, .14);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        flex-shrink: 0;
        letter-spacing: .5px;
    }

    .ue-title {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 2px;
    }

    .ue-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, .6);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ue-close {
        background: rgba(255, 255, 255, .12);
        border: none;
        color: #fff;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s;
        flex-shrink: 0;
    }

    .ue-close:hover {
        background: rgba(255, 255, 255, .22);
    }

    /* ── Body ── */
    .ue-body {
        padding: 22px 24px 6px;
        overflow-y: auto;
        flex: 1 1 auto;
        min-height: 0;
    }

    .ue-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }

    .ue-input-wrap {
        position: relative;
    }

    .ue-input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #94a3b8;
        pointer-events: none;
    }

    .ue-input {
        width: 100%;
        height: 42px;
        padding: 0 14px 0 34px;
        border: 1.5px solid #dbe4f0;
        border-radius: 9px;
        font-size: 13px;
        color: #1e293b;
        background: #f9fafb;
        outline: none;
        transition: border-color .15s, box-shadow .15s, background .15s;
        appearance: none;
    }

    .ue-input:focus {
        border-color: #253447;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 52, 71, .08);
    }

    .ue-input-err {
        border-color: #dc2626 !important;
    }

    .ue-input-has-toggle {
        padding-right: 38px;
    }

    .ue-toggle-pass {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 4px 6px;
        font-size: 13px;
        color: #94a3b8;
        cursor: pointer;
        line-height: 1;
    }

    .ue-toggle-pass:hover {
        color: #253447;
    }

    .ue-select {
        cursor: pointer;
    }

    .ue-err {
        display: block;
        font-size: 11.5px;
        color: #dc2626;
        margin-top: 4px;
    }

    .ue-file {
        display: block;
        width: 100%;
        font-size: 12.5px;
        color: #1e293b;
        background: #f9fafb;
        border: 1.5px solid #dbe4f0;
        border-radius: 9px;
        padding: 8px 12px;
        margin-bottom: 6px;
    }

    .ue-avatar-preview {
        display: none;
        width: 56px;
        height: 56px;
        border-radius: 10px;
        object-fit: cover;
        margin: 6px 0;
    }

    .ue-hint {
        display: block;
        font-size: 11px;
        color: #94a3b8;
    }

    /* ── Footer ── */
    .ue-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 24px;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
        border-radius: 0 0 calc(0.5rem - 1px) calc(0.5rem - 1px);
        flex-shrink: 0;
    }

    .ue-btn-cancel {
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 9px;
        cursor: pointer;
        transition: background .15s;
    }

    .ue-btn-cancel:hover {
        background: #f1f5f9;
    }

    .ue-btn-save {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #253447, #1a2737);
        border: none;
        border-radius: 9px;
        cursor: pointer;
        box-shadow: 0 3px 12px rgba(37, 52, 71, .25);
        transition: opacity .15s, transform .1s;
    }

    .ue-btn-save:hover {
        opacity: .9;
        transform: translateY(-1px);
    }

    .ue-btn-save:disabled {
        opacity: .75;
        cursor: not-allowed;
        pointer-events: none;
        transform: none;
    }

    .ue-btn-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-right: 7px;
        vertical-align: -2px;
        border: 2px solid rgba(255, 255, 255, .35);
        border-top-color: #fff;
        border-radius: 50%;
        animation: usrSpin .7s linear infinite;
    }

    /* ── Mobile ── */
    @media (max-width: 575.98px) {
        .ue-header {
            padding: 14px 16px;
            gap: 10px;
        }

        .ue-avatar {
            width: 38px;
            height: 38px;
            font-size: 14px;
        }

        .ue-title {
            font-size: 14px;
        }

        .ue-sub {
            font-size: 11px;
        }

        .ue-body {
            padding: 16px 16px 4px;
        }

        .ue-footer {
            padding: 12px 16px;
            flex-direction: column-reverse;
        }

        .ue-footer button {
            width: 100%;
            justify-content: center;
        }
    }
</style>
