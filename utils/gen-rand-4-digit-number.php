<?php

function rand4Digit()
{
    // Generate a random integer between 1000 and 9999 (inclusive)
    $randomInt = rand(1000, 9999);

    // Convert the integer to a string
    $randomNumberString = strval($randomInt);

    // Pad with leading zeros if necessary to ensure 4 digits
    return str_pad($randomNumberString, 4, '0', STR_PAD_LEFT);
}
