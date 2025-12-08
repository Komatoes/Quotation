/**
 * GLOBAL FORM VALIDATION SYSTEM
 * Handles validation for ALL forms across the application
 * 
 * Features:
 * - Price formatting (2400 → 2,400)
 * - Name validation (letters/spaces/hyphens/apostrophes only)
 * - Contact number validation (11 digits max, numbers only)
 * - Quantity formatting (with commas)
 * - Prevents negative values
 * - Real-time error display
 * - Auto-initialization on page load
 * - MutationObserver for dynamic elements
 */

class GlobalFormValidator {
    constructor() {
        this.init();
    }

    /**
     * Initialize validator on page load and watch for dynamic elements
     */
    init() {
        // Initialize existing form elements
        this.attachValidationListeners();
        
        // Watch for dynamically added elements
        this.watchDynamicElements();
    }

    /**
     * Attach validation listeners to all form inputs
     */
    attachValidationListeners() {
        // Price inputs (Material price, Labor fee, Delivery fee, Fees, Unit price)
        document.querySelectorAll(
            'input[type="number"].price-input, ' +
            'input[name*="price"], ' +
            'input[name*="fee"], ' +
            'input[name*="labor"], ' +
            'input[name*="delivery"], ' +
            'input[name*="unit_price"], ' +
            'input[data-type="price"], ' +
            'input[data-type="currency"], ' +
            'input[data-validate="price"], ' +
            'input[placeholder*="Price"], ' +
            'input[placeholder*="price"], ' +
            'input[placeholder*="Fee"], ' +
            'input[placeholder*="fee"]'
        ).forEach(input => {
            if (!input.dataset.priceValidated) {
                input.dataset.priceValidated = 'true';
                // Only format on blur to avoid interfering with typing
                input.addEventListener('blur', () => this.formatPrice(input));
                // NO keyup/change events - let user type freely, validate on blur only
            }
        });

        // Name inputs (First name, Last name, Material name, Subject, Unit - but NOT unit_price!)
        document.querySelectorAll(
            'input[name*="name"], ' +
            'input[name*="first"], ' +
            'input[name*="last"], ' +
            'input[name*="subject"], ' +
            'input[name="unit"], ' +
            'input[data-validate="name"], ' +
            'input[data-type="name"], ' +
            'input[placeholder*="Name"], ' +
            'input[placeholder*="name"], ' +
            'input[placeholder*="Subject"], ' +
            'input[placeholder*="subject"], ' +
            'input[placeholder*="Unit"], ' +
            'input[placeholder*="unit"]'
        ).forEach(input => {
            // SKIP unit_price inputs - they should only get price validation
            if (input.name && input.name.includes('unit_price')) return;
            
            if (!input.dataset.nameValidated) {
                input.dataset.nameValidated = 'true';
                input.addEventListener('blur', () => this.validateName(input));
                input.addEventListener('keypress', (e) => this.validateNameKeypress(e));
                input.addEventListener('paste', (e) => this.validateNamePaste(e));
            }
        });

        // Contact number inputs
        document.querySelectorAll(
            'input[name*="contact"], ' +
            'input[name*="phone"], ' +
            'input[name*="number"], ' +
            'input[data-validate="contact"], ' +
            'input[data-type="phone"], ' +
            'input[type="tel"], ' +
            'input[placeholder*="Contact"], ' +
            'input[placeholder*="contact"], ' +
            'input[placeholder*="Phone"], ' +
            'input[placeholder*="phone"]'
        ).forEach(input => {
            if (!input.dataset.contactValidated) {
                input.dataset.contactValidated = 'true';
                input.addEventListener('input', () => this.validateContact(input));
                input.addEventListener('keypress', (e) => this.validateContactKeypress(e));
                input.addEventListener('paste', (e) => this.validateContactPaste(e));
            }
        });

        // Quantity inputs
        document.querySelectorAll(
            'input[name*="quantity"], ' +
            'input[data-validate="quantity"], ' +
            'input[data-type="quantity"]'
        ).forEach(input => {
            if (!input.dataset.quantityValidated) {
                input.dataset.quantityValidated = 'true';
                input.addEventListener('blur', () => this.formatQuantity(input));
                input.addEventListener('keyup', () => this.formatQuantity(input));
            }
        });
    }

    /**
     * Watch for dynamically added elements and attach validators
     */
    watchDynamicElements() {
        const observer = new MutationObserver(() => {
            this.attachValidationListeners();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: false
        });
    }

    /**
     * Format price with thousand separators: 10000 → 10,000
     */
    formatPrice(input) {
        if (!input || !input.value) return;

        // CRITICAL: Don't format type="number" inputs!
        // HTML5 number inputs don't accept commas and handle validation natively
        if (input.type === 'number') {
            // Just remove any invalid characters that might have been pasted
            let value = input.value.toString().replace(/[^0-9.-]/g, '');
            input.value = value;
            return;
        }

        let value = input.value.toString().replace(/,/g, '');
        
        // Prevent negative values
        if (value.startsWith('-')) {
            value = value.substring(1);
        }

        // Allow decimals
        if (/^\d+(\.\d{0,2})?$/.test(value)) {
            const parts = value.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            input.value = parts.join('.');
            this.clearError(input);
            return;
        }

        // If contains invalid characters after removing numbers/dots/commas
        if (value.length > 0 && !/^\d+(\.\d{0,2})?$/.test(value)) {
            this.showError(input, 'Price must contain only numbers and decimal point');
            input.value = '';
        }
    }

    /**
     * Format quantity with thousand separators
     */
    formatQuantity(input) {
        if (!input || !input.value) return;

        let value = input.value.toString().replace(/,/g, '');

        // Prevent negative values
        if (value.startsWith('-')) {
            value = value.substring(1);
        }

        if (/^\d+$/.test(value)) {
            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            this.clearError(input);
            return;
        }

        if (value.length > 0 && !/^\d+$/.test(value)) {
            this.showError(input, 'Quantity must be a whole number');
            input.value = '';
        }
    }

    /**
     * Validate name field - only letters, spaces, hyphens, apostrophes
     * BLOCKS: Numbers, special characters except hyphens and apostrophes
     */
    validateName(input) {
        if (!input || !input.value) {
            this.clearError(input);
            return true;
        }

        const value = input.value.trim();
        const nameRegex = /^[a-zA-Z\s\-']+$/;

        if (!nameRegex.test(value)) {
            this.showError(
                input,
                'Only letters, spaces, hyphens and apostrophes allowed. No numbers or special characters.'
            );
            return false;
        }

        this.clearError(input);
        return true;
    }

    /**
     * Block invalid characters on keypress for name fields
     */
    validateNameKeypress(event) {
        const char = String.fromCharCode(event.which);
        const nameRegex = /^[a-zA-Z\s\-']$/;

        if (!nameRegex.test(char)) {
            event.preventDefault();
            this.showError(
                event.target,
                'Numbers and special characters not allowed'
            );
        }
    }

    /**
     * Validate pasted content in name fields
     */
    validateNamePaste(event) {
        event.preventDefault();
        const pastedText = (event.clipboardData || window.clipboardData).getData('text');
        const nameRegex = /^[a-zA-Z\s\-']+$/;

        if (!nameRegex.test(pastedText)) {
            this.showError(
                event.target,
                'Pasted content contains invalid characters'
            );
            return;
        }

        event.target.value = pastedText;
        this.clearError(event.target);
    }

    /**
     * Validate contact number - 11 digits max, numbers only
     */
    validateContact(input) {
        if (!input || !input.value) {
            this.clearError(input);
            return true;
        }

        let value = input.value.replace(/\D/g, '');

        // Limit to 11 digits
        if (value.length > 11) {
            value = value.substring(0, 11);
            this.showError(input, 'Contact number cannot exceed 11 digits');
        } else {
            this.clearError(input);
        }

        input.value = value;
        return value.length <= 11;
    }

    /**
     * Block invalid characters on keypress for contact fields
     */
    validateContactKeypress(event) {
        const char = String.fromCharCode(event.which);

        // Allow only numbers
        if (!/\d/.test(char)) {
            event.preventDefault();
            this.showError(
                event.target,
                'Only numbers allowed'
            );
        }

        // Check length (11 max)
        if (event.target.value.length >= 11) {
            event.preventDefault();
            this.showError(
                event.target,
                'Contact number cannot exceed 11 digits'
            );
        }
    }

    /**
     * Validate pasted content in contact fields
     */
    validateContactPaste(event) {
        event.preventDefault();
        const pastedText = (event.clipboardData || window.clipboardData).getData('text');
        let value = pastedText.replace(/\D/g, '');

        // Limit to 11 digits
        if (value.length > 11) {
            value = value.substring(0, 11);
            this.showError(
                event.target,
                'Contact number cannot exceed 11 digits'
            );
        }

        event.target.value = value;
    }

    /**
     * Show validation error message
     */
    showError(input, message) {
        this.clearError(input);

        input.classList.add('is-invalid');
        input.style.borderColor = '#dc3545';

        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.875em';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;

        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }

    /**
     * Clear validation error message
     */
    clearError(input) {
        input.classList.remove('is-invalid');
        input.style.borderColor = '';

        const errorDiv = input.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
            errorDiv.remove();
        }
    }

    /**
     * Get numeric value from formatted price
     */
    getNumericValue(value) {
        return parseFloat(value.toString().replace(/,/g, ''));
    }

    /**
     * Format a number with thousand separators
     */
    formatWithCommas(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Validate entire form before submission
     */
    validateForm(formElement) {
        if (!formElement) return true;

        let isValid = true;
        const inputs = formElement.querySelectorAll('input');

        inputs.forEach(input => {
            if (input.name && input.value) {
                // Check if it's a name field
                if (input.name.includes('name') || 
                    input.name.includes('first') || 
                    input.name.includes('last') || 
                    input.name.includes('subject') || 
                    input.name.includes('unit')) {
                    if (!this.validateName(input)) isValid = false;
                }

                // Check if it's a contact field
                if (input.name.includes('contact') || 
                    input.name.includes('phone')) {
                    if (!this.validateContact(input)) isValid = false;
                }
            }
        });

        return isValid;
    }
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.globalValidator = new GlobalFormValidator();
});

// Fallback initialization if DOMContentLoaded already fired
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.globalValidator = new GlobalFormValidator();
    });
} else {
    window.globalValidator = new GlobalFormValidator();
}

/**
 * Helper function to format prices in display text
 * Usage: formatDisplayPrice(2400) → "2,400"
 */
function formatDisplayPrice(value) {
    if (!value) return '0';
    const numValue = parseFloat(value.toString().replace(/,/g, ''));
    if (isNaN(numValue)) return '0';
    return numValue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Helper function to strip commas from price
 * Usage: stripPrice("2,400") → 2400
 */
function stripPrice(value) {
    if (!value) return 0;
    return parseFloat(value.toString().replace(/,/g, ''));
}

/**
 * Update Grand Total in Quotation View
 * Calculates total from materials (via .line-total) + labor fee + delivery fee
 * Formats with thousand separators (commas)
 */
function updateQuotationGrandTotal() {
    const laborFeeInput = document.getElementById('laborFee');
    const deliveryFeeInput = document.getElementById('deliveryFee');
    const grandTotalAmount = document.getElementById('grandTotalAmount');
    
    if (!grandTotalAmount) {
        return; // Not on quotation view
    }
    
    // Sum all visible .line-total cells (already computed correctly by backend)
    let materialsCost = 0;
    document.querySelectorAll('.line-total').forEach(el => {
        try {
            const txt = el.textContent || '';
            const raw = txt.replace(/[^0-9.\-]/g, ''); // Remove currency, commas, etc.
            const v = parseFloat(raw);
            if (!isNaN(v)) materialsCost += v;
        } catch (e) { /* ignore malformed */ }
    });
    
    // Extract numeric values from fee inputs (strip commas)
    let laborFee = 0;
    let deliveryFee = 0;
    if (laborFeeInput) {
        laborFee = stripPrice(laborFeeInput.value) || 0;
    }
    if (deliveryFeeInput) {
        deliveryFee = stripPrice(deliveryFeeInput.value) || 0;
    }
    
    // Calculate grand total
    const grandTotal = materialsCost + laborFee + deliveryFee;
    
    // Format and display with commas
    const formattedTotal = formatDisplayPrice(grandTotal);
    grandTotalAmount.textContent = formattedTotal;
}

/**
 * Initialize Quotation Fee Formatting
 * Attaches event listeners to fee inputs for comma formatting
 */
document.addEventListener('DOMContentLoaded', function() {
    const laborFeeInput = document.getElementById('laborFee');
    const deliveryFeeInput = document.getElementById('deliveryFee');
    
    if (laborFeeInput) {
        // Format on blur (when user clicks away)
        laborFeeInput.addEventListener('blur', function() {
            const value = stripPrice(this.value) || 0;
            this.value = formatDisplayPrice(value);
            updateQuotationGrandTotal();
        });
        
        // Remove commas on focus (for editing)
        laborFeeInput.addEventListener('focus', function() {
            this.value = stripPrice(this.value);
        });
    }
    
    if (deliveryFeeInput) {
        // Format on blur (when user clicks away)
        deliveryFeeInput.addEventListener('blur', function() {
            const value = stripPrice(this.value) || 0;
            this.value = formatDisplayPrice(value);
            updateQuotationGrandTotal();
        });
        
        // Remove commas on focus (for editing)
        deliveryFeeInput.addEventListener('focus', function() {
            this.value = stripPrice(this.value);
        });
    }
    
    // Format grand total on page load
    updateQuotationGrandTotal();
});
