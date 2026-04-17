{{-- Slot Position Editor partial --}}
{{-- Include AFTER the upload zone card, inside a col-lg-6 --}}
{{-- Exposes: #slot-editor-wrap, #slot-canvas, #slot-bg-img, #slot-list, #btn-clear-slots --}}

<div class="card border-0 shadow-sm" style="position:sticky;top:76px;">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
        <div>
            <h6 class="card-title mb-0 fw-bold">Posisi Slot Foto</h6>
            <p class="card-subtitle text-muted small mt-1 mb-0">Untuk user awam, cukup upload frame lalu klik generate otomatis</p>
        </div>
        <button type="button" id="btn-clear-slots" class="btn btn-sm btn-outline-danger d-none">
            <i class="mdi mdi-delete-sweep-outline me-1"></i> Hapus Semua
        </button>
    </div>
    <div class="card-body pt-2">
        <div class="alert alert-primary py-2 px-3 small mb-3">
            <strong>Cara mudah:</strong> 1. Upload frame 2. Isi jumlah slot 3. Klik <em>Generate Otomatis</em> 4. Simpan.
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" id="btn-generate-auto" class="btn btn-primary btn-sm">
                <i class="mdi mdi-auto-fix me-1"></i> Generate Otomatis
            </button>
            <button type="button" id="btn-layout-1" class="btn btn-outline-secondary btn-sm">
                1 Kolom
            </button>
            <button type="button" id="btn-layout-2" class="btn btn-outline-secondary btn-sm">
                2 Kolom
            </button>
            <button type="button" id="btn-layout-3" class="btn btn-outline-secondary btn-sm">
                3 Kolom
            </button>
        </div>

        {{-- Placeholder (shown until image is loaded) --}}
        <div id="slot-editor-placeholder" class="text-center py-5 text-muted">
            <i class="mdi mdi-image-frame" style="font-size:3rem;opacity:.25;display:block;margin-bottom:.75rem;"></i>
            Upload gambar frame terlebih dahulu, lalu klik generate otomatis.
        </div>

        {{-- Canvas editor --}}
        <div id="slot-editor-wrap" class="d-none" style="position:relative;user-select:none;border-radius:12px;overflow:hidden;border:2px solid #dee2e6;">
            <img id="slot-bg-img" src="" alt="" style="display:block;width:100%;height:auto;pointer-events:none;" draggable="false">
            <canvas id="slot-canvas" style="position:absolute;top:0;left:0;width:100%;height:100%;cursor:crosshair;touch-action:none;"></canvas>
        </div>

        {{-- Slot list --}}
        <div id="slot-list" class="mt-3"></div>
        <div id="slot-editor-status" class="alert alert-warning py-2 px-3 mt-3 mb-0 small d-none"></div>

        <div class="form-text mt-2">
            <i class="mdi mdi-information-outline me-1"></i>
            Slot akan dibuat otomatis. Kalau perlu, baru koreksi manual dengan klik dan seret.
        </div>
    </div>
</div>

<script>
(function () {
    var canvas        = document.getElementById('slot-canvas');
    var bgImg         = document.getElementById('slot-bg-img');
    var wrap          = document.getElementById('slot-editor-wrap');
    var placeholder   = document.getElementById('slot-editor-placeholder');
    var slotListEl    = document.getElementById('slot-list');
    var clearBtn      = document.getElementById('btn-clear-slots');
    var slotJsonInput = document.getElementById('slot-positions-json');
    var prevImg       = document.getElementById('prev-thumbnail-img');
    var statusEl      = document.getElementById('slot-editor-status');
    var formEl        = document.getElementById('tmpl-form');
    var photoSlotsEl  = document.querySelector('[name="photo_slots"]');
    var printSizeEl   = document.getElementById('print-size-value');
    var generateAutoBtn = document.getElementById('btn-generate-auto');
    var layout1Btn    = document.getElementById('btn-layout-1');
    var layout2Btn    = document.getElementById('btn-layout-2');
    var layout3Btn    = document.getElementById('btn-layout-3');

    var slots          = [];
    var hasFrameLoaded = false;

    // Interaction state
    var HANDLE_SIZE = 7; // px display
    var action      = null;
    // action types:
    //   {type:'draw', startX, startY}
    //   {type:'drag', idx, offsetX, offsetY}
    //   {type:'resize', idx, handle, origSlot, startX, startY}
    // handle: 'nw','n','ne','e','se','s','sw','w'

    var selectedIdx = -1;

    // Load existing valid slots from hidden input
    try {
        var existing = JSON.parse(slotJsonInput.value || '[]');
        if (Array.isArray(existing) && existing.length && existing[0] && existing[0].x !== null) {
            slots = existing;
        }
    } catch (e) {}
    // Immediately normalize — removes null-valued stale entries from old templates
    saveToInput();

    function getScale() {
        return {
            sx: bgImg.naturalWidth  / (bgImg.offsetWidth  || 1),
            sy: bgImg.naturalHeight / (bgImg.offsetHeight || 1),
        };
    }

    function resizeCanvas() {
        canvas.width  = bgImg.offsetWidth;
        canvas.height = bgImg.offsetHeight;
        redraw();
    }

    // ── Handle geometry helpers ──────────────────────────────────────────────

    function getHandles(dx, dy, dw, dh) {
        var mx = dx + dw / 2, my = dy + dh / 2;
        return {
            nw: [dx,      dy     ],
            n:  [mx,      dy     ],
            ne: [dx + dw, dy     ],
            e:  [dx + dw, my     ],
            se: [dx + dw, dy + dh],
            s:  [mx,      dy + dh],
            sw: [dx,      dy + dh],
            w:  [dx,      my     ],
        };
    }

    var HANDLE_CURSORS = {
        nw: 'nw-resize', n: 'n-resize', ne: 'ne-resize',
        e:  'e-resize',  se: 'se-resize', s: 's-resize',
        sw: 'sw-resize', w: 'w-resize',
    };

    function hitHandle(px, py, dx, dy, dw, dh) {
        var hs = HANDLE_SIZE + 3; // slightly larger hit area
        var handles = getHandles(dx, dy, dw, dh);
        var keys    = Object.keys(handles);
        for (var k = 0; k < keys.length; k++) {
            var name = keys[k];
            var hx   = handles[name][0];
            var hy   = handles[name][1];
            if (Math.abs(px - hx) <= hs && Math.abs(py - hy) <= hs) return name;
        }
        return null;
    }

    function hitSlot(px, py, dx, dy, dw, dh) {
        return px >= dx && px <= dx + dw && py >= dy && py <= dy + dh;
    }

    function slotToDisplay(slot) {
        var s = getScale();
        return { dx: slot.x / s.sx, dy: slot.y / s.sy, dw: slot.width / s.sx, dh: slot.height / s.sy };
    }

    function findHit(px, py) {
        // check in reverse order so top-drawn slots take priority
        for (var i = slots.length - 1; i >= 0; i--) {
            var d      = slotToDisplay(slots[i]);
            var handle = hitHandle(px, py, d.dx, d.dy, d.dw, d.dh);
            if (handle) return { idx: i, handle: handle };
        }
        for (var j = slots.length - 1; j >= 0; j--) {
            var dd = slotToDisplay(slots[j]);
            if (hitSlot(px, py, dd.dx, dd.dy, dd.dw, dd.dh)) return { idx: j, handle: null };
        }
        return null;
    }

    function redraw() {
        var ctx    = canvas.getContext('2d');
        var scale  = getScale();
        var sx     = scale.sx;
        var sy     = scale.sy;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        slots.forEach(function (slot, i) {
            var dx = slot.x / sx;
            var dy = slot.y / sy;
            var dw = slot.width  / sx;
            var dh = slot.height / sy;
            var isSelected = (i === selectedIdx);

            ctx.fillStyle = isSelected ? 'rgba(249,200,70,.28)' : 'rgba(85,110,230,.18)';
            ctx.fillRect(dx, dy, dw, dh);

            ctx.strokeStyle = isSelected ? '#f9c846' : '#556ee6';
            ctx.lineWidth   = isSelected ? 2.5 : 2;
            ctx.setLineDash([]);
            ctx.strokeRect(dx, dy, dw, dh);

            // Number label
            var labelSize = Math.min(dw, dh) * 0.35;
            labelSize = Math.max(12, Math.min(labelSize, 26));
            ctx.fillStyle = isSelected ? '#b8860b' : '#556ee6';
            ctx.font      = 'bold ' + labelSize + 'px sans-serif';
            ctx.fillText(i + 1, dx + 6, dy + labelSize + 2);

            // Resize handles (only for selected, or always show for usability)
            var handles = getHandles(dx, dy, dw, dh);
            var hkeys   = Object.keys(handles);
            for (var k = 0; k < hkeys.length; k++) {
                var hpos = handles[hkeys[k]];
                ctx.fillStyle   = isSelected ? '#f9c846' : '#ffffff';
                ctx.strokeStyle = isSelected ? '#b8860b' : '#556ee6';
                ctx.lineWidth   = 1.5;
                ctx.fillRect(hpos[0] - HANDLE_SIZE / 2, hpos[1] - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);
                ctx.strokeRect(hpos[0] - HANDLE_SIZE / 2, hpos[1] - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);
            }
        });

        updateSlotList();
        saveToInput();
    }

    function updateSlotList() {
        clearBtn.classList.toggle('d-none', slots.length === 0);

        if (slots.length === 0) {
            slotListEl.innerHTML = '<p class="text-muted small mb-0">Belum ada slot. Gambar kotak di atas frame.</p>';
            updateStatus();
            return;
        }

        slotListEl.innerHTML = slots.map(function (s, i) {
            return '<div class="d-flex align-items-center justify-content-between mb-1 px-2 py-1 rounded" style="background:#f8f9fa;font-size:.8rem;">'
                 + '<span><span class="badge bg-primary me-2">' + (i + 1) + '</span>'
                 + Math.round(s.x) + ', ' + Math.round(s.y) + ' &nbsp;&#8212;&nbsp; '
                 + Math.round(s.width) + ' &times; ' + Math.round(s.height) + ' px</span>'
                 + '<button type="button" onclick="slotEditorRemove(' + i + ')" class="btn btn-sm btn-outline-danger py-0 px-1" style="line-height:1.3;">'
                 + '<i class="mdi mdi-close" style="font-size:.75rem;"></i></button>'
                 + '</div>';
        }).join('');

        updateStatus();
    }

    function saveToInput() {
        slotJsonInput.value = JSON.stringify(slots);
    }

    function getExpectedSlotCount() {
        var value = parseInt(photoSlotsEl && photoSlotsEl.value ? photoSlotsEl.value : '0', 10);
        return Number.isFinite(value) ? value : 0;
    }

    function updateStatus() {
        if (!statusEl) return;

        var expected = getExpectedSlotCount();

        if (!hasFrameLoaded) {
            statusEl.className = 'alert alert-warning py-2 px-3 mt-3 mb-0 small';
            statusEl.textContent = 'Upload frame terlebih dahulu untuk membuat slot otomatis.';
            statusEl.classList.remove('d-none');
            return;
        }

        if (!expected) {
            statusEl.className = 'alert alert-warning py-2 px-3 mt-3 mb-0 small';
            statusEl.textContent = 'Isi jumlah slot foto terlebih dahulu.';
            statusEl.classList.remove('d-none');
            return;
        }

        if (slots.length === 0) {
            statusEl.className = 'alert alert-warning py-2 px-3 mt-3 mb-0 small';
            statusEl.textContent = 'Belum ada slot yang digambar.';
            statusEl.classList.remove('d-none');
            return;
        }

        if (slots.length !== expected) {
            statusEl.className = 'alert alert-warning py-2 px-3 mt-3 mb-0 small';
            statusEl.textContent = 'Jumlah slot saat ini ' + slots.length + ' dari target ' + expected + '.';
            statusEl.classList.remove('d-none');
            return;
        }

        statusEl.className = 'alert alert-success py-2 px-3 mt-3 mb-0 small';
        statusEl.textContent = 'Jumlah slot sudah sesuai: ' + expected + '.';
        statusEl.classList.remove('d-none');
    }

    // Exposed globally so inline onclick works
    window.slotEditorRemove = function (i) {
        slots.splice(i, 1);
        redraw();
    };

    clearBtn.addEventListener('click', function () {
        slots = [];
        redraw();
    });

    function suggestColumns(count, printSize, naturalWidth, naturalHeight) {
        var normalizedPrintSize = String(printSize || '').trim().toLowerCase();
        var isPortraitFrame = (naturalHeight || 0) >= (naturalWidth || 0);

        if (normalizedPrintSize === 'strip') return 1;
        if (count <= 1) return 1;

        if (isPortraitFrame) {
            if (count <= 3) return 1;
            if (count <= 8) return 2;
            return 3;
        }

        if (count <= 3) return count;
        if (count <= 6) return 3;
        return 4;
    }

    function generateSlots(columns) {
        if (!hasFrameLoaded) {
            updateStatus();
            alert('Upload frame terlebih dahulu.');
            return;
        }

        var count = getExpectedSlotCount();

        if (!count) {
            updateStatus();
            alert('Isi jumlah slot foto terlebih dahulu.');
            return;
        }

        var naturalWidth = bgImg.naturalWidth || 1;
        var naturalHeight = bgImg.naturalHeight || 1;
        var cols = Math.max(1, Math.min(columns || 1, count));
        var rows = Math.ceil(count / cols);
        var isStrip = String(printSizeEl && printSizeEl.value ? printSizeEl.value : '').trim().toLowerCase() === 'strip';
        var paddingX = Math.round(naturalWidth * (isStrip ? 0.08 : 0.05));
        var paddingY = Math.round(naturalHeight * (isStrip ? 0.05 : 0.05));
        var gapX = Math.round(naturalWidth * (isStrip ? 0.025 : 0.02));
        var gapY = Math.round(naturalHeight * (isStrip ? 0.02 : 0.018));
        var usableWidth = naturalWidth - (paddingX * 2) - (gapX * (cols - 1));
        var usableHeight = naturalHeight - (paddingY * 2) - (gapY * (rows - 1));
        var slotWidth = Math.floor(usableWidth / cols);
        var slotHeight = Math.floor(usableHeight / rows);

        slots = Array.from({ length: count }, function (_, index) {
            var col = index % cols;
            var row = Math.floor(index / cols);

            return {
                x: paddingX + col * (slotWidth + gapX),
                y: paddingY + row * (slotHeight + gapY),
                width: slotWidth,
                height: slotHeight,
            };
        });

        redraw();
    }

    // ── Pixel-based slot detection (port dari template-slot-utils.ts) ──────────

    function isNearWhiteOrTransparent(r, g, b, a) {
        if (a < 10) return true;
        var max = Math.max(r, g, b);
        var min = Math.min(r, g, b);
        return max > 236 && min > 224 && (max - min) < 18;
    }

    function detectSlotsFromPixels(expectedCount) {
        var maxWidth = 320;
        var naturalW = bgImg.naturalWidth;
        var naturalH = bgImg.naturalHeight;
        var ratio    = Math.min(1, maxWidth / naturalW);
        var w        = Math.max(1, Math.round(naturalW * ratio));
        var h        = Math.max(1, Math.round(naturalH * ratio));

        var offscreen = document.createElement('canvas');
        offscreen.width  = w;
        offscreen.height = h;
        var ctx = offscreen.getContext('2d');
        if (!ctx) return [];

        ctx.drawImage(bgImg, 0, 0, w, h);
        var imageData = ctx.getImageData(0, 0, w, h);
        var data      = imageData.data;
        var visited   = new Uint8Array(w * h);
        var regions   = [];

        for (var py = 0; py < h; py++) {
            for (var px = 0; px < w; px++) {
                var idx = py * w + px;
                if (visited[idx]) continue;
                visited[idx] = 1;

                var pi = idx * 4;
                if (!isNearWhiteOrTransparent(data[pi], data[pi+1], data[pi+2], data[pi+3])) continue;

                // Flood-fill
                var queue  = [idx];
                var area   = 0;
                var left   = px, right  = px;
                var top    = py, bottom = py;

                while (queue.length > 0) {
                    var cur  = queue.pop();
                    var curX = cur % w;
                    var curY = Math.floor(cur / w);
                    area++;
                    if (curX < left)   left   = curX;
                    if (curX > right)  right  = curX;
                    if (curY < top)    top    = curY;
                    if (curY > bottom) bottom = curY;

                    var neighbors = [cur - 1, cur + 1, cur - w, cur + w];
                    for (var ni = 0; ni < neighbors.length; ni++) {
                        var nb = neighbors[ni];
                        if (nb < 0 || nb >= w * h) continue;
                        var nbX = nb % w;
                        var nbY = Math.floor(nb / w);
                        if (Math.abs(nbX - curX) + Math.abs(nbY - curY) !== 1) continue;
                        if (visited[nb]) continue;
                        visited[nb] = 1;
                        var npi = nb * 4;
                        if (isNearWhiteOrTransparent(data[npi], data[npi+1], data[npi+2], data[npi+3])) {
                            queue.push(nb);
                        }
                    }
                }

                var rw = right - left + 1;
                var rh = bottom - top + 1;
                var aspect = rw / rh;

                // Filter: terlalu kecil atau rasio tidak masuk akal
                if (area < w * h * 0.008) continue;
                if (rw < w * 0.08 || rh < h * 0.06) continue;
                if (aspect < 0.25 || aspect > 4.0) continue;

                regions.push({ left: left, top: top, right: right, bottom: bottom, area: area });
            }
        }

        // Ambil N region terbesar, lalu urutkan top→bottom, left→right
        var scaleX = naturalW / w;
        var scaleY = naturalH / h;

        var selected = regions
            .sort(function (a, b) { return b.area - a.area; })
            .slice(0, expectedCount)
            .sort(function (a, b) { return a.top === b.top ? a.left - b.left : a.top - b.top; });

        return selected.map(function (reg) {
            return {
                x:      Math.round(reg.left  * scaleX),
                y:      Math.round(reg.top   * scaleY),
                width:  Math.round((reg.right  - reg.left + 1) * scaleX),
                height: Math.round((reg.bottom - reg.top  + 1) * scaleY),
            };
        });
    }

    function generateAutomaticSlots() {
        if (!hasFrameLoaded) {
            updateStatus();
            alert('Upload frame terlebih dahulu.');
            return;
        }

        var count = getExpectedSlotCount();
        if (!count) {
            updateStatus();
            alert('Isi jumlah slot foto terlebih dahulu.');
            return;
        }

        // Coba deteksi dari pixel dulu
        var detected = detectSlotsFromPixels(count);

        if (detected.length === count) {
            slots = detected;
            redraw();
            return;
        }

        // Fallback: deteksi dapat sebagian → tetap gunakan jika >= 50%
        if (detected.length >= Math.ceil(count / 2) && detected.length > 0) {
            slots = detected;
            redraw();
            if (statusEl) {
                statusEl.className = 'alert alert-warning py-2 px-3 mt-3 mb-0 small';
                statusEl.textContent = 'Deteksi otomatis menemukan ' + detected.length + ' dari ' + count + ' slot. Gambar ulang slot yang kurang.';
                statusEl.classList.remove('d-none');
            }
            return;
        }

        // Fallback: matematika (untuk frame tanpa area putih jelas)
        generateSlots(
            suggestColumns(count, printSizeEl && printSizeEl.value, bgImg.naturalWidth, bgImg.naturalHeight)
        );
    }

    if (generateAutoBtn) {
        generateAutoBtn.addEventListener('click', generateAutomaticSlots);
    }

    if (layout1Btn) {
        layout1Btn.addEventListener('click', function () { generateSlots(1); });
    }

    if (layout2Btn) {
        layout2Btn.addEventListener('click', function () { generateSlots(2); });
    }

    if (layout3Btn) {
        layout3Btn.addEventListener('click', function () { generateSlots(3); });
    }

    if (photoSlotsEl) {
        photoSlotsEl.addEventListener('input', updateStatus);
        photoSlotsEl.addEventListener('change', updateStatus);
        photoSlotsEl.addEventListener('change', function () {
            if (hasFrameLoaded) {
                generateAutomaticSlots();
            }
        });
    }

    if (printSizeEl) {
        printSizeEl.addEventListener('change', function () {
            if (hasFrameLoaded) {
                generateAutomaticSlots();
            }
        });
    }

    if (formEl) {
        formEl.addEventListener('submit', function (e) {
            if (slots.length !== getExpectedSlotCount()) {
                e.preventDefault();
                updateStatus();
                alert('Jumlah slot pada editor harus sama dengan jumlah slot foto.');
            }
        });
    }

    // ── Input helpers ────────────────────────────────────────────────────────
    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        return {
            x: (e.clientX - rect.left) * (canvas.width / rect.width),
            y: (e.clientY - rect.top)  * (canvas.height / rect.height),
        };
    }

    function getTouchPos(e) {
        var touch = e.touches[0] || e.changedTouches[0];
        var rect  = canvas.getBoundingClientRect();
        return {
            x: (touch.clientX - rect.left) * (canvas.width / rect.width),
            y: (touch.clientY - rect.top)  * (canvas.height / rect.height),
        };
    }

    // ── Apply resize delta ───────────────────────────────────────────────────
    function applyResize(orig, handle, dx, dy) {
        var x = orig.x, y = orig.y, w = orig.width, h = orig.height;
        var s = getScale();
        var ddx = dx * s.sx, ddy = dy * s.sy;
        var MIN = 20;

        if (handle === 'nw') { x += ddx; y += ddy; w -= ddx; h -= ddy; }
        else if (handle === 'n')  { y += ddy; h -= ddy; }
        else if (handle === 'ne') { y += ddy; w += ddx; h -= ddy; }
        else if (handle === 'e')  { w += ddx; }
        else if (handle === 'se') { w += ddx; h += ddy; }
        else if (handle === 's')  { h += ddy; }
        else if (handle === 'sw') { x += ddx; w -= ddx; h += ddy; }
        else if (handle === 'w')  { x += ddx; w -= ddx; }

        // Enforce minimum size
        if (w < MIN) { if (handle.indexOf('w') !== -1) { x = orig.x + orig.width - MIN; } w = MIN; }
        if (h < MIN) { if (handle.indexOf('n') !== -1) { y = orig.y + orig.height - MIN; } h = MIN; }

        return { x: Math.round(x), y: Math.round(y), width: Math.round(w), height: Math.round(h) };
    }

    // ── Mouse events ─────────────────────────────────────────────────────────
    canvas.addEventListener('mousedown', function (e) {
        var p   = getPos(e);
        var hit = findHit(p.x, p.y);

        if (hit) {
            selectedIdx = hit.idx;
            if (hit.handle) {
                action = { type: 'resize', idx: hit.idx, handle: hit.handle,
                            origSlot: Object.assign({}, slots[hit.idx]),
                            startX: p.x, startY: p.y };
            } else {
                var d  = slotToDisplay(slots[hit.idx]);
                action = { type: 'drag', idx: hit.idx,
                            offsetX: p.x - d.dx, offsetY: p.y - d.dy };
            }
        } else {
            selectedIdx = -1;
            action = { type: 'draw', startX: p.x, startY: p.y };
        }
        redraw();
    });

    canvas.addEventListener('mousemove', function (e) {
        var p = getPos(e);

        // Update cursor
        if (!action) {
            var hov = findHit(p.x, p.y);
            if (hov && hov.handle) {
                canvas.style.cursor = HANDLE_CURSORS[hov.handle];
            } else if (hov) {
                canvas.style.cursor = 'move';
            } else {
                canvas.style.cursor = 'crosshair';
            }
        }

        if (!action) return;

        if (action.type === 'draw') {
            redraw();
            var ctx = canvas.getContext('2d');
            ctx.strokeStyle = '#e74c3c';
            ctx.lineWidth   = 2;
            ctx.setLineDash([6, 3]);
            ctx.strokeRect(action.startX, action.startY, p.x - action.startX, p.y - action.startY);
            ctx.setLineDash([]);

        } else if (action.type === 'drag') {
            var s  = getScale();
            var d  = slotToDisplay(slots[action.idx]);
            var nx = (p.x - action.offsetX) * s.sx;
            var ny = (p.y - action.offsetY) * s.sy;
            slots[action.idx].x = Math.round(Math.max(0, nx));
            slots[action.idx].y = Math.round(Math.max(0, ny));
            redraw();

        } else if (action.type === 'resize') {
            var updated = applyResize(
                action.origSlot, action.handle,
                p.x - action.startX, p.y - action.startY
            );
            slots[action.idx] = updated;
            redraw();
        }
    });

    canvas.addEventListener('mouseup', function (e) {
        if (!action) return;
        var p = getPos(e);

        if (action.type === 'draw') {
            addSlotFromDisplay(action.startX, action.startY, p.x, p.y);
        }
        action = null;
        canvas.style.cursor = 'crosshair';
    });

    canvas.addEventListener('mouseleave', function () {
        if (action && action.type === 'draw') {
            action = null;
            redraw();
        }
        canvas.style.cursor = 'crosshair';
    });

    // ── Touch events ─────────────────────────────────────────────────────────
    canvas.addEventListener('touchstart', function (e) {
        e.preventDefault();
        var p   = getTouchPos(e);
        var hit = findHit(p.x, p.y);

        if (hit) {
            selectedIdx = hit.idx;
            if (hit.handle) {
                action = { type: 'resize', idx: hit.idx, handle: hit.handle,
                            origSlot: Object.assign({}, slots[hit.idx]),
                            startX: p.x, startY: p.y };
            } else {
                var d  = slotToDisplay(slots[hit.idx]);
                action = { type: 'drag', idx: hit.idx,
                            offsetX: p.x - d.dx, offsetY: p.y - d.dy };
            }
        } else {
            selectedIdx = -1;
            action = { type: 'draw', startX: p.x, startY: p.y };
        }
        redraw();
    }, { passive: false });

    canvas.addEventListener('touchmove', function (e) {
        e.preventDefault();
        if (!action) return;
        var p = getTouchPos(e);

        if (action.type === 'draw') {
            redraw();
            var ctx = canvas.getContext('2d');
            ctx.strokeStyle = '#e74c3c';
            ctx.lineWidth   = 2;
            ctx.setLineDash([6, 3]);
            ctx.strokeRect(action.startX, action.startY, p.x - action.startX, p.y - action.startY);
            ctx.setLineDash([]);

        } else if (action.type === 'drag') {
            var s  = getScale();
            var nx = (p.x - action.offsetX) * s.sx;
            var ny = (p.y - action.offsetY) * s.sy;
            slots[action.idx].x = Math.round(Math.max(0, nx));
            slots[action.idx].y = Math.round(Math.max(0, ny));
            redraw();

        } else if (action.type === 'resize') {
            var updated = applyResize(
                action.origSlot, action.handle,
                p.x - action.startX, p.y - action.startY
            );
            slots[action.idx] = updated;
            redraw();
        }
    }, { passive: false });

    canvas.addEventListener('touchend', function (e) {
        e.preventDefault();
        if (!action) return;
        var p = getTouchPos(e);

        if (action.type === 'draw') {
            addSlotFromDisplay(action.startX, action.startY, p.x, p.y);
        }
        action = null;
    }, { passive: false });

    // ── Helpers ──────────────────────────────────────────────────────────────
    function addSlotFromDisplay(x1, y1, x2, y2) {
        var dw = Math.abs(x2 - x1);
        var dh = Math.abs(y2 - y1);
        if (dw < 10 || dh < 10) return; // too small → ignore

        var scale = getScale();
        slots.push({
            x:      Math.round(Math.min(x1, x2) * scale.sx),
            y:      Math.round(Math.min(y1, y2) * scale.sy),
            width:  Math.round(dw * scale.sx),
            height: Math.round(dh * scale.sy),
        });
        redraw();
    }

    function loadFrameIntoEditor(src) {
        if (!src || src === window.location.href) return;
        bgImg.onload = function () {
            hasFrameLoaded = true;
            placeholder.classList.add('d-none');
            wrap.classList.remove('d-none');
            // defer so layout is settled
            requestAnimationFrame(function () {
                resizeCanvas();
                if (slots.length === 0) {
                    generateAutomaticSlots();
                } else {
                    updateStatus();
                }
            });
        };
        bgImg.src = src;
    }

    // Watch for thumbnail image src change (fires after upload preview)
    var observer = new MutationObserver(function () {
        loadFrameIntoEditor(prevImg.getAttribute('src'));
    });
    observer.observe(prevImg, { attributes: true, attributeFilter: ['src'] });

    // On page load (edit page: existing image already set)
    if (prevImg.src && prevImg.src !== window.location.href) {
        if (prevImg.complete && prevImg.naturalWidth > 0) {
            loadFrameIntoEditor(prevImg.src);
        } else {
            prevImg.addEventListener('load', function () {
                loadFrameIntoEditor(prevImg.src);
            });
        }
    }

    window.addEventListener('resize', function () {
        if (!wrap.classList.contains('d-none')) resizeCanvas();
    });

    updateStatus();
}());
</script>
