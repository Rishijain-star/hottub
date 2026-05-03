<div class="modal-backdrop" id="globalDeleteConfirmModal">
    <div class="modal-container modal-container--sm">
        <div class="modal-header">
            <div class="modal-title">Confirm Delete</div>
            <button type="button" class="modal-close" data-delete-close>✕</button>
        </div>
        <div class="text-sm text-muted" id="globalDeleteConfirmText">Are you sure you want to delete this item?</div>
        <form id="globalDeleteConfirmForm" method="POST" action="#">
            @csrf
            @method('DELETE')
            <div class="modal-actions">
                <button type="submit" class="btn btn--primary">OK</button>
                <button type="button" class="btn" data-delete-close>Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('globalDeleteConfirmModal');
    const deleteForm = document.getElementById('globalDeleteConfirmForm');
    const deleteText = document.getElementById('globalDeleteConfirmText');

    if (!deleteModal || !deleteForm || !deleteText) {
        return;
    }

    const openDeleteModal = function () {
        deleteModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeDeleteModal = function () {
        deleteModal.classList.remove('active');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.js-open-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = btn.getAttribute('data-action');
            const entity = btn.getAttribute('data-entity') || 'item';
            if (!action) {
                return;
            }

            deleteForm.action = action;
            deleteText.textContent = 'Are you sure you want to delete this ' + entity + '?';
            openDeleteModal();
        });
    });

    deleteModal.querySelectorAll('[data-delete-close]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            closeDeleteModal();
        });
    });

    deleteModal.addEventListener('click', function (e) {
        if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && deleteModal.classList.contains('active')) {
            closeDeleteModal();
        }
    });
});
</script>
