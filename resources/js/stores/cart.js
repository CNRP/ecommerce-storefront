// Define component functions BEFORE Alpine initializes
window.addToCartComponent = function(productId, variantId = null, initialQuantity = 1) {
    return {
        quantity: initialQuantity,
        loading: false,
        async addToCart() {
            if (this.loading) return;
            this.loading = true;
            try {
                await Alpine.store('cart').addItem(productId, variantId, this.quantity);
                if (this.quantity > 1) {
                    this.quantity = 1;
                }
            } catch (error) {
                // Error handled in store
            } finally {
                this.loading = false;
            }
        },
        increaseQuantity() {
            if (this.quantity < 10) this.quantity++;
        },
        decreaseQuantity() {
            if (this.quantity > 1) this.quantity--;
        }
    };
};

window.toastContainer = function() {
    return {
        toasts: [],
        nextId: 1,
        addToast(detail) {
            const toast = {
                id: this.nextId++,
                message: detail.message,
                type: detail.type || 'info',
                show: true
            };
            this.toasts.push(toast);
            setTimeout(() => {
                this.removeToast(toast.id);
            }, 5000);
        },
        removeToast(id) {
            const index = this.toasts.findIndex(toast => toast.id === id);
            if (index > -1) {
                this.toasts[index].show = false;
                setTimeout(() => {
                    const newIndex = this.toasts.findIndex(toast => toast.id === id);
                    if (newIndex > -1) {
                        this.toasts.splice(newIndex, 1);
                    }
                }, 300);
            }
        }
    };
};

// Initialize Alpine store
document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        isOpen: false,
        items: [],
        count: 0,
        total: 0,
        loading: false,

        init() {
            this.loadCart();
        },

        async loadCart() {
            try {
                const response = await fetch('/api/cart/data', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) throw new Error('Failed to load cart');

                const data = await response.json();

                if (data.success) {
                    // Ensure items have keys
                    this.items = (data.items || []).map(item => ({
                        ...item,
                        key: item.key || this.generateKey(item.product_id, item.variant_id)
                    }));
                    this.count = data.count || 0;
                    this.total = data.total || 0;
                }
            } catch (error) {
                console.error('Failed to load cart:', error);
                this.items = [];
                this.count = 0;
                this.total = 0;
            }
        },

        async addItem(productId, variantId = null, quantity = 1) {
            this.loading = true;
            try {
                const response = await fetch('/api/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: variantId,
                        quantity: quantity
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadCart();
                    this.showToast(`Added ${quantity} item${quantity > 1 ? 's' : ''} to cart!`, 'success');
                    this.animateCartIcon();
                    return data;
                } else {
                    throw new Error(data.message || 'Failed to add item to cart');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                this.showToast('Error adding item to cart', 'error');
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async removeItem(key) {
            try {
                const response = await fetch(`/api/cart/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadCart();
                    this.showToast('Item removed from cart', 'info');
                } else {
                    throw new Error(data.message || 'Failed to remove item');
                }
            } catch (error) {
                console.error('Error removing from cart:', error);
                this.showToast('Error removing item', 'error');
            }
        },

        async updateQuantity(key, quantity) {
            if (quantity <= 0) {
                return this.removeItem(key);
            }
            try {
                const response = await fetch(`/api/cart/${key}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity })
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadCart();
                } else {
                    throw new Error(data.message || 'Failed to update quantity');
                }
            } catch (error) {
                console.error('Error updating quantity:', error);
                this.showToast('Error updating quantity', 'error');
            }
        },

        open() { this.isOpen = true; },
        close() { this.isOpen = false; },
        toggle() { this.isOpen = !this.isOpen; },

        get formattedTotal() {
            return '£' + (this.total || 0).toFixed(2);
        },

        generateKey(productId, variantId = null) {
            return variantId ? `${productId}-${variantId}` : `${productId}`;
        },

        animateCartIcon() {
            const cartIcon = document.querySelector('[x-text="$store.cart.count"]');
            if (cartIcon) {
                cartIcon.classList.add('animate-bounce');
                setTimeout(() => cartIcon.classList.remove('animate-bounce'), 1000);
            }
        },

        showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    });
});

// Global helper function
window.addToCart = async function(productId, variantId = null, quantity = 1) {
    try {
        await Alpine.store('cart').addItem(productId, variantId, quantity);
    } catch (error) {
        console.error('Error adding to cart:', error);
    }
};
