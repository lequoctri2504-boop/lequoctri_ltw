// ==================== GLOBAL VARIABLES ====================
let cart = JSON.parse(localStorage.getItem('cart')) || [];
let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];

// ==================== DOM READY ====================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart and wishlist
    updateCartBadge();
    updateWishlistBadge();
    
    // Product tabs
    initProductTabs();
    
    // Account menu
    initAccountMenu();
    
    // Dashboard navigation
    initDashboardNav();
    
    // Add to cart buttons
    initAddToCartButtons();
    
    // Wishlist buttons
    initWishlistButtons();
    
    // Thumbnail gallery
    initThumbnailGallery();
    
    // Quantity selectors
    initQuantitySelectors();
    
    // Color and storage options
    initProductOptions();
});

// ==================== CART FUNCTIONS ====================
function updateCartBadge() {
    const badges = document.querySelectorAll('.cart-btn .badge');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    badges.forEach(badge => {
        badge.textContent = totalItems;
    });
}

function updateWishlistBadge() {
    const badges = document.querySelectorAll('.wishlist-btn .badge');
    badges.forEach(badge => {
        badge.textContent = wishlist.length;
    });
}

function initAddToCartButtons() {
    const addToCartBtns = document.querySelectorAll('.btn-cart');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productCard = this.closest('.product-card') || this.closest('.product-detail-info');
            
            // Get product info
            const product = {
                id: Date.now(), // Simple ID generation
                name: productCard.querySelector('h3')?.textContent || productCard.querySelector('h1')?.textContent || 'Sáº£n pháº©m',
                price: productCard.querySelector('.price-new')?.textContent || '0â‚«',
                image: productCard.querySelector('img')?.src || '',
                quantity: 1
            };
            
            // Check if product already in cart
            const existingProduct = cart.find(item => item.name === product.name);
            if (existingProduct) {
                existingProduct.quantity++;
            } else {
                cart.push(product);
            }
            
            // Save to localStorage
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Update badge
            updateCartBadge();
            
            // Show toast
            showToast('ÄÃ£ thÃªm vÃ o giá» hÃ ng!', 'success');
        });
    });
}

function initWishlistButtons() {
    const wishlistBtns = document.querySelectorAll('.wishlist-icon, .btn-wishlist');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productCard = this.closest('.product-card');
            
            if (productCard) {
                const product = {
                    id: Date.now(),
                    name: productCard.querySelector('h3')?.textContent || 'Sáº£n pháº©m',
                    price: productCard.querySelector('.price-new')?.textContent || '0â‚«',
                    image: productCard.querySelector('img')?.src || ''
                };
                
                // Toggle wishlist
                const existingIndex = wishlist.findIndex(item => item.name === product.name);
                if (existingIndex !== -1) {
                    wishlist.splice(existingIndex, 1);
                    showToast('ÄÃ£ xÃ³a khá»i yÃªu thÃ­ch', 'info');
                } else {
                    wishlist.push(product);
                    showToast('ÄÃ£ thÃªm vÃ o yÃªu thÃ­ch!', 'success');
                }
                
                // Save to localStorage
                localStorage.setItem('wishlist', JSON.stringify(wishlist));
                
                // Update badge
                updateWishlistBadge();
            }
        });
    });
}

// ==================== TOAST NOTIFICATION ====================
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    
    toast.textContent = message;
    toast.className = 'toast show';
    
    if (type === 'error') {
        toast.style.background = '#DC3545';
    } else if (type === 'info') {
        toast.style.background = '#17A2B8';
    } else {
        toast.style.background = '#28A745';
    }
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// ==================== PRODUCT TABS ====================
function initProductTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs
            tabBtns.forEach(tb => tb.classList.remove('active'));
            tabContents.forEach(tc => tc.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            document.getElementById(targetTab)?.classList.add('active');
        });
    });
}

// ==================== ACCOUNT MENU ====================
function initAccountMenu() {
    const menuItems = document.querySelectorAll('.account-menu .menu-item');
    const contentSections = document.querySelectorAll('.content-section');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = this.getAttribute('data-section');
            
            // Remove active class from all items
            menuItems.forEach(mi => mi.classList.remove('active'));
            contentSections.forEach(cs => cs.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            document.getElementById(targetSection)?.classList.add('active');
        });
    });
}

// ==================== DASHBOARD NAVIGATION ====================
function initDashboardNav() {
    const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
    const sections = document.querySelectorAll('.dashboard-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetSection = this.getAttribute('data-section');
            
            // Remove active class from all items
            navItems.forEach(ni => ni.classList.remove('active'));
            sections.forEach(sec => sec.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            document.getElementById(targetSection)?.classList.add('active');
        });
    });
}

// ==================== THUMBNAIL GALLERY ====================
function initThumbnailGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail-list img');
    const mainImage = document.getElementById('mainImg');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked thumbnail
            this.classList.add('active');
            
            // Update main image
            if (mainImage) {
                mainImage.src = this.src.replace('w=100&h=100', 'w=600&h=600');
            }
        });
    });
}

// ==================== QUANTITY SELECTORS ====================
function initQuantitySelectors() {
    const quantitySelectors = document.querySelectorAll('.item-quantity');
    
    quantitySelectors.forEach(selector => {
        const minusBtn = selector.querySelector('.qty-btn:first-child');
        const plusBtn = selector.querySelector('.qty-btn:last-child');
        const input = selector.querySelector('input');
        
        if (minusBtn && input) {
            minusBtn.addEventListener('click', function() {
                let value = parseInt(input.value) || 1;
                if (value > 1) {
                    input.value = value - 1;
                }
            });
        }
        
        if (plusBtn && input) {
            plusBtn.addEventListener('click', function() {
                let value = parseInt(input.value) || 1;
                input.value = value + 1;
            });
        }
    });
}

// ==================== PRODUCT OPTIONS ====================
function initProductOptions() {
    // Color buttons
    const colorBtns = document.querySelectorAll('.color-btn');
    colorBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            colorBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update selected option text
            const selectedColor = this.getAttribute('data-color');
            const selectedOption = this.closest('.option-group').querySelector('.selected-option');
            if (selectedOption) {
                selectedOption.textContent = selectedColor;
            }
        });
    });
    
    // Storage buttons
    const storageBtns = document.querySelectorAll('.storage-btn');
    storageBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            storageBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// ==================== FILTER TOGGLE (Mobile) ====================
function toggleFilter() {
    const filterSidebar = document.querySelector('.filter-sidebar');
    if (filterSidebar) {
        filterSidebar.classList.toggle('show');
    }
}

// ==================== SEARCH FUNCTIONALITY ====================
const searchInput = document.querySelector('.search-box input');
if (searchInput) {
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                // Redirect to products page with search query
                window.location.href = `products.html?search=${encodeURIComponent(query)}`;
            }
        }
    });
}

// ==================== CHAT WIDGET ====================
const chatToggle = document.querySelector('.chat-toggle');
if (chatToggle) {
    chatToggle.addEventListener('click', function() {
        showToast('TÃ­nh nÄƒng chat Ä‘ang Ä‘Æ°á»£c phÃ¡t triá»ƒn!', 'info');
    });
}

// ==================== LOGIN/LOGOUT ====================
const loginBtn = document.getElementById('loginBtn');
const logoutBtn = document.getElementById('logoutBtn');

if (loginBtn) {
    loginBtn.addEventListener('click', function(e) {
        e.preventDefault();
        showToast('Chá»©c nÄƒng Ä‘Äƒng nháº­p Ä‘ang Ä‘Æ°á»£c phÃ¡t triá»ƒn!', 'info');
    });
}

if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Báº¡n cÃ³ cháº¯c muá»‘n Ä‘Äƒng xuáº¥t?')) {
            showToast('ÄÃ£ Ä‘Äƒng xuáº¥t thÃ nh cÃ´ng!', 'success');
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        }
    });
}

// ==================== FORM VALIDATIONS ====================
const forms = document.querySelectorAll('form');
forms.forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if form is valid
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#DC3545';
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (isValid) {
            showToast('ÄÃ£ lÆ°u thÃ nh cÃ´ng!', 'success');
            
            // For checkout form, redirect to success page
            if (form.closest('.checkout-page')) {
                setTimeout(() => {
                    window.location.href = 'account.html';
                }, 1500);
            }
        } else {
            showToast('Vui lÃ²ng Ä‘iá»n Ä‘áº§y Ä‘á»§ thÃ´ng tin!', 'error');
        }
    });
});

// ==================== COUNTDOWN TIMER ====================
function updateCountdown() {
    const countdownElements = document.querySelectorAll('.countdown');
    
    countdownElements.forEach(countdown => {
        const spans = countdown.querySelectorAll('span');
        if (spans.length === 3) {
            // Simple countdown - decrease by 1 second
            let hours = parseInt(spans[0].textContent) || 0;
            let minutes = parseInt(spans[1].textContent) || 0;
            let seconds = parseInt(spans[2].textContent) || 0;
            
            if (seconds > 0) {
                seconds--;
            } else if (minutes > 0) {
                minutes--;
                seconds = 59;
            } else if (hours > 0) {
                hours--;
                minutes = 59;
                seconds = 59;
            }
            
            spans[0].textContent = String(hours).padStart(2, '0');
            spans[1].textContent = String(minutes).padStart(2, '0');
            spans[2].textContent = String(seconds).padStart(2, '0');
        }
    });
}

// Update countdown every second
setInterval(updateCountdown, 1000);

// ==================== REMOVE ITEM FROM CART ====================
const removeCartBtns = document.querySelectorAll('.item-actions .text-danger');
removeCartBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Báº¡n cÃ³ cháº¯c muá»‘n xÃ³a sáº£n pháº©m nÃ y?')) {
            const cartItem = this.closest('.cart-item');
            if (cartItem) {
                cartItem.remove();
                showToast('ÄÃ£ xÃ³a sáº£n pháº©m khá»i giá» hÃ ng', 'info');
                updateCartBadge();
            }
        }
    });
});

// ==================== WISHLIST REMOVE ====================
const wishlistRemoveBtns = document.querySelectorAll('.wishlist-remove');
wishlistRemoveBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Báº¡n cÃ³ cháº¯c muá»‘n xÃ³a khá»i yÃªu thÃ­ch?')) {
            const productCard = this.closest('.product-card');
            if (productCard) {
                productCard.remove();
                showToast('ÄÃ£ xÃ³a khá»i danh sÃ¡ch yÃªu thÃ­ch', 'info');
                updateWishlistBadge();
            }
        }
    });
});

// ==================== ORDER ACTIONS ====================
const orderActionBtns = document.querySelectorAll('.order-actions .btn');
orderActionBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.textContent.trim();
        
        if (action.includes('Chi tiáº¿t') || action.includes('Xem')) {
            showToast('Äang táº£i chi tiáº¿t Ä‘Æ¡n hÃ ng...', 'info');
        } else if (action.includes('ÄÃ¡nh giÃ¡')) {
            showToast('Má»Ÿ form Ä‘Ã¡nh giÃ¡ sáº£n pháº©m...', 'info');
        } else if (action.includes('Há»§y')) {
            if (confirm('Báº¡n cÃ³ cháº¯c muá»‘n há»§y Ä‘Æ¡n hÃ ng nÃ y?')) {
                showToast('ÄÃ£ gá»­i yÃªu cáº§u há»§y Ä‘Æ¡n hÃ ng!', 'success');
            }
        }
    });
});

// ==================== QUICK VIEW ====================
const quickViewBtns = document.querySelectorAll('.quick-view');
quickViewBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        showToast('Chá»©c nÄƒng xem nhanh Ä‘ang Ä‘Æ°á»£c phÃ¡t triá»ƒn!', 'info');
    });
});

// ==================== CONSOLE MESSAGE ====================
console.log('%cðŸš€ PhoneShop Website', 'color: #D70018; font-size: 24px; font-weight: bold;');
console.log('%cðŸ“± Há»‡ thá»‘ng quáº£n lÃ½ bÃ¡n hÃ ng Ä‘iá»‡n thoáº¡i', 'color: #666; font-size: 14px;');
console.log('%câœ¨ Frontend Demo - HTML/CSS/JavaScript', 'color: #999; font-size: 12px;');
console.log('%cDeveloped with â¤ï¸', 'color: #D70018; font-size: 12px;');

// ==================== HELPER FUNCTIONS ====================
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND' 
    }).format(amount);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(date));
}

// ==================== EXPORT FOR GLOBAL USE ====================
window.PhoneShop = {
    showToast,
    formatCurrency,
    formatDate,
    cart,
    wishlist
};






// ==================== CHI TIáº¾T Sáº¢N PHáº¨M ====================
// Khi click vÃ o product card â†’ lÆ°u dá»¯ liá»‡u + chuyá»ƒn trang
document.querySelectorAll('.product-card').forEach(card => {
    card.style.cursor = 'pointer';
    card.addEventListener('click', () => {
        const product = {
            title: card.querySelector('h3').textContent,
            price: card.querySelector('.price-new').textContent,
            oldPrice: card.querySelector('.price-old')?.textContent || '',
            img: card.querySelector('.product-image img').src,
            rating: card.querySelector('.product-rating').innerHTML,
            description: 'Sáº£n pháº©m chÃ­nh hÃ£ng, báº£o hÃ nh Ä‘áº§y Ä‘á»§. Giao hÃ ng nhanh trong 2h.'
        };

        // LÆ°u vÃ o LocalStorage
        localStorage.setItem('currentProduct', JSON.stringify(product));

        // Chuyá»ƒn trang
        window.location.href = 'product-detail.html';
    });
});

// Khi vÃ o trang chi tiáº¿t â†’ load dá»¯ liá»‡u
if (window.location.pathname.includes('product-detail.html')) {
    const product = JSON.parse(localStorage.getItem('currentProduct'));
    if (product) {
        document.getElementById('breadcrumb-title').textContent = product.title;
        document.getElementById('product-title').textContent = product.title;
        document.getElementById('product-price').textContent = product.price;
        document.getElementById('product-old-price').textContent = product.oldPrice;
        document.getElementById('main-img').src = product.img;
        document.getElementById('product-rating').innerHTML = product.rating;
        document.getElementById('product-description').innerHTML = '<p>' + product.description + '</p>';

        // Thumbnail (giáº£ láº­p 3 áº£nh)
        document.querySelectorAll('.thumb-img').forEach((thumb, i) => {
            thumb.src = product.img;
        });
    }

    // Chá»n mÃ u
    document.querySelectorAll('.color-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('selected-color').textContent = btn.dataset.color;
        });
    });

    // ThÃªm giá» hÃ ng tá»« trang chi tiáº¿t
    document.querySelector('.btn-cart-detail').addEventListener('click', () => {
        const cart = JSON.parse(localStorage.getItem('phoneShopCart')) || [];
        cart.push({
            id: Date.now(),
            name: product.title,
            price: parseInt(product.price.replace(/[^0-9]/g, '')),
            img: product.img
        });
        localStorage.setItem('phoneShopCart', JSON.stringify(cart));
        updateCartBadge();
        showToast('ÄÃ£ thÃªm vÃ o giá» hÃ ng!');
    });
}

