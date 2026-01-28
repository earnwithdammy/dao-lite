<?php

/**
 * Verify a Solana transaction:
 * - sent FROM treasury wallet
 * - sent TO recipient wallet
 * - amount >= expected amount
 */
function verifySolanaTx($txHash, $expectedFrom, $expectedTo, $expectedAmount)
{
    $payload = json_encode([
        "jsonrpc" => "2.0",
        "id"      => 1,
        "method"  => "getTransaction",
        "params"  => [
            $txHash,
            [
                "encoding" => "jsonParsed",
                "maxSupportedTransactionVersion" => 0
            ]
        ]
    ]);

    $ch = curl_init("https://api.mainnet-beta.solana.com");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 15
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        return false;
    }

    $data = json_decode($response, true);

    if (!isset($data['result']['transaction'], $data['result']['meta'])) {
        return false;
    }

    $tx      = $data['result'];
    $message = $tx['transaction']['message'];
    $meta    = $tx['meta'];

    if (!isset($message['instructions'])) {
        return false;
    }

    $totalSent = 0;
    $foundFrom = false;
    $foundTo   = false;

    foreach ($message['instructions'] as $inst) {
        if (
            isset($inst['parsed']['type']) &&
            $inst['parsed']['type'] === 'transfer'
        ) {
            $info = $inst['parsed']['info'];

            if (
                isset($info['source'], $info['destination'], $info['lamports'])
            ) {
                if ($info['source'] === $expectedFrom) {
                    $foundFrom = true;
                }

                if ($info['destination'] === $expectedTo) {
                    $foundTo = true;
                    $totalSent += $info['lamports'] / 1_000_000_000;
                }
            }
        }
    }

    if (!$foundFrom || !$foundTo) {
        return false;
    }

    if ($totalSent < $expectedAmount) {
        return false;
    }

    return true;
}