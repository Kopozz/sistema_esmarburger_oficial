<?php
/**
 * ESMAR BURGER - Carta Interactiva (Index)
 * Avance 2 - Ingeniería Web
 */
require_once 'config.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();
// Obtener todos los productos
$stmt = $pdo->query("SELECT * FROM productos");
$productos = $stmt->fetchAll();

// Obtener todas las categorías únicas
$stmtCat = $pdo->query("SELECT DISTINCT categoria FROM productos");
$categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <span class="hero-subtitle">Calidad Premium</span>
        <h1 class="hero-title">Sabor que te hará flotar en <span>Gravedad Cero</span></h1>
        <p class="hero-desc">Disfruta de las mejores hamburguesas y broasters artesanales, preparados con ingredientes seleccionados. ¡Todos nuestros platos salen con bebida gratis!</p>
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <a href="#menu-carta" class="btn btn-primary">Ver la Carta</a>
            <a href="tel:935550240" class="btn btn-secondary">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                935 550 240
            </a>
        </div>
    </div>
    <div class="hero-img-container">
        <div class="hero-circle"></div>
        <!-- Usaremos un SVG premium animado para representar la deliciosa hamburguesa si no cargamos una imagen directa -->
        <svg class="hero-img" viewBox="0 0 500 500" width="380" height="380" xmlns="http://www.w3.org/2000/svg">
            <!-- Glow background -->
            <defs>
                <radialGradient id="burgerGlow" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#ff6b00" stop-opacity="0.3"/>
                    <stop offset="100%" stop-color="transparent" stop-opacity="0"/>
                </radialGradient>
            </defs>
            <circle cx="250" cy="250" r="200" fill="url(#burgerGlow)" />
            
            <!-- Burger components stacked -->
            <!-- Top Bun -->
            <path d="M120 220 C120 120, 380 120, 380 220 Z" fill="#E67E22" stroke="#D35400" stroke-width="4" />
            <!-- Sesame seeds -->
            <circle cx="180" cy="160" r="3" fill="#FFF" />
            <circle cx="220" cy="140" r="3" fill="#FFF" />
            <circle cx="250" cy="170" r="3" fill="#FFF" />
            <circle cx="280" cy="150" r="3" fill="#FFF" />
            <circle cx="320" cy="180" r="3" fill="#FFF" />
            
            <!-- Melted Cheese -->
            <path d="M115 220 L385 220 L360 250 L310 230 L270 265 L210 230 L170 255 L140 225 Z" fill="#F1C40F" />
            
            <!-- Patty -->
            <rect x="110" y="245" width="280" height="35" rx="10" fill="#5C3A21" stroke="#3E2723" stroke-width="3" />
            
            <!-- Lettuce (wavy) -->
            <path d="M100 280 C130 295, 170 265, 200 280 C230 295, 270 265, 300 280 C330 295, 370 265, 400 280 L385 295 L115 295 Z" fill="#2ECC71" />
            
            <!-- Bottom Bun -->
            <path d="M120 295 L380 295 C380 330, 120 330, 120 295 Z" fill="#D35400" />
            
            <!-- Shadow -->
            <ellipse cx="250" cy="360" rx="140" ry="15" fill="rgba(0,0,0,0.4)" />
        </svg>
    </div>
</section>

<!-- Menu / Carta Section -->
<section class="menu-section" id="menu-carta">
    <div class="section-header">
        <h2 class="section-title">Nuestra <span>Carta</span></h2>
        <p style="color: var(--text-secondary);">Elige tus favoritos de nuestro menú seleccionado</p>
    </div>

    <!-- Filtros de Categoría -->
    <div class="category-tabs">
        <button class="tab-btn active" data-category="todos">Todos</button>
        <?php foreach ($categorias as $cat): ?>
            <button class="tab-btn" data-category="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $cat))); ?>">
                <?php echo htmlspecialchars($cat); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Buscador Sutil -->
    <div style="max-width: 500px; margin: 0 auto 3rem; display: flex; gap: 0.5rem;">
        <input type="text" id="menu-search" class="form-input" placeholder="Buscar hamburguesas, combos, broaster..." style="border-radius: 30px; padding-left: 1.5rem;">
    </div>

    <!-- Grid de Platos -->
    <div class="menu-grid" id="menu-grid">
        <?php foreach ($productos as $prod): ?>
            <?php 
                $catClass = htmlspecialchars(strtolower(str_replace(' ', '-', $prod['categoria'])));
            ?>
            <div class="product-card glass-panel" data-category="<?php echo $catClass; ?>" data-name="<?php echo htmlspecialchars(strtolower($prod['nombre'])); ?>">
                <div class="product-img-wrapper">
                    <span class="product-category"><?php echo htmlspecialchars($prod['categoria']); ?></span>
                    
                    <!-- SVG Ilustrativo por categoría para simular fotos de platos de forma premium y evitar enlaces rotos -->
                    <div class="product-img-placeholder">
                        <?php if ($prod['categoria'] === 'Hamburguesas'): ?>
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.5">
                                <path d="M3 11c0-4 4-7 9-7s9 3 9 7M2 13h20M2 16h20M3 18c0 2 4 3 9 3s9-1 9-3"/>
                            </svg>
                        <?php elseif ($prod['categoria'] === 'Salchipapas'): ?>
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--secondary)" stroke-width="1.5">
                                <path d="M5 3l2 18M9 3l1 18M13 3l-1 18M17 3l-2 18M21 3l-3 18M3 18h18"/>
                            </svg>
                        <?php elseif ($prod['categoria'] === 'Broaster'): ?>
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.5">
                                <path d="M12 21c-4.97 0-9-4.03-9-9s4.03-9 9-9 9 4.03 9 9-4.03 9-9 9z M12 6v12M6 12h12"/>
                            </svg>
                        <?php else: ?>
                            <!-- Combos -->
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--secondary)" stroke-width="1.5">
                                <path d="M3 3h18v18H3z M8 8h8M8 12h8M8 16h8"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-title"><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                    <p class="product-desc"><?php echo htmlspecialchars($prod['descripcion']); ?></p>
                    <div class="product-footer">
                        <span class="product-price">S/ <?php echo number_format($prod['precio'], 2); ?></span>
                        
                        <button class="btn btn-primary add-to-cart-btn" data-id="<?php echo $prod['id']; ?>" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                            Añadir +
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Footer de Información adicional de Delivery -->
<section style="background: rgba(0,0,0,0.2); border-y: 1px solid var(--border-color); padding: 4rem 2rem; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h3 class="title-decor" style="color: var(--primary); font-size: 1.8rem; margin-bottom: 1rem;">¡TODOS LOS PLATOS SALEN CON BEBIDA!</h3>
        <p style="color: var(--text-secondary); margin-bottom: 2rem;">¿Prefieres hacer tu pedido directo al WhatsApp? Llámanos o escríbenos a nuestros números de atención inmediata.</p>
        <div style="display: flex; justify-content: center; gap: 3rem; flex-wrap: wrap;">
            <div>
                <p style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase;">Delivery Central 1</p>
                <p style="font-size: 1.5rem; font-weight: 800; color: var(--secondary);">935 550 240</p>
            </div>
            <div>
                <p style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase;">Delivery Central 2</p>
                <p style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">921 157 440</p>
            </div>
        </div>
    </div>
</section>

<!-- Notificación emergente flotante minimalista -->
<div id="toast" style="position: fixed; bottom: 2rem; right: 2rem; background: var(--bg-card); border-left: 4px solid var(--success); padding: 1rem 1.5rem; border-radius: var(--radius-sm); color: var(--text-primary); opacity: 0; pointer-events: none; transition: var(--transition); z-index: 1000; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
    Producto agregado al carrito.
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Filtrar por categorías con pestañas
    const tabBtns = document.querySelectorAll('.tab-btn');
    const productCards = document.querySelectorAll('.product-card');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const category = btn.getAttribute('data-category');
            
            productCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                if (category === 'todos' || cardCategory === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // 2. Buscador en tiempo real
    const searchInput = document.getElementById('menu-search');
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        
        productCards.forEach(card => {
            const name = card.getAttribute('data-name');
            const matchesSearch = name.includes(query);
            
            const activeTab = document.querySelector('.tab-btn.active');
            const category = activeTab.getAttribute('data-category');
            const cardCategory = card.getAttribute('data-category');
            
            const matchesCategory = (category === 'todos' || cardCategory === category);

            if (matchesSearch && matchesCategory) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // 3. Sistema dinámico de añadir al carrito sin recargar
    const addBtns = document.querySelectorAll('.add-to-cart-btn');
    const badge = document.querySelector('.badge');
    const toast = document.getElementById('toast');

    addBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const prodId = this.getAttribute('data-id');

            // Petición AJAX/Fetch para guardar en la sesión del carrito
            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&id=${prodId}&qty=1`
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    // Actualizar el número del badge en el header
                    if (badge) {
                        badge.textContent = data.totalItems;
                    } else {
                        // Si no existía el badge, recargar para mostrarlo en el header de forma limpia
                        window.location.reload();
                    }
                    
                    // Mostrar notificación flotante
                    toast.style.opacity = '1';
                    setTimeout(() => {
                        toast.style.opacity = '0';
                    }, 2500);
                }
            })
            .catch(err => console.error(err));
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
