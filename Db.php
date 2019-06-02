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
        //Inserts Data into the database, first removes the unwanted characters
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
        //Then it checks where it will be inserted
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
                //Inserts the comments that come with the Data, this only happens when the data is a Post.
                foreach($data->comments as $comment){
                    $this->input("Comment",$comment,$data->id);
                }
        break;

        case "Comment":
            //kills the insert of new valeus if there's no PostId
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
    function statisticsSearcher($whats,$conditions,$table,$counting=false, $return=false){
        //Create a format that will Select what is asked, returns an Array or just a number if counting is true
        $format="SELECT ";
        foreach($whats as $what){
        $format.=$what.",";
        }
        $format=rtrim($format,',');
        $array=array();
        //echo $format.PHP_EOL;
        $format.=" FROM ";
        $innerJoin=false;
        
        /*
        What is the thing the user wishs to search (array)
        Conditions is an array with conditions (array)
        table is the name of the table (String)
        counting enables the return of the number of rows that we got from the select (bool)
        return enables the return of an array with the rows selected (bool)
        */
        $count=0; //Used to get the number of results
        $res=array(); // Used to get the results
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
        
        if($counting){
            //echo "COUNT".PHP_EOL;
            $array['Count']=$count;
        }
        if($return){
            //echo "RETURN".PHP_EOL;
            $array['Result']=$res;
        }
        return $array;
    }
}