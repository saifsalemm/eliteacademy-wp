<?php

function set_firestore_doc($id = null, $collection, $data)
{
    $updated_at = $id ? time() : '';
    $created_at = $id ? null : time();

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://setfirestoredoc-xd7yhazkuq-uc.a.run.app',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "auth": "dnfl34f8jwco84tb4ohrh39r32fqhb2oir0184nd1",
    "id": "' . $id . '",
    "collection": "' . $collection . '",
    "data": ' . $data . '
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
}
