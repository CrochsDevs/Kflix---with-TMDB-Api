<div class="modal-overlay" id="movieModal">
    <div class="modal-container">
        <button class="modal-close" id="closeModal">
            <i class="fas fa-times"></i>
        </button>

        <div class="modal-body" id="modalBody">
            <div id="modalLoading" class="spinner-container">
                <div class="spinner"></div>
            </div>

            <div id="modalContent"></div>
        </div>

        <div class="modal-footer">
            <button class="btn-play-modal" id="modalPlayBtn">
                <i class="fas fa-play"></i> Watch Now
            </button>
            <button class="btn-add-list" id="modalAddToList">
                <i class="far fa-heart"></i> My List
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<style>
/* ==================== MODAL STYLES ==================== */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.modal-container {
    background-color: #141414;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.7);
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background-color: #e50914;
}

.modal-body {
    max-height: calc(90vh - 70px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #e50914 #333;
}

.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #333;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #e50914;
    border-radius: 4px;
}

/* Spinner */
.spinner-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-top-color: #e50914;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Modal Content */
#modalContent {
    padding: 20px;
}

.modal-movie-content {
    display: flex;
    gap: 30px;
    color: white;
}

.modal-movie-poster {
    flex: 0 0 250px;
}

.modal-movie-poster img {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.modal-movie-info {
    flex: 1;
}

.modal-movie-title {
    font-size: 2rem;
    margin: 0 0 15px 0;
    color: white;
}

.modal-movie-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    color: #aaa;
}

.modal-movie-rating {
    color: #ffd700;
    font-weight: bold;
}

.modal-movie-year,
.modal-movie-runtime,
.modal-movie-maturity {
    padding: 2px 8px;
    background-color: #333;
    border-radius: 4px;
}

.modal-movie-tagline {
    font-style: italic;
    color: #ccc;
    margin: 15px 0;
}

.modal-movie-overview {
    line-height: 1.6;
    color: #ddd;
    margin: 20px 0;
}

.modal-movie-details {
    background-color: rgba(0, 0, 0, 0.3);
    padding: 20px;
    border-radius: 8px;
    border-left: 3px solid #e50914;
}

.modal-movie-detail {
    margin-bottom: 10px;
    color: #ccc;
}

.modal-movie-detail strong {
    color: white;
    margin-right: 10px;
}

/* Modal Footer */
.modal-footer {
    display: flex;
    gap: 15px;
    padding: 20px;
    background-color: #1a1a1a;
    border-top: 1px solid #333;
}

.btn-play-modal,
.btn-add-list {
    padding: 12px 25px;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-play-modal {
    background-color: white;
    color: black;
}

.btn-play-modal:hover {
    background-color: rgba(255, 255, 255, 0.8);
}

.btn-add-list {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-add-list:hover {
    background-color: rgba(255, 255, 255, 0.2);
    border-color: #e50914;
}

.btn-add-list i.fas {
    color: #e50914;
}

/* Toast Container */
.toast-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 10000;
}

.toast {
    background-color: #333;
    color: white;
    padding: 12px 24px;
    border-radius: 4px;
    margin-top: 10px;
    border-left: 3px solid #e50914;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .modal-container {
        width: 95%;
    }
    
    .modal-movie-content {
        flex-direction: column;
    }
    
    .modal-movie-poster {
        flex: 0 0 auto;
        max-width: 200px;
        margin: 0 auto;
    }
    
    .modal-footer {
        flex-direction: column;
    }
    
    .btn-play-modal,
    .btn-add-list {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- REMOVE ANY JAVASCRIPT THAT SETS document.title IN THIS FILE -->
<script>
    // WALANG document.title DITO - KFLIX LANG!
</script>