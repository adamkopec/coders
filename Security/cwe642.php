<?php
//CWE-642
if (isset($_COOKIE[['autologin'])) {
    $loginData = unserialize($_COOKIE['autologin']);

    if ($loginData['token'] == getDbToken($loginData['user'])) {
        echo "Welcome back, {$loginData['user']}";
    } else {
        echo "Autologin failed";
    }
}

//bezpieczne? tokeny i inne takie...

//ciacho prawidłowe
a:2{s:4:"user";i:123;s:3:"key";s:32:"md5..."}

if ('md5...' == getDbToken($loginData['user'])) { } //ok

//ciacho spreparowane
a:2{s:4:"user";i:123;s:3:"key";b:1}

if (true == getDbToken($loginData['user'])) { } //hmm... true == dowolny niepusty string :)

//alternatywnie
a:2{s:4:"user";i:123;s:3:"key";b:0}
//jeśli getDbToken zwraca null, null == false - znowu się udało
