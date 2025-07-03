<?php

class OffersRewards {
    private static $dataFile = 'data/offers_rewards.json';
    
    public static function initializeDataFile() {
        if (!file_exists(self::$dataFile)) {
            $defaultData = [
                'special_offers' => [],
                'available_rewards' => [],
                'promo_codes' => []
            ];
            self::saveData($defaultData);
        }
    }
    
    public static function loadData() {
        if (!file_exists(self::$dataFile)) {
            self::initializeDataFile();
        }
        
        $data = file_get_contents(self::$dataFile);
        return json_decode($data, true) ?: [
            'special_offers' => [],
            'available_rewards' => [],
            'promo_codes' => []
        ];
    }
    
    public static function saveData($data) {
        $dir = dirname(self::$dataFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(self::$dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    // Special Offers Methods
    public static function getSpecialOffers($activeOnly = true) {
        $data = self::loadData();
        $offers = $data['special_offers'] ?? [];
        
        if ($activeOnly) {
            $offers = array_filter($offers, function($offer) {
                return $offer['is_active'];
            });
        }
        
        return array_values($offers);
    }
    
    public static function addSpecialOffer($offerData) {
        $data = self::loadData();
        $offerData['id'] = uniqid('offer_');
        $offerData['created_at'] = date('Y-m-d H:i:s');
        $offerData['usage_count'] = 0;
        
        $data['special_offers'][] = $offerData;
        self::saveData($data);
        
        return $offerData['id'];
    }
    
    public static function updateSpecialOffer($id, $offerData) {
        $data = self::loadData();
        
        foreach ($data['special_offers'] as &$offer) {
            if ($offer['id'] === $id) {
                $offer = array_merge($offer, $offerData);
                $offer['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        self::saveData($data);
        return true;
    }
    
    public static function deleteSpecialOffer($id) {
        $data = self::loadData();
        
        $data['special_offers'] = array_filter($data['special_offers'], function($offer) use ($id) {
            return $offer['id'] !== $id;
        });
        
        self::saveData($data);
        return true;
    }
    
    // Available Rewards Methods
    public static function getAvailableRewards($activeOnly = true) {
        $data = self::loadData();
        $rewards = $data['available_rewards'] ?? [];
        
        if ($activeOnly) {
            $rewards = array_filter($rewards, function($reward) {
                return $reward['is_active'];
            });
        }
        
        return array_values($rewards);
    }
    
    public static function addAvailableReward($rewardData) {
        $data = self::loadData();
        $rewardData['id'] = uniqid('reward_');
        $rewardData['created_at'] = date('Y-m-d H:i:s');
        $rewardData['unlocked_by_count'] = 0;
        
        $data['available_rewards'][] = $rewardData;
        self::saveData($data);
        
        return $rewardData['id'];
    }
    
    public static function updateAvailableReward($id, $rewardData) {
        $data = self::loadData();
        
        foreach ($data['available_rewards'] as &$reward) {
            if ($reward['id'] === $id) {
                $reward = array_merge($reward, $rewardData);
                $reward['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        self::saveData($data);
        return true;
    }
    
    public static function deleteAvailableReward($id) {
        $data = self::loadData();
        
        $data['available_rewards'] = array_filter($data['available_rewards'], function($reward) use ($id) {
            return $reward['id'] !== $id;
        });
        
        self::saveData($data);
        return true;
    }
    
    // Promo Codes Methods
    public static function getPromoCodes($activeOnly = true) {
        $data = self::loadData();
        $codes = $data['promo_codes'] ?? [];
        
        if ($activeOnly) {
            $codes = array_filter($codes, function($code) {
                return $code['is_active'] && 
                       ($code['valid_until'] === null || strtotime($code['valid_until']) > time()) &&
                       ($code['max_usage'] === null || $code['usage_count'] < $code['max_usage']);
            });
        }
        
        return array_values($codes);
    }
    
    public static function validatePromoCode($code) {
        $codes = self::getPromoCodes(true);
        
        foreach ($codes as $promoCode) {
            if (strtoupper($promoCode['code']) === strtoupper($code)) {
                return $promoCode;
            }
        }
        
        return null;
    }
    
    public static function addPromoCode($codeData) {
        $data = self::loadData();
        $codeData['created_at'] = date('Y-m-d H:i:s');
        $codeData['usage_count'] = 0;
        
        $data['promo_codes'][] = $codeData;
        self::saveData($data);
        
        return true;
    }
    
    public static function updatePromoCode($code, $codeData) {
        $data = self::loadData();
        
        foreach ($data['promo_codes'] as &$promoCode) {
            if (strtoupper($promoCode['code']) === strtoupper($code)) {
                $promoCode = array_merge($promoCode, $codeData);
                $promoCode['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        self::saveData($data);
        return true;
    }
    
    public static function deletePromoCode($code) {
        $data = self::loadData();
        
        $data['promo_codes'] = array_filter($data['promo_codes'], function($promoCode) use ($code) {
            return strtoupper($promoCode['code']) !== strtoupper($code);
        });
        
        self::saveData($data);
        return true;
    }
    
    // Statistics Methods
    public static function getStatistics() {
        $data = self::loadData();
        
        $activeOffers = count(array_filter($data['special_offers'], function($offer) {
            return $offer['is_active'];
        }));
        
        $totalRedemptions = array_sum(array_column($data['special_offers'], 'usage_count'));
        
        $activeRewards = count(array_filter($data['available_rewards'], function($reward) {
            return $reward['is_active'];
        }));
        
        $totalUnlockedRewards = array_sum(array_column($data['available_rewards'], 'unlocked_by_count'));
        
        return [
            'active_offers' => $activeOffers,
            'total_redemptions' => $totalRedemptions,
            'active_rewards' => $activeRewards,
            'total_unlocked_rewards' => $totalUnlockedRewards
        ];
    }
    
    // Helper method to get offers for display on home/offers pages
    public static function getOffersForDisplay() {
        $offers = self::getSpecialOffers(true);
        
        // Convert to the format expected by the frontend
        $formattedOffers = [];
        foreach ($offers as $offer) {
            $formattedOffers[] = [
                'icon' => $offer['icon'],
                'title' => $offer['title'],
                'desc' => $offer['description'],
                'note' => $offer['note'],
                'badge' => $offer['badge']
            ];
        }
        
        return $formattedOffers;
    }
    
    // Helper method to get rewards for display on offers page
    public static function getRewardsForDisplay($userPoints = 0) {
        $rewards = self::getAvailableRewards(true);
        
        // Convert to the format expected by the frontend
        $formattedRewards = [];
        foreach ($rewards as $reward) {
            $isUnlocked = $userPoints >= $reward['required_points'];
            $progress = min(100, round($userPoints / $reward['required_points'] * 100));
            
            $formattedRewards[] = [
                'icon' => $reward['icon'],
                'title' => $reward['title'],
                'desc' => $reward['description'],
                'tier' => $reward['tier'],
                'points' => $reward['required_points'],
                'is_unlocked' => $isUnlocked,
                'progress' => $progress
            ];
        }
        
        return $formattedRewards;
    }
}
?> 