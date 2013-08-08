<?php

if ($this->getAttachmentFromContent()) {
    try{
        if (function_exists('tidy_repair_string')) {
            $xhtml = tidy_repair_string($this->getContent(), array(
                'output-xhtml' => true,
                'show-body-only' => true,
                'doctype' => 'strict',
                'drop-font-tags' => true,
                'drop-proprietary-attributes' => true,
                'lower-literals' => true,
                'quote-ampersand' => true,
                'wrap' => 0), 'utf8');
        } else {
            $xhtml = $this->getContent();
        }

        $baseUrl = $this->getBaseUrl();

        $xhtml = preg_replace('#(src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="'.$baseUrl.'$2$3',$xhtml);

        $dom = new DOMDocument(null, 'UTF-8');
        $dom->loadHTML($xhtml);
        $images = $dom->getElementsByTagName('img');

        for ($i = 0; $i < $images->length; $i++) {
            $img = $images->item($i);

            $url = $img->getAttribute('src');

            $image_http = new Zend_Http_Client($url);
            $image_content = $image_http->request()->getBody();

            $response = $image_http->getLastResponse();

            $pathinfo = pathinfo($url);

            $mime_type = $response->getHeader('Content-type');

            $mime = new Empathy_Message_Part($image_content);
            $mime->location = $url;
            $mime->type        = $mime_type;//.";\n\tname=\"".$pathinfo['basename']."\"";
            $mime->disposition = Zend_Mime::DISPOSITION_INLINE;
            $mime->encoding    = Zend_Mime::ENCODING_BASE64;
            $mime->filename    = $pathinfo['basename'];

            $oZendMail->addAttachment($mime);
        }
    } catch(Exception $e) {
        // bład niskiego znaczenia można pominać.
    }

    /*
     * Wylaczenie logowania error'ow dla DOMDocument
     */
    restore_error_handler();
}

/* ************************************************************************************************************************ */
// przyklad napisany z glowy - nie kojarze juz, w ktorym miejscu byl, pochodzacy z Rovese
// wyrzucanie wyjatku jako sposob sterowania przeplywem operacji w petli - namiastka instrukcji goto
$result = true;
try {
    foreach($products as $product) {
        // sprawdzanie, czy produkt ma costam
        if(!in_array($product, $prdAttr)) {
            throw new Empathy_Exception('product_incorrect_structure');
        }
    }
} catch(Exception $e) {
    $result = false;
}

if($result) {
    // do something
} else {
    $this->_helper->_redirector->goToRoute(array(), 'basket_checkout_simple');
}


?>