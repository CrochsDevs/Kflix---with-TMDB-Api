<div id="movieModal" class="modal" style="display:none;">
    <div class="modal-overlay" onclick="closeMovieModal()"></div>
    <div class="modal-content" id="modalContent">
        <div class="modal-header">
            <button class="modal-close" id="closeModal" onclick="closeMovieModal()">&times;</button>
        </div>
        <div id="modalLoading" style="display:none;justify-content:center;align-items:center;height:400px;"><div class="spinner"></div></div>
        <div id="modalBody"></div>
        <div class="modal-footer" id="modalFooter">
            <button class="btn-play" id="modalPlayBtn"><i class="fas fa-play"></i> Play</button>
            <button class="btn-more" id="modalAddToList"><i class="far fa-heart"></i> Add to List</button>
        </div>
    </div>
</div>
