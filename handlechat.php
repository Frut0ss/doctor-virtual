<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// API Key de GroqCloud
$apiKey = 'TU API KEY AQUI';

$input = json_decode(file_get_contents("php://input"), true);
$message = $input['message'] ?? '';

// Registro de depuración para verificar la entrada
file_put_contents('php://stderr', "Mensaje recibido: " . print_r($input, true) . "\n");

// Verificamos que el mensaje esté presente
if (!$message) {
    http_response_code(400);
    echo json_encode(['response' => 'No se recibió un mensaje válido']);
    exit;
}

function getGroqCloudResponse($message, $apiKey) {
    // Preparamos los datos en el formato adecuado para la API de Groq
    $data = [
        'model' => 'llama3-8b-8192',  // Modelo que quieres usar
        'messages' => [
            // Instrucción para el comportamiento del modelo
            [
                'role' => 'system',
                'content' => 'Eres un asistente experto en enfermedades infecciosas aunque no tienes por que mencionarlo pero si la pregunta no esta relacionada con enfermedades infecciosas no la respondas. Siempre debes responder en español."'
            ],
            // El mensaje enviado por el usuario
            [
                'role' => 'user',
                'content' => $message  // El mensaje enviado por el usuario
            ]
        ]
    ];

    // URL de la API de GroqCloud
    $url = 'https://api.groq.com/openai/v1/chat/completions';  // Asegúrate de verificar la URL correcta en la documentación de GroqCloud.

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(['response' => 'Error en la solicitud: ' . curl_error($ch)]);
        file_put_contents('php://stderr', "cURL Error: " . curl_error($ch) . "\n");
        curl_close($ch);
        exit;
    }

    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpStatus !== 200) {
        file_put_contents('php://stderr', "HTTP Status Code: $httpStatus\n");
        file_put_contents('php://stderr', "Respuesta completa de la API: $result\n");
        http_response_code(500);
        echo json_encode(['response' => 'Error en la respuesta de la API']);
        exit;
    }

    // Decodificamos la respuesta
    $response = json_decode($result, true);

    // Registro de depuración para verificar la respuesta de la API
    file_put_contents('php://stderr', "Respuesta de la API: " . print_r($response, true) . "\n");

    // Asegúrate de acceder al contenido de la respuesta de la API correctamente
    return $response['choices'][0]['message']['content'] ?? 'No se pudo obtener respuesta';
}

$responseMessage = getGroqCloudResponse($message, $apiKey);
echo json_encode(['response' => $responseMessage]);
?>
