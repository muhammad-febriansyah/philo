<style>
/* ─── Upload Zone ─── */
.upload-zone {
    border: 2px dashed #ced4da;
    border-radius: 12px;
    padding: 1.75rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
    background: #f8f9fb;
    position: relative;
    min-height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
.upload-zone:hover, .upload-zone.dragover { border-color: #556ee6; background: #eef1fd; }
.upload-zone input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; z-index: 2; width: 100%; height: 100%;
}
.zone-placeholder i { font-size: 2.2rem; color: #aab3c6; }
.zone-placeholder p  { margin: .4rem 0 .1rem; font-weight: 500; font-size: .88rem; color: #495057; }
.zone-placeholder span { font-size: .78rem; color: #9aa1ad; }
.zone-preview { display: none; position: relative; }
.zone-preview img { max-height: 100px; border-radius: 10px; border: 2px solid #556ee6; box-shadow: 0 4px 14px rgba(85,110,230,.18); }
.zone-preview .btn-zone-clear {
    position: absolute; top: -8px; right: -8px; z-index: 10;
    width: 24px; height: 24px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center; padding: 0; font-size: 11px;
}

/* ─── Fabric Canvas Wrapper ─── */
#fabric-canvas-wrapper {
    position: relative;
    background: repeating-conic-gradient(#2a2a3e 0% 25%, #1a1a2e 0% 50%) 0 0 / 20px 20px;
    border-radius: 0 0 12px 12px;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
#fabric-canvas-wrapper canvas { display: block; }

/* Editor placeholder */
#editor-placeholder {
    color: #6c757d;
    text-align: center;
    padding: 3rem;
    pointer-events: none;
}
#editor-placeholder i { font-size: 3.5rem; opacity: .25; display: block; margin-bottom: 1rem; }
#editor-placeholder p { margin: 0; font-size: .9rem; line-height: 1.6; }

/* HTML slot number labels */
#slot-labels-container {
    position: absolute; inset: 0;
    pointer-events: none;
}
.slot-label-badge {
    position: absolute;
    transform: translate(-50%, -50%);
    background: #f9c846;
    color: #1a1a2e;
    font-size: 13px;
    font-weight: 700;
    border-radius: 50%;
    width: 26px; height: 26px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.4);
    border: 2px solid rgba(255,255,255,.6);
    pointer-events: none;
}

/* ─── Editor Toolbar ─── */
.editor-toolbar {
    background: #f8f9fb;
    border-bottom: 1px solid #e9ecef;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.editor-toolbar .mode-btn.active {
    background: #556ee6;
    color: #fff;
    border-color: #556ee6;
}

/* ─── Tips bar ─── */
.editor-tips {
    padding: 8px 14px;
    background: #fafafa;
    border-top: 1px solid #e9ecef;
    font-size: .75rem;
    color: #9aa1ad;
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    border-radius: 0 0 12px 12px;
}

/* ─── Switch ─── */
.form-switch-md .form-check-input { width: 44px; height: 22px; cursor: pointer; }
</style>
