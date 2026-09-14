@extends('layouts.app')

@section('content')
<div class="container py-4" style="font-family:'Poppins', sans-serif;">
  <style>
    [data-filter],
    [data-mark-all],
    [data-clear] {
      position: relative;
      z-index: 2;
      pointer-events: auto;
    }
  </style>
  @php
    $notifs = $notifs ?? collect();
  @endphp

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="fw-semibold mb-1">Notifikasi</h4>
      <div class="text-muted small">
        Info terbaru terkait permintaan pelayanan Anda.
        <span class="ms-1">Belum dibaca: <strong data-unread-count>{{ (int) ($unreadCount ?? 0) }}</strong></span>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-primary" data-filter="all">Semua</button>
          <button type="button" class="btn btn-sm btn-outline-primary" data-filter="baru">
            Belum Dibaca (<span data-unread-count>{{ (int) ($unreadCount ?? 0) }}</span>)
          </button>
        </div>
        <div class="d-flex gap-2">
          <button
            type="button"
            class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center"
            data-mark-all
            data-mark-all-url="{{ route('notifikasi.mark-all-read') }}"
            title="Tandai semua dibaca"
          >
            <i class="bi bi-check2-circle"></i>
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center"
            data-clear
            data-clear-url="{{ route('notifikasi.clear-all') }}"
            title="Hapus semua"
          >
            <i class="bi bi-trash3"></i>
          </button>
        </div>
      </div>

      <div class="list-group" data-list>
        @forelse($notifs as $notif)
          @php
            $isUnread = empty($notif->read_at);
            $status = $isUnread ? 'baru' : 'dibaca';
            $timeText = $notif->created_at ? $notif->created_at->diffForHumans() : '-';
          @endphp
          <a
            href="{{ route('notifikasi.open', $notif->id) }}"
            class="list-group-item list-group-item-action border-0 border-bottom"
            data-item
            data-status="{{ $status }}"
          >
            <div class="d-flex gap-3">
              <div class="pt-2">
                <div class="rounded-circle" style="width:10px; height:10px; background: {{ $isUnread ? '#0d6efd' : '#ccc' }};"></div>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-semibold">{{ $notif->title ?? '-' }}</div>
                    <div class="text-muted small">{{ $notif->message ?? '-' }}</div>
                  </div>
                  <div class="text-muted small">{{ $timeText }}</div>
                </div>
              </div>
            </div>
          </a>
        @empty
          <div class="text-center text-muted py-3">Belum ada notifikasi.</div>
        @endforelse
      </div>
      <div class="alert alert-warning d-none mt-3 py-2 small" data-empty>Notifikasi tidak ditemukan.</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const list = document.querySelector('[data-list]');
    const empty = document.querySelector('[data-empty]');
    const filterBtns = document.querySelectorAll('[data-filter]');
    const markAll = document.querySelector('[data-mark-all]');
    const clearBtn = document.querySelector('[data-clear]');
    const unreadCountEls = document.querySelectorAll('[data-unread-count]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const setUnreadCount = (count) => {
      unreadCountEls.forEach((el) => {
        el.textContent = String(count);
      });
    };

    const requestJson = async (url, method) => {
      const response = await fetch(url, {
        method,
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
      });

      const payload = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(payload.message || 'Request gagal diproses.');
      }

      return payload;
    };

    const applyFilter = (type) => {
      const items = list?.querySelectorAll('[data-item]') || [];
      let visible = 0;
      items.forEach((item) => {
        const status = item.getAttribute('data-status') || '';
        const show = type === 'all' || status === type;
        item.classList.toggle('d-none', !show);
        if (show) visible += 1;
      });
      if (empty) empty.classList.toggle('d-none', visible > 0);
    };

    const setActiveFilterButton = (activeBtn) => {
      filterBtns.forEach((b) => b.classList.remove('btn-primary'));
      filterBtns.forEach((b) => b.classList.add('btn-outline-primary'));
      activeBtn.classList.remove('btn-outline-primary');
      activeBtn.classList.add('btn-primary');
    };

    document.addEventListener('click', (event) => {
      const btn = event.target.closest('[data-filter]');
      if (!btn) return;
      if (!btn.isConnected) return;
      event.preventDefault();
      event.stopPropagation();

      if (![...filterBtns].includes(btn)) return;

      setActiveFilterButton(btn);
      applyFilter(btn.getAttribute('data-filter') || 'all');
    });

    markAll?.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const url = markAll.getAttribute('data-mark-all-url') || '';
      if (!url) return;

      const originalHtml = markAll.innerHTML;
      markAll.disabled = true;
      markAll.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

      requestJson(url, 'POST')
        .then((payload) => {
          list?.querySelectorAll('[data-item]').forEach((item) => {
            item.setAttribute('data-status', 'dibaca');
            const dot = item.querySelector('div[style*="width:10px"]');
            if (dot) dot.style.background = '#ccc';
          });
          setUnreadCount(Number(payload.unreadCount ?? 0));

          const allBtn = [...filterBtns].find((btn) => (btn.getAttribute('data-filter') || 'all') === 'all');
          if (allBtn) {
            setActiveFilterButton(allBtn);
          }
          applyFilter('all');
        })
        .catch((error) => {
          if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Gagal', text: error.message || 'Gagal menandai semua notifikasi.' });
            return;
          }
          alert(error.message || 'Gagal menandai semua notifikasi.');
        })
        .finally(() => {
          markAll.disabled = false;
          markAll.innerHTML = originalHtml;
        });
    });

    clearBtn?.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const url = clearBtn.getAttribute('data-clear-url') || '';
      if (!url) return;

      const originalHtml = clearBtn.innerHTML;
      clearBtn.disabled = true;
      clearBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

      requestJson(url, 'DELETE')
        .then((payload) => {
          list?.replaceChildren();
          if (empty) empty.classList.remove('d-none');
          setUnreadCount(Number(payload.unreadCount ?? 0));
        })
        .catch((error) => {
          if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Gagal', text: error.message || 'Gagal menghapus semua notifikasi.' });
            return;
          }
          alert(error.message || 'Gagal menghapus semua notifikasi.');
        })
        .finally(() => {
          clearBtn.disabled = false;
          clearBtn.innerHTML = originalHtml;
        });
    });

    filterBtns.forEach((btn) => {
      btn.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (!btn.isConnected) return;
        setActiveFilterButton(btn);
        applyFilter(btn.getAttribute('data-filter') || 'all');
      });
    });

    applyFilter('all');
  });
</script>
@endpush
