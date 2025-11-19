<?php

class Pet {
    public $id;
    public $name;
    public $tag;
    
    public function __construct($id = null, $name = null, $tag = null) {
        $this->id = $id;
        $this->name = $name;
        $this->tag = $tag;
    }
}

class ApiError {
    public $code;
    public $message;
    
    public function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
}

class PetStoreAPI {
    private $allPetsMap;
    private $lastPetId;
    
    const ALLIGATOR_ID = 1;
    const ALLIGATOR_NAME = "Barnaby";
    const ALLIGATOR_TAG = "Vicious";
    
    const AARDVARK_ID = 2;
    const AARDVARK_NAME = "Colin";
    const AARDVARK_TAG = "Accountant";
    
    public function __construct() {
        $this->initializePets();
    }
    
    private function initializePets() {
        error_log("Initialising Pet Map for demo");
        
        $this->allPetsMap = [];
        $this->lastPetId = self::AARDVARK_ID;
        
        // Add pets to Map
        $alligator = new Pet(self::ALLIGATOR_ID, self::ALLIGATOR_NAME, self::ALLIGATOR_TAG);
        $this->allPetsMap[self::ALLIGATOR_ID] = $alligator;
        
        $aardvark = new Pet(self::AARDVARK_ID, self::AARDVARK_NAME, self::AARDVARK_TAG);
        $this->allPetsMap[self::AARDVARK_ID] = $aardvark;
    }
    
    /**
     * Get all pets
     * Retrieve a list of all pets
     * 
     * @return Pet[]
     */
    public function getPets() {
        return array_values($this->allPetsMap);
    }
    
    /**
     * Create a pet
     * Create a new pet in the system
     * 
     * @param array $petData
     * @return array
     */
    public function createPet($petData) {
        // Validate required field
        if (!isset($petData['name']) || empty($petData['name'])) {
            $errorResponse = new ApiError(2000, "Required property not defined: name");
            return [
                'status' => 400,
                'body' => $errorResponse
            ];
        }
        
        // Create new pet
        $this->lastPetId++;
        $newPet = new Pet(
            $this->lastPetId,
            $petData['name'],
            $petData['tag'] ?? null
        );
        
        $this->allPetsMap[$this->lastPetId] = $newPet;
        
        return [
            'status' => 201,
            'body' => $newPet
        ];
    }
    
    /**
     * Get pet by ID
     * Retrieve a pet by its ID
     * 
     * @param int $petId
     * @return array
     */
    public function getPetById($petId) {
        if (!isset($this->allPetsMap[$petId])) {
            $errorResponse = new ApiError(1000, "Unknown Pet identifier");
            return [
                'status' => 400,
                'body' => $errorResponse
            ];
        }
        
        return [
            'status' => 200,
            'body' => $this->allPetsMap[$petId]
        ];
    }
}

// Simple HTTP Router and Handler
class PetStoreRouter {
    private $api;
    
    public function __construct() {
        $this->api = new PetStoreAPI();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];
        
        // Remove query string if present
        $path = strtok($path, '?');
        
        header('Content-Type: application/json');
        
        try {
            // Debug: log the request
            error_log("Request: $method $path");
            
            switch (true) {
                case $path === '/openapi/code_first.php/pets' && $method === 'GET':
                    $this->handleGetPets();
                    break;
                    
                case $path === '/openapi/code_first.php/pets' && $method === 'POST':
                    $this->handleCreatePet();
                    break;
                    
                case preg_match('#^/openapi/code_first\.php/pets/(\d+)$#', $path, $matches) && $method === 'GET':
                    $this->handleGetPetById($matches[1]);
                    break;
                    
                default:
                    error_log("No route found for: $method $path");
                    $this->sendResponse(404, new ApiError(404, 'Endpoint not found. Available: GET/POST /pets, GET /pets/{id}'));
            }
        } catch (Exception $e) {
            error_log("Error handling request: " . $e->getMessage());
            $this->sendResponse(500, new ApiError(500, 'Internal server error'));
        }
    }
    
    private function handleGetPets() {
        $pets = $this->api->getPets();
        $this->sendResponse(200, $pets);
    }
    
    private function handleCreatePet() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, new ApiError(400, 'Invalid JSON'));
            return;
        }
        
        $result = $this->api->createPet($input);
        $this->sendResponse($result['status'], $result['body']);
    }
    
    private function handleGetPetById($petId) {
        $result = $this->api->getPetById((int)$petId);
        $this->sendResponse($result['status'], $result['body']);
    }
    
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
}

// Check what kind of request this is
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Debug information
error_log("SCRIPT_NAME: $scriptName");
error_log("REQUEST_URI: $requestUri");

// Check if this is an API request (contains '/pets' in the path)
if (strpos($requestUri, '/pets') !== false) {
    // This is an API request - handle it
    $router = new PetStoreRouter();
    $router->handleRequest();
    exit;
} else {
    // This is a request for the web interface
    displayWebInterface();
}

function displayWebInterface() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Pet Store API - Code First</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .endpoint { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .method { display: inline-block; padding: 5px 10px; color: white; border-radius: 3px; font-weight: bold; margin-right: 10px; }
            .get { background: #28a745; }
            .post { background: #007bff; }
            button { padding: 8px 15px; margin-left: 10px; cursor: pointer; }
            input { padding: 5px; margin: 0 5px; width: 60px; }
            #result { margin-top: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 5px; min-height: 100px; }
            .response { background: white; padding: 10px; border: 1px solid #ccc; margin-top: 10px; overflow-x: auto; }
            .status-success { color: #28a745; font-weight: bold; }
            .status-error { color: #dc3545; font-weight: bold; }
            .test-area { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <h1>🐾 Pet Store API - Code First Approach</h1>
        <p>This is a <strong>code-first</strong> implementation where the PHP code defines the API behavior.</p>
        
        <div class="test-area">
            <h3>🚀 Test the API Endpoints:</h3>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <strong>/openapi/code_first.php/pets</strong> - Get all pets
                <button onclick="testEndpoint('GET', '/openapi/code_first.php/pets')">Test GET /pets</button>
            </div>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <strong>/openapi/code_first.php/pets/{id}</strong> - Get pet by ID
                <input type="number" id="petId" value="1" placeholder="ID" min="1">
                <button onclick="testGetPetById()">Test GET /pets/{id}</button>
            </div>
            
            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/openapi/code_first.php/pets</strong> - Create new pet
                <button onclick="testCreatePet()">Test POST /pets</button>
            </div>
        </div>
        
        <h3>📊 API Test Result:</h3>
        <div id="result">
            <p>Click the buttons above to test the API endpoints. The results will appear here.</p>
        </div>

        <div class="test-area">
            <h3>🔧 Quick Test Commands (curl):</h3>
            <pre>
# Get all pets
curl -X GET http://localhost/openapi/code_first.php/pets

# Get pet by ID
curl -X GET http://localhost/openapi/code_first.php/pets/1

# Create new pet
curl -X POST http://localhost/openapi/code_first.php/pets \
  -H "Content-Type: application/json" \
  -d '{"name": "Rex", "tag": "Dog"}'
            </pre>
        </div>
        
        <script>
            async function testEndpoint(method, url, data = null) {
                const resultDiv = document.getElementById('result');
                resultDiv.innerHTML = `<p>🔄 Calling ${method} ${url}...</p>`;
                
                try {
                    const options = {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    };
                    
                    if (data) {
                        options.body = JSON.stringify(data);
                    }
                    
                    const response = await fetch(url, options);
                    const result = await response.json();
                    
                    const statusClass = response.status >= 200 && response.status < 300 ? 'status-success' : 'status-error';
                    
                    resultDiv.innerHTML = `
                        <div><strong>Endpoint:</strong> ${method} ${url}</div>
                        <div class="${statusClass}"><strong>Status:</strong> ${response.status} ${response.statusText}</div>
                        <div><strong>Response:</strong></div>
                        <div class="response"><pre>${JSON.stringify(result, null, 2)}</pre></div>
                    `;
                } catch (error) {
                    resultDiv.innerHTML = `
                        <div><strong>Endpoint:</strong> ${method} ${url}</div>
                        <div class="status-error"><strong>Error:</strong> ${error.toString()}</div>
                    `;
                }
            }
            
            function testGetPetById() {
                const petId = document.getElementById('petId').value || '1';
                testEndpoint('GET', '/openapi/code_first.php/pets/' + petId);
            }
            
            function testCreatePet() {
                const petData = {
                    name: 'Pet_' + Math.floor(Math.random() * 1000),
                    tag: ['Dog', 'Cat', 'Bird', 'Fish'][Math.floor(Math.random() * 4)]
                };
                testEndpoint('POST', '/openapi/code_first.php/pets', petData);
            }
            
            // Test all endpoints on load
            window.onload = function() {
                testEndpoint('GET', '/openapi/code_first.php/pets');
            }
        </script>
        
        <h3>📖 About Code-First Approach:</h3>
        <ul>
            <li><strong>Business logic first</strong> - Code defines the API behavior</li>
            <li><strong>Models as PHP classes</strong> - Pet and ApiError are PHP classes</li>
            <li><strong>Can generate OpenAPI later</strong> - Documentation comes from code</li>
            <li><strong>Full control</strong> - Complete control over implementation</li>
            <li><strong>Faster development</strong> - No need to write spec first</li>
        </ul>
        
        <h3>🔍 Current API Structure:</h3>
        <ul>
            <li><code>GET /openapi/code_first.php/pets</code> - Returns array of all pets</li>
            <li><code>POST /openapi/code_first.php/pets</code> - Creates new pet (requires name)</li>
            <li><code>GET /openapi/code_first.php/pets/{id}</code> - Returns specific pet by ID</li>
        </ul>
    </body>
    </html>
    <?php
}

// Command line test function
if (php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST'])) {
    function runCommandLineTests() {
        echo "=== Pet Store API (Code-First) - CLI Tests ===\n";
        
        $api = new PetStoreAPI();
        
        echo "\n1. Getting all pets:\n";
        $pets = $api->getPets();
        echo json_encode($pets, JSON_PRETTY_PRINT) . "\n";
        
        echo "\n2. Creating a new pet:\n";
        $result = $api->createPet(['name' => 'Rex', 'tag' => 'Dog']);
        echo "Status: " . $result['status'] . "\n";
        echo "Body: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n";
        
        echo "\n3. Getting pet by ID (1):\n";
        $result = $api->getPetById(1);
        echo "Status: " . $result['status'] . "\n";
        echo "Body: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n";
        
        echo "\n4. Getting non-existent pet:\n";
        $result = $api->getPetById(999);
        echo "Status: " . $result['status'] . "\n";
        echo "Body: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n";
    }
    
    runCommandLineTests();
}
