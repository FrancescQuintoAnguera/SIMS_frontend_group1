/**
 * Booking JavaScript Module
 * Handles vehicle booking form submission, validation, and cost calculation
 */

class BookingManager {
    constructor() {
        this.selectedVehicle = null;
        this.pricePerMinute = 0.30; // Default price
        this.unlockFee = 0.50;
        this.init();
    }

    init() {
        // Initialize date/time pickers with minimum date as now
        this.initializeDateTimePickers();
        
        // Bind event listeners
        this.bindEvents();
        
        // Load vehicle details if vehicle_id is in URL
        this.loadVehicleFromURL();
    }

    initializeDateTimePickers() {
        const now = new Date();
        const minDateTime = this.formatDateTimeLocal(now);
        
        const startInput = document.getElementById('start_datetime');
        const endInput = document.getElementById('end_datetime');
        
        if (startInput) {
            startInput.min = minDateTime;
            startInput.value = minDateTime;
        }
        
        if (endInput) {
            // Set minimum end time to 15 minutes from now
            const minEnd = new Date(now.getTime() + 15 * 60000);
            endInput.min = this.formatDateTimeLocal(minEnd);
            endInput.value = this.formatDateTimeLocal(minEnd);
        }
    }

    formatDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    formatDateTimeSQL(dateTimeLocal) {
        // Convert from datetime-local format to SQL format
        return dateTimeLocal.replace('T', ' ') + ':00';
    }

    bindEvents() {
        // Calculate cost when dates change
        const startInput = document.getElementById('start_datetime');
        const endInput = document.getElementById('end_datetime');
        
        if (startInput) {
            startInput.addEventListener('change', () => this.calculateCost());
        }
        
        if (endInput) {
            endInput.addEventListener('change', () => this.calculateCost());
        }
        
        // Handle booking form submission
        const bookingForm = document.getElementById('booking-form');
        if (bookingForm) {
            bookingForm.addEventListener('submit', (e) => this.handleBookingSubmit(e));
        }
        
        // Handle search form submission
        const searchForm = document.getElementById('search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => this.handleSearchSubmit(e));
        }
    }

    loadVehicleFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const vehicleId = urlParams.get('vehicle_id');
        
        if (vehicleId) {
            this.loadVehicleDetails(vehicleId);
        }
    }

    async loadVehicleDetails(vehicleId) {
        try {
            const response = await fetch(`./php/api/vehicles.php?action=details&id=${vehicleId}`);
            const data = await response.json();
            
            if (data.success && data.vehicle) {
                this.selectedVehicle = data.vehicle;
                this.pricePerMinute = data.vehicle.price_per_minute || 0.30;
                this.displayVehicleDetails(data.vehicle);
                this.calculateCost();
            } else {
                this.showError('Vehicle not found');
            }
        } catch (error) {
            console.error('Error loading vehicle:', error);
            this.showError('Failed to load vehicle details');
        }
    }

    displayVehicleDetails(vehicle) {
        const container = document.getElementById('vehicle-details');
        if (!container) return;
        
        container.innerHTML = `
            <div class="bg-white rounded-lg shadow-md p-6">
                ${vehicle.image_url ? `
                    <img src="${vehicle.image_url}" alt="${vehicle.model}" 
                         class="w-full h-48 object-cover rounded-lg mb-4">
                ` : ''}
                <h3 class="text-2xl font-bold text-gray-800 mb-2">${vehicle.model}</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Type:</span>
                        <span class="font-semibold ml-2">${vehicle.type}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Battery:</span>
                        <span class="font-semibold ml-2">${vehicle.battery}%</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Price:</span>
                        <span class="font-semibold ml-2">€${vehicle.price_per_minute}/min</span>
                    </div>
                    <div>
                        <span class="text-gray-600">License:</span>
                        <span class="font-semibold ml-2">${vehicle.license_plate}</span>
                    </div>
                </div>
                ${vehicle.is_accessible ? `
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <span class="text-blue-800 font-semibold">♿ Accessible Vehicle</span>
                    </div>
                ` : ''}
            </div>
        `;
    }

    calculateCost() {
        const startInput = document.getElementById('start_datetime');
        const endInput = document.getElementById('end_datetime');
        const costDisplay = document.getElementById('total-cost');
        const durationDisplay = document.getElementById('duration');
        
        if (!startInput || !endInput || !costDisplay) return;
        
        const startValue = startInput.value;
        const endValue = endInput.value;
        
        if (!startValue || !endValue) return;
        
        const start = new Date(startValue);
        const end = new Date(endValue);
        
        // Validate dates
        if (end <= start) {
            costDisplay.textContent = '€0.00';
            if (durationDisplay) durationDisplay.textContent = '0 minutes';
            return;
        }
        
        // Calculate duration in minutes
        const durationMs = end - start;
        const durationMinutes = Math.ceil(durationMs / 60000);
        
        // Calculate total cost
        const timeCost = durationMinutes * this.pricePerMinute;
        const totalCost = timeCost + this.unlockFee;
        
        // Update display
        costDisplay.textContent = `€${totalCost.toFixed(2)}`;
        
        if (durationDisplay) {
            const hours = Math.floor(durationMinutes / 60);
            const minutes = durationMinutes % 60;
            if (hours > 0) {
                durationDisplay.textContent = `${hours}h ${minutes}min`;
            } else {
                durationDisplay.textContent = `${minutes} minutes`;
            }
        }
        
        // Update cost breakdown
        const breakdownElement = document.getElementById('cost-breakdown');
        if (breakdownElement) {
            breakdownElement.innerHTML = `
                <div class="text-sm text-gray-600 space-y-1">
                    <div class="flex justify-between">
                        <span>Duration:</span>
                        <span>${durationMinutes} minutes</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Time cost (€${this.pricePerMinute}/min):</span>
                        <span>€${timeCost.toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Unlock fee:</span>
                        <span>€${this.unlockFee.toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-800 pt-2 border-t">
                        <span>Total:</span>
                        <span>€${totalCost.toFixed(2)}</span>
                    </div>
                </div>
            `;
        }
    }

    async handleBookingSubmit(event) {
        event.preventDefault();
        
        const startInput = document.getElementById('start_datetime');
        const endInput = document.getElementById('end_datetime');
        const submitButton = event.target.querySelector('button[type="submit"]');
        
        // Client-side validation
        if (!this.validateBookingForm(startInput.value, endInput.value)) {
            return;
        }
        
        // Show loading state
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="animate-pulse">Processing...</span>';
        }
        
        try {
            const bookingData = {
                vehicle_id: this.selectedVehicle.id,
                start_datetime: this.formatDateTimeSQL(startInput.value),
                end_datetime: this.formatDateTimeSQL(endInput.value)
            };
            
            const response = await fetch('./php/api/book-vehicle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(bookingData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showBookingConfirmation(data.booking);
            } else {
                this.showError(data.message_ca || data.message || 'Booking failed');
            }
        } catch (error) {
            console.error('Booking error:', error);
            this.showError('Failed to process booking. Please try again.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Confirm Booking';
            }
        }
    }

    validateBookingForm(startValue, endValue) {
        if (!startValue || !endValue) {
            this.showError('Please select start and end date/time');
            return false;
        }
        
        const start = new Date(startValue);
        const end = new Date(endValue);
        const now = new Date();
        
        if (start < now) {
            this.showError('Start date/time must be in the future');
            return false;
        }
        
        if (end <= start) {
            this.showError('End date/time must be after start date/time');
            return false;
        }
        
        return true;
    }

    showBookingConfirmation(booking) {
        const confirmationHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-xl p-8 max-w-md mx-4">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Booking Confirmed!</h2>
                        <p class="text-gray-600 mb-6">Your vehicle has been reserved successfully.</p>
                        
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Booking ID:</span>
                                    <span class="font-semibold">#${booking.id}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Vehicle:</span>
                                    <span class="font-semibold">${booking.vehicle_model}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Duration:</span>
                                    <span class="font-semibold">${booking.duration_minutes} min</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Cost:</span>
                                    <span class="font-semibold text-green-600">€${booking.total_cost}</span>
                                </div>
                            </div>
                        </div>
                        
                        <button onclick="window.location.href='./index.php'" 
                                class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition">
                            Back to Home
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', confirmationHTML);
    }

    async handleSearchSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const params = new URLSearchParams();
        
        // Build query parameters
        for (let [key, value] of formData.entries()) {
            if (value) {
                if (key.includes('datetime')) {
                    params.append(key, this.formatDateTimeSQL(value));
                } else {
                    params.append(key, value);
                }
            }
        }
        
        // Show loading state
        const resultsContainer = document.getElementById('search-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = '<div class="text-center py-8"><span class="animate-pulse">Searching...</span></div>';
        }
        
        try {
            const response = await fetch(`./php/api/search-vehicles.php?${params.toString()}`);
            const data = await response.json();
            
            if (data.success) {
                this.displaySearchResults(data.vehicles);
            } else {
                this.showError(data.message || 'Search failed');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Failed to search vehicles');
        }
    }

    displaySearchResults(vehicles) {
        const resultsContainer = document.getElementById('search-results');
        if (!resultsContainer) return;
        
        if (vehicles.length === 0) {
            resultsContainer.innerHTML = `
                <div class="text-center py-12">
                    <p class="text-gray-600 text-lg">No vehicles found matching your criteria.</p>
                </div>
            `;
            return;
        }
        
        const resultsHTML = vehicles.map(vehicle => `
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                ${vehicle.image_url ? `
                    <img src="${vehicle.image_url}" alt="${vehicle.model}" 
                         class="w-full h-48 object-cover">
                ` : `
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-400">No image</span>
                    </div>
                `}
                <div class="p-4">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">${vehicle.model}</h3>
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Type:</span>
                            <span class="font-semibold">${vehicle.type}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Battery:</span>
                            <span class="font-semibold">${vehicle.battery}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Price:</span>
                            <span class="font-semibold">€${vehicle.price_per_minute}/min</span>
                        </div>
                        ${vehicle.distance !== undefined ? `
                            <div class="flex justify-between">
                                <span class="text-gray-600">Distance:</span>
                                <span class="font-semibold">${vehicle.distance} km</span>
                            </div>
                        ` : ''}
                    </div>
                    ${vehicle.is_accessible ? `
                        <div class="mb-3 p-2 bg-blue-50 rounded text-center">
                            <span class="text-blue-800 text-sm font-semibold">♿ Accessible</span>
                        </div>
                    ` : ''}
                    <a href="./pages/vehicle/booking.php?vehicle_id=${vehicle.id}" 
                       class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition">
                        Book Now
                    </a>
                </div>
            </div>
        `).join('');
        
        resultsContainer.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${resultsHTML}
            </div>
        `;
    }

    showError(message) {
        const errorHTML = `
            <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg z-50" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span>${message}</span>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', errorHTML);
        
        // Remove error after 5 seconds
        setTimeout(() => {
            const errorElement = document.querySelector('[role="alert"]');
            if (errorElement) {
                errorElement.remove();
            }
        }, 5000);
    }
}

// Initialize booking manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.bookingManager = new BookingManager();
});
