<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the user query from the POST request
  $userQuery = $_POST['query'];

  // Set up the Gemini API endpoint and key
  $apiUrl = 'https://gemini.googleapis.com/v1/chat/completions';  // Replace with the actual Gemini API endpoint
  $apiKey = 'AIzaSyBuJYtYI4rYPTJYmhELnwND7rmekpHjVr8';  // Make sure to replace this with your actual Gemini API key

  // Prepare the data to send in the POST request
  $data = [
    'model' => 'gemini', // Model name
    'messages' => [['role' => 'user', 'content' => $userQuery]]
  ];

  // Initialize cURL session
  $ch = curl_init();

  // Set cURL options
  curl_setopt($ch, CURLOPT_URL, $apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
  ]);

  // Execute cURL request and get the response
  $response = curl_exec($ch);

  // Close cURL session
  curl_close($ch);

  // Check for errors
  if ($response === false) {
    die('Error communicating with Gemini API');
  }

  // Decode and return the response
  $responseData = json_decode($response, true);
  if (isset($responseData['choices'][0]['message']['content'])) {
    echo json_encode(['response' => $responseData['choices'][0]['message']['content']]);
  } else {
    echo json_encode(['response' => 'Sorry, I didn\'t understand that.']);
  }
} else {
  echo json_encode(['response' => 'Invalid request method.']);
}
