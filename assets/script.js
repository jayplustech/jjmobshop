// Cart Logic
let cart = JSON.parse(localStorage.getItem('jj_cart')) || [];

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();

    // If on checkout page, render cart
    if (window.location.pathname.includes('checkout.php')) {
        renderCheckout();
    }
});

function addToCart(id, name, price, image) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ id, name, price, image, quantity: 1 });
    }
    saveCart();
    alert(name + ' added to cart!');
}

function saveCart() {
    localStorage.setItem('jj_cart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const countEl = document.getElementById('cart-count');

    if (countEl) {
        countEl.innerText = count;

        // Trigger Animation
        countEl.classList.remove('bump');
        void countEl.offsetWidth; // Trigger reflow
        countEl.classList.add('bump');

        // Optional: Hide if 0
        if (count === 0) {
            countEl.style.display = 'none';
        } else {
            countEl.style.display = 'inline-block';
        }
    }
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
                <td>$${item.price}</td>
                <td>
                    <button class="btn-qty" onclick="updateQuantity(${item.id}, -1)">-</button> 
                    ${item.quantity} 
                    <button class="btn-qty" onclick="updateQuantity(${item.id}, 1)">+</button>
                </td>
                <td>$${itemTotal.toFixed(2)}</td>
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
