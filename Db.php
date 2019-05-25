<?php

class Db{
    private $servername;
    private $username;
    private $password;
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
    $this->conn = mysqli_connect($this->servername, $this->username, $this->password);
    // Check connection
    if ($this->conn->connect_error) {
        die("Connection failed: " . $this->conn->connect_error."".PHP_EOL);
    }
    echo "Connected successfully".PHP_EOL;
    install();
    echo "also";
    $sql = "CREATE DATABASE IF NOT EXISTS test;";
    if ($this->conn->query($sql) === FALSE) {
        echo "Error creating database: " . $this->conn->error."".PHP_EOL;
    }
    $sql = "CREATE TABLE IF NOT EXISTS Posts (
        PostId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        Title VARCHAR(30) NOT NULL,
        Body VARCHAR(999) NOT NULL,
        Score VARCHAR(50) NOT NULL,
        RedditorId VARCHAR(30) NOT NULL,
        Awards VARCHAR(50) NOT NULL,
        Created DATETIME(YYYY-MM-DD hh:mm:ss) NOT NULL,
        Subreddit VARCHAR(50) NOT NULL,
        NumComments INT(6) NOT NULL,
        reg_date TIMESTAMP
        )";
        
    if ($this->conn->query($sql) === FALSE) {
            echo "POSTS: Error creating table: " . $this->conn->error."".PHP_EOL;
    }

    $sql = "CREATE TABLE IF NOT EXISTS Comments (
            CommentsId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Awards VARCHAR(50) NOT NULL,
            Score VARCHAR(50) NOT NULL,
            Replies INT(6) NOT NULL,
            Redditor VARCHAR(30) NOT NULL,
            Body VARCHAR(999) NOT NULL,
            Created DATETIME(YYYY-MM-DD hh:mm:ss) NOT NULL,
            PostId INT(6),
            FOREIGN KEY (PostId) REFERENCES Posts(PostId)
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
            $sql = "INSERT INTO Posts("
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
            $sql="SELECT PostId FROM Posts WHERE Title='".$data->title."'";
            $id = $this->conn->query($sql);
            foreach($data->comments as $comment){
                input("Comment",$comment,$id);
            }
    break;

    case "Comment":
        if($id===null){
            return FALSE;
        }
        $sql = "INSERT INTO Comments("
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
public function install(){
    $ret = true;
    echo "INSTALL";

    if ($this->conn!==false){
        $installProcedure =
            $this->getInstallProcedure();
        //executar o procedimento de instalação

        foreach ($installProcedure as $i){
            $queryResult =
                $this->conn->query($i);

            $e = mysqli_errno($this->conn);
            $eM = mysqli_error($this->conn);

            $bAdmissibleError =
                array_search(
                    $e,
                    MemorizadorBaseadoEmBD::ADMISSIBLE_ERRORS
                )!==false;

            $ret =
                $ret
                &&
                ($queryResult||$bAdmissibleError);

            $this->mErrors[] = $e;
            $this->mErrorMsgs[] = $eM;

            /*
            $strMsg = sprintf(
                "st: %s\ncode: %d\nmsg: %s\n",
                $i,
                $e,
                $eM
            );
            echo $strMsg;
            */
        }//foreach
    }//if
    echo $this->errorToString();
    return $ret;
}//install    
public function getInstallProcedure(){
    $installProcedure = [];

    $installProcedure[] =
        sprintf(
            MemorizadorBaseadoEmBD::CREATE_SCHEMA,
            $this->mSchema
        );

    $installProcedure[] =
        sprintf(
            MemorizadorBaseadoEmBD::CREATE_TABLE,
            $this->mSchema,
            MemorizadorBaseadoEmBD::TABLE_NAME,
            MemorizadorBaseadoEmBD::FIELD_ID,
            MemorizadorBaseadoEmBD::FIELD_CONTENT,
            MemorizadorBaseadoEmBD::FIELD_ENTRYDATE,
            MemorizadorBaseadoEmBD::FIELD_ID
        );

    return $installProcedure;
}//getInstallProcedure
public function errorToString(
    $pHowMany = false
){
    $pHowMany = $pHowMany===false ?
        count($this->mErrors)
        :
        $pHowMany;

    $ret = "";

    $iHowManyErrors = count($this->mErrors);
    for(
        $i=$iHowManyErrors-1, $counter=0; //inits
        /*$i>=0*/
        $counter<$pHowMany; //exp continuidade
        $i-- , $counter++ //updates
    ){
        $msg = sprintf(
            "error code: %d%serror msg: %s%s",
            $this->mErrors[$i],
            PHP_EOL,
            $this->mErrorMsgs[$i],
            PHP_EOL
        );
        $ret.=$msg;
    }//for

    return $ret;
}//errorToString    
} 