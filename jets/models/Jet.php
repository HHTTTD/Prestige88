<?php
require_once 'config/database.php';

class Jet {
    public static function getAll() {
        return Database::loadJets();
    }
    
    public static function getById($jetId) {
        $jets = self::getAll();
        foreach ($jets as $jet) {
            if ($jet['id'] === $jetId) {
                return $jet;
            }
        }
        return null;
    }
    
    public static function getAvailable() {
        $jets = self::getAll();
        return array_filter($jets, function($jet) {
            return $jet['status'] === 'available';
        });
    }
    
    public static function updateStatus($jetId, $status) {
        $jets = self::getAll();
        foreach ($jets as &$jet) {
            if ($jet['id'] === $jetId) {
                $jet['status'] = $status;
                $jet['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        Database::saveJets($jets);
    }
    
    public static function create($jetData) {
        $jets = self::getAll();
        
        $newJet = [
            'id' => uniqid(),
            'model' => $jetData['model'],
            'capacity' => intval($jetData['capacity']),
            'price_per_hour' => floatval($jetData['price_per_hour']),
            'range_km' => intval($jetData['range_km']),
            'max_speed' => intval($jetData['max_speed']),
            'amenities' => $jetData['amenities'] ?? [],
            'image' => $jetData['image'] ?? '',
            'status' => 'available',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $jets[] = $newJet;
        Database::saveJets($jets);
        
        return $newJet;
    }
    
    public static function update($jetId, $jetData) {
        $jets = self::getAll();
        
        foreach ($jets as &$jet) {
            if ($jet['id'] === $jetId) {
                $jet['model'] = $jetData['model'];
                $jet['capacity'] = intval($jetData['capacity']);
                $jet['price_per_hour'] = floatval($jetData['price_per_hour']);
                $jet['range_km'] = intval($jetData['range_km']);
                $jet['max_speed'] = intval($jetData['max_speed']);
                $jet['amenities'] = $jetData['amenities'] ?? [];
                $jet['image'] = $jetData['image'] ?? $jet['image'];
                $jet['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        Database::saveJets($jets);
    }
    
    public static function delete($jetId) {
        $jets = self::getAll();
        $jets = array_filter($jets, function($jet) use ($jetId) {
            return $jet['id'] !== $jetId;
        });
        Database::saveJets(array_values($jets));
    }

    public static function findAll() {
        return Database::loadJets();
    }

    public static function findById($id) {
        $jets = self::findAll();
        foreach ($jets as $jet) {
            if ($jet['id'] === $id) {
                return $jet;
            }
        }
        return null;
    }
}