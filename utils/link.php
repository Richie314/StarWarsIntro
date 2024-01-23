<?php

class Link
{
    public $Url;
    public $Img;
    public $Text;
    public function __construct($url, $img, $text)
    {
        $this->Url = $url;
        $this->Img = $img;
        $this->Text = htmlspecialchars($text);
    }
}