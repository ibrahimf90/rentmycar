<?php
// ============================================
// Project : Rent a Car
// File    : rent.php
// Purpose : 5-Step Rental Wizard
// ============================================
require_once 'config.php';

// Must be logged in
if (!isLoggedIn()) {
    redirect('signin.php?redirect=rent.php');
}

$userId = currentUserId();
$error = '';
$success = false;
$rentalData = null;

// Comprehensive list of cities and towns in Germany
$germanCities = [
    'Aachen', 'Aalen', 'Achern', 'Ahrensburg', 'Altenburg', 'Alzey', 'Amberg', 'Ansbach', 'Apolda', 'Arnsberg', 
    'Aschaffenburg', 'Aschersleben', 'Augsburg', 'Aurich', 'Bad Homburg', 'Bad Honnef', 'Bad Kreuznach', 
    'Bad Oeynhausen', 'Bad Oldesloe', 'Bad Salzuflen', 'Baden-Baden', 'Baunatal', 'Bautzen', 'Bayreuth', 
    'Beckum', 'Bensheim', 'Bergheim', 'Bergisch Gladbach', 'Berlin', 'Bernburg', 'Biberach', 'Bielefeld', 
    'Bietigheim-Bissingen', 'Bingen', 'Bitterfeld-Wolfen', 'Bochum', 'Bocholt', 'Bonn', 'Bottrop', 
    'Brandenburg an der Havel', 'Braunschweig', 'Bremen', 'Bremerhaven', 'Bretten', 'Bruchsal', 'Brühl', 
    'Buchen', 'Bühl', 'Bünde', 'Buxtehude', 'Calw', 'Castrop-Rauxel', 'Celle', 'Chemnitz', 'Cloppenburg', 
    'Coburg', 'Cochem', 'Coesfeld', 'Cologne (Köln)', 'Cottbus', 'Crailsheim', 'Cuxhaven', 'Dachau', 
    'Darmstadt', 'Deggendorf', 'Delmenhorst', 'Dessau-Roßlau', 'Detmold', 'Dillenburg', 'Dinkelsbühl', 
    'Dinslaken', 'Döbeln', 'Dormagen', 'Dorsten', 'Dortmund', 'Dresden', 'Duisburg', 'Düren', 'Düsseldorf', 
    'Eberswalde', 'Eckernförde', 'Edewecht', 'Eichstätt', 'Eisenach', 'Erding', 'Erftstadt', 'Erfurt', 
    'Erkelenz', 'Erlangen', 'Eschweiler', 'Essen', 'Esslingen', 'Ettlingen', 'Euskirchen', 'Flensburg', 
    'Forchheim', 'Frankenthal', 'Frankfurt am Main', 'Frankfurt (Oder)', 'Freiberg', 'Freiburg im Breisgau', 
    'Freising', 'Freital', 'Friedberg', 'Friedrichshafen', 'Fulda', 'Fürstenwalde', 'Fürth', 'Garbsen', 
    'Gauting', 'Geesthacht', 'Gelsenkirchen', 'Gera', 'Giessen', 'Gladbeck', 'Goch', 'Göppingen', 'Görlitz', 
    'Goslar', 'Gotha', 'Göttingen', 'Greifswald', 'Greven', 'Gronau', 'Gummersbach', 'Güstrow', 'Gütersloh', 
    'Haan', 'Haar', 'Hagen', 'Halberstadt', 'Halle (Saale)', 'Halter am See', 'Hamburg', 'Hameln', 'Hamm', 
    'Hanau', 'Hannover', 'Harsewinkel', 'Hattingen', 'Heidelberg', 'Heidenheim', 'Heilbronn', 'Heinsberg', 
    'Helmstedt', 'Hemer', 'Hennef', 'Herford', 'Herne', 'Herten', 'Herzogenaurach', 'Hilden', 'Hildesheim', 
    'Hof', 'Homburg', 'Horb', 'Höxter', 'Hückelhoven', 'Hürth', 'Husum', 'Ibbenbüren', 'Idar-Oberstein', 
    'Ilmenau', 'Ingolstadt', 'Iserlohn', 'Itzehoe', 'Jena', 'Jülich', 'Kaarst', 'Kaiserslautern', 'Kamp-Lintfort', 
    'Karben', 'Karlsruhe', 'Kassel', 'Kehl', 'Kelheim', 'Kempten', 'Kerpen', 'Kiel', 'Kirchheim unter Teck', 
    'Kleve', 'Koblenz', 'Koenigstein', 'Konstanz', 'Konz', 'Krefeld', 'Kronberg', 'Kulmbach', 'Lahr', 
    'Lampertheim', 'Landau', 'Landsberg am Lech', 'Landshut', 'Langen', 'Langenfeld', 'Lehrte', 'Leimen', 
    'Leinfelden-Echterdingen', 'Leipzig', 'Lemgo', 'Lennestadt', 'Leonberg', 'Leutkirch', 'Leverkusen', 
    'Lichtenfels', 'Limburg', 'Lindau', 'Lingen', 'Lippstadt', 'Lohmar', 'Lörrach', 'Lübeck', 'Lüdenscheid', 
    'Ludwigsburg', 'Ludwigshafen', 'Lüneburg', 'Lünen', 'Magdeburg', 'Maintal', 'Mainz', 'Mannheim', 
    'Marburg', 'Marl', 'Mayen', 'Mechernich', 'Meckenheim', 'Meerbusch', 'Meiningen', 'Meißen', 'Melle', 
    'Memmingen', 'Menden', 'Meppen', 'Merzig', 'Meschede', 'Mettmann', 'Metzingen', 'Minden', 'Moers', 
    'Mönchengladbach', 'Monheim', 'Monschau', 'Montabaur', 'Mühldorf', 'Mühlhausen', 'Mülheim an der Ruhr', 
    'Munich (München)', 'Münster', 'Nettetal', 'Neu-Isenburg', 'Neu-Ulm', 'Neubrandenburg', 'Neumünster', 
    'Neunkirchen', 'Neuruppin', 'Neuss', 'Neustadt an der Weinstraße', 'Neuwied', 'Nienburg', 'Norden', 
    'Norderstedt', 'Nordhorn', 'Nördlingen', 'Northeim', 'Nuremberg (Nürnberg)', 'Nürtingen', 'Oberhausen', 
    'Oer-Erkenschwick', 'Offenbach am Main', 'Offenburg', 'Oldenburg', 'Olpe', 'Oranienburg', 'Osnabrück', 
    'Osterode', 'Overath', 'Paderborn', 'Papenburg', 'Passau', 'Peine', 'Pforzheim', 'Pirmasens', 'Pirna', 
    'Plauen', 'Plettenberg', 'Porta Westfalica', 'Potsdam', 'Quedlinburg', 'Quickborn', 'Radebeul', 
    'Radolfzell', 'Rastatt', 'Ratingen', 'Ravensburg', 'Recklinghausen', 'Regensburg', 'Reichenbach', 
    'Remscheid', 'Rendsburg', 'Reutlingen', 'Rheda-Wiedenbrück', 'Rheinbach', 'Rheinberg', 'Rheine', 
    'Rheinfelden', 'Rinteln', 'Rostock', 'Rottenburg', 'Rottweil', 'Rudolstadt', 'Rüsselsheim', 'Saalfeld', 
    'Saarbrücken', 'Saarlouis', 'Salzgitter', 'Sangerhausen', 'Sankt Augustin', 'Schleswig', 'Schloß Holte-Stukenbrock', 
    'Schmalkalden', 'Schorndorf', 'Schramberg', 'Schwäbisch Gmünd', 'Schwäbisch Hall', 'Schwandorf', 
    'Schwedt', 'Schweinfurt', 'Schwerte', 'Schwetzingen', 'Schwerin', 'Siegburg', 'Siegen', 'Singen', 
    'Sinsheim', 'Soest', 'Solingen', 'Spandau', 'Speyer', 'Spremberg', 'Stade', 'Stadtallendorf', 
    'Starnberg', 'Stassfurt', 'Steinfurt', 'Stendal', 'Stolberg', 'Stralsund', 'Straubing', 'Stuttgart', 
    'Suhl', 'Sulingen', 'Syke', 'Teltow', 'Templin', 'Torgau', 'Trier', 'Troisdorf', 'Trossingen', 
    'Tübingen', 'Tuttlingen', 'Uelzen', 'Ulm', 'Unna', 'Unterhaching', 'Varel', 'Vechta', 'Velbert', 
    'Verden', 'Verl', 'Viersen', 'Villingen-Schwenningen', 'Voerde', 'Völklingen', 'Waghäusel', 
    'Waiblingen', 'Waldkraiburg', 'Waldkirch', 'Walsrode', 'Wandsbek', 'Wangen', 'Warburg', 'Warendorf', 
    'Weiden', 'Weimar', 'Weingarten', 'Weinheim', 'Weinstadt', 'Weissenfels', 'Werdau', 'Werne', 
    'Wernigerode', 'Wertheim', 'Wesel', 'Wesseling', 'Westerstede', 'Wetzlar', 'Weyhe', 'Wiesbaden', 
    'Wilhelmshaven', 'Winnenden', 'Wismar', 'Witten', 'Wittenberge', 'Wittlich', 'Wolfenbüttel', 
    'Wolfsburg', 'Worms', 'Wriezen', 'Wunstorf', 'Wuppertal', 'Würzburg', 'Xanten', 'Zeitz', 'Zell', 
    'Zirndorf', 'Zittau', 'Zweibrücken', 'Zwickau'
];

// Initially no cars displayed - will fetch filtered cars via AJAX based on selected dates
$cars = [];

// Fetch user's available coupons
$couponsResult = mysqli_query($conn, "SELECT * FROM coupons WHERE user_id = $userId AND is_used = 0");
$availableCoupons = [];
while ($row = mysqli_fetch_assoc($couponsResult)) {
    $availableCoupons[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_rental'])) {
    $carId           = (int) $_POST['car_id'];
    $startDate       = clean($conn, $_POST['start_date']);
    $endDate         = clean($conn, $_POST['end_date']);
    
    // Address components
    $deliveryStreet  = isset($_POST['delivery_street']) ? clean($conn, $_POST['delivery_street']) : '';
    $deliveryZip     = isset($_POST['delivery_zip']) ? clean($conn, $_POST['delivery_zip']) : '';
    $deliveryCity    = isset($_POST['delivery_city']) ? clean($conn, $_POST['delivery_city']) : '';
    
    // Demo booking verification details
    $demoNumber      = isset($_POST['demo_number']) ? clean($conn, $_POST['demo_number']) : '';
    $demoName        = isset($_POST['demo_name']) ? clean($conn, $_POST['demo_name']) : '';
    $demoExpiry      = isset($_POST['demo_expiry']) ? clean($conn, $_POST['demo_expiry']) : '';
    $demoCode        = isset($_POST['demo_code']) ? clean($conn, $_POST['demo_code']) : '';

    $couponCode      = isset($_POST['coupon_code']) ? clean($conn, $_POST['coupon_code']) : '';

    // 1. Validate Dates
    $start = new DateTime($startDate);
    $end   = new DateTime($endDate);
    $today = new DateTime('today');

    if ($start < $today) {
        $error = 'Start date cannot be in the past.';
    } elseif ($end <= $start) {
        $error = 'End date must be after start date.';
    }
    
    // 2. Validate Address components
    if (empty($error)) {
        if (empty($deliveryStreet) || strlen($deliveryStreet) < 5) {
            $error = 'Please enter a valid street address.';
        } elseif (empty($deliveryZip) || !preg_match('/^\d{5}$/', $deliveryZip)) {
            $error = 'Please enter a valid 5-digit German postal code (PLZ).';
        } elseif (empty($deliveryCity) || !in_array($deliveryCity, $germanCities)) {
            $error = 'Please select a valid city in Germany.';
        } else {
            $deliveryAddress = $deliveryStreet . ', ' . $deliveryZip . ' ' . $deliveryCity . ', Germany';
        }
    }

    // 3. Validate demo booking details
    if (empty($error)) {
        $numClean = str_replace(' ', '', $demoNumber);
        if (empty($numClean) || strlen($numClean) < 16 || !ctype_digit($numClean)) {
            $error = 'Please enter a valid 16-digit demo number.';
        } elseif (empty($demoName)) {
            $error = 'Please enter the name for this booking.';
        } elseif (empty($demoExpiry) || strlen($demoExpiry) < 5) {
            $error = 'Please enter the demo expiry date in MM/YY format.';
        } else {
            $expParts = explode('/', $demoExpiry);
            if (count($expParts) !== 2) {
                $error = 'Expiry date must be in MM/YY format.';
            } else {
                $expMonth = (int)$expParts[0];
                $expYear  = (int)$expParts[1];
                if ($expMonth < 1 || $expMonth > 12) {
                    $error = 'Expiry month must be between 01 and 12.';
                } else {
                    $currentYear  = (int)date('y');
                    $currentMonth = (int)date('m');
                    if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
                        $error = 'The demo expiry date has passed or the expiry year is invalid.';
                    }
                }
            }
        }
    }

    if (empty($error)) {
        $demoCodeClean = preg_replace('/\D/', '', $demoCode);
        if (strlen($demoCodeClean) !== 3 || strlen($demoCode) !== 3) {
            $error = 'Demo security code must be exactly 3 digits.';
        }
    }

    // Process rental if validations succeeded
    if (empty($error)) {
        $totalDays = $start->diff($end)->days;
        if ($totalDays < 1) $totalDays = 1;

        // Fetch car
        $carResult = mysqli_query($conn, "SELECT * FROM cars WHERE id = $carId AND is_available = 1");
        $car = mysqli_fetch_assoc($carResult);

        if (!$car) {
            $error = 'Selected car is not available.';
        } else {
            // Check if car is already booked during these dates (double check on backend)
            $checkBooking = mysqli_query($conn,
                "SELECT id FROM rentals 
                 WHERE car_id = $carId 
                   AND status != 'cancelled' 
                   AND start_date <= '$endDate' 
                   AND end_date >= '$startDate'
                 LIMIT 1"
            );
            
            if ($checkBooking && mysqli_num_rows($checkBooking) > 0) {
                $error = 'Selected car is already booked during the selected dates. Please choose another car.';
            } else {
                $pricePerDay = $car['price_per_day'];
                $durationDiscount = getDurationDiscount($totalDays);
                $couponDiscount = 0;
                $usedCouponCode = null;

                // Validate coupon
                if (!empty($couponCode)) {
                    $couponCheck = mysqli_query($conn,
                        "SELECT * FROM coupons WHERE code = '$couponCode' AND user_id = $userId AND is_used = 0 LIMIT 1"
                    );
                    $coupon = mysqli_fetch_assoc($couponCheck);
                    if ($coupon) {
                        $couponDiscount = $coupon['discount_pct'];
                        $usedCouponCode = $couponCode;
                    } else {
                        $error = 'Invalid or already used coupon code.';
                    }
                }

                if (empty($error)) {
                    $totalPrice = calculateFinalPrice($pricePerDay, $totalDays, $durationDiscount, $couponDiscount);

                    // Insert rental
                    $stmt = mysqli_query($conn,
                        "INSERT INTO rentals (user_id, car_id, start_date, end_date, total_days, price_per_day,
                         duration_discount, coupon_discount, coupon_code, total_price, delivery_address, status)
                         VALUES ($userId, $carId, '$startDate', '$endDate', $totalDays, $pricePerDay,
                         $durationDiscount, $couponDiscount, " .
                        ($usedCouponCode ? "'$usedCouponCode'" : "NULL") .
                        ", $totalPrice, '$deliveryAddress', 'pending')"
                    );

                    if ($stmt) {
                        // Update total rents
                        mysqli_query($conn, "UPDATE users SET total_rents = total_rents + 1 WHERE id = $userId");

                        // Mark coupon as used
                        if ($usedCouponCode) {
                            mysqli_query($conn, "UPDATE coupons SET is_used = 1 WHERE code = '$usedCouponCode' AND user_id = $userId");
                        }

                        // Check for new coupon generation
                        checkAndGenerateCoupon($conn, $userId);

                        $success = true;
                        $rentalData = [
                            'car'       => $car['brand'] . ' ' . $car['model'],
                            'car_year'  => $car['year'],
                            'start'     => $startDate,
                            'end'       => $endDate,
                            'days'      => $totalDays,
                            'price'     => $totalPrice,
                            'address'   => $deliveryAddress,
                            'dur_disc'  => $durationDiscount,
                            'coup_disc' => $couponDiscount,
                        ];
                    } else {
                        $error = 'Failed to create rental. Please try again.';
                    }
                }
            }
        }
    }
}

$pageTitle  = 'Rent a Car — MyCar';
$activePage = '';
include 'header.php';
?>



<!-- ========== PAGE HERO ========== -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <p class="section-tag">Premium Rental</p>
      <h1>Rent a <span class="gold-text">Car</span></h1>
      <p>Complete the steps below to reserve your dream car.</p>
    </div>
  </div>
</section>

<!-- ========== WIZARD SECTION ========== -->
<section class="section">
  <div class="container">

    <?php if ($error): ?>
      <div class="alert alert-error rent-alert"><?= $error ?></div>
    <?php endif; ?>

    <!-- Progress Bar -->
    <div class="wizard-progress" id="wizardProgress">
      <div class="wizard-step active" data-step="1">
        <div class="wizard-step-circle">1</div>
        <span class="wizard-step-label">Dates</span>
      </div>
      <div class="wizard-line" id="line1"><div class="wizard-line-fill"></div></div>
      <div class="wizard-step" data-step="2">
        <div class="wizard-step-circle">2</div>
        <span class="wizard-step-label">Car</span>
      </div>
      <div class="wizard-line" id="line2"><div class="wizard-line-fill"></div></div>
      <div class="wizard-step" data-step="3">
        <div class="wizard-step-circle">3</div>
        <span class="wizard-step-label">Address</span>
      </div>
      <div class="wizard-line" id="line3"><div class="wizard-line-fill"></div></div>
      <div class="wizard-step" data-step="4">
        <div class="wizard-step-circle">4</div>
        <span class="wizard-step-label">Review</span>
      </div>
      <div class="wizard-line" id="line4"><div class="wizard-line-fill"></div></div>
      <div class="wizard-step" data-step="5">
        <div class="wizard-step-circle">5</div>
        <span class="wizard-step-label">Done</span>
      </div>
    </div>

    <!-- Wizard Body -->
    <form method="POST" id="rentalForm" data-initial-step="<?= $success ? 5 : ($error ? 4 : 1) ?>">
    <input type="hidden" name="confirm_rental" value="1">
    <input type="hidden" name="car_id" id="hiddenCarId" value="<?= isset($_POST['car_id']) ? htmlspecialchars($_POST['car_id']) : '' ?>">
    <input type="hidden" name="coupon_code" id="hiddenCouponCode" value="<?= isset($_POST['coupon_code']) ? htmlspecialchars($_POST['coupon_code']) : '' ?>">

    <div class="wizard-body" id="wizardBody">

      <!-- ===== STEP 1: DATES ===== -->
      <div class="wizard-panel active" id="step1">
        <h2 class="wizard-heading">
          Choose Your <span class="gold-text">Dates</span>
        </h2>
        <p class="wizard-intro">Select your pickup and return dates. Longer rentals get bigger discounts!</p>

        <div class="dates-grid">
          <div class="date-card">
            <label for="startDate">📅 Start Date</label>
            <input type="date" id="startDate" name="start_date" required value="<?= isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : '' ?>">
          </div>
          <div class="date-card">
            <label for="endDate">📅 End Date</label>
            <input type="date" id="endDate" name="end_date" required value="<?= isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : '' ?>">
          </div>
        </div>

        <div class="date-summary is-hidden" id="dateSummary">
          <div class="date-summary-item">
            <span class="ds-label">Duration</span>
            <span class="ds-value" id="daysCount">0 days</span>
          </div>
          <div class="date-summary-divider"></div>
          <div class="date-summary-item">
            <span class="ds-label">Discount</span>
            <span class="ds-value discount-green" id="discountPct">0%</span>
          </div>
          <div class="date-summary-divider"></div>
          <div class="date-summary-item">
            <span class="ds-label">Discount Tier</span>
            <span class="ds-value discount-tier-value" id="discountTier">—</span>
          </div>
        </div>

        <!-- Coupon -->
        <div class="coupon-section">
          <h3>🎁 Have a Coupon Code?</h3>
          <div class="coupon-input-row">
            <input type="text" id="couponInput" placeholder="Enter coupon code..." maxlength="30">
            <button type="button" id="applyCouponBtn">Apply</button>
          </div>
          <div id="couponMsg"></div>
          <?php if (count($availableCoupons) > 0): ?>
          <p class="coupon-note">
            💡 You have <?= count($availableCoupons) ?> available coupon(s).
            Check <a href="old_rent.php" class="rent-link">My Rentals</a> to view them.
          </p>
          <?php endif; ?>
        </div>
      </div>

      <!-- ===== STEP 2: CHOOSE CAR ===== -->
      <div class="wizard-panel" id="step2">
        <h2 class="wizard-heading">
          Choose Your <span class="gold-text">Car</span>
        </h2>
        <p class="wizard-intro">Select the car you'd like to rent. Click on a car to choose it.</p>

        <div class="cars-select-grid" id="carsGrid">
          <div class="cars-grid-message">🚗 Select your rental dates above to see available cars</div>
        </div>

        <div class="price-summary is-hidden" id="priceSummary">
          <h3>💰 Price Breakdown</h3>
          <div class="price-line">
            <span class="pl-label" id="psCarName">—</span>
            <span class="pl-value" id="psPricePerDay">$0/day</span>
          </div>
          <div class="price-line">
            <span class="pl-label">Duration</span>
            <span class="pl-value" id="psDays">0 days</span>
          </div>
          <div class="price-line">
            <span class="pl-label">Subtotal</span>
            <span class="pl-value" id="psSubtotal">$0.00</span>
          </div>
          <div class="price-line discount is-hidden" id="psDurDiscLine">
            <span class="pl-label">Duration Discount</span>
            <span class="pl-value" id="psDurDisc">-0%</span>
          </div>
          <div class="price-line discount is-hidden" id="psCoupDiscLine">
            <span class="pl-label">Coupon Discount</span>
            <span class="pl-value" id="psCoupDisc">-0%</span>
          </div>
          <div class="price-line total">
            <span class="pl-label">Total</span>
            <span class="pl-value" id="psTotal">$0.00</span>
          </div>
        </div>
      </div>

      <!-- ===== STEP 3: ADDRESS ===== -->
      <div class="wizard-panel" id="step3">
        <h2 class="wizard-heading">
          Delivery <span class="gold-text">Address</span>
        </h2>
        <p class="wizard-intro">Tell us where to deliver your car. We'll bring it right to your door!</p>

        <div class="address-layout">
          <div class="address-form-wrap">
            <h3>📍 Delivery Location</h3>
            <p>Our delivery service is exclusive to Germany. Please enter your address details.</p>
            
            <input type="hidden" id="deliveryAddress" name="delivery_address" value="">

            <div class="card-form-group">
              <label for="deliveryStreet">Street Address & House Number</label>
              <input type="text" id="deliveryStreet" name="delivery_street" class="address-input-field" placeholder="e.g. Friedrichstraße 12, Apt 4" value="<?= isset($_POST['delivery_street']) ? htmlspecialchars($_POST['delivery_street']) : '' ?>">
            </div>

            <div class="card-form-row">
              <div class="card-form-group">
                <label for="deliveryZip">Postal Code (PLZ)</label>
                <input type="text" id="deliveryZip" name="delivery_zip" class="address-input-field" placeholder="e.g. 10117" maxlength="5" value="<?= isset($_POST['delivery_zip']) ? htmlspecialchars($_POST['delivery_zip']) : '' ?>">
              </div>
              
              <div class="card-form-group">
                <label for="deliveryCity">City</label>
                <select id="deliveryCity" name="delivery_city" class="address-select-field">
                  <option value="">Select a city...</option>
                  <?php foreach ($germanCities as $cityOption): ?>
                    <option value="<?= $cityOption ?>" <?= (isset($_POST['delivery_city']) && $_POST['delivery_city'] === $cityOption) ? 'selected' : '' ?>><?= $cityOption ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="card-form-group">
              <label>Country</label>
              <input type="text" value="Germany" readonly class="readonly-country">
            </div>

            <p class="coupon-note">
              🚗 Your car will arrive within <strong class="gold-highlight">24 hours</strong> of confirmation.
            </p>
          </div>

          <div class="order-summary-card" id="orderSummary3">
            <h3>📋 Order Summary</h3>
            <div class="order-item">
              <span class="oi-label">🚗 Car</span>
              <span class="oi-value" id="os3Car">—</span>
            </div>
            <div class="order-item">
              <span class="oi-label">📅 Dates</span>
              <span class="oi-value" id="os3Dates">—</span>
            </div>
            <div class="order-item">
              <span class="oi-label">⏱ Duration</span>
              <span class="oi-value" id="os3Days">—</span>
            </div>
            <div class="order-item">
              <span class="oi-label">📍 Address</span>
              <span class="oi-value summary-address" id="os3Address">—</span>
            </div>
            <div class="order-item summary-total-row">
              <span class="oi-label summary-total-label">💰 Total</span>
              <span class="oi-value summary-total-value" id="os3Total">$0.00</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== STEP 4: REVIEW ===== -->
      <div class="wizard-panel" id="step4">
        <h2 class="wizard-heading">
          Booking <span class="gold-text">Review</span>
        </h2>
        <p class="wizard-intro">Enter demo booking details to complete the rental.</p>

        <div class="payment-layout">
          <div class="card-section">
            <!-- Demo booking visualization -->
            <div class="credit-card-wrapper">
              <div class="credit-card" id="demoPanel">
                <!-- FRONT -->
                <div class="card-front">
                  <div class="card-top-row">
                    <div class="card-chip">
                      <div class="chip-inner"></div>
                    </div>
                    <div class="card-type-logo" id="demoTypeLogo">DEMO</div>
                  </div>
                  <div class="card-number-display" id="demoNumberDisplay">
                    •••• •••• •••• ••••
                  </div>
                  <div class="card-bottom-row">
                    <div class="card-holder-area">
                      <div class="card-label-small">Booking Name</div>
                      <div class="card-holder-display" id="demoNameDisplay">YOUR NAME</div>
                    </div>
                    <div class="card-expiry-area">
                      <div class="card-label-small">Valid Until</div>
                      <div class="card-expiry-display" id="demoExpiryDisplay">MM/YY</div>
                    </div>
                  </div>
                </div>
                <!-- BACK -->
                <div class="card-back">
                  <div class="card-magnetic-strip"></div>
                  <div class="verify-code-area">
                    <div class="verify-code-label">Code</div>
                    <div class="verify-code-strip" id="demoCodeDisplay">•••</div>
                  </div>
                  <div class="card-back-logo">MyCar</div>
                </div>
              </div>
            </div>

            <!-- Demo booking form -->
            <div class="card-form">
              <h3>Booking Details</h3>
              <div class="card-form-group">
                <label>Demo Number</label>
                <input type="text" id="demoNumber" name="demo_number" placeholder="1234 5678 9012 3456" maxlength="19" required
                       value="<?= isset($_POST['demo_number']) ? htmlspecialchars($_POST['demo_number']) : '' ?>">
              </div>
              <div class="card-form-group">
                <label>Booking Name</label>
                <input type="text" id="demoName" name="demo_name" placeholder="John Doe" required
                       value="<?= isset($_POST['demo_name']) ? htmlspecialchars($_POST['demo_name']) : '' ?>">
              </div>
              <div class="card-form-row">
                <div class="card-form-group">
                  <label>Valid Until</label>
                  <input type="text" id="demoExpiry" name="demo_expiry" placeholder="MM/YY" maxlength="5" required
                         pattern="(0[1-9]|1[0-2])/[0-9]{2}"
                         value="<?= isset($_POST['demo_expiry']) ? htmlspecialchars($_POST['demo_expiry']) : '' ?>"
                         title="Expiry date must be MM/YY format with valid month (01-12)">
                </div>
                <div class="card-form-group">
                  <label>Security Code</label>
                  <input type="text" id="demoCode" name="demo_code" placeholder="123" maxlength="3" required
                         value="<?= isset($_POST['demo_code']) ? htmlspecialchars($_POST['demo_code']) : '' ?>">
                </div>
              </div>
            </div>
          </div>

          <!-- Final Summary -->
          <div class="payment-summary">
            <h3>📋 Final Summary</h3>
            <div class="order-item">
              <span class="oi-label">🚗 Car</span>
              <span class="oi-value" id="ps4Car">—</span>
            </div>
            <div class="order-item">
              <span class="oi-label">📅 Dates</span>
              <span class="oi-value" id="ps4Dates">—</span>
            </div>
            <div class="order-item">
              <span class="oi-label">⏱ Duration</span>
              <span class="oi-value" id="ps4Days">—</span>
            </div>
            <div class="order-item">
              <span class="oi-label">📍 Address</span>
              <span class="oi-value summary-address" id="ps4Address">—</span>
            </div>
            <div class="price-line payment-base-line">
              <span class="pl-label">Base Price</span>
              <span class="pl-value" id="ps4Base">$0.00</span>
            </div>
            <div class="price-line discount is-hidden" id="ps4DurLine">
              <span class="pl-label">Duration Discount</span>
              <span class="pl-value" id="ps4DurDisc">-0%</span>
            </div>
            <div class="price-line discount is-hidden" id="ps4CoupLine">
              <span class="pl-label">Coupon Discount</span>
              <span class="pl-value" id="ps4CoupDisc">-0%</span>
            </div>
            <div class="price-line total">
              <span class="pl-label">Total</span>
              <span class="pl-value" id="ps4Total">$0.00</span>
            </div>

            <button type="submit" class="wizard-btn wizard-btn-next confirm-pay-btn">
              Confirm Booking
            </button>
          </div>
        </div>
      </div>

      <!-- ===== STEP 5: SUCCESS ===== -->
      <?php if ($success && $rentalData): ?>
      <div class="wizard-panel" id="step5">
        <div class="success-wrapper">
          <div class="success-icon-wrap">
            <div class="success-circle">
              <svg class="success-checkmark" viewBox="0 0 50 50" fill="none">
                <path d="M14 27L22 35L36 17" stroke="#48c78e" stroke-width="3.5"
                      stroke-linecap="round" stroke-linejoin="round"
                      stroke-dasharray="100" stroke-dashoffset="0"/>
              </svg>
            </div>
          </div>
          <h2 class="success-title">Booking <span class="gold-text">Confirmed!</span></h2>
          <p class="success-sub">Your rental has been successfully processed.</p>
          <div class="success-eta">🚗 Your car will arrive within 24 hours</div>

          <div class="success-details">
            <h3>Rental <span class="gold-text">Summary</span></h3>
            <div class="order-item">
              <span class="oi-label">🚗 Car</span>
              <span class="oi-value"><?= htmlspecialchars($rentalData['car']) ?> (<?= $rentalData['car_year'] ?>)</span>
            </div>
            <div class="order-item">
              <span class="oi-label">📅 Dates</span>
              <span class="oi-value"><?= date('d M', strtotime($rentalData['start'])) ?> → <?= date('d M Y', strtotime($rentalData['end'])) ?></span>
            </div>
            <div class="order-item">
              <span class="oi-label">⏱ Duration</span>
              <span class="oi-value"><?= $rentalData['days'] ?> days</span>
            </div>
            <div class="order-item">
              <span class="oi-label">📍 Address</span>
              <span class="oi-value success-address"><?= htmlspecialchars($rentalData['address']) ?></span>
            </div>
            <?php if ($rentalData['dur_disc'] > 0): ?>
            <div class="order-item">
              <span class="oi-label positive-text">🎉 Duration Discount</span>
              <span class="oi-value positive-text">-<?= $rentalData['dur_disc'] ?>%</span>
            </div>
            <?php endif; ?>
            <?php if ($rentalData['coup_disc'] > 0): ?>
            <div class="order-item">
              <span class="oi-label positive-text">🎁 Coupon Discount</span>
              <span class="oi-value positive-text">-<?= $rentalData['coup_disc'] ?>%</span>
            </div>
            <?php endif; ?>
            <div class="order-item summary-total-row">
              <span class="oi-label summary-total-label">💰 Total Paid</span>
              <span class="oi-value success-total-value">$<?= number_format($rentalData['price'], 2) ?></span>
            </div>
          </div>

          <div class="success-actions">
            <a href="old_rent.php" class="btn-primary">🚗 View My Rentals</a>
            <a href="rent.php" class="btn-outline">🔄 Rent Another Car</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <!-- Wizard Navigation -->
    <div class="wizard-nav <?php if ($success): ?>is-hidden<?php endif; ?>" id="wizardNav">
      <button type="button" class="wizard-btn wizard-btn-back back-btn-hidden" id="backBtn">
        ← Back
      </button>
      <button type="button" class="wizard-btn wizard-btn-next" id="nextBtn">
        Next →
      </button>
    </div>

    </form>
  </div>
</section>

<!-- Confetti container for success -->
<?php if ($success): ?>
<div class="confetti-container" id="confettiContainer"></div>
<?php endif; ?>

<?php include 'footer.php'; ?>
