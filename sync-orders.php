<?php
/**
 * User: noff
 * Date: 22/07/14
 * Time: 23:22

 * This is an example of mass export orders to REES46 procedure.
 *
 * Assumes you have tables in database:
 * orders:
 *   id:integer
 *   user_id:integer
 *   total_sum:integer
 *   created_at:datetime
 * order_items:
 *   order_id:integer
 *   item_id:integer
 *   price:integer
 *   amount:integer
 * items:
 *   id:integer
 *   price:integer
 *   category_id:integer
 *   is_available:boolean
 *
 */

// Edit these parameters according to your shop credentials
define('SHOP_ID', 'b6ef5e0003904ba8245eb7aac0c286');
define('SHOP_SECRET', '14fd926018b405cbb18c24b6724a5ad8');

// Here prepare your orders data from your database.
// In this example we just prepare array of data instead of database data.
// So you need to change this code according your shop architecture.
$orders = array(
	array(
		'id' => 'order3',
		'status' => 0
	),
	array(
		'id' => 'order4',
		'status' => 1
	)
);



// ** Prepare CURL object

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_URL, 'http://api.rees46.com/import/sync_orders');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// Split orders by 1000 per request
$chunks = array_chunk($orders, 1000);

foreach($chunks as $key => $chunk) {
	$data = array(
		'shop_id' => SHOP_ID,
		'shop_secret' => SHOP_SECRET,
		'orders' => $chunk
	);
	$body = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

	$response = curl_exec($ch);
	$response_info = curl_getinfo($ch);

	if($response_info['http_code'] < 200 || $response_info['http_code'] >= 300) {
		$data_as_string = var_export($data, true);
		$response_as_string = var_export($response_info, true);
		error_log("Error request \n\nData:\n'{$data_as_string}'\n\nResponse Info:\n{$response_as_string}\n\nResponse:\n'{$response_body->message}'");
		exit(1);
	} else {
		echo 'Chunk ' . ($key+1) . ' done. Response: ' . $response . PHP_EOL;
	}

}





// ** Close all connections and clean

curl_close($ch);

echo 'Done.' . PHP_EOL;