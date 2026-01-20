<?php
require('includes/db.php');
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// PAWAPAY CONFIGURATION
$pawapay_config = include('payments/config.php');
require_once('payments/PawaPayHandler.php');
$pawapay = new PawaPayHandler($pawapay_config);

function log_msg($message) {
    $logFile = 'logs/api.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capture Data
    $address = $_POST['address'] ?? '';
    $order_email = $_POST['order_email'] ?? $_SESSION['user_email'] ?? '';
    $cart_json = $_POST['cart_data'] ?? '[]';
    $cart_items = json_decode($cart_json, true);
    
    if (empty($cart_items)) {
        header("Location: index.php");
        exit();
    }
    
    $total_amount = 0;
    $valid_items = [];
    
    // Calculate Total and Validate Items
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();
        if ($product) {
            $total_amount += $product['price'] * $item['quantity'];
            $valid_items[] = $item;
        }
    }

    // --- AZAMPAY ORDER PLACEMENT ---
    if (isset($_POST['final_pay']) && $total_amount > 0) {
        $selected_method = $_POST['selected_method'] ?? 'Manual';
        $phone_number = $_POST['phone_number'] ?? '';

        try {
            $pdo->beginTransaction();
            // Initial status 'pending' until callback confirms payment
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_email, total_amount, status, address, payment_method, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?, ?)");
            $tx_ref = 'ORD-' . time() . '-' . $_SESSION['user_id'];
            $stmt->execute([$_SESSION['user_id'], $order_email, $total_amount, $address, $selected_method, $tx_ref]);
            $order_id = $pdo->lastInsertId();
            
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($valid_items as $item) {
                $stmtPrice = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmtPrice->execute([$item['id']]);
                $product = $stmtPrice->fetch();
                if ($product) {
                    $stmtItem->execute([$order_id, $item['id'], $item['quantity'], $product['price']]);
                }
            }
            $pdo->commit();

            // TRIGGER PAWAPAY
            if ($selected_method !== 'Manual') {
                $deposit_id = generate_uuid();
                
                // Sanitize phone number: ensure starts with 255, remove leading + or 0
                $phone_number = preg_replace('/^\+/', '', $phone_number);
                if (str_starts_with($phone_number, '0')) {
                    $phone_number = '255' . substr($phone_number, 1);
                } elseif (!str_starts_with($phone_number, '255')) {
                    $phone_number = '255' . $phone_number;
                }

                // Map providers
                $provider_map = [
                    'mpesa' => 'VODACOM_TZA',
                    'tigo' => 'TIGO_TZA',
                    'airtel' => 'AIRTEL_TZA',
                    'halotel' => 'HALOTEL_TZA'
                ];
                
                $provider = $provider_map[$selected_method] ?? 'VODACOM_TZA';

                $pawa_response = $pawapay->deposit([
                    'depositId' => $deposit_id,
                    'amount' => $total_amount,
                    'phoneNumber' => $phone_number,
                    'provider' => $provider
                ]);

                log_msg("Deposit Initiated - ID: $deposit_id, Phone: $phone_number, Provider: $provider, Amount: $total_amount");

                if ($pawa_response['status'] == 200 || $pawa_response['status'] == 202) {
                    // Success triggering deposit request
                    log_msg("Deposit Request Successful - ID: $deposit_id");
                    $stmtUpdate = $pdo->prepare("UPDATE orders SET transaction_id = ? WHERE id = ?");
                    $stmtUpdate->execute([$deposit_id, $order_id]);

                    header("Location: order_success.php?id=$order_id&msg=stk_sent&deposit_id=$deposit_id");
                    exit();
                } else {
                    $error_msg = $pawa_response['response']['message'] ?? 'Unknown error';
                    if (isset($pawa_response['response']['errors'])) {
                        $error_msg .= " : " . json_encode($pawa_response['response']['errors']);
                    }
                    $error = "PawaPay Error: " . $error_msg;
                    log_msg("Deposit Request Failed - ID: $deposit_id. Error: $error");
                }
            } else {
                // Fallback for Manual
                header("Location: order_success.php?id=$order_id");
                exit();
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Order placement failed: " . $e->getMessage();
            log_msg("Order Processing Exception: " . $e->getMessage());
        }
    } else {
        if (isset($_POST['final_pay'])) {
            $error = "Error: Total amount is 0 or invalid items.";
        }
    }
} else {
    // GET request (render page)
}

include('includes/header.php');
?>

<style>
    .payment-wrapper { max-width: 600px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .payment-total { text-align: center; margin-bottom: 40px; }
    .payment-total h2 { font-size: 1.1rem; color: #777; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
    .payment-total .amount { font-size: 2.5rem; font-weight: 800; color: #0A1A2F; }
    
    .payment-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
    .payment-card { 
        position: relative; padding: 20px; border: 2px solid #f0f0f0; border-radius: 15px; cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; align-items: center; gap: 10px;
        background: #fafafa;
    }
    .payment-card:hover { transform: translateY(-5px); border-color: #00d084; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    .payment-card.active { border-color: #00d084; background: #f0fff9; box-shadow: 0 0 0 4px rgba(0,208,132,0.1); }
    .payment-card i { font-size: 2rem; color: #555; }
    .payment-card span { font-weight: 600; font-size: 0.9rem; color: #333; }
    
    /* Brand specific colors */
    .payment-card.mpesa.active i { color: #e11c2a; } /* Vodacom Red */
    .payment-card.tigo.active i { color: #0033a0; }  /* Tigo Blue */
    .payment-card.airtel.active i { color: #ff0000; } /* Airtel Red */
    .payment-card.halotel.active i { color: #ff8200; } /* Halotel Orange */
    
    .btn-pay { 
        width: 100%; padding: 18px; border-radius: 12px; font-size: 1.1rem; font-weight: 700; background: #00d084; color: #fff; 
        border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 8px 15px rgba(0,208,132,0.3);
    }
    .btn-pay:hover { background: #00b372; transform: scale(1.02); }
    
    .status-badge { padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 600; }
</style>

<div class="container">
    <div class="payment-wrapper">
        <div class="payment-total">
            <h2><?php echo $txt['total_amount']; ?></h2>
            <div class="amount">TZS <?php echo number_format($total_amount, 2); ?></div>
        </div>
        
        <div class="status-badge" style="background:#e8f0fe; color:#1a73e8; text-align:center;">
            <i class="fas fa-lock"></i> <?php echo $txt['secure_checkout']; ?>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger" style="background:#fff5f5; color:#c53030; padding:15px; border-radius:10px; border:1px solid #feb2b2; margin-bottom:25px; font-size:0.9rem;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="API.php" id="payment-form">
            <input type="hidden" name="address" value="<?php echo htmlspecialchars($address); ?>">
            <input type="hidden" name="order_email" value="<?php echo htmlspecialchars($order_email); ?>">
            <input type="hidden" name="cart_data" value="<?php echo htmlspecialchars($cart_json); ?>">
            <input type="hidden" name="selected_method" id="selected-method" value="mpesa">
            
            <div id="phone-input-group" style="margin-bottom: 25px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#555;"><?php echo $txt['phone_number']; ?></label>
                <input type="text" name="phone_number" id="phone_number" placeholder="e.g. 2557XXXXXXXX" required style="width:100%; padding:15px; border:1px solid #ddd; border-radius:12px; font-size:1rem;">
                <small style="color:#777; margin-top:5px; display:block;"><?php echo ($lang == 'sw' ? 'Weka namba ili upate ujumbe wa malipo' : 'Enter number to receive payment prompt'); ?></small>
            </div>

            <h3 style="margin-bottom:20px; text-align:center; color:#555; font-size:1rem; font-weight:600;"><?php echo $txt['payment_method']; ?></h3>
            
            <div class="payment-methods">
                <div class="payment-card mpesa active" onclick="selectMethod('mpesa', this)">
                    <i class="fas fa-mobile-alt"></i>
                    <span>M-Pesa</span>
                    <small style="font-size:0.7rem; color:#999;">Vodacom</small>
                </div>
                <div class="payment-card tigo" onclick="selectMethod('tigo', this)">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Tigo Pesa</span>
                    <small style="font-size:0.7rem; color:#999;">Tigo</small>
                </div>
                <div class="payment-card airtel" onclick="selectMethod('airtel', this)">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Airtel Money</span>
                    <small style="font-size:0.7rem; color:#999;">Airtel</small>
                </div>
                <div class="payment-card halotel" onclick="selectMethod('halotel', this)">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Halopesa</span>
                    <small style="font-size:0.7rem; color:#999;">Halotel</small>
                </div>
            </div>
            
            <button type="submit" name="final_pay" class="btn btn-pay">
                <?php echo $txt['confirm_pay']; ?>
            </button>
        </form>
    </div>
</div>

<script>
function selectMethod(method, element) {
    document.getElementById('selected-method').value = method;
    document.querySelectorAll('.payment-card').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    
    const phoneGroup = document.getElementById('phone-input-group');
    if (method === 'card') {
        phoneGroup.style.display = 'none';
        document.getElementById('phone_number').required = false;
    } else {
        phoneGroup.style.display = 'block';
        document.getElementById('phone_number').required = true;
    }
}
</script>

<?php include('includes/footer.php'); ?>