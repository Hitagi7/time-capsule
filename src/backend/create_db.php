<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('localhost', 'root', '');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS timelocks";
if ($conn->query($sql) === TRUE) {
    echo "Database created or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db('timelocks');

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)";

//CREATE TABLE messages (
  //id INT AUTO_INCREMENT PRIMARY KEY,
 // user_email VARCHAR(255) NOT NULL,
 // content TEXT NOT NULL,
 // open_date DATE NOT NULL,
 // created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 // FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
//);

if ($conn->query($sql) === TRUE) {
    echo "Table users created or already exists<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$conn->close();
echo "Setup complete";
?> 