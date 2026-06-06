// ============================================
// Script.js - Main JavaScript for Rent a Car
// ============================================

// ========== HEADER FUNCTIONALITY ==========
// Header scroll effect
window.addEventListener("scroll", () => {
  const header = document.getElementById("header");
  if (header) {
    header.classList.toggle("scrolled", window.scrollY > 50);
  }
});

// User dropdown
const userBtn = document.getElementById("userBtn");
const dropdown = document.getElementById("dropdown");
if (userBtn) {
  userBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.classList.toggle("open");
  });
  document.addEventListener("click", () => dropdown.classList.remove("open"));
}

// Hamburger mobile menu
const hamburger = document.getElementById("hamburger");
const nav = document.getElementById("nav");
if (hamburger) {
  hamburger.addEventListener("click", () => {
    nav.classList.toggle("open");
    hamburger.classList.toggle("active");
  });
}

// ========== ANIMATE CARDS ON SCROLL ==========
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) e.target.classList.add("visible");
    });
  },
  { threshold: 0.1 },
);

document
  .querySelectorAll(
    ".car-card, .why-card, .disc-card, .service-card, .value-card, .team-card, .highlight-item, .why-card",
  )
  .forEach((el) => observer.observe(el));

// ========== SIGNIN / REGISTER TABS ==========
const tabBtns = document.querySelectorAll(".tab-btn");
const tabContents = document.querySelectorAll(".tab-content");
if (tabBtns.length > 0) {
  tabBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      tabBtns.forEach((b) => b.classList.remove("active"));
      tabContents.forEach((c) => c.classList.remove("active"));
      btn.classList.add("active");
      const target = document.getElementById(btn.dataset.tab);
      if (target) target.classList.add("active");
    });
  });

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("tab") === "register") {
    tabBtns.forEach((b) => b.classList.remove("active"));
    tabContents.forEach((c) => c.classList.remove("active"));
    const regBtn = document.querySelector('[data-tab="register"]');
    const regTab = document.getElementById("register");
    if (regBtn) regBtn.classList.add("active");
    if (regTab) regTab.classList.add("active");
  }
}

// ========== PASSWORD SHOW/HIDE ==========
document.querySelectorAll(".toggle-password").forEach((btn) => {
  btn.addEventListener("click", () => {
    const input = document.querySelector(btn.dataset.target);
    if (input) {
      input.type = input.type === "password" ? "text" : "password";
      btn.textContent = input.type === "password" ? "👁️" : "🙈";
    }
  });
});

// ========== AUTO HIDE ALERTS ==========
document.querySelectorAll(".alert").forEach((alert) => {
  setTimeout(() => {
    alert.style.opacity = "0";
    alert.style.transition = "opacity 0.5s";
    setTimeout(() => alert.remove(), 500);
  }, 4000);
});

// ========== RENTAL WIZARD ==========
(() => {
// ============================================
// WIZARD ENGINE
// ============================================

const rentalForm = document.getElementById('rentalForm');
if (!rentalForm) return;

// State
const initialStep = Number.parseInt(rentalForm.dataset.initialStep || '1', 10);
let currentStep = Number.isNaN(initialStep) ? 1 : initialStep;
let selectedCarId = null;
let selectedCarPrice = 0;
let selectedCarName = '';
let totalDays = 0;
let durationDiscount = 0;
let couponDiscount = 0;
let couponCode = '';

// Available coupons data will be validated dynamically via AJAX check_coupon.php
function bindRentalWizardEvents() {
  document.getElementById('applyCouponBtn')?.addEventListener('click', applyCoupon);

  document.getElementById('deliveryStreet')?.addEventListener('input', assembleAddress);
  document.getElementById('deliveryZip')?.addEventListener('input', assembleAddress);
  document.getElementById('deliveryCity')?.addEventListener('change', assembleAddress);

  const demoNumber = document.getElementById('demoNumber');
  demoNumber?.addEventListener('input', () => {
    formatDemoNumber(demoNumber);
    updateDemoDisplay();
  });

  document.getElementById('demoName')?.addEventListener('input', updateDemoDisplay);

  const demoExpiry = document.getElementById('demoExpiry');
  demoExpiry?.addEventListener('input', () => {
    formatExpiry(demoExpiry);
    updateDemoDisplay();
  });

  const demoCode = document.getElementById('demoCode');
  demoCode?.addEventListener('input', updateDemoDisplay);
  demoCode?.addEventListener('focus', () => flipDemoPanel(true));
  demoCode?.addEventListener('blur', () => flipDemoPanel(false));

  document.getElementById('backBtn')?.addEventListener('click', () => changeStep(-1));
  document.getElementById('nextBtn')?.addEventListener('click', () => changeStep(1));

  const carsGrid = document.getElementById('carsGrid');
  carsGrid?.addEventListener('click', (event) => {
    const card = event.target.closest('.car-select-card');
    if (card && carsGrid.contains(card)) selectCar(card);
  });
  carsGrid?.addEventListener('error', (event) => {
    if (event.target instanceof HTMLImageElement) {
      event.target.src = 'images/car_default.jpg';
    }
  }, true);
}

bindRentalWizardEvents();

// ===== ASSEMBLE DELIVERY ADDRESS =====
function assembleAddress() {
  const street = document.getElementById('deliveryStreet').value.trim();
  const zip = document.getElementById('deliveryZip').value.trim();
  const city = document.getElementById('deliveryCity').value;
  const fullAddressEl = document.getElementById('deliveryAddress');

  if (street || zip || city) {
    let addr = street;
    if (zip && city) {
      addr += (addr ? ', ' : '') + zip + ' ' + city;
    } else if (zip) {
      addr += (addr ? ', ' : '') + zip;
    } else if (city) {
      addr += (addr ? ', ' : '') + city;
    }
    addr += (addr ? ', ' : '') + 'Germany';
    fullAddressEl.value = addr;
  } else {
    fullAddressEl.value = '';
  }

  updateOrderSummary();
}

// Set min date to today
const today = new Date();
const todayStr = today.toISOString().split('T')[0];
document.getElementById('startDate').min = todayStr;
document.getElementById('endDate').min = todayStr;

// Init progress for success page
if (currentStep === 5) {
  updateProgress(5);
  launchConfetti();
}

// ===== DATE CALCULATIONS =====
document.getElementById('startDate').addEventListener('change', () => {
  calculateDates();
  autoFetchAvailableCars();
});
document.getElementById('endDate').addEventListener('change', () => {
  calculateDates();
  autoFetchAvailableCars();
});

function autoFetchAvailableCars() {
  const startVal = document.getElementById('startDate').value;
  const endVal   = document.getElementById('endDate').value;
  
  // Only fetch if both dates are selected
  if (!startVal || !endVal) return;
  
  const grid = document.getElementById('carsGrid');
  grid.innerHTML = '<div class="cars-grid-message">🚗 Loading available cars...</div>';
  
  fetch('get_available_cars.php?start_date=' + encodeURIComponent(startVal) + '&end_date=' + encodeURIComponent(endVal))
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        renderCars(data.cars);
      } else {
        grid.innerHTML = '<div class="cars-grid-message">⚠️ ' + escapeHtml(data.message) + '</div>';
      }
    })
    .catch(error => {
      console.error('Error fetching available cars:', error);
      grid.innerHTML = '<div class="cars-grid-message">⚠️ Error loading cars. Please try again.</div>';
    });
}

function calculateDates() {
  const startVal = document.getElementById('startDate').value;
  const endVal   = document.getElementById('endDate').value;

  // Update end date min
  if (startVal) {
    const nextDay = new Date(startVal);
    nextDay.setDate(nextDay.getDate() + 1);
    document.getElementById('endDate').min = nextDay.toISOString().split('T')[0];
  }

  if (startVal && endVal) {
    const start = new Date(startVal);
    const end   = new Date(endVal);
    const diff  = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

    if (diff > 0) {
      totalDays = diff;
      durationDiscount = getDurationDiscountJS(diff);

      document.getElementById('dateSummary').style.display = 'flex';
      document.getElementById('daysCount').textContent = diff + ' day' + (diff !== 1 ? 's' : '');
      document.getElementById('discountPct').textContent = durationDiscount + '%';

      let tierText = '—';
      if (diff >= 40) tierText = '🏆 Gold Tier (40+ days)';
      else if (diff >= 20) tierText = '🥈 Silver Tier (20+ days)';
      else if (diff >= 10) tierText = '🥉 Bronze Tier (10+ days)';
      else tierText = 'No discount (min 10 days)';
      document.getElementById('discountTier').textContent = tierText;

      updatePriceSummary();
    } else {
      document.getElementById('dateSummary').style.display = 'none';
      totalDays = 0;
    }
  }
}

function getDurationDiscountJS(days) {
  if (days >= 40) return 30;
  if (days >= 20) return 20;
  if (days >= 10) return 10;
  return 0;
}

// ===== COUPON =====
function applyCoupon() {
  const input = document.getElementById('couponInput').value.trim().toUpperCase();
  const msgEl = document.getElementById('couponMsg');

  if (!input) {
    msgEl.innerHTML = '<div class="coupon-msg invalid">Please enter a coupon code.</div>';
    return;
  }

  // Show loading state
  msgEl.innerHTML = '<div class="coupon-msg coupon-checking">Checking coupon...</div>';

  fetch('check_coupon.php?code=' + encodeURIComponent(input))
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        couponDiscount = parseFloat(data.discount_pct);
        couponCode = input;
        document.getElementById('hiddenCouponCode').value = input;
        msgEl.innerHTML = '<div class="coupon-msg valid">✅ Coupon applied! ' + couponDiscount + '% discount.</div>';
      } else {
        couponDiscount = 0;
        couponCode = '';
        document.getElementById('hiddenCouponCode').value = '';
        msgEl.innerHTML = '<div class="coupon-msg invalid">❌ ' + data.message + '</div>';
      }
      updatePriceSummary();
    })
    .catch(error => {
      console.error('Error validating coupon:', error);
      couponDiscount = 0;
      couponCode = '';
      document.getElementById('hiddenCouponCode').value = '';
      msgEl.innerHTML = '<div class="coupon-msg invalid">❌ Error validating coupon. Please try again.</div>';
      updatePriceSummary();
    });
}

// ===== CAR SELECTION =====
function selectCar(el) {
  // Remove selection from all
  document.querySelectorAll('.car-select-card').forEach(c => c.classList.remove('selected'));
  // Select this one
  el.classList.add('selected');

  selectedCarId    = el.dataset.carId;
  selectedCarPrice = parseFloat(el.dataset.price);
  selectedCarName  = el.dataset.name + ' (' + el.dataset.year + ')';

  document.getElementById('hiddenCarId').value = selectedCarId;
  updatePriceSummary();
}

// ===== PRICE CALCULATION =====
function updatePriceSummary() {
  if (!selectedCarPrice || !totalDays) return;

  const base     = selectedCarPrice * totalDays;
  const afterDur = base - (base * durationDiscount / 100);
  const total    = afterDur - (afterDur * couponDiscount / 100);

  // Step 2 summary
  const ps = document.getElementById('priceSummary');
  ps.style.display = 'block';
  document.getElementById('psCarName').textContent = selectedCarName;
  document.getElementById('psPricePerDay').textContent = '$' + selectedCarPrice.toFixed(2) + '/day';
  document.getElementById('psDays').textContent = totalDays + ' days';
  document.getElementById('psSubtotal').textContent = '$' + base.toFixed(2);
  document.getElementById('psTotal').textContent = '$' + total.toFixed(2);

  if (durationDiscount > 0) {
    document.getElementById('psDurDiscLine').style.display = 'flex';
    document.getElementById('psDurDisc').textContent = '-' + durationDiscount + '%';
  } else {
    document.getElementById('psDurDiscLine').style.display = 'none';
  }

  if (couponDiscount > 0) {
    document.getElementById('psCoupDiscLine').style.display = 'flex';
    document.getElementById('psCoupDisc').textContent = '-' + couponDiscount + '%';
  } else {
    document.getElementById('psCoupDiscLine').style.display = 'none';
  }

  // Also update summaries in steps 3 and 4
  updateOrderSummary();
}

function updateOrderSummary() {
  const startVal = document.getElementById('startDate').value;
  const endVal   = document.getElementById('endDate').value;
  const addr     = document.getElementById('deliveryAddress').value;

  const base     = selectedCarPrice * totalDays;
  const afterDur = base - (base * durationDiscount / 100);
  const total    = afterDur - (afterDur * couponDiscount / 100);

  // Format dates
  let dateStr = '—';
  if (startVal && endVal) {
    const s = new Date(startVal);
    const e = new Date(endVal);
    dateStr = s.toLocaleDateString('en-GB', {day:'numeric',month:'short'}) + ' → ' +
              e.toLocaleDateString('en-GB', {day:'numeric',month:'short',year:'numeric'});
  }

  // Step 3
  document.getElementById('os3Car').textContent     = selectedCarName || '—';
  document.getElementById('os3Dates').textContent    = dateStr;
  document.getElementById('os3Days').textContent     = totalDays ? totalDays + ' days' : '—';
  document.getElementById('os3Address').textContent  = addr || '—';
  document.getElementById('os3Total').textContent    = '$' + total.toFixed(2);

  // Step 4
  document.getElementById('ps4Car').textContent      = selectedCarName || '—';
  document.getElementById('ps4Dates').textContent    = dateStr;
  document.getElementById('ps4Days').textContent     = totalDays ? totalDays + ' days' : '—';
  document.getElementById('ps4Address').textContent  = addr || '—';
  document.getElementById('ps4Base').textContent     = '$' + base.toFixed(2);
  document.getElementById('ps4Total').textContent    = '$' + total.toFixed(2);

  if (durationDiscount > 0) {
    document.getElementById('ps4DurLine').style.display = 'flex';
    document.getElementById('ps4DurDisc').textContent = '-' + durationDiscount + '%';
  } else {
    document.getElementById('ps4DurLine').style.display = 'none';
  }
  if (couponDiscount > 0) {
    document.getElementById('ps4CoupLine').style.display = 'flex';
    document.getElementById('ps4CoupDisc').textContent = '-' + couponDiscount + '%';
  } else {
    document.getElementById('ps4CoupLine').style.display = 'none';
  }
}

// ===== DEMO BOOKING DETAILS =====
function formatDemoNumber(input) {
  let val = input.value.replace(/\D/g, '');
  val = val.substring(0, 16);
  let formatted = val.replace(/(.{4})/g, '$1 ').trim();
  input.value = formatted;
}

function formatExpiry(input) {
  let val = input.value.replace(/\D/g, '');
  if (val.length > 4) val = val.substring(0, 4);
  
  // Validate and restrict month to 01-12
  if (val.length >= 2) {
    const month = parseInt(val.substring(0, 2), 10);
    if (month > 12) {
      // If month is invalid (> 12), only keep the first digit and let user continue
      val = val.substring(0, 1);
    }
  }
  
  // Format as MM/YY
  if (val.length >= 3) {
    val = val.substring(0, 2) + '/' + val.substring(2);
  }
  input.value = val;
}

function updateDemoDisplay() {
  // Number
  const numVal = document.getElementById('demoNumber').value.replace(/\D/g, '');
  let display = '';
  for (let i = 0; i < 16; i++) {
    if (i > 0 && i % 4 === 0) display += ' ';
    display += i < numVal.length ? numVal[i] : '•';
  }
  document.getElementById('demoNumberDisplay').textContent = display;

  const logo = document.getElementById('demoTypeLogo');
  logo.textContent = 'DEMO';

  // Name
  const nameVal = document.getElementById('demoName').value.trim();
  document.getElementById('demoNameDisplay').textContent = nameVal || 'YOUR NAME';

  // Expiry
  const expVal = document.getElementById('demoExpiry').value;
  document.getElementById('demoExpiryDisplay').textContent = expVal || 'MM/YY';

  // Security code
  const codeVal = document.getElementById('demoCode').value;
  let codeDisplay = '';
  for (let i = 0; i < 3; i++) {
    codeDisplay += i < codeVal.length ? codeVal[i] : '•';
  }
  document.getElementById('demoCodeDisplay').textContent = codeDisplay;
}

function flipDemoPanel(flip) {
  const card = document.getElementById('demoPanel');
  if (flip) {
    card.classList.add('flipped');
  } else {
    card.classList.remove('flipped');
  }
}

// ===== WIZARD NAVIGATION =====
function changeStep(dir) {
  const newStep = currentStep + dir;

  // Validate current step before going forward
  if (dir > 0) {
    if (!validateStep(currentStep)) return;
  }

  if (newStep < 1 || newStep > 4) return;

  // If going to Step 2, fetch available cars first
  if (currentStep === 1 && dir > 0) {
    const startVal = document.getElementById('startDate').value;
    const endVal   = document.getElementById('endDate').value;
    
    const grid = document.getElementById('carsGrid');
    grid.innerHTML = '<div class="cars-grid-message">🚗 Loading available cars for these dates...</div>';
    
    fetch('get_available_cars.php?start_date=' + encodeURIComponent(startVal) + '&end_date=' + encodeURIComponent(endVal))
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderCars(data.cars);
          proceedToStep(newStep);
        } else {
          showToast('Failed to load cars: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error fetching available cars:', error);
        showToast('Error loading available cars. Please try again.');
      });
    return;
  }

  proceedToStep(newStep);
}

function proceedToStep(newStep) {
  currentStep = newStep;

  // Show/hide panels
  document.querySelectorAll('.wizard-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('step' + currentStep).classList.add('active');

  // Update progress
  updateProgress(currentStep);

  // Show/hide nav buttons
  document.getElementById('backBtn').style.visibility = currentStep === 1 ? 'hidden' : 'visible';

  if (currentStep === 4) {
    document.getElementById('nextBtn').style.display = 'none';
  } else {
    document.getElementById('nextBtn').style.display = 'inline-flex';
  }

  // Update summaries when entering step 3 or 4
  if (currentStep >= 3) {
    updateOrderSummary();
  }

  // Scroll to top of wizard
  document.querySelector('.wizard-progress').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function renderCars(cars) {
  const grid = document.getElementById('carsGrid');
  if (cars.length === 0) {
    grid.innerHTML = '<div class="cars-grid-message">🚗 No cars are available for the selected dates. Please choose different dates.</div>';
    selectedCarId = null;
    selectedCarPrice = 0;
    selectedCarName = '';
    document.getElementById('hiddenCarId').value = '';
    document.getElementById('priceSummary').style.display = 'none';
    return;
  }

  let html = '';
  cars.forEach(car => {
    const isSelected = (selectedCarId && selectedCarId.toString() === car.id.toString());
    html += `
      <div class="car-select-card ${isSelected ? 'selected' : ''}" data-car-id="${car.id}"
           data-price="${car.price_per_day}"
           data-name="${escapeHtml(car.brand)} ${escapeHtml(car.model)}"
           data-year="${car.year}">
        <div class="car-select-check">✓</div>
        <div class="car-select-img">
          <img src="images/${car.id}.jpg"
               alt="${escapeHtml(car.brand)} ${escapeHtml(car.model)}">
        </div>
        <div class="car-select-info">
          <div class="car-select-name">${escapeHtml(car.brand)} ${escapeHtml(car.model)}</div>
          <div class="car-select-year">${car.year}</div>
          <div class="car-select-meta">
            <span>🪑 ${car.seats} Seats</span>
            <span>⛽ ${escapeHtml(car.fuel_type)}</span>
            <span>⚙️ ${escapeHtml(car.transmission)}</span>
          </div>
          <div class="car-select-footer">
            <div class="car-select-price">
              <span class="price-num">$${car.price_per_day.toFixed(2)}</span>
              <span class="price-label">/day</span>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  grid.innerHTML = html;
}

function escapeHtml(text) {
  if (!text) return '';
  return text
    .toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function validateStep(step) {
  switch(step) {
    case 1: {
      const s = document.getElementById('startDate').value;
      const e = document.getElementById('endDate').value;
      if (!s || !e) {
        showToast('Please select both start and end dates.');
        return false;
      }
      if (new Date(e) <= new Date(s)) {
        showToast('End date must be after start date.');
        return false;
      }
      return true;
    }
    case 2: {
      if (!selectedCarId) {
        showToast('Please select a car.');
        return false;
      }
      return true;
    }
    case 3: {
      const street = document.getElementById('deliveryStreet').value.trim();
      const zip = document.getElementById('deliveryZip').value.trim();
      const city = document.getElementById('deliveryCity').value;

      if (!street) {
        showToast('Please enter a street address.');
        return false;
      }
      if (street.length < 5) {
        showToast('Please enter a valid street address (minimum 5 characters).');
        return false;
      }
      if (!zip) {
        showToast('Please enter a German postal code.');
        return false;
      }
      if (!/^\d{5}$/.test(zip)) {
        showToast('German postal codes must be exactly 5 digits.');
        return false;
      }
      if (!city) {
        showToast('Please select a city in Germany.');
        return false;
      }
      return true;
    }
    case 4: {
      const num  = document.getElementById('demoNumber').value.replace(/\s/g, '');
      const name = document.getElementById('demoName').value.trim();
      const exp  = document.getElementById('demoExpiry').value;
      const code = document.getElementById('demoCode').value;

      if (num.length < 16) { showToast('Please enter a valid demo number.'); return false; }
      if (!name)           { showToast('Please enter the booking name.'); return false; }
      
      // Expiry Date MM/YY Validation
      if (exp.length < 5)  { showToast('Please enter a valid expiry date (MM/YY).'); return false; }
      if (!exp.includes('/')) { showToast('Expiry date must be in MM/YY format (e.g., 05/28).'); return false; }
      
      const expParts = exp.split('/');
      if (expParts.length !== 2) {
        showToast('Expiry date must be in MM/YY format.');
        return false;
      }
      const expMonth = parseInt(expParts[0], 10);
      const expYear = parseInt(expParts[1], 10);
      
      // Validate month is 01-12
      if (isNaN(expMonth) || expMonth < 1 || expMonth > 12) {
        showToast('Expiry month must be between 01 and 12.');
        return false;
      }
      
      // Validate year is 2 digits
      if (isNaN(expYear) || expYear < 0 || expYear > 99) {
        showToast('Expiry year must be 2 digits (00-99).');
        return false;
      }
      
      const now = new Date();
      const currentYear = now.getFullYear() % 100;
      const currentMonth = now.getMonth() + 1;
      if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
        showToast('The demo expiry date has passed or the expiry year is invalid.');
        return false;
      }

      // Security code validation
      const codeDigits = code.replace(/\D/g, '');
      if (codeDigits.length !== 3 || code.length !== 3) {
        showToast('Security code must be exactly 3 digits.');
        return false;
      }
      return true;
    }
  }
  return true;
}

function updateProgress(step) {
  const steps = document.querySelectorAll('.wizard-step');
  const lines = [
    document.getElementById('line1'),
    document.getElementById('line2'),
    document.getElementById('line3'),
    document.getElementById('line4')
  ];

  steps.forEach((s, i) => {
    const sNum = i + 1;
    s.classList.remove('active', 'completed');
    if (sNum < step) {
      s.classList.add('completed');
      s.querySelector('.wizard-step-circle').textContent = '✓';
    } else if (sNum === step) {
      s.classList.add('active');
      s.querySelector('.wizard-step-circle').textContent = sNum;
    } else {
      s.querySelector('.wizard-step-circle').textContent = sNum;
    }
  });

  lines.forEach((line, i) => {
    if (i < step - 1) {
      line.classList.add('filled');
    } else {
      line.classList.remove('filled');
    }
  });
}

// ===== TOAST NOTIFICATIONS =====
function showToast(msg) {
  // Remove existing
  const existing = document.querySelector('.wizard-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = 'wizard-toast';
  toast.innerHTML = '⚠️ ' + msg;
  document.body.appendChild(toast);

  requestAnimationFrame(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';
  });

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(-50%) translateY(20px)';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ===== CONFETTI =====
function launchConfetti() {
  const container = document.getElementById('confettiContainer');
  if (!container) return;
  const colors = ['#e0b84b', '#f0d080', '#48c78e', '#ff6b6b', '#4da6ff', '#fff'];

  for (let i = 0; i < 60; i++) {
    setTimeout(() => {
      const confetti = document.createElement('div');
      confetti.className = 'confetti';
      confetti.style.left = Math.random() * 100 + '%';
      confetti.style.width = Math.random() * 8 + 5 + 'px';
      confetti.style.height = Math.random() * 8 + 5 + 'px';
      confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
      confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
      confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
      confetti.style.animationDelay = '0s';
      container.appendChild(confetti);

      setTimeout(() => confetti.remove(), 4000);
    }, i * 50);
  }

  // Remove container after animation
  setTimeout(() => container.remove(), 5000);
}

// ===== GO TO STEP HELPER =====
function goToStep(step) {
  if (step < 1 || step > 4) return;
  currentStep = step;

  // Show/hide panels
  document.querySelectorAll('.wizard-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('step' + currentStep).classList.add('active');

  // Update progress
  updateProgress(currentStep);

  // Show/hide nav buttons
  document.getElementById('backBtn').style.visibility = currentStep === 1 ? 'hidden' : 'visible';

  if (currentStep === 4) {
    document.getElementById('nextBtn').style.display = 'none';
  } else {
    document.getElementById('nextBtn').style.display = 'inline-flex';
  }

  // Update summaries
  updateOrderSummary();
}

// ===== INTERCEPT FORM SUBMISSION & FORCE STEP VALIDATION =====
rentalForm.addEventListener('submit', function(e) {
  // Prevent submission if the user is not on Step 4 (e.g. hit Enter elsewhere)
  if (currentStep < 4) {
    e.preventDefault();
    changeStep(1);
    return false;
  }

  // Validate all steps strictly on submission
  if (!validateStep(1)) { e.preventDefault(); goToStep(1); return false; }
  if (!validateStep(2)) { e.preventDefault(); goToStep(2); return false; }
  if (!validateStep(3)) { e.preventDefault(); goToStep(3); return false; }
  if (!validateStep(4)) { e.preventDefault(); goToStep(4); return false; }
  
  return true;
});

// ===== INITIALIZE AUTO-CALCULATIONS ON PAGE LOAD =====
document.addEventListener('DOMContentLoaded', () => {
  // Restore preselected car
  const preselectedCarId = document.getElementById('hiddenCarId').value;
  if (preselectedCarId) {
    const cardEl = document.querySelector(`.car-select-card[data-car-id="${preselectedCarId}"]`);
    if (cardEl) {
      selectCar(cardEl);
    }
  }

  // Parse and trigger dates if values are present
  calculateDates();
  
  // Parse and trigger address assembly
  assembleAddress();

  // Parse and trigger demo display rendering
  updateDemoDisplay();

  // If there was an error, make sure we show step 4 directly
  if (currentStep === 4) {
    goToStep(4);
  }
});
})();
