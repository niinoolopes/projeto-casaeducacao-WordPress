<?php
/*
template name: CRM - 1
*/
//ini_set('display_errors', 'on');
header('Content-Type: text/html; charset=utf-8');

$url_master = $_POST['returnURL'];
//$url_erro = "www.casaeducacao.com.br";
// $_authtoken_CRM = "23b487ce76862290292dbf1ea5bf889a";
$_authtoken_CRM = "b96b127847925b220e94157d2da26993";

$url_zoho_contact   = "https://crm.zoho.com/crm/private/json/Contacts/searchRecords?authtoken=" . $_authtoken_CRM . "&scope=crmapi&";
$url_zoho_curso     = "https://crm.zoho.com/crm/private/json/Products/searchRecords?authtoken=" . $_authtoken_CRM . "&scope=crmapi&";
$url_zoho_solution  = "https://crm.zoho.com/crm/private/json/Solutions/searchRecords?authtoken=" . $_authtoken_CRM . "&scope=crmapi&";
$url_ins_curso      = "https://crm.zoho.com/crm/private/json/Solutions/insertRecords";
$url_ins_task       = "https://crm.zoho.com/crm/private/json/Tasks/insertRecords";

/*Buscando os valores no HTML ao clicar em gravar*/
$nomecompleto       = explode(' ', trim($_POST['FirstName']));
$primeironome       = array_shift($nomecompleto);
$segundonome        = isset($_POST['LastName']) ? trim($_POST['LastName']) : implode(" ", $nomecompleto);
$mobile             = trim($_POST['Mobile']);
$email              = trim($_POST['Email']);
$description        = trim($_POST['Description']);
$curso              = trim($_POST['CONTACTCF5']);
$class              = trim($_POST['CONTACTCF6']);
$situaca            = trim($_POST['CONTACTCF7']);
$leadsource         = trim($_POST['LeadSource']);

//echo $curso;
$criteria_email = "criteria=" . urlencode(utf8_encode("(Email:" . $email . ")"));
$url_zoho         = $url_zoho_contact . $criteria_email;

$prossegue = false;
//echo ini_get('max_execution_time');
ini_set('max_execution_time', 90);
//conectando com o localhost - mysql
$conexao = mysqli_connect('vestaspsql.alest.com.br', 'casaedu_site', 'GhSRn5LdTv');
if (!$conexao){
    die ("Erro de conexão -> ".mysqli_error());
}

//conectando com a tabela do banco de dados
// $banco = mysql_select_db("casaedu_site", $conexao);
$banco = mysqli_select_db($conexao,"casaedu_site" );
if (!$banco){
    die ("Erro de conexão com banco de dados -> ".mysqli_error());
}


$checkn = "SELECT * FROM crm_zoho WHERE email = '$email' ORDER BY id DESC LIMIT 1";
$sqlcheckn = mysqli_query($checkn,$conexao);
$rowsn = mysqli_num_rows($sqlcheckn);

$query = "INSERT INTO `crm_zoho` ( `nome`, `sobrenome` , `email` , `telefone` , `mensagem` , `curso` , `data` , `id` )
VALUES ('$primeironome', '$segundonome', '$email', '$mobile', '$description', '$curso', NOW() , '')";

if ($rowsn == 0) {

    mysqli_query($query,$conexao);

    $novo = true;
}
else
{
        mysqli_query($query,$conexao);

        $checkp = "SELECT * FROM crm_zoho WHERE email = '$email' ORDER BY id ASC LIMIT 1";
        $sqlcheckatu = mysqli_query($checkp,$conexao);

        $return = array();
        while($row=mysqli_fetch_assoc($sqlcheckatu)){
            array_push($return,array('value'=>$row['data']));
        }
        $primeira = $return[0]['value'];


        $sqlcheckn = mysqli_query($checkn,$conexao);
        $agora = array();

        while($row=mysqli_fetch_assoc($sqlcheckn)){
            array_push($agora,array('value'=>$row['data']));
        }

        $atual = $agora[0]['value'];
        $diffseconds = strtotime($atual)-strtotime($primeira);;

        $novo = false;
}


if (!$novo)
{

    //verifica os 70 segundos
    if ($diffseconds <= 70){

        //break;
        $i = 1;
        while ($i <= round(((70-$diffseconds)/5)) && !$prossegue) {

            $i++;

            $result           = sendGet($url_zoho);
            $result = explode("\r\n\r\n", $result, 2);

            //echo $result[1];
            //break;

            if ($result[1] != null && !strpos($result[1], "nodata") && !strpos($result[1], "error")) {
                $prossegue = true;
                //echo $prossegue;
            }
            else
            {
                sleep(5);
            }

        }

    }
    else {

        $result           = sendGet($url_zoho);
        $result = explode("\r\n\r\n", $result, 2);

        //echo $result[1];
        //break;

        if ($result[1] != null && !strpos($result[1], "nodata") && !strpos($result[1], "error")) {
            $prossegue = true;
            //echo $prossegue;
        }
    }

}
else
{
    $result           = sendGet($url_zoho);
    $result = explode("\r\n\r\n", $result, 2);

    //echo $result[1];
    //break;

    if ($result[1] != null && !strpos($result[1], "nodata") && !strpos($result[1], "error")) {
        $prossegue = true;
        //echo $prossegue;
    }

}

if ($prossegue){
//if ($result[1] != null && !strpos($result[1], "nodata") && !strpos($result[1], "error")) {

    $dadoscontact = json_decode($result[1]);
    //echo $dadoscontact;
    //break;

    //Parser dados
    $ROW = $dadoscontact->response->result->Contacts->row;
    if (sizeof($ROW) == 1)
        $FL = $dadoscontact->response->result->Contacts->row->FL;
    elseif (sizeof($ROW) > 1)
        $FL = $dadoscontact->response->result->Contacts->row[0]->FL;

    $record = processa($FL);

    $idcontact    = $record['CONTACTID'];
    $ownerid = $record['SMOWNERID'];
    //echo $ownerid;

    //echo $idcontact;
    //echo $result[1];

    $criteria_contact = "criteria=" . urlencode(utf8_encode("(Contato_ID:" . $idcontact . ")"));
    $url_zoho         = $url_zoho_solution . $criteria_contact;
    //echo $url_zoho;
    $result           = sendGet($url_zoho);
    $result = explode("\r\n\r\n", $result, 2);
    //echo $result[1];

    if ($result[1] != null && !strpos($result[1], "nodata") && !strpos($result[1], "error")) {

        $dadoscurso = json_decode($result[1]);
        //echo $dadoscurso;
        //break;


        //echo $result[1];

        //Parser dados
        $ROW = $dadoscurso->response->result->Solutions->row;
        if (sizeof($ROW) == 1){
            $FL = $dadoscurso->response->result->Solutions->row->FL;

            $record = processa($FL);
            //echo $record;

            $courses = array ("course" => array("id" => $record['SOLUTIONID'], "name" => $record['Product Name'], "contactid" => $record['Contato_ID']));
            //print_r($courses);
        }
        elseif (sizeof($ROW) > 1){

            $courses = array();

            for ($x = 0; $x < sizeof($ROW); $x++) {

                $FL = $dadoscurso->response->result->Solutions->row[$x]->FL;
                $record = processa($FL);

                array_push($courses, array("id" => $record['SOLUTIONID'], "name" => $record['Product Name'], "contactid" => $record['Contato_ID']));
            }

            //print_r($courses);

        }

        $jatem = 0;
        //echo $curso;
        $descurso = explode(" - ",$curso,2);
        foreach ($courses as $one){     //($x = 0; $x < sizeof($courses); $x++) {
            //echo $one['name'];
            //echo "---";
            //echo $descurso[1];
            //echo strcmp($one['name'],$descurso[1]);
            if (strcmp($one['name'],$descurso[1])==0)
            {
                $jatem = 1;
                break;

            }
        }

        //echo $result[1];
        if($jatem == 1)
        {
            //echo "entrou task contact";
            //break;
            //criar tarefa apenas
            $result = addTaskContact($url_ins_task,$_authtoken_CRM,$ownerid,$curso,$idcontact,$description);
            $result   = explode("\r\n\r\n", $result, 2);
            $response = json_decode($result[1]);
            //echo $result[1];

            if (strpos($result[1], "Record(s) added successfully")) {

                $ROWIN = $response->response->result->recorddetail;
                if (sizeof($ROWIN) == 1)
                    $FLIN = $response->response->result->recorddetail->FL;
                elseif (sizeof($ROWIN) > 1)
                    $FLIN = $response->response->result->recorddetail[0]->FL;

                $recordIN = processa($FLIN);

                $recordId = $recordIN["Id"];

                header("Location: ".$url_master);

            } else {
                header("Location: ".$url_master."?error");
                //echo $result[1];
            }

        }
        else
        {
            //echo "entrou add curso";
            //break;
            //adiciona curso e tarefa
            $result = addCursoContact($url_ins_curso,$url_zoho_curso,$_authtoken_CRM,$ownerid,$descurso[1],$descurso[0],$idcontact);
            $result   = explode("\r\n\r\n", $result, 2);
            //echo $result[1];
            $response = json_decode($result[1]);

            if (strpos($result[1], "Record(s) added successfully")) {

                $ROWIN = $response->response->result->recorddetail;
                if (sizeof($ROWIN) == 1)
                    $FLIN = $response->response->result->recorddetail->FL;
                elseif (sizeof($ROWIN) > 1)
                    $FLIN = $response->response->result->recorddetail[0]->FL;

                $recordIN = processa($FLIN);

                $recordId = $recordIN["Id"];

                //cria tarefa
                $result = addTaskContact($url_ins_task,$_authtoken_CRM,$ownerid,$curso,$idcontact,$description);
                $result   = explode("\r\n\r\n", $result, 2);
                $response = json_decode($result[1]);

                if (strpos($result[1], "Record(s) added successfully")) {

                    $ROWIN = $response->response->result->recorddetail;
                    if (sizeof($ROWIN) == 1)
                        $FLIN = $response->response->result->recorddetail->FL;
                    elseif (sizeof($ROWIN) > 1)
                        $FLIN = $response->response->result->recorddetail[0]->FL;

                    $recordIN = processa($FLIN);

                    $recordId = $recordIN["Id"];

                    header("Location: ".$url_master);

                } else {
                    header("Location: ".$url_master."?error");
                    //echo $result[1];
                }

            } else {
                header("Location: ".$url_master."?error");
                //echo $result[1];
            }

        }
    }
    else
    {
        $descurso = explode(" - ",$curso,2);
        $result = addCursoContact($url_ins_curso,$url_zoho_curso,$_authtoken_CRM,$ownerid,$descurso[1],$descurso[0],$idcontact);
        $result   = explode("\r\n\r\n", $result, 2);
        $response = json_decode($result[1]);

        if (strpos($result[1], "Record(s) added successfully")) {

            $ROWIN = $response->response->result->recorddetail;
            if (sizeof($ROWIN) == 1)
                $FLIN = $response->response->result->recorddetail->FL;
            elseif (sizeof($ROWIN) > 1)
                $FLIN = $response->response->result->recorddetail[0]->FL;

            $recordIN = processa($FLIN);

            $recordId = $recordIN["Id"];

            //cria tarefa
            $result = addTaskContact($url_ins_task,$_authtoken_CRM,$ownerid,$curso,$idcontact,$description);
            $result   = explode("\r\n\r\n", $result, 2);
            $response = json_decode($result[1]);

            if (strpos($result[1], "Record(s) added successfully")) {

                $ROWIN = $response->response->result->recorddetail;
                if (sizeof($ROWIN) == 1)
                    $FLIN = $response->response->result->recorddetail->FL;
                elseif (sizeof($ROWIN) > 1)
                    $FLIN = $response->response->result->recorddetail[0]->FL;

                $recordIN = processa($FLIN);

                $recordId = $recordIN["Id"];

                header("Location: ".$url_master);

            } else {
                header("Location: ".$url_master."?error");
                //echo $result[1];
            }

        } else {
            header("Location: ".$url_master."?error");
            //echo $result[1];
        }

    }

}
else
{
    //sleep(5);
    $form = geraForm($primeironome,$segundonome,$mobile,$email,$description,$leadsource, $class, $situaca,$curso,$url_master);
    echo $form;
    //header("Location: ".$url_master);
}



/*ADICIONA TAREFA*/
function addTaskContact($url,$_authtoken_CRM,$owid,$cur,$id,$msg)
{
    //echo $url;

    $dataCadastro = date("m/d/Y");
    $subject = '[NOVO] Entrar em Contato ('.$cur.')';

    //echo $subject;

    $xmlData = '<Tasks><row no="1">';
    $xmlData .= '<FL val="SMOWNERID">' . $owid . '</FL>';
    $xmlData .= '<FL val="Subject">' . $subject . '</FL>';
    $xmlData .= '<FL val="Due Date">' . $dataCadastro . '</FL>';
    //$xmlData .= '<FL val="Status">'.utf8_encode('Não Iniciado').'</FL>';
    $xmlData .= '<FL val="Status">Em Aberto</FL>';
    $xmlData .= '<FL val="CONTACTID">' . $id . '</FL>';
    //$xmlData .= '<FL val="RELATEDTOID">' . $id . '</FL>';
    $xmlData .= '<FL val="SEMODULE">Contacts</FL>';
    $xmlData .= '<FL val="Priority">Alta</FL>';
    $xmlData .= '<FL val="Send Notification Email">true</FL>';
    $xmlData .= '<FL val="Description">'.$msg.'</FL>';
    $xmlData .= '</row></Tasks>';

    //parametros default
    $curl_req = curl_init();

    curl_setopt($curl_req, CURLOPT_URL, $url);
    curl_setopt($curl_req, CURLOPT_POST, 1);
    curl_setopt($curl_req, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curl_req, CURLOPT_HEADER, 1);
    curl_setopt($curl_req, CURLOPT_VERBOSE, 1);//standard i/o streams
    curl_setopt($curl_req, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl_req, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, 0);

    //montando par de campo do formulário HTML valor.
    //$post = "newFormat=1&authtoken=".$_authtoken_CRM."&scope=crmapi&wfTrigger=true&xmlData=".$xmlData;

    $post = array(
        "newFormat" => "1",
        "authtoken" => $_authtoken_CRM,
        "scope" => "crmapi",
        "wfTrigger" => "true",
        "xmlData" => $xmlData
    );


    //echo $xmlData;

    //Inserindo no Zoho CRM
    curl_setopt($curl_req, CURLOPT_POSTFIELDS, $post);
    //echo $curl_req;
    $result = curl_exec($curl_req);
    //echo $result;
    curl_close($curl_req);

    return $result;


}

/*ADICIONA Curso*/
function addCursoContact($url,$urlcurso,$_authtoken_CRM,$owid,$cur,$tp,$id)
{

    //echo "entrou funcao add curso";
    //break;
    //$criteria_curso = "criteria=" . str_replace(" ","%20","((Product Name:" . $cur . ")AND(Cursos:". $tp .")AND(Product Active:true))");
    //$url_zoho         = $urlcurso . $criteria_curso;
    //echo $url_zoho;
    //$result           = sendGet($url_zoho);
    //echo $result;
    //break;

    //$criteria_curso = str_replace(" ","%20","((Product Name:" . $cur . ")AND(Cursos:". $tp .")AND(Product Active:true))");
    $criteria_curso = "((Product Name:" . $cur . ")AND(Cursos:". $tp .")AND(Product Active:true))";
    $result = sendPost($urlcurso,$criteria_curso,$_authtoken_CRM);

    $return = $result;
    $result = explode("\r\n\r\n", $result, 2);

    //echo $result[1];
    //break;

    if ($result[1] != null && !strpos($result[1], "nodata") && !strpos($result[1], "error")) {

        $dadoscourso = json_decode($result[1]);

        //Parser dados
        $ROW = $dadoscourso->response->result->Products->row;
        if (sizeof($ROW) == 1)
            $FL = $dadoscourso->response->result->Products->row->FL;
        elseif (sizeof($ROW) > 1)
            $FL = $dadoscourso->response->result->Products->row[0]->FL;

        $record = processa($FL);

        $idcurso    = $record['PRODUCTID'];

        //echo $idcurso;

        $xmlData = '<Solutions><row no="1">';
        $xmlData .= '<FL val="SMOWNERID">' . $owid . '</FL>';
        $xmlData .= '<FL val="PRODUCTID">' . $idcurso . '</FL>';
        $xmlData .= '<FL val="Status">'.utf8_encode('Interesse Explicito').'</FL>';
        $xmlData .= '<FL val="Contato_ID">' . $id . '</FL>';
        $xmlData .= '<FL val="Solution Title">' . $cur . '</FL>';
        $xmlData .= '</row></Solutions>';

        //parametros default
        $curl_req = curl_init();

        curl_setopt($curl_req, CURLOPT_URL, $url);
        curl_setopt($curl_req, CURLOPT_POST, 1);
        curl_setopt($curl_req, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl_req, CURLOPT_HEADER, 1);
        curl_setopt($curl_req, CURLOPT_VERBOSE, 1);//standard i/o streams
        curl_setopt($curl_req, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_req, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, 0);

        //montando par de campo do formulário HTML valor.
        //$post = "newFormat=1&authtoken=".$_authtoken_CRM."&scope=crmapi&wfTrigger=true&xmlData=".$xmlData;

        $post = array(
            "newFormat" => "1",
            "authtoken" => $_authtoken_CRM,
            "scope" => "crmapi",
            "wfTrigger" => "true",
            "xmlData" => $xmlData
        );


        //echo $xmlData;

        //Inserindo no Zoho CRM
        curl_setopt($curl_req, CURLOPT_POSTFIELDS, $post);
        //echo $curl_req;
        $result = curl_exec($curl_req);
        //echo $result;
        curl_close($curl_req);
        $return = $result;

        return $return;

    }
    else
    {
        return $return;
    }


}


/*ENVIA REQUISIÇÃO VIA GET  */
function sendGet($url)
{

    $curl_request = curl_init();
    curl_setopt($curl_request, CURLOPT_URL, $url);
    curl_setopt($curl_request, CURLOPT_HTTPGET, true);
    curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curl_request, CURLOPT_HEADER, 1);
    curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);
    $result = curl_exec($curl_request);
    curl_close($curl_request);

    return $result;

}


/*ENVIA REQUISIÇÃO VIA GET  */
function sendPost($url,$criteria,$_authtoken_CRM)
{

        $curl_req = curl_init();
        //echo $url;
        curl_setopt($curl_req, CURLOPT_URL, $url);
        curl_setopt($curl_req, CURLOPT_POST, 1);
        curl_setopt($curl_req, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl_req, CURLOPT_HEADER, 1);
        curl_setopt($curl_req, CURLOPT_VERBOSE, 1);//standard i/o streams
        curl_setopt($curl_req, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_req, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, 0);

        $post = array(
            "authtoken" => $_authtoken_CRM,
            "scope" => "crmapi",
            "criteria" => $criteria
        );

        //echo $xmlData;

        //Inserindo no Zoho CRM
        curl_setopt($curl_req, CURLOPT_POSTFIELDS, $post);
        //print_r($post);
        //print_r($curl_req);
        //break;
        $result = curl_exec($curl_req);
        //echo $result;
        curl_close($curl_req);


    return $result;

}

function processa($FL)
{

    foreach ($FL as $field) {

        $key          = $field->val;
        $value        = $field->content;
        $record[$key] = $value;
        //echo $key;
        //echo $value;
    }
    return $record;
}

function getTextBetweenTags($string, $tagname, $tagfim)
{
    $pattern = "/<$tagname>(.*?)<\/$tagfim>/";
    preg_match($pattern, $string, $matches);
    return $matches[1];
}


function geraForm($primeironome,$segundonome,$mobile,$email,$description,$leadsource, $class, $situaca,$curso,$urlreturn)
{

    $html = "<!DOCTYPE html>";
    $html .= "<html>";
    $html .= "<head>";
    $html .= "<meta charset='utf-8' />";
    $html .= "<title>Formulário</title>";
    $html .= "<script type='text/javascript' src='http://code.jquery.com/jquery-1.9.0.js'></script>";
    $html .= "<link rel='stylesheet' href='http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css'>";
    $html .= "<script src='http://code.jquery.com/jquery-1.10.2.js'></script>";
    $html .= "<script src='http://code.jquery.com/ui/1.11.2/jquery-ui.js'></script>";

    $html .= "</head>";
    $html .= "<body>";

    $html .= "<div class='formulario' style='display:none;'>";
    $html .= "<form method='post' enctype='multipart/form-data' name='WebToContacts2303993000001542444' id='WebToContacts2303993000001542444' action='https://crm.zoho.com/crm/WebToContactForm'>";

    $html .= "   <!-- Do not remove this code. -->";
    $html .= "  <input type='text' style='display:none;' name='xnQsjsdp' value='ac7c6674f75b655a8c6c8b225d5e87c54335a8dc9b0ca29ccb648f7a01050132'/>";
    $html .= "  <input type='hidden' name='zc_gad' id='zc_gad' value=''/>";
    $html .= "  <input type='text' style='display:none;' name='xmIwtLD' value='d0d8ee3d9ac30c4cc6a5eb911bf84ac07b5c80779c0cb228cb44542abf52d732'/>";
    $html .= "  <input type='text' style='display:none;'  name='actionType' value='Q29udGFjdHM='/>";

    $html .= "  <input type='text' style='display:none;' name='returnURL' value='".$urlreturn."' /> ";
    $html .= "   <!-- Do not remove this code. -->";

    $html .= "  <style>";
    $html .= "      tr , td {";
    $html .= "          padding:6px;";
    $html .= "          border-spacing:0px;";
    $html .= "          border-width:0px;";
    $html .= "          }";
    $html .= "  </style>";
    $html .= "  <table style='width:600px;background-color:white;color:black'>";

    $html .= "  <tr><td colspan='2' style='text-align:left;color:black;font-family:Arial;font-size:14px;'><strong>Casa Educa&ccedil;&atilde;o</strong></td></tr>";

    $html .= "      <tr style='display:none;' ><td style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:50%'>Fonte de Candidato</td><td style='width:250px;'>";
    $html .= "          <select style='width:250px;' name='Lead Source'>";
    $html .= "          <option selected value='".str_replace(" ","&#x20;",htmlentities($leadsource, ENT_COMPAT, 'UTF-8'))."'>".htmlentities($leadsource, ENT_COMPAT, 'UTF-8')."</option>";
    $html .= "          </select></td></tr>";


    $html .= "  <tr style='display:none;' ><td style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:50%'>Cursos de Interesse</td><td style='width:250px;'>";
    $html .= "      <select style='width:250px;' name='CONTACTCF5'>";
    $html .= "          <option selected value='".str_replace(" ","&#x20;",htmlentities($curso, ENT_COMPAT, 'UTF-8'))."'>".htmlentities($curso, ENT_COMPAT, 'UTF-8')."</option>";
    $html .= "      </select></td></tr>";

    $html .= "  <tr><td  style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:200px;'>Primeiro Nome<span style='color:red;'>*</span></td><td style='width:250px;' ><input type='text' style='width:250px;'  maxlength='40' name='First Name' value='".str_replace(" ","&#x20;",htmlentities($primeironome, ENT_COMPAT, 'UTF-8'))."'/></td><td><span>Primeiro nome</span></td>";

    $html .= "  <tr><td  style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:200px;'>Sobrenome<span style='color:red;'>*</span></td><td style='width:250px;' ><input type='text' style='width:250px;'  maxlength='80' name='Last Name' value='".str_replace(" ","&#x20;",htmlentities($segundonome, ENT_COMPAT, 'UTF-8'))."'/></td><td><span>Sobrenome</span></td>";

    $html .= "  <tr><td  style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:200px;'>Celular<span style='color:red;'>*</span></td><td style='width:250px;' ><input type='text' style='width:250px;'  maxlength='30' name='Mobile' value='".$mobile."'/></td><td><span>DDD &#x2b; Telefone</span></td>";

    $html .= "  <tr><td  style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:200px;'>E-mail<span style='color:red;'>*</span></td><td style='width:250px;' ><input type='text' style='width:250px;'  maxlength='100' name='Email' value='".$email."'/></td><td><span>Email</span></td>";

    $html .= "  <tr><td  style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:200px;'>Descri&ccedil;&atilde;o </td><td> <textarea name='Description' maxlength='1000' style='width:250px;'>".str_replace(' ','&#x20;',htmlentities($description, ENT_COMPAT, 'UTF-8'))."</textarea></td><td><span>Ol&aacute;, gostaria de receber mais informa&ccedil;&otilde;es sobre o curso.</span></td>";


    $html .= "      <tr style='display:none;' ><td style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:50%'>Classifica&ccedil;&atilde;o</td><td style='width:250px;'>";
    $html .= "          <select style='width:250px;' name='CONTACTCF6'>";
    $html .= "          <option selected value='".str_replace(" ","&#x20;",htmlentities($class, ENT_COMPAT, 'UTF-8'))."'>".htmlentities($class, ENT_COMPAT, 'UTF-8')."</option>";
    $html .= "          </select></td></tr>";

    $html .= "      <tr style='display:none;' ><td style='nowrap:nowrap;text-align:left;font-size:12px;font-family:Arial;width:50%'>Situa&ccedil;&atilde;o</td><td style='width:250px;'>";
    $html .= "          <select style='width:250px;' name='CONTACTCF7'>";
    $html .= "          <option selected value='".str_replace(" ","&#x20;",htmlentities($situaca, ENT_COMPAT, 'UTF-8'))."'>".htmlentities($situaca, ENT_COMPAT, 'UTF-8')."</option>";
    $html .= "          </select></td></tr>";


    $html .= "  <tr><td colspan='2' style='text-align:center; padding-top:15px;'>";
    $html .= "      <input style='font-size:12px;color:#131307' type='submit' value='Submeter' />";
    $html .= "      <input type='reset' style='font-size:12px;color:#131307' value='Reiniciar' />";
    $html .= "      </td>";
    $html .= "  </tr>";
    $html .= "   </table>";
    $html .= "  <script>";
    $html .= "      $(document).ready(function(){";
    $html .= "           $('#WebToContacts2303993000001542444').submit();";
    $html .= "      });";
    $html .= "  </script>";

    $html .= "  </form>";
    $html .= "  </div>";

    $html .= "</body>";
    $html .= "</html>";


    return utf8_encode($html);

}


?>
