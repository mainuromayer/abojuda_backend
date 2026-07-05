<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password')
            ]);
        }

        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        $posts = [
            // Buy & Sell | Category: 1
            [
                'user_id' => $user->id,
                'category_id' => $categories->where('name', 'Buy & Sell')->first()->id ?? 1,
                'photos' => ['./backend/assets/media/posts/mobile.png'],
                'title' => 'Samsung Galaxy 524 Ultra - Excellent Condition',
                'description' => 'Selling my Samsung Galaxy S24 Ultra. Used for 3 months only. No scratches. Comes with original box and accessories. All features working perfectly. Titanium Black, 256GB.',
                'price' => 1199,
                'condition' => ['Like New'],
                'province' => 'Toronto',
                'city' => 'Manitoba',
                'address' => '1450 Taylor Ave, Winnipeg, MB R3N IY6, Canada, Toronto, Manitoba, Scarborough, Ontario',
                'phone' => '+1-416-555-1234',
                'whatsapp' => '+1-416-555-1234',
                'email' => 'alnour@example.com',
                'is_app_chat' => true,
            ],

            // Jobs | Category: 2
            [
                'user_id' => $user->id,
                'category_id' => $categories->where('name', 'Jobs')->first()->id ?? 2,
                'thumbnail' => './backend/assets/media/posts/job.png',
                'title' => 'Restaurant Cook - Full Time',
                'company_name' => 'Bagdad Restaurant, Toronto',
                'description' => "We are looking for an experienced cook for our Middle Eastern restaurant in downtown Toronto. Minimum 2 years experience in Arabic cuisine required. Competitive salary and benefits.",
                'job_type' => ['Full Time'],
                'province' => 'Ontario',
                'city' => 'Toronto',
                'address' => '123 Main St, Toronto, ON',
                'phone' => '+1-416-555-0100',
                'whatsapp' => '+1-416-555-0100',
                'email' => 'webdev@example.com',
                'is_app_chat' => true,
            ],

            // Business Directory | Category: 3
            [
                'user_id' => $user->id,
                'category_id' => $categories->where('name', 'Business Directory')->first()->id ?? 3,
                'thumbnail' => './backend/assets/media/posts/business_directory.png',
                'logo' => './backend/assets/media/posts/business_logo.png',
                'business_name' => 'Halal Butcher Shop - Vancouver',
                'business_category' => 'Food & Grocery',
                'description' => 'Premium halal butcher shop serving the Toronto community since 2015. Fresh daily cuts of beef, lamb, and poultry. Certified halal. Custom cuts available. Home delivery available for orders over $50.',
                'website' => 'https://alnour.ca',
                'photos' => ['./backend/assets/media/posts/business_directory.png'],
                'province' => 'British Columbia',
                'city' => 'Vancouver',
                'address' => '456 Business Rd, Vancouver, BC',
                'phone' => '+1-604-555-0200',
                'email' => 'contact@techsolutions.com',
                'is_app_chat' => true,
                '_business_hours' => [
                    'sunday' => [
                        'is_closed' => true,
                        'opening_hour' => '10:00 AM',
                        'closing_time' => '7:00 PM'
                    ],
                    'monday' => [
                        'is_closed' => false,
                        'opening_hour' => '10:00 AM',
                        'closing_time' => '7:00 PM'
                    ],
                    'tuesday' => [
                        'is_closed' => false,
                        'opening_hour' => '10:00 AM',
                        'closing_time' => '7:00 PM'
                    ],
                    'wednesday' => [
                        'is_closed' => false,
                        'opening_hour' => '10:00 AM',
                        'closing_time' => '7:00 PM'
                    ],
                    'thursday' => [
                        'is_closed' => false,
                        'opening_hour' => '10:00 AM',
                        'closing_time' => '7:00 PM'
                    ],
                    'friday' => [
                        'is_closed' => false,
                        'opening_hour' => '10:00 AM',
                        'closing_time' => '7:00 PM'
                    ],
                    'saturday' => [
                        'is_closed' => false,
                        'opening_hour' => '10:00 AM',
                        'closing_time' => '7:00 PM'
                    ]
                ]
            ],

            // Services | Category: 4
            [
                'user_id' => $user->id,
                'category_id' => $categories->where('name', 'Services')->first()->id ?? 4,
                'photos' => ['./backend/assets/media/posts/service.png'],
                'title' => 'Frontend Developer Required',
                'description' => 'We are looking for an experienced Frontend Developer.',
                'service_area' => ['Full Time', 'Remote'],
                'province' => 'Alberta',
                'city' => 'Calgary',
                'address' => '789 Career Ave, Calgary, AB',
                'phone' => '+1-403-555-0300',
                'whatsapp' => '+1-416-555-0100',
                'email' => 'hr@company.com',
                'is_app_chat' => true,
            ],
        ];

        foreach ($posts as $postData) {
            $businessHours = null;
            if (isset($postData['_business_hours'])) {
                $businessHours = $postData['_business_hours'];
                unset($postData['_business_hours']);
            }

            $post = Post::create($postData);

            if ($businessHours) {
                $bh = new BusinessHour();
                $bh->post_id = $post->id;
                $bh->sunday = $businessHours['sunday'] ?? null;
                $bh->monday = $businessHours['monday'] ?? null;
                $bh->tuesday = $businessHours['tuesday'] ?? null;
                $bh->wednesday = $businessHours['wednesday'] ?? null;
                $bh->thursday = $businessHours['thursday'] ?? null;
                $bh->friday = $businessHours['friday'] ?? null;
                $bh->saturday = $businessHours['saturday'] ?? null;
                $bh->save();
            }
        }
    }
}
