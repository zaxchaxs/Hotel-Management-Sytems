<?php
require_once 'includes/config.php';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-6 text-center">About Our Hotel</h1>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="md:flex">
            <div class="md:w-1/2">
                <img src="assets/images/hotel-exterior.jpg" alt="Hotel Exterior" class="w-full h-full object-cover">
            </div>
            <div class="p-6 md:w-1/2">
                <h2 class="text-2xl font-semibold mb-4">Welcome to Luxury Stays</h2>
                <p class="text-gray-700 mb-4">
                    Established in 2010, Luxury Stays has been providing exceptional hospitality to guests from around the world. 
                    Our commitment to excellence, attention to detail, and personalized service sets us apart in the hospitality industry.
                </p>
                <p class="text-gray-700 mb-4">
                    Located in the heart of the city, our hotel offers easy access to major attractions, shopping centers, and business districts.
                    Whether you're traveling for business or pleasure, we provide the perfect accommodations to meet your needs.
                </p>
                <p class="text-gray-700">
                    We pride ourselves on creating a warm, welcoming environment where every guest feels at home,
                    coupled with the luxury and amenities of a world-class hotel.
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
        <h2 class="text-3xl font-bold text-center mb-8">Our Facilities</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Swimming Pool</h3>
                <p class="text-gray-600 text-sm">Enjoy our outdoor pool with temperature control for year-round swimming</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Fitness Center</h3>
                <p class="text-gray-600 text-sm">State-of-the-art equipment and professional trainers available</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Spa & Wellness</h3>
                <p class="text-gray-600 text-sm">Rejuvenate with our range of spa treatments and wellness services</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Business Center</h3>
                <p class="text-gray-600 text-sm">Modern facilities for meetings and conferences with technical support</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Restaurant & Bar</h3>
                <p class="text-gray-600 text-sm">Multiple dining options serving gourmet cuisine and specialty drinks</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Concierge Services</h3>
                <p class="text-gray-600 text-sm">Personalized assistance for travel arrangements and local recommendations</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Laundry Services</h3>
                <p class="text-gray-600 text-sm">Same-day laundry and dry cleaning for guests</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold mb-2">Free Wi-Fi</h3>
                <p class="text-gray-600 text-sm">High-speed internet access throughout the property</p>
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