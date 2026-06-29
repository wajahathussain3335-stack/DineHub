<?php
include 'config.php';

// Security Check: Sirf Admin ke liye access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";
$msg_type = "";

// --- HANDLE APPROVAL ACTION ---
if (isset($_GET['approve_id'])) {
    $approve_id = (int)$_GET['approve_id'];
    $update_query = "UPDATE restaurants SET status='approved' WHERE id=$approve_id";
    if (mysqli_query($conn, $update_query)) {
        $msg = "Restaurant kamyabi se approve ho gaya hai!";
        $msg_type = "success";
    }
}

// --- HANDLE DELETE USER ACTION (Sellers/Buyers) ---
if (isset($_GET['delete_user_id'])) {
    $delete_id = (int)$_GET['delete_user_id'];
    
    // Pehle check karein ke kahin admin khud ko delete na kar raha ho safety ke liye
    if ($delete_id === (int)$_SESSION['user_id']) {
        $msg = "Aap khud ka account delete nahi kar sakte!";
        $msg_type = "error";
    } else {
        // Agar user vendor hai toh uske restaurant ko bhi pehle saaf karna behtar hai
        mysqli_query($conn, "DELETE FROM restaurants WHERE user_id=$delete_id");
        
        // Final User Delete Query
        $delete_query = "DELETE FROM users WHERE id=$delete_id";
        if (mysqli_query($conn, $delete_query)) {
            $msg = "User account aur us se mutalqa data kamyabi se delete ho gaya!";
            $msg_type = "success";
        } else {
            $msg = "User delete karne me koi rukawat aayi.";
            $msg_type = "error";
        }
    }
}

// Fetch Pending Restaurants
$pending_result = mysqli_query($conn, "SELECT r.*, u.name as owner_name FROM restaurants r JOIN users u ON r.user_id = u.id WHERE r.status='pending' ORDER BY r.created_at DESC");

// Fetch All Users (Sellers and Buyers) excluding Admin
$users_result = mysqli_query($conn, "SELECT id, name, email, role, created_at FROM users WHERE role != 'admin' ORDER BY role DESC, id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineHub Central Admin - Management Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen antialiased">

    <?php include 'header.php'; ?>

    <div class="container mx-auto mt-8 px-4 max-w-6xl pb-16">
        
        <div class="mb-8">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Central Control Panel</h1>
            <p class="text-sm text-slate-500 mt-0.5">Platform ke tamand vendors, customers aur approvals ko yahan se control karein.</p>
        </div>
        
        <?php if($msg != ""): ?>
            <div class="p-4 rounded-xl mb-6 text-sm font-semibold shadow-sm transition-all <?php echo ($msg_type == 'success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 'bg-rose-50 text-rose-800 border border-rose-100'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Pending Approval Requests
                </h3>
            </div>
            
            <?php if(mysqli_num_rows($pending_result) == 0): ?>
                <div class="p-6 text-center text-sm text-slate-400 italic">Koi nayi approval request pending nahi hai.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">Logo</th>
                                <th class="px-6 py-3 text-left">Restaurant Name</th>
                                <th class="px-6 py-3 text-left">Owner</th>
                                <th class="px-6 py-3 text-left">Address</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700 bg-white">
                            <?php while($row = mysqli_fetch_assoc($pending_result)): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <img src="uploads/<?php echo $row['logo']; ?>" class="w-10 h-10 rounded-xl object-cover border border-slate-100 shadow-sm">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-500"><?php echo htmlspecialchars($row['owner_name']); ?></td>
                                    <td class="px-6 py-4 max-w-xs truncate text-slate-500"><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="admin_dashboard.php?approve_id=<?php echo $row['id']; ?>" class="inline-flex bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shadow-sm">
                                            Approve ✅
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    🛡️ Platform Users Directory (Sellers & Buyers)
                </h3>
            </div>
            
            <?php if(mysqli_num_rows($users_result) == 0): ?>
                <div class="p-6 text-center text-sm text-slate-400 italic">Platform par koi users registered nahi hain.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">User Details</th>
                                <th class="px-6 py-3 text-left">Email Address</th>
                                <th class="px-6 py-3 text-left">Account Type</th>
                                <th class="px-6 py-3 text-left">Registered On</th>
                                <th class="px-6 py-3 text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700 bg-white">
                            <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($user['role'] == 'vendor'): ?>
                                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold uppercase tracking-wide">Seller / Vendor</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold uppercase tracking-wide">Buyer / Customer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400">
                                        <?php echo date('d M, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="admin_dashboard.php?delete_user_id=<?php echo $user['id']; ?>" 
                                           onclick="return confirm('Kya aap waqai is account aur is se mutalqa saara record mukammal delete karna chahte hain?')" 
                                           class="inline-flex bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/40 px-3 py-1.5 rounded-xl text-xs font-bold transition-all">
                                            Delete Account ❌
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>