<?php
include 'config.php';

// Security Check: Sirf logged-in vendors jinka restaurant approved hai wahi aa sakte hain
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Restaurant ki ID nikalna
$resto_query = mysqli_query($conn, "SELECT * FROM restaurants WHERE user_id='$user_id' AND status='approved'");
if (mysqli_num_rows($resto_query) == 0) {
    // Agar restaurant approved nahi hai toh dashboard par wapas bhejo
    header("Location: vendor_dashboard.php");
    exit();
}
$restaurant = mysqli_fetch_assoc($resto_query);
$restaurant_id = $restaurant['id'];

$msg = "";
$msg_type = "";

// 1. HANDLE ADD CATEGORY
if (isset($_POST['add_category'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    if (!empty($cat_name)) {
        $insert_cat = "INSERT INTO categories (restaurant_id, name) VALUES ('$restaurant_id', '$cat_name')";
        if (mysqli_query($conn, $insert_cat)) {
            $msg = "Category kamyabi se add ho gayi!";
            $msg_type = "success";
        }
    }
}

// 2. HANDLE ADD MENU ITEM
if (isset($_POST['add_item'])) {
    $category_id = (int)$_POST['category_id'];
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Dish Image Upload Logic
    $item_image = "";
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        $target_dir = "uploads/";
        $item_image = time() . "_" . basename($_FILES["item_image"]["name"]);
        move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_dir . $item_image);
    }

    if (!empty($category_id) && !empty($item_name) && !empty($price)) {
        $insert_item = "INSERT INTO menu_items (category_id, name, price, image, description) VALUES ('$category_id', '$item_name', '$price', '$item_image', '$description')";
        if (mysqli_query($conn, $insert_item)) {
            $msg = "Nayi dish/item menu me add ho gayi hai!";
            $msg_type = "success";
        } else {
            $msg = "Item add karne me koi masla aaya.";
            $msg_type = "error";
        }
    }
}

// Fetch categories for forms and listing
$cat_query = mysqli_query($conn, "SELECT * FROM categories WHERE restaurant_id='$restaurant_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineHub - Manage Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include 'header.php'; ?>
    <div class="container mx-auto mt-8 px-4 max-w-6xl">
        
        <?php if($msg != ""): ?>
            <div class="p-4 rounded-lg mb-6 text-sm font-semibold <?php echo ($msg_type == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <div class="bg-white p-6 rounded-xl shadow-md h-fit">
                <h3 class="text-lg font-bold text-gray-800 mb-4">📁 Add Food Category</h3>
                <form action="manage_menu.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" name="category_name" placeholder="e.g., Fast Food, Desi, Drinks" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <button type="submit" name="add_category" class="w-full bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold py-2 px-4 rounded-md shadow transition">
                        Add Category
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md md:col-span-2">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🍔 Add New Dish / Item</h3>
                <form action="manage_menu.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Select Category</label>
                        <select name="category_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                            <option value="">-- Choose Category --</option>
                            <?php 
                            // Reset pointer to loop categories again
                            mysqli_data_seek($cat_query, 0);
                            while($cat = mysqli_fetch_assoc($cat_query)): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dish Name</label>
                        <input type="text" name="item_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price (PKR)</label>
                        <input type="number" name="price" step="0.01" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dish Image</label>
                        <input type="file" name="item_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description (Short details)</label>
                        <textarea name="description" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"></textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" name="add_item" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded-md shadow transition">
                            Save Dish to Menu 🚀
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-xl font-bold text-gray-800 mb-6">📋 Current Restaurant Menu</h3>

            <?php
            mysqli_data_seek($cat_query, 0);
            if(mysqli_num_rows($cat_query) == 0):
            ?>
                <p class="text-gray-500 text-sm">Abhi tak aapne koi category ya items add nahi kiye.</p>
            <?php else: ?>
                <?php while($cat = mysqli_fetch_assoc($cat_query)): 
                    $cat_id = $cat['id'];
                    // Is specific category ke items fetch karna
                    $items_query = mysqli_query($conn, "SELECT * FROM menu_items WHERE category_id='$cat_id' ORDER BY id DESC");
                ?>
                    <div class="mb-8 border-b border-gray-100 pb-6 last:border-0">
                        <h4 class="text-lg font-bold text-orange-600 bg-orange-50 px-4 py-2 rounded-md mb-4 inline-block">
                            📂 <?php echo $cat['name']; ?>
                        </h4>

                        <?php if(mysqli_num_rows($items_query) == 0): ?>
                            <p class="text-gray-400 text-xs italic pl-4">Is category me koi dish add nahi hui.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pl-2">
                                <?php while($item = mysqli_fetch_assoc($items_query)): ?>
                                    <div class="border border-gray-100 rounded-lg p-3 flex space-x-3 bg-gray-50 shadow-sm">
                                        <?php if(!empty($item['image'])): ?>
                                            <img src="uploads/<?php echo $item['image']; ?>" class="w-20 h-20 rounded-lg object-cover border">
                                        <?php else: ?>
                                            <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center text-xs text-gray-400">No Image</div>
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <h5 class="font-bold text-gray-800 text-sm"><?php echo $item['name']; ?></h5>
                                            <p class="text-xs text-gray-500 line-clamp-2 mt-0.5"><?php echo $item['description']; ?></p>
                                            <div class="text-sm font-extrabold text-green-700 mt-2">Rs. <?php echo number_format($item['price'], 2); ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>