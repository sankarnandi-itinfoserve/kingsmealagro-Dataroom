{{-- Shared look for both the admin dashboard and the per-user dashboard —
     keep these two views visually identical; only their data differs. --}}
<style>
    /* prevent any child element from causing page-level horizontal scroll */
    .main-wrapper {
        overflow-x: hidden;
    }

    /* ── Section headings (outside card) ───────────────────── */
    .db-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }

    .db-section-sub {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* ── Project card (horizontal) ──────────────────────────── */
    .db-pcard {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1.5px solid #253447;
        background: #fff;
        text-decoration: none;
        transition: background .13s, box-shadow .13s;
    }

    .db-pcard:hover {
        background: #f8fafc;
        box-shadow: 0 4px 14px rgba(37, 52, 71, .1);
    }

    .db-pcard-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .db-pcard-body {
        flex: 1;
        min-width: 0;
    }

    .db-pcard-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        font-size: 10px;
        font-weight: 600;
        color: #15803d;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        padding: 2px 8px;
        border-radius: 99px;
    }

    .db-pcard-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #0a8d3a;
        flex-shrink: 0;
        animation: db-pulse-dot 1.4s ease-in-out infinite;
    }

    @keyframes db-pulse-dot {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: .35;
            transform: scale(1.4);
        }
    }

    .db-pcard-name {
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        white-space: normal;
        word-break: break-word;
        line-height: 1.3;
        margin-bottom: 2px;
    }

    .db-pcard-meta {
        font-size: 11px;
        color: #fff;
    }

    .db-pcard-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid rgba(255, 255, 255, .1);
        padding-top: 9px;
        margin-top: auto;
        font-size: 11px;
        color: #fff;
    }

    .db-pcard-foot i {
        color: #fff;
        transition: color .13s;
        font-size: 11px;
    }

    .db-pcard:hover .db-pcard-foot i {
        color: #253447;
    }

    /* ── Archive card (horizontal) ──────────────────────────── */
    .db-archive-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 14px 16px;
        border-radius: 12px;
        background: #0d9488;
        text-decoration: none;
        transition: opacity .13s, box-shadow .13s;
        box-shadow: 0 4px 16px rgba(48, 118, 206, .18);
    }

    .db-archive-card:hover {
        opacity: .92;
        box-shadow: 0 6px 22px rgba(48, 118, 206, .26);
    }

    /* Closed projects specifically — visually distinct (red) from the
               active-project cards above, which share this same card shell. */
    .db-archive-card-closed {
        background: #c0312a;
        box-shadow: 0 4px 16px rgba(192, 49, 42, .25);
    }

    .db-archive-card-closed:hover {
        box-shadow: 0 6px 22px rgba(192, 49, 42, .35);
    }

    /* Favorites card on the per-user dashboard — same shell, yellow instead
       of the "closed"/red treatment above, since nothing here is closed. */
    .db-archive-card-favorite {
        background: #d4a017;
        box-shadow: 0 4px 16px rgba(212, 160, 23, .25);
    }

    .db-archive-card-favorite:hover {
        box-shadow: 0 6px 22px rgba(212, 160, 23, .35);
    }

    .db-archive-card-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 12px;
    }

    .db-archive-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        flex-shrink: 0;
        background: rgba(255, 255, 255, .12);
        color: #fff;
        font-size: 17px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .db-archive-card-count {
        font-size: 28px;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .db-archive-card-sub {
        font-size: 11.5px;
        color: #fff;
        margin-top: 3px;
    }

    .db-archive-card-link {
        margin-top: auto;
        font-size: 11.5px;
        font-weight: 600;
        color: #fff;
        border-top: 1px solid rgba(255, 255, 255, .1);
        padding-top: 10px;
        display: flex;
        align-items: center;
    }

    .db-archive-card:hover .db-archive-card-link {
        color: #fff;
    }

    /* ── New Project CTA card (horizontal) ──────────────────── */
    .db-newproj-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1.5px dashed #253447;
        background: #fff;
        text-decoration: none;
        transition: background .13s, box-shadow .13s;
    }

    .db-newproj-card:hover {
        background: #f8fafc;
        box-shadow: 0 4px 14px rgba(37, 52, 71, .08);
    }

    .db-newproj-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 12px;
    }

    .db-newproj-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        flex-shrink: 0;
        background: #253447;
        color: #fff;
        font-size: 20px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .db-newproj-icon i {
        display: inline-block;
        line-height: 1;
    }

    .db-newproj-title {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .db-newproj-sub {
        font-size: 11px;
        color: #94a3b8;
    }

    .db-newproj-link {
        font-size: 11.5px;
        font-weight: 600;
        color: #253447;
        border-top: 1px solid #f1f5f9;
        padding-top: 9px;
        margin-top: auto;
    }

    .db-newproj-card:hover .db-newproj-link {
        color: #0ea5e9;
    }

    /* empty placeholder */
    .db-pcard-empty {
        border-style: dashed !important;
        background: #fafafa !important;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 8px;
        color: #94a3b8;
        font-size: 13px;
    }

    .db-pcard-empty i {
        font-size: 22px;
        opacity: .35;
    }

    /* ── Hero ────────────────────────────────────────────────── */
    .db-hero {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        background: #253447;
        box-shadow: 0 8px 32px rgba(37, 52, 71, .22);
    }

    .db-hero-bg {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
        background:
            radial-gradient(ellipse at 80% 0%, rgba(99, 102, 241, .25) 0%, transparent 60%),
            radial-gradient(ellipse at 10% 100%, rgba(14, 165, 233, .15) 0%, transparent 50%);
    }

    .db-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        padding: 26px 30px;
    }

    .db-hero-greet {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .db-hero-avatar {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        flex-shrink: 0;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .18);
        color: #fff;
        font-size: 22px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .db-hero-title {
        font-size: 20px;
        font-weight: 700;
        color: #fff;
    }

    .db-hero-title span {
        color: #0d9488;
    }

    .db-hero-sub {
        font-size: 12.5px;
        color: rgba(255, 255, 255, .45);
        margin-top: 3px;
    }

    .db-hero-stats {
        display: flex;
        align-items: center;
        gap: 0;
        background: rgba(255, 255, 255, .07);
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 14px;
        overflow: hidden;
    }

    .db-hstat {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 22px;
        transition: background .15s;
    }

    .db-hstat:hover {
        background: rgba(255, 255, 255, .05);
    }

    .db-hstat-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .db-hstat-sep {
        width: 1px;
        background: rgba(255, 255, 255, .1);
        align-self: stretch;
        margin: 8px 0;
    }

    .db-hstat-val {
        font-size: 21px;
        font-weight: 800;
        color: #fff;
        font-variant-numeric: tabular-nums;
        line-height: 1.15;
    }

    .db-hstat-lbl {
        font-size: 11px;
        color: rgba(255, 255, 255, .5);
        margin-top: 3px;
        white-space: nowrap;
    }

    /* ── Card shell ─────────────────────────────────────────── */
    .db-card {
        background: #fff;
        border-radius: 16px;
        padding: 22px 24px;
        box-shadow: 0 2px 16px rgba(37, 52, 71, .07);
        border: 1px solid #f1f5f9;
    }

    .db-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 18px;
        gap: 12px;
    }

    .db-card-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #1e293b;
    }

    .db-card-sub {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 3px;
    }

    .db-viewall {
        font-size: 12.5px;
        font-weight: 600;
        color: #253447;
        text-decoration: none;
        white-space: nowrap;
        padding: 5px 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        transition: background .13s, color .13s;
    }

    .db-viewall:hover {
        background: #253447;
        color: #fff;
        border-color: #253447;
    }

    /* ── Project scroller ───────────────────────────────────── */
    .db-proj-scroll-wrap {
        overflow: hidden;
    }

    .db-proj-scroller {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding-bottom: 6px;
        scrollbar-width: none;
    }

    .db-proj-scroller::-webkit-scrollbar {
        display: none;
    }

    .db-proj-card {
        flex-shrink: 0;
        width: 188px;
        display: flex;
        flex-direction: column;
        gap: 0;
        border-radius: 14px;
        border: 1.5px solid #f1f5f9;
        background: #f8fafc;
        padding: 16px;
        text-decoration: none;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .db-proj-card:hover {
        border-color: #253447;
        background: #fff;
        box-shadow: 0 6px 20px rgba(37, 52, 71, .11);
    }

    .db-proj-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .db-proj-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        color: #fff;
        font-size: 16px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .db-proj-badge {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        background: #dcfce7;
        color: #16a34a;
        padding: 3px 8px;
        border-radius: 99px;
    }

    .db-proj-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }

    .db-proj-meta {
        font-size: 11.5px;
        color: #94a3b8;
        margin-bottom: 12px;
    }

    .db-proj-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid #f1f5f9;
        padding-top: 10px;
        margin-top: auto;
    }

    .db-proj-ago {
        font-size: 11px;
        color: #94a3b8;
    }

    .db-proj-go {
        color: #cbd5e1;
        font-size: 12px;
        transition: color .15s;
    }

    .db-proj-card:hover .db-proj-go {
        color: #253447;
    }

    /* New project CTA */
    .db-proj-new {
        border: 1.5px dashed #cbd5e1 !important;
        background: #fff !important;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 10px;
    }

    .db-proj-new:hover {
        border-color: #253447 !important;
        background: #f8fafc !important;
    }

    .db-proj-new-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: #253447;
        color: #fff;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
    }

    .db-proj-new-title {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
    }

    .db-proj-new-sub {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 3px;
    }

    /* ── Quick Actions grid ─────────────────────────────────── */
    .db-actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding-top: 4px;
    }

    .db-action-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 20px 12px 16px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        text-decoration: none;
        transition: box-shadow .18s, transform .18s, background .18s;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
    }

    .db-action-tile:hover {
        background: #e8edf5;
        box-shadow: 0 6px 20px rgba(15, 23, 42, .11);
        transform: translateY(-2px);
    }

    .db-at-icon {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: #253447;
        color: #fff;
        box-shadow: 0 2px 6px rgba(37, 52, 71, .25);
    }

    .db-at-lbl {
        font-size: 12.5px;
        font-weight: 600;
        color: #1e293b;
        text-align: center;
        line-height: 1.3;
    }

    /* ── Recent Users / Recent Activity list ────────────────── */
    .db-user-list {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .db-user-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 6px 0;
        border-bottom: 1px solid #f8fafc;
    }

    .db-user-row:last-child {
        border-bottom: none;
    }

    .db-u-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        flex-shrink: 0;
        color: #000;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .db-u-meta {
        flex: 1;
        min-width: 0;
    }

    .db-u-name {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .db-u-name-link {
        text-decoration: none;
    }

    .db-u-name-link:hover .db-u-name {
        color: #2563eb;
        text-decoration: underline;
    }

    .db-u-email {
        font-size: 11.5px;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .db-u-status {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 99px;
        flex-shrink: 0;
    }

    .db-u-active {
        background: #16a34a;
        color: #fff;
    }

    .db-u-inactive {
        background: #dc2626;
        color: #fff;
    }

    /* Small action badge — used by the per-user dashboard's Recent Activity
       panel, same palette as the full Activity Logs page. */
    .db-act-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .db-act-badge.created {
        background: #dcfce7;
        color: #15803d;
    }

    .db-act-badge.updated {
        background: #fef3c7;
        color: #b45309;
    }

    .db-act-badge.deleted {
        background: #fee2e2;
        color: #b91c1c;
    }

    .db-act-badge.restored {
        background: #dbeafe;
        color: #1e40af;
    }

    .db-act-badge.downloaded {
        background: #e0f2fe;
        color: #0369a1;
    }

    .db-act-badge.login {
        background: #ecfdf5;
        color: #047857;
    }

    .db-act-badge.logout {
        background: #f1f5f9;
        color: #475569;
    }

    .db-act-badge.password_changed {
        background: #ede9fe;
        color: #6d28d9;
    }

    .db-act-desc {
        font-size: 12.5px;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .db-act-time {
        font-size: 11px;
        color: #94a3b8;
        flex-shrink: 0;
    }

    .db-act-empty {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #94a3b8;
        font-size: 13px;
        padding: 24px 4px;
    }

    .db-act-empty i {
        font-size: 18px;
        opacity: .5;
    }

    /* ── Files scroller ─────────────────────────────────────── */
    .db-scroll-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .12s, color .12s;
    }

    .db-scroll-btn:hover {
        background: #253447;
        color: #fff;
        border-color: #253447;
    }

    .db-files-wrap {
        overflow: hidden;
    }

    .db-files-track {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding: 4px 2px 8px;
        scrollbar-width: none;
    }

    .db-files-track::-webkit-scrollbar {
        display: none;
    }

    .db-file-card {
        flex-shrink: 0;
        width: 136px;
        display: flex;
        flex-direction: column;
        border-radius: 12px;
        border: 1.5px solid #f1f5f9;
        background: #f8fafc;
        overflow: hidden;
        text-decoration: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .db-file-card:hover {
        border-color: #253447;
        box-shadow: 0 4px 14px rgba(37, 52, 71, .1);
    }

    .db-file-thumb {
        height: 76px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        flex-shrink: 0;
    }

    .db-file-info {
        padding: 8px 10px 10px;
    }

    .db-file-name {
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .db-file-ext {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 2px;
        text-transform: uppercase;
    }

    .db-files-empty {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #94a3b8;
        font-size: 13px;
        padding: 24px 4px;
    }

    .db-files-empty i {
        font-size: 18px;
        opacity: .5;
    }

    /* ── Responsive ─────────────────────────────────────────── */
    @media (max-width: 900px) {
        .db-hero-inner {
            flex-direction: column;
            align-items: flex-start;
        }

        .db-hero-stats {
            width: 100%;
            justify-content: space-between;
        }

        .db-actions-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 600px) {
        .db-hero-inner {
            padding: 20px;
        }

        .db-hstat {
            padding: 10px 14px;
        }

        .db-hstat-val {
            font-size: 18px;
        }

        .db-actions-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }

        .db-action-tile {
            padding: 14px 6px 12px;
        }

        .db-at-icon {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .db-at-lbl {
            font-size: 11px;
        }
    }
</style>
