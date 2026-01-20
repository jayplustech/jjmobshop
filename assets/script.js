// Cart Logic
let cart = JSON.parse(localStorage.getItem('jj_cart')) || [];

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();

    // If on checkout page, render cart
    if (window.location.pathname.includes('checkout.php')) {
        renderCheckout();
    }

    // Sidebar Toggle Logic
    const menuBtn = document.getElementById('menu-toggle');
    const closeBtn = document.getElementById('menu-close');
    const sidebar = document.getElementById('sidebar-nav');
    const overlay = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }

    if (menuBtn) menuBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
});

function addToCart(id, name, price, image) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ id, name, price, image, quantity: 1 });
    }
    saveCart();
    showToast('Success', name + ' added to cart!');
}

function showToast(title, message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-msg">${message}</div>
        </div>
    `;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

function saveCart() {
    localStorage.setItem('jj_cart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    // Read local storage cart
    let currentCart = JSON.parse(localStorage.getItem('jj_cart')) || [];
    let localCount = currentCart.reduce((sum, item) => sum + item.quantity, 0);

    const countEl = document.getElementById('cart-count');
    if (!countEl) return;

    // Set display based on local cart
    countEl.innerText = localCount;
    countEl.style.display = (localCount > 0) ? 'inline-block' : 'none';
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    saveCart();
    renderCheckout();
}

function updateQuantity(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(id);
        } else {
            saveCart();
            renderCheckout();
        }
    }
}

function renderCheckout() {
    const container = document.getElementById('cart-display');
    const totalEl = document.getElementById('cart-total');
    const inputEl = document.getElementById('cart-data-input');

    if (!container) return; // Not on checkout page or element missing

    if (cart.length === 0) {
        container.innerHTML = '<p>Your cart is empty.</p>';
        if (totalEl) totalEl.innerText = '0.00';
        return;
    }

    let html = '<table><thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th>Action</th></tr></thead><tbody>';
    let total = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        html += `
            <tr>
                <td>${item.name}</td>
                <td>TZS ${item.price}</td>
                <td>
                    <button class="btn-qty" onclick="updateQuantity(${item.id}, -1)">-</button> 
                    ${item.quantity} 
                    <button class="btn-qty" onclick="updateQuantity(${item.id}, 1)">+</button>
                </td>
                <td>TZS ${itemTotal.toFixed(2)}</td>
                <td><button onclick="removeFromCart(${item.id})" class="btn-danger" style="padding:5px;">X</button></td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    container.innerHTML = html;

    if (totalEl) totalEl.innerText = total.toFixed(2);

    // Update hidden input for form submission
    if (inputEl) inputEl.value = JSON.stringify(cart);
}
