<?php
include 'config.php';

// Check karna ke URL me restaurant ID hai ya nahi
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$restaurant_id = (int)$_GET['id'];

// Restaurant ki details nikalna
$resto_query = mysqli_query($conn, "SELECT * FROM restaurants WHERE id='$restaurant_id' AND status='approved'");
if (mysqli_num_rows($resto_query) == 0) {
    header("Location: index.php");
    exit();
}
$restaurant = mysqli_fetch_assoc($resto_query);

$msg = "";
$msg_type = "";

// ORDER SUBMISSION LOGIC (Jab form submit ho)
// --- Is block ko restaurant_menu.php ke top PHP section me replace karein ---
if (isset($_POST['place_order'])) {
    if (!isset($_SESSION['user_id'])) {
        $msg = "Order book karne ke liye pehle login karein!";
        $msg_type = "error";
    } else {
        $user_id = $_SESSION['user_id'];
        $cart_data = json_decode($_POST['cart_json'], true);
        $total_amount = (float)$_POST['total_amount'];
        // Table number capture karna
        $table_number = mysqli_real_escape_string($conn, $_POST['table_number']);

        if (!empty($cart_data) && !empty($table_number)) {
            // Updated Query with table_number
            $order_query = "INSERT INTO orders (user_id, restaurant_id, table_number, total_amount, status) VALUES ('$user_id', '$restaurant_id', '$table_number', '$total_amount', 'pending')";
            if (mysqli_query($conn, $order_query)) {
                $order_id = mysqli_insert_id($conn);

                foreach ($cart_data as $item) {
                    $item_id = (int)$item['id'];
                    $qty = (int)$item['qty'];
                    $price = (float)$item['price'];

                    mysqli_query($conn, "INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES ('$order_id', '$item_id', '$qty', '$price')");
                }
                $msg = "Zabardast! Aapka order Table " . htmlspecialchars($table_number) . " ke liye book ho gaya hai.";
                $msg_type = "success";
            }
        } else {
            $msg = "Cart khali hai ya Table Number darj nahi kiya!";
            $msg_type = "error";
        }
    }
}

// Fetch categories for this restaurant
$categories = mysqli_query($conn, "SELECT * FROM categories WHERE restaurant_id='$restaurant_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $restaurant['name']; ?> - Menu | DineHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen">

    <?php include 'header.php'; ?>
    <div class="container mx-auto mt-8 px-4 max-w-6xl">
        
        <?php if($msg != ""): ?>
            <div class="p-4 rounded-lg mb-6 text-sm font-semibold <?php echo ($msg_type == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6">
    <div class="relative flex items-center">
        <span class="absolute left-3 text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input type="text" id="menuSearchInput" onkeyup="filterMenuItems()" placeholder="Search dishes instantly (e.g., Pizza, Burger, Drink)..." 
               class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none text-sm focus:border-blue-500 focus:bg-white transition-all">
    </div>
</div>

<script>
function filterMenuItems() {
    let input = document.getElementById('menuSearchInput').value.toLowerCase();
    
    // Har ek dish card ko select karna
    let dishCards = document.querySelectorAll('.grid > .border.border-gray-100'); 
    
    // Category blocks ko select karna taake agar saari dishes chup jayein toh category bhi chup jaye
    let categoryBlocks = document.querySelectorAll('.lg\:col-span-2 > .bg-white');

    categoryBlocks.forEach(block => {
        let itemsInBlock = block.querySelectorAll('.border.border-gray-100');
        let visibleCount = 0;

        itemsInBlock.forEach(item => {
            // Item ka naam nikalna
            let dishName = item.querySelector('h4').innerText.toLowerCase();
            
            if (dishName.includes(input)) {
                item.style.setProperty('display', 'flex', 'important');
                visibleCount++;
            } else {
                item.style.setProperty('display', 'none', 'important');
            }
        });

        // Agar is category me koi dish filter match nahi hui toh poori category ki heading chupa do
        if (visibleCount === 0 && input !== '') {
            block.style.display = 'none';
        } else {
            block.style.display = 'block';
        }
    });
}
</script>
            <div class="lg:col-span-2 space-y-8">
                <?php if (mysqli_num_rows($categories) == 0): ?>
                    <p class="text-gray-500">Is restaurant ne abhi tak koi menu add nahi kiya.</p>
                <?php else: ?>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): 
                        $cat_id = $cat['id'];
                        $items = mysqli_query($conn, "SELECT * FROM menu_items WHERE category_id='$cat_id'");
                        if (mysqli_num_rows($items) > 0):
                    ?>
                        <div class="bg-white p-6 rounded-xl shadow-sm">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">📁 <?php echo $cat['name']; ?></h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                                    <div class="border border-gray-100 rounded-lg p-3 flex space-x-3 bg-gray-50 items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <?php if($item['image']): ?>
                                                <img src="uploads/<?php echo $item['image']; ?>" class="w-16 h-16 rounded-lg object-cover">
                                            <?php endif; ?>
                                            <div>
                                                <h4 class="font-bold text-gray-800 text-sm"><?php echo $item['name']; ?></h4>
                                                <p class="text-green-700 font-extrabold text-sm mt-1">Rs. <?php echo number_format($item['price'], 2); ?></p>
                                            </div>
                                        </div>
                                        <button onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>', <?php echo $item['price']; ?>)" class="bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs px-3 py-2 rounded shadow transition">
                                            Add +
                                        </button>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endif; endwhile; ?>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md h-fit sticky top-24">
                <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2 flex items-center justify-between">
                    <span>🛒 Your Booking</span>
                    <span id="cart-count" class="bg-orange-100 text-orange-600 text-xs font-bold px-2.5 py-0.5 rounded-full">0</span>
                </h3>

                <div id="cart-items" class="space-y-3 max-h-60 overflow-y-auto mb-4 text-sm text-gray-600">
                    <p class="text-gray-400 italic text-center py-4">Cart khali hai. Items add karein!</p>
                </div>

                <div class="border-t pt-4 flex justify-between items-center font-bold text-gray-800 text-lg mb-6">
                    <span>Total Amount:</span>
                    <span class="text-orange-600">Rs. <span id="cart-total">0.00</span></span>
                </div>

                <form action="" method="POST" onsubmit="return prepareCheckout()">
                    <input type="hidden" name="cart_json" id="cart_json">
                    <input type="hidden" name="total_amount" id="total_amount_input">
                    <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Your Table Number / Name</label>
                    <input type="text" name="table_number" placeholder="e.g., Table 5, VIP-2" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm">
                    </div>
                    <button type="submit" name="place_order" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow transition block text-center">
                        Confirm Menu Booking 🚀
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        let cart = [];

        function addToCart(id, name, price) {
            let found = cart.find(item => item.id === id);
            if (found) {
                found.qty++;
            } else {
                cart.push({ id: id, name: name, price: price, qty: 1 });
            }
            updateCartUI();
        }

        function updateQty(id, amt) {
            let found = cart.find(item => item.id === id);
            if (found) {
                found.qty += amt;
                if (found.qty <= 0) {
                    cart = cart.filter(item => item.id !== id);
                }
            }
            updateCartUI();
        }

        function updateCartUI() {
            let cartItemsDiv = document.getElementById('cart-items');
            let cartTotalSpan = document.getElementById('cart-total');
            let cartCountSpan = document.getElementById('cart-count');
            
            if (cart.length === 0) {
                cartItemsDiv.innerHTML = '<p class="text-gray-400 italic text-center py-4">Cart khali hai. Items add karein!</p>';
                cartTotalSpan.innerText = '0.00';
                cartCountSpan.innerText = '0';
                return;
            }

            let html = '';
            let total = 0;
            let count = 0;

            cart.forEach(item => {
                let subtotal = item.price * item.qty;
                total += subtotal;
                count += item.qty;
                html += `
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <span class="font-semibold text-gray-800">${item.name}</span>
                            <span class="block text-xs text-gray-400">Rs. ${item.price} x ${item.qty}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQty(${item.id}, -1)" class="bg-gray-200 text-gray-800 px-2 rounded font-bold text-xs hover:bg-gray-300">-</button>
                            <span class="font-bold text-sm">${item.qty}</span>
                            <button onclick="updateQty(${item.id}, 1)" class="bg-gray-200 text-gray-800 px-2 rounded font-bold text-xs hover:bg-gray-300">+</button>
                        </div>
                    </div>
                `;
            });

            cartItemsDiv.innerHTML = html;
            cartTotalSpan.innerText = total.toLocaleString(undefined, {minimumFractionDigits: 2});
            cartCountSpan.innerText = count;
        }

        function prepareCheckout() {
            if (cart.length === 0) {
                alert('Pehle cart me kuch add karein!');
                return false;
            }
            // JavaScript array ko string bana kar hidden input fields me save karna taake PHP ko mil sake
            document.getElementById('cart_json').value = JSON.stringify(cart);
            let total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            document.getElementById('total_amount_input').value = total;
            return true;
        }
    </script>
</body>
</html>