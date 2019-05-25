<?php

class Db{
    private $servername;
    private $username;
    private $password;
function __construct($servername, $username, $password)
{
    $this->servername=$servername;
    $this->username=$username;
    $this->password=$password;
}
function initDB(){
// Create connection
$conn = new mysqli($this->servername, $this->username, $this->password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
$sql = "CREATE DATABASE IF NOT EXISTS test;";
if ($conn->query($sql) === FALSE) {
    echo "Error creating database: " . $conn->error;
}
}
function input()
{
    
}
} 