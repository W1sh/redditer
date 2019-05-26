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
function initDB(){

    // Create connection
    $this->schemaName="RedditerStatistics";
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

    $sql = "CREATE TABLE IF NOT EXISTS ".$this->schemaName.".Posts (
        PostId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        Title VARCHAR(100) NOT NULL,
        Body VARCHAR(999) ,
        Score VARCHAR(50) ,
        RedditorId VARCHAR(30) NOT NULL,
        Awards VARCHAR(50) ,
        Created DATETIME NOT NULL,
        Subreddit VARCHAR(50) NOT NULL,
        NumComments INT(10),
        reg_date TIMESTAMP
        )";
        
    if ($this->conn->query($sql) === FALSE) {
            echo "POSTS: Error creating table: " . $this->conn->error."".PHP_EOL;
    }

    $sql = "CREATE TABLE IF NOT EXISTS ".$this->schemaName.".Comments (
            CommentsId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Awards VARCHAR(50),
            Score VARCHAR(50),
            Replies INT(6),
            Redditor VARCHAR(30) NOT NULL,
            Body VARCHAR(999) NOT NULL,
            Created DATETIME NOT NULL,
            PostId INT(6) UNSIGNED,
            reg_date TIMESTAMP,
            FOREIGN KEY (PostId) REFERENCES ".$this->schemaName.".Posts(PostId)
            )";
            // ' do body quebra a String
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
           /* $sql = sprintf("INSERT INTO %s.Posts(Title, Body, Score, RedditorId, Awards, Created, Subreddit, NumComments,reg_date) VALUES
            ('%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)",$this->schemaName,$data->title,$data->body==NULL?NULL:$data->body,$data->score==NULL?NULL:$data->score,
                $data->author==NULL?NULL:$data->author,$data->awards==NULL?NULL:$data->awards,$data->created==NULL?NULL:$data->created,
                $data->subreddit==NULL?NULL:$data->subreddit,$data->numComments==NULL?NULL:$data->numComments);
            if ($this->conn->query($sql) === FALSE) {
                echo "INSERT POSTS: Error creating table: " . $this->conn->error."".PHP_EOL;
            }*/
            $sql="SELECT PostId FROM ".$this->schemaName.".Posts WHERE Title='".$data->title."'";
            $result = $this->conn->query($sql);
            $id=0;
            while($row = $result->fetch_assoc()) {
                $id=$row["PostId"];
            }
            foreach($data->comments as $comment){
                $this->input("Comment",$comment,$id);
            }
    break;

    case "Comment":
        if($id===null){
            return FALSE;
        }
        $sql = sprintf("INSERT INTO %s.Comments(Awards, Score, Replies, Redditor, Body, Created, PostId, reg_date) VALUES(
        '%s','%s','%s','%s','%s','%s','%s',CURRENT_TIMESTAMP)",$this->schemaName,$data->awards==NULL?NULL:$data->awards,$data->score==NULL?NULL:$data->score,
        $data->replies==NULL?NULL:$data->replies,$data->author==NULL?NULL:$data->author,$data->body==NULL?NULL:$data->body,$data->created==NULL?NULL:$data->created,
        $id);
            /* $sql = sprintf("INSERT INTO %s.Posts(Title, Body, Score, RedditorId, Awards, Created, Subreddit, NumComments,reg_date) VALUES
            ('%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)",$this->schemaName,$data->title,$data->body==NULL?NULL:$data->body,$data->score==NULL?NULL:$data->score,
            $data->author==NULL?NULL:$data->author,$data->awards==NULL?NULL:$data->awards,$data->created==NULL?NULL:$data->created,
            $data->subreddit==NULL?NULL:$data->subreddit,$data->numComments==NULL?NULL:$data->numComments);
       */ if ($this->conn->query($sql) === FALSE) {
                echo "INSERT COM: Error creating table: " . $this->conn->error."".PHP_EOL;
        }
    break;
    }
}  
}
$t=new Db("localhost", "root", "");
$t->initDB("teste"); 