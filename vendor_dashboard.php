<?php
include 'config.php';

// Security Check: Agar user logged in nahi hai ya vendor nahi hai, toh bhagao yahan se
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// 1. Handle Restaurant Profile Submission
if (isset($_POST['create_restaurant'])) {
    $r_name = mysqli_real_escape_string($conn, $_POST['restaurant_name']);
    $r_address = mysqli_real_escape_string($conn, $_POST['restaurant_address']);
    
    // Logo Upload Logic
    $logo_name = "";
    if (isset($_FILES['restaurant_logo']) && $_FILES['restaurant_logo']['error'] == 0) {
        $target_dir = "uploads/";
        // Agar folder nahi bana hua toh bana dega
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $logo_name = time() . "_" . basename($_FILES["restaurant_logo"]["name"]);
        $target_file = $target_dir . $logo_name;
        move_uploaded_file($_FILES["restaurant_logo"]["tmp_name"], $target_file);
    }

    $insert_query = "INSERT INTO restaurants (user_id, name, logo, address, status) VALUES ('$user_id', '$r_name', '$logo_name', '$r_address', 'pending')";
    if (mysqli_query($conn, $insert_query)) {
        $msg = "Restaurant ki details jama ho gayi hain! Admin approval ka intezar karein.";
        $msg_type = "success";
    } else {
        $msg = "Kuch ghalat ho gaya, dobara koshish karein.";
        $msg_type = "error";
    }
}

// 2. Check if Restaurant already exists for this user
$check_resto = mysqli_query($conn, "SELECT * FROM restaurants WHERE user_id='$user_id'");
$restaurant = mysqli_fetch_assoc($check_resto);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineHub - Vendor Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
<?php include 'header.php'; ?>

    <div class="container mx-auto mt-8 px-4 max-w-4xl">
        
        <?php if($msg != ""): ?>
            <div class="p-4 rounded-lg mb-6 text-sm font-semibold <?php echo ($msg_type == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php if (!$restaurant): ?>
            <div class="bg-white p-8 rounded-xl shadow-md">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Register Your Restaurant</h3>
                <p class="text-gray-500 mb-6">Apne hotel ya restaurant ki maloomat darj karein taake log aapka menu dekh saken.</p>
                
                <form action="vendor_dashboard.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Restaurant Name</label>
                        <input type="text" name="restaurant_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Restaurant Address</label>
                        <textarea name="restaurant_address" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Restaurant Logo</label>
                        <input type="file" name="restaurant_logo" accept="image/*" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                    </div>
                    <button type="submit" name="create_restaurant" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-md shadow transition">
                        Submit for Approval
                    </button>
                </form>
            </div>

        <?php elseif ($restaurant['status'] == 'pending'): ?>
            <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r-xl shadow-md text-center">
                <div class="text-amber-600 text-5xl mb-3">⏳</div>
                <h3 class="text-xl font-bold text-amber-800 mb-1">Approval Pending</h3>
                <p class="text-amber-700">Aapke restaurant <strong>"<?php echo $restaurant['name']; ?>"</strong> ki details mil gayi hain. Admin jald hi ise approve kar dega, jiske baad aap apna menu add kar sakenge.</p>
            </div>

        <?php elseif ($restaurant['status'] == 'approved'): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 col-span-3">
                    <?php if($restaurant['logo']): ?>
                        <img src="uploads/<?php echo $restaurant['logo']; ?>" class="w-16 h-16 rounded-full object-cover border-2 border-orange-500">
                    <?php endif; ?>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo $restaurant['name']; ?></h2>
                        <p class="text-gray-500 text-sm">📍 <?php echo $restaurant['address']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Management Panel</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="manage_menu.php" class="p-4 border border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition text-center block">
                        <span class="text-2xl">🍔</span>
                        <span class="block font-semibold text-gray-700 mt-2">Manage Menu & Categories</span>
                    </a>
                    <a href="vendor_orders.php" class="p-4 border border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition text-center block">
                        <span class="text-2xl">📋</span>
                        <span class="block font-semibold text-gray-700 mt-2">View Orders & Bookings</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>