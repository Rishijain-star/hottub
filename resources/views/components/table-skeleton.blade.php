<div class="table-skeleton">
    <div class="skeleton-header">
        @for($i = 0; $i < ($columns ?? 5); $i++)
            <div class="skeleton-cell"></div>
        @endfor
    </div>
    @for($j = 0; $j < ($rows ?? 7); $j++)
        <div class="skeleton-row">
            @for($i = 0; $i < ($columns ?? 5); $i++)
                <div class="skeleton-cell"></div>
            @endfor
        </div>
    @endfor
</div>

<style>
.table-skeleton {
    width: 100%;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #eee;
}
.skeleton-header, .skeleton-row {
    display: flex;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}
.skeleton-header {
    background: #f9fafb;
}
.skeleton-cell {
    flex: 1;
    height: 20px;
    background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    margin: 0 10px;
    border-radius: 4px;
}
.skeleton-header .skeleton-cell {
    background: #e5e7eb;
}
@keyframes skeleton-loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>
