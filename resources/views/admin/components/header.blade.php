<nav class="navbar navbar-expand-lg bg-white shadow-sm px-4 py-3">

    <div class="container-fluid">

        {{-- LEFT SECTION --}}
        <div class="d-flex align-items-center gap-3">

            {{-- Sidebar Toggle (desktop/tablet) --}}
            <button id="toggleSidebar" class="btn btn-light d-none d-lg-inline-flex">
                <i class="fa fa-bars"></i>
            </button>

            {{-- Logo --}}
            <a href="#" class="navbar-brand fw-bold mb-0 me-0">
                {{ config('app.name', 'AdminPanel') }}
            </a>

            {{-- Dynamic Page Title --}}
            <span class="text-muted d-none d-md-inline">
                / @yield('page_title', 'Dashboard')
            </span>

        </div>


        {{-- RIGHT SECTION --}}
        <div class="d-flex align-items-center gap-3">



            <div class="dropdown">

                <button class="btn d-flex align-items-center gap-2" data-bs-toggle="dropdown">

                    @if (auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="rounded-circle"
                            width="35" height="35" style="object-fit:cover;" alt="User Avatar">
                    @else
                        <i class="fa-solid fa-circle-user"
                            style="font-size:35px;color:#94a3b8;line-height:1;flex-shrink:0;"></i>
                    @endif

                    <span class="d-none d-md-inline" style="text-align:left;line-height:1.2;">
                        <span
                            style="display:block;font-size:13px;font-weight:700;color:#1e293b;">{{ Auth::user()->full_name }}</span>
                        <span style="display:block;font-size:11px;color:#94a3b8;">{{ Auth::user()->email }}</span>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end hdr-user-menu">
                    <li>
                        <a class="hdr-menu-item" href="/profile">
                            <span class="hdr-menu-item-icon"><i class="fa-solid fa-user"></i></span>
                            Profile
                        </a>
                    </li>
                    <li>
                        <hr class="hdr-menu-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="hdr-menu-item hdr-menu-item-danger">
                                <span class="hdr-menu-item-icon"><i
                                        class="fa-solid fa-arrow-right-from-bracket"></i></span>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>

                <style>
                    .hdr-bell-btn {
                        position: relative;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 40px;
                        height: 40px;
                        border-radius: 10px;
                        color: #64748b;
                        font-size: 17px;
                        text-decoration: none;
                        transition: background .12s, color .12s;
                    }

                    .hdr-bell-btn:hover {
                        background: #f1f5f9;
                        color: #253447;
                    }

                    .hdr-bell-badge {
                        position: absolute;
                        top: 2px;
                        right: 2px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        min-width: 16px;
                        height: 16px;
                        padding: 0 4px;
                        border-radius: 50px;
                        background: #e53935;
                        color: #fff;
                        font-size: 10px;
                        font-weight: 700;
                        line-height: 1;
                        box-shadow: 0 0 0 2px #fff;
                    }

                    .hdr-user-menu {
                        min-width: 230px;
                        padding: 6px;
                        border: 1px solid #e9eef6;
                        border-radius: 14px;
                        box-shadow: 0 8px 28px rgba(37, 52, 71, .13);
                        margin-top: 8px !important;
                    }

                    .hdr-menu-divider {
                        margin: 4px 4px;
                        border-color: #f1f5f9;
                    }

                    .hdr-menu-item {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        width: 100%;
                        padding: 2px 10px;
                        border-radius: 9px;
                        font-size: 13px;
                        font-weight: 500;
                        color: #334155;
                        text-decoration: none;
                        background: none;
                        border: none;
                        cursor: pointer;
                        transition: background .12s, color .12s;
                        text-align: left;
                    }

                    .hdr-menu-item:hover {
                        background: #f1f5f9;
                        color: #253447;
                    }

                    .hdr-menu-item-danger {
                        color: #dc2626;
                    }

                    .hdr-menu-item-danger:hover {
                        background: #fff1f2;
                        color: #dc2626;
                    }

                    .hdr-menu-item-icon {
                        width: 28px;
                        height: 28px;
                        border-radius: 7px;
                        background: rgba(37, 52, 71, .07);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 12px;
                        flex-shrink: 0;
                    }

                    .hdr-menu-item-danger .hdr-menu-item-icon {
                        background: #fee2e2;
                    }
                </style>

            </div>
            {{-- Sidebar Toggle (mobile) --}}
            <button id="toggleSidebarMobile" class="btn btn-light d-lg-none">
                <i class="fa fa-bars"></i>
            </button>

        </div>

    </div>

</nav>
