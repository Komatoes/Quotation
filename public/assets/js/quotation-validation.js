/**
 * Quotation Management - Input Validation and Formatting Utilities
 * Features:
 * - Price formatting (automatic comma insertion while typing)
 * - Name field validation (no numbers or special characters)
 * - Negative value prevention
 * - Real-time validation feedback
 */

(function() {
    'use strict';

    const QuotationValidation = {
        /**
         * Format price input with thousand separators
         * @param {string} value - The value to format
         * @returns {string} Formatted price
         */
        formatPrice: function(value) {
            // Remove all non-numeric characters except decimal point
            let numericValue = value.replace(/[^\d.]/g, '');
            
            // Remove extra decimal points, keeping only the first one
            let parts = numericValue.split('.');
            if (parts.length > 2) {
                numericValue = parts[0] + '.' + parts.slice(1).join('');
            }

            // Limit to 2 decimal places
            if (parts[1] && parts[1].length > 2) {
                numericValue = parts[0] + '.' + parts[1].substring(0, 2);
            }

            // Split into integer and decimal parts
            const [integerPart, decimalPart] = numericValue.split('.');
            
            // Add thousand separators to integer part
            const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            
            // Combine back
            return decimalPart !== undefined ? `${formattedInteger}.${decimalPart}` : formattedInteger;
        },

        /**
         * Extract numeric value from formatted price
         * @param {string} value - Formatted price string
         * @returns {number} Numeric value
         */
        getNumericValue: function(value) {
            return parseFloat(value.replace(/[^\d.]/g, '')) || 0;
        },

        /**
         * Validate name field (no numbers or special characters)
         * @param {string} value - The value to validate
         * @returns {boolean} True if valid
         */
        isValidName: function(value) {
            // Allow only letters, spaces, hyphens, and apostrophes
            const nameRegex = /^[a-zA-Z\s\-']*$/;
            return nameRegex.test(value);
        },

        /**
         * Initialize price input formatting
         */
        initPriceInputs: function() {
            const priceInputs = document.querySelectorAll('.price-input, input[type="currency"]');
            
            priceInputs.forEach(input => {
                // Prevent negative values
                input.addEventListener('input', (e) => {
                    let value = e.target.value;
                    let numericValue = this.getNumericValue(value);

                    // Prevent negative values
                    if (numericValue < 0) {
                        numericValue = 0;
                    }

                    // Format and update
                    const formatted = this.formatPrice(numericValue.toString());
                    e.target.value = formatted;
                });

                // On blur, ensure proper formatting
                input.addEventListener('blur', (e) => {
                    let value = e.target.value;
                    if (value.trim() === '') {
                        e.target.value = '0';
                    } else {
                        const formatted = this.formatPrice(value);
                        e.target.value = formatted;
                    }
                });

                // On focus, show unformatted value for editing
                input.addEventListener('focus', (e) => {
                    e.target.select();
                });
            });
        },

        /**
         * Initialize name field validation
         */
        initNameInputs: function() {
            const nameInputs = document.querySelectorAll('.name-input, input[data-validate="name"]');
            
            nameInputs.forEach(input => {
                input.addEventListener('input', (e) => {
                    let value = e.target.value;
                    
                    // Remove invalid characters
                    const cleanedValue = value.replace(/[^a-zA-Z\s\-']/g, '');
                    
                    if (cleanedValue !== value) {
                        e.target.value = cleanedValue;
                        this.showValidationError(e.target, 'Only letters, spaces, hyphens, and apostrophes are allowed');
                    } else {
                        this.clearValidationError(e.target);
                    }
                });

                input.addEventListener('blur', (e) => {
                    if (!this.isValidName(e.target.value)) {
                        this.showValidationError(e.target, 'Invalid characters in name field');
                    } else {
                        this.clearValidationError(e.target);
                    }
                });
            });
        },

        /**
         * Initialize quantity input validation
         */
        initQuantityInputs: function() {
            const quantityInputs = document.querySelectorAll('.quantity-input, input[type="number"][data-validate="positive"]');
            
            quantityInputs.forEach(input => {
                input.addEventListener('input', (e) => {
                    let value = parseInt(e.target.value) || 0;
                    
                    // Prevent negative values
                    if (value < 0) {
                        e.target.value = '0';
                        this.showValidationError(e.target, 'Quantity cannot be negative');
                    } else {
                        this.clearValidationError(e.target);
                    }
                });
            });
        },

        /**
         * Show validation error
         * @param {HTMLElement} input - Input element
         * @param {string} message - Error message
         */
        showValidationError: function(input, message) {
            input.classList.add('is-invalid');
            
            let errorDiv = input.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback d-block';
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
            
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        },

        /**
         * Clear validation error
         * @param {HTMLElement} input - Input element
         */
        clearValidationError: function(input) {
            input.classList.remove('is-invalid');
            
            const errorDiv = input.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                errorDiv.style.display = 'none';
            }
        },

        /**
         * Validate form before submission
         * @param {HTMLFormElement} form - Form element
         * @returns {boolean} True if form is valid
         */
        validateForm: function(form) {
            let isValid = true;
            const inputs = form.querySelectorAll('[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    this.showValidationError(input, 'This field is required');
                    isValid = false;
                }
            });

            return isValid;
        },

        /**
         * Initialize all validations
         */
        init: function() {
            this.initPriceInputs();
            this.initNameInputs();
            this.initQuantityInputs();

            // Initialize for dynamically added elements
            const observer = new MutationObserver(() => {
                this.initPriceInputs();
                this.initNameInputs();
                this.initQuantityInputs();
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            QuotationValidation.init();
        });
    } else {
        QuotationValidation.init();
    }

    // Export for global use
    window.QuotationValidation = QuotationValidation;
})();
