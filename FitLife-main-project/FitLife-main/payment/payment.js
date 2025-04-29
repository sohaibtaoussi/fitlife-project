document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('payment-form');
    
    if (paymentForm) {
        // Format card number as user types
        const cardNumberInput = document.getElementById('card_number');
        cardNumberInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Add spaces after every 4 digits
            if (value.length > 0) {
                value = value.match(/.{1,4}/g).join(' ');
            }
            
            // Update the input value
            this.value = value;
        });
        
        // Format expiry date as user types
        const expiryInput = document.getElementById('expiry_date');
        expiryInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Add slash after first 2 digits
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            
            // Update the input value
            this.value = value;
        });
        
        // Validate CVV - only allow digits
        const cvvInput = document.getElementById('cvv');
        cvvInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // Form submission validation
        paymentForm.addEventListener('submit', function(e) {
            const cardNumber = cardNumberInput.value.replace(/\s+/g, '');
            const cardHolder = document.getElementById('card_holder').value;
            const expiry = expiryInput.value;
            const cvv = cvvInput.value;
            
            let isValid = true;
            let errorMessage = '';
            
            // Validate card number (simple check for length)
            if (cardNumber.length < 13 || cardNumber.length > 19) {
                isValid = false;
                errorMessage = 'Please enter a valid card number';
            }
            
            // Validate card holder name
            else if (cardHolder.trim().length < 3) {
                isValid = false;
                errorMessage = 'Please enter the cardholder name';
            }
            
            // Validate expiry date format
            else if (!/^\d{2}\/\d{2}$/.test(expiry)) {
                isValid = false;
                errorMessage = 'Please enter a valid expiry date (MM/YY)';
            }
            
            // Check if card is expired
            else {
                const [expMonth, expYear] = expiry.split('/');
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear() % 100; // Get last 2 digits of year
                const currentMonth = currentDate.getMonth() + 1; // JS months are 0-indexed
                
                if (
                    parseInt(expYear) < currentYear || 
                    (parseInt(expYear) === currentYear && parseInt(expMonth) < currentMonth)
                ) {
                    isValid = false;
                    errorMessage = 'Your card has expired';
                }
            }
            
            // Validate CVV
            if (cvv.length < 3 || cvv.length > 4) {
                isValid = false;
                errorMessage = 'Please enter a valid CVV/security code';
            }
            
            // If validation fails, prevent form submission
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        }
    }
}        