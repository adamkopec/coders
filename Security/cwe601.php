<?php
if (isset($_POST['submit'])) {
    //zrób coś

    header('Location: ' . $_GET['redirect']);
    exit();
}

?>
<form method="post" action="">
    Imię: <input type="text" name="name" />
    <input type="submit" name="submit" />
</form>

<!-- a co z http://strona.pl/?redirect=http://zuuuooo.pl/?przybadz_szatanie -->

Rozwiązanie 1:
http://strona.pl/?redirect=1

Rozwiązanie 2:
http://strona.pl/?hash=76da2763u998acef87e&redirect=http://dobro.pl/

Rozwiązanie 3:
http://strona.pl/?redirect=http://tylko-z-bialej-listy.pl/  (uwaga na przeglądarki, csrf)