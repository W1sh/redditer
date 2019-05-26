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
            Redditor VARCHAR(30) NOT NULL,
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
                
        if ($this->conn->query($sql) === FALSE) {
            echo "COMMENT: Error creating table: " . $this->conn->error."".PHP_EOL;
        }   
    }

    function input($table, $data,$id=null)
    {
        if($data->title!=NULL){
            $title=str_replace("’","",$data->title);
            $title=str_replace("'","",$title);
            //echo "Title: ".$title.PHP_EOL;
        }
        if($data->body!=NULL){
            $body=str_replace("’","",$data->body);
            $body=str_replace("'","",$body);
            //echo "Body: ".$body.PHP_EOL;
        }
        
        switch($table){
            case "Post":
                $sql = sprintf("INSERT INTO %s.Posts(Title, Body, Score, Redditor, Awards, Created, Subreddit, NumComments,reg_date) VALUES
                ('%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)",$this->schemaName,$title,$data->body==NULL?NULL:$body,$data->score==NULL?NULL:$data->score,
                    $data->author==NULL?NULL:$data->author,$data->awards==NULL?NULL:$data->awards,$data->created==NULL?NULL:$data->created,
                    $data->subreddit==NULL?NULL:$data->subreddit,$data->numComments==NULL?NULL:$data->numComments);
                
                if ($this->conn->query($sql) === FALSE) {
                    echo "INSERT POSTS: Error creating table: " . $this->conn->error."".PHP_EOL;
                }
                $sql="SELECT PostId FROM ".$this->schemaName.".Posts WHERE Title='".$title."'";
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
            $data->replies==NULL?NULL:$data->replies,$data->author==NULL?NULL:$data->author,$data->body==NULL?NULL:$body,$data->created==NULL?NULL:$data->created,
            $id);
                /* $sql = sprintf("INSERT INTO %s.Posts(Title, Body, Score, RedditorId, Awards, Created, Subreddit, NumComments,reg_date) VALUES
                ('%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)",$this->schemaName,$data->title,$data->body==NULL?NULL:$data->body,$data->score==NULL?NULL:$data->score,
                $data->author==NULL?NULL:$data->author,$data->awards==NULL?NULL:$data->awards,$data->created==NULL?NULL:$data->created,
                $data->subreddit==NULL?NULL:$data->subreddit,$data->numComments==NULL?NULL:$data->numComments);
        */ // ' do body quebra a String
            if ($this->conn->query($sql) === FALSE) {
                    echo "INSERT COM: Error creating insert: " . $this->conn->error."".PHP_EOL;
            }
        break;
        }
    }  
    function statistcsSearcher($what,$conditions,$table,$counting=false, $return=false){
        $format= $sql="SELECT ".$what." FROM ";
        $innerJoin=false;
        /*
        What is the thing the user wishs to search
        Conditions is an array with conditions
        table is the name of the table
        counting enables the return of the number of rows that we got from the select
        return enables the return of an array with the rows selected
        */
        $count=0;
        $res=array();
        if($table==""){
            $format.=$this->schemaName.".Posts INNER JOIN ".$this->schemaName.".Comments";
            $innerJoin=true;
        }else{
            $format.=$this->schemaName.".".$table;
            echo "Table: ".$table.PHP_EOL;
        }
        if(!empty($conditions)){
            foreach($conditions as $condition){
                $sql="";
                if($innerJoin){
                    $sql=$format." WHERE Posts.".$condition;
                    $sql.="OR Comments.".$condition;
                }else{
                    $sql=$format." WHERE ".$condition;
                }
                echo $sql;
                if ($this->conn->query($sql) === FALSE) {
                    echo "INSERT POSTS: Error creating table: " . $this->conn->error."".PHP_EOL;
                }
                $result = $this->conn->query($sql);      
                while($row = $result->fetch_assoc()) {
                    if($counting){
                        $count++;
                    }
                    if($return){
                        array_push($res,$row);
                    }
                }
            }
        }else{
        $result = $this->conn->query($format);      
            while($row = $result->fetch_assoc()) {
                if($counting){
                    $count++;
                }
                if($return){
                    array_push($res,$row);
                }
            }
        }
        if($counting&&$return||!$counting&&!$return){
            return $ar=array( 'Result'=>$res,'Count'=>$count);
        }
        if($counting) return $count;
        if($return) return $res;
    }
}
