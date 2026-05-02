<style>
/* ─────────── Form shell ─────────── */
.pkg-form-shell {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ─────────── Section card ─────────── */
.pkg-section {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.05);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}
.pkg-section-head {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.4rem 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    background: linear-gradient(180deg, #fafaf5 0%, #fff 100%);
}
.pkg-section-num {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #18181b;
    color: #fafaf5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
    flex-shrink: 0;
}
.pkg-section-title {
    font-weight: 700;
    color: #18181b;
    font-size: 1.05rem;
    letter-spacing: -0.01em;
    margin: 0;
}
.pkg-section-sub {
    font-size: 0.83rem;
    color: #71717a;
    margin: 2px 0 0 0;
    line-height: 1.45;
}
.pkg-section-body {
    padding: 1.5rem;
}

/* ─────────── Labels & inputs ─────────── */
.pkg-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #18181b;
    margin-bottom: 0.4rem;
    letter-spacing: 0;
}
.pkg-label-hint {
    color: #a1a1aa;
    font-weight: 500;
    font-size: 0.78rem;
}
.pkg-help-text {
    font-size: 0.78rem;
    color: #71717a;
    margin: 0 0 0.7rem 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.pkg-help-text .mdi { color: #a1a1aa; font-size: 1rem; }
.pkg-input {
    border-radius: 10px !important;
    border: 1.5px solid #e4e4e7 !important;
    padding: 0.75rem 0.9rem !important;
    font-size: 0.92rem !important;
    transition: border-color 0.18s, box-shadow 0.18s !important;
}
.pkg-input:focus {
    border-color: #18181b !important;
    box-shadow: 0 0 0 4px rgba(24,24,27,0.08) !important;
}

/* ─────────── Toggle switch (active/inactive) ─────────── */
.pkg-toggle-row {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 1rem 1.15rem;
    background: #fafaf5;
    border-radius: 12px;
    cursor: pointer;
    margin: 0;
    transition: background 0.15s;
}
.pkg-toggle-row:hover { background: #f4f4ed; }
.pkg-toggle-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.pkg-toggle-switch {
    width: 42px;
    height: 24px;
    background: #d4d4d8;
    border-radius: 999px;
    position: relative;
    transition: background 0.2s;
    flex-shrink: 0;
    margin-top: 1px;
}
.pkg-toggle-switch::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.pkg-toggle-input:checked ~ .pkg-toggle-switch {
    background: #22c55e;
}
.pkg-toggle-input:checked ~ .pkg-toggle-switch::after {
    transform: translateX(18px);
}
.pkg-toggle-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.pkg-toggle-title {
    font-weight: 600;
    color: #18181b;
    font-size: 0.92rem;
}
.pkg-toggle-desc {
    font-size: 0.78rem;
    color: #71717a;
}

/* ─────────── Chip group (jumlah foto / cetak) ─────────── */
.pkg-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}
.pkg-chip-btn {
    background: #fafaf5;
    border: 1.5px solid transparent;
    color: #52525b;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.6rem 1.1rem;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.15s ease;
    font-variant-numeric: tabular-nums;
}
.pkg-chip-btn:hover {
    background: #f4f4ed;
    color: #18181b;
}
.pkg-chip-btn.active {
    background: #18181b;
    color: #fafaf5;
    border-color: #18181b;
    box-shadow: 0 4px 10px -3px rgba(24,24,27,0.3);
}
.pkg-chip-custom {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-left: 0.25rem;
    padding-left: 0.75rem;
    border-left: 1px dashed #e4e4e7;
}
.pkg-chip-custom input {
    border-radius: 8px !important;
    border: 1.5px solid #e4e4e7 !important;
    text-align: center;
    font-weight: 600 !important;
}

/* ─────────── Print size cards (visual radio) ─────────── */
.pkg-size-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
}
.pkg-size-card {
    position: relative;
    background: #fff;
    border: 1.5px solid #e4e4e7;
    border-radius: 14px;
    padding: 1rem 0.9rem;
    cursor: pointer;
    transition: all 0.18s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.65rem;
    margin: 0;
}
.pkg-size-card:hover {
    border-color: #a1a1aa;
    background: #fafaf5;
}
.pkg-size-card.active {
    border-color: #18181b;
    background: #fafaf5;
    box-shadow: 0 0 0 3px rgba(24,24,27,0.08);
}
.pkg-size-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.pkg-size-preview {
    background: linear-gradient(135deg, #fef9c3 0%, #fde68a 100%);
    border: 1.5px dashed #c9a800;
    border-radius: 4px;
    flex-shrink: 0;
    max-width: 100%;
    max-height: 100px;
}
.pkg-size-info {
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.pkg-size-name {
    font-weight: 700;
    color: #18181b;
    font-size: 0.95rem;
}
.pkg-size-desc {
    font-size: 0.72rem;
    color: #71717a;
    line-height: 1.3;
}
.pkg-size-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 22px;
    height: 22px;
    background: #18181b;
    color: #fafaf5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    opacity: 0;
    transition: opacity 0.18s;
}
.pkg-size-card.active .pkg-size-check {
    opacity: 1;
}

/* ─────────── Price input ─────────── */
.pkg-price-input-wrap {
    display: flex;
    align-items: center;
    border: 1.5px solid #e4e4e7;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    transition: border-color 0.18s, box-shadow 0.18s;
}
.pkg-price-input-wrap:focus-within {
    border-color: #18181b;
    box-shadow: 0 0 0 4px rgba(24,24,27,0.08);
}
.pkg-price-prefix {
    padding: 0.85rem 1rem;
    font-weight: 600;
    color: #71717a;
    background: #fafaf5;
    border-right: 1.5px solid #e4e4e7;
    font-size: 0.95rem;
}
.pkg-price-input {
    border: none !important;
    flex: 1;
    padding: 0.85rem 1rem !important;
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    color: #18181b !important;
    background: transparent !important;
    outline: none !important;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.01em;
}
.pkg-price-input:focus { box-shadow: none !important; }
.pkg-price-preview {
    background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
    border-radius: 12px;
    padding: 0.85rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.pkg-price-preview-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #b45309;
    font-weight: 600;
}
.pkg-price-preview strong {
    font-size: 1.35rem;
    font-weight: 800;
    color: #18181b;
    letter-spacing: -0.02em;
    font-variant-numeric: tabular-nums;
}

/* ─────────── Template hint + toggle ─────────── */
.pkg-template-hint {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.83rem;
    color: #71717a;
    background: #fafaf5;
    padding: 0.55rem 0.9rem;
    border-radius: 8px;
    flex: 1;
}
.pkg-template-hint.hint-success {
    background: #f0fdf4;
    color: #15803d;
}
.pkg-template-hint.hint-danger {
    background: #fef2f2;
    color: #b91c1c;
}
.pkg-template-hint .mdi {
    flex-shrink: 0;
    font-size: 1rem;
}
.pkg-template-toggle {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.83rem;
    color: #52525b;
    cursor: pointer;
    user-select: none;
    margin: 0;
}
.pkg-template-toggle input {
    accent-color: #18181b;
    cursor: pointer;
}
.pkg-template-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    background: #fafaf5;
    border: 1.5px dashed #e4e4e7;
    border-radius: 12px;
    color: #71717a;
}
.pkg-template-empty .mdi {
    font-size: 2.4rem;
    color: #a1a1aa;
    margin-bottom: 0.5rem;
}

/* ─────────── Template cards ─────────── */
.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 12px;
}
.template-card {
    border: 1.5px solid #e4e4e7;
    border-radius: 12px;
    background: #fff;
    transition: all .18s ease;
    position: relative;
    cursor: pointer;
    user-select: none;
}
.template-card:hover { border-color: #a1a1aa; }
.template-checkbox {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.template-card-body {
    margin: 0;
    display: grid;
    grid-template-columns: 72px 1fr 24px;
    gap: 10px;
    padding: 12px;
    cursor: pointer;
    min-height: 116px;
}
.template-thumb {
    width: 72px;
    height: 92px;
    border-radius: 8px;
    border: 1px solid #e4e4e7;
    background: #fafaf5;
    overflow: hidden;
}
.template-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.template-thumb-empty {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a1a1aa;
    font-size: 1.3rem;
}
.template-title {
    font-size: .92rem;
    color: #18181b;
    font-weight: 600;
}
.template-meta {
    font-size: .76rem;
    color: #71717a;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}
.template-size-badge {
    display: inline-block;
    padding: 1px 6px;
    background: #fef9c3;
    color: #b45309;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.7rem;
}
.template-slot-info {
    color: #71717a;
}
.template-status-badge {
    display: inline-block;
    font-size: .68rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 999px;
    background: #f0fdf4;
    color: #15803d;
}
.template-reason {
    margin-top: 4px;
    font-size: .72rem;
    color: #b91c1c;
    min-height: 0;
}
.template-check-indicator {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    border: 1.5px solid #d4d4d8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    align-self: start;
    transition: all 0.15s;
}
.template-card.selected {
    border-color: #18181b;
    box-shadow: 0 0 0 3px rgba(24,24,27,0.08);
    background: #fafaf5;
}
.template-card.selected .template-check-indicator {
    border-color: #18181b;
    background: #18181b;
    color: #fff;
}
.template-card.incompatible {
    opacity: .4;
    background: #fafaf5;
    cursor: not-allowed;
}
.template-card.incompatible .template-card-body {
    cursor: not-allowed;
}
.template-card.incompatible .template-status-badge {
    background: #fef2f2;
    color: #b91c1c;
}
.template-card.hidden-incompatible { display: none; }

/* Selected preview */
.pkg-selected-preview {
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 12px;
    padding: 1rem 1.15rem;
    background: #fafaf5;
}
.selected-template-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.selected-item {
    display: flex;
    gap: 8px;
    align-items: center;
    border: 1px solid #e4e4e7;
    background: #fff;
    border-radius: 8px;
    padding: 6px 10px 6px 6px;
}
.selected-item img,
.selected-item .selected-empty-thumb {
    width: 32px;
    height: 40px;
    border-radius: 4px;
    border: 1px solid #e4e4e7;
    object-fit: cover;
    background: #fafaf5;
    flex-shrink: 0;
}
.selected-empty-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a1a1aa;
    font-size: 0.85rem;
}
.selected-item h6 {
    margin: 0;
    font-size: .8rem;
    color: #18181b;
    font-weight: 600;
}
.selected-item p {
    margin: 0;
    font-size: .68rem;
    color: #71717a;
}
.selected-empty {
    padding: 0.5rem 0;
}

/* ─────────── Action bar ─────────── */
.pkg-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 0 0.5rem;
    flex-wrap: wrap;
}
.pkg-action-bar .btn-lg {
    padding: 0.85rem 1.75rem;
    font-size: 0.93rem;
    font-weight: 600;
    border-radius: 12px;
}
.pkg-submit {
    box-shadow: 0 6px 16px -6px rgba(24,24,27,0.4);
    transition: all 0.18s;
}
.pkg-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px -6px rgba(24,24,27,0.5);
}
</style>
