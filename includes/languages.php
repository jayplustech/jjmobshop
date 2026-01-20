<?php
$translations = [
    'en' => [
        // Navigation & General
        'home' => 'Home', 'shop' => 'Shop', 'my_orders' => 'My Orders', 'cart' => 'Cart', 'contact' => 'Contact',
        'login' => 'Login', 'logout' => 'Logout', 'register' => 'Register', 'search_placeholder' => 'Search products...',
        'latest_models' => 'Latest <span>Models</span>', 'add_to_cart' => 'Add to Cart', 'categories' => 'Categories',
        'total_amount' => 'Total Amount', 'place_order' => 'Place Order', 'sort_by' => 'Sort by:',
        'newest' => 'Newest', 'price_low_high' => 'Price: Low to High', 'price_high_low' => 'Price: High to Low',
        'no_products' => 'No products found. Try a different search.', 'show_all' => 'Show All Products',
        'search_results' => "Search Results for '<span>{query}</span>'", 'category_label' => "Category: <span>{name}</span>",
        'lang_name' => 'English', 'switch_to' => 'Kiswahili',
        
        // Auth Pages
        'login_title' => 'Login to Account', 'register_title' => 'Create Account', 'email_label' => 'Email Address',
        'password_label' => 'Password', 'name_label' => 'Full Name', 'accept_terms' => 'I agree to the Terms & Conditions',
        'already_have_acc' => 'Already have an account?', 'forgot_password' => 'Forgot password?',
        
        // Checkout & API (Payment Selection)
        'checkout_summary' => 'Order Summary', 'empty_cart' => 'Your cart is empty', 'item' => 'Item',
        'qty' => 'Qty', 'price' => 'Price', 'delivery_address' => 'Delivery Address', 'enter_address' => 'Enter your physical address...',
        'payment_method' => 'Select Payment Method', 'phone_number' => 'Payment Phone Number',
        'mpesa' => 'M-Pesa', 'tigo' => 'Tigo Pesa', 'airtel' => 'Airtel Money', 'halotel' => 'Halopesa',
        'confirm_pay' => 'Confirm & Pay Now', 'secure_checkout' => 'Secure Sandbox Checkout',
        
        // Order Success
        'order_placed' => 'Order Placed Successfully!', 'order_id' => 'Order ID', 'payment_prompt_sent' => 'Payment Prompt Sent!',
        'check_phone' => 'Please check your phone and enter your PIN to complete the payment.',
        'verifying_pin' => 'Verifying PIN input...', 'pin_entered' => 'PIN Entered!', 'validating_tx' => 'Validating transaction...',
        'tx_success' => 'Transaction Successful!', 'tx_failed' => 'Transaction Failed!', 'redirecting' => 'Redirecting...',
        
        // My Orders
        'order_date' => 'Date', 'order_status' => 'Status', 'order_action' => 'Action', 'view' => 'View',
        'no_orders' => "You haven't placed any orders yet.", 'order_details' => 'Order Details',
        'status_pending' => 'Pending', 'status_paid' => 'Paid', 'status_failed' => 'Failed', 'status_completed' => 'Completed',
        'status_processing' => 'Processing', 'status_cancelled' => 'Cancelled'
    ],
    'sw' => [
        // Navigation & General
        'home' => 'Nyumbani', 'shop' => 'Duka', 'my_orders' => 'Oda Zangu', 'cart' => 'Kikapu', 'contact' => 'Mawasiliano',
        'login' => 'Ingia', 'logout' => 'Toka', 'register' => 'Sajili', 'search_placeholder' => 'Tafuta bidhaa...',
        'latest_models' => 'Mifano <span>Mipya</span>', 'add_to_cart' => 'Weka Kikapuni', 'categories' => 'Vipengele',
        'total_amount' => 'Jumla ya Malipo', 'place_order' => 'Kamilisha Oda', 'sort_by' => 'Panga kwa:',
        'newest' => 'Mpya Zaidi', 'price_low_high' => 'Bei: Chini kwenda Juu', 'price_high_low' => 'Bei: Juu kwenda Chini',
        'no_products' => 'Hatukupata bidhaa unayotafuta. Jaribu tena.', 'show_all' => 'Onyesha Bidhaa Zote',
        'search_results' => "Matokeo ya '<span>{query}</span>'", 'category_label' => "Kipengele: <span>{name}</span>",
        'lang_name' => 'Kiswahili', 'switch_to' => 'English',

        // Auth Pages
        'login_title' => 'Ingia kwenye Akaunti', 'register_title' => 'Tengeneza Akaunti', 'email_label' => 'Barua Pepe (Email)',
        'password_label' => 'Nywila (Password)', 'name_label' => 'Jina Kamili', 'accept_terms' => 'Nakubaliana na Masharti na Vigezo',
        'already_have_acc' => 'Tayari una akaunti?', 'forgot_password' => 'Umesahau nywila?',

        // Checkout & API (Payment Selection)
        'checkout_summary' => 'Muhtasari wa Oda', 'empty_cart' => 'Kikapu chako kiko wazi', 'item' => 'Bidhaa',
        'qty' => 'Idadi', 'price' => 'Bei', 'delivery_address' => 'Anwani ya Kufikishiwa', 'enter_address' => 'Andika mahali unapoishi...',
        'payment_method' => 'Chagua Njia ya Malipo', 'phone_number' => 'Namba ya Simu ya Malipo',
        'mpesa' => 'M-Pesa', 'tigo' => 'Tigo Pesa', 'airtel' => 'Airtel Money', 'halotel' => 'Halopesa',
        'confirm_pay' => 'Thibitisha na Lipa Sasa', 'secure_checkout' => 'Malipo Salama (Sandbox)',

        // Order Success
        'order_placed' => 'Oda Imepokelewa Kikamilifu!', 'order_id' => 'Namba ya Oda', 'payment_prompt_sent' => 'Ujumbe wa Malipo Tumetuma!',
        'check_phone' => 'Tafadhali kagua simu yako na uweke PIN ili kukamilisha malipo.',
        'verifying_pin' => 'Tunaandikisha PIN yako...', 'pin_entered' => 'PIN Imewekwa!', 'validating_tx' => 'Tunahakiki muamala...',
        'tx_success' => 'Malipo Yamefanikiwa!', 'tx_failed' => 'Malipo Yamefeli!', 'redirecting' => 'Tunakuhamisha...',

        // My Orders
        'order_date' => 'Tarehe', 'order_status' => 'Hali', 'order_action' => 'Hatua', 'view' => 'Angalia',
        'no_orders' => "Bado hujaweka oda yoyote.", 'order_details' => 'Maelezo ya Oda',
        'status_pending' => 'Inasubiri', 'status_paid' => 'Imelipwa', 'status_failed' => 'Imefeli', 'status_completed' => 'Imekamilika',
        'status_processing' => 'Inashughulikiwa', 'status_cancelled' => 'Imeghairiwa'
    ]
];

// Determine current language
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'] === 'sw' ? 'sw' : 'en';
}

$lang = $_SESSION['lang'] ?? 'en';
$txt = $translations[$lang];

/**
 * Helper function for dynamic translations with placeholders
 */
function lang_replace($key, $placeholders = []) {
    global $txt;
    $string = $txt[$key] ?? $key;
    foreach ($placeholders as $k => $v) {
        $string = str_replace('{' . $k . '}', $v, $string);
    }
    return $string;
}
