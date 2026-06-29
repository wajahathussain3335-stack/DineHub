<?php
include 'config.php';

// Search logic handling
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM restaurants WHERE status='approved' AND (name LIKE '%$search_query%' OR address LIKE '%$search_query%') ORDER BY id DESC";
} else {
    $query = "SELECT * FROM restaurants WHERE status='approved' ORDER BY id DESC";
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineHub - Premium Culinary Experience</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50/50 text-slate-900 min-h-screen flex flex-col antialiased">
    <?php include 'header.php'; ?>

    <header class="relative bg-white border-b border-slate-100 py-16 sm:py-24 px-4 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-blue-50/30 via-transparent to-indigo-50/20 pointer-events-none"></div>
        <div class="max-w-4xl mx-auto text-center relative z-10">
            <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 mb-4 animate-fade-in">
                ✨ Discover & Dine Premium
            </span>
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight sm:leading-none mb-4">
                Flavors Delivered to <br class="hidden sm:inline"><span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Your Exact Table</span>
            </h1>
            <p class="text-slate-500 text-sm sm:text-lg max-w-2xl mx-auto mb-8 leading-relaxed">
                Apne pasandeeda restaurant ka digital menu browse karein, table select karein aur behtareen dining ka lutf uthayein.
            </p>

            <form action="index.php" method="GET" class="max-w-lg mx-auto flex items-center bg-white border border-slate-200 p-2 rounded-2xl shadow-xl shadow-slate-200/40 focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100 transition-all">
                <div class="flex-1 flex items-center pl-3">
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search restaurant name or area..." class="w-full bg-transparent border-0 outline-none text-sm text-slate-800 px-3 py-2 placeholder-slate-400">
                </div>
                <?php if(!empty($search_query)): ?>
                    <a href="index.php" class="text-xs text-slate-400 hover:text-slate-600 px-2">Clear</a>
                <?php endif; ?>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl transition-all shadow-sm">
                    Search
                </button>
            </form>
        </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-12 max-w-6xl flex-grow">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-8">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Available Premium Partners</h2>
                <p class="text-xs text-slate-400 mt-0.5">Handpicked top tier restaurants just for you</p>
            </div>
            <?php if(!empty($search_query)): ?>
                <span class="text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-lg font-medium">
                    Showing results for "<?php echo htmlspecialchars($search_query); ?>"
                </span>
            <?php endif; ?>
        </div>

        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-100 shadow-sm max-w-md mx-auto">
                <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center text-xl mx-auto mb-4">🔍</div>
                <h3 class="text-base font-bold text-slate-800 mb-1">No Restaurants Found</h3>
                <p class="text-slate-400 text-xs px-4">Aapki search ke mutabiq koi hotel nahi mila. Please spellings check karein ya naya search karein.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col justify-between group">
                        
                        <div class="p-5 sm:p-6 flex items-start space-x-4">
                            <?php if (!empty($row['logo'])): ?>
                                <img src="uploads/<?php echo $row['logo']; ?>" class="w-16 h-16 rounded-2xl object-cover border border-slate-100 shadow-sm flex-shrink-0">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-600 font-bold rounded-2xl flex items-center justify-center text-sm flex-shrink-0 border border-blue-100/50">Dine</div>
                            <?php endif; ?>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-base sm:text-lg font-bold text-slate-800 group-hover:text-blue-600 transition-colors truncate">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </h3>
                                <p class="text-slate-400 text-xs mt-1 leading-relaxed line-clamp-2">
                                    📍 <?php echo htmlspecialchars($row['address']); ?>
                                </p>
                                <span class="inline-flex items-center gap-1 mt-3 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold tracking-wide uppercase rounded-md">
                                    <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span> Instant Dine-In
                                </span>
                            </div>
                        </div>

                        <div class="px-5 sm:p-6 pb-5 sm:pb-6 pt-0">
                            <a href="restaurant_menu.php?id=<?php echo $row['id']; ?>" class="block text-center w-full bg-slate-900 hover:bg-blue-600 text-white font-bold text-xs sm:text-sm py-3 rounded-xl shadow-sm transition-all duration-200">
                                Explore Digital Menu
                            </a>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-white text-slate-400 py-6 text-center text-xs border-t border-slate-100">
        <p>&copy; 2026 DineHub Luxury Platform. Built for Premium Presentation.</p>
    </footer>

</body>
</html>