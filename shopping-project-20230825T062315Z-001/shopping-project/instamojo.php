<?php 
include 'config.php';

session_start();
$user = $_SESSION['username'];

$db = new Database();
$db->select('options','site_name',null,null,null,null);
$site_name = $db->getResult();


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://www.instamojo.com/api/1.1/payment-requests/');
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_HTTPHEADER,
            array("X-Api-Key: d82016f839e13cd0a79afc0ef5b288b3",
                  "X-Auth-Token: 3827881f669c11e8dad8a023fd1108c2"));
$payload = Array(
    'purpose' => 'Payment to '.$site_name[0]['site_name'],
    'amount' => $_POST['product_total'],
    // 'phone' => '',
    'buyer_name' => $user,
    'redirect_url' => $hostname.'/success.php',
    // 'send_email' => true,
    // 'webhook' => 'http://www.example.com/webhook/',
    // 'send_sms' => true,
    // 'email' => 'rainkapil@gmail.com',
    'allow_repeated_payments' => false
);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
$response = curl_exec($ch);
curl_close($ch);
$response = json_decode($response); 
 //echo '<pre>';
 //print_r($response);
//exit;
$_SESSION['TID'] = $response->payment_request->id;
$params1 = [
    'item_number' => $_POST['product_id'],
    'txn_id' => $response->payment_request->id,
    'payment_gross' => $_POST['product_total'],
    'payment_status' => 'credit',
];
$params2 = [
    'product_id' => $_POST['product_id'],
    'product_qty' => $_POST['product_qty'],
    'total_amount' => $_POST['product_total'],
    'product_user' => $_SESSION['user_id'],
    'order_date' => date('Y-m-d'),
    'pay_req_id' => $response->payment_request->id
];
$db = new Database();
$db->insert('payments',$params1);
$db->insert('order_products',$params2);
$db->getResult();

header('Location: '.$response->payment_request->longurl);

?>