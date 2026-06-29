/**
 * ESMAR-BURGER — JavaScript Principal
 */

document.addEventListener('DOMContentLoaded', function() {

    // ===== NAVBAR SCROLL EFFECT =====
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ===== NAVBAR TOGGLE (MOBILE) =====
    const navToggle = document.getElementById('navbar-toggle');
    const navMenu = document.getElementById('navbar-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('abierto');
            // Animar las barras del toggle
            navToggle.classList.toggle('activo');
        });

        // Cerrar al hacer click en un enlace
        navMenu.querySelectorAll('.nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                navMenu.classList.remove('abierto');
                navToggle.classList.remove('activo');
            });
        });
    }

    // ===== SIDEBAR TOGGLE (ADMIN) =====
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const adminSidebar = document.getElementById('admin-sidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('abierto');
        });

        // Cerrar sidebar al hacer click fuera
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992 && adminSidebar.classList.contains('abierto')) {
                if (!adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    adminSidebar.classList.remove('abierto');
                }
            }
        });
    }

    // ===== ANIMACIONES AL SCROLL (Intersection Observer) =====
    const elementosAnimar = document.querySelectorAll('.animar');
    if (elementosAnimar.length > 0) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        elementosAnimar.forEach(function(el) {
            observer.observe(el);
        });
    }

    // ===== CERRAR ALERTAS AUTOMÁTICAMENTE =====
    const alertas = document.querySelectorAll('.alerta');
    alertas.forEach(function(alerta) {
        setTimeout(function() {
            alerta.style.opacity = '0';
            alerta.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alerta.remove();
            }, 300);
        }, 5000);
    });

    // ===== PEDIDOS - TOGGLE DETALLE =====
    const pedidoHeaders = document.querySelectorAll('.pedido-header');
    pedidoHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            const body = this.nextElementSibling;
            if (body && body.classList.contains('pedido-body')) {
                body.classList.toggle('abierto');
            }
        });
    });

    // ===== CONFIRMACIONES DE ACCIONES =====
    document.querySelectorAll('[data-confirmar]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var mensaje = this.getAttribute('data-confirmar') || '¿Estás seguro?';
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    });

    // ===== MODAL ADMIN =====
    // Abrir modal
    document.querySelectorAll('[data-modal]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modalId = this.getAttribute('data-modal');
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('activo');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Cerrar modal
    document.querySelectorAll('.modal-cerrar, .modal-overlay').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (e.target === this) {
                this.closest('.modal-overlay').classList.remove('activo');
                document.body.style.overflow = '';
            }
        });
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.activo').forEach(function(modal) {
                modal.classList.remove('activo');
                document.body.style.overflow = '';
            });
        }
    });

    // ===== FILTROS DE CATEGORÍA =====
    const filtrosBtns = document.querySelectorAll('.filtro-btn');
    filtrosBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Remover activo de todos
            filtrosBtns.forEach(function(b) { b.classList.remove('activo'); });
            this.classList.add('activo');

            var categoria = this.getAttribute('data-categoria');
            var productos = document.querySelectorAll('.producto-card');

            productos.forEach(function(card) {
                if (categoria === 'todos' || card.getAttribute('data-categoria') === categoria) {
                    card.style.display = '';
                    card.style.animation = 'fadeIn 0.4s ease-out';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // ===== GRÁFICOS DE BARRAS - ANIMACIÓN =====
    const barras = document.querySelectorAll('.barra');
    if (barras.length > 0) {
        const barrasObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var altura = entry.target.getAttribute('data-height');
                    entry.target.style.height = altura + '%';
                    barrasObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        barras.forEach(function(barra) {
            barra.style.height = '0';
            barrasObserver.observe(barra);
        });
    // ===== AJAX ADD TO CART =====
    const btnsAddCarrito = document.querySelectorAll('.btn-add-carrito');
    btnsAddCarrito.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productoId = this.getAttribute('data-id');
            const originalHTML = this.innerHTML;
            
            // Loading state
            this.innerHTML = '<i class="ph-bold ph-spinner"></i> Agregando...';
            this.style.pointerEvents = 'none';

            fetch('api/agregar_carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ producto_id: productoId })
            })
            .then(response => response.json())
            .then(data => {
                this.innerHTML = originalHTML;
                this.style.pointerEvents = 'auto';

                if (data.success) {
                    mostrarToast(data.mensaje);
                    // Actualizar el contador del carrito en el navbar
                    let cartBadge = document.getElementById('carrito-badge');
                    if (!cartBadge) {
                        const navCarrito = document.querySelector('.nav-carrito');
                        if (navCarrito) {
                            cartBadge = document.createElement('span');
                            cartBadge.className = 'carrito-badge';
                            cartBadge.id = 'carrito-badge';
                            navCarrito.appendChild(cartBadge);
                        }
                    }
                    if (cartBadge) {
                        cartBadge.textContent = data.total_items;
                    }
                } else {
                    mostrarToast(data.mensaje, 'error');
                }
            })
            .catch(error => {
                this.innerHTML = originalHTML;
                this.style.pointerEvents = 'auto';
                mostrarToast('Error al procesar la solicitud.', 'error');
            });
        });
    });

    // ===== TOAST NOTIFICATION SYSTEM =====
    function mostrarToast(mensaje, tipo = 'success') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${tipo}`;
        
        const icono = tipo === 'success' ? '✅' : '❌';
        
        toast.innerHTML = `
            <span class="toast-icono">${icono}</span>
            <span class="toast-texto">${mensaje}</span>
        `;
        
        toastContainer.appendChild(toast);
        
        // Animación de entrada
        setTimeout(() => toast.classList.add('mostrar'), 10);
        
        // Remover después de 3s
        setTimeout(() => {
            toast.classList.remove('mostrar');
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 300);
        }, 3000);
    }

});
