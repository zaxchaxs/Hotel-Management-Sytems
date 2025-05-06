<?php
require_once 'includes/config.php';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-6 text-center">About Triton</h1>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="md:flex">
            <div class="md:w-1/2">
                <img src="assets/images/hotel2.png" alt="Hotel Exterior" class="w-full h-full object-cover">
            </div>
            <div class="p-6 md:w-1/2">
                <h2 class="text-2xl font-semibold mb-4">Welcome to Triton!</h2>
                <p class="text-gray-700 mb-4">
                    Didirikan pada tahun 2010, Triton hadir sebagai simbol keanggunan dan ketenangan dalam dunia perhotelan modern. 
                    Dengan dedikasi terhadap kualitas, perhatian pada setiap detail, dan pelayanan yang dipersonalisasi, 
                    kami berkomitmen untuk menghadirkan pengalaman menginap yang tak hanya nyaman, tetapi juga berkesan. 
                    Setiap tamu kami adalah prioritas utama, dan setiap pengalaman menginap adalah cerminan dari standar tinggi yang kami pegang teguh.
                </p>
                <p class="text-gray-700 mb-4">
                    Berlokasi strategis di jantung kota, Triton menawarkan kemudahan akses ke berbagai destinasi penting—mulai dari pusat bisnis, 
                    area perbelanjaan, hingga tempat wisata ikonik. Baik Anda bepergian untuk urusan bisnis maupun liburan, 
                    Triton menyediakan akomodasi yang dirancang dengan cermat untuk memenuhi kebutuhan dan gaya hidup Anda.
                </p>
                <p class="text-gray-700">
                    Kami percaya bahwa kenyamanan sejati lahir dari perpaduan suasana yang hangat, desain elegan, dan layanan profesional. 
                    Di Triton, Anda tidak hanya menginap—Anda merasakan ketenangan, kemewahan, dan kualitas yang dirancang untuk menciptakan pengalaman menginap yang istimewa.
                </p>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-center mb-2">Luxury Experience</h3>
                <p class="text-gray-600 text-center">
                    Enjoy premium amenities, spacious rooms, and exceptional service that creates an unforgettable stay.
                </p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-center mb-2">24/7 Service</h3>
                <p class="text-gray-600 text-center">
                    Our dedicated staff is available around the clock to ensure your comfort and address any needs.
                </p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-center mb-2">Fine Dining</h3>
                <p class="text-gray-600 text-center">
                    Savor exquisite cuisine at our on-site restaurants, featuring local and international flavors.
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-gray-100 rounded-lg p-8 mb-12">
        <h2 class="text-3xl font-bold text-center mb-8">Fasilitas yang Tersedia</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Kolam Renang</h3>
                <p class="text-gray-600 text-sm">Nikmati kolam renang outdoor kami dengan kontrol suhu untuk berenang sepanjang tahun</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Fitness Center</h3>
                <p class="text-gray-600 text-sm">Tersedia peralatan canggih dan pelatih profesional</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Spa & Kesehatan</h3>
                <p class="text-gray-600 text-sm">Remajakan dirimu dengan rangkaian perawatan spa dan layanan kesehatan kami</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Pusat Bisnis</h3>
                <p class="text-gray-600 text-sm">Fasilitas modern untuk pertemuan dan konferensi dengan dukungan teknis</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Restoran & Bar</h3>
                <p class="text-gray-600 text-sm">Beberapa pilihan bersantap yang menyajikan masakan gourmet dan minuman spesial</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Layanan Concierge</h3>
                <p class="text-gray-600 text-sm">Bantuan pribadi untuk pengaturan perjalanan dan rekomendasi lokal</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Layanan Laundry</h3>
                <p class="text-gray-600 text-sm">Laundry dan dry cleaning di hari yang sama untuk tamu</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Wi-Fi Gratis</h3>
                <p class="text-gray-600 text-sm">Akses internet berkecepatan tinggi di seluruh properti</p>
            </div>
        </div>
    </div>
    
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-center mb-8">Meet Our Team</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="assets/images/team-1.jpg" alt="Team Member" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg">Sarah Johnson</h3>
                    <p class="text-gray-600 text-sm">General Manager</p>
                    <p class="mt-2 text-sm">15+ years of experience in luxury hospitality management</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="assets/images/team-2.jpg" alt="Team Member" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg">David Chen</h3>
                    <p class="text-gray-600 text-sm">Executive Chef</p>
                    <p class="mt-2 text-sm">Award-winning culinary expert specializing in fusion cuisine</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="assets/images/team-3.jpg" alt="Team Member" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg">Maria Rodriguez</h3>
                    <p class="text-gray-600 text-sm">Guest Relations Manager</p>
                    <p class="mt-2 text-sm">Dedicated to ensuring exceptional guest experiences</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="assets/images/team-4.jpg" alt="Team Member" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg">James Wilson</h3>
                    <p class="text-gray-600 text-sm">Head of Housekeeping</p>
                    <p class="mt-2 text-sm">Ensures immaculate cleanliness and comfort across all rooms</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center">
        <h2 class="text-3xl font-bold mb-4">Experience Luxury Stays</h2>
        <p class="text-xl text-gray-600 mb-6">We look forward to welcoming you to our hotel.</p>
        <a href="rooms.php" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-8 rounded-lg">
            Browse Our Rooms
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>