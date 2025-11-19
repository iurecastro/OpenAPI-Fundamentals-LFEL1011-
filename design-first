<?php

class PetStoreAPI {
    private $baseUrl;
    private $apiKey;
    
    public function __construct($baseUrl, $apiKey) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }
    
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'api-key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    // GET /pets - Retrieve all pets
    public function getAllPets() {
        return $this->makeRequest('/pets', 'GET');
    }
    
    // GET /pets/{petId} - Retrieve a specific pet
    public function getPet($petId) {
        return $this->makeRequest("/pets/{$petId}", 'GET');
    }
    
    // POST /pets - Create a new pet
    public function createPet($name, $type = null) {
        $data = ['name' => $name];
        
        if ($type) {
            $data['type'] = $type;
        }
        
        return $this->makeRequest('/pets', 'POST', $data);
    }
}

// Example usage
try {
    // Configuration
    $baseUrl = 'https://api.example.com'; // Replace with actual API URL
    $apiKey = 'your-api-key-here'; // Replace with your API key
    
    // Create API instance
    $petStore = new PetStoreAPI($baseUrl, $apiKey);
    
    echo "=== PET STORE API CLIENT ===\n\n";
    
    // 1. Create a new pet
    echo "1. Creating a new pet...\n";
    $newPet = $petStore->createPet('Rex', 'Dog');
    
    if ($newPet['status'] === 201) {
        echo "✅ Pet created successfully!\n";
        echo "ID: " . $newPet['data']['id'] . "\n";
        echo "Name: " . $newPet['data']['name'] . "\n";
        if (isset($newPet['data']['tag'])) {
            echo "Type: " . $newPet['data']['tag'] . "\n";
        }
    } else {
        echo "❌ Error creating pet: " . ($newPet['data']['message'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    // 2. Get all pets
    echo "2. Retrieving all pets...\n";
    $allPets = $petStore->getAllPets();
    
    if ($allPets['status'] === 200) {
        echo "✅ " . count($allPets['data']) . " pets found:\n";
        foreach ($allPets['data'] as $pet) {
            echo " - ID: {$pet['id']}, Name: {$pet['name']}";
            if (isset($pet['tag'])) {
                echo ", Type: {$pet['tag']}";
            }
            echo "\n";
        }
    } else {
        echo "❌ Error retrieving pets: " . ($allPets['data']['message'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    // 3. Get a specific pet (replace 1 with a valid ID)
    echo "3. Retrieving a specific pet...\n";
    $petId = 1; // Replace with a valid ID
    $specificPet = $petStore->getPet($petId);
    
    if ($specificPet['status'] === 200) {
        echo "✅ Pet found:\n";
        echo "ID: " . $specificPet['data']['id'] . "\n";
        echo "Name: " . $specificPet['data']['name'] . "\n";
        if (isset($specificPet['data']['tag'])) {
            echo "Type: " . $specificPet['data']['tag'] . "\n";
        }
    } else {
        echo "❌ Error retrieving pet: " . ($specificPet['data']['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Simple usage example function
function simpleExample() {
    $api = new PetStoreAPI('https://api.example.com', 'your-api-key');
    
    // Get all pets
    $pets = $api->getAllPets();
    
    // Create a pet
    $newPet = $api->createPet('Fluffy', 'Cat');
    
    return [
        'all_pets' => $pets,
        'new_pet' => $newPet
    ];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Pet Store API Client</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .pet { border: 1px solid #ddd; padding: 10px; margin: 5px 0; }
        .container { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pet Store API Client</h1>
        
        <h2>Available Operations:</h2>
        <ul>
            <li><strong>GET /pets</strong> - List all pets</li>
            <li><strong>GET /pets/{id}</strong> - Get a specific pet</li>
            <li><strong>POST /pets</strong> - Create a new pet</li>
        </ul>
        
        <h2>Usage Example:</h2>
        <pre>
// Initialize API client
$petStore = new PetStoreAPI('https://api.example.com', 'your-api-key');

// Get all pets
$pets = $petStore->getAllPets();

// Get specific pet
$pet = $petStore->getPet(1);

// Create new pet
$newPet = $petStore->createPet('Buddy', 'Dog');
        </pre>
        
        <h2>Test the API:</h2>
        <form method="POST">
            <button type="submit" name="test_get_all">Test GET /pets</button>
            <button type="submit" name="test_create">Test POST /pets</button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['test_get_all'])) {
                echo "<h3>Testing GET /pets:</h3>";
                $result = $petStore->getAllPets();
                echo "<pre>" . print_r($result, true) . "</pre>";
            }
            
            if (isset($_POST['test_create'])) {
                echo "<h3>Testing POST /pets:</h3>";
                $result = $petStore->createPet('TestPet_' . rand(100, 999), 'TestType');
                echo "<pre>" . print_r($result, true) . "</pre>";
            }
        }
        ?>
    </div>
</body>
</html>
