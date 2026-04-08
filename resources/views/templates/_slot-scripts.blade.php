<script>
/* =============================================================
   FABRIC.JS PHOTO SLOT EDITOR
   ============================================================= */
(function () {
    var fc        = null;   // fabric.Canvas instance
    var mode      = 'draw'; // 'draw' | 'select'
    var isDrawing = false;
    var drawRect  = null;
    var origX     = 0, origY = 0;
    var frameNaturalW = 1, frameNaturalH = 1;

    // ─── Public API (used by create/edit pages) ───────────────

    window.editorInit = function (canvasId) {
        fc = new fabric.Canvas(canvasId, {
            selection:        true,
            preserveObjectStacking: true,
        });

        /* Custom control appearance */
        fabric.Object.prototype.set({
            cornerColor:        '#556ee6',
            cornerStyle:        'circle',
            cornerSize:         10,
            borderColor:        '#f9c846',
            borderScaleFactor:  2,
            transparentCorners: false,
            padding:            4,
        });

        _bindEvents();
        setMode('draw');
    };

    window.editorLoadFrame = function (src) {
        fabric.Image.fromURL(src, function (img) {
            frameNaturalW = img.width;
            frameNaturalH = img.height;

            var container  = document.getElementById('fabric-canvas-wrapper');
            var maxW       = container.offsetWidth || 700;
            var maxH       = 520;
            var ratio      = Math.min(maxW / img.width, maxH / img.height, 1);
            var dispW      = Math.round(img.width  * ratio);
            var dispH      = Math.round(img.height * ratio);

            fc.setWidth(dispW);
            fc.setHeight(dispH);
            fc.setBackgroundImage(img, fc.renderAll.bind(fc), {
                scaleX: dispW / img.width,
                scaleY: dispH / img.height,
            });

            // Hide placeholder, show canvas
            _togglePlaceholder(false);
            fc.renderAll();
            _updateLabels();
        }, { crossOrigin: 'anonymous' });
    };

    window.editorLoadSlots = function (slots) {
        if (!slots || !slots.length) return;
        // Wait until frame is loaded (background image set)
        var attempt = 0;
        var timer   = setInterval(function () {
            attempt++;
            if (fc.backgroundImage || attempt > 40) {
                clearInterval(timer);
                if (!fc.backgroundImage) return;

                var scaleX = fc.width  / frameNaturalW;
                var scaleY = fc.height / frameNaturalH;

                slots.forEach(function (s) {
                    _addRect(
                        s.x      * scaleX,
                        s.y      * scaleY,
                        s.width  * scaleX,
                        s.height * scaleY
                    );
                });
                fc.renderAll();
                _updateLabels();
                _syncJson();
            }
        }, 80);
    };

    window.clearAllSlots = function () {
        fc.getObjects('rect').forEach(function (r) { fc.remove(r); });
        fc.discardActiveObject();
        fc.renderAll();
        _updateLabels();
        _syncJson();
    };

    window.deleteSelected = function () {
        var active = fc.getActiveObjects();
        active.forEach(function (obj) { fc.remove(obj); });
        fc.discardActiveObject();
        fc.renderAll();
        _updateLabels();
        _syncJson();
    };

    window.setMode = function (newMode) {
        mode = newMode;

        /* Update toolbar button states */
        document.querySelectorAll('.mode-btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });

        if (mode === 'select') {
            fc.isDrawingMode    = false;
            fc.selection        = true;
            fc.defaultCursor    = 'default';
            fc.hoverCursor      = 'move';
            fc.getObjects('rect').forEach(function (r) { r.selectable = true; r.evented = true; });
        } else {
            fc.isDrawingMode    = false;
            fc.selection        = false;
            fc.defaultCursor    = 'crosshair';
            fc.hoverCursor      = 'crosshair';
            fc.discardActiveObject();
            fc.getObjects('rect').forEach(function (r) { r.selectable = false; r.evented = false; });
        }
        fc.renderAll();
    };

    window.getSlotPositions = function () {
        if (!fc || !fc.backgroundImage) return [];
        var scaleX = frameNaturalW / fc.width;
        var scaleY = frameNaturalH / fc.height;
        return fc.getObjects('rect').map(function (r) {
            var bRect = r.getBoundingRect(true, true);
            return {
                x:      Math.round(bRect.left   * scaleX),
                y:      Math.round(bRect.top    * scaleY),
                width:  Math.round(bRect.width  * scaleX),
                height: Math.round(bRect.height * scaleY),
            };
        });
    };

    // ─── Private helpers ─────────────────────────────────────

    function _addRect(left, top, width, height) {
        var rect = new fabric.Rect({
            left:        left,
            top:         top,
            width:       width,
            height:      height,
            fill:        'rgba(249,200,70,0.15)',
            stroke:      '#f9c846',
            strokeWidth: 2,
            strokeUniform: true,
            selectable:  mode === 'select',
            evented:     mode === 'select',
            hasRotatingPoint: false,
        });
        fc.add(rect);
        return rect;
    }

    function _bindEvents() {
        /* ── Draw mode: click + drag to create rect ── */
        fc.on('mouse:down', function (opt) {
            if (mode !== 'draw' || !fc.backgroundImage) return;
            if (opt.target) return; // clicked on existing object
            isDrawing = true;
            var p = fc.getPointer(opt.e);
            origX = p.x; origY = p.y;
            drawRect = _addRect(origX, origY, 1, 1);
        });

        fc.on('mouse:move', function (opt) {
            if (!isDrawing || !drawRect) return;
            var p = fc.getPointer(opt.e);
            var w = p.x - origX, h = p.y - origY;
            drawRect.set({
                left:   w < 0 ? p.x  : origX,
                top:    h < 0 ? p.y  : origY,
                width:  Math.abs(w),
                height: Math.abs(h),
            });
            drawRect.setCoords();
            fc.renderAll();
        });

        fc.on('mouse:up', function () {
            if (!isDrawing) return;
            isDrawing = false;
            if (drawRect && (drawRect.width < 15 || drawRect.height < 15)) {
                fc.remove(drawRect); // too small — discard
            }
            drawRect = null;
            _updateLabels();
            _syncJson();
        });

        /* ── Object modified (moved / resized) ── */
        fc.on('object:modified', function () { _updateLabels(); _syncJson(); });
        fc.on('object:removed',  function () { _updateLabels(); _syncJson(); });
        fc.on('after:render',    function () { _updateLabels(); });

        /* ── Keyboard delete ── */
        document.addEventListener('keydown', function (e) {
            if ((e.key === 'Delete' || e.key === 'Backspace') &&
                document.activeElement.tagName !== 'INPUT' &&
                document.activeElement.tagName !== 'TEXTAREA') {
                deleteSelected();
            }
        });
    }

    function _updateLabels() {
        var container = document.getElementById('slot-labels-container');
        if (!container || !fc) return;
        container.innerHTML = '';

        var rects = fc.getObjects('rect');
        var badge = document.getElementById('slot-count-badge');
        if (badge) badge.textContent = rects.length + ' slot';

        rects.forEach(function (r, i) {
            var center = r.getCenterPoint();
            var zoom   = fc.getZoom();
            var vpt    = fc.viewportTransform;
            var x      = center.x * zoom + vpt[4];
            var y      = center.y * zoom + vpt[5];

            var label = document.createElement('div');
            label.className = 'slot-label-badge';
            label.style.left = x + 'px';
            label.style.top  = y + 'px';
            label.textContent = i + 1;
            container.appendChild(label);
        });
    }

    function _syncJson() {
        var inp = document.getElementById('slot-positions-json');
        if (inp) inp.value = JSON.stringify(getSlotPositions());
    }

    function _togglePlaceholder(show) {
        var ph = document.getElementById('editor-placeholder');
        if (ph) ph.style.display = show ? 'block' : 'none';
    }

    window._syncJson = _syncJson;

})();
</script>
