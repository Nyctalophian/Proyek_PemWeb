const API = 'api.php';
let currentDetailId = null;
let searchTimeout   = null;

$(function () {
  loadStats();
  loadBarang();
  bindEvents();
});

function bindEvents () {
  $('#menuToggle').on('click', openSidebar);
  $('#sidebarOverlay').on('click', closeSidebar);

  $('.nav-item').on('click', function (e) {
    e.preventDefault();
    $('.nav-item').removeClass('active');
    $(this).addClass('active');
    $('.topbar-title').text($(this).text().trim());
    if ($(this).data('page') === 'laporan-saya') { loadStats(); loadBarang(); }
    closeSidebar();
  });

  $('#searchInput').on('input', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadBarang, 350);
  });

  $('#filterKategori, #filterStatus').on('change', loadBarang);

  $('#btnLaporBarang').on('click', openCreateModal);

  $('#btnCloseModal, #btnCancelModal').on('click', closeModal);
  $('#modalLapor').on('click', function (e) {
    if ($(e.target).is('#modalLapor')) closeModal();
  });

  $('#btnSubmitModal').on('click', submitForm);

  const $area = $('#uploadArea');
  $area.on('dragover', function (e) {
    e.preventDefault();
    $area.addClass('drag-over');
  });
  $area.on('dragleave drop', function () {
    $area.removeClass('drag-over');
  });
  $area.on('drop', function (e) {
    e.preventDefault();
    const file = e.originalEvent.dataTransfer.files[0];
    if (file) setPreviewFile(file);
  });

  $('#inpGambar').on('change', function () {
    const file = this.files[0];
    if (file) setPreviewFile(file);
  });

  $('#btnHapusGambar').on('click', clearPreview);

  $('#btnCloseDetail, #btnCloseDetail2').on('click', closeDetail);
  $('#modalDetail').on('click', function (e) {
    if ($(e.target).is('#modalDetail')) closeDetail();
  });

  $('#btnEditFromDetail').on('click', function () {
    closeDetail();
    loadForEdit(currentDetailId);
  });
  $('#btnDeleteFromDetail').on('click', function () {
    closeDetail();
    confirmDelete(currentDetailId);
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') { closeModal(); closeDetail(); closeSidebar(); }
  });
}

function openSidebar () {
  $('#sidebar').addClass('open');
  $('#sidebarOverlay').addClass('active');
  $('body').css('overflow', 'hidden');
}

function closeSidebar () {
  $('#sidebar').removeClass('open');
  $('#sidebarOverlay').removeClass('active');
  $('body').css('overflow', '');
}

function setPreviewFile (file) {
  const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  if (!allowed.includes(file.type)) {
    showToast('Format tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.', 'error');
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    showToast('Ukuran gambar maks 5 MB.', 'error');
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    $('#imgPreviewSrc').attr('src', e.target.result);
    $('#imgPreview').show();
    $('#uploadArea').hide();
    $('#currentImgWrap').hide();
  };
  reader.readAsDataURL(file);

  const dt = new DataTransfer();
  dt.items.add(file);
  $('#inpGambar')[0].files = dt.files;
}

function clearPreview () {
  $('#inpGambar').val('');
  $('#imgPreview').hide();
  $('#uploadArea').show();
}

function loadStats () {
  $.get(API, { action: 'stats' })
    .done(function (res) {
      $('#statDipublikasikan').text(res.dipublikasikan ?? 0);
      $('#statMenunggu').text(res.menunggu ?? 0);
      $('#statTotal').text(res.total ?? 0);
    })
    .fail(function () { showToast('Gagal memuat statistik', 'error'); });
}

function loadBarang () {
  const params = {
    search:   $('#searchInput').val().trim(),
    kategori: $('#filterKategori').val(),
    status:   $('#filterStatus').val(),
  };

  $('#barangList').html(
    '<div class="loading-state"><div class="spinner"></div><p>Memuat data…</p></div>'
  );

  $.get(API, params)
    .done(renderBarang)
    .fail(function () {
      $('#barangList').html(
        '<div class="empty-state"><p>Gagal memuat data. Pastikan XAMPP dan database berjalan.</p></div>'
      );
    });
}

const EMOJI_MAP = {
  Aksesori: '👓', Dompet: '👜', Elektronik: '📱', Pakaian: '👕', Lainnya: '📦'
};

function renderBarang (list) {
  const $grid = $('#barangList').empty();

  if (!list || list.length === 0) {
    $grid.html(`
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <p>Belum ada barang ditemukan</p>
      </div>`);
    return;
  }

  list.forEach(function (item, i) {
    const statusClass = item.status === 'dipublikasikan' ? 'badge-dipublikasikan' : 'badge-menunggu';
    const statusLabel = item.status === 'dipublikasikan' ? 'Siap Diklaim' : 'Menunggu';
    const tanggal     = formatTanggal(item.tanggal_ditemukan, 'short');

    const imgHtml = item.gambar
      ? `<img src="uploads/${escHtml(item.gambar)}"
              alt="${escHtml(item.nama)}"
              onerror="this.style.display='none';this.nextElementSibling.style.display='grid'" />
         <div class="card-img-placeholder" style="display:none">${EMOJI_MAP[item.kategori] || '📦'}</div>`
      : `<div class="card-img-placeholder">${EMOJI_MAP[item.kategori] || '📦'}</div>`;

    const $card = $(`
      <div class="barang-card" style="animation-delay:${i * 0.05}s">
        <div class="card-img-wrap">
          ${imgHtml}
          <span class="card-badge ${statusClass}">${statusLabel}</span>
        </div>
        <div class="card-body">
          <p class="card-name">${escHtml(item.nama)}</p>
          <p class="card-desc">${escHtml(item.deskripsi)}</p>
          <div class="card-meta">
            <span class="card-meta-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              ${escHtml(item.lokasi)}
            </span>
            <span class="card-meta-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              ${tanggal}
            </span>
          </div>
        </div>
        <div class="card-footer">
          <span class="card-kategori">${escHtml(item.kategori)}</span>
          <div class="card-actions">
            <button class="btn-sm btn-view">Lihat</button>
            <button class="btn-sm btn-del">Hapus</button>
          </div>
        </div>
      </div>
    `);

    $card.find('.btn-view').on('click', function () { openDetail(item.id); });
    $card.find('.btn-del').on('click',  function () { confirmDelete(item.id); });
    $grid.append($card);
  });
}

function openDetail (id) {
  currentDetailId = id;

  $.get(API, { action: 'detail', id: id })
    .done(function (item) {
      if (item.error) { showToast(item.error, 'error'); return; }

      const badgeCls = item.status === 'dipublikasikan' ? 'badge-dipublikasikan' : 'badge-menunggu';
      const badgeTxt = item.status === 'dipublikasikan' ? 'Dipublikasikan' : 'Menunggu Verifikasi';
      const tanggal  = formatTanggal(item.tanggal_ditemukan, 'long');

      const imgBlock = item.gambar
        ? `<img src="uploads/${escHtml(item.gambar)}"
                alt="${escHtml(item.nama)}"
                onerror="this.style.display='none';this.nextElementSibling.style.display='grid'" />
           <div class="detail-img-placeholder" style="display:none">${EMOJI_MAP[item.kategori] || '📦'}</div>`
        : `<div class="detail-img-placeholder">${EMOJI_MAP[item.kategori] || '📦'}</div>`;

      $('#detailContent').html(`
        <div class="detail-img-wrap">${imgBlock}</div>
        <p class="detail-name">${escHtml(item.nama)}</p>
        <span class="card-badge ${badgeCls}" style="display:inline-block;margin-bottom:12px">${badgeTxt}</span>
        <p class="detail-desc">${escHtml(item.deskripsi)}</p>
        <div class="detail-row">
          <span class="detail-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            ${escHtml(item.lokasi)}
          </span>
          <span class="detail-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            ${tanggal}
          </span>
          <span class="detail-chip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
              <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            ${escHtml(item.kategori)}
          </span>
        </div>
      `);

      $('#modalDetail').fadeIn(150);
    })
    .fail(function () { showToast('Gagal memuat detail', 'error'); });
}

function closeDetail () {
  $('#modalDetail').fadeOut(150);
  currentDetailId = null;
}

function openCreateModal () {
  $('#editId').val('');
  $('#modalTitle').text('Laporkan Barang');
  $('#btnSubmitModal').text('Simpan');
  $('#statusGroup').hide();
  resetForm();
  $('#modalLapor').fadeIn(150);
}

function loadForEdit (id) {
  $.get(API, { action: 'detail', id: id })
    .done(function (item) {
      if (item.error) { showToast(item.error, 'error'); return; }

      resetForm();
      $('#editId').val(item.id);
      $('#inpNama').val(item.nama);
      $('#inpDeskripsi').val(item.deskripsi);
      $('#inpLokasi').val(item.lokasi);
      $('#inpTanggal').val(item.tanggal_ditemukan);
      $('#inpKategori').val(item.kategori);
      $('#inpStatus').val(item.status);
      $('#statusGroup').show();
      $('#modalTitle').text('Edit Barang');
      $('#btnSubmitModal').text('Update');

      if (item.gambar) {
        $('#currentImgSrc').attr('src', 'uploads/' + item.gambar);
        $('#currentImgWrap').show();
      } else {
        $('#currentImgWrap').hide();
      }

      $('#modalLapor').fadeIn(150);
    })
    .fail(function () { showToast('Gagal memuat data', 'error'); });
}

function closeModal () {
  $('#modalLapor').fadeOut(150);
}

function resetForm () {
  $('#inpNama, #inpDeskripsi, #inpLokasi, #inpTanggal').val('');
  $('#inpKategori').val('');
  $('#formError').text('');
  clearPreview();
  $('#currentImgWrap').hide();
}

function submitForm () {
  const id        = $('#editId').val();
  const nama      = $('#inpNama').val().trim();
  const deskripsi = $('#inpDeskripsi').val().trim();
  const lokasi    = $('#inpLokasi').val().trim();
  const tanggal   = $('#inpTanggal').val();
  const kategori  = $('#inpKategori').val();
  const status    = $('#inpStatus').val() || 'menunggu';

  if (!nama || !deskripsi || !lokasi || !tanggal || !kategori) {
    $('#formError').text('Semua field bertanda * wajib diisi.');
    return;
  }
  $('#formError').text('');

  const fd = new FormData();
  fd.append('nama',              nama);
  fd.append('deskripsi',         deskripsi);
  fd.append('lokasi',            lokasi);
  fd.append('tanggal_ditemukan', tanggal);
  fd.append('kategori',          kategori);
  fd.append('status',            status);

  if (id) {
    fd.append('id',      id);
    fd.append('_method', 'PUT');
  }

  const fileInput = $('#inpGambar')[0];
  if (fileInput.files.length > 0) {
    fd.append('gambar', fileInput.files[0]);
  }

  $('#btnSubmitModal').prop('disabled', true).text('Menyimpan…');

  $.ajax({
    url:         API,
    method:      'POST',    
    data:        fd,
    processData: false,     
    contentType: false,     
  })
  .done(function (res) {
    if (res.success) {
      closeModal();
      showToast(res.message || 'Berhasil!', 'success');
      loadStats();
      loadBarang();
    } else {
      $('#formError').text(res.error || 'Terjadi kesalahan');
    }
  })
  .fail(function (xhr) {
    const msg = xhr.responseJSON?.error || 'Gagal menghubungi server. Pastikan XAMPP berjalan.';
    $('#formError').text(msg);
  })
  .always(function () {
    $('#btnSubmitModal').prop('disabled', false).text(id ? 'Update' : 'Simpan');
  });
}

function confirmDelete (id) {
  if (!confirm('Hapus barang ini? Tindakan tidak dapat dibatalkan.')) return;

  $.ajax({ url: API + '?id=' + id, method: 'DELETE' })
    .done(function (res) {
      if (res.success) {
        showToast(res.message, 'success');
        loadStats();
        loadBarang();
      } else {
        showToast(res.error || 'Gagal menghapus', 'error');
      }
    })
    .fail(function () { showToast('Gagal menghubungi server', 'error'); });
}

function showToast (msg, type) {
  type = type || 'info';
  const $t = $('#toast');
  $t.removeClass('success error info').addClass(type).text(msg).addClass('show');
  setTimeout(function () { $t.removeClass('show'); }, 3200);
}

function escHtml (str) {
  return $('<div>').text(str ?? '').html();
}

function formatTanggal (dateStr, style) {
  if (!dateStr) return '–';
  try {
    return new Date(dateStr).toLocaleDateString('id-ID', {
      day: '2-digit', month: style === 'long' ? 'long' : 'short', year: 'numeric'
    });
  } catch (e) { return dateStr; }
}