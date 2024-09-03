<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

##################### db connection start ##################### 
$servername = "localhost";
$username = "root";
// $password = "Kent338621";
$password = "";
$dbname = "kentdb";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
##################### db connection end ##################### 




##################### get payload data start ##################### 
$query = "SELECT payload FROM kentdb.shopifyorderplaced WHERE orderid = ?";
$stmt = $conn->prepare($query);
$orderid = 1458; 
$stmt->bind_param("i", $orderid); 
$stmt->execute();
$result = $stmt->get_result();

// Fetching all rows
$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row['payload'];
}

// Output payload data
foreach ($rows as $payload) {
    echo "Payload: " . $payload . "<br>";
    $data = json_decode($payload, true);

    if ($data !== null) {
        // Check if the 'order_status_url' field exists
        if (isset($data['order_status_url'])) {
            $order_status_url = $data['order_status_url'];
            echo "Order Status URL: " . $order_status_url;
        } else {
            echo "Order Status URL not found in payload.";
        }
    } 

}


##################### send sms start ##################### 



######################### sms test form test template ##########end

function check_template($mobile, $OrderId, $name,$order_status_url) {
    if(isset($order_status_url)){
        
                $url = "https://fromkent.com/index.php?src=manual&url=" . $order_status_url ;

                $ch = curl_init();   
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 180);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
                    
                    $headers = array();
                    $headers[] = 'Accept:application/json';
                    $headers[] = 'Content-type:application/json';
                    
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);   
                    $data = curl_exec($ch);
                    if (empty($data) OR (curl_getinfo($ch, CURLINFO_HTTP_CODE != 200))) {
                    $data = FALSE;
                    }
                    curl_close($ch);
                    $result =  json_decode($data, TRUE);
                    if(isset($result['url'])){
                        $url = $result['url'];
                        $txt_sms = "
                        Hello, {$name}. Thanks for purchasing from Kuhl Fans. The order with id {$OrderId} has been created successfully. Opt out: { $url} \n\nBest Regards, \nKENT
                        ";
                        $txt_sms = trim(str_replace(' ', '%20', $txt_sms));
                        $post_string = "pcode=KENT&acode=KENT-PUSH&pin=kpt56&mnumber=".$mobile."&message=".$txt_sms;
                        $opts = array(
                            'http' => array(
                                'method' => "POST",
                                'header' => "Content-Type: application/x-www-form-urlencoded",
                                'content'=> $post_string
                            )
                        );
                        $context = stream_context_create($opts);
                        $result = file_get_contents('http://japi.instaalerts.zone/failsafe/HttpPublishLink', false, $context);
                        return $result;
                        

                    }
    }
  
}




// $stmt = $conn->prepare("INSERT INTO shopifyorderplaced (payload, placeid, orderid, mobileno, username, smsstatus,companyname) VALUES (?, ?, ?, ?, ?, ?,?)");

// $stmt->bind_param("sssssss", $payload, $cart_token, $OrderId, $mobile, $name, $smsstatus,$company_name);

// $stmt->execute();
// if ($stmt->affected_rows > 0) {
//     echo 'Success: Data inserted successfully.';
// } else {
//     echo 'Error: Data insertion failed.';
// }
$stmt->close();

$conn->close();
