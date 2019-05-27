?php
/**
 * Created by PhpStorm.
 * User: research
 * Date: 2018-12-11
 * Time: 12:22
 */

/*
 * em 2018-12-11 escreviamos
 * MYSQLI_REPORT_ALL
 * mas tal opção provoca exceções de runtime em qualquer
 * operação errada com mysqli
 * o que nos impede de capturar os erros, da forma que temos
 * programada
 *
 * MYSQLI_REPORT_ERROR
 */
mysqli_report(MYSQLI_REPORT_ERROR);

class MemorizadorBaseadoEmBD
{
    const ADMISSIBLE_ERRORS = [
        0, //no error
        1007 //schema already exists
    ];

    const DEFAULT_HOST = "localhost";
    const DEFAULT_USER = "test";
    const DEFAULT_PASS = "1234";
    const DEFAULT_PORT = 3306;

    private $mHost;
    private $mUser;
    private $mPass;
    private $mSchema;
    private $mPort;

    private $mDB;
    public function getDB(){return $this->mDB;}

    //logging dos códigos de erro das operações sobre a BD
    private $mErrors;
    public function getErrors(){return $this->mErrors;}
    //logging das mensagens correspondentes aos códigos de erro
    private $mErrorMsgs;
    public function getErrorMsgs(){return $this->mErrorMsgs;}

    const FIELD_ID = "id";
    const FIELD_CONTENT = "content";
    const FIELD_ENTRYDATE = "entryDate";

    const TABLE_NAME = "contents";

    //CREATE SCHEMA `xpto` ;
    /*
     * para símbolos de muitas linguagens e emojis
     * são precisos até 4 bytes para 1 único char
     */
    //const CREATE_SCHEMA = "create schema `%s` DEFAULT CHARACTER SET utf8mb4";
    const CREATE_SCHEMA = "create schema `%s`";
    const CREATE_TABLE = "create table if not exists `%s`.`%s` (
      `%s` int not null auto_increment,
      `%s` text null,
      `%s` datetime not null,
    primary key (`%s`) )";

    public function __construct(
        $pSchema
    )
    {
        $this->mHost = MemorizadorBaseadoEmBD::DEFAULT_HOST;
        $this->mUser = MemorizadorBaseadoEmBD::DEFAULT_USER;
        $this->mPass = MemorizadorBaseadoEmBD::DEFAULT_PASS;
        $this->mSchema = $pSchema;
        $this->mPort = MemorizadorBaseadoEmBD::DEFAULT_PORT;

        $this->mDB = mysqli_connect(
            $this->mHost,
            $this->mUser,
            $this->mPass
        );
        $this->mErrors = [];
        $this->mErrorMsgs = [];

        $e = mysqli_connect_errno();
        $eM = mysqli_connect_error();
        $this->mErrors[] = $e;
        $this->mErrorMsgs[] = $eM;

        /*
        if ($this->mDB!==false){
            $this->install();
        }//if
        */
    }//__construct

    public function install(){
        $ret = true;
        if ($this->mDB!==false){
            $installProcedure =
                $this->getInstallProcedure();
            //executar o procedimento de instalação

            foreach ($installProcedure as $i){
                $queryResult =
                    $this->mDB->query($i);

                $e = mysqli_errno($this->mDB);
                $eM = mysqli_error($this->mDB);

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

    public function close(){
        if ($this->mDB!==false){
            $this->mDB->close();
        }
    }//close

    public function indexOrIndexesOfData(
        $pData,
        $pStrict = false
    ){
        $bCheck0 = $this->mDB;
        if($bCheck0){
            $pData = mysqli_real_escape_string(
                $this->mDB,
                $pData
            );

            $pData = $pStrict ? $pData : "%$pData%";

            $queryFormat =
                $pStrict
                ?
                "select `%s` from `%s`.`%s` where `%s`='%s'"
                :
                "select `%s` from `%s`.`%s` where `%s` like '%s'";
                ;

            $query = sprintf(
                $queryFormat,
                MemorizadorBaseadoEmBD::FIELD_ID,
                $this->mSchema,
                MemorizadorBaseadoEmBD::TABLE_NAME,
                MemorizadorBaseadoEmBD::FIELD_CONTENT,
                $pData
            );

            $queryResult = $this->mDB->query($query);
            $e = mysqli_errno($this->mDB);
            $eM = mysqli_error($this->mDB);
            $this->mErrors[] = $e;
            $this->mErrorMsgs[] = $eM;

            echo $this->errorToString(1);

            if ($queryResult!==false){
                /*
                 * [
                 * 0 => ["id"=>?, "contents"=>??, "entryDate"=>?]
                 * 1 => ["id"=>?, "contents"=>??, "entryDate"=>?]
                 * ]
                 *
                 */
                $results =
                    mysqli_fetch_all(
                        $queryResult,
                        MYSQLI_ASSOC
                    );

                $iHowManyResults =
                    mysqli_num_rows($queryResult);

                $ret = [];
                foreach ($results as $r){
                    $ret[] = $r[MemorizadorBaseadoEmBD::FIELD_ID];
                }//foreach select result
                return $ret;
            }//query bem sucedida
        }//we have database handler
        return false;
    }//indexOrIndexesOfData

    public function insertData(
        $pData
    ){
        $bCheck0 = $this->mDB;
        if ($bCheck0){
            $pData = mysqli_real_escape_string(
                $this->mDB,
                $pData
            );
            $queryFormat =
            "insert into `%s`.`%s` values (null, '%s', '%s')";

            $strNow = date("Y-m-d H:i:s"); //2018-12-12 11:58:00
            $query = sprintf(
                $queryFormat,
                $this->mSchema,
                MemorizadorBaseadoEmBD::TABLE_NAME,
                $pData,
                $strNow
            );
            /*
             * insert
             * into
             * `aca1812`.`contents` values
             * (null, 'a', '2018-12-12- 12:01:02")
             */

            //true on success and false on failure
            $queryResult = $this->mDB->query($query);
            $e = mysqli_errno($this->mDB);
            $eM = mysqli_error($this->mDB);
            $this->mErrors[] = $e;
            $this->mErrorMsgs[] = $eM;

            echo $this->errorToString(1);

            return $queryResult;
        }//if we have a database handler
        return false; //no DB pointer, no operation
    }//insertData
}//MemorizadorBaseadoEmBD

function teste01(){
    $o = new MemorizadorBaseadoEmBD("schema_aca1812");
    $ok = $o->install();
    echo $ok ? "instalou ok" : "panic!";

    $o->close();
}//teste01

function teste02(){
    $o = new MemorizadorBaseadoEmBD("schema_aca1812");
    //$okInstall = $o->install();
    $okInsert = $o->insertData("sunny outside");
    echo $okInsert ? "inseriu" : "ooopsps";
    $o->close();
}

function teste03(){
    $o = new MemorizadorBaseadoEmBD("schema_aca1812");
    $recordsIndexes = $o->indexOrIndexesOfData(" out");
    $iHowMany = count($recordsIndexes);
    echo "$iHowMany satisfy the query";
    $o->close();
}

//teste01();
//teste02();
teste03();