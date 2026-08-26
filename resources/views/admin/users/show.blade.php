<div class="profile-wrapper user-details">

    <div class="profile-card">

        {{-- Header --}}
        <div class="profile-header">
            @if ($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" class="profile-avatar" alt="{{ $user->displayName }}">
            @else
                <i class="fa-solid fa-circle-user" style="font-size:80px;color:#94a3b8;flex-shrink:0;line-height:1;"></i>
            @endif

            <div>
                <div class="profile-name">{{ $user->displayName }}</div>
                <div class="profile-email">{{ $user->email }}</div>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="info-grid">

            <div class="info-item">
                <div class="info-label">First Name</div>
                <div class="info-value">{{ $user->fname }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Last Name</div>
                <div class="info-value">{{ $user->lname }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Username</div>
                <div class="info-value">{{ $user->username }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Role</div>
                <div class="info-value">{{ $user->role ?? 'N/A' }}</div>
            </div>

<div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">
                    @if(is_null($user->deleted_at))
                        <span class="badge-active">Active</span>
                    @else
                        <span class="badge-inactive">Inactive</span>
                    @endif
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>        
        </div>

    </div>

</div>