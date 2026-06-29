<?php
include 'config.php';

// Security Check: Sirf approved vendors ke liye
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Restaurant ID nikalna
$resto_query = mysqli_query($conn, "SELECT id FROM restaurants WHERE user_id='$user_id' AND status='approved'");
if (mysqli_num_rows($resto_query) == 0) {
    header("Location: vendor_dashboard.php");
    exit();
}
$resto = mysqli_fetch_assoc($resto_query);
$restaurant_id = $resto['id'];


// --- 1. AJAX ENDPOINT (Background Check) ---
// Agar JavaScript background me check karegi toh yeh block execute hoga
if (isset($_GET['check_new_orders'])) {
    $count_query = mysqli_query($conn, "SELECT COUNT(id) as total FROM orders WHERE restaurant_id='$restaurant_id' AND status='pending'");
    $count_data = mysqli_fetch_assoc($count_query);
    echo $count_data['total'];
    exit(); // Yahan script ruk jayegi taake baaki HTML render na ho AJAX me
}


// --- 2. HANDLE STATUS UPDATE ---
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    mysqli_query($conn, "UPDATE orders SET status='$new_status' WHERE id='$order_id' AND restaurant_id='$restaurant_id'");
}

// --- 3. FETCH ALL ORDERS ---
$orders_query = mysqli_query($conn, "SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.restaurant_id = '$restaurant_id' ORDER BY o.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineHub - Manage Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

    <audio id="notification-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-500.wav" preload="auto"></audio>

    <?php include 'header.php'; ?>

    <div class="container mx-auto mt-8 px-4 max-w-6xl">
        
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-xl font-bold text-gray-800 mb-6">📋 Customer Bookings & Orders</h3>

            <?php if (mysqli_num_rows($orders_query) == 0): ?>
                <p class="text-gray-500 text-sm text-center py-8">Abhi tak koi order ya booking nahi aayi.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-medium">
                            <tr>
                                <th class="px-6 py-3 text-left">Order ID</th>
                                <th class="px-6 py-3 text-left">Table No.</th> <th class="px-6 py-3 text-left">Customer</th>
                                <th class="px-6 py-3 text-left">Customer</th>
                                <th class="px-6 py-3 text-left">Items Ordered</th>
                                <th class="px-6 py-3 text-left">Total Amount</th>
                                <th class="px-6 py-3 text-left">Time</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm text-gray-700">
                            <?php while ($order = mysqli_fetch_assoc($orders_query)): 
                                $order_id = $order['id'];
                                
                                // Is order ke items fetch karna
                                $items_query = mysqli_query($conn, "SELECT oi.*, mi.name as dish_name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id WHERE oi.order_id = '$order_id'");
                            ?>
                                <tr class="<?php echo ($order['status'] == 'pending') ? 'bg-orange-50/50' : ''; ?>">
                                    <td class="px-6 py-4 font-bold text-gray-900">#DH-<?php echo $order['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-orange-100 text-orange-800 rounded-md font-extrabold text-xs">
                                    🪑 <?php echo htmlspecialchars($order['table_number']); ?>
                                    </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold"><?php echo $order['customer_name']; ?></td>
                                    <td class="px-6 py-4">
                                        <ul class="list-disc list-inside text-xs space-y-0.5">
                                            <?php while ($item = mysqli_fetch_assoc($items_query)): ?>
                                                <li><?php echo $item['dish_name']; ?> <span class="text-gray-400">x<?php echo $item['quantity']; ?></span></li>
                                            <?php endwhile; ?>
                                        </ul>
                                    </td>
                                    <td class="px-6 py-4 font-extrabold text-green-700">Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td class="px-6 py-4 text-xs text-gray-500"><?php echo date('h:i A (d M)', strtotime($order['created_at'])); ?></td>
                                    <td class="px-6 py-4">
                                        <?php 
                                        $status_classes = [
                                            'pending' => 'bg-orange-100 text-orange-800',
                                            'preparing' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $class = $status_classes[$order['status']] ?? 'bg-gray-100';
                                        ?>
                                        <span class="px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider <?php echo $class; ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="vendor_orders.php" method="POST" class="flex items-center space-x-2">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="bg-white border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                                                <option value="pending" <?php if($order['status']=='pending') echo 'selected'; ?>>Pending</option>
                                                <option value="preparing" <?php if($order['status']=='preparing') echo 'selected'; ?>>Preparing</option>
                                                <option value="completed" <?php if($order['status']=='completed') echo 'selected'; ?>>Completed</option>
                                                <option value="cancelled" <?php if($order['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="bg-gray-800 text-white px-2 py-1 rounded text-xs font-bold hover:bg-orange-600 transition">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Pehle se mojood pending orders ka count store karne ke liye variable
        // Isko hum PHP se initialize kar rahe hain taake page load hote hi sound na baje
        let currentPendingCount = <?php 
            mysqli_data_seek($orders_query, 0); 
            $p_count = 0;
            while($o = mysqli_fetch_assoc($orders_query)) { if($o['status'] == 'pending') $p_count++; }
            echo $p_count;
        ?>;

        // Har 10 seconds baad database check karne ka function
        setInterval(function() {
            // AJAX Request sending to the same file with a special URL parameter
            fetch('vendor_orders.php?check_new_orders=1')
                .then(response => response.text())
                .then(data => {
                    let newPendingCount = parseInt(data.trim());

                    // Agar database me pending orders pehle se zyada ho gaye hain
                    if (newPendingCount > currentPendingCount) {
                        // Sound play karo
                        document.getElementById('notification-sound').play().catch(function(error) {
                            console.log("Browser ne sound block ki, user interaction lazmi hai:", error);
                        });
                        
                        // Page ko auto-refresh karo taake naya order table me nazar aaye
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Agar vendor ne order status change kiya ho aur count kam hua ho, toh locally sync rakhein
                        currentPendingCount = newPendingCount;
                    }
                });
        }, 10000); // 10000 milliseconds = 10 seconds
    </script>
</body>
</html>