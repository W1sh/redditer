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
            PostId VARCHAR(10) NOT NULL PRIMARY KEY,
            Title VARCHAR(999) NOT NULL,
            Body VARCHAR(999),
            Score INT(18),
            Redditor VARCHAR(30) NOT NULL,
            Awards INT(18),
            PostUrl VARCHAR(300) NOT NULL,
            IsVideo BOOL NOT NULL,
            Url VARCHAR(18) NOT NULL,
            ImageId VARCHAR(18) NOT NULL,
            Created VARCHAR(30) NOT NULL,
            Subreddit VARCHAR(50) NOT NULL,
            NumComments INT(18),
            reg_date TIMESTAMP
            )";    
        if ($this->conn->query($sql) === FALSE) {
                echo "POSTS: Error creating table: " . $this->conn->error."".PHP_EOL;
        }

        $sql = "CREATE TABLE IF NOT EXISTS ".$this->schemaName.".Comments (
                CommentsId VARCHAR(10) NOT NULL PRIMARY KEY,
                Awards INT(18),
                Score INT(18),
                Replies INT(18),
                Redditor VARCHAR(30) NOT NULL,
                Body VARCHAR(999) NOT NULL,
                Created VARCHAR(30) NOT NULL,
                PostId VARCHAR(10) NOT NULL,
                reg_date TIMESTAMP,
                FOREIGN KEY (PostId) REFERENCES ".$this->schemaName.".Posts(PostId)
                )";
                
        if ($this->conn->query($sql) === FALSE) {
            echo "COMMENT: Error creating table: " . $this->conn->error."".PHP_EOL;
        }   
    }
    function banCharacters($unwanted, $string)
    {
        foreach($unwanted as $ban){
            $string=str_replace($ban,"",$string);
        }
        return $string;
    }
    function input($table, $data,$id=null)
    {
        $unwanted=array("'","“","”","’");
        if($data->title!=NULL){
            $titleSQL = $this->banCharacters($unwanted, $data->title);
            $titleSQL=mysqli_real_escape_string($this->conn, $titleSQL);
            //echo $titleSQL.PHP_EOL;
        }
        if($data->body!=NULL){

            $bodySQL= $this->banCharacters($unwanted, $data->body);
           /* $body=str_replace("“","\"",$body);
            $body=str_replace("”","\"",$body);*/
            $bodySQL=mysqli_real_escape_string($this->conn, $bodySQL);
            //echo $bodySQL.PHP_EOL;            
        }
        
        switch($table){
            case "Post":
                
                
                $sql = sprintf("INSERT INTO %s.Posts VALUES
                ('%s','%s','%s','%s','%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)",$this->schemaName,$data->id,$titleSQL,$data->body==NULL?NULL:$bodySQL,$data->score==NULL?NULL:$data->score,
                    $data->author==NULL?NULL:$data->author,$data->awards==NULL?NULL:$data->awards,$data->postUrl,$data->contentUrl['is_video'],
                    $data->contentUrl['url'],$data->contentUrl['image_id'],$data->created==NULL?NULL:$data->created,
                    $data->subreddit==NULL?NULL:$data->subreddit,$data->numComments==NULL?NULL:$data->numComments);
                    if ($this->conn->query($sql) === FALSE) {
                    echo "INSERT POSTS: Error creating insert: " . $this->conn->error."".PHP_EOL;
                }
           
                foreach($data->comments as $comment){
                    $this->input("Comment",$comment,$data->id);
                }
        break;

        case "Comment":
            if($id===null){
                return FALSE;
            }
            $sql = sprintf("INSERT INTO %s.Comments VALUES(
            '%s','%s','%s','%s','%s','%s','%s','%s',CURRENT_TIMESTAMP)",$this->schemaName,$data->id,$data->awards==NULL?NULL:$data->awards,$data->score==NULL?NULL:$data->score,
            $data->replies==NULL?NULL:$data->replies,$data->author==NULL?NULL:$data->author,$data->body==NULL?NULL:$bodySQL,$data->created==NULL?NULL:$data->created,
            $id);
            if ($this->conn->query($sql) === FALSE) {
                    echo "INSERT COM: Error creating insert: " . $this->conn->error."".PHP_EOL;
            }
        break;
        }
    }  
    function statistcsSearcher($whats,$conditions,$table,$counting=false, $return=false){
        $format="SELECT ";
        foreach($whats as $what){
        $format.=$what.",";
        }
        $format=rtrim($format,',');
        //echo $format.PHP_EOL;
        $format.=" FROM ";
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
        $format.=$this->schemaName.".".$table;
        if(!empty($conditions)){
            foreach($conditions as $condition){
                $format.=" WHERE ".$condition;
                if ($this->conn->query($format) === FALSE) {
                    echo "SELECT: Error selecting: " . $this->conn->error.";".PHP_EOL;
                }
            }
        }
        if ($this->conn->query($format) === FALSE) {
            die ("SELECT: Error selecting: " . $this->conn->error.";".PHP_EOL);
        }
        $result = $this->conn->query($format);      
            while($row = $result->fetch_assoc()) {
                if($counting){
                    $count++;
                }
                if($return){
                   $res['object'][]=$row;
                }
            }
        
        if($counting&&$return||!$counting&&!$return){
            echo "BOTH".PHP_EOL;
            return $ar=array( 'Result'=>$res,'Count'=>$count);
        }
        if($counting){
            echo "COUNT".PHP_EOL;
            return $count;}
        if($return){
            echo "RETURN".PHP_EOL;
            return $res;}
    }
}