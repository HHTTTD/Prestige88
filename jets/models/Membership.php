<?php
class Membership {
    public static function getTiers() {
        return [
            'silver' => [
                'name' => 'Silver Tier',
                'name_th' => 'สมาชิกเงิน',
                'icon' => 'fa-medal',
                'color' => 'gray-500',
                'bg_color' => 'gray-100',
                'discount' => 0,
                'benefits' => [
                    'Welcome gift',
                    'Exclusive member-only content or resources',
                    'Early access to sales or new products',
                    'Birthday reward'
                ]
            ],
            'gold' => [
                'name' => 'Gold Tier',
                'name_th' => 'สมาชิกทอง',
                'icon' => 'fa-star',
                'color' => 'yellow-500',
                'bg_color' => 'yellow-100',
                'discount' => 5,
                'benefits' => [
                    'All Silver Tier benefits',
                    'Faster shipping',
                    'Dedicated concierge support line',
                    'Free gift wrapping',
                    'Annual gift'
                ]
            ],
            'platinum' => [
                'name' => 'Platinum Tier',
                'name_th' => 'สมาชิกแพลทินัม',
                'icon' => 'fa-gem',
                'color' => 'purple-500',
                'bg_color' => 'purple-100',
                'discount' => 10,
                'benefits' => [
                    'All Silver and Gold Tier benefits',
                    'Free expedited shipping',
                    'Early access to new product launches',
                    'Invitations to exclusive events or webinars',
                    'Personalized recommendations or consultations',
                    'A luxury travel package according to your lifestyle'
                ]
            ],
            'black' => [
                'name' => 'Black Tier',
                'name_th' => 'สมาชิกดำ',
                'icon' => 'fa-crown',
                'color' => 'black',
                'bg_color' => 'gray-900',
                'text_color' => 'white',
                'discount' => 15,
                'benefits' => [
                    '*Invitation Only',
                    'All Silver, Gold, and Platinum Tier benefits',
                    'Private jet hours',
                    'Highest priority concierge service with dedicated account manager',
                    'Exclusive experiences',
                    'Luxury gifts or upgrades',
                    'Invitations to VIP events'
                ]
            ]
        ];
    }
    
    public static function calculateTier($userId) {
        $bookings = Database::loadBookings();
        $userBookings = array_filter($bookings, function($booking) use ($userId) {
            return $booking['user_id'] === $userId && $booking['status'] === 'confirmed';
        });
        
        $totalBookings = count($userBookings);
        $totalSpending = array_sum(array_column($userBookings, 'total_cost'));
        
        $tiers = self::getTiers();
        
        // Check from highest to lowest tier
        foreach (['black', 'platinum', 'gold', 'silver'] as $tierKey) {
            $tier = $tiers[$tierKey];
            if ($totalBookings >= $tier['min_bookings'] || $totalSpending >= $tier['min_spending']) {
                return $tierKey;
            }
        }
        
        return 'silver';
    }
    
    public static function getDiscount($userId) {
        $tier = self::calculateTier($userId);
        $tiers = self::getTiers();
        return $tiers[$tier]['discount'];
    }

    // ฟังก์ชันคำนวณ tier จากคะแนน
    public static function getTierByPoints($points) {
        if ($points >= 20000) return 'black';
        if ($points >= 10000) return 'platinum';
        if ($points >= 4000) return 'gold';
        return 'silver';
    }

    // ฟังก์ชันคำนวณข้อมูล tier ถัดไปและ progress
    public static function getNextTierInfo($points) {
        if ($points < 4000) {
            return [
                'next_tier' => 'Gold',
                'points_to_next' => 4000 - $points,
                'next_tier_points' => 4000,
                'progress_percent' => round($points / 4000 * 100)
            ];
        } elseif ($points < 10000) {
            return [
                'next_tier' => 'Platinum',
                'points_to_next' => 10000 - $points,
                'next_tier_points' => 10000,
                'progress_percent' => round(($points - 4000) / 6000 * 100)
            ];
        } elseif ($points < 20000) {
            return [
                'next_tier' => 'Black',
                'points_to_next' => 20000 - $points,
                'next_tier_points' => 20000,
                'progress_percent' => round(($points - 10000) / 10000 * 100)
            ];
        } else {
            return [
                'next_tier' => null,
                'points_to_next' => 0,
                'next_tier_points' => $points,
                'progress_percent' => 100
            ];
        }
    }
}