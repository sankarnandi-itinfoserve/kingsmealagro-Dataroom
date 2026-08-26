<div class="ue-wrap">

    <div class="ue-header">
        <div class="flex-grow-1">
            <h5 class="ue-title">Edit User</h5>
            <p class="ue-sub">{{ $user->fname }} {{ $user->lname }} &middot; {{ $user->email }}</p>
        </div>
        <button type="button" class="ue-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="ue-photo-block">
        <label for="avatarInput" class="ue-photo-wrap">
            <img id="ueAvatarPreview" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}"
                class="ue-photo-preview" style="{{ $user->avatar ? '' : 'display:none' }}" alt="">
            <i id="ueAvatarDefault" class="fa-solid fa-circle-user ue-photo-preview ue-photo-default"
                style="{{ $user->avatar ? 'display:none' : '' }}"></i>
            <span class="ue-photo-edit"><i class="fa-solid fa-pencil"></i></span>
        </label>
    </div>

    <form id="userForm" action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="ue-body">

            <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/png,image/jpeg"
                onchange="previewAvatar(event, 'ueAvatarPreview', 'ueAvatarDefault')">
            @error('avatar')
                <span class="ue-err d-block mb-3">{{ $message }}</span>
            @enderror

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="ue-label">First Name</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-user ue-input-icon"></i>
                        <input type="text" name="fname" value="{{ old('fname', $user->fname) }}"
                            class="ue-input @error('fname') ue-input-err @enderror" required>
                    </div>
                    @error('fname')
                        <span class="ue-err">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="ue-label">Last Name</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-user ue-input-icon"></i>
                        <input type="text" name="lname" value="{{ old('lname', $user->lname) }}"
                            class="ue-input @error('lname') ue-input-err @enderror" required>
                    </div>
                    @error('lname')
                        <span class="ue-err">{{ $message }}</span>
                    @enderror
                </div>
            </div>


            {{-- Role --}}
            @php $currentRole = $user->getRoleNames()->first(); @endphp
            @can('manage roles_permissions')
                <div class="mb-1">
                    <label class="ue-label">Role</label>
                    <div class="ue-input-wrap">
                        <i class="fa-solid fa-user-shield ue-input-icon"></i>
                        @if (in_array($currentRole, ['admin', 'super-admin']))
                            <input type="text" class="ue-input"
                                value="{{ ucwords(str_replace('-', ' ', $currentRole)) }}" disabled>
                        @else
                            <select name="role" class="ue-input ue-select @error('role') ue-input-err @enderror">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ old('role', $currentRole) === $role->name ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @error('role')
                        <span class="ue-err">{{ $message }}</span>
                    @enderror
                </div>
            @endcan

        </div>

        <div class="ue-footer">
            <button type="button" class="ue-btn-cancel" onclick="closeModal()">Cancel</button>
            <button type="button" class="ue-btn-save" onclick="submitForm()">
                <i class="fa-solid fa-check me-1"></i> Save Changes
            </button>
        </div>

    </form>

</div>

<style>
    .ue-wrap {
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 3.5rem);
        overflow: hidden;
    }

    .ue-wrap>form {
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

    /* ── Photo ── */
    .ue-photo-block {
        display: flex;
        justify-content: center;
        padding: 22px 24px 0;
        flex-shrink: 0;
    }

    .ue-photo-wrap {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .ue-photo-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #dbe4f0;
    }

    .ue-photo-default {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef2f7;
        color: #94a3b8;
        font-size: 64px;
    }

    .ue-photo-edit {
        position: absolute;
        right: -2px;
        bottom: -2px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #253447, #1a2737);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        border: 2px solid #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .18);
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

    .ue-input:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .ue-input-err {
        border-color: #dc2626 !important;
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
