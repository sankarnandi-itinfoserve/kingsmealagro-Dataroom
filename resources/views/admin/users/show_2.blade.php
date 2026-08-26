@extends('admin.layouts.app')

@section('title', 'User Details')
@section('page_title', 'User Details')

@section('content')


<div class="profile-wrapper user-details">

    <div class="profile-card">

        {{-- Header --}}
        <div class="profile-header">
            <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : 'https://i.pravatar.cc/150' }}"
                 class="profile-avatar">

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
                <div class="info-label">User Type</div>
                <div class="info-value">{{ $user->user_type ?? 'N/A' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">
                    @if($user->active)
                        <span class="badge-active">Active</span>
                    @else
                        <span class="badge-inactive">Inactive</span>
                    @endif
                </div>
            </div>

        </div>

        {{-- Footer Actions --}}
        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Edit User</a>
        </div>

    </div>

</div>

@endsection