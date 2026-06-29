<?php
// Confirm karna ke session active hai, agar config.php pehle include nahi hui toh session start kar dega
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50 px-4 sm:px-8 py-4 flex justify-between items-center transition-all">
    <a href="index.php" class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-blue-700 to-indigo-600 bg-clip-text text-transparent">
        Dine<span class="text-slate-900">Hub</span>
    </a>
    
    <div class="flex items-center space-x-3 sm:space-x-4">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="hidden sm:block text-right">
                <p class="text-xs text-slate-400 font-medium">Welcome back</p>
                <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></p>
            </div>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'vendor'): ?>
                <a href="vendor_dashboard.php" class="text-xs sm:text-sm font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl transition-all">Vendor Panel</a>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin_dashboard.php" class="text-xs sm:text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-2 rounded-xl transition-all">Admin Panel</a>
            <?php endif; ?>
            
            <a href="logout.php" class="border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-sm">Logout</a>
        <?php else: ?>
            <a href="login.php" class="text-xs sm:text-sm font-bold text-slate-600 hover:text-blue-600 transition-all px-2">Log In</a>
            <a href="register.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-md shadow-blue-500/10">Create Account</a>
        <?php endif; ?>
    </div>
</nav>