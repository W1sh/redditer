<?php

class Db{
    private $servername;
    private $username;
    private $password;
    private $schemaName;
    private $conn;
function __construct($servername, $username, $password)
{
    $this->servername=$servername;
    $this->username=$username;
    $this->password=$password;
    
   /* // Create connection
    $this->conn = new mysqli($this->servername, $this->username, $this->password);*/
}
function initDB($DbName){

    // Create connection
    $this->schemaName=$DbName;
    $this->conn = mysqli_connect($this->servername, $this->username, $this->password);
    // Check connection
    if ($this->conn->connect_error) {
        die("Connection failed: " . $this->conn->connect_error."".PHP_EOL);
    }
    echo "Connected successfully".PHP_EOL;

    $sql = "CREATE SCHEMA IF NOT EXISTS ".$this->schemaName.";";

    if ($this->conn->query($sql) === FALSE) {
        echo "Error creating database: " . $this->conn->error."".PHP_EOL;
    }
    echo $DbName;
    echo $this->schemaName;
    $sql = "CREATE TABLE IF NOT EXISTS ".$this->schemaName.".Posts (
        PostId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        Title VARCHAR(30) NOT NULL,
        Body VARCHAR(999) NOT NULL,
        Score VARCHAR(50) NOT NULL,
        RedditorId VARCHAR(30) NOT NULL,
        Awards VARCHAR(50) NOT NULL,
        Created DATETIME NOT NULL,
        Subreddit VARCHAR(50) NOT NULL,
        NumComments INT(6) NOT NULL,
        reg_date TIMESTAMP
        )";
        
    if ($this->conn->query($sql) === FALSE) {
            echo "POSTS: Error creating table: " . $this->conn->error."".PHP_EOL;
    }

    $sql = "CREATE TABLE IF NOT EXISTS ".$this->schemaName.".Comments (
            CommentsId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Awards VARCHAR(50) NOT NULL,
            Score VARCHAR(50) NOT NULL,
            Replies INT(6) NOT NULL,
            Redditor VARCHAR(30) NOT NULL,
            Body VARCHAR(999) NOT NULL,
            Created DATETIME NOT NULL,
            PostId INT(6),
            FOREIGN KEY (PostId) REFERENCES Posts(PostId),
            reg_date TIMESTAMP
            )";
            
    if ($this->conn->query($sql) === FALSE) {
        echo "COMMENT: Error creating table: " . $this->conn->error."".PHP_EOL;
    }

 /*   $sql = "CREATE TABLE IF NOT EXISTS Redditors (
                RedditorId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                Username VARCHAR(30) NOT NULL,
                NumberOfPosts INT(6) NOT NULL,
                )";
                
    if ($this->conn->query($sql) === FALSE) {
        echo "Error creating table: " . $this->conn->error;
    }*/
        
}
function input($table, $data,$id=null)
{
    switch($table){
        case "Post":
            $sql = "INSERT INTO ".$this->schemaName.".Posts("
                .$data->title.
                ",".$data->body.
                ",".$data->score.
                ",".$data->author.
                ",".$data->awards.
                ",".$data->created.
                ",".$data->subreddit.
                ",".$data->numComments.")";
                    
            if ($this->conn->query($sql) === FALSE) {
                echo "INSERT POSTS: Error creating table: " . $this->conn->error."".PHP_EOL;
            }
            $sql="SELECT PostId FROM ".$this->schemaName.".Posts WHERE Title='".$data->title."'";
            $id = $this->conn->query($sql);
            foreach($data->comments as $comment){
                input("Comment",$comment,$id);
            }
    break;

    case "Comment":
        if($id===null){
            return FALSE;
        }
        $sql = "INSERT INTO ".$this->schemaName.".Comments("
        .$data->awards.
        ",".$data->score.
        ",".$data->replies.
        ",".$data->author.
        ",".$data->body.
        ",".$data->created.
        ",".$id.")";
            
        if ($this->conn->query($sql) === FALSE) {
                echo "INSERT COM: Error creating table: " . $this->conn->error."".PHP_EOL;
        }
    break;
    }
}  
}
$t=new Db("localhost", "root", "");
$t->initDB("teste"); 