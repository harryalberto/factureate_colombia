<?php
class DB_Sql {
  var $Host     = ""; // Hostname of our MySQL server.
  var $Database = ""; // Logical database name on that server.
  var $User     = ""; // User und Password for login.
  var $Password = "";

  var $Link_ID  = 0;  // Result of mysql_connect().
  var $Query_ID = 0;  // Result of most recent mysql_query().
  var $Record   = array();  // current mysql_fetch_array()-result.
  var $Row;           // current row number.
  var $NumRows  = 0;
  var $Errno    = 0;  // error state of query...
  var $Error    = "";
  var $Ok = 0;

  function decrip($text){
        $longitud = strlen($text);
        $cod = '';
        $dec = '';
     
        for ($i = 0; $i < $longitud; $i++){
            $pos = ($longitud - $i) * -1;
            $caracter = substr($text,$pos,1);
          
            if ($caracter == '-'){
               $valor = (($cod + 64 - 49) / 2) - 43;
               $dec .= chr($valor);
               $cod = '';
            }
            else $cod .= $caracter;
        }

        return $dec;
  }
  
  function connect() {
    if ( 0 == $this->Link_ID ) {
      $pencrip = $this->decrip($this->Password);
      $this->Link_ID = pg_connect("host=$this->Host dbname=$this->Database user=$this->User password=$pencrip") or die("Error al conectar:".pg_last_error());
    }
  }

  function query($Query_String) {
    //$this->connect();
    $this->Query_ID = pg_query($this->Link_ID,$Query_String);
    $this->Row  = 0;
    /*
    if (!$this->Query_ID) {
      $this->halt("Error en el SQL: ".$Query_String);
    }
    */
    return $this->Query_ID;
  }

  function next_record() {
    $this->Record = pg_fetch_object($this->Query_ID);
    $this->Row   += 1;
    //$stat = is_array($this->Record);
    $stat = $this->Record;
    /*if (!$stat) {
      $this->Query_ID = 0;
    }*/
    
    return $stat;
  }
  function nrows(){
    $qrows = pg_num_rows($this->Query_ID);
    return $qrows;
  }
  function close() {
	  //if ( 0 == $this->Link_ID ) {
			pg_close($this->Link_ID);
		//}
  }
  /////////////////////////////////////////////////////
  function halt($msg) {
    printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
    printf("<b>MySQL Error</b>: %s (%s)<br>\n", $this->Errno, $this->Error);
    die("Sesi�n detenida.");
  }

  function goon($mesg){
  	echo" Exito : ".$mesg;
  }
	function seek($pos) {
	    $status = mysqli_data_seek($this->Query_ID, $pos);
	    if ($status)
    	  $this->Row = $pos;
		 else
		 	echo "fallo";
    	return;
  	}
	function counts($idqry){
		$this->NumRows = mysqli_num_rows($idqry);
		return $this->NumRows;
	}
}
?>
