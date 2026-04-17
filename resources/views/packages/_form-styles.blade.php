<style>
.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 12px;
}

.template-card {
    border: 1px solid #dce1eb;
    border-radius: 10px;
    background: #fff;
    transition: all .2s ease;
    position: relative;
}

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
}

.template-thumb {
    width: 72px;
    height: 92px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
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
    color: #94a3b8;
    font-size: 1.3rem;
}

.template-title {
    font-size: .95rem;
    color: #0f172a;
}

.template-meta {
    font-size: .8rem;
    color: #64748b;
}

.template-status-badge {
    display: inline-block;
    font-size: .72rem;
    padding: 2px 8px;
    border-radius: 999px;
    background: #ecfdf3;
    color: #0f8b4c;
}

.template-reason {
    margin-top: 4px;
    font-size: .75rem;
    color: #b91c1c;
    min-height: 18px;
}

.template-check-indicator {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    align-self: start;
}

.template-card.selected {
    border-color: #556ee6;
    box-shadow: 0 0 0 2px rgba(85, 110, 230, .15);
}

.template-card.selected .template-check-indicator {
    border-color: #556ee6;
    background: #556ee6;
    color: #fff;
}

.template-card.incompatible {
    opacity: .45;
    background: #f8fafc;
}

.template-card.incompatible .template-card-body {
    cursor: not-allowed;
}

.template-card.incompatible .template-status-badge {
    background: #fef2f2;
    color: #b91c1c;
}

.selected-template-panel {
    border: 1px solid #dce1eb;
    border-radius: 10px;
    padding: 12px;
    min-height: 280px;
    background: #fff;
}

.selected-template-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.selected-item {
    display: flex;
    gap: 10px;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px;
}

.selected-item img,
.selected-item .selected-empty-thumb {
    width: 46px;
    height: 58px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    object-fit: cover;
    background: #f8fafc;
}

.selected-empty-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

.selected-item h6 {
    margin: 0;
    font-size: .88rem;
    color: #0f172a;
}

.selected-item p {
    margin: 0;
    font-size: .75rem;
    color: #64748b;
}
</style>
