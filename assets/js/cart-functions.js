/**
 * File JavaScript bổ sung cho PhoneShop
 * Thêm vào cuối file assets/js/main.js
 */

// ==================== UPDATE CART COUNT ====================
function updateCartCount() {
    // Nếu đã đăng nhập, lấy từ server
    fetch(SITE_URL + '/api/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('cart-count').textContent = data.count;
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

// Update cart count khi load trang
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

// ==================== AJAX ADD TO CART ====================
function addToCartAjax(productId, quantity = 1) {
    // Hiển thị loading
    showToast('Đang thêm vào giỏ...', 'info');
    
    fetch(SITE_URL + '/api/cart-add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Đã thêm vào giỏ hàng!', 'success');
            
            // Update cart count badge
            if (data.cart_count !== undefined) {
                document.getElementById('cart-count').textContent = data.cart_count;
            }
            
            // Animate cart icon
            const cartBtn = document.querySelector('.cart-btn');
            if (cartBtn) {
                cartBtn.classList.add('bounce');
                setTimeout(() => cartBtn.classList.remove('bounce'), 500);
            }
        } else {
            showToast(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra!', 'error');
    });
}

// ==================== CART UPDATE QUANTITY ====================
function updateCartQuantity(productId, change) {
    fetch(SITE_URL + '/api/cart-update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            change: change
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload để cập nhật tổng tiền
        } else {
            showToast(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra!', 'error');
    });
}

// ==================== CART REMOVE ITEM ====================
function removeCartItem(productId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        return;
    }
    
    fetch(SITE_URL + '/api/cart-remove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Đã xóa sản phẩm!', 'success');
            location.reload();
        } else {
            showToast(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra!', 'error');
    });
}

// ==================== CANCEL ORDER ====================
function cancelOrderAjax(orderId) {
    if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
        return;
    }
    
    fetch(SITE_URL + '/api/cancel-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Đã hủy đơn hàng!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra!', 'error');
    });
}

// ==================== ANIMATION ====================
// Thêm CSS animation cho cart button
const style = document.createElement('style');
style.textContent = `
    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    .cart-btn.bounce {
        animation: bounce 0.5s ease;
    }
`;
document.head.appendChild(style);

// ==================== PRODUCT QUICK VIEW ====================
function quickViewProduct(productId) {
    // TODO: Implement quick view modal
    showToast('Tính năng xem nhanh đang được phát triển!', 'info');
}

// ==================== WISHLIST ====================
function toggleWishlist(productId) {
    // TODO: Implement wishlist functionality
    showToast('Tính năng yêu thích đang được phát triển!', 'info');
}

// ==================== SEARCH SUGGESTIONS ====================
let searchTimeout;
const searchInput = document.querySelector('.search-box input');

if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                // TODO: Implement search suggestions
                console.log('Searching for:', query);
            }, 300);
        }
    });
}

// ==================== FORM VALIDATION ====================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'var(--danger-color)';
            field.focus();
        } else {
            field.style.borderColor = '';
        }
    });
    
    if (!isValid) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'error');
    }
    
    return isValid;
}

// ==================== SCROLL TO TOP ====================
const scrollToTopBtn = document.createElement('button');
scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
scrollToTopBtn.className = 'scroll-to-top';
scrollToTopBtn.style.cssText = `
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: none;
    z-index: 998;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
`;

scrollToTopBtn.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

document.body.appendChild(scrollToTopBtn);

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.style.display = 'block';
    } else {
        scrollToTopBtn.style.display = 'none';
    }
});

// ==================== IMAGE LAZY LOADING ====================
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    const lazyImages = document.querySelectorAll('img.lazy');
    lazyImages.forEach(img => imageObserver.observe(img));
}

// ==================== PRODUCT FILTER ====================
function filterProducts() {
    const category = document.querySelector('input[name="category"]:checked')?.value || '';
    const priceRange = document.querySelector('input[name="price_range"]:checked')?.value || '';
    const brand = document.querySelector('input[name="brand"]:checked')?.value || '';
    
    let url = 'products.php?';
    const params = [];
    
    if (category) params.push('category=' + category);
    if (priceRange) params.push('price=' + priceRange);
    if (brand) params.push('brand=' + brand);
    
    url += params.join('&');
    window.location.href = url;
}

// ==================== COUNTDOWN TIMER (Enhanced) ====================
function initCountdown(elementId, endTime) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = endTime - now;
        
        if (distance < 0) {
            element.innerHTML = '<span>Đã kết thúc</span>';
            return;
        }
        
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        element.innerHTML = `
            <span>${String(hours).padStart(2, '0')}</span>:
            <span>${String(minutes).padStart(2, '0')}</span>:
            <span>${String(seconds).padStart(2, '0')}</span>
        `;
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
}

// ==================== EXPORT ====================
window.PhoneShopExtended = {
    addToCartAjax,
    updateCartQuantity,
    removeCartItem,
    cancelOrderAjax,
    quickViewProduct,
    toggleWishlist,
    validateForm,
    filterProducts,
    updateCartCount
};

console.log('%c✨ PhoneShop Extended JS Loaded', 'color: #4CAF50; font-weight: bold;');
