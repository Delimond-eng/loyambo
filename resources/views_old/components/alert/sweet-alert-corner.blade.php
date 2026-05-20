{{-- resources/views/components/sweet-alert-corner.blade.php --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Fonction pour afficher les alertes en coin
function showCornerAlert(message, type = 'success') {
    // Définir les couleurs selon le type
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8',
        validation: '#fd7e14'
    };
    
    const background = colors[type] || colors.success;
    
    // Configuration Toast de SweetAlert2 - Timer réduit à 2000ms (2s)
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000, // 2 secondes
        timerProgressBar: true,
        background: background,
        color: '#ffffff',
        customClass: {
            popup: 'colored-toast'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    
    Toast.fire({
        icon: type,
        title: message
    });
}

// Fonction pour les erreurs de validation Laravel
function showValidationErrors(errors) {
    let errorMessages = '';
    
    if (typeof errors === 'object') {
        // Si c'est un objet d'erreurs Laravel
        Object.values(errors).forEach(errorArray => {
            errorArray.forEach(error => {
                errorMessages += `${error}<br>`;
            });
        });
    } else if (typeof errors === 'string') {
        errorMessages = errors;
    } else {
        errorMessages = 'Une erreur de validation est survenue';
    }
    
    showCornerAlert(errorMessages, 'validation');
}

// Fonctions helpers pour différents types d'alertes
function showSuccess(message) {
    showCornerAlert(message, 'success');
}

function showError(message) {
    showCornerAlert(message, 'error');
}

function showWarning(message) {
    showCornerAlert(message, 'warning');
}

function showInfo(message) {
    showCornerAlert(message, 'info');
}

// Écouter les événements Livewire si utilisé
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les messages flash Laravel
    @if(session('success'))
        showSuccess('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        showError('{{ session('error') }}');
    @endif
    
    @if(session('warning'))
        showWarning('{{ session('warning') }}');
    @endif
    
    @if(session('info'))
        showInfo('{{ session('info') }}');
    @endif
    
    @if($errors->any())
        showValidationErrors({!! json_encode($errors->all()) !!});
    @endif
});
</script>

<style>
/* Personnalisation des Toast */
.colored-toast {
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    border: none !important;
}

.colored-toast.swal2-icon-success {
    border-left: 4px solid #1e7e34 !important;
}

.colored-toast.swal2-icon-error {
    border-left: 4px solid #c82333 !important;
}

.colored-toast.swal2-icon-warning {
    border-left: 4px solid #e0a800 !important;
}

.colored-toast.swal2-icon-info {
    border-left: 4px solid #138496 !important;
}

.colored-toast.swal2-icon-question {
    border-left: 4px solid #6c757d !important;
}

/* Animation d'entrée et de sortie plus rapides */
.swal2-popup {
    animation: slideInRight 0.3s ease-out !important;
}

.swal2-hide {
    animation: slideOutRight 0.3s ease-in !important;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Barre de progression plus rapide */
.swal2-timer-progress-bar {
    animation: timerProgress 2s linear !important;
}

@keyframes timerProgress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .colored-toast {
        margin: 10px !important;
        max-width: calc(100% - 20px) !important;
    }
}
</style>
@endpush