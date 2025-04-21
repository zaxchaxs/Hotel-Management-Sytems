/**
 * Hotel Management System
 * main.js - Main JavaScript file for the frontend
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
      mobileMenuBtn.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
      });
    }
    
    // Room filter functionality
    const filterForm = document.getElementById('room-filter-form');
    if (filterForm) {
      initializeRoomFilter(filterForm);
    }
    
    // Initialize date pickers
    initializeDatePickers();
    
    // Initialize room booking calculator
    initializeBookingCalculator();
    
    // Initialize form validations
    initializeFormValidations();
  });
  
  /**
   * Initialize room filtering
   */
  function initializeRoomFilter(filterForm) {
    const checkInDate = filterForm.querySelector('input[name="check_in"]');
    const checkOutDate = filterForm.querySelector('input[name="check_out"]');
    const roomTypeSelect = filterForm.querySelector('select[name="room_type"]');
    const capacitySelect = filterForm.querySelector('select[name="capacity"]');
    
    // Set minimum date for check-in and check-out to today
    const today = new Date();
    const todayFormatted = today.toISOString().split('T')[0];
    
    if (checkInDate) {
      checkInDate.min = todayFormatted;
      
      // When check-in date changes, update check-out minimum date
      checkInDate.addEventListener('change', function() {
        if (checkOutDate && this.value) {
          checkOutDate.min = this.value;
          
          // If check-out date is now invalid, clear it
          if (checkOutDate.value && checkOutDate.value < this.value) {
            checkOutDate.value = '';
          }
        }
      });
    }
    
    if (checkOutDate) {
      checkOutDate.min = todayFormatted;
    }
  }
  
  /**
   * Initialize date pickers across the site
   */
  function initializeDatePickers() {
    // This would typically use a date picker library
    // For simplicity, we're just using HTML5 date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
      // Set minimum date to today for all date inputs that don't have a min attribute
      if (!input.min) {
        const today = new Date();
        input.min = today.toISOString().split('T')[0];
      }
    });
  }
  
  /**
   * Initialize booking calculator
   */
  function initializeBookingCalculator() {
    const bookingForm = document.getElementById('booking-form');
    
    if (bookingForm) {
      const checkInInput = bookingForm.querySelector('input[name="check_in"]');
      const checkOutInput = bookingForm.querySelector('input[name="check_out"]');
      const totalNightsElement = document.getElementById('total-nights');
      const totalPriceElement = document.getElementById('total-price');
      const pricePerNightElement = document.getElementById('price-per-night');
      
      if (checkInInput && checkOutInput && totalNightsElement && totalPriceElement && pricePerNightElement) {
        const pricePerNight = parseFloat(pricePerNightElement.dataset.price || 0);
        
        function calculateTotal() {
          if (checkInInput.value && checkOutInput.value) {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);
            
            if (checkOut > checkIn) {
              const diffTime = checkOut.getTime() - checkIn.getTime();
              const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
              const totalPrice = diffDays * pricePerNight;
              
              totalNightsElement.textContent = diffDays;
              totalPriceElement.textContent = '$' + totalPrice.toFixed(2);
              bookingForm.querySelector('input[name="total_price"]').value = totalPrice.toFixed(2);
            }
          }
        }
        
        checkInInput.addEventListener('change', calculateTotal);
        checkOutInput.addEventListener('change', calculateTotal);
        
        // Calculate initial values if dates are pre-filled
        calculateTotal();
      }
    }
  }
  
  /**
   * Initialize form validations
   */
  function initializeFormValidations() {
    // Credit card form formatting
    const cardNumberInput = document.querySelector('input[name="card_number"]');
    if (cardNumberInput) {
      cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = '';
        
        for (let i = 0; i < value.length && i < 16; i++) {
          if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
          }
          formattedValue += value[i];
        }
        
        e.target.value = formattedValue;
      });
    }
    
    // Expiry date formatting (MM/YY)
    const expiryDateInput = document.querySelector('input[name="expiry_date"]');
    if (expiryDateInput) {
      expiryDateInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length > 2) {
          value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        e.target.value = value;
      });
    }
    
    // Payment method toggling
    const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
    const creditCardForm = document.getElementById('credit-card-form');
    const paypalForm = document.getElementById('paypal-form');
    
    if (paymentMethodInputs.length && creditCardForm && paypalForm) {
      function togglePaymentForm() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (selectedMethod === 'credit_card') {
          creditCardForm.classList.remove('hidden');
          paypalForm.classList.add('hidden');
        } else if (selectedMethod === 'paypal') {
          creditCardForm.classList.add('hidden');
          paypalForm.classList.remove('hidden');
        }
      }
      
      paymentMethodInputs.forEach(input => {
        input.addEventListener('change', togglePaymentForm);
      });
      
      // Initialize on page load
      togglePaymentForm();
    }
    
    // Form submissions with validation
    const forms = document.querySelectorAll('form.validate-form');
    forms.forEach(form => {
      form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
          if (!field.value.trim()) {
            isValid = false;
            field.classList.add('border-red-500');
            
            // Create or update error message
            let errorMessage = field.parentNode.querySelector('.error-message');
            if (!errorMessage) {
              errorMessage = document.createElement('p');
              errorMessage.className = 'error-message text-red-500 text-xs mt-1';
              field.parentNode.appendChild(errorMessage);
            }
            errorMessage.textContent = 'This field is required';
          } else {
            field.classList.remove('border-red-500');
            const errorMessage = field.parentNode.querySelector('.error-message');
            if (errorMessage) {
              errorMessage.remove();
            }
          }
        });
        
        if (!isValid) {
          e.preventDefault();
        }
      });
    });
  }
  
  /**
   * Room availability check function
   */
  function checkRoomAvailability(roomId, checkIn, checkOut) {
    // This would typically be an AJAX call to the server
    // For simplicity, we'll just show an example of what it would look like
    
    if (!roomId || !checkIn || !checkOut) {
      return false;
    }
    
    const data = {
      room_id: roomId,
      check_in: checkIn,
      check_out: checkOut
    };
    
    // Example AJAX request
    fetch('/api/check-availability', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
      if (data.available) {
        // Room is available
        document.getElementById('availability-message').textContent = 'Room is available!';
        document.getElementById('availability-message').className = 'text-green-500';
        document.getElementById('book-now-button').disabled = false;
      } else {
        // Room is not available
        document.getElementById('availability-message').textContent = 'Room is not available for selected dates.';
        document.getElementById('availability-message').className = 'text-red-500';
        document.getElementById('book-now-button').disabled = true;
      }
    })
    .catch(error => {
      console.error('Error checking availability:', error);
    });
  }